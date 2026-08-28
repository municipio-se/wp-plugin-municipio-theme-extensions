<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions\Layout;

use Closure;
use MunicipioThemeExtensions\Customizer\ContentLayoutSettings;

final class ContentLayout
{
    private const INSIDE = 'inside';
    private const OUTSIDE = 'outside';
    private const CENTER = 'center';

    /** @var null|Closure(string, array<string, mixed>, mixed, bool): string */
    private readonly ?Closure $renderer;

    public function __construct(?callable $renderer = null)
    {
        $this->renderer = $renderer === null ? null : Closure::fromCallable($renderer);
    }

    /**
     * @param array<string, mixed> $viewData
     * @return array<string, mixed>
     */
    public function filterViewData(array $viewData): array
    {
        if (!is_singular()) {
            return $viewData;
        }

        $customizer = $viewData['customizer'] ?? null;
        $alignment = $this->value(
            $customizer,
            'mxArticleAlignment',
            ContentLayoutSettings::ARTICLE_ALIGNMENT_SETTING,
            'left',
        );
        $placement = $this->value(
            $customizer,
            'mxContentAreaPlacement',
            ContentLayoutSettings::CONTENT_AREA_PLACEMENT_SETTING,
            self::OUTSIDE,
        );

        $viewData['centerContent'] = $alignment === self::CENTER;

        return $viewData;
    }

    public function filterOuterContentArea(bool $show): bool
    {
        return $show && $this->placement() === self::OUTSIDE;
    }

    public function renderInsideContentArea(): void
    {
        if (!$this->shouldRenderInsideContentArea()) {
            return;
        }

        $renderer =
            $this->renderer
            ?? static fn(string $view, array $data, mixed $paths, bool $formatError): string => render_blade_view(
                $view,
                $data,
                $paths,
                $formatError,
            );

        echo
            $renderer(
                'partials.sidebar',
                ['id' => 'content-area', 'classes' => ['o-grid', 'municipio-theme-extensions-content-area--inside']],
                false,
                false,
            )
        ;
    }

    private function placement(): string
    {
        return (string) get_theme_mod(ContentLayoutSettings::CONTENT_AREA_PLACEMENT_SETTING, self::OUTSIDE);
    }

    private function supportsInsidePlacement(mixed $pageTemplate): bool
    {
        return $pageTemplate === false || $pageTemplate === '' || $pageTemplate === 'page-centered.blade.php';
    }

    private function shouldRenderInsideContentArea(): bool
    {
        return is_singular()
        && $this->placement() === self::INSIDE
        && $this->supportsInsidePlacement(get_page_template_slug(get_queried_object_id()));
    }

    private function value(mixed $customizer, string $controllerProperty, string $setting, string $default): string
    {
        $values = (array) $customizer;

        return (string) ($values[$controllerProperty] ?? $values[$setting] ?? get_theme_mod($setting, $default));
    }
}
