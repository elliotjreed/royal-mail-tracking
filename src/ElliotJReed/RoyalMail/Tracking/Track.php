<?php

declare(strict_types=1);

namespace ElliotJReed\RoyalMail\Tracking;

use DateTimeImmutable;
use ElliotJReed\RoyalMail\Tracking\Entity\EstimatedDelivery;
use ElliotJReed\RoyalMail\Tracking\Entity\Event;
use ElliotJReed\RoyalMail\Tracking\Entity\InternationalPostalProvider;
use ElliotJReed\RoyalMail\Tracking\Entity\Link;
use ElliotJReed\RoyalMail\Tracking\Entity\Links;
use ElliotJReed\RoyalMail\Tracking\Entity\MailPiece;
use ElliotJReed\RoyalMail\Tracking\Entity\Signature;
use ElliotJReed\RoyalMail\Tracking\Entity\Summary;
use ElliotJReed\RoyalMail\Tracking\Exception\Forbidden;
use ElliotJReed\RoyalMail\Tracking\Exception\InvalidCredentials;
use ElliotJReed\RoyalMail\Tracking\Exception\RequestError;
use ElliotJReed\RoyalMail\Tracking\Exception\RequestLimitExceeded;
use ElliotJReed\RoyalMail\Tracking\Exception\RoyalMailServerError;
use ElliotJReed\RoyalMail\Tracking\Formatter\ErrorResponse;
use ElliotJReed\RoyalMail\Tracking\Normaliser\NullObjectNormaliser;
use Exception;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Serializer;

abstract class Track
{
    private ClientInterface $httpClient;
    private string $clientId;
    private string $clientSecret;

    /**
     * @param \GuzzleHttp\ClientInterface $httpClient            Guzzle Client
     * @param string                      $royalMailClientId     Royal Mail Client ID
     * @param string                      $royalMailClientSecret Royal Mail Client Secret
     */
    public function __construct(ClientInterface $httpClient, string $royalMailClientId, string $royalMailClientSecret)
    {
        $this->httpClient = $httpClient;
        $this->clientId = $royalMailClientId;
        $this->clientSecret = $royalMailClientSecret;
    }

    protected function request(string $url): ResponseInterface
    {
        try {
            $response = $this->httpClient->request(
                'GET',
                $url,
                ['headers' => [
                    'Accept' => 'application/json',
                    'X-Accept-RMG-Terms' => 'yes',
                    'X-IBM-Client-Id' => $this->clientId,
                    'X-IBM-Client-Secret' => $this->clientSecret
                ]]
            );
        } catch (RequestException $exception) {
            $this->handleError(
                (int) $exception->getCode(),
                $exception->getResponse()?->getBody()->getContents() ?? $exception->getMessage()
            );
        } catch (ClientExceptionInterface $exception) {
            $this->handleError(
                (int) $exception->getCode(),
                $exception->getMessage()
            );
        }

        return $response;
    }

    protected function buildMailPiece(array $mailPiece): MailPiece
    {
        $tracking = (new MailPiece())
            ->setMailPieceId($mailPiece['mailPieceId'])
            ->setCarrierShortName($mailPiece['carrierShortName'] ?? null)
            ->setCarrierFullName($mailPiece['carrierFullName'] ?? null);

        if (isset($mailPiece['summary'])) {
            $summary = $mailPiece['summary'];

            try {
                $lastEventDateTime = new DateTimeImmutable($summary['lastEventDateTime']);
            } catch (Exception) {
                $lastEventDateTime = null;
            }

            $trackingSummary = (new Summary())
                ->setUniqueItemId($summary['uniqueItemId'] ?? null)
                ->setOneDBarcode($summary['oneDBarcode'] ?? null)
                ->setProductId($summary['productId'] ?? null)
                ->setProductName($summary['productName'] ?? null)
                ->setProductDescription($summary['productDescription'] ?? null)
                ->setProductCategory($summary['productCategory'] ?? null)
                ->setDestinationCountryCode($summary['destinationCountryCode'] ?? null)
                ->setDestinationCountryName($summary['destinationCountryName'] ?? null)
                ->setOriginCountryCode($summary['originCountryCode'] ?? null)
                ->setOriginCountryName($summary['originCountryName'] ?? null)
                ->setLastEventCode($summary['lastEventCode'] ?? null)
                ->setLastEventName($summary['lastEventName'] ?? null)
                ->setLastEventDateTime($lastEventDateTime)
                ->setLastEventLocationName($summary['lastEventLocationName'] ?? null)
                ->setStatusDescription($summary['statusDescription'] ?? null)
                ->setStatusCategory($summary['statusCategory'] ?? null)
                ->setStatusHelpText($summary['statusHelpText'] ?? null)
                ->setSummaryLine($summary['summaryLine'] ?? null);

            if (isset($summary['internationalPostalProvider'])) {
                $internationalPostalProvider = $summary['internationalPostalProvider'];

                $trackingSummary->setInternationalPostalProvider((new InternationalPostalProvider())
                    ->setUrl($internationalPostalProvider['url'] ?? null)
                    ->setTitle($internationalPostalProvider['title'] ?? null)
                    ->setDescription($internationalPostalProvider['description'] ?? null));
            }

            $tracking->setSummary($trackingSummary);
        }

        if (isset($mailPiece['signature'])) {
            $signature = $mailPiece['signature'];

            try {
                $signatureDateTime = new DateTimeImmutable($signature['signatureDateTime']);
            } catch (Exception) {
                $signatureDateTime = null;
            }

            $tracking->setSignature((new Signature())
                ->setRecipientName($signature['recipientName'] ?? null)
                ->setSignatureDateTime($signatureDateTime)
                ->setImageId($signature['imageId'] ?? null));
        }

        if (isset($mailPiece['estimatedDelivery'])) {
            $estimatedDelivery = $mailPiece['estimatedDelivery'];

            $dateString = $estimatedDelivery['date'];
            $date = DateTimeImmutable::createFromFormat('Y-m-d', $dateString);

            if ($date) {
                $startOfEstimatedWindow = $dateString . ' ' . $estimatedDelivery['startOfEstimatedWindow'];
                $endOfEstimatedWindow = $dateString . ' ' . $estimatedDelivery['endOfEstimatedWindow'];
                $tracking->setEstimatedDelivery((new EstimatedDelivery())->setDate($date->setTime(0, 0)));

                try {
                    $tracking->getEstimatedDelivery()
                        ->setStartOfEstimatedWindow(new DateTimeImmutable($startOfEstimatedWindow))
                        ->setEndOfEstimatedWindow(new DateTimeImmutable($endOfEstimatedWindow));
                } catch (Exception) {
                }
            }
        }

        if (isset($mailPiece['links'])) {
            $links = $mailPiece['links'];

            $trackingLinks = new Links();

            if (isset($links['summary'])) {
                $summaryLink = $links['summary'];

                $trackingLinks->setSummary((new Link())
                    ->setHref($summaryLink['href'] ?? null)
                    ->setTitle($summaryLink['title'] ?? null)
                    ->setDescription($summaryLink['description'] ?? null));
            }

            if (isset($links['signature'])) {
                $signatureLink = $links['signature'];

                $trackingLinks->setSignature((new Link())
                    ->setHref($signatureLink['href'] ?? null)
                    ->setTitle($signatureLink['title'] ?? null)
                    ->setDescription($signatureLink['description'] ?? null));
            }

            if (isset($links['redelivery'])) {
                $redelivery = $links['redelivery'];

                $trackingLinks->setRedelivery((new Link())
                    ->setHref($redelivery['href'] ?? null)
                    ->setTitle($redelivery['title'] ?? null)
                    ->setDescription($redelivery['description'] ?? null));
            }
            $tracking->setLinks($trackingLinks);
        }

        if (isset($mailPiece['events'])) {
            foreach ($mailPiece['events'] as $event) {
                $tracking->addEvent((new Event())
                    ->setEventCode($event['eventCode'] ?? null)
                    ->setEventName($event['eventName'] ?? null)
                    ->setEventDateTime(new DateTimeImmutable($event['eventDateTime']))
                    ->setLocationName($event['locationName'] ?? null));
            }
        }

        if (isset($mailPiece['error'])) {
            $tracking->setError(ErrorResponse::formatError($mailPiece['error']));
        }

        return $tracking;
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

    protected function handleError(int $statusCode, ?string $responseBody): void
    {
        $error = null;

        try {
            $error = ErrorResponse::formatErrors(\json_decode($responseBody, true, 8, \JSON_THROW_ON_ERROR));

            if (null !== $error->getMoreInformation()) {
                $message = '(' . ($error->getHttpCode() ?? $statusCode) . ') ' . $error->getMoreInformation();
            } elseif ([] !== $error->getErrors()) {
                $firstError = $error->getErrors()[0];
                $message = '(' . $firstError->getErrorCode() . ') ' . $firstError->getErrorDescription();
            } else {
                $message = '(' . $statusCode . ') ' . $responseBody ?? 'Unexpected response received from Royal Mail.';
            }
        } catch (JsonException) {
            $message = '(' . $statusCode . ') ' . $responseBody ?? 'Unexpected response received from Royal Mail.';
        }

        throw match ($statusCode) {
            400 => (new RequestError($message))->setErrorResponse($error),
            401 => (new InvalidCredentials($message))->setErrorResponse($error),
            403 => (new Forbidden($message))->setErrorResponse($error),
            429 => (new RequestLimitExceeded($message))->setErrorResponse($error),
            default => (new RoyalMailServerError($message))->setErrorResponse($error)
        };
    }
}
