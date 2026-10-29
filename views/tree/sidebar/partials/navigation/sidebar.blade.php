@if (!empty($menuItems))
    <div id="menu-sidebar" tabindex="-1">
    {!! $themeExtensionsTree($menuItems, __('Side navigation', 'municipio-theme-extensions'), ['c-nav--sidebar', 'c-nav--bordered', 'u-print-display--none', 's-nav-sidebar']) !!}
    </div>
@endif
