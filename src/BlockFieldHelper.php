<?php

declare(strict_types=1);

namespace Vjik\AcfHelper;

use RuntimeException;
use Vjik\SimpleTypeCaster\TypeCaster;
use Yiisoft\Arrays\ArrayHelper;
use Yiisoft\Strings\NumericHelper;

use function count;
use function function_exists;
use function is_array;

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

    public static function getStringOrNull(string $selector, string $fullBlockId): ?string
    {
        return TypeCaster::toStringOrNull(
            self::getBlockData($fullBlockId)[$selector] ?? null
        );
    }

    private static function getBlockData(string $fullBlockId): array
    {
        $ids = explode(self::ID_SEPARATOR, $fullBlockId, 2);
        if (count($ids) !== 2) {
            return [];
        }

        $postId = $ids[0];
        if (!NumericHelper::isInteger($postId)) {
            return [];
        }
        $postId = (int)$postId;

        $blockId = $ids[1];
        if (empty($blockId)) {
            return [];
        }

        $post = get_post($postId);
        if ($post === null) {
            return [];
        }

        $blocks = parse_blocks($post->post_content);
        foreach ($blocks as $block) {
            if (is_array($block) && ArrayHelper::getValue($block, ['attrs', 'id']) === $blockId) {
                $data = ArrayHelper::getValue($block, ['attrs', 'data'], []);
                return is_array($data) ? $data : [];
            }
        }

        return [];
    }
}
