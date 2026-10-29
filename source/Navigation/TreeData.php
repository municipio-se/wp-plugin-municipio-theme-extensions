<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions\Navigation;

use Closure;
use RuntimeException;

final class TreeData
{
    private int $count = 0;

    /**
     * Resolve only branches declared by Municipio, preserving its menu boundaries and filters.
     * A partial or cyclic response must leave the server-rendered navigation in place.
     */
    public function expand(array $items, Closure $resolve, array $ancestors = [], string $path = ''): array
    {
        $result = [];
        foreach (array_values($items) as $index => $item) {
            if (!is_array($item) || ++$this->count > 10_000 || count($ancestors) > 100) {
                throw new RuntimeException('Invalid navigation tree.');
            }
            $id = (string) ($item['id'] ?? '');
            $key = $path . '/' . $index . ':' . $id;
            $children = $item['children'] ?? [];
            if ($children === true) {
                $fetch = (string) ($item['attributeList']['data-fetch-url'] ?? '');
                if ($fetch === '' || in_array($fetch, $ancestors, true)) {
                    throw new RuntimeException('Unresolvable navigation branch.');
                }
                $children = $resolve($fetch);
                $nextAncestors = [...$ancestors, $fetch];
            } else {
                $nextAncestors = $ancestors;
            }
            $result[] = [
                'key' => $key,
                'id' => $id,
                'label' => html_entity_decode(
                    strip_tags((string) ($item['label'] ?? '')),
                    ENT_QUOTES | ENT_HTML5,
                    'UTF-8',
                ),
                'href' => (string) ($item['href'] ?? ''),
                'target' => (string) ($item['target'] ?? $item['attributeList']['target'] ?? '_top'),
                'rel' => (string) ($item['rel'] ?? $item['attributeList']['rel'] ?? $item['xfn'] ?? ''),
                'children' => $this->expand(is_array($children) ? $children : [], $resolve, $nextAncestors, $key),
            ];
        }
        return $result;
    }

    /** Remove page-specific state before sharing a snapshot between instances. */
    public static function seed(array $items): array
    {
        return array_map(static function (array $item): array {
            unset($item['active'], $item['ancestor'], $item['isCached']);
            if (is_array($item['children'] ?? null)) {
                $item['children'] = self::seed($item['children']);
            }
            return $item;
        }, array_values($items));
    }
}
