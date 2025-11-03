<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'جزئیات Wash-Up';
$user = $user ?? (class_exists('AuthHelper') && AuthHelper::getUser() ? AuthHelper::getUser() : null);
$detailMode = isset($detailMode) && $detailMode === 'final' ? 'final' : 'washup';

$evaluationSummary = $evaluationSummary ?? [
    'id' => 0,
    'title' => 'ارزیابی',
    'date_display' => '—',
    'general_model_label' => '',
    'specific_model_label' => '',
    'schedule_title' => '',
];
$evaluateeSummary = $evaluateeSummary ?? [
    'id' => 0,
    'label' => 'ارزیابی‌شونده',
];
$scorerSummary = $scorerSummary ?? [];
$toolHeaders = $toolHeaders ?? [];
$toolSummaries = $toolSummaries ?? [];
if (empty($toolHeaders) && !empty($toolSummaries) && is_array($toolSummaries)) {
    $toolHeaders = array_values($toolSummaries);
}
$toolHeaderMap = [];
foreach ($toolHeaders as $toolHeader) {
    $toolId = (int) ($toolHeader['id'] ?? 0);
    if ($toolId > 0) {
        $toolHeaderMap[$toolId] = $toolHeader;
    }
}
$competencySummaries = $competencySummaries ?? [];
$detailStats = $detailStats ?? [
    'total_scores' => 0,
    'average_score' => null,
    'tools_total' => 0,
    'tools_with_scores' => 0,
    'competencies_total' => 0,
];
$visibilityContext = $visibilityContext ?? [
    'role_label' => 'کاربر سازمان',
    'user_display' => 'کاربر سازمان',
    'message' => '',
    'can_view_all' => false,
    'is_evaluator' => false,
    'is_evaluatee' => false,
];

$pageMessages = $pageMessages ?? [];
$successMessage = $successMessage ?? null;
$errorMessage = $errorMessage ?? null;
$warningMessage = $warningMessage ?? null;
$infoMessage = $infoMessage ?? null;
$toolsWithoutScores = $toolsWithoutScores ?? [];
$finalRecommendation = $finalRecommendation ?? null;
$agreedScoresAction = $agreedScoresAction ?? UtilityHelper::baseUrl('organizations/wash-up/agreed-scores');
$canEditAgreedScores = $canEditAgreedScores ?? false;

if (!function_exists('washup_escape_html')) {
    function washup_escape_html($value, string $default = '—'): string
    {
        if (is_array($value)) {
            $value = implode('، ', array_filter(array_map(static function ($item) {
                return is_scalar($item) ? (string) $item : '';
            }, $value)));
        } elseif (is_object($value)) {
            if (method_exists($value, '__toString')) {
                $value = (string) $value;
            } else {
                $value = $default;
            }
        }

        $value = trim((string) $value);
        if ($value === '') {
            $value = $default;
        }

        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

$backLink = $backLink ?? UtilityHelper::baseUrl('organizations/wash-up');
$listLink = $listLink ?? $backLink;
$washUpLink = $washUpLink ?? UtilityHelper::currentUrl();
$finalLink = $finalLink ?? UtilityHelper::currentUrl();
$lastUpdatedDisplay = $lastUpdatedDisplay ?? null;
$lastUpdatedAgo = $lastUpdatedAgo ?? null;
$canFinalize = $canFinalize ?? false;

$modeLabel = $detailMode === 'final' ? 'توصیه نهایی' : 'Wash-Up';
$secondaryModeLabel = $detailMode === 'final' ? 'Wash-Up' : 'توصیه نهایی';
$secondaryModeLink = $detailMode === 'final' ? $washUpLink : $finalLink;
$secondaryModeIcon = $detailMode === 'final' ? 'document-text-outline' : 'checkmark-done-outline';

$useLegacyWashUpLayout = $useLegacyWashUpLayout ?? false;
$legacyLayoutLink = $legacyLayoutLink ?? ($washUpLink ?? UtilityHelper::currentUrl());
$modernLayoutLink = $modernLayoutLink ?? ($washUpLink ?? UtilityHelper::currentUrl());

$globalEvaluatorStats = [];
$preparedCompetencies = [];
$toolEvaluatorColumnsMap = [];

foreach ($competencySummaries as $competency) {
    if (!is_array($competency)) {
        continue;
    }

    $examples = [];
    if (!empty($competency['examples']) && is_array($competency['examples'])) {
        $examples = array_values($competency['examples']);
    }

    $toolCells = [];
    if (!empty($competency['tool_cells']) && is_array($competency['tool_cells'])) {
        $toolCells = array_values($competency['tool_cells']);
    }

    $toolCellsById = [];
    $toolTotalsSum = 0.0;
    $toolTotalsCount = 0;
    $toolScorerTotals = [];

    foreach ($toolCells as $cell) {
        $toolId = (int) ($cell['tool_id'] ?? 0);
        if ($toolId > 0) {
            $toolCellsById[$toolId] = $cell;
        }
        $toolCountValue = (int) ($cell['count'] ?? 0);
        if (isset($cell['total']) && $cell['total'] !== null && $toolCountValue > 0) {
            $toolTotalsSum += (float) $cell['total'];
            $toolTotalsCount++;
        }
        if (!empty($cell['scorers']) && is_array($cell['scorers'])) {
            foreach ($cell['scorers'] as $scorerLabel) {
                $normalizedLabel = trim((string) $scorerLabel);
                if ($normalizedLabel === '') {
                    $normalizedLabel = 'ارزیاب نامشخص';
                }
                if ($toolId > 0) {
                    if (!isset($toolScorerTotals[$toolId])) {
                        $toolScorerTotals[$toolId] = [];
                    }
                    if (!isset($toolScorerTotals[$toolId][$normalizedLabel])) {
                        $toolScorerTotals[$toolId][$normalizedLabel] = [
                            'label' => $normalizedLabel,
                            'total' => 0.0,
                            'count' => 0,
                        ];
                    }
                }
            }
        }
    }

    $toolsWithScores = array_values(array_filter($toolCells, static function (array $cell): bool {
        $count = (int) ($cell['count'] ?? 0);
        $hasTotal = array_key_exists('total', $cell) && $cell['total'] !== null;
        $hasAverage = array_key_exists('average', $cell) && $cell['average'] !== null;
        return $count > 0 || $hasTotal || $hasAverage;
    }));
    if (empty($toolsWithScores) && !empty($toolCells)) {
        $toolsWithScores = $toolCells;
    }

    $examplesCount = (int) ($competency['examples_count'] ?? count($examples));
    $overallCount = (int) ($competency['overall_count'] ?? 0);
    $countBase = $examplesCount > 0 ? $examplesCount : $overallCount;
    $sumBase = $examplesCount > 0 ? ($competency['examples_sum'] ?? 0) : ($competency['overall_sum'] ?? 0);
    $avgBase = $examplesCount > 0 ? ($competency['examples_average'] ?? null) : ($competency['overall_average'] ?? null);
    $hasScores = $countBase > 0;
    $sumDisplay = $hasScores ? UtilityHelper::englishToPersian(number_format((float) $sumBase, 2)) : '۰.۰۰';
    $avgDisplay = $hasScores && $avgBase !== null ? UtilityHelper::englishToPersian(number_format((float) $avgBase, 2)) : '—';
    $countDisplay = UtilityHelper::englishToPersian((string) $countBase);

    $evaluatorStats = [];
    $exampleScoresTotal = 0.0;
    $exampleScoresCount = 0;
    if (!empty($examples)) {
        foreach ($examples as $exampleEntry) {
            $scores = $exampleEntry['scores'] ?? [];
            if (!is_array($scores)) {
                continue;
            }
            foreach ($scores as $scoreEntry) {
                if (!isset($scoreEntry['score'])) {
                    continue;
                }
                $label = trim((string) ($scoreEntry['scorer_label'] ?? ''));
                if ($label === '') {
                    $label = 'ارزیاب نامشخص';
                }
                $scoreValue = (float) $scoreEntry['score'];
                $exampleScoresTotal += $scoreValue;
                $exampleScoresCount++;
                $toolIdForScore = (int) ($scoreEntry['tool_id'] ?? 0);
                if ($toolIdForScore > 0) {
                    if (!isset($toolScorerTotals[$toolIdForScore])) {
                        $toolScorerTotals[$toolIdForScore] = [];
                    }
                    if (!isset($toolScorerTotals[$toolIdForScore][$label])) {
                        $toolScorerTotals[$toolIdForScore][$label] = [
                            'label' => $label,
                            'total' => 0.0,
                            'count' => 0,
                        ];
                    }
                    $toolScorerTotals[$toolIdForScore][$label]['total'] += $scoreValue;
                    $toolScorerTotals[$toolIdForScore][$label]['count']++;
                }
                if (!isset($evaluatorStats[$label])) {
                    $evaluatorStats[$label] = [
                        'total' => 0.0,
                        'count' => 0,
                    ];
                }
                $evaluatorStats[$label]['total'] += $scoreValue;
                $evaluatorStats[$label]['count']++;
            }
        }
    }

    if (empty($evaluatorStats)) {
        foreach ($toolCells as $cell) {
            $scorerNames = $cell['scorers'] ?? [];
            if (!is_array($scorerNames)) {
                continue;
            }
            foreach ($scorerNames as $scorerName) {
                $label = trim((string) $scorerName);
                if ($label === '') {
                    continue;
                }
                if (!isset($evaluatorStats[$label])) {
                    $evaluatorStats[$label] = [
                        'total' => null,
                        'count' => 0,
                    ];
                }
            }
        }
    }

    $evaluatorTotalSum = 0.0;
    $hasEvaluatorTotals = false;
    foreach ($evaluatorStats as $stat) {
        if ($stat['total'] !== null) {
            $hasEvaluatorTotals = true;
            $evaluatorTotalSum += $stat['total'];
        }
    }
    $evaluatorTotalDisplay = $hasEvaluatorTotals
        ? UtilityHelper::englishToPersian(number_format($evaluatorTotalSum, 2))
        : '—';

    foreach ($evaluatorStats as $label => $stat) {
        if (!isset($globalEvaluatorStats[$label])) {
            $globalEvaluatorStats[$label] = [
                'total' => 0.0,
                'count' => 0,
                'has_value' => false,
            ];
        }
        if ($stat['total'] !== null) {
            $globalEvaluatorStats[$label]['total'] += $stat['total'];
            $globalEvaluatorStats[$label]['count'] += $stat['count'];
            $globalEvaluatorStats[$label]['has_value'] = true;
        }
    }

    $exampleAverage = $exampleScoresCount > 0 ? $exampleScoresTotal / $exampleScoresCount : null;
    $toolEvaluatorSum = 0.0;
    $toolEvaluatorCount = 0;

    $toolScorerBreakdown = [];
    foreach ($toolScorerTotals as $toolIdKey => $scorerSet) {
        $filteredSet = array_filter($scorerSet, static function (array $entry): bool {
            $count = (int) ($entry['count'] ?? 0);
            $total = $entry['total'] ?? null;
            return $count > 0 && $total !== null;
        });
        if (empty($filteredSet)) {
            continue;
        }

        uasort($filteredSet, static function (array $a, array $b): int {
            return strcmp((string) ($a['label'] ?? ''), (string) ($b['label'] ?? ''));
        });

        $scorerEntries = [];
        foreach ($filteredSet as $entry) {
            $label = $entry['label'] ?? 'ارزیاب';
            $count = (int) ($entry['count'] ?? 0);
            $total = (float) ($entry['total'] ?? 0.0);
            $average = $count > 0 ? $total / max($count, 1) : null;
            $entry['count'] = $count;
            $entry['total'] = $total;
            $entry['average'] = $average;
            $scorerEntries[] = $entry;

            $toolEvaluatorSum += $total;
            $toolEvaluatorCount++;

            if (!isset($toolEvaluatorColumnsMap[$toolIdKey])) {
                $toolEvaluatorColumnsMap[$toolIdKey] = [];
            }
            if (!isset($toolEvaluatorColumnsMap[$toolIdKey][$label])) {
                $toolName = $toolCellsById[$toolIdKey]['tool_name'] ?? ($toolHeaderMap[$toolIdKey]['name'] ?? 'ابزار');
                $toolEvaluatorColumnsMap[$toolIdKey][$label] = [
                    'tool_id' => $toolIdKey,
                    'tool_name' => $toolName,
                    'scorer_label' => $label,
                ];
            }
        }

        if (!empty($scorerEntries)) {
            $toolScorerBreakdown[$toolIdKey] = $scorerEntries;
        }
    }

    if ($toolEvaluatorCount > 0) {
        $toolTotalsSum = $toolEvaluatorSum;
        $toolTotalsCount = $toolEvaluatorCount;
    }

    $toolTotalsAverage = $toolTotalsCount > 0 ? $toolTotalsSum / $toolTotalsCount : null;

    $agreedScoreName = 'agreed_scores[' . (int) ($competency['id'] ?? 0) . ']';
    $storedAgreedScore = $competency['agreed_score'] ?? null;
    $agreedScoreValue = '';

    if ($storedAgreedScore !== null) {
        $agreedScoreValue = number_format((float) $storedAgreedScore, 2, '.', '');
    } elseif ($toolTotalsAverage !== null) {
        $agreedScoreValue = number_format((float) $toolTotalsAverage, 2, '.', '');
    } elseif ($exampleAverage !== null) {
        $agreedScoreValue = number_format((float) $exampleAverage, 2, '.', '');
    } elseif ($avgBase !== null) {
        $agreedScoreValue = number_format((float) $avgBase, 2, '.', '');
    }

    $preparedCompetencies[] = [
        'competency' => $competency,
        'examples' => $examples,
        'tool_cells_by_id' => $toolCellsById,
        'tools_for_display' => $toolsWithScores,
        'sum_display' => $sumDisplay,
        'avg_display' => $avgDisplay,
        'count_display' => $countDisplay,
        'agreed_name' => $agreedScoreName,
        'agreed_value' => $agreedScoreValue,
        'evaluator_stats' => $evaluatorStats,
        'evaluator_total_display' => $evaluatorTotalDisplay,
        'example_average' => $exampleAverage,
        'example_scores_total' => $exampleScoresTotal,
        'example_scores_count' => $exampleScoresCount,
        'tool_totals_average' => $toolTotalsAverage,
        'tool_totals_sum' => $toolTotalsSum,
        'tool_totals_count' => $toolTotalsCount,
        'tool_scorer_breakdown' => $toolScorerBreakdown,
    ];
}

$toolEvaluatorColumns = [];
foreach ($toolHeaders as $toolHeader) {
    $toolId = (int) ($toolHeader['id'] ?? 0);
    if ($toolId <= 0) {
        continue;
    }
    if (empty($toolEvaluatorColumnsMap[$toolId])) {
        continue;
    }
    $columns = $toolEvaluatorColumnsMap[$toolId];
    uasort($columns, static function (array $a, array $b): int {
        return strcmp((string) ($a['scorer_label'] ?? ''), (string) ($b['scorer_label'] ?? ''));
    });
    foreach ($columns as $column) {
        $toolEvaluatorColumns[] = $column;
    }
}

$additional_css = $additional_css ?? [];
$additional_js = $additional_js ?? [];
$inline_styles = $inline_styles ?? '';
$inline_scripts = $inline_scripts ?? '';

$inline_styles .= <<<'CSS'
    body {
        background: #f4f6fb;
    }
    .washup-card {
        border-radius: 24px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
    }
    .washup-header-card {
        position: relative;
        overflow: hidden;
    }
    .washup-header-card::after {
        content: '';
        position: absolute;
        inset-inline-end: -60px;
        inset-block-start: -60px;
        width: 200px;
        height: 200px;
        background: radial-gradient(circle at center, rgba(59, 130, 246, 0.28), transparent 70%);
        z-index: 0;
    }
    .washup-header-card > * {
        position: relative;
        z-index: 1;
    }
    .detail-meta-list {
        font-size: 0.85rem;
        color: #475569;
        gap: 12px;
    }
    .detail-meta-list ion-icon {
        color: #2563eb;
        font-size: 1.1rem;
    }
    .summary-stat-card {
        border-radius: 20px;
        background: #f8fafc;
        padding: 18px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        color: #64748b;
        min-height: 120px;
    }
    .summary-stat-card .label {
        font-weight: 600;
        margin-bottom: 6px;
    }
    .summary-stat-card .value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1e293b;
    }
    .summary-stat-card .icon {
        font-size: 2.4rem;
        color: rgba(59, 130, 246, 0.75);
    }
    .competency-table thead th {
        background: #f1f5f9;
        color: #0f172a;
        font-weight: 600;
        border: none;
    }
    .competency-table tbody td {
        vertical-align: middle;
        border-color: #e2e8f0;
    }
    .competency-cell .title {
        font-weight: 600;
        color: #0f172a;
    }
    .competency-cell .meta {
        margin-top: 8px;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        color: #64748b;
        font-size: 0.85rem;
    }
    .competency-cell .badge {
        background: #e2e8f0;
        color: #0f172a;
        border-radius: 999px;
        padding: 3px 10px;
    }
    .competency-stat {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
    }
    .competency-stat .stat-label {
        font-size: 0.85rem;
        color: #6b7280;
    }
    .competency-stat .stat-value {
        font-size: 1.1rem;
        font-weight: 600;
        color: #0f172a;
    }
    .legacy-competency-table thead th {
        background: #f1f5f9;
        color: #0f172a;
        font-weight: 600;
        border: none;
        vertical-align: middle;
    }
    .legacy-competency-table tbody td {
        border-color: #e2e8f0;
        padding: 18px 16px;
        vertical-align: top;
    }
    .legacy-competency-cell .title {
        display: block;
        font-weight: 600;
        color: #0f172a;
        margin-bottom: 6px;
    }
    .legacy-example-list {
        margin: 0;
        padding-inline-start: 20px;
        display: grid;
        gap: 4px;
        font-size: 0.88rem;
        color: #475569;
    }
    .legacy-example-list li {
        line-height: 1.6;
    }
    .legacy-example-list li + li {
        border-top: 1px solid #e2e8f0;
        padding-top: 6px;
        margin-top: 6px;
    }
    .legacy-tool-total {
        font-size: 1.25rem;
        font-weight: 700;
        color: #0f172a;
    }
    .legacy-tool-header {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
    }
    .legacy-tool-header .tool {
        font-weight: 600;
        color: #0f172a;
        font-size: 0.85rem;
    }
    .legacy-tool-header .scorer {
        font-size: 0.82rem;
        color: #1d4ed8;
    }
    .legacy-tool-scorer-list {
        display: grid;
        gap: 6px;
    }
    .legacy-tool-scorer-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 6px 10px;
        font-size: 0.85rem;
        color: #334155;
    }
    .legacy-tool-scorer-item .label {
        font-weight: 600;
        color: #1d4ed8;
    }
    .legacy-tool-scorer-item .value {
        font-weight: 600;
        color: #0f172a;
    }
    .layout-toggle .btn {
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding-inline: 18px;
    }
    .layout-toggle ion-icon {
        font-size: 1rem;
    }
    .empty-state {
        background: #f8fafc;
        border: 1px dashed #cbd5f5;
        border-radius: 16px;
        padding: 32px;
        text-align: center;
        color: #64748b;
    }
    .action-buttons .btn {
        border-radius: 999px;
    }
    .scorer-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 999px;
        background: rgba(37, 99, 235, 0.12);
        color: #1d4ed8;
        font-size: 0.85rem;
    }
    .scorer-badge ion-icon {
        font-size: 1rem;
    }
    .final-recommendation-card {
        border: 1px solid rgba(37, 99, 235, 0.16);
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.08), rgba(37, 99, 235, 0.02));
    }
    .final-recommendation-card .meta {
        color: #475569;
        font-size: 0.85rem;
    }
    .final-recommendation-content {
        display: grid;
        gap: 18px;
        margin-top: 18px;
    }
    .recommendation-section {
        background: rgba(255, 255, 255, 0.72);
        border-radius: 16px;
        padding: 18px 20px;
        border: 1px solid rgba(148, 163, 184, 0.24);
    }
    .recommendation-section .section-title {
        font-size: 0.85rem;
        font-weight: 600;
        color: #1d4ed8;
        margin-bottom: 10px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .recommendation-section .section-body {
        font-size: 0.95rem;
        line-height: 1.7;
        color: #0f172a;
        white-space: pre-wrap;
    }
    .recommendation-empty {
        border: 1px dashed rgba(37, 99, 235, 0.28);
        border-radius: 16px;
        padding: 20px;
        background: rgba(37, 99, 235, 0.04);
        color: #1d4ed8;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .tool-chip {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 12px 16px;
        background: #f8fafc;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .tool-chip .tool-name {
        font-weight: 600;
        color: #1e293b;
    }
    .tool-chip .tool-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        font-size: 0.85rem;
        color: #64748b;
    }
    .tool-chip .mini-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.75rem;
        padding: 4px 10px;
        border-radius: 999px;
        background: rgba(37, 99, 235, 0.12);
        color: #1d4ed8;
    }
    .evaluator-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .evaluator-chip {
        border-radius: 16px;
        background: rgba(15, 23, 42, 0.04);
        padding: 10px 14px;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px;
        font-size: 0.88rem;
        color: #1e293b;
    }
    .evaluator-chip .name {
        font-weight: 600;
    }
    .evaluator-chip .score {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: #fff;
        border-radius: 999px;
        padding: 3px 10px;
        color: #0f172a;
        border: 1px solid rgba(148, 163, 184, 0.4);
    }
    .evaluator-chip .score .label {
        color: #64748b;
        font-size: 0.78rem;
    }
    .evaluator-summary-card {
        border: 1px solid #dbeafe;
        background: #eff6ff;
        border-radius: 20px;
        padding: 20px 24px;
    }
    .evaluator-summary-card h5 {
        font-size: 1rem;
        color: #1d4ed8;
        margin-bottom: 16px;
        font-weight: 700;
    }
    .evaluator-summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 12px;
    }
    .evaluator-summary-grid .evaluator-chip {
        background: #fff;
    }
    .agreed-score-input {
        width: 120px;
        max-width: 100%;
        padding: 8px 12px;
        border-radius: 12px;
        border: 1px solid rgba(148, 163, 184, 0.5);
        background: #fff;
        font-size: 0.95rem;
        text-align: center;
        color: #0f172a;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .agreed-score-input:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    }
    @media (max-width: 992px) {
        .summary-stat-card {
            flex-direction: row;
        }
    }
    @media (max-width: 768px) {
        .detail-meta-list {
            flex-direction: column;
            align-items: flex-start;
        }
        .tool-table thead {
            display: none;
        }
        .tool-table tbody tr {
            display: block;
            margin-bottom: 16px;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 16px;
            background: #f9fbff;
        }
        .tool-table tbody td {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            border: none;
            padding: 6px 0;
        }
        .tool-table tbody td::before {
            content: attr(data-label);
            font-weight: 600;
            color: #64748b;
        }
        .competency-stat {
            align-items: flex-start;
        }
    }
CSS;

include __DIR__ . '/../../layouts/organization-header.php';
include __DIR__ . '/../../layouts/organization-sidebar.php';

$navbarUser = $user;
include __DIR__ . '/../../layouts/organization-navbar.php';
?>

<div class="page-content-wrapper">
    <div class="page-content">
        <div class="row g-24">
            <div class="col-12">
                <div class="card washup-card washup-header-card shadow-sm">
                    <div class="card-body p-24">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-18">
                            <div class="flex-grow-1">
                                <div class="d-flex flex-wrap align-items-center gap-8 mb-12">
                                    <a href="<?= htmlspecialchars($backLink, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light btn-sm rounded-pill d-inline-flex align-items-center gap-6">
                                        <ion-icon name="arrow-back-outline"></ion-icon>
                                        بازگشت
                                    </a>
                                    <span class="badge bg-main-50 text-main-600 rounded-pill px-16 py-8">
                                        حالت فعلی: <?= htmlspecialchars($modeLabel, ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </div>
                                <h2 class="mb-6 text-gray-900 fw-bold">
                                    <?= htmlspecialchars($evaluationSummary['title'] ?? 'ارزیابی', ENT_QUOTES, 'UTF-8'); ?>
                                </h2>
                                <?php if (!empty($evaluationSummary['schedule_title'])): ?>
                                    <p class="mb-10 text-gray-600">
                                        <?= htmlspecialchars($evaluationSummary['schedule_title'], ENT_QUOTES, 'UTF-8'); ?>
                                    </p>
                                <?php endif; ?>
                                <?php if (!empty($visibilityContext['message'])): ?>
                                    <p class="mb-16 text-gray-500 small">
                                        <?= htmlspecialchars($visibilityContext['message'], ENT_QUOTES, 'UTF-8'); ?>
                                    </p>
                                <?php endif; ?>
                                <ul class="detail-meta-list list-unstyled d-flex flex-wrap align-items-center mb-0">
                                    <li class="d-flex align-items-center gap-8">
                                        <ion-icon name="calendar-outline"></ion-icon>
                                        <span>تاریخ: <?= htmlspecialchars($evaluationSummary['date_display'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></span>
                                    </li>
                                    <?php if (!empty($evaluateeSummary['label'])): ?>
                                        <li class="d-flex align-items-center gap-8">
                                            <ion-icon name="person-circle-outline"></ion-icon>
                                            <span>ارزیابی‌شونده: <?= htmlspecialchars($evaluateeSummary['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        </li>
                                    <?php endif; ?>
                                    <?php if (!empty($visibilityContext['role_label'])): ?>
                                        <li class="d-flex align-items-center gap-8">
                                            <ion-icon name="shield-checkmark-outline"></ion-icon>
                                            <span>نقش شما: <?= htmlspecialchars($visibilityContext['role_label'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        </li>
                                    <?php endif; ?>
                                    <?php if (!empty($evaluationSummary['general_model_label'])): ?>
                                        <li class="d-flex align-items-center gap-8">
                                            <ion-icon name="layers-outline"></ion-icon>
                                            <span>مدل عمومی: <?= htmlspecialchars($evaluationSummary['general_model_label'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        </li>
                                    <?php endif; ?>
                                    <?php if (!empty($evaluationSummary['specific_model_label'])): ?>
                                        <li class="d-flex align-items-center gap-8">
                                            <ion-icon name="grid-outline"></ion-icon>
                                            <span>مدل اختصاصی: <?= htmlspecialchars($evaluationSummary['specific_model_label'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        </li>
                                    <?php endif; ?>
                                    <?php if (!empty($lastUpdatedDisplay)): ?>
                                        <li class="d-flex align-items-center gap-8">
                                            <ion-icon name="time-outline"></ion-icon>
                                            <span>
                                                آخرین بروزرسانی: <?= htmlspecialchars($lastUpdatedDisplay, ENT_QUOTES, 'UTF-8'); ?>
                                                <?php if (!empty($lastUpdatedAgo)): ?>
                                                    (<?= htmlspecialchars($lastUpdatedAgo, ENT_QUOTES, 'UTF-8'); ?>)
                                                <?php endif; ?>
                                            </span>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                            <div class="action-buttons d-flex flex-column flex-sm-row flex-lg-column align-items-stretch gap-8">
                                <a href="<?= htmlspecialchars($secondaryModeLink, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-primary d-inline-flex align-items-center gap-6">
                                    <ion-icon name="<?= htmlspecialchars($secondaryModeIcon, ENT_QUOTES, 'UTF-8'); ?>"></ion-icon>
                                    <?= htmlspecialchars($secondaryModeLabel, ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                                <?php if ($canFinalize): ?>
                                    <a href="<?= htmlspecialchars($finalLink, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary d-inline-flex align-items-center gap-6">
                                        <ion-icon name="checkmark-done-outline"></ion-icon>
                                        ثبت / ویرایش توصیه نهایی
                                    </a>
                                <?php endif; ?>
                                <a href="<?= htmlspecialchars($listLink, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light border d-inline-flex align-items-center gap-6">
                                    <ion-icon name="list-outline"></ion-icon>
                                    بازگشت به لیست
                                </a>
                            </div>
                        </div>
                        <?php if (!empty($visibilityContext['user_display'])): ?>
                            <div class="mt-16 text-gray-500 small">
                                مشاهده‌کننده: <?= washup_escape_html($visibilityContext['user_display'], 'کاربر سازمان'); ?>
                                <?php if (!empty($visibilityContext['can_view_all'])): ?>
                                    <span class="ms-8 badge bg-success-50 text-success-700 rounded-pill px-12 py-6">دسترسی کامل</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($scorerSummary) && is_array($scorerSummary)): ?>
                            <div class="d-flex flex-wrap align-items-center gap-8 mt-20">
                                <?php foreach ($scorerSummary as $scorer): ?>
                                    <span class="scorer-badge">
                                        <ion-icon name="person-outline"></ion-icon>
                                        <?= washup_escape_html($scorer, 'ارزیاب'); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if (!empty($successMessage) || !empty($errorMessage) || !empty($warningMessage) || !empty($infoMessage)): ?>
                <div class="col-12">
                    <?php if (!empty($successMessage)): ?>
                        <div class="alert alert-success rounded-16 d-flex align-items-center gap-12" role="alert">
                            <ion-icon name="checkmark-circle-outline"></ion-icon>
                            <span><?= washup_escape_html($successMessage); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($errorMessage)): ?>
                        <div class="alert alert-danger rounded-16 d-flex align-items-center gap-12" role="alert">
                            <ion-icon name="alert-circle-outline"></ion-icon>
                            <span><?= washup_escape_html($errorMessage); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($warningMessage)): ?>
                        <div class="alert alert-warning rounded-16 d-flex align-items-center gap-12" role="alert">
                            <ion-icon name="warning-outline"></ion-icon>
                            <span><?= washup_escape_html($warningMessage); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($infoMessage)): ?>
                        <div class="alert alert-info rounded-16 d-flex align-items-center gap-12" role="alert">
                            <ion-icon name="information-circle-outline"></ion-icon>
                            <span><?= washup_escape_html($infoMessage); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($pageMessages)): ?>
                <div class="col-12">
                    <?php foreach ($pageMessages as $message): ?>
                        <?php
                            $type = $message['type'] ?? 'info';
                            $text = $message['text'] ?? '';
                            $alertClass = 'alert-info';
                            if ($type === 'warning') {
                                $alertClass = 'alert-warning';
                            } elseif ($type === 'success') {
                                $alertClass = 'alert-success';
                            } elseif ($type === 'danger' || $type === 'error') {
                                $alertClass = 'alert-danger';
                            }
                        ?>
                        <div class="alert <?= washup_escape_html($alertClass, 'alert-info'); ?> rounded-16" role="alert">
                            <?= washup_escape_html($text); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($toolsWithoutScores)): ?>
                <div class="col-12">
                    <div class="alert alert-warning rounded-16 d-flex align-items-center gap-12" role="alert">
                        <ion-icon name="list-outline"></ion-icon>
                        <span>
                            برای برخی ابزارها هنوز امتیازی ثبت نشده است:
                            <?php
                                $toolNames = array_map(static function ($tool) {
                                    return $tool['name'] ?? '';
                                }, $toolsWithoutScores);
                                $toolNames = array_filter($toolNames);
                            ?>
                            <?= washup_escape_html(implode('، ', $toolNames)); ?>
                        </span>
                    </div>
                </div>
            <?php endif; ?>

            <?php
                $statCards = [
                    [
                        'label' => 'کل امتیازها',
                        'value' => UtilityHelper::englishToPersian((string) ($detailStats['total_scores'] ?? 0)),
                        'icon' => 'stats-chart-outline',
                    ],
                    [
                        'label' => 'میانگین کلی',
                        'value' => $detailStats['average_score'] !== null ? UtilityHelper::englishToPersian((string) $detailStats['average_score']) : '—',
                        'icon' => 'speedometer-outline',
                    ],
                    [
                        'label' => 'تعداد ابزارها',
                        'value' => UtilityHelper::englishToPersian((string) ($detailStats['tools_total'] ?? 0)),
                        'icon' => 'construct-outline',
                    ],
                    [
                        'label' => 'ابزارهای دارای امتیاز',
                        'value' => UtilityHelper::englishToPersian((string) ($detailStats['tools_with_scores'] ?? 0)),
                        'icon' => 'checkmark-done-outline',
                    ],
                    [
                        'label' => 'شایستگی‌ها',
                        'value' => UtilityHelper::englishToPersian((string) ($detailStats['competencies_total'] ?? 0)),
                        'icon' => 'sparkles-outline',
                    ],
                ];
            ?>
            <?php foreach ($statCards as $card): ?>
                <div class="col-12 col-sm-6 col-xl-4">
                    <div class="summary-stat-card shadow-sm">
                        <div>
                            <div class="label"><?= htmlspecialchars($card['label'], ENT_QUOTES, 'UTF-8'); ?></div>
                            <div class="value"><?= htmlspecialchars($card['value'], ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <div class="icon">
                            <ion-icon name="<?= htmlspecialchars($card['icon'], ENT_QUOTES, 'UTF-8'); ?>"></ion-icon>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if ($detailMode === 'final'): ?>
                <div class="col-12">
                    <div class="card washup-card shadow-sm final-recommendation-card">
                        <div class="card-body p-24">
                            <?php $hasFinalMeta = is_array($finalRecommendation) && (!empty($finalRecommendation['updated_at_display']) || !empty($finalRecommendation['updated_by'])); ?>
                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-16">
                                <div>
                                    <h4 class="mb-4 text-gray-900 fw-semibold d-flex align-items-center gap-8">
                                        <ion-icon name="ribbon-outline"></ion-icon>
                                        توصیه نهایی برای <?= washup_escape_html($evaluateeSummary['label'] ?? 'ارزیابی‌شونده'); ?>
                                    </h4>
                                    <?php if ($hasFinalMeta): ?>
                                        <div class="meta d-flex flex-wrap gap-12">
                                            <?php if (!empty($finalRecommendation['updated_at_display'])): ?>
                                                <span class="d-inline-flex align-items-center gap-6">
                                                    <ion-icon name="time-outline"></ion-icon>
                                                    آخرین بروزرسانی: <?= washup_escape_html($finalRecommendation['updated_at_display']); ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if (!empty($finalRecommendation['updated_by'])): ?>
                                                <span class="d-inline-flex align-items-center gap-6">
                                                    <ion-icon name="person-circle-outline"></ion-icon>
                                                    ثبت‌کننده: <?= washup_escape_html($finalRecommendation['updated_by']); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php if ($canFinalize): ?>
                                    <a href="<?= htmlspecialchars($finalLink, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-primary rounded-pill d-inline-flex align-items-center gap-6">
                                        <ion-icon name="create-outline"></ion-icon>
                                        ویرایش توصیه
                                    </a>
                                <?php endif; ?>
                            </div>
                            <div class="final-recommendation-content">
                                <?php if ($finalRecommendation): ?>
                                    <div class="recommendation-section">
                                        <div class="section-title">
                                            <ion-icon name="chatbubble-ellipses-outline"></ion-icon>
                                            خلاصه و توصیه نهایی
                                        </div>
                                        <div class="section-body">
                                            <?= nl2br(washup_escape_html($finalRecommendation['recommendation_text'] ?? '', '—')); ?>
                                        </div>
                                    </div>
                                    <div class="recommendation-section">
                                        <div class="section-title">
                                            <ion-icon name="trending-up-outline"></ion-icon>
                                            پیشنهادهای توسعه
                                        </div>
                                        <div class="section-body">
                                            <?= nl2br(washup_escape_html($finalRecommendation['development_text'] ?? '', '—')); ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="recommendation-empty">
                                        <ion-icon name="information-circle-outline"></ion-icon>
                                        <div>
                                            هنوز توصیه نهایی برای این ارزیابی‌شونده ثبت نشده است. از دکمه «ثبت / ویرایش توصیه نهایی» برای ثبت جمع‌بندی استفاده کنید.
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="col-12">
                <div class="card washup-card shadow-sm">
                    <div class="card-body p-24">
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-20 gap-12">
                            <div>
                                <h4 class="mb-2 text-gray-900 fw-semibold">خلاصه ابزارهای ارزیابی</h4>
                                <div class="text-gray-500 small">وضعیت هر ابزار و میزان مشارکت ارزیاب‌ها</div>
                            </div>
                        </div>

                        <?php if (!empty($toolHeaders)): ?>
                            <div class="table-responsive">
                                <table class="table tool-table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>عنوان ابزار</th>
                                            <th>میانگین امتیاز</th>
                                            <th>تعداد امتیاز</th>
                                            <th>شایستگی‌های پوشش داده‌شده</th>
                                            <th>ارزیاب‌ها</th>
                                            <th>آخرین بروزرسانی</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($toolHeaders as $tool): ?>
                                            <tr>
                                                <td data-label="عنوان ابزار">
                                                    <span class="fw-semibold text-gray-900"><?= htmlspecialchars($tool['name'] ?? 'ابزار', ENT_QUOTES, 'UTF-8'); ?></span>
                                                    <?php if (!empty($tool['question_type'])): ?>
                                                        <div class="text-xs text-gray-500 mt-4">نوع سوال: <?= htmlspecialchars($tool['question_type'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td data-label="میانگین امتیاز">
                                                    <span class="pill-badge info">
                                                        <?= htmlspecialchars($tool['average'] !== null ? UtilityHelper::englishToPersian((string) $tool['average']) : '—', ENT_QUOTES, 'UTF-8'); ?>
                                                    </span>
                                                </td>
                                                <td data-label="تعداد امتیاز">
                                                    <span class="pill-badge success">
                                                        <?= htmlspecialchars(UtilityHelper::englishToPersian((string) ($tool['scores_count'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>
                                                    </span>
                                                </td>
                                                <td data-label="شایستگی‌های پوشش داده‌شده">
                                                    <span class="tool-badge">
                                                        <?= htmlspecialchars(UtilityHelper::englishToPersian((string) ($tool['competency_total'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?> شایستگی
                                                    </span>
                                                </td>
                                                <td data-label="ارزیاب‌ها">
                                                    <?php if (!empty($tool['scorers'])): ?>
                                                        <div class="d-flex flex-wrap gap-6">
                                                            <?php foreach ($tool['scorers'] as $scorer): ?>
                                                                <span class="scorer-badge">
                                                                    <ion-icon name="person-outline"></ion-icon>
                                                                    <?= htmlspecialchars($scorer, ENT_QUOTES, 'UTF-8'); ?>
                                                                </span>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-muted small">اطلاعاتی موجود نیست</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td data-label="آخرین بروزرسانی">
                                                    <?= htmlspecialchars($tool['last_updated_display'] ?? '—', ENT_QUOTES, 'UTF-8'); ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <ion-icon name="pulse-outline" class="fs-3 mb-8"></ion-icon>
                                <p class="mb-0">هیچ ابزار فعالی برای نمایش وجود ندارد.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-16 gap-12">
                    <h4 class="mb-0 text-gray-900 fw-semibold">شایستگی‌ها و جزئیات امتیازدهی</h4>
                    <div class="layout-toggle d-flex align-items-center gap-8">
                        <?php if ($useLegacyWashUpLayout): ?>
                            <a href="<?= htmlspecialchars($modernLayoutLink, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-primary btn-sm">
                                <ion-icon name="sparkles-outline"></ion-icon>
                                بازگشت به نمای جدید
                            </a>
                            <a href="<?= htmlspecialchars($listLink, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light btn-sm">
                                <ion-icon name="list-outline"></ion-icon>
                                بازگشت به لیست
                            </a>
                        <?php else: ?>
                            <a href="<?= htmlspecialchars($legacyLayoutLink, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-secondary btn-sm">
                                <ion-icon name="time-outline"></ion-icon>
                                نمایش نسخه قدیم Wash-Up
                            </a>
                            <a href="<?= htmlspecialchars($listLink, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light btn-sm">
                                <ion-icon name="list-outline"></ion-icon>
                                بازگشت به لیست
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if (!empty($preparedCompetencies)): ?>
                <div class="col-12">
                    <?php if ($canEditAgreedScores): ?>
                        <form action="<?= htmlspecialchars($agreedScoresAction, ENT_QUOTES, 'UTF-8'); ?>" method="post" class="agreed-score-form<?= $useLegacyWashUpLayout ? ' mb-3' : ''; ?>">
                    <?php endif; ?>
                        <div class="card washup-card shadow-sm">
                            <div class="card-body p-24">
                                <?php if ($canEditAgreedScores): ?>
                                    <input type="hidden" name="evaluation_id" value="<?= htmlspecialchars((string) ($evaluationSummary['id'] ?? 0), ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="evaluatee_id" value="<?= htmlspecialchars((string) ($evaluateeSummary['id'] ?? 0), ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="layout" value="<?= htmlspecialchars($useLegacyWashUpLayout ? 'legacy' : 'modern', ENT_QUOTES, 'UTF-8'); ?>">
                                <?php endif; ?>
                                <div class="table-responsive">
                                    <?php if ($useLegacyWashUpLayout): ?>
                                        <table class="table legacy-competency-table align-middle mb-0 text-center">
                                            <thead>
                                                <tr>
                                                    <th>شایستگی</th>
                                                    <th>مصداق‌های رفتاری</th>
                                                    <?php if (!empty($toolEvaluatorColumns)): ?>
                                                        <?php foreach ($toolEvaluatorColumns as $column): ?>
                                                            <th>
                                                                <div class="legacy-tool-header">
                                                                    <span class="tool"><?= htmlspecialchars($column['tool_name'] ?? 'ابزار', ENT_QUOTES, 'UTF-8'); ?></span>
                                                                    <span class="scorer"><?= htmlspecialchars($column['scorer_label'] ?? 'ارزیاب', ENT_QUOTES, 'UTF-8'); ?></span>
                                                                </div>
                                                            </th>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <?php foreach ($toolHeaders as $toolHeader): ?>
                                                            <th>
                                                                <div><?= htmlspecialchars($toolHeader['name'] ?? 'ابزار', ENT_QUOTES, 'UTF-8'); ?></div>
                                                                <?php if (!empty($toolHeader['scorers'])): ?>
                                                                    <small class="text-muted"><?= htmlspecialchars(implode('، ', $toolHeader['scorers']), ENT_QUOTES, 'UTF-8'); ?></small>
                                                                <?php endif; ?>
                                                            </th>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                    <th>میانگین امتیاز ابزارها</th>
                                                    <th>امتیاز توافقی</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($preparedCompetencies as $item): ?>
                                                    <?php
                                                        $competency = $item['competency'];
                                                        $examples = $item['examples'];
                                                        $toolCellsById = $item['tool_cells_by_id'];
                                                        $toolScorerBreakdownMap = $item['tool_scorer_breakdown'] ?? [];
                                                    ?>
                                                    <tr>
                                                        <td class="legacy-competency-cell text-start">
                                                            <span class="title"><?= htmlspecialchars($competency['title'] ?? 'شایستگی', ENT_QUOTES, 'UTF-8'); ?></span>
                                                        </td>
                                                        <td class="text-start">
                                                            <?php if (!empty($examples)): ?>
                                                                <ul class="legacy-example-list">
                                                                    <?php foreach ($examples as $example): ?>
                                                                        <li><?= htmlspecialchars($example['text'] ?? '', ENT_QUOTES, 'UTF-8'); ?></li>
                                                                    <?php endforeach; ?>
                                                                </ul>
                                                            <?php else: ?>
                                                                <span class="text-muted">مصداقی ثبت نشده است.</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <?php if (!empty($toolEvaluatorColumns)): ?>
                                                            <?php
                                                                $scorerLookup = [];
                                                                foreach ($toolScorerBreakdownMap as $lookupToolId => $entries) {
                                                                    foreach ($entries as $entry) {
                                                                        $lookupLabel = $entry['label'] ?? 'ارزیاب';
                                                                        $scorerLookup[$lookupToolId][$lookupLabel] = $entry;
                                                                    }
                                                                }
                                                            ?>
                                                            <?php foreach ($toolEvaluatorColumns as $column): ?>
                                                                <?php
                                                                    $toolId = (int) ($column['tool_id'] ?? 0);
                                                                    $scorerLabel = $column['scorer_label'] ?? 'ارزیاب';
                                                                    $scoreEntry = $toolId > 0 && isset($scorerLookup[$toolId][$scorerLabel])
                                                                        ? $scorerLookup[$toolId][$scorerLabel]
                                                                        : null;
                                                                    $totalValue = $scoreEntry !== null && isset($scoreEntry['total']) ? (float) $scoreEntry['total'] : null;
                                                                ?>
                                                                <td class="legacy-tool-cell">
                                                                    <?php if ($totalValue !== null): ?>
                                                                        <div class="legacy-tool-total">
                                                                            <?= UtilityHelper::englishToPersian(number_format($totalValue, 2)); ?>
                                                                        </div>
                                                                    <?php else: ?>
                                                                        <span class="text-muted">—</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                            <?php endforeach; ?>
                                                        <?php else: ?>
                                                            <?php foreach ($toolHeaders as $toolHeader): ?>
                                                                <?php
                                                                    $toolId = (int) ($toolHeader['id'] ?? 0);
                                                                    $cell = $toolId > 0 ? ($toolCellsById[$toolId] ?? null) : null;
                                                                    $totalValue = $cell !== null && isset($cell['total']) ? (float) $cell['total'] : null;
                                                                ?>
                                                                <td class="legacy-tool-cell">
                                                                    <?php if ($cell !== null && $totalValue !== null): ?>
                                                                        <div class="legacy-tool-total">
                                                                            <?= UtilityHelper::englishToPersian(number_format($totalValue, 2)); ?>
                                                                        </div>
                                                                    <?php else: ?>
                                                                        <span class="text-muted">—</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                        <td>
                                                            <?php if (isset($item['tool_totals_average']) && $item['tool_totals_average'] !== null): ?>
                                                                <span class="legacy-tool-total">
                                                                    <?= UtilityHelper::englishToPersian(number_format((float) $item['tool_totals_average'], 2)); ?>
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="text-muted">—</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <input
                                                                type="number"
                                                                step="0.01"
                                                                class="agreed-score-input"
                                                                name="<?= htmlspecialchars($item['agreed_name'], ENT_QUOTES, 'UTF-8'); ?>"
                                                                value="<?= htmlspecialchars($item['agreed_value'], ENT_QUOTES, 'UTF-8'); ?>"
                                                                placeholder="—"
                                                                inputmode="decimal"
                                                                dir="ltr"
                                                                <?= $canEditAgreedScores ? '' : ' readonly'; ?>
                                                            />
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    <?php else: ?>
                                        <table class="table competency-table align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>شایستگی</th>
                                                    <th>ابزارهای دارای امتیاز</th>
                                                    <th>جزئیات ارزیاب‌ها</th>
                                                    <th>جمع امتیاز مصداق‌ها</th>
                                                    <th>میانگین امتیاز</th>
                                                    <th>تعداد امتیاز</th>
                                                    <th>امتیاز کل ارزیاب‌ها</th>
                                                    <th>امتیاز توافقی</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($preparedCompetencies as $item): ?>
                                                    <?php
                                                        $competency = $item['competency'];
                                                        $toolsWithScores = $item['tools_for_display'];
                                                        $evaluatorStats = $item['evaluator_stats'];
                                                    ?>
                                                    <tr>
                                                        <td class="competency-cell">
                                                            <span class="title"><?= htmlspecialchars($competency['title'] ?? 'شایستگی', ENT_QUOTES, 'UTF-8'); ?></span>
                                                            <div class="meta">
                                                                <?php if (!empty($competency['dimension'])): ?>
                                                                    <span class="badge"><?= htmlspecialchars($competency['dimension'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                                <?php endif; ?>
                                                                <?php if (!empty($competency['code'])): ?>
                                                                    <span class="badge">کد: <?= htmlspecialchars($competency['code'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                                <?php endif; ?>
                                                                <?php if (!empty($item['examples'])): ?>
                                                                    <span class="badge"><?= UtilityHelper::englishToPersian((string) count($item['examples'])); ?> نمونه رفتاری</span>
                                                                <?php endif; ?>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <?php if (!empty($toolsWithScores)): ?>
                                                                <div class="d-grid gap-2">
                                                                    <?php foreach ($toolsWithScores as $cell): ?>
                                                                        <div class="tool-chip">
                                                                            <span class="tool-name"><?= htmlspecialchars($cell['tool_name'] ?? 'ابزار', ENT_QUOTES, 'UTF-8'); ?></span>
                                                                            <div class="tool-meta">
                                                                                <?php if (!empty($cell['total'])): ?>
                                                                                    <span class="mini-badge">
                                                                                        <ion-icon name="calculator-outline"></ion-icon>
                                                                                        جمع: <?= UtilityHelper::englishToPersian(number_format((float) $cell['total'], 2)); ?>
                                                                                    </span>
                                                                                <?php endif; ?>
                                                                                <?php if (!empty($cell['average'])): ?>
                                                                                    <span class="mini-badge">
                                                                                        <ion-icon name="speedometer-outline"></ion-icon>
                                                                                        میانگین: <?= UtilityHelper::englishToPersian(number_format((float) $cell['average'], 2)); ?>
                                                                                    </span>
                                                                                <?php endif; ?>
                                                                                <?php if (!empty($cell['count'])): ?>
                                                                                    <span class="mini-badge">
                                                                                        <ion-icon name="git-branch-outline"></ion-icon>
                                                                                        تعداد: <?= UtilityHelper::englishToPersian((string) $cell['count']); ?>
                                                                                    </span>
                                                                                <?php endif; ?>
                                                                            </div>
                                                                            <?php if (!empty($cell['scorers'])): ?>
                                                                                <div class="tool-meta">
                                                                                    <ion-icon name="people-circle-outline"></ion-icon>
                                                                                    <?= htmlspecialchars(implode('، ', $cell['scorers']), ENT_QUOTES, 'UTF-8'); ?>
                                                                                </div>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                            <?php else: ?>
                                                                <span class="text-muted small">ابزاری برای این شایستگی امتیازدهی نشده است.</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?php if (!empty($evaluatorStats)): ?>
                                                                <div class="d-grid gap-2">
                                                                    <?php foreach ($evaluatorStats as $label => $stat): ?>
                                                                        <?php
                                                                            $totalEvaluator = $stat['total'];
                                                                            $countEvaluator = (int) ($stat['count'] ?? 0);
                                                                            $avgEvaluator = $countEvaluator > 0 && $totalEvaluator !== null
                                                                                ? round($totalEvaluator / max($countEvaluator, 1), 2)
                                                                                : null;
                                                                        ?>
                                                                        <div class="evaluator-chip">
                                                                            <span class="name">
                                                                                <ion-icon name="person-circle-outline"></ion-icon>
                                                                                <?= washup_escape_html($label, 'ارزیاب'); ?>
                                                                            </span>
                                                                            <span class="score">
                                                                                <span class="label">جمع</span>
                                                                                <span><?= $totalEvaluator !== null ? UtilityHelper::englishToPersian(number_format((float) $totalEvaluator, 2)) : '—'; ?></span>
                                                                            </span>
                                                                            <span class="score">
                                                                                <span class="label">میانگین</span>
                                                                                <span><?= $avgEvaluator !== null ? UtilityHelper::englishToPersian(number_format((float) $avgEvaluator, 2)) : '—'; ?></span>
                                                                            </span>
                                                                            <span class="score">
                                                                                <span class="label">تعداد</span>
                                                                                <span><?= UtilityHelper::englishToPersian((string) $countEvaluator); ?></span>
                                                                            </span>
                                                                        </div>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                            <?php else: ?>
                                                                <span class="text-muted small">اطلاعاتی از ارزیاب‌ها موجود نیست.</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <div class="competency-stat">
                                                                <span class="stat-label">جمع امتیاز</span>
                                                                <span class="stat-value"><?= htmlspecialchars($item['sum_display'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="competency-stat">
                                                                <span class="stat-label">میانگین امتیاز</span>
                                                                <span class="stat-value"><?= htmlspecialchars($item['avg_display'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="competency-stat">
                                                                <span class="stat-label">تعداد امتیاز</span>
                                                                <span class="stat-value"><?= htmlspecialchars($item['count_display'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="competency-stat">
                                                                <span class="stat-label">مجموع ارزیاب‌ها</span>
                                                                <span class="stat-value"><?= htmlspecialchars($item['evaluator_total_display'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="competency-stat">
                                                                <span class="stat-label">امتیاز توافقی</span>
                                                                <input
                                                                    type="number"
                                                                    step="0.01"
                                                                    class="agreed-score-input"
                                                                    name="<?= htmlspecialchars($item['agreed_name'], ENT_QUOTES, 'UTF-8'); ?>"
                                                                    value="<?= htmlspecialchars($item['agreed_value'], ENT_QUOTES, 'UTF-8'); ?>"
                                                                    placeholder="—"
                                                                    inputmode="decimal"
                                                                    dir="ltr"
                                                                    <?= $canEditAgreedScores ? '' : ' readonly'; ?>
                                                                />
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    <?php endif; ?>
                                </div>
                                <?php if ($canEditAgreedScores): ?>
                                    <div class="d-flex flex-wrap justify-content-between align-items-center mt-24 gap-12">
                                        <div class="text-muted small">
                                            در صورت نیاز می‌توانید امتیاز توافقی هر شایستگی را به‌صورت دستی تنظیم کرده و سپس ذخیره کنید.
                                        </div>
                                        <button type="submit" class="btn btn-primary rounded-pill px-24">
                                            <ion-icon name="save-outline"></ion-icon>
                                            ذخیره امتیازهای توافقی
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php if ($canEditAgreedScores): ?>
                        </form>
                    <?php endif; ?>
                </div>

                <?php
                    $summaryEvaluators = array_filter($globalEvaluatorStats, static function (array $stat): bool {
                        return !empty($stat['has_value']) && ($stat['total'] ?? 0) !== 0;
                    });
                ?>
                <?php if (!empty($summaryEvaluators)): ?>
                    <div class="col-12">
                        <div class="evaluator-summary-card shadow-sm">
                            <h5>خلاصه امتیازات ارزیاب‌ها</h5>
                            <div class="evaluator-summary-grid">
                                <?php foreach ($summaryEvaluators as $label => $stat): ?>
                                    <?php
                                        $total = (float) ($stat['total'] ?? 0);
                                        $count = (int) ($stat['count'] ?? 0);
                                        $avg = $count > 0 ? round($total / max($count, 1), 2) : null;
                                        $totalDisplay = UtilityHelper::englishToPersian(number_format($total, 2));
                                        $avgDisplay = $avg !== null ? UtilityHelper::englishToPersian(number_format($avg, 2)) : '—';
                                        $countDisplay = UtilityHelper::englishToPersian((string) $count);
                                    ?>
                                    <div class="evaluator-chip">
                                        <span class="name"><ion-icon name="person-circle-outline"></ion-icon> <?= washup_escape_html($label, 'ارزیاب'); ?></span>
                                        <span class="score"><span class="label">جمع</span><span><?= washup_escape_html($totalDisplay); ?></span></span>
                                        <span class="score"><span class="label">میانگین</span><span><?= washup_escape_html($avgDisplay); ?></span></span>
                                        <span class="score"><span class="label">تعداد</span><span><?= washup_escape_html($countDisplay, '۰'); ?></span></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="empty-state">
                        <ion-icon name="time-outline" class="fs-3 mb-8"></ion-icon>
                        <p class="mb-0">داده‌ای برای نمایش جزئیات Wash-Up یافت نشد. پس از ثبت امتیازها، این بخش تکمیل خواهد شد.</p>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/organization-footer.php'; ?>

<script>
    (function () {
        function normalizePersianNumber(value) {
            if (typeof value !== 'string' || value === '') {
                return value;
            }

            var persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
            var arabicDigits = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];

            var normalized = '';
            for (var i = 0; i < value.length; i++) {
                var char = value.charAt(i);
                var indexPersian = persianDigits.indexOf(char);
                if (indexPersian !== -1) {
                    normalized += indexPersian.toString();
                    continue;
                }
                var indexArabic = arabicDigits.indexOf(char);
                if (indexArabic !== -1) {
                    normalized += indexArabic.toString();
                    continue;
                }
                normalized += char;
            }

            return normalized;
        }

        function handleAgreedScoreInput(event) {
            var target = event.target;
            if (!target || target.value === '') {
                return;
            }

            var normalized = normalizePersianNumber(target.value);
            if (normalized !== target.value) {
                var selectionStart = target.selectionStart;
                target.value = normalized;
                if (typeof selectionStart === 'number') {
                    target.setSelectionRange(selectionStart, selectionStart);
                }
            }
        }

        var agreedInputs = document.querySelectorAll('.agreed-score-input');
        if (agreedInputs && agreedInputs.length > 0) {
            agreedInputs.forEach(function (input) {
                input.addEventListener('input', handleAgreedScoreInput);
                input.addEventListener('change', function (event) {
                    var normalized = normalizePersianNumber(event.target.value);
                    if (normalized !== event.target.value) {
                        event.target.value = normalized;
                    }
                });
            });
        }
    })();
</script>
