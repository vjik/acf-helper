<?php

declare(strict_types=1);

namespace Vjik\AcfHelper;

use RuntimeException;
use Vjik\SimpleTypeCaster\TypeCaster;
use Yiisoft\Arrays\ArrayHelper;
use Yiisoft\Strings\NumericHelper;

use function array_key_exists;
use function count;
use function function_exists;
use function is_array;

final class BlockFieldHelper
{
    private const ID_KEY = 'acf-field-id';
    private const ID_SEPARATOR = '~';

    public static function useBlockId(string $blockName, ?string $idPrefix = null): void
    {
        add_filter(
            'acf/pre_save_block',
            static function ($attributes) use ($blockName, $idPrefix) {
                if (($attributes['name'] ?? '') === ('acf/' . $blockName)) {
                    $attributes[self::ID_KEY] = uniqid($idPrefix ?? '', true);
                }
                return $attributes;
            }
        );
    }

    /**
     * @param array $attributes The block attributes.
     */
    public static function getFullBlockId(array $attributes): string
    {
        return get_the_ID() . self::ID_SEPARATOR . ($attributes[self::ID_KEY] ?? '');
    }

    public static function getStringOrNull(
        string $selector,
        string $fullBlockId,
        bool $formatValue = true,
        bool $trim = true,
    ): ?string
    {
        return TypeCaster::toStringOrNull(
            self::getValue($selector, $fullBlockId, $formatValue),
            trim: $trim,
        );
    }

    public static function getPostId(string $fullBlockId): ?int
    {
        $ids = explode(self::ID_SEPARATOR, $fullBlockId, 2);
        if (count($ids) !== 2) {
            return null;
        }

        return TypeCaster::toIntOrNull($ids[0]);
    }

    /**
     * @return mixed
     */
    public static function getValue(string $selector, string $fullBlockId, bool $formatValue = true)
    {
        if (!function_exists('acf_setup_meta')) {
            throw new RuntimeException('Function "acf_setup_meta" not found.');
        }
        if (!function_exists('get_field')) {
            throw new RuntimeException('Function "get_field" not found.');
        }
        if (!function_exists('acf_reset_meta')) {
            throw new RuntimeException('Function "acf_reset_meta" not found.');
        }

        $ids = explode(self::ID_SEPARATOR, $fullBlockId, 2);
        if (count($ids) !== 2) {
            return null;
        }

        $postId = $ids[0];
        if (!NumericHelper::isInteger($postId)) {
            return null;
        }
        $postId = (int) $postId;

        $blockId = $ids[1];
        if (empty($blockId)) {
            return null;
        }

        $post = get_post($postId);
        if ($post === null) {
            return null;
        }

        $blocks = parse_blocks($post->post_content);

        return self::searchValueInBlocks($blocks, $blockId, $selector, $formatValue);
    }

    private static function searchValueInBlocks(
        array $blocks,
        string $blockId,
        string $selector,
        bool $formatValue
    ): mixed {
        foreach ($blocks as $block) {
            if (is_array($block)) {
                if (ArrayHelper::getValue($block, ['attrs', self::ID_KEY]) === $blockId) {
                    $data = $block['attrs']['data'] ?? [];
                    if (!is_array($data) || !array_key_exists($selector, $data)) {
                        return null;
                    }

                    acf_setup_meta($data, $blockId, true);
                    $value = get_field($selector, false, $formatValue);
                    acf_reset_meta($blockId);

                    return $value;
                }

                $innerBlocks = ArrayHelper::getValue($block, ['innerBlocks']);
                if (is_array($innerBlocks)) {
                    $value = self::searchValueInBlocks($innerBlocks, $blockId, $selector, $formatValue);
                    if ($value !== null) {
                        return $value;
                    }
                }
            }
        }
        return null;
    }
}
