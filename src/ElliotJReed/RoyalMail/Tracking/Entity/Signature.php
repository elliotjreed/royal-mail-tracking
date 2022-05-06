<?php

declare(strict_types=1);

namespace ElliotJReed\RoyalMail\Tracking\Entity;

use DateTimeImmutable;

final class Signature
{
    private ?string $uniqueItemId = null;
    private ?string $recipientName = null;
    private ?DateTimeImmutable $signatureDateTime = null;
    private ?string $imageId = null;
    private ?string $oneDBarcode = null;
    private ?string $imageFormat = null;
    private ?int $height = null;
    private ?int $width = null;
    private ?string $image = null;

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
     * @return string|null Name of the person who provided the signature
     */
    public function getRecipientName(): ?string
    {
        return $this->recipientName;
    }

    /**
     * @param string|null $recipientName Name of the person who provided the signature
     *
     * @return $this
     */
    public function setRecipientName(?string $recipientName): self
    {
        $this->recipientName = $recipientName;

        return $this;
    }

    /**
     * @return \DateTimeImmutable|null Date and time the signature was captured
     */
    public function getSignatureDateTime(): ?DateTimeImmutable
    {
        return $this->signatureDateTime;
    }

    /**
     * @param \DateTimeImmutable|null $signatureDateTime Date and time the signature was captured
     *
     * @return $this
     */
    public function setSignatureDateTime(?DateTimeImmutable $signatureDateTime): self
    {
        $this->signatureDateTime = $signatureDateTime;

        return $this;
    }

    /**
     * @return string|null Identifier associated with the signature image
     */
    public function getImageId(): ?string
    {
        return $this->imageId;
    }

    /**
     * @param string|null $imageId Identifier associated with the signature image
     *
     * @return $this
     */
    public function setImageId(?string $imageId): self
    {
        $this->imageId = $imageId;

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
     * @return string|null Media type of the signature image, i.e. image/svg+xml or image/png
     */
    public function getImageFormat(): ?string
    {
        return $this->imageFormat;
    }

    /**
     * @param string|null $imageFormat Media type of the signature image, i.e. image/svg+xml or image/png
     */
    public function setImageFormat(?string $imageFormat): self
    {
        $this->imageFormat = $imageFormat;

        return $this;
    }

    /**
     * @return int|null Height of the signature image (in pixels)
     */
    public function getHeight(): ?int
    {
        return $this->height;
    }

    /**
     * @param int|null $height Height of the signature image (in pixels)
     */
    public function setHeight(?int $height): self
    {
        $this->height = $height;

        return $this;
    }

    /**
     * @return int|null Width of the signature image (in pixels)
     */
    public function getWidth(): ?int
    {
        return $this->width;
    }

    /**
     * @param int|null $width Width of the signature image (in pixels)
     */
    public function setWidth(?int $width): self
    {
        $this->width = $width;

        return $this;
    }

    /**
     * @return string|null SVG image or base64 encoded PNG image
     */
    public function getImage(): ?string
    {
        return $this->image;
    }

    /**
     * @param string|null $image SVG image or base64 encoded PNG image
     */
    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }
}
