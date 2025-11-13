<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'ویرایش کاربر سازمانی';
$user = (class_exists('AuthHelper') && AuthHelper::getUser()) ? AuthHelper::getUser() : [
    'name' => 'کاربر سازمان',
    'email' => 'organization@example.com'
];
$additional_css = $additional_css ?? [];
$additional_js = $additional_js ?? [];
$inline_styles = $inline_styles ?? '';
$inline_scripts = $inline_scripts ?? '';

AuthHelper::startSession();

$validationErrors = $validationErrors ?? [];
$errorMessage = flash('error');
$organizationUser = $organizationUser ?? [];

$additional_css[] = 'public/themes/dashkote/plugins/select2/css/select2.min.css';
$additional_css[] = 'public/themes/dashkote/plugins/select2/css/select2-bootstrap4.css';
$additional_css[] = 'https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/css/persian-datepicker.min.css';
$additional_js[] = 'public/themes/dashkote/plugins/select2/js/select2.min.js';
$additional_js[] = 'public/themes/dashkote/js/form-select2.js';
$additional_js[] = 'https://cdn.jsdelivr.net/npm/persian-date@1.1.0/dist/persian-date.min.js';
$additional_js[] = 'https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js';

$inline_styles .= "
    body {
        background: #f5f7fb;
    }
    .organization-user-form .form-check {
        padding-right: 2.5rem;
        padding-left: 0;
    }
    .organization-user-form .form-check .form-check-input {
        margin-right: -2.5rem;
        margin-left: 0;
        float: right;
    }
    .organization-user-form .form-check-label {
        margin-right: 0.5rem;
    }
    .organization-user-form .form-check.form-switch {
        padding-right: 3.75rem;
    }
    .organization-user-form .form-check.form-switch .form-check-input {
        margin-right: -3.75rem;
    }
    .organization-user-form .form-label,
    .organization-user-form .form-check-label,
    .organization-user-form small {
        text-align: right;
    }
    .organization-user-form .form-control,
    .organization-user-form .form-select {
        text-align: right;
        direction: rtl;
    }
    .organization-user-form input[type=\"email\"],
    .organization-user-form input[type=\"password\"],
    .organization-user-form input[name=\"username\"],
    .organization-user-form input[name=\"evaluation_code\"],
    .organization-user-form input[name=\"personnel_code\"] {
        direction: ltr;
        text-align: left;
    }
    .organization-user-form .ltr-input {
        direction: ltr;
        text-align: left;
    }
    .organization-user-form .btn ion-icon {
        font-size: 18px;
    }
    .organization-user-form .date-picker-input {
        cursor: pointer;
        background-color: #ffffff;
    }
    .organization-user-form .form-section {
        border: 1px solid #e5e7eb;
        border-radius: 20px;
        background: #ffffff;
        padding: 20px 24px;
    }
    .organization-user-form .form-section + .form-section {
        margin-top: 16px;
    }
    .organization-user-form .form-section-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 16px;
    }
    .organization-user-form .form-section-title {
        font-weight: 600;
        color: #111827;
        margin: 0;
    }
    .organization-user-form .form-section-description {
        color: #6b7280;
        margin: 0;
        font-size: 0.9rem;
    }
    .organization-user-form #citySelect:disabled {
        background-color: #f9fafb;
        color: #9ca3af;
        cursor: not-allowed;
    }
";

include __DIR__ . '/../../layouts/organization-header.php';
include __DIR__ . '/../../layouts/organization-sidebar.php';

$navbarUser = $user;

$executiveUnits = $executiveUnits ?? [];
$serviceLocations = $serviceLocations ?? [];
$organizationPosts = $organizationPosts ?? [];
$iranProvinces = $iranProvinces ?? [];

$executiveUnitNames = array_values(array_filter(array_map(static function (array $unit): string {
    return trim((string) ($unit['name'] ?? ''));
}, $executiveUnits)));

$serviceLocationNames = array_values(array_filter(array_map(static function (array $location): string {
    return trim((string) ($location['name'] ?? ''));
}, $serviceLocations)));

$organizationPostNames = array_values(array_filter(array_map(static function (array $post): string {
    return trim((string) ($post['name'] ?? ''));
}, $organizationPosts)));

$currentExecutiveUnit = old('executive_devices', $organizationUser['executive_devices'] ?? '');
$currentServiceLocation = old('service_location', $organizationUser['service_location'] ?? '');
$currentOrganizationPost = old('organization_post', $organizationUser['organization_post'] ?? '');
$currentProvince = old('province', $organizationUser['province'] ?? '');
$currentCity = old('city', $organizationUser['city'] ?? '');

$provinceCityJson = json_encode($iranProvinces, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

$inline_scripts .= str_replace(
    '__PROVINCE_DATA__',
    $provinceCityJson,
    <<<'SCRIPT'
    document.addEventListener('DOMContentLoaded', function () {
        var provinceCities = __PROVINCE_DATA__;
        var provinceSelect = document.getElementById('provinceSelect');
        var citySelect = document.getElementById('citySelect');

        if (!provinceSelect || !citySelect) {
            return;
        }

        var hasjQuery = typeof window.jQuery !== 'undefined';
        var $provinceSelect = hasjQuery ? window.jQuery(provinceSelect) : null;
        var $citySelect = hasjQuery ? window.jQuery(citySelect) : null;
        var initialProvince = provinceSelect.dataset.selected || '';
        var initialCity = citySelect.dataset.selected || '';

        if ($provinceSelect && $citySelect && typeof $provinceSelect.select2 === 'function') {
            var placeholderProvince = 'انتخاب استان';
            var placeholderCityDefault = 'انتخاب شهر';
            var placeholderCityNoProvince = 'ابتدا استان را انتخاب کنید';

            function populateProvinceOptions() {
                $provinceSelect.empty();
                $provinceSelect.append(new Option(placeholderProvince, '', !initialProvince, !initialProvince));

                Object.keys(provinceCities).forEach(function (province) {
                    var isSelected = province === initialProvince;
                    $provinceSelect.append(new Option(province, province, isSelected, isSelected));
                });

                $provinceSelect.val(initialProvince || '').trigger('change.select2');
            }

            function populateCityOptions(selectedProvince) {
                var cities = Object.prototype.hasOwnProperty.call(provinceCities, selectedProvince) ? provinceCities[selectedProvince] : [];
                var hasProvince = cities.length > 0;

                $citySelect.empty();

                var placeholderText = hasProvince ? placeholderCityDefault : placeholderCityNoProvince;
                $citySelect.append(new Option(placeholderText, '', !initialCity, !initialCity));

                if (!hasProvince) {
                    if (initialCity) {
                        var legacyOption = new Option(initialCity, initialCity, true, true);
                        $citySelect.append(legacyOption);
                        $citySelect.prop('disabled', false);
                        $citySelect.val(initialCity).trigger('change.select2');
                        return;
                    }

                    $citySelect.prop('disabled', true).val('').trigger('change.select2');
                    return;
                }

                cities.forEach(function (city) {
                    var isSelected = initialCity === city;
                    $citySelect.append(new Option(city, city, isSelected, isSelected));
                });

                var selectedValue = '';

                if (initialCity && cities.indexOf(initialCity) !== -1) {
                    selectedValue = initialCity;
                }

                if (initialCity && selectedValue === '') {
                    var fallbackOption = new Option(initialCity + ' (قدیمی)', initialCity, true, true);
                    $citySelect.append(fallbackOption);
                    selectedValue = initialCity;
                }

                $citySelect.prop('disabled', false);
                $citySelect.val(selectedValue).trigger('change.select2');
                initialCity = selectedValue;
            }

            populateProvinceOptions();
            populateCityOptions(initialProvince);

            $provinceSelect.on('change', function () {
                initialProvince = this.value;
                initialCity = '';
                populateCityOptions(initialProvince);
            });

            $citySelect.on('change', function () {
                initialCity = this.value;
            });

            return;
        }

        function triggerNativeChange(element) {
            var event = new Event('change', { bubbles: false });
            element.dispatchEvent(event);
        }

        function setDisabled(selectEl, disabled) {
            if (disabled) {
                selectEl.setAttribute('disabled', 'disabled');
            } else {
                selectEl.removeAttribute('disabled');
            }

            selectEl.disabled = disabled;
        }

        function renderProvinceOptionsNative() {
            var fragment = document.createDocumentFragment();
            var defaultOption = new Option('انتخاب استان', '', initialProvince === '', initialProvince === '');
            fragment.appendChild(defaultOption);

            Object.keys(provinceCities).forEach(function (province) {
                var option = new Option(province, province, province === initialProvince, province === initialProvince);
                fragment.appendChild(option);
            });

            provinceSelect.innerHTML = '';
            provinceSelect.appendChild(fragment);
        }

        function renderCityOptionsNative(provinceValue) {
            var hasProvince = provinceValue && Object.prototype.hasOwnProperty.call(provinceCities, provinceValue);
            var fragment = document.createDocumentFragment();
            var defaultText = hasProvince ? 'انتخاب شهر' : 'ابتدا استان را انتخاب کنید';
            var defaultOption = new Option(defaultText, '', initialCity === '', initialCity === '');
            fragment.appendChild(defaultOption);

            if (!hasProvince) {
                citySelect.innerHTML = '';
                citySelect.appendChild(fragment);
                setDisabled(citySelect, true);

                if (initialCity) {
                    var legacyOption = new Option(initialCity, initialCity, true, true);
                    citySelect.appendChild(legacyOption);
                    setDisabled(citySelect, false);
                }

                triggerNativeChange(citySelect);
                return;
            }

            setDisabled(citySelect, false);

            provinceCities[provinceValue].forEach(function (city) {
                var option = new Option(city, city, initialCity === city, initialCity === city);
                fragment.appendChild(option);
            });

            citySelect.innerHTML = '';
            citySelect.appendChild(fragment);

            if (initialCity && provinceCities[provinceValue].indexOf(initialCity) === -1) {
                var fallbackOption = new Option(initialCity + ' (قدیمی)', initialCity, true, true);
                citySelect.appendChild(fallbackOption);
            }

            triggerNativeChange(citySelect);
        }

        renderProvinceOptionsNative();
        renderCityOptionsNative(initialProvince || provinceSelect.value);

        provinceSelect.addEventListener('change', function () {
            initialProvince = provinceSelect.value;
            initialCity = '';
            renderCityOptionsNative(initialProvince);
        });

        citySelect.addEventListener('change', function () {
            initialCity = citySelect.value;
        });
    });
SCRIPT
);
$inline_scripts .= "\n";

$inline_scripts .= <<<'SCRIPT'
    document.addEventListener('DOMContentLoaded', function () {
    var dateSelectors = ['#expirationDate', '#reportDate', '#letterDate'];

        dateSelectors.forEach(function (selector) {
            var inputElement = document.querySelector(selector);
            if (!inputElement) {
                return;
            }

            inputElement.setAttribute('autocomplete', 'off');
            if (!inputElement.classList.contains('date-picker-input')) {
                inputElement.classList.add('date-picker-input');
            }
        });

        var $ = window.jQuery;
        if (typeof $ === 'undefined' || typeof $.fn.persianDatepicker !== 'function') {
            dateSelectors.forEach(function (selector) {
                var fallbackInput = document.querySelector(selector);
                if (fallbackInput) {
                    fallbackInput.removeAttribute('readonly');
                }
            });
            return;
        }

        dateSelectors.forEach(function (selector) {
            var $input = $(selector);
            if (!$input.length) {
                return;
            }

            var currentValue = $input.val();
            $input.prop('readOnly', true);

            $input.persianDatepicker({
                format: 'YYYY/MM/DD',
                initialValue: currentValue !== '',
                initialValueType: 'persian',
                autoClose: true,
                persianDigit: false,
                calendar: {
                    persian: {
                        locale: 'fa',
                        leapYearMode: 'astronomical'
                    }
                },
                toolbox: {
                    calendarSwitch: {
                        enabled: false
                    },
                    todayButton: {
                        enabled: true,
                        text: 'امروز'
                    },
                    submitButton: {
                        enabled: true,
                        text: 'تأیید'
                    }
                },
                navigator: {
                    enabled: true,
                    nextText: 'بعدی',
                    prevText: 'قبلی'
                },
                timePicker: {
                    enabled: false
                }
            });
        });
    });
SCRIPT;
$inline_scripts .= "\n";

$checkboxState = static function (string $key, string $fallback = '0') use ($organizationUser): bool {
    $default = $fallback;
    if (isset($organizationUser[$key])) {
        $default = ((int) $organizationUser[$key] === 1) ? '1' : '0';
    }

    return old($key, $default) === '1';
};
?>

<?php include __DIR__ . '/../../layouts/organization-navbar.php'; ?>

<div class="page-content-wrapper">
    <div class="page-content">
        <div class="row g-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-24 h-100">
                    <div class="card-body p-24">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-16 mb-24">
                            <div class="flex-grow-1 ">
                                <h2 class="mb-6 text-gray-900">ویرایش کاربر</h2>
                                <p class="text-gray-500 mb-0">اطلاعات کاربر سازمانی را به‌روزرسانی کنید. برای حفظ رمز عبور فعلی، فیلد رمز را خالی بگذارید.</p>
                            </div>
                            <div class="d-flex gap-10 flex-wrap">
                                <a href="<?= UtilityHelper::baseUrl('organizations/users'); ?>" class="btn btn-outline-gray rounded-pill px-24 d-flex align-items-center gap-8">
                                    <ion-icon name="arrow-undo-outline"></ion-icon>
                                    بازگشت به لیست
                                </a>
                                <a href="<?= UtilityHelper::baseUrl('organizations/users/create'); ?>" class="btn btn-outline-main rounded-pill px-24 d-flex align-items-center gap-8">
                                    <ion-icon name="person-add-outline"></ion-icon>
                                    ایجاد کاربر جدید
                                </a>
                            </div>
                        </div>

                        <?php if (!empty($errorMessage)): ?>
                            <div class="alert alert-danger rounded-16 d-flex align-items-center gap-12" role="alert">
                                <ion-icon name="warning-outline"></ion-icon>
                                <span><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        <?php endif; ?>

                        <form action="<?= UtilityHelper::baseUrl('organizations/users/update'); ?>" method="post" class="organization-user-form" enctype="multipart/form-data">
                            <?= csrf_field(); ?>
                            <input type="hidden" name="user_id" value="<?= htmlspecialchars((string) ($organizationUser['id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">

                            <div class="form-section">
                                <div class="form-section-header">
                                    <h5 class="form-section-title">اطلاعات حساب کاربری</h5>
                                    <p class="form-section-description">نام کاربری و ایمیل کاربر را بررسی کنید و در صورت ضرورت رمز عبور را تغییر دهید.</p>
                                </div>
                                <div class="row g-16">
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">نام کاربری <span class="text-danger">*</span></label>
                                        <input type="text" name="username" class="form-control" value="<?= htmlspecialchars(old('username', $organizationUser['username'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: user001" required>
                                        <?php if (!empty($validationErrors['username'])): ?>
                                            <small class="text-danger d-block mt-6"><?= htmlspecialchars($validationErrors['username'], ENT_QUOTES, 'UTF-8'); ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">رمز عبور</label>
                                        <input type="password" name="password" class="form-control" placeholder="در صورت عدم تغییر، خالی بگذارید">
                                        <?php if (!empty($validationErrors['password'])): ?>
                                            <small class="text-danger d-block mt-6"><?= htmlspecialchars($validationErrors['password'], ENT_QUOTES, 'UTF-8'); ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">ایمیل</label>
                                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars(old('email', $organizationUser['email'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="example@mail.com">
                                        <?php if (!empty($validationErrors['email'])): ?>
                                            <small class="text-danger d-block mt-6"><?= htmlspecialchars($validationErrors['email'], ENT_QUOTES, 'UTF-8'); ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <div class="form-section-header">
                                    <h5 class="form-section-title">اطلاعات هویتی و ارزیابی</h5>
                                    <p class="form-section-description">جزئیات هویتی و کدهای ارزیابی کاربر را تکمیل کنید.</p>
                                </div>
                                <div class="row g-16">
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">نام <span class="text-danger">*</span></label>
                                        <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars(old('first_name', $organizationUser['first_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: علی" required>
                                        <?php if (!empty($validationErrors['first_name'])): ?>
                                            <small class="text-danger d-block mt-6"><?= htmlspecialchars($validationErrors['first_name'], ENT_QUOTES, 'UTF-8'); ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">نام خانوادگی <span class="text-danger">*</span></label>
                                        <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars(old('last_name', $organizationUser['last_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: رضایی" required>
                                        <?php if (!empty($validationErrors['last_name'])): ?>
                                            <small class="text-danger d-block mt-6"><?= htmlspecialchars($validationErrors['last_name'], ENT_QUOTES, 'UTF-8'); ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">نام پدر</label>
                                        <input type="text" name="father_name" class="form-control" value="<?= htmlspecialchars(old('father_name', $organizationUser['father_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: یونس">
                                        <?php if (!empty($validationErrors['father_name'])): ?>
                                            <small class="text-danger d-block mt-6"><?= htmlspecialchars($validationErrors['father_name'], ENT_QUOTES, 'UTF-8'); ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">جنسیت</label>
                                        <?php $selectedGender = old('gender', $organizationUser['gender'] ?? ''); ?>
                                        <select name="gender" class="form-select">
                                            <option value="">انتخاب نشده</option>
                                            <option value="male" <?= $selectedGender === 'male' ? 'selected' : ''; ?>>مرد</option>
                                            <option value="female" <?= $selectedGender === 'female' ? 'selected' : ''; ?>>زن</option>
                                            <option value="other" <?= $selectedGender === 'other' ? 'selected' : ''; ?>>سایر</option>
                                        </select>
                                        <?php if (!empty($validationErrors['gender'])): ?>
                                            <small class="text-danger d-block mt-6"><?= htmlspecialchars($validationErrors['gender'], ENT_QUOTES, 'UTF-8'); ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">کد ارزیابی <span class="text-danger">*</span></label>
                                        <input type="text" name="evaluation_code" class="form-control ltr-input" value="<?= htmlspecialchars(old('evaluation_code', $organizationUser['evaluation_code'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: EVA-001" required>
                                        <?php if (!empty($validationErrors['evaluation_code'])): ?>
                                            <small class="text-danger d-block mt-6"><?= htmlspecialchars($validationErrors['evaluation_code'], ENT_QUOTES, 'UTF-8'); ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">کد ملی <span class="text-danger">*</span></label>
                                        <input type="text" name="national_code" class="form-control ltr-input" value="<?= htmlspecialchars(old('national_code', $organizationUser['national_code'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: ۰۰۱۲۳۴۵۶۷۸" required>
                                        <?php if (!empty($validationErrors['national_code'])): ?>
                                            <small class="text-danger d-block mt-6"><?= htmlspecialchars($validationErrors['national_code'], ENT_QUOTES, 'UTF-8'); ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">کد پرسنلی <span class="text-danger">*</span></label>
                                        <input type="text" name="personnel_code" class="form-control ltr-input" value="<?= htmlspecialchars(old('personnel_code', $organizationUser['personnel_code'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: PRS-452" required>
                                        <?php if (!empty($validationErrors['personnel_code'])): ?>
                                            <small class="text-danger d-block mt-6"><?= htmlspecialchars($validationErrors['personnel_code'], ENT_QUOTES, 'UTF-8'); ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">شماره نامه</label>
                                        <input type="text" name="letter_number" class="form-control ltr-input" value="<?= htmlspecialchars(old('letter_number', $organizationUser['letter_number'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: ۳۲۰/۱۴۲۲">
                                        <?php if (!empty($validationErrors['letter_number'])): ?>
                                            <small class="text-danger d-block mt-6"><?= htmlspecialchars($validationErrors['letter_number'], ENT_QUOTES, 'UTF-8'); ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">تاریخ نامه</label>
                                        <input type="text" name="letter_date" id="letterDate" class="form-control date-picker-input" value="<?= htmlspecialchars(old('letter_date', $organizationUser['letter_date'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: ۱۴۰۴/۰۸/۰۳" autocomplete="off" readonly>
                                        <?php if (!empty($validationErrors['letter_date'])): ?>
                                            <small class="text-danger d-block mt-6"><?= htmlspecialchars($validationErrors['letter_date'], ENT_QUOTES, 'UTF-8'); ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="row g-16 mt-0">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">تصویر نامه</label>
                                        <input type="file" name="letter_image" class="form-control" accept="image/*">
                                        <small class="text-muted d-block mt-6">فرمت‌های مجاز: JPG، PNG، GIF یا WEBP</small>
                                        <?php if (!empty($validationErrors['letter_image'])): ?>
                                            <small class="text-danger d-block mt-6"><?= htmlspecialchars($validationErrors['letter_image'], ENT_QUOTES, 'UTF-8'); ?></small>
                                        <?php endif; ?>
                                        <?php $existingLetterImage = $organizationUser['letter_image_path'] ?? null; ?>
                                        <?php if (!empty($existingLetterImage)): ?>
                                            <div class="mt-8">
                                                <a href="<?= UtilityHelper::baseUrl('public/' . ltrim($existingLetterImage, '/')); ?>" target="_blank" class="d-inline-flex align-items-center gap-6 text-decoration-none">
                                                    <ion-icon name="open-outline"></ion-icon>
                                                    <span>مشاهده تصویر فعلی</span>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <div class="form-section-header">
                                    <h5 class="form-section-title">جایگاه سازمانی</h5>
                                    <p class="form-section-description">اطلاعات سازمانی کاربر را از گزینه‌های موجود انتخاب کنید.</p>
                                </div>
                                <div class="row g-16">
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">واحد سازمانی</label>
                                        <select name="executive_devices" class="form-select">
                                            <option value="">انتخاب واحد سازمانی</option>
                                            <?php foreach ($executiveUnits as $unit): ?>
                                                <?php
                                                    $unitName = trim((string) ($unit['name'] ?? ''));
                                                    if ($unitName === '') {
                                                        continue;
                                                    }
                                                ?>
                                                <option value="<?= htmlspecialchars($unitName, ENT_QUOTES, 'UTF-8'); ?>" <?= $currentExecutiveUnit === $unitName ? 'selected' : ''; ?>>
                                                    <?= htmlspecialchars($unitName, ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                            <?php if ($currentExecutiveUnit !== '' && !in_array($currentExecutiveUnit, $executiveUnitNames, true)): ?>
                                                <option value="<?= htmlspecialchars($currentExecutiveUnit, ENT_QUOTES, 'UTF-8'); ?>" selected><?= htmlspecialchars($currentExecutiveUnit, ENT_QUOTES, 'UTF-8'); ?> (قدیمی)</option>
                                            <?php endif; ?>
                                            <?php if (empty($executiveUnits)): ?>
                                                <option value="" disabled>واحد سازمانی ثبت نشده است.</option>
                                            <?php endif; ?>
                                        </select>
                                        <small class="text-muted d-block mt-6">برای افزودن واحد جدید به بخش «واحدهای سازمانی» مراجعه کنید.</small>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">محل خدمت</label>
                                        <select name="service_location" class="form-select">
                                            <option value="">انتخاب محل خدمت</option>
                                            <?php foreach ($serviceLocations as $location): ?>
                                                <?php
                                                    $locationName = trim((string) ($location['name'] ?? ''));
                                                    if ($locationName === '') {
                                                        continue;
                                                    }
                                                    $locationCode = trim((string) ($location['code'] ?? ''));
                                                    $locationLabel = $locationCode !== '' ? $locationName . ' (' . $locationCode . ')' : $locationName;
                                                ?>
                                                <option value="<?= htmlspecialchars($locationName, ENT_QUOTES, 'UTF-8'); ?>" <?= $currentServiceLocation === $locationName ? 'selected' : ''; ?>>
                                                    <?= htmlspecialchars($locationLabel, ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                            <?php if ($currentServiceLocation !== '' && !in_array($currentServiceLocation, $serviceLocationNames, true)): ?>
                                                <option value="<?= htmlspecialchars($currentServiceLocation, ENT_QUOTES, 'UTF-8'); ?>" selected><?= htmlspecialchars($currentServiceLocation, ENT_QUOTES, 'UTF-8'); ?> (قدیمی)</option>
                                            <?php endif; ?>
                                            <?php if (empty($serviceLocations)): ?>
                                                <option value="" disabled>محل خدمتی ثبت نشده است.</option>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">پست سازمانی</label>
                                        <select name="organization_post" class="form-select">
                                            <option value="">انتخاب پست سازمانی</option>
                                            <?php foreach ($organizationPosts as $post): ?>
                                                <?php
                                                    $postName = trim((string) ($post['name'] ?? ''));
                                                    if ($postName === '') {
                                                        continue;
                                                    }
                                                ?>
                                                <option value="<?= htmlspecialchars($postName, ENT_QUOTES, 'UTF-8'); ?>" <?= $currentOrganizationPost === $postName ? 'selected' : ''; ?>>
                                                    <?= htmlspecialchars($postName, ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                            <?php if ($currentOrganizationPost !== '' && !in_array($currentOrganizationPost, $organizationPostNames, true)): ?>
                                                <option value="<?= htmlspecialchars($currentOrganizationPost, ENT_QUOTES, 'UTF-8'); ?>" selected><?= htmlspecialchars($currentOrganizationPost, ENT_QUOTES, 'UTF-8'); ?> (قدیمی)</option>
                                            <?php endif; ?>
                                            <?php if (empty($organizationPosts)): ?>
                                                <option value="" disabled>پست سازمانی ثبت نشده است.</option>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <div class="form-section-header">
                                    <h5 class="form-section-title">اطلاعات جغرافیایی و زمان‌بندی</h5>
                                    <p class="form-section-description">استان و شهر محل خدمت و تاریخ‌های مرتبط را مدیریت کنید.</p>
                                </div>
                                <div class="row g-16">
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold" for="provinceSelect">استان</label>
                                        <select name="province" id="provinceSelect" class="form-select single-select w-100" data-selected="<?= htmlspecialchars($currentProvince, ENT_QUOTES, 'UTF-8'); ?>" data-placeholder="انتخاب استان" data-allow-clear="true">
                                            <option value="">انتخاب استان</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold" for="citySelect">شهر</label>
                                        <select name="city" id="citySelect" class="form-select single-select w-100" data-selected="<?= htmlspecialchars($currentCity, ENT_QUOTES, 'UTF-8'); ?>" data-placeholder="انتخاب شهر" data-allow-clear="true" <?= $currentProvince === '' ? 'disabled' : ''; ?>>
                                            <option value="">ابتدا استان را انتخاب کنید</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">تاریخ انقضا</label>
                                        <input type="text" name="expiration_date" id="expirationDate" class="form-control date-picker-input" value="<?= htmlspecialchars(old('expiration_date', $organizationUser['expiration_date'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: ۱۴۰۴/۰۶/۳۱" autocomplete="off" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">تاریخ گزارش</label>
                                        <input type="text" name="report_date" id="reportDate" class="form-control date-picker-input" value="<?= htmlspecialchars(old('report_date', $organizationUser['report_date'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: ۱۴۰۴/۰۱/۱۵" autocomplete="off" readonly>
                                    </div>
                                    <div class="col-md-4 d-flex align-items-end">
                                        <div class="form-check form-switch ms-auto">
                                            <input class="form-check-input" type="checkbox" role="switch" id="showReportDate" name="show_report_date_instead_of_calendar" value="1" <?= $checkboxState('show_report_date_instead_of_calendar') ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="showReportDate">نمایش تاریخ گزارش به جای تقویم</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <div class="form-section-header">
                                    <h5 class="form-section-title">نقش‌ها و وضعیت</h5>
                                    <p class="form-section-description">وضعیت فعال بودن حساب و نقش‌های سامانه‌ای کاربر را مشخص کنید.</p>
                                </div>
                                <div class="row g-16">
                                    <div class="col-md-2 col-sm-4 col-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="is_system_admin" id="isSystemAdmin" value="1" <?= $checkboxState('is_system_admin') ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="isSystemAdmin">ادمین سیستم</label>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="is_manager" id="isManager" value="1" <?= $checkboxState('is_manager') ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="isManager">مدیر</label>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="is_evaluee" id="isEvaluee" value="1" <?= $checkboxState('is_evaluee') ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="isEvaluee">ارزیابی‌شونده</label>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="is_evaluator" id="isEvaluator" value="1" <?= $checkboxState('is_evaluator') ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="isEvaluator">ارزیاب</label>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1" <?= $checkboxState('is_active', '1') ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="isActive">فعال</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-12 mt-28">
                                <button type="submit" class="btn btn-main rounded-pill px-32 d-flex align-items-center gap-8">
                                    <ion-icon name="save-outline"></ion-icon>
                                    به‌روزرسانی کاربر
                                </button>
                                <a href="<?= UtilityHelper::baseUrl('organizations/users'); ?>" class="btn btn-outline-gray rounded-pill px-28">انصراف</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    <?php include __DIR__ . '/../../layouts/organization-footer.php'; ?>
</div>
