<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions\Layout;

final class SectionPlacement
{
    private const POST_TYPES = ['mod-section-split', 'mod-section-full', 'mod-section-featured', 'mod-section-card'];
    private const CONTENT_AREAS = ['content-area', 'content-area-top', 'content-area-bottom'];

    /**
     * Sections are allowed in all three article content areas by design. Preserve
     * Municipio's restrictions for other areas and unrelated module types.
     * Reindex the list because the editor consumes it as a JSON array.
     *
     * @param array<string, mixed> $specification
     * @return array<string, mixed>
     */
    public function filterIncompatibility(array $specification, string $postType): array
    {
        if (
            !in_array($postType, self::POST_TYPES, true) || !is_array($specification['sidebar_incompability'] ?? null)
        ) {
            return $specification;
        }

        $specification['sidebar_incompability'] = array_values(array_filter(
            $specification['sidebar_incompability'],
            static fn(mixed $area): bool => !in_array($area, self::CONTENT_AREAS, true),
        ));

        return $specification;
    }
}
