<?php

declare(strict_types=1);

namespace Tests\ElliotJReed\RoyalMail\Tracking;

use DateTimeImmutable;
use ElliotJReed\RoyalMail\Tracking\Exception\BadRequest;
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
use ElliotJReed\RoyalMail\Tracking\Signature;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class SignatureTest extends TestCase
{
    public function testItSendsRequestToRoyalMail(): void
    {
        $mock = new MockHandler([new Response(200, [], $this->mockResponse())]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        (new Signature($client, 'client-id', 'client-secret'))
            ->setTrackingNumber('123456789GB');

        $this->assertSame('/mailpieces/v2/123456789GB/signature', $mock->getLastRequest()->getUri()->getPath());
        $this->assertSame('GET', $mock->getLastRequest()->getMethod());

        $headers = $mock->getLastRequest()->getHeaders();
        $this->assertSame('application/json', $headers['Accept'][0]);
        $this->assertSame('yes', $headers['X-Accept-RMG-Terms'][0]);
        $this->assertSame('client-id', $headers['X-IBM-Client-Id'][0]);
        $this->assertSame('client-secret', $headers['X-IBM-Client-Secret'][0]);
    }

    public function testItReturnsTrackingData(): void
    {
        $mock = new MockHandler([new Response(200, [], $this->mockResponse())]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        $response = (new Signature($client, 'client-id', 'client-secret'))
            ->setTrackingNumber('123456789GB')
            ->getResponse()
            ->getMailPieces();

        $this->assertSame('090367574000000FE1E1B', $response->getMailPieceId());
        $this->assertSame('RM', $response->getCarrierShortName());
        $this->assertSame('Royal Mail Group Ltd', $response->getCarrierFullName());

        $signature = $response->getSignature();
        $this->assertSame('Elliot', $signature->getRecipientName());
        $this->assertEquals(new DateTimeImmutable('2017-03-30T16:15:00+01:00'), $signature->getSignatureDateTime());
        $this->assertSame('001234', $signature->getImageId());
        $this->assertSame('FQ087430672GB', $signature->getOneDBarcode());
        $this->assertSame(530, $signature->getHeight());
        $this->assertSame(660, $signature->getWidth());
        $this->assertSame('090367574000000FE1E1B', $signature->getUniqueItemId());
        $this->assertSame('image/svg+xml', $signature->getImageFormat());
        $this->assertSame('<svg></svg>', $signature->getImage());

        $events = $response->getLinks()->getEvents();
        $this->assertSame('/mailpieces/v2/FQ087430672GB/events', $events->getHref());
        $this->assertSame('Events', $events->getTitle());
        $this->assertSame('Get events', $events->getDescription());

        $linkSummary = $response->getLinks()->getSummary();
        $this->assertSame('/mailpieces/v2/summary?mailPieceId=090367574000000FE1E1B', $linkSummary->getHref());
        $this->assertSame('Summary', $linkSummary->getTitle());
        $this->assertSame('Get summary', $linkSummary->getDescription());
    }

    public function testItReturnsTrackingDataInTheEventOfDateErrorException(): void
    {
        $apiResponse = '{
          "mailPieces": {
            "mailPieceId": "090367574000000FE1E1B",
            "carrierShortName": "RM",
            "carrierFullName": "Royal Mail Group Ltd",
            "signature": {
              "uniqueItemId": "090367574000000FE1E1B",
              "oneDBarcode": "FQ087430672GB",
              "recipientName": "Elliot",
              "signatureDateTime": "UNPARSABLE DATETIME",
              "imageFormat": "image/svg+xml",
              "imageId": "001234",
              "height": 530,
              "width": 660,
              "image": "<svg></svg>"
            }
          }
        }';
        $mock = new MockHandler([new Response(200, [], $apiResponse)]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        $response = (new Signature($client, 'client-id', 'client-secret'))
            ->setTrackingNumber('123456789GB')
            ->getResponse()
            ->getMailPieces();

        $this->assertSame('090367574000000FE1E1B', $response->getMailPieceId());
        $this->assertSame('RM', $response->getCarrierShortName());
        $this->assertSame('Royal Mail Group Ltd', $response->getCarrierFullName());

        $signature = $response->getSignature();
        $this->assertSame('Elliot', $signature->getRecipientName());
        $this->assertNull($signature->getSignatureDateTime());
    }

    public function testItReturnsJsonEncodedTrackingData(): void
    {
        $mock = new MockHandler([new Response(200, [], $this->mockResponse())]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        $response = (new Signature($client, 'client-id', 'client-secret'))
            ->setTrackingNumber('123456789GB');

        $this->assertJsonStringEqualsJsonString('{
          "errors": [],
          "httpCode": null,
          "httpMessage": null,
          "mailPieces": {
            "carrierFullName": "Royal Mail Group Ltd",
            "carrierShortName": "RM",
            "links": {
              "events": {
                "description": "Get events",
                "href": "/mailpieces/v2/FQ087430672GB/events",
                "title": "Events"
              },
              "summary": {
                "description": "Get summary",
                "href": "/mailpieces/v2/summary?mailPieceId=090367574000000FE1E1B",
                "title": "Summary"
              }
            },
            "mailPieceId": "090367574000000FE1E1B",
            "signature": {
              "height": 530,
              "image": "<svg></svg>",
              "imageFormat": "image/svg+xml",
              "imageId": "001234",
              "oneDBarcode": "FQ087430672GB",
              "recipientName": "Elliot",
              "signatureDateTime": "2017-03-30T16:15:00+01:00",
              "uniqueItemId": "090367574000000FE1E1B",
              "width": 660
            }
          },
          "moreInformation": null
        }', $response->asJson());
    }

    public function testItRemovesNonAlphanumericCharactersFromTrackingNumber(): void
    {
        $mock = new MockHandler([new Response(200, [], $this->mockResponse())]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        $response = (new Signature($client, 'client-id', 'client-secret'))
            ->setTrackingNumber('!"£$%^&*()12345             6789GB)(*&^%$!')
            ->getResponse()
            ->getMailPieces();

        $this->assertSame('/mailpieces/v2/123456789GB/signature', $mock->getLastRequest()->getUri()->getPath());
        $this->assertSame('090367574000000FE1E1B', $response->getMailPieceId());
    }

    public function testItThrowsResponseErrorWhenInvalidJsonReturnedFromApi(): void
    {
        $mock = new MockHandler([new Response(200, [], 'NOT JSON')]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        $this->expectException(RoyalMailResponseError::class);
        $this->expectExceptionMessage('(200) NOT JSON');

        (new Signature($client, 'client-id', 'client-secret'))
            ->setTrackingNumber('123456789GB');
    }

    public function testItThrowsExceptionWhenRequestExceptionThrownByGuzzle(): void
    {
        $mock = new MockHandler([
            new RequestException(
                'Error Communicating with Server',
                new Request('GET', '/mailpieces/v2/123456789GB/events')
            )
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        $this->expectException(RoyalMailResponseError::class);
        $this->expectExceptionMessage('Error Communicating with Server');

        (new Signature($client, 'client-id', 'client-secret'))
            ->setTrackingNumber('123456789GB');
    }

    public function testItThrowsExceptionWhenTransferExceptionThrownByGuzzle(): void
    {
        $mock = new MockHandler([
            new TransferException('Transfer Exception')
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        $this->expectException(RoyalMailResponseError::class);
        $this->expectExceptionMessage('Transfer Exception');

        (new Signature($client, 'client-id', 'client-secret'))
            ->setTrackingNumber('123456789GB');
    }

    public function testItThrowsInvalidBarcodeReferenceOn400Response(): void
    {
        $mock = new MockHandler([
            new Response(400, [], \json_encode([
                'httpCode' => 400,
                'httpMessage' => 'HTTP message',
                'moreInformation' => 'More information',
                'errors' => [[
                    'errorCode' => 'E1142',
                    'errorDescription' => 'Error description',
                    'errorCause' => 'Error cause',
                    'errorResolution' => 'Error resolution'
                ]]
            ]))
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        try {
            (new Signature($client, 'client-id', 'client-secret'))
                ->setTrackingNumber('123456789GB')->getResponse();
        } catch (InvalidBarcodeReference $exception) {
            $this->assertSame('Error description', $exception->getMessage());

            $response = $exception->getResponse();

            $this->assertSame(400, $response->getHttpCode());
            $this->assertSame('HTTP message', $response->getHttpMessage());
            $this->assertSame('More information', $response->getMoreInformation());
            $this->assertSame('E1142', $response->getErrors()[0]->getErrorCode());
            $this->assertSame('Error description', $response->getErrors()[0]->getErrorDescription());
            $this->assertSame('Error cause', $response->getErrors()[0]->getErrorCause());
            $this->assertSame('Error resolution', $response->getErrors()[0]->getErrorResolution());
        }
    }

    public function testItThrowsInvalidBarcodeReferenceOn404Response(): void
    {
        $mock = new MockHandler([
            new Response(404, [], \json_encode([
                'httpCode' => 404,
                'httpMessage' => 'HTTP message',
                'moreInformation' => 'More information',
                'errors' => [[
                    'errorCode' => 'E1142',
                    'errorDescription' => 'Error description',
                    'errorCause' => 'Error cause',
                    'errorResolution' => 'Error resolution'
                ]]
            ]))
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        try {
            (new Signature($client, 'client-id', 'client-secret'))
                ->setTrackingNumber('123456789GB')->getResponse();
        } catch (InvalidBarcodeReference $exception) {
            $this->assertSame('Error description', $exception->getMessage());

            $response = $exception->getResponse();

            $this->assertSame(404, $response->getHttpCode());
            $this->assertSame('HTTP message', $response->getHttpMessage());
            $this->assertSame('More information', $response->getMoreInformation());
            $this->assertSame('E1142', $response->getErrors()[0]->getErrorCode());
            $this->assertSame('Error description', $response->getErrors()[0]->getErrorDescription());
            $this->assertSame('Error cause', $response->getErrors()[0]->getErrorCause());
            $this->assertSame('Error resolution', $response->getErrors()[0]->getErrorResolution());
        }
    }

    public function testItThrowsProofOfDeliveryUnavailable(): void
    {
        $mock = new MockHandler([
            new Response(404, [], \json_encode([
                'httpCode' => 404,
                'httpMessage' => 'HTTP message',
                'moreInformation' => 'More information',
                'errors' => [[
                    'errorCode' => 'E1144',
                    'errorDescription' => 'Error description',
                    'errorCause' => 'Error cause',
                    'errorResolution' => 'Error resolution'
                ]]
            ]))
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        try {
            (new Signature($client, 'client-id', 'client-secret'))
                ->setTrackingNumber('123456789GB');
        } catch (ProofOfDeliveryUnavailable $exception) {
            $this->assertSame('Error description', $exception->getMessage());

            $response = $exception->getResponse();

            $this->assertSame(404, $response->getHttpCode());
            $this->assertSame('HTTP message', $response->getHttpMessage());
            $this->assertSame('More information', $response->getMoreInformation());
            $this->assertSame('E1144', $response->getErrors()[0]->getErrorCode());
            $this->assertSame('Error description', $response->getErrors()[0]->getErrorDescription());
            $this->assertSame('Error cause', $response->getErrors()[0]->getErrorCause());
            $this->assertSame('Error resolution', $response->getErrors()[0]->getErrorResolution());
        }
    }

    public function testItThrowsProofOfDeliveryUnavailableForProduct(): void
    {
        $mock = new MockHandler([
            new Response(404, [], \json_encode([
                'httpCode' => 404,
                'httpMessage' => 'HTTP message',
                'moreInformation' => 'More information',
                'errors' => [[
                    'errorCode' => 'E1145',
                    'errorDescription' => 'Error description',
                    'errorCause' => 'Error cause',
                    'errorResolution' => 'Error resolution'
                ]]
            ]))
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        try {
            (new Signature($client, 'client-id', 'client-secret'))
                ->setTrackingNumber('123456789GB');
        } catch (ProofOfDeliveryUnavailableForProduct $exception) {
            $this->assertSame('Error description', $exception->getMessage());

            $response = $exception->getResponse();

            $this->assertSame(404, $response->getHttpCode());
            $this->assertSame('HTTP message', $response->getHttpMessage());
            $this->assertSame('More information', $response->getMoreInformation());
            $this->assertSame('E1145', $response->getErrors()[0]->getErrorCode());
            $this->assertSame('Error description', $response->getErrors()[0]->getErrorDescription());
            $this->assertSame('Error cause', $response->getErrors()[0]->getErrorCause());
            $this->assertSame('Error resolution', $response->getErrors()[0]->getErrorResolution());
        }
    }

    public function testItThrowsTrackingNotSupported(): void
    {
        $mock = new MockHandler([
            new Response(404, [], \json_encode([
                'httpCode' => 404,
                'httpMessage' => 'HTTP message',
                'moreInformation' => 'More information',
                'errors' => [[
                    'errorCode' => 'E1283',
                    'errorDescription' => 'Error description',
                    'errorCause' => 'Error cause',
                    'errorResolution' => 'Error resolution'
                ]]
            ]))
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        try {
            (new Signature($client, 'client-id', 'client-secret'))
                ->setTrackingNumber('123456789GB');
        } catch (TrackingNotSupported $exception) {
            $this->assertSame('Error description', $exception->getMessage());

            $response = $exception->getResponse();

            $this->assertSame(404, $response->getHttpCode());
            $this->assertSame('HTTP message', $response->getHttpMessage());
            $this->assertSame('More information', $response->getMoreInformation());
            $this->assertSame('E1283', $response->getErrors()[0]->getErrorCode());
            $this->assertSame('Error description', $response->getErrors()[0]->getErrorDescription());
            $this->assertSame('Error cause', $response->getErrors()[0]->getErrorCause());
            $this->assertSame('Error resolution', $response->getErrors()[0]->getErrorResolution());
        }
    }

    public function testItThrowsDeliveryUpdateNotAvailable(): void
    {
        $mock = new MockHandler([
            new Response(404, [], \json_encode([
                'httpCode' => 404,
                'httpMessage' => 'HTTP message',
                'moreInformation' => 'More information',
                'errors' => [[
                    'errorCode' => 'E1284',
                    'errorDescription' => 'Error description',
                    'errorCause' => 'Error cause',
                    'errorResolution' => 'Error resolution'
                ]]
            ]))
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        try {
            (new Signature($client, 'client-id', 'client-secret'))
                ->setTrackingNumber('123456789GB');
        } catch (DeliveryUpdateNotAvailable $exception) {
            $this->assertSame('Error description', $exception->getMessage());

            $response = $exception->getResponse();

            $this->assertSame(404, $response->getHttpCode());
            $this->assertSame('HTTP message', $response->getHttpMessage());
            $this->assertSame('More information', $response->getMoreInformation());
            $this->assertSame('E1284', $response->getErrors()[0]->getErrorCode());
            $this->assertSame('Error description', $response->getErrors()[0]->getErrorDescription());
            $this->assertSame('Error cause', $response->getErrors()[0]->getErrorCause());
            $this->assertSame('Error resolution', $response->getErrors()[0]->getErrorResolution());
        }
    }

    public function testItThrowsUpdateNotAvailable(): void
    {
        $mock = new MockHandler([
            new Response(404, [], \json_encode([
                'httpCode' => 404,
                'httpMessage' => 'HTTP message',
                'moreInformation' => 'More information',
                'errors' => [[
                    'errorCode' => 'E1308',
                    'errorDescription' => 'Error description',
                    'errorCause' => 'Error cause',
                    'errorResolution' => 'Error resolution'
                ]]
            ]))
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        try {
            (new Signature($client, 'client-id', 'client-secret'))
                ->setTrackingNumber('123456789GB');
        } catch (UpdateNotAvailable $exception) {
            $this->assertSame('Error description', $exception->getMessage());

            $response = $exception->getResponse();

            $this->assertSame(404, $response->getHttpCode());
            $this->assertSame('HTTP message', $response->getHttpMessage());
            $this->assertSame('More information', $response->getMoreInformation());
            $this->assertSame('E1308', $response->getErrors()[0]->getErrorCode());
            $this->assertSame('Error description', $response->getErrors()[0]->getErrorDescription());
            $this->assertSame('Error cause', $response->getErrors()[0]->getErrorCause());
            $this->assertSame('Error resolution', $response->getErrors()[0]->getErrorResolution());
        }
    }

    public function testItThrowsTrackingUnavailableWhenHttpStatusIs200AndHttpCodeIs503(): void
    {
        $mock = new MockHandler([
            new Response(200, [], \json_encode([
                'httpCode' => 503,
                'httpMessage' => 'HTTP message',
                'moreInformation' => 'More information',
                'errors' => [[
                    'errorCode' => 'E1307',
                    'errorDescription' => 'Error description',
                    'errorResolution' => 'Error resolution'
                ]]
            ]))
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        try {
            (new Signature($client, 'client-id', 'client-secret'))
                ->setTrackingNumber('123456789GB');
        } catch (TrackingUnavailable $exception) {
            $this->assertSame('Error description', $exception->getMessage());

            $response = $exception->getResponse();

            $this->assertSame(503, $response->getHttpCode());
            $this->assertSame('HTTP message', $response->getHttpMessage());
            $this->assertSame('More information', $response->getMoreInformation());
            $this->assertSame('E1307', $response->getErrors()[0]->getErrorCode());
            $this->assertSame('Error description', $response->getErrors()[0]->getErrorDescription());
            $this->assertNull($response->getErrors()[0]->getErrorCause());
            $this->assertSame('Error resolution', $response->getErrors()[0]->getErrorResolution());
        }
    }

    public function testItThrowsClientIdNotRegistered(): void
    {
        $mock = new MockHandler([
            new Response(401, [], \json_encode([
                'httpCode' => 401,
                'httpMessage' => 'HTTP message',
                'moreInformation' => 'More information'
            ]))
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        try {
            (new Signature($client, 'client-id', 'client-secret'))
                ->setTrackingNumber('123456789GB');
        } catch (ClientIdNotRegistered $exception) {
            $this->assertSame('More information', $exception->getMessage());

            $response = $exception->getResponse();

            $this->assertSame(401, $response->getHttpCode());
            $this->assertSame('HTTP message', $response->getHttpMessage());
            $this->assertSame('More information', $response->getMoreInformation());
            $this->assertSame([], $response->getErrors());
        }
    }

    public function testItThrowsUriNotFound(): void
    {
        $mock = new MockHandler([
            new Response(404, [], \json_encode([
                'httpCode' => 404,
                'httpMessage' => 'HTTP message',
                'moreInformation' => 'More information'
            ]))
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        try {
            (new Signature($client, 'client-id', 'client-secret'))
                ->setTrackingNumber('123456789GB');
        } catch (UriNotFound $exception) {
            $this->assertSame('More information', $exception->getMessage());

            $response = $exception->getResponse();

            $this->assertSame(404, $response->getHttpCode());
            $this->assertSame('HTTP message', $response->getHttpMessage());
            $this->assertSame('More information', $response->getMoreInformation());
            $this->assertSame([], $response->getErrors());
        }
    }

    public function testItThrowsMethodNotAllowed(): void
    {
        $mock = new MockHandler([
            new Response(405, [], \json_encode([
                'httpCode' => 405,
                'httpMessage' => 'HTTP message',
                'moreInformation' => 'More information'
            ]))
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        try {
            (new Signature($client, 'client-id', 'client-secret'))
                ->setTrackingNumber('123456789GB');
        } catch (MethodNotAllowed $exception) {
            $this->assertSame('More information', $exception->getMessage());

            $response = $exception->getResponse();

            $this->assertSame(405, $response->getHttpCode());
            $this->assertSame('HTTP message', $response->getHttpMessage());
            $this->assertSame('More information', $response->getMoreInformation());
            $this->assertSame([], $response->getErrors());
        }
    }

    public function testItThrowsMaximumParametersExceeded(): void
    {
        $mock = new MockHandler([
            new Response(400, [], \json_encode([
                'httpCode' => 400,
                'httpMessage' => 'HTTP message',
                'errors' => [[
                    'errorCode' => 'E0013',
                    'errorDescription' => 'Error description',
                    'errorResolution' => 'Error resolution'
                ]]
            ]))
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        try {
            (new Signature($client, 'client-id', 'client-secret'))
                ->setTrackingNumber('123456789GB');
        } catch (MaximumParametersExceeded $exception) {
            $this->assertSame('Error description', $exception->getMessage());

            $response = $exception->getResponse();

            $this->assertSame(400, $response->getHttpCode());
            $this->assertSame('HTTP message', $response->getHttpMessage());
            $this->assertNull($response->getMoreInformation());
            $this->assertSame('E0013', $response->getErrors()[0]->getErrorCode());
            $this->assertSame('Error description', $response->getErrors()[0]->getErrorDescription());
            $this->assertNull($response->getErrors()[0]->getErrorCause());
            $this->assertSame('Error resolution', $response->getErrors()[0]->getErrorResolution());
        }
    }

    public function testItThrowsSchemaValidationFailed(): void
    {
        $mock = new MockHandler([
            new Response(400, [], \json_encode([
                'httpCode' => 400,
                'httpMessage' => 'HTTP message',
                'errors' => [[
                    'errorCode' => 'E0004',
                    'errorDescription' => 'Error description',
                    'errorCause' => 'Error cause',
                    'errorResolution' => 'Error resolution'
                ]]
            ]))
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        try {
            (new Signature($client, 'client-id', 'client-secret'))
                ->setTrackingNumber('123456789GB');
        } catch (SchemaValidationFailed $exception) {
            $this->assertSame('Error description', $exception->getMessage());

            $response = $exception->getResponse();

            $this->assertSame(400, $response->getHttpCode());
            $this->assertSame('HTTP message', $response->getHttpMessage());
            $this->assertNull($response->getMoreInformation());
            $this->assertSame('E0004', $response->getErrors()[0]->getErrorCode());
            $this->assertSame('Error description', $response->getErrors()[0]->getErrorDescription());
            $this->assertSame('Error cause', $response->getErrors()[0]->getErrorCause());
            $this->assertSame('Error resolution', $response->getErrors()[0]->getErrorResolution());
        }
    }

    public function testItThrowsTooManyRequests(): void
    {
        $mock = new MockHandler([
            new Response(429, [], \json_encode([
                'httpCode' => 429,
                'httpMessage' => 'HTTP message',
                'moreInformation' => 'More information',
                'errors' => [[
                    'errorCode' => 'E0010',
                    'errorDescription' => 'Error description',
                    'errorCause' => 'Error cause',
                    'errorResolution' => 'Error resolution'
                ]]
            ]))
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        try {
            (new Signature($client, 'client-id', 'client-secret'))
                ->setTrackingNumber('123456789GB');
        } catch (TooManyRequests $exception) {
            $this->assertSame('Error description', $exception->getMessage());

            $response = $exception->getResponse();

            $this->assertSame(429, $response->getHttpCode());
            $this->assertSame('HTTP message', $response->getHttpMessage());
            $this->assertSame('More information', $response->getMoreInformation());
            $this->assertSame('E0010', $response->getErrors()[0]->getErrorCode());
            $this->assertSame('Error description', $response->getErrors()[0]->getErrorDescription());
            $this->assertSame('Error cause', $response->getErrors()[0]->getErrorCause());
            $this->assertSame('Error resolution', $response->getErrors()[0]->getErrorResolution());
        }
    }

    public function testItThrowsInternalServerError(): void
    {
        $mock = new MockHandler([
            new Response(500, [], \json_encode([
                'httpCode' => 500,
                'httpMessage' => 'HTTP message',
                'errors' => [[
                    'errorCode' => 'E0009',
                    'errorDescription' => 'Error description',
                    'errorCause' => 'Error cause',
                    'errorResolution' => 'Error resolution'
                ]]
            ]))
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        try {
            (new Signature($client, 'client-id', 'client-secret'))
                ->setTrackingNumber('123456789GB');
        } catch (InternalServerError $exception) {
            $this->assertSame('Error description', $exception->getMessage());

            $response = $exception->getResponse();

            $this->assertSame(500, $response->getHttpCode());
            $this->assertSame('HTTP message', $response->getHttpMessage());
            $this->assertNull($response->getMoreInformation());
            $this->assertSame('E0009', $response->getErrors()[0]->getErrorCode());
            $this->assertSame('Error description', $response->getErrors()[0]->getErrorDescription());
            $this->assertSame('Error cause', $response->getErrors()[0]->getErrorCause());
            $this->assertSame('Error resolution', $response->getErrors()[0]->getErrorResolution());
        }
    }

    public function testItThrowsServiceUnavailable(): void
    {
        $mock = new MockHandler([
            new Response(503, [], \json_encode([
                'httpCode' => 503,
                'httpMessage' => 'HTTP message',
                'errors' => [[
                    'errorCode' => 'E0001',
                    'errorDescription' => 'Error description',
                    'errorCause' => 'Error cause',
                    'errorResolution' => 'Error resolution'
                ]]
            ]))
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        try {
            (new Signature($client, 'client-id', 'client-secret'))
                ->setTrackingNumber('123456789GB');
        } catch (ServiceUnavailable $exception) {
            $this->assertSame('Error description', $exception->getMessage());

            $response = $exception->getResponse();

            $this->assertSame(503, $response->getHttpCode());
            $this->assertSame('HTTP message', $response->getHttpMessage());
            $this->assertNull($response->getMoreInformation());
            $this->assertSame('E0001', $response->getErrors()[0]->getErrorCode());
            $this->assertSame('Error description', $response->getErrors()[0]->getErrorDescription());
            $this->assertSame('Error cause', $response->getErrors()[0]->getErrorCause());
            $this->assertSame('Error resolution', $response->getErrors()[0]->getErrorResolution());
        }
    }

    public function testItThrowsBadRequestWhenErrorsNotReturned(): void
    {
        $mock = new MockHandler([
            new Response(400, [], \json_encode([
                'httpCode' => 400,
                'httpMessage' => 'HTTP message',
                'moreInformation' => 'More information'
            ]))
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        try {
            (new Signature($client, 'client-id', 'client-secret'))
                ->setTrackingNumber('123456789GB');
        } catch (BadRequest $exception) {
            $this->assertSame('More information', $exception->getMessage());

            $response = $exception->getResponse();

            $this->assertSame(400, $response->getHttpCode());
            $this->assertSame('HTTP message', $response->getHttpMessage());
            $this->assertSame('More information', $response->getMoreInformation());
        }
    }

    public function testItThrowsTooManyRequestsWhenErrorsNotReturned(): void
    {
        $mock = new MockHandler([
            new Response(429, [], \json_encode([
                'httpCode' => 429,
                'httpMessage' => 'HTTP message',
                'moreInformation' => 'More information'
            ]))
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        try {
            (new Signature($client, 'client-id', 'client-secret'))
                ->setTrackingNumber('123456789GB');
        } catch (TooManyRequests $exception) {
            $this->assertSame('More information', $exception->getMessage());

            $response = $exception->getResponse();

            $this->assertSame(429, $response->getHttpCode());
            $this->assertSame('HTTP message', $response->getHttpMessage());
            $this->assertSame('More information', $response->getMoreInformation());
        }
    }

    public function testItThrowsInternalServerErrorWhenErrorsNotReturned(): void
    {
        $mock = new MockHandler([
            new Response(500, [], \json_encode([
                'httpCode' => 500,
                'httpMessage' => 'HTTP message',
                'moreInformation' => 'More information'
            ]))
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        try {
            (new Signature($client, 'client-id', 'client-secret'))
                ->setTrackingNumber('123456789GB');
        } catch (InternalServerError $exception) {
            $this->assertSame('More information', $exception->getMessage());

            $response = $exception->getResponse();

            $this->assertSame(500, $response->getHttpCode());
            $this->assertSame('HTTP message', $response->getHttpMessage());
            $this->assertSame('More information', $response->getMoreInformation());
        }
    }

    public function testItThrowsInternalServerErrorWhenErrorsMoreInformationAndHttpMessageNotReturned(): void
    {
        $mock = new MockHandler([
            new Response(500, [], \json_encode([
                'httpCode' => 500
            ]))
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        try {
            (new Signature($client, 'client-id', 'client-secret'))
                ->setTrackingNumber('123456789GB');
        } catch (InternalServerError $exception) {
            $this->assertSame('Royal Mail Error', $exception->getMessage());

            $response = $exception->getResponse();

            $this->assertSame(500, $response->getHttpCode());
        }
    }

    public function testItThrowsServiceUnavailableWhenErrorsAndMoreInformationNotReturned(): void
    {
        $mock = new MockHandler([
            new Response(503, [], \json_encode([
                'httpCode' => 503,
                'httpMessage' => 'HTTP message'
            ]))
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        try {
            (new Signature($client, 'client-id', 'client-secret'))
                ->setTrackingNumber('123456789GB');
        } catch (ServiceUnavailable $exception) {
            $this->assertSame('HTTP message', $exception->getMessage());

            $response = $exception->getResponse();

            $this->assertSame(503, $response->getHttpCode());
            $this->assertSame('HTTP message', $response->getHttpMessage());
            $this->assertNull($response->getMoreInformation());
        }
    }

    public function testItReturnsResponseAndDoesNotThrowInvalidBarcodeReferenceOn400Response(): void
    {
        $mock = new MockHandler([
            new Response(400, [], \json_encode([
                'httpCode' => 400,
                'httpMessage' => 'HTTP message',
                'moreInformation' => 'More information',
                'errors' => [[
                    'errorCode' => 'E1142',
                    'errorDescription' => 'Error description',
                    'errorCause' => 'Error cause',
                    'errorResolution' => 'Error resolution'
                ]]
            ]))
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        $response = (new Signature($client, 'client-id', 'client-secret', false, false))
            ->setTrackingNumber('123456789GB')->getResponse();

        $this->assertSame(400, $response->getHttpCode());
        $this->assertSame('HTTP message', $response->getHttpMessage());
        $this->assertSame('More information', $response->getMoreInformation());
        $this->assertSame('E1142', $response->getErrors()[0]->getErrorCode());
        $this->assertSame('Error description', $response->getErrors()[0]->getErrorDescription());
        $this->assertSame('Error cause', $response->getErrors()[0]->getErrorCause());
        $this->assertSame('Error resolution', $response->getErrors()[0]->getErrorResolution());
    }

    public function testItReturnsResponseAndDoesNotThrowInvalidBarcodeReferenceOn404Response(): void
    {
        $mock = new MockHandler([
            new Response(404, [], \json_encode([
                'httpCode' => 404,
                'httpMessage' => 'HTTP message',
                'moreInformation' => 'More information',
                'errors' => [[
                    'errorCode' => 'E1142',
                    'errorDescription' => 'Error description',
                    'errorCause' => 'Error cause',
                    'errorResolution' => 'Error resolution'
                ]]
            ]))
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        $response = (new Signature($client, 'client-id', 'client-secret', false, false))
            ->setTrackingNumber('123456789GB')->getResponse();

        $this->assertSame(404, $response->getHttpCode());
        $this->assertSame('HTTP message', $response->getHttpMessage());
        $this->assertSame('More information', $response->getMoreInformation());
        $this->assertSame('E1142', $response->getErrors()[0]->getErrorCode());
        $this->assertSame('Error description', $response->getErrors()[0]->getErrorDescription());
        $this->assertSame('Error cause', $response->getErrors()[0]->getErrorCause());
        $this->assertSame('Error resolution', $response->getErrors()[0]->getErrorResolution());
    }

    public function testItReturnsResponseAndDoesNotThrowProofOfDeliveryUnavailable(): void
    {
        $mock = new MockHandler([
            new Response(404, [], \json_encode([
                'httpCode' => 404,
                'httpMessage' => 'HTTP message',
                'moreInformation' => 'More information',
                'errors' => [[
                    'errorCode' => 'E1144',
                    'errorDescription' => 'Error description',
                    'errorCause' => 'Error cause',
                    'errorResolution' => 'Error resolution'
                ]]
            ]))
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        $response = (new Signature($client, 'client-id', 'client-secret', false, false))
            ->setTrackingNumber('123456789GB')->getResponse();

        $this->assertSame(404, $response->getHttpCode());
        $this->assertSame('HTTP message', $response->getHttpMessage());
        $this->assertSame('More information', $response->getMoreInformation());
        $this->assertSame('E1144', $response->getErrors()[0]->getErrorCode());
        $this->assertSame('Error description', $response->getErrors()[0]->getErrorDescription());
        $this->assertSame('Error cause', $response->getErrors()[0]->getErrorCause());
        $this->assertSame('Error resolution', $response->getErrors()[0]->getErrorResolution());
    }

    public function testItReturnsResponseAndDoesNotThrowProofOfDeliveryUnavailableForProduct(): void
    {
        $mock = new MockHandler([
            new Response(404, [], \json_encode([
                'httpCode' => 404,
                'httpMessage' => 'HTTP message',
                'moreInformation' => 'More information',
                'errors' => [[
                    'errorCode' => 'E1145',
                    'errorDescription' => 'Error description',
                    'errorCause' => 'Error cause',
                    'errorResolution' => 'Error resolution'
                ]]
            ]))
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        $response = (new Signature($client, 'client-id', 'client-secret', false, false))
            ->setTrackingNumber('123456789GB')->getResponse();

        $this->assertSame(404, $response->getHttpCode());
        $this->assertSame('HTTP message', $response->getHttpMessage());
        $this->assertSame('More information', $response->getMoreInformation());
        $this->assertSame('E1145', $response->getErrors()[0]->getErrorCode());
        $this->assertSame('Error description', $response->getErrors()[0]->getErrorDescription());
        $this->assertSame('Error cause', $response->getErrors()[0]->getErrorCause());
        $this->assertSame('Error resolution', $response->getErrors()[0]->getErrorResolution());
    }

    public function testItReturnsResponseAndDoesNotThrowTrackingNotSupported(): void
    {
        $mock = new MockHandler([
            new Response(404, [], \json_encode([
                'httpCode' => 404,
                'httpMessage' => 'HTTP message',
                'moreInformation' => 'More information',
                'errors' => [[
                    'errorCode' => 'E1283',
                    'errorDescription' => 'Error description',
                    'errorCause' => 'Error cause',
                    'errorResolution' => 'Error resolution'
                ]]
            ]))
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        $response = (new Signature($client, 'client-id', 'client-secret', false, false))
            ->setTrackingNumber('123456789GB')->getResponse();

        $this->assertSame(404, $response->getHttpCode());
        $this->assertSame('HTTP message', $response->getHttpMessage());
        $this->assertSame('More information', $response->getMoreInformation());
        $this->assertSame('E1283', $response->getErrors()[0]->getErrorCode());
        $this->assertSame('Error description', $response->getErrors()[0]->getErrorDescription());
        $this->assertSame('Error cause', $response->getErrors()[0]->getErrorCause());
        $this->assertSame('Error resolution', $response->getErrors()[0]->getErrorResolution());
    }

    public function testItReturnsResponseAndDoesNotThrowDeliveryUpdateNotAvailable(): void
    {
        $mock = new MockHandler([
            new Response(404, [], \json_encode([
                'httpCode' => 404,
                'httpMessage' => 'HTTP message',
                'moreInformation' => 'More information',
                'errors' => [[
                    'errorCode' => 'E1284',
                    'errorDescription' => 'Error description',
                    'errorCause' => 'Error cause',
                    'errorResolution' => 'Error resolution'
                ]]
            ]))
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        $response = (new Signature($client, 'client-id', 'client-secret', false, false))
            ->setTrackingNumber('123456789GB')->getResponse();

        $this->assertSame(404, $response->getHttpCode());
        $this->assertSame('HTTP message', $response->getHttpMessage());
        $this->assertSame('More information', $response->getMoreInformation());
        $this->assertSame('E1284', $response->getErrors()[0]->getErrorCode());
        $this->assertSame('Error description', $response->getErrors()[0]->getErrorDescription());
        $this->assertSame('Error cause', $response->getErrors()[0]->getErrorCause());
        $this->assertSame('Error resolution', $response->getErrors()[0]->getErrorResolution());
    }

    public function testItReturnsResponseAndDoesNotThrowUpdateNotAvailable(): void
    {
        $mock = new MockHandler([
            new Response(404, [], \json_encode([
                'httpCode' => 404,
                'httpMessage' => 'HTTP message',
                'moreInformation' => 'More information',
                'errors' => [[
                    'errorCode' => 'E1308',
                    'errorDescription' => 'Error description',
                    'errorCause' => 'Error cause',
                    'errorResolution' => 'Error resolution'
                ]]
            ]))
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        $response = (new Signature($client, 'client-id', 'client-secret', false, false))
            ->setTrackingNumber('123456789GB')->getResponse();

        $this->assertSame(404, $response->getHttpCode());
        $this->assertSame('HTTP message', $response->getHttpMessage());
        $this->assertSame('More information', $response->getMoreInformation());
        $this->assertSame('E1308', $response->getErrors()[0]->getErrorCode());
        $this->assertSame('Error description', $response->getErrors()[0]->getErrorDescription());
        $this->assertSame('Error cause', $response->getErrors()[0]->getErrorCause());
        $this->assertSame('Error resolution', $response->getErrors()[0]->getErrorResolution());
    }

    public function testItReturnsResponseAndDoesNotThrowTrackingUnavailableWhenHttpStatusIs200AndHttpCodeIs503(): void
    {
        $mock = new MockHandler([
            new Response(200, [], \json_encode([
                'httpCode' => 503,
                'httpMessage' => 'HTTP message',
                'moreInformation' => 'More information',
                'errors' => [[
                    'errorCode' => 'E1307',
                    'errorDescription' => 'Error description',
                    'errorResolution' => 'Error resolution'
                ]]
            ]))
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        $response = (new Signature($client, 'client-id', 'client-secret', false, false))
            ->setTrackingNumber('123456789GB')->getResponse();

        $this->assertSame(503, $response->getHttpCode());
        $this->assertSame('HTTP message', $response->getHttpMessage());
        $this->assertSame('More information', $response->getMoreInformation());
        $this->assertSame('E1307', $response->getErrors()[0]->getErrorCode());
        $this->assertSame('Error description', $response->getErrors()[0]->getErrorDescription());
        $this->assertNull($response->getErrors()[0]->getErrorCause());
        $this->assertSame('Error resolution', $response->getErrors()[0]->getErrorResolution());
    }

    public function testItReturnsResponseAndDoesNotThrowClientIdNotRegistered(): void
    {
        $mock = new MockHandler([
            new Response(401, [], \json_encode([
                'httpCode' => 401,
                'httpMessage' => 'HTTP message',
                'moreInformation' => 'More information'
            ]))
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        $response = (new Signature($client, 'client-id', 'client-secret', false, false))
            ->setTrackingNumber('123456789GB')->getResponse();

        $this->assertSame(401, $response->getHttpCode());
        $this->assertSame('HTTP message', $response->getHttpMessage());
        $this->assertSame('More information', $response->getMoreInformation());
        $this->assertSame([], $response->getErrors());
    }

    public function testItReturnsResponseAndDoesNotThrowUriNotFound(): void
    {
        $mock = new MockHandler([
            new Response(404, [], \json_encode([
                'httpCode' => 404,
                'httpMessage' => 'HTTP message',
                'moreInformation' => 'More information'
            ]))
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        $response = (new Signature($client, 'client-id', 'client-secret', false, false))
            ->setTrackingNumber('123456789GB')->getResponse();

        $this->assertSame(404, $response->getHttpCode());
        $this->assertSame('HTTP message', $response->getHttpMessage());
        $this->assertSame('More information', $response->getMoreInformation());
        $this->assertSame([], $response->getErrors());
    }

    public function testItReturnsResponseAndDoesNotThrowMethodNotAllowed(): void
    {
        $mock = new MockHandler([
            new Response(405, [], \json_encode([
                'httpCode' => 405,
                'httpMessage' => 'HTTP message',
                'moreInformation' => 'More information'
            ]))
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        $response = (new Signature($client, 'client-id', 'client-secret', false, false))
            ->setTrackingNumber('123456789GB')->getResponse();

        $this->assertSame(405, $response->getHttpCode());
        $this->assertSame('HTTP message', $response->getHttpMessage());
        $this->assertSame('More information', $response->getMoreInformation());
        $this->assertSame([], $response->getErrors());
    }

    public function testItReturnsResponseAndDoesNotThrowMaximumParametersExceeded(): void
    {
        $mock = new MockHandler([
            new Response(400, [], \json_encode([
                'httpCode' => 400,
                'httpMessage' => 'HTTP message',
                'errors' => [[
                    'errorCode' => 'E0013',
                    'errorDescription' => 'Error description',
                    'errorResolution' => 'Error resolution'
                ]]
            ]))
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        $response = (new Signature($client, 'client-id', 'client-secret', false, false))
            ->setTrackingNumber('123456789GB')->getResponse();

        $this->assertSame(400, $response->getHttpCode());
        $this->assertSame('HTTP message', $response->getHttpMessage());
        $this->assertNull($response->getMoreInformation());
        $this->assertSame('E0013', $response->getErrors()[0]->getErrorCode());
        $this->assertSame('Error description', $response->getErrors()[0]->getErrorDescription());
        $this->assertNull($response->getErrors()[0]->getErrorCause());
        $this->assertSame('Error resolution', $response->getErrors()[0]->getErrorResolution());
    }

    public function testItReturnsResponseAndDoesNotThrowSchemaValidationFailed(): void
    {
        $mock = new MockHandler([
            new Response(400, [], \json_encode([
                'httpCode' => 400,
                'httpMessage' => 'HTTP message',
                'errors' => [[
                    'errorCode' => 'E0004',
                    'errorDescription' => 'Error description',
                    'errorCause' => 'Error cause',
                    'errorResolution' => 'Error resolution'
                ]]
            ]))
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        $response = (new Signature($client, 'client-id', 'client-secret', false, false))
            ->setTrackingNumber('123456789GB')->getResponse();

        $this->assertSame(400, $response->getHttpCode());
        $this->assertSame('HTTP message', $response->getHttpMessage());
        $this->assertNull($response->getMoreInformation());
        $this->assertSame('E0004', $response->getErrors()[0]->getErrorCode());
        $this->assertSame('Error description', $response->getErrors()[0]->getErrorDescription());
        $this->assertSame('Error cause', $response->getErrors()[0]->getErrorCause());
        $this->assertSame('Error resolution', $response->getErrors()[0]->getErrorResolution());
    }

    public function testItReturnsResponseAndDoesNotThrowTooManyRequests(): void
    {
        $mock = new MockHandler([
            new Response(429, [], \json_encode([
                'httpCode' => 429,
                'httpMessage' => 'HTTP message',
                'moreInformation' => 'More information',
                'errors' => [[
                    'errorCode' => 'E0010',
                    'errorDescription' => 'Error description',
                    'errorCause' => 'Error cause',
                    'errorResolution' => 'Error resolution'
                ]]
            ]))
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        $response = (new Signature($client, 'client-id', 'client-secret', false, false))
            ->setTrackingNumber('123456789GB')->getResponse();

        $this->assertSame(429, $response->getHttpCode());
        $this->assertSame('HTTP message', $response->getHttpMessage());
        $this->assertSame('More information', $response->getMoreInformation());
        $this->assertSame('E0010', $response->getErrors()[0]->getErrorCode());
        $this->assertSame('Error description', $response->getErrors()[0]->getErrorDescription());
        $this->assertSame('Error cause', $response->getErrors()[0]->getErrorCause());
        $this->assertSame('Error resolution', $response->getErrors()[0]->getErrorResolution());
    }

    public function testItReturnsResponseAndDoesNotThrowInternalServerError(): void
    {
        $mock = new MockHandler([
            new Response(500, [], \json_encode([
                'httpCode' => 500,
                'httpMessage' => 'HTTP message',
                'errors' => [[
                    'errorCode' => 'E0009',
                    'errorDescription' => 'Error description',
                    'errorCause' => 'Error cause',
                    'errorResolution' => 'Error resolution'
                ]]
            ]))
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        $response = (new Signature($client, 'client-id', 'client-secret', false, false))
            ->setTrackingNumber('123456789GB')->getResponse();

        $this->assertSame(500, $response->getHttpCode());
        $this->assertSame('HTTP message', $response->getHttpMessage());
        $this->assertNull($response->getMoreInformation());
        $this->assertSame('E0009', $response->getErrors()[0]->getErrorCode());
        $this->assertSame('Error description', $response->getErrors()[0]->getErrorDescription());
        $this->assertSame('Error cause', $response->getErrors()[0]->getErrorCause());
        $this->assertSame('Error resolution', $response->getErrors()[0]->getErrorResolution());
    }

    public function testItReturnsResponseAndDoesNotThrowServiceUnavailable(): void
    {
        $mock = new MockHandler([
            new Response(503, [], \json_encode([
                'httpCode' => 503,
                'httpMessage' => 'HTTP message',
                'errors' => [[
                    'errorCode' => 'E0001',
                    'errorDescription' => 'Error description',
                    'errorCause' => 'Error cause',
                    'errorResolution' => 'Error resolution'
                ]]
            ]))
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        $response = (new Signature($client, 'client-id', 'client-secret', false, false))
            ->setTrackingNumber('123456789GB')->getResponse();

        $this->assertSame(503, $response->getHttpCode());
        $this->assertSame('HTTP message', $response->getHttpMessage());
        $this->assertNull($response->getMoreInformation());
        $this->assertSame('E0001', $response->getErrors()[0]->getErrorCode());
        $this->assertSame('Error description', $response->getErrors()[0]->getErrorDescription());
        $this->assertSame('Error cause', $response->getErrors()[0]->getErrorCause());
        $this->assertSame('Error resolution', $response->getErrors()[0]->getErrorResolution());
    }

    private function mockResponse(): string
    {
        return '{
          "mailPieces": {
            "mailPieceId": "090367574000000FE1E1B",
            "carrierShortName": "RM",
            "carrierFullName": "Royal Mail Group Ltd",
            "signature": {
              "uniqueItemId": "090367574000000FE1E1B",
              "oneDBarcode": "FQ087430672GB",
              "recipientName": "Elliot",
              "signatureDateTime": "2017-03-30T16:15:00+01:00",
              "imageFormat": "image/svg+xml",
              "imageId": "001234",
              "height": 530,
              "width": 660,
              "image": "<svg></svg>"
            },
            "links": {
              "events": {
                "href": "/mailpieces/v2/FQ087430672GB/events",
                "title": "Events",
                "description": "Get events"
              },
              "summary": {
                "href": "/mailpieces/v2/summary?mailPieceId=090367574000000FE1E1B",
                "title": "Summary",
                "description": "Get summary"
              }
            }
          }
        }';
    }
}
