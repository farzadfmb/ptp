(function ($) {
    'use strict';

    if (typeof $ === 'undefined' || !$.fn || !$.fn.select2) {
        return;
    }

    $.fn.select2.defaults.set('theme', 'bootstrap4');
    $.fn.select2.defaults.set('dir', 'rtl');

    $.fn.select2.defaults.set('language', {
        errorLoading: function () { return 'بارگذاری نتایج امکان‌پذیر نیست.'; },
        inputTooLong: function (args) {
            var overChars = args.input.length - args.maximum;
            return 'لطفاً ' + overChars + ' کاراکتر را حذف کنید.';
        },
        inputTooShort: function (args) {
            var remainingChars = args.minimum - args.input.length;
            return 'لطفاً حداقل ' + remainingChars + ' کاراکتر وارد کنید.';
        },
        loadingMore: function () { return 'در حال بارگذاری نتایج بیشتر...'; },
        maximumSelected: function (args) {
            return 'شما فقط می‌توانید ' + args.maximum + ' مورد را انتخاب کنید.';
        },
        noResults: function () { return 'هیچ نتیجه‌ای یافت نشد.'; },
        searching: function () { return 'در حال جستجو...'; }
    });

    $(function () {
        $('.js-searchable-multiselect').each(function () {
            var $element = $(this);

            if (!$element.hasClass('multiple-select')) {
                $element.addClass('multiple-select');
            }

            if (!$element.attr('data-width')) {
                $element.attr('data-width', '100%');
            }

            if (!$element.attr('data-placeholder')) {
                var placeholder = $element.attr('placeholder') || '';
                if (placeholder !== '') {
                    $element.attr('data-placeholder', placeholder);
                }
            }
        });
    });
})(window.jQuery);
