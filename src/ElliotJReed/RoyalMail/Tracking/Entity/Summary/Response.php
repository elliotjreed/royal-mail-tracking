<?php

declare(strict_types=1);

namespace ElliotJReed\RoyalMail\Tracking\Entity\Summary;

final class Response extends \ElliotJReed\RoyalMail\Tracking\Entity\Response
{
    private array $mailPieces = [];

    /**
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\Summary\MailPieces[] Object containing tracking information for
     *                                                                     the requested mail item. This will only be
     *                                                                     provided in the event that no error
     *                                                                     information is returned.
     */
    public function getMailPieces(): array
    {
        return $this->mailPieces;
    }

    /**
     * @param \ElliotJReed\RoyalMail\Tracking\Entity\Summary\MailPieces[] $mailPieces Object containing tracking
     *                                                                                information for the requested
     *                                                                                mail item. This will only be
     *                                                                                provided in the event that no
     *                                                                                error information is returned.
     *
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\Summary\Response
     */
    public function setMailPieces(array $mailPieces): self
    {
        $this->mailPieces = $mailPieces;

        return $this;
    }
}
