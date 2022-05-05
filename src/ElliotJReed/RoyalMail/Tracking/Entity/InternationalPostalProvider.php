<?php

declare(strict_types=1);

namespace ElliotJReed\RoyalMail\Tracking\Entity;

final class InternationalPostalProvider
{
    private ?string $url = null;
    private ?string $title = null;
    private ?string $description = null;

    /**
     * @return string|null URL link to website of delivery provider for international parcels
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string|null $url URL link to website of delivery provider for international parcels
     *
     * @return $this
     */
    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return string|null Name of the international postal organisation
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title Name of the international postal organisation
     *
     * @return $this
     */
    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string|null Description of the international postal organisation
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description Description of the international postal organisation
     *
     * @return $this
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
