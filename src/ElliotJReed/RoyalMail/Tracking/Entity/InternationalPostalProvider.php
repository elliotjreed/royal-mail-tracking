<?php

declare(strict_types=1);

namespace ElliotJReed\RoyalMail\Tracking\Entity;

final class InternationalPostalProvider
{
    private string $url;
    private string $title;
    private string $description;

    /**
     * @return string URL link to website of delivery provider for international parcels
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url URL link to website of delivery provider for international parcels
     *
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\InternationalPostalProvider
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return string|null name of the international postal organisation
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string $title name of the international postal organisation
     *
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\InternationalPostalProvider
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string description of the international postal organisation
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description description of the international postal organisation
     *
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\InternationalPostalProvider
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
