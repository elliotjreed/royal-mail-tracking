<?php

declare(strict_types=1);

namespace ElliotJReed\RoyalMail\Tracking\Exception;

final class Forbidden extends RoyalMailError
{
    protected $message = 'Access denied.';
}
