<?php

declare(strict_types=1);

namespace ElliotJReed\RoyalMail\Tracking;

use ElliotJReed\RoyalMail\Tracking\Entity\MailPiece;
use ElliotJReed\RoyalMail\Tracking\Exception\ResponseError;
use JsonException;

class Signature extends Track
{
    private ?MailPiece $mailPiece = null;

    /**
     * The signature operation provides the details captured at the point of delivery as proof that delivery
     * has occurred. Note that a proof of delivery is only captured by the delivery track for those service offerings
     * that require a signature on delivery.
     *
     * @param string $trackingNumber The Royal Mail tracking ID (e.g. AB0123456789GB)
     *
     * @return $this
     *
     * @throws \ElliotJReed\RoyalMail\Tracking\Exception\RoyalMailError
     */
    public function setTrackingNumber(string $trackingNumber): self
    {
        $response = $this->request(
            'https://api.royalmail.net/mailpieces/v2/' . $this->sanitiseTrackingId($trackingNumber) . '/signature'
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

        $this->mailPiece = $this->buildMailPiece($decoded['mailPieces']);

        return $this;
    }

    /**
     * @return \ElliotJReed\RoyalMail\Tracking\Entity\MailPiece|null The MailPiece object, or null when
     *                                                               setTrackingNumber method has not been called
     */
    public function get(): ?MailPiece
    {
        return $this->mailPiece;
    }

    /**
     * @return string The MailPiece signature object as a JSON string
     */
    public function asJson(): string
    {
        return $this->serialiseToJson($this->mailPiece);
    }
}
