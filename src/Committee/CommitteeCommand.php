<?php

namespace App\Committee;

use App\Address\Address;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Validator\CommitteeProvisionalSupervisor as AssertCommitteeProvisionalSupervisorValid;
use App\Validator\UniqueCommittee as AssertUniqueCommittee;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @AssertUniqueCommittee
 */
class CommitteeCommand
{
    /** @var Committee */
    protected $committee;

    /**
     * @Assert\NotBlank
     * @Assert\Length(min=2, max=50)
     */
    public $name;

    /**
     * @Assert\NotBlank
     * @Assert\Length(min=5, max=140, minMessage="committee.description.min_length", maxMessage="committee.description.max_length")
     */
    public $description;

    /**
     * The committee address.
     *
     * @var Address
     *
     * @Assert\Valid
     */
    protected $address;

    /**
     * @AssertPhoneNumber(defaultRegion="FR")
     * @Assert\Expression(
     *     expression="(value == null and this.getCommittee()) or (value != '' and value != null)",
     *     message="common.phone_number.required"
     * )
     */
    protected $phone;

    /**
     * @Assert\Url
     * @Assert\Length(max=255)
     */
    public $facebookPageUrl;

    /**
     * @Assert\Length(min=1, max=15)
     * @Assert\Regex("/^@?([a-zA-Z0-9_]){1,15}$/", message="common.twitter_nickname.invalid_format")
     */
    public $twitterNickname;

    /**
     * @var Adherent|null
     *
     * @AssertCommitteeProvisionalSupervisorValid(gender="male", errorPath="provisionalSupervisorMale", groups={"with_provisional_supervisors"})
     * @Assert\Expression(
     *     expression="(value == null and this.getProvisionalSupervisorFemale() != null) or value != null",
     *     message="committee.provisional_supervisor.empty",
     * groups={"with_provisional_supervisors"})
     * )
     */
    protected $provisionalSupervisorMale;

    /**
     * @var Adherent|null
     *
     * @AssertCommitteeProvisionalSupervisorValid(gender="female", errorPath="provisionalSupervisorFemale", groups={"with_provisional_supervisors"})
     */
    protected $provisionalSupervisorFemale;

    protected function __construct(Address $address = null)
    {
        $this->address = $address ?: new Address();
    }

    public static function createFromCommittee(Committee $committee): self
    {
        $dto = new self(Address::createFromAddress($committee->getPostAddress()));
        $dto->name = $committee->getName();
        $dto->description = $committee->getDescription();
        $dto->phone = $committee->getPhone();
        $dto->facebookPageUrl = $committee->getFacebookPageUrl();
        $dto->twitterNickname = $committee->getTwitterNickname();
        $dto->committee = $committee;

        return $dto;
    }

    public function getCityName(): string
    {
        return $this->address->getCityName();
    }

    public function setPhone(PhoneNumber $phone = null): void
    {
        $this->phone = $phone;
    }

    public function getPhone(): ?PhoneNumber
    {
        return $this->phone;
    }

    public function getCommittee(): ?Committee
    {
        return $this->committee;
    }

    public function setAddress(Address $address): void
    {
        $this->address = $address;
    }

    public function getAddress(): Address
    {
        return $this->address;
    }

    public function setProvisionalSupervisorFemale(?Adherent $adherent): void
    {
        $this->provisionalSupervisorFemale = $adherent;
    }

    public function getProvisionalSupervisorFemale(): ?Adherent
    {
        return $this->provisionalSupervisorFemale;
    }

    public function setProvisionalSupervisorMale(?Adherent $adherent): void
    {
        $this->provisionalSupervisorMale = $adherent;
    }

    public function getProvisionalSupervisorMale(): ?Adherent
    {
        return $this->provisionalSupervisorMale;
    }
}
