[![Contributor Covenant](https://img.shields.io/badge/Contributor%20Covenant-v2.0%20adopted-ff69b4.svg)](code-of-conduct.md)

# Royal Mail Tracking for PHP

Please raise an issue in the GitHub repository if you are able, if not please feel free to [contact me](https://www.elliotjreed.com/contact).

Pull requests for bug fixes or potential changes are welcome!

Towards the bottom of this readme is a list of some of the event codes and their rough definition.

## Usage

PHP 8.2 or above is required. For PHP 8.1 use version 4.2.0.

To install the package via [Composer](https://getcomposer.org/download/):

```bash
composer require elliotjreed/royal-mail-tracking
```

Three means of fetching tracking data are available:
 - `Events`: for detailed information and full history of a single tracking number;
 - `Signature`: the signature data (including image data - either base64-encoded PNG, or an SVG);
 - `Summary`: for the most recent event for multiple tracking numbers.

Details for each are outlined below, with examples included.

Information about error handling is provided below the `Events`, `Signature`, and `Summary` information
(and is worth reading as `Summary` errors are handled differently to `Events` and `Summary` errors).

### Instantiation / Setup

```php
$tracking = (new \ElliotJReed\RoyalMail\Tracking\Events(
    new \GuzzleHttp\Client(),
    'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
    '12345678901234567890123456789012345678901234567890',
    true, // Optional, when true (default: true) exceptions will be thrown for tracking errors
    true, // Optional, when true (default: true) exceptions will be thrown for technical (eg. 500 HTTP response) errors
    'https://api.royalmail.net/mailpieces/v2' // Optional, when set the default API endpoint can be overridden (default: 'https://api.royalmail.net/mailpieces/v2')
));
```

```php
$signature = (new \ElliotJReed\RoyalMail\Tracking\Signature(
    new \GuzzleHttp\Client(),
    'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
    '12345678901234567890123456789012345678901234567890',
    true, // Optional, when true (default: true) exceptions will be thrown for tracking errors
    true, // Optional, when true (default: true) exceptions will be thrown for technical (eg. 500 HTTP response) errors
    'https://api.royalmail.net/mailpieces/v2' // Optional, when set the default API endpoint can be overridden (default: 'https://api.royalmail.net/mailpieces/v2')
));
```

```php
$summary = (new \ElliotJReed\RoyalMail\Tracking\Summary(
    new \GuzzleHttp\Client(),
    'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
    '12345678901234567890123456789012345678901234567890',
    true, // Optional, when true (default: true) exceptions will be thrown for tracking errors
    true, // Optional, when true (default: true) exceptions will be thrown for technical (eg. 500 HTTP response) errors
    'https://api.royalmail.net/mailpieces/v2' // Optional, when set the default API endpoint can be overridden (default: 'https://api.royalmail.net/mailpieces/v2')
));
```

### Events

The behaviour of the events operation is to provide a history of tracks for a single mail item.

Returns the summary, signature metadata, estimated delivery window and events for a supplied tracking number.

```php
$tracking = (new \ElliotJReed\RoyalMail\Tracking\Events(
    new \GuzzleHttp\Client(),
    'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
    '12345678901234567890123456789012345678901234567890'
));

$mailPieces = $tracking->setTrackingNumber('AB1234567890GB')->getResponse()->getMailPieces();

$mailPieces->getMailPieceId(); // 090367574000000FE1E1B
$mailPieces->getCarrierShortName(); // RM
$mailPieces->getCarrierFullName(); // Royal Mail Group Ltd

$summary = $mailPieces->getSummary();
$summary->getUniqueItemId(); // 090367574000000FE1E1B
$summary->getOneDBarcode(); // FQ087430672GB
$summary->getProductId(); // SD2
$summary->getProductName(); // Special Delivery Guaranteed
$summary->getProductDescription(); // Our guaranteed next day service with tracking and a signature on delivery
$summary->getProductCategory(); // NON-INTERNATIONAL
$summary->getDestinationCountryCode(); // GBR
$summary->getDestinationCountryName(); // United Kingdom of Great Britain and Northern Ireland
$summary->getOriginCountryCode(); // GBR
$summary->getOriginCountryName(); // United Kingdom of Great Britain and Northern Ireland
$summary->getLastEventCode(); // EVNMI
$summary->getLastEventName(); // Forwarded - Mis-sort
$summary->getLastEventDateTime(); // new DateTimeImmutable('2016-10-20T10:04:00+01:00')
$summary->getLastEventLocationName(); // Stafford DO
$summary->getStatusDescription(); // It is being redirected
$summary->getStatusCategory(); // IN TRANSIT
$summary->getStatusHelpText(); // The item is in transit
$summary->getSummaryLine(); // Item FQ087430672GB was forwarded to the Delivery Office on 2016-10-20.

$internationalPostalProvider = $summary->getInternationalPostalProvider();
$internationalPostalProvider->getUrl(); // https://www.royalmail.com/track-your-item
$internationalPostalProvider->getTitle(); // Royal Mail Group Ltd
$internationalPostalProvider->getDescription(); // Royal Mail Group Ltd

$signature = $mailPieces->getSignature();
$signature->getRecipientName(); // Elliot
$signature->getSignatureDateTime(); // new DateTimeImmutable('2016-10-20T10:04:00+01:00')
$signature->getImageId(); // 001234

$estimatedDelivery = $mailPieces->getEstimatedDelivery();
$estimatedDelivery->getDate(); // new DateTimeImmutable('2017-02-20T00:00:00+00:00')
$estimatedDelivery->getStartOfEstimatedWindow(); // new DateTimeImmutable('2017-02-20T08:00:00+01:00')
$estimatedDelivery->getEndOfEstimatedWindow(); // new DateTimeImmutable('2017-02-20T11:00:00+01:00')

$events = $mailPieces->getEvents();
$event = $events[0];
$event->getEventCode(); // EVNMI
$event->getEventName(); // Forwarded - Mis-sort
$event->getEventDateTime(); // new DateTimeImmutable('2016-10-20T10:04:00+01:00')
$event->getLocationName(); // Stafford DO

$linkSummary = $mailPieces->getLinks()->getSummary();
$linkSummary->getHref(); // /mailpieces/v2/summary?mailPieceId=090367574000000FE1E1B
$linkSummary->getTitle(); // Summary
$linkSummary->getDescription(); // Get summary

$linkSignature = $mailPieces->getLinks()->getSignature();
$linkSignature->getHref(); // /mailpieces/v2/090367574000000FE1E1B/signature
$linkSignature->getTitle(); // Signature
$linkSignature->getDescription(); // Get signature

$linkRedelivery = $mailPieces->getLinks()->getRedelivery();
$linkRedelivery->getHref(); // /personal/receiving-mail/redelivery
$linkRedelivery->getTitle(); // Redelivery
$linkRedelivery->getDescription(); // Book a redelivery
```

#### JSON output

```php
$tracking = (new \ElliotJReed\RoyalMail\Tracking\Events(
    new \GuzzleHttp\Client(),
    'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
    '12345678901234567890123456789012345678901234567890'
));

echo $tracking->setTrackingNumber('AB1234567890GB')->asJson();
```

Would output the Royal Mail response as JSON:

```json
{
    "errors": [],
    "httpCode": null,
    "httpMessage": null,
    "mailPieces": {
        "carrierFullName": "Royal Mail Group Ltd",
        "carrierShortName": "RM",
        "estimatedDelivery": {
            "date": "2017-02-20T00:00:00+00:00",
            "endOfEstimatedWindow": "2017-02-20T11:00:00+01:00",
            "startOfEstimatedWindow": "2017-02-20T08:00:00+01:00"
        },
        "events": [
            {
                "eventCode": "EVNMI",
                "eventDateTime": "2016-10-20T10:04:00+01:00",
                "eventName": "Forwarded - Mis-sort",
                "locationName": "Stafford DO"
            }
        ],
        "links": {
            "redelivery": {
                "description": "Book a redelivery",
                "href": "/personal/receiving-mail/redelivery",
                "title": "Redelivery"
            },
            "signature": {
                "description": "Get signature",
                "href": "/mailpieces/v2/090367574000000FE1E1B/signature",
                "title": "Signature"
            },
            "summary": {
                "description": "Get summary",
                "href": "/mailpieces/v2/summary?mailPieceId=090367574000000FE1E1B",
                "title": "Summary"
            }
        },
        "mailPieceId": "090367574000000FE1E1B",
        "signature": {
            "imageId": "001234",
            "recipientName": "Elliot",
            "signatureDateTime": "2016-10-20T10:04:00+01:00"
        },
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
    },
    "moreInformation": null
}
```

### Signature

```php
$tracking = (new \ElliotJReed\RoyalMail\Tracking\Signature(
    new \GuzzleHttp\Client(),
    'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
    '12345678901234567890123456789012345678901234567890'
));

$mailPieces = $tracking->setTrackingNumber('AB1234567890GB')->getResponse()->getMailPieces();

$mailPieces->getMailPieceId(); // 090367574000000FE1E1B
$mailPieces->getCarrierShortName(); // RM
$mailPieces->getCarrierFullName(); // Royal Mail Group Ltd

$signature = $mailPieces->getSignature();
$signature->getRecipientName(); // Elliot
$signature->getSignatureDateTime(); // new DateTimeImmutable('2017-03-30T16:15:00+01:00')
$signature->getImageId(); // 001234
$signature->getOneDBarcode(); // FQ087430672GB
$signature->getHeight(); // 530
$signature->getWidth(); // 660
$signature->getUniqueItemId(); // 090367574000000FE1E1B
$signature->getImageFormat(); // image/svg+xml
$signature->getImage(); // <svg></svg>

$events = $mailPieces->getLinks()->getEvents();
$events->getHref(); // /mailpieces/v2/FQ087430672GB/events
$events->getTitle(); // Events
$events->getDescription(); // Get events

$linkSummary = $mailPieces->getLinks()->getSummary();
$linkSummary->getHref(); // /mailpieces/v2/summary?mailPieceId=090367574000000FE1E1B
$linkSummary->getTitle(); // Summary
$linkSummary->getDescription(); // Get summary
```

#### JSON output

```php
$tracking = (new \ElliotJReed\RoyalMail\Tracking\Signature(
    new \GuzzleHttp\Client(),
    'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
    '12345678901234567890123456789012345678901234567890'
));

echo $tracking->setTrackingNumber('AB1234567890GB')->asJson();
```

Would output the Royal Mail tracking response as JSON:

```json
{
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
}
```

### Summary

The behaviour of the summary operation is to allow customers to obtain the latest tracking data for a mail item.

This operation returns the summary of one or more tracking numbers provided in the request.

This operation only allows a maximum of 30 tracking numbers to be provided in the `->setTrackingNumbers()` method
(eg. `->setTrackingNumbers(['AB0123456789GB', 'CD0123456789GB'])`).

```php
$summary = (new \ElliotJReed\RoyalMail\Tracking\Summary(
    new \GuzzleHttp\Client(),
    'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
    '12345678901234567890123456789012345678901234567890'
));

$mailPieces = $summary->setTrackingNumbers('AB1234567890GB', 'CD1234567890GB')->getResponse()->getMailPieces();

$firstMailPieces = $mailPieces[0];

$firstMailPieces->getMailPieceId(); // 090367574000000FE1E1B
$firstMailPieces->getCarrierShortName(); // RM
$firstMailPieces->getCarrierFullName(); // Royal Mail Group Ltd

$summary = $firstMailPieces->getSummary();
$summary->getUniqueItemId(); // 090367574000000FE1E1B
$summary->getOneDBarcode(); // FQ087430672GB
$summary->getProductId(); // SD2
$summary->getProductName(); // Special Delivery Guaranteed
$summary->getProductDescription(); // Our guaranteed next day service with tracking and a signature on delivery
$summary->getProductCategory(); // NON-INTERNATIONAL
$summary->getDestinationCountryCode(); // GBR
$summary->getDestinationCountryName(); // United Kingdom of Great Britain and Northern Ireland
$summary->getOriginCountryCode(); // GBR
$summary->getOriginCountryName(); // United Kingdom of Great Britain and Northern Ireland
$summary->getLastEventCode(); // EVNMI
$summary->getLastEventName(); // Forwarded - Mis-sort
$summary->getLastEventDateTime(); // new DateTimeImmutable('2016-10-20T10:04:00+01:00')
$summary->getLastEventLocationName(); // Stafford DO
$summary->getStatusDescription(); // It is being redirected
$summary->getStatusCategory(); // IN TRANSIT
$summary->getStatusHelpText(); // The item is in transit
$summary->getSummaryLine(); // Item FQ087430672GB was forwarded to the Delivery Office on 2016-10-20.

$internationalPostalProvider = $summary->getInternationalPostalProvider();
$internationalPostalProvider->getUrl(); // https://www.royalmail.com/track-your-item
$internationalPostalProvider->getTitle(); // Royal Mail Group Ltd
$internationalPostalProvider->getDescription(); // Royal Mail Group Ltd

$events = $firstMailPieces->getLinks()->getEvents();
$events->getHref(); // /mailpieces/v2/FQ087430672GB/events
$events->getTitle(); // Events
$events->getDescription(); // Get events

$error = $firstMailPieces->getError();
$error->getErrorCode(); // E1142
$error->getErrorDescription(); // Barcode reference $mailPieceId isn't recognised
$error->getErrorCause(); // A mail item with that barcode cannot be located
$error->getErrorResolution(); // Check barcode and resubmit
```

#### JSON output

```php
$summary = (new \ElliotJReed\RoyalMail\Tracking\Summary(
    new \GuzzleHttp\Client(),
    'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
    '12345678901234567890123456789012345678901234567890'
));

echo $summary->asJson();
```

Would output the Royal Mail response as JSON:

```json
{
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
}
```

### Errors

Errors are thrown as instances of the `\ElliotJReed\RoyalMail\Tracking\Exception\RoyalMailError` exception.

Further error information can be accessed through the `->getResponse()` method on the thrown exception when available.

Potential causes and resolutions can be accessed through `->getErrorResponse()->getErrors()` when available.

#### Fatal Exceptions / Errors

When the response from Royal Mail's API is one which falls outside of their expected responses a
`\ElliotJReed\RoyalMail\Tracking\Exception\RoyalMailResponseError` is thrown.

This would only be thrown if there is a serious outage at Royal Mail's API (for example, a DNS failure).

#### Technical Exceptions / Errors

Exceptions are thrown for `Events`, `Signature`, and `Summary` requests for application-specific / technical errors by default (eg. invalid API credentials),
as instances of the `\ElliotJReed\RoyalMail\Tracking\Exception\RoyalMailTechnicalError` exception.

```php
try {
    $tracking = (new \ElliotJReed\RoyalMail\Tracking\Events(
        new \GuzzleHttp\Client(),
        'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
        '12345678901234567890123456789012345678901234567890'
    ));
    $tracking->setTrackingNumber('AB1234567890GB');
} catch (\ElliotJReed\RoyalMail\Tracking\Exception\RoyalMailTechnicalError $exception) {
    echo $exception->getMessage();
    echo $exception->getResponse()?->getHttpCode();
    echo $exception->getResponse()?->getHttpMessage();
    echo $exception->getResponse()?->getMoreInformation();

    echo $exception->getResponse()?->getErrors()[0]->getErrorCode();
    echo $exception->getResponse()?->getErrors()[0]->getErrorDescription();
    echo $exception->getResponse()?->getErrors()[0]->getErrorCause();
    echo $exception->getResponse()?->getErrors()[0]->getErrorResolution();
}
```

##### Disabling Exceptions

Exceptions for application-specific can be disabled so that errors are returned in the `Response` object (via `->getResponse()`).

This can be useful for example when parsing the result and handling errors in, say, Javascript on the frontend.

To disable exceptions being thrown set `$throwExceptionOnTechnicalError` to `false` in the constructor:

```php
$tracking = (new \ElliotJReed\RoyalMail\Tracking\Events(
    new \GuzzleHttp\Client(),
    'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
    '12345678901234567890123456789012345678901234567890',
    true, // $throwExceptionOnTrackingError
    false // $throwExceptionOnTechnicalError
));
$response = $tracking->setTrackingNumber('AB1234567890GB')->getResponse();

echo $response->getMoreInformation();
echo $response->getErrors()[0]->getErrorCode();
echo $response->getErrors()[0]->getErrorCause();
echo $response->getErrors()[0]->getErrorDescription();
echo $response->getErrors()[0]->getErrorResolution();
```

```php
$tracking = (new \ElliotJReed\RoyalMail\Tracking\Signature(
    new \GuzzleHttp\Client(),
    'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
    '12345678901234567890123456789012345678901234567890',
    true, // $throwExceptionOnTrackingError
    false // $throwExceptionOnTechnicalError
));
$response = $tracking->setTrackingNumber('AB1234567890GB')->getResponse();

echo $response->getMoreInformation();
echo $response->getErrors()[0]->getErrorCode();
echo $response->getErrors()[0]->getErrorCause();
echo $response->getErrors()[0]->getErrorDescription();
echo $response->getErrors()[0]->getErrorResolution();
```

```php
$tracking = (new \ElliotJReed\RoyalMail\Tracking\Summary(
    new \GuzzleHttp\Client(),
    'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
    '12345678901234567890123456789012345678901234567890',
    false // $throwExceptionOnTechnicalError
));
$response = $tracking->setTrackingNumbers('AB1234567890GB')->getResponse();

echo $response->getMoreInformation();
echo $response->getErrors()[0]->getErrorCode();
echo $response->getErrors()[0]->getErrorCause();
echo $response->getErrors()[0]->getErrorDescription();
echo $response->getErrors()[0]->getErrorResolution();
```

#### Business Exceptions / Errors

Exceptions are thrown for `Events` and `Signature` requests for business/tracking number-specific errors by default (eg. invalid tracking number),
as instances of the `\ElliotJReed\RoyalMail\Tracking\Exception\RoyalMailTrackingError` exception.

##### Events and Signature

```php
try {
    $tracking = (new \ElliotJReed\RoyalMail\Tracking\Events(
        new \GuzzleHttp\Client(),
        'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
        '12345678901234567890123456789012345678901234567890'
    ));

    $tracking->setTrackingNumber('AB1234567890GB');
} catch (\ElliotJReed\RoyalMail\Tracking\Exception\RoyalMailTrackingError $exception) {
    echo $exception->getMessage();
    echo $exception->getResponse()?->getHttpCode();
    echo $exception->getResponse()?->getHttpMessage();
    echo $exception->getResponse()?->getMoreInformation();

    echo $exception->getMessage();
    echo $exception->getResponse()?->getHttpCode();
    echo $exception->getResponse()?->getHttpMessage();
    echo $exception->getResponse()?->getMoreInformation();

    echo $exception->getResponse()?->getErrors()[0]->getErrorCode();
    echo $exception->getResponse()?->getErrors()[0]->getErrorDescription();
    echo $exception->getResponse()?->getErrors()[0]->getErrorCause();
    echo $exception->getResponse()?->getErrors()[0]->getErrorResolution();
}
```

##### Summary

Errors for business/tracking number-specific `Summary` request errors are returned in the `MailPiece` object rather than thrown as exceptions
(as `Summary` will return information for multiple tracking numbers).

Because of this, when an error is encountered an exception will only be thrown when it is an application-specific error,
and *not* when the error is related to a particular tracking number. Instead, the error is returned via the `Mailpieces` object for specific
the tracking number.

```php
$tracking = (new \ElliotJReed\RoyalMail\Tracking\Summary(
    new \GuzzleHttp\Client(),
    'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
    '12345678901234567890123456789012345678901234567890'
));

$response = $tracking->setTrackingNumbers('AB1234567890GB')->getResponse();

echo $response->getMailPieces()[0]->getError()->getMoreInformation();
echo $response->getMailPieces()[0]->getError()->getErrorCode();
echo $response->getMailPieces()[0]->getError()->getErrorCause();
echo $response->getMailPieces()[0]->getError()->getErrorDescription();
echo $response->getMailPieces()[0]->getError()->getErrorResolution();
```

#### Disabling Exceptions

Exceptions for business/tracking number-specific for the `Events` and `Signature` can be disabled so that errors are returned
in the `Response` object (via `->getResponse()`).

To disable exceptions being thrown set `$throwExceptionOnTrackingError` to `false` in the constructor:

```php
$tracking = (new \ElliotJReed\RoyalMail\Tracking\Events(
    new \GuzzleHttp\Client(),
    'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
    '12345678901234567890123456789012345678901234567890',
    false // $throwExceptionOnTrackingError
));
$response = $tracking->setTrackingNumber('AB1234567890GB')->getResponse();

echo $response->getMoreInformation();
echo $response->getErrors()[0]->getErrorCode();
echo $response->getErrors()[0]->getErrorCause();
echo $response->getErrors()[0]->getErrorDescription();
echo $response->getErrors()[0]->getErrorResolution();
```

```php
$tracking = (new \ElliotJReed\RoyalMail\Tracking\Signature(
    new \GuzzleHttp\Client(),
    'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
    '12345678901234567890123456789012345678901234567890',
    false // $throwExceptionOnTrackingError
));
$response = $tracking->setTrackingNumber('AB1234567890GB')->getResponse();

echo $response->getMoreInformation();
echo $response->getErrors()[0]->getErrorCode();
echo $response->getErrors()[0]->getErrorCause();
echo $response->getErrors()[0]->getErrorDescription();
echo $response->getErrors()[0]->getErrorResolution();
```

#### Summary errors

The `Summary` call returns information for multiple tracking numbers, some of which may contain errors.

```php
$summary = (new \ElliotJReed\RoyalMail\Tracking\Summary(
    new \GuzzleHttp\Client(),
    'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
    '12345678901234567890123456789012345678901234567890'
));

$summary->setTrackingNumbers('AB1234567890GB', 'CD1234567890GB');

foreach ($summary->get() as $mailPiece) {
    if ($mailPiece->getError() !== null) {
        echo $responseError->getErrorCode() . \PHP_EOL;
        echo $responseError->getErrorDescription() . \PHP_EOL;
        echo $responseError->getErrorCause() . \PHP_EOL;
        echo $responseError->getErrorResolution() . \PHP_EOL;
    }
}
```

### Using in Symfony

To use this library in Symfony, add the following to the `services.yaml` to have the `Events`, `Signature`, and `Summary` available for autowiring:

```yaml
  guzzle.client.royal_mail_tracking:
    class: GuzzleHttp\Client
    arguments:
      - {
          timeout: 10
        }

  ElliotJReed\RoyalMail\Tracking\Events:
    class: ElliotJReed\RoyalMail\Tracking\Events
    arguments:
      $httpClient: '@guzzle.client.royal_mail_tracking'
      $royalMailClientId: '%env(string:ROYAL_MAIL_API_CLIENT_ID)%'
      $royalMailClientSecret: '%env(string:ROYAL_MAIL_API_CLIENT_SECRET)%'
      $throwExceptionOnTrackingError: true
      $throwExceptionOnTechnicalError: true

  ElliotJReed\RoyalMail\Tracking\Signature:
    class: ElliotJReed\RoyalMail\Tracking\Signature
    arguments:
      $httpClient: '@guzzle.client.royal_mail_tracking'
      $royalMailClientId: '%env(string:ROYAL_MAIL_API_CLIENT_ID)%'
      $royalMailClientSecret: '%env(string:ROYAL_MAIL_API_CLIENT_SECRET)%'
      $throwExceptionOnTrackingError: true
      $throwExceptionOnTechnicalError: true

  ElliotJReed\RoyalMail\Tracking\Summary:
    class: ElliotJReed\RoyalMail\Tracking\Summary
    arguments:
      $httpClient: '@guzzle.client.royal_mail_tracking'
      $royalMailClientId: '%env(string:ROYAL_MAIL_API_CLIENT_ID)%'
      $royalMailClientSecret: '%env(string:ROYAL_MAIL_API_CLIENT_SECRET)%'
      $throwExceptionOnTechnicalError: true
```

## Event codes

EVAIP: Sender despatching item.

EVAIE: Sender despatching item.

ASRXS: As requested, if we can't deliver your item, we'll arrange a Redelivery on [X]. If no one's in again, we'll deliver your item to your chosen Safeplace. We'll let you know when it's delivered.

EVAIP: The sender has advised they've despatched your item to us.

EVBAH: We have your item at National Parcel Hub and it's on its way.

EVDAC: We have your item at [X] MC and it's on its way.

EVDAV: We have your item at [X] MC and it's on its way.

EVGMI: Sorry, your item went to [X] DO in error, so we re-routed it immediately. More information will be available as it travels through our network.

EVGPD: Your Item was received by [X] DO on [X] and is now due for delivery today.

EVIAV: Your item has reached [X] MC and will now be sent to your local delivery office.

EVIMC: Your item has reached [X] MC and will now be sent to your local delivery office.

EVIPI: Sorry, your item went to [X] MC in error, so we re-routed it immediately. More information will be available as it travels through our network.

EVKAA: Sorry, we were unable to deliver this item on [X] as the address was inaccessible. We will attempt to deliver your item again on the next working day.

EVKAI: Sorry, we were unable to deliver this item on [X] as it was not possible to identify the delivery address. It will now be returned to the sender.

EVKDN: We delivered your item to your neighbour [X] at [X] on [X].

EVKGA: Sorry, we were unable to deliver this item on [X] as the recipient is no longer at that address. We're returning the item to the sender.

EVKLC: Your item has been delivered to your nominated collection point on [X] and it's now ready for you to collect.

EVKNA: Sorry, we tried to deliver your parcel on [X] but there didn't seem to be anyone in. Please choose an option below.

EVKOP: Your item was delivered on [X].

EVKRF: Sorry, we were unable to deliver this item on [X] as the recipient refused to accept it. It will now be returned to the sender.

EVKSF: Your item was delivered to your Safeplace on [X].

EVKSP: Your item was delivered on [X].

EVKSU: Sorry, we've been unable to deliver your item to your nominated Safeplace today, [X]. Please choose an option below.

EVNAA: Sorry, we were unable to deliver this item on [X] as the address was inaccessible. We will attempt to deliver your item again on the next working day.

EVNAR: Due for Redelivery the Next Working Day.

EVNDA: Sorry, we tried to deliver your parcel on [X] but there didn't seem to be anyone in. Please choose an option below.

EVNDN: Sorry, we were unable to deliver this item on [X]. We'll attempt to deliver it on the next working day. See below for more information.

EVNGA: Sorry, we were unable to deliver this item on [X] as the recipient is no longer at that address. We're returning the item to the sender.

EVNKS: As requested, we're holding this item at [X] DO as part of our Keepsafe service. See below for more information.

EVNMI: Sorry, your item went to [X] DO in error, so we re-routed it immediately. More information will be available as it travels through our network.

EVNOC: Your item was collected from [X] DO on [X].

EVNRF: Sorry, we were unable to deliver this item on [X] as the recipient refused to accept it. It will now be returned to the sender.

EVNRT: We're holding this item at [X] DO. We've received a request not to deliver mail to the property your item is addressed to today. We'll attempt delivery as per the instructions we've received.

EVPLC: Item [X] was collected by the customer from [X] Post Office [X] on [X].

EVRTS: Your item was delivered back to the sender on [X].

RFRXS: As requested, we'll arrange a Redelivery on [X]. If no one's home, we'll deliver your item to your chosen neighbour.

RNRXS: As requested, we'll arrange a Redelivery on [X] to your chosen address.

RORXS: As requested, we'll arrange a Redelivery for your item on [X].

RPRXS: As requested, we'll arrange a Redelivery to your chosen Post Office.

RSRXS: As requested, we'll arrange a Redelivery on [X]. If no one's home, we'll deliver your item to your chosen Safeplace. We'll let you know when it's delivered.

## Development

PHP 8.0 or above and Composer is expected to be installed.

### Installing Composer

For instructions on how to install Composer visit [getcomposer.org](https://getcomposer.org/download/).

### Installing

After cloning this repository, change into the newly created directory and run:

```bash
composer install
```

or if you have installed Composer locally in your current directory:

```bash
php composer.phar install
```

This will install all dependencies needed for the project.

Henceforth, the rest of this README will assume `composer` is installed globally (ie. if you are using `composer.phar` you will need to use `composer.phar` instead of `composer` in your terminal / command-line).

## Running the Tests

### Unit tests

Unit testing in this project is via [PHPUnit](https://phpunit.de/).

All unit tests can be run by executing:

```bash
composer phpunit
```

#### Debugging

To have PHPUnit stop and report on the first failing test encountered, run:

```bash
composer phpunit:debug
```

## Code formatting

A standard for code style can be important when working in teams, as it means that less time is spent by developers processing what they are reading (as everything will be consistent).

Code formatting is automated via [PHP-CS-Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer).
PHP-CS-Fixer will not format line lengths which do form part of the PSR-2 coding standards so these will product warnings when checked by [PHP Code Sniffer](https://github.com/squizlabs/PHP_CodeSniffer).

These can be run by executing:

```bash
composer phpcs
```

### Running everything

All of the tests can be run by executing:

```bash
composer test
```

### Outdated dependencies

Checking for outdated Composer dependencies can be performed by executing:

```bash
composer outdated
```

### Validating Composer configuration

Checking that the [composer.json](composer.json) is valid can be performed by executing:

```bash
composer validate --no-check-publish
```

### Running via GNU Make

If GNU [Make](https://www.gnu.org/software/make/) is installed, you can replace the above `composer` command prefixes with `make`.

All of the tests can be run by executing:

```bash
make test
```

### Running the tests on a Continuous Integration platform (eg. Github Actions or Travis)

Specific output formats better suited to CI platforms are included as Composer scripts.

To output unit test coverage in text and Clover XML format (which can be used for services such as [Coveralls](https://coveralls.io/)):

```
composer phpunit:ci
```

To output PHP-CS-Fixer (dry run) and PHPCS results in checkstyle format (which GitHub Actions will use to output a readable format):

```
composer phpcs:github-actions
```

## Built With

  - [PHP](https://secure.php.net/)
  - [Composer](https://getcomposer.org/)
  - [PHPUnit](https://phpunit.de/)
  - [PHP Code Sniffer](https://github.com/squizlabs/PHP_CodeSniffer)
  - [GNU Make](https://www.gnu.org/software/make/)

## License

This project is licensed under the MIT License - see the [LICENCE.md](LICENCE.md) file for details.
