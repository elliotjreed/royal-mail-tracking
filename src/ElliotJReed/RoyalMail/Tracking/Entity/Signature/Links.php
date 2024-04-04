<?php

declare(strict_types=1);

namespace ElliotJReed\RoyalMail\Tracking\Entity\Signature;

use ElliotJReed\RoyalMail\Tracking\Entity\Link;

final class Links
{
    private ?Link $summary = null;
    private ?Link $events = null;

    /**
     * @return Link|null summary link information
     */
    public function getSummary(): ?Link
    {
        return $this->summary;
    }

    /**
     * @param Link|null $summary summary link information
     */
    public function setSummary(?Link $summary): self
    {
        $this->summary = $summary;

        return $this;
    }

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
