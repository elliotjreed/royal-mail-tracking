<?php

declare(strict_types=1);

namespace ElliotJReed\RoyalMail\Tracking\Entity;

final class Link
{
    private ?string $href = null;
    private ?string $title = null;
    private ?string $description = null;

    /**
     * @return string|null relative URL link for summary information
     */
    public function getHref(): ?string
    {
        return $this->href;
    }

    /**
     * @param string|null $href relative URL link for summary information
     *
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\Link
     */
    public function setHref(?string $href): self
    {
        $this->href = $href;

        return $this;
    }

    /**
     * @return string|null title of the link
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title title of the link
     *
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\Link
     */
    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string|null description of the link
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description description of the link
     *
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\Link
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
