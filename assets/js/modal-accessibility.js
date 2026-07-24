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
 * values, so restore a sane tab order: keep the (scrollable) content focusable
 * with `tabindex="0"`, and drop the heading from the tab order entirely. No
 * visual or behavioural change.
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
                el.setAttribute('tabindex', '0');
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
