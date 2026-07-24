/**
 * Modal focus-order accessibility mitigation.
 *
 * Modern Municipio's modal component (helsingborg-stad/component-library,
 * source/php/Component/Modal/modal.blade.php) renders positive `tabindex`
 * values: `tabindex="2"` on the modal content and, when a heading is present,
 * `tabindex="1"` on the modal heading. Positive tabindex overrides the natural
 * DOM order and pulls those elements to the front of the whole page's tab
 * sequence — a focus-order violation (WCAG 2.4.3) reported by Lighthouse.
 *
 * The `<dialog>` element already manages focus and nothing relies on these
 * values, so restore a sane tab order: take the non-interactive content wrapper
 * out of the tab order (`tabindex="-1"`) and drop the heading from it entirely,
 * leaving the keyboard focus flow to the modal's real controls. No visual or
 * behavioural change.
 *
 * This is a site-level mitigation. The underlying markup lives upstream in
 * helsingborg-stad/component-library; an upstream fix is tracked separately.
 */
(function () {
    'use strict';

    function isPositiveTabindex(el) {
        return parseInt(el.getAttribute('tabindex') || '0', 10) > 0;
    }

    function fix() {
        document.querySelectorAll('.c-modal__content').forEach(function (el) {
            if (isPositiveTabindex(el)) {
                // The content wrapper is not interactive, so take it out of the
                // tab order entirely rather than leaving it as a tab stop.
                el.setAttribute('tabindex', '-1');
            }
        });

        document.querySelectorAll('.c-modal__heading').forEach(function (el) {
            if (isPositiveTabindex(el)) {
                el.removeAttribute('tabindex');
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fix);
    } else {
        fix();
    }
})();
