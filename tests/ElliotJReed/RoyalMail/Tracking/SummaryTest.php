<?php

declare(strict_types=1);

namespace Tests\ElliotJReed\RoyalMail\Tracking;

use DateTimeImmutable;
use ElliotJReed\RoyalMail\Tracking\Exception\BadRequest;
use ElliotJReed\RoyalMail\Tracking\Exception\ClientIdNotRegistered;
use ElliotJReed\RoyalMail\Tracking\Exception\InternalServerError;
use ElliotJReed\RoyalMail\Tracking\Exception\MaximumParametersExceeded;
use ElliotJReed\RoyalMail\Tracking\Exception\MethodNotAllowed;
use ElliotJReed\RoyalMail\Tracking\Exception\RoyalMailResponseError;
use ElliotJReed\RoyalMail\Tracking\Exception\SchemaValidationFailed;
use ElliotJReed\RoyalMail\Tracking\Exception\ServiceUnavailable;
use ElliotJReed\RoyalMail\Tracking\Exception\TooManyRequests;
use ElliotJReed\RoyalMail\Tracking\Exception\UriNotFound;
use ElliotJReed\RoyalMail\Tracking\Summary;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class SummaryTest extends TestCase
{
    public function testItSendsRequestToRoyalMail(): void
    {
        $mock = new MockHandler([new Response(200, [], $this->mockResponse())]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        (new Summary($client, 'client-id', 'client-secret'))
            ->setTrackingNumbers(['123456789GB']);

        $this->assertSame('/mailpieces/v2/summary', $mock->getLastRequest()->getUri()->getPath());
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

        $response = (new Summary($client, 'client-id', 'client-secret'))
            ->setTrackingNumbers(['123456789GB'])->getResponse()->getMailPieces()[0];

        $this->assertSame('090367574000000FE1E1B', $response->getMailPieceId());
        $this->assertSame('RM', $response->getCarrierShortName());
        $this->assertSame('Royal Mail Group Ltd', $response->getCarrierFullName());

        $summary = $response->getSummary();
        $this->assertSame('090367574000000FE1E1B', $summary->getUniqueItemId());
        $this->assertSame('FQ087430672GB', $summary->getOneDBarcode());
        $this->assertSame('SD2', $summary->getProductId());
        $this->assertSame('Special Delivery Guaranteed', $summary->getProductName());
        $this->assertSame(
            'Our guaranteed next day service with tracking and a signature on delivery',
            $summary->getProductDescription()
        );
        $this->assertSame('NON-INTERNATIONAL', $summary->getProductCategory());
        $this->assertSame('GBR', $summary->getDestinationCountryCode());
        $this->assertSame(
            'United Kingdom of Great Britain and Northern Ireland',
            $summary->getDestinationCountryName()
        );
        $this->assertSame('GBR', $summary->getOriginCountryCode());
        $this->assertSame('United Kingdom of Great Britain and Northern Ireland', $summary->getOriginCountryName());
        $this->assertSame('EVNMI', $summary->getLastEventCode());
        $this->assertSame('Forwarded - Mis-sort', $summary->getLastEventName());
        $this->assertEquals(new DateTimeImmutable('2016-10-20T10:04:00+01:00'), $summary->getLastEventDateTime());
        $this->assertSame('Stafford DO', $summary->getLastEventLocationName());
        $this->assertSame('It is being redirected', $summary->getStatusDescription());
        $this->assertSame('IN TRANSIT', $summary->getStatusCategory());
        $this->assertSame('The item is in transit', $summary->getStatusHelpText());
        $this->assertSame(
            'Item FQ087430672GB was forwarded to the Delivery Office on 2016-10-20.',
            $summary->getSummaryLine()
        );

        $internationalPostalProvider = $summary->getInternationalPostalProvider();
        $this->assertSame('https://www.royalmail.com/track-your-item', $internationalPostalProvider->getUrl());
        $this->assertSame('Royal Mail Group Ltd', $internationalPostalProvider->getTitle());
        $this->assertSame('Royal Mail Group Ltd', $internationalPostalProvider->getDescription());

        $events = $response->getLinks()->getEvents();
        $this->assertSame('/mailpieces/v2/FQ087430672GB/events', $events->getHref());
        $this->assertSame('Events', $events->getTitle());
        $this->assertSame('Get events', $events->getDescription());

        $error = $response->getError();
        $this->assertSame('E1142', $error->getErrorCode());
        $this->assertSame('Barcode reference mailPieceId is not recognised', $error->getErrorDescription());
        $this->assertSame('A mail item with that barcode cannot be located', $error->getErrorCause());
        $this->assertSame('Check barcode and resubmit', $error->getErrorResolution());
    }

    public function testItReturnsTrackingDataInTheEventOfDateErrorException(): void
    {
        $apiResponse = '{
          "mailPieces": [
            {
              "mailPieceId": "090367574000000FE1E1B",
              "status": "200",
              "carrierShortName": "RM",
              "carrierFullName": "Royal Mail Group Ltd",
              "summary": {
                "uniqueItemId": "090367574000000FE1E1B",
                "lastEventDateTime": "UNPARSEABLE DATETIME"
              }
            }
          ]
        }';
        $mock = new MockHandler([new Response(200, [], $apiResponse)]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        $response = (new Summary($client, 'client-id', 'client-secret'))
            ->setTrackingNumbers(['123456789GB'])->getResponse()->getMailPieces()[0];

        $this->assertSame('090367574000000FE1E1B', $response->getMailPieceId());
        $this->assertSame('RM', $response->getCarrierShortName());
        $this->assertSame('Royal Mail Group Ltd', $response->getCarrierFullName());

        $summary = $response->getSummary();
        $this->assertSame('090367574000000FE1E1B', $summary->getUniqueItemId());
        $this->assertNull($summary->getLastEventDateTime());
    }

    public function testItReturnsTrackingDataAsJson(): void
    {
        $mock = new MockHandler([new Response(200, [], $this->mockResponse())]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        $response = (new Summary($client, 'client-id', 'client-secret'))
            ->setTrackingNumbers(['123456789GB']);

        $this->assertJsonStringEqualsJsonString('{
          "errors": [],
          "httpCode": null,
          "httpMessage": null,
          "mailPieces": [
            {
              "carrierFullName": "Royal Mail Group Ltd",
              "carrierShortName": "RM",
              "error": {
                "errorCause": "A mail item with that barcode cannot be located",
                "errorCode": "E1142",
                "errorDescription": "Barcode reference mailPieceId is not recognised",
                "errorResolution": "Check barcode and resubmit"
              },
              "links": {
                "events": {
                  "description": "Get events",
                  "href": "/mailpieces/v2/FQ087430672GB/events",
                  "title": "Events"
                }
              },
              "mailPieceId": "090367574000000FE1E1B",
              "summary": {
                "destinationCountryCode": "GBR",
                "destinationCountryName": "United Kingdom of Great Britain and Northern Ireland",
                "internationalPostalProvider": {
                  "description": "Royal Mail Group Ltd",
                  "title": "Royal Mail Group Ltd",
                  "url": "https://www.royalmail.com/track-your-item"
                },
                "lastEventCode": "EVNMI",
                "lastEventDateTime": "2016-10-20T10:04:00+01:00",
                "lastEventLocationName": "Stafford DO",
                "lastEventName": "Forwarded - Mis-sort",
                "oneDBarcode": "FQ087430672GB",
                "originCountryCode": "GBR",
                "originCountryName": "United Kingdom of Great Britain and Northern Ireland",
                "productCategory": "NON-INTERNATIONAL",
                "productDescription": "Our guaranteed next day service with tracking and a signature on delivery",
                "productId": "SD2",
                "productName": "Special Delivery Guaranteed",
                "statusCategory": "IN TRANSIT",
                "statusDescription": "It is being redirected",
                "statusHelpText": "The item is in transit",
                "summaryLine": "Item FQ087430672GB was forwarded to the Delivery Office on 2016-10-20.",
                "uniqueItemId": "090367574000000FE1E1B"
              }
            }
          ],
          "moreInformation": null
        }', $response->asJson());
    }

    public function testItRemovesNonAlphanumericCharactersFromTrackingNumbers(): void
    {
        $mock = new MockHandler([new Response(200, [], $this->mockResponse())]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        $response = (new Summary($client, 'client-id', 'client-secret'))
            ->setTrackingNumbers(['!"£$%^&*()12345             6789GB)(*&^%$!', 'JH987654321GB'])
            ->getResponse();

        $this->assertSame('/mailpieces/v2/summary', $mock->getLastRequest()->getUri()->getPath());
        $this->assertSame('mailPieceId=123456789GB,JH987654321GB', $mock->getLastRequest()->getUri()->getQuery());
        $this->assertSame('090367574000000FE1E1B', $response->getMailPieces()[0]->getMailPieceId());
    }

    public function testItThrowsExceptionWhenResponseIsSuccessfulButJsonIsInvalid(): void
    {
        $mock = new MockHandler([new Response(200, [], 'NOT JSON')]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        $this->expectException(RoyalMailResponseError::class);
        $this->expectExceptionMessage('(200) NOT JSON');

        (new Summary($client, 'client-id', 'client-secret'))
            ->setTrackingNumbers(['123456789GB']);
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

        (new Summary($client, 'client-id', 'client-secret'))
            ->setTrackingNumbers(['123456789GB']);
    }

    public function testItThrowsExceptionWhenTransferExceptionThrownByGuzzle(): void
    {
        $mock = new MockHandler([
            new TransferException('Transfer Exception')
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        $this->expectException(RoyalMailResponseError::class);
        $this->expectExceptionMessage('Transfer Exception');

        (new Summary($client, 'client-id', 'client-secret'))
            ->setTrackingNumbers(['123456789GB']);
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
            (new Summary($client, 'client-id', 'client-secret'))
                ->setTrackingNumbers(['AB0AB0123456789GB']);
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
            (new Summary($client, 'client-id', 'client-secret'))
                ->setTrackingNumbers(['AB0AB0123456789GB']);
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
            (new Summary($client, 'client-id', 'client-secret'))
                ->setTrackingNumbers(['AB0AB0123456789GB']);
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
            (new Summary($client, 'client-id', 'client-secret'))
                ->setTrackingNumbers(['AB0AB0123456789GB']);
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
            (new Summary($client, 'client-id', 'client-secret'))
                ->setTrackingNumbers(['AB0AB0123456789GB']);
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
            (new Summary($client, 'client-id', 'client-secret'))
                ->setTrackingNumbers(['AB0AB0123456789GB']);
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
            (new Summary($client, 'client-id', 'client-secret'))
                ->setTrackingNumbers(['AB0AB0123456789GB']);
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
            (new Summary($client, 'client-id', 'client-secret'))
                ->setTrackingNumbers(['AB0AB0123456789GB']);
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
            (new Summary($client, 'client-id', 'client-secret'))
                ->setTrackingNumbers(['123456789GB']);
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
            (new Summary($client, 'client-id', 'client-secret'))
                ->setTrackingNumbers(['123456789GB']);
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
            (new Summary($client, 'client-id', 'client-secret'))
                ->setTrackingNumbers(['123456789GB']);
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
            (new Summary($client, 'client-id', 'client-secret'))
                ->setTrackingNumbers(['123456789GB']);
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
            (new Summary($client, 'client-id', 'client-secret'))
                ->setTrackingNumbers(['123456789GB']);
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

        $response = (new Summary($client, 'client-id', 'client-secret', false))
            ->setTrackingNumbers(['AB0AB0123456789GB'])->getResponse();

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

        $response = (new Summary($client, 'client-id', 'client-secret', false))
            ->setTrackingNumbers(['AB0AB0123456789GB'])->getResponse();

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

        $response = (new Summary($client, 'client-id', 'client-secret', false))
            ->setTrackingNumbers(['AB0AB0123456789GB'])->getResponse();

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

        $response = (new Summary($client, 'client-id', 'client-secret', false))
            ->setTrackingNumbers(['AB0AB0123456789GB'])->getResponse();

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

        $response = (new Summary($client, 'client-id', 'client-secret', false))
            ->setTrackingNumbers(['AB0AB0123456789GB'])->getResponse();

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

        $response = (new Summary($client, 'client-id', 'client-secret', false))
            ->setTrackingNumbers(['AB0AB0123456789GB'])->getResponse();

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

        $response = (new Summary($client, 'client-id', 'client-secret', false))
            ->setTrackingNumbers(['AB0AB0123456789GB'])->getResponse();

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

        $response = (new Summary($client, 'client-id', 'client-secret', false))
            ->setTrackingNumbers(['AB0AB0123456789GB'])->getResponse();

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

        $response = (new Summary($client, 'client-id', 'client-secret', false))
            ->setTrackingNumbers(['AB0AB0123456789GB'])->getResponse();

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

        $response = (new Summary($client, 'client-id', 'client-secret', false))
            ->setTrackingNumbers(['AB0AB0123456789GB'])->getResponse();

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

        $response = (new Summary($client, 'client-id', 'client-secret', false))
            ->setTrackingNumbers(['AB0AB0123456789GB'])->getResponse();

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

        $response = (new Summary($client, 'client-id', 'client-secret', false))
            ->setTrackingNumbers(['AB0AB0123456789GB'])->getResponse();

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

        $response = (new Summary($client, 'client-id', 'client-secret', false))
            ->setTrackingNumbers(['AB0AB0123456789GB'])->getResponse();

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

        $response = (new Summary($client, 'client-id', 'client-secret', false))
            ->setTrackingNumbers(['AB0AB0123456789GB'])->getResponse();

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

        $response = (new Summary($client, 'client-id', 'client-secret', false))
            ->setTrackingNumbers(['AB0AB0123456789GB'])->getResponse();

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

        $response = (new Summary($client, 'client-id', 'client-secret', false))
            ->setTrackingNumbers(['AB0AB0123456789GB'])->getResponse();

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
          "mailPieces": [
            {
              "mailPieceId": "090367574000000FE1E1B",
              "status": "200",
              "carrierShortName": "RM",
              "carrierFullName": "Royal Mail Group Ltd",
              "summary": {
                "uniqueItemId": "090367574000000FE1E1B",
                "oneDBarcode": "FQ087430672GB",
                "productId": "SD2",
                "productName": "Special Delivery Guaranteed",
                "productDescription": "Our guaranteed next day service with tracking and a signature on delivery",
                "productCategory": "NON-INTERNATIONAL",
                "destinationCountryCode": "GBR",
                "destinationCountryName": "United Kingdom of Great Britain and Northern Ireland",
                "originCountryCode": "GBR",
                "originCountryName": "United Kingdom of Great Britain and Northern Ireland",
                "lastEventCode": "EVNMI",
                "lastEventName": "Forwarded - Mis-sort",
                "lastEventDateTime": "2016-10-20T10:04:00+01:00",
                "lastEventLocationName": "Stafford DO",
                "statusDescription": "It is being redirected",
                "statusCategory": "IN TRANSIT",
                "statusHelpText": "The item is in transit",
                "summaryLine": "Item FQ087430672GB was forwarded to the Delivery Office on 2016-10-20.",
                "internationalPostalProvider": {
                  "url": "https://www.royalmail.com/track-your-item",
                  "title": "Royal Mail Group Ltd",
                  "description": "Royal Mail Group Ltd"
                }
              },
              "links": {
                "events": {
                  "href": "/mailpieces/v2/FQ087430672GB/events",
                  "title": "Events",
                  "description": "Get events"
                }
              },
              "error": {
                "errorCode": "E1142",
                "errorDescription": "Barcode reference mailPieceId is not recognised",
                "errorCause": "A mail item with that barcode cannot be located",
                "errorResolution": "Check barcode and resubmit"
              }
            }
          ]
        }';
    }
}
