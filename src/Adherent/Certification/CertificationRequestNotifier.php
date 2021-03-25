<?php

namespace App\Adherent\Certification;

use App\Entity\CertificationRequest;
use App\Mailer\MailerService;
use App\Mailer\Message\CertificationRequestApprovedMessage;
use App\Mailer\Message\CertificationRequestBlockedMessage;
use App\Mailer\Message\CertificationRequestPendingMessage;
use App\Mailer\Message\CertificationRequestRefusedMessage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CertificationRequestNotifier
{
    private $mailer;
    private $urlGenerator;
    private $translator;

    public function __construct(
        MailerService $transactionalMailer,
        UrlGeneratorInterface $urlGenerator,
        TranslatorInterface $translator
    ) {
        $this->mailer = $transactionalMailer;
        $this->urlGenerator = $urlGenerator;
        $this->translator = $translator;
    }

    public function sendPendingMessage(CertificationRequest $certificationRequest): void
    {
        $this->mailer->sendMessage(CertificationRequestPendingMessage::create($certificationRequest));
    }

    public function sendApprovalMessage(CertificationRequest $certificationRequest): void
    {
        $this->mailer->sendMessage(CertificationRequestApprovedMessage::create($certificationRequest));
    }

    public function sendRefusalMessage(CertificationRequest $certificationRequest): void
    {
        $refusalReason = $certificationRequest->isRefusedWithOtherReason()
            ? $certificationRequest->getCustomRefusalReason()
            : $this->translator->trans($certificationRequest->getRefusalReasonKey())
        ;

        $this->mailer->sendMessage(CertificationRequestRefusedMessage::create(
            $certificationRequest,
            $refusalReason,
            $this->urlGenerator->generate('app_certification_request_form', [], UrlGeneratorInterface::ABSOLUTE_URL)
        ));
    }

    public function sendBlockMessage(CertificationRequest $certificationRequest): void
    {
        $this->mailer->sendMessage(CertificationRequestBlockedMessage::create($certificationRequest));
    }
}
