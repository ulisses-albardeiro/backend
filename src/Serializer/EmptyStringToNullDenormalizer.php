<?php

namespace App\Serializer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;

final class EmptyStringToNullDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private const ALREADY_CALLED = 'EMPTY_STRING_TO_NULL_DENORMALIZER_ALREADY_CALLED';

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED] = true;

        if (is_array($data)) {
            $data = $this->convertEmptyStringsToNull($data);
        }

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return str_starts_with($type, 'App\\DTO\\');
    }

    private function convertEmptyStringsToNull(array $data): array
    {
        foreach ($data as $key => $value) {
            if ($value === '') {
                $data[$key] = null;
            } elseif (is_array($value)) {
                $data[$key] = $this->convertEmptyStringsToNull($value);
            }
        }

        return $data;
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }
}
