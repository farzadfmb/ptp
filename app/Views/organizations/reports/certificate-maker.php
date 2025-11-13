<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'گواهی ساز';
$isReadonly = isset($isReadonly) ? (bool) $isReadonly : false;
$successMessage = $successMessage ?? null;
$errorMessage = $errorMessage ?? null;
$logoInputValue = isset($logoInputValue) ? (string) $logoInputValue : '';
$formAction = isset($formAction) ? (string) $formAction : UtilityHelper::baseUrl('organizations/reports/certificate-maker');
$csrfToken = isset($csrfToken) ? (string) $csrfToken : AuthHelper::generateCsrfToken();
$sampleCertificate = isset($sampleCertificate) && is_array($sampleCertificate) ? $sampleCertificate : [];

$certificateLogoUrl = isset($sampleCertificate['logo_url']) ? (string) $sampleCertificate['logo_url'] : UtilityHelper::baseUrl('public/assets/images/logo/favicon.png');
$organizationName = isset($sampleCertificate['organization_name']) ? (string) $sampleCertificate['organization_name'] : 'سازمان امور مالیاتی کشور';
$organizationSubtitle = isset($sampleCertificate['organization_subtitle']) ? (string) $sampleCertificate['organization_subtitle'] : 'کانون ارزیابی و توسعه شایستگی';
$certificateTitle = isset($sampleCertificate['title']) ? (string) $sampleCertificate['title'] : 'گواهی‌نامه';
$certificateSubtitle = isset($sampleCertificate['title_secondary']) ? (string) $sampleCertificate['title_secondary'] : 'صلاحیت عمومی مدیران حرفه‌ای';
$certificateNumber = isset($sampleCertificate['certificate_number']) ? (string) $sampleCertificate['certificate_number'] : '۳۲۴۰/۷۹۴۰';
$issueDateLabel = isset($sampleCertificate['issue_date']) ? (string) $sampleCertificate['issue_date'] : UtilityHelper::englishToPersian(date('Y/m/d'));
$certificateBodyTemplate = isset($certificateBodyTemplate)
    ? (string) $certificateBodyTemplate
    : (isset($sampleCertificate['certificate_body']) ? (string) $sampleCertificate['certificate_body'] : 'بدینوسیله گواهی می‌شود متن اصلی گواهی را در این قسمت بنویسید.');
$certificateBodyRendered = isset($certificateBodyRendered)
    ? (string) $certificateBodyRendered
    : preg_replace_callback('/{{\s*([a-zA-Z0-9_]+)\s*}}/u', function ($matches) use ($sampleCertificate) {
        $key = $matches[1];
        return isset($sampleCertificate[$key]) ? (string) $sampleCertificate[$key] : '';
    }, $certificateBodyTemplate);
$subjectTitle = isset($sampleCertificate['subject_title']) ? (string) $sampleCertificate['subject_title'] : 'آقای';
if (!in_array($subjectTitle, ['آقای', 'خانم'], true)) {
    $subjectTitle = 'آقای';
}
$subjectName = isset($sampleCertificate['subject_name']) ? (string) $sampleCertificate['subject_name'] : 'مختار سلیمانی‌کیا';
$subjectFatherName = isset($sampleCertificate['subject_father_name']) ? (string) $sampleCertificate['subject_father_name'] : 'یونس';
$subjectNationalId = isset($sampleCertificate['subject_national_id']) ? (string) $sampleCertificate['subject_national_id'] : '۰۵۹۶۲۸۹۰۸۱';
$letterNumber = isset($sampleCertificate['letter_number']) ? (string) $sampleCertificate['letter_number'] : '۳۲۰/۱۴۲۲';
$letterDate = isset($sampleCertificate['letter_date']) ? (string) $sampleCertificate['letter_date'] : $issueDateLabel;
$signatureRightName = isset($sampleCertificate['signature_right_name']) ? (string) $sampleCertificate['signature_right_name'] : 'نام امضاءکننده راست';
$signatureRightTitle = isset($sampleCertificate['signature_right_title']) ? (string) $sampleCertificate['signature_right_title'] : 'سمت امضاءکننده راست';
$signatureLeftName = isset($sampleCertificate['signature_left_name']) ? (string) $sampleCertificate['signature_left_name'] : 'نام امضاءکننده چپ';
$signatureLeftTitle = isset($sampleCertificate['signature_left_title']) ? (string) $sampleCertificate['signature_left_title'] : 'سمت امضاءکننده چپ';
$logoPosition = isset($sampleCertificate['logo_position']) ? (string) $sampleCertificate['logo_position'] : 'right';
$metaPosition = isset($sampleCertificate['meta_position']) ? (string) $sampleCertificate['meta_position'] : ($logoPosition === 'left' ? 'right' : 'left');
$logoPosition = in_array($logoPosition, ['left', 'right'], true) ? $logoPosition : 'right';
$metaPosition = in_array($metaPosition, ['left', 'right'], true) ? $metaPosition : ($logoPosition === 'left' ? 'right' : 'left');

$additional_css = $additional_css ?? [];
$additional_js = $additional_js ?? [];
$inline_styles = $inline_styles ?? '';
$inline_scripts = $inline_scripts ?? '';

$inline_styles .= <<<CSS
    @font-face {
        font-family: 'IranNastaliq';
        src: url('https://cdn.jsdelivr.net/gh/rastikerdar/irannastaliq-font@v1.101/dist/FarsiNastaliq.woff2') format('woff2'),
             url('https://cdn.jsdelivr.net/gh/rastikerdar/irannastaliq-font@v1.101/dist/FarsiNastaliq.woff') format('woff');
        font-weight: 400;
        font-style: normal;
        font-display: swap;
    }
    .certificate-maker-wrapper {
        background: #f5f8fb;
        min-height: 100vh;
    }
    .certificate-preview-card {
        border: none;
        background: linear-gradient(120deg, #ffffff 0%, #f3f8ff 40%, #eef7ff 100%);
    }
    .certificate-canvas {
        font-family: 'IranNastaliq', 'Vazirmatn', Tahoma, sans-serif;
        position: relative;
        background: #fcfeff;
        border-radius: 28px;
        padding: 56px 64px;
        border: 2px solid rgba(15, 118, 110, 0.25);
        box-shadow: 0 16px 40px rgba(15, 23, 42, 0.08);
        overflow: hidden;
        direction: rtl;
    }
    .certificate-canvas::before,
    .certificate-canvas::after {
        content: '';
        position: absolute;
        left: 0;
        right: 0;
        height: 18px;
        pointer-events: none;
    }
    .certificate-canvas::before {
        top: 0;
        background: linear-gradient(90deg, #006a4e 0%, #0f9d58 45%, #fbbc05 100%);
    }
    .certificate-canvas::after {
        bottom: 0;
        background: linear-gradient(90deg, #fbbc05 0%, #0f9d58 55%, #006a4e 100%);
    }
    .certificate-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 24px;
        margin-bottom: 36px;
    }
    .certificate-meta {
        display: flex;
        flex-direction: column;
        gap: 10px;
        font-size: 15px;
        color: #0f172a;
        font-weight: 600;
    }
    .certificate-meta span.meta-label {
        font-weight: 700;
        margin-left: 6px;
        color: #1f2937;
    }
    .certificate-meta span.meta-value {
        font-family: 'Vazirmatn', Tahoma, sans-serif;
        font-weight: 700;
    }
    .certificate-logo-wrapper {
        text-align: right;
    }
    .certificate-top.logo-left .certificate-logo-wrapper {
        text-align: left;
    }
    .certificate-top.meta-left .certificate-meta {
        order: 1;
        text-align: left;
        align-items: flex-start;
    }
    .certificate-top.meta-left .certificate-logo-wrapper {
        order: 2;
    }
    .certificate-top.meta-right .certificate-meta {
        order: 2;
        text-align: right;
        align-items: flex-end;
    }
    .certificate-top.meta-right .certificate-logo-wrapper {
        order: 1;
    }
    .certificate-logo-wrapper img {
        width: 110px;
        height: 110px;
        object-fit: contain;
    }
    .certificate-canvas [data-field] {
        font-family: 'Vazirmatn', Tahoma, sans-serif;
        font-weight: 700;
    }
    .certificate-canvas [data-field="certificate_body"] {
        line-height: 2.4;
    }
    .certificate-title-block {
        text-align: center;
        margin-bottom: 32px;
    }
    .certificate-title {
        font-size: 40px;
        font-weight: 800;
        color: #7f1d1d;
        letter-spacing: -1px;
    }
    .certificate-title-secondary {
        font-size: 26px;
        font-weight: 700;
        color: #991b1b;
        margin-top: 8px;
    }
    .certificate-body-text {
        font-size: 17px;
        line-height: 2.4;
        color: #1e293b;
        text-align: justify;
        margin: 0 auto;
        max-width: 90%;
    }
    .certificate-signatures {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        margin-top: 52px;
        gap: 24px;
    }
    .certificate-signature {
        text-align: center;
        min-width: 200px;
    }
    .certificate-signature .signature-name {
        font-size: 18px;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 6px;
    }
    .certificate-signature .signature-title {
        font-size: 15px;
        color: #475569;
    }
    .certificate-actions {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    @media (max-width: 991px) {
        .certificate-canvas {
            padding: 40px 32px;
        }
        .certificate-top {
            flex-direction: column-reverse;
            align-items: stretch;
        }
        .certificate-top.meta-left .certificate-logo-wrapper,
        .certificate-top.meta-right .certificate-logo-wrapper {
            order: 2;
        }
        .certificate-top.meta-left .certificate-meta,
        .certificate-top.meta-right .certificate-meta {
            order: 1;
            text-align: center;
            align-items: center;
        }
        .certificate-logo-wrapper {
            text-align: center;
        }
        .certificate-body-text {
            max-width: 100%;
        }
        .certificate-signatures {
            flex-direction: column;
            align-items: center;
        }
    }
CSS;

$inline_scripts .= <<<'JS'
    document.addEventListener('DOMContentLoaded', function () {
        const previewRoot = document.querySelector('[data-role="certificate-preview"]');
        if (!previewRoot) {
            return;
        }

        const formRoot = document.querySelector('[data-role="certificate-form"]');

        const findCertificateStyles = function () {
            const styleTags = Array.from(document.querySelectorAll('style'));
            for (let i = 0; i < styleTags.length; i++) {
                const styleEl = styleTags[i];
                const content = styleEl.textContent || '';
                if (content.indexOf('.certificate-maker-wrapper') !== -1 && content.indexOf('.certificate-canvas') !== -1) {
                    return content;
                }
            }
            return '';
        };

        const openPrintPopup = function () {
            const printContainer = document.getElementById('certificate-print-root');
            if (!printContainer) {
                window.print();
                return;
            }

            const printWindow = window.open('', 'certificate_print_popup', 'width=1024,height=768,scrollbars=yes,resizable=yes');
            if (!printWindow || printWindow.closed) {
                alert('مرورگر مانع باز شدن پنجره چاپ شد. لطفاً پاپ‌آپ را مجاز کنید.');
                return;
            }

            const clone = printContainer.cloneNode(true);
            const canvasElement = clone.querySelector('.certificate-canvas');
            const innerHtml = canvasElement ? canvasElement.outerHTML : clone.innerHTML;
            const styleContent = findCertificateStyles();

            const printHtml = `<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>چاپ گواهی</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css">
    <style>
        html, body {
            margin: 0;
            padding: 0;
            background: #ffffff;
        }
        @page {
            size: A4 landscape;
            margin: 15mm;
        }
        .print-wrapper {
            width: 100%;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }
        .print-wrapper .certificate-canvas {
            width: 100%;
            max-width: 297mm;
            min-height: calc(210mm - 40mm);
            box-shadow: none !important;
            border-width: 0 !important;
            background: #ffffff !important;
            margin: 0 auto;
            padding: 18mm 22mm !important;
            border-radius: 0 !important;
        }
        .print-wrapper .certificate-top,
        .print-wrapper .certificate-title-block,
        .print-wrapper .certificate-body-text,
        .print-wrapper .certificate-signatures {
            max-width: 100% !important;
        }
${styleContent}
        @media print {
            html, body {
                width: 297mm;
                height: 210mm;
            }
        }
    </style>
</head>
<body>
    <div class="print-wrapper">${innerHtml}</div>
    <script>
        window.addEventListener('load', function () {
            window.focus();
            window.print();
            setTimeout(function () {
                window.close();
            }, 500);
        });
    <\/script>
</body>
</html>`;

            printWindow.document.open();
            printWindow.document.write(printHtml);
            printWindow.document.close();
        };

        const setupPrintHandling = function () {
            const printButtons = document.querySelectorAll('[data-action="print-certificate"]');
            if (printButtons.length === 0) {
                return;
            }

            printButtons.forEach(function (button) {
                button.addEventListener('click', function (event) {
                    event.preventDefault();
                    openPrintPopup();
                });
            });
        };

        setupPrintHandling();

        if (!formRoot) {
            return;
        }

        const escapeHtml = function (value) {
            if (value === null || value === undefined) {
                return '';
            }

            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        };

        const bodyFieldName = 'certificate_body';
        const textControls = formRoot.querySelectorAll('[data-control]');
        const imageControls = formRoot.querySelectorAll('[data-control-image]');
        const layoutControls = formRoot.querySelectorAll('[data-control-layout]');
        const initialControlValues = {};
        const fieldDefaults = {};
        const fieldValues = {};
        const initialImageValues = {};
        const initialImageSources = {};
        const initialLayoutValues = {};
        const layoutAppliers = {};

        const renderTemplate = function (template) {
            if (template === null || template === undefined) {
                return '';
            }

            const stringTemplate = String(template);
            let result = '';
            let lastIndex = 0;
            const regex = /{{\s*([a-zA-Z0-9_]+)\s*}}/g;
            let match;

            while ((match = regex.exec(stringTemplate)) !== null) {
                if (match.index > lastIndex) {
                    result += escapeHtml(stringTemplate.slice(lastIndex, match.index));
                }

                const key = match[1];
                const replacement = fieldValues[key] !== undefined && fieldValues[key] !== ''
                    ? fieldValues[key]
                    : (fieldDefaults[key] !== undefined ? fieldDefaults[key] : '');
                result += escapeHtml(replacement);

                lastIndex = regex.lastIndex;
            }

            if (lastIndex < stringTemplate.length) {
                result += escapeHtml(stringTemplate.slice(lastIndex));
            }

            return result;
        };

        const refreshCertificateBody = function () {
            const bodyTarget = previewRoot.querySelector('[data-field="' + bodyFieldName + '"]');
            if (!bodyTarget) {
                return;
            }

            const templateValue = fieldValues[bodyFieldName] !== undefined && fieldValues[bodyFieldName] !== ''
                ? fieldValues[bodyFieldName]
                : (fieldDefaults[bodyFieldName] !== undefined ? fieldDefaults[bodyFieldName] : '');

            const rendered = renderTemplate(templateValue);
            bodyTarget.innerHTML = rendered.replace(/\n/g, '<br>');
        };

        textControls.forEach(function (control) {
            const field = control.getAttribute('data-control');
            if (!field) {
                return;
            }

            initialControlValues[field] = control.value;

            const targets = previewRoot.querySelectorAll('[data-field="' + field + '"]');
            const firstTarget = targets.length > 0 ? targets[0] : null;
            const defaultAttr = control.getAttribute('data-default');
            const defaultFromTarget = firstTarget ? firstTarget.textContent : '';
            const defaultValue = defaultAttr !== null ? defaultAttr : (defaultFromTarget !== '' ? defaultFromTarget : control.value);
            fieldDefaults[field] = defaultValue;

            if (field === bodyFieldName) {
                fieldValues[field] = control.value;
                const handleBodyChange = function () {
                    fieldValues[field] = control.value;
                    refreshCertificateBody();
                };
                control.addEventListener('input', handleBodyChange);
                control.addEventListener('change', handleBodyChange);
                return;
            }

            fieldValues[field] = control.value !== '' ? control.value : (fieldDefaults[field] !== undefined ? fieldDefaults[field] : '');

            const applyValue = function () {
                const raw = control.value.trim();
                const chosen = raw === '' ? (fieldDefaults[field] !== undefined ? fieldDefaults[field] : '') : raw;
                fieldValues[field] = chosen;
                targets.forEach(function (target) {
                    target.innerHTML = escapeHtml(chosen).replace(/\n/g, '<br>');
                });
                if (fieldValues[bodyFieldName] !== undefined) {
                    refreshCertificateBody();
                }
            };

            applyValue();
            control.addEventListener('input', applyValue);
            control.addEventListener('change', applyValue);
        });

        if (fieldValues[bodyFieldName] === undefined) {
            const bodyControl = formRoot.querySelector('[data-control="' + bodyFieldName + '"]');
            const fallback = bodyControl ? bodyControl.value : '';
            fieldDefaults[bodyFieldName] = fallback;
            fieldValues[bodyFieldName] = fallback;
        }

        refreshCertificateBody();

        imageControls.forEach(function (control) {
            const field = control.getAttribute('data-control-image');
            const target = previewRoot.querySelector('[data-field-image="' + field + '"]');
            if (!target) {
                return;
            }
            const defaultSrc = target.getAttribute('data-original-src') || target.getAttribute('src');

            initialImageValues[field] = control.value;
            initialImageSources[field] = defaultSrc;

            const applyValue = function () {
                const raw = control.value.trim();
                target.setAttribute('src', raw !== '' ? raw : defaultSrc);
            };

            control.addEventListener('input', applyValue);
            control.addEventListener('change', applyValue);
        });

        layoutControls.forEach(function (control) {
            const selector = control.getAttribute('data-target-selector') || '';
            const target = selector !== '' ? previewRoot.querySelector(selector) : null;
            if (!target) {
                return;
            }

            const key = control.name || selector;
            initialLayoutValues[key] = control.value;

            const layoutType = control.getAttribute('data-layout-type') || 'logo';

            const applyLayout = function () {
                const rawValue = control.value === 'left' ? 'left' : 'right';
                if (layoutType === 'meta') {
                    target.classList.remove('meta-left', 'meta-right');
                    target.classList.add(rawValue === 'left' ? 'meta-left' : 'meta-right');
                    target.setAttribute('data-meta-position', rawValue);
                } else {
                    target.classList.remove('logo-left', 'logo-right');
                    target.classList.add(rawValue === 'left' ? 'logo-left' : 'logo-right');
                    target.setAttribute('data-logo-position', rawValue);
                }
            };

            layoutAppliers[key] = applyLayout;
            applyLayout();
            control.addEventListener('change', applyLayout);
        });

        const handleReset = function (event) {
            event.preventDefault();
            textControls.forEach(function (control) {
                const field = control.getAttribute('data-control');
                if (!field) {
                    return;
                }
                control.value = initialControlValues[field] ?? '';
                control.dispatchEvent(new Event('input', { bubbles: true }));
                control.dispatchEvent(new Event('change', { bubbles: true }));
            });
            imageControls.forEach(function (control) {
                const field = control.getAttribute('data-control-image');
                const target = previewRoot.querySelector('[data-field-image="' + field + '"]');
                if (!target) {
                    return;
                }
                const initialValue = initialImageValues[field] ?? '';
                const defaultSrc = initialImageSources[field] || target.getAttribute('data-original-src') || target.getAttribute('src');
                control.value = initialValue;
                const raw = control.value.trim();
                target.setAttribute('src', raw !== '' ? raw : defaultSrc);
            });

            layoutControls.forEach(function (control) {
                const selector = control.getAttribute('data-target-selector') || '';
                const key = control.name || selector;
                if (!(key in initialLayoutValues)) {
                    return;
                }
                control.value = initialLayoutValues[key];
                const apply = layoutAppliers[key];
                if (typeof apply === 'function') {
                    apply();
                }
            });

            refreshCertificateBody();
        };

        document.querySelectorAll('[data-action="reset"]').forEach(function (button) {
            button.addEventListener('click', handleReset);
        });

        const openPreviewPopup = function () {
            const popup = window.open('', 'certificate_full_preview', 'width=1200,height=800,scrollbars=yes,resizable=yes');
            if (!popup || popup.closed) {
                alert('مرورگر مانع باز شدن پنجره جدید شد. لطفاً پاپ‌آپ را مجاز کنید.');
                return;
            }

            const clone = previewRoot.cloneNode(true);
            clone.removeAttribute('data-role');

            const wrapper = document.createElement('div');
            wrapper.className = 'preview-popup-wrapper';
            wrapper.appendChild(clone);

            const styleContent = findCertificateStyles();
            const popupHtml = `<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>پیش‌نمایش گواهی</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css">
    <style>
        body {
            margin: 0;
            background: #f1f5f9;
            color: #0f172a;
            font-family: 'IranNastaliq', 'Vazirmatn', Tahoma, sans-serif;
        }
        .preview-popup-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px 32px;
        }
        .preview-popup-wrapper .certificate-preview-card {
            box-shadow: 0 24px 64px rgba(15, 23, 42, 0.2);
            border-radius: 24px;
        }
${styleContent}
    </style>
</head>
<body>
${wrapper.innerHTML}
</body>
</html>`;
            popup.document.open();
            popup.document.write(popupHtml);
            popup.document.close();
        };

        const handlePreview = function (event) {
            event.preventDefault();
            openPreviewPopup();
        };

        document.querySelectorAll('[data-action="preview"]').forEach(function (button) {
            button.addEventListener('click', handlePreview);
        });
    });
JS;

include __DIR__ . '/../../layouts/organization-header.php';
include __DIR__ . '/../../layouts/organization-sidebar.php';
include __DIR__ . '/../../layouts/organization-navbar.php';
?>

<div class="page-content-wrapper certificate-maker-wrapper">
    <div class="page-content">
        <div class="row g-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-24 mb-0">
                    <div class="card-body p-24">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-12">
                            <div>
                                <h2 class="mb-8"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></h2>
                                <?php if (!$isReadonly): ?>
                                    <p class="text-muted mb-0">در این بخش می‌توانید متن‌های گواهی پایان دوره را شخصی‌سازی کرده و پیش‌نمایش آن را مشاهده کنید.</p>
                                <?php endif; ?>
                            </div>
                            <?php if (!$isReadonly): ?>
                                <div class="certificate-actions">
                                    <button type="submit" form="certificate-maker-form" class="btn btn-success d-flex align-items-center gap-6">
                                        <ion-icon name="save-outline"></ion-icon>
                                        ذخیره تنظیمات
                                    </button>
                                    <button type="button" class="btn btn-outline-primary d-flex align-items-center gap-6" data-action="preview">
                                        <ion-icon name="eye-outline"></ion-icon>
                                        پیش‌نمایش کامل
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary d-flex align-items-center gap-6" data-action="reset">
                                        <ion-icon name="refresh-outline"></ion-icon>
                                        بازنشانی مقادیر نمونه
                                    </button>
                                </div>
                            <?php endif; ?>
                            <?php if ($isReadonly): ?>
                                <div class="certificate-actions">
                                    <button type="button" class="btn btn-main d-flex align-items-center gap-6" data-action="print-certificate">
                                        <ion-icon name="print-outline"></ion-icon>
                                        چاپ گواهی
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($successMessage)): ?>
                            <div class="alert alert-success mt-16 mb-0">
                                <?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($errorMessage)): ?>
                            <div class="alert alert-danger mt-16 mb-0">
                                <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 align-items-start mt-1">
            <div class="<?= $isReadonly ? 'col-12' : 'col-xl-7'; ?>">
                <div id="certificate-print-root">
                    <div class="certificate-preview-card card shadow-sm rounded-24" data-role="certificate-preview">
                        <div class="card-body p-32">
                            <div class="certificate-canvas">
                            <div class="certificate-top <?= $logoPosition === 'left' ? 'logo-left' : 'logo-right'; ?> <?= $metaPosition === 'right' ? 'meta-right' : 'meta-left'; ?>" data-role="certificate-top">
                                <div class="certificate-meta">
                                    <div class="certificate-meta-row">
                                        <span class="meta-label">شماره:</span>
                                        <span class="meta-value" data-field="certificate_number"><?= htmlspecialchars($certificateNumber, ENT_QUOTES, 'UTF-8'); ?></span>
                                    </div>
                                    <div class="certificate-meta-row">
                                        <span class="meta-label">تاریخ:</span>
                                        <span class="meta-value" data-field="issue_date"><?= htmlspecialchars($issueDateLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                                    </div>
                                </div>
                                <div class="certificate-logo-wrapper">
                                    <img src="<?= htmlspecialchars($certificateLogoUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="لوگوی سازمان" data-field-image="logo_url" data-original-src="<?= htmlspecialchars($certificateLogoUrl, ENT_QUOTES, 'UTF-8'); ?>">
                                </div>
                            </div>
                            <div class="certificate-title-block">
                                <div class="certificate-title" data-field="title"><?= htmlspecialchars($certificateTitle, ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="certificate-title-secondary" data-field="title_secondary"><?= htmlspecialchars($certificateSubtitle, ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="certificate-body-text" data-field="certificate_body"><?= nl2br(htmlspecialchars($certificateBodyRendered, ENT_QUOTES, 'UTF-8')); ?></div>
                            <div class="certificate-signatures">
                                <div class="certificate-signature">
                                    <div class="signature-name" data-field="signature_right_name"><?= htmlspecialchars($signatureRightName, ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="signature-title" data-field="signature_right_title"><?= htmlspecialchars($signatureRightTitle, ENT_QUOTES, 'UTF-8'); ?></div>
                                </div>
                                <div class="certificate-signature">
                                    <div class="signature-name" data-field="signature_left_name"><?= htmlspecialchars($signatureLeftName, ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="signature-title" data-field="signature_left_title"><?= htmlspecialchars($signatureLeftTitle, ENT_QUOTES, 'UTF-8'); ?></div>
                                </div>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php if (!$isReadonly): ?>
                <div class="col-xl-5">
                    <div class="card border-0 shadow-sm rounded-24">
                        <form method="post" action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8'); ?>" class="card-body p-24" data-role="certificate-form" id="certificate-maker-form">
                        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                        <h5 class="mb-20">ویرایش متن گواهی</h5>
                        <div class="mb-16">
                            <label class="form-label">عنوان اصلی</label>
                            <input type="text" class="form-control" name="title" data-control="title" placeholder="مثال: گواهی‌نامه" value="<?= htmlspecialchars($certificateTitle, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="mb-16">
                            <label class="form-label">عنوان فرعی</label>
                            <input type="text" class="form-control" name="title_secondary" data-control="title_secondary" placeholder="مثال: صلاحیت عمومی مدیران حرفه‌ای" value="<?= htmlspecialchars($certificateSubtitle, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="row g-3">
                            <div class="col-sm-4">
                                <label class="form-label">عنوان فرد</label>
                                <select class="form-select" name="subject_title" data-control="subject_title">
                                    <option value="آقای"<?= $subjectTitle === 'آقای' ? ' selected' : ''; ?>>آقای</option>
                                    <option value="خانم"<?= $subjectTitle === 'خانم' ? ' selected' : ''; ?>>خانم</option>
                                </select>
                            </div>
                            <div class="col-sm-8">
                                <label class="form-label">نام و نام خانوادگی</label>
                                <input type="text" class="form-control" name="subject_name" data-control="subject_name" placeholder="مثال: مختار سلیمانی‌کیا" value="<?= htmlspecialchars($subjectName, ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-sm-6">
                                <label class="form-label">نام پدر</label>
                                <input type="text" class="form-control" name="subject_father_name" data-control="subject_father_name" placeholder="مثال: یونس" value="<?= htmlspecialchars($subjectFatherName, ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">کد ملی</label>
                                <input type="text" class="form-control" name="subject_national_id" data-control="subject_national_id" placeholder="مثال: ۰۵۹۶۲۸۹۰۸۱" value="<?= htmlspecialchars($subjectNationalId, ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-sm-6">
                                <label class="form-label">نام سازمان در متن</label>
                                <input type="text" class="form-control" name="organization_name" data-control="organization_name" placeholder="مثال: اداره کل امور مالیاتی استان آذربایجان شرقی" value="<?= htmlspecialchars($organizationName, ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">شماره نامه</label>
                                <input type="text" class="form-control" name="letter_number" data-control="letter_number" placeholder="مثال: ۳۲۰/۱۴۲۲" value="<?= htmlspecialchars($letterNumber, ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-sm-6">
                                <label class="form-label">تاریخ نامه</label>
                                <input type="text" class="form-control" name="letter_date" data-control="letter_date" placeholder="مثال: ۱۴۰۴/۰۸/۰۳" value="<?= htmlspecialchars($letterDate, ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">شماره گواهی</label>
                                <input type="text" class="form-control" name="certificate_number" data-control="certificate_number" placeholder="مثال: ۳۲۴۰/۷۹۴۰" value="<?= htmlspecialchars($certificateNumber, ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-sm-6">
                                <label class="form-label">تاریخ گواهی</label>
                                <input type="text" class="form-control" name="issue_date" data-control="issue_date" placeholder="مثال: ۱۴۰۴/۰۸/۰۳" value="<?= htmlspecialchars($issueDateLabel, ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>
                        <div class="mb-16 mt-1">
                            <label class="form-label">متن اصلی گواهی</label>
                            <textarea class="form-control" rows="6" name="certificate_body" data-control="certificate_body" placeholder="می‌توانید از علامت‌های {{...}} برای جایگذاری خودکار مقادیر استفاده کنید."><?= htmlspecialchars($certificateBodyTemplate, ENT_QUOTES, 'UTF-8'); ?></textarea>
                            <div class="form-text">نمونه متغیرها: {{subject_title}} ، {{subject_name}} ، {{subject_father_name}} ، {{subject_national_id}} ، {{letter_number}} ، {{letter_date}} ، {{organization_name}}</div>
                        </div>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label">نام امضاءکننده راست</label>
                                <input type="text" class="form-control" name="signature_right_name" data-control="signature_right_name" placeholder="مثال: دکتر اشکان میرَقی" value="<?= htmlspecialchars($signatureRightName, ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">سمت امضاءکننده راست</label>
                                <input type="text" class="form-control" name="signature_right_title" data-control="signature_right_title" placeholder="مثال: مدیر کانون ارزیابی" value="<?= htmlspecialchars($signatureRightTitle, ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-sm-6">
                                <label class="form-label">نام امضاءکننده چپ</label>
                                <input type="text" class="form-control" name="signature_left_name" data-control="signature_left_name" placeholder="مثال: حسین درخوشی" value="<?= htmlspecialchars($signatureLeftName, ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">سمت امضاءکننده چپ</label>
                                <input type="text" class="form-control" name="signature_left_title" data-control="signature_left_title" placeholder="مثال: معاون توسعه مدیریت و منابع" value="<?= htmlspecialchars($signatureLeftTitle, ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>
                        <div class="mt-16">
                            <label class="form-label">آدرس لوگو (اختیاری)</label>
                            <input type="url" class="form-control" name="logo_url" data-control-image="logo_url" placeholder="آدرس تصویر لوگو" value="<?= htmlspecialchars($logoInputValue, ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="form-text">در صورت خالی بودن، لوگوی فعلی نمایش داده می‌شود.</div>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-sm-6">
                                <label class="form-label">جایگاه لوگو</label>
                                <select class="form-select" name="logo_position" data-control-layout data-layout-type="logo" data-target-selector="[data-role='certificate-top']">
                                    <option value="right"<?= $logoPosition === 'right' ? ' selected' : ''; ?>>لوگو سمت راست</option>
                                    <option value="left"<?= $logoPosition === 'left' ? ' selected' : ''; ?>>لوگو سمت چپ</option>
                                </select>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">جایگاه شماره و تاریخ</label>
                                <select class="form-select" name="meta_position" data-control-layout data-layout-type="meta" data-target-selector="[data-role='certificate-top']">
                                    <option value="left"<?= $metaPosition === 'left' ? ' selected' : ''; ?>>سمت چپ</option>
                                    <option value="right"<?= $metaPosition === 'right' ? ' selected' : ''; ?>>سمت راست</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-24 d-grid gap-2">
                            <button type="submit" class="btn btn-success d-flex align-items-center gap-6 justify-content-center">
                                <ion-icon name="save-outline"></ion-icon>
                                ذخیره تغییرات
                            </button>
                            <button type="button" class="btn btn-outline-primary" data-action="reset">بازنشانی به مقادیر اولیه</button>
                            <button type="button" class="btn btn-main" data-action="preview">پیش‌نمایش کامل</button>
                        </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/organization-footer.php'; ?>
