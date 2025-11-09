<?php
/** @var array $courseInfo */
/** @var array $toolInfo */
/** @var array $examMeta */
/** @var array $selectedAnswers */
/** @var string $formAction */
/** @var array $enrollment */
/** @var bool $examIsUnlocked */
/** @var bool $hasCompletedAttempt */
/** @var string $examStatusMessage */
/** @var string|null $examStatusDetail */
/** @var bool $formLocked */
/** @var string|null $completedAttemptAt */
/** @var int|null $timerSecondsRemaining */
/** @var bool $timerExpired */

if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$questions = isset($questions) && is_array($questions) ? $questions : [];
$selectedAnswers = isset($selectedAnswers) && is_array($selectedAnswers) ? $selectedAnswers : [];
$csrfToken = AuthHelper::generateCsrfToken();
$durationMinutes = isset($toolInfo['duration_minutes']) && $toolInfo['duration_minutes'] !== ''
    ? (int) $toolInfo['duration_minutes']
    : 0;
$durationDisplay = $durationMinutes > 0
    ? $durationMinutes . ' دقیقه'
    : 'بدون محدودیت زمان';
$isDiscExam = !empty($toolInfo['is_disc']);
$isOptionalExam = !empty($toolInfo['is_optional']);
$timerSecondsRemaining = isset($timerSecondsRemaining) && $timerSecondsRemaining !== null
    ? (int)$timerSecondsRemaining
    : null;
$timerExpired = !empty($timerExpired ?? false);

$breadcrumb = [
    ['title' => 'خانه', 'url' => UtilityHelper::baseUrl('')],
    ['title' => 'دوره ها', 'url' => UtilityHelper::baseUrl('courses')],
    ['title' => $courseInfo['title'], 'url' => UtilityHelper::baseUrl('courses/show?course_id=' . urlencode((string)$courseInfo['id']))],
    ['title' => 'آزمون دوره'],
];

$title = 'آزمون دوره - ' . htmlspecialchars($courseInfo['title']);
$isLocked = !$examIsUnlocked;
?>
<?php include __DIR__ . '/../../layouts/home-header.php'; ?>
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <?php foreach ($breadcrumb as $crumb): ?>
                        <li class="breadcrumb-item<?= empty($crumb['url']) ? ' active' : '' ?>">
                            <?php if (!empty($crumb['url'])): ?>
                                <a href="<?= htmlspecialchars($crumb['url']) ?>"><?= htmlspecialchars($crumb['title']) ?></a>
                            <?php else: ?>
                                <?= htmlspecialchars($crumb['title']) ?>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                        <div>
                            <h1 class="h4 mb-2">آزمون دوره: <?= htmlspecialchars($courseInfo['title']) ?></h1>
                            <p class="mb-1 text-muted">هدف: <?= htmlspecialchars($toolInfo['name']) ?></p>
                            <?php if (!empty($toolInfo['description'])): ?>
                                <p class="mb-0 small text-secondary">توضیحات: <?= nl2br(htmlspecialchars($toolInfo['description'])) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="text-md-right">
                            <span class="badge bg-light text-dark border">مدت زمان: <?= htmlspecialchars($durationDisplay) ?></span>
                            <?php if ($toolInfo['code']): ?>
                                <div class="mt-2 small text-secondary">کد آزمون: <?= htmlspecialchars($toolInfo['code']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($examStatusMessage): ?>
        <div class="row">
            <div class="col-12">
                <div class="alert <?= $isLocked ? 'alert-warning' : 'alert-info' ?>">
                    <div><?= htmlspecialchars($examStatusMessage) ?></div>
                    <?php if (!empty($examStatusDetail)): ?>
                        <div class="small text-muted mt-2">دلیل: <?= nl2br(htmlspecialchars($examStatusDetail)) ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($hasCompletedAttempt): ?>
        <div class="row">
            <div class="col-12">
                <div class="alert alert-success">
                    <div>آزمون این دوره با موفقیت تکمیل شده است.</div>
                    <?php if (!empty($examMeta['attempt_summary'])): ?>
                        <div class="mt-2 small text-muted">نتیجه: <?= htmlspecialchars($examMeta['attempt_summary']) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($completedAttemptAt)): ?>
                        <div class="mt-2 small text-muted">تاریخ اتمام: <?= htmlspecialchars(JalaliHelper::toJalali($completedAttemptAt, 'Y/m/d H:i')) ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>پرسش های آزمون</span>
                    <?php if ($durationMinutes > 0): ?>
                        <?php if ($formLocked): ?>
                            <?php if ($timerExpired): ?>
                                <span class="small text-muted">زمان آزمون به پایان رسیده است.</span>
                            <?php else: ?>
                                <span class="small text-muted">حداکثر زمان آزمون: <?= htmlspecialchars($durationDisplay) ?></span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="small text-muted">زمان باقی مانده: <span id="courseExamCountdown">--:--</span></span>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if ($isLocked): ?>
                        <p class="text-muted mb-0">شرایط لازم برای شروع آزمون هنوز تکمیل نشده است.</p>
                    <?php elseif (empty($questions)): ?>
                        <p class="text-muted mb-0">برای این آزمون هنوز سوالی ثبت نشده است.</p>
                    <?php else: ?>
                        <?php if (!$formLocked): ?>
                            <div id="courseExamExpiredNotice" class="alert alert-warning d-none" role="alert">
                                زمان آزمون به پایان رسیده است؛ امکان ثبت پاسخ جدید وجود ندارد.
                            </div>
                        <?php endif; ?>
                        <form id="courseExamForm" method="post" action="<?= htmlspecialchars($formAction) ?>">
                            <input type="hidden" name="course_id" value="<?= htmlspecialchars((string)$courseInfo['id']) ?>">
                            <input type="hidden" name="action" value="submit_exam">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                            <input type="hidden" name="attempt_id" value="<?= htmlspecialchars((string)($examMeta['attempt_id'] ?? '')) ?>">

                            <?php if ($isDiscExam): ?>
                                <div class="alert alert-secondary">
                                    در هر سوال، یکی از گزینه‌ها را به عنوان «بهترین توصیف» و گزینه دیگری را به عنوان «ضعیف‌ترین توصیف» انتخاب کنید. هر گزینه فقط می‌تواند در یکی از این دو نقش قرار بگیرد.
                                </div>
                            <?php endif; ?>

                            <?php if ($isOptionalExam && !$formLocked): ?>
                                <div class="alert alert-info">
                                    پاسخ به سوالات این آزمون اختیاری است، بنابراین در صورت نیاز می‌توانید بدون انتخاب گزینه از سوال عبور کنید.
                                </div>
                            <?php endif; ?>

                            <?php foreach ($questions as $index => $question): ?>
                                <?php
                                $questionId = (int)($question['id'] ?? 0);
                                if ($questionId <= 0) {
                                    continue;
                                }

                                $questionTitle = trim((string)($question['title'] ?? ''));
                                $questionText = trim((string)($question['text'] ?? ''));
                                $questionDescription = trim((string)($question['description'] ?? ''));
                                $questionImage = $question['image_path'] ?? null;

                                if ($questionTitle !== '' && $questionText !== '') {
                                    $normalizedTitle = preg_replace('/\s+/u', ' ', $questionTitle);
                                    $normalizedText = preg_replace('/\s+/u', ' ', $questionText);
                                    if ($normalizedTitle === $normalizedText) {
                                        $questionText = '';
                                    }
                                }
                                $answers = is_array($question['answers'] ?? null) ? $question['answers'] : [];
                                $isDescriptionOnly = !empty($question['is_description_only']);
                                $questionIndexLabel = UtilityHelper::englishToPersian((string)($question['display_index'] ?? ($index + 1)));

                                $existingAnswer = $selectedAnswers[$questionId] ?? null;
                                $bestSelected = 0;
                                $leastSelected = 0;
                                if (is_array($existingAnswer)) {
                                    $bestSelected = isset($existingAnswer['best']) ? (int)$existingAnswer['best'] : 0;
                                    $leastSelected = isset($existingAnswer['least']) ? (int)$existingAnswer['least'] : 0;
                                }

                                $singleSelected = !is_array($existingAnswer) ? (int)($existingAnswer ?? 0) : 0;
                                $textAnswerValue = !is_array($existingAnswer) ? (string)($existingAnswer ?? '') : '';

                                $imageUrl = null;
                                if (!empty($questionImage)) {
                                    $rawImagePath = trim((string)$questionImage);
                                    if ($rawImagePath !== '') {
                                        if (preg_match('#^(https?:)?//#i', $rawImagePath)) {
                                            $imageUrl = $rawImagePath;
                                        } else {
                                            $normalizedPath = ltrim($rawImagePath, '/');
                                            if (strpos($normalizedPath, 'public/') !== 0) {
                                                $normalizedPath = 'public/' . $normalizedPath;
                                            }
                                            $imageUrl = UtilityHelper::baseUrl($normalizedPath);
                                        }
                                    }
                                }
                                ?>
                                <div class="mb-4">
                                    <div class="mb-2 fw-semibold">سوال <?= htmlspecialchars($questionIndexLabel) ?></div>
                                    <div class="ms-3">
                                        <?php if ($questionTitle !== ''): ?>
                                            <div class="mb-2"><?= htmlspecialchars($questionTitle) ?></div>
                                        <?php endif; ?>
                                        <?php if ($questionText !== ''): ?>
                                            <div class="mb-2"><?= nl2br(htmlspecialchars($questionText)) ?></div>
                                        <?php endif; ?>
                                        <?php if ($questionDescription !== ''): ?>
                                            <div class="mb-2 text-muted small"><?= nl2br(htmlspecialchars($questionDescription)) ?></div>
                                        <?php endif; ?>
                                        <?php if ($imageUrl): ?>
                                            <div class="mb-3 text-center">
                                                <img src="<?= htmlspecialchars($imageUrl) ?>" alt="تصویر سوال" class="img-fluid rounded">
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($isDiscExam && !empty($answers)): ?>
                                            <div class="table-responsive">
                                                <table class="table table-sm align-middle mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th scope="col">گزینه</th>
                                                            <th scope="col" class="text-center">بهترین</th>
                                                            <th scope="col" class="text-center">ضعیف‌ترین</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($answers as $answer): ?>
                                                            <?php
                                                            $answerId = (int)($answer['id'] ?? 0);
                                                            if ($answerId <= 0) {
                                                                continue;
                                                            }
                                                            $answerLabel = trim((string)($answer['text'] ?? 'گزینه'));
                                                            ?>
                                                            <tr>
                                                                <td><?= htmlspecialchars($answerLabel) ?></td>
                                                                <td class="text-center">
                                                                    <input
                                                                        type="radio"
                                                                        class="form-check-input disc-choice-input-best"
                                                                        name="answers[<?= $questionId ?>][best]"
                                                                        id="answer_<?= $questionId ?>_best_<?= $answerId ?>"
                                                                        value="<?= htmlspecialchars((string)$answerId) ?>"
                                                                        <?= $bestSelected === $answerId ? 'checked' : '' ?>
                                                                        <?= $formLocked ? 'disabled' : '' ?>
                                                                    >
                                                                </td>
                                                                <td class="text-center">
                                                                    <input
                                                                        type="radio"
                                                                        class="form-check-input disc-choice-input-least"
                                                                        name="answers[<?= $questionId ?>][least]"
                                                                        id="answer_<?= $questionId ?>_least_<?= $answerId ?>"
                                                                        value="<?= htmlspecialchars((string)$answerId) ?>"
                                                                        <?= $leastSelected === $answerId ? 'checked' : '' ?>
                                                                        <?= $formLocked ? 'disabled' : '' ?>
                                                                    >
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php elseif (!empty($answers) && !$isDescriptionOnly): ?>
                                            <div class="btn-group-vertical w-100" role="group">
                                                <?php foreach ($answers as $answer): ?>
                                                    <?php
                                                    $answerId = (int)($answer['id'] ?? 0);
                                                    if ($answerId <= 0) {
                                                        continue;
                                                    }
                                                    $answerLabel = trim((string)($answer['text'] ?? 'گزینه'));
                                                    $isActive = $singleSelected === $answerId;
                                                    ?>
                                                    <input
                                                        type="radio"
                                                        class="btn-check"
                                                        name="answers[<?= $questionId ?>]"
                                                        id="answer_<?= $questionId ?>_<?= $answerId ?>"
                                                        value="<?= htmlspecialchars((string)$answerId) ?>"
                                                        autocomplete="off"
                                                        <?= $isActive ? 'checked' : '' ?>
                                                        <?= $formLocked ? 'disabled' : '' ?>
                                                    >
                                                    <label
                                                        class="btn btn-outline-primary text-start mb-2 rounded-1<?= $isActive ? ' active' : '' ?>"
                                                        for="answer_<?= $questionId ?>_<?= $answerId ?>"
                                                    >
                                                        <?= htmlspecialchars($answerLabel) ?>
                                                    </label>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php elseif ($isDescriptionOnly): ?>
                                            <p class="text-muted mb-0">این بخش فقط برای مطالعه است و نیاز به انتخاب گزینه ندارد.</p>
                                        <?php else: ?>
                                            <textarea
                                                class="form-control"
                                                name="answers[<?= $questionId ?>]"
                                                rows="4"
                                                <?= $formLocked ? 'disabled' : '' ?>
                                            ><?= htmlspecialchars($textAnswerValue) ?></textarea>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary" <?= $formLocked ? 'disabled' : '' ?>>ثبت آزمون</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($durationMinutes > 0 && !$formLocked && $timerSecondsRemaining !== null): ?>
    <script>
        (function () {
            var countdownElement = document.getElementById('courseExamCountdown');
            var formElement = document.getElementById('courseExamForm');
            if (!countdownElement || !formElement) {
                return;
            }

            var totalSeconds = <?= (int) max(0, $timerSecondsRemaining) ?>;
            var expiredNotice = document.getElementById('courseExamExpiredNotice');

            function formatTime(seconds) {
                var mins = Math.floor(seconds / 60);
                var secs = seconds % 60;
                return (mins < 10 ? '0' + mins : mins) + ':' + (secs < 10 ? '0' + secs : secs);
            }

            function disableForm() {
                if (expiredNotice) {
                    expiredNotice.classList.remove('d-none');
                }

                var fields = formElement.querySelectorAll('input, textarea, button');
                fields.forEach(function (field) {
                    field.setAttribute('disabled', 'disabled');
                });
            }

            countdownElement.textContent = formatTime(totalSeconds);

            var intervalId = window.setInterval(function () {
                totalSeconds -= 1;

                if (totalSeconds <= 0) {
                    countdownElement.textContent = '00:00';
                    window.clearInterval(intervalId);
                    disableForm();
                    return;
                }

                countdownElement.textContent = formatTime(totalSeconds);
            }, 1000);
        })();
    </script>
<?php endif; ?>
<?php include __DIR__ . '/../../layouts/home-footer.php'; ?>
