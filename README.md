[![Contributor Covenant](https://img.shields.io/badge/Contributor%20Covenant-v2.0%20adopted-ff69b4.svg)](code-of-conduct.md)

# Royal Mail Tracking for PHP

## Usage

To install the package via [Composer](https://getcomposer.org/download/):

```bash
composer require elliotjreed/royal-mail-tracking
```

### Errors

Errors are thrown as instances of the `\ElliotJReed\RoyalMail\Tracking\Exception\RoyalMailError` exception.

Further error information can be access through the `->getErrorResponse()` method on the thrown exception when available.

Potential causes and resolutions can be accessed through `->getErrorResponse()->getErrors()` when available.

Exceptions are thrown for `Events` and `Signature` requests for both application-specific errors (eg. invalid API credentials)
_and_ business-specific errors (eg. the tracking number provided does not exist).

Errors for business-specific `Summary` request errors are returned in the `MailPiece` object rather than thrown as exceptions
(as `Summary` will return information for multiple tracking numbers).

```php
try {
    $tracking = (new \ElliotJReed\RoyalMail\Tracking\Events(
        new \GuzzleHttp\Client(),
        'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
        '12345678901234567890123456789012345678901234567890'
    ));
    $tracking->setTrackingNumber('AB1234567890GB');
} catch (\ElliotJReed\RoyalMail\Tracking\Exception\RoyalMailError $exception) {
    echo $exception->getMessage() . \PHP_EOL;
    echo $exception->getErrorResponse()->getHttpCode() . \PHP_EOL;
    echo $exception->getErrorResponse()->getHttpMessage() . \PHP_EOL;
    echo $exception->getErrorResponse()->getMoreInformation() . \PHP_EOL . \PHP_EOL;

    $responseErrors = $exception->getErrorResponse()->getErrors();
    foreach ($responseErrors as $responseError) {
        echo $responseError->getErrorCode() . \PHP_EOL;
        echo $responseError->getErrorDescription() . \PHP_EOL;
        echo $responseError->getErrorCause() . \PHP_EOL;
        echo $responseError->getErrorResolution() . \PHP_EOL;
    }
}
```

#### Summary errors

The `Summary` call returns information for multiple tracking numbers, some of which may contain errors.

Because of this, when an error is encountered an exception will only be thrown when it is an application-specific error,
and *not* when the error is related to a particular tracking number. Instead, the error is returned via the `MailPiece` object.

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

### Events

The behaviour of the events operation is to provide a history of tracks for a single mail item.

Returns the summary, signature metadata, estimated delivery window and events for a supplied tracking number.

```php
$response = (new Events($client, 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee', '12345678901234567890123456789012345678901234567890'))
	->setTrackingNumber('FQ087430672GB')
	->get();

$response->getMailPieceId(); // 090367574000000FE1E1B
$response->getCarrierShortName(); // RM
$response->getCarrierFullName(); // Royal Mail Group Ltd

$summary = $response->getSummary();
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

$signature = $response->getSignature();
$signature->getRecipientName(); // Elliot
$signature->getSignatureDateTime(); // new DateTimeImmutable('2016-10-20T10:04:00+01:00')
$signature->getImageId(); // 001234

$estimatedDelivery = $response->getEstimatedDelivery();
$estimatedDelivery->getDate(); // new DateTimeImmutable('2017-02-20T00:00:00+00:00')
$estimatedDelivery->getStartOfEstimatedWindow(); // new DateTimeImmutable('2017-02-20T08:00:00+01:00')
$estimatedDelivery->getEndOfEstimatedWindow(); // new DateTimeImmutable('2017-02-20T11:00:00+01:00')

$events = $response->getEvents();
$event = $events[0];
$event->getEventCode(); // EVNMI
$event->getEventName(); // Forwarded - Mis-sort
$event->getEventDateTime(); // new DateTimeImmutable('2016-10-20T10:04:00+01:00')
$event->getLocationName(); // Stafford DO

$linkSummary = $response->getLinks()->getSummary();
$linkSummary->getHref(); // /mailpieces/v2/summary?mailPieceId=090367574000000FE1E1B
$linkSummary->getTitle(); // Summary
$linkSummary->getDescription(); // Get summary

$linkSignature = $response->getLinks()->getSignature();
$linkSignature->getHref(); // /mailpieces/v2/090367574000000FE1E1B/signature
$linkSignature->getTitle(); // Signature
$linkSignature->getDescription(); // Get signature

$linkRedelivery = $response->getLinks()->getRedelivery();
$linkRedelivery->getHref(); // /personal/receiving-mail/redelivery
$linkRedelivery->getTitle(); // Redelivery
$linkRedelivery->getDescription(); // Book a redelivery
```

#### JSON output

```php
echo (new Events($client, 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee', '12345678901234567890123456789012345678901234567890'))
	->setTrackingNumber('FQ087430672GB')
	->asJson();
```

Would output the Royal Mail events response as JSON:

```json
{
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
}
```

### Summary

The behaviour of the summary operation is to allow customers to obtain the latest tracking data for a mail item.

This operation returns the summary of one or more tracking numbers provided in the request.

This operation only allows a maximum of 30 tracking numbers to be provided in the `->setTrackingNumbers()` method
(eg. `->setTrackingNumbers('AB0123456789GB', 'CD0123456789GB')`).

```php
$response = (new Summary($client, 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee', '12345678901234567890123456789012345678901234567890'))
	->setTrackingNumbers('FQ087430672GB')
	->get();

$firstResponse = $response[0];

$firstResponse->getMailPieceId(); // 090367574000000FE1E1B
$firstResponse->getCarrierShortName(); // RM
$firstResponse->getCarrierFullName(); // Royal Mail Group Ltd

$summary = $firstResponse->getSummary();
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

$events = $firstResponse->getLinks()->getEvents();
$events->getHref(); // /mailpieces/v2/FQ087430672GB/events
$events->getTitle(); // Events
$events->getDescription(); // Get events

$error = $firstResponse->getError();
$error->getErrorCode(); // E1142
$error->getErrorDescription(); // Barcode reference $mailPieceId isn't recognised
$error->getErrorCause(); // A mail item with that barcode cannot be located
$error->getErrorResolution(); // Check barcode and resubmit
```

#### JSON output

```php
echo (new Summary($client, 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee', '12345678901234567890123456789012345678901234567890'))
	->setTrackingNumbers('123456789GB')
	->asJson();
```

Would output the Royal Mail summary response as JSON:

```json
[
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
]
```

### Signature

```php
$response = (new Signature($client, 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee', '12345678901234567890123456789012345678901234567890'))
	->setTrackingNumber('FQ087430672GB')
	->get();

$response->getMailPieceId(); // 090367574000000FE1E1B
$response->getCarrierShortName(); // RM
$response->getCarrierFullName(); // Royal Mail Group Ltd

$signature = $response->getSignature();
$signature->getRecipientName(); // Elliot
$signature->getSignatureDateTime(); // new DateTimeImmutable('2017-03-30T16:15:00+01:00')
$signature->getImageId(); // 001234
$signature->getOneDBarcode(); // FQ087430672GB
$signature->getHeight(); // 530
$signature->getWidth(); // 660
$signature->getUniqueItemId(); // 090367574000000FE1E1B
$signature->getImageFormat(); // image/svg+xml
$signature->getImage(); // <svg></svg>

$events = $firstResponse->getLinks()->getEvents();
$events->getHref(); // /mailpieces/v2/FQ087430672GB/events
$events->getTitle(); // Events
$events->getDescription(); // Get events

$linkSummary = $response->getLinks()->getSummary();
$linkSummary->getHref(); // /mailpieces/v2/summary?mailPieceId=090367574000000FE1E1B
$linkSummary->getTitle(); // Summary
$linkSummary->getDescription(); // Get summary
```

#### JSON output

```php
echo (new Summary($client, 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee', '12345678901234567890123456789012345678901234567890'))
	->setTrackingNumbers('FQ087430672GB')
	->asJson();
```

Would output the Royal Mail tracking response as JSON:

```json
{
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
    "recipientName": "Simon",
    "signatureDateTime": "2017-03-30T16:15:00+01:00",
    "uniqueItemId": "090367574000000FE1E1B",
    "width": 660
  }
}
```

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

### Static analysis

Static analysis tools can point to potential "weak spots" in your code, and can be useful in identifying unexpected side-effects.

[Psalm](https://psalm.dev/) is configured at its highest levels, meaning false positives are quite likely.

Static analysis tests can be run by executing:

```bash
composer static-analysis
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
composer phpcs:ci
```

#### Github Actions

Look at the example in [.github/workflows/main.yml](.github/workflows/main.yml).

#### Travis

Look at the example in [.travis.yml](.travis.yml).

## Built With

  - [PHP](https://secure.php.net/)
  - [Composer](https://getcomposer.org/)
  - [PHPUnit](https://phpunit.de/)
  - [Psalm](https://psalm.dev/)
  - [PHP Code Sniffer](https://github.com/squizlabs/PHP_CodeSniffer)
  - [GNU Make](https://www.gnu.org/software/make/)

## License

This project is licensed under the MIT License - see the [LICENCE.md](LICENCE.md) file for details.
