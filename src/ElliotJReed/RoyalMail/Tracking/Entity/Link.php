<?php

declare(strict_types=1);

namespace ElliotJReed\RoyalMail\Tracking\Entity;

final class Link
{
    private ?string $href = null;
    private ?string $title = null;
    private ?string $description = null;

    /**
     * @return string|null Relative URL link for summary information
     */
    public function getHref(): ?string
    {
        return $this->href;
    }

    /**
     * @param string|null $href Relative URL link for summary information
     *
     * @return $this
     */
    public function setHref(?string $href): self
    {
        $this->href = $href;

        return $this;
    }

    /**
     * @return string|null Title of the link
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title Title of the link
     *
     * @return $this
     */
    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string|null Description of the link
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description Description of the link
     *
     * @return $this
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
