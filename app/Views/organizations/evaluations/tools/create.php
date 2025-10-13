<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../../../Helpers/autoload.php';
}

$title = $title ?? 'ایجاد ابزار ارزیابی';
$user = (class_exists('AuthHelper') && AuthHelper::getUser()) ? AuthHelper::getUser() : [
    'name' => 'کاربر سازمان',
    'email' => 'organization@example.com',
];
$additional_css = $additional_css ?? [];
$additional_js = $additional_js ?? [];
$inline_styles = $inline_styles ?? '';
$inline_scripts = $inline_scripts ?? '';

AuthHelper::startSession();

$validationErrors = $validationErrors ?? [];
$errorMessage = $errorMessage ?? null;
$questionTypeSuggestions = [
    'چندگزینه‌ای',
    'طیف لیکرت',
    'درست/نادرست',
    'تشریحی کوتاه',
    'تشریحی بلند',
    'مقیاس ۱ تا ۵',
    'مقیاس ۱ تا ۷',
    'رتبه‌بندی',
    'بله/خیر'
];
$calculationFormulaSuggestions = [
    'MBTI',
    'DISC',
    'NEO',
    'HBDI',
    'تفکر تحلیلی',
    'افق‌سنجی شغلی',
    'ارزیابی شایستگی ۳۶۰ درجه',
    'شاخص مشارکت کارکنان'
];

$inline_styles .= <<<'CSS'
    body {
        background: #f5f7fb;
    }
    .evaluation-tool-form .form-label {
        font-weight: 600;
        color: #111827;
    }
    .evaluation-tool-form .form-text {
        color: #6b7280;
        font-size: 0.78rem;
    }
    .evaluation-tool-form .ltr-input {
        direction: ltr;
        text-align: left;
    }
    .evaluation-tool-form textarea {
        min-height: 120px;
    }
CSS;

include __DIR__ . '/../../../layouts/organization-header.php';
include __DIR__ . '/../../../layouts/organization-sidebar.php';

$navbarUser = $user;
?>

<?php include __DIR__ . '/../../../layouts/organization-navbar.php'; ?>

<div class="page-content-wrapper">
    <div class="page-content">
        <div class="row g-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-24 h-100">
                    <div class="card-body p-24">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-16 mb-24">
                            <div>
                                <h2 class="mb-6 text-gray-900">ایجاد ابزار ارزیابی</h2>
                                <p class="text-gray-500 mb-0">جزئیات ابزار ارزیابی جدید را ثبت کنید تا در فرآیندهای سنجش استفاده شود.</p>
                            </div>
                            <div>
                                <a href="<?= UtilityHelper::baseUrl('organizations/evaluation-tools'); ?>" class="btn btn-outline-gray rounded-pill px-24 d-inline-flex align-items-center gap-8">
                                    بازگشت به لیست
                                    <ion-icon name="arrow-undo-outline"></ion-icon>
                                </a>
                            </div>
                        </div>

                        <?php if (!empty($errorMessage)): ?>
                            <div class="alert alert-danger rounded-16 d-flex align-items-center gap-12" role="alert">
                                <ion-icon name="warning-outline"></ion-icon>
                                <span><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        <?php endif; ?>

                        <form action="<?= UtilityHelper::baseUrl('organizations/evaluation-tools'); ?>" method="post" class="evaluation-tool-form">
                            <?= csrf_field(); ?>
                            <div class="row g-16">
                                <div class="col-md-4">
                                    <label class="form-label">کد <span class="text-danger">*</span></label>
                                    <input type="text" name="code" class="form-control ltr-input" placeholder="مثال: TOOL-01" value="<?= htmlspecialchars(old('code', ''), ENT_QUOTES, 'UTF-8'); ?>" required>
                                    <?php if (!empty($validationErrors['code'])): ?>
                                        <div class="text-danger mt-6"><?= htmlspecialchars($validationErrors['code'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php else: ?>
                                        <div class="form-text">کد باید در سازمان یکتا باشد.</div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">نام ابزار <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" placeholder="مثال: پرسشنامه رضایت کارکنان" value="<?= htmlspecialchars(old('name', ''), ENT_QUOTES, 'UTF-8'); ?>" required>
                                    <?php if (!empty($validationErrors['name'])): ?>
                                        <div class="text-danger mt-6"><?= htmlspecialchars($validationErrors['name'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">نوع سوالات <span class="text-danger">*</span></label>
                                    <input type="text" name="question_type" class="form-control" list="question-type-suggestions" placeholder="نوع سوالات، مثال: چندگزینه‌ای" value="<?= htmlspecialchars(old('question_type', ''), ENT_QUOTES, 'UTF-8'); ?>" required>
                                    <datalist id="question-type-suggestions">
                                        <?php foreach ($questionTypeSuggestions as $suggestion): ?>
                                            <option value="<?= htmlspecialchars($suggestion, ENT_QUOTES, 'UTF-8'); ?>"></option>
                                        <?php endforeach; ?>
                                    </datalist>
                                    <?php if (!empty($validationErrors['question_type'])): ?>
                                        <div class="text-danger mt-6"><?= htmlspecialchars($validationErrors['question_type'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">راهنما</label>
                                    <textarea name="guide" class="form-control" placeholder="توضیح کوتاه درباره نحوه استفاده از ابزار"><?= htmlspecialchars(old('guide', ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">توضیح ابزار</label>
                                    <textarea name="description" class="form-control" placeholder="اطلاعات تکمیلی درباره ابزار"><?= htmlspecialchars(old('description', ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">مدت زمان (دقیقه)</label>
                                    <input type="number" name="duration_minutes" class="form-control ltr-input" min="0" placeholder="مثال: 30" value="<?= htmlspecialchars(old('duration_minutes', ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php if (!empty($validationErrors['duration_minutes'])): ?>
                                        <div class="text-danger mt-6"><?= htmlspecialchars($validationErrors['duration_minutes'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">آزمون میباشد؟</label>
                                    <?php $oldIsExam = (string) old('is_exam', '0'); ?>
                                    <div class="form-switch pt-2">
                                        <input type="checkbox" name="is_exam" value="1" class="form-check-input" <?= in_array($oldIsExam, ['1', 'true', 'on'], true) ? 'checked' : ''; ?>>
                                        <span class="form-text ms-2">در صورت فعال بودن، ابزار به عنوان آزمون ثبت میشود.</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">فقط ثبت نتیجه میباشد؟</label>
                                    <?php $oldIsResultOnly = (string) old('is_result_only', '0'); ?>
                                    <div class="form-switch pt-2">
                                        <input type="checkbox" name="is_result_only" value="1" class="form-check-input" <?= in_array($oldIsResultOnly, ['1', 'true', 'on'], true) ? 'checked' : ''; ?>>
                                        <span class="form-text ms-2">در صورت فعال بودن، تنها نتیجه نهایی ثبت خواهد شد.</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">عدم اجبار در جواب دادن</label>
                                    <?php $oldIsOptional = (string) old('is_optional', '0'); ?>
                                    <div class="form-switch pt-2">
                                        <input type="checkbox" name="is_optional" value="1" class="form-check-input" <?= in_array($oldIsOptional, ['1', 'true', 'on'], true) ? 'checked' : ''; ?>>
                                        <span class="form-text ms-2">فعال‌سازی این گزینه، پاسخ به سوالات را اختیاری میکند.</span>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">فرمول محاسبه</label>
                                    <input type="text" name="calculation_formula" class="form-control" list="calculation-formula-suggestions" placeholder="مثال: MBTI یا NEO" value="<?= htmlspecialchars(old('calculation_formula', ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <datalist id="calculation-formula-suggestions">
                                        <?php foreach ($calculationFormulaSuggestions as $suggestion): ?>
                                            <option value="<?= htmlspecialchars($suggestion, ENT_QUOTES, 'UTF-8'); ?>"></option>
                                        <?php endforeach; ?>
                                    </datalist>
                                    <div class="form-text">می‌توانید یکی از آزمون‌های متداول را انتخاب یا مقدار دلخواه وارد کنید.</div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-12 mt-28">
                                <button type="submit" class="btn btn-main rounded-pill px-32 d-inline-flex align-items-center gap-8">
                                    <ion-icon name="save-outline"></ion-icon>
                                    <span>ثبت ابزار</span>
                                </button>
                                <a href="<?= UtilityHelper::baseUrl('organizations/evaluation-tools'); ?>" class="btn btn-outline-gray rounded-pill px-28">انصراف</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php include __DIR__ . '/../../../layouts/organization-footer.php'; ?>
    </div>
</div>
