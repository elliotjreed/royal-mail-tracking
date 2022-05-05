<?php

declare(strict_types=1);

namespace ElliotJReed\RoyalMail\Tracking\Entity;

final class ErrorResponseError
{
    private ?string $errorCode = null;
    private ?string $errorDescription = null;
    private ?string $errorCause = null;
    private ?string $errorResolution = null;

    /**
     * @return string|null Code associated with the error condition
     */
    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    /**
     * @param string|null $errorCode Code associated with the error condition
     *
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\ErrorResponseError
     */
    public function setErrorCode(?string $errorCode): self
    {
        $this->errorCode = $errorCode;

        return $this;
    }

    /**
     * @return string|null Description of the error condition
     */
    public function getErrorDescription(): ?string
    {
        return $this->errorDescription;
    }

    /**
     * @param string|null $errorDescription Description of the error condition
     *
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\ErrorResponseError
     */
    public function setErrorDescription(?string $errorDescription): self
    {
        $this->errorDescription = $errorDescription;

        return $this;
    }

    /**
     * @return string|null Cause of the error (if known)
     */
    public function getErrorCause(): ?string
    {
        return $this->errorCause;
    }

    /**
     * @param string|null $errorCause Cause of the error (if known)
     *
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\ErrorResponseError
     */
    public function setErrorCause(?string $errorCause): self
    {
        $this->errorCause = $errorCause;

        return $this;
    }

    /**
     * @return string|null Description of the resolution and action required to correct the error
     */
    public function getErrorResolution(): ?string
    {
        return $this->errorResolution;
    }

    /**
     * @param string|null $errorResolution Description of the resolution and action required to correct the error
     *
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\ErrorResponseError
     */
    public function setErrorResolution(?string $errorResolution): self
    {
        $this->errorResolution = $errorResolution;

        return $this;
    }
}
