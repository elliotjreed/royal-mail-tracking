<?php

declare(strict_types=1);

namespace ElliotJReed\RoyalMail\Tracking\Exception;

final class InvalidCredentials extends RoyalMailError
{
    protected $message = 'Client ID or Client Secret is invalid.';
}
