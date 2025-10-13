// Persian Font Enforcer Script
// This script ensures Persian fonts are applied to all elements

(function () {
    'use strict';

    var html = document.documentElement;

    function normaliseDocumentDirection() {
        if (!html.getAttribute('lang')) {
            html.setAttribute('lang', 'fa');
        }

        if (html.getAttribute('dir') !== 'rtl') {
            html.setAttribute('dir', 'rtl');
        }

        html.classList.add('rtl-enabled');
    }

    function applyBodyClasses() {
        if (!document.body) {
            return;
        }

        document.body.classList.add('font-persian', 'rtl-body');
        document.body.setAttribute('dir', 'rtl');
    }

    function initialise() {
        normaliseDocumentDirection();
        applyBodyClasses();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialise);
    } else {
        initialise();
    }

    var observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            if (!mutation.addedNodes || !mutation.addedNodes.length) {
                return;
            }

            applyBodyClasses();
        });
    });

    if (document.body) {
        observer.observe(document.body, { childList: true, subtree: true });
    } else {
        document.addEventListener('DOMContentLoaded', function () {
            observer.observe(document.body, { childList: true, subtree: true });
        });
    }
})();