<?php

declare(strict_types=1);

namespace ElliotJReed\RoyalMail\Tracking\Exception;

use ElliotJReed\RoyalMail\Tracking\Entity\Response;

abstract class RoyalMailError extends \Exception
{
    protected ?Response $response = null;

    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function setResponse(Response $response): self
    {
        $this->response = $response;

        return $this;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }
}
