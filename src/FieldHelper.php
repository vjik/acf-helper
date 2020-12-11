<?php

declare(strict_types=1);

namespace Vjik\AcfHelper;

use RuntimeException;
use Vjik\SimpleTypeCaster\TypeCaster;
use WP_Post;

final class FieldHelper
{
    /**
     * @param string $selector
     * @param mixed $postId
     * @param bool $formatValue
     * @return int|null
     */
    public static function getIntOrNull(string $selector, $postId = false, bool $formatValue = true): ?int
    {
        return TypeCaster::toIntOrNull(
            self::get($selector, $postId, $formatValue)
        );
    }

    /**
     * @param string $selector
     * @param mixed $postId
     * @param bool $formatValue
     * @return string|null
     */
    public static function getStringOrNull(string $selector, $postId = false, bool $formatValue = true): ?string
    {
        return TypeCaster::toStringOrNull(
            self::get($selector, $postId, $formatValue)
        );
    }

    /**
     * @param string $selector
     * @param mixed $postId
     * @param bool $formatValue
     * @return bool
     */
    public static function getBool(string $selector, $postId = false, bool $formatValue = true): bool
    {
        return (bool)self::get($selector, $postId, $formatValue);
    }

    /**
     * @param string $selector
     * @param mixed $postId
     * @param bool $formatValue
     * @return array
     */
    public static function getArray(string $selector, $postId = false, bool $formatValue = true): array
    {
        return TypeCaster::toArray(
            self::get($selector, $postId, $formatValue)
        );
    }

    /**
     * @param string $selector
     * @param false $postId
     * @param bool $formatValue
     * @return WP_Post|null
     */
    public static function getPostOrNull(string $selector, $postId = false, bool $formatValue = true): ?WP_Post
    {
        $value = self::get($selector, $postId, $formatValue);
        return $value instanceof WP_Post ? $value : null;
    }

    /**
     * @param string $selector
     * @param mixed $postId
     * @param bool $formatValue
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
