<?php

declare(strict_types=1);

namespace ElliotJReed\RoyalMail\Tracking\Entity\Event;

use DateTimeImmutable;

final class EstimatedDelivery
{
    private ?DateTimeImmutable $date = null;
    private ?DateTimeImmutable $startOfEstimatedWindow = null;
    private ?DateTimeImmutable $endOfEstimatedWindow = null;

    /**
     * @return \DateTimeImmutable|null date of the estimated delivery window
     */
    public function getDate(): ?DateTimeImmutable
    {
        return $this->date;
    }

    /**
     * @param \DateTimeImmutable|null $date date of the estimated delivery window
     *
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\Event\EstimatedDelivery
     */
    public function setDate(?DateTimeImmutable $date): self
    {
        $estimatedDeliveryDate = $date;

        $this->date = $estimatedDeliveryDate;

        return $this;
    }

    /**
     * @return \DateTimeImmutable|null start of the estimated delivery window
     */
    public function getStartOfEstimatedWindow(): ?DateTimeImmutable
    {
        return $this->startOfEstimatedWindow;
    }

    /**
     * @param \DateTimeImmutable|null $startOfEstimatedWindow start of the estimated delivery window
     *
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\Event\EstimatedDelivery
     */
    public function setStartOfEstimatedWindow(?DateTimeImmutable $startOfEstimatedWindow): self
    {
        $this->startOfEstimatedWindow = $startOfEstimatedWindow;

        return $this;
    }

    /**
     * @return \DateTimeImmutable|null end of the estimated delivery window
     */
    public function getEndOfEstimatedWindow(): ?DateTimeImmutable
    {
        return $this->endOfEstimatedWindow;
    }

    /**
     * @param \DateTimeImmutable|null $endOfEstimatedWindow end of the estimated delivery window
     *
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\Event\EstimatedDelivery
     */
    public function setEndOfEstimatedWindow(?DateTimeImmutable $endOfEstimatedWindow): self
    {
        $this->endOfEstimatedWindow = $endOfEstimatedWindow;

        return $this;
    }
}
