<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions\Modularity;

/**
 * Modularity builds each module wrapper's id from
 * `{post_type}-{ID}-{uniqid()}` (Modularity/source/php/Display.php).
 * uniqid() alone has limited time resolution, so two modules of the same
 * type rendered back-to-back on the same post (e.g. two Spacer modules)
 * can receive an identical id.
 *
 * This is our direct fix, applied on `Modularity/Display/BeforeModule`. The
 * long-term fix is upstream: swap uniqid() for wp_unique_id() in
 * Display.php so ids are unique by construction instead of by chance — see
 * patches/municipio/unique-module-id.patch in municipio-cloud-tooling for
 * the proposed diff. Remove this class once that ships in an installed
 * Municipio release.
 */
final class UniqueModuleId
{
    /** @var array<string, true> */
    private array $seenIds = [];

    public function filterBeforeModule(string $beforeModule): string
    {
        if (!preg_match('/id="([^"]*)"/', $beforeModule, $matches)) {
            return $beforeModule;
        }

        $id = $matches[1];
        if ($id === '' || !array_key_exists($id, $this->seenIds)) {
            $this->seenIds[$id] = true;
            return $beforeModule;
        }

        $uniqueId = wp_unique_id($id . '-');
        $this->seenIds[$uniqueId] = true;

        return preg_replace('/id="' . preg_quote($id, '/') . '"/', 'id="' . $uniqueId . '"', $beforeModule, 1);
    }
}
