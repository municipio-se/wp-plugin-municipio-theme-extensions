/**
 * Slider pagination accessibility mitigation.
 *
 * Modern Municipio's slider (helsingborg-stad/styleguide,
 * source/components/slider/index.ts → changeNavigationButtonsToSpans) replaces
 * Splide's native `<button role="tab">` pagination pages with non-interactive
 * `<span class="c-slider__dot">` elements, but leaves Splide's `role="tablist"`
 * and `aria-label` on the surrounding `<ul>`. The result is a "tablist" with no
 * `tab` children — an ARIA violation (WCAG 1.3.1 / 4.1.2) reported by axe-core
 * and Lighthouse.
 *
 * The dots are a decorative, non-interactive progress indicator: slides are
 * navigated with the arrow controls, and per-slide state is exposed on the
 * Splide track. So the correct, low-risk fix is to stop the indicator from
 * claiming interactive tablist semantics and hide it from assistive technology,
 * without changing its appearance.
 *
 * This is a site-level mitigation. The underlying behaviour lives upstream in
 * helsingborg-stad/styleguide; an upstream fix is tracked separately.
 */
(function () {
    'use strict';

    var STEPPERS = '.c-slider__steppers';

    function neutralize(list) {
        if (!(list instanceof Element)) {
            return;
        }

        // Already neutralized and Splide has not re-added the tablist role.
        if (list.getAttribute('role') !== 'tablist' && list.getAttribute('aria-hidden') === 'true') {
            return;
        }

        list.removeAttribute('role');
        list.removeAttribute('aria-label');
        list.removeAttribute('aria-orientation');
        list.setAttribute('aria-hidden', 'true');
    }

    function scan(root) {
        var scope = root && root.querySelectorAll ? root : document;
        scope.querySelectorAll(STEPPERS).forEach(neutralize);
    }

    function init() {
        scan(document);

        // Splide re-mounts the pagination on refresh/resize, which re-adds the
        // tablist role, so re-apply whenever steppers appear or the role returns.
        var observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                if (
                    mutation.type === 'attributes' &&
                    mutation.target instanceof Element &&
                    mutation.target.matches(STEPPERS)
                ) {
                    neutralize(mutation.target);
                    return;
                }

                mutation.addedNodes.forEach(function (node) {
                    if (!(node instanceof Element)) {
                        return;
                    }

                    if (node.matches(STEPPERS)) {
                        neutralize(node);
                    }

                    scan(node);
                });
            });
        });

        observer.observe(document.body, {
            subtree: true,
            childList: true,
            attributes: true,
            attributeFilter: ['role'],
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
