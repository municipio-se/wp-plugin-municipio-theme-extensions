<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions;

use MunicipioThemeExtensions\Customizer\HeaderSettings;
use MunicipioThemeExtensions\Customizer\TypographySettings;

final class Plugin
{
    public function __construct(
        private readonly string $pluginFile,
        private readonly string $version,
    ) {}

    public function register(): void
    {
        $headerSettings = new HeaderSettings();
        $typographySettings = new TypographySettings();

        add_action('municipio_customizer_section_registered', [$headerSettings, 'register'], 10, 1);
        add_action('municipio_customizer_section_registered', [$typographySettings, 'register'], 10, 1);

        (new Assets($this->pluginFile, $this->version))->register();
    }
}
