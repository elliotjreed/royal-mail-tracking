<?php

declare(strict_types=1);

namespace ElliotJReed\RoyalMail\Tracking\Entity\Summary;

use ElliotJReed\RoyalMail\Tracking\Entity\ErrorResponse;

final class MailPieces
{
    private string $mailPieceId;
    private ?string $carrierShortName = null;
    private ?string $carrierFullName = null;
    private ?Summary $summary = null;
    private ?Links $links = null;
    private ?ErrorResponse $error = null;

    /**
     * @return string|null 1D or 2D barcode provided in the API request
     */
    public function getMailPieceId(): ?string
    {
        return $this->mailPieceId;
    }

    /**
     * @param string|null $mailPieceId 1D or 2D barcode provided in the API request
     *
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\Summary\MailPieces
     */
    public function setMailPieceId(?string $mailPieceId): self
    {
        $this->mailPieceId = $mailPieceId;

        return $this;
    }

    /**
     * @return string|null short name of the carrier
     */
    public function getCarrierShortName(): ?string
    {
        return $this->carrierShortName;
    }

    /**
     * @param string|null $carrierShortName short name of the carrier
     *
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\Summary\MailPieces
     */
    public function setCarrierShortName(?string $carrierShortName): self
    {
        $this->carrierShortName = $carrierShortName;

        return $this;
    }

    /**
     * @return string|null full name of the carrier
     */
    public function getCarrierFullName(): ?string
    {
        return $this->carrierFullName;
    }

    /**
     * @param string|null $carrierFullName full name of the carrier
     *
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\Summary\MailPieces
     */
    public function setCarrierFullName(?string $carrierFullName): self
    {
        $this->carrierFullName = $carrierFullName;

        return $this;
    }

    /**
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\Summary\Summary|null object containing summary data
     */
    public function getSummary(): ?Summary
    {
        return $this->summary;
    }

    /**
     * @param \ElliotJReed\RoyalMail\Tracking\Entity\Summary\Summary|null $summary object containing summary data
     *
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\Summary\MailPieces
     */
    public function setSummary(?Summary $summary): self
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\Event\Links|null object containing API links
     */
    public function getLinks(): ?Links
    {
        return $this->links;
    }

    /**
     * @param \ElliotJReed\RoyalMail\Tracking\Entity\Summary\Links|null $links object containing API links
     *
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\Summary\MailPieces
     */
    public function setLinks(?Links $links): self
    {
        $this->links = $links;

        return $this;
    }

    /**
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\ErrorResponse|null container for mail piece related error
     *                                                                   information
     */
    public function getError(): ?ErrorResponse
    {
        return $this->error;
    }

    /**
     * @param \ElliotJReed\RoyalMail\Tracking\Entity\ErrorResponse|null $error container for mail piece related error
     *                                                                         information
     *
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\Summary\MailPieces
     */
    public function setError(?ErrorResponse $error): self
    {
        $this->error = $error;

        return $this;
    }
}
