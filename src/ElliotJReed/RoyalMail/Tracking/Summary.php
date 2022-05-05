<?php

declare(strict_types=1);

namespace ElliotJReed\RoyalMail\Tracking;

use ElliotJReed\RoyalMail\Tracking\Exception\ResponseError;
use JsonException;

class Summary extends Track
{
    private array $summary = [];

    /**
     * The behaviour of the summary operation is to allow customers to obtain the latest tracking data for a mail item.
     * This operation returns the summary of one or more tracking numbers provided in the request.
     * This operation only allows a maximum of 30 tracking numbers to be provided in the mailPieceId query parameter
     * via a comma separated list.
     *
     * Returns the summary, signature metadata, estimated delivery window and events for the supplied tracking numbers.
     * See: https://developer.royalmail.net/node/1874997
     *
     * @param string ...$trackingNumber The Royal Mail tracking IDs (e.g. AB0123456789GB, CD01234567890GB)
     *
     * @return $this
     *
     * @throws \ElliotJReed\RoyalMail\Tracking\Exception\RoyalMailError
     */
    public function setTrackingNumbers(string ...$trackingNumber): self
    {
        $trackingNumbers = \array_map('self::sanitiseTrackingId', $trackingNumber);
        $response = $this->request(
            'https://api.royalmail.net/mailpieces/v2/summary?mailPieceId=' . \implode(',', $trackingNumbers)
        );

        $statusCode = $response->getStatusCode();
        $contents = $response->getBody()->getContents();

        if (200 !== $statusCode) {
            $this->handleError($statusCode, $contents);
        }

        try {
            $decoded = \json_decode($contents, true, 512, \JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new ResponseError('(' . $statusCode . ') ' . $contents);
        }

        if (!isset($decoded['mailPieces'])) {
            throw new ResponseError('(' . $statusCode . ') ' . $contents);
        }

        foreach ($decoded['mailPieces'] as $mailPiece) {
            $this->summary[] = $this->buildMailPiece($mailPiece);
        }

        return $this;
    }

    /**
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\MailPiece[] An array of MailPiece objects
     */
    public function get(): array
    {
        return $this->summary;
    }

    /**
     * @return string The array of MailPieces summary data as a JSON string
     */
    public function asJson(): string
    {
        return $this->serialiseToJson($this->summary);
    }
}
