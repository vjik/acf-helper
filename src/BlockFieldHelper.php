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
use function is_string;

final class BlockFieldHelper
{
    private const ID_SEPARATOR = '~';

    public static function getFullBlockId(): string
    {
        if (!function_exists('acf_get_valid_post_id')) {
            throw new RuntimeException('Function "acf_get_valid_post_id" not found.');
        }

        return get_the_ID() . self::ID_SEPARATOR . acf_get_valid_post_id();
    }

    public static function getStringOrNull(string $selector, string $fullBlockId, bool $formatValue = true): ?string
    {
        return TypeCaster::toStringOrNull(
            self::getValue($selector, $fullBlockId, $formatValue)
        );
    }

    /**
     * @return mixed
     */
    public static function getValue(string $selector, string $fullBlockId, bool $formatValue = true)
    {
        if (!function_exists('acf_maybe_get_field')) {
            throw new RuntimeException('Function "acf_maybe_get_field" not found.');
        }
        if (!function_exists('acf_format_value')) {
            throw new RuntimeException('Function "acf_format_value" not found.');
        }

        $ids = explode(self::ID_SEPARATOR, $fullBlockId, 2);
        if (count($ids) !== 2) {
            return null;
        }

        $postId = $ids[0];
        if (!NumericHelper::isInteger($postId)) {
            return null;
        }
        $postId = (int)$postId;

        $blockId = $ids[1];
        if (empty($blockId)) {
            return null;
        }

        $post = get_post($postId);
        if ($post === null) {
            return null;
        }

        $blocks = parse_blocks($post->post_content);
        foreach ($blocks as $block) {
            if (is_array($block) && ArrayHelper::getValue($block, ['attrs', 'id']) === $blockId) {
                $data = $block['attrs']['data'] ?? [];
                if (!is_array($data) || !array_key_exists($selector, $data)) {
                    return null;
                }

                $value = $data[$selector];

                if ($formatValue) {
                    $fieldId = $data['_' . $selector] ?? null;
                    if (is_string($fieldId) && $fieldId !== '') {
                        $field = acf_maybe_get_field($fieldId);
                        if ($field !== false) {
                            $value = acf_format_value($value, 0, $field);
                        }
                    }
                }

                return $value;
            }
        }

        return null;
    }
}
