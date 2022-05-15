<?php

declare(strict_types=1);

namespace ElliotJReed\RoyalMail\Tracking\Entity\Event;

final class Response extends \ElliotJReed\RoyalMail\Tracking\Entity\Response
{
    private ?MailPieces $mailPieces = null;

    /**
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\Event\MailPieces|null Object containing tracking information for
     *                                                                      the requested mail item. This will only be
     *                                                                      provided in the event that no error
     *                                                                      information is returned.
     */
    public function getMailPieces(): ?MailPieces
    {
        return $this->mailPieces;
    }

    /**
     * @param \ElliotJReed\RoyalMail\Tracking\Entity\Event\MailPieces|null $mailPieces Object containing tracking
     *                                                                                 information for the requested
     *                                                                                 mail item. This will only be
     *                                                                                 provided in the event that no
     *                                                                                 error information is returned.
     *
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\Event\Response
     */
    public function setMailPieces(?MailPieces $mailPieces): self
    {
        $this->mailPieces = $mailPieces;

        return $this;
    }
}
