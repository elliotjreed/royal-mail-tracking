<?php

declare(strict_types=1);

namespace ElliotJReed\RoyalMail\Tracking\Entity\Event;

use DateTimeImmutable;

final class Signature
{
    private ?string $recipientName = null;
    private ?DateTimeImmutable $signatureDateTime = null;
    private ?string $imageId = null;

    /**
     * @return string|null name of the person who provided the signature
     */
    public function getRecipientName(): ?string
    {
        return $this->recipientName;
    }

    /**
     * @param string|null $recipientName name of the person who provided the signature
     */
    public function setRecipientName(?string $recipientName): self
    {
        $this->recipientName = $recipientName;

        return $this;
    }

    /**
     * @return DateTimeImmutable|null date and time the signature was captured
     */
    public function getSignatureDateTime(): ?DateTimeImmutable
    {
        return $this->signatureDateTime;
    }

    /**
     * @param DateTimeImmutable|null $signatureDateTime date and time the signature was captured
     */
    public function setSignatureDateTime(?DateTimeImmutable $signatureDateTime): self
    {
        $this->signatureDateTime = $signatureDateTime;

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
     */
    public function setImageId(?string $imageId): self
    {
        $this->imageId = $imageId;

        return $this;
    }
}
