<?php

declare(strict_types=1);

namespace ElliotJReed\RoyalMail\Tracking\Normaliser;

use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

final class NullObjectNormaliser extends ObjectNormalizer
{
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $data = parent::normalize($object, $format, $context);

        return \array_filter($data, static function ($value): bool {
            return null !== $value;
        });
    }
}
