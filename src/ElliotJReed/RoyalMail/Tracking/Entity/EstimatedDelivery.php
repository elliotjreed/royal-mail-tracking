<?php

declare(strict_types=1);

namespace ElliotJReed\RoyalMail\Tracking\Entity;

use DateTimeImmutable;

final class EstimatedDelivery
{
    private ?DateTimeImmutable $date = null;
    private ?DateTimeImmutable $startOfEstimatedWindow = null;
    private ?DateTimeImmutable $endOfEstimatedWindow = null;

    /**
     * @return \DateTimeImmutable|null Date of the estimated delivery window
     */
    public function getDate(): ?DateTimeImmutable
    {
        return $this->date;
    }

    /**
     * @param \DateTimeImmutable|null $date Date of the estimated delivery window
     *
     * @return $this
     */
    public function setDate(?DateTimeImmutable $date): self
    {
        $estimatedDeliveryDate = $date;

        $this->date = $estimatedDeliveryDate;

        return $this;
    }

    /**
     * @return \DateTimeImmutable|null Start of the estimated delivery window
     */
    public function getStartOfEstimatedWindow(): ?DateTimeImmutable
    {
        return $this->startOfEstimatedWindow;
    }

    /**
     * @param \DateTimeImmutable|null $startOfEstimatedWindow Start of the estimated delivery window
     *
     * @return $this
     */
    public function setStartOfEstimatedWindow(?DateTimeImmutable $startOfEstimatedWindow): self
    {
        $this->startOfEstimatedWindow = $startOfEstimatedWindow;

        return $this;
    }

    /**
     * @return \DateTimeImmutable|null End of the estimated delivery window
     */
    public function getEndOfEstimatedWindow(): ?DateTimeImmutable
    {
        return $this->endOfEstimatedWindow;
    }

    /**
     * @param \DateTimeImmutable|null $endOfEstimatedWindow End of the estimated delivery window
     *
     * @return $this
     */
    public function setEndOfEstimatedWindow(?DateTimeImmutable $endOfEstimatedWindow): self
    {
        $this->endOfEstimatedWindow = $endOfEstimatedWindow;

        return $this;
    }
}
