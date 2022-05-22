<?php

declare(strict_types=1);

namespace ElliotJReed\RoyalMail\Tracking;

use DateTimeImmutable;
use ElliotJReed\RoyalMail\Tracking\Entity\Link;
use ElliotJReed\RoyalMail\Tracking\Entity\Signature\Links;
use ElliotJReed\RoyalMail\Tracking\Entity\Signature\MailPieces;
use ElliotJReed\RoyalMail\Tracking\Entity\Signature\Response;
use ElliotJReed\RoyalMail\Tracking\Entity\Signature\Signature as SignatureEntity;
use ElliotJReed\RoyalMail\Tracking\Exception\RoyalMailResponseError;
use Exception;
use GuzzleHttp\ClientInterface;
use JsonException;

class Signature extends Track
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
     * The signature operation provides the details captured at the point of delivery as proof that delivery
     * has occurred. Note that a proof of delivery is only captured by the delivery track for those service offerings
     * that require a signature on delivery.
     *
     * @param string $trackingNumber The Royal Mail tracking ID (e.g. AB0123456789GB)
     *
     * @return $this
     *
     * @throws \ElliotJReed\RoyalMail\Tracking\Exception\RoyalMailTechnicalError Thrown when a technical error occurs
     *                                                                           (eg. invalid Client ID or Client
     *                                                                           secret). RoyalMailTechnicalError
     *                                                                           exceptions can be "turned off" by
     *                                                                           setting $throwExceptionOnTechnicalError
     *                                                                           to false in the constructor.
     * @throws \ElliotJReed\RoyalMail\Tracking\Exception\RoyalMailTrackingError Thrown when a business/tracking error
     *                                                                          occurs (eg. invalid tracking number).
     *                                                                          RoyalMailTrackingError exceptions can
     *                                                                          be "turned off" by setting
     *                                                                          $throwExceptionOnTrackingError to false
     *                                                                          in the constructor.
     * @throws \ElliotJReed\RoyalMail\Tracking\Exception\RoyalMailResponseError Thrown in the event of an API server
     *                                                                          outage or critical error
     *                                                                          (eg. DNS failure).
     */
    public function setTrackingNumber(string $trackingNumber): self
    {
        $apiResponse = $this->request(
            'https://api.royalmail.net/mailpieces/v2/' . $this->sanitiseTrackingId($trackingNumber) . '/signature'
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
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\Signature\Response the Royal Mail response object
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

        if (!empty($mailPiece['signature'])) {
            $tracking->setSignature($this->buildSignature($mailPiece['signature']));
        }

        if (!empty($mailPiece['links'])) {
            $tracking->setLinks($this->buildLinks($mailPiece));
        }

        return $tracking;
    }

    private function buildSignature(array $signatureArray): SignatureEntity
    {
        $signature = (new SignatureEntity())
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
