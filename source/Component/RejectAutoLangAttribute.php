<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions\Component;

/**
 * The article wrapper's language field can be left on "auto detected
 * language" (a valid ACF choice in helsingborg-stad/Municipio's per-page
 * language field), which the theme renders as a literal, invalid
 * lang="auto" attribute (views/v3/partials/article.blade.php).
 *
 * This is our direct fix, applied on the component-library filter
 * ComponentLibrary/Component/Element/Data, which fires for every rendered
 * @element() component. Scoped strictly to the article wrapper (id
 * "article") so no other element is affected.
 *
 * The long-term fix is upstream: exclude 'auto' before it reaches
 * article.blade.php's attributeList — see
 * patches/municipio/reject-auto-lang-attribute.patch in
 * municipio-cloud-tooling for the proposed diff. Remove this class once
 * that ships in an installed Municipio release.
 */
final class RejectAutoLangAttribute
{
    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function filterData(array $data): array
    {
        if (($data['id'] ?? null) !== 'article') {
            return $data;
        }

        if (($data['attributeList']['lang'] ?? null) === 'auto') {
            unset($data['attributeList']['lang']);
        }

        return $data;
    }
}
