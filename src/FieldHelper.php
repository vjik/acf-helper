<?php

declare(strict_types=1);

namespace Vjik\AcfHelper;

use DateTimeImmutable;
use Exception;
use RuntimeException;
use Vjik\SimpleTypeCaster\TypeCaster;
use WP_Post;

use function function_exists;

final class FieldHelper
{
    /**
     * @param mixed $postId
     */
    public static function getIntOrNull(string $selector, $postId = false, bool $formatValue = true): ?int
    {
        return TypeCaster::toIntOrNull(
            self::get($selector, $postId, $formatValue)
        );
    }

    /**
     * @param mixed $postId
     */
    public static function getStringOrNull(string $selector, $postId = false, bool $formatValue = true): ?string
    {
        return TypeCaster::toStringOrNull(
            self::get($selector, $postId, $formatValue)
        );
    }

    /**
     * @param mixed $postId
     */
    public static function getBool(string $selector, $postId = false, bool $formatValue = true): bool
    {
        return (bool)self::get($selector, $postId, $formatValue);
    }

    /**
     * @param mixed $postId
     */
    public static function getArray(string $selector, $postId = false, bool $formatValue = true): array
    {
        return TypeCaster::toArray(
            self::get($selector, $postId, $formatValue)
        );
    }

    /**
     * @param mixed $postId
     */
    public static function getArrayOrNull(string $selector, $postId = false, bool $formatValue = true): array
    {
        return TypeCaster::toArrayOrNull(
            self::get($selector, $postId, $formatValue)
        );
    }

    /**
     * @param mixed $postId
     *
     * @return WP_Post[]
     */
    public static function getArrayOfPosts(string $selector, $postId = false, bool $formatValue = true): array
    {
        $posts = [];
        foreach (self::getArray($selector, $postId, $formatValue) as $value) {
            if ($value instanceof WP_Post) {
                $posts[] = $value;
            }
        }
        return $posts;
    }

    /**
     * @param mixed $postId
     */
    public static function getPostOrNull(string $selector, $postId = false, bool $formatValue = true): ?WP_Post
    {
        $value = self::get($selector, $postId, $formatValue);
        return $value instanceof WP_Post ? $value : null;
    }

    /**
     * @param mixed $postId
     */
    public static function getDateTimeImmutableOrNull(
        string $selector,
        $postId = false,
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

    /**
     * @param mixed $postId
     *
     * @return mixed
     */
    private static function get(string $selector, $postId = false, bool $formatValue = true)
    {
        if (!function_exists('get_field')) {
            throw new RuntimeException('Function "get_field" not found.');
        }

        return get_field($selector, $postId, $formatValue);
    }
}
