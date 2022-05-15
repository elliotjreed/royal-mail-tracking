<?php

declare(strict_types=1);

namespace ElliotJReed\RoyalMail\Tracking;

use ElliotJReed\RoyalMail\Tracking\Entity\ErrorResponse;
use ElliotJReed\RoyalMail\Tracking\Entity\Response;
use ElliotJReed\RoyalMail\Tracking\Exception\ClientIdNotRegistered;
use ElliotJReed\RoyalMail\Tracking\Exception\DeliveryUpdateNotAvailable;
use ElliotJReed\RoyalMail\Tracking\Exception\InternalServerError;
use ElliotJReed\RoyalMail\Tracking\Exception\InvalidBarcodeReference;
use ElliotJReed\RoyalMail\Tracking\Exception\MaximumParametersExceeded;
use ElliotJReed\RoyalMail\Tracking\Exception\MethodNotAllowed;
use ElliotJReed\RoyalMail\Tracking\Exception\ProofOfDeliveryUnavailable;
use ElliotJReed\RoyalMail\Tracking\Exception\ProofOfDeliveryUnavailableForProduct;
use ElliotJReed\RoyalMail\Tracking\Exception\RoyalMailResponseError;
use ElliotJReed\RoyalMail\Tracking\Exception\SchemaValidationFailed;
use ElliotJReed\RoyalMail\Tracking\Exception\ServiceUnavailable;
use ElliotJReed\RoyalMail\Tracking\Exception\TooManyRequests;
use ElliotJReed\RoyalMail\Tracking\Exception\TrackingNotSupported;
use ElliotJReed\RoyalMail\Tracking\Exception\TrackingUnavailable;
use ElliotJReed\RoyalMail\Tracking\Exception\UpdateNotAvailable;
use ElliotJReed\RoyalMail\Tracking\Exception\UriNotFound;
use ElliotJReed\RoyalMail\Tracking\Normaliser\NullObjectNormaliser;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Serializer;

abstract class Track
{
    /**
     * @param \GuzzleHttp\ClientInterface $httpClient Guzzle Client
     * @param string $clientId Royal Mail Client ID
     * @param string $clientSecret Royal Mail Client Secret
     * @param bool $throwExceptionOnTrackingError If true exceptions will be thrown for Events and Signature operations
     *                                            when a tracking error is encountered (eg. if the tracking number is
     *                                            invalid).
     * @param bool $throwExceptionOnTechnicalError If true exceptions will be thrown when a technical error is
     *                                             encountered (eg. if the Client ID or Client Secret is incorrect).
     */
    public function __construct(
        private ClientInterface $httpClient,
        private string $clientId,
        private string $clientSecret,
        private bool $throwExceptionOnTrackingError = true,
        private bool $throwExceptionOnTechnicalError = true
    ) {
    }

    protected function request(string $url): ResponseInterface
    {
        try {
            return $this->httpClient->request(
                'GET',
                $url,
                [
                    'headers' => [
                        'Accept' => 'application/json',
                        'X-Accept-RMG-Terms' => 'yes',
                        'X-IBM-Client-Id' => $this->clientId,
                        'X-IBM-Client-Secret' => $this->clientSecret
                    ],
                    'options' => [
                        RequestOptions::HTTP_ERRORS => false
                    ]
                ]
            );
        } catch (RequestException $exception) {
            if ($exception->hasResponse()) {
                return $exception->getResponse();
            }

            throw new RoyalMailResponseError($exception->getMessage());
        } catch (ClientExceptionInterface $exception) {
            throw new RoyalMailResponseError($exception->getMessage());
        }
    }

    protected function sanitiseTrackingId(string $trackingId): string
    {
        return \preg_replace('/[^[:alnum:]]/u', '', $trackingId);
    }

    protected function serialiseToJson(array | object $object): string
    {
        return (new Serializer(
            [new DateTimeNormalizer(), new NullObjectNormaliser()],
            [new JsonEncoder()]
        ))->serialize($object, 'json');
    }

    protected function handleErrors(Response $response, array $apiResponse): void
    {
        $response
            ->setHttpCode(isset($apiResponse['httpCode']) ? (int) $apiResponse['httpCode'] : null)
            ->setHttpMessage($apiResponse['httpMessage'] ?? null)
            ->setMoreInformation($apiResponse['moreInformation'] ?? null);

        if (isset($apiResponse['errors']) && \is_array($apiResponse['errors'])) {
            $errors = [];
            foreach ($apiResponse['errors'] as $error) {
                $errors[] = (new ErrorResponse())
                    ->setErrorCode($error['errorCode'] ?? null)
                    ->setErrorDescription($error['errorDescription'] ?? null)
                    ->setErrorCause($error['errorCause'] ?? null)
                    ->setErrorResolution($error['errorResolution'] ?? null);
            }

            $response->setErrors($errors);
        }

        $this->throwError($response);
    }

    private function throwError(Response $response): void
    {
        $errors = $response->getErrors();

        if (!empty($errors)) {
            $errorDescription = $errors[0]?->getErrorDescription();

            if ($this->throwExceptionOnTrackingError) {
                switch ($errors[0]?->getErrorCode()) {
                    case 'E1142':
                        throw (new InvalidBarcodeReference($errorDescription))->setResponse($response);
                    case 'E1144':
                        throw (new ProofOfDeliveryUnavailable($errorDescription))->setResponse($response);
                    case 'E1145':
                        throw (new ProofOfDeliveryUnavailableForProduct($errorDescription))->setResponse($response);
                    case 'E1283':
                        throw (new TrackingNotSupported($errorDescription))->setResponse($response);
                    case 'E1284':
                        throw (new DeliveryUpdateNotAvailable($errorDescription))->setResponse($response);
                    case 'E1308':
                        throw (new UpdateNotAvailable($errorDescription))->setResponse($response);
                    case 'E1307':
                        throw (new TrackingUnavailable($errorDescription))->setResponse($response);
                }
            }

            if ($this->throwExceptionOnTechnicalError) {
                switch ($errors[0]?->getErrorCode()) {
                    case 'E0013':
                        throw (new MaximumParametersExceeded($errorDescription))->setResponse($response);
                    case 'E0004':
                        throw (new SchemaValidationFailed($errorDescription))->setResponse($response);
                    case 'E0010':
                        throw (new TooManyRequests($errorDescription))->setResponse($response);
                    case 'E0009':
                        throw (new InternalServerError($errorDescription))->setResponse($response);
                    case 'E0001':
                        throw (new ServiceUnavailable($errorDescription))->setResponse($response);
                }
            }
        }

        if ($this->throwExceptionOnTechnicalError) {
            switch ($response->getHttpCode()) {
                case 401:
                    throw (new ClientIdNotRegistered($response->getMoreInformation()))->setResponse($response);
                case 404:
                    throw (new UriNotFound($response->getMoreInformation()))->setResponse($response);
                case 405:
                    throw (new MethodNotAllowed($response->getMoreInformation()))->setResponse($response);
            }
        }
    }
}
