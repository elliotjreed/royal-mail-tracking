<?php

declare(strict_types=1);

namespace ElliotJReed\RoyalMail\Tracking\Entity;

final class MailPiece
{
    private ?string $mailPieceId = null;
    private ?string $carrierShortName = null;
    private ?string $carrierFullName = null;
    private ?Summary $summary = null;
    private ?Signature $signature = null;
    private ?EstimatedDelivery $estimatedDelivery = null;
    /** @var \ElliotJReed\RoyalMail\Tracking\Entity\Event[] */
    private ?array $events = null;
    private ?Links $links = null;
    private ?ErrorResponseError $error = null;

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
     * @return $this
     */
    public function setMailPieceId(?string $mailPieceId): self
    {
        $this->mailPieceId = $mailPieceId;

        return $this;
    }

    /**
     * @return string|null Short name of the carrier
     */
    public function getCarrierShortName(): ?string
    {
        return $this->carrierShortName;
    }

    /**
     * @param string|null $carrierShortName Short name of the carrier
     *
     * @return $this
     */
    public function setCarrierShortName(?string $carrierShortName): self
    {
        $this->carrierShortName = $carrierShortName;

        return $this;
    }

    /**
     * @return string|null Full name of the carrier
     */
    public function getCarrierFullName(): ?string
    {
        return $this->carrierFullName;
    }

    /**
     * @param string|null $carrierFullName Full name of the carrier
     *
     * @return $this
     */
    public function setCarrierFullName(?string $carrierFullName): self
    {
        $this->carrierFullName = $carrierFullName;

        return $this;
    }

    /**
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\Summary|null Object containing mail piece summary data
     */
    public function getSummary(): ?Summary
    {
        return $this->summary;
    }

    /**
     * @param \ElliotJReed\RoyalMail\Tracking\Entity\Summary|null $summary Object containing mail piece summary data
     *
     * @return $this
     */
    public function setSummary(?Summary $summary): self
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\Signature|null Object containing signature metadata data
     */
    public function getSignature(): ?Signature
    {
        return $this->signature;
    }

    /**
     * @param \ElliotJReed\RoyalMail\Tracking\Entity\Signature|null $signature Object containing signature metadata data
     *
     * @return $this
     */
    public function setSignature(?Signature $signature): self
    {
        $this->signature = $signature;

        return $this;
    }

    /**
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\EstimatedDelivery|null Object containing the estimated delivery
     *                                                                       data
     */
    public function getEstimatedDelivery(): ?EstimatedDelivery
    {
        return $this->estimatedDelivery;
    }

    /**
     * @param \ElliotJReed\RoyalMail\Tracking\Entity\EstimatedDelivery|null $estimatedDelivery Object containing the
     *                                                                                         estimated delivery data
     *
     * @return $this
     */
    public function setEstimatedDelivery(?EstimatedDelivery $estimatedDelivery): self
    {
        $this->estimatedDelivery = $estimatedDelivery;

        return $this;
    }

    /**
     * @return array|null Array containing mail piece event information
     */
    public function getEvents(): ?array
    {
        return $this->events;
    }

    /**
     * @param \ElliotJReed\RoyalMail\Tracking\Entity\Event $event Mail piece event information
     *
     * @return $this
     */
    public function addEvent(Event $event): self
    {
        $this->events[] = $event;

        return $this;
    }

    /**
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\Links|null Object containing API links
     */
    public function getLinks(): ?Links
    {
        return $this->links;
    }

    /**
     * @param \ElliotJReed\RoyalMail\Tracking\Entity\Links|null $links Object containing API links
     *
     * @return $this
     */
    public function setLinks(?Links $links): self
    {
        $this->links = $links;

        return $this;
    }

    /**
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\ErrorResponseError|null Error information
     */
    public function getError(): ?ErrorResponseError
    {
        return $this->error;
    }

    /**
     * @param \ElliotJReed\RoyalMail\Tracking\Entity\ErrorResponseError|null $error Error information
     *
     * @return $this
     */
    public function setError(?ErrorResponseError $error): self
    {
        $this->error = $error;

        return $this;
    }
}
