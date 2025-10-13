(function ($) {
    'use strict';

    const languageUrl = 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/fa.json';
    const persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    const TEXT_NODE = 3;
    const ELEMENT_NODE = 1;

    function toPersianDigits(value) {
        if (typeof value !== 'string') {
            value = (value ?? '').toString();
        }
        return value.replace(/\d/g, function (digit) {
            return persianDigits[digit] ?? digit;
        });
    }

    function convertNodeDigits(element) {
        if (!element || !element.childNodes) {
            return;
        }

        Array.prototype.forEach.call(element.childNodes, function (node) {
            if (node.nodeType === TEXT_NODE) {
                node.nodeValue = toPersianDigits(node.nodeValue);
            } else if (node.nodeType === ELEMENT_NODE) {
                convertNodeDigits(node);
            }
        });
    }

    function convertWrapperDigits($wrapper) {
        const selectors = [
            '.dataTables_info',
            '.dataTables_paginate .paginate_button',
            '.dataTables_length label'
        ];

        selectors.forEach(function (selector) {
            $wrapper.find(selector).each(function () {
                convertNodeDigits(this);
                const title = this.getAttribute('title');
                if (title) {
                    this.setAttribute('title', toPersianDigits(title));
                }
            });
        });
    }

    function buildDom(options, hasCustomDom) {
        if (hasCustomDom) {
            return options.dom;
        }

        const segments = [];
        const topRow = [];

        if (options.lengthChange !== false) {
            topRow.push("<'col-lg-4 col-md-5 col-sm-12 text-start text-md-start'l>");
        }

        topRow.push("<'col-lg-8 col-md-7 col-sm-12 text-start text-md-end'f>");

        if (topRow.length) {
            segments.push("<'row align-items-center mb-3'" + topRow.join('') + ">");
        }

        segments.push("<'row'<'col-12'tr>>");

        const bottomRow = [];
        bottomRow.push("<'col-md-6 col-sm-12 text-start text-md-start'i>");

        if (options.paging !== false) {
            bottomRow.push("<'col-md-6 col-sm-12 text-start text-md-end'p>");
        }

        if (bottomRow.length) {
            segments.push("<'row align-items-center mt-3'" + bottomRow.join('') + ">");
        }

        return segments.join('');
    }

    $(document).ready(function () {
        if (!$.fn.DataTable) {
            return;
        }

        const $tables = $('table.js-data-table');
        if (!$tables.length) {
            return;
        }

        $tables.each(function () {
            const table = this;
            const $table = $(table);

            if ($.fn.DataTable.isDataTable(table)) {
                return;
            }

            if ($table.is('[data-datatable-skip="true"]')) {
                return;
            }

            const hasComplexSpans = $table.find('tbody [rowspan], tbody [colspan]').length > 0;
            if (hasComplexSpans) {
                console.warn('Skipping DataTables initialization for table because it contains rowspans or colspans in the tbody.', table);
                return;
            }

            const defaultOptions = {
                responsive: true,
                autoWidth: false,
                paging: false,
                lengthChange: false,
                pageLength: -1,
                language: {
                    url: languageUrl,
                    searchPlaceholder: 'جستجو...',
                    emptyTable: 'داده‌ای برای نمایش وجود ندارد.'
                },
                columnDefs: [
                    { targets: 'no-sort', orderable: false },
                    { targets: 'no-search', searchable: false }
                ],
                drawCallback: function () {
                    const $wrapper = $(this.api().table().container());
                    convertWrapperDigits($wrapper);
                },
                initComplete: function () {
                    const $wrapper = $(this.api().table().container());
                    const $searchInput = $wrapper.find('input[type="search"]');
                    $searchInput.addClass('form-control form-control-sm rounded-pill').attr('placeholder', 'جستجو...');

                    const $lengthSelect = $wrapper.find('select');
                    $lengthSelect.addClass('form-select form-select-sm rounded-pill');

                    convertWrapperDigits($wrapper);
                }
            };

            const customOptions = $table.attr('data-datatable-options');
            let parsedOptions;
            let hasCustomDom = false;
            if (customOptions) {
                try {
                    parsedOptions = JSON.parse(customOptions);
                    hasCustomDom = Object.prototype.hasOwnProperty.call(parsedOptions, 'dom');
                } catch (error) {
                    console.warn('Invalid DataTable options on table', error);
                    parsedOptions = undefined;
                }
            }

            let options = $.extend(true, {}, defaultOptions, parsedOptions || {});

            options.dom = buildDom(options, hasCustomDom);

            const dataTable = $table.DataTable(options);

            if (dataTable.responsive && typeof dataTable.responsive.disable === 'function') {
                let responsiveEnabled = true;
                const attrValue = $table.data('responsiveDesktopMin');
                const optionValue = options.responsiveDesktopMin;
                const desktopMinWidth = parseInt(attrValue || optionValue, 10);

                if (!Number.isNaN(desktopMinWidth) && desktopMinWidth > 0) {
                    const $window = $(window);
                    const namespace = '.dt-responsive-toggle-' + Math.random().toString(36).slice(2, 10);

                    const toggleResponsive = function () {
                        const viewportWidth = window.innerWidth || document.documentElement.clientWidth;
                        if (viewportWidth >= desktopMinWidth) {
                            if (responsiveEnabled) {
                                dataTable.responsive.disable();
                                responsiveEnabled = false;
                                $table.removeClass('dtr-inline collapsed');
                                $table.find('th.dtr-hidden, td.dtr-hidden').removeClass('dtr-hidden');
                                $table.find('th.dtr-control, td.dtr-control').removeClass('dtr-control');
                                $table.find('tr').removeClass('parent');
                                dataTable.columns().every(function () {
                                    this.visible(true);
                                });
                                if (options.scrollX) {
                                    dataTable.columns.adjust();
                                }
                            }
                        } else {
                            if (!responsiveEnabled) {
                                dataTable.responsive.enable();
                                responsiveEnabled = true;
                                $table.addClass('dtr-inline collapsed');
                            }
                            dataTable.columns.adjust();
                            if (typeof dataTable.responsive.recalc === 'function') {
                                dataTable.responsive.recalc();
                            }
                        }
                    };

                    toggleResponsive();
                    $window.on('resize' + namespace, function () {
                        window.requestAnimationFrame(toggleResponsive);
                    });

                    $table.on('destroy.dt', function () {
                        $window.off('resize' + namespace);
                    });
                }
            }
        });
    });
})(jQuery);
