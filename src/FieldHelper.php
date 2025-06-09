<?php

declare(strict_types=1);

namespace Vjik\AcfHelper;

use DateTimeImmutable;
use Exception;
use RuntimeException;
use Vjik\SimpleTypeCaster\TypeCaster;
use WP_Post;

use function function_exists;
use function is_array;

final class FieldHelper
{
    public static function getIntOrNull(string $selector, mixed $postId = false, bool $formatValue = true): ?int
    {
        return TypeCaster::toIntOrNull(
            self::get($selector, $postId, $formatValue)
        );
    }

    public static function getStringOrNull(
        string $selector,
        mixed $postId = false,
        bool $formatValue = true,
        bool $trim = true,
    ): ?string
    {
        return TypeCaster::toStringOrNull(
            self::get($selector, $postId, $formatValue),
            trim: $trim,
        );
    }

    public static function getString(
        string $selector,
        mixed $postId = false,
        bool $formatValue = true,
        bool $trim = true,
    ): string
    {
        return TypeCaster::toString(
            self::get($selector, $postId, $formatValue),
            trim: $trim,
        );
    }

    public static function getBool(string $selector, mixed $postId = false, bool $formatValue = true): bool
    {
        return (bool)self::get($selector, $postId, $formatValue);
    }

    public static function getArray(string $selector, mixed $postId = false, bool $formatValue = true): array
    {
        return TypeCaster::toArray(
            self::get($selector, $postId, $formatValue)
        );
    }

    public static function getArrayOrNull(string $selector, mixed $postId = false, bool $formatValue = true): ?array
    {
        return TypeCaster::toArrayOrNull(
            self::get($selector, $postId, $formatValue)
        );
    }

    /**
     * @return array[]
     */
    public static function getArrayOfArrays(string $selector, mixed $postId = false, bool $formatValue = true): array
    {
        $arrays = [];
        foreach (self::getArray($selector, $postId, $formatValue) as $value) {
            if (is_array($value)) {
                $arrays[] = $value;
            }
        }
        return $arrays;
    }

    /**
     * @return WP_Post[]
     */
    public static function getArrayOfPosts(string $selector, mixed $postId = false, bool $formatValue = true): array
    {
        $posts = [];
        foreach (self::getArray($selector, $postId, $formatValue) as $value) {
            if ($value instanceof WP_Post) {
                $posts[] = $value;
            }
        }
        return $posts;
    }

    public static function getPostOrNull(string $selector, mixed $postId = false, bool $formatValue = true): ?WP_Post
    {
        $value = self::get($selector, $postId, $formatValue);
        return $value instanceof WP_Post ? $value : null;
    }

    public static function getDateTimeImmutableOrNull(
        string $selector,
        mixed $postId = false,
        bool $formatValue = true
    ): ?DateTimeImmutable {
        $value = self::getStringOrNull($selector, $postId, $formatValue);
        if ($value === null) {
            return null;
        }

        $timestamp = (int)$value;
        if ((string)$timestamp === $value) {
            return (new DateTimeImmutable())->setTimestamp($timestamp);
        }

        try {
            return (new DateTimeImmutable($value));
        } catch (Exception $e) {
            return null;
        }
    }

    private static function get(string $selector, mixed $postId = false, bool $formatValue = true): mixed
    {
        if (!function_exists('get_field')) {
            throw new RuntimeException('Function "get_field" not found.');
        }

        return get_field($selector, $postId, $formatValue);
    }
}
