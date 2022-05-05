<?php

declare(strict_types=1);

namespace ElliotJReed\RoyalMail\Tracking\Entity;

use DateTimeImmutable;

final class Event
{
    private ?string $eventCode = null;
    private ?string $eventName = null;
    private ?DateTimeImmutable $eventDateTime = null;
    private ?string $locationName = null;

    /**
     * @return string|null Code associated with the mail piece event
     */
    public function getEventCode(): ?string
    {
        return $this->eventCode;
    }

    /**
     * @param string|null $eventCode Code associated with the mail piece event
     *
     * @return $this
     */
    public function setEventCode(?string $eventCode): self
    {
        $this->eventCode = $eventCode;

        return $this;
    }

    /**
     * @return string|null Name of the event
     */
    public function getEventName(): ?string
    {
        return $this->eventName;
    }

    /**
     * @param string|null $eventName Name of the event
     *
     * @return $this
     */
    public function setEventName(?string $eventName): self
    {
        $this->eventName = $eventName;

        return $this;
    }

    /**
     * @return \DateTimeImmutable|null Date and time of the event
     */
    public function getEventDateTime(): ?DateTimeImmutable
    {
        return $this->eventDateTime;
    }

    /**
     * @param \DateTimeImmutable|null $eventDateTime Date and time of the event
     *
     * @return $this
     */
    public function setEventDateTime(?DateTimeImmutable $eventDateTime): self
    {
        $this->eventDateTime = $eventDateTime;

        return $this;
    }

    /**
     * @return string|null Location name associated with the mail piece event
     */
    public function getLocationName(): ?string
    {
        return $this->locationName;
    }

    /**
     * @param string|null $locationName Location name associated with the mail piece event
     *
     * @return $this
     */
    public function setLocationName(?string $locationName): self
    {
        $this->locationName = $locationName;

        return $this;
    }
}
