<?php

declare(strict_types=1);

namespace ElliotJReed\RoyalMail\Tracking\Entity;

final class ErrorResponse
{
    private ?string $errorCode = null;
    private ?string $errorDescription = null;
    private ?string $errorCause = null;
    private ?string $errorResolution = null;

    /**
     * @return string|null code associated with the error condition
     */
    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    /**
     * @param string|null $errorCode code associated with the error condition
     *
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\ErrorResponse
     */
    public function setErrorCode(?string $errorCode): self
    {
        $this->errorCode = $errorCode;

        return $this;
    }

    /**
     * @return string|null description of the error condition
     */
    public function getErrorDescription(): ?string
    {
        return $this->errorDescription;
    }

    /**
     * @param string|null $errorDescription description of the error condition
     *
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\ErrorResponse
     */
    public function setErrorDescription(?string $errorDescription): self
    {
        $this->errorDescription = $errorDescription;

        return $this;
    }

    /**
     * @return string|null cause of the error (if known)
     */
    public function getErrorCause(): ?string
    {
        return $this->errorCause;
    }

    /**
     * @param string|null $errorCause cause of the error (if known)
     *
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\ErrorResponse
     */
    public function setErrorCause(?string $errorCause): self
    {
        $this->errorCause = $errorCause;

        return $this;
    }

    /**
     * @return string|null description of the resolution and action required to correct the error
     */
    public function getErrorResolution(): ?string
    {
        return $this->errorResolution;
    }

    /**
     * @param string|null $errorResolution description of the resolution and action required to correct the error
     *
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\ErrorResponse
     */
    public function setErrorResolution(?string $errorResolution): self
    {
        $this->errorResolution = $errorResolution;

        return $this;
    }
}
