<?php

declare(strict_types=1);

namespace ElliotJReed\RoyalMail\Tracking\Exception;

final class RoyalMailServerError extends RoyalMailError
{
    protected $message = 'Royal Mail server error.';
}
