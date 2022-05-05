<?php

declare(strict_types=1);

namespace ElliotJReed\RoyalMail\Tracking\Exception;

use ElliotJReed\RoyalMail\Tracking\Entity\ErrorResponse;
use Exception;

abstract class RoyalMailError extends Exception
{
    protected $message = 'Royal Mail error.';
    private ?ErrorResponse $errorResponse = null;

    public function setErrorResponse(?ErrorResponse $errorResponse): self
    {
        $this->errorResponse = $errorResponse;

        return $this;
    }

    public function getErrorResponse(): ?ErrorResponse
    {
        return $this->errorResponse;
    }
}
