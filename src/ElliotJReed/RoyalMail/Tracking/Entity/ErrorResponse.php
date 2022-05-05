<?php

declare(strict_types=1);

namespace ElliotJReed\RoyalMail\Tracking\Entity;

final class ErrorResponse
{
    private ?int $httpCode = null;
    private ?string $httpMessage = null;
    private ?string $moreInformation = null;
    private array $errors = [];

    /**
     * @return int|null HTTP error code
     */
    public function getHttpCode(): ?int
    {
        return $this->httpCode;
    }

    /**
     * @param int|null $httpCode HTTP error code
     *
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\ErrorResponse
     */
    public function setHttpCode(?int $httpCode): self
    {
        $this->httpCode = $httpCode;

        return $this;
    }

    /**
     * @return string|null HTTP error code description
     */
    public function getHttpMessage(): ?string
    {
        return $this->httpMessage;
    }

    /**
     * @param string|null $httpMessage HTTP error code description
     *
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\ErrorResponse
     */
    public function setHttpMessage(?string $httpMessage): self
    {
        $this->httpMessage = $httpMessage;

        return $this;
    }

    /**
     * @return string|null Information relating to the error condition
     */
    public function getMoreInformation(): ?string
    {
        return $this->moreInformation;
    }

    /**
     * @param string|null $moreInformation Information relating to the error condition
     *
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\ErrorResponse
     */
    public function setMoreInformation(?string $moreInformation): self
    {
        $this->moreInformation = $moreInformation;

        return $this;
    }

    /**
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\ErrorResponseError[] Array of ErrorResponseErrors
     *                                                                     containing error information
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param \ElliotJReed\RoyalMail\Tracking\Entity\ErrorResponseError[] Array of ErrorResponseErrors
     *                                                                    containing error information
     *
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\ErrorResponse
     */
    public function setErrors(array $errors): self
    {
        $this->errors = $errors;

        return $this;
    }
}
