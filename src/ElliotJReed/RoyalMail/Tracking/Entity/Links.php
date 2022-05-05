<?php

declare(strict_types=1);

namespace ElliotJReed\RoyalMail\Tracking\Entity;

final class Links
{
    private ?Link $summary = null;
    private ?Link $signature = null;
    private ?Link $redelivery = null;

    /**
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\Link|null Summary link information
     */
    public function getSummary(): ?Link
    {
        return $this->summary;
    }

    /**
     * @param \ElliotJReed\RoyalMail\Tracking\Entity\Link|null $summary Summary link information
     *
     * @return $this
     */
    public function setSummary(?Link $summary): self
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\Link|null Signature link information
     */
    public function getSignature(): ?Link
    {
        return $this->signature;
    }

    /**
     * @param \ElliotJReed\RoyalMail\Tracking\Entity\Link|null $signature Signature link information
     *
     * @return $this
     */
    public function setSignature(?Link $signature): self
    {
        $this->signature = $signature;

        return $this;
    }

    /**
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\Link|null Redelivery link information
     */
    public function getRedelivery(): ?Link
    {
        return $this->redelivery;
    }

    /**
     * @param \ElliotJReed\RoyalMail\Tracking\Entity\Link|null $redelivery Redelivery link information
     *
     * @return $this
     */
    public function setRedelivery(?Link $redelivery): self
    {
        $this->redelivery = $redelivery;

        return $this;
    }
}
