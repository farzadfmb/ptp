<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$baseTitle = $title ?? 'گواهی پایان دوره';
$orgName = $orgName ?? '';
$orgLogoUrl = $orgLogoUrl ?? UtilityHelper::baseUrl('public/assets/images/logo/logo.png');

$fullName = $fullName ?? '';
$evaluationTitle = $evaluationTitle ?? '';
$competencyModelDisplay = $competencyModelDisplay ?? '—';
$evaluationDateDisplay = $dateMeta['display'] ?? '—';

// Settings prepared by controller (with defaults)
$certificateSettings = $certificateSettings ?? [
    'title_ribbon_text' => 'گواهی پایان دوره',
    'statement_text' => 'گزارش بازخورد',
    'template_key' => 'classic',
    'show_org_logo' => 1,
    'show_signatures' => 1,
    'enable_decorations' => 1,
    'pdf_mode' => 'simple',
    'extra_footer_text' => null,
];
$_titleRibbon = (string)($certificateSettings['title_ribbon_text'] ?? 'گواهی پایان دوره');
$_statement = (string)($certificateSettings['statement_text'] ?? 'گزارش بازخورد');
$_templateKey = (string)($certificateSettings['template_key'] ?? 'classic');
$_showLogo = (int)($certificateSettings['show_org_logo'] ?? 1) === 1;
$_showSigns = (int)($certificateSettings['show_signatures'] ?? 1) === 1;
$_decor = (int)($certificateSettings['enable_decorations'] ?? 1) === 1;
$_pdfMode = (string)($certificateSettings['pdf_mode'] ?? 'simple');
$_extraFooter = $certificateSettings['extra_footer_text'] ?? null;
$_enableSecond = (int)($certificateSettings['enable_second_page'] ?? 0) === 1;
$_secondImg = $certificateSettings['second_page_image_path'] ?? null;
$_secondImgW = $certificateSettings['second_page_image_width_mm'] ?? null;
$_secondImgH = $certificateSettings['second_page_image_height_mm'] ?? null;
$_enableThird = (int)($certificateSettings['enable_third_page'] ?? 0) === 1;
$_thirdImg = $certificateSettings['third_page_image_path'] ?? null;
$_thirdImgW = $certificateSettings['third_page_image_width_mm'] ?? null;
$_thirdImgH = $certificateSettings['third_page_image_height_mm'] ??  null;
$_thirdItems = $certificateSettings['third_page_items'] ?? [];
// Fourth page
$_enableFourth = (int)($certificateSettings['enable_fourth_page'] ?? 0) === 1;
$_fourthImg = $certificateSettings['fourth_page_image_path'] ?? null;
$_fourthImgW = $certificateSettings['fourth_page_image_width_mm'] ?? null;
$_fourthImgH = $certificateSettings['fourth_page_image_height_mm'] ??  null;
// Fifth page
$_enableFifth = (int)($certificateSettings['enable_fifth_page'] ?? 0) === 1;
$_fifthText = isset($certificateSettings['fifth_page_text']) ? ltrim((string)$certificateSettings['fifth_page_text']) : '';
$_modelImage = $competencyModelImagePath ?? null;
$_fourthAlign = (string)($certificateSettings['fourth_page_text_align'] ?? '');
$_fifthAlign = (string)($certificateSettings['fifth_page_text_align'] ?? '');
// Sixth page
$_enableSixth = (int)($certificateSettings['enable_sixth_page'] ?? 0) === 1;
$_sixthText = isset($certificateSettings['sixth_page_text']) ? ltrim((string)$certificateSettings['sixth_page_text']) : '';
$_modelCompetencies = isset($modelCompetenciesForPage6) && is_array($modelCompetenciesForPage6) ? $modelCompetenciesForPage6 : [];
$_sixthAlign = (string)($certificateSettings['sixth_page_text_align'] ?? '');
// Seventh page
$_enableSeventh = (int)($certificateSettings['enable_seventh_page'] ?? 0) === 1;
$_seventhText = isset($certificateSettings['seventh_page_text']) ? ltrim((string)$certificateSettings['seventh_page_text']) : '';
$_seventhImg = $certificateSettings['seventh_page_image_path'] ?? null;
$_seventhAlign = (string)($certificateSettings['seventh_page_text_align'] ?? '');
// Eighth page
$_enableEighth = (int)($certificateSettings['enable_eighth_page'] ?? 0) === 1;
$_page8Tools = isset($certificateSettings['page8_tools']) && is_array($certificateSettings['page8_tools']) ? $certificateSettings['page8_tools'] : [];
// Ninth page
$_enableNinth = (int)($certificateSettings['enable_ninth_page'] ?? 0) === 1;
$_ninthText = isset($certificateSettings['ninth_page_text']) ? ltrim((string)$certificateSettings['ninth_page_text']) : '';
$_ninthAlign = (string)($certificateSettings['ninth_page_text_align'] ?? '');
$_ninthItems = isset($certificateSettings['ninth_page_items']) && is_array($certificateSettings['ninth_page_items']) ? $certificateSettings['ninth_page_items'] : [];
// Tenth page (MBTI intro)
$_enableTenth = (int)($certificateSettings['enable_tenth_page'] ?? 0) === 1;
$_tenthText = isset($certificateSettings['tenth_page_text']) ? ltrim((string)$certificateSettings['tenth_page_text']) : '';
$_tenthAlign = (string)($certificateSettings['tenth_page_text_align'] ?? '');
// Eleventh page (MBTI result)
$_enableEleventh = (int)($certificateSettings['enable_eleventh_page'] ?? 0) === 1;
$_eleventhText = isset($certificateSettings['eleventh_page_text']) ? ltrim((string)$certificateSettings['eleventh_page_text']) : '';
$_eleventhAlign = (string)($certificateSettings['eleventh_page_text_align'] ?? '');
$_mbti = isset($certificateSettings['mbti']) && is_array($certificateSettings['mbti']) ? $certificateSettings['mbti'] : ['has_mbti'=>false];
// Page order from settings (slugs: 'details' => page 2, 'toc' => page 3). If not set, use enabled ones in default order
$_pageOrder = isset($certificateSettings['page_order']) && is_array($certificateSettings['page_order']) ? $certificateSettings['page_order'] : [];
if (empty($_pageOrder)) {
    if ($_enableSecond) { $_pageOrder[] = 'details'; }
    if ($_enableThird) { $_pageOrder[] = 'toc'; }
    if ($_enableFourth) { $_pageOrder[] = 'page4'; }
    if ($_enableFifth) { $_pageOrder[] = 'page5'; }
    if ($_enableSixth) { $_pageOrder[] = 'page6'; }
    if ($_enableSeventh) { $_pageOrder[] = 'page7'; }
    if ($_enableEighth) { $_pageOrder[] = 'page8'; }
    if ($_enableNinth) { $_pageOrder[] = 'page9'; }
    if ($_enableTenth) { $_pageOrder[] = 'page10'; }
    if ($_enableEleventh) { $_pageOrder[] = 'page11'; }
}

?><!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= htmlspecialchars($baseTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <!-- Persian webfont: Vazirmatn -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/vazirmatn@33.0.1/Vazirmatn-font-face.css">
    <!-- Client-side PDF libs -->
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
    <style>
        /* Landscape A4 */
        @page { size: A4 landscape; margin: 15mm; }
        html, body { height: 100%; }
        body {
            font-family: Vazirmatn, Tahoma, IRANSans, system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, Arial, "Helvetica Neue", "Noto Color Emoji", sans-serif;
            background: #eef2f7;
            color: #0f172a;
            margin: 0;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
        }
        :root{
            --ink:#0f172a;
            --muted:#64748b;
            --border:#e2e8f0;
            --accent:#0ea5e9; /* cyan */
            --accent-2:#10b981; /* emerald */
            --gold:#eab308;
        }
        .page {
            width: 297mm;
            min-height: 210mm;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 14px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 16px 40px rgba(15, 23, 42, 0.12);
            /* subtle background pattern */
            background-image: radial-gradient(ellipse at 120% -10%, rgba(14,165,233,0.08), rgba(16,185,129,0.06) 40%, transparent 60%),
                              repeating-linear-gradient(135deg, rgba(226,232,240,0.35) 0 2px, transparent 2px 6px);
            background-size: cover;
        }
        /* Template variants */
        .template-classic .page { box-shadow: 0 16px 40px rgba(15, 23, 42, 0.12); }
        .template-classic .page { background-image: radial-gradient(ellipse at 120% -10%, rgba(14,165,233,0.08), rgba(16,185,129,0.06) 40%, transparent 60%), repeating-linear-gradient(135deg, rgba(226,232,240,0.35) 0 2px, transparent 2px 6px); }
        .template-minimal .page { box-shadow: none; background-image: none; border: 1px solid var(--border); }
        .template-minimal .title-ribbon { background: #0f172a; }
        .template-minimal .certificate-body { border: 1px solid var(--border); }
        .template-bordered .page { background-image: none; border: 1.5px solid var(--border); }
        .template-bordered .certificate-body { border: 2px solid var(--border); }
        .page::before, .page::after{
            content:""; position:absolute; inset:auto; pointer-events:none;
        }
        /* decorative corner accents */
    .page.decor::before{ right:-40mm; top:-40mm; width:120mm; height:120mm; border-radius:50%; background: radial-gradient(circle at 30% 30%, rgba(14,165,233,0.18), transparent 60%);} 
    .page.decor::after{ left:-50mm; bottom:-50mm; width:140mm; height:140mm; border-radius:50%; background: radial-gradient(circle at 70% 70%, rgba(16,185,129,0.15), transparent 60%);} 

        .page-inner { padding: 18mm 22mm; position: relative; z-index: 1; }

        .brand {
            display: flex; align-items: center; gap: 10px;
            position: absolute; top: 12mm; left: 14mm;
            background: rgba(255,255,255,0.75);
            backdrop-filter: blur(4px);
            border: 1px solid var(--border);
            border-radius: 999px;
            padding: 6px 10px;
        }
        .brand img { height: 26px; width: auto; object-fit: contain; opacity: 0.9; }
        .brand .org-name { font-size: 12px; color: var(--muted); white-space: nowrap; }

        .header { text-align: center; margin-top: 18mm; }
        .title-ribbon {
            display: inline-flex; align-items: center; gap: 10px;
            background: linear-gradient(90deg, var(--accent), var(--accent-2));
            color: #fff; padding: 10px 22px; border-radius: 999px; font-weight: 800; letter-spacing: 0;
            box-shadow: 0 10px 24px rgba(14,165,233,0.25);
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
        }
        .subtitle { margin-top: 8px; font-size: 13px; color: var(--muted); }

        /* framed body */
        .certificate-body {
            margin-top: 14mm;
            padding: 14mm 18mm;
            position: relative;
            border-radius: 12px;
            background: #fff;
            border: 1.5px solid var(--border);
        }
        .certificate-body::before{
            content:""; position:absolute; inset:10px; border-radius: 10px;
            border: 1px dashed rgba(148,163,184,0.55);
        }

        .statement { text-align: center; margin-bottom: 10mm; color: var(--muted); font-size: 14px; }
        .recipient { text-align: center; font-size: 24px; font-weight: 800; color: var(--ink); }
        .separator { width: 90px; height: 4px; border-radius: 4px; margin: 6mm auto 10mm; background: linear-gradient(90deg, var(--gold), transparent); }

        .grid { display: grid; grid-template-columns: 180px 1fr; gap: 8mm 14mm; max-width: 200mm; margin: 0 auto; }
        .label { color: #475569; font-size: 13px; }
        .value { font-size: 16px; font-weight: 700; color: var(--ink); }

        .signatures { display: flex; justify-content: center; gap: 30mm; margin-top: 14mm; }
        .sign { text-align: center; }
        .sign .line { width: 70mm; height: 0; border-top: 2px solid var(--border); margin: 12mm auto 4mm; }
        .sign .caption { font-size: 12px; color: #475569; }

        .footer {
            position: absolute; bottom: 10mm; left: 0; right: 0;
            display: flex; justify-content: space-between; align-items: center; padding: 0 22mm; color: var(--muted); font-size: 11px;
        }

        .actions { position: fixed; top: 12px; right: 12px; display: none; gap:8px; }
        .btn-print { background: var(--accent); color:#fff; border:none; padding:8px 14px; border-radius:10px; cursor:pointer; box-shadow:0 8px 16px rgba(14,165,233,0.25); }
        .btn-print:hover{ filter: brightness(1.05); }
        @media screen { .actions { display: inline-flex; } }
        @media print { body { background: #fff; } .page { box-shadow: none; border-radius: 0; } .actions { display: none !important; } }

        /* Simplified look for PDF export to prevent layout shifts */
        .pdf-export .page {
            box-shadow: none !important;
            background: #ffffff !important;
            background-image: none !important;
            border-radius: 0 !important;
        }
        .pdf-export .page::before,
        .pdf-export .page::after { display: none !important; }
        .pdf-export .brand { background: transparent !important; backdrop-filter: none !important; border: none !important; }
        .pdf-export .title-ribbon { background: var(--accent) !important; box-shadow: none !important; }
        .pdf-export .certificate-body { border: 1px solid var(--border) !important; }
        .pdf-export .certificate-body::before { display: none !important; }
    </style>
    <script>
        window.addEventListener('load', function() {
            // Auto-open print dialog (optional). Comment out if not desired.
            // window.print();
        });
    </script>
    </head>
<body>
    <div class="template-<?= htmlspecialchars($_templateKey, ENT_QUOTES, 'UTF-8'); ?>">
    <div class="page<?= $_decor ? ' decor' : ''; ?> certificate-page" data-page-index="1">
        <div class="actions">
            <button class="btn-print" onclick="window.print()">چاپ</button>
            <button class="btn-print" style="background:#10b981" onclick="exportCertificatePdf()">دانلود PDF</button>
        </div>
        <?php if ($_showLogo): ?>
        <div class="brand">
            <img src="<?= htmlspecialchars($orgLogoUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="Organization Logo" crossorigin="anonymous" />
            <?php if (!empty($orgName)): ?>
                <span class="org-name"><?= htmlspecialchars($orgName, ENT_QUOTES, 'UTF-8'); ?></span>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="page-inner">
            <div class="header">
                <div class="title-ribbon"><?= htmlspecialchars($_titleRibbon, ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="subtitle">این گواهی تأیید می‌کند که اطلاعات زیر مربوط به دوره و آزمون‌های تکمیل‌شده است.</div>
                <?php $isPreview = isset($_GET['preview']) && in_array((string)$_GET['preview'], ['1','true','yes'], true); if ($isPreview): ?>
                    <div class="subtitle" style="color:#ef4444; font-weight:700;">حالت پیش‌نمایش (ممکن است برخی آزمون‌ها کامل نشده باشند)</div>
                <?php endif; ?>
            </div>

            <div class="certificate-body">
                <div class="statement"><?= htmlspecialchars($_statement, ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="recipient"><?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="separator"></div>

                <div class="grid">
                    <div class="label">عنوان ارزیابی</div>
                    <div class="value"><?= htmlspecialchars($evaluationTitle, ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="label">مدل شایستگی</div>
                    <div class="value"><?= htmlspecialchars($competencyModelDisplay, ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="label">تاریخ ارزیابی</div>
                    <div class="value"><?= htmlspecialchars($evaluationDateDisplay, ENT_QUOTES, 'UTF-8'); ?></div>
                </div>

                <?php if ($_showSigns): ?>
                <div class="signatures">
                    <div class="sign">
                        <div class="line"></div>
                        <div class="caption">تایید مسئول دوره</div>
                    </div>
                    <div class="sign">
                        <div class="line"></div>
                        <div class="caption">مهر سازمان</div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="footer">
                <div>سازمان: <?= htmlspecialchars($orgName ?: '—', ENT_QUOTES, 'UTF-8'); ?></div>
                <div>
                    <span>تاریخ چاپ: <?= UtilityHelper::englishToPersian(date('Y/m/d')); ?></span>
                    <?php if (!empty($_extraFooter)): ?>
                        <span class="mx-2">|</span>
                        <span><?= htmlspecialchars($_extraFooter, ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php $pageIndex = 2; foreach ($_pageOrder as $slug): if ($slug==='details' && !$_enableSecond) continue; if ($slug==='toc' && !$_enableThird) continue; if ($slug==='page4' && !$_enableFourth) continue; if ($slug==='page5' && !$_enableFifth) continue; if ($slug==='page6' && !$_enableSixth) continue; if ($slug==='page7' && !$_enableSeventh) continue; if ($slug==='page8' && !$_enableEighth) continue; if ($slug==='page9' && !$_enableNinth) continue; if ($slug==='page10' && !$_enableTenth) continue; if ($slug==='page11' && !$_enableEleventh) continue; ?>
    <?php if ($slug !== 'page8' && $slug !== 'page6' && $slug !== 'page9'): ?>
    <div class="page<?= $_decor ? ' decor' : ''; ?> certificate-page" data-page-index="<?= (int)$pageIndex; ?>">
        <?php if ($_showLogo): ?>
        <div class="brand">
            <img src="<?= htmlspecialchars($orgLogoUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="Organization Logo" crossorigin="anonymous" />
            <?php if (!empty($orgName)): ?>
                <span class="org-name"><?= htmlspecialchars($orgName, ENT_QUOTES, 'UTF-8'); ?></span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <div class="page-inner">
            <div class="header">
                <?php if ($slug==='details'): ?>
                    <div class="title-ribbon">
                        <?php $page2Ribbon = trim((string)($certificateSettings['second_page_title_ribbon_text'] ?? '')); if ($page2Ribbon==='') { $page2Ribbon='جزئیات ارزیابی‌شونده'; } echo htmlspecialchars($page2Ribbon, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                    <div class="subtitle">این صفحه شامل مشخصات کاربر و تصویر پیوست‌شده است.</div>
                <?php elseif ($slug==='toc'): ?>
                    <div class="title-ribbon">
                        <?php $page3Ribbon = trim((string)($certificateSettings['third_page_title_ribbon_text'] ?? '')); if ($page3Ribbon==='') { $page3Ribbon='فهرست گواهی'; } echo htmlspecialchars($page3Ribbon, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                    <div class="subtitle">فهرست مطالب این گزارش/گواهی</div>
                <?php elseif ($slug==='page4'): ?>
                    <div class="title-ribbon">
                        <?php $page4Ribbon = trim((string)($certificateSettings['fourth_page_title_ribbon_text'] ?? '')); if ($page4Ribbon==='') { $page4Ribbon='متن تکمیلی'; } echo htmlspecialchars($page4Ribbon, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                    <div class="subtitle">مطالب تکمیلی گزارش</div>
                <?php elseif ($slug==='page5'): ?>
                    <div class="title-ribbon">
                        <?php $page5Ribbon = trim((string)($certificateSettings['fifth_page_title_ribbon_text'] ?? '')); if ($page5Ribbon==='') { $page5Ribbon='مدل شایستگی'; } echo htmlspecialchars($page5Ribbon, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                    <div class="subtitle">متن آزاد + تصویر مدل شایستگی انتخاب‌شده</div>
                <?php elseif ($slug==='page6'): ?>
                    <div class="title-ribbon">
                        <?php $page6Ribbon = trim((string)($certificateSettings['sixth_page_title_ribbon_text'] ?? '')); if ($page6Ribbon==='') { $page6Ribbon='شایستگی‌ها و تعاریف'; } echo htmlspecialchars($page6Ribbon, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                    <div class="subtitle">متن توضیحی + جدول شایستگی‌ها و تعریف شایستگی‌ها</div>
                <?php elseif ($slug==='page7'): ?>
                    <div class="title-ribbon">
                        <?php $page7Ribbon = trim((string)($certificateSettings['seventh_page_title_ribbon_text'] ?? '')); if ($page7Ribbon==='') { $page7Ribbon='متن و تصویر'; } echo htmlspecialchars($page7Ribbon, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                    <div class="subtitle">متن آزاد + تصویر (در صورت وجود)</div>
                <?php elseif ($slug==='page8'): ?>
                    <div class="title-ribbon">
                        <?php $page8Ribbon = trim((string)($certificateSettings['eighth_page_title_ribbon_text'] ?? '')); if ($page8Ribbon==='') { $page8Ribbon='ابزارهای ارزیابی'; } echo htmlspecialchars($page8Ribbon, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                    <div class="subtitle">فهرست ابزارهای تخصیص‌یافته به این ارزیابی</div>
                <?php elseif ($slug==='page10'): ?>
                    <div class="title-ribbon">
                        <?php $page10Ribbon = trim((string)($certificateSettings['tenth_page_title_ribbon_text'] ?? '')); if ($page10Ribbon==='') { $page10Ribbon='معرفی آزمون MBTI'; } echo htmlspecialchars($page10Ribbon, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                    <div class="subtitle">توضیحات کلی درباره MBTI و نحوه تفسیر نتایج</div>
                <?php elseif ($slug==='page11'): ?>
                    <div class="title-ribbon">
                        <?php $page11Ribbon = trim((string)($certificateSettings['eleventh_page_title_ribbon_text'] ?? '')); if ($page11Ribbon==='') { $page11Ribbon='نتایج آزمون MBTI'; } echo htmlspecialchars($page11Ribbon, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                    <div class="subtitle">تیپ شخصیتی و غلبه ترجیحات</div>
                <?php endif; ?>
            </div>
            <div class="certificate-body">
                <?php if ($slug==='details'): ?>
                    <?php $hasSecondImg = !empty($_secondImg); ?>
                    <div style="display: grid; grid-template-columns: <?= $hasSecondImg ? '1fr 1fr' : '1fr'; ?>; gap: 12mm; align-items: start;">
                        <div>
                            <div class="grid" style="max-width: 100%;">
                                <div class="label">نام</div>
                                <div class="value"><?= htmlspecialchars($evaluatee['first_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="label">نام خانوادگی</div>
                                <div class="value"><?= htmlspecialchars($evaluatee['last_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="label">نام کاربری</div>
                                <div class="value"><?= htmlspecialchars($evaluatee['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="label">کد ملی</div>
                                <div class="value"><?= htmlspecialchars($evaluatee['national_code'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="label">کد پرسنلی</div>
                                <div class="value"><?= htmlspecialchars($evaluatee['personnel_code'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="label">پست سازمانی</div>
                                <div class="value"><?= htmlspecialchars($evaluatee['organization_post'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="label">محل خدمت</div>
                                <div class="value"><?= htmlspecialchars($evaluatee['service_location'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="label">تاریخ ایجاد گزارش</div>
                                <div class="value"><?= UtilityHelper::englishToPersian(date('Y/m/d')); ?></div>
                            </div>
                        </div>
                        <?php if ($hasSecondImg): ?>
                        <div>
                            <?php $w = is_numeric($_secondImgW ?? null) ? (float) $_secondImgW : null; $h = is_numeric($_secondImgH ?? null) ? (float) $_secondImgH : null; $style = 'border-radius:8px; border:1px solid var(--border); object-fit:contain; max-width:100%; height:auto;'; if ($w !== null) { $style .= ' width: ' . htmlspecialchars((string)$w, ENT_QUOTES, 'UTF-8') . 'mm;'; } if ($h !== null) { $style .= ' height: ' . htmlspecialchars((string)$h, ENT_QUOTES, 'UTF-8') . 'mm;'; } ?>
                            <img src="<?= UtilityHelper::baseUrl('public/' . ltrim($_secondImg,'/')); ?>" alt="ضمیمه" style="<?= $style; ?>" crossorigin="anonymous" />
                        </div>
                        <?php endif; ?>
                    </div>
                <?php elseif ($slug==='toc'): ?>
                    <?php $hasThirdImg = !empty($_thirdImg); ?>
                    <div style="display:grid; grid-template-columns: <?= $hasThirdImg ? '1fr 1fr' : '1fr'; ?>; gap: 12mm; align-items:start;">
                        <div>
                            <div class="table-responsive">
                                <table class="table" style="width:100%; border-collapse:collapse;">
                                    <thead>
                                        <tr>
                                            <th style="text-align:right; padding:6px 8px; border-bottom:1px solid var(--border); color:#475569; font-weight:600;">عنوان</th>
                                            <th style="text-align:right; padding:6px 8px; border-bottom:1px solid var(--border); color:#475569; font-weight:600;">صفحه</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($_thirdItems) && is_array($_thirdItems)): ?>
                                            <?php foreach ($_thirdItems as $it): ?>
                                                <tr>
                                                    <td style="padding:6px 8px; border-bottom:1px dashed var(--border); color:#0f172a; font-size:14px;"><?= htmlspecialchars((string)($it['title'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td style="padding:6px 8px; border-bottom:1px dashed var(--border); color:#0f172a; font-weight:700; text-align:left; direction:ltr;"><?= htmlspecialchars((string)($it['page'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="2" style="padding:10px; color:#64748b;">آیتمی ثبت نشده است.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php if ($hasThirdImg): ?>
                        <div>
                            <?php $w3 = is_numeric($_thirdImgW ?? null) ? (float) $_thirdImgW : null; $h3 = is_numeric($_thirdImgH ?? null) ? (float) $_thirdImgH : null; $style3 = 'border-radius:8px; border:1px solid var(--border); object-fit:contain; max-width:100%; height:auto;'; if ($w3 !== null) { $style3 .= ' width: ' . htmlspecialchars((string)$w3, ENT_QUOTES, 'UTF-8') . 'mm;'; } if ($h3 !== null) { $style3 .= ' height: ' . htmlspecialchars((string)$h3, ENT_QUOTES, 'UTF-8') . 'mm;'; } ?>
                            <img src="<?= UtilityHelper::baseUrl('public/' . ltrim($_thirdImg,'/')); ?>" alt="ضمیمه" style="<?= $style3; ?>" crossorigin="anonymous" />
                        </div>
                        <?php endif; ?>
                    </div>
                <?php elseif ($slug==='page4'): ?>
                    <?php $hasFourthImg = !empty($_fourthImg); ?>
                    <div style="display:grid; grid-template-columns: <?= $hasFourthImg ? '1fr 1fr' : '1fr'; ?>; gap: 12mm; align-items:start;">
                        <div>
                            <?php $ta = $_fourthAlign==='center'?'center':($_fourthAlign==='justify'?'justify':($_fourthAlign==='left'?'left':'right')); ?>
                            <div class="content-text" style="white-space:pre-wrap; line-height:1.9; color:#0f172a; font-size:14px; text-align: <?= htmlspecialchars($ta, ENT_QUOTES, 'UTF-8'); ?>; padding:0; margin:0;">
                                <?php $__t4 = isset($certificateSettings['fourth_page_text']) ? ltrim((string)$certificateSettings['fourth_page_text']) : ''; echo nl2br(htmlspecialchars($__t4, ENT_QUOTES, 'UTF-8')); ?>
                            </div>
                        </div>
                        <?php if ($hasFourthImg): ?>
                        <div>
                            <?php $w4 = is_numeric($_fourthImgW ?? null) ? (float) $_fourthImgW : null; $h4 = is_numeric($_fourthImgH ?? null) ? (float) $_fourthImgH : null; $style4 = 'border-radius:8px; border:1px solid var(--border); object-fit:contain; max-width:100%; height:auto;'; if ($w4 !== null) { $style4 .= ' width: ' . htmlspecialchars((string)$w4, ENT_QUOTES, 'UTF-8') . 'mm;'; } if ($h4 !== null) { $style4 .= ' height: ' . htmlspecialchars((string)$h4, ENT_QUOTES, 'UTF-8') . 'mm;'; } ?>
                            <img src="<?= UtilityHelper::baseUrl('public/' . ltrim($_fourthImg,'/')); ?>" alt="ضمیمه" style="<?= $style4; ?>" crossorigin="anonymous" />
                        </div>
                        <?php endif; ?>
                    </div>
                <?php elseif ($slug==='page5'): ?>
                    <?php $hasModelImg = !empty($_modelImage); ?>
                    <div style="display:grid; grid-template-columns: <?= $hasModelImg ? '1fr 1fr' : '1fr'; ?>; gap: 12mm; align-items:start;">
                        <div>
                            <?php $ta5 = $_fifthAlign==='center'?'center':($_fifthAlign==='justify'?'justify':($_fifthAlign==='left'?'left':'right')); ?>
                            <div class="content-text" style="white-space:pre-wrap; line-height:1.9; color:#0f172a; font-size:14px; text-align: <?= htmlspecialchars($ta5, ENT_QUOTES, 'UTF-8'); ?>; padding:0; margin:0;">
                                <?= nl2br(htmlspecialchars((string)$_fifthText, ENT_QUOTES, 'UTF-8')); ?>
                            </div>
                        </div>
                        <?php if ($hasModelImg): ?>
                        <div>
                            <?php $style5 = 'border-radius:8px; border:1px solid var(--border); object-fit:contain; max-width:100%; height:auto;'; ?>
                            <img src="<?= UtilityHelper::baseUrl('public/' . ltrim((string)$_modelImage,'/')); ?>" alt="مدل شایستگی" style="<?= $style5; ?>" crossorigin="anonymous" />
                        </div>
                        <?php endif; ?>
                    </div>
                <?php elseif ($slug==='page6'): ?>
                    <div style="display:grid; grid-template-rows: auto auto; gap: 10mm;">
                        <div>
                            <?php $ta6 = $_sixthAlign==='center'?'center':($_sixthAlign==='justify'?'justify':($_sixthAlign==='left'?'left':'right')); ?>
                            <div class="content-text" style="white-space:pre-wrap; line-height:1.9; color:#0f172a; font-size:14px; text-align: <?= htmlspecialchars($ta6, ENT_QUOTES, 'UTF-8'); ?>; padding:0; margin:0;">
                                <?= nl2br(htmlspecialchars((string)$_sixthText, ENT_QUOTES, 'UTF-8')); ?>
                            </div>
                        </div>
                        <div>
                            <div class="table-responsive">
                                <table class="table" style="width:100%; border-collapse:collapse;">
                                    <thead>
                                        <tr>
                                            <th style="text-align:right; padding:6px 8px; border-bottom:1px solid var(--border); color:#475569; font-weight:600;">شایستگی</th>
                                            <th style="text-align:center; padding:6px 8px; border-bottom:1px solid var(--border); color:#475569; font-weight:600;">تعریف شایستگی</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($_modelCompetencies)): ?>
                                            <?php foreach ($_modelCompetencies as $comp): ?>
                                                <tr>
                                                    <td style="vertical-align:top; padding:6px 8px; border-bottom:1px dashed var(--border); color:#0f172a; font-size:14px; font-weight:700;">
                                                        <?= htmlspecialchars((string)($comp['title'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                                    </td>
                                                    <td style="text-align:right; padding:4px 0 4px 8px; border-bottom:1px dashed var(--border); color:#0f172a; font-size:14px; white-space:pre-wrap; "><?php $__def = isset($comp['definition']) ? (string)$comp['definition'] : ''; $__def = ltrim($__def); echo nl2br(htmlspecialchars($__def, ENT_QUOTES, 'UTF-8')); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="2" style="padding:10px; color:#64748b;">برای مدل شایستگی انتخاب‌شده، شایستگی‌ای ثبت نشده است.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php elseif ($slug==='page7'): ?>
                    <?php $hasSeventhImg = !empty($_seventhImg); ?>
                    <div style="display:grid; grid-template-rows: auto <?= $hasSeventhImg ? 'auto' : ''; ?>; gap: 10mm;">
                        <div>
                            <?php $ta7 = $_seventhAlign==='center'?'center':($_seventhAlign==='justify'?'justify':($_seventhAlign==='left'?'left':'right')); ?>
                            <div class="content-text" style="white-space:pre-wrap; line-height:1.9; color:#0f172a; font-size:14px; text-align: <?= htmlspecialchars($ta7, ENT_QUOTES, 'UTF-8'); ?>; padding:0; margin:0;">
                                <?= nl2br(htmlspecialchars((string)$_seventhText, ENT_QUOTES, 'UTF-8')); ?>
                            </div>
                        </div>
                        <?php if ($hasSeventhImg): ?>
                        <div style="display:flex; justify-content:center;">
                            <?php $style7 = 'border-radius:8px; border:1px solid var(--border); object-fit:contain; max-width:100%; height:auto;'; ?>
                            <img src="<?= UtilityHelper::baseUrl('public/' . ltrim((string)$_seventhImg,'/')); ?>" alt="تصویر صفحه هفتم" style="<?= $style7; ?>" crossorigin="anonymous" />
                        </div>
                        <?php endif; ?>
                    </div>
                <?php elseif ($slug==='page8'): ?>
                    <div>
                        <div class="table-responsive">
                            <table class="table" style="width:100%; border-collapse:collapse;">
                                <thead>
                                    <tr>
                                        <th style="text-align:right; padding:6px 8px; border-bottom:1px solid var(--border); color:#475569; font-weight:600;">نام ابزار</th>
                                        <th style="text-align:right; padding:6px 8px; border-bottom:1px solid var(--border); color:#475569; font-weight:600;">تعریف ابزار</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($_page8Tools)): ?>
                                        <?php foreach ($_page8Tools as $t): ?>
                                            <tr>
                                                <td style="vertical-align:top; padding:6px 8px; border-bottom:1px dashed var(--border); color:#0f172a; font-size:14px; font-weight:700;">
                                                    <?= htmlspecialchars((string)($t['tool_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                                </td>
                                                <td style="text-align:right; padding:4px 0 4px 8px; border-bottom:1px dashed var(--border); color:#0f172a; font-size:14px; white-space:pre-wrap; ">
                                                    <?php $__td = isset($t['tool_description']) ? (string)$t['tool_description'] : ''; $__td = ltrim($__td); echo nl2br(htmlspecialchars($__td, ENT_QUOTES, 'UTF-8')); ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="2" style="padding:10px; color:#64748b;">ابزاری برای این ارزیابی اختصاص نیافته است.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php elseif ($slug==='page10'): ?>
                    <?php $ta10 = $_tenthAlign==='center'?'center':($_tenthAlign==='justify'?'justify':($_tenthAlign==='left'?'left':'right')); ?>
                    <div class="content-text" style="white-space:pre-wrap; line-height:1.9; color:#0f172a; font-size:14px; text-align: <?= htmlspecialchars($ta10, ENT_QUOTES, 'UTF-8'); ?>; padding:0; margin:0;">
                        <?= nl2br(htmlspecialchars((string)$_tenthText, ENT_QUOTES, 'UTF-8')); ?>
                    </div>
                <?php elseif ($slug==='page11'): ?>
                    <?php $ta11 = $_eleventhAlign==='center'?'center':($_eleventhAlign==='justify'?'justify':($_eleventhAlign==='left'?'left':'right')); ?>
                    <?php $hasMbti = !empty($_mbti['has_mbti']); $axes = $_mbti['axes'] ?? []; $typeCode = strtoupper((string)($_mbti['type_code'] ?? '')); ?>
                    <div style="display:grid; grid-template-rows:auto auto auto auto; gap:8mm;">
                        <div>
                            <div style="font-size:22px; font-weight:800; color:#0f172a;">
                                <?php $typeTitle = trim((string)($_mbti['type_title'] ?? '')); $titleDisplay = $typeTitle!=='' ? ($typeTitle.' ('.$typeCode.')') : ($typeCode!==''?$typeCode:'—'); echo htmlspecialchars($titleDisplay, ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                            <?php if ($hasMbti && !empty($_mbti['type_summary'])): ?>
                            <div style="margin-top:4mm; color:#334155; font-size:14px; white-space:pre-wrap;">
                                <?= nl2br(htmlspecialchars((string)$_mbti['type_summary'], ENT_QUOTES, 'UTF-8')); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php // 4x4 MBTI grid (diamond-like presentation) ?>
                        <div>
                            <?php
                                $allTypes = ['ISTJ','ISFJ','INFJ','INTJ','ISTP','ISFP','INFP','INTP','ESTP','ESFP','ENFP','ENTP','ESTJ','ESFJ','ENFJ','ENTJ'];
                                // Arrange in 4 rows of 4
                                $rows = array_chunk($allTypes, 4);
                            ?>
                            <div style="display:grid; grid-template-columns: repeat(4, 1fr); gap:6px; max-width:200mm; margin:0 auto;">
                                <?php foreach ($rows as $r): ?>
                                    <?php foreach ($r as $tc): $is = ($typeCode === $tc); ?>
                                        <div style="text-align:center; padding:8px 6px; border:1px solid var(--border); border-radius:10px; font-weight:700; font-size:12px; color:<?= $is ? '#0f172a' : '#475569'; ?>; background:<?= $is ? 'linear-gradient(90deg, rgba(14,165,233,0.12), rgba(16,185,129,0.12))' : '#ffffff'; ?>; box-shadow:<?= $is ? '0 6px 14px rgba(14,165,233,0.18)' : 'none'; ?>;">
                                            <?= htmlspecialchars($tc, ENT_QUOTES, 'UTF-8'); ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </div>
                            <div style="text-align:center; font-size:12px; color:#64748b; margin-top:4mm;">نمای کلی ۱۶ تیپ شخصیتی MBTI</div>
                        </div>
                        <div>
                            <?php
                                $renderAxis = function($labelA, $labelB) use ($axes) {
                                    $key = $labelA.$labelB;
                                    $row = $axes[$key] ?? [];
                                    $aPct = (int)($row[$labelA.'_pct'] ?? 0);
                                    $bPct = (int)($row[$labelB.'_pct'] ?? 0);
                                    $aVal = (int)($row[$labelA] ?? 0);
                                    $bVal = (int)($row[$labelB] ?? 0);
                                    $total = max(1, $aVal + $bVal);
                                    ?>
                                    <div style="margin:6px 0;">
                                      <div style="display:flex; justify-content:space-between; font-size:12px; color:#64748b;">
                                        <span><?= htmlspecialchars($labelA, ENT_QUOTES, 'UTF-8'); ?> (<?= UtilityHelper::englishToPersian((string)$aVal); ?>)</span>
                                        <span><?= htmlspecialchars($labelB, ENT_QUOTES, 'UTF-8'); ?> (<?= UtilityHelper::englishToPersian((string)$bVal); ?>)</span>
                                      </div>
                                      <div style="height:10px; background:#e2e8f0; border-radius:999px; overflow:hidden;">
                                        <div style="height:100%; width:<?= (int)max(0,min(100,$aPct)); ?>%; background:linear-gradient(90deg, var(--accent), var(--accent-2));"></div>
                                      </div>
                                    </div>
                                    <?php
                                };
                            ?>
                            <div>
                                <?php $renderAxis('E','I'); $renderAxis('S','N'); $renderAxis('T','F'); $renderAxis('J','P'); ?>
                            </div>
                        </div>
                        <div>
                            <div class="content-text" style="white-space:pre-wrap; line-height:1.9; color:#0f172a; font-size:14px; text-align: <?= htmlspecialchars($ta11, ENT_QUOTES, 'UTF-8'); ?>; padding:0; margin:0;">
                                <?= nl2br(htmlspecialchars((string)$_eleventhText, ENT_QUOTES, 'UTF-8')); ?>
                            </div>
                            <?php if ($hasMbti && !empty($_mbti['type_description'])): ?>
                            <div style="margin-top:6mm; color:#334155; font-size:14px; white-space:pre-wrap;">
                                <?= nl2br(htmlspecialchars((string)$_mbti['type_description'], ENT_QUOTES, 'UTF-8')); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php // Features list (categorized) with simple pagination if long ?>
                        <?php
                            $featuresByCat = isset($_mbti['features']) && is_array($_mbti['features']) ? $_mbti['features'] : [];
                            $flattened = [];
                            foreach ($featuresByCat as $cat => $list) {
                                $flattened[] = ['type' => 'heading', 'text' => (string)$cat];
                                if (is_array($list)) {
                                    foreach ($list as $ft) { $flattened[] = ['type' => 'item', 'text' => (string)$ft]; }
                                }
                            }
                        ?>
                        <?php if (!empty($flattened)): ?>
                        <div>
                            <div style="font-weight:800; color:#0f172a; margin-bottom:2mm;">ویژگی‌های تیپ شخصیتی</div>
                            <div>
                                <?php $renderCount = 0; $maxItemsFirst = 18; // conservative fit
                                foreach ($flattened as $row): if ($renderCount >= $maxItemsFirst) break; $renderCount++; ?>
                                    <?php if ($row['type']==='heading'): ?>
                                        <div style="margin-top:3mm; font-weight:800; color:#0f172a;">• <?= htmlspecialchars($row['text'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php else: ?>
                                        <div style="margin:2px 0; color:#334155;">- <?= htmlspecialchars($row['text'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="footer">
                <div>سازمان: <?= htmlspecialchars($orgName ?: '—', ENT_QUOTES, 'UTF-8'); ?></div>
                <div>
                    <span>تاریخ چاپ: <?= UtilityHelper::englishToPersian(date('Y/m/d')); ?></span>
                    <?php if (!empty($_extraFooter)): ?>
                        <span class="mx-2">|</span>
                        <span><?= htmlspecialchars($_extraFooter, ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php $pageIndex++; ?>
    <?php elseif ($slug === 'page11'): // continuation pages for MBTI features if overflow ?>
        <?php
            $featuresByCat = isset($_mbti['features']) && is_array($_mbti['features']) ? $_mbti['features'] : [];
            $flat = [];
            foreach ($featuresByCat as $cat => $list) {
                $flat[] = ['type'=>'heading','text'=>(string)$cat];
                if (is_array($list)) { foreach ($list as $ft) { $flat[] = ['type'=>'item','text'=>(string)$ft]; } }
            }
            $maxItemsFirst = 18; $pageChunk = 32; // first page consumed up to 18 items, continuation pages 32 each
            $remaining = [];
            if (count($flat) > $maxItemsFirst) { $remaining = array_slice($flat, $maxItemsFirst); }
            $chunks = !empty($remaining) ? array_chunk($remaining, $pageChunk) : [];
        ?>
        <?php foreach ($chunks as $cIndex => $subset): ?>
        <div class="page<?= $_decor ? ' decor' : ''; ?> certificate-page" data-page-index="<?= (int)$pageIndex; ?>">
            <?php if ($_showLogo): ?>
            <div class="brand">
                <img src="<?= htmlspecialchars($orgLogoUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="Organization Logo" crossorigin="anonymous" />
                <?php if (!empty($orgName)): ?>
                    <span class="org-name"><?= htmlspecialchars($orgName, ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <div class="page-inner">
                <div class="header">
                    <div class="title-ribbon">
                        <?php $page11Ribbon = trim((string)($certificateSettings['eleventh_page_title_ribbon_text'] ?? '')); if ($page11Ribbon==='') { $page11Ribbon='نتایج آزمون MBTI'; }
                        $page11Ribbon .= ' - ادامه';
                        echo htmlspecialchars($page11Ribbon, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                    <div class="subtitle">ویژگی‌های تکمیلی تیپ شخصیتی</div>
                </div>
                <div class="certificate-body">
                    <div>
                        <?php foreach ($subset as $row): ?>
                            <?php if ($row['type']==='heading'): ?>
                                <div style="margin-top:3mm; font-weight:800; color:#0f172a;">• <?= htmlspecialchars($row['text'], ENT_QUOTES, 'UTF-8'); ?></div>
                            <?php else: ?>
                                <div style="margin:2px 0; color:#334155;">- <?= htmlspecialchars($row['text'], ENT_QUOTES, 'UTF-8'); ?></div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="footer">
                    <div>سازمان: <?= htmlspecialchars($orgName ?: '—', ENT_QUOTES, 'UTF-8'); ?></div>
                    <div>
                        <span>تاریخ چاپ: <?= UtilityHelper::englishToPersian(date('Y/m/d')); ?></span>
                        <?php if (!empty($_extraFooter)): ?>
                            <span class="mx-2">|</span>
                            <span><?= htmlspecialchars($_extraFooter, ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php $pageIndex++; endforeach; ?>
    <?php elseif ($slug === 'page6'): // paginate competencies across multiple pages ?>
        <?php
            $rowsPerPage6 = 12; // safe default for current layout
            $comps = $_modelCompetencies;
            $chunks6 = [];
            if (!empty($comps)) {
                $chunks6 = array_chunk($comps, $rowsPerPage6);
            } else {
                $chunks6 = [[]];
            }
        ?>
        <?php foreach ($chunks6 as $chunkIndex6 => $subset6): ?>
        <div class="page<?= $_decor ? ' decor' : ''; ?> certificate-page" data-page-index="<?= (int)$pageIndex; ?>">
            <?php if ($_showLogo): ?>
            <div class="brand">
                <img src="<?= htmlspecialchars($orgLogoUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="Organization Logo" crossorigin="anonymous" />
                <?php if (!empty($orgName)): ?>
                    <span class="org-name"><?= htmlspecialchars($orgName, ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <div class="page-inner">
                <div class="header">
                    <div class="title-ribbon">
                        <?php $page6Ribbon = trim((string)($certificateSettings['sixth_page_title_ribbon_text'] ?? '')); if ($page6Ribbon==='') { $page6Ribbon='شایستگی‌ها و تعاریف'; }
                        if ($chunkIndex6 > 0) { $page6Ribbon .= ' - ادامه'; }
                        echo htmlspecialchars($page6Ribbon, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                    <div class="subtitle">متن توضیحی + جدول شایستگی‌ها و تعریف شایستگی‌ها</div>
                </div>
                <div class="certificate-body">
                    <?php if ($chunkIndex6 === 0): ?>
                    <div style="margin-bottom: 10mm;">
                        <?php $ta6 = $_sixthAlign==='center'?'center':($_sixthAlign==='justify'?'justify':($_sixthAlign==='left'?'left':'right')); ?>
                        <div class="content-text" style="white-space:pre-wrap; line-height:1.9; color:#0f172a; font-size:14px; text-align: <?= htmlspecialchars($ta6, ENT_QUOTES, 'UTF-8'); ?>; padding:0; margin:0;">
                            <?= nl2br(htmlspecialchars((string)$_sixthText, ENT_QUOTES, 'UTF-8')); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div>
                        <div class="table-responsive">
                            <table class="table" style="width:100%; border-collapse:collapse;">
                                <thead>
                                    <tr>
                                        <th style="text-align:right; padding:6px 8px; border-bottom:1px solid var(--border); color:#475569; font-weight:600;">شایستگی</th>
                                        <th style="text-align:center; padding:6px 8px; border-bottom:1px solid var(--border); color:#475569; font-weight:600;">تعریف شایستگی</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($subset6)): ?>
                                        <?php foreach ($subset6 as $comp): ?>
                                            <tr>
                                                <td style="vertical-align:top; padding:6px 8px; border-bottom:1px dashed var(--border); color:#0f172a; font-size:14px; font-weight:700;">
                                                    <?= htmlspecialchars((string)($comp['title'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                                </td>
                                                <td style="text-align:right; padding:4px 0 4px 8px; border-bottom:1px dashed var(--border); color:#0f172a; font-size:14px; white-space:pre-wrap; ">
                                                    <?php $__def = isset($comp['definition']) ? (string)$comp['definition'] : ''; $__def = ltrim($__def); echo nl2br(htmlspecialchars($__def, ENT_QUOTES, 'UTF-8')); ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="2" style="padding:10px; color:#64748b;">برای مدل شایستگی انتخاب‌شده، شایستگی‌ای ثبت نشده است.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="footer">
                    <div>سازمان: <?= htmlspecialchars($orgName ?: '—', ENT_QUOTES, 'UTF-8'); ?></div>
                    <div>
                        <span>تاریخ چاپ: <?= UtilityHelper::englishToPersian(date('Y/m/d')); ?></span>
                        <?php if (!empty($_extraFooter)): ?>
                            <span class="mx-2">|</span>
                            <span><?= htmlspecialchars($_extraFooter, ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php $pageIndex++; endforeach; ?>
    <?php elseif ($slug === 'page9'): // two-row page with text + table; paginate rows if needed ?>
        <?php
            $rowsPerPage9 = 14; // safe default
            $items9 = $_ninthItems;
            $chunks9 = !empty($items9) ? array_chunk($items9, $rowsPerPage9) : [[]];
        ?>
        <?php foreach ($chunks9 as $chunkIndex9 => $subset9): ?>
        <div class="page<?= $_decor ? ' decor' : ''; ?> certificate-page" data-page-index="<?= (int)$pageIndex; ?>">
            <?php if ($_showLogo): ?>
            <div class="brand">
                <img src="<?= htmlspecialchars($orgLogoUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="Organization Logo" crossorigin="anonymous" />
                <?php if (!empty($orgName)): ?>
                    <span class="org-name"><?= htmlspecialchars($orgName, ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <div class="page-inner">
                <div class="header">
                    <div class="title-ribbon">
                        <?php $page9Ribbon = trim((string)($certificateSettings['ninth_page_title_ribbon_text'] ?? '')); if ($page9Ribbon==='') { $page9Ribbon='نتایج تکمیلی'; }
                        if ($chunkIndex9 > 0) { $page9Ribbon .= ' - ادامه'; }
                        echo htmlspecialchars($page9Ribbon, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                    <div class="subtitle">متن توضیحی + جدول (امتیاز - توضیحات - نتیجه)</div>
                </div>
                <div class="certificate-body">
                    <?php if ($chunkIndex9 === 0): ?>
                    <div style="margin-bottom: 10mm;">
                        <?php $ta9 = $_ninthAlign==='center'?'center':($_ninthAlign==='justify'?'justify':($_ninthAlign==='left'?'left':'right')); ?>
                        <div class="content-text" style="white-space:pre-wrap; line-height:1.9; color:#0f172a; font-size:14px; text-align: <?= htmlspecialchars($ta9, ENT_QUOTES, 'UTF-8'); ?>; padding:0; margin:0;">
                            <?= nl2br(htmlspecialchars((string)$_ninthText, ENT_QUOTES, 'UTF-8')); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div>
                        <div class="table-responsive">
                            <table class="table" style="width:100%; border-collapse:collapse;">
                                <thead>
                                    <tr>
                                        <th style="text-align:right; padding:6px 8px; border-bottom:1px solid var(--border); color:#475569; font-weight:600;">امتیاز</th>
                                        <th style="text-align:right; padding:6px 8px; border-bottom:1px solid var(--border); color:#475569; font-weight:600;">توضیحات</th>
                                        <th style="text-align:right; padding:6px 8px; border-bottom:1px solid var(--border); color:#475569; font-weight:600;">نتیجه</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($subset9)): ?>
                                        <?php foreach ($subset9 as $row): ?>
                                            <tr>
                                                <td style="vertical-align:top; padding:6px 8px; border-bottom:1px dashed var(--border); color:#0f172a; font-size:14px; font-weight:700; min-width:40px;">
                                                    <?= htmlspecialchars((string)($row['score'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                                </td>
                                                <td style="text-align:right; padding:4px 8px; border-bottom:1px dashed var(--border); color:#0f172a; font-size:14px; white-space:pre-wrap;">
                                                    <?php $__desc = isset($row['description']) ? (string)$row['description'] : ''; $__desc = ltrim($__desc); echo nl2br(htmlspecialchars($__desc, ENT_QUOTES, 'UTF-8')); ?>
                                                </td>
                                                <td style="text-align:right; padding:4px 8px; border-bottom:1px dashed var(--border); color:#0f172a; font-size:14px; white-space:pre-wrap; min-width:80px;">
                                                    <?php $__res = isset($row['result']) ? (string)$row['result'] : ''; $__res = ltrim($__res); echo nl2br(htmlspecialchars($__res, ENT_QUOTES, 'UTF-8')); ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" style="padding:10px; color:#64748b;">آیتمی ثبت نشده است.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="footer">
                    <div>سازمان: <?= htmlspecialchars($orgName ?: '—', ENT_QUOTES, 'UTF-8'); ?></div>
                    <div>
                        <span>تاریخ چاپ: <?= UtilityHelper::englishToPersian(date('Y/m/d')); ?></span>
                        <?php if (!empty($_extraFooter)): ?>
                            <span class="mx-2">|</span>
                            <span><?= htmlspecialchars($_extraFooter, ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php $pageIndex++; endforeach; ?>
    <?php else: // slug === 'page8' (paginate tools across multiple pages) ?>
        <?php
            $tools = $_page8Tools;
            $rowsPerPage = 16; // safe default for current typography/layout
            $chunks = [];
            if (!empty($tools)) {
                $chunks = array_chunk($tools, $rowsPerPage);
            } else {
                $chunks = [[]];
            }
        ?>
        <?php foreach ($chunks as $chunkIndex => $toolsSubset): ?>
        <div class="page<?= $_decor ? ' decor' : ''; ?> certificate-page" data-page-index="<?= (int)$pageIndex; ?>">
            <?php if ($_showLogo): ?>
            <div class="brand">
                <img src="<?= htmlspecialchars($orgLogoUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="Organization Logo" crossorigin="anonymous" />
                <?php if (!empty($orgName)): ?>
                    <span class="org-name"><?= htmlspecialchars($orgName, ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <div class="page-inner">
                <div class="header">
                    <div class="title-ribbon">
                        <?php $page8Ribbon = trim((string)($certificateSettings['eighth_page_title_ribbon_text'] ?? '')); if ($page8Ribbon==='') { $page8Ribbon='ابزارهای ارزیابی'; }
                        if ($chunkIndex > 0) { $page8Ribbon .= ' - ادامه'; }
                        echo htmlspecialchars($page8Ribbon, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                    <div class="subtitle">فهرست ابزارهای تخصیص‌یافته به این ارزیابی</div>
                </div>
                <div class="certificate-body">
                    <div>
                        <div class="table-responsive">
                            <table class="table" style="width:100%; border-collapse:collapse;">
                                <thead>
                                    <tr>
                                        <th style="text-align:right; padding:6px 8px; border-bottom:1px solid var(--border); color:#475569; font-weight:600;">نام ابزار</th>
                                        <th style="text-align:right; padding:6px 8px; border-bottom:1px solid var(--border); color:#475569; font-weight:600;">تعریف ابزار</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($toolsSubset)): ?>
                                        <?php foreach ($toolsSubset as $t): ?>
                                            <tr>
                                                <td style="vertical-align:top; padding:6px 8px; border-bottom:1px dashed var(--border); color:#0f172a; font-size:14px; font-weight:700;">
                                                    <?= htmlspecialchars((string)($t['tool_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                                </td>
                                                <td style="text-align:right; padding:4px 0 4px 8px; border-bottom:1px dashed var(--border); color:#0f172a; font-size:14px; white-space:pre-wrap; ">
                                                    <?php $__td = isset($t['tool_description']) ? (string)$t['tool_description'] : ''; $__td = ltrim($__td); echo nl2br(htmlspecialchars($__td, ENT_QUOTES, 'UTF-8')); ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="2" style="padding:10px; color:#64748b;">ابزاری برای این ارزیابی اختصاص نیافته است.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="footer">
                    <div>سازمان: <?= htmlspecialchars($orgName ?: '—', ENT_QUOTES, 'UTF-8'); ?></div>
                    <div>
                        <span>تاریخ چاپ: <?= UtilityHelper::englishToPersian(date('Y/m/d')); ?></span>
                        <?php if (!empty($_extraFooter)): ?>
                            <span class="mx-2">|</span>
                            <span><?= htmlspecialchars($_extraFooter, ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php $pageIndex++; endforeach; ?>
    <?php endif; // end page8 custom rendering ?>
    <?php endforeach; ?>
    <script>
        function waitForFonts() {
            if (document.fonts && document.fonts.ready) {
                return document.fonts.ready.catch(function(){ return Promise.resolve(); });
            }
            // Fallback if Font Loading API isn't available
            return new Promise(function(resolve){ setTimeout(resolve, 300); });
        }

        async function exportCertificatePdf(){
            try {
                const { jsPDF } = window.jspdf || window.jspPDF || {};
                if (!jsPDF) {
                    alert('کتابخانه PDF در دسترس نیست. لطفاً اتصال اینترنت را بررسی کنید.');
                    return;
                }

                const pages = Array.from(document.querySelectorAll('.certificate-page'));
                const actionsEl = document.querySelector('.actions');
                if (actionsEl) actionsEl.style.display = 'none';

                await waitForFonts();

                // Ensure high-quality render
                const scale = Math.min(2, window.devicePixelRatio || 1.5);
                const useSimple = <?= json_encode($_pdfMode === 'simple'); ?>;
                const pdf = new jsPDF('landscape', 'mm', 'a4');
                const pageWidth = pdf.internal.pageSize.getWidth();
                const pageHeight = pdf.internal.pageSize.getHeight();

                for (let i = 0; i < pages.length; i++) {
                    if (useSimple) document.body.classList.add('pdf-export');
                    const canvas = await html2canvas(pages[i], {
                        scale,
                        useCORS: true,
                        allowTaint: true,
                        backgroundColor: '#ffffff',
                        foreignObjectRendering: true,
                        removeContainer: true,
                    });
                    const imgData = canvas.toDataURL('image/jpeg', 0.98);
                    if (i === 0) {
                        pdf.addImage(imgData, 'JPEG', 0, 0, pageWidth, pageHeight, undefined, 'FAST');
                    } else {
                        pdf.addPage('a4', 'landscape');
                        pdf.addImage(imgData, 'JPEG', 0, 0, pageWidth, pageHeight, undefined, 'FAST');
                    }
                    if (useSimple) document.body.classList.remove('pdf-export');
                }

                const fileName = 'feedback-report-' + new Date().toISOString().slice(0,19).replace(/[:T]/g,'-') + '.pdf';
                pdf.save(fileName);
            } catch (e) {
                console.error(e);
                alert('خطا در تولید PDF. لطفاً دوباره تلاش کنید.');
            } finally {
                const actionsEl = document.querySelector('.actions');
                if (actionsEl) actionsEl.style.display = '';
                document.body.classList.remove('pdf-export');
            }
        }
    </script>
    </body>
    </html>
