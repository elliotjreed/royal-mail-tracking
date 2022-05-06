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
            $tracking->setSummary($this->buildSummary($mailPiece));
        }

        if (isset($mailPiece['signature'])) {
            $tracking->setSignature($this->buildSignature($mailPiece['signature']));
        }

        if (isset($mailPiece['estimatedDelivery'])) {
            $tracking->setEstimatedDelivery($this->buildEstimatedDelivery($mailPiece['estimatedDelivery']));
        }

        if (isset($mailPiece['links'])) {
            $tracking->setLinks($this->buildLinks($mailPiece));
        }

        if (isset($mailPiece['events'])) {
            foreach ($mailPiece['events'] as $event) {
                $tracking->addEvent($this->buildEvent($event));
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

    private function buildSummary(array $mailPiece): Summary
    {
        $trackingSummary = (new Summary())
            ->setUniqueItemId($mailPiece['summary']['uniqueItemId'] ?? null)
            ->setOneDBarcode($mailPiece['summary']['oneDBarcode'] ?? null)
            ->setProductId($mailPiece['summary']['productId'] ?? null)
            ->setProductName($mailPiece['summary']['productName'] ?? null)
            ->setProductDescription($mailPiece['summary']['productDescription'] ?? null)
            ->setProductCategory($mailPiece['summary']['productCategory'] ?? null)
            ->setDestinationCountryCode($mailPiece['summary']['destinationCountryCode'] ?? null)
            ->setDestinationCountryName($mailPiece['summary']['destinationCountryName'] ?? null)
            ->setOriginCountryCode($mailPiece['summary']['originCountryCode'] ?? null)
            ->setOriginCountryName($mailPiece['summary']['originCountryName'] ?? null)
            ->setLastEventCode($mailPiece['summary']['lastEventCode'] ?? null)
            ->setLastEventName($mailPiece['summary']['lastEventName'] ?? null)
            ->setLastEventLocationName($mailPiece['summary']['lastEventLocationName'] ?? null)
            ->setStatusDescription($mailPiece['summary']['statusDescription'] ?? null)
            ->setStatusCategory($mailPiece['summary']['statusCategory'] ?? null)
            ->setStatusHelpText($mailPiece['summary']['statusHelpText'] ?? null)
            ->setSummaryLine($mailPiece['summary']['summaryLine'] ?? null);

        try {
            $trackingSummary->setLastEventDateTime(new DateTimeImmutable($mailPiece['summary']['lastEventDateTime']));
        } catch (Exception) {
        }

        if (isset($mailPiece['summary']['internationalPostalProvider'])) {
            $internationalPostalProvider = $mailPiece['summary']['internationalPostalProvider'];

            $trackingSummary->setInternationalPostalProvider((new InternationalPostalProvider())
                ->setUrl($internationalPostalProvider['url'] ?? null)
                ->setTitle($internationalPostalProvider['title'] ?? null)
                ->setDescription($internationalPostalProvider['description'] ?? null));
        }

        return $trackingSummary;
    }

    private function buildSignature(array $signatureArray): Signature
    {
        $signature = (new Signature())
            ->setRecipientName($signatureArray['recipientName'] ?? null)
            ->setImageId($signatureArray['imageId'] ?? null)
            ->setImage($signatureArray['image'] ?? null)
            ->setHeight($signatureArray['height'] ?? null)
            ->setWidth($signatureArray['width'] ?? null)
            ->setImageFormat($signatureArray['imageFormat'] ?? null)
            ->setUniqueItemId($signatureArray['uniqueItemId'] ?? null)
            ->setOneDBarcode($signatureArray['oneDBarcode'] ?? null);

        try {
            $signature->setSignatureDateTime(new DateTimeImmutable($signatureArray['signatureDateTime']));
        } catch (Exception) {
        }

        return $signature;
    }

    private function buildEvent(array $eventArray): Event
    {
        $event = (new Event())
            ->setEventCode($eventArray['eventCode'] ?? null)
            ->setEventName($eventArray['eventName'] ?? null)
            ->setLocationName($eventArray['locationName'] ?? null);

        try {
            $event->setEventDateTime(new DateTimeImmutable($eventArray['eventDateTime']));
        } catch (Exception) {
        }

        return $event;
    }

    private function buildLinks(array $mailPiece): Links
    {
        $trackingLinks = new Links();

        if (isset($mailPiece['links']['summary'])) {
            $summaryLink = $mailPiece['links']['summary'];

            $trackingLinks->setSummary((new Link())
                ->setHref($summaryLink['href'] ?? null)
                ->setTitle($summaryLink['title'] ?? null)
                ->setDescription($summaryLink['description'] ?? null));
        }

        if (isset($mailPiece['links']['signature'])) {
            $signatureLink = $mailPiece['links']['signature'];

            $trackingLinks->setSignature((new Link())
                ->setHref($signatureLink['href'] ?? null)
                ->setTitle($signatureLink['title'] ?? null)
                ->setDescription($signatureLink['description'] ?? null));
        }

        if (isset($mailPiece['links']['redelivery'])) {
            $redelivery = $mailPiece['links']['redelivery'];

            $trackingLinks->setRedelivery((new Link())
                ->setHref($redelivery['href'] ?? null)
                ->setTitle($redelivery['title'] ?? null)
                ->setDescription($redelivery['description'] ?? null));
        }

        if (isset($mailPiece['links']['events'])) {
            $events = $mailPiece['links']['events'];

            $trackingLinks->setEvents((new Link())
                ->setHref($events['href'] ?? null)
                ->setTitle($events['title'] ?? null)
                ->setDescription($events['description'] ?? null));
        }

        return $trackingLinks;
    }

    private function buildEstimatedDelivery(array $estimatedDeliveryArray): ?EstimatedDelivery
    {
        $estimatedDelivery = null;
        $dateString = $estimatedDeliveryArray['date'];
        $date = DateTimeImmutable::createFromFormat('Y-m-d', $dateString);

        if ($date) {
            $estimatedDelivery = (new EstimatedDelivery())->setDate($date->setTime(0, 0));
            $startOfEstimatedWindow = $dateString . ' ' . $estimatedDeliveryArray['startOfEstimatedWindow'];
            $endOfEstimatedWindow = $dateString . ' ' . $estimatedDeliveryArray['endOfEstimatedWindow'];

            try {
                $estimatedDelivery
                    ->setStartOfEstimatedWindow(new DateTimeImmutable($startOfEstimatedWindow))
                    ->setEndOfEstimatedWindow(new DateTimeImmutable($endOfEstimatedWindow));
            } catch (Exception) {
            }
        }

        return $estimatedDelivery;
    }
}
