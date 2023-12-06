<?php

declare(strict_types=1);

namespace ElliotJReed\RoyalMail\Tracking\Entity\Event;

use ElliotJReed\RoyalMail\Tracking\Entity\Summary\Summary;

final class MailPieces
{
    private string $mailPieceId;
    private string $carrierShortName;
    private string $carrierFullName;
    private ?Summary $summary = null;
    private ?Signature $signature = null;
    private ?EstimatedDelivery $estimatedDelivery = null;
    private array $events = [];
    private ?Links $links = null;

    /**
     * @return string 1D or 2D barcode provided in the API request
     */
    public function getMailPieceId(): string
    {
        return $this->mailPieceId;
    }

    /**
     * @param string $mailPieceId 1D or 2D barcode provided in the API request
     */
    public function setMailPieceId(string $mailPieceId): self
    {
        $this->mailPieceId = $mailPieceId;

        return $this;
    }

    /**
     * @return string short name of the carrier
     */
    public function getCarrierShortName(): string
    {
        return $this->carrierShortName;
    }

    /**
     * @param string $carrierShortName short name of the carrier
     */
    public function setCarrierShortName(string $carrierShortName): self
    {
        $this->carrierShortName = $carrierShortName;

        return $this;
    }

    /**
     * @return string full name of the carrier
     */
    public function getCarrierFullName(): string
    {
        return $this->carrierFullName;
    }

    /**
     * @param string $carrierFullName full name of the carrier
     */
    public function setCarrierFullName(string $carrierFullName): self
    {
        $this->carrierFullName = $carrierFullName;

        return $this;
    }

    /**
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\Summary\Summary|null object containing mail piece summary data
     */
    public function getSummary(): ?Summary
    {
        return $this->summary;
    }

    /**
     * @param \ElliotJReed\RoyalMail\Tracking\Entity\Summary\Summary|null $summary object containing mail piece summary
     *                                                                             data
     */
    public function setSummary(?Summary $summary): self
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\Event\Signature|null object containing signature metadata data
     */
    public function getSignature(): ?Signature
    {
        return $this->signature;
    }

    /**
     * @param \ElliotJReed\RoyalMail\Tracking\Entity\Event\Signature|null $signature object containing signature
     *                                                                               metadata
     */
    public function setSignature(?Signature $signature): self
    {
        $this->signature = $signature;

        return $this;
    }

    /**
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\Event\EstimatedDelivery|null object containing the estimated
     *                                                                             delivery data
     */
    public function getEstimatedDelivery(): ?EstimatedDelivery
    {
        return $this->estimatedDelivery;
    }

    /**
     * @param \ElliotJReed\RoyalMail\Tracking\Entity\Event\EstimatedDelivery|null $estimatedDelivery Object containing
     *                                                                                               the estimated
     *                                                                                               delivery data
     */
    public function setEstimatedDelivery(?EstimatedDelivery $estimatedDelivery): self
    {
        $this->estimatedDelivery = $estimatedDelivery;

        return $this;
    }

    /**
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\Event\Event[] array containing mail piece event information
     */
    public function getEvents(): array
    {
        return $this->events;
    }

    /**
     * @param \ElliotJReed\RoyalMail\Tracking\Entity\Event\Event $event mail piece event information
     */
    public function addEvent(Event $event): self
    {
        $this->events[] = $event;

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
     * @param \ElliotJReed\RoyalMail\Tracking\Entity\Event\Links|null $links object containing API links
     */
    public function setLinks(?Links $links): self
    {
        $this->links = $links;

        return $this;
    }
}
