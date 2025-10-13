<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../../../../Helpers/autoload.php';
}

$title = $title ?? 'سوالات ابزار ارزیابی';
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

$evaluationTool = $evaluationTool ?? [];
$questions = $questions ?? [];
$successMessage = $successMessage ?? null;
$errorMessage = $errorMessage ?? null;
$warningMessage = $warningMessage ?? null;

$examImportContext = $examImportContext ?? [];
$examImportContext = array_merge([
    'recommended' => [],
    'others' => [],
    'has_exams' => false,
    'suggested_exam_id' => null,
    'total' => 0,
], is_array($examImportContext) ? $examImportContext : []);

$canImportFromBank = !empty($examImportContext['has_exams']);
$suggestedExamId = $examImportContext['suggested_exam_id'] ?? null;

$recommendedExams = [];
$otherExams = [];

if (!empty($examImportContext['recommended']) && is_array($examImportContext['recommended'])) {
    $recommendedExams = array_values($examImportContext['recommended']);
}

if (!empty($examImportContext['others']) && is_array($examImportContext['others'])) {
    $otherExams = array_values($examImportContext['others']);
}

if (!empty($recommendedExams)) {
    $recommendedIds = array_column($recommendedExams, 'id');
    $otherExams = array_filter($otherExams, static function ($exam) use ($recommendedIds) {
        return !in_array($exam['id'] ?? null, $recommendedIds, true);
    });
}

$recommendedExams = array_slice($recommendedExams, 0, 6);
$otherExams = array_slice(array_values($otherExams), 0, 12);

$firstRecommendedExamId = !empty($recommendedExams) ? (int) ($recommendedExams[0]['id'] ?? 0) : null;
$firstOtherExamId = !empty($otherExams) ? (int) ($otherExams[0]['id'] ?? 0) : null;

$toolName = htmlspecialchars((string) ($evaluationTool['name'] ?? 'ابزار ناشناس'), ENT_QUOTES, 'UTF-8');
$toolCode = htmlspecialchars((string) ($evaluationTool['code'] ?? ''), ENT_QUOTES, 'UTF-8');
$toolIdAttr = htmlspecialchars((string) ($evaluationTool['id'] ?? ''), ENT_QUOTES, 'UTF-8');
$isExamTool = (int) ($evaluationTool['is_exam'] ?? 0) === 1;

$inline_styles .= <<<'CSS'
    body {
        background: #f5f7fb;
    }
    .evaluation-questions-card {
        border-radius: 24px;
        border: 1px solid #e4e9f2;
    }
    .evaluation-questions-header h2 {
        font-size: 22px;
        font-weight: 600;
    }
    .evaluation-questions-table thead th,
    .evaluation-questions-table tbody td {
        white-space: nowrap;
        vertical-align: middle;
    }
    .evaluation-questions-table tbody tr {
        opacity: 1 !important;
        visibility: visible !important;
        transform: none !important;
        display: table-row !important;
    }
    .badge-question-meta {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 4px 12px;
        font-size: 13px;
        background: #eef2ff;
        color: #312e81;
    }
    .status-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        border-radius: 999px;
        font-weight: 600;
        font-size: 13px;
    }
    .status-chip--info {
        background: #e0f2fe;
        color: #075985;
    }
    .status-chip--danger {
        background: #fee2e2;
        color: #b91c1c;
    }
    .status-chip--success {
        background: #dcfce7;
        color: #166534;
    }
    .questions-action-bar .btn {
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding-inline: 20px;
    }
    .questions-empty {
        text-align: center;
        color: #475467;
        font-size: 16px;
        padding: 32px 0;
    }
    .question-bank-modal {
        position: fixed;
        inset: 0;
        z-index: 1080;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 24px;
    }
    .question-bank-modal--visible {
        display: flex;
    }
    .question-bank-modal__overlay {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.45);
    }
    .question-bank-modal__content {
        position: relative;
        background: #ffffff;
        border-radius: 24px;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.18);
        width: min(720px, 96%);
        max-height: 88vh;
        display: flex;
        flex-direction: column;
        padding: 28px;
        overflow: hidden;
    }
    .question-bank-modal__header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
    }
    .question-bank-modal__title {
        font-size: 20px;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
    }
    .question-bank-modal__close {
        background: transparent;
        border: none;
        color: #6b7280;
        font-size: 22px;
        line-height: 1;
        cursor: pointer;
    }
    .question-bank-modal__body {
        overflow-y: auto;
        padding-right: 8px;
        margin-right: -8px;
    }
    .question-bank-exam-group-title {
        font-size: 16px;
        font-weight: 600;
        color: #1f2937;
        margin: 16px 0 8px;
    }
    .question-bank-exam-list {
        display: grid;
        gap: 12px;
    }
    .question-bank-exam-item {
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        padding: 14px 18px;
        display: flex;
        gap: 14px;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .question-bank-exam-item:hover {
        border-color: #c7d2fe;
        box-shadow: 0 12px 24px rgba(79, 70, 229, 0.12);
    }
    .question-bank-exam-item input[type="radio"] {
        margin-top: 4px;
    }
    .question-bank-exam-info {
        flex: 1;
    }
    .question-bank-exam-title {
        font-weight: 600;
        color: #111827;
        margin-bottom: 6px;
        font-size: 16px;
    }
    .question-bank-exam-description {
        font-size: 13px;
        color: #6b7280;
        margin-bottom: 8px;
    }
    .question-bank-exam-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        font-size: 12px;
    }
    .question-bank-exam-meta span {
        background: #eef2ff;
        color: #3730a3;
        padding: 4px 10px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .question-bank-modal__footer {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 18px;
    }
    .question-bank-empty {
        text-align: center;
        color: #6b7280;
        padding: 32px 0;
    }
    body.modal-open {
        overflow: hidden;
    }
CSS;

$tableOptions = [
    'paging' => true,
    'lengthChange' => true,
    'pageLength' => 10,
    'responsive' => true,
    'lengthMenu' => [[10, 25, 50, -1], ['۱۰', '۲۵', '۵۰', 'همه']],
    'dom' => "<'row align-items-center mb-3'<'col-lg-4 col-md-6 col-sm-12 text-start text-md-start'l><'col-lg-8 col-md-6 col-sm-12 text-start text-md-end'f>>" .
        "<'row'<'col-12'tr>>" .
        "<'row align-items-center mt-3'<'col-md-6 col-sm-12 text-start text-md-start'i><'col-md-6 col-sm-12 text-start text-md-end'p>>",
    'order' => [[1, 'asc']],
    'columnDefs' => [
        ['targets' => 0, 'orderable' => false, 'searchable' => false, 'className' => 'text-nowrap'],
        ['targets' => 2, 'orderable' => false],
    ],
];

$tableOptionsAttr = htmlspecialchars(json_encode($tableOptions, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');

include __DIR__ . '/../../../../layouts/organization-header.php';
include __DIR__ . '/../../../../layouts/organization-sidebar.php';

$navbarUser = $user;
?>

<?php include __DIR__ . '/../../../../layouts/organization-navbar.php'; ?>

<div class="page-content-wrapper">
    <div class="page-content">
        <div class="row g-4">
            <div class="col-12">
                <div class="card evaluation-questions-card shadow-sm h-100">
                    <div class="card-body p-24">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-16 mb-24 evaluation-questions-header">
                            <div>
                                <div class="d-flex flex-wrap align-items-center gap-12 mb-8">
                                    <h2 class="mb-0 text-gray-900">سوالات ابزار: <?= $toolName; ?></h2>
                                    <?php if ($toolCode !== ''): ?>
                                        <span class="badge-question-meta"><ion-icon name="barcode-outline"></ion-icon> کد: <?= $toolCode; ?></span>
                                    <?php endif; ?>
                                    <span class="badge-question-meta"><ion-icon name="layers-outline"></ion-icon> تعداد سوالات: <?= UtilityHelper::englishToPersian((string) ($evaluationTool['questions_count'] ?? 0)); ?></span>
                                </div>
                                <p class="text-gray-500 mb-0">لیست سوالات ثبت شده برای این ابزار را مشاهده و مدیریت کنید.</p>
                            </div>
                            <div class="questions-action-bar d-flex flex-wrap gap-12">
                                <a href="<?= UtilityHelper::baseUrl('organizations/evaluation-tools'); ?>" class="btn btn-outline-gray d-inline-flex align-items-center gap-8">
                                    <ion-icon name="arrow-undo-outline"></ion-icon>
                                    بازگشت
                                </a>
                                <a href="<?= UtilityHelper::baseUrl('organizations/evaluation-tools/questions/create?tool_id=' . $toolIdAttr); ?>" class="btn btn-main d-inline-flex align-items-center gap-8">
                                    <ion-icon name="add-circle-outline"></ion-icon>
                                    افزودن سوال
                                </a>
                                <button
                                    type="button"
                                    class="btn btn-outline-main d-inline-flex align-items-center gap-8<?= $canImportFromBank ? '' : ' disabled'; ?>"
                                    id="open-question-bank-modal"
                                    data-can-import="<?= $canImportFromBank ? '1' : '0'; ?>"
                                    <?= $canImportFromBank ? '' : 'disabled'; ?>
                                    <?= $canImportFromBank ? '' : 'title="در حال حاضر آزمونی برای وارد کردن وجود ندارد."'; ?>
                                >
                                    <ion-icon name="library-outline"></ion-icon>
                                    اضافه کردن از بانک سوالات
                                </button>
                            </div>
                        </div>

                        <?php if (!$isExamTool): ?>
                            <div class="alert alert-warning rounded-16 d-flex align-items-center gap-12" role="alert">
                                <ion-icon name="alert-circle-outline"></ion-icon>
                                <span>این ابزار به عنوان آزمون علامت‌گذاری نشده است؛ با این حال می‌توانید سوالات را مدیریت کنید.</span>
                            </div>
                        <?php endif; ?>

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

                        <?php if (!empty($warningMessage)): ?>
                            <div class="alert alert-warning rounded-16 d-flex align-items-center gap-12" role="alert">
                                <ion-icon name="alert-circle-outline"></ion-icon>
                                <span><?= htmlspecialchars($warningMessage, ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        <?php endif; ?>

                        <div class="table-responsive rounded-16 border border-gray-100 mt-3">
                            <table class="table align-middle mb-0 evaluation-questions-table js-data-table" data-datatable-options="<?= $tableOptionsAttr; ?>">
                                <thead class="bg-gray-100 text-gray-700">
                                    <tr>
                                        <th scope="col"  style="width: 10%;" class="no-sort no-search text-start">عملیات</th>
                                        <th scope="col" style="width: 10%;" class="text-start">کد سوال</th>
                                        <th scope="col" style="width: 10%;"class="text-start">ترتیب</th>
                                        <th scope="col"  style="width: 10%;" class="text-start">فقط توضیح می‌باشد</th>
                                        <th scope="col" class="text-start">عنوان سوال</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($questions)): ?>
                                        <?php foreach ($questions as $question): ?>
                                            <?php
                                                $code = htmlspecialchars((string) ($question['code'] ?? ''), ENT_QUOTES, 'UTF-8');
                                                $order = isset($question['display_order']) && $question['display_order'] !== null
                                                    ? UtilityHelper::englishToPersian((string) $question['display_order'])
                                                    : '—';
                                                $isDescriptionOnly = (int) ($question['is_description_only'] ?? 0) === 1;
                                                $titleValue = htmlspecialchars((string) ($question['title'] ?? ''), ENT_QUOTES, 'UTF-8');
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="d-inline-flex align-items-center gap-8">
                                                        <a href="<?= UtilityHelper::baseUrl('organizations/evaluation-tools/questions/edit?tool_id=' . $toolIdAttr . '&id=' . (int) ($question['id'] ?? 0)); ?>" class="btn btn-outline-main btn-sm" title="ویرایش سوال">
                                                            <ion-icon name="create-outline"></ion-icon>
                                                        </a>
                                                        <form action="<?= UtilityHelper::baseUrl('organizations/evaluation-tools/questions/delete'); ?>" method="post" class="d-inline-flex" onsubmit="return confirm('آیا از حذف این سوال اطمینان دارید؟');">
                                                            <?= csrf_field(); ?>
                                                            <input type="hidden" name="tool_id" value="<?= $toolIdAttr; ?>">
                                                            <input type="hidden" name="id" value="<?= (int) ($question['id'] ?? 0); ?>">
                                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="حذف سوال">
                                                                <ion-icon name="trash-outline"></ion-icon>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                                <td><?= $code !== '' ? $code : '—'; ?></td>
                                                <td><?= $order; ?></td>
                                                <td>
                                                    <?php if ($isDescriptionOnly): ?>
                                                        <span class="status-chip status-chip--info">
                                                            <ion-icon name="information-circle-outline"></ion-icon>
                                                            بله
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="status-chip status-chip--success">
                                                            <ion-icon name="checkmark-circle-outline"></ion-icon>
                                                            خیر
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= $titleValue !== '' ? $titleValue : 'بدون عنوان'; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                                <td colspan="5">
                                                <div class="questions-empty">
                                                    هنوز سوالی برای این ابزار ثبت نشده است. از دکمه «افزودن سوال» برای شروع استفاده کنید.
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

        <form action="<?= UtilityHelper::baseUrl('organizations/evaluation-tools/questions/import'); ?>" method="post" id="question-bank-import-form" class="d-none">
            <?= csrf_field(); ?>
            <input type="hidden" name="tool_id" value="<?= $toolIdAttr; ?>">
            <input type="hidden" name="exam_id" id="question-bank-selected-exam">
        </form>

        <div class="question-bank-modal" id="question-bank-modal" aria-hidden="true" role="dialog" aria-modal="true">
            <div class="question-bank-modal__overlay" data-modal-dismiss></div>
            <div class="question-bank-modal__content">
                <div class="question-bank-modal__header">
                    <h3 class="question-bank-modal__title">انتخاب آزمون مرجع</h3>
                    <button type="button" class="question-bank-modal__close" data-modal-dismiss aria-label="بستن">
                        &times;
                    </button>
                </div>
                <p class="text-gray-500 mb-0">
                    آزمون مناسب را از بانک سوالات انتخاب کنید تا سوالات آن به صورت خودکار به ابزار «<?= $toolName; ?>» افزوده شود.
                </p>

                <div class="question-bank-modal__body mt-16">
                    <?php if ($canImportFromBank): ?>
                        <?php if (!empty($recommendedExams)): ?>
                            <div>
                                <div class="question-bank-exam-group-title">پیشنهاد شده برای این ابزار</div>
                                <div class="question-bank-exam-list">
                                    <?php foreach ($recommendedExams as $exam): ?>
                                        <?php
                                            $examId = (int) ($exam['id'] ?? 0);
                                            $inputId = 'exam-option-' . $examId;
                                            $isChecked = $suggestedExamId !== null
                                                ? ((int) $suggestedExamId === $examId)
                                                : ($firstRecommendedExamId !== null && $examId === $firstRecommendedExamId);
                                            $examTitle = htmlspecialchars((string) ($exam['title'] ?? 'آزمون بدون عنوان'), ENT_QUOTES, 'UTF-8');
                                            $examType = htmlspecialchars((string) ($exam['type'] ?? ''), ENT_QUOTES, 'UTF-8');
                                            $examSlug = htmlspecialchars((string) ($exam['slug'] ?? ''), ENT_QUOTES, 'UTF-8');
                                            $examDescription = trim((string) ($exam['description'] ?? ''));
                                            if ($examDescription !== '') {
                                                if (mb_strlen($examDescription) > 170) {
                                                    $examDescription = mb_substr($examDescription, 0, 170) . '…';
                                                }
                                                $examDescription = htmlspecialchars($examDescription, ENT_QUOTES, 'UTF-8');
                                            }
                                            $examScore = isset($exam['score']) ? (int) $exam['score'] : null;
                                        ?>
                                        <label class="question-bank-exam-item" for="<?= $inputId; ?>">
                                            <input type="radio" name="exam_id" id="<?= $inputId; ?>" value="<?= $examId; ?>" <?= $isChecked ? 'checked' : ''; ?>>
                                            <div class="question-bank-exam-info">
                                                <div class="question-bank-exam-title"><?= $examTitle; ?></div>
                                                <?php if ($examDescription !== ''): ?>
                                                    <div class="question-bank-exam-description"><?= $examDescription; ?></div>
                                                <?php endif; ?>
                                                <div class="question-bank-exam-meta">
                                                    <?php if ($examType !== ''): ?>
                                                        <span>نوع آزمون: <?= $examType; ?></span>
                                                    <?php endif; ?>
                                                    <?php if ($examSlug !== ''): ?>
                                                        <span>اسلاگ: <?= $examSlug; ?></span>
                                                    <?php endif; ?>
                                                    <?php if ($examScore !== null): ?>
                                                        <span>امتیاز تطبیق: <?= UtilityHelper::englishToPersian((string) $examScore); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($otherExams)): ?>
                            <div class="mt-20">
                                <div class="question-bank-exam-group-title">سایر آزمون‌های موجود</div>
                                <div class="question-bank-exam-list">
                                    <?php foreach ($otherExams as $exam): ?>
                                        <?php
                                            $examId = (int) ($exam['id'] ?? 0);
                                            $inputId = 'exam-option-' . $examId;
                                            $isChecked = false;
                                            if (empty($recommendedExams)) {
                                                if ($suggestedExamId !== null) {
                                                    $isChecked = ((int) $suggestedExamId === $examId);
                                                } else {
                                                    $isChecked = ($firstOtherExamId !== null && $examId === $firstOtherExamId);
                                                }
                                            }
                                            $examTitle = htmlspecialchars((string) ($exam['title'] ?? 'آزمون بدون عنوان'), ENT_QUOTES, 'UTF-8');
                                            $examType = htmlspecialchars((string) ($exam['type'] ?? ''), ENT_QUOTES, 'UTF-8');
                                            $examSlug = htmlspecialchars((string) ($exam['slug'] ?? ''), ENT_QUOTES, 'UTF-8');
                                            $examDescription = trim((string) ($exam['description'] ?? ''));
                                            if ($examDescription !== '') {
                                                if (mb_strlen($examDescription) > 170) {
                                                    $examDescription = mb_substr($examDescription, 0, 170) . '…';
                                                }
                                                $examDescription = htmlspecialchars($examDescription, ENT_QUOTES, 'UTF-8');
                                            }
                                            $examScore = isset($exam['score']) ? (int) $exam['score'] : null;
                                        ?>
                                        <label class="question-bank-exam-item" for="<?= $inputId; ?>">
                                            <input type="radio" name="exam_id" id="<?= $inputId; ?>" value="<?= $examId; ?>" <?= $isChecked ? 'checked' : ''; ?>>
                                            <div class="question-bank-exam-info">
                                                <div class="question-bank-exam-title"><?= $examTitle; ?></div>
                                                <?php if ($examDescription !== ''): ?>
                                                    <div class="question-bank-exam-description"><?= $examDescription; ?></div>
                                                <?php endif; ?>
                                                <div class="question-bank-exam-meta">
                                                    <?php if ($examType !== ''): ?>
                                                        <span>نوع آزمون: <?= $examType; ?></span>
                                                    <?php endif; ?>
                                                    <?php if ($examSlug !== ''): ?>
                                                        <span>اسلاگ: <?= $examSlug; ?></span>
                                                    <?php endif; ?>
                                                    <?php if ($examScore !== null): ?>
                                                        <span>امتیاز تطبیق: <?= UtilityHelper::englishToPersian((string) $examScore); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="question-bank-empty">
                            در حال حاضر آزمونی در بانک سوالات ثبت نشده است. پس از افزودن آزمون‌ها در بخش سوپر ادمین، امکان وارد کردن سوالات از این بخش فعال خواهد شد.
                        </div>
                    <?php endif; ?>
                </div>

                <div class="question-bank-modal__footer">
                    <button type="button" class="btn btn-outline-gray rounded-pill px-24" data-modal-dismiss>انصراف</button>
                    <button type="button" class="btn btn-main rounded-pill px-28" id="confirm-question-bank-import" <?= $canImportFromBank ? '' : 'disabled'; ?>>افزودن سوالات</button>
                </div>
            </div>
        </div>

        <?php include __DIR__ . '/../../../../layouts/organization-footer.php'; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('question-bank-modal');
        const openButton = document.getElementById('open-question-bank-modal');
        const confirmButton = document.getElementById('confirm-question-bank-import');
        const form = document.getElementById('question-bank-import-form');
        const targetInput = document.getElementById('question-bank-selected-exam');

        if (!modal || !openButton) {
            return;
        }

        const overlay = modal.querySelector('.question-bank-modal__overlay');
        const dismissElements = modal.querySelectorAll('[data-modal-dismiss]');

        function openModal() {
            if (openButton.dataset.canImport !== '1') {
                return;
            }

            modal.classList.add('question-bank-modal--visible');
            document.body.classList.add('modal-open');

            window.setTimeout(function () {
                const selectedRadio = modal.querySelector('input[name="exam_id"]:checked');
                if (selectedRadio) {
                    selectedRadio.focus();
                } else {
                    const firstRadio = modal.querySelector('input[name="exam_id"]');
                    if (firstRadio) {
                        firstRadio.focus();
                    }
                }
            }, 100);
        }

        function closeModal() {
            modal.classList.remove('question-bank-modal--visible');
            document.body.classList.remove('modal-open');
        }

        openButton.addEventListener('click', openModal);

        dismissElements.forEach(function (element) {
            element.addEventListener('click', closeModal);
        });

        if (overlay) {
            overlay.addEventListener('click', closeModal);
        }

        modal.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });

        if (confirmButton) {
            confirmButton.addEventListener('click', function () {
                if (confirmButton.hasAttribute('disabled')) {
                    closeModal();
                    return;
                }

                const selectedRadio = modal.querySelector('input[name="exam_id"]:checked');
                if (!selectedRadio) {
                    alert('لطفاً یک آزمون را برای وارد کردن سوالات انتخاب کنید.');
                    return;
                }

                if (targetInput) {
                    targetInput.value = selectedRadio.value;
                }

                closeModal();

                if (form) {
                    form.submit();
                }
            });
        }
    });
</script>
