<?php

declare(strict_types=1);

namespace ElliotJReed\RoyalMail\Tracking\Exception;

final class RequestError extends RoyalMailError
{
    protected $message = 'Bad request.';
}
