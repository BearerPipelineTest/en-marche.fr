<?php

namespace App\Committee;

use App\Committee\Event\FollowCommitteeEvent;
use App\Committee\Event\UnfollowCommitteeEvent;
use App\Entity\Administrator;
use App\Entity\Committee;
use App\Entity\CommitteeMembership;
use App\Entity\Reporting\CommitteeMembershipAction;
use App\Entity\Reporting\CommitteeMembershipHistory;
use App\Entity\Reporting\CommitteeMergeHistory;
use App\Events;
use App\Membership\UserEvents;
use App\Repository\CommitteeMembershipRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CommitteeMergeCommandHandler
{
    private $dispatcher;
    private $em;
    private $committeeMembershipRepository;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        EntityManagerInterface $em,
        CommitteeMembershipRepository $committeeMembershipRepository
    ) {
        $this->dispatcher = $dispatcher;
        $this->em = $em;
        $this->committeeMembershipRepository = $committeeMembershipRepository;
    }

    public function countNewMembers(CommitteeMergeCommand $committeeMergeCommand): int
    {
        $sourceCommittee = $committeeMergeCommand->getSourceCommittee();
        $destinationCommittee = $committeeMergeCommand->getDestinationCommittee();

        return \count($this
            ->committeeMembershipRepository
            ->findMembersToMerge($sourceCommittee, $destinationCommittee)
        );
    }

    public function handle(CommitteeMergeCommand $committeeMergeCommand): void
    {
        $sourceCommittee = $committeeMergeCommand->getSourceCommittee();
        $destinationCommittee = $committeeMergeCommand->getDestinationCommittee();
        $administrator = $committeeMergeCommand->getMergedBy();

        try {
            $this->em->beginTransaction();

            $newFollowers = $this->committeeMembershipRepository->findMembersToMerge($sourceCommittee, $destinationCommittee);

            $mergedMemberships = [];
            foreach ($newFollowers as $newFollower) {
                $adherent = $newFollower->getAdherent();
                $this->em->persist($membership = $adherent->followCommittee($destinationCommittee, $newFollower->getSubscriptionDate()));

                $this->em->persist($this->createCommitteeMembershipHistory($membership, CommitteeMembershipAction::JOIN()));

                $mergedMemberships[] = $membership;
            }

            $this
                ->committeeMembershipRepository
                ->findHostMemberships($sourceCommittee)
                ->map(function (CommitteeMembership $membership) {
                    $membership->setPrivilege(CommitteeMembership::COMMITTEE_FOLLOWER);
                })
            ;

            if ($supervisorMembership = $this->committeeMembershipRepository->findSupervisorMembership($sourceCommittee)) {
                $supervisorMembership->setPrivilege(CommitteeMembership::COMMITTEE_FOLLOWER);
            }

            $sourceCommittee->refused();

            $this->em->persist($this->createCommitteeMergeHistory(
                $sourceCommittee,
                $destinationCommittee,
                $mergedMemberships,
                $administrator
            ));

            $this->em->flush();

            $this->transferVotingMemberships(
                $destinationCommittee,
                $this->committeeMembershipRepository->findVotingMemberships($sourceCommittee)
            );

            $this->em->commit();
        } catch (\Exception $exception) {
            $this->em->rollback();

            throw $exception;
        }

        foreach ($newFollowers as $follower) {
            $this->dispatcher->dispatch(
                UserEvents::USER_UPDATE_COMMITTEE_PRIVILEGE,
                new FollowCommitteeEvent($follower->getAdherent(), $destinationCommittee)
            );
        }

        $this->dispatchCommitteeUpdate($sourceCommittee);
        $this->dispatchCommitteeUpdate($destinationCommittee);
    }

    public function revert(CommitteeMergeHistory $committeeMergeHistory, Administrator $administrator): void
    {
        $sourceCommittee = $committeeMergeHistory->getSourceCommittee();
        $destinationCommittee = $committeeMergeHistory->getDestinationCommittee();
        $mergedMemberships = $committeeMergeHistory->getMergedMemberships();

        try {
            $this->em->beginTransaction();

            $committeeMergeHistory->revert($administrator);
            $sourceCommittee->approved();

            $this->em->flush();

            $this->transferVotingMemberships($sourceCommittee, array_filter($mergedMemberships, function (CommitteeMembership $membership) {
                return $membership->isVotingCommittee();
            }));
            $this->revertDestinationCommitteeMemberships($destinationCommittee, $mergedMemberships);

            $this->em->commit();
        } catch (\Exception $exception) {
            $this->em->rollback();

            throw $exception;
        }

        foreach ($mergedMemberships as $membership) {
            $this->dispatcher->dispatch(
                UserEvents::USER_UPDATE_COMMITTEE_PRIVILEGE,
                new UnfollowCommitteeEvent($membership->getAdherent(), $destinationCommittee)
            );
        }

        $this->dispatchCommitteeUpdate($sourceCommittee);
        $this->dispatchCommitteeUpdate($destinationCommittee);
    }

    private function revertDestinationCommitteeMemberships(Committee $committee, array $memberships): void
    {
        foreach ($memberships as $membership) {
            $committee->decrementMembersCount();

            $this->em->remove($membership);

            $this->em->persist($this->createCommitteeMembershipHistory($membership, CommitteeMembershipAction::LEAVE()));
        }

        $this->em->flush();
    }

    private function dispatchCommitteeUpdate(Committee $committee): void
    {
        $this->dispatcher->dispatch(Events::COMMITTEE_UPDATED, new CommitteeEvent($committee));
    }

    private function transferVotingMemberships(Committee $committee, array $votingMemberships): void
    {
        if (!$votingMemberships) {
            return;
        }

        $votingAdherents = array_map(static function (CommitteeMembership $membership) {
            if ($election = $membership->getCommittee()->getCommitteeElection()) {
                $membership->removeCommitteeCandidacyForElection($election);
            }

            return $membership->getAdherent();
        }, $votingMemberships);

        $this->committeeMembershipRepository->updateVoteStatusForAdherents(current($votingMemberships)->getCommittee(), $votingAdherents, false);

        $this->em->flush();

        $this->committeeMembershipRepository->updateVoteStatusForAdherents($committee, $votingAdherents, true);
    }

    private function createCommitteeMembershipHistory(
        CommitteeMembership $membership,
        CommitteeMembershipAction $action
    ): CommitteeMembershipHistory {
        return new CommitteeMembershipHistory($membership, $action);
    }

    private function createCommitteeMergeHistory(
        Committee $sourceCommittee,
        Committee $destinationCommittee,
        array $mergedMemberships,
        Administrator $administrator
    ): CommitteeMergeHistory {
        return new CommitteeMergeHistory($sourceCommittee, $destinationCommittee, $mergedMemberships, $administrator);
    }
}
