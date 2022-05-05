<?php

declare(strict_types=1);

namespace ElliotJReed\RoyalMail\Tracking\Exception;

final class ResponseError extends RoyalMailError
{
    protected $message = 'Unexpected response returned from API.';
}
