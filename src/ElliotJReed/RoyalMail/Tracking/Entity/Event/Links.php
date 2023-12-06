<?php

declare(strict_types=1);

namespace ElliotJReed\RoyalMail\Tracking\Entity\Event;

use ElliotJReed\RoyalMail\Tracking\Entity\Link;

final class Links
{
    private ?Link $summary = null;
    private ?Link $signature = null;
    private ?Link $redelivery = null;

    /**
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\Link|null summary link information
     */
    public function getSummary(): ?Link
    {
        return $this->summary;
    }

    /**
     * @param \ElliotJReed\RoyalMail\Tracking\Entity\Link|null $summary summary link information
     */
    public function setSummary(?Link $summary): self
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\Link|null signature link information
     */
    public function getSignature(): ?Link
    {
        return $this->signature;
    }

    /**
     * @param \ElliotJReed\RoyalMail\Tracking\Entity\Link|null $signature signature link information
     */
    public function setSignature(?Link $signature): self
    {
        $this->signature = $signature;

        return $this;
    }

    /**
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\Link|null redelivery link information
     */
    public function getRedelivery(): ?Link
    {
        return $this->redelivery;
    }

    /**
     * @param \ElliotJReed\RoyalMail\Tracking\Entity\Link|null $redelivery redelivery link information
     */
    public function setRedelivery(?Link $redelivery): self
    {
        $this->redelivery = $redelivery;

        return $this;
    }
}
