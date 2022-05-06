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
            ->setTrackingNumber('123456789GB')->get();

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

    public function testItReturnsJsonEncodedTrackingData(): void
    {
        $mock = new MockHandler([new Response(200, [], $this->mockResponse())]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        $response = (new Signature($client, 'client-id', 'client-secret'))
            ->setTrackingNumber('123456789GB');

        $this->assertJsonStringEqualsJsonString('{
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
        }', $response->asJson());
    }

    public function testItRemovesNonAlphanumericCharactersFromTrackingNumber(): void
    {
        $mock = new MockHandler([new Response(200, [], $this->mockResponse())]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        $response = (new Signature($client, 'client-id', 'client-secret'))
            ->setTrackingNumber('!"£$%^&*()12345             6789GB)(*&^%$!')->get();

        $this->assertSame('/mailpieces/v2/123456789GB/signature', $mock->getLastRequest()->getUri()->getPath());

        $this->assertSame('090367574000000FE1E1B', $response->getMailPieceId());
    }

    public function testItThrowsResponseErrorWhenInvalidJsonReturnedFromApi(): void
    {
        $mock = new MockHandler([new Response(200, [], 'NOT JSON')]);
        $client = new Client(['handler' => HandlerStack::create($mock), 'http_errors' => true]);

        $this->expectException(ResponseError::class);
        $this->expectExceptionMessage('(200) NOT JSON');

        (new Signature($client, 'client-id', 'client-secret'))
            ->setTrackingNumber('123456789GB');
    }

    public function testItThrowsResponseErrorWhenInvalidJsonReturnedFromApiAndHttpErrorsSetToFalse(): void
    {
        $mock = new MockHandler([new Response(200, [], 'NOT JSON')]);
        $client = new Client(['handler' => HandlerStack::create($mock), 'http_errors' => false]);

        $this->expectException(ResponseError::class);
        $this->expectExceptionMessage('(200) NOT JSON');

        (new Signature($client, 'client-id', 'client-secret'))
            ->setTrackingNumber('123456789GB');
    }

    public function testItThrowsResponseErrorWhenJsonReturnedFromApiDoesNotContainExpectedInformation(): void
    {
        $mock = new MockHandler([new Response(200, [], '{"something":"unexpected"}')]);
        $client = new Client(['handler' => HandlerStack::create($mock), 'http_errors' => true]);

        $this->expectException(ResponseError::class);
        $this->expectExceptionMessage('(200) {"something":"unexpected"}');

        (new Signature($client, 'client-id', 'client-secret'))
            ->setTrackingNumber('123456789GB');
    }

    public function testItThrowsResponseErrorWhenJsonReturnedFromApiDoesNotContainExpectedInformationWhenHttpErrorsSetToFalse(): void
    {
        $mock = new MockHandler([new Response(200, [], '{"something":"unexpected"}')]);
        $client = new Client(['handler' => HandlerStack::create($mock), 'http_errors' => true]);

        $this->expectException(ResponseError::class);
        $this->expectExceptionMessage('(200) {"something":"unexpected"}');

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
        $client = new Client(['handler' => HandlerStack::create($mock), 'http_errors' => true]);

        $this->expectException(RoyalMailServerError::class);
        $this->expectExceptionMessage('Error Communicating with Server');

        (new Signature($client, 'client-id', 'client-secret'))
            ->setTrackingNumber('123456789GB');
    }

    public function testItThrowsExceptionWhenTransferExceptionThrownByGuzzle(): void
    {
        $mock = new MockHandler([
            new TransferException('Transfer Exception')
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock), 'http_errors' => true]);

        $this->expectException(RoyalMailServerError::class);
        $this->expectExceptionMessage('Transfer Exception');

        (new Signature($client, 'client-id', 'client-secret'))
            ->setTrackingNumber('123456789GB');
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
            (new Signature($client, 'client-id', 'client-secret'))
                ->setTrackingNumber('123456789GB');
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
            (new Signature($client, 'client-id', 'client-secret'))
                ->setTrackingNumber('123456789GB');
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
            (new Signature($client, 'client-id', 'client-secret'))
                ->setTrackingNumber('123456789GB');
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
            (new Signature($client, 'client-id', 'client-secret'))
                ->setTrackingNumber('123456789GB');
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
            (new Signature($client, 'client-id', 'client-secret'))
                ->setTrackingNumber('123456789GB');
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
            (new Signature($client, 'client-id', 'client-secret'))
                ->setTrackingNumber('123456789GB');
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
            (new Signature($client, 'client-id', 'client-secret'))
                ->setTrackingNumber('123456789GB');
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
            (new Signature($client, 'client-id', 'client-secret'))
                ->setTrackingNumber('123456789GB');
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
            (new Signature($client, 'client-id', 'client-secret'))
                ->setTrackingNumber('123456789GB');
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
            (new Signature($client, 'client-id', 'client-secret'))
                ->setTrackingNumber('123456789GB');
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
