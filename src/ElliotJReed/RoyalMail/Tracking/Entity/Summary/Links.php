<?php

declare(strict_types=1);

namespace ElliotJReed\RoyalMail\Tracking\Entity\Summary;

use ElliotJReed\RoyalMail\Tracking\Entity\Link;

final class Links
{
    private ?Link $events = null;

    /**
     * @return Link|null container for events link information
     */
    public function getEvents(): ?Link
    {
        return $this->events;
    }

    /**
     * @param Link|null $events container for events link information
     */
    public function setEvents(?Link $events): self
    {
        $this->events = $events;

        return $this;
    }
}
