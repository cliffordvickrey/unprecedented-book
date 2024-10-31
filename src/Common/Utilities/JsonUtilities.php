<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Utilities;

use CliffordVickrey\Book2024\Common\Exception\BookRuntimeException;
use Webmozart\Assert\Assert;

class JsonUtilities
{
    public static function jsonEncode(mixed $payload, bool $pretty = false): string
    {
        $encodeOptions = \JSON_HEX_TAG | \JSON_HEX_APOS | \JSON_HEX_QUOT | \JSON_HEX_AMP | \JSON_THROW_ON_ERROR;

        if ($pretty) {
            $encodeOptions |= \JSON_PRETTY_PRINT;
        }

        $json = json_encode($payload, $encodeOptions);

        Assert::string($json, \sprintf('Could not JSON encode %s', get_debug_type($payload)));

        return $json;
    }

    /**
     * @param TPayload $type
     *
     * @return TPayload
     *
     * @template TPayload
     */
    public static function jsonDecode(string $json, mixed $type = []): mixed
    {
        $payloadIsArray = \is_array($type);

        try {
            $decoded = json_decode($json, $payloadIsArray, flags: \JSON_THROW_ON_ERROR);
        } catch (\JsonException $ex) {
            throw new BookRuntimeException($ex->getMessage(), previous: $ex);
        }

        $actual = \gettype($decoded);
        $expected = \gettype($type);

        $msg = \sprintf('Expected JSON payload of type %s; got %s', $expected, $actual);
        Assert::true($actual === $expected, $msg);

        /* @var TPayload */
        return $decoded;
    }
}
