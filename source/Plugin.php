<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions;

use MunicipioThemeExtensions\Customizer\HeaderSettings;
use MunicipioThemeExtensions\Customizer\MenuSettings;
use MunicipioThemeExtensions\Customizer\TypographySettings;
use MunicipioThemeExtensions\Navigation\SecondaryMenu;

final class Plugin
{
    public function __construct(
        private readonly string $pluginFile,
        private readonly string $version,
    ) {}

    public function register(): void
    {
        $headerSettings = new HeaderSettings();
        $menuSettings = new MenuSettings();
        $typographySettings = new TypographySettings();
        $secondaryMenu = new SecondaryMenu();

        add_action('municipio_customizer_section_registered', [$headerSettings, 'register'], 10, 1);
        add_action('municipio_customizer_section_registered', [$menuSettings, 'register'], 10, 1);
        add_action('municipio_customizer_section_registered', [$typographySettings, 'register'], 10, 1);
        add_filter('Municipio/Template/viewData', [$secondaryMenu, 'filterViewData'], 10, 1);

        (new Assets($this->pluginFile, $this->version))->register();
    }
}
