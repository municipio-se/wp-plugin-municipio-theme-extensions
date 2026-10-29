@if (!empty($mobileMenu['items']))
    @php
        $treePanelId = wp_unique_id('mte-mobile-');
        $treeScreenClasses = array_map(fn($size) => 'u-display--none@' . $size, array_diff(['xs', 'sm', 'md', 'lg', 'xl'], $screenSizes ?? $customizer->drawerScreenSizes ?? ['xs', 'sm']));
        $treeDrawerModifiers = array_map(fn($modifier) => 'c-drawer--' . $modifier, apply_filters('ComponentLibrary/Component/Modifier', [], ['site.header.drawer']));
    @endphp
    <details class="mte-mobile {{ implode(' ', $treeScreenClasses) }}" data-tree-panel>
        <summary class="c-button c-button__{{ $customizer->headerTriggerButtonType }} c-button__{{ $customizer->headerTriggerButtonType }}--{{ $customizer->headerTriggerButtonColor }} c-button--{{ $customizer->headerTriggerButtonSize }} mobile-menu-trigger s-header-button" aria-controls="{{ $treePanelId }}">
            <span class="c-button__label"><span class="c-button__label-text">{{ $lang->menu }}</span><span class="c-button__label-icon">@icon(['icon' => 'menu', 'size' => 'md']) @endicon</span></span>
        </summary>
        <div id="{{ $treePanelId }}" class="c-drawer c-drawer--right c-drawer--{{ !empty($mobileSecondaryMenu['items']) ? 'duotone' : 'monotone' }} {{ implode(' ', $treeDrawerModifiers) }} s-drawer-menu mte-mobile__content" data-tree-dialog-content aria-label="{{ $lang->menu }}">
            <div class="c-drawer__header">
                @includeWhen($showMobileSearchDrawer, 'partials.search.drawer-search-form', ['classList' => ['search-form', 'u-margin__top--2', 'u-width--100']])
                <button type="button" class="c-drawer__close mte-mobile__close" data-tree-close hidden>
                    <span>{{ $lang->close }}</span>
                    @icon(['icon' => 'close', 'size' => 'md']) @endicon
                </button>
            </div>
            {!! $themeExtensionsTree($mobileMenu['items'], $lang->menu, ['c-nav--drawer', 'site-nav-mobile__primary', 's-nav-drawer', 's-nav-drawer-primary', !empty($customizer->drawerDivider) ? 'c-nav--bordered' : '', !empty($customizer->drawerDividerTopLevelOnly) ? 'c-nav--bordered-top-level' : ''], true) !!}
            @if (!empty($mobileSecondaryMenu['items']))
                @nav([
                    'id' => $treePanelId . '-secondary',
                    'classList' => ['c-nav--drawer', 'site-nav-mobile__secondary', 's-nav-drawer', 's-nav-drawer-secondary', !empty($customizer->drawerDivider) ? 'c-nav--bordered' : '', !empty($customizer->drawerDividerTopLevelOnly) ? 'c-nav--bordered-top-level' : ''],
                    'items' => $mobileSecondaryMenu['items'],
                    'direction' => 'vertical',
                    'includeToggle' => true,
                    'height' => 'sm',
                    'expandLabel' => $lang->expand,
                    'context' => 'site.mobile-menu'
                ])
                @endnav
            @endif
            @if (!empty($customizer->headerLoginLogoutShowInMobileMenu))
                @include('partials.header.components.user', ['classList' => ['user--drawer']])
            @endif
        </div>
    </details>
@endif
