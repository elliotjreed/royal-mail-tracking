<?php

declare(strict_types=1);

namespace ElliotJReed\RoyalMail\Tracking\Entity\Signature;

final class Signature
{
    private ?string $uniqueItemId = null;
    private ?string $oneDBarcode = null;
    private ?string $recipientName = null;
    private ?\DateTimeImmutable $signatureDateTime = null;
    private string $imageFormat = '';
    private ?string $imageId = null;
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
     *
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\Signature\Signature
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
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\Signature\Signature
     */
    public function setOneDBarcode(?string $oneDBarcode): self
    {
        $this->oneDBarcode = $oneDBarcode;

        return $this;
    }

    /**
     * @return string|null name of the person who provided the signature
     */
    public function getRecipientName(): ?string
    {
        return $this->recipientName;
    }

    /**
     * @param string|null $recipientName name of the person who provided the signature
     *
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\Signature\Signature
     */
    public function setRecipientName(?string $recipientName): self
    {
        $this->recipientName = $recipientName;

        return $this;
    }

    /**
     * @return \DateTimeImmutable|null date and time the signature was captured
     */
    public function getSignatureDateTime(): ?\DateTimeImmutable
    {
        return $this->signatureDateTime;
    }

    /**
     * @param \DateTimeImmutable|null $signatureDateTime date and time the signature was captured
     *
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\Signature\Signature
     */
    public function setSignatureDateTime(?\DateTimeImmutable $signatureDateTime): self
    {
        $this->signatureDateTime = $signatureDateTime;

        return $this;
    }

    /**
     * @return string Media type of the signature image, i.e. image/svg+xml or image/png.
     */
    public function getImageFormat(): string
    {
        return $this->imageFormat;
    }

    /**
     * @param string $imageFormat Media type of the signature image, i.e. image/svg+xml or image/png.
     *
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\Signature\Signature
     */
    public function setImageFormat(string $imageFormat): self
    {
        $this->imageFormat = $imageFormat;

        return $this;
    }

    /**
     * @return string|null identifier associated with the signature image
     */
    public function getImageId(): ?string
    {
        return $this->imageId;
    }

    /**
     * @param string|null $imageId identifier associated with the signature image
     *
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\Signature\Signature
     */
    public function setImageId(?string $imageId): self
    {
        $this->imageId = $imageId;

        return $this;
    }

    /**
     * @return int|null height of the signature image (in pixels)
     */
    public function getHeight(): ?int
    {
        return $this->height;
    }

    /**
     * @param int|null $height height of the signature image (in pixels)
     *
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\Signature\Signature
     */
    public function setHeight(?int $height): self
    {
        $this->height = $height;

        return $this;
    }

    /**
     * @return int|null width of the signature image (in pixels)
     */
    public function getWidth(): ?int
    {
        return $this->width;
    }

    /**
     * @param int|null $width width of the signature image (in pixels)
     *
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\Signature\Signature
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
     *
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\Signature\Signature
     */
    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }
}
