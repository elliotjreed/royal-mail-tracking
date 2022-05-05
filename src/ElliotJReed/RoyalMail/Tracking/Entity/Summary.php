<?php

declare(strict_types=1);

namespace ElliotJReed\RoyalMail\Tracking\Entity;

use DateTimeImmutable;

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
     *
     * @return $this
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
     *
     * @return $this
     */
    public function setOneDBarcode(?string $oneDBarcode): self
    {
        $this->oneDBarcode = $oneDBarcode;

        return $this;
    }

    /**
     * @return string|null Identifier for the tracked product
     */
    public function getProductId(): ?string
    {
        return $this->productId;
    }

    /**
     * @param string|null $productId Identifier for the tracked product
     *
     * @return $this
     */
    public function setProductId(?string $productId): self
    {
        $this->productId = $productId;

        return $this;
    }

    /**
     * @return string|null Name of the tracked product
     */
    public function getProductName(): ?string
    {
        return $this->productName;
    }

    /**
     * @param string|null $productName Name of the tracked product
     *
     * @return $this
     */
    public function setProductName(?string $productName): self
    {
        $this->productName = $productName;

        return $this;
    }

    /**
     * @return string|null Description of tracked product
     */
    public function getProductDescription(): ?string
    {
        return $this->productDescription;
    }

    /**
     * @param string|null $productDescription Description of tracked product
     *
     * @return $this
     */
    public function setProductDescription(?string $productDescription): self
    {
        $this->productDescription = $productDescription;

        return $this;
    }

    /**
     * @return string|null Category for tracked product
     */
    public function getProductCategory(): ?string
    {
        return $this->productCategory;
    }

    /**
     * @param string|null $productCategory Category for tracked product
     *
     * @return $this
     */
    public function setProductCategory(?string $productCategory): self
    {
        $this->productCategory = $productCategory;

        return $this;
    }

    /**
     * @return string|null Destination country code
     */
    public function getDestinationCountryCode(): ?string
    {
        return $this->destinationCountryCode;
    }

    /**
     * @param string|null $destinationCountryCode Destination country code
     *
     * @return $this
     */
    public function setDestinationCountryCode(?string $destinationCountryCode): self
    {
        $this->destinationCountryCode = $destinationCountryCode;

        return $this;
    }

    /**
     * @return string|null Destination country name
     */
    public function getDestinationCountryName(): ?string
    {
        return $this->destinationCountryName;
    }

    /**
     * @param string|null $destinationCountryName Destination country name
     *
     * @return $this
     */
    public function setDestinationCountryName(?string $destinationCountryName): self
    {
        $this->destinationCountryName = $destinationCountryName;

        return $this;
    }

    /**
     * @return string|null Origin country code
     */
    public function getOriginCountryCode(): ?string
    {
        return $this->originCountryCode;
    }

    /**
     * @param string|null $originCountryCode Origin country code
     *
     * @return $this
     */
    public function setOriginCountryCode(?string $originCountryCode): self
    {
        $this->originCountryCode = $originCountryCode;

        return $this;
    }

    /**
     * @return string|null Origin country name
     */
    public function getOriginCountryName(): ?string
    {
        return $this->originCountryName;
    }

    /**
     * @param string|null $originCountryName Origin country name
     *
     * @return $this
     */
    public function setOriginCountryName(?string $originCountryName): self
    {
        $this->originCountryName = $originCountryName;

        return $this;
    }

    /**
     * @return string|null Code associated with the latest mail piece event
     */
    public function getLastEventCode(): ?string
    {
        return $this->lastEventCode;
    }

    /**
     * @param string|null $lastEventCode Code associated with the latest mail piece event
     *
     * @return $this
     */
    public function setLastEventCode(?string $lastEventCode): self
    {
        $this->lastEventCode = $lastEventCode;

        return $this;
    }

    /**
     * @return string|null Name of the latest mail piece event
     */
    public function getLastEventName(): ?string
    {
        return $this->lastEventName;
    }

    /**
     * @param string|null $lastEventName Name of the latest mail piece event
     *
     * @return $this
     */
    public function setLastEventName(?string $lastEventName): self
    {
        $this->lastEventName = $lastEventName;

        return $this;
    }

    /**
     * @return DateTimeImmutable|null Date and time associated with the latest mail piece event
     */
    public function getLastEventDateTime(): ?DateTimeImmutable
    {
        return $this->lastEventDateTime;
    }

    /**
     * @param \DateTimeImmutable|null $lastEventDateTime Date and time associated with the latest mail piece event
     *
     * @return $this
     */
    public function setLastEventDateTime(?DateTimeImmutable $lastEventDateTime): self
    {
        $this->lastEventDateTime = $lastEventDateTime;

        return $this;
    }

    /**
     * @return string|null Location name associated with the latest mail piece event
     */
    public function getLastEventLocationName(): ?string
    {
        return $this->lastEventLocationName;
    }

    /**
     * @param string|null $lastEventLocationName Location name associated with the latest mail piece event
     *
     * @return $this
     */
    public function setLastEventLocationName(?string $lastEventLocationName): self
    {
        $this->lastEventLocationName = $lastEventLocationName;

        return $this;
    }

    /**
     * @return string|null Status description associated with the latest mail piece event
     */
    public function getStatusDescription(): ?string
    {
        return $this->statusDescription;
    }

    /**
     * @param string|null $statusDescription Status description associated with the latest mail piece event
     *
     * @return $this
     */
    public function setStatusDescription(?string $statusDescription): self
    {
        $this->statusDescription = $statusDescription;

        return $this;
    }

    /**
     * @return string|null Status category associated with the latest mail piece event
     */
    public function getStatusCategory(): ?string
    {
        return $this->statusCategory;
    }

    /**
     * @param string|null $statusCategory Status category associated with the latest mail piece event
     *
     * @return $this
     */
    public function setStatusCategory(?string $statusCategory): self
    {
        $this->statusCategory = $statusCategory;

        return $this;
    }

    /**
     * @return string|null Status help text associated with the latest mail piece event
     */
    public function getStatusHelpText(): ?string
    {
        return $this->statusHelpText;
    }

    /**
     * @param string|null $statusHelpText Status help text associated with the latest mail piece event
     *
     * @return $this
     */
    public function setStatusHelpText(?string $statusHelpText): self
    {
        $this->statusHelpText = $statusHelpText;

        return $this;
    }

    /**
     * @return string|null Summary message for mail item's last tracking scan
     */
    public function getSummaryLine(): ?string
    {
        return $this->summaryLine;
    }

    /**
     * @param string|null $summaryLine Summary message for mail item's last tracking scan
     *
     * @return $this
     */
    public function setSummaryLine(?string $summaryLine): self
    {
        $this->summaryLine = $summaryLine;

        return $this;
    }

    /**
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\InternationalPostalProvider|null Object containing details of
     *                                                                                 international postal organisation
     */
    public function getInternationalPostalProvider(): ?InternationalPostalProvider
    {
        return $this->internationalPostalProvider;
    }

    /**
     * @param \ElliotJReed\RoyalMail\Tracking\Entity\InternationalPostalProvider|null $internationalPostalProvider
     *                                                                                                             Object containing details of
     *                                                                                                             international postal organisation
     *
     * @return $this
     */
    public function setInternationalPostalProvider(?InternationalPostalProvider $internationalPostalProvider): self
    {
        $this->internationalPostalProvider = $internationalPostalProvider;

        return $this;
    }
}
