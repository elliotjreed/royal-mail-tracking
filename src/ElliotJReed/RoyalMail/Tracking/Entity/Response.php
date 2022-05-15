<?php

declare(strict_types=1);

namespace ElliotJReed\RoyalMail\Tracking\Entity;

abstract class Response
{
    private ?int $httpCode = null;
    private ?string $httpMessage = null;
    private ?string $moreInformation = null;
    private ?array $errors = null;

    /**
     * @return int|null HTTP error code. This will only be populated in the event of an error condition.
     */
    public function getHttpCode(): ?int
    {
        return $this->httpCode;
    }

    /**
     * @param int|null $httpCode HTTP error code. This will only be populated in the event of an error condition.
     *
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\Response
     */
    public function setHttpCode(?int $httpCode): self
    {
        $this->httpCode = $httpCode;

        return $this;
    }

    /**
     * @return string|null HTTP error code description. This will only be populated in the event of an error condition.
     */
    public function getHttpMessage(): ?string
    {
        return $this->httpMessage;
    }

    /**
     * @param string|null $httpMessage HTTP error code description.
     *                                 This will only be populated in the event of an error condition.
     *
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\Response
     */
    public function setHttpMessage(?string $httpMessage): self
    {
        $this->httpMessage = $httpMessage;

        return $this;
    }

    /**
     * @return string|null information relating to the error condition
     */
    public function getMoreInformation(): ?string
    {
        return $this->moreInformation;
    }

    /**
     * @param string|null $moreInformation information relating to the error condition
     *
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\Response
     */
    public function setMoreInformation(?string $moreInformation): self
    {
        $this->moreInformation = $moreInformation;

        return $this;
    }

    /**
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\ErrorResponse[]|null array containing error information
     */
    public function getErrors(): ?array
    {
        return $this->errors;
    }

    /**
     * @param \ElliotJReed\RoyalMail\Tracking\Entity\ErrorResponse[]|null $errors array containing error information
     *
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\Response
     */
    public function setErrors(?array $errors): self
    {
        $this->errors = $errors;

        return $this;
    }
}
