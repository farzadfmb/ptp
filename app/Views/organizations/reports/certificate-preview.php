<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'پیش‌نمایش گواهی';
$user = $user ?? null;
$organization = $organization ?? null;
$pages = isset($pages) && is_array($pages) ? $pages : [];
$componentMap = isset($componentMap) && is_array($componentMap) ? $componentMap : [];
$sampleData = isset($sampleData) && is_array($sampleData) ? $sampleData : [];
$runtimeDatasets = isset($runtimeDatasets) && is_array($runtimeDatasets) ? $runtimeDatasets : [];
$activePageId = isset($activePageId) && is_string($activePageId) ? $activePageId : '';
$totalPages = isset($totalPages) ? (int) $totalPages : count($pages);
$builderStateJson = $builderStateJson ?? htmlspecialchars(json_encode(['pages' => []], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');

$additional_css = $additional_css ?? [];
$additional_js = $additional_js ?? [];
// Add html2canvas, jsPDF and Custom Certificate PDF
$html2canvasCdn = 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js';
$jsPdfCdn = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
$customPdf = 'public/assets/js/custom-certificate-pdf.js?v=' . time();
if (!in_array($html2canvasCdn, $additional_js, true)) {
    $additional_js[] = $html2canvasCdn;
}
if (!in_array($jsPdfCdn, $additional_js, true)) {
    $additional_js[] = $jsPdfCdn;
}
if (!in_array($customPdf, $additional_js, true)) {
    $additional_js[] = $customPdf;
}
$inline_styles = $inline_styles ?? '';
$inline_scripts = $inline_scripts ?? '';
$isEmbedded = isset($isEmbedded)
    ? (bool) $isEmbedded
    : in_array((string) ($_GET['embed'] ?? ''), ['1', 'true', 'yes'], true);

$projectRoot = dirname(__DIR__, 4);
$previewCssRelative = 'public/assets/css/certificate-preview.css';
$previewCssPath = $projectRoot . '/' . $previewCssRelative;
$previewCssVersion = is_file($previewCssPath) ? (string) filemtime($previewCssPath) : (string) time();
$additional_css[] = $previewCssRelative . '?v=' . $previewCssVersion;

include __DIR__ . '/../../layouts/organization-header.php';
if (!$isEmbedded) {
    include __DIR__ . '/../../layouts/organization-sidebar.php';
    include __DIR__ . '/../../layouts/organization-navbar.php';
}

// Hide top-of-page UI within this preview page by default (can be overridden by controller)
$hideTopUi = $hideTopUi ?? false;
if ($hideTopUi) {
    echo '<style>'
        . '/* Hide supplementary preview panels */\n'
        . '.certificate-preview-selection,\n'
        . '.certificate-preview-summary { display: none !important; }\n'
        . '/* Hide per-page header and badges (template/size/orientation) */\n'
        . '.certificate-page-header,\n'
        . '.certificate-page-badges { display: none !important; }\n'
        . '</style>';
}

$evaluationOptions = isset($evaluationOptions) && is_array($evaluationOptions) ? $evaluationOptions : [];
$evaluateeOptions = isset($evaluateeOptions) && is_array($evaluateeOptions) ? $evaluateeOptions : [];
$selectedEvaluation = isset($selectedEvaluation) && is_array($selectedEvaluation) ? $selectedEvaluation : null;
$selectedEvaluationId = isset($selectedEvaluationId) ? (int) $selectedEvaluationId : ($selectedEvaluation['id'] ?? 0);
$selectedEvaluatee = isset($selectedEvaluatee) && is_array($selectedEvaluatee) ? $selectedEvaluatee : null;
$selectedEvaluateeId = isset($selectedEvaluateeId) ? (int) $selectedEvaluateeId : ($selectedEvaluatee['id'] ?? 0);
$totalEvaluations = isset($totalEvaluations) ? (int) $totalEvaluations : 0;
$hasRuntimeData = isset($hasRuntimeData) ? (bool) $hasRuntimeData : false;
$selectedEvaluationTitle = $selectedEvaluation['title'] ?? ($sampleData['evaluation_title'] ?? '');
$selectedEvaluationTitle = is_string($selectedEvaluationTitle) ? trim($selectedEvaluationTitle) : '';
$selectedEvaluationDateLabel = $selectedEvaluation['date_display'] ?? ($sampleData['evaluation_period'] ?? '');
$selectedEvaluationDateLabel = is_string($selectedEvaluationDateLabel) ? trim($selectedEvaluationDateLabel) : '';
$selectedEvaluateeName = '';
$selectedEvaluateeUsername = '';
if ($selectedEvaluatee !== null) {
    $selectedEvaluateeName = $selectedEvaluatee['display_name'] ?? ($selectedEvaluatee['label'] ?? '');
    if (isset($selectedEvaluatee['user']) && is_array($selectedEvaluatee['user'])) {
        $selectedEvaluateeUsername = $selectedEvaluatee['user']['username'] ?? '';
    }
} elseif (isset($sampleData['user_full_name'])) {
    $selectedEvaluateeName = (string) $sampleData['user_full_name'];
}
$selectedEvaluateeName = is_string($selectedEvaluateeName) ? trim($selectedEvaluateeName) : '';
$selectedEvaluateeUsername = $selectedEvaluateeUsername !== '' ? (string) $selectedEvaluateeUsername : ($sampleData['user_username'] ?? '');
$selectedEvaluateeUsername = is_string($selectedEvaluateeUsername) ? trim($selectedEvaluateeUsername) : '';
$evaluateeCountLabel = '';
if ($selectedEvaluation !== null && isset($selectedEvaluation['evaluatees']) && is_array($selectedEvaluation['evaluatees'])) {
    $evaluateeCount = count($selectedEvaluation['evaluatees']);
    if ($evaluateeCount > 0) {
        $evaluateeCountLabel = UtilityHelper::englishToPersian((string) $evaluateeCount) . ' نفر';
    }
} elseif (!empty($sampleData['evaluatees_count'])) {
    $evaluateeCountLabel = (string) $sampleData['evaluatees_count'];
}
$dataVersionLabel = $hasRuntimeData ? 'داده ارزیابی واقعی' : 'پیش‌فرض نمایشی';
$hasEvaluations = $totalEvaluations > 0;
if (!isset($selectionActionUrl) || !is_string($selectionActionUrl) || trim($selectionActionUrl) === '') {
    $selectionActionUrl = UtilityHelper::baseUrl('organizations/reports/certificate-preview');
}

$escape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

$alignmentClass = static function ($align) {
    switch ($align) {
        case 'left':
            return 'certificate-align-left';
        case 'center':
            return 'certificate-align-center';
        case 'justify':
            return 'certificate-align-justify';
        case 'right':
        default:
            return 'certificate-align-right';
    }
};

$sanitizeClass = static function ($value) {
    if (!is_string($value)) {
        return '';
    }
    $value = strtolower($value);
    $sanitized = preg_replace('/[^a-z0-9_-]/', '', $value);
    return $sanitized !== null ? $sanitized : '';
};

$allowedPageSizes = ['a4', 'a5'];
$allowedOrientations = ['portrait', 'landscape'];

$pageSizeLabels = [
    'a4' => 'A4',
    'a5' => 'A5',
];

$orientationLabels = [
    'portrait' => 'عمودی',
    'landscape' => 'افقی',
];

$getPageScale = static function (string $sizeClass, string $orientationClass): float {
    $matrix = [
        'a4' => ['portrait' => 1.0, 'landscape' => 0.92],
        'a5' => ['portrait' => 0.9, 'landscape' => 0.82],
    ];
    $scale = $matrix[$sizeClass][$orientationClass] ?? 1.0;
    if (!is_numeric($scale)) {
        $scale = 1.0;
    }
    $scale = (float) $scale;
    if ($scale < 0.7) {
        $scale = 0.7;
    } elseif ($scale > 1.0) {
        $scale = 1.0;
    }
    return $scale;
};

$getTableRowsPerChunk = static function (string $sizeClass, string $orientationClass, int $columnCount): int {
    $base = $sizeClass === 'a4' ? 18 : 12;
    if ($orientationClass === 'landscape') {
        $base = (int) floor($base * 0.75);
    }
    if ($columnCount >= 5) {
        $base -= 3;
    } elseif ($columnCount >= 4) {
        $base -= 2;
    }
    $base = max(6, $base);
    return $base;
};

$normalizePageSize = static function ($value) use ($sanitizeClass, $allowedPageSizes) {
    $raw = is_string($value) ? strtolower($value) : '';
    $normalized = $sanitizeClass($raw);
    if (!in_array($normalized, $allowedPageSizes, true)) {
        $normalized = 'a4';
    }
    return $normalized;
};

$normalizeOrientation = static function ($value) use ($sanitizeClass, $allowedOrientations) {
    $raw = is_string($value) ? strtolower($value) : '';
    $normalized = $sanitizeClass($raw);
    if (!in_array($normalized, $allowedOrientations, true)) {
        $normalized = 'portrait';
    }
    return $normalized;
};

$sanitizeDimension = static function ($value) {
    if (!is_string($value)) {
        return null;
    }
    $trimmed = trim($value);
    if ($trimmed === '') {
        return null;
    }
    if (preg_match('/^\d{1,4}(?:\.\d+)?%$/', $trimmed)) {
        return $trimmed;
    }
    if (preg_match('/^\d{1,4}(?:\.\d+)?px$/', $trimmed)) {
        return $trimmed;
    }
    return null;
};

$sanitizeColor = static function ($value, string $fallback = '#0f172a') {
    $fallback = $fallback !== '' ? $fallback : '#0f172a';
    if (!is_string($value)) {
        return $fallback;
    }
    $trimmed = trim($value);
    if ($trimmed === '') {
        return $fallback;
    }
    if (strlen($trimmed) > 64) {
        $trimmed = substr($trimmed, 0, 64);
    }
    if (preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{4}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/', $trimmed)) {
        return $trimmed;
    }
    if (preg_match('/^rgba?\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})(\s*,\s*(0|0?\.\d+|1(?:\.0+)?))?\s*\)$/', $trimmed, $matches)) {
        $r = (int) $matches[1];
        $g = (int) $matches[2];
        $b = (int) $matches[3];
        if ($r > 255 || $g > 255 || $b > 255) {
            return $fallback;
        }
        if (!empty($matches[4])) {
            $alpha = (float) ($matches[5] ?? 1);
            if ($alpha < 0 || $alpha > 1) {
                return $fallback;
            }
        }
        return $trimmed;
    }
    return $fallback;
};

$colorToRgba = static function (string $color, float $alpha) use ($sanitizeColor) {
    $alpha = max(0, min(1, $alpha));
    $color = $sanitizeColor($color, '#0f172a');

    if (preg_match('/^#([0-9a-fA-F]{3})$/', $color, $matches)) {
        $hex = $matches[1];
        $r = hexdec(str_repeat($hex[0], 2));
        $g = hexdec(str_repeat($hex[1], 2));
        $b = hexdec(str_repeat($hex[2], 2));
        return sprintf('rgba(%d,%d,%d,%.3f)', $r, $g, $b, $alpha);
    }

    if (preg_match('/^#([0-9a-fA-F]{6})$/', $color, $matches)) {
        $hex = $matches[1];
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        return sprintf('rgba(%d,%d,%d,%.3f)', $r, $g, $b, $alpha);
    }

    if (preg_match('/^#([0-9a-fA-F]{8})$/', $color, $matches)) {
        $hex = $matches[1];
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        return sprintf('rgba(%d,%d,%d,%.3f)', $r, $g, $b, $alpha);
    }

    if (preg_match('/^#([0-9a-fA-F]{4})$/', $color, $matches)) {
        $hex = $matches[1];
        $r = hexdec(str_repeat($hex[0], 2));
        $g = hexdec(str_repeat($hex[1], 2));
        $b = hexdec(str_repeat($hex[2], 2));
        return sprintf('rgba(%d,%d,%d,%.3f)', $r, $g, $b, $alpha);
    }

    if (preg_match('/^rgba?\(([^)]+)\)$/', $color, $matches)) {
        $parts = array_map('trim', explode(',', $matches[1]));
        $r = isset($parts[0]) ? (int) $parts[0] : 15;
        $g = isset($parts[1]) ? (int) $parts[1] : 23;
        $b = isset($parts[2]) ? (int) $parts[2] : 42;
        return sprintf('rgba(%d,%d,%d,%.3f)', $r, $g, $b, $alpha);
    }

    return sprintf('rgba(%d,%d,%d,%.3f)', 15, 23, 42, $alpha);
};

$sanitizeLineStyle = static function ($value) {
    $allowed = ['solid', 'dashed', 'double'];
    if (!is_string($value)) {
        return 'solid';
    }
    $trimmed = strtolower(trim($value));
    return in_array($trimmed, $allowed, true) ? $trimmed : 'solid';
};

$normalizeList = static function ($value) use (&$normalizeList) {
    if (is_array($value)) {
        $result = [];
        foreach ($value as $item) {
            if (!is_string($item)) {
                continue;
            }
            $clean = trim($item);
            if ($clean === '') {
                continue;
            }
            $result[] = $clean;
        }
        return $result;
    }
    if (is_string($value)) {
        $decoded = json_decode($value, true);
        if (is_array($decoded)) {
            return $normalizeList($decoded);
        }
        $parts = preg_split('/\r?\n|,/', $value);
        if (is_array($parts)) {
            return $normalizeList($parts);
        }
    }
    return [];
};

$sanitizeIconName = static function ($value, string $fallback = 'ellipse-outline') {
    if (!is_string($value)) {
        return $fallback;
    }
    $trimmed = trim($value);
    if ($trimmed === '') {
        return $fallback;
    }
    if (!preg_match('/^[a-z0-9-]+$/i', $trimmed)) {
        return $fallback;
    }
    return strtolower($trimmed);
};

$clampPercent = static function ($value) {
    if (!is_numeric($value)) {
        return 0.0;
    }
    $percent = (float) $value;
    if ($percent < 0) {
        $percent = 0;
    } elseif ($percent > 100) {
        $percent = 100;
    }
    return $percent;
};

$formatPercentLabel = static function ($value) use ($clampPercent) {
    $value = $clampPercent($value);
    $rounded = (int) round($value);
    return UtilityHelper::englishToPersian((string) $rounded) . '%';
};

$getMbtiPreference = static function (array $props, array $sampleProfile, array $datasetProfile, string $propKey, float $default = 50.0) use ($clampPercent) {
    if (isset($datasetProfile[$propKey]) && is_numeric($datasetProfile[$propKey])) {
        return $clampPercent($datasetProfile[$propKey]);
    }
    if (isset($props[$propKey]) && is_numeric($props[$propKey])) {
        return $clampPercent($props[$propKey]);
    }
    if (isset($sampleProfile[$propKey]) && is_numeric($sampleProfile[$propKey])) {
        return $clampPercent($sampleProfile[$propKey]);
    }
    return $clampPercent($default);
};

$sanitizeUrl = static function ($value) {
    if (!is_string($value)) {
        return '';
    }
    $trimmed = trim($value);
    if ($trimmed === '') {
        return '';
    }
    return filter_var($trimmed, FILTER_VALIDATE_URL) ? $trimmed : '';
};

$resolveAssetUrl = static function ($value) use ($sanitizeUrl) {
    if (!is_string($value)) {
        return '';
    }
    $trimmed = trim($value);
    if ($trimmed === '') {
        return '';
    }
    if (stripos($trimmed, 'data:') === 0) {
        return $sanitizeUrl($trimmed);
    }
    if (preg_match('/^https?:\/\//i', $trimmed)) {
        return $sanitizeUrl($trimmed);
    }
    if (strpos($trimmed, '//') === 0) {
        return $sanitizeUrl('https:' . $trimmed);
    }
    $normalized = stripos($trimmed, 'public/') === 0 ? $trimmed : 'public/' . ltrim($trimmed, '/');
    return $sanitizeUrl(UtilityHelper::baseUrl($normalized));
};

$isTruthy = static function ($value) {
    return in_array($value, [1, '1', true, 'true', 'on'], true);
};

$organizationName = $sampleData['organization_name'] ?? ($organization['name'] ?? 'سازمان شما');
$pdfNameParts = [];
if ($selectedEvaluateeName !== '') {
    $pdfNameParts[] = $selectedEvaluateeName;
}
if ($selectedEvaluationTitle !== '') {
    $pdfNameParts[] = $selectedEvaluationTitle;
}
if ($organizationName !== '') {
    $pdfNameParts[] = $organizationName;
}
$pdfSlugSource = trim(implode('-', $pdfNameParts));
$pdfSlug = $pdfSlugSource !== '' ? UtilityHelper::slugify($pdfSlugSource) : '';
if ($pdfSlug === '') {
    $pdfSlug = 'certificate-preview';
}
$pdfFileName = $pdfSlug . '-' . date('Ymd-His') . '.pdf';

if ($isEmbedded) {
    $inline_styles .= "
    body { background: #f8fafc; }
    .wrapper { background: transparent; }
    .page-content-wrapper { margin-inline-start: 0; }
    .top-header, .sidebar-wrapper, .logo-icon, .overlay, .back-to-top, .page-footer, .switcher-wrapper { display: none !important; }
    /* Hide per-page header and badges */
    .certificate-page-header, .certificate-page-badges { display: none !important; }
    /* Footer page number */
    .certificate-preview-page { position: relative; }
    .certificate-page-footer { position: absolute; bottom: 8px; left: 0; right: 0; text-align: center; color: #64748b; font-size: 12px; pointer-events: none; }
    /* Floating download button */
    .floating-download-btn { 
        position: fixed; 
        top: 20px; 
        right: 20px;
        z-index: 99999; 
        color: white; 
        border: none; 
        padding: 14px 24px; 
        border-radius: 12px; 
        cursor: pointer; 
        font-family: inherit; 
        font-size: 15px; 
        font-weight: 700; 
        box-shadow: 0 4px 20px rgba(16, 185, 129, 0.4); 
        transition: all 0.3s ease; 
        display: flex; 
        align-items: center; 
        gap: 10px; 
    }
    .floating-download-btn:hover { transform: translateY(-3px); box-shadow: 0 8px 30px rgba(16, 185, 129, 0.5); }
    .floating-download-btn:active { transform: translateY(-1px); }
    .floating-download-btn svg { width: 20px; height: 20px; }
    
    /* Print styles for PDF generation */
    @media print { 
        @page { 
            size: A4 portrait; 
            margin: 0; 
        }
        
        body { 
            background: white !important; 
            margin: 0 !important;
            padding: 0 !important;
        }
        
        .floating-download-btn { display: none !important; }
        .certificate-page-header, .certificate-page-badges { display: none !important; }
        
        .certificate-preview-page { 
            page-break-after: always; 
            page-break-inside: avoid;
            margin: 0 !important;
            padding: 0 !important;
            box-shadow: none !important;
            border-radius: 0 !important;
            width: 100% !important;
            min-height: auto !important;
        }
        
        .certificate-preview-page:last-child {
            page-break-after: auto;
        }
        
        .certificate-page-body {
            height: auto !important;
            max-height: none !important;
            overflow: visible !important;
        }
        
        .certificate-page-body-inner {
            transform: none !important;
        }
        
        .certificate-page-footer { display: none !important; }
    }
";
}

$skillLookup = [];
if (isset($sampleData['skills']) && is_array($sampleData['skills'])) {
    foreach ($sampleData['skills'] as $skillRow) {
        if (!is_array($skillRow)) {
            continue;
        }
        $key = isset($skillRow['key']) ? (string) $skillRow['key'] : '';
        if ($key === '') {
            continue;
        }
        $skillLookup[$key] = $skillRow;
    }
}

$formatProfileFieldValue = static function (string $fieldKey) use ($sampleData, $organization, $user) {
    switch ($fieldKey) {
        case 'full_name':
            if (!empty($sampleData['user_full_name'])) {
                return (string) $sampleData['user_full_name'];
            }
            $first = $sampleData['user_first_name'] ?? ($user['first_name'] ?? '');
            $last = $sampleData['user_last_name'] ?? ($user['last_name'] ?? '');
            $full = trim($first . ' ' . $last);
            if ($full !== '') {
                return $full;
            }
            return 'کاربر نمونه';
        case 'national_id':
            $value = $sampleData['user_national_id'] ?? ($user['national_code'] ?? '');
            return $value !== '' ? UtilityHelper::englishToPersian((string) $value) : '—';
        case 'personnel_code':
            $value = $sampleData['user_personnel_code'] ?? ($user['personnel_code'] ?? '');
            return $value !== '' ? UtilityHelper::englishToPersian((string) $value) : '—';
        case 'job_title':
            return $sampleData['user_job_title'] ?? ($user['job_title'] ?? '—');
        case 'organization_post':
            return $sampleData['user_organization_post'] ?? ($user['organization_post'] ?? '—');
        case 'department':
            return $sampleData['user_department'] ?? ($user['department'] ?? '—');
        case 'service_location':
            return $sampleData['user_service_location'] ?? ($user['service_location'] ?? '—');
        case 'username':
            $value = $sampleData['user_username'] ?? ($user['username'] ?? '');
            return $value !== '' ? $value : '—';
        default:
            return '—';
    }
};

$elementWidthClass = static function (array $props) {
    $mode = '';
    if (isset($props['widthMode']) && is_string($props['widthMode'])) {
        $mode = strtolower($props['widthMode']);
    } elseif (isset($props['layout']) && is_string($props['layout'])) {
        $mode = strtolower($props['layout']);
    }
    if (!in_array($mode, ['half', 'full'], true)) {
        $mode = 'full';
    }
    return $mode === 'half' ? 'certificate-element-half' : '';
};

$activePageData = null;
foreach ($pages as $pageCandidate) {
    if ($activePageId !== '' && isset($pageCandidate['id']) && $pageCandidate['id'] === $activePageId) {
        $activePageData = $pageCandidate;
        break;
    }
}

if ($activePageData === null && !empty($pages)) {
    $activePageData = $pages[0];
}

$activePageSummaryLabel = null;
if ($activePageData !== null) {
    $activeSizeClass = $normalizePageSize($activePageData['size'] ?? 'a4');
    $activeOrientationClass = $normalizeOrientation($activePageData['orientation'] ?? 'portrait');
    $activeSizeLabel = $pageSizeLabels[$activeSizeClass] ?? strtoupper($activeSizeClass);
    $activeOrientationLabel = $orientationLabels[$activeOrientationClass] ?? 'عمودی';
    $activePageSummaryLabel = $activeSizeLabel . ' / ' . $activeOrientationLabel;
}
$signers = isset($sampleData['signers']) && is_array($sampleData['signers']) ? $sampleData['signers'] : [];

$globalElements = [];
$globalElementIds = [];
foreach ($pages as $pageCandidate) {
    $pageElements = isset($pageCandidate['elements']) && is_array($pageCandidate['elements']) ? $pageCandidate['elements'] : [];
    foreach ($pageElements as $elementCandidate) {
        if (!is_array($elementCandidate)) {
            continue;
        }
        $propsCandidate = isset($elementCandidate['props']) && is_array($elementCandidate['props']) ? $elementCandidate['props'] : [];
        if (!$isTruthy($propsCandidate['applyToAllPages'] ?? 0)) {
            continue;
        }
        $elementId = isset($elementCandidate['id']) ? (string) $elementCandidate['id'] : '';
        if ($elementId === '') {
            $elementId = 'shared-' . md5(json_encode($elementCandidate));
        }
        if (!isset($globalElementIds[$elementId])) {
            $globalElementIds[$elementId] = true;
            $globalElements[] = $elementCandidate;
        }
    }
}

?>

<div class="page-content-wrapper certificate-preview-wrapper">
    <div class="page-content">
        
        <?php if ($isEmbedded): ?>
            <!-- Floating Download PDF Controls -->
            <div class="floating-pdf-controls">
                <button type="button" 
                   id="downloadPdfBtn" 
                   data-action="download-certificate-pdf"
                   class="floating-download-btn" 
                   title="دانلود گواهی به صورت PDF">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="7 10 12 15 17 10"/>
                        <line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                    <span>دانلود PDF</span>
                </button>
            </div>
            
            <style>
                /* Certificate PDF Controls Styling */
                /* Persian font optimization - CRITICAL for proper character joining */
                
                * {
                    font-feature-settings: "kern" 1, "liga" 1 !important;
                    font-kerning: normal !important;
                }
                
                body, div, p, span, td, th, li, h1, h2, h3, h4, h5, h6 {
                    font-family: Tahoma, Arial, sans-serif !important;
                    text-rendering: geometricPrecision !important;
                    -webkit-font-smoothing: subpixel-antialiased !important;
                    letter-spacing: 0 !important;
                }
                
                .floating-pdf-controls {
                    position: fixed;
                    bottom: 30px;
                    right: 30px;
                    z-index: 99999;
                    font-family: Tahoma, Arial, sans-serif;
                }
                
                .floating-download-btn {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    padding: 14px 24px;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    border: none;
                    border-radius: 50px;
                    font-size: 15px;
                    font-weight: 600;
                    cursor: pointer;
                    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
                    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                    font-family: 'Vazirmatn', sans-serif;
                    animation: pulse 2s ease-in-out infinite;
                }
                
                .floating-download-btn:hover {
                    transform: translateY(-3px);
                    box-shadow: 0 15px 40px rgba(102, 126, 234, 0.6);
                    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
                }
                
                .floating-download-btn:active {
                    transform: translateY(-1px);
                    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.5);
                }
                
                .floating-download-btn.is-loading {
                    cursor: not-allowed;
                    opacity: 0.7;
                    pointer-events: none;
                }
                
                .floating-download-btn:disabled {
                    cursor: not-allowed;
                    opacity: 0.6;
                }
                
                .floating-download-btn svg {
                    width: 20px;
                    height: 20px;
                    stroke-width: 2.5;
                    transition: transform 0.3s ease;
                }
                
                .floating-download-btn:hover svg {
                    transform: translateY(2px);
                }
                
                .floating-download-btn span {
                    font-size: 15px;
                    line-height: 1;
                }
                
                /* Pulse animation */
                @keyframes pulse {
                    0%, 100% {
                        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
                    }
                    50% {
                        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.7);
                    }
                }
                
                @keyframes spin {
                    from { transform: rotate(0deg); }
                    to { transform: rotate(360deg); }
                }
            </style>
            
            <!-- Loading Modal -->
            <div id="pdfLoadingModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); z-index: 999999; backdrop-filter: blur(5px);">
                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; border-radius: 20px; padding: 40px 60px; text-align: center; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
                    <div style="margin-bottom: 20px;">
                        <div style="width: 60px; height: 60px; border: 4px solid #e5e7eb; border-top-color: #667eea; border-radius: 50%; margin: 0 auto; animation: spin 0.8s linear infinite;"></div>
                    </div>
                    <h3 style="margin: 0 0 10px 0; font-size: 20px; color: #1f2937;">در حال تولید PDF...</h3>
                    <p style="margin: 0; color: #6b7280; font-size: 14px;" id="pdfProgressText">لطفاً صبر کنید</p>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (!$isEmbedded): ?>
            <div class="certificate-preview-toolbar">
                <div class="d-flex flex-wrap align-items-center gap-10">
                    <a href="<?= UtilityHelper::baseUrl('organizations/reports/certificate-builder'); ?>" class="btn btn-outline-secondary">
                        <ion-icon name="arrow-back-outline"></ion-icon>
                        بازگشت به سازنده
                    </a>
                    <button type="button" class="btn btn-main" data-action="download-certificate-pdf" data-export-filename="<?= $escape($pdfFileName); ?>">
                        <ion-icon name="download-outline"></ion-icon>
                        دانلود PDF
                    </button>
                    <a href="#" onclick="window.print(); return false;" class="btn btn-outline-main">
                        <ion-icon name="print-outline"></ion-icon>
                        چاپ یا ذخیره PDF
                    </a>
                </div>
                <?php if (!empty($pages)): ?>
                    <div class="d-flex align-items-center gap-8">
                        <label for="page-selector" class="form-label mb-0 small text-muted">انتخاب صفحه</label>
                        <select id="page-selector" class="form-select" onchange="if(this.value){document.getElementById(this.value)?.scrollIntoView({behavior:'smooth'});}">
                            <?php foreach ($pages as $page): ?>
                                <?php $pageIdForOption = $escape($page['id'] ?? ''); ?>
                                <option value="certificate-page-<?= $pageIdForOption; ?>" <?= ($page['id'] ?? '') === $activePageId ? 'selected' : ''; ?>>
                                    <?= $escape($page['name'] ?? 'صفحه'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($hasEvaluations)): ?>
            <div class="certificate-preview-selection">
                <form method="get" action="<?= $escape($selectionActionUrl); ?>" class="certificate-preview-selection-form">
                    <?php
                        $preservedQuery = $_GET ?? [];
                        if (is_array($preservedQuery)) {
                            unset($preservedQuery['evaluation_id'], $preservedQuery['evaluation'], $preservedQuery['evaluationId'], $preservedQuery['evaluatee_id'], $preservedQuery['evaluatee'], $preservedQuery['evaluateeId']);
                            foreach ($preservedQuery as $paramKey => $paramValue) {
                                if (!is_string($paramKey)) { continue; }
                                if (is_array($paramValue)) { continue; }
                                echo '<input type="hidden" name="' . $escape($paramKey) . '" value="' . $escape((string) $paramValue) . '">';
                            }
                        }
                    ?>
                    <div class="selection-field">
                        <label for="evaluation-selector" class="form-label">برنامه ارزیابی</label>
                        <select id="evaluation-selector" name="evaluation_id" class="form-select" onchange="const form=this.form; const evaluatee=form.querySelector('#evaluatee-selector'); if(evaluatee){evaluatee.selectedIndex=0;} form.submit();">
                            <?php foreach ($evaluationOptions as $option): ?>
                                <?php $value = (string) ($option['value'] ?? ''); ?>
                                <?php $label = (string) ($option['label'] ?? ''); ?>
                                <option value="<?= $escape($value); ?>" <?= ($value !== '' && (int) $value === $selectedEvaluationId) ? 'selected' : ''; ?>><?= $escape($label); ?></option>
                            <?php endforeach; ?>
                            <?php if (empty($evaluationOptions)): ?>
                                <option value="" selected>برنامه‌ای در دسترس نیست</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="selection-field">
                        <label for="evaluatee-selector" class="form-label">ارزیاب‌شونده</label>
                        <select id="evaluatee-selector" name="evaluatee_id" class="form-select" <?= empty($evaluateeOptions) ? 'disabled' : ''; ?> onchange="this.form.submit();">
                            <option value="">انتخاب ارزیاب‌شونده</option>
                            <?php foreach ($evaluateeOptions as $option): ?>
                                <?php $value = (int) ($option['value'] ?? 0); ?>
                                <?php $label = (string) ($option['label'] ?? ''); ?>
                                <option value="<?= $escape((string) $value); ?>" <?= $value === $selectedEvaluateeId ? 'selected' : ''; ?>><?= $escape($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <div class="certificate-preview-summary">
            <div class="summary-item">
                <div class="label">سازمان</div>
                <div class="value"><?= $escape($organizationName); ?></div>
            </div>
            <?php if ($selectedEvaluationTitle !== ''): ?>
                <div class="summary-item">
                    <div class="label">ارزیابی انتخاب‌شده</div>
                    <div class="value"><?= $escape($selectedEvaluationTitle); ?></div>
                </div>
            <?php endif; ?>
            <?php if ($selectedEvaluationDateLabel !== '' && $selectedEvaluationDateLabel !== '—'): ?>
                <div class="summary-item">
                    <div class="label">تاریخ ارزیابی</div>
                    <div class="value"><?= $escape($selectedEvaluationDateLabel); ?></div>
                </div>
            <?php endif; ?>
            <?php if ($selectedEvaluateeName !== ''): ?>
                <div class="summary-item">
                    <div class="label">ارزیاب‌شونده فعال</div>
                    <div class="value"><?= $escape($selectedEvaluateeName); ?></div>
                </div>
            <?php endif; ?>
            <?php if ($selectedEvaluateeUsername !== ''): ?>
                <div class="summary-item">
                    <div class="label">نام کاربری</div>
                    <div class="value">@<?= $escape($selectedEvaluateeUsername); ?></div>
                </div>
            <?php endif; ?>
            <?php if ($evaluateeCountLabel !== ''): ?>
                <div class="summary-item">
                    <div class="label">تعداد ارزیاب‌شونده‌ها</div>
                    <div class="value"><?= $escape($evaluateeCountLabel); ?></div>
                </div>
            <?php endif; ?>
            <div class="summary-item">
                <div class="label">تعداد صفحات</div>
                <div class="value"><?= $escape((string) $totalPages); ?></div>
            </div>
            <div class="summary-item">
                <div class="label">تاریخ صدور نمونه</div>
                <div class="value"><?= $escape($sampleData['issued_at'] ?? '۱۴۰۴/۰۸/۰۱'); ?></div>
            </div>
            <div class="summary-item">
                <div class="label">نسخه داده</div>
                <div class="value"><?= $escape($dataVersionLabel); ?></div>
            </div>
            <?php if ($activePageSummaryLabel !== null): ?>
                <div class="summary-item">
                    <div class="label">چیدمان صفحه فعال</div>
                    <div class="value"><?= $escape($activePageSummaryLabel); ?></div>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!$hasEvaluations): ?>
            <div class="alert alert-light rounded-16">برای این سازمان هنوز برنامه ارزیابی ثبت نشده است. پس از ایجاد ارزیابی می‌توانید پیش‌نمایش گواهی را با داده‌های واقعی مشاهده کنید.</div>
        <?php elseif ($selectedEvaluation !== null && $selectedEvaluatee === null && !empty($evaluateeOptions)): ?>
            <div class="alert alert-info rounded-16">لطفاً از فهرست بالا یک ارزیاب‌شونده را انتخاب کنید تا نتایج مربوط به او بارگذاری شود.</div>
        <?php elseif ($selectedEvaluation !== null && $selectedEvaluatee !== null && !$hasRuntimeData): ?>
            <div class="alert alert-light rounded-16">برای این ارزیاب‌شونده داده‌ای از آزمون‌های انتخاب‌شده یافت نشد. پیش‌نمایش با داده‌های نمونه نمایش داده می‌شود.</div>
        <?php elseif (empty($pages)): ?>
            <div class="alert alert-warning rounded-16">هنوز صفحه‌ای در سازنده تعریف نشده است. برای مشاهده پیش‌نمایش ابتدا در سازنده حداقل یک صفحه ایجاد کنید.</div>
        <?php endif; ?>

    <div class="certificate-pages-container">
    <?php foreach ($pages as $index => $page): ?>
            <?php
                $pageId = isset($page['id']) ? (string) $page['id'] : 'page-' . ($index + 1);
                $pageName = isset($page['name']) ? (string) $page['name'] : 'صفحه ' . ($index + 1);
                $templateKey = isset($page['template']) ? (string) $page['template'] : 'classic';
                $templateClass = $sanitizeClass($templateKey);
                if ($templateClass === '') {
                    $templateClass = 'classic';
                }
                $sizeClass = $normalizePageSize($page['size'] ?? 'a4');
                $orientationClass = $normalizeOrientation($page['orientation'] ?? 'portrait');
                $sizeLabelText = $pageSizeLabels[$sizeClass] ?? strtoupper($sizeClass);
                $orientationLabelText = $orientationLabels[$orientationClass] ?? 'عمودی';
                $elements = isset($page['elements']) && is_array($page['elements']) ? $page['elements'] : [];
                $isActive = $pageId === $activePageId;
                $pageScale = $getPageScale($sizeClass, $orientationClass);
                if (!empty($globalElements)) {
                    $combinedElementsMap = [];
                    foreach ($globalElements as $sharedElement) {
                        if (!is_array($sharedElement)) {
                            continue;
                        }
                        $sharedId = isset($sharedElement['id']) ? (string) $sharedElement['id'] : '';
                        if ($sharedId !== '') {
                            $combinedElementsMap[$sharedId] = $sharedElement;
                        } else {
                            $combinedElementsMap[] = $sharedElement;
                        }
                    }
                    foreach ($elements as $elementIndex => $elementObj) {
                        if (!is_array($elementObj)) {
                            continue;
                        }
                        $elementIdKey = isset($elementObj['id']) ? (string) $elementObj['id'] : '';
                        if ($elementIdKey !== '') {
                            $combinedElementsMap[$elementIdKey] = $elementObj;
                        } else {
                            $combinedElementsMap[] = $elementObj;
                        }
                    }
                    $elements = array_values($combinedElementsMap);
                }
            ?>
            <section id="certificate-page-<?= $escape($pageId); ?>" class="certificate-preview-page certificate-template-<?= $escape($templateClass); ?> certificate-size-<?= $escape($sizeClass); ?> certificate-orientation-<?= $escape($orientationClass); ?><?= $isActive ? ' is-active' : ''; ?>">
                <div class="certificate-page-header">
                    <h4><?= $escape($pageName); ?></h4>
                    <div class="certificate-page-badges d-flex flex-wrap align-items-center gap-8">
                        <span class="badge bg-light text-dark">قالب: <?= $escape($templateKey); ?></span>
                        <span class="badge bg-light text-dark">اندازه: <?= $escape($sizeLabelText); ?></span>
                        <span class="badge bg-light text-dark">چیدمان: <?= $escape($orientationLabelText); ?></span>
                    </div>
                </div>
                <div class="certificate-page-body" style="--page-scale: <?= $escape(number_format($pageScale, 4, '.', '')); ?>;">
                    <div class="certificate-page-body-inner">
                    <?php foreach ($elements as $element): ?>
                        <?php
                            if (!is_array($element)) {
                                continue;
                            }
                            $type = isset($element['type']) ? (string) $element['type'] : '';
                            if ($type === '' || !isset($componentMap[$type])) {
                                continue;
                            }
                            $props = isset($element['props']) && is_array($element['props']) ? $element['props'] : [];
                            $elementIdAttr = '';
                            if (isset($element['id']) && is_string($element['id']) && $element['id'] !== '') {
                                $elementIdAttr = ' data-element-id="' . $escape($element['id']) . '"';
                            }
                        ?>

                        <?php if ($type === 'hero_heading'): ?>
                            <?php
                                // Support both old 'variant' and new 'fontSize'
                                $fontSize = null;
                                if (isset($props['fontSize']) && is_numeric($props['fontSize'])) {
                                    $fontSize = max(6, min(200, (int)$props['fontSize']));
                                } elseif (isset($props['variant'])) {
                                    // Fallback for old variant system
                                    $variant = $sanitizeClass($props['variant']);
                                    $variantSizes = ['display' => 48, 'headline' => 36, 'title' => 28];
                                    $fontSize = $variantSizes[$variant] ?? 48;
                                }
                                if ($fontSize === null) {
                                    $fontSize = 48; // Default
                                }
                                
                                $alignClass = $alignmentClass($props['align'] ?? 'center');
                                $text = $props['text'] ?? 'عنوان بزرگ گواهی';
                                $color = $sanitizeColor($props['color'] ?? '#111827', '#111827');
                                $styleOption = $sanitizeClass($props['style'] ?? 'plain');
                                $allowedHeroStyles = ['plain', 'pill', 'outline', 'ribbon', 'underline'];
                                if (!in_array($styleOption, $allowedHeroStyles, true)) {
                                    $styleOption = 'plain';
                                }
                                $widthClass = $elementWidthClass($props);
                                $classParts = ['certificate-element', 'certificate-hero', $alignClass, 'style-' . $styleOption, $widthClass];
                                $classAttr = implode(' ', array_filter($classParts));
                                $accentSoft = $colorToRgba($color, 0.18);
                                $accentStrong = $colorToRgba($color, 0.28);
                                $accentLine = $colorToRgba($color, 0.45);
                                $styleAttr = '--hero-color:' . $color . ';--hero-accent:' . $accentSoft . ';--hero-accent-strong:' . $accentStrong . ';--hero-accent-line:' . $accentLine . ';font-size:' . $fontSize . 'px;';
                            ?>
                            <div class="<?= $escape($classAttr); ?>" style="<?= $escape($styleAttr); ?>">
                                <div class="certificate-hero-text">
                                    <?= nl2br($escape($text)); ?>
                                </div>
                            </div>

                        <?php elseif ($type === 'section_heading'): ?>
                            <?php
                                $alignClass = $alignmentClass($props['align'] ?? 'right');
                                $text = $props['text'] ?? 'عنوان بخش';
                                $color = $sanitizeColor($props['color'] ?? '#1f2937', '#1f2937');
                                $showDivider = !empty($props['showDivider']);
                                $styleAttr = '--section-color:' . $color . ';--section-divider-color:' . $color . ';';
                                $widthClass = $elementWidthClass($props);
                                $classParts = ['certificate-element', 'certificate-section-heading', $alignClass, $widthClass];
                                if ($showDivider) {
                                    $classParts[] = 'has-divider';
                                }
                                $classAttr = implode(' ', array_filter($classParts));
                            ?>
                            <div class="<?= $escape($classAttr); ?>" style="<?= $escape($styleAttr); ?>">
                                <span class="certificate-section-label"><?= $escape($text); ?></span>
                                <?php if ($showDivider): ?>
                                    <span class="certificate-section-divider"></span>
                                <?php endif; ?>
                            </div>

                        <?php elseif ($type === 'user_full_name'): ?>
                            <?php
                                $alignClass = $alignmentClass($props['alignment'] ?? 'center');
                                $showLabel = !empty($props['showLabel']);
                                $label = $props['label'] ?? 'نام ارزیاب‌شونده';
                                $value = $sampleData['user_full_name'] ?? 'نام نمونه';
                                $widthClass = $elementWidthClass($props);
                                $classParts = ['certificate-element', $alignClass, $widthClass];
                                $classAttr = implode(' ', array_filter($classParts));
                            ?>
                            <div class="<?= $escape($classAttr); ?>">
                                <?php if ($showLabel): ?>
                                    <span class="certificate-user-label"><?= $escape($label); ?></span>
                                <?php endif; ?>
                                <div class="certificate-user-value"><?= $escape($value); ?></div>
                            </div>

                        <?php elseif ($type === 'user_job_title'): ?>
                            <?php
                                $alignClass = $alignmentClass($props['alignment'] ?? 'center');
                                $showLabel = !empty($props['showLabel']);
                                $label = $props['label'] ?? 'عنوان شغلی';
                                $value = $sampleData['user_job_title'] ?? 'عنوان شغلی نمونه';
                                $widthClass = $elementWidthClass($props);
                                $classParts = ['certificate-element', $alignClass, $widthClass];
                                $classAttr = implode(' ', array_filter($classParts));
                            ?>
                            <div class="<?= $escape($classAttr); ?>">
                                <?php if ($showLabel): ?>
                                    <span class="certificate-user-label"><?= $escape($label); ?></span>
                                <?php endif; ?>
                                <div class="certificate-user-value"><?= $escape($value); ?></div>
                            </div>

                        <?php elseif ($type === 'user_profile_field'): ?>
                            <?php
                                $field = is_string($props['field'] ?? null) ? $props['field'] : 'national_id';
                                $allowedFields = ['full_name', 'national_id', 'personnel_code', 'job_title', 'organization_post', 'department', 'service_location', 'username'];
                                if (!in_array($field, $allowedFields, true)) {
                                    $field = 'national_id';
                                }
                                $alignClass = $alignmentClass($props['alignment'] ?? 'right');
                                $showLabel = !empty($props['showLabel']);
                                $customLabel = isset($props['customLabel']) ? trim((string) $props['customLabel']) : '';
                                $defaultLabels = [
                                    'full_name' => 'نام و نام خانوادگی',
                                    'national_id' => 'کد ملی',
                                    'personnel_code' => 'کد پرسنلی',
                                    'job_title' => 'عنوان شغلی',
                                    'organization_post' => 'پست سازمانی',
                                    'department' => 'واحد سازمانی',
                                    'service_location' => 'محل خدمت',
                                    'username' => 'نام کاربری',
                                ];
                                $label = $customLabel !== '' ? $customLabel : ($defaultLabels[$field] ?? 'اطلاعات کاربر');
                                $value = $formatProfileFieldValue($field);
                                $widthClass = $elementWidthClass($props);
                                $classParts = ['certificate-element', $alignClass, $widthClass];
                                $classAttr = implode(' ', array_filter($classParts));
                            ?>
                            <div class="<?= $escape($classAttr); ?>">
                                <?php if ($showLabel): ?>
                                    <span class="certificate-user-label"><?= $escape($label); ?></span>
                                <?php endif; ?>
                                <div class="certificate-user-value"><?= $escape($value); ?></div>
                            </div>

                        <?php elseif ($type === 'user_profile_overview'): ?>
                            <?php
                                $alignment = is_string($props['alignment'] ?? null) ? $props['alignment'] : 'right';
                                $alignClass = $alignmentClass($alignment);
                                $layout = is_string($props['layout'] ?? null) ? $props['layout'] : 'two-column';
                                $layout = in_array($layout, ['list', 'two-column'], true) ? $layout : 'two-column';
                                $appearance = is_string($props['appearance'] ?? null) ? $props['appearance'] : 'card';
                                $appearance = in_array($appearance, ['card', 'subtle', 'plain'], true) ? $appearance : 'card';
                                $titleText = isset($props['title']) ? trim((string) $props['title']) : '';
                                $showLabels = !empty($props['showLabels']);
                                $fieldsConfig = [
                                    'full_name' => !empty($props['showFullName']),
                                    'national_id' => !empty($props['showNationalId']),
                                    'personnel_code' => !empty($props['showPersonnelCode']),
                                    'job_title' => !empty($props['showJobTitle']),
                                    'organization_post' => !empty($props['showOrganizationPost']),
                                    'department' => !empty($props['showDepartment']),
                                    'service_location' => !empty($props['showServiceLocation']),
                                    'username' => !empty($props['showUsername']),
                                ];
                                $defaultLabels = [
                                    'full_name' => 'نام و نام خانوادگی',
                                    'national_id' => 'کد ملی',
                                    'personnel_code' => 'کد پرسنلی',
                                    'job_title' => 'عنوان شغلی',
                                    'organization_post' => 'پست سازمانی',
                                    'department' => 'واحد سازمانی',
                                    'service_location' => 'محل خدمت',
                                    'username' => 'نام کاربری',
                                ];
                                $rows = [];
                                foreach ($fieldsConfig as $key => $enabled) {
                                    if (!$enabled) {
                                        continue;
                                    }
                                    $rows[] = [
                                        'label' => $defaultLabels[$key] ?? $key,
                                        'value' => $formatProfileFieldValue($key),
                                    ];
                                }
                                if (empty($rows)) {
                                    $rows[] = ['label' => 'اطلاعات کاربر', 'value' => 'موردی برای نمایش انتخاب نشده است.'];
                                }
                                $containerClass = 'certificate-profile-overview appearance-' . $appearance . ' layout-' . $layout;
                                $widthClass = $elementWidthClass($props);
                                $outerClassParts = ['certificate-element', $alignClass, $widthClass];
                                $outerClassAttr = implode(' ', array_filter($outerClassParts));
                            ?>
                            <div class="<?= $escape($outerClassAttr); ?>">
                                <div class="<?= $escape($containerClass); ?>">
                                    <?php if ($titleText !== ''): ?>
                                        <div class="profile-overview-title"><?= $escape($titleText); ?></div>
                                    <?php endif; ?>
                                    <div class="profile-overview-grid">
                                        <?php foreach ($rows as $row): ?>
                                            <div class="profile-overview-item">
                                                <?php if ($showLabels): ?>
                                                    <div class="profile-overview-label"><?= $escape($row['label']); ?></div>
                                                <?php endif; ?>
                                                <div class="profile-overview-value"><?= $escape($row['value']); ?></div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>

                        <?php elseif ($type === 'user_avatar'): ?>
                            <?php
                                $shape = $sanitizeClass($props['shape'] ?? 'circle');
                                if ($shape === '') {
                                    $shape = 'circle';
                                }
                                $size = $sanitizeClass($props['size'] ?? 'medium');
                                if ($size === '') {
                                    $size = 'medium';
                                }
                                $showFrame = !empty($props['showFrame']);
                                $initials = $sampleData['user_initials'] ?? 'SJ';
                                $avatarClass = 'certificate-avatar shape-' . $shape . ' size-' . $size;
                                if ($showFrame) {
                                    $avatarClass .= ' with-frame';
                                }
                                $widthClass = $elementWidthClass($props);
                                $outerClassParts = ['certificate-element', 'certificate-align-center', $widthClass];
                                $outerClassAttr = implode(' ', array_filter($outerClassParts));
                            ?>
                            <div class="<?= $escape($outerClassAttr); ?>">
                                <div class="<?= $escape($avatarClass); ?>"><?= $escape($initials); ?></div>
                            </div>

                        <?php elseif ($type === 'evaluation_summary'): ?>
                            <?php
                                $layout = $props['layout'] ?? 'two-column';
                                $allowedLayouts = ['single-column', 'two-column', 'highlight'];
                                if (!in_array($layout, $allowedLayouts, true)) {
                                    $layout = 'two-column';
                                }
                                $layoutClass = 'layout-' . $layout;
                                $showDates = !empty($props['showDates']);
                                $showEvaluators = !empty($props['showEvaluators']);
                                $showEvaluatees = !empty($props['showEvaluatees']);
                                $headline = isset($props['headline']) ? trim((string) $props['headline']) : '';
                                $widthClass = $elementWidthClass($props);
                                $outerClassAttr = implode(' ', array_filter(['certificate-element', $widthClass]));
                                $accentColor = $sanitizeColor($props['accentColor'] ?? '#2563eb', '#2563eb');
                            ?>
                            <div class="<?= $escape($outerClassAttr); ?>">
                                <?php if ($headline !== ''): ?>
                                    <div class="certificate-summary-headline"><?= $escape($headline); ?></div>
                                <?php endif; ?>
                                <div class="certificate-evaluation-summary <?= $escape($layoutClass); ?>">
                                    <div>
                                        <div class="summary-label">عنوان ارزیابی</div>
                                        <div class="summary-value"><?= $escape($sampleData['evaluation_title'] ?? 'عنوان ارزیابی نمونه'); ?></div>
                                    </div>
                                    <?php if ($showDates): ?>
                                        <div>
                                            <div class="summary-label">دوره اجرا</div>
                                            <div class="summary-value"><?= $escape($sampleData['evaluation_period'] ?? 'نامشخص'); ?></div>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($showEvaluators): ?>
                                        <div>
                                            <div class="summary-label">تعداد ارزیاب</div>
                                            <div class="summary-value"><?= $escape($sampleData['evaluators_count'] ?? '۶ ارزیاب'); ?></div>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($showEvaluatees): ?>
                                        <div>
                                            <div class="summary-label">تعداد ارزیاب‌شونده</div>
                                            <div class="summary-value"><?= $escape($sampleData['evaluatees_count'] ?? '۱ ارزیاب‌شونده'); ?></div>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="summary-label">تاریخ صدور</div>
                                        <div class="summary-value"><?= $escape($sampleData['issued_at'] ?? '۱۴۰۴/۰۸/۰۱'); ?></div>
                                    </div>
                                </div>
                            </div>

                        <?php elseif ($type === 'score_badges'): ?>
                            <?php
                                $displayMode = $props['displayMode'] ?? 'badges';
                                if (!in_array($displayMode, ['badges', 'progress', 'tiles'], true)) {
                                    $displayMode = 'badges';
                                }
                                $showOverallScore = !empty($props['showOverallScore']);
                                $showRanking = !empty($props['showRanking']);
                                $overallScore = $sampleData['overall_score'] ?? '۸۶';
                                $overallSuffix = $sampleData['overall_score_suffix'] ?? 'از ۱۰۰';
                                $overallNumeric = isset($sampleData['overall_score_numeric']) ? (int) $sampleData['overall_score_numeric'] : 86;
                                $overallNumeric = max(0, min(100, $overallNumeric));
                                $rankingPosition = isset($sampleData['ranking_position']) ? (int) $sampleData['ranking_position'] : 10;
                                $rankingTotal = isset($sampleData['ranking_total']) ? (int) $sampleData['ranking_total'] : 120;
                                $overallBadgeClass = 'certificate-score-badge' . ($displayMode === 'tiles' ? ' dark' : '');
                                $rankingBadgeClass = 'certificate-score-badge' . ($displayMode === 'tiles' ? '' : ' dark');
                                $accentColor = $sanitizeColor($props['accentColor'] ?? '#0f766e', '#0f766e');
                                $scoreStyle = '--score-accent:' . $accentColor . ';';
                                $widthClass = $elementWidthClass($props);
                                $outerClassAttr = implode(' ', array_filter(['certificate-element', $widthClass]));
                            ?>
                            <div class="<?= $escape($outerClassAttr); ?>">
                                <div class="certificate-score-badges" style="<?= $escape($scoreStyle); ?>">
                                    <?php if ($showOverallScore): ?>
                                        <div class="<?= $escape($overallBadgeClass); ?>">
                                            <div class="score-label">امتیاز کلی</div>
                                            <div class="score-value"><?= $escape($overallScore); ?></div>
                                            <div class="text-muted small"><?= $escape($overallSuffix); ?></div>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($displayMode === 'progress'): ?>
                                        <div class="certificate-score-badge progress">
                                            <div class="score-label">پیشرفت به هدف</div>
                                            <div class="certificate-score-progress-bar">
                                                <div class="certificate-score-progress" style="width: <?= (int) $overallNumeric; ?>%;"></div>
                                            </div>
                                            <div class="text-muted small mt-2">هدف تعیین‌شده سازمان</div>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($showRanking): ?>
                                        <div class="<?= $escape($rankingBadgeClass); ?>">
                                            <div class="score-label">رتبه نسبی</div>
                                            <div class="score-value"><?= $escape($rankingPosition); ?></div>
                                            <div class="text-muted small">از <?= $escape($rankingTotal); ?> نفر</div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                        <?php elseif ($type === 'dynamic_table'): ?>
                            <?php
                                $modeRaw = isset($props['mode']) && is_string($props['mode']) ? strtolower(trim($props['mode'])) : 'custom';
                                $mode = in_array($modeRaw, ['custom', 'competency_model', 'evaluation_tools'], true) ? $modeRaw : 'custom';
                                $showTitle = $isTruthy($props['showTitle'] ?? 1);
                                $titleText = isset($props['title']) ? trim((string) $props['title']) : '';
                                $showHeader = $isTruthy($props['showHeader'] ?? 1);
                                $showRowNumbers = $isTruthy($props['showRowNumbers'] ?? 0);
                                $showCompetencyDefinition = $isTruthy($props['competencyShowDefinition'] ?? 1);
                                $showToolDescription = $isTruthy($props['toolsShowDescription'] ?? 1);
                                $widthClass = $elementWidthClass($props);
                                $outerClassAttr = implode(' ', array_filter(['certificate-element', $widthClass]));
                                $tableStyleRaw = isset($props['tableStyle']) && is_string($props['tableStyle']) ? strtolower(trim($props['tableStyle'])) : 'grid';
                                $allowedTableStyles = ['grid', 'minimal', 'striped', 'soft'];
                                $tableStyle = in_array($tableStyleRaw, $allowedTableStyles, true) ? $tableStyleRaw : 'grid';
                                $tableBlockClasses = ['certificate-table-block', 'certificate-table-style-' . $tableStyle];
                                $tableClassAttr = implode(' ', ['certificate-table', 'certificate-table--' . $tableStyle]);
                                $tableColumns = [];
                                $tableRows = [];
                                $emptyMessage = null;

                                if ($mode === 'custom') {
                                    $tableData = isset($props['tableData']) && is_array($props['tableData']) ? $props['tableData'] : [];
                                    $rawColumns = isset($tableData['columns']) && is_array($tableData['columns']) ? $tableData['columns'] : [];
                                    foreach ($rawColumns as $columnValue) {
                                        if (!is_string($columnValue)) {
                                            continue;
                                        }
                                        $clean = trim($columnValue);
                                        if ($clean === '') {
                                            $clean = 'ستون';
                                        }
                                        if (function_exists('mb_substr')) {
                                            $clean = mb_substr($clean, 0, 120, 'UTF-8');
                                        } else {
                                            $clean = substr($clean, 0, 120);
                                        }
                                        $tableColumns[] = $clean;
                                        if (count($tableColumns) >= 8) {
                                            break;
                                        }
                                    }
                                    if (empty($tableColumns)) {
                                        $tableColumns = ['ستون اول', 'ستون دوم'];
                                    }

                                    $rawRows = isset($tableData['rows']) && is_array($tableData['rows']) ? $tableData['rows'] : [];
                                    foreach ($rawRows as $rowValues) {
                                        if (!is_array($rowValues)) {
                                            continue;
                                        }
                                        $row = [];
                                        foreach ($tableColumns as $index => $columnTitle) {
                                            $cell = $rowValues[$index] ?? '';
                                            if (!is_string($cell)) {
                                                $cell = (string) $cell;
                                            }
                                            $cell = trim($cell);
                                            if (function_exists('mb_substr')) {
                                                $cell = mb_substr($cell, 0, 240, 'UTF-8');
                                            } else {
                                                $cell = substr($cell, 0, 240);
                                            }
                                            $row[] = $cell;
                                        }
                                        $tableRows[] = $row;
                                        if (count($tableRows) >= 20) {
                                            break;
                                        }
                                    }
                                    if (empty($tableRows)) {
                                        $tableRows[] = array_fill(0, count($tableColumns), '');
                                    }
                                } elseif ($mode === 'competency_model') {
                                    $dataset = isset($sampleData['competencies']) && is_array($sampleData['competencies']) ? $sampleData['competencies'] : [];
                                    $tableColumns[] = 'شایستگی';
                                    if ($showCompetencyDefinition) {
                                        $tableColumns[] = 'تعریف';
                                    }
                                    foreach ($dataset as $item) {
                                        if (!is_array($item)) {
                                            continue;
                                        }
                                        $name = isset($item['name']) ? trim((string) $item['name']) : '';
                                        $definition = isset($item['definition']) ? trim((string) $item['definition']) : '';
                                        if ($name === '') {
                                            continue;
                                        }
                                        $row = [$name];
                                        if ($showCompetencyDefinition) {
                                            $row[] = $definition;
                                        }
                                        $tableRows[] = $row;
                                    }
                                    if (empty($tableRows)) {
                                        $emptyMessage = 'اطلاعات شایستگی برای نمایش موجود نیست.';
                                    }
                                } else {
                                    $dataset = isset($sampleData['evaluation_tools']) && is_array($sampleData['evaluation_tools']) ? $sampleData['evaluation_tools'] : [];
                                    $tableColumns[] = 'ابزار ارزیابی';
                                    if ($showToolDescription) {
                                        $tableColumns[] = 'توضیح';
                                    }
                                    foreach ($dataset as $item) {
                                        if (!is_array($item)) {
                                            continue;
                                        }
                                        $name = isset($item['name']) ? trim((string) $item['name']) : '';
                                        $description = isset($item['description']) ? trim((string) $item['description']) : '';
                                        if ($name === '') {
                                            continue;
                                        }
                                        $row = [$name];
                                        if ($showToolDescription) {
                                            $row[] = $description;
                                        }
                                        $tableRows[] = $row;
                                    }
                                    if (empty($tableRows)) {
                                        $emptyMessage = 'اطلاعات ابزارهای انتخاب‌شده در دسترس نیست.';
                                    }
                                }

                                if (empty($tableColumns)) {
                                    $tableColumns = ['ستون'];
                                }

                                $renderHeader = $mode === 'custom' ? $showHeader : true;

                                if ($showTitle && $titleText === '') {
                                    if ($mode === 'competency_model') {
                                        $titleText = $sampleData['competency_model_name'] ?? 'شایستگی‌های ارزیابی';
                                    } elseif ($mode === 'evaluation_tools') {
                                        $titleText = 'ابزارهای ارزیابی انتخاب‌شده';
                                    } else {
                                        $titleText = 'جدول اطلاعات';
                                    }
                                }

                                $sizeBehaviorRaw = isset($props['sizeBehavior']) && is_string($props['sizeBehavior']) ? strtolower(trim($props['sizeBehavior'])) : 'auto_scale';
                                $allowedSizeBehaviors = ['auto_scale', 'allow_split'];
                                $sizeBehavior = in_array($sizeBehaviorRaw, $allowedSizeBehaviors, true) ? $sizeBehaviorRaw : 'auto_scale';
                                $tableBlockClasses[] = $sizeBehavior === 'allow_split' ? 'certificate-table-behavior-split' : 'certificate-table-behavior-fit';
                                $tableBlockClassAttr = implode(' ', $tableBlockClasses);
                            ?>
                            <div class="<?= $escape($outerClassAttr); ?>"<?= $elementIdAttr; ?>>
                                <div class="<?= $escape($tableBlockClassAttr); ?>" data-size-behavior="<?= $escape($sizeBehavior); ?>">
                                    <?php if ($showTitle && $titleText !== ''): ?>
                                        <div class="certificate-table-title"><?= $escape($titleText); ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($tableRows)): ?>
                                        <?php
                                            $tableChunks = [$tableRows];
                                            $tableChunkOffsets = [0];
                                            if ($sizeBehavior === 'allow_split') {
                                                $rowsPerChunk = $getTableRowsPerChunk($sizeClass, $orientationClass, count($tableColumns));
                                                if ($rowsPerChunk > 0 && count($tableRows) > $rowsPerChunk) {
                                                    $tableChunks = [];
                                                    $tableChunkOffsets = [];
                                                    $totalRows = count($tableRows);
                                                    for ($offset = 0; $offset < $totalRows; $offset += $rowsPerChunk) {
                                                        $tableChunks[] = array_slice($tableRows, $offset, $rowsPerChunk);
                                                        $tableChunkOffsets[] = $offset;
                                                    }
                                                }
                                            }
                                            $tableChunkCount = count($tableChunks);
                                        ?>
                                        <?php foreach ($tableChunks as $chunkIndex => $chunkRows): ?>
                                            <?php
                                                $chunkOffset = $tableChunkOffsets[$chunkIndex] ?? 0;
                                                $chunkWrapperClasses = ['certificate-table-wrapper'];
                                                if ($sizeBehavior === 'allow_split' && $chunkIndex > 0) {
                                                    $chunkWrapperClasses[] = 'is-continuation';
                                                }
                                                $chunkWrapperClassAttr = implode(' ', $chunkWrapperClasses);
                                            ?>
                                            <div class="certificate-table-chunk" data-chunk-index="<?= $chunkIndex; ?>" data-chunk-count="<?= $tableChunkCount; ?>">
                                                <div class="<?= $escape($chunkWrapperClassAttr); ?>" dir="rtl">
                                                    <?php if ($sizeBehavior === 'allow_split' && $chunkIndex > 0): ?>
                                                        <div class="certificate-table-continuation-label">ادامه جدول</div>
                                                    <?php endif; ?>
                                                    <table class="<?= $escape($tableClassAttr); ?>">
                                                    <?php if ($renderHeader): ?>
                                                        <thead>
                                                            <tr>
                                                                <?php if ($showRowNumbers): ?>
                                                                    <th class="certificate-table-index">#</th>
                                                                <?php endif; ?>
                                                                <?php foreach ($tableColumns as $columnLabel): ?>
                                                                    <th><?= $escape($columnLabel); ?></th>
                                                                <?php endforeach; ?>
                                                            </tr>
                                                        </thead>
                                                    <?php endif; ?>
                                                    <tbody>
                                                        <?php foreach ($chunkRows as $rowNumber => $rowCells): ?>
                                                            <tr>
                                                                <?php if ($showRowNumbers): ?>
                                                                    <td class="certificate-table-index"><?= $escape(UtilityHelper::englishToPersian((string) ($chunkOffset + $rowNumber + 1))); ?></td>
                                                                <?php endif; ?>
                                                                <?php foreach ($rowCells as $cellValue): ?>
                                                                    <td><?= nl2br($escape($cellValue)); ?></td>
                                                                <?php endforeach; ?>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="alert alert-light text-muted mb-0"><?= $escape($emptyMessage ?? 'داده‌ای برای نمایش یافت نشد.'); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                        <?php elseif ($type === 'assessment_tool_cards'): ?>
                            <?php
                                $layoutRaw = isset($props['layout']) && is_string($props['layout']) ? strtolower(trim($props['layout'])) : 'grid';
                                $allowedLayouts = ['grid', 'list', 'compact'];
                                $layout = in_array($layoutRaw, $allowedLayouts, true) ? $layoutRaw : 'grid';
                                $showIcons = $isTruthy($props['showIcons'] ?? 1);
                                $showDescription = $isTruthy($props['showDescription'] ?? 1);
                                $showCategory = $isTruthy($props['showCategory'] ?? 1);
                                $showMeta = $isTruthy($props['showMeta'] ?? 1);
                                $categoryFilter = isset($props['categoryFilter']) && is_string($props['categoryFilter']) ? trim($props['categoryFilter']) : '';
                                $maxItemsRaw = isset($props['maxItems']) ? (int) $props['maxItems'] : 6;
                                $maxItems = $maxItemsRaw > 0 ? min($maxItemsRaw, 20) : 6;
                                $displayModeRaw = isset($props['displayMode']) && is_string($props['displayMode']) ? strtolower(trim($props['displayMode'])) : 'all';
                                $displayMode = in_array($displayModeRaw, ['all', 'selected'], true) ? $displayModeRaw : 'all';
                                $selectedToolIds = [];
                                if (isset($props['selectedToolIds'])) {
                                    $rawSelected = $props['selectedToolIds'];
                                    if (is_string($rawSelected)) {
                                        $decoded = json_decode($rawSelected, true);
                                        if (is_array($decoded)) {
                                            $rawSelected = $decoded;
                                        } else {
                                            $rawSelected = preg_split('/\s*,\s*/', $rawSelected);
                                        }
                                    }
                                    if (is_array($rawSelected)) {
                                        foreach ($rawSelected as $selectedValue) {
                                            if (is_array($selectedValue)) {
                                                if (isset($selectedValue['value'])) {
                                                    $selectedValue = $selectedValue['value'];
                                                } elseif (isset($selectedValue['id'])) {
                                                    $selectedValue = $selectedValue['id'];
                                                }
                                            }
                                            if (!is_string($selectedValue) && !is_numeric($selectedValue)) {
                                                continue;
                                            }
                                            $selectedValue = trim((string) $selectedValue);
                                            if ($selectedValue === '') {
                                                continue;
                                            }
                                            if (!in_array($selectedValue, $selectedToolIds, true)) {
                                                $selectedToolIds[] = $selectedValue;
                                            }
                                        }
                                    }
                                }
                                $allTools = [];
                                // Prefer real runtime dataset of assessment tools; fallback to sample data
                                $toolsSource = [];
                                if (isset($runtimeDatasets['assessment_tools']) && is_array($runtimeDatasets['assessment_tools']) && !empty($runtimeDatasets['assessment_tools'])) {
                                    $toolsSource = $runtimeDatasets['assessment_tools'];
                                } elseif (isset($sampleData['assessment_tools']) && is_array($sampleData['assessment_tools'])) {
                                    $toolsSource = $sampleData['assessment_tools'];
                                }
                                if (!empty($toolsSource)) {
                                    foreach ($toolsSource as $toolRow) {
                                        if (!is_array($toolRow)) {
                                            continue;
                                        }
                                        $toolId = '';
                                        if (isset($toolRow['id'])) {
                                            $toolId = trim((string) $toolRow['id']);
                                        } elseif (isset($toolRow['code'])) {
                                            $toolId = trim((string) $toolRow['code']);
                                        }
                                        if ($toolId === '') {
                                            $toolId = isset($toolRow['name']) ? md5((string) $toolRow['name']) : md5(json_encode($toolRow));
                                        }
                                        $allTools[] = [
                                            'id' => $toolId,
                                            'name' => isset($toolRow['name']) ? (string) $toolRow['name'] : '',
                                            'category' => isset($toolRow['category']) ? (string) $toolRow['category'] : '',
                                            'description' => isset($toolRow['description']) ? (string) $toolRow['description'] : '',
                                            'estimatedTime' => isset($toolRow['estimatedTime']) ? (string) $toolRow['estimatedTime'] : '',
                                            'difficulty' => isset($toolRow['difficulty']) ? (string) $toolRow['difficulty'] : '',
                                            'icon' => isset($toolRow['icon']) ? (string) $toolRow['icon'] : 'ellipse-outline',
                                            // Meta fields (if present in runtime dataset)
                                            'status_label' => isset($toolRow['status_label']) ? (string) $toolRow['status_label'] : '',
                                            'progress_label' => isset($toolRow['progress_label']) ? (string) $toolRow['progress_label'] : '',
                                            'score_label' => isset($toolRow['score_label']) ? (string) $toolRow['score_label'] : '',
                                            'completed_at_label' => isset($toolRow['completed_at_label']) ? (string) $toolRow['completed_at_label'] : '',
                                        ];
                                    }
                                }
                                if ($displayMode === 'selected') {
                                    if (!empty($selectedToolIds)) {
                                        $toolMap = [];
                                        foreach ($allTools as $toolEntry) {
                                            $toolMap[$toolEntry['id']] = $toolEntry;
                                        }
                                        $ordered = [];
                                        foreach ($selectedToolIds as $selectedId) {
                                            if (isset($toolMap[$selectedId])) {
                                                $ordered[] = $toolMap[$selectedId];
                                            }
                                        }
                                        $allTools = $ordered;
                                    } else {
                                        $allTools = [];
                                    }
                                }
                                if ($categoryFilter !== '') {
                                    $allTools = array_values(array_filter($allTools, static function (array $tool) use ($categoryFilter) {
                                        return stripos($tool['category'], $categoryFilter) !== false;
                                    }));
                                }
                                $toolsToDisplay = $displayMode === 'all'
                                    ? array_slice($allTools, 0, $maxItems)
                                    : $allTools;
                                $widthClass = $elementWidthClass($props);
                                $outerClassAttr = implode(' ', array_filter(['certificate-element', $widthClass]));
                            ?>
                            <div class="<?= $escape($outerClassAttr); ?>"<?= $elementIdAttr; ?>>
                                <div class="certificate-assessment-cards layout-<?= $escape($layout); ?>">
                                    <?php if (!empty($toolsToDisplay)): ?>
                                        <?php foreach ($toolsToDisplay as $tool): ?>
                                            <?php
                                                $iconName = $sanitizeIconName($tool['icon'], 'flask-outline');
                                                $name = $tool['name'] !== '' ? $tool['name'] : 'ابزار ارزیابی';
                                                $category = $tool['category'];
                                                $description = $tool['description'];
                                                $estimatedTime = $tool['estimatedTime'];
                                                $difficulty = $tool['difficulty'];
                                                $statusLabel = isset($tool['status_label']) ? trim((string) $tool['status_label']) : '';
                                                $progressLabel = isset($tool['progress_label']) ? trim((string) $tool['progress_label']) : '';
                                                $scoreLabel = isset($tool['score_label']) ? trim((string) $tool['score_label']) : '';
                                                $completedAtLabel = isset($tool['completed_at_label']) ? trim((string) $tool['completed_at_label']) : '';
                                                $showStatus = $statusLabel !== '';
                                                $showProgress = $progressLabel !== '';
                                                $showScore = $scoreLabel !== '';
                                                $showCompletedAt = $completedAtLabel !== '';
                                                $hasMetaContent = $estimatedTime !== '' || $difficulty !== '' || $showStatus || $showProgress || $showScore || $showCompletedAt;
                                            ?>
                                            <div class="certificate-assessment-card">
                                                <?php if ($showIcons): ?>
                                                    <div class="certificate-assessment-card-icon">
                                                        <ion-icon name="<?= $escape($iconName); ?>"></ion-icon>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="certificate-assessment-card-content">
                                                    <div class="certificate-assessment-card-header">
                                                        <div class="certificate-assessment-card-title"><?= $escape($name); ?></div>
                                                        <?php if ($showCategory && $category !== ''): ?>
                                                            <span class="certificate-assessment-card-category"><?= $escape($category); ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php if ($showDescription && $description !== ''): ?>
                                                        <p class="certificate-assessment-card-description"><?= $escape($description); ?></p>
                                                    <?php endif; ?>
                                                    <?php if ($showMeta && $hasMetaContent): ?>
                                                        <div class="certificate-assessment-card-meta">
                                                            <?php if ($estimatedTime !== ''): ?>
                                                                <span class="meta-pill">
                                                                    <ion-icon name="time-outline"></ion-icon>
                                                                    <?= $escape($estimatedTime); ?>
                                                                </span>
                                                            <?php endif; ?>
                                                            <?php if ($difficulty !== ''): ?>
                                                                <span class="meta-pill">
                                                                    <ion-icon name="bar-chart-outline"></ion-icon>
                                                                    <?= $escape($difficulty); ?>
                                                                </span>
                                                            <?php endif; ?>
                                                            <?php if ($showStatus): ?>
                                                                <span class="meta-pill">
                                                                    <ion-icon name="checkmark-circle-outline"></ion-icon>
                                                                    <?= $escape($statusLabel); ?>
                                                                </span>
                                                            <?php endif; ?>
                                                            <?php if ($showProgress): ?>
                                                                <span class="meta-pill">
                                                                    <ion-icon name="list-outline"></ion-icon>
                                                                    <?= $escape($progressLabel); ?>
                                                                </span>
                                                            <?php endif; ?>
                                                            <?php if ($showScore): ?>
                                                                <span class="meta-pill">
                                                                    <ion-icon name="ribbon-outline"></ion-icon>
                                                                    <?= $escape($scoreLabel); ?>
                                                                </span>
                                                            <?php endif; ?>
                                                            <?php if ($showCompletedAt): ?>
                                                                <span class="meta-pill">
                                                                    <ion-icon name="calendar-outline"></ion-icon>
                                                                    <?= $escape($completedAtLabel); ?>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="certificate-assessment-empty">
                                            <ion-icon name="information-circle-outline"></ion-icon>
                                            <span>ابزاری برای نمایش یافت نشد.</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                        <?php elseif ($type === 'public_profile'): ?>
                            <?php
                                $profileDataset = isset($runtimeDatasets['public_profile']) && is_array($runtimeDatasets['public_profile']) ? $runtimeDatasets['public_profile'] : [];
                                $profileData = !empty($profileDataset)
                                    ? $profileDataset
                                    : (isset($sampleData['public_profile']) && is_array($sampleData['public_profile']) ? $sampleData['public_profile'] : []);
                                $headlineProp = isset($props['headline']) ? trim((string) $props['headline']) : '';
                                $defaultHeadline = isset($profileData['headline']) ? trim((string) $profileData['headline']) : 'پروفایل رفتاری';
                                $headline = $headlineProp !== '' ? $headlineProp : $defaultHeadline;
                                $showHeadline = $isTruthy($props['showHeadline'] ?? 1);
                                $showSummary = $isTruthy($props['showSummary'] ?? 1);
                                $layoutRaw = isset($props['layout']) && is_string($props['layout']) ? strtolower(trim($props['layout'])) : 'split';
                                $layout = in_array($layoutRaw, ['split', 'stacked'], true) ? $layoutRaw : 'split';
                                $styleRaw = isset($props['style']) && is_string($props['style']) ? strtolower(trim($props['style'])) : 'card';
                                $style = in_array($styleRaw, ['card', 'panel', 'minimal'], true) ? $styleRaw : 'card';
                                $accentColor = $sanitizeColor($props['accentColor'] ?? ($profileData['accentColor'] ?? '#2563eb'), '#2563eb');
                                $personaName = isset($profileData['persona_name']) ? trim((string) $profileData['persona_name']) : '';
                                $styleName = isset($profileData['style_name']) ? trim((string) $profileData['style_name']) : '';
                                $summaryText = isset($profileData['summary']) ? trim((string) $profileData['summary']) : '';
                                if ($summaryText === '' && isset($profileData['short_description'])) {
                                    $summaryText = trim((string) $profileData['short_description']);
                                }

                                $sectionsRaw = isset($profileData['sections']) && is_array($profileData['sections']) ? $profileData['sections'] : [];
                                $sectionsNormalized = [];
                                foreach ($sectionsRaw as $sectionKey => $sectionValue) {
                                    if (!is_array($sectionValue)) {
                                        continue;
                                    }
                                    $keyCandidate = is_string($sectionKey) ? trim($sectionKey) : '';
                                    if ($keyCandidate === '') {
                                        if (isset($sectionValue['key'])) {
                                            $keyCandidate = trim((string) $sectionValue['key']);
                                        } elseif (isset($sectionValue['id'])) {
                                            $keyCandidate = trim((string) $sectionValue['id']);
                                        }
                                    }
                                    if ($keyCandidate === '') {
                                        continue;
                                    }
                                    $sectionsNormalized[$keyCandidate] = $sectionValue;
                                }

                                $defaultSectionOrder = ['general_tendencies', 'work_preferences', 'effectiveness_requirements', 'behavior_overview', 'companion_requirements'];
                                $sectionOrder = $defaultSectionOrder;
                                if (isset($props['sectionOrder'])) {
                                    $orderProp = $props['sectionOrder'];
                                    if (is_string($orderProp)) {
                                        $decodedOrder = json_decode($orderProp, true);
                                        if (is_array($decodedOrder)) {
                                            $orderProp = $decodedOrder;
                                        } else {
                                            $orderProp = array_map('trim', explode(',', $orderProp));
                                        }
                                    }
                                    if (is_array($orderProp)) {
                                        $cleanOrder = [];
                                        foreach ($orderProp as $item) {
                                            if (!is_string($item) && !is_numeric($item)) {
                                                continue;
                                            }
                                            $itemKey = trim((string) $item);
                                            if ($itemKey === '' || !in_array($itemKey, $defaultSectionOrder, true) || in_array($itemKey, $cleanOrder, true)) {
                                                continue;
                                            }
                                            $cleanOrder[] = $itemKey;
                                        }
                                        if (!empty($cleanOrder)) {
                                            $sectionOrder = array_merge($cleanOrder, array_diff($defaultSectionOrder, $cleanOrder));
                                        }
                                    }
                                }

                                $sectionLabels = [
                                    'general_tendencies' => 'گرایش‌های کلی',
                                    'work_preferences' => 'ترجیحات کاری',
                                    'effectiveness_requirements' => 'نیازهای موفقیت',
                                    'companion_requirements' => 'انتظارات از دیگران',
                                    'behavior_overview' => 'الگوی رفتاری',
                                ];

                                $sectionToggles = [
                                    'general_tendencies' => $isTruthy($props['showGeneralTendencies'] ?? 1),
                                    'work_preferences' => $isTruthy($props['showWorkPreferences'] ?? 1),
                                    'effectiveness_requirements' => $isTruthy($props['showEffectivenessRequirements'] ?? 1),
                                    'companion_requirements' => $isTruthy($props['showCompanionRequirements'] ?? 0),
                                    'behavior_overview' => $isTruthy($props['showBehaviorOverview'] ?? 1),
                                ];

                                $sectionsToRender = [];
                                foreach ($sectionOrder as $sectionKey) {
                                    if (empty($sectionToggles[$sectionKey])) {
                                        continue;
                                    }
                                    $entry = $sectionsNormalized[$sectionKey] ?? [];
                                    if (!is_array($entry)) {
                                        continue;
                                    }
                                    $title = isset($entry['title']) ? trim((string) $entry['title']) : '';
                                    if ($title === '' && isset($entry['label'])) {
                                        $title = trim((string) $entry['label']);
                                    }
                                    if ($title === '') {
                                        $title = $sectionLabels[$sectionKey] ?? '';
                                    }
                                    $text = '';
                                    foreach (['text', 'content', 'summary', 'description', 'value'] as $textField) {
                                        if (!isset($entry[$textField])) {
                                            continue;
                                        }
                                        $candidate = trim((string) $entry[$textField]);
                                        if ($candidate !== '') {
                                            $text = $candidate;
                                            break;
                                        }
                                    }
                                    $bullets = [];
                                    foreach (['bullets', 'items', 'list', 'highlights'] as $listField) {
                                        if (!isset($entry[$listField])) {
                                            continue;
                                        }
                                        $listItems = $normalizeList($entry[$listField]);
                                        if (!empty($listItems)) {
                                            $bullets = $listItems;
                                            break;
                                        }
                                    }
                                    if ($text === '' && empty($bullets)) {
                                        continue;
                                    }
                                    $sectionsToRender[] = [
                                        'title' => $title,
                                        'text' => $text,
                                        'bullets' => $bullets,
                                    ];
                                }

                                $strengthHighlights = $isTruthy($props['showStrengthHighlights'] ?? 1)
                                    ? $normalizeList($profileData['strengths'] ?? [])
                                    : [];
                                $collaborationTips = $isTruthy($props['showCollaborationTips'] ?? 1)
                                    ? $normalizeList($profileData['collaboration_tips'] ?? [])
                                    : [];
                                $developmentFocus = $isTruthy($props['showDevelopmentFocus'] ?? 1)
                                    ? $normalizeList($profileData['development_focus'] ?? [])
                                    : [];
                                $stressSignals = $isTruthy($props['showStressSignals'] ?? 0)
                                    ? $normalizeList($profileData['stress_signals'] ?? [])
                                    : [];

                                $hasSidebar = !empty($strengthHighlights) || !empty($collaborationTips) || !empty($developmentFocus) || !empty($stressSignals);
                                $effectiveLayout = ($layout === 'split' && !$hasSidebar) ? 'stacked' : $layout;

                                $widthClass = $elementWidthClass($props);
                                $outerClassAttr = implode(' ', array_filter(['certificate-element', $widthClass]));

                                $sourceLabel = isset($profileData['source_tool']) ? trim((string) $profileData['source_tool']) : '';
                                if ($sourceLabel === '' && isset($profileData['source'])) {
                                    $sourceLabel = trim((string) $profileData['source']);
                                }

                                $updatedAtLabel = isset($profileData['updated_at_formatted']) ? trim((string) $profileData['updated_at_formatted']) : '';
                                if ($updatedAtLabel === '' && isset($profileData['updated_at'])) {
                                    $timestampCandidate = strtotime((string) $profileData['updated_at']);
                                    if ($timestampCandidate !== false) {
                                        $updatedAtLabel = UtilityHelper::englishToPersian(date('Y/m/d', $timestampCandidate));
                                    }
                                }

                                $personaBadge = $personaName !== '' ? $personaName : $styleName;
                                $hasProfileContent = ($showSummary && $summaryText !== '') || !empty($sectionsToRender) || $hasSidebar;
                            ?>
                            <div class="<?= $escape($outerClassAttr); ?>"<?= $elementIdAttr; ?>>
                                <?php if (!$hasProfileContent): ?>
                                    <div class="certificate-public-profile-empty">
                                        <ion-icon name="information-circle-outline"></ion-icon>
                                        <span>داده‌ای برای پروفایل در دسترس نیست.</span>
                                    </div>
                                <?php else: ?>
                                    <div class="certificate-public-profile style-<?= $escape($style); ?> layout-<?= $escape($effectiveLayout); ?>" style="--profile-accent: <?= $escape($accentColor); ?>;">
                                        <?php if (($showHeadline && $headline !== '') || $personaBadge !== ''): ?>
                                            <div class="public-profile-headline">
                                                <?php if ($showHeadline && $headline !== ''): ?>
                                                    <span class="headline-text"><?= $escape($headline); ?></span>
                                                <?php endif; ?>
                                                <?php if ($personaBadge !== ''): ?>
                                                    <span class="headline-subtle"><?= $escape($personaBadge); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="public-profile-body">
                                            <div class="public-profile-main">
                                                <?php if ($showSummary && $summaryText !== ''): ?>
                                                    <p class="public-profile-summary"><?= nl2br($escape($summaryText)); ?></p>
                                                <?php endif; ?>
                                                <?php foreach ($sectionsToRender as $section): ?>
                                                    <div class="public-profile-section">
                                                        <?php if ($section['title'] !== ''): ?>
                                                            <div class="section-title"><?= $escape($section['title']); ?></div>
                                                        <?php endif; ?>
                                                        <?php if ($section['text'] !== ''): ?>
                                                            <p><?= nl2br($escape($section['text'])); ?></p>
                                                        <?php endif; ?>
                                                        <?php if (!empty($section['bullets'])): ?>
                                                            <ul>
                                                                <?php foreach ($section['bullets'] as $bullet): ?>
                                                                    <li><?= $escape($bullet); ?></li>
                                                                <?php endforeach; ?>
                                                            </ul>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <?php if ($hasSidebar): ?>
                                                <div class="public-profile-sidebar">
                                                    <?php if (!empty($strengthHighlights)): ?>
                                                        <div class="public-profile-card highlights">
                                                            <div class="card-title">نقاط قوت کلیدی</div>
                                                            <ul>
                                                                <?php foreach ($strengthHighlights as $item): ?>
                                                                    <li><?= $escape($item); ?></li>
                                                                <?php endforeach; ?>
                                                            </ul>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($collaborationTips)): ?>
                                                        <div class="public-profile-card collaboration">
                                                            <div class="card-title">توصیه‌های همکاری</div>
                                                            <ul>
                                                                <?php foreach ($collaborationTips as $item): ?>
                                                                    <li><?= $escape($item); ?></li>
                                                                <?php endforeach; ?>
                                                            </ul>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($developmentFocus)): ?>
                                                        <div class="public-profile-card development">
                                                            <div class="card-title">تمرکزهای توسعه</div>
                                                            <ul>
                                                                <?php foreach ($developmentFocus as $item): ?>
                                                                    <li><?= $escape($item); ?></li>
                                                                <?php endforeach; ?>
                                                            </ul>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($stressSignals)): ?>
                                                        <div class="public-profile-card stress">
                                                            <div class="card-title">علائم تنش</div>
                                                            <ul>
                                                                <?php foreach ($stressSignals as $item): ?>
                                                                    <li><?= $escape($item); ?></li>
                                                                <?php endforeach; ?>
                                                            </ul>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($sourceLabel !== '' || $updatedAtLabel !== ''): ?>
                                            <div class="public-profile-footnote">
                                                <?php if ($sourceLabel !== ''): ?>
                                                    <span><ion-icon name="flask-outline"></ion-icon><?= $escape($sourceLabel); ?></span>
                                                <?php endif; ?>
                                                <?php if ($updatedAtLabel !== ''): ?>
                                                    <span><ion-icon name="time-outline"></ion-icon><?= $escape($updatedAtLabel); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                        <?php elseif ($type === 'mbti_profile'): ?>
                            <?php
                                $sampleMbti = isset($sampleData['mbti_profile']) && is_array($sampleData['mbti_profile']) ? $sampleData['mbti_profile'] : [];
                                $datasetMbti = isset($runtimeDatasets['mbti_profile']) && is_array($runtimeDatasets['mbti_profile']) ? $runtimeDatasets['mbti_profile'] : [];
                                $headline = isset($props['headline']) ? trim((string) $props['headline']) : '';
                                $typeCode = isset($datasetMbti['type_code']) ? trim((string) $datasetMbti['type_code']) : '';
                                if ($typeCode === '' && isset($props['typeCode'])) {
                                    $typeCode = trim((string) $props['typeCode']);
                                }
                                if ($typeCode === '' && isset($sampleMbti['type_code'])) {
                                    $typeCode = (string) $sampleMbti['type_code'];
                                }
                                $personaName = isset($datasetMbti['persona_name']) ? trim((string) $datasetMbti['persona_name']) : '';
                                if ($personaName === '' && isset($props['personaName'])) {
                                    $personaName = trim((string) $props['personaName']);
                                }
                                if ($personaName === '' && isset($sampleMbti['persona_name'])) {
                                    $personaName = (string) $sampleMbti['persona_name'];
                                }
                                $typeTitle = isset($datasetMbti['type_title']) ? trim((string) $datasetMbti['type_title']) : '';
                                $typeCategory = isset($datasetMbti['type_category']) ? trim((string) $datasetMbti['type_category']) : '';
                                $typeSummary = isset($datasetMbti['type_summary']) ? trim((string) $datasetMbti['type_summary']) : '';
                                $typeShortDescription = isset($datasetMbti['type_short_description']) ? trim((string) $datasetMbti['type_short_description']) : '';
                                $typeDescription = isset($datasetMbti['type_description']) ? trim((string) $datasetMbti['type_description']) : '';
                                $cognitiveFunctions = isset($datasetMbti['cognitive_functions']) ? trim((string) $datasetMbti['cognitive_functions']) : '';
                                $summaryText = isset($datasetMbti['summary']) ? trim((string) $datasetMbti['summary']) : '';
                                if ($summaryText === '' && isset($props['summary'])) {
                                    $summaryText = trim((string) $props['summary']);
                                }
                                if ($summaryText === '' && isset($sampleMbti['summary'])) {
                                    $summaryText = (string) $sampleMbti['summary'];
                                }
                                $startOnNextPage = $isTruthy($props['startOnNextPage'] ?? 0);
                                $showTypeOverview = $isTruthy($props['showTypeOverview'] ?? 1);
                                $normalizeCategoryTitle = static function ($title) {
                                    $title = trim((string) $title);
                                    if ($title === '') {
                                        return '';
                                    }
                                    $normalized = preg_replace('/\s+/u', ' ', $title);
                                    return is_string($normalized) ? $normalized : $title;
                                };
                                $categoryLabelOverrides = [];
                                $applyLabelOverrides = static function ($source, array &$target) use ($normalizeCategoryTitle) {
                                    if ($source === null) {
                                        return;
                                    }
                                    if (is_string($source)) {
                                        $decoded = json_decode($source, true);
                                        if (is_array($decoded)) {
                                            $source = $decoded;
                                        }
                                    }
                                    if (!is_array($source)) {
                                        return;
                                    }
                                    foreach ($source as $rawKey => $rawValue) {
                                        $categoryCandidate = null;
                                        $labelCandidate = null;
                                        if (is_array($rawValue)) {
                                            if (isset($rawValue['category']) && isset($rawValue['label'])) {
                                                $categoryCandidate = $rawValue['category'];
                                                $labelCandidate = $rawValue['label'];
                                            } elseif (count($rawValue) >= 2) {
                                                $values = array_values($rawValue);
                                                $categoryCandidate = $values[0];
                                                $labelCandidate = $values[1];
                                            } else {
                                                continue;
                                            }
                                        } else {
                                            $categoryCandidate = $rawKey;
                                            $labelCandidate = $rawValue;
                                        }
                                        if (!is_string($categoryCandidate) && !is_numeric($categoryCandidate)) {
                                            continue;
                                        }
                                        if (!is_string($labelCandidate) && !is_numeric($labelCandidate)) {
                                            continue;
                                        }
                                        $categoryTrimmed = trim((string) $categoryCandidate);
                                        if ($categoryTrimmed === '') {
                                            continue;
                                        }
                                        $labelTrimmed = trim((string) $labelCandidate);
                                        if ($labelTrimmed === '') {
                                            continue;
                                        }
                                        if (function_exists('mb_substr')) {
                                            $categoryTrimmed = mb_substr($categoryTrimmed, 0, 160, 'UTF-8');
                                            $labelTrimmed = mb_substr($labelTrimmed, 0, 160, 'UTF-8');
                                        } else {
                                            $categoryTrimmed = substr($categoryTrimmed, 0, 160);
                                            $labelTrimmed = substr($labelTrimmed, 0, 160);
                                        }
                                        $normalizedKey = $normalizeCategoryTitle($categoryTrimmed);
                                        if ($normalizedKey === '') {
                                            continue;
                                        }
                                        $target[$normalizedKey] = $labelTrimmed;
                                    }
                                };
                                $applyLabelOverrides($sampleMbti['feature_category_labels'] ?? null, $categoryLabelOverrides);
                                $applyLabelOverrides($datasetMbti['feature_category_labels'] ?? null, $categoryLabelOverrides);
                                $applyLabelOverrides($props['featureCategoryLabels'] ?? null, $categoryLabelOverrides);
                                $resolveGroupTitle = static function ($rawTitle) use ($normalizeCategoryTitle, $categoryLabelOverrides) {
                                    $cleanTitle = trim((string) $rawTitle);
                                    if ($cleanTitle === '') {
                                        return [
                                            'title' => '',
                                            'displayTitle' => '',
                                            'normalizedTitle' => '',
                                        ];
                                    }
                                    $normalizedTitle = $normalizeCategoryTitle($cleanTitle);
                                    $displayTitle = $cleanTitle;
                                    if ($normalizedTitle !== '' && isset($categoryLabelOverrides[$normalizedTitle])) {
                                        $candidate = trim((string) $categoryLabelOverrides[$normalizedTitle]);
                                        if ($candidate !== '') {
                                            $displayTitle = $candidate;
                                        }
                                    }
                                    return [
                                        'title' => $cleanTitle,
                                        'displayTitle' => $displayTitle,
                                        'normalizedTitle' => $normalizedTitle,
                                    ];
                                };
                                $selectedCategorySet = null;
                                if (array_key_exists('selectedFeatureCategories', $props)) {
                                    $selectionRaw = $props['selectedFeatureCategories'];
                                    if ($selectionRaw === null) {
                                        $selectedCategorySet = null;
                                    } else {
                                        $selectedCategorySet = [];
                                        $selectionValues = [];
                                        if (is_array($selectionRaw)) {
                                            $selectionValues = $selectionRaw;
                                        } elseif (is_string($selectionRaw)) {
                                            $trimmedSelection = trim($selectionRaw);
                                            if ($trimmedSelection !== '') {
                                                $decodedSelection = json_decode($trimmedSelection, true);
                                                if (is_array($decodedSelection)) {
                                                    $selectionValues = $decodedSelection;
                                                } else {
                                                    $selectionValues = explode(',', $trimmedSelection);
                                                }
                                            }
                                        }
                                        foreach ($selectionValues as $selectionValue) {
                                            if (!is_string($selectionValue) && !is_numeric($selectionValue)) {
                                                continue;
                                            }
                                            $selectionTitle = $normalizeCategoryTitle($selectionValue);
                                            if ($selectionTitle === '') {
                                                continue;
                                            }
                                            $selectedCategorySet[$selectionTitle] = true;
                                        }
                                        if (empty($selectedCategorySet)) {
                                            $selectedCategorySet = [];
                                        }
                                    }
                                }
                                $featureGroups = [];
                                if (isset($datasetMbti['feature_groups']) && is_array($datasetMbti['feature_groups'])) {
                                    foreach ($datasetMbti['feature_groups'] as $group) {
                                        if (!is_array($group)) {
                                            continue;
                                        }
                                        $groupTitle = isset($group['category']) ? trim((string) $group['category']) : '';
                                        $groupItems = isset($group['items']) && is_array($group['items']) ? $normalizeList($group['items']) : [];
                                        if ($groupTitle === '' && !empty($groupItems)) {
                                            $groupTitle = 'ویژگی‌ها';
                                        }
                                        if (empty($groupItems)) {
                                            continue;
                                        }
                                        $titleMeta = $resolveGroupTitle($groupTitle);
                                        if ($titleMeta['title'] === '') {
                                            continue;
                                        }
                                        $featureGroup = [
                                            'title' => $titleMeta['title'],
                                            'displayTitle' => $titleMeta['displayTitle'],
                                            'normalizedTitle' => $titleMeta['normalizedTitle'],
                                            'items' => $groupItems,
                                        ];
                                        if (isset($group['icon'])) {
                                            $iconValue = trim((string) $group['icon']);
                                            if ($iconValue !== '') {
                                                $featureGroup['icon'] = $iconValue;
                                            }
                                        }
                                        $featureGroups[] = $featureGroup;
                                    }
                                }
                                if (empty($featureGroups) && isset($datasetMbti['features']) && is_array($datasetMbti['features'])) {
                                    foreach ($datasetMbti['features'] as $category => $items) {
                                        $groupItems = $normalizeList($items);
                                        if (empty($groupItems)) {
                                            continue;
                                        }
                                        $rawTitle = is_string($category) && trim($category) !== '' ? trim($category) : 'ویژگی‌ها';
                                        $titleMeta = $resolveGroupTitle($rawTitle);
                                        if ($titleMeta['title'] === '') {
                                            continue;
                                        }
                                        $featureGroups[] = [
                                            'title' => $titleMeta['title'],
                                            'displayTitle' => $titleMeta['displayTitle'],
                                            'normalizedTitle' => $titleMeta['normalizedTitle'],
                                            'items' => $groupItems,
                                        ];
                                    }
                                }
                                if (empty($featureGroups) && isset($sampleMbti['feature_groups']) && is_array($sampleMbti['feature_groups'])) {
                                    foreach ($sampleMbti['feature_groups'] as $group) {
                                        if (!is_array($group)) {
                                            continue;
                                        }
                                        $groupTitle = isset($group['category']) ? trim((string) $group['category']) : '';
                                        $groupItems = isset($group['items']) && is_array($group['items']) ? $normalizeList($group['items']) : [];
                                        if ($groupTitle === '' && !empty($groupItems)) {
                                            $groupTitle = 'ویژگی‌ها';
                                        }
                                        if (empty($groupItems)) {
                                            continue;
                                        }
                                        $titleMeta = $resolveGroupTitle($groupTitle);
                                        if ($titleMeta['title'] === '') {
                                            continue;
                                        }
                                        $featureGroup = [
                                            'title' => $titleMeta['title'],
                                            'displayTitle' => $titleMeta['displayTitle'],
                                            'normalizedTitle' => $titleMeta['normalizedTitle'],
                                            'items' => $groupItems,
                                        ];
                                        if (isset($group['icon'])) {
                                            $iconValue = trim((string) $group['icon']);
                                            if ($iconValue !== '') {
                                                $featureGroup['icon'] = $iconValue;
                                            }
                                        }
                                        $featureGroups[] = $featureGroup;
                                    }
                                }
                                if ($selectedCategorySet !== null && is_array($selectedCategorySet)) {
                                    $featureGroups = array_values(array_filter($featureGroups, static function ($group) use ($selectedCategorySet, $normalizeCategoryTitle) {
                                        if (!is_array($group)) {
                                            return false;
                                        }
                                        $normalizedTitle = '';
                                        if (isset($group['normalizedTitle']) && is_string($group['normalizedTitle'])) {
                                            $normalizedTitle = trim($group['normalizedTitle']);
                                        }
                                        if ($normalizedTitle === '' && isset($group['title'])) {
                                            $normalizedTitle = $normalizeCategoryTitle($group['title']);
                                        }
                                        if ($normalizedTitle === '') {
                                            return false;
                                        }
                                        return isset($selectedCategorySet[$normalizedTitle]);
                                    }));
                                }
                                $accentColor = $sanitizeColor($props['accentColor'] ?? '#4f46e5', '#4f46e5');
                                if (isset($datasetMbti['accentColor'])) {
                                    $accentColor = $sanitizeColor($datasetMbti['accentColor'], $accentColor);
                                }
                                $showSummary = $isTruthy($props['showSummary'] ?? 1) && $summaryText !== '';
                                $strengthSource = [];
                                if (!empty($datasetMbti['strengths'])) {
                                    $strengthSource = $datasetMbti['strengths'];
                                } elseif (isset($props['strengths'])) {
                                    $strengthSource = $props['strengths'];
                                } elseif (isset($sampleMbti['strengths'])) {
                                    $strengthSource = $sampleMbti['strengths'];
                                }
                                $allowStrengths = $isTruthy($props['showStrengths'] ?? 1);
                                $strengths = $normalizeList($strengthSource);
                                $showStrengths = $allowStrengths && !empty($strengths);
                                $growthSource = [];
                                if (!empty($datasetMbti['growth_areas'])) {
                                    $growthSource = $datasetMbti['growth_areas'];
                                } elseif (isset($props['growthAreas'])) {
                                    $growthSource = $props['growthAreas'];
                                } elseif (isset($sampleMbti['growth_areas'])) {
                                    $growthSource = $sampleMbti['growth_areas'];
                                }
                                $growthAreas = $normalizeList($growthSource);
                                $allowGrowthAreas = $isTruthy($props['showGrowthAreas'] ?? 1);
                                $showGrowthAreas = $allowGrowthAreas && !empty($growthAreas);
                                $showCollaborationStyle = $isTruthy($props['showCollaborationStyle'] ?? 1);
                                $showPreferences = $isTruthy($props['showPreferenceBars'] ?? 1);
                                $preferenceRows = [];
                                if ($showPreferences) {
                                    $preferenceRows = [
                                        ['key' => 'preferenceEI', 'left' => 'E', 'right' => 'I', 'label' => 'انرژی (E / I)'],
                                        ['key' => 'preferenceSN', 'left' => 'N', 'right' => 'S', 'label' => 'سبک دریافت اطلاعات (N / S)'],
                                        ['key' => 'preferenceTF', 'left' => 'F', 'right' => 'T', 'label' => 'تصمیم‌گیری (F / T)'],
                                        ['key' => 'preferenceJP', 'left' => 'P', 'right' => 'J', 'label' => 'سبک زندگی (P / J)'],
                                    ];
                                    foreach ($preferenceRows as &$preferenceRow) {
                                            $value = $getMbtiPreference($props, $sampleMbti, $datasetMbti, $preferenceRow['key'], 50);
                                        $preferenceRow['value'] = $value;
                                        $preferenceRow['complement'] = 100 - $value;
                                        $preferenceRow['labelPercent'] = $formatPercentLabel($value);
                                        $preferenceRow['labelComplement'] = $formatPercentLabel(100 - $value);
                                    }
                                    unset($preferenceRow);
                                }
                                $widthClass = $elementWidthClass($props);
                                $outerClassParts = ['certificate-element', $widthClass];
                                if ($startOnNextPage) {
                                    $outerClassParts[] = 'certificate-element-break-before';
                                }
                                $outerClassAttr = implode(' ', array_filter($outerClassParts));
                                $startOnNextPageAttr = $startOnNextPage ? ' data-start-on-next-page="1"' : '';
                            ?>
                            <div class="<?= $escape($outerClassAttr); ?>"<?= $elementIdAttr; ?><?= $startOnNextPageAttr; ?>>
                                <div class="certificate-mbti-profile" style="--mbti-accent: <?= $escape($accentColor); ?>;">
                                    <?php if ($headline !== ''): ?>
                                        <div class="certificate-mbti-headline"><?= $escape($headline); ?></div>
                                    <?php endif; ?>
                                    <?php if ($showTypeOverview): ?>
                                        <div class="certificate-mbti-header">
                                            <div class="certificate-mbti-header-main">
                                                <?php if ($typeCode !== ''): ?>
                                                    <div class="certificate-mbti-type"><?= $escape($typeCode); ?></div>
                                                <?php endif; ?>
                                                <?php if ($personaName !== ''): ?>
                                                    <div class="certificate-mbti-persona"><?= $escape($personaName); ?></div>
                                                <?php endif; ?>
                                                <?php if ($typeCategory !== ''): ?>
                                                    <span class="certificate-mbti-tag"><?= $escape($typeCategory); ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ($typeSummary !== '' && $typeSummary !== $summaryText): ?>
                                                <div class="certificate-mbti-inline-summary"><?= $escape($typeSummary); ?></div>
                                            <?php elseif ($typeShortDescription !== '' && $typeShortDescription !== $summaryText): ?>
                                                <div class="certificate-mbti-inline-summary"><?= $escape($typeShortDescription); ?></div>
                                            <?php endif; ?>
                                            <?php if ($typeDescription !== '' && $typeDescription !== $summaryText): ?>
                                                <div class="certificate-mbti-description"><?= $escape($typeDescription); ?></div>
                                            <?php endif; ?>
                                            <?php if ($cognitiveFunctions !== ''): ?>
                                                <div class="certificate-mbti-cognitive">
                                                    <ion-icon name="git-compare-outline"></ion-icon>
                                                    <span><?= $escape($cognitiveFunctions); ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($showSummary): ?>
                                        <div class="certificate-mbti-summary"><?= $escape($summaryText); ?></div>
                                    <?php endif; ?>
                                    <?php if ($showPreferences && !empty($preferenceRows)): ?>
                                        <div class="certificate-mbti-preferences">
                                            <?php foreach ($preferenceRows as $row): ?>
                                                <div class="mbti-preference-row">
                                                    <div class="mbti-preference-label"><?= $escape($row['label']); ?></div>
                                                    <div class="mbti-preference-scale">
                                                        <div class="mbti-preference-bar">
                                                            <span class="pref-left"><?= $escape($row['left']); ?></span>
                                                            <span class="pref-right"><?= $escape($row['right']); ?></span>
                                                            <div class="mbti-preference-fill" style="--pref-value: <?= $escape(number_format($row['value'], 2, '.', '')); ?>%;"></div>
                                                        </div>
                                                        <div class="mbti-preference-values">
                                                            <span><?= $escape($row['labelPercent']); ?></span>
                                                            <span><?= $escape($row['labelComplement']); ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php
                                        $listGroups = [];
                                        if (!empty($featureGroups)) {
                                            $listGroups = [];
                                            $categoryVisibility = [
                                                'نقاط قوت اصلی' => $allowStrengths,
                                                'فرصت‌های رشد' => $allowGrowthAreas,
                                                'سبک همکاری ترجیحی' => $showCollaborationStyle,
                                            ];
                                            foreach ($featureGroups as $group) {
                                                if (!is_array($group)) {
                                                    $listGroups[] = $group;
                                                    continue;
                                                }
                                                $title = isset($group['title']) ? trim((string) $group['title']) : '';
                                                $displayTitle = isset($group['displayTitle']) ? trim((string) $group['displayTitle']) : $title;
                                                if ($title === '' && $displayTitle === '') {
                                                    continue;
                                                }
                                                $normalizedTitle = '';
                                                if (isset($group['normalizedTitle']) && is_string($group['normalizedTitle'])) {
                                                    $normalizedTitle = trim($group['normalizedTitle']);
                                                }
                                                if ($normalizedTitle === '' && $title !== '') {
                                                    $normalizedTitle = $normalizeCategoryTitle($title);
                                                }
                                                $compareCandidates = [];
                                                if ($title !== '') {
                                                    $compareCandidates[] = $title;
                                                }
                                                if ($displayTitle !== '' && $displayTitle !== $title) {
                                                    $compareCandidates[] = $displayTitle;
                                                }
                                                if (empty($compareCandidates) && $displayTitle !== '') {
                                                    $compareCandidates[] = $displayTitle;
                                                }
                                                $shouldInclude = true;
                                                foreach ($categoryVisibility as $label => $isVisible) {
                                                    if ($label === '') {
                                                        continue;
                                                    }
                                                    foreach ($compareCandidates as $candidate) {
                                                        $normalizedCandidate = preg_replace('/\s+/', ' ', $candidate);
                                                        $normalizedCandidate = is_string($normalizedCandidate) ? $normalizedCandidate : $candidate;
                                                        $position = null;
                                                        if (function_exists('mb_stripos')) {
                                                            $position = mb_stripos($normalizedCandidate, $label);
                                                        } else {
                                                            $position = stripos($normalizedCandidate, $label);
                                                        }
                                                        if ($position !== false) {
                                                            $shouldInclude = $isVisible;
                                                            break 2;
                                                        }
                                                    }
                                                }
                                                if ($shouldInclude) {
                                                    $normalizedValue = $normalizedTitle;
                                                    if ($normalizedValue === '' && $displayTitle !== '') {
                                                        $normalizedValue = $normalizeCategoryTitle($displayTitle);
                                                    }
                                                    $group['title'] = $title;
                                                    if ($displayTitle !== '') {
                                                        $group['displayTitle'] = $displayTitle;
                                                    }
                                                    if ($normalizedValue !== '') {
                                                        $group['normalizedTitle'] = $normalizedValue;
                                                    }
                                                    $listGroups[] = $group;
                                                }
                                            }
                                        } else {
                                            if ($showStrengths) {
                                                $titleMeta = $resolveGroupTitle('نقاط قوت اصلی');
                                                $listGroups[] = [
                                                    'title' => $titleMeta['title'],
                                                    'displayTitle' => $titleMeta['displayTitle'],
                                                    'normalizedTitle' => $titleMeta['normalizedTitle'],
                                                    'icon' => 'sparkles-outline',
                                                    'items' => $strengths,
                                                ];
                                            }
                                            if ($showGrowthAreas) {
                                                $titleMeta = $resolveGroupTitle('فرصت‌های رشد');
                                                $listGroups[] = [
                                                    'title' => $titleMeta['title'],
                                                    'displayTitle' => $titleMeta['displayTitle'],
                                                    'normalizedTitle' => $titleMeta['normalizedTitle'],
                                                    'icon' => 'trending-up-outline',
                                                    'items' => $growthAreas,
                                                ];
                                            }
                                            if ($showCollaborationStyle && isset($datasetMbti['preferred_collaboration']) && is_array($datasetMbti['preferred_collaboration'])) {
                                                $collaborationItems = $normalizeList($datasetMbti['preferred_collaboration']);
                                                if (!empty($collaborationItems)) {
                                                    $titleMeta = $resolveGroupTitle('سبک همکاری ترجیحی');
                                                    $listGroups[] = [
                                                        'title' => $titleMeta['title'],
                                                        'displayTitle' => $titleMeta['displayTitle'],
                                                        'normalizedTitle' => $titleMeta['normalizedTitle'],
                                                        'icon' => 'people-outline',
                                                        'items' => $collaborationItems,
                                                    ];
                                                }
                                            }
                                        }
                                        if ($selectedCategorySet !== null && is_array($selectedCategorySet)) {
                                            $listGroups = array_values(array_filter($listGroups, static function ($group) use ($selectedCategorySet, $normalizeCategoryTitle) {
                                                if (!is_array($group)) {
                                                    return false;
                                                }
                                                $normalizedTitle = '';
                                                if (isset($group['normalizedTitle']) && is_string($group['normalizedTitle'])) {
                                                    $normalizedTitle = trim($group['normalizedTitle']);
                                                }
                                                if ($normalizedTitle === '' && isset($group['title'])) {
                                                    $normalizedTitle = $normalizeCategoryTitle($group['title']);
                                                }
                                                if ($normalizedTitle === '' && isset($group['displayTitle'])) {
                                                    $normalizedTitle = $normalizeCategoryTitle($group['displayTitle']);
                                                }
                                                if ($normalizedTitle === '') {
                                                    return false;
                                                }
                                                return isset($selectedCategorySet[$normalizedTitle]);
                                            }));
                                        }
                                    ?>
                                    <?php if (!empty($listGroups)): ?>
                                        <div class="certificate-mbti-lists">
                                            <?php foreach ($listGroups as $group): ?>
                                                <?php
                                                    $groupTitleRaw = isset($group['displayTitle']) ? (string) $group['displayTitle'] : (isset($group['title']) ? (string) $group['title'] : '');
                                                    $groupTitle = trim($groupTitleRaw);
                                                    $groupItems = isset($group['items']) && is_array($group['items']) ? $normalizeList($group['items']) : [];
                                                    if ($groupTitle === '' || empty($groupItems)) {
                                                        continue;
                                                    }
                                                    $groupIcon = isset($group['icon']) ? trim((string) $group['icon']) : '';
                                                    if ($groupIcon === '') {
                                                        $groupIcon = 'ellipse-outline';
                                                    }
                                                ?>
                                                <div class="mbti-list">
                                                    <div class="mbti-list-title">
                                                        <ion-icon name="<?= $escape($groupIcon); ?>"></ion-icon>
                                                        <?= $escape($groupTitle); ?>
                                                    </div>
                                                    <ul>
                                                        <?php foreach ($groupItems as $item): ?>
                                                            <li><?= $escape($item); ?></li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                        <?php elseif ($type === 'mbti_type_matrix'): ?>
                            <?php
                                $sampleMbti = isset($sampleData['mbti_profile']) && is_array($sampleData['mbti_profile']) ? $sampleData['mbti_profile'] : [];
                                $datasetMbti = isset($runtimeDatasets['mbti_profile']) && is_array($runtimeDatasets['mbti_profile']) ? $runtimeDatasets['mbti_profile'] : [];
                                $headline = isset($props['headline']) ? trim((string) $props['headline']) : '';
                                $typeCode = isset($datasetMbti['type_code']) ? trim((string) $datasetMbti['type_code']) : '';
                                if ($typeCode === '' && isset($props['typeCode'])) {
                                    $typeCode = trim((string) $props['typeCode']);
                                }
                                if ($typeCode === '' && isset($sampleMbti['type_code'])) {
                                    $typeCode = (string) $sampleMbti['type_code'];
                                }
                                $typeTitle = isset($datasetMbti['type_title']) ? trim((string) $datasetMbti['type_title']) : '';
                                if ($typeTitle === '' && isset($props['typeTitle'])) {
                                    $typeTitle = trim((string) $props['typeTitle']);
                                }
                                if ($typeTitle === '' && isset($sampleMbti['type_title'])) {
                                    $typeTitle = (string) $sampleMbti['type_title'];
                                }
                                $highlightCode = '';
                                if ($typeCode !== '') {
                                    $highlightCode = strtoupper(preg_replace('/[^A-Z]/', '', $typeCode));
                                    if (!is_string($highlightCode)) {
                                        $highlightCode = '';
                                    }
                                }
                                if ($highlightCode !== '') {
                                    $highlightCode = substr($highlightCode, 0, 4);
                                }
                                if ($highlightCode === '' && isset($sampleMbti['type_code'])) {
                                    $sampleType = strtoupper(preg_replace('/[^A-Z]/', '', (string) $sampleMbti['type_code']));
                                    if (is_string($sampleType)) {
                                        $highlightCode = substr($sampleType, 0, 4);
                                    }
                                }
                                $showLegend = $isTruthy($props['showLegend'] ?? 1);
                                $legendTypeCode = '';
                                $legendTypeTitle = '';
                                if ($showLegend && $highlightCode !== '') {
                                    $legendTypeCode = $highlightCode;
                                    if ($typeTitle !== '') {
                                        $legendTypeTitle = $typeTitle;
                                    }
                                }
                                $accentColor = $sanitizeColor($props['accentColor'] ?? '#2563eb', '#2563eb');
                                $inactiveColor = $sanitizeColor($props['inactiveColor'] ?? 'rgba(15, 23, 42, 0.16)', 'rgba(15, 23, 42, 0.16)');
                                $matrixRows = [
                                    ['ISTJ', 'ISFJ', 'INFJ', 'INTJ'],
                                    ['ISTP', 'ISFP', 'INFP', 'INTP'],
                                    ['ESTP', 'ESFP', 'ENFP', 'ENTP'],
                                    ['ESTJ', 'ESFJ', 'ENFJ', 'ENTJ'],
                                ];
                                $startOnNextPage = $isTruthy($props['startOnNextPage'] ?? 0);
                                $widthClass = $elementWidthClass($props);
                                $outerClassParts = ['certificate-element', $widthClass];
                                if ($startOnNextPage) {
                                    $outerClassParts[] = 'certificate-element-break-before';
                                }
                                $outerClassAttr = implode(' ', array_filter($outerClassParts));
                                $startOnNextPageAttr = $startOnNextPage ? ' data-start-on-next-page="1"' : '';
                                $styleAttr = '--matrix-accent: ' . $accentColor . '; --matrix-muted: ' . $inactiveColor . ';';
                            ?>
                            <div class="<?= $escape($outerClassAttr); ?>"<?= $elementIdAttr; ?><?= $startOnNextPageAttr; ?>>
                                <div class="certificate-mbti-type-matrix" style="<?= $escape($styleAttr); ?>">
                                    <?php if ($headline !== ''): ?>
                                        <div class="certificate-mbti-matrix-headline"><?= $escape($headline); ?></div>
                                    <?php endif; ?>
                                    <div class="certificate-mbti-matrix-grid">
                                        <?php foreach ($matrixRows as $row): ?>
                                            <div class="certificate-mbti-matrix-row">
                                                <?php foreach ($row as $code): ?>
                                                    <?php
                                                        $cellCode = strtoupper((string) $code);
                                                        $isActive = $highlightCode !== '' && $cellCode === $highlightCode;
                                                        $cellClasses = ['mbti-matrix-cell'];
                                                        if ($isActive) {
                                                            $cellClasses[] = 'is-active';
                                                        }
                                                        $cellAttr = implode(' ', $cellClasses);
                                                    ?>
                                                    <div class="<?= $escape($cellAttr); ?>">
                                                        <span><?= $escape($cellCode); ?></span>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php if ($legendTypeCode !== ''): ?>
                                        <div class="certificate-mbti-matrix-legend">
                                            <span class="matrix-legend-label">تیپ شخصیتی:</span>
                                            <span class="matrix-legend-type"><?= $escape($legendTypeCode); ?></span>
                                            <?php if ($legendTypeTitle !== ''): ?>
                                                <span class="matrix-legend-sep">–</span>
                                                <span class="matrix-legend-title"><?= $escape($legendTypeTitle); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                        <?php elseif ($type === 'disc_profile_chart'): ?>
                            <?php
                                $sampleDisc = isset($sampleData['disc_profile']) && is_array($sampleData['disc_profile']) ? $sampleData['disc_profile'] : [];
                                $runtimeDisc = isset($runtimeDatasets['disc_profile']) && is_array($runtimeDatasets['disc_profile']) ? $runtimeDatasets['disc_profile'] : [];
                                $useRuntimeDisc = !empty($runtimeDisc);

                                $accentColor = $sanitizeColor($props['accentColor'] ?? '#f97316', '#f97316');

                                if ($useRuntimeDisc) {
                                    $runtimeIntensity = isset($runtimeDisc['intensity_scores']) && is_array($runtimeDisc['intensity_scores'])
                                        ? $runtimeDisc['intensity_scores']
                                        : [];
                                    $scoreD = (float) ($runtimeIntensity['D'] ?? ($runtimeDisc['D'] ?? 0));
                                    $scoreI = (float) ($runtimeIntensity['I'] ?? ($runtimeDisc['I'] ?? 0));
                                    $scoreS = (float) ($runtimeIntensity['S'] ?? ($runtimeDisc['S'] ?? 0));
                                    $scoreC = (float) ($runtimeIntensity['C'] ?? ($runtimeDisc['C'] ?? 0));
                                } else {
                                    $scoreD = isset($props['scoreD']) && is_numeric($props['scoreD']) ? (float) $props['scoreD'] : (float) ($sampleDisc['D'] ?? 0);
                                    $scoreI = isset($props['scoreI']) && is_numeric($props['scoreI']) ? (float) $props['scoreI'] : (float) ($sampleDisc['I'] ?? 0);
                                    $scoreS = isset($props['scoreS']) && is_numeric($props['scoreS']) ? (float) $props['scoreS'] : (float) ($sampleDisc['S'] ?? 0);
                                    $scoreC = isset($props['scoreC']) && is_numeric($props['scoreC']) ? (float) $props['scoreC'] : (float) ($sampleDisc['C'] ?? 0);
                                }

                                $summaryText = '';
                                if ($useRuntimeDisc) {
                                    $summaryText = isset($runtimeDisc['summary']) ? trim((string) $runtimeDisc['summary']) : '';
                                }
                                if ($summaryText === '' && isset($props['summaryText'])) {
                                    $summaryText = trim((string) $props['summaryText']);
                                }
                                if ($summaryText === '' && isset($sampleDisc['summary'])) {
                                    $summaryText = (string) $sampleDisc['summary'];
                                }
                                $showSummary = $isTruthy($props['showSummary'] ?? 1) && $summaryText !== '';

                                $highlights = [];
                                if ($useRuntimeDisc) {
                                    $highlights = $normalizeList($runtimeDisc['highlights'] ?? []);
                                }
                                if (empty($highlights)) {
                                    $highlights = $normalizeList($props['highlights'] ?? ($sampleDisc['highlights'] ?? []));
                                }
                                $showHighlights = $isTruthy($props['showHighlights'] ?? 1) && !empty($highlights);

                                $scoreMap = [
                                    ['key' => 'D', 'label' => 'Dominance', 'value' => $scoreD],
                                    ['key' => 'I', 'label' => 'Influence', 'value' => $scoreI],
                                    ['key' => 'S', 'label' => 'Steadiness', 'value' => $scoreS],
                                    ['key' => 'C', 'label' => 'Conscientiousness', 'value' => $scoreC],
                                ];
                                foreach ($scoreMap as &$scoreRow) {
                                    $scoreRow['percent'] = $clampPercent($scoreRow['value']);
                                    $scoreRow['labelPercent'] = $formatPercentLabel($scoreRow['percent']);
                                }
                                unset($scoreRow);

                                $dominantCode = $useRuntimeDisc ? trim((string) ($runtimeDisc['primary_code'] ?? '')) : '';
                                $dominantLabel = $useRuntimeDisc ? trim((string) ($runtimeDisc['primary_label'] ?? '')) : '';
                                $dominantDisplay = $dominantLabel !== '' ? $dominantLabel : $dominantCode;
                                if ($dominantDisplay !== '' && $dominantCode !== '' && stripos($dominantDisplay, $dominantCode) === false) {
                                    $dominantDisplay .= ' (' . $dominantCode . ')';
                                }
                                $showDominant = $dominantDisplay !== '';

                                $widthClass = $elementWidthClass($props);
                                $outerClassAttr = implode(' ', array_filter(['certificate-element', $widthClass]));
                            ?>
                            <div class="<?= $escape($outerClassAttr); ?>"<?= $elementIdAttr; ?>>
                                <div class="certificate-disc-profile" style="--disc-accent: <?= $escape($accentColor); ?>;">
                                    <?php if ($showDominant): ?>
                                        <div class="certificate-disc-dominant">
                                            <span class="disc-dominant-label">سبک غالب:</span>
                                            <span class="disc-dominant-value"><?= $escape($dominantDisplay); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <div class="certificate-disc-chart">
                                        <?php foreach ($scoreMap as $scoreRow): ?>
                                            <div class="disc-bar-row">
                                                <div class="disc-bar-label">
                                                    <span class="disc-letter"><?= $escape($scoreRow['key']); ?></span>
                                                    <span class="disc-label-text"><?= $escape($scoreRow['label']); ?></span>
                                                </div>
                                                <div class="disc-bar-track">
                                                    <div class="disc-bar-fill" style="width: <?= $escape(number_format($scoreRow['percent'], 2, '.', '')); ?>%;"></div>
                                                </div>
                                                <div class="disc-bar-value"><?= $escape($scoreRow['labelPercent']); ?></div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php if ($showSummary && $summaryText !== ''): ?>
                                        <div class="certificate-disc-summary"><?= $escape($summaryText); ?></div>
                                    <?php endif; ?>
                                    <?php if ($showHighlights): ?>
                                        <ul class="certificate-disc-highlights">
                                            <?php foreach ($highlights as $item): ?>
                                                <li>
                                                    <ion-icon name="checkmark-circle-outline"></ion-icon>
                                                    <span><?= $escape($item); ?></span>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </div>
                            </div>

                        <?php elseif ($type === 'disc_profile_overview'): ?>
                            <?php
                                $sampleDisc = isset($sampleData['disc_profile']) && is_array($sampleData['disc_profile']) ? $sampleData['disc_profile'] : [];
                                $runtimeDisc = isset($runtimeDatasets['disc_profile']) && is_array($runtimeDatasets['disc_profile']) ? $runtimeDatasets['disc_profile'] : [];
                                $useRuntimeDisc = !empty($runtimeDisc);
                                $discData = $useRuntimeDisc ? $runtimeDisc : $sampleDisc;

                                static $discOverviewInlineStylesPrinted = false;
                                if (!$discOverviewInlineStylesPrinted):
                                    $discOverviewInlineStylesPrinted = true;
                            ?>
                                    <style>
                                        .certificate-disc-overview {
                                            border-radius: 22px;
                                            padding: 26px;
                                            display: flex;
                                            flex-direction: column;
                                            gap: 22px;
                                            background: linear-gradient(140deg, rgba(249, 115, 22, 0.12), rgba(15, 23, 42, 0.04));
                                            border: 1px solid rgba(15, 23, 42, 0.08);
                                            box-shadow: 0 18px 44px -28px rgba(15, 23, 42, 0.46);
                                        }
                                        .disc-overview-headline {
                                            font-size: 1.05rem;
                                            font-weight: 600;
                                            color: #0f172a;
                                            display: inline-flex;
                                            align-items: center;
                                            gap: 10px;
                                        }
                                        .disc-overview-headline::before {
                                            content: '';
                                            width: 12px;
                                            height: 12px;
                                            border-radius: 999px;
                                            background: var(--disc-accent, #f97316);
                                            box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.18);
                                        }
                                        .disc-overview-stats {
                                            display: flex;
                                            flex-wrap: wrap;
                                            gap: 16px;
                                        }
                                        .disc-overview-card {
                                            flex: 1;
                                            min-width: 220px;
                                            border: 1px solid rgba(15, 23, 42, 0.08);
                                            border-radius: 16px;
                                            padding: 18px 20px;
                                            background-color: rgba(255, 255, 255, 0.82);
                                            display: flex;
                                            flex-direction: column;
                                            gap: 8px;
                                        }
                                        .disc-overview-card--dominant {
                                            border-top: 3px solid var(--disc-accent, #f97316);
                                        }
                                        .disc-overview-card--secondary {
                                            border-top: 3px solid var(--disc-support, #0f172a);
                                        }
                                        .disc-overview-card-label {
                                            font-size: 0.85rem;
                                            font-weight: 600;
                                            color: rgba(15, 23, 42, 0.65);
                                            text-transform: uppercase;
                                            letter-spacing: 0.04em;
                                        }
                                        .disc-overview-card-value {
                                            font-size: 1.25rem;
                                            font-weight: 600;
                                            color: #0f172a;
                                        }
                                        .disc-overview-card-meta {
                                            font-size: 0.85rem;
                                            color: rgba(15, 23, 42, 0.6);
                                        }
                                        .disc-overview-card-desc {
                                            font-size: 0.9rem;
                                            line-height: 1.6;
                                            color: rgba(15, 23, 42, 0.8);
                                            margin: 0;
                                        }
                                        .disc-overview-grid {
                                            display: grid;
                                            grid-template-columns: repeat(2, minmax(0, 1fr));
                                            gap: 22px;
                                        }
                                        .disc-overview-grid--single {
                                            grid-template-columns: 1fr;
                                        }
                                        .disc-overview-column {
                                            display: flex;
                                            flex-direction: column;
                                            gap: 18px;
                                        }
                                        .disc-overview-block {
                                            background-color: rgba(255, 255, 255, 0.86);
                                            border: 1px solid rgba(15, 23, 42, 0.1);
                                            border-radius: 16px;
                                            padding: 18px 20px;
                                            box-shadow: 0 16px 36px -36px rgba(15, 23, 42, 0.48);
                                            display: flex;
                                            flex-direction: column;
                                            gap: 14px;
                                        }
                                        .disc-overview-block-title {
                                            font-size: 0.95rem;
                                            font-weight: 600;
                                            color: #0f172a;
                                        }
                                        .disc-overview-summary {
                                            font-size: 0.95rem;
                                            line-height: 1.7;
                                            color: rgba(15, 23, 42, 0.82);
                                        }
                                        .disc-counts-table-wrapper {
                                            overflow-x: auto;
                                        }
                                        .disc-counts-table {
                                            width: 100%;
                                            border-collapse: separate;
                                            border-spacing: 0;
                                            min-width: 420px;
                                            font-size: 0.9rem;
                                            border: 1px solid rgba(15, 23, 42, 0.12);
                                            border-radius: 12px;
                                            overflow: hidden;
                                            background-color: #ffffff;
                                        }
                                        .disc-counts-table thead th {
                                            background: linear-gradient(120deg, rgba(249, 115, 22, 0.18), rgba(249, 115, 22, 0.05));
                                            color: #0f172a;
                                            font-weight: 600;
                                            text-align: right;
                                            padding: 12px 14px;
                                            border-bottom: 1px solid rgba(15, 23, 42, 0.12);
                                        }
                                        .disc-counts-table thead th:first-child {
                                            border-top-right-radius: 12px;
                                        }
                                        .disc-counts-table thead th:last-child {
                                            border-top-left-radius: 12px;
                                        }
                                        .disc-counts-table tbody td,
                                        .disc-counts-table tfoot th,
                                        .disc-counts-table tfoot td {
                                            padding: 12px 14px;
                                            border-bottom: 1px solid rgba(15, 23, 42, 0.08);
                                            vertical-align: middle;
                                        }
                                        .disc-counts-table tbody tr:last-child td {
                                            border-bottom: none;
                                        }
                                        .disc-counts-table tfoot th,
                                        .disc-counts-table tfoot td {
                                            background: rgba(15, 23, 42, 0.06);
                                            font-weight: 600;
                                            color: #0f172a;
                                            border-top: 1px solid rgba(15, 23, 42, 0.12);
                                        }
                                        .disc-counts-table tfoot th:first-child,
                                        .disc-counts-table tfoot td:first-child {
                                            border-bottom-right-radius: 12px;
                                        }
                                        .disc-counts-table tfoot th:last-child,
                                        .disc-counts-table tfoot td:last-child {
                                            border-bottom-left-radius: 12px;
                                        }
                                        .disc-counts-table td:first-child,
                                        .disc-counts-table th:first-child {
                                            width: 32%;
                                        }
                                        .disc-counts-table tbody tr:nth-child(2n) {
                                            background-color: rgba(15, 23, 42, 0.02);
                                        }
                                        .disc-counts-table tbody tr:hover {
                                            background-color: rgba(249, 115, 22, 0.08);
                                        }
                                        .disc-counts-progress {
                                            display: flex;
                                            align-items: center;
                                            gap: 12px;
                                        }
                                        .disc-counts-progress-track {
                                            flex: 1;
                                            height: 10px;
                                            border-radius: 999px;
                                            background-color: rgba(15, 23, 42, 0.1);
                                            overflow: hidden;
                                            position: relative;
                                        }
                                        .disc-counts-progress-track--muted {
                                            background-color: rgba(15, 23, 42, 0.08);
                                        }
                                        .disc-counts-progress-fill {
                                            position: absolute;
                                            left: 0;
                                            top: 0;
                                            bottom: 0;
                                            border-radius: 999px;
                                            background: linear-gradient(90deg, var(--disc-accent, #f97316), rgba(249, 115, 22, 0.7));
                                        }
                                        .disc-counts-progress-value {
                                            font-size: 0.85rem;
                                            font-weight: 600;
                                            color: #0f172a;
                                            min-width: 52px;
                                        }
                                        .disc-counts-stat {
                                            font-size: 0.9rem;
                                            font-weight: 500;
                                            color: rgba(15, 23, 42, 0.82);
                                            text-align: center;
                                        }
                                        .disc-letter-badge {
                                            display: inline-flex;
                                            align-items: center;
                                            justify-content: center;
                                            width: 28px;
                                            height: 28px;
                                            border-radius: 999px;
                                            background-color: rgba(249, 115, 22, 0.16);
                                            color: var(--disc-accent, #f97316);
                                            font-weight: 600;
                                            font-size: 0.9rem;
                                            margin-left: 6px;
                                        }
                                        .disc-counts-dimension-label {
                                            font-weight: 500;
                                            color: #0f172a;
                                        }
                                        .disc-overview-list {
                                            list-style: none;
                                            margin: 0;
                                            padding: 0;
                                            display: flex;
                                            flex-direction: column;
                                            gap: 8px;
                                        }
                                        .disc-overview-list li {
                                            font-size: 0.9rem;
                                            line-height: 1.6;
                                            color: rgba(15, 23, 42, 0.82);
                                        }
                                        .disc-overview-list--highlights li {
                                            display: flex;
                                            align-items: center;
                                            gap: 10px;
                                            padding: 12px 14px;
                                            border-radius: 12px;
                                            background-color: rgba(255, 255, 255, 0.86);
                                            border: 1px solid rgba(15, 23, 42, 0.06);
                                            box-shadow: 0 12px 28px -28px rgba(15, 23, 42, 0.38);
                                        }
                                        .disc-overview-list--highlights li ion-icon {
                                            color: var(--disc-accent, #f97316);
                                            font-size: 1.1rem;
                                        }
                                        .disc-overview-list--bullets li {
                                            position: relative;
                                            padding-left: 18px;
                                        }
                                        .disc-overview-list--bullets li::before {
                                            content: '•';
                                            position: absolute;
                                            left: 0;
                                            top: 0;
                                            color: var(--disc-accent, #f97316);
                                            font-size: 1rem;
                                            line-height: 1.6;
                                        }
                                        .disc-overview-sections {
                                            display: flex;
                                            flex-direction: column;
                                            gap: 16px;
                                        }
                                        .disc-overview-sections--grid {
                                            display: grid;
                                            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                                            gap: 16px;
                                        }
                                        .disc-overview-subsection {
                                            border: 1px solid rgba(15, 23, 42, 0.08);
                                            border-radius: 14px;
                                            padding: 16px 18px;
                                            background-color: rgba(255, 255, 255, 0.94);
                                            box-shadow: 0 16px 36px -36px rgba(15, 23, 42, 0.42);
                                        }
                                        .disc-overview-subsection-title {
                                            font-size: 0.92rem;
                                            font-weight: 600;
                                            color: #0f172a;
                                            margin-bottom: 10px;
                                        }
                                    </style>
                            <?php
                                endif;

                                $accentColor = $sanitizeColor($props['accentColor'] ?? '#f97316', '#f97316');
                                $supportColor = $sanitizeColor($props['supportColor'] ?? '#0f172a', '#0f172a');

                                $headline = isset($props['headline']) ? trim((string) $props['headline']) : 'تحلیل جامع DISC';
                                $showHeadline = $isTruthy($props['showHeadline'] ?? 1) && $headline !== '';

                                $summaryText = '';
                                if ($useRuntimeDisc) {
                                    $summaryText = isset($discData['summary']) ? trim((string) $discData['summary']) : '';
                                }
                                if ($summaryText === '' && isset($props['summaryText'])) {
                                    $summaryText = trim((string) $props['summaryText']);
                                }
                
                                if ($summaryText === '' && isset($sampleDisc['summary'])) {
                                    $summaryText = (string) $sampleDisc['summary'];
                                }
                                $showSummary = $isTruthy($props['showSummary'] ?? 1) && $summaryText !== '';

                                $highlights = [];
                                if ($useRuntimeDisc) {
                                    $highlights = $normalizeList($discData['highlights'] ?? []);
                                }
                                if (empty($highlights)) {
                                    $highlights = $normalizeList($props['highlights'] ?? ($sampleDisc['highlights'] ?? []));
                                }
                                $showHighlights = $isTruthy($props['showHighlights'] ?? 1) && !empty($highlights);
                                $highlightTitle = isset($props['highlightTitle']) ? trim((string) $props['highlightTitle']) : 'نکات برجسته';

                                $dominantCode = strtoupper(trim((string) ($discData['primary_code'] ?? '')));
                                $dominantLabel = trim((string) ($discData['primary_label'] ?? ''));
                                $dominantDisplay = $dominantLabel !== '' ? $dominantLabel : $dominantCode;
                                if ($dominantDisplay !== '' && $dominantCode !== '' && stripos($dominantDisplay, $dominantCode) === false) {
                                    $dominantDisplay .= ' (' . $dominantCode . ')';
                                }

                                $secondaryCodeRaw = isset($discData['secondary_code']) ? (string) $discData['secondary_code'] : '';
                                $secondaryLabel = isset($discData['secondary_label']) ? trim((string) $discData['secondary_label']) : '';
                                $showSecondary = $isTruthy($props['showSecondary'] ?? 1) && ($secondaryLabel !== '' || $secondaryCodeRaw !== '');

                                $bestCounts = isset($discData['best_counts']) && is_array($discData['best_counts']) ? $discData['best_counts'] : [];
                                $leastCounts = isset($discData['least_counts']) && is_array($discData['least_counts']) ? $discData['least_counts'] : [];

                                $discLetters = ['D', 'I', 'S', 'C'];
                                foreach ($discLetters as $letter) {
                                    if (!isset($bestCounts[$letter])) {
                                        $bestCounts[$letter] = 0;
                                    }
                                    if (!isset($leastCounts[$letter])) {
                                        $leastCounts[$letter] = 0;
                                    }
                                }

                                $bestTotal = array_sum($bestCounts);
                                $leastTotal = array_sum($leastCounts);

                                $discLabels = ['D' => 'Dominance', 'I' => 'Influence', 'S' => 'Steadiness', 'C' => 'Conscientiousness'];
                                $scoreRows = [];
                                foreach ($discLetters as $letter) {
                                    $percentValue = isset($discData[$letter]) ? (float) $discData[$letter] : 0.0;
                                    if ($percentValue <= 0 && $bestTotal > 0) {
                                        $percentValue = (($bestCounts[$letter] ?? 0) * 100) / max(1, $bestTotal);
                                    }
                                    $percentValue = $clampPercent($percentValue);
                                    $scoreRows[$letter] = [
                                        'key' => $letter,
                                        'label' => $discLabels[$letter],
                                        'percent' => $percentValue,
                                        'labelPercent' => $formatPercentLabel($percentValue),
                                        'best' => (int) ($bestCounts[$letter] ?? 0),
                                        'least' => (int) ($leastCounts[$letter] ?? 0),
                                    ];
                                }

                                $primaryPercentLabel = ($dominantCode !== '' && isset($scoreRows[$dominantCode]))
                                    ? $scoreRows[$dominantCode]['labelPercent']
                                    : '';

                                $secondaryPercentLabel = '';
                                if ($secondaryCodeRaw !== '') {
                                    $secondaryPrimaryLetter = strtoupper(substr($secondaryCodeRaw, 0, 1));
                                    if (isset($scoreRows[$secondaryPrimaryLetter])) {
                                        $secondaryPercentLabel = $scoreRows[$secondaryPrimaryLetter]['labelPercent'];
                                    }
                                }

                                $primaryMeta = isset($discData['primary_meta']) && is_array($discData['primary_meta']) ? $discData['primary_meta'] : [];
                                $secondaryMeta = isset($discData['secondary_meta']) && is_array($discData['secondary_meta']) ? $discData['secondary_meta'] : [];
                                $shortPrimary = isset($primaryMeta['short_description']) ? trim((string) $primaryMeta['short_description']) : '';
                                if ($shortPrimary === '' && $summaryText !== '') {
                                    $shortPrimary = $summaryText;
                                }
                                $shortSecondary = isset($secondaryMeta['short_description']) ? trim((string) $secondaryMeta['short_description']) : '';

                                $countsHeadline = isset($props['countsHeadline']) ? trim((string) $props['countsHeadline']) : 'مقایسه پاسخ‌ها';
                                $hasCounts = $isTruthy($props['showCounts'] ?? 1) && $bestTotal > 0;

                                $visibleSections = isset($props['visibleSections']) && is_array($props['visibleSections'])
                                    ? array_values(array_filter($props['visibleSections'], static function ($item) {
                                        return is_string($item) && trim($item) !== '';
                                    }))
                                    : ['general_tendencies', 'work_preferences', 'effectiveness_requirements', 'companion_requirements'];

                                $sectionTitles = [
                                    'general_tendencies' => 'گرایش‌های کلی',
                                    'work_preferences' => 'ترجیحات کاری',
                                    'effectiveness_requirements' => 'نیازهای موفقیت',
                                    'companion_requirements' => 'انتظارات از همکاران',
                                ];

                                $sectionEntries = [];
                                foreach ($visibleSections as $sectionKey) {
                                    $sectionKeyClean = is_string($sectionKey) ? trim($sectionKey) : '';
                                    if ($sectionKeyClean === '') {
                                        continue;
                                    }
                                    $items = [];
                                    if (isset($primaryMeta[$sectionKeyClean])) {
                                        $items = $normalizeList($primaryMeta[$sectionKeyClean]);
                                    }
                                    if (empty($items) && isset($secondaryMeta[$sectionKeyClean])) {
                                        $items = $normalizeList($secondaryMeta[$sectionKeyClean]);
                                    }
                                    if (empty($items)) {
                                        continue;
                                    }
                                    $sectionEntries[] = [
                                        'key' => $sectionKeyClean,
                                        'title' => $sectionTitles[$sectionKeyClean] ?? 'سرفصل رفتاری',
                                        'items' => $items,
                                    ];
                                }
                                $showSections = $isTruthy($props['showSections'] ?? 1) && !empty($sectionEntries);
                                $sectionsDisplay = isset($props['sectionsDisplay']) && in_array($props['sectionsDisplay'], ['stacked', 'two-column'], true)
                                    ? $props['sectionsDisplay']
                                    : 'two-column';

                                $widthClass = $elementWidthClass($props);
                                $outerClassAttr = implode(' ', array_filter(['certificate-element', $widthClass]));
                            ?>
                            <div class="<?= $escape($outerClassAttr); ?>"<?= $elementIdAttr; ?>>
                                <div class="certificate-disc-overview" style="--disc-accent: <?= $escape($accentColor); ?>; --disc-support: <?= $escape($supportColor); ?>;">
                                    <?php if ($showHeadline): ?>
                                        <div class="disc-overview-headline"><?= $escape($headline); ?></div>
                                    <?php endif; ?>
                                    <?php if ($dominantDisplay !== '' || ($showSecondary && ($secondaryLabel !== '' || $secondaryCodeRaw !== ''))): ?>
                                        <div class="disc-overview-stats">
                                            <?php if ($dominantDisplay !== ''): ?>
                                                <div class="disc-overview-card disc-overview-card--dominant">
                                                    <div class="disc-overview-card-label">سبک غالب</div>
                                                    <div class="disc-overview-card-value"><?= $escape($dominantDisplay); ?></div>
                                                    <?php if ($primaryPercentLabel !== ''): ?>
                                                        <div class="disc-overview-card-meta"><?= $escape($primaryPercentLabel); ?></div>
                                                    <?php endif; ?>
                                                    <?php if ($shortPrimary !== ''): ?>
                                                        <p class="disc-overview-card-desc"><?= $escape($shortPrimary); ?></p>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($showSecondary && ($secondaryLabel !== '' || $secondaryCodeRaw !== '')): ?>
                                                <?php
                                                    $secondaryDisplay = $secondaryLabel !== '' ? $secondaryLabel : $secondaryCodeRaw;
                                                    if ($secondaryDisplay !== '' && $secondaryCodeRaw !== '' && stripos($secondaryDisplay, $secondaryCodeRaw) === false) {
                                                        $secondaryDisplay .= ' (' . strtoupper($secondaryCodeRaw) . ')';
                                                    }
                                                ?>
                                                <div class="disc-overview-card disc-overview-card--secondary">
                                                    <div class="disc-overview-card-label">سبک پشتیبان</div>
                                                    <div class="disc-overview-card-value"><?= $escape($secondaryDisplay); ?></div>
                                                    <?php if ($secondaryPercentLabel !== ''): ?>
                                                        <div class="disc-overview-card-meta"><?= $escape($secondaryPercentLabel); ?></div>
                                                    <?php endif; ?>
                                                    <?php if ($shortSecondary !== ''): ?>
                                                        <p class="disc-overview-card-desc"><?= $escape($shortSecondary); ?></p>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php
                                        $leftColumnHasContent = $showSummary || $hasCounts;
                                        $rightColumnHasContent = $showHighlights || $showSections;
                                        $gridClass = 'disc-overview-grid';
                                        if (!$leftColumnHasContent || !$rightColumnHasContent) {
                                            $gridClass .= ' disc-overview-grid--single';
                                        }
                                    ?>

                                    <?php if ($leftColumnHasContent || $rightColumnHasContent): ?>
                                        <div class="<?= $escape($gridClass); ?>">
                                            <?php if ($leftColumnHasContent): ?>
                                                <div class="disc-overview-column">
                                                    <?php if ($showSummary): ?>
                                                        <div class="disc-overview-block">
                                                            <div class="disc-overview-block-title">خلاصه تحلیلی</div>
                                                            <div class="disc-overview-summary"><?= $escape($summaryText); ?></div>
                                                        </div>
                                                    <?php endif; ?>

                                                    <?php if ($hasCounts): ?>
                                                        <div class="disc-overview-block">
                                                            <div class="disc-overview-block-title"><?= $escape($countsHeadline); ?></div>
                                                            <div class="disc-counts-table-wrapper">
                                                                <table class="disc-counts-table">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>مولفه</th>
                                                                            <th>شدت نسبی</th>
                                                                            <th>پاسخ‌های بیشتر</th>
                                                                            <th>پاسخ‌های کمتر</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php foreach ($discLetters as $letter): ?>
                                                                            <?php $row = $scoreRows[$letter]; ?>
                                                                            <tr>
                                                                                <td>
                                                                                    <span class="disc-letter-badge"><?= $escape($row['key']); ?></span>
                                                                                    <span class="disc-counts-dimension-label"><?= $escape($row['label']); ?></span>
                                                                                </td>
                                                                                <td>
                                                                                    <div class="disc-counts-progress">
                                                                                        <div class="disc-counts-progress-track">
                                                                                            <div class="disc-counts-progress-fill" style="width: <?= $escape(number_format($row['percent'], 2, '.', '')); ?>%;"></div>
                                                                                        </div>
                                                                                        <div class="disc-counts-progress-value"><?= $escape($row['labelPercent']); ?></div>
                                                                                    </div>
                                                                                </td>
                                                                                <td class="disc-counts-stat"><?= $escape((string) $row['best']); ?></td>
                                                                                <td class="disc-counts-stat"><?= $escape((string) $row['least']); ?></td>
                                                                            </tr>
                                                                        <?php endforeach; ?>
                                                                    </tbody>
                                                                    <tfoot>
                                                                        <tr>
                                                                            <th>جمع</th>
                                                                            <th>
                                                                                <div class="disc-counts-progress">
                                                                                    <div class="disc-counts-progress-track disc-counts-progress-track--muted">
                                                                                        <div class="disc-counts-progress-fill" style="width: 100%;"></div>
                                                                                    </div>
                                                                                    <div class="disc-counts-progress-value"><?= $escape($formatPercentLabel(100.0)); ?></div>
                                                                                </div>
                                                                            </th>
                                                                            <th class="disc-counts-stat"><?= $escape((string) $bestTotal); ?></th>
                                                                            <th class="disc-counts-stat"><?= $escape((string) $leastTotal); ?></th>
                                                                        </tr>
                                                                    </tfoot>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($rightColumnHasContent): ?>
                                                <div class="disc-overview-column">
                                                    <?php if ($showHighlights): ?>
                                                        <div class="disc-overview-block">
                                                            <div class="disc-overview-block-title"><?= $escape($highlightTitle); ?></div>
                                                            <ul class="disc-overview-list disc-overview-list--highlights">
                                                                <?php foreach ($highlights as $item): ?>
                                                                    <li>
                                                                        <ion-icon name="sparkles-outline"></ion-icon>
                                                                        <span><?= $escape($item); ?></span>
                                                                    </li>
                                                                <?php endforeach; ?>
                                                            </ul>
                                                        </div>
                                                    <?php endif; ?>

                                                    <?php if ($showSections): ?>
                                                        <div class="disc-overview-block">
                                                            <div class="disc-overview-block-title">ترجیحات رفتاری</div>
                                                            <div class="disc-overview-sections<?= $sectionsDisplay === 'two-column' ? ' disc-overview-sections--grid' : ''; ?>">
                                                                <?php foreach ($sectionEntries as $entry): ?>
                                                                    <div class="disc-overview-subsection">
                                                                        <div class="disc-overview-subsection-title"><?= $escape($entry['title']); ?></div>
                                                                        <ul class="disc-overview-list disc-overview-list--bullets">
                                                                            <?php foreach ($entry['items'] as $item): ?>
                                                                                <li><?= $escape($item); ?></li>
                                                                            <?php endforeach; ?>
                                                                        </ul>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                        <?php elseif ($type === 'disc_single_graph'): ?>
                            <?php
                                $letters = ['D', 'I', 'S', 'C'];
                                $sampleDisc = isset($sampleData['disc_profile']) && is_array($sampleData['disc_profile']) ? $sampleData['disc_profile'] : [];
                                $runtimeDisc = isset($runtimeDatasets['disc_profile']) && is_array($runtimeDatasets['disc_profile']) ? $runtimeDatasets['disc_profile'] : [];
                                $useRuntimeDisc = !empty($runtimeDisc);

                                $points = [];
                                if ($useRuntimeDisc) {
                                    $runtimeIntensity = isset($runtimeDisc['intensity_scores']) && is_array($runtimeDisc['intensity_scores'])
                                        ? $runtimeDisc['intensity_scores']
                                        : [];
                                    foreach ($letters as $letter) {
                                        if (isset($runtimeIntensity[$letter]) && is_numeric($runtimeIntensity[$letter])) {
                                            $points[$letter] = (float) $runtimeIntensity[$letter];
                                            continue;
                                        }
                                        $points[$letter] = isset($runtimeDisc[$letter]) && is_numeric($runtimeDisc[$letter]) ? (float) $runtimeDisc[$letter] : 0.0;
                                    }
                                }

                                if (empty($points)) {
                                    $points = [
                                        'D' => isset($props['scoreD']) && is_numeric($props['scoreD']) ? (float) $props['scoreD'] : (float) ($sampleDisc['D'] ?? 0),
                                        'I' => isset($props['scoreI']) && is_numeric($props['scoreI']) ? (float) $props['scoreI'] : (float) ($sampleDisc['I'] ?? 0),
                                        'S' => isset($props['scoreS']) && is_numeric($props['scoreS']) ? (float) $props['scoreS'] : (float) ($sampleDisc['S'] ?? 0),
                                        'C' => isset($props['scoreC']) && is_numeric($props['scoreC']) ? (float) $props['scoreC'] : (float) ($sampleDisc['C'] ?? 0),
                                    ];
                                }

                                $accentColor = $sanitizeColor($props['accentColor'] ?? '#f97316', '#f97316');
                                if (isset($runtimeDisc['accentColor'])) {
                                    $accentColor = $sanitizeColor($runtimeDisc['accentColor'], $accentColor);
                                }

                                $showHeadline = $isTruthy($props['showHeadline'] ?? 1);
                                $headline = isset($props['headline']) ? trim((string) $props['headline']) : 'نمودار DISC';
                                if ($headline === '' && isset($sampleDisc['single_graph_headline'])) {
                                    $headline = (string) $sampleDisc['single_graph_headline'];
                                }

                                $showValues = $isTruthy($props['showValues'] ?? 1);

                                $summaryText = '';
                                if ($useRuntimeDisc && isset($runtimeDisc['summary'])) {
                                    $summaryText = trim((string) $runtimeDisc['summary']);
                                }
                                if ($summaryText === '' && isset($props['summaryText'])) {
                                    $summaryText = trim((string) $props['summaryText']);
                                }
                                if ($summaryText === '' && isset($sampleDisc['summary'])) {
                                    $summaryText = (string) $sampleDisc['summary'];
                                }
                                $showSummary = $isTruthy($props['showSummary'] ?? 1) && $summaryText !== '';

                                static $discSingleGraphStylesPrinted = false;
                                if (!$discSingleGraphStylesPrinted):
                                    $discSingleGraphStylesPrinted = true;
                            ?>
                                    <style>
                                        .certificate-disc-single {
                                            border: 1px solid rgba(15, 23, 42, 0.1);
                                            border-radius: 20px;
                                            padding: 24px;
                                            background: linear-gradient(140deg, rgba(249, 115, 22, 0.12), rgba(15, 23, 42, 0.03));
                                            display: flex;
                                            flex-direction: column;
                                            gap: 20px;
                                            box-shadow: 0 14px 36px -30px rgba(15, 23, 42, 0.48);
                                            width: min(100%, 420px);
                                            flex: 0 0 auto;
                                        }
                                        .disc-single-size-small { width: min(100%, 320px); }
                                        .disc-single-size-medium { width: min(100%, 420px); }
                                        .disc-single-size-large { width: min(100%, 520px); }
                                        .disc-single-outer {
                                            display: flex;
                                            width: 100%;
                                        }
                                        .disc-single-outer-left { justify-content: flex-start; }
                                        .disc-single-outer-center { justify-content: center; }
                                        .disc-single-outer-right { justify-content: flex-end; }
                                        .disc-single-headline {
                                            font-size: 1.05rem;
                                            font-weight: 600;
                                            color: #0f172a;
                                            display: inline-flex;
                                            align-items: center;
                                            gap: 10px;
                                        }
                                        .disc-single-headline::before {
                                            content: '';
                                            width: 10px;
                                            height: 10px;
                                            border-radius: 999px;
                                            background: var(--disc-accent, #f97316);
                                            box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.2);
                                        }
                                        .disc-single-chart {
                                            width: 100%;
                                        }
                                        .disc-single-svg {
                                            width: 100%;
                                            height: auto;
                                        }
                                        .disc-single-plot {
                                            fill: rgba(249, 115, 22, 0.08);
                                            stroke: rgba(15, 23, 42, 0.08);
                                            stroke-width: 1;
                                        }
                                        .disc-single-gridline {
                                            stroke: rgba(15, 23, 42, 0.12);
                                            stroke-width: 0.8;
                                            stroke-dasharray: 4 4;
                                        }
                                        .disc-single-polyline {
                                            fill: none;
                                            stroke: var(--disc-accent, #f97316);
                                            stroke-width: 3;
                                            stroke-linejoin: round;
                                            stroke-linecap: round;
                                        }
                                        .disc-single-dot {
                                            fill: #ffffff;
                                            stroke: var(--disc-accent, #f97316);
                                            stroke-width: 2.2;
                                        }
                                        .disc-single-scale {
                                            fill: rgba(15, 23, 42, 0.6);
                                            font-size: 0.7rem;
                                            text-anchor: end;
                                        }
                                        .disc-single-letter {
                                            fill: #0f172a;
                                            font-size: 0.9rem;
                                            font-weight: 600;
                                            text-anchor: middle;
                                        }
                                        .disc-single-values {
                                            width: 100%;
                                            border-collapse: separate;
                                            border-spacing: 0;
                                            font-size: 0.88rem;
                                            text-align: center;
                                            border: 1px solid rgba(15, 23, 42, 0.1);
                                            border-radius: 14px;
                                            overflow: hidden;
                                            background-color: rgba(255, 255, 255, 0.9);
                                        }
                                        .disc-single-values thead th {
                                            padding: 10px 12px;
                                            background: rgba(15, 23, 42, 0.06);
                                            color: #0f172a;
                                            font-weight: 600;
                                        }
                                        .disc-single-values tbody td {
                                            padding: 10px 12px;
                                            border-top: 1px solid rgba(15, 23, 42, 0.06);
                                            color: rgba(15, 23, 42, 0.8);
                                        }
                                        .disc-single-summary {
                                            font-size: 0.92rem;
                                            line-height: 1.7;
                                            color: rgba(15, 23, 42, 0.8);
                                            border-right: 3px solid var(--disc-accent, #f97316);
                                            padding: 12px 16px;
                                            border-radius: 14px;
                                            background-color: rgba(255, 255, 255, 0.85);
                                        }
                                    </style>
                            <?php
                                endif;

                                $buildSingleSvg = static function (array $points, array $letters) {
                                    $chartWidth = 240;
                                    $chartHeight = 200;
                                    $marginX = 24;
                                    $marginY = 26;
                                    $values = array_values($points);
                                    $scaleMin = min(0, floor(min($values) / 10) * 10);
                                    $scaleMax = max(100, ceil(max($values) / 10) * 10);
                                    if ($scaleMax <= $scaleMin) {
                                        $scaleMax = $scaleMin + 10;
                                    }
                                    $range = max(1, $scaleMax - $scaleMin);
                                    $plotHeight = $chartHeight - ($marginY * 2);
                                    $plotWidth = $chartWidth - ($marginX * 2);
                                    $stepX = $plotWidth / (max(1, count($letters) - 1));

                                    $pointsPath = [];
                                    $dots = [];
                                    foreach ($letters as $index => $letter) {
                                        $value = $points[$letter] ?? 0.0;
                                        $ratio = ($value - $scaleMin) / $range;
                                        $clamped = max(0.0, min(1.0, $ratio));
                                        $x = $marginX + ($index * $stepX);
                                        $y = $marginY + ((1 - $clamped) * $plotHeight);
                                        $pointsPath[] = number_format($x, 2, '.', '') . ',' . number_format($y, 2, '.', '');
                                        $dots[] = '<circle cx="' . number_format($x, 2, '.', '') . '" cy="' . number_format($y, 2, '.', '') . '" r="4" class="disc-single-dot" />';
                                    }

                                    $gridLines = [];
                                    $ticks = 5;
                                    for ($i = 0; $i <= $ticks; $i++) {
                                        $ratio = $i / $ticks;
                                        $y = $marginY + ((1 - $ratio) * $plotHeight);
                                        $value = $scaleMin + ($ratio * $range);
                                        $gridLines[] = '<line x1="' . $marginX . '" y1="' . number_format($y, 2, '.', '') . '" x2="' . ($chartWidth - $marginX) . '" y2="' . number_format($y, 2, '.', '') . '" class="disc-single-gridline" />';
                                        $gridLines[] = '<text x="' . ($marginX - 8) . '" y="' . number_format($y + 4, 2, '.', '') . '" class="disc-single-scale">' . number_format($value, 0, '.', '') . '</text>';
                                    }

                                    $lettersLabels = [];
                                    foreach ($letters as $index => $letter) {
                                        $x = $marginX + ($index * $stepX);
                                        $lettersLabels[] = '<text x="' . number_format($x, 2, '.', '') . '" y="' . ($chartHeight - 10) . '" class="disc-single-letter">' . htmlspecialchars($letter, ENT_QUOTES, 'UTF-8') . '</text>';
                                    }

                                    $polyline = '<polyline points="' . implode(' ', $pointsPath) . '" class="disc-single-polyline" />';

                                    return '<svg class="disc-single-svg" viewBox="0 0 ' . $chartWidth . ' ' . $chartHeight . '" preserveAspectRatio="xMidYMid meet">'
                                        . '<rect x="' . $marginX . '" y="' . $marginY . '" width="' . $plotWidth . '" height="' . $plotHeight . '" class="disc-single-plot" />'
                                        . implode('', $gridLines)
                                        . $polyline
                                        . implode('', $dots)
                                        . implode('', $lettersLabels)
                                        . '</svg>';
                                };

                                $chartSize = isset($props['chartSize']) && is_string($props['chartSize']) ? strtolower(trim($props['chartSize'])) : 'medium';
                                if (!in_array($chartSize, ['small', 'medium', 'large'], true)) {
                                    $chartSize = 'medium';
                                }

                                $align = isset($props['align']) && is_string($props['align']) ? strtolower(trim($props['align'])) : 'center';
                                if (!in_array($align, ['left', 'center', 'right'], true)) {
                                    $align = 'center';
                                }

                                $sizeClassMap = [
                                    'small' => 'disc-single-size-small',
                                    'medium' => 'disc-single-size-medium',
                                    'large' => 'disc-single-size-large',
                                ];
                                $outerAlignClassMap = [
                                    'left' => 'disc-single-outer-left',
                                    'center' => 'disc-single-outer-center',
                                    'right' => 'disc-single-outer-right',
                                ];

                                $widthClass = $elementWidthClass($props);
                                $outerClasses = array_filter(['certificate-element', $widthClass, 'disc-single-outer', $outerAlignClassMap[$align] ?? 'disc-single-outer-center']);
                                $outerClassAttr = implode(' ', $outerClasses);

                                $innerClasses = array_filter(['certificate-disc-single', $sizeClassMap[$chartSize] ?? $sizeClassMap['medium']]);
                                $innerClassAttr = implode(' ', $innerClasses);
                            ?>
                            <div class="<?= $escape($outerClassAttr); ?>"<?= $elementIdAttr; ?>>
                                <div class="<?= $escape($innerClassAttr); ?>" style="--disc-accent: <?= $escape($accentColor); ?>;">
                                    <?php if ($showHeadline && $headline !== ''): ?>
                                        <div class="disc-single-headline"><?= $escape($headline); ?></div>
                                    <?php endif; ?>
                                    <div class="disc-single-chart">
                                        <?= $buildSingleSvg($points, $letters); ?>
                                    </div>
                                    <?php if ($showValues): ?>
                                        <table class="disc-single-values">
                                            <thead>
                                                <tr>
                                                    <?php foreach ($letters as $letter): ?>
                                                        <th><?= $escape($letter); ?></th>
                                                    <?php endforeach; ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <?php foreach ($letters as $letter): ?>
                                                        <td><?= $escape(number_format($points[$letter] ?? 0, 0, '.', '')); ?></td>
                                                    <?php endforeach; ?>
                                                </tr>
                                            </tbody>
                                        </table>
                                    <?php endif; ?>
                                    <?php if ($showSummary): ?>
                                        <div class="disc-single-summary"><?= $escape($summaryText); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                        <?php elseif ($type === 'disc_triple_graphs'): ?>
                            <?php
                                $letters = ['D', 'I', 'S', 'C'];
                                $sampleDisc = isset($sampleData['disc_profile']) && is_array($sampleData['disc_profile']) ? $sampleData['disc_profile'] : [];
                                $sampleTriple = isset($sampleDisc['triple_graphs']) && is_array($sampleDisc['triple_graphs']) ? $sampleDisc['triple_graphs'] : [];
                                $runtimeDisc = isset($runtimeDatasets['disc_profile']) && is_array($runtimeDatasets['disc_profile']) ? $runtimeDatasets['disc_profile'] : [];
                                $runtimeTriple = isset($runtimeDisc['triple_graphs']) && is_array($runtimeDisc['triple_graphs']) ? $runtimeDisc['triple_graphs'] : [];

                                $graphsInput = [];
                                if (isset($runtimeTriple['graphs']) && is_array($runtimeTriple['graphs']) && !empty($runtimeTriple['graphs'])) {
                                    $graphsInput = $runtimeTriple['graphs'];
                                }
                                if (empty($graphsInput)) {
                                    $graphsJsonProp = isset($props['graphsJson']) ? trim((string) $props['graphsJson']) : '';
                                    if ($graphsJsonProp !== '') {
                                        $decoded = json_decode($graphsJsonProp, true);
                                        if (is_array($decoded)) {
                                            $graphsInput = $decoded;
                                        }
                                    }
                                }
                                if (empty($graphsInput) && isset($sampleTriple['graphs']) && is_array($sampleTriple['graphs'])) {
                                    $graphsInput = $sampleTriple['graphs'];
                                }

                                $normalizeGraphs = static function ($graphs) use ($letters) {
                                    $normalized = [];
                                    if (!is_array($graphs)) {
                                        return $normalized;
                                    }
                                    foreach ($graphs as $graph) {
                                        if (!is_array($graph)) {
                                            continue;
                                        }
                                        $points = [];
                                        $rawPoints = $graph['points'] ?? ($graph['values'] ?? null);
                                        if (is_array($rawPoints)) {
                                            foreach ($letters as $letter) {
                                                if (isset($rawPoints[$letter]) && is_numeric($rawPoints[$letter])) {
                                                    $points[$letter] = (float) $rawPoints[$letter];
                                                }
                                            }
                                        }
                                        if (count($points) !== 4) {
                                            continue;
                                        }
                                        $normalized[] = [
                                            'title' => isset($graph['title']) ? trim((string) $graph['title']) : '',
                                            'subtitle' => isset($graph['subtitle']) ? trim((string) $graph['subtitle']) : '',
                                            'points' => $points,
                                            'summary' => isset($graph['summary']) ? trim((string) $graph['summary']) : '',
                                            'notes' => isset($graph['notes']) && is_array($graph['notes']) ? $graph['notes'] : [],
                                        ];
                                    }
                                    return $normalized;
                                };

                                $graphsData = $normalizeGraphs($graphsInput);
                                if (empty($graphsData) && isset($sampleTriple['graphs'])) {
                                    $graphsData = $normalizeGraphs($sampleTriple['graphs']);
                                }
                                if (empty($graphsData)) {
                                    $graphsData = [];
                                }

                                $descriptionHeadline = isset($props['descriptionHeadline']) ? trim((string) $props['descriptionHeadline']) : 'مفهوم نمودارها';
                                $descriptionList = [];
                                if (isset($runtimeTriple['descriptions']) && is_array($runtimeTriple['descriptions'])) {
                                    $descriptionList = $normalizeList($runtimeTriple['descriptions']);
                                }
                                if (empty($descriptionList)) {
                                    $descriptionList = $normalizeList($props['descriptionList'] ?? []);
                                }
                                if (empty($descriptionList) && isset($sampleTriple['descriptions'])) {
                                    $descriptionList = $normalizeList($sampleTriple['descriptions']);
                                }

                                $showHeadline = $isTruthy($props['showHeadline'] ?? 1);
                                $headline = isset($props['headline']) ? trim((string) $props['headline']) : 'نمودارهای استاندارد DISC';
                                if ($headline === '' && isset($sampleTriple['headline'])) {
                                    $headline = trim((string) $sampleTriple['headline']);
                                }

                                static $discTripleGraphStylesPrinted = false;
                                if (!$discTripleGraphStylesPrinted):
                                    $discTripleGraphStylesPrinted = true;
                            ?>
                                    <style>
                                        .certificate-disc-triple {
                                            border: 1px solid rgba(15, 23, 42, 0.1);
                                            border-radius: 22px;
                                            padding: 28px;
                                            background: linear-gradient(135deg, rgba(15, 23, 42, 0.04), rgba(255, 255, 255, 0.9));
                                            box-shadow: 0 18px 40px -32px rgba(15, 23, 42, 0.5);
                                            display: flex;
                                            flex-direction: column;
                                            gap: 24px;
                                        }
                                        .disc-triple-headline {
                                            font-size: 1.08rem;
                                            font-weight: 600;
                                            color: #0f172a;
                                            display: inline-flex;
                                            align-items: center;
                                            gap: 10px;
                                        }
                                        .disc-triple-headline::before {
                                            content: '';
                                            width: 12px;
                                            height: 12px;
                                            border-radius: 999px;
                                            background: var(--disc-accent, #2563eb);
                                            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.18);
                                        }
                                        .disc-triple-grid {
                                            display: grid;
                                            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                                            gap: 20px;
                                        }
                                        .disc-triple-card {
                                            background-color: rgba(255, 255, 255, 0.9);
                                            border: 1px solid rgba(15, 23, 42, 0.08);
                                            border-radius: 18px;
                                            padding: 18px;
                                            display: flex;
                                            flex-direction: column;
                                            gap: 14px;
                                            box-shadow: 0 16px 36px -34px rgba(15, 23, 42, 0.42);
                                        }
                                        .disc-triple-card-header {
                                            display: flex;
                                            flex-direction: column;
                                            gap: 4px;
                                        }
                                        .disc-triple-card-title {
                                            font-size: 1rem;
                                            font-weight: 600;
                                            color: #0f172a;
                                        }
                                        .disc-triple-card-subtitle {
                                            font-size: 0.88rem;
                                            color: rgba(15, 23, 42, 0.7);
                                        }
                                        .disc-triple-svg {
                                            width: 100%;
                                            height: auto;
                                        }
                                        .disc-triple-plot {
                                            fill: rgba(37, 99, 235, 0.05);
                                            stroke: rgba(15, 23, 42, 0.08);
                                            stroke-width: 1;
                                        }
                                        .disc-triple-gridline {
                                            stroke: rgba(15, 23, 42, 0.12);
                                            stroke-width: 0.8;
                                            stroke-dasharray: 4 4;
                                        }
                                        .disc-triple-polyline {
                                            fill: none;
                                            stroke: var(--disc-accent, #2563eb);
                                            stroke-width: 3;
                                            stroke-linejoin: round;
                                            stroke-linecap: round;
                                        }
                                        .disc-triple-dot {
                                            fill: #ffffff;
                                            stroke: var(--disc-accent, #2563eb);
                                            stroke-width: 2;
                                        }
                                        .disc-triple-scale {
                                            fill: rgba(15, 23, 42, 0.65);
                                            font-size: 0.7rem;
                                            text-anchor: end;
                                        }
                                        .disc-triple-letter {
                                            fill: #0f172a;
                                            font-size: 0.9rem;
                                            font-weight: 600;
                                            text-anchor: middle;
                                        }
                                        .disc-triple-values {
                                            width: 100%;
                                            border-collapse: separate;
                                            border-spacing: 0;
                                            font-size: 0.85rem;
                                            text-align: center;
                                        }
                                        .disc-triple-values thead th {
                                            padding: 6px 8px;
                                            background-color: rgba(15, 23, 42, 0.05);
                                            color: #0f172a;
                                            font-weight: 600;
                                        }
                                        .disc-triple-values tbody td {
                                            padding: 6px 8px;
                                            border-bottom: 1px solid rgba(15, 23, 42, 0.08);
                                            color: rgba(15, 23, 42, 0.75);
                                        }
                                        .disc-triple-values tbody tr:last-child td {
                                            border-bottom: none;
                                        }
                                        .disc-triple-summary {
                                            font-size: 0.9rem;
                                            line-height: 1.6;
                                            color: rgba(15, 23, 42, 0.78);
                                        }
                                        .disc-triple-notes {
                                            display: flex;
                                            flex-wrap: wrap;
                                            gap: 10px;
                                            font-size: 0.82rem;
                                            color: rgba(15, 23, 42, 0.7);
                                        }
                                        .disc-triple-note {
                                            padding: 6px 10px;
                                            border-radius: 999px;
                                            background-color: rgba(37, 99, 235, 0.1);
                                        }
                                        .disc-triple-descriptions {
                                            display: flex;
                                            flex-direction: column;
                                            gap: 12px;
                                        }
                                        .disc-triple-descriptions-title {
                                            font-size: 0.95rem;
                                            font-weight: 600;
                                            color: #0f172a;
                                        }
                                        .disc-triple-descriptions ul {
                                            margin: 0;
                                            padding-right: 18px;
                                            display: flex;
                                            flex-direction: column;
                                            gap: 10px;
                                            font-size: 0.9rem;
                                            color: rgba(15, 23, 42, 0.82);
                                        }
                                        .disc-triple-descriptions li {
                                            position: relative;
                                        }
                                        .disc-triple-descriptions li::before {
                                            content: '•';
                                            position: absolute;
                                            right: -16px;
                                            top: 0;
                                            color: var(--disc-accent, #2563eb);
                                        }
                                    </style>
                            <?php
                                endif;

                                $widthClass = $elementWidthClass($props);
                                $outerClassAttr = implode(' ', array_filter(['certificate-element', $widthClass]));

                                $buildSvg = static function (array $points, array $letters) {
                                    $chartWidth = 240;
                                    $chartHeight = 220;
                                    $marginX = 24;
                                    $marginY = 28;
                                    $minValue = min($points);
                                    $maxValue = max($points);
                                    $scaleMin = min($minValue, 0.0);
                                    $scaleMax = max($maxValue, 100.0);
                                    if ($scaleMax - $scaleMin < 10) {
                                        $scaleMax += 5;
                                        $scaleMin -= 5;
                                    }
                                    $range = max(1.0, $scaleMax - $scaleMin);
                                    $plotHeight = $chartHeight - (2 * $marginY);
                                    $plotWidth = $chartWidth - (2 * $marginX);
                                    $stepX = $plotWidth / (count($letters) - 1);
                                    $pointsPath = [];
                                    $dotElements = [];
                                    foreach ($letters as $index => $letter) {
                                        $value = $points[$letter];
                                        $normalized = ($value - $scaleMin) / $range;
                                        $clamped = max(0.0, min(1.0, $normalized));
                                        $x = $marginX + ($index * $stepX);
                                        $y = $marginY + ((1 - $clamped) * $plotHeight);
                                        $pointsPath[] = number_format($x, 2, '.', '') . ',' . number_format($y, 2, '.', '');
                                        $dotElements[] = '<circle cx="' . number_format($x, 2, '.', '') . '" cy="' . number_format($y, 2, '.', '') . '" r="4" class="disc-triple-dot" />';
                                    }
                                    $gridLines = [];
                                    $ticks = 5;
                                    for ($i = 0; $i <= $ticks; $i++) {
                                        $ratio = $i / $ticks;
                                        $y = $marginY + ((1 - $ratio) * $plotHeight);
                                        $value = $scaleMin + ($ratio * $range);
                                        $gridLines[] = '<line x1="' . $marginX . '" y1="' . number_format($y, 2, '.', '') . '" x2="' . ($chartWidth - $marginX) . '" y2="' . number_format($y, 2, '.', '') . '" class="disc-triple-gridline" />';
                                        $gridLines[] = '<text x="' . ($marginX - 8) . '" y="' . number_format($y + 4, 2, '.', '') . '" class="disc-triple-scale">' . number_format($value, 0, '.', '') . '</text>';
                                    }
                                    $letterLabels = [];
                                    foreach ($letters as $index => $letter) {
                                        $x = $marginX + ($index * $stepX);
                                        $letterLabels[] = '<text x="' . number_format($x, 2, '.', '') . '" y="' . ($chartHeight - 10) . '" class="disc-triple-letter">' . htmlspecialchars($letter, ENT_QUOTES, 'UTF-8') . '</text>';
                                    }

                                    $polyline = '<polyline points="' . implode(' ', $pointsPath) . '" class="disc-triple-polyline" />';
                                    return '<svg class="disc-triple-svg" viewBox="0 0 ' . $chartWidth . ' ' . $chartHeight . '" preserveAspectRatio="xMidYMid meet">'
                                        . '<rect x="' . $marginX . '" y="' . $marginY . '" width="' . $plotWidth . '" height="' . $plotHeight . '" class="disc-triple-plot" />'
                                        . implode('', $gridLines)
                                        . $polyline
                                        . implode('', $dotElements)
                                        . implode('', $letterLabels)
                                        . '</svg>';
                                };
                            ?>
                            <div class="<?= $escape($outerClassAttr); ?>"<?= $elementIdAttr; ?>>
                                <div class="certificate-disc-triple" style="--disc-accent: <?= $escape($accentColor); ?>;">
                                    <?php if ($showHeadline && $headline !== ''): ?>
                                        <div class="disc-triple-headline"><?= $escape($headline); ?></div>
                                    <?php endif; ?>

                                    <?php if (!empty($graphsData)): ?>
                                        <div class="disc-triple-grid">
                                            <?php foreach ($graphsData as $graph): ?>
                                                <?php
                                                    $graphSvg = $buildSvg($graph['points'], $letters);
                                                    $notes = [];
                                                    if (!empty($graph['notes']) && is_array($graph['notes'])) {
                                                        foreach ($graph['notes'] as $noteKey => $noteValue) {
                                                            if (!is_string($noteValue) && !is_numeric($noteValue)) {
                                                                continue;
                                                            }
                                                            $notes[] = trim($noteKey) !== ''
                                                                ? trim((string) $noteKey) . ': ' . trim((string) $noteValue)
                                                                : trim((string) $noteValue);
                                                        }
                                                    }
                                                ?>
                                                <div class="disc-triple-card">
                                                    <div class="disc-triple-card-header">
                                                        <?php if (!empty($graph['title'])): ?>
                                                            <div class="disc-triple-card-title"><?= $escape($graph['title']); ?></div>
                                                        <?php endif; ?>
                                                        <?php if (!empty($graph['subtitle'])): ?>
                                                            <div class="disc-triple-card-subtitle"><?= $escape($graph['subtitle']); ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?= $graphSvg; ?>
                                                    <table class="disc-triple-values">
                                                        <thead>
                                                            <tr>
                                                                <?php foreach ($letters as $letter): ?>
                                                                    <th><?= $escape($letter); ?></th>
                                                                <?php endforeach; ?>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <?php foreach ($letters as $letter): ?>
                                                                    <td><?= $escape(number_format($graph['points'][$letter], 0, '.', '')); ?></td>
                                                                <?php endforeach; ?>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                    <?php if (!empty($graph['summary'])): ?>
                                                        <div class="disc-triple-summary"><?= $escape($graph['summary']); ?></div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($notes)): ?>
                                                        <div class="disc-triple-notes">
                                                            <?php foreach ($notes as $noteText): ?>
                                                                <span class="disc-triple-note"><?= $escape($noteText); ?></span>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($descriptionList)): ?>
                                        <div class="disc-triple-descriptions">
                                            <?php if ($descriptionHeadline !== ''): ?>
                                                <div class="disc-triple-descriptions-title"><?= $escape($descriptionHeadline); ?></div>
                                            <?php endif; ?>
                                            <ul>
                                                <?php foreach ($descriptionList as $item): ?>
                                                    <li><?= $escape($item); ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                        <?php elseif ($type === 'gauge_indicator'): ?>
                            <?php
                                $headline = isset($props['headline']) ? trim((string) $props['headline']) : '';
                                $label = isset($props['label']) ? trim((string) $props['label']) : 'شاخص';
                                $unit = isset($props['unit']) ? trim((string) $props['unit']) : '';
                                $value = isset($props['value']) && is_numeric($props['value']) ? (float) $props['value'] : 0.0;
                                $maxValue = isset($props['maxValue']) && is_numeric($props['maxValue']) ? (float) $props['maxValue'] : 100.0;
                                $sizeRaw = isset($props['size']) && is_string($props['size']) ? strtolower(trim($props['size'])) : 'medium';
                                $size = in_array($sizeRaw, ['small', 'medium', 'large'], true) ? $sizeRaw : 'medium';
                                $accentColor = $sanitizeColor($props['accentColor'] ?? '#0ea5e9', '#0ea5e9');
                                $showDescription = $isTruthy($props['showDescription'] ?? 1);
                                $description = isset($props['description']) ? trim((string) $props['description']) : '';
                                $sourceKey = isset($props['sourceKey']) ? trim((string) $props['sourceKey']) : '';
                                if ($sourceKey !== '' && isset($skillLookup[$sourceKey]) && is_array($skillLookup[$sourceKey])) {
                                    $skillRow = $skillLookup[$sourceKey];
                                    if (isset($skillRow['score']) && is_numeric($skillRow['score'])) {
                                        $value = (float) $skillRow['score'];
                                    }
                                    if (isset($skillRow['max']) && is_numeric($skillRow['max'])) {
                                        $maxValue = (float) $skillRow['max'];
                                    }
                                    if (($label === 'شاخص' || $label === '') && isset($skillRow['label'])) {
                                        $label = (string) $skillRow['label'];
                                    }
                                }
                                $percent = 0.0;
                                if ($maxValue > 0) {
                                    $percent = max(0.0, min(1.0, $value / $maxValue));
                                }
                                $percentLabel = $formatPercentLabel($percent * 100);
                                $needleAngle = -90 + ($percent * 180);
                                $arcRadius = 90.0;
                                $arcLength = M_PI * $arcRadius;
                                $dashArray = number_format($arcLength, 4, '.', '');
                                $dashOffset = number_format($arcLength * (1 - $percent), 4, '.', '');
                                $widthClass = $elementWidthClass($props);
                                $outerClassAttr = implode(' ', array_filter(['certificate-element', $widthClass]));
                                $valueLabel = UtilityHelper::englishToPersian(number_format($value, 0));
                                $maxLabel = UtilityHelper::englishToPersian(number_format($maxValue, 0));
                            ?>
                            <div class="<?= $escape($outerClassAttr); ?>"<?= $elementIdAttr; ?>>
                                <div class="certificate-gauge" data-gauge-size="<?= $escape($size); ?>" style="--gauge-accent: <?= $escape($accentColor); ?>;">
                                    <?php if ($headline !== ''): ?>
                                        <div class="certificate-gauge-headline"><?= $escape($headline); ?></div>
                                    <?php endif; ?>
                                    <div class="certificate-gauge-body">
                                        <svg class="certificate-gauge-svg" viewBox="0 0 200 110" role="img" aria-label="<?= $escape($label); ?>">
                                            <path class="gauge-arc-bg" d="M10 100 A90 90 0 0 1 190 100" />
                                            <path class="gauge-arc-fill" d="M10 100 A90 90 0 0 1 190 100" style="stroke-dasharray: <?= $dashArray; ?>; stroke-dashoffset: <?= $dashOffset; ?>;" />
                                            <line class="gauge-needle" x1="100" y1="100" x2="100" y2="30" style="transform: rotate(<?= $escape(number_format($needleAngle, 2, '.', '')); ?>deg); transform-origin: 100px 100px;" />
                                            <circle class="gauge-needle-pivot" cx="100" cy="100" r="6" />
                                        </svg>
                                        <div class="certificate-gauge-info">
                                            <div class="certificate-gauge-label"><?= $escape($label); ?></div>
                                            <div class="certificate-gauge-value">
                                                <span class="value-number"><?= $valueLabel; ?></span>
                                                <?php if ($unit !== ''): ?>
                                                    <span class="value-unit"><?= $escape($unit); ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="certificate-gauge-range">حداکثر <?= $maxLabel; ?> (<?= $escape($percentLabel); ?>)</div>
                                        </div>
                                    </div>
                                    <?php if ($showDescription && $description !== ''): ?>
                                        <div class="certificate-gauge-description"><?= $escape($description); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                        <?php elseif ($type === 'analytical_thinking_insight'): ?>
                            <?php
                                $props = is_array($props) ? $props : [];
                                $runtimeAnalytical = isset($runtimeDatasets['analytical']) && is_array($runtimeDatasets['analytical'])
                                    ? $runtimeDatasets['analytical']
                                    : [];
                                $sampleAnalytical = isset($sampleData['analytical']) && is_array($sampleData['analytical'])
                                    ? $sampleData['analytical']
                                    : [];

                                $accentColor = $sanitizeColor($props['accentColor'] ?? ($runtimeAnalytical['accentColor'] ?? '#0ea5e9'), '#0ea5e9');
                                $showHeadline = $isTruthy($props['showHeadline'] ?? 1);
                                $headline = isset($props['headline']) ? trim((string) $props['headline']) : '';
                                if ($headline === '' && isset($sampleAnalytical['headline'])) {
                                    $headline = (string) $sampleAnalytical['headline'];
                                }
                                if ($headline === '') {
                                    $headline = 'نتیجه تفکر تحلیلی';
                                }

                                $analysisHeadline = isset($props['analysisHeadline']) ? trim((string) $props['analysisHeadline']) : '';
                                if ($analysisHeadline === '' && isset($runtimeAnalytical['analysis_headline'])) {
                                    $analysisHeadline = trim((string) $runtimeAnalytical['analysis_headline']);
                                }
                                if ($analysisHeadline === '' && isset($sampleAnalytical['analysis_headline'])) {
                                    $analysisHeadline = trim((string) $sampleAnalytical['analysis_headline']);
                                }
                                if ($analysisHeadline === '') {
                                    $analysisHeadline = 'تفسیر عملکرد';
                                }

                                $showDetails = $isTruthy($props['showDetails'] ?? 1);
                                $showBreakdown = $isTruthy($props['showBreakdown'] ?? 1);
                                $showAnalysisText = $isTruthy($props['showAnalysisText'] ?? 1);

                                $score = null;
                                if (isset($runtimeAnalytical['score']) && is_numeric($runtimeAnalytical['score'])) {
                                    $score = (float) $runtimeAnalytical['score'];
                                } elseif (isset($runtimeAnalytical['percent']) && is_numeric($runtimeAnalytical['percent'])) {
                                    $score = (float) $runtimeAnalytical['percent'];
                                } elseif (isset($sampleAnalytical['score']) && is_numeric($sampleAnalytical['score'])) {
                                    $score = (float) $sampleAnalytical['score'];
                                }
                                if ($score === null) {
                                    $score = isset($props['value']) && is_numeric($props['value']) ? (float) $props['value'] : 0.0;
                                }
                                $score = max(0.0, min(100.0, $score));

                                $answered = isset($runtimeAnalytical['answered']) ? (int) $runtimeAnalytical['answered'] : null;
                                if ($answered === null && isset($sampleAnalytical['answered'])) {
                                    $answered = (int) $sampleAnalytical['answered'];
                                }
                                if ($answered === null && isset($props['answered']) && is_numeric($props['answered'])) {
                                    $answered = (int) $props['answered'];
                                }
                                $answered = max(0, (int) ($answered ?? 0));

                                $correct = isset($runtimeAnalytical['correct']) ? (int) $runtimeAnalytical['correct'] : null;
                                if ($correct === null && isset($sampleAnalytical['correct'])) {
                                    $correct = (int) $sampleAnalytical['correct'];
                                }
                                if ($correct === null && isset($props['correct']) && is_numeric($props['correct'])) {
                                    $correct = (int) $props['correct'];
                                }
                                $correct = max(0, (int) ($correct ?? 0));

                                $incorrect = isset($runtimeAnalytical['incorrect']) ? (int) $runtimeAnalytical['incorrect'] : null;
                                if ($incorrect === null && isset($sampleAnalytical['incorrect'])) {
                                    $incorrect = (int) $sampleAnalytical['incorrect'];
                                }
                                if ($incorrect === null) {
                                    $incorrect = max(0, $answered - $correct);
                                }
                                $incorrect = max(0, (int) $incorrect);

                                $thresholdSource = [];
                                if (isset($runtimeAnalytical['thresholds']) && is_array($runtimeAnalytical['thresholds'])) {
                                    $thresholdSource = $runtimeAnalytical['thresholds'];
                                } elseif (isset($sampleAnalytical['thresholds']) && is_array($sampleAnalytical['thresholds'])) {
                                    $thresholdSource = $sampleAnalytical['thresholds'];
                                }
                                $lowThreshold = isset($props['lowThreshold']) && is_numeric($props['lowThreshold'])
                                    ? (int) $props['lowThreshold']
                                    : (int) ($thresholdSource['low'] ?? 50);
                                $mediumThreshold = isset($props['mediumThreshold']) && is_numeric($props['mediumThreshold'])
                                    ? (int) $props['mediumThreshold']
                                    : (int) ($thresholdSource['medium'] ?? 60);
                                if ($mediumThreshold <= $lowThreshold) {
                                    $mediumThreshold = $lowThreshold + 10;
                                }
                                $lowThreshold = max(0, min(100, $lowThreshold));
                                $mediumThreshold = max(0, min(100, $mediumThreshold));

                                $band = isset($runtimeAnalytical['band']) ? (string) $runtimeAnalytical['band'] : '';
                                if ($band === '') {
                                    if ($score >= $mediumThreshold) {
                                        $band = 'high';
                                    } elseif ($score >= $lowThreshold) {
                                        $band = 'medium';
                                    } else {
                                        $band = 'low';
                                    }
                                }

                                $defaultRangeLabels = ['low' => 'نیاز به توسعه', 'medium' => 'در مسیر رشد', 'high' => 'سطح پیشرفته'];
                                $rangeLabels = $defaultRangeLabels;
                                if (isset($sampleAnalytical['range_labels']) && is_array($sampleAnalytical['range_labels'])) {
                                    $rangeLabels = array_merge($rangeLabels, array_filter($sampleAnalytical['range_labels'], static function ($value) {
                                        return is_string($value) && trim($value) !== '';
                                    }));
                                }
                                if (isset($runtimeAnalytical['range_labels']) && is_array($runtimeAnalytical['range_labels'])) {
                                    $rangeLabels = array_merge($rangeLabels, array_filter($runtimeAnalytical['range_labels'], static function ($value) {
                                        return is_string($value) && trim($value) !== '';
                                    }));
                                }
                                $bandLabel = $rangeLabels[$band] ?? $defaultRangeLabels[$band] ?? 'سطح عملکرد';

                                $analysisTexts = [
                                    'low' => isset($props['lowText']) ? trim((string) $props['lowText']) : '',
                                    'medium' => isset($props['mediumText']) ? trim((string) $props['mediumText']) : '',
                                    'high' => isset($props['highText']) ? trim((string) $props['highText']) : '',
                                ];
                                if (isset($sampleAnalytical['analysis_texts']) && is_array($sampleAnalytical['analysis_texts'])) {
                                    $analysisTexts = array_merge($analysisTexts, array_map('trim', $sampleAnalytical['analysis_texts']));
                                }
                                if (isset($runtimeAnalytical['analysis_texts']) && is_array($runtimeAnalytical['analysis_texts'])) {
                                    $analysisTexts = array_merge($analysisTexts, array_map('trim', $runtimeAnalytical['analysis_texts']));
                                }

                                $analysisText = isset($analysisTexts[$band]) && $analysisTexts[$band] !== ''
                                    ? $analysisTexts[$band]
                                    : (isset($runtimeAnalytical['analysis_text']) ? (string) $runtimeAnalytical['analysis_text'] : '');

                                $percent = $score / 100.0;
                                $percent = max(0.0, min(1.0, $percent));
                                $needleAngle = -90 + ($percent * 180);
                                $arcRadius = 90.0;
                                $arcLength = M_PI * $arcRadius;
                                $dashArray = number_format($arcLength, 4, '.', '');
                                $dashOffset = number_format($arcLength * (1 - $percent), 4, '.', '');

                                $percentLabel = $formatPercentLabel($score);
                                $scoreLabel = UtilityHelper::englishToPersian(number_format($score, 0));
                                $maxLabel = UtilityHelper::englishToPersian(number_format(100, 0));
                                $answeredLabel = UtilityHelper::englishToPersian(number_format($answered, 0));
                                $correctLabel = UtilityHelper::englishToPersian(number_format($correct, 0));
                                $incorrectLabel = UtilityHelper::englishToPersian(number_format($incorrect, 0));
                                $lowThresholdLabel = UtilityHelper::englishToPersian(number_format($lowThreshold, 0));
                                $mediumThresholdLabel = UtilityHelper::englishToPersian(number_format($mediumThreshold, 0));

                                $alignValue = isset($props['alignment']) ? (string) $props['alignment'] : 'center';
                                $alignClass = $alignmentClass($alignValue);
                                $widthClass = $elementWidthClass($props);
                                $outerClassAttr = implode(' ', array_filter(['certificate-element', $alignClass, $widthClass]));
                                $bandClass = 'analytical-band-' . $sanitizeClass($band);

                                static $analyticalInsightStylesPrinted = false;
                                if (!$analyticalInsightStylesPrinted):
                                    $analyticalInsightStylesPrinted = true;
                            ?>
                                <style>
                                    .certificate-analytical-insight {
                                        position: relative;
                                        margin: 0 auto;
                                        border: 1px solid rgba(15, 23, 42, 0.08);
                                        border-radius: 20px;
                                        padding: 24px 28px;
                                        background: #ffffff;
                                        display: flex;
                                        flex-direction: column;
                                        gap: 20px;
                                        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
                                    }
                                    .certificate-analytical-insight::before {
                                        content: '';
                                        position: absolute;
                                        inset: 0;
                                        border-radius: 20px;
                                        pointer-events: none;
                                        background: linear-gradient(135deg, rgba(14, 165, 233, 0.08), rgba(14, 165, 233, 0) 45%);
                                        opacity: 0.9;
                                    }
                                    .certificate-analytical-insight > * { position: relative; z-index: 1; }
                                    .analytical-insight-headline {
                                        font-size: 1.35rem;
                                        font-weight: 700;
                                        color: #0f172a;
                                        display: inline-flex;
                                        gap: 10px;
                                        align-items: center;
                                    }
                                    .analytical-insight-headline::before {
                                        content: '';
                                        width: 22px;
                                        height: 4px;
                                        border-radius: 999px;
                                        background: var(--analytical-accent, #0ea5e9);
                                        display: inline-block;
                                    }
                                    .analytical-insight-body {
                                        display: flex;
                                        flex-wrap: wrap;
                                        gap: 28px;
                                        align-items: stretch;
                                        justify-content: space-between;
                                    }
                                    .analytical-insight-gauge {
                                        flex: 1 1 240px;
                                        max-width: 320px;
                                        display: flex;
                                        flex-direction: column;
                                        align-items: center;
                                        gap: 12px;
                                    }
                                    .analytical-insight-gauge svg {
                                        width: 100%;
                                        height: auto;
                                    }
                                    .analytical-insight-gauge svg .gauge-arc-bg { stroke: #e2e8f0; stroke-width: 14; fill: none; stroke-linecap: round; }
                                    .analytical-insight-gauge svg .gauge-arc-fill { stroke: var(--analytical-accent, #0ea5e9); stroke-width: 14; fill: none; stroke-linecap: round; }
                                    .analytical-insight-gauge svg .gauge-needle { stroke: #0f172a; stroke-width: 3; stroke-linecap: round; }
                                    .analytical-insight-gauge svg .gauge-needle-pivot { fill: #0f172a; }
                                    .analytical-insight-percent {
                                        font-size: 2.6rem;
                                        font-weight: 700;
                                        color: var(--analytical-accent, #0ea5e9);
                                        line-height: 1.05;
                                    }
                                    .analytical-insight-scoreline {
                                        font-weight: 500;
                                        color: #1f2937;
                                        font-size: 0.95rem;
                                    }
                                    .analytical-insight-meta {
                                        flex: 1 1 280px;
                                        display: flex;
                                        flex-direction: column;
                                        gap: 18px;
                                    }
                                    .analytical-insight-summary {
                                        display: flex;
                                        flex-direction: column;
                                        gap: 12px;
                                    }
                                    .analytical-insight-band {
                                        display: inline-flex;
                                        align-items: center;
                                        gap: 8px;
                                        padding: 6px 16px;
                                        border-radius: 999px;
                                        font-weight: 600;
                                        background: rgba(14, 165, 233, 0.12);
                                        color: var(--analytical-accent, #0ea5e9);
                                        width: max-content;
                                    }
                                    .analytical-insight-band::before {
                                        content: '';
                                        width: 10px;
                                        height: 10px;
                                        border-radius: 999px;
                                        background: currentColor;
                                        opacity: 0.9;
                                    }
                                    .analytical-band-low { background: rgba(239, 68, 68, 0.12); color: #dc2626; }
                                    .analytical-band-medium { background: rgba(249, 115, 22, 0.14); color: #c2410c; }
                                    .analytical-band-high { background: rgba(14, 165, 233, 0.12); color: var(--analytical-accent, #0ea5e9); }
                                    .analytical-insight-thresholds {
                                        font-size: 0.85rem;
                                        color: #64748b;
                                    }
                                    .analytical-insight-stats {
                                        display: grid;
                                        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
                                        gap: 12px;
                                    }
                                    .analytical-insight-stat {
                                        background: rgba(15, 23, 42, 0.04);
                                        border-radius: 12px;
                                        padding: 12px 16px;
                                        text-align: center;
                                    }
                                    .analytical-insight-stat-label {
                                        font-size: 0.82rem;
                                        color: #475569;
                                        margin-bottom: 6px;
                                    }
                                    .analytical-insight-stat-value {
                                        font-size: 1.15rem;
                                        font-weight: 600;
                                        color: #1e293b;
                                    }
                                    .analytical-insight-analysis {
                                        border-top: 1px dashed rgba(15, 23, 42, 0.12);
                                        padding-top: 14px;
                                        color: #334155;
                                        line-height: 1.9;
                                    }
                                    .analytical-insight-analysis-title {
                                        font-weight: 600;
                                        color: #0f172a;
                                        margin-bottom: 6px;
                                    }
                                    @media (max-width: 768px) {
                                        .analytical-insight-body { flex-direction: column; align-items: center; }
                                        .analytical-insight-meta { width: 100%; }
                                        .analytical-insight-gauge { max-width: 360px; }
                                    }
                                </style>
                            <?php endif; ?>
                            <div class="<?= $escape($outerClassAttr); ?>"<?= $elementIdAttr; ?>>
                                <div class="certificate-analytical-insight" style="--analytical-accent: <?= $escape($accentColor); ?>;">
                                    <?php if ($showHeadline && $headline !== ''): ?>
                                        <div class="analytical-insight-headline"><?= $escape($headline); ?></div>
                                    <?php endif; ?>
                                    <div class="analytical-insight-body">
                                        <div class="analytical-insight-gauge">
                                            <svg class="analytical-insight-svg" viewBox="0 0 200 110" role="img" aria-label="تفکر تحلیلی">
                                                <path class="gauge-arc-bg" d="M10 100 A90 90 0 0 1 190 100" />
                                                <path class="gauge-arc-fill" d="M10 100 A90 90 0 0 1 190 100" style="stroke-dasharray: <?= $dashArray; ?>; stroke-dashoffset: <?= $dashOffset; ?>;" />
                                                <line class="gauge-needle" x1="100" y1="100" x2="100" y2="30" style="transform: rotate(<?= $escape(number_format($needleAngle, 2, '.', '')); ?>deg); transform-origin: 100px 100px;" />
                                                <circle class="gauge-needle-pivot" cx="100" cy="100" r="6" />
                                            </svg>
                                            <div class="analytical-insight-percent"><?= $escape($percentLabel); ?></div>
                                            <div class="analytical-insight-scoreline">امتیاز کل: <?= $escape($scoreLabel); ?> از <?= $escape($maxLabel); ?></div>
                                        </div>
                                        <div class="analytical-insight-meta">
                                            <?php if ($showDetails): ?>
                                                <div class="analytical-insight-summary">
                                                    <div class="analytical-insight-band <?= $escape($bandClass); ?>"><?= $escape($bandLabel); ?></div>
                                                    <div class="analytical-insight-thresholds">آستانه رشد: <?= $escape($lowThresholdLabel); ?> | آستانه پیشرفته: <?= $escape($mediumThresholdLabel); ?></div>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($showBreakdown): ?>
                                                <div class="analytical-insight-stats">
                                                    <div class="analytical-insight-stat">
                                                        <div class="analytical-insight-stat-label">پاسخ داده شده</div>
                                                        <div class="analytical-insight-stat-value"><?= $escape($answeredLabel); ?></div>
                                                    </div>
                                                    <div class="analytical-insight-stat">
                                                        <div class="analytical-insight-stat-label">پاسخ صحیح</div>
                                                        <div class="analytical-insight-stat-value"><?= $escape($correctLabel); ?></div>
                                                    </div>
                                                    <div class="analytical-insight-stat">
                                                        <div class="analytical-insight-stat-label">پاسخ نادرست</div>
                                                        <div class="analytical-insight-stat-value"><?= $escape($incorrectLabel); ?></div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($showAnalysisText && $analysisText !== ''): ?>
                                                <div class="analytical-insight-analysis">
                                                    <?php if ($analysisHeadline !== ''): ?>
                                                        <div class="analytical-insight-analysis-title"><?= $escape($analysisHeadline); ?></div>
                                                    <?php endif; ?>
                                                    <?= nl2br($escape($analysisText)); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <?php elseif ($type === 'custom_paragraph'): ?>
                            <?php
                                $text = isset($props['text']) ? (string) $props['text'] : '';
                                $align = is_string($props['align'] ?? null) ? $props['align'] : 'right';
                                $alignClass = $alignmentClass($align);
                                $color = $sanitizeColor($props['color'] ?? '#334155', '#334155');
                                $lineHeight = isset($props['lineHeight']) ? (float) $props['lineHeight'] : 1.8;
                                if (!is_finite($lineHeight) || $lineHeight < 1.1) {
                                    $lineHeight = 1.1;
                                } elseif ($lineHeight > 2.6) {
                                    $lineHeight = 2.6;
                                }
                                $widthClass = $elementWidthClass($props);
                                $outerClassAttr = implode(' ', array_filter(['certificate-element', 'certificate-custom-paragraph', $alignClass, $widthClass]));
                                $styleAttr = 'color:' . $color . ';line-height:' . number_format($lineHeight, 2, '.', '') . ';';
                            ?>
                            <?php
                                $renderText = $text !== '' ? rtrim($text, "\r\n") : '';
                                if ($renderText !== '') {
                                    $renderText = ltrim($renderText, "\r\n");
                                }
                                if ($renderText === '') {
                                    $renderText = 'متن نمونه پاراگراف';
                                }
                            ?>
                            <div class="<?= $escape($outerClassAttr); ?>"<?= $elementIdAttr; ?> style="<?= $escape($styleAttr); ?>">
                                <?= nl2br($escape($renderText)); ?>
                            </div>

                        <?php elseif ($type === 'custom_image'): ?>
                            <?php
                                $mode = $props['mode'] ?? 'dynamic';
                                if (!in_array($mode, ['dynamic', 'static'], true)) {
                                    $mode = 'dynamic';
                                }
                                $staticUrl = $sanitizeUrl($props['staticUrl'] ?? '');
                                $width = isset($props['width']) ? $sanitizeDimension((string) $props['width']) : null;
                                $borderRadius = isset($props['borderRadius']) ? $sanitizeDimension((string) $props['borderRadius']) : null;
                                $styleParts = [];
                                if ($width !== null) {
                                    $styleParts[] = 'max-width:' . $width;
                                }
                                if ($borderRadius !== null) {
                                    $styleParts[] = 'border-radius:' . $borderRadius;
                                }
                                $styleAttr = !empty($styleParts) ? implode(';', $styleParts) : '';
                                $altText = isset($props['altText']) ? (string) $props['altText'] : 'تصویر گواهی';
                                $alignValue = isset($props['align']) ? (string) $props['align'] : 'center';
                                $alignClass = $alignmentClass($alignValue);
                                $widthClass = $elementWidthClass($props);
                                $outerClassAttr = implode(' ', array_filter(['certificate-element', $alignClass, $widthClass]));
                                $dynamicSourceRaw = isset($props['dynamicSource']) && is_string($props['dynamicSource']) ? $props['dynamicSource'] : 'evaluation_cover';
                                $dynamicSource = $sanitizeClass($dynamicSourceRaw);
                                $allowedDynamicSources = ['evaluation_cover', 'organization_logo', 'participant_avatar', 'competency_model'];
                                if (!in_array($dynamicSource, $allowedDynamicSources, true)) {
                                    $dynamicSource = 'evaluation_cover';
                                }
                                $dynamicLabels = [
                                    'evaluation_cover' => 'تصویر کاور ارزیابی',
                                    'organization_logo' => 'لوگوی سازمان',
                                    'participant_avatar' => 'تصویر ارزیاب‌شونده',
                                    'competency_model' => 'تصویر مدل شایستگی',
                                ];
                                $dynamicLookupKeys = [
                                    'evaluation_cover' => ['evaluation_cover_image_url', 'evaluation_cover_image_path'],
                                    'organization_logo' => ['organization_logo_url', 'organization_logo_path'],
                                    'participant_avatar' => ['participant_avatar_url', 'participant_avatar_path'],
                                    'competency_model' => ['competency_model_image_url', 'competency_model_image_path'],
                                ];
                                $dynamicUrl = '';
                                if ($mode === 'dynamic') {
                                    $candidateKeys = $dynamicLookupKeys[$dynamicSource] ?? [];
                                    foreach ($candidateKeys as $candidateKey) {
                                        if (!isset($sampleData[$candidateKey])) {
                                            continue;
                                        }
                                        $candidateValue = $resolveAssetUrl((string) $sampleData[$candidateKey]);
                                        if ($candidateValue !== '') {
                                            $dynamicUrl = $candidateValue;
                                            break;
                                        }
                                    }
                                    if ($dynamicUrl === '' && $dynamicSource === 'competency_model' && isset($competencyModelImagePath)) {
                                        $dynamicUrl = $resolveAssetUrl((string) $competencyModelImagePath);
                                    }
                                }
                                $imageUrl = '';
                                $hasImage = false;
                                if ($mode === 'static' && $staticUrl !== '') {
                                    $imageUrl = $staticUrl;
                                    $hasImage = true;
                                } elseif ($mode === 'dynamic' && $dynamicUrl !== '') {
                                    $imageUrl = $dynamicUrl;
                                    $hasImage = true;
                                    if ($altText === '' || $altText === 'تصویر گواهی') {
                                        $altText = $dynamicLabels[$dynamicSource] ?? 'تصویر پویا';
                                    }
                                }
                                $imageContainerClass = 'certificate-custom-image' . ($hasImage ? ' has-image' : '');
                                $placeholderMessage = $mode === 'dynamic'
                                    ? ('در خروجی نهایی، ' . ($dynamicLabels[$dynamicSource] ?? 'تصویر پویا') . ' نمایش داده می‌شود.')
                                    : 'برای نمایش، آدرس یا فایل تصویر ثابت را تنظیم کنید.';
                            ?>
                            <div class="<?= $escape($outerClassAttr); ?>">
                                <div class="<?= $escape($imageContainerClass); ?>" style="<?= $escape($styleAttr); ?>">
                                    <?php if ($hasImage): ?>
                                        <img src="<?= $escape($imageUrl); ?>" alt="<?= $escape($altText); ?>">
                                    <?php else: ?>
                                        <div class="placeholder">
                                            <ion-icon name="images-outline" style="font-size: 36px;"></ion-icon>
                                            <div class="mt-2"><?= $escape($placeholderMessage); ?></div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                        <?php elseif ($type === 'logo_display'): ?>
                            <?php
                                $source = $props['source'] ?? 'organization';
                                if (!in_array($source, ['organization', 'system'], true)) {
                                    $source = 'organization';
                                }
                                $alignClass = $alignmentClass($props['align'] ?? 'center');
                                $size = $props['size'] ?? 'medium';
                                $allowedSizes = ['small', 'medium', 'large'];
                                if (!in_array($size, $allowedSizes, true)) {
                                    $size = 'medium';
                                }
                                $sizePercent = isset($props['sizePercent']) ? (int) $props['sizePercent'] : 0;
                                if (!is_int($sizePercent)) { $sizePercent = (int) $sizePercent; }
                                if ($sizePercent < 0) { $sizePercent = 0; }
                                if ($sizePercent > 100) { $sizePercent = 100; }
                                $showBorder = !empty($props['showBorder']);
                                $showLabel = !empty($props['showLabel']);
                                $customLabel = isset($props['customLabel']) ? trim((string) $props['customLabel']) : '';
                                $defaultLabel = $source === 'system' ? 'لوگوی سامانه' : 'لوگوی سازمان';
                                $labelText = $customLabel !== '' ? $customLabel : $defaultLabel;
                                $widthClass = $elementWidthClass($props);
                                $elementClassParts = ['certificate-element', $alignClass, $widthClass];
                                $elementClassAttr = implode(' ', array_filter($elementClassParts));
                                $logoUrl = '';
                                if ($source === 'system') {
                                    $logoUrl = isset($sampleData['system_logo_url']) ? (string) $sampleData['system_logo_url'] : '';
                                } else {
                                    if (isset($organization['logo_url']) && is_string($organization['logo_url']) && $organization['logo_url'] !== '') {
                                        $logoUrl = (string) $organization['logo_url'];
                                    } else {
                                        $logoUrl = isset($sampleData['organization_logo_url']) ? (string) $sampleData['organization_logo_url'] : '';
                                    }
                                }
                            ?>
                            <div class="<?= $escape($elementClassAttr); ?>">
                                <?php if ($showLabel): ?>
                                    <div class="certificate-logo-label text-muted small mb-2"><?= $escape($labelText); ?></div>
                                <?php endif; ?>
                                <?php
                                    $logoStyleAttr = '';
                                    if ($sizePercent > 0) {
                                        $clamped = max(5, min(100, $sizePercent));
                                        $logoStyleAttr = 'style="width: ' . $escape((string)$clamped) . '%;"';
                                    }
                                ?>
                                <div class="certificate-logo-display size-<?= $escape($size); ?><?= $showBorder ? ' with-border' : ''; ?>" <?= $logoStyleAttr; ?>>
                                    <?php if ($logoUrl !== ''): ?>
                                        <img src="<?= $escape($logoUrl); ?>" alt="<?= $escape($labelText); ?>">
                                    <?php else: ?>
                                        <div class="certificate-logo-placeholder">
                                            <ion-icon name="image-outline"></ion-icon>
                                            <span><?= $escape($defaultLabel); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                        <?php elseif ($type === 'signature_block'): ?>
                            <?php
                                $showFirst = !empty($props['showFirstSigner']);
                                $showSecond = !empty($props['showSecondSigner']);
                                $showSeal = !empty($props['showSeal']);
                                $headline = isset($props['headline']) ? trim((string) $props['headline']) : '';
                                $firstLabel = isset($props['firstSignerLabel']) ? trim((string) $props['firstSignerLabel']) : 'نماینده اول';
                                $secondLabel = isset($props['secondSignerLabel']) ? trim((string) $props['secondSignerLabel']) : 'نماینده دوم';
                                $sealLabel = isset($props['sealLabel']) ? trim((string) $props['sealLabel']) : 'مهر سازمان';
                                $firstSigner = $signers[0] ?? [];
                                $secondSigner = $signers[1] ?? [];
                                $widthClass = $elementWidthClass($props);
                                $outerClassAttr = implode(' ', array_filter(['certificate-element', $widthClass]));
                            ?>
                            <div class="<?= $escape($outerClassAttr); ?>">
                                <?php if ($headline !== ''): ?>
                                    <div class="certificate-signature-headline"><?= $escape($headline); ?></div>
                                <?php endif; ?>
                                <div class="certificate-signature-block">
                                    <?php if ($showFirst): ?>
                                        <div class="certificate-signature">
                                            <div class="signer-label text-muted small"><?= $escape($firstLabel); ?></div>
                                            <div class="signer-name"><?= $escape($firstSigner['name'] ?? ''); ?></div>
                                            <?php if (!empty($firstSigner['title'])): ?>
                                                <div class="signer-title"><?= $escape($firstSigner['title']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($showSecond): ?>
                                        <div class="certificate-signature">
                                            <div class="signer-label text-muted small"><?= $escape($secondLabel); ?></div>
                                            <div class="signer-name"><?= $escape($secondSigner['name'] ?? ''); ?></div>
                                            <?php if (!empty($secondSigner['title'])): ?>
                                                <div class="signer-title"><?= $escape($secondSigner['title']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($showSeal): ?>
                                        <div class="certificate-signature seal">
                                            <div class="signer-label text-muted small"><?= $escape($sealLabel); ?></div>
                                            <div class="seal-placeholder">نمونه مهر</div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                        <?php elseif ($type === 'divider'): ?>
                            <?php
                                $color = $sanitizeColor($props['color'] ?? 'rgba(15, 23, 42, 0.16)', 'rgba(15, 23, 42, 0.16)');
                                $thickness = isset($props['thickness']) ? (int) $props['thickness'] : 2;
                                $thickness = max(1, min(12, $thickness));
                                $lineStyle = $sanitizeLineStyle($props['style'] ?? 'solid');
                                $dividerStyle = sprintf('background:%s;height:%dpx;border-bottom:%dpx %s %s;', $color, $thickness, $thickness, $lineStyle, $color);
                                $widthClass = $elementWidthClass($props);
                                $outerClassAttr = implode(' ', array_filter(['certificate-element', $widthClass]));
                            ?>
                            <div class="<?= $escape($outerClassAttr); ?>">
                                <hr class="certificate-divider" style="<?= $escape($dividerStyle); ?>">
                            </div>

                        <?php elseif ($type === 'spacer'): ?>
                            <?php
                                $height = isset($props['height']) ? (int) $props['height'] : 32;
                                $height = max(8, min(240, $height));
                                $widthClass = $elementWidthClass($props);
                                $outerClassAttr = implode(' ', array_filter(['certificate-element', 'certificate-spacer', $widthClass]));
                            ?>
                            <div class="<?= $escape($outerClassAttr); ?>" style="height: <?= $height; ?>px;"></div>

                        <?php elseif ($type === 'chart_placeholder'): ?>
                            <?php
                                $chartType = $props['chartType'] ?? 'radar';
                                if (!in_array($chartType, ['radar', 'bar', 'line'], true)) {
                                    $chartType = 'radar';
                                }
                                $showLegend = !empty($props['showLegend']);
                                $widthClass = $elementWidthClass($props);
                                $outerClassAttr = implode(' ', array_filter(['certificate-element', $widthClass]));
                            ?>
                            <div class="<?= $escape($outerClassAttr); ?>">
                                <div class="certificate-chart-placeholder">
                                    <ion-icon name="stats-chart-outline" style="font-size: 40px;"></ion-icon>
                                    <div class="mt-2">محل قرارگیری نمودار <?= $escape($chartType); ?></div>
                                    <?php if ($showLegend): ?>
                                        <div class="text-muted small mt-2">راهنمای نمودار در خروجی نهایی نمایش داده می‌شود.</div>
                                    <?php endif; ?>
                                </div>
                            </div>

                        <?php elseif ($type === 'washup_final_result'): ?>
                            <?php
                                // Get dataset from runtime or sample
                                $washupDataset = [];
                                if (isset($runtimeDatasets['washup_agreed']) && is_array($runtimeDatasets['washup_agreed']) && !empty($runtimeDatasets['washup_agreed'])) {
                                    $washupDataset = $runtimeDatasets['washup_agreed'];
                                } elseif (isset($sampleData['washup_agreed']) && is_array($sampleData['washup_agreed'])) {
                                    $washupDataset = $sampleData['washup_agreed'];
                                }

                                $componentTitle = isset($props['title']) ? trim((string) $props['title']) : 'نتیجه نهایی و جمع‌بندی';
                                $showSummary = $isTruthy($props['showSummary'] ?? 1);
                                $showRecommendations = $isTruthy($props['showRecommendations'] ?? 1);
                                $showDevelopmentSuggestions = $isTruthy($props['showDevelopmentSuggestions'] ?? 1);
                                $showNextSteps = $isTruthy($props['showNextSteps'] ?? 1);
                                $appearance = isset($props['appearance']) && is_string($props['appearance']) ? strtolower(trim($props['appearance'])) : 'card';
                                if (!in_array($appearance, ['card', 'boxed', 'minimal'], true)) {
                                    $appearance = 'card';
                                }
                                $widthClass = $elementWidthClass($props);
                                $outerClassAttr = implode(' ', array_filter(['certificate-element', $widthClass]));
                                
                                // Get actual data from database
                                $summaryText = '';
                                $recommendationText = '';
                                $developmentText = '';
                                
                                // Use actual data if available
                                if (isset($washupDataset['recommendation_text'])) {
                                    $recommendationText = trim((string) $washupDataset['recommendation_text']);
                                }
                                if (isset($washupDataset['development_text'])) {
                                    $developmentText = trim((string) $washupDataset['development_text']);
                                }
                                
                                // Build summary from washup items
                                if (!empty($washupDataset['items'])) {
                                    $itemCount = count($washupDataset['items']);
                                    $avgScore = 0;
                                    foreach ($washupDataset['items'] as $item) {
                                        $avgScore += (float) ($item['agreed_score'] ?? 0);
                                    }
                                    $avgScore = $itemCount > 0 ? $avgScore / $itemCount : 0;
                                    $avgScoreFormatted = rtrim(rtrim(number_format($avgScore, 2, '.', ''), '0'), '.');
                                    
                                    $summaryText = sprintf(
                                        'در این ارزیابی، تعداد %s شایستگی مورد بررسی قرار گرفت. میانگین امتیاز توافقی %s می‌باشد.',
                                        UtilityHelper::englishToPersian((string) $itemCount),
                                        UtilityHelper::englishToPersian($avgScoreFormatted)
                                    );
                                }
                                
                                // Parse recommendations and development text into arrays
                                $recommendations = [];
                                if ($recommendationText !== '') {
                                    // Split by newlines and filter empty lines
                                    $lines = explode("\n", $recommendationText);
                                    foreach ($lines as $line) {
                                        $line = trim($line);
                                        // Remove bullet points if present
                                        $line = preg_replace('/^[-•\*]\s*/', '', $line);
                                        if ($line !== '') {
                                            $recommendations[] = $line;
                                        }
                                    }
                                }
                                
                                $developmentSuggestions = [];
                                if ($developmentText !== '') {
                                    $lines = explode("\n", $developmentText);
                                    foreach ($lines as $line) {
                                        $line = trim($line);
                                        $line = preg_replace('/^[-•\*]\s*/', '', $line);
                                        if ($line !== '') {
                                            $developmentSuggestions[] = $line;
                                        }
                                    }
                                }
                                
                                // Next steps can be added later as a separate field in the database
                                $nextSteps = [];
                            ?>
                            <div class="<?= $escape($outerClassAttr); ?>">
                                <div class="certificate-washup-result certificate-washup-result--<?= $escape($appearance); ?>">
                                    <?php if ($componentTitle !== ''): ?>
                                        <h3 class="washup-result-title"><?= $escape($componentTitle); ?></h3>
                                    <?php endif; ?>
                                    
                                    <?php if ($showSummary && !empty($summaryText)): ?>
                                        <div class="washup-result-section washup-result-summary">
                                            <div class="washup-section-icon">
                                                <ion-icon name="document-text-outline"></ion-icon>
                                            </div>
                                            <div class="washup-section-content">
                                                <h4 class="washup-section-title">جمع‌بندی کلی</h4>
                                                <p class="washup-section-text"><?= $escape($summaryText); ?></p>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($showRecommendations && !empty($recommendations)): ?>
                                        <div class="washup-result-section washup-result-recommendations">
                                            <div class="washup-section-icon">
                                                <ion-icon name="bulb-outline"></ion-icon>
                                            </div>
                                            <div class="washup-section-content">
                                                <h4 class="washup-section-title">توصیه‌های بهبود</h4>
                                                <ul class="washup-section-list">
                                                    <?php foreach ($recommendations as $recommendation): ?>
                                                        <?php if (is_string($recommendation) && trim($recommendation) !== ''): ?>
                                                            <li><?= $escape(trim($recommendation)); ?></li>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($showDevelopmentSuggestions && !empty($developmentSuggestions)): ?>
                                        <div class="washup-result-section washup-result-development">
                                            <div class="washup-section-icon">
                                                <ion-icon name="trending-up-outline"></ion-icon>
                                            </div>
                                            <div class="washup-section-content">
                                                <h4 class="washup-section-title">پیشنهادات توسعه</h4>
                                                <ul class="washup-section-list">
                                                    <?php foreach ($developmentSuggestions as $suggestion): ?>
                                                        <?php if (is_string($suggestion) && trim($suggestion) !== ''): ?>
                                                            <li><?= $escape(trim($suggestion)); ?></li>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($showNextSteps && !empty($nextSteps)): ?>
                                        <div class="washup-result-section washup-result-next-steps">
                                            <div class="washup-section-icon">
                                                <ion-icon name="arrow-forward-circle-outline"></ion-icon>
                                            </div>
                                            <div class="washup-section-content">
                                                <h4 class="washup-section-title">گام‌های بعدی</h4>
                                                <ul class="washup-section-list">
                                                    <?php foreach ($nextSteps as $step): ?>
                                                        <?php if (is_string($step) && trim($step) !== ''): ?>
                                                            <li><?= $escape(trim($step)); ?></li>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (empty($summaryText) && empty($recommendations) && empty($developmentSuggestions) && empty($nextSteps)): ?>
                                        <div class="text-center text-muted py-4">
                                            <ion-icon name="information-circle-outline" style="font-size: 48px; opacity: 0.5;"></ion-icon>
                                            <p class="mt-3 mb-0">هنوز نتیجه نهایی و توصیه‌ها برای این ارزیابی ثبت نشده است.</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                        <?php elseif ($type === 'washup_agreed_competencies'): ?>
                            <?php
                                // Resolve dataset: prefer runtime, then sample
                                $washupDataset = [];
                                if (isset($runtimeDatasets['washup_agreed']) && is_array($runtimeDatasets['washup_agreed']) && !empty($runtimeDatasets['washup_agreed'])) {
                                    $washupDataset = $runtimeDatasets['washup_agreed'];
                                } elseif (isset($sampleData['washup_agreed']) && is_array($sampleData['washup_agreed'])) {
                                    $washupDataset = $sampleData['washup_agreed'];
                                }

                                $items = [];
                                if (isset($washupDataset['items']) && is_array($washupDataset['items'])) {
                                    $items = $washupDataset['items'];
                                }

                                $showHeadline = $isTruthy($props['showHeadline'] ?? 1);
                                $headlineProp = isset($props['headline']) ? trim((string) $props['headline']) : '';
                                $headline = $headlineProp !== '' ? $headlineProp : (isset($washupDataset['headline']) ? trim((string) $washupDataset['headline']) : '');

                                $showSummary = $isTruthy($props['showSummary'] ?? 1);
                                $summaryProp = isset($props['summary']) ? trim((string) $props['summary']) : '';
                                $summaryText = $summaryProp !== '' ? $summaryProp : (isset($washupDataset['summary']) ? trim((string) $washupDataset['summary']) : '');

                                $layoutRaw = isset($props['layout']) && is_string($props['layout']) ? strtolower(trim($props['layout'])) : 'cards';
                                $allowedLayouts = ['cards', 'list', 'table', 'grouped_by_tool'];
                                $layout = in_array($layoutRaw, $allowedLayouts, true) ? $layoutRaw : 'cards';

                                $maxItems = isset($props['maxItems']) ? (int) $props['maxItems'] : 6;
                                $maxItems = $maxItems > 0 ? min($maxItems, 50) : 6;

                                $showScore = $isTruthy($props['showScore'] ?? 1);
                                $showGauges = $isTruthy($props['showGauges'] ?? 1);
                                $gaugeMaxProp = isset($props['gaugeMax']) ? (float) $props['gaugeMax'] : 0.0;
                                $gaugeMax = $gaugeMaxProp > 0 ? $gaugeMaxProp : 0.0; // if 0, will auto-detect
                                $gaugeSizePx = isset($props['gaugeSize']) ? (int) $props['gaugeSize'] : 42;
                                $showDimension = $isTruthy($props['showDimension'] ?? 1);
                                $showCompetencyCode = $isTruthy($props['showCompetencyCode'] ?? 0);
                                $showCompetencyNature = $isTruthy($props['showCompetencyNature'] ?? 1);
                                $showTools = $isTruthy($props['showTools'] ?? 1);
                                $showUpdatedInfo = $isTruthy($props['showUpdatedInfo'] ?? 1);
                                $showFooter = $isTruthy($props['showFooter'] ?? 0);
                                $footerNoteProp = isset($props['footerNote']) ? trim((string) $props['footerNote']) : '';
                                $footerNote = $footerNoteProp !== '' ? $footerNoteProp : (isset($washupDataset['footer_note']) ? trim((string) $washupDataset['footer_note']) : '');
                                $maxPerTool = isset($props['maxPerTool']) ? (int) $props['maxPerTool'] : 0; // 0 = no limit per tool
                                $eachToolOnNewPage = $isTruthy($props['eachToolOnNewPage'] ?? 0);
                                // Example notes table controls
                                $showExampleNotes = $isTruthy($props['showExampleNotes'] ?? 1);
                                $exampleNotesTitle = isset($props['exampleNotesTitle']) ? trim((string) $props['exampleNotesTitle']) : 'مصداق‌ها و توضیحات ارزیاب';
                                $exampleNotesShowEvaluator = $isTruthy($props['exampleNotesShowEvaluator'] ?? 1);
                                // Grouped-by-tool presentation controls
                                $toolHeaderAlignRaw = isset($props['toolHeaderAlign']) && is_string($props['toolHeaderAlign']) ? strtolower(trim($props['toolHeaderAlign'])) : '';
                                $toolHeaderSizeRaw = isset($props['toolHeaderSize']) && is_string($props['toolHeaderSize']) ? strtolower(trim($props['toolHeaderSize'])) : '';
                                $scoreInline = $isTruthy($props['scoreInline'] ?? 1);
                                $scoreOrderRaw = isset($props['scoreOrder']) && is_string($props['scoreOrder']) ? strtolower(trim($props['scoreOrder'])) : 'score_first';
                                // Grouped-by-tool cards/list options
                                $groupedItemAppearanceRaw = isset($props['groupedItemAppearance']) && is_string($props['groupedItemAppearance']) ? strtolower(trim($props['groupedItemAppearance'])) : 'cards';
                                $groupedItemAppearance = in_array($groupedItemAppearanceRaw, ['cards','list'], true) ? $groupedItemAppearanceRaw : 'cards';
                                $groupedCardsPerRow = isset($props['groupedCardsPerRow']) ? (int) $props['groupedCardsPerRow'] : 2;
                                if ($groupedCardsPerRow < 1) { $groupedCardsPerRow = 1; } elseif ($groupedCardsPerRow > 4) { $groupedCardsPerRow = 4; }
                                $groupedCardBorder = $isTruthy($props['groupedCardBorder'] ?? 1);
                                $groupedCardShadow = $isTruthy($props['groupedCardShadow'] ?? 0);
                                $groupedCardInnerLayoutRaw = isset($props['groupedCardInnerLayout']) && is_string($props['groupedCardInnerLayout']) ? strtolower(trim($props['groupedCardInnerLayout'])) : 'vertical';
                                $groupedCardInnerLayout = in_array($groupedCardInnerLayoutRaw, ['vertical','horizontal'], true) ? $groupedCardInnerLayoutRaw : 'vertical';

                                $emptyMessageProp = isset($props['emptyMessage']) ? trim((string) $props['emptyMessage']) : '';
                                $emptyMessage = $emptyMessageProp !== '' ? $emptyMessageProp : (isset($washupDataset['empty_message']) ? trim((string) $washupDataset['empty_message']) : 'برای این ارزیابی هنوز امتیاز توافقی ثبت نشده است.');

                                $accentColor = $sanitizeColor($props['accentColor'] ?? ($washupDataset['accentColor'] ?? '#2563eb'), '#2563eb');
                                $alignClass = $alignmentClass($props['alignment'] ?? 'right');
                                $widthClass = $elementWidthClass($props);
                                $outerClassAttr = implode(' ', array_filter(['certificate-element', $alignClass, $widthClass]));

                                $itemsToRender = array_slice($items, 0, $maxItems);
                                $itemsForGrouping = $items; // For grouped_by_tool we need full set, not globally truncated
                                $hasItems = !empty($itemsToRender);
                            ?>
                            <div class="<?= $escape($outerClassAttr); ?>"<?= $elementIdAttr; ?>>
                                <div class="certificate-washup" style="--washup-accent: <?= $escape($accentColor); ?>;" dir="rtl">
                                    <?php if ($showHeadline && $headline !== ''): ?>
                                        <div class="certificate-washup-headline"><?= $escape($headline); ?></div>
                                    <?php endif; ?>
                                    <?php if ($showSummary && $summaryText !== ''): ?>
                                        <div class="certificate-washup-summary text-muted"><?= nl2br($escape($summaryText)); ?></div>
                                    <?php endif; ?>

                                    <?php if ($hasItems): ?>
                                        <?php
                                            // Mini gauge renderer for inline usage
                                            $renderMiniGauge = static function ($score, $max, string $accent, int $sizePx) use ($escape): string {
                                                $value = is_numeric($score) ? (float) $score : 0.0;
                                                $maxVal = is_numeric($max) && $max > 0 ? (float) $max : 100.0;
                                                if ($maxVal <= 0) { $maxVal = 100.0; }
                                                $pct = max(0.0, min(1.0, $value / $maxVal));
                                                // Geometry for a small semi-circle gauge
                                                $radius = 20.0; // in viewBox units
                                                $arcLength = M_PI * $radius; // half circle
                                                $dashArray = number_format($arcLength, 4, '.', '');
                                                $dashOffset = number_format($arcLength * (1 - $pct), 4, '.', '');
                                                $width = max(24, min(120, $sizePx));
                                                $strokeWidth = 3;
                                                // Inline styles to avoid external CSS dependency
                                                $svg = '<svg viewBox="0 0 48 28" width="' . (int) $width . '" height="' . (int) round($width * (28/48)) . '" aria-hidden="true" class="washup-mini-gauge" style="display:inline-block; vertical-align:middle;">
                                                    <path d="M4 24 A20 20 0 0 1 44 24" fill="none" stroke="#e5e7eb" stroke-width="' . $strokeWidth . '" stroke-linecap="round" />
                                                    <path d="M4 24 A20 20 0 0 1 44 24" fill="none" stroke="' . $escape($accent) . '" stroke-width="' . $strokeWidth . '" stroke-linecap="round" style="stroke-dasharray: ' . $dashArray . '; stroke-dashoffset: ' . $dashOffset . ';" />
                                                </svg>';
                                                return $svg;
                                            };
                                        ?>
                                        <?php if ($layout === 'cards'): ?>
                                            <div class="certificate-washup-cards">
                                                <?php foreach ($itemsToRender as $it): ?>
                                                    <?php
                                                        $title = isset($it["competency_title"]) ? trim((string) $it["competency_title"]) : '';
                                                        $code = isset($it["competency_code"]) ? trim((string) $it["competency_code"]) : '';
                                                        $dimension = isset($it["dimension"]) ? trim((string) $it["dimension"]) : '';
                                                        $scoreLabel = isset($it["score_label"]) ? trim((string) $it["score_label"]) : '';
                                                        $tools = isset($it['tools']) && is_array($it['tools']) ? $it['tools'] : [];
                                                        $toolsLabel = !empty($tools) ? implode('، ', array_slice(array_map('strval', $tools), 0, 6)) : '';
                                                        $updatedBy = isset($it['updated_by']) ? trim((string) $it['updated_by']) : '';
                                                        $updatedAt = isset($it['updated_at_display']) ? trim((string) $it['updated_at_display']) : '';
                                                    ?>
                                                    <div class="certificate-washup-card" style="border-color: <?= $escape($accentColor); ?>;">
                                                        <div class="washup-card-header">
                                                            <div class="washup-card-title"><?= $escape($title !== '' ? $title : 'شایستگی'); ?></div>
                                                            <?php if ($showScore && $scoreLabel !== ''): ?>
                                                                <span class="washup-card-score" style="color: <?= $escape($accentColor); ?>;">
                                                                    <ion-icon name="ribbon-outline"></ion-icon>
                                                                    <?= $escape($scoreLabel); ?>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="washup-card-meta">
                                                            <?php if ($showDimension && $dimension !== ''): ?>
                                                                <span class="meta-pill"><ion-icon name="layer-outline"></ion-icon><?= $escape($dimension); ?></span>
                                                            <?php endif; ?>
                                                            <?php if ($showCompetencyCode && $code !== ''): ?>
                                                                <span class="meta-pill"><ion-icon name="barcode-outline"></ion-icon><?= $escape($code); ?></span>
                                                            <?php endif; ?>
                                                            <?php if ($showTools && $toolsLabel !== ''): ?>
                                                                <span class="meta-pill"><ion-icon name="flask-outline"></ion-icon><?= $escape($toolsLabel); ?></span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <?php if ($showUpdatedInfo && ($updatedBy !== '' || $updatedAt !== '')): ?>
                                                            <div class="washup-card-footnote text-muted small">
                                                                <?php if ($updatedBy !== ''): ?>
                                                                    <span><ion-icon name="person-outline"></ion-icon><?= $escape($updatedBy); ?></span>
                                                                <?php endif; ?>
                                                                <?php if ($updatedAt !== ''): ?>
                                                                    <span><ion-icon name="time-outline"></ion-icon><?= $escape($updatedAt); ?></span>
                                                                <?php endif; ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php elseif ($layout === 'list'): ?>
                                            <ul class="certificate-washup-list">
                                                <?php foreach ($itemsToRender as $it): ?>
                                                    <?php
                                                        $title = isset($it["competency_title"]) ? trim((string) $it["competency_title"]) : '';
                                                        $code = isset($it["competency_code"]) ? trim((string) $it["competency_code"]) : '';
                                                        $dimension = isset($it["dimension"]) ? trim((string) $it["dimension"]) : '';
                                                        $scoreLabel = isset($it["score_label"]) ? trim((string) $it["score_label"]) : '';
                                                        $tools = isset($it['tools']) && is_array($it['tools']) ? $it['tools'] : [];
                                                        $updatedBy = isset($it['updated_by']) ? trim((string) $it['updated_by']) : '';
                                                        $updatedAt = isset($it['updated_at_display']) ? trim((string) $it['updated_at_display']) : '';
                                                    ?>
                                                    <li>
                                                        <div class="washup-list-row">
                                                            <div class="washup-list-main">
                                                                <span class="title"><?= $escape($title !== '' ? $title : 'شایستگی'); ?></span>
                                                                <?php if ($showDimension && $dimension !== ''): ?><span class="sep">•</span><span class="dim"><?= $escape($dimension); ?></span><?php endif; ?>
                                                                <?php if ($showCompetencyCode && $code !== ''): ?><span class="sep">•</span><span class="code"><?= $escape($code); ?></span><?php endif; ?>
                                                            </div>
                                                            <div class="washup-list-side">
                                                                <?php if ($showScore && $scoreLabel !== ''): ?>
                                                                    <span class="score" style="color: <?= $escape($accentColor); ?>;">
                                                                        <?= $escape($scoreLabel); ?>
                                                                    </span>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                        <?php if ($showTools && !empty($tools)): ?>
                                                            <div class="washup-list-tools text-muted small">
                                                                <ion-icon name="flask-outline"></ion-icon>
                                                                <?= $escape(implode('، ', array_slice(array_map('strval', $tools), 0, 8))); ?>
                                                            </div>
                                                        <?php endif; ?>
                                                        <?php if ($showUpdatedInfo && ($updatedBy !== '' || $updatedAt !== '')): ?>
                                                            <div class="washup-list-footnote text-muted small">
                                                                <?php if ($updatedBy !== ''): ?><span><ion-icon name="person-outline"></ion-icon><?= $escape($updatedBy); ?></span><?php endif; ?>
                                                                <?php if ($updatedAt !== ''): ?><span><ion-icon name="time-outline"></ion-icon><?= $escape($updatedAt); ?></span><?php endif; ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php elseif ($layout === 'grouped_by_tool'): ?>
                                            <?php
                                                // Build tool groups based on tools listed per competency item
                                                $groupOrder = [];
                                                if (isset($washupDataset['tool_names']) && is_array($washupDataset['tool_names'])) {
                                                    foreach ($washupDataset['tool_names'] as $tn) {
                                                        $tnorm = is_string($tn) ? trim($tn) : '';
                                                        if ($tnorm !== '' && !in_array($tnorm, $groupOrder, true)) { $groupOrder[] = $tnorm; }
                                                    }
                                                }
                                                $grouped = [];
                                                $unknownKey = 'سایر ابزارها';
                                                foreach ($itemsForGrouping as $it) {
                                                    $tools = isset($it['tools']) && is_array($it['tools']) ? $it['tools'] : [];
                                                    $compId = isset($it['competency_id']) ? (string) $it['competency_id'] : '';
                                                    if (!empty($tools)) {
                                                        foreach ($tools as $tn) {
                                                            $key = is_string($tn) ? trim($tn) : '';
                                                            if ($key === '') { $key = $unknownKey; }
                                                            if (!isset($grouped[$key])) { $grouped[$key] = []; }
                                                            // prevent duplicates by competency id inside each group
                                                            $dup = false;
                                                            if ($compId !== '') {
                                                                foreach ($grouped[$key] as $row) {
                                                                    if ((string) ($row['competency_id'] ?? '') === $compId) { $dup = true; break; }
                                                                }
                                                            }
                                                            if (!$dup) { $grouped[$key][] = $it; }
                                                            if ($key !== $unknownKey && !in_array($key, $groupOrder, true)) { $groupOrder[] = $key; }
                                                        }
                                                    } else {
                                                        if (!isset($grouped[$unknownKey])) { $grouped[$unknownKey] = []; }
                                                        $grouped[$unknownKey][] = $it;
                                                        if (!in_array($unknownKey, $groupOrder, true)) { $groupOrder[] = $unknownKey; }
                                                    }
                                                }

                                                // Auto-detect gauge max if not provided explicitly
                                                if ($gaugeMax <= 0) {
                                                    $maxDetected = 0.0;
                                                    foreach ($itemsForGrouping as $it) {
                                                        if (isset($it['agreed_score']) && is_numeric($it['agreed_score'])) {
                                                            $val = (float) $it['agreed_score'];
                                                            if ($val > $maxDetected) { $maxDetected = $val; }
                                                        }
                                                    }
                                                    if ($maxDetected <= 0) { $maxDetected = 100.0; }
                                                    // common scales: 5, 10, 100
                                                    if ($maxDetected <= 5.0) { $gaugeMax = 5.0; }
                                                    elseif ($maxDetected <= 10.0) { $gaugeMax = 10.0; }
                                                    else { $gaugeMax = 100.0; }
                                                }
                                            ?>
                                            <?php
                                                // Decide effective header alignment/size (auto when not set)
                                                $effectiveHeaderAlign = in_array($toolHeaderAlignRaw, ['right','center','left','auto',''], true)
                                                    ? $toolHeaderAlignRaw : '';
                                                if ($effectiveHeaderAlign === '' || $effectiveHeaderAlign === 'auto') {
                                                    $effectiveHeaderAlign = $eachToolOnNewPage ? 'center' : 'right';
                                                }
                                                $effectiveHeaderSize = in_array($toolHeaderSizeRaw, ['normal','large','xlarge'], true)
                                                    ? $toolHeaderSizeRaw : '';
                                                if ($effectiveHeaderSize === '') {
                                                    $effectiveHeaderSize = $eachToolOnNewPage ? 'large' : 'normal';
                                                }
                                                $titleFontSize = '1rem';
                                                if ($effectiveHeaderSize === 'large') { $titleFontSize = '1.35rem'; }
                                                elseif ($effectiveHeaderSize === 'xlarge') { $titleFontSize = '1.6rem'; }
                                                $headerJustify = $effectiveHeaderAlign === 'center' ? 'center' : 'flex-start';
                                                $scoreFirst = ($scoreOrderRaw !== 'gauge_first');
                                            ?>
                                            <div class="certificate-washup-bytool" data-split-per-tool="<?= $eachToolOnNewPage ? '1' : '0'; ?>">
                                                <?php foreach ($groupOrder as $toolName): ?>
                                                    <?php $rows = isset($grouped[$toolName]) ? $grouped[$toolName] : []; if (empty($rows)) { continue; } ?>
                                                    <div class="washup-bytool-group" style="margin-bottom: 12px;">
                                                        <div class="washup-bytool-header" style="display:flex; align-items:center; gap:8px; margin-bottom:8px; justify-content: <?= $escape($headerJustify); ?>; text-align: <?= $escape($effectiveHeaderAlign === 'center' ? 'center' : 'right'); ?>;">
                                                            <ion-icon name="flask-outline" style="font-size: <?= $escape($effectiveHeaderSize === 'xlarge' ? '1.4rem' : ($effectiveHeaderSize === 'large' ? '1.2rem' : '1rem')); ?>;"></ion-icon>
                                                            <div class="title" style="font-weight:700; color: <?= $escape($accentColor); ?>; font-size: <?= $escape($titleFontSize); ?>;">
                                                                <?= $escape($toolName); ?>
                                                            </div>
                                                            <div class="count text-muted small" style="<?= $escape($effectiveHeaderAlign === 'center' ? '' : 'margin-right:auto;'); ?>">
                                                                <?= $escape(UtilityHelper::englishToPersian((string) count($rows))); ?> شایستگی
                                                            </div>
                                                        </div>
                                                        <?php $rowsToShow = ($maxPerTool > 0) ? array_slice($rows, 0, $maxPerTool) : $rows; ?>
                                                        <?php if ($groupedItemAppearance === 'cards'): ?>
                                                            <?php
                                                                $gridCols = max(1, min(4, (int) $groupedCardsPerRow));
                                                                $gridStyle = 'display:grid; grid-template-columns: repeat(' . $gridCols . ', minmax(0,1fr)); gap:12px; padding:0; margin:0;';
                                                            ?>
                                                            <div class="washup-bytool-grid" style="<?= $escape($gridStyle); ?>">
                                                                <?php foreach ($rowsToShow as $it): ?>
                                                                    <?php
                                                                        $title = isset($it['competency_title']) ? trim((string) $it['competency_title']) : '';
                                                                        $dimension = isset($it['dimension']) ? trim((string) $it['dimension']) : '';
                                                                        $code = isset($it['competency_code']) ? trim((string) $it['competency_code']) : '';
                                                                        $scoreLabel = isset($it['score_label']) ? trim((string) $it['score_label']) : '';
                                                                        $scoreValue = isset($it['agreed_score']) && is_numeric($it['agreed_score']) ? (float) $it['agreed_score'] : null;
                                                                        // Infer competency nature (general/specific) if present in item or via dimension name
                                                                        $natureLabel = '';
                                                                        if (isset($it['competency_nature'])) {
                                                                            $natureRaw = trim((string) $it['competency_nature']);
                                                                            $natureLower = strtolower($natureRaw);
                                                                            if (in_array($natureLower, ['general','عمومی'], true)) {
                                                                                $natureLabel = 'شایستگی عمومی';
                                                                            } elseif (in_array($natureLower, ['special','specific','اختصاصی','اختصاصى'], true)) {
                                                                                $natureLabel = 'شایستگی اختصاصی';
                                                                            } elseif ($natureRaw !== '') {
                                                                                $natureLabel = $natureRaw;
                                                                            }
                                                                        } elseif (isset($it['is_general'])) {
                                                                            $isGen = (int) $it['is_general'] === 1 || $it['is_general'] === true || $it['is_general'] === '1';
                                                                            $natureLabel = $isGen ? 'شایستگی عمومی' : 'شایستگی اختصاصی';
                                                                        } else {
                                                                            $dimCheck = $dimension;
                                                                            if ($dimCheck !== '') {
                                                                                if (function_exists('mb_stripos')) {
                                                                                    if (mb_stripos($dimCheck, 'عمومی', 0, 'UTF-8') !== false) { $natureLabel = 'شایستگی عمومی'; }
                                                                                    elseif (mb_stripos($dimCheck, 'اختصاص', 0, 'UTF-8') !== false) { $natureLabel = 'شایستگی اختصاصی'; }
                                                                                } else {
                                                                                    if (stripos($dimCheck, 'عمومی') !== false) { $natureLabel = 'شایستگی عمومی'; }
                                                                                    elseif (stripos($dimCheck, 'اختصاص') !== false) { $natureLabel = 'شایستگی اختصاصی'; }
                                                                                }
                                                                            }
                                                                        }
                                                                        // Avoid showing dimension if it repeats nature
                                                                        $suppressDimensionForNature = false;
                                                                        if ($showCompetencyNature && $natureLabel !== '' && $dimension !== '') {
                                                                            if (function_exists('mb_strtolower')) {
                                                                                $dn = trim((string) $dimension);
                                                                                $nl = trim((string) $natureLabel);
                                                                                $dnLower = mb_strtolower($dn, 'UTF-8');
                                                                                $nlLower = mb_strtolower($nl, 'UTF-8');
                                                                                if ($dnLower === $nlLower) {
                                                                                    $suppressDimensionForNature = true;
                                                                                } else {
                                                                                    $hasGeneral = mb_stripos($dnLower, 'عمومی', 0, 'UTF-8') !== false && mb_stripos($nlLower, 'عمومی', 0, 'UTF-8') !== false;
                                                                                    $hasSpecific = mb_stripos($dnLower, 'اختصاص', 0, 'UTF-8') !== false && mb_stripos($nlLower, 'اختصاص', 0, 'UTF-8') !== false;
                                                                                    if ($hasGeneral || $hasSpecific) { $suppressDimensionForNature = true; }
                                                                                }
                                                                            } else {
                                                                                $dn = strtolower(trim((string) $dimension));
                                                                                $nl = strtolower(trim((string) $natureLabel));
                                                                                if ($dn === $nl || (stripos($dn, 'عمومی') !== false && stripos($nl, 'عمومی') !== false) || (stripos($dn, 'اختصاص') !== false && stripos($nl, 'اختصاص') !== false)) {
                                                                                    $suppressDimensionForNature = true;
                                                                                }
                                                                            }
                                                                        }
                                                                        $cardStyles = [];
                                                                        $cardStyles[] = 'padding:10px';
                                                                        $cardStyles[] = 'border-radius:10px';
                                                                        if ($groupedCardBorder) {
                                                                            $cardStyles[] = 'border:1px solid #e5e7eb';
                                                                            $cardStyles[] = 'border-inline-start:3px solid ' . $accentColor;
                                                                            $cardStyles[] = 'padding-inline-start:12px';
                                                                        }
                                                                        $cardStyles[] = 'background:#fff';
                                                                        if ($groupedCardShadow) {
                                                                            $cardStyles[] = 'box-shadow:0 2px 10px rgba(0,0,0,0.06)';
                                                                        }
                                                                        $cardStyleAttr = implode('; ', $cardStyles);
                                                                    ?>
                                                                    <div class="washup-bytool-card" style="<?= $escape($cardStyleAttr); ?>">
                                                                        <?php if ($groupedCardInnerLayout === 'vertical'): ?>
                                                                            <div class="card-vert" style="display:flex; flex-direction:column; align-items:center; gap:6px; text-align:center;">
                                                                                <div class="bytool-gauge" style="display:flex; align-items:center; justify-content:center; gap:6px;">
                                                                                    <?php if ($showGauges && $scoreValue !== null): ?>
                                                                                        <?= $renderMiniGauge($scoreValue, $gaugeMax, $accentColor, $gaugeSizePx); ?>
                                                                                    <?php endif; ?>
                                                                                </div>
                                                                                <?php if ($showScore && $scoreLabel !== ''): ?>
                                                                                    <div class="bytool-score" style="color: <?= $escape($accentColor); ?>; font-weight:700;">
                                                                                        <?= $escape($scoreLabel); ?>
                                                                                    </div>
                                                                                <?php endif; ?>
                                                                                <div class="bytool-title" style="min-width:0;">
                                                                                    <span class="comp-title" style="font-weight:800;">
                                                                                        <?= $escape($title !== '' ? $title : 'شایستگی'); ?>
                                                                                    </span>
                                                                                    <?php if ($showCompetencyNature && $natureLabel !== ''): ?>
                                                                                        <div class="nature text-muted small" style="margin-top:4px;"><?= $escape($natureLabel); ?></div>
                                                                                    <?php endif; ?>
                                                                                    <?php if ($showDimension && $dimension !== '' && !$suppressDimensionForNature): ?>
                                                                                        <div class="dim text-muted small" style="margin-top:2px;"><?= $escape($dimension); ?></div>
                                                                                    <?php endif; ?>
                                                                                    <?php if ($showCompetencyCode && $code !== ''): ?>
                                                                                        <div class="code text-muted small" style="margin-top:2px;"><?= $escape($code); ?></div>
                                                                                    <?php endif; ?>
                                                                                </div>
                                                                            </div>
                                                                        <?php else: ?>
                                                                            <div class="card-row" style="display:flex; align-items:center; justify-content:space-between; gap:8px;">
                                                                                <div class="bytool-main" style="min-width:0;">
                                                                                    <span class="comp-title" style="font-weight:600;"><?= $escape($title !== '' ? $title : 'شایستگی'); ?></span>
                                                                                    <?php if ($showDimension && $dimension !== ''): ?><span class="sep" style="color:#999; margin:0 6px;">•</span><span class="dim text-muted small"><?= $escape($dimension); ?></span><?php endif; ?>
                                                                                    <?php if ($showCompetencyCode && $code !== ''): ?><span class="sep" style="color:#999; margin:0 6px;">•</span><span class="code text-muted small"><?= $escape($code); ?></span><?php endif; ?>
                                                                                </div>
                                                                                <div class="bytool-side" style="display:flex; align-items:center; gap:8px; <?= $escape($scoreInline ? 'flex-direction:row; white-space:nowrap;' : 'flex-direction:column; align-items:flex-end;'); ?>">
                                                                                    <?php if ($scoreFirst): ?>
                                                                                        <?php if ($showScore && $scoreLabel !== ''): ?>
                                                                                            <span class="score" style="color: <?= $escape($accentColor); ?>; font-weight:700; white-space:nowrap;">
                                                                                                <?= $escape($scoreLabel); ?>
                                                                                            </span>
                                                                                        <?php endif; ?>
                                                                                        <?php if ($showGauges && $scoreValue !== null): ?>
                                                                                            <?= $renderMiniGauge($scoreValue, $gaugeMax, $accentColor, $gaugeSizePx); ?>
                                                                                        <?php endif; ?>
                                                                                    <?php else: ?>
                                                                                        <?php if ($showGauges && $scoreValue !== null): ?>
                                                                                            <?= $renderMiniGauge($scoreValue, $gaugeMax, $accentColor, $gaugeSizePx); ?>
                                                                                        <?php endif; ?>
                                                                                        <?php if ($showScore && $scoreLabel !== ''): ?>
                                                                                            <span class="score" style="color: <?= $escape($accentColor); ?>; font-weight:700; white-space:nowrap;">
                                                                                                <?= $escape($scoreLabel); ?>
                                                                                            </span>
                                                                                        <?php endif; ?>
                                                                                    <?php endif; ?>
                                                                                </div>
                                                                            </div>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        <?php else: ?>
                                                            <ul class="washup-bytool-list" style="list-style:none; padding:0; margin:0;">
                                                            <?php foreach ($rowsToShow as $it): ?>
                                                                <?php
                                                                    $title = isset($it['competency_title']) ? trim((string) $it['competency_title']) : '';
                                                                    $dimension = isset($it['dimension']) ? trim((string) $it['dimension']) : '';
                                                                    $code = isset($it['competency_code']) ? trim((string) $it['competency_code']) : '';
                                                                    $scoreLabel = isset($it['score_label']) ? trim((string) $it['score_label']) : '';
                                                                    $scoreValue = isset($it['agreed_score']) && is_numeric($it['agreed_score']) ? (float) $it['agreed_score'] : null;
                                                                ?>
                                                                <li class="washup-bytool-row" style="display:flex; align-items:center; justify-content:space-between; gap:12px; padding:8px 0; border-bottom:1px dashed #eee;">
                                                                    <div class="bytool-main" style="min-width:0;">
                                                                        <span class="comp-title" style="font-weight:500;"><?= $escape($title !== '' ? $title : 'شایستگی'); ?></span>
                                                                        <?php if ($showDimension && $dimension !== ''): ?><span class="sep" style="color:#999; margin:0 6px;">•</span><span class="dim text-muted small"><?= $escape($dimension); ?></span><?php endif; ?>
                                                                        <?php if ($showCompetencyCode && $code !== ''): ?><span class="sep" style="color:#999; margin:0 6px;">•</span><span class="code text-muted small"><?= $escape($code); ?></span><?php endif; ?>
                                                                    </div>
                                                                    <div class="bytool-side" style="display:flex; align-items:center; gap:8px; <?= $escape($scoreInline ? 'flex-direction:row; white-space:nowrap;' : 'flex-direction:column; align-items:flex-end;'); ?>">
                                                                        <?php if ($scoreFirst): ?>
                                                                            <?php if ($showScore && $scoreLabel !== ''): ?>
                                                                                <span class="score" style="color: <?= $escape($accentColor); ?>; font-weight:600; white-space:nowrap;">
                                                                                    <?= $escape($scoreLabel); ?>
                                                                                </span>
                                                                            <?php endif; ?>
                                                                            <?php if ($showGauges && $scoreValue !== null): ?>
                                                                                <?= $renderMiniGauge($scoreValue, $gaugeMax, $accentColor, $gaugeSizePx); ?>
                                                                            <?php endif; ?>
                                                                        <?php else: ?>
                                                                            <?php if ($showGauges && $scoreValue !== null): ?>
                                                                                <?= $renderMiniGauge($scoreValue, $gaugeMax, $accentColor, $gaugeSizePx); ?>
                                                                            <?php endif; ?>
                                                                            <?php if ($showScore && $scoreLabel !== ''): ?>
                                                                                <span class="score" style="color: <?= $escape($accentColor); ?>; font-weight:600; white-space:nowrap;">
                                                                                    <?= $escape($scoreLabel); ?>
                                                                                </span>
                                                                            <?php endif; ?>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </li>
                                                            <?php endforeach; ?>
                                                            </ul>
                                                        <?php endif; ?>
                                                        <?php
                                                            // Example notes table under each tool group (if data available)
                                                            $examplesByTool = isset($washupDataset['examples_by_tool']) && is_array($washupDataset['examples_by_tool']) ? $washupDataset['examples_by_tool'] : [];
                                                            $exampleRows = isset($examplesByTool[$toolName]) && is_array($examplesByTool[$toolName]) ? $examplesByTool[$toolName] : [];
                                                        ?>
                                                        <?php if ($showExampleNotes && !empty($exampleRows)): ?>
                                                            <?php
                                                                $columns = ['شایستگی', 'مصداق', 'توضیح ارزیاب'];
                                                                if ($exampleNotesShowEvaluator) {
                                                                    $columns[] = 'ارزیاب';
                                                                }
                                                                $tableRows = [];
                                                                foreach ($exampleRows as $er) {
                                                                    $ct = isset($er['competency_title']) ? trim((string) $er['competency_title']) : '';
                                                                    $ex = isset($er['example_text']) ? trim((string) $er['example_text']) : '';
                                                                    $nt = isset($er['note']) ? trim((string) $er['note']) : '';
                                                                    $ev = isset($er['evaluator']) ? trim((string) $er['evaluator']) : '';
                                                                    $row = [$ct !== '' ? $ct : '—', $ex !== '' ? $ex : '—', $nt !== '' ? $nt : '—'];
                                                                    if ($exampleNotesShowEvaluator) { $row[] = $ev; }
                                                                    $tableRows[] = $row;
                                                                }
                                                                // Chunk rows for splitting
                                                                $rowsPerChunk = $getTableRowsPerChunk($sizeClass, $orientationClass, count($columns));
                                                                $chunks = [$tableRows];
                                                                if ($rowsPerChunk > 0 && count($tableRows) > $rowsPerChunk) {
                                                                    $chunks = [];
                                                                    $total = count($tableRows);
                                                                    for ($off = 0; $off < $total; $off += $rowsPerChunk) {
                                                                        $chunks[] = array_slice($tableRows, $off, $rowsPerChunk);
                                                                    }
                                                                }
                                                            ?>
                                                            <div class="certificate-table-block certificate-table-behavior-split" data-size-behavior="allow_split" style="margin-top:10px;">
                                                                <?php if ($exampleNotesTitle !== ''): ?>
                                                                    <div class="certificate-table-title"><?= $escape($exampleNotesTitle); ?></div>
                                                                <?php endif; ?>
                                                                <?php foreach ($chunks as $chunkIndex => $chunkRows): ?>
                                                                    <div class="certificate-table-chunk" data-chunk-index="<?= $chunkIndex; ?>" data-chunk-count="<?= count($chunks); ?>">
                                                                        <div class="certificate-table-wrapper<?= $chunkIndex > 0 ? ' is-continuation' : ''; ?>" dir="rtl">
                                                                            <?php if ($chunkIndex > 0): ?><div class="certificate-table-continuation-label">ادامه جدول</div><?php endif; ?>
                                                                            <table class="certificate-table certificate-table--grid">
                                                                                <thead>
                                                                                    <tr>
                                                                                        <?php foreach ($columns as $col): ?><th><?= $escape($col); ?></th><?php endforeach; ?>
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody>
                                                                                    <?php foreach ($chunkRows as $row): ?>
                                                                                        <tr>
                                                                                            <?php foreach ($row as $cell): ?><td><?= nl2br($escape($cell)); ?></td><?php endforeach; ?>
                                                                                        </tr>
                                                                                    <?php endforeach; ?>
                                                                                </tbody>
                                                                            </table>
                                                                        </div>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        <?php elseif ($showExampleNotes): ?>
                                                            <div class="alert alert-light text-muted" style="margin-top:10px;">
                                                                مصداقی با توضیح ارزیاب برای این ابزار ثبت نشده است.
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <?php
                                                $columns = ['شایستگی'];
                                                if ($showDimension) { $columns[] = 'حوزه'; }
                                                if ($showCompetencyCode) { $columns[] = 'کد'; }
                                                if ($showScore) { $columns[] = 'امتیاز'; }
                                                if ($showTools) { $columns[] = 'ابزارهای موثر'; }
                                                if ($showUpdatedInfo) { $columns[] = 'به‌روزرسانی'; }
                                            ?>
                                            <div class="certificate-washup-table-wrapper">
                                                <table class="certificate-table certificate-table--grid" dir="rtl">
                                                    <thead>
                                                        <tr>
                                                            <?php foreach ($columns as $col): ?>
                                                                <th><?= $escape($col); ?></th>
                                                            <?php endforeach; ?>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($itemsToRender as $it): ?>
                                                            <?php
                                                                $title = isset($it["competency_title"]) ? trim((string) $it["competency_title"]) : '';
                                                                $code = isset($it["competency_code"]) ? trim((string) $it["competency_code"]) : '';
                                                                $dimension = isset($it["dimension"]) ? trim((string) $it["dimension"]) : '';
                                                                $scoreLabel = isset($it["score_label"]) ? trim((string) $it["score_label"]) : '';
                                                                $tools = isset($it['tools']) && is_array($it['tools']) ? $it['tools'] : [];
                                                                $updatedBy = isset($it['updated_by']) ? trim((string) $it['updated_by']) : '';
                                                                $updatedAt = isset($it['updated_at_display']) ? trim((string) $it['updated_at_display']) : '';
                                                                $updatedText = trim(($updatedBy !== '' ? ($updatedBy) : '') . ($updatedAt !== '' ? (' - ' . $updatedAt) : ''));
                                                            ?>
                                                            <tr>
                                                                <td><?= $escape($title !== '' ? $title : 'شایستگی'); ?></td>
                                                                <?php if ($showDimension): ?><td><?= $escape($dimension); ?></td><?php endif; ?>
                                                                <?php if ($showCompetencyCode): ?><td><?= $escape($code); ?></td><?php endif; ?>
                                                                <?php if ($showScore): ?><td><?= $escape($scoreLabel); ?></td><?php endif; ?>
                                                                <?php if ($showTools): ?><td><?= $escape(!empty($tools) ? implode('، ', array_slice(array_map('strval', $tools), 0, 8)) : ''); ?></td><?php endif; ?>
                                                                <?php if ($showUpdatedInfo): ?><td><?= $escape($updatedText); ?></td><?php endif; ?>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div class="alert alert-light text-muted mb-0"><?= $escape($emptyMessage); ?></div>
                                    <?php endif; ?>

                                    <?php if ($showFooter && $footerNote !== ''): ?>
                                        <div class="certificate-washup-footer text-muted small"><?= nl2br($escape($footerNote)); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                        <?php else: ?>
                            <?php $widthClass = $elementWidthClass($props); $outerClassAttr = implode(' ', array_filter(['certificate-element', $widthClass])); ?>
                            <div class="<?= $escape($outerClassAttr); ?>">
                                <div class="alert alert-warning mb-0">نمایش این المان در پیش‌نمایش پشتیبانی نمی‌شود.</div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    </div>
                </div>
                <div class="certificate-page-footer"><span class="page-number"></span></div>
            </section>
    <?php endforeach; ?>
    </div>
    </div>
</div>

<script>
(function () {
    var pageSelector = document.getElementById('page-selector');
    var hasRunSplit = false;
    var hasProcessedStartBreaks = false;

    function parsePx(value) {
        var num = parseFloat(value);
        return isNaN(num) ? 0 : num;
    }

    function createContinuationPage(originalPage, chunkIndex) {
        var newPage = document.createElement('section');
        newPage.className = originalPage.className + ' certificate-preview-page--continuation';
        var baseId = originalPage.id && originalPage.id !== '' ? originalPage.id : 'certificate-page';
        newPage.id = baseId + '-cont-' + chunkIndex + '-' + Math.random().toString(16).slice(2);

        var header = originalPage.querySelector('.certificate-page-header');
        if (header) {
            var headerClone = header.cloneNode(true);
            var titleEl = headerClone.querySelector('h4');
            if (titleEl) {
                var baseText = titleEl.textContent.replace(/\s+-\s+ادامه.*$/u, '').trim();
                if (baseText === '') {
                    baseText = 'صفحه';
                }
                titleEl.textContent = baseText + ' - ادامه';
            }
            newPage.appendChild(headerClone);
        }

        var body = document.createElement('div');
        body.className = 'certificate-page-body';
        var originalBody = originalPage.querySelector('.certificate-page-body');
        if (originalBody) {
            var scaleValue = originalBody.style.getPropertyValue('--page-scale');
            if (scaleValue) {
                body.style.setProperty('--page-scale', scaleValue);
            }
            if (originalBody.dataset.baseScale) {
                body.dataset.baseScale = originalBody.dataset.baseScale;
            }
        }

        var bodyInner = document.createElement('div');
        bodyInner.className = 'certificate-page-body-inner';
        body.appendChild(bodyInner);
        newPage.appendChild(body);

        return newPage;
    }

    function handleStartOnNextPageElements() {
        if (hasProcessedStartBreaks) {
            return [];
        }
        hasProcessedStartBreaks = true;

        var createdPages = [];
        var pages = Array.from(document.querySelectorAll('.certificate-preview-page'));
        if (!pages.length) {
            return createdPages;
        }

        for (var i = 0; i < pages.length; i += 1) {
            var page = pages[i];
            var bodyInner = page.querySelector('.certificate-page-body-inner');
            if (!bodyInner) {
                continue;
            }

            var children = Array.from(bodyInner.children);
            var breakIndex = -1;
            for (var j = 0; j < children.length; j += 1) {
                if (children[j].dataset && children[j].dataset.startOnNextPage === '1') {
                    breakIndex = j;
                    break;
                }
            }

            if (breakIndex <= 0) {
                continue;
            }

            var newPage = createContinuationPage(page, breakIndex);
            var newBodyInner = newPage.querySelector('.certificate-page-body-inner');
            var toMove = children.slice(breakIndex);
            toMove.forEach(function (element) {
                if (element.dataset) {
                    element.dataset.startOnNextPage = '0';
                }
                newBodyInner.appendChild(element);
            });

            page.parentNode.insertBefore(newPage, page.nextSibling);
            createdPages.push(newPage);

            pages.splice(i + 1, 0, newPage);
        }

        if (createdPages.length && pageSelector) {
            var existingOptions = Array.from(pageSelector.options || []);
            createdPages.forEach(function (newPage) {
                var exists = existingOptions.some(function (opt) {
                    return opt.value === newPage.id;
                });
                if (exists) {
                    return;
                }
                var headerTitle = newPage.querySelector('.certificate-page-header h4');
                var option = document.createElement('option');
                option.value = newPage.id;
                option.textContent = headerTitle ? headerTitle.textContent : 'صفحه جدید';
                pageSelector.appendChild(option);
            });
        }

        return createdPages;
    }

    function handleSplitByToolGroups() {
        var created = [];
        var pages = Array.from(document.querySelectorAll('.certificate-preview-page'));
        if (!pages.length) return created;

        pages.forEach(function (page) {
            var bodyInner = page.querySelector('.certificate-page-body-inner');
            if (!bodyInner) return;
            var splitBlocks = Array.from(bodyInner.querySelectorAll('.certificate-washup-bytool[data-split-per-tool="1"]'));
            if (!splitBlocks.length) return;

            splitBlocks.forEach(function (block) {
                if (block.dataset.splitProcessed === '1') return;
                var elementContainer = block.closest('.certificate-element');
                if (!elementContainer) { block.dataset.splitProcessed = '1'; return; }
                var groups = Array.from(block.querySelectorAll('.washup-bytool-group'));
                if (groups.length <= 1) { block.dataset.splitProcessed = '1'; return; }

                var pageHeader = page.querySelector('.certificate-page-header h4');
                var baseTitle = pageHeader ? (pageHeader.textContent || '').replace(/\s+-\s+ادامه.*$/u, '').trim() : '';
                var lastInserted = page;

                // Clone each extra group into a continuation page
                for (var gi = 1; gi < groups.length; gi += 1) {
                    var cloneElement = elementContainer.cloneNode(true);
                    var cloneBlock = cloneElement.querySelector('.certificate-washup-bytool');
                    if (!cloneBlock) continue;
                    cloneBlock.dataset.splitProcessed = '1';
                    var allGroups = Array.from(cloneBlock.querySelectorAll('.washup-bytool-group'));
                    allGroups.forEach(function (g, idx) { if (idx !== gi) { g.remove(); } });

                    var newPage = createContinuationPage(page, gi);
                    var headerTitle = newPage.querySelector('.certificate-page-header h4');
                    if (headerTitle && baseTitle) { headerTitle.textContent = baseTitle + ' - ادامه'; }
                    var newBodyInner = newPage.querySelector('.certificate-page-body-inner');
                    newBodyInner.appendChild(cloneElement);
                    lastInserted.parentNode.insertBefore(newPage, lastInserted.nextSibling);
                    lastInserted = newPage;
                    created.push(newPage);
                }

                // Keep only first group in original block
                for (var rm = groups.length - 1; rm >= 1; rm -= 1) {
                    groups[rm].remove();
                }
                block.dataset.splitProcessed = '1';
            });
        });

        if (created.length) {
            var selector = document.getElementById('page-selector');
            if (selector) {
                created.forEach(function (newPage) {
                    var headerTitle = newPage.querySelector('.certificate-page-header h4');
                    var option = document.createElement('option');
                    option.value = newPage.id;
                    option.textContent = headerTitle ? headerTitle.textContent : 'صفحه ادامه';
                    selector.appendChild(option);
                });
            }
        }
        return created;
    }

    function handleSplitTables() {
        if (hasRunSplit) {
            return [];
        }
        hasRunSplit = true;

        var newPages = [];
        var pages = Array.from(document.querySelectorAll('.certificate-preview-page'));
        if (!pages.length) {
            return newPages;
        }
        var pageContainer = pages[0].parentNode;
        if (!pageContainer) {
            return newPages;
        }

        pages.forEach(function (page) {
            var bodyInner = page.querySelector('.certificate-page-body-inner');
            if (!bodyInner) {
                return;
            }
            var splitBlocks = Array.from(bodyInner.querySelectorAll('.certificate-table-block[data-size-behavior="allow_split"]'));
            if (!splitBlocks.length) {
                return;
            }

            splitBlocks.forEach(function (block) {
                if (block.dataset.splitProcessed === '1') {
                    return;
                }
                block.dataset.splitProcessed = '1';

                var elementContainer = block.closest('.certificate-element');
                if (!elementContainer) {
                    return;
                }

                var chunks = Array.from(block.querySelectorAll('.certificate-table-chunk'));
                if (chunks.length <= 1) {
                    return;
                }

                var titleEl = block.querySelector('.certificate-table-title');
                var titleText = titleEl ? titleEl.textContent.trim() : '';
                var lastInserted = page;

                chunks.slice(1).forEach(function (chunk) {
                    var chunkIndex = parseInt(chunk.dataset.chunkIndex || '0', 10);
                    var cloneElement = elementContainer.cloneNode(true);
                    var cloneBlock = cloneElement.querySelector('.certificate-table-block');
                    if (!cloneBlock) {
                        return;
                    }

                    cloneBlock.querySelectorAll('.certificate-table-chunk').forEach(function (clonedChunk) {
                        var clonedIndex = parseInt(clonedChunk.dataset.chunkIndex || '0', 10);
                        if (clonedIndex !== chunkIndex) {
                            clonedChunk.remove();
                        } else {
                            var wrapper = clonedChunk.querySelector('.certificate-table-wrapper');
                            if (wrapper) {
                                wrapper.classList.add('is-continuation');
                            }
                        }
                    });

                    var clonedTitle = cloneBlock.querySelector('.certificate-table-title');
                    if (clonedTitle) {
                        if (titleText !== '') {
                            clonedTitle.textContent = titleText + ' (ادامه)';
                        } else {
                            clonedTitle.remove();
                        }
                    }

                    var newPage = createContinuationPage(page, chunkIndex);
                    var newBodyInner = newPage.querySelector('.certificate-page-body-inner');
                    newBodyInner.appendChild(cloneElement);

                    lastInserted.parentNode.insertBefore(newPage, lastInserted.nextSibling);
                    lastInserted = newPage;
                    newPages.push(newPage);

                    chunk.remove();
                });

                block.querySelectorAll('.certificate-table-chunk').forEach(function (originalChunk) {
                    var idx = parseInt(originalChunk.dataset.chunkIndex || '0', 10);
                    if (idx > 0) {
                        originalChunk.remove();
                    }
                });
            });
        });

        if (newPages.length && pageSelector) {
            newPages.forEach(function (newPage) {
                var headerTitle = newPage.querySelector('.certificate-page-header h4');
                var option = document.createElement('option');
                option.value = newPage.id;
                option.textContent = headerTitle ? headerTitle.textContent : 'صفحه ادامه';
                pageSelector.appendChild(option);
            });
        }

        return newPages;
    }

    function applyPageScaling() {
        document.querySelectorAll('.certificate-page-body').forEach(function (body) {
            var baseScale = parseFloat(body.dataset.baseScale || body.style.getPropertyValue('--page-scale') || '1');
            if (!isFinite(baseScale) || baseScale <= 0) {
                baseScale = 1;
            }
            body.dataset.baseScale = baseScale.toString();

            var page = body.closest('.certificate-preview-page');
            var bodyInner = body.querySelector('.certificate-page-body-inner');
            if (!page || !bodyInner) {
                return;
            }

            var pageStyles = window.getComputedStyle(page);
            var minHeight = parsePx(pageStyles.minHeight);
            if (minHeight <= 0) {
                minHeight = page.clientHeight || bodyInner.scrollHeight;
            }
            var paddingTop = parsePx(pageStyles.paddingTop);
            var paddingBottom = parsePx(pageStyles.paddingBottom);

            var bodyRect = body.getBoundingClientRect();
            var pageRect = page.getBoundingClientRect();
            var offsetTop = bodyRect.top - pageRect.top;
            if (!isFinite(offsetTop)) {
                offsetTop = paddingTop;
            }

            var availableHeight = minHeight - offsetTop - paddingBottom;
            if (!(availableHeight > 0)) {
                availableHeight = minHeight;
            }

            var scrollHeight = bodyInner.scrollHeight;
            var targetScale = baseScale;
            if (scrollHeight > 0) {
                var scaleByHeight = availableHeight / scrollHeight;
                if (scaleByHeight < targetScale) {
                    targetScale = Math.max(scaleByHeight, 0.5);
                }
            }

            var clampedHeight = availableHeight > 0 ? availableHeight : scrollHeight;
            if (!isFinite(clampedHeight) || clampedHeight <= 0) {
                clampedHeight = 0;
            }

            body.style.setProperty('--page-scale', targetScale.toFixed(4));
            body.dataset.currentScale = targetScale.toFixed(4);
            if (clampedHeight > 0) {
                body.style.height = clampedHeight.toFixed(2) + 'px';
                body.style.maxHeight = clampedHeight.toFixed(2) + 'px';
                body.style.overflow = 'hidden';
            } else {
                body.style.removeProperty('height');
                body.style.removeProperty('max-height');
            }
        });
    }

    function initializePreviewLayout() {
        document.querySelectorAll('.certificate-page-body').forEach(function (body) {
            if (!body.dataset.baseScale) {
                var initialScale = parseFloat(body.style.getPropertyValue('--page-scale') || '1');
                if (!isFinite(initialScale) || initialScale <= 0) {
                    initialScale = 1;
                }
                body.dataset.baseScale = initialScale.toString();
            }
        });

        handleStartOnNextPageElements();
        handleSplitByToolGroups();
        handleSplitTables();
        requestAnimationFrame(function () {
            applyPageScaling();
            // Update bottom page numbers after any splitting
            var pages = Array.from(document.querySelectorAll('.certificate-preview-page'));
            pages.forEach(function(page, idx){
                var footerNum = page.querySelector('.certificate-page-footer .page-number');
                if (footerNum) {
                    footerNum.textContent = (idx + 1).toString();
                }
            });
        });
    }

    document.addEventListener('DOMContentLoaded', initializePreviewLayout);
    window.addEventListener('resize', function () {
        applyPageScaling();
    });
    window.addEventListener('load', function () {
        applyPageScaling();
    });
})();
</script>


<script>
// Professional Certificate PDF Generator - نسخه نهایی و حرفه‌ای
(function () {
    console.log('🎯 Professional Certificate PDF Generator - Initializing...');
    
    // Debug: Check if Professional PDF is loaded
    setTimeout(function() {
        if (typeof CustomCertificatePdf !== 'undefined') {
            console.log('✅ CustomCertificatePdf is available!');
        } else {
            console.error('❌ CustomCertificatePdf is NOT available!');
            console.log('Available objects:', Object.keys(window).filter(k => k.includes('Certificate') || k.includes('Pdf')));
        }
    }, 1000);
    
    var downloadButton = document.querySelector('[data-action="download-certificate-pdf"]');
    
    if (!downloadButton) {
        console.error('❌ Download button NOT found!');
        return;
    }

    console.log('✅ Download button found');

    var exportInProgress = false;

    downloadButton.addEventListener('click', async function (event) {
        event.preventDefault();
        
        if (exportInProgress) {
            console.log('⏳ Export already in progress');
            return;
        }

        // Check if our Professional generator is available
        if (typeof CustomCertificatePdf === 'undefined') {
            alert('❌ خطا: کتابخانه PDF بارگذاری نشده است! لطفاً صفحه را Refresh کنید.');
            console.error('CustomCertificatePdf is not loaded');
            return;
        }

        console.log('📦 Starting PDF export with Professional Generator...');
        console.log('🎯 Using dom-to-image-more for perfect font rendering');
        console.log('� Fixed size: 210x297mm (A4) - Independent of monitor');
        
        exportInProgress = true;
        downloadButton.classList.add('is-loading');
        downloadButton.disabled = true;
        
        var originalHTML = downloadButton.innerHTML;
        
        // Create beautiful loading overlay
        const loadingOverlay = document.createElement('div');
        loadingOverlay.id = 'pdfLoadingOverlay';
        loadingOverlay.innerHTML = `
            <div class="loading-backdrop"></div>
            <div class="loading-card">
                <div class="loading-spinner">
                    <svg width="80" height="80" viewBox="0 0 100 100">
                        <circle cx="50" cy="50" r="45" stroke="#e5e7eb" stroke-width="8" fill="none"/>
                        <circle cx="50" cy="50" r="45" stroke="#667eea" stroke-width="8" fill="none" 
                                stroke-dasharray="283" stroke-dashoffset="75" 
                                style="animation: progress 2s ease-in-out infinite;"/>
                    </svg>
                    <div class="loading-icon">📄</div>
                </div>
                <h3 class="loading-title">در حال تولید PDF</h3>
                <p class="loading-text">لطفاً صبر کنید، فایل شما در حال آماده‌سازی است...</p>
                <div class="loading-progress-bar">
                    <div class="loading-progress-fill"></div>
                </div>
                <p class="loading-status">آماده‌سازی...</p>
            </div>
        `;
        document.body.appendChild(loadingOverlay);
        
        // Update button text
        downloadButton.innerHTML = '<svg style="width:20px;height:20px;animation:spin 1s linear infinite" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a10 10 0 0 1 10 10"/></svg><span>در حال پردازش...</span>';

        try {
            // Create Custom generator instance with OPTIMIZED settings
            const generator = new CustomCertificatePdf({
                pageWidth: 210,       // mm - A4 width (ثابت)
                pageHeight: 297,      // mm - A4 height (ثابت)
                scale: 2,             // REDUCED: 2x scale (192 DPI) for smaller file
                quality: 0.92,        // Quality reduced for smaller file
                backgroundColor: '#ffffff',
                compress: true,       // فشرده‌سازی PDF
                debug: true           // نمایش لاگ‌ها
            });

            // Generate PDF with progress callback
            const filename = 'certificate_' + Date.now() + '.pdf';
            
            console.log('⚡ Generating PDF...');
            
            // Progress callback
            const updateProgress = (info) => {
                const statusEl = document.querySelector('.loading-status');
                const progressFill = document.querySelector('.loading-progress-fill');
                
                if (statusEl) {
                    statusEl.textContent = info.message;
                }
                
                if (progressFill) {
                    progressFill.style.animation = 'none';
                    progressFill.style.width = info.progress + '%';
                }
            };
            
            await generator.generate('.certificate-preview-page', filename, updateProgress);
            
            console.log('✅ PDF generated successfully!');
            
            // Success notification
            const successMsg = document.createElement('div');
            successMsg.style.cssText = 'position:fixed;top:20px;right:20px;background:#10b981;color:white;padding:16px 24px;border-radius:12px;box-shadow:0 10px 25px rgba(0,0,0,0.2);z-index:99999;font-family:Vazirmatn,sans-serif;font-weight:600;animation:slideIn 0.3s ease;';
            successMsg.textContent = '✅ فایل PDF با موفقیت دانلود شد!';
            document.body.appendChild(successMsg);
            
            setTimeout(() => {
                successMsg.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => document.body.removeChild(successMsg), 300);
            }, 3000);

        } catch (error) {
            console.error('❌ PDF generation failed:', error);
            
            // Remove loading overlay
            const overlay = document.getElementById('pdfLoadingOverlay');
            if (overlay) overlay.remove();
            
            // Show error message
            const errorMsg = document.createElement('div');
            errorMsg.style.cssText = 'position:fixed;top:20px;right:20px;background:#ef4444;color:white;padding:16px 24px;border-radius:12px;box-shadow:0 10px 25px rgba(0,0,0,0.2);z-index:99999;font-family:Tahoma,sans-serif;font-weight:600;animation:slideIn 0.3s ease;';
            errorMsg.textContent = '❌ خطا در تولید PDF: ' + error.message;
            document.body.appendChild(errorMsg);
            
            setTimeout(() => {
                errorMsg.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => document.body.removeChild(errorMsg), 300);
            }, 4000);
            
        } finally {
            // Remove loading overlay
            const overlay = document.getElementById('pdfLoadingOverlay');
            if (overlay) {
                overlay.style.opacity = '0';
                setTimeout(() => overlay.remove(), 300);
            }
            
            // Restore button
            exportInProgress = false;
            downloadButton.classList.remove('is-loading');
            downloadButton.disabled = false;
            downloadButton.innerHTML = originalHTML;
        }
    });

    // Add animations and loading styles
    var style = document.createElement('style');
    style.textContent = `
        @keyframes spin { 
            from { transform: rotate(0deg); } 
            to { transform: rotate(360deg); } 
        }
        @keyframes slideIn {
            from { transform: translateX(400px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(400px); opacity: 0; }
        }
        @keyframes progress {
            0% { stroke-dashoffset: 283; }
            50% { stroke-dashoffset: 75; }
            100% { stroke-dashoffset: 283; }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes progressBar {
            0% { width: 0%; }
            25% { width: 25%; }
            50% { width: 50%; }
            75% { width: 75%; }
            100% { width: 100%; }
        }
        
        /* Loading Overlay Styles */
        #pdfLoadingOverlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 999999;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s ease;
            transition: opacity 0.3s ease;
        }
        
        #pdfLoadingOverlay .loading-backdrop {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(8px);
        }
        
        #pdfLoadingOverlay .loading-card {
            position: relative;
            background: white;
            border-radius: 24px;
            padding: 48px 64px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            text-align: center;
            max-width: 500px;
            animation: fadeIn 0.5s ease 0.2s both;
        }
        
        #pdfLoadingOverlay .loading-spinner {
            position: relative;
            width: 80px;
            height: 80px;
            margin: 0 auto 24px;
        }
        
        #pdfLoadingOverlay .loading-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 32px;
            animation: spin 3s linear infinite;
        }
        
        #pdfLoadingOverlay .loading-title {
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
            margin: 0 0 12px 0;
            font-family: Tahoma, Arial, sans-serif;
        }
        
        #pdfLoadingOverlay .loading-text {
            font-size: 15px;
            color: #6b7280;
            margin: 0 0 24px 0;
            font-family: Tahoma, Arial, sans-serif;
        }
        
        #pdfLoadingOverlay .loading-progress-bar {
            width: 100%;
            height: 8px;
            background: #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 12px;
        }
        
        #pdfLoadingOverlay .loading-progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            border-radius: 4px;
            animation: progressBar 3s ease-in-out infinite;
        }
        
        #pdfLoadingOverlay .loading-status {
            font-size: 13px;
            color: #9ca3af;
            margin: 0;
            font-family: Tahoma, Arial, sans-serif;
            font-weight: 500;
        }
    `;
    document.head.appendChild(style);
})();
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check for auto download parameter
    var urlParams = new URLSearchParams(window.location.search);
    var autoDownload = urlParams.get('auto_download') === '1';
    var evaluationId = urlParams.get('evaluation_id') || '';
    var evaluateeId = urlParams.get('evaluatee_id') || '';
    
    var downloadButton = document.querySelector('[data-action="download-certificate-pdf"]');
    
    // Auto trigger download if requested
    if (autoDownload && evaluationId && evaluateeId && downloadButton) {
        setTimeout(function() {
            downloadButton.click();
        }, 1000);
    }
});
</script>

<?php include __DIR__ . '/../../layouts/organization-footer.php'; ?>
