<?php

declare(strict_types=1);

namespace ElliotJReed\RoyalMail\Tracking;

use DateTimeImmutable;
use ElliotJReed\RoyalMail\Tracking\Entity\Event\EstimatedDelivery;
use ElliotJReed\RoyalMail\Tracking\Entity\Event\Event;
use ElliotJReed\RoyalMail\Tracking\Entity\Event\Links;
use ElliotJReed\RoyalMail\Tracking\Entity\Event\MailPieces;
use ElliotJReed\RoyalMail\Tracking\Entity\Event\Response;
use ElliotJReed\RoyalMail\Tracking\Entity\Event\Signature;
use ElliotJReed\RoyalMail\Tracking\Entity\InternationalPostalProvider;
use ElliotJReed\RoyalMail\Tracking\Entity\Link;
use ElliotJReed\RoyalMail\Tracking\Entity\Summary\Summary;
use ElliotJReed\RoyalMail\Tracking\Exception\RoyalMailResponseError;
use Exception;
use GuzzleHttp\ClientInterface;
use JsonException;

class Events extends Track
{
    private Response $response;

    public function __construct(
        ClientInterface $httpClient,
        string $royalMailClientId,
        string $royalMailClientSecret,
        bool $throwExceptionOnTrackingError = true,
        bool $throwExceptionOnTechnicalError = true
    ) {
        parent::__construct(
            $httpClient,
            $royalMailClientId,
            $royalMailClientSecret,
            $throwExceptionOnTrackingError,
            $throwExceptionOnTechnicalError
        );

        $this->response = new Response();
    }

    /**
     * The behaviour of the events operation is to provide a history of tracks for a single mail item.
     *
     * Returns the summary, signature metadata, estimated delivery window and events for a supplied tracking number.
     * See: https://developer.royalmail.net/node/1874997
     *
     * @param string $trackingNumber The Royal Mail tracking ID (e.g. AB0123456789GB).
     *
     * @throws \ElliotJReed\RoyalMail\Tracking\Exception\RoyalMailTechnicalError Thrown when a technical error occurs
     *                                                                           (eg. invalid Client ID or Client
     *                                                                           secret). RoyalMailTechnicalError
     *                                                                           exceptions can be "turned off" by
     *                                                                           setting $throwExceptionOnTechnicalError
     *                                                                           to false in the constructor.
     * @throws \ElliotJReed\RoyalMail\Tracking\Exception\RoyalMailTrackingError  Thrown when a business/tracking error
     *                                                                           occurs (eg. invalid tracking number).
     *                                                                           RoyalMailTrackingError exceptions can be
     *                                                                           "turned off" by setting
     *                                                                           $throwExceptionOnTrackingError to false
     *                                                                           in the constructor.
     * @throws \ElliotJReed\RoyalMail\Tracking\Exception\RoyalMailResponseError  Thrown in the event of an API server
     *                                                                           outage or critical error
     *                                                                           (eg. DNS failure).
     */
    public function setTrackingNumber(string $trackingNumber): self
    {
        $apiResponse = $this->request(
            'https://api.royalmail.net/mailpieces/v2/' . $this->sanitiseTrackingId($trackingNumber) . '/events'
        );

        $statusCode = $apiResponse->getStatusCode();
        $contents = $apiResponse->getBody()->getContents();

        try {
            $decoded = \json_decode($contents, true, 512, \JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new RoyalMailResponseError('(' . $statusCode . ') ' . $contents);
        }

        if (isset($decoded['mailPieces'])) {
            $this->response->setMailPieces($this->buildMailPieces($decoded['mailPieces']));
        }

        /*
         * httpCode will only be populated in the event of an error condition.
         */
        if (isset($decoded['httpCode']) || !empty($decoded['errors'])) {
            $this->handleErrors($this->response, $decoded);
        }

        return $this;
    }

    /**
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\Event\Response the Royal Mail response object
     */
    public function getResponse(): Response
    {
        return $this->response;
    }

    /**
     * @return string the Response object as a JSON string, containing Mailpieces or errors
     */
    public function asJson(): string
    {
        return $this->serialiseToJson($this->response);
    }

    private function buildMailPieces(array $mailPiece): MailPieces
    {
        $tracking = (new MailPieces())
            ->setMailPieceId($mailPiece['mailPieceId'])
            ->setCarrierShortName($mailPiece['carrierShortName'] ?? null)
            ->setCarrierFullName($mailPiece['carrierFullName'] ?? null);

        if (!empty($mailPiece['summary'])) {
            $tracking->setSummary($this->buildSummary($mailPiece));
        }

        if (!empty($mailPiece['signature'])) {
            $tracking->setSignature($this->buildSignature($mailPiece['signature']));
        }

        if (!empty($mailPiece['estimatedDelivery'])) {
            $tracking->setEstimatedDelivery($this->buildEstimatedDelivery($mailPiece['estimatedDelivery']));
        }

        if (!empty($mailPiece['links'])) {
            $tracking->setLinks($this->buildLinks($mailPiece));
        }

        if (!empty($mailPiece['events'])) {
            foreach ($mailPiece['events'] as $event) {
                $tracking->addEvent($this->buildEvent($event));
            }
        }

        return $tracking;
    }

    private function buildSummary(array $mailPiece)
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

        if (!empty($mailPiece['summary']['internationalPostalProvider'])) {
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
            ->setImageId($signatureArray['imageId'] ?? null);

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

        if (!empty($mailPiece['links']['summary'])) {
            $summaryLink = $mailPiece['links']['summary'];

            $trackingLinks->setSummary((new Link())
                ->setHref($summaryLink['href'] ?? null)
                ->setTitle($summaryLink['title'] ?? null)
                ->setDescription($summaryLink['description'] ?? null));
        }

        if (!empty($mailPiece['links']['signature'])) {
            $signatureLink = $mailPiece['links']['signature'];

            $trackingLinks->setSignature((new Link())
                ->setHref($signatureLink['href'] ?? null)
                ->setTitle($signatureLink['title'] ?? null)
                ->setDescription($signatureLink['description'] ?? null));
        }

        if (!empty($mailPiece['links']['redelivery'])) {
            $redelivery = $mailPiece['links']['redelivery'];

            $trackingLinks->setRedelivery((new Link())
                ->setHref($redelivery['href'] ?? null)
                ->setTitle($redelivery['title'] ?? null)
                ->setDescription($redelivery['description'] ?? null));
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
