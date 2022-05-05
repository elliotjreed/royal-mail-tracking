<?php

declare(strict_types=1);

namespace ElliotJReed\RoyalMail\Tracking\Exception;

final class RequestLimitExceeded extends RoyalMailError
{
    protected $message = 'API request limit exceeded. Contact Royal Mail API support.';
}
