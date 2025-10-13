<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../../../../Helpers/autoload.php';
}

$title = $title ?? 'افزودن سوال جدید';
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
$evaluationTool = $evaluationTool ?? [];

$toolName = htmlspecialchars((string) ($evaluationTool['name'] ?? 'ابزار ناشناس'), ENT_QUOTES, 'UTF-8');
$toolIdAttr = htmlspecialchars((string) ($evaluationTool['id'] ?? ''), ENT_QUOTES, 'UTF-8');

$defaultAnswers = [
    ['key' => 'ans_a', 'code' => 'A', 'option' => '', 'score_numeric' => '', 'score_character' => '', 'display_order' => 1, 'is_correct' => 1],
    ['key' => 'ans_b', 'code' => 'B', 'option' => '', 'score_numeric' => '', 'score_character' => '', 'display_order' => 2, 'is_correct' => 0],
    ['key' => 'ans_c', 'code' => 'C', 'option' => '', 'score_numeric' => '', 'score_character' => '', 'display_order' => 3, 'is_correct' => 0],
    ['key' => 'ans_d', 'code' => 'D', 'option' => '', 'score_numeric' => '', 'score_character' => '', 'display_order' => 4, 'is_correct' => 0],
];

$oldAnswers = old('answers', []);
$oldCorrectAnswer = (string) old('correct_answer', '');

$preparedAnswers = [];
if (is_array($oldAnswers) && !empty($oldAnswers)) {
    foreach ($oldAnswers as $key => $answer) {
        $preparedAnswers[] = [
            'key' => (string) $key,
            'code' => (string) ($answer['code'] ?? ''),
            'option' => (string) ($answer['option'] ?? ''),
            'score_numeric' => (string) ($answer['score_numeric'] ?? ''),
            'score_character' => (string) ($answer['score_character'] ?? ''),
            'display_order' => (string) ($answer['display_order'] ?? ''),
            'is_correct' => (string) $key === $oldCorrectAnswer ? 1 : 0,
        ];
    }
}

if (empty($preparedAnswers)) {
    $preparedAnswers = $defaultAnswers;
    $oldCorrectAnswer = $defaultAnswers[0]['key'];
}

$initialAnswersJson = json_encode($preparedAnswers, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
$initialCorrectAnswerJson = json_encode($oldCorrectAnswer, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

$inline_styles .= <<<'CSS'
    body {
        background: #f5f7fb;
    }
    .question-form-card {
        border-radius: 24px;
        border: 1px solid #e4e9f2;
    }
    .question-form .form-label {
        font-weight: 600;
        color: #111827;
    }
    .question-form textarea {
        min-height: 140px;
    }
    .answers-wrapper {
        display: grid;
        gap: 16px;
    }
    .answer-item {
        border: 1px solid #e4e9f2;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.05);
    }
    .answer-item .card-body {
        padding: 16px;
    }
    .answer-item .remove-answer-btn {
        font-size: 14px;
        color: #ef4444;
    }
    .answer-section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
    }
    .answer-section-header h3 {
        font-size: 18px;
        margin: 0;
    }
CSS;

include __DIR__ . '/../../../../layouts/organization-header.php';
include __DIR__ . '/../../../../layouts/organization-sidebar.php';

$navbarUser = $user;
?>

<?php include __DIR__ . '/../../../../layouts/organization-navbar.php'; ?>

<div class="page-content-wrapper">
    <div class="page-content">
        <div class="row g-4">
            <div class="col-12">
                <div class="card question-form-card shadow-sm h-100">
                    <div class="card-body p-24">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-16 mb-24">
                            <div>
                                <h2 class="mb-6 text-gray-900">افزودن سوال جدید برای <?= $toolName; ?></h2>
                                <p class="text-gray-500 mb-0">متن سوال و گزینه‌های آن را ثبت کنید. قبل از ذخیره، گزینه‌های پاسخ را تکمیل نمایید.</p>
                            </div>
                            <div class="d-flex flex-wrap gap-10">
                                <a href="<?= UtilityHelper::baseUrl('organizations/evaluation-tools/questions?tool_id=' . $toolIdAttr); ?>" class="btn btn-outline-gray rounded-pill px-24 d-inline-flex align-items-center gap-8">
                                    <ion-icon name="arrow-undo-outline"></ion-icon>
                                    بازگشت به سوالات
                                </a>
                            </div>
                        </div>

                        <?php if (!empty($errorMessage)): ?>
                            <div class="alert alert-danger rounded-16 d-flex align-items-center gap-12" role="alert">
                                <ion-icon name="warning-outline"></ion-icon>
                                <span><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        <?php endif; ?>

                        <form action="<?= UtilityHelper::baseUrl('organizations/evaluation-tools/questions'); ?>" method="post" enctype="multipart/form-data" class="question-form">
                            <?= csrf_field(); ?>
                            <input type="hidden" name="tool_id" value="<?= $toolIdAttr; ?>">

                            <div class="row g-16">
                                <div class="col-md-4">
                                    <label class="form-label">کد سوال <span class="text-danger">*</span></label>
                                    <input type="text" name="code" class="form-control" placeholder="مثال: Q-01" value="<?= htmlspecialchars(old('code', ''), ENT_QUOTES, 'UTF-8'); ?>" required>
                                    <?php if (!empty($validationErrors['code'])): ?>
                                        <div class="text-danger mt-6"><?= htmlspecialchars($validationErrors['code'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">ترتیب نمایش</label>
                                    <input type="number" name="display_order" class="form-control" min="0" placeholder="مثال: 1" value="<?= htmlspecialchars(old('display_order', ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php if (!empty($validationErrors['display_order'])): ?>
                                        <div class="text-danger mt-6"><?= htmlspecialchars($validationErrors['display_order'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php else: ?>
                                        <div class="form-text">در صورت خالی بودن، بر اساس ترتیب ثبت مرتب می‌شود.</div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">فقط توضیح می‌باشد؟</label>
                                    <?php $oldDescriptionOnly = (string) old('is_description_only', '0'); ?>
                                    <div class="form-switch pt-2">
                                        <input type="checkbox" name="is_description_only" value="1" class="form-check-input" <?= in_array($oldDescriptionOnly, ['1', 'true', 'on'], true) ? 'checked' : ''; ?>>
                                        <span class="form-text ms-2">در صورت فعال بودن، این سوال به عنوان توضیح بدون پاسخ در نظر گرفته می‌شود.</span>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">متن سوال <span class="text-danger">*</span></label>
                                    <textarea name="question" class="form-control" placeholder="متن کامل سوال را وارد کنید" required><?= htmlspecialchars(old('question', ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                                    <?php if (!empty($validationErrors['question'])): ?>
                                        <div class="text-danger mt-6"><?= htmlspecialchars($validationErrors['question'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">توضیحات سوال</label>
                                    <textarea name="description" class="form-control" placeholder="توضیح تکمیلی برای سوال (اختیاری)"><?= htmlspecialchars(old('description', ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">تصویر سوال</label>
                                    <input type="file" name="question_image" class="form-control" accept="image/*">
                                    <?php if (!empty($validationErrors['question_image'])): ?>
                                        <div class="text-danger mt-6"><?= htmlspecialchars($validationErrors['question_image'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php else: ?>
                                        <div class="form-text">فرمت‌های مجاز: JPG, PNG, GIF, WEBP</div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="mt-32">
                                <div class="answer-section-header mb-12">
                                    <h3>گزینه‌های پاسخ</h3>
                                    <div class="d-flex align-items-center gap-12">
                                        <button type="button" class="btn btn-outline-main rounded-pill px-20" id="add-answer-btn">
                                            <ion-icon name="add-outline"></ion-icon>
                                            افزودن گزینه جدید
                                        </button>
                                    </div>
                                </div>
                                <p class="text-gray-500 mb-16">حداقل دو گزینه را تکمیل کنید و یک گزینه را به عنوان پاسخ صحیح انتخاب نمایید.</p>
                                <?php if (!empty($validationErrors['answers'])): ?>
                                    <?php $answersErrors = (array) $validationErrors['answers']; ?>
                                    <div class="alert alert-danger rounded-16 d-flex align-items-center gap-12" role="alert">
                                        <ion-icon name="alert-circle-outline"></ion-icon>
                                        <span><?= htmlspecialchars($answersErrors[0] ?? 'لطفاً گزینه‌های سوال را بررسی کنید.', ENT_QUOTES, 'UTF-8'); ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($validationErrors['correct_answer'])): ?>
                                    <div class="alert alert-danger rounded-16 d-flex align-items-center gap-12" role="alert">
                                        <ion-icon name="alert-circle-outline"></ion-icon>
                                        <span><?= htmlspecialchars($validationErrors['correct_answer'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    </div>
                                <?php endif; ?>

                                <div class="answers-wrapper" id="answers-container"></div>
                            </div>

                            <div class="d-flex justify-content-end gap-12 mt-32">
                                <button type="submit" class="btn btn-main rounded-pill px-32 d-inline-flex align-items-center gap-8">
                                    <ion-icon name="save-outline"></ion-icon>
                                    <span>ذخیره سوال</span>
                                </button>
                                <a href="<?= UtilityHelper::baseUrl('organizations/evaluation-tools/questions?tool_id=' . $toolIdAttr); ?>" class="btn btn-outline-gray rounded-pill px-28">انصراف</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php include __DIR__ . '/../../../../layouts/organization-footer.php'; ?>
    </div>
</div>

<template id="answer-row-template">
    <div class="answer-item">
        <div class="card-body">
            <div class="row g-12 align-items-end">
                <div class="col-md-2">
                    <label class="form-label">کد جواب <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" data-field="code" placeholder="مثال: A">
                </div>
                <div class="col-md-3">
                    <label class="form-label">متن گزینه <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" data-field="option" placeholder="متن گزینه">
                </div>
                <div class="col-md-2">
                    <label class="form-label">امتیاز عددی</label>
                    <input type="text" class="form-control" data-field="score_numeric" placeholder="مثال: 5 یا 0.5">
                </div>
                <div class="col-md-2">
                    <label class="form-label">امتیاز کاراکتری</label>
                    <input type="text" class="form-control" data-field="score_character" placeholder="مثال: A+">
                </div>
                <div class="col-md-1">
                    <label class="form-label">ترتیب</label>
                    <input type="number" class="form-control" data-field="display_order" min="0" placeholder="1">
                </div>
                <div class="col-md-2">
                    <label class="form-label">گزینه صحیح</label>
                    <div class="form-check mt-2">
                        <input class="form-check-input answer-correct-radio" type="radio" name="correct_answer" value="">
                        <label class="form-check-label">انتخاب</label>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-end mt-12">
                <button type="button" class="btn btn-link remove-answer-btn">حذف گزینه</button>
            </div>
        </div>
    </div>
</template>

<script>
    const initialAnswersData = <?= $initialAnswersJson ?: '[]'; ?>;
    const initialCorrectAnswer = <?= $initialCorrectAnswerJson ?: '""'; ?>;

    document.addEventListener('DOMContentLoaded', function () {
        const answersContainer = document.getElementById('answers-container');
        const addAnswerBtn = document.getElementById('add-answer-btn');
        const answerTemplate = document.getElementById('answer-row-template');
        let currentCorrectAnswer = initialCorrectAnswer || '';

        function createAnswerKey() {
            return 'ans_' + Date.now() + '_' + Math.floor(Math.random() * 1000);
        }

        function createAnswerRow(answer) {
            const fragment = answerTemplate.content.cloneNode(true);
            const wrapper = fragment.querySelector('.answer-item');
            const key = answer.key || createAnswerKey();
            wrapper.dataset.key = key;

            fragment.querySelectorAll('[data-field]').forEach(function (input) {
                const field = input.getAttribute('data-field');
                input.name = `answers[${key}][${field}]`;
                if (field === 'display_order' && (answer[field] === undefined || answer[field] === null || answer[field] === '')) {
                    input.value = answersContainer.childElementCount + 1;
                } else if (answer[field] !== undefined && answer[field] !== null) {
                    input.value = answer[field];
                }
            });

            const radio = fragment.querySelector('.answer-correct-radio');
            radio.value = key;
            if ((answer.is_correct && Number(answer.is_correct) === 1) || currentCorrectAnswer === key) {
                radio.checked = true;
            }
            radio.addEventListener('change', function () {
                if (radio.checked) {
                    currentCorrectAnswer = key;
                }
            });

            const removeBtn = fragment.querySelector('.remove-answer-btn');
            removeBtn.addEventListener('click', function () {
                if (answersContainer.childElementCount <= 2) {
                    alert('حداقل دو گزینه باید وجود داشته باشد.');
                    return;
                }
                const wasChecked = wrapper.querySelector('.answer-correct-radio').checked;
                wrapper.remove();
                if (wasChecked) {
                    const firstRadio = answersContainer.querySelector('.answer-correct-radio');
                    if (firstRadio) {
                        firstRadio.checked = true;
                        currentCorrectAnswer = firstRadio.value;
                    }
                }
            });

            answersContainer.appendChild(fragment);
        }

        const initialAnswers = Array.isArray(initialAnswersData) ? initialAnswersData : [];
        if (initialAnswers.length) {
            initialAnswers.forEach(function (answer) {
                createAnswerRow(answer);
            });
        }

        if (!answersContainer.childElementCount) {
            createAnswerRow({});
            createAnswerRow({});
        }

        if (!answersContainer.querySelector('.answer-correct-radio:checked')) {
            const firstRadio = answersContainer.querySelector('.answer-correct-radio');
            if (firstRadio) {
                firstRadio.checked = true;
                currentCorrectAnswer = firstRadio.value;
            }
        }

        addAnswerBtn.addEventListener('click', function () {
            createAnswerRow({
                key: createAnswerKey(),
                code: '',
                option: '',
                score_numeric: '',
                score_character: '',
                display_order: answersContainer.childElementCount + 1,
                is_correct: 0
            });
        });
    });
</script>
