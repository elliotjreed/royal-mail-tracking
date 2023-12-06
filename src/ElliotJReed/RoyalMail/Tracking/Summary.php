<?php

declare(strict_types=1);

namespace ElliotJReed\RoyalMail\Tracking;

use DateTimeImmutable;
use ElliotJReed\RoyalMail\Tracking\Entity\ErrorResponse;
use ElliotJReed\RoyalMail\Tracking\Entity\InternationalPostalProvider;
use ElliotJReed\RoyalMail\Tracking\Entity\Link;
use ElliotJReed\RoyalMail\Tracking\Entity\Summary\Links;
use ElliotJReed\RoyalMail\Tracking\Entity\Summary\MailPieces;
use ElliotJReed\RoyalMail\Tracking\Entity\Summary\Response;
use ElliotJReed\RoyalMail\Tracking\Entity\Summary\Summary as SummaryEntity;
use ElliotJReed\RoyalMail\Tracking\Exception\RoyalMailResponseError;
use Exception;
use GuzzleHttp\ClientInterface;
use JsonException;

class Summary extends Track
{
    private Response $response;

    public function __construct(
        ClientInterface $httpClient,
        string $royalMailClientId,
        string $royalMailClientSecret,
        bool $throwExceptionOnTechnicalError = true
    ) {
        parent::__construct(
            $httpClient,
            $royalMailClientId,
            $royalMailClientSecret,
            false,
            $throwExceptionOnTechnicalError
        );

        $this->response = new Response();
    }

    /**
     * The behaviour of the summary operation is to allow customers to obtain the latest tracking data for a mail item.
     * This operation returns the summary of one or more tracking numbers provided in the request.
     * This operation only allows a maximum of 30 tracking numbers to be provided in the mailPieceId query parameter
     * via a comma separated list.
     *
     * Returns the summary, signature metadata, estimated delivery window and events for the supplied tracking numbers.
     * See: https://developer.royalmail.net/node/1874997
     *
     * @param string[] $trackingNumbers The Royal Mail tracking IDs (e.g. AB0123456789GB, CD01234567890GB).
     *
     * @return $this
     *
     * @throws \ElliotJReed\RoyalMail\Tracking\Exception\RoyalMailTechnicalError Thrown when a technical error occurs
     *                                                                           (eg. invalid Client ID or Client
     *                                                                           secret). RoyalMailTechnicalError
     *                                                                           exceptions can be "turned off" by
     *                                                                           setting $throwExceptionOnTechnicalError
     *                                                                           to false in the constructor.
     * @throws \ElliotJReed\RoyalMail\Tracking\Exception\RoyalMailResponseError  Thrown in the event of an API server
     *                                                                           outage or critical error
     *                                                                           (eg. DNS failure).
     */
    public function setTrackingNumbers(array $trackingNumbers): self
    {
        $callback = fn (string $trackingId): string => \preg_replace('/[^[:alnum:]]/u', '', $trackingId);

        $sanitisedTrackingNumbers = \array_map($callback, $trackingNumbers);
        $apiResponse = $this->request(
            'https://api.royalmail.net/mailpieces/v2/summary?mailPieceId=' . \implode(',', $sanitisedTrackingNumbers)
        );

        $statusCode = $apiResponse->getStatusCode();
        $contents = $apiResponse->getBody()->getContents();

        try {
            $decoded = \json_decode($contents, true, 512, \JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new RoyalMailResponseError('(' . $statusCode . ') ' . $contents);
        }

        if (isset($decoded['mailPieces'])) {
            $mailPieces = [];
            foreach ($decoded['mailPieces'] as $mailPiece) {
                $mailPieces[] = $this->buildMailPieces($mailPiece);
            }

            $this->response->setMailPieces($mailPieces);
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
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\Summary\Response the Royal Mail response object
     */
    public function getResponse(): Response
    {
        return $this->response;
    }

    /**
     * @return string the Response object as a JSON string, containing an array of Mailpieces
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

        if (!empty($mailPiece['links'])) {
            $tracking->setLinks($this->buildLinks($mailPiece));
        }

        if (!empty($mailPiece['error'])) {
            $error = $mailPiece['error'];
            $tracking->setError((new ErrorResponse())
                ->setErrorCode($error['errorCode'])
                ->setErrorDescription($error['errorDescription'])
                ->setErrorCause($error['errorCause'])
                ->setErrorResolution($error['errorResolution']));
        }

        return $tracking;
    }

    private function buildSummary(array $mailPiece): SummaryEntity
    {
        $trackingSummary = (new SummaryEntity())
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

    private function buildLinks(array $mailPiece): Links
    {
        $trackingLinks = new Links();

        if (!empty($mailPiece['links']['events'])) {
            $events = $mailPiece['links']['events'];

            $trackingLinks->setEvents((new Link())
                ->setHref($events['href'] ?? null)
                ->setTitle($events['title'] ?? null)
                ->setDescription($events['description'] ?? null));
        }

        return $trackingLinks;
    }
}
