<?php

declare(strict_types=1);

namespace ElliotJReed\RoyalMail\Tracking\Entity\Event;

final class Event
{
    private ?string $eventCode = null;
    private ?string $eventName = null;
    private \DateTimeImmutable $eventDateTime;
    private ?string $locationName = null;

    /**
     * @return string|null code associated with the mail piece event
     */
    public function getEventCode(): ?string
    {
        return $this->eventCode;
    }

    /**
     * @param string|null $eventCode code associated with the mail piece event
     *
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\Event\Event
     */
    public function setEventCode(?string $eventCode): self
    {
        $this->eventCode = $eventCode;

        return $this;
    }

    /**
     * @return string|null name of the event
     */
    public function getEventName(): ?string
    {
        return $this->eventName;
    }

    /**
     * @param string|null $eventName name of the event
     *
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\Event\Event
     */
    public function setEventName(?string $eventName): self
    {
        $this->eventName = $eventName;

        return $this;
    }

    /**
     * @return \DateTimeImmutable date and time of the event
     */
    public function getEventDateTime(): \DateTimeImmutable
    {
        return $this->eventDateTime;
    }

    /**
     * @param \DateTimeImmutable $eventDateTime date and time of the event
     *
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\Event\Event
     */
    public function setEventDateTime(\DateTimeImmutable $eventDateTime): self
    {
        $this->eventDateTime = $eventDateTime;

        return $this;
    }

    /**
     * @return string|null location name associated with the mail piece event
     */
    public function getLocationName(): ?string
    {
        return $this->locationName;
    }

    /**
     * @param string|null $locationName location name associated with the mail piece event
     *
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\Event\Event
     */
    public function setLocationName(?string $locationName): self
    {
        $this->locationName = $locationName;

        return $this;
    }
}
