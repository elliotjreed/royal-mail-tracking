<?php

declare(strict_types=1);

namespace ElliotJReed\RoyalMail\Tracking\Entity;

use DateTimeImmutable;

final class Signature
{
    private ?string $recipientName = null;
    private ?DateTimeImmutable $signatureDateTime = null;
    private ?string $imageId = null;

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
}
