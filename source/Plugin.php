<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions;

use MunicipioThemeExtensions\Customizer\DrawerSettings;
use MunicipioThemeExtensions\Customizer\HeaderSettings;
use MunicipioThemeExtensions\Customizer\MenuSettings;
use MunicipioThemeExtensions\Customizer\OnePageSettings;
use MunicipioThemeExtensions\Customizer\ResponsiveHeaderSettings;
use MunicipioThemeExtensions\Customizer\SecondaryNavigationPosition;
use MunicipioThemeExtensions\Customizer\TypographySettings;
use MunicipioThemeExtensions\Header\StandardHeader;
use MunicipioThemeExtensions\Navigation\BelowTitleNavigation;
use MunicipioThemeExtensions\Navigation\PageHideSecondaryMenu;
use MunicipioThemeExtensions\Navigation\SecondaryMenu;
use MunicipioThemeExtensions\OnePage\ClassicContent;

final class Plugin
{
    public function __construct(
        private readonly string $pluginFile,
        private readonly string $version,
    ) {}

    public function register(): void
    {
        $headerSettings = new HeaderSettings();
        $drawerSettings = new DrawerSettings();
        $menuSettings = new MenuSettings();
        $onePageSettings = new OnePageSettings();
        $responsiveHeaderSettings = new ResponsiveHeaderSettings();
        $typographySettings = new TypographySettings();
        $secondaryNavigationPosition = new SecondaryNavigationPosition();
        $standardHeader = new StandardHeader();
        $secondaryMenu = new SecondaryMenu();
        $pageHideSecondaryMenu = new PageHideSecondaryMenu();
        $belowTitleNavigation = new BelowTitleNavigation($pageHideSecondaryMenu);
        $onePageClassicContent = new ClassicContent();

        add_action('municipio_customizer_section_registered', [$headerSettings, 'register'], 10, 1);
        add_action('municipio_customizer_section_registered', [$drawerSettings, 'register'], 10, 1);
        add_action('municipio_customizer_section_registered', [$menuSettings, 'register'], 10, 1);
        add_action('municipio_customizer_section_registered', [$onePageSettings, 'register'], 10, 1);
        add_action('municipio_customizer_section_registered', [$responsiveHeaderSettings, 'register'], 10, 1);
        add_action('municipio_customizer_section_registered', [$typographySettings, 'register'], 10, 1);
        add_filter(
            SecondaryNavigationPosition::FIELD_ARGUMENTS_FILTER,
            [$secondaryNavigationPosition, 'filterFieldArguments'],
            10,
            1,
        );
        add_action('acf/init', [$pageHideSecondaryMenu, 'registerField']);
        add_action('article_content_before', [$belowTitleNavigation, 'render']);
        add_filter(
            'Municipio/Hook/headerSecondaryNavigationTabsClass',
            [$standardHeader, 'filterTabMenuClassList'],
            10,
            2,
        );
        add_filter(
            'Municipio/Hook/headerSecondaryNavigationTabsHeight',
            [$standardHeader, 'filterTabMenuHeight'],
            10,
            2,
        );
        add_filter('Municipio/Search/Hero_search_placeholder', [$standardHeader, 'filterHeroSearchPlaceholder'], 10, 2);
        add_filter('Municipio/Template/viewData', [$secondaryMenu, 'filterViewData'], 10, 1);
        add_filter('Municipio/Template/viewData', [$pageHideSecondaryMenu, 'filterViewData'], 20, 1);
        add_filter('Municipio/Template/viewData', [$onePageClassicContent, 'filterViewData'], 10, 1);
        add_filter(
            'Municipio/Controller/Singular/showTitleOnOnePage',
            [$onePageClassicContent, 'filterShowTitle'],
            10,
            2,
        );

        (new Assets($this->pluginFile, $this->version))->register();
    }
}
