<nav aria-label="{{ $label }}" class="municipio-theme-extensions-below-title-navigation u-margin__top--2 u-margin__bottom--4">
    <ul class="u-unlist u-padding--0 u-margin--0 u-display--flex u-flex-wrap u-gap-2">
        @foreach ($items as $item)
            <li class="u-margin__top--0">
                @button([
                    'text' => $item['text'],
                    'href' => $item['href'],
                    'color' => 'secondary',
                ])
                @endbutton
            </li>
        @endforeach
    </ul>
</nav>
