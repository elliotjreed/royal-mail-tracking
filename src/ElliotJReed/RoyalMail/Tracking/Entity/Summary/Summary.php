<?php

declare(strict_types=1);

namespace ElliotJReed\RoyalMail\Tracking\Entity\Summary;

use DateTimeImmutable;
use ElliotJReed\RoyalMail\Tracking\Entity\InternationalPostalProvider;

final class Summary
{
    private ?string $uniqueItemId = null;
    private ?string $oneDBarcode = null;
    private ?string $productId = null;
    private ?string $productName = null;
    private ?string $productDescription = null;
    private ?string $productCategory = null;
    private ?string $destinationCountryCode = null;
    private ?string $destinationCountryName = null;
    private ?string $originCountryCode = null;
    private ?string $originCountryName = null;
    private ?string $lastEventCode = null;
    private ?string $lastEventName = null;
    private ?DateTimeImmutable $lastEventDateTime = null;
    private ?string $lastEventLocationName = null;
    private ?string $statusDescription = null;
    private ?string $statusCategory = null;
    private ?string $statusHelpText = null;
    private ?string $summaryLine = null;
    private ?InternationalPostalProvider $internationalPostalProvider = null;

    /**
     * @return string|null 2D barcode for the mail piece
     */
    public function getUniqueItemId(): ?string
    {
        return $this->uniqueItemId;
    }

    /**
     * @param string|null $uniqueItemId 2D barcode for the mail piece
     */
    public function setUniqueItemId(?string $uniqueItemId): self
    {
        $this->uniqueItemId = $uniqueItemId;

        return $this;
    }

    /**
     * @return string|null 1D barcode for the mail piece
     */
    public function getOneDBarcode(): ?string
    {
        return $this->oneDBarcode;
    }

    /**
     * @param string|null $oneDBarcode 1D barcode for the mail piece
     */
    public function setOneDBarcode(?string $oneDBarcode): self
    {
        $this->oneDBarcode = $oneDBarcode;

        return $this;
    }

    /**
     * @return string|null identifier for the tracked product
     */
    public function getProductId(): ?string
    {
        return $this->productId;
    }

    /**
     * @param string|null $productId identifier for the tracked product
     */
    public function setProductId(?string $productId): self
    {
        $this->productId = $productId;

        return $this;
    }

    /**
     * @return string|null name of the tracked product
     */
    public function getProductName(): ?string
    {
        return $this->productName;
    }

    /**
     * @param string|null $productName name of the tracked product
     */
    public function setProductName(?string $productName): self
    {
        $this->productName = $productName;

        return $this;
    }

    /**
     * @return string|null description of tracked product
     */
    public function getProductDescription(): ?string
    {
        return $this->productDescription;
    }

    /**
     * @param string|null $productDescription description of tracked product
     */
    public function setProductDescription(?string $productDescription): self
    {
        $this->productDescription = $productDescription;

        return $this;
    }

    /**
     * @return string|null category for tracked product
     */
    public function getProductCategory(): ?string
    {
        return $this->productCategory;
    }

    /**
     * @param string|null $productCategory category for tracked product
     */
    public function setProductCategory(?string $productCategory): self
    {
        $this->productCategory = $productCategory;

        return $this;
    }

    /**
     * @return string|null destination country code
     */
    public function getDestinationCountryCode(): ?string
    {
        return $this->destinationCountryCode;
    }

    /**
     * @param string|null $destinationCountryCode destination country code
     */
    public function setDestinationCountryCode(?string $destinationCountryCode): self
    {
        $this->destinationCountryCode = $destinationCountryCode;

        return $this;
    }

    /**
     * @return string|null destination country name
     */
    public function getDestinationCountryName(): ?string
    {
        return $this->destinationCountryName;
    }

    /**
     * @param string|null $destinationCountryName destination country name
     */
    public function setDestinationCountryName(?string $destinationCountryName): self
    {
        $this->destinationCountryName = $destinationCountryName;

        return $this;
    }

    /**
     * @return string|null origin country code
     */
    public function getOriginCountryCode(): ?string
    {
        return $this->originCountryCode;
    }

    /**
     * @param string|null $originCountryCode origin country code
     */
    public function setOriginCountryCode(?string $originCountryCode): self
    {
        $this->originCountryCode = $originCountryCode;

        return $this;
    }

    /**
     * @return string|null origin country name
     */
    public function getOriginCountryName(): ?string
    {
        return $this->originCountryName;
    }

    /**
     * @param string|null $originCountryName origin country name
     */
    public function setOriginCountryName(?string $originCountryName): self
    {
        $this->originCountryName = $originCountryName;

        return $this;
    }

    /**
     * @return string|null code associated with the latest mail piece event
     */
    public function getLastEventCode(): ?string
    {
        return $this->lastEventCode;
    }

    /**
     * @param string|null $lastEventCode code associated with the latest mail piece event
     */
    public function setLastEventCode(?string $lastEventCode): self
    {
        $this->lastEventCode = $lastEventCode;

        return $this;
    }

    /**
     * @return string|null name of the latest mail piece event
     */
    public function getLastEventName(): ?string
    {
        return $this->lastEventName;
    }

    /**
     * @param string|null $lastEventName name of the latest mail piece event
     */
    public function setLastEventName(?string $lastEventName): self
    {
        $this->lastEventName = $lastEventName;

        return $this;
    }

    /**
     * @return DateTimeImmutable|null date and time associated with the latest mail piece event
     */
    public function getLastEventDateTime(): ?DateTimeImmutable
    {
        return $this->lastEventDateTime;
    }

    /**
     * @param DateTimeImmutable|null $lastEventDateTime date and time associated with the latest mail piece event
     */
    public function setLastEventDateTime(?DateTimeImmutable $lastEventDateTime): self
    {
        $this->lastEventDateTime = $lastEventDateTime;

        return $this;
    }

    /**
     * @return string|null location name associated with the latest mail piece event
     */
    public function getLastEventLocationName(): ?string
    {
        return $this->lastEventLocationName;
    }

    /**
     * @param string|null $lastEventLocationName location name associated with the latest mail piece event
     */
    public function setLastEventLocationName(?string $lastEventLocationName): self
    {
        $this->lastEventLocationName = $lastEventLocationName;

        return $this;
    }

    /**
     * @return string|null status description associated with the latest mail piece event
     */
    public function getStatusDescription(): ?string
    {
        return $this->statusDescription;
    }

    /**
     * @param string|null $statusDescription status description associated with the latest mail piece event
     */
    public function setStatusDescription(?string $statusDescription): self
    {
        $this->statusDescription = $statusDescription;

        return $this;
    }

    /**
     * @return string|null status category associated with the latest mail piece event
     */
    public function getStatusCategory(): ?string
    {
        return $this->statusCategory;
    }

    /**
     * @param string|null $statusCategory status category associated with the latest mail piece event
     */
    public function setStatusCategory(?string $statusCategory): self
    {
        $this->statusCategory = $statusCategory;

        return $this;
    }

    /**
     * @return string|null status help text associated with the latest mail piece event
     */
    public function getStatusHelpText(): ?string
    {
        return $this->statusHelpText;
    }

    /**
     * @param string|null $statusHelpText status help text associated with the latest mail piece event
     */
    public function setStatusHelpText(?string $statusHelpText): self
    {
        $this->statusHelpText = $statusHelpText;

        return $this;
    }

    /**
     * @return string|null summary message for mail item's last tracking scan
     */
    public function getSummaryLine(): ?string
    {
        return $this->summaryLine;
    }

    /**
     * @param string|null $summaryLine summary message for mail item's last tracking scan
     */
    public function setSummaryLine(?string $summaryLine): self
    {
        $this->summaryLine = $summaryLine;

        return $this;
    }

    /**
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\InternationalPostalProvider|null object containing details of
     *                                                                                 international postal
     *                                                                                 organisation
     */
    public function getInternationalPostalProvider(): ?InternationalPostalProvider
    {
        return $this->internationalPostalProvider;
    }

    /**
     * @param \ElliotJReed\RoyalMail\Tracking\Entity\InternationalPostalProvider|null $internationalPostalProvider object containing details of
     *                                                                                                             international postal organisation
     */
    public function setInternationalPostalProvider(?InternationalPostalProvider $internationalPostalProvider): self
    {
        $this->internationalPostalProvider = $internationalPostalProvider;

        return $this;
    }
}
