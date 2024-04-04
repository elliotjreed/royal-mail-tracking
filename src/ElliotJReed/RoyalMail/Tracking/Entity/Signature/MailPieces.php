<?php

declare(strict_types=1);

namespace ElliotJReed\RoyalMail\Tracking\Entity\Signature;

final class MailPieces
{
    private string $mailPieceId;
    private ?string $carrierShortName = null;
    private ?string $carrierFullName = null;
    private ?Signature $signature = null;
    private ?Links $links = null;

    /**
     * @return string|null 1D or 2D barcode provided in the API request
     */
    public function getMailPieceId(): ?string
    {
        return $this->mailPieceId;
    }

    /**
     * @param string|null $mailPieceId 1D or 2D barcode provided in the API request
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
     */
    public function setCarrierFullName(?string $carrierFullName): self
    {
        $this->carrierFullName = $carrierFullName;

        return $this;
    }

    /**
     * @return Signature|null object containing signature metadata
     */
    public function getSignature(): ?Signature
    {
        return $this->signature;
    }

    /**
     * @param Signature|null $signature object containing signature
     *                                  metadata
     */
    public function setSignature(?Signature $signature): self
    {
        $this->signature = $signature;

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
     * @param Links|null $links object containing API links
     */
    public function setLinks(?Links $links): self
    {
        $this->links = $links;

        return $this;
    }
}
