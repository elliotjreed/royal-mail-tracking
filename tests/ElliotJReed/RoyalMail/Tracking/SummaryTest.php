<?php

declare(strict_types=1);

namespace Tests\ElliotJReed\RoyalMail\Tracking;

use DateTimeImmutable;
use ElliotJReed\RoyalMail\Tracking\Exception\Forbidden;
use ElliotJReed\RoyalMail\Tracking\Exception\InvalidCredentials;
use ElliotJReed\RoyalMail\Tracking\Exception\RequestError;
use ElliotJReed\RoyalMail\Tracking\Exception\RequestLimitExceeded;
use ElliotJReed\RoyalMail\Tracking\Exception\ResponseError;
use ElliotJReed\RoyalMail\Tracking\Exception\RoyalMailServerError;
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
            ->setTrackingNumbers('123456789GB');

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
            ->setTrackingNumbers('123456789GB')->get()[0];

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
    }

    public function testItReturnsTrackingDataError(): void
    {
        $mock = new MockHandler([new Response(200, [], '{
          "mailPieces": [
            {
              "mailPieceId": "090367574000000FE1E1B",
              "status": "200",
              "carrierShortName": "RM",
              "carrierFullName": "Royal Mail Group Ltd",
              "error": {
                "errorCode": "E1142",
                "errorDescription": "Barcode reference $mailPieceId isn\'t recognised",
                "errorCause": "A mail item with that barcode cannot be located",
                "errorResolution": "Check barcode and resubmit"
              }
            }
          ]
        }')]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        $response = (new Summary($client, 'client-id', 'client-secret'))
            ->setTrackingNumbers('123456789GB')->get()[0];

        $this->assertSame('090367574000000FE1E1B', $response->getMailPieceId());
        $this->assertSame('RM', $response->getCarrierShortName());
        $this->assertSame('Royal Mail Group Ltd', $response->getCarrierFullName());
        $this->assertSame('E1142', $response->getError()->getErrorCode());
        $this->assertSame('Barcode reference $mailPieceId isn\'t recognised', $response->getError()->getErrorDescription());
        $this->assertSame('A mail item with that barcode cannot be located', $response->getError()->getErrorCause());
        $this->assertSame('Check barcode and resubmit', $response->getError()->getErrorResolution());
    }

    public function testItReturnsTrackingDataAsJson(): void
    {
        $mock = new MockHandler([new Response(200, [], $this->mockResponse())]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        $response = (new Summary($client, 'client-id', 'client-secret'))
            ->setTrackingNumbers('123456789GB');

        $this->assertJsonStringEqualsJsonString('[
          {
            "carrierFullName": "Royal Mail Group Ltd",
            "carrierShortName": "RM",
            "error": {
              "errorCause": "A mail item with that barcode cannot be located",
              "errorCode": "E1142",
              "errorDescription": "Barcode reference mailPieceId isn not recognised",
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
        ]', $response->asJson());
    }

    public function testItRemovesNonAlphanumericCharactersFromTrackingNumbers(): void
    {
        $mock = new MockHandler([new Response(200, [], $this->mockResponse())]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        $response = (new Summary($client, 'client-id', 'client-secret'))
            ->setTrackingNumbers('!"£$%^&*()12345             6789GB)(*&^%$!', 'JH987654321GB')->get();

        $this->assertSame('/mailpieces/v2/summary', $mock->getLastRequest()->getUri()->getPath());
        $this->assertSame('mailPieceId=123456789GB,JH987654321GB', $mock->getLastRequest()->getUri()->getQuery());
        $this->assertSame('090367574000000FE1E1B', $response[0]->getMailPieceId());
    }

    public function testItThrowsExceptionWhenResponseIsSuccessfulButJsonIsInvalid(): void
    {
        $mock = new MockHandler([new Response(200, [], 'NOT JSON')]);
        $client = new Client(['handler' => HandlerStack::create($mock), 'http_errors' => true]);

        $this->expectException(ResponseError::class);
        $this->expectExceptionMessage('(200) NOT JSON');

        (new Summary($client, 'client-id', 'client-secret'))
            ->setTrackingNumbers('123456789GB');
    }

    public function testItThrowsExceptionWhenResponseIsSuccessfulButJsonIsInvalidWhenHttpErrorSetToFalse(): void
    {
        $mock = new MockHandler([new Response(200, [], 'NOT JSON')]);
        $client = new Client(['handler' => HandlerStack::create($mock), 'http_errors' => false]);

        $this->expectException(ResponseError::class);
        $this->expectExceptionMessage('(200) NOT JSON');

        (new Summary($client, 'client-id', 'client-secret'))
            ->setTrackingNumbers('123456789GB');
    }

    public function testItThrowsResponseErrorWhenJsonReturnedFromApiDoesNotContainExpectedInformation(): void
    {
        $mock = new MockHandler([new Response(200, [], '{"something":"unexpected"}')]);
        $client = new Client(['handler' => HandlerStack::create($mock), 'http_errors' => true]);

        $this->expectException(ResponseError::class);
        $this->expectExceptionMessage('(200) {"something":"unexpected"}');

        (new Summary($client, 'client-id', 'client-secret'))
            ->setTrackingNumbers('123456789GB');
    }

    public function testItThrowsResponseErrorWhenJsonReturnedFromApiDoesNotContainExpectedInformationWhenHttpErrorsSetToFalse(): void
    {
        $mock = new MockHandler([new Response(200, [], '{"something":"unexpected"}')]);
        $client = new Client(['handler' => HandlerStack::create($mock), 'http_errors' => true]);

        $this->expectException(ResponseError::class);
        $this->expectExceptionMessage('(200) {"something":"unexpected"}');

        (new Summary($client, 'client-id', 'client-secret'))
            ->setTrackingNumbers('123456789GB');
    }

    public function testItThrowsInvalidCredentialsExceptionWhenGuzzleExceptionsOnAndBadRequestHttpStatus(): void
    {
        $mock = new MockHandler([
            new Response(400, [], \json_encode([
                'httpCode' => 400,
                'httpMessage' => 'HTTP message',
                'moreInformation' => 'More information',
                'errors' => [[
                    'errorCode' => 'Error code',
                    'errorDescription' => 'Error description',
                    'errorCause' => 'Error cause',
                    'errorResolution' => 'Error resolution'
                ]]
            ]))
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock), 'http_errors' => true]);

        try {
            (new Summary($client, 'client-id', 'client-secret'))
                ->setTrackingNumbers('123456789GB');
        } catch (RequestError $exception) {
            $this->assertSame('(400) More information', $exception->getMessage());
            $this->assertSame(400, $exception->getErrorResponse()->getHttpCode());
            $this->assertSame('HTTP message', $exception->getErrorResponse()->getHttpMessage());
            $this->assertSame('More information', $exception->getErrorResponse()->getMoreInformation());
            $this->assertSame('Error code', $exception->getErrorResponse()->getErrors()[0]->getErrorCode());
            $this->assertSame('Error description', $exception->getErrorResponse()->getErrors()[0]->getErrorDescription());
            $this->assertSame('Error cause', $exception->getErrorResponse()->getErrors()[0]->getErrorCause());
            $this->assertSame('Error resolution', $exception->getErrorResponse()->getErrors()[0]->getErrorResolution());
        }
    }

    public function testItThrowsExceptionWhenRequestExceptionThrownByGuzzle(): void
    {
        $mock = new MockHandler([
            new RequestException(
                'Error Communicating with Server',
                new Request('GET', '/mailpieces/v2/123456789GB/events')
            )
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock), 'http_errors' => true]);

        $this->expectException(RoyalMailServerError::class);
        $this->expectExceptionMessage('Error Communicating with Server');

        (new Summary($client, 'client-id', 'client-secret'))
            ->setTrackingNumbers('123456789GB');
    }

    public function testItThrowsExceptionWhenTransferExceptionThrownByGuzzle(): void
    {
        $mock = new MockHandler([
            new TransferException('Transfer Exception')
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock), 'http_errors' => true]);

        $this->expectException(RoyalMailServerError::class);
        $this->expectExceptionMessage('Transfer Exception');

        (new Summary($client, 'client-id', 'client-secret'))
            ->setTrackingNumbers('123456789GB');
    }

    public function testItThrowsInvalidCredentialsExceptionWhenGuzzleExceptionsOffAndBadRequestHttpStatus(): void
    {
        $mock = new MockHandler([
            new Response(400, [], \json_encode([
                'httpCode' => 400,
                'httpMessage' => 'HTTP message',
                'moreInformation' => 'More information',
                'errors' => [[
                    'errorCode' => 'Error code',
                    'errorDescription' => 'Error description',
                    'errorCause' => 'Error cause',
                    'errorResolution' => 'Error resolution'
                ]]
            ]))
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock), 'http_errors' => false]);

        try {
            (new Summary($client, 'client-id', 'client-secret'))
                ->setTrackingNumbers('123456789GB');
        } catch (RequestError $exception) {
            $this->assertSame('(400) More information', $exception->getMessage());
            $this->assertSame(400, $exception->getErrorResponse()->getHttpCode());
            $this->assertSame('HTTP message', $exception->getErrorResponse()->getHttpMessage());
            $this->assertSame('More information', $exception->getErrorResponse()->getMoreInformation());
            $this->assertSame('Error code', $exception->getErrorResponse()->getErrors()[0]->getErrorCode());
            $this->assertSame('Error description', $exception->getErrorResponse()->getErrors()[0]->getErrorDescription());
            $this->assertSame('Error cause', $exception->getErrorResponse()->getErrors()[0]->getErrorCause());
            $this->assertSame('Error resolution', $exception->getErrorResponse()->getErrors()[0]->getErrorResolution());
        }
    }

    public function testItThrowsInvalidCredentialsExceptionWhenGuzzleExceptionsOnAndUnauthorisedHttpStatus(): void
    {
        $mock = new MockHandler([
            new Response(401, [], \json_encode([
                'httpCode' => 401,
                'httpMessage' => 'HTTP message',
                'moreInformation' => 'More information',
                'errors' => [[
                    'errorCode' => 'Error code',
                    'errorDescription' => 'Error description',
                    'errorCause' => 'Error cause',
                    'errorResolution' => 'Error resolution'
                ]]
            ]))
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock), 'http_errors' => true]);

        try {
            (new Summary($client, 'client-id', 'client-secret'))
                ->setTrackingNumbers('123456789GB');
        } catch (InvalidCredentials $exception) {
            $this->assertSame('(401) More information', $exception->getMessage());
            $this->assertSame(401, $exception->getErrorResponse()->getHttpCode());
            $this->assertSame('HTTP message', $exception->getErrorResponse()->getHttpMessage());
            $this->assertSame('More information', $exception->getErrorResponse()->getMoreInformation());
            $this->assertSame('Error code', $exception->getErrorResponse()->getErrors()[0]->getErrorCode());
            $this->assertSame('Error description', $exception->getErrorResponse()->getErrors()[0]->getErrorDescription());
            $this->assertSame('Error cause', $exception->getErrorResponse()->getErrors()[0]->getErrorCause());
            $this->assertSame('Error resolution', $exception->getErrorResponse()->getErrors()[0]->getErrorResolution());
        }
    }

    public function testItThrowsInvalidCredentialsExceptionWhenGuzzleExceptionsOffAndUnauthorisedHttpStatus(): void
    {
        $mock = new MockHandler([
            new Response(401, [], \json_encode([
                'httpCode' => 401,
                'httpMessage' => 'HTTP message',
                'moreInformation' => 'More information',
                'errors' => [[
                    'errorCode' => 'Error code',
                    'errorDescription' => 'Error description',
                    'errorCause' => 'Error cause',
                    'errorResolution' => 'Error resolution'
                ]]
            ]))
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock), 'http_errors' => false]);

        try {
            (new Summary($client, 'client-id', 'client-secret'))
                ->setTrackingNumbers('123456789GB');
        } catch (InvalidCredentials $exception) {
            $this->assertSame('(401) More information', $exception->getMessage());
            $this->assertSame(401, $exception->getErrorResponse()->getHttpCode());
            $this->assertSame('HTTP message', $exception->getErrorResponse()->getHttpMessage());
            $this->assertSame('More information', $exception->getErrorResponse()->getMoreInformation());
            $this->assertSame('Error code', $exception->getErrorResponse()->getErrors()[0]->getErrorCode());
            $this->assertSame('Error description', $exception->getErrorResponse()->getErrors()[0]->getErrorDescription());
            $this->assertSame('Error cause', $exception->getErrorResponse()->getErrors()[0]->getErrorCause());
            $this->assertSame('Error resolution', $exception->getErrorResponse()->getErrors()[0]->getErrorResolution());
        }
    }

    public function testItThrowsInvalidCredentialsExceptionWhenGuzzleExceptionsOnAndForbiddenHttpStatus(): void
    {
        $mock = new MockHandler([
            new Response(403, [], \json_encode([
                'httpCode' => 403,
                'httpMessage' => 'HTTP message',
                'moreInformation' => 'More information',
                'errors' => [[
                    'errorCode' => 'Error code',
                    'errorDescription' => 'Error description',
                    'errorCause' => 'Error cause',
                    'errorResolution' => 'Error resolution'
                ]]
            ]))
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock), 'http_errors' => true]);

        try {
            (new Summary($client, 'client-id', 'client-secret'))
                ->setTrackingNumbers('123456789GB');
        } catch (Forbidden $exception) {
            $this->assertSame('(403) More information', $exception->getMessage());
            $this->assertSame(403, $exception->getErrorResponse()->getHttpCode());
            $this->assertSame('HTTP message', $exception->getErrorResponse()->getHttpMessage());
            $this->assertSame('More information', $exception->getErrorResponse()->getMoreInformation());
            $this->assertSame('Error code', $exception->getErrorResponse()->getErrors()[0]->getErrorCode());
            $this->assertSame('Error description', $exception->getErrorResponse()->getErrors()[0]->getErrorDescription());
            $this->assertSame('Error cause', $exception->getErrorResponse()->getErrors()[0]->getErrorCause());
            $this->assertSame('Error resolution', $exception->getErrorResponse()->getErrors()[0]->getErrorResolution());
        }
    }

    public function testItThrowsInvalidCredentialsExceptionWhenGuzzleExceptionsOffAndForbiddenHttpStatus(): void
    {
        $mock = new MockHandler([
            new Response(403, [], \json_encode([
                'httpCode' => 403,
                'httpMessage' => 'HTTP message',
                'moreInformation' => 'More information',
                'errors' => [[
                    'errorCode' => 'Error code',
                    'errorDescription' => 'Error description',
                    'errorCause' => 'Error cause',
                    'errorResolution' => 'Error resolution'
                ]]
            ]))
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock), 'http_errors' => false]);

        try {
            (new Summary($client, 'client-id', 'client-secret'))
                ->setTrackingNumbers('123456789GB');
        } catch (Forbidden $exception) {
            $this->assertSame('(403) More information', $exception->getMessage());
            $this->assertSame(403, $exception->getErrorResponse()->getHttpCode());
            $this->assertSame('HTTP message', $exception->getErrorResponse()->getHttpMessage());
            $this->assertSame('More information', $exception->getErrorResponse()->getMoreInformation());
            $this->assertSame('Error code', $exception->getErrorResponse()->getErrors()[0]->getErrorCode());
            $this->assertSame('Error description', $exception->getErrorResponse()->getErrors()[0]->getErrorDescription());
            $this->assertSame('Error cause', $exception->getErrorResponse()->getErrors()[0]->getErrorCause());
            $this->assertSame('Error resolution', $exception->getErrorResponse()->getErrors()[0]->getErrorResolution());
        }
    }

    public function testItThrowsInvalidCredentialsExceptionWhenGuzzleExceptionsOnAndTooManyRequestsHttpStatus(): void
    {
        $mock = new MockHandler([
            new Response(429, [], \json_encode([
                'httpCode' => 429,
                'httpMessage' => 'HTTP message',
                'moreInformation' => 'More information',
                'errors' => [[
                    'errorCode' => 'Error code',
                    'errorDescription' => 'Error description',
                    'errorCause' => 'Error cause',
                    'errorResolution' => 'Error resolution'
                ]]
            ]))
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock), 'http_errors' => true]);

        try {
            (new Summary($client, 'client-id', 'client-secret'))
                ->setTrackingNumbers('123456789GB');
        } catch (RequestLimitExceeded $exception) {
            $this->assertSame('(429) More information', $exception->getMessage());
            $this->assertSame(429, $exception->getErrorResponse()->getHttpCode());
            $this->assertSame('HTTP message', $exception->getErrorResponse()->getHttpMessage());
            $this->assertSame('More information', $exception->getErrorResponse()->getMoreInformation());
            $this->assertSame('Error code', $exception->getErrorResponse()->getErrors()[0]->getErrorCode());
            $this->assertSame('Error description', $exception->getErrorResponse()->getErrors()[0]->getErrorDescription());
            $this->assertSame('Error cause', $exception->getErrorResponse()->getErrors()[0]->getErrorCause());
            $this->assertSame('Error resolution', $exception->getErrorResponse()->getErrors()[0]->getErrorResolution());
        }
    }

    public function testItThrowsInvalidCredentialsExceptionWhenGuzzleExceptionsOffAndTooManyRequestsHttpStatus(): void
    {
        $mock = new MockHandler([
            new Response(429, [], \json_encode([
                'httpCode' => 429,
                'httpMessage' => 'HTTP message',
                'moreInformation' => 'More information',
                'errors' => [[
                    'errorCode' => 'Error code',
                    'errorDescription' => 'Error description',
                    'errorCause' => 'Error cause',
                    'errorResolution' => 'Error resolution'
                ]]
            ]))
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock), 'http_errors' => false]);

        try {
            (new Summary($client, 'client-id', 'client-secret'))
                ->setTrackingNumbers('123456789GB');
        } catch (RequestLimitExceeded $exception) {
            $this->assertSame('(429) More information', $exception->getMessage());
            $this->assertSame(429, $exception->getErrorResponse()->getHttpCode());
            $this->assertSame('HTTP message', $exception->getErrorResponse()->getHttpMessage());
            $this->assertSame('More information', $exception->getErrorResponse()->getMoreInformation());
            $this->assertSame('Error code', $exception->getErrorResponse()->getErrors()[0]->getErrorCode());
            $this->assertSame('Error description', $exception->getErrorResponse()->getErrors()[0]->getErrorDescription());
            $this->assertSame('Error cause', $exception->getErrorResponse()->getErrors()[0]->getErrorCause());
            $this->assertSame('Error resolution', $exception->getErrorResponse()->getErrors()[0]->getErrorResolution());
        }
    }

    public function testItThrowsInvalidCredentialsExceptionWhenGuzzleExceptionsOnAndServerErrorHttpStatus(): void
    {
        $mock = new MockHandler([
            new Response(500, [], \json_encode([
                'httpCode' => 500,
                'httpMessage' => 'HTTP message',
                'moreInformation' => 'More information',
                'errors' => [[
                    'errorCode' => 'Error code',
                    'errorDescription' => 'Error description',
                    'errorCause' => 'Error cause',
                    'errorResolution' => 'Error resolution'
                ]]
            ]))
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock), 'http_errors' => true]);

        try {
            (new Summary($client, 'client-id', 'client-secret'))
                ->setTrackingNumbers('123456789GB');
        } catch (RoyalMailServerError $exception) {
            $this->assertSame('(500) More information', $exception->getMessage());
            $this->assertSame(500, $exception->getErrorResponse()->getHttpCode());
            $this->assertSame('HTTP message', $exception->getErrorResponse()->getHttpMessage());
            $this->assertSame('More information', $exception->getErrorResponse()->getMoreInformation());
            $this->assertSame('Error code', $exception->getErrorResponse()->getErrors()[0]->getErrorCode());
            $this->assertSame('Error description', $exception->getErrorResponse()->getErrors()[0]->getErrorDescription());
            $this->assertSame('Error cause', $exception->getErrorResponse()->getErrors()[0]->getErrorCause());
            $this->assertSame('Error resolution', $exception->getErrorResponse()->getErrors()[0]->getErrorResolution());
        }
    }

    public function testItThrowsInvalidCredentialsExceptionWhenGuzzleExceptionsOffAndServerErrorHttpStatus(): void
    {
        $mock = new MockHandler([
            new Response(500, [], \json_encode([
                'httpCode' => 500,
                'httpMessage' => 'HTTP message',
                'moreInformation' => 'More information',
                'errors' => [[
                    'errorCode' => 'Error code',
                    'errorDescription' => 'Error description',
                    'errorCause' => 'Error cause',
                    'errorResolution' => 'Error resolution'
                ]]
            ]))
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock), 'http_errors' => false]);

        try {
            (new Summary($client, 'client-id', 'client-secret'))
                ->setTrackingNumbers('123456789GB');
        } catch (RoyalMailServerError $exception) {
            $this->assertSame('(500) More information', $exception->getMessage());
            $this->assertSame(500, $exception->getErrorResponse()->getHttpCode());
            $this->assertSame('HTTP message', $exception->getErrorResponse()->getHttpMessage());
            $this->assertSame('More information', $exception->getErrorResponse()->getMoreInformation());
            $this->assertSame('Error code', $exception->getErrorResponse()->getErrors()[0]->getErrorCode());
            $this->assertSame('Error description', $exception->getErrorResponse()->getErrors()[0]->getErrorDescription());
            $this->assertSame('Error cause', $exception->getErrorResponse()->getErrors()[0]->getErrorCause());
            $this->assertSame('Error resolution', $exception->getErrorResponse()->getErrors()[0]->getErrorResolution());
        }
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
                "errorDescription": "Barcode reference mailPieceId isn not recognised",
                "errorCause": "A mail item with that barcode cannot be located",
                "errorResolution": "Check barcode and resubmit"
              }
            }
          ]
        }';
    }
}
