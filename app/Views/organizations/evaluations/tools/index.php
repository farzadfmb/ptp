<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../../../Helpers/autoload.php';
}

$title = $title ?? 'ابزارهای ارزیابی';
$user = (class_exists('AuthHelper') && AuthHelper::getUser()) ? AuthHelper::getUser() : [
    'name' => 'کاربر سازمان',
    'email' => 'organization@example.com',
];
$additional_css = $additional_css ?? [];
$additional_js = $additional_js ?? [];
$inline_styles = $inline_styles ?? '';
$inline_scripts = $inline_scripts ?? '';

$additional_css[] = 'https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css';
$additional_css[] = 'https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css';
$additional_js[] = 'https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js';
$additional_js[] = 'https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js';
$additional_js[] = 'https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js';
$additional_js[] = 'https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js';
$additional_js[] = 'public/assets/js/datatables-init.js';

$evaluationTools = $evaluationTools ?? [];
$successMessage = $successMessage ?? null;
$errorMessage = $errorMessage ?? null;

$inline_styles .= <<<'CSS'
    body {
        background: #f5f7fb;
    }
    .evaluation-tools-card {
        border-radius: 24px;
        border: 1px solid #e4e9f2;
    }
    .evaluation-tools-header h2 {
        font-size: 22px;
        font-weight: 600;
    }
    .evaluation-tools-header p {
        color: #475467;
    }
    .evaluation-tools-table thead th,
    .evaluation-tools-table tbody td {
        white-space: nowrap;
        vertical-align: middle;
    }
    .evaluation-tools-table tbody tr {
        opacity: 1 !important;
        visibility: visible !important;
        transform: none !important;
        display: table-row !important;
    }
    .evaluation-tools-table .table-actions {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .evaluation-tools-table .table-actions .btn {
        width: 36px;
        height: 36px;
        padding: 0;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .evaluation-tools-table .table-actions ion-icon {
        font-size: 20px;
    }
    .evaluation-tools-table .questions-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 4px 12px;
        border-radius: 999px;
        font-size: 13px;
        background: #eef2ff;
        color: #363f72;
        min-width: 40px;
    }
    .status-icon {
        width: 32px;
        height: 32px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }
    .status-icon.success {
        background: #ecfdf3;
        color: #027a48;
    }
    .status-icon.primary {
        background: #eff8ff;
        color: #175cd3;
    }
    .status-icon.muted {
        background: #f2f4f7;
        color: #475467;
    }
    .status-icon.warning {
        background: #fff6ed;
        color: #b93815;
    }
    .btn-placeholder {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        cursor: not-allowed;
    }
    .btn-question-manage {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 6px 16px;
        font-weight: 500;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .btn-question-manage ion-icon {
        font-size: 18px;
    }
    .btn-question-manage:focus-visible {
        outline: 3px solid rgba(124, 58, 237, 0.3);
        outline-offset: 2px;
    }
    .btn-question-purple {
        background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        color: #fff;
        border: none;
        box-shadow: 0 8px 20px rgba(124, 58, 237, 0.25);
    }
    .btn-question-purple:hover {
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 10px 24px rgba(124, 58, 237, 0.35);
    }
    .btn-question-red {
        background: linear-gradient(135deg, #f87171, #ef4444);
        color: #fff;
        border: none;
        box-shadow: 0 8px 20px rgba(239, 68, 68, 0.25);
    }
    .btn-question-red:disabled,
    .btn-question-red.disabled {
        opacity: 0.8;
        cursor: not-allowed;
        box-shadow: none;
        transform: none;
    }
    .evaluation-tools-empty {
        text-align: center;
        color: #475467;
        font-size: 16px;
    }
CSS;

$tableOptions = [
    'paging' => true,
    'lengthChange' => true,
    'pageLength' => 10,
    'responsive' => true,
    'lengthMenu' => [[10, 25, 50, -1], ['۱۰', '۲۵', '۵۰', 'همه']],
    'order' => [[1, 'asc']],
    'columnDefs' => [
        ['targets' => 0, 'orderable' => false, 'searchable' => false],
        ['targets' => 6, 'orderable' => false, 'searchable' => false],
    ],
];

$tableOptionsAttr = htmlspecialchars(json_encode($tableOptions, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');

$renderStatusIcon = static function (bool $condition, string $trueModifier, string $falseModifier, string $trueTitle, string $falseTitle): string {
    $modifier = $condition ? $trueModifier : $falseModifier;
    $title = $condition ? $trueTitle : $falseTitle;
    $icon = $condition ? 'checkmark-outline' : 'close-outline';
    $aria = $condition ? 'بله' : 'خیر';

    return sprintf(
        '<span class="status-icon %s" title="%s"><ion-icon name="%s"></ion-icon><span class="visually-hidden">%s</span></span>',
        htmlspecialchars($modifier, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($title, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($icon, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($aria, ENT_QUOTES, 'UTF-8')
    );
};

include __DIR__ . '/../../../layouts/organization-header.php';
include __DIR__ . '/../../../layouts/organization-sidebar.php';

$navbarUser = $user;
?>

<?php include __DIR__ . '/../../../layouts/organization-navbar.php'; ?>

<div class="page-content-wrapper">
    <div class="page-content">
        <div class="row g-4">
            <div class="col-12">
                <div class="card evaluation-tools-card shadow-sm h-100">
                    <div class="card-body p-24">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-16 mb-24 evaluation-tools-header">
                            <div>
                                <h2 class="mb-6 text-gray-900">ابزارهای ارزیابی</h2>
                                <p class="text-gray-500 mb-0">ابزارهای ارزیابی سازمان را مدیریت کنید و ابزارهای جدید بسازید.</p>
                            </div>
                            <div>
                                <a href="<?= UtilityHelper::baseUrl('organizations/evaluation-tools/create'); ?>" class="btn btn-main">
                                    <ion-icon name="add-circle-outline"></ion-icon>
                                    افزودن ابزار جدید
                                </a>
                            </div>
                        </div>

                        <?php if (!empty($successMessage)): ?>
                            <div class="alert alert-success rounded-16 d-flex align-items-center gap-12" role="alert">
                                <ion-icon name="checkmark-circle-outline"></ion-icon>
                                <span><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($errorMessage)): ?>
                            <div class="alert alert-danger rounded-16 d-flex align-items-center gap-12" role="alert">
                                <ion-icon name="warning-outline"></ion-icon>
                                <span><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        <?php endif; ?>

                        <div class="table-responsive rounded-16 border border-gray-100 mt-3">
                            <table class="table align-middle mb-0 evaluation-tools-table js-data-table" data-datatable-options="<?= $tableOptionsAttr; ?>">
                                <thead class="bg-gray-100 text-gray-700">
                                    <tr>
                                        <th scope="col" class="no-sort no-search">عملیات</th>
                                        <th scope="col">نام</th>
                                        <th scope="col">نوع سوالات</th>
                                        <th scope="col">مدت زمان (دقیقه)</th>
                                        <th scope="col">عدم اجبار در جواب دادن</th>
                                        <th scope="col">تعداد سوالات</th>
                                        <th scope="col" class="no-sort no-search">تعریف سوالات</th>
                                        <th scope="col">آزمون می‌باشد؟</th>
                                        <th scope="col">فقط ثبت نتیجه می‌باشد؟</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($evaluationTools)): ?>
                                        <?php foreach ($evaluationTools as $tool): ?>
                                            <?php
                                                $toolId = (int) ($tool['id'] ?? 0);
                                                $toolIdAttr = htmlspecialchars((string) $toolId, ENT_QUOTES, 'UTF-8');
                                                $nameValue = htmlspecialchars($tool['name'] ?? '-', ENT_QUOTES, 'UTF-8');
                                                $typeValue = htmlspecialchars($tool['question_type'] ?? '-', ENT_QUOTES, 'UTF-8');
                                                $durationRaw = $tool['duration_minutes'] ?? null;
                                                $durationDisplay = $durationRaw !== null
                                                    ? UtilityHelper::englishToPersian((string) $durationRaw)
                                                    : '—';
                                                $questionsCount = max(0, (int) ($tool['questions_count'] ?? 0));
                                                $questionsDisplay = UtilityHelper::englishToPersian((string) $questionsCount);
                                                $isOptional = (int) ($tool['is_optional'] ?? 0) === 1;
                                                $isExam = (int) ($tool['is_exam'] ?? 0) === 1;
                                                $isResultOnly = (int) ($tool['is_result_only'] ?? 0) === 1;
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="table-actions">
                                                        <a href="<?= UtilityHelper::baseUrl('organizations/evaluation-tools/edit?id=' . $toolIdAttr); ?>" class="btn btn-outline-main" title="ویرایش ابزار">
                                                            <ion-icon name="create-outline"></ion-icon>
                                                            <span class="visually-hidden">ویرایش</span>
                                                        </a>
                                                        <form action="<?= UtilityHelper::baseUrl('organizations/evaluation-tools/delete'); ?>" method="post" class="d-inline-flex" onsubmit="return confirm('آیا از حذف این ابزار ارزیابی اطمینان دارید؟');">
                                                            <?= csrf_field(); ?>
                                                            <input type="hidden" name="id" value="<?= $toolIdAttr; ?>">
                                                            <button type="submit" class="btn btn-outline-danger" title="حذف ابزار">
                                                                <ion-icon name="trash-outline"></ion-icon>
                                                                <span class="visually-hidden">حذف</span>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                                <td><?= $nameValue; ?></td>
                                                <td><?= $typeValue; ?></td>
                                                <td><?= $durationDisplay; ?></td>
                                                <td><?= $renderStatusIcon($isOptional, 'success', 'muted', 'پاسخ‌دهی اختیاری است', 'پاسخ‌دهی اجباری است'); ?></td>
                                                <td>
                                                    <span class="questions-badge"><?= $questionsDisplay; ?></span>
                                                </td>
                                                <td>
                                                    <?php if ($isExam): ?>
                                                        <a href="<?= UtilityHelper::baseUrl('organizations/evaluation-tools/questions?tool_id=' . $toolIdAttr); ?>" class="btn btn-question-manage btn-question-purple" title="مدیریت سوالات">
                                                            <ion-icon name="list-circle-outline"></ion-icon>
                                                            تعریف سوالات
                                                        </a>
                                                    <?php else: ?>
                                                        <button type="button" class="btn btn-question-manage btn-question-red" title="برای ابزارهای غیر آزمونی فعال نیست" disabled>
                                                            <ion-icon name="alert-circle-outline"></ion-icon>
                                                            تعریف سوالات
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= $renderStatusIcon($isExam, 'primary', 'muted', 'این ابزار آزمون است', 'این ابزار آزمون نیست'); ?></td>
                                                <td><?= $renderStatusIcon($isResultOnly, 'warning', 'muted', 'فقط نتیجه ثبت می‌شود', 'فرآیند کامل ثبت می‌شود'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="9" class="py-32">
                                                <div class="evaluation-tools-empty">
                                                    هیچ ابزاری ثبت نشده است. برای شروع، دکمه «افزودن ابزار جدید» را انتخاب کنید.
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include __DIR__ . '/../../../layouts/organization-footer.php'; ?>
    </div>
</div>
