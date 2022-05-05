<?php

declare(strict_types=1);

namespace ElliotJReed\RoyalMail\Tracking\Formatter;

use ElliotJReed\RoyalMail\Tracking\Entity\ErrorResponseError;

final class ErrorResponse
{
    public static function formatErrors(array $errorResponse): \ElliotJReed\RoyalMail\Tracking\Entity\ErrorResponse
    {
        $response = new \ElliotJReed\RoyalMail\Tracking\Entity\ErrorResponse();
        $errors = [];

        if (isset($errorResponse['httpCode'])) {
            $response->setHttpCode((int) $errorResponse['httpCode']);
        }

        if (isset($errorResponse['httpMessage'])) {
            $response->setHttpMessage($errorResponse['httpMessage']);
        }

        if (isset($errorResponse['moreInformation'])) {
            $response->setMoreInformation($errorResponse['moreInformation']);
        }

        if (isset($errorResponse['errors']) && \is_array($errorResponse['errors'])) {
            foreach ($errorResponse['errors'] as $error) {
                $errors[] = self::formatError($error);
            }
        }

        $response->setErrors($errors);

        return $response;
    }

    public static function formatError(array $error): ErrorResponseError
    {
        $errorResponse = new ErrorResponseError();

        if (isset($error['errorCode'])) {
            $errorResponse->setErrorCode(\trim($error['errorCode']));
        }

        if (isset($error['errorDescription'])) {
            $errorResponse->setErrorDescription(\trim($error['errorDescription']));
        }

        if (isset($error['errorCause'])) {
            $errorResponse->setErrorCause(\trim($error['errorCause']));
        }

        if (isset($error['errorResolution'])) {
            $errorResponse->setErrorResolution(\trim($error['errorResolution']));
        }

        return $errorResponse;
    }
}
