<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'آزمون‌ها';
$additional_css = $additional_css ?? [];
$additional_js = $additional_js ?? [];
$inline_styles = $inline_styles ?? '';
$inline_scripts = $inline_scripts ?? '';

$inline_styles .= <<<'CSS'
    .exam-progress-card {
        border-radius: 24px;
        border: 1px solid rgba(148, 163, 184, 0.2);
        box-shadow: 0 18px 32px rgba(15, 23, 42, 0.06);
        background-color: #ffffff;
    }
    .exam-progress-card .card-body {
        padding: 28px;
    }
    .progress-headline {
        font-size: 1.25rem;
        font-weight: 700;
        color: #0f172a;
    }
    .progress-summary {
        font-size: 0.95rem;
        color: #475569;
    }
    .exam-steps-line {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
    }
    .exam-step-badge {
        border-radius: 18px;
        padding: 16px 18px;
        min-width: 220px;
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.08), rgba(59, 130, 246, 0.02));
        border: 1px solid rgba(59, 130, 246, 0.15);
        color: #0f172a;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .exam-step-badge a {
        color: inherit;
        text-decoration: none;
        display: block;
    }
    .exam-step-badge.is-current {
        border-color: rgba(59, 130, 246, 0.45);
        box-shadow: 0 14px 32px rgba(59, 130, 246, 0.15);
    }
    .exam-step-badge.is-complete {
        background: linear-gradient(135deg, rgba(34, 197, 94, 0.12), rgba(34, 197, 94, 0.04));
        border-color: rgba(34, 197, 94, 0.35);
    }
    .exam-step-badge.is-disabled {
        cursor: default;
        opacity: 0.75;
    }
    .exam-step-title {
        font-weight: 600;
        margin-bottom: 6px;
    }
    .exam-step-status {
        font-size: 0.85rem;
        color: #475569;
    }
    .exam-details-card {
        border-radius: 24px;
        border: 1px solid rgba(148, 163, 184, 0.18);
        box-shadow: 0 22px 32px rgba(15, 23, 42, 0.06);
    }
    .exam-details-card .card-body {
        padding: 30px;
    }
    .rounded-20 {
        border-radius: 20px !important;
    }
    .rounded-24 {
        border-radius: 24px !important;
    }
    .exam-locked-note {
        margin-bottom: 1.5rem;
    }
    .exam-questions-wrapper {
        transition: opacity 0.2s ease;
    }
    .question-card {
        border: 1px solid rgba(226, 232, 240, 0.9);
        border-radius: 20px;
        background-color: #f8fafc;
        padding: 24px;
        margin-bottom: 20px;
    }
    .question-index {
        font-weight: 600;
        color: #2563eb;
        margin-bottom: 12px;
    }
    .question-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 12px;
        color: #0f172a;
    }
    .question-text {
        font-size: 1rem;
        color: #1e293b;
        margin-bottom: 12px;
    }
    .question-description {
        font-size: 0.95rem;
        color: #475569;
        background: rgba(148, 163, 184, 0.12);
        padding: 12px 16px;
        border-radius: 14px;
        margin-bottom: 16px;
    }
    .question-image {
        margin-bottom: 16px;
        text-align: center;
    }
    .answers-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: grid;
        gap: 12px;
    }
    .answer-option {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 12px 16px;
        border-radius: 14px;
        border: 1px solid rgba(148, 163, 184, 0.35);
        background-color: #ffffff;
        cursor: pointer;
        transition: border-color 0.2s ease, background-color 0.2s ease;
    }
    .answer-option input {
        accent-color: #2563eb;
    }
    .answer-option:hover {
        border-color: rgba(59, 130, 246, 0.55);
        background-color: rgba(59, 130, 246, 0.08);
    }
    .answer-option.disabled {
        cursor: not-allowed;
        opacity: 0.65;
    }
    .disc-answers-list {
        display: grid;
        gap: 16px;
        margin-top: 12px;
    }
    .disc-answer-row {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 16px;
        align-items: center;
        background-color: #ffffff;
        border: 1px solid rgba(148, 163, 184, 0.3);
        border-radius: 16px;
        padding: 14px 16px;
    }
    .disc-answer-text {
        flex: 1 1 auto;
        color: #0f172a;
        font-weight: 500;
    }
    .disc-answer-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }
    .disc-choice-btn.disabled {
        opacity: 0.6;
        pointer-events: none;
    }
    .exam-empty-state {
        text-align: center;
        padding: 32px;
        border-radius: 20px;
        background-color: rgba(241, 245, 249, 0.8);
        color: #475569;
    }
    .question-progress-meter .progress {
        height: 12px;
        background-color: rgba(226, 232, 240, 0.9);
    }
    .question-progress-counter {
        font-weight: 600;
        color: #0f172a;
    }
    .question-nav-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }
    #examFeedback {
        transition: opacity 0.2s ease;
    }
    #examFeedback.exam-feedback-visible {
        opacity: 1;
    }
    /* Timer styles */
    .exam-timer-badge {
        background: rgba(241, 245, 249, 0.9);
        border: 1px solid rgba(148, 163, 184, 0.35);
        color: #0f172a; /* black-ish */
        font-weight: 800;
        font-size: 1.25rem; /* bigger */
    }
    #examTimer {
        font-variant-numeric: tabular-nums;
        letter-spacing: 0.5px;
    }
    .exam-timer-badge.timer-warning {
        background: rgba(254, 226, 226, 0.85); /* light red */
        border-color: rgba(220, 38, 38, 0.35);
    }
    .exam-timer-badge.timer-warning #examTimer {
        color: #dc2626; /* red */
    }
    .exam-timer-badge.timer-warning #examTimer.timer-blink {
        animation: timerBlink 1s steps(1) infinite;
    }
    @keyframes timerBlink {
        0%, 49% { color: #dc2626; }
        50%, 100% { color: #0f172a; }
    }
    @media (max-width: 768px) {
        .exam-step-badge {
            min-width: 100%;
        }
        .disc-answer-row {
            flex-direction: column;
            align-items: flex-start;
        }
        .disc-answer-actions {
            width: 100%;
            justify-content: flex-start;
        }
    }
CSS;

$inline_scripts .= <<<'JS'
    document.addEventListener('DOMContentLoaded', function () {
        'use strict';

        const examForm = document.getElementById('examQuestionsForm');
        if (!examForm) {
            return;
        }

        const questionCards = Array.from(examForm.querySelectorAll('.question-card'));
        const totalQuestions = Number.parseInt(examForm.dataset.totalQuestions || '0', 10);
        let currentIndex = Math.min(
            Math.max(Number.parseInt(examForm.dataset.currentIndex || '0', 10) || 0, 0),
            Math.max(questionCards.length - 1, 0)
        );

    const allowAjax = !!examForm.dataset.ajaxUrl;
    const allowFinish = examForm.dataset.allowFinish === '1';
    const examCompleted = examForm.dataset.completed === '1';
    const ajaxUrl = examForm.dataset.ajaxUrl || '';
    const questionsInitiallyLocked = examForm.dataset.questionsLocked === '1';
    const isOptionalExam = examForm.dataset.optional === '1';

        const prevButton = document.getElementById('questionPrevButton');
        const nextButton = document.getElementById('questionNextButton');
        const finishButton = document.getElementById('examFinishButton');
        const progressCounter = document.getElementById('questionProgressCounter');
        const progressFill = document.getElementById('questionProgressFill');
        const progressPercentLabel = document.getElementById('questionProgressPercent');
        const answeredCountLabel = document.getElementById('questionAnsweredCount');
        const feedbackBox = document.getElementById('examFeedback');
        const lockedNotice = document.getElementById('examLockedNotice');
        const startExamButton = document.getElementById('startExamButton');
        const instructionsModalEl = document.getElementById('examInstructionsModal');

    const actionInput = document.getElementById('examActionInput');
    const directionInput = document.getElementById('examDirectionInput');
    const questionIdInput = document.getElementById('examQuestionIdInput');
    const targetIndexInput = document.getElementById('examTargetIndexInput');
    const expiredInput = document.getElementById('examExpiredInput');
    const timerEl = document.getElementById('examTimer');
    const timerBadgeEl = document.getElementById('examTimerBadge');
    const durationSeconds = Number.parseInt(examForm.dataset.durationSeconds || '0', 10);

        let lastSyncController = null;
    let isFinishingExam = false;
        // Timer state
        let timerInterval = null;
        let timerDeadline = 0;
        let timerStarted = false;

        function createDebounced(fn, wait) {
            let timeoutId = null;
            let lastArgs = [];
            let lastContext = null;

            function debounced() {
                lastArgs = Array.from(arguments);
                lastContext = this;
                if (timeoutId !== null) {
                    window.clearTimeout(timeoutId);
                }
                timeoutId = window.setTimeout(function () {
                    timeoutId = null;
                    fn.apply(lastContext, lastArgs);
                }, wait);
            }

            debounced.flush = function () {
                if (timeoutId !== null) {
                    window.clearTimeout(timeoutId);
                    timeoutId = null;
                    fn.apply(lastContext, lastArgs);
                }
            };

            return debounced;
        }

        function getActiveCard() {
            return questionCards[currentIndex] || null;
        }

        function formatTimeLeft(totalSeconds) {
            totalSeconds = Math.max(0, Math.floor(totalSeconds));
            const mm = String(Math.floor(totalSeconds / 60)).padStart(2, '0');
            const ss = String(totalSeconds % 60).padStart(2, '0');
            return mm + ':' + ss;
        }

        function handleTimeExpired() {
            if (examCompleted) return;
            if (timerInterval) {
                clearInterval(timerInterval);
                timerInterval = null;
            }
            isFinishingExam = true;
            if (expiredInput) expiredInput.value = '1';
            if (actionInput) actionInput.value = 'finish_exam';
            if (directionInput) directionInput.value = '';
            if (questionIdInput) questionIdInput.value = '';
            if (targetIndexInput) targetIndexInput.value = '';
            try { showFeedback('زمان آزمون به پایان رسید. در حال ثبت پاسخ‌ها...', 'warning'); } catch (e) {}
            try { if (typeof autosaveQuestion !== 'undefined' && autosaveQuestion && typeof autosaveQuestion.flush === 'function') { autosaveQuestion.flush(); } } catch (e) {}
            try { examForm.submit(); } catch (e) {}
        }

        const warningThresholdSec = (function () {
            if (!(durationSeconds > 0)) return 0;
            const twentyPercent = Math.floor(durationSeconds * 0.2);
            return Math.min(300, twentyPercent); // min(5 minutes, 20% of duration)
        })();

        function updateTimer() {
            if (!timerEl) return;
            const now = Date.now();
            const remainingMs = Math.max(0, timerDeadline - now);
            const remainingSec = Math.ceil(remainingMs / 1000);
            timerEl.textContent = formatTimeLeft(remainingSec);
            if (timerBadgeEl) {
                const isWarning = warningThresholdSec > 0 && remainingSec <= warningThresholdSec;
                timerBadgeEl.classList.toggle('timer-warning', isWarning);
                if (isWarning) {
                    timerEl.classList.add('timer-blink');
                } else {
                    timerEl.classList.remove('timer-blink');
                }
            }
            if (remainingMs <= 0) {
                handleTimeExpired();
            }
        }

        function startTimer() {
            if (timerStarted) return;
            if (examCompleted) return;
            if (!timerEl) return;
            if (!(durationSeconds > 0)) return;
            timerStarted = true;
            timerDeadline = Date.now() + (durationSeconds * 1000);
            updateTimer();
            timerInterval = setInterval(updateTimer, 1000);
        }

        function collectAnswers() {
            const payload = {};
            questionCards.forEach(function (card) {
                const questionId = card.dataset.questionId;
                if (!questionId) {
                    return;
                }
                if (card.dataset.questionDisc === '1') {
                    const best = card.querySelector('.disc-choice-input-best:checked');
                    const least = card.querySelector('.disc-choice-input-least:checked');
                    payload[questionId] = {
                        best: best ? best.value : null,
                        least: least ? least.value : null,
                    };
                } else {
                    const single = card.querySelector('.single-choice-input:checked');
                    payload[questionId] = single ? single.value : null;
                }
            });
            return payload;
        }

        function escapeCssValue(value) {
            if (window.CSS && typeof window.CSS.escape === 'function') {
                return window.CSS.escape(value);
            }
            return String(value).replace(/([^a-zA-Z0-9_-])/g, '\\$1');
        }

        function saveLocalState() {}

        function loadLocalState() {
            return null;
        }

        function clearLocalState() {}

        function applyStateToForm(state) {
            if (!state || typeof state !== 'object') {
                return;
            }
            const answers = state.answers || {};
            Object.keys(answers).forEach(function (questionId) {
                const card = questionCards.find(function (item) {
                    return item.dataset.questionId === questionId;
                });
                if (!card) {
                    return;
                }

                if (card.dataset.questionDisc === '1') {
                    const bestValue = answers[questionId] && answers[questionId].best ? String(answers[questionId].best) : null;
                    const leastValue = answers[questionId] && answers[questionId].least ? String(answers[questionId].least) : null;
                    card.querySelectorAll('.disc-choice-input-best').forEach(function (input) {
                        input.checked = bestValue !== null && input.value === bestValue;
                    });
                    card.querySelectorAll('.disc-choice-input-least').forEach(function (input) {
                        input.checked = leastValue !== null && input.value === leastValue;
                    });
                    card.dispatchEvent(new Event('change', { bubbles: true }));
                } else {
                    const value = answers[questionId];
                    if (value === null || typeof value === 'undefined') {
                        return;
                    }
                    const input = card.querySelector('.single-choice-input[value="' + escapeCssValue(String(value)) + '"]');
                    if (input) {
                        input.checked = true;
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }
            });

            if (Number.isInteger(state.currentIndex) && state.currentIndex >= 0 && state.currentIndex < questionCards.length) {
                currentIndex = state.currentIndex;
            }
        }

        function clearFeedback() {
            if (!feedbackBox) {
                return;
            }
            feedbackBox.classList.add('d-none');
            feedbackBox.classList.remove('alert-warning', 'alert-danger', 'alert-success', 'exam-feedback-visible');
            feedbackBox.textContent = '';
        }

        function showFeedback(message, type) {
            if (!feedbackBox || !message) {
                return;
            }
            const alertType = type || 'warning';
            feedbackBox.classList.remove('d-none', 'alert-warning', 'alert-danger', 'alert-success');
            feedbackBox.classList.add('alert', `alert-${alertType}`, 'exam-feedback-visible');
            feedbackBox.textContent = message;
        }

        function isCardAnswered(card) {
            if (!card) {
                return false;
            }
            if (card.dataset.questionDisc === '1') {
                const best = card.querySelector('.disc-choice-input-best:checked');
                const least = card.querySelector('.disc-choice-input-least:checked');
                return !!(best && least && best.value !== least.value);
            }
            const single = card.querySelector('.single-choice-input:checked');
            return !!single;
        }

        function updateProgressUI() {
            const totalQuestions = questionCards.length;
            const requiredCards = questionCards.filter(function (card) {
                return card.dataset.required === '1';
            });
            const trackedCards = requiredCards.length > 0 ? requiredCards : questionCards;
            let answered = 0;
            trackedCards.forEach(function (card) {
                if (isCardAnswered(card)) {
                    answered += 1;
                }
            });
            const denominator = trackedCards.length;
            const percent = denominator > 0 ? Math.round((answered / denominator) * 100) : 0;

            if (progressCounter) {
                const displayIndex = totalQuestions > 0 ? currentIndex + 1 : 0;
                progressCounter.textContent = totalQuestions > 0
                    ? `سوال ${displayIndex.toLocaleString('fa-IR')} از ${totalQuestions.toLocaleString('fa-IR')}`
                    : 'بدون سوال';
            }
            if (answeredCountLabel) {
                answeredCountLabel.textContent = answered.toLocaleString('fa-IR');
            }
            if (progressPercentLabel) {
                progressPercentLabel.textContent = percent.toLocaleString('fa-IR') + '٪';
            }
            if (progressFill) {
                progressFill.style.width = `${percent}%`;
                progressFill.setAttribute('aria-valuenow', String(percent));
            }
        }

        function updateNavigationButtons() {
            if (prevButton) {
                if (currentIndex <= 0) {
                    prevButton.classList.add('d-none');
                } else {
                    prevButton.classList.remove('d-none');
                }
                prevButton.disabled = examCompleted;
            }

            if (nextButton) {
                if (currentIndex >= questionCards.length - 1) {
                    nextButton.classList.add('d-none');
                } else {
                    nextButton.classList.remove('d-none');
                }
                nextButton.disabled = examCompleted;
            }

            if (finishButton) {
                const shouldShow = allowFinish && !examCompleted && currentIndex >= questionCards.length - 1 && questionCards.length > 0;
                finishButton.classList.toggle('d-none', !shouldShow);
            }
        }

        function showQuestion(index) {
            if (!questionCards.length) {
                return;
            }
            currentIndex = Math.max(0, Math.min(questionCards.length - 1, index));
            questionCards.forEach(function (card, idx) {
                if (idx === currentIndex) {
                    card.classList.remove('d-none');
                } else {
                    card.classList.add('d-none');
                }
            });
            examForm.dataset.currentIndex = String(currentIndex);
            clearFeedback();
            updateNavigationButtons();
            updateProgressUI();
        }

        function validateDiscQuestion(card) {
            const best = card.querySelector('.disc-choice-input-best:checked');
            const least = card.querySelector('.disc-choice-input-least:checked');

            if (!best || !least) {
                showFeedback('برای ادامه، لطفاً بهترین و ضعیف‌ترین توصیف را مشخص کنید.', 'warning');
                return false;
            }

            if (best.value === least.value) {
                showFeedback('گزینه انتخاب‌شده نمی‌تواند هم زمان بهترین و ضعیف‌ترین باشد.', 'warning');
                return false;
            }

            return true;
        }

        function validateSingleChoiceQuestion(card) {
            const choice = card.querySelector('.single-choice-input:checked');
            if (!choice) {
                showFeedback('برای ادامه، یکی از گزینه‌های سوال را انتخاب کنید.', 'warning');
                return false;
            }
            return true;
        }

        function validateQuestion(card) {
            if (!card || card.dataset.required !== '1' || examCompleted) {
                return true;
            }
            if (card.dataset.questionDisc === '1') {
                return validateDiscQuestion(card);
            }
            return validateSingleChoiceQuestion(card);
        }

        function captureQuestionId(card) {
            if (!card || !questionIdInput) {
                return;
            }
            questionIdInput.value = card.dataset.questionId || '';
        }

        async function syncSession(action, direction, targetIndex, options) {
            const opts = Object.assign({
                silent: false,
                abortPending: true,
                includeQuestion: true,
            }, options || {});

            if (!allowAjax || examCompleted || !ajaxUrl || !actionInput) {
                return;
            }

            if (examForm.dataset.questionsLocked === '1') {
                return;
            }

            if (opts.abortPending && lastSyncController) {
                lastSyncController.abort();
            }

            const controller = new AbortController();
            lastSyncController = controller;

            if (opts.includeQuestion) {
                const activeCard = getActiveCard();
                captureQuestionId(activeCard);
            }

            actionInput.value = action;
            if (directionInput) {
                directionInput.value = direction || '';
            }
            if (targetIndexInput) {
                targetIndexInput.value = typeof targetIndex === 'number' ? String(targetIndex) : '';
            }

            const formData = new FormData(examForm);

            try {
                await fetch(ajaxUrl, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin',
                    redirect: 'follow',
                    signal: controller.signal,
                });
            } catch (error) {
                if (error.name !== 'AbortError') {
                    console.warn('sync failed', error);
                    if (!opts.silent) {
                        showFeedback('ذخیره‌سازی پاسخ با مشکل مواجه شد. اتصال اینترنت را بررسی کنید.', 'warning');
                    }
                }
            } finally {
                if (lastSyncController === controller) {
                    lastSyncController = null;
                }
                actionInput.value = 'navigate_question';
                if (directionInput) {
                    directionInput.value = '';
                }
                if (questionIdInput) {
                    questionIdInput.value = '';
                }
                if (targetIndexInput) {
                    targetIndexInput.value = '';
                }
            }
        }

        const autosaveQuestion = createDebounced(function () {
            if (isFinishingExam) {
                saveLocalState();
                return;
            }
            if (!allowAjax || examCompleted || examForm.dataset.questionsLocked === '1') {
                saveLocalState();
                return;
            }
            saveLocalState();
            syncSession('navigate_question', '', currentIndex, { silent: true });
        }, 400);
        const originalAutosaveFlush = autosaveQuestion.flush;
        autosaveQuestion.flush = function () {
            if (isFinishingExam) {
                saveLocalState();
                return;
            }
            if (typeof originalAutosaveFlush === 'function') {
                originalAutosaveFlush();
            }
        };

        function setupDiscQuestion(card) {
            if (!card) {
                return;
            }
            const bestInputs = card.querySelectorAll('.disc-choice-input-best');
            const leastInputs = card.querySelectorAll('.disc-choice-input-least');

            const allInputs = Array.from(bestInputs).concat(Array.from(leastInputs));
            allInputs.forEach(function (input) {
                input.dataset.initialDisabled = input.disabled ? '1' : '0';
                const relatedLabel = card.querySelector(`label[for="${input.id}"]`);
                if (relatedLabel) {
                    relatedLabel.dataset.initialDisabled = relatedLabel.classList.contains('disabled') ? '1' : '0';
                }
            });

            function toggleDisabledStates() {
                const bestSelected = card.querySelector('.disc-choice-input-best:checked');
                const leastSelected = card.querySelector('.disc-choice-input-least:checked');

                leastInputs.forEach(function (input) {
                    if (input.dataset.initialDisabled === '1') {
                        return;
                    }
                    const label = card.querySelector(`label[for="${input.id}"]`);
                    if (bestSelected && input.value === bestSelected.value && !input.checked) {
                        input.disabled = true;
                        if (label) {
                            label.classList.add('disabled');
                            label.setAttribute('aria-disabled', 'true');
                        }
                    } else {
                        input.disabled = false;
                        if (label && label.dataset.initialDisabled !== '1') {
                            label.classList.remove('disabled');
                            label.removeAttribute('aria-disabled');
                        }
                    }
                });

                bestInputs.forEach(function (input) {
                    if (input.dataset.initialDisabled === '1') {
                        return;
                    }
                    const label = card.querySelector(`label[for="${input.id}"]`);
                    if (leastSelected && input.value === leastSelected.value && !input.checked) {
                        input.disabled = true;
                        if (label) {
                            label.classList.add('disabled');
                            label.setAttribute('aria-disabled', 'true');
                        }
                    } else {
                        input.disabled = false;
                        if (label && label.dataset.initialDisabled !== '1') {
                            label.classList.remove('disabled');
                            label.removeAttribute('aria-disabled');
                        }
                    }
                });
            }

            card.addEventListener('change', function (event) {
                const target = event.target;
                if (!(target instanceof HTMLInputElement)) {
                    return;
                }
                if (target.classList.contains('disc-choice-input-best') || target.classList.contains('disc-choice-input-least')) {
                    toggleDisabledStates();
                    clearFeedback();
                    updateProgressUI();
                    autosaveQuestion();
                }
            });

            toggleDisabledStates();
        }

        function applyLocalProgress() {
            const state = loadLocalState();
            if (state) {
                applyStateToForm(state);
            }
        }

        if (examCompleted) {
            clearLocalState();
        } else {
            applyLocalProgress();
        }

        questionCards.forEach(function (card) {
            if (card.dataset.questionDisc !== '1') {
                card.addEventListener('change', function (event) {
                    const target = event.target;
                    if (!(target instanceof HTMLInputElement)) {
                        return;
                    }
                    if (target.classList.contains('single-choice-input')) {
                        clearFeedback();
                        updateProgressUI();
                        autosaveQuestion();
                    }
                });
            }
        });

        questionCards.forEach(function (card) {
            if (card.dataset.questionDisc === '1') {
                setupDiscQuestion(card);
            }
        });

        showQuestion(currentIndex);
        updateNavigationButtons();
        updateProgressUI();
        saveLocalState();

        if (prevButton) {
            prevButton.addEventListener('click', async function () {
                const targetIdx = Math.max(0, currentIndex - 1);
                await syncSession('navigate_previous', 'previous', targetIdx, { silent: false, abortPending: true });
                showQuestion(targetIdx);
                saveLocalState();
            });
        }

        if (nextButton) {
            nextButton.addEventListener('click', async function () {
                const currentCard = questionCards[currentIndex];
                if (!validateQuestion(currentCard)) {
                    return;
                }
                const targetIdx = Math.min(questionCards.length - 1, currentIndex + 1);
                await syncSession('navigate_next', 'next', targetIdx, { silent: false, abortPending: true });
                showQuestion(targetIdx);
                saveLocalState();
            });
        }

        if (finishButton) {
            finishButton.addEventListener('click', function () {
                isFinishingExam = true;
                const currentCard = questionCards[currentIndex];
                if (!validateQuestion(currentCard)) {
                    isFinishingExam = false;
                    return;
                }
                if (actionInput) {
                    actionInput.value = 'finish_exam';
                }
                if (directionInput) {
                    directionInput.value = '';
                }
                if (questionIdInput) {
                    questionIdInput.value = '';
                }
                if (targetIndexInput) {
                    targetIndexInput.value = '';
                }
                saveLocalState();
            });
        }

        examForm.addEventListener('submit', function (event) {
            if (examCompleted) {
                return;
            }
            if (!finishButton || actionInput.value !== 'finish_exam') {
                event.preventDefault();
                return;
            }
            if (expiredInput && expiredInput.value === '1') {
                try { clearLocalState(); } catch (e) {}
                return;
            }
            const unanswered = [];
            questionCards.forEach(function (card, index) {
                if (card.dataset.required !== '1') {
                    return;
                }
                if (card.dataset.questionDisc === '1') {
                    const best = card.querySelector('.disc-choice-input-best:checked');
                    const least = card.querySelector('.disc-choice-input-least:checked');
                    if (!(best && least && best.value !== least.value)) {
                        unanswered.push(index);
                    }
                } else {
                    const choice = card.querySelector('.single-choice-input:checked');
                    if (!choice) {
                        unanswered.push(index);
                    }
                }
            });

            if (unanswered.length > 0) {
                event.preventDefault();
                const firstUnanswered = unanswered[0];
                showQuestion(firstUnanswered);
                showFeedback('پیش از پایان آزمون، لطفاً به تمامی سوالات پاسخ دهید.', 'warning');
            } else {
                clearLocalState();
            }
        });

        if (startExamButton) {
            startExamButton.addEventListener('click', function () {
                clearFeedback();
                if (lockedNotice) {
                    lockedNotice.classList.add('d-none');
                }
                examForm.classList.remove('d-none');
                examForm.dataset.questionsLocked = '0';
                saveLocalState();

                if (instructionsModalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const instance = bootstrap.Modal.getInstance(instructionsModalEl);
                    if (instance) {
                        instance.hide();
                    }
                }
                if (durationSeconds > 0) {
                    startTimer();
                }
            });
        }

        if (instructionsModalEl && instructionsModalEl.dataset.autoshow === '1') {
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const modal = new bootstrap.Modal(instructionsModalEl, {
                    backdrop: 'static',
                    keyboard: false
                });
                modal.show();
            }
        }

        if (!questionsInitiallyLocked && !examCompleted) {
            autosaveQuestion();
            if (durationSeconds > 0) {
                startTimer();
            }
        }

        window.addEventListener('beforeunload', function () {
            saveLocalState();
            autosaveQuestion.flush();
        });

        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'hidden') {
                saveLocalState();
            }
        });
    });
JS;

AuthHelper::startSession();
$user = AuthHelper::getUser();
$navbarUser = $user;

$examSteps = is_array($examSteps ?? null) ? $examSteps : [];
$progressHeadline = trim((string) ($progressHeadline ?? 'وضعیت آزمون'));
$progressSummary = trim((string) ($progressSummary ?? 'پیشرفت آزمون خود را در اینجا دنبال کنید.'));
$currentTool = $currentTool ?? null;
$currentToolId = isset($currentToolId) ? (int) $currentToolId : (int) ($currentTool['tool_id'] ?? 0);
$currentToolIsDisc = !empty($currentToolIsDisc);
$currentToolIsOptional = !empty($currentToolIsOptional);
$currentToolIsCompleted = !empty($currentToolIsCompleted);
$questionsLocked = !empty($questionsLocked);
$allowFinish = !empty($allowFinish);
$selectedAnswers = is_array($selectedAnswers ?? null) ? $selectedAnswers : [];
$questions = is_array($questions ?? null) ? $questions : [];
$currentQuestionIndex = (int) ($currentQuestionIndex ?? 0);
$totalQuestions = count($questions);
$evaluationId = (int) ($evaluationId ?? 0);
$examIntroText = trim((string) ($examIntroText ?? ''));
$evaluationTitle = trim((string) ($evaluationTitle ?? ''));
$calendarUrl = $calendarUrl ?? UtilityHelper::baseUrl('tests/training-calendar');
$currentUserId = (int) ($user['id'] ?? 0);
$currentOrgUserId = (int) ($user['organization_user_id'] ?? 0);
$allExamsCompleted = !empty($allExamsCompleted);
$requiredQuestionsCount = 0;
$answeredRequiredCount = 0;
$answeredOptionalCount = 0;
$examDurationSeconds = (int) ($examDurationSeconds ?? 0);
$initialTimerText = '';
if ($examDurationSeconds > 0 && !$currentToolIsCompleted) {
    $mm = str_pad((string) floor($examDurationSeconds / 60), 2, '0', STR_PAD_LEFT);
    $ss = str_pad((string) ($examDurationSeconds % 60), 2, '0', STR_PAD_LEFT);
    $initialTimerText = $mm . ':' . $ss;
}
if ($totalQuestions > 0) {
    foreach ($questions as $question) {
        $questionId = (int) ($question['id'] ?? 0);
        if ($questionId <= 0) {
            continue;
        }

        $isDescriptionOnly = !empty($question['is_description_only']);
        $questionHasAnswers = !empty($question['answers']);
        $questionRequiresAnswer = !$currentToolIsOptional && !$isDescriptionOnly && $questionHasAnswers;

        $isAnswered = false;

        if ($currentToolIsDisc) {
            $answer = $selectedAnswers[$questionId] ?? null;
            if (is_array($answer)) {
                $best = (int) ($answer['best'] ?? 0);
                $least = (int) ($answer['least'] ?? 0);
                $isAnswered = ($best > 0 && $least > 0 && $best !== $least);
            }
        } else {
            $answerId = (int) ($selectedAnswers[$questionId] ?? 0);
            $isAnswered = ($answerId > 0);
        }

        if ($questionRequiresAnswer) {
            $requiredQuestionsCount++;
            if ($isAnswered) {
                $answeredRequiredCount++;
            }
        } elseif ($isAnswered) {
            $answeredOptionalCount++;
        }
    }
}

$trackedQuestionsCount = $requiredQuestionsCount > 0 ? $requiredQuestionsCount : $totalQuestions;
$answeredCount = $requiredQuestionsCount > 0 ? $answeredRequiredCount : ($answeredRequiredCount + $answeredOptionalCount);
$progressPercent = $trackedQuestionsCount > 0 ? max(0, min(100, round(($answeredCount / $trackedQuestionsCount) * 100))) : 0;
$csrfToken = AuthHelper::generateCsrfToken();
$shouldAutoShowIntro = ($questionsLocked && !$currentToolIsCompleted);

include __DIR__ . '/../../layouts/home-header.php';
include __DIR__ . '/../../layouts/home-sidebar.php';
?>

<?php include __DIR__ . '/../../layouts/home-navbar.php'; ?>

<div class="page-content-wrapper">
    <div class="page-content">
        <div class="row g-4">
            <div class="col-12">
                <div class="card exam-progress-card border-0">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                            <div>
                                <div class="progress-headline mb-1">
                                    <?= htmlspecialchars($progressHeadline, ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                                <p class="progress-summary mb-0">
                                    <?= htmlspecialchars($progressSummary, ENT_QUOTES, 'UTF-8'); ?>
                                </p>
                            </div>
                            <div class="text-end small text-secondary d-flex flex-column gap-1">
                                <?php if ($evaluationTitle !== ''): ?>
                                    <div>
                                        <span class="fw-semibold text-dark">عنوان ارزیابی:</span>
                                        <span><?= htmlspecialchars($evaluationTitle, ENT_QUOTES, 'UTF-8'); ?></span>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <span class="fw-semibold text-dark">سوالات پاسخ داده شده:</span>
                                    <span><span id="questionAnsweredCount"><?= htmlspecialchars(UtilityHelper::englishToPersian((string) $answeredCount), ENT_QUOTES, 'UTF-8'); ?></span> از <?= htmlspecialchars(UtilityHelper::englishToPersian((string) $trackedQuestionsCount), ENT_QUOTES, 'UTF-8'); ?><?= $currentToolIsOptional ? ' (اختیاری)' : ''; ?></span>
                                </div>
                                <div>
                                    <span class="fw-semibold text-dark">پیشرفت:</span>
                                    <span id="questionProgressPercent"><?= htmlspecialchars(UtilityHelper::englishToPersian((string) $progressPercent), ENT_QUOTES, 'UTF-8'); ?>٪</span>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($examSteps)): ?>
                            <div class="exam-steps-line mb-4">
                                <?php foreach ($examSteps as $step): ?>
                                    <?php
                                        $stepStatus = $step['status'] ?? 'upcoming';
                                        $stepClass = 'exam-step-badge';
                                        if ($stepStatus === 'complete') {
                                            $stepClass .= ' is-complete';
                                        } elseif ($stepStatus === 'current') {
                                            $stepClass .= ' is-current';
                                        }
                                        $stepName = $step['name'] ?? '';
                                        $statusLabel = 'در انتظار';
                                        if ($stepStatus === 'complete') {
                                            $statusLabel = 'تکمیل شده';
                                        } elseif ($stepStatus === 'current') {
                                            $statusLabel = 'در حال برگزاری';
                                        }
                                        $isClickable = !empty($step['is_clickable']) && !empty($step['link']);
                                        if (!$isClickable && $stepStatus === 'complete') {
                                            $stepClass .= ' is-disabled';
                                        }
                                    ?>
                                    <div class="<?= htmlspecialchars($stepClass, ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php if ($isClickable): ?>
                                            <a href="<?= htmlspecialchars($step['link'], ENT_QUOTES, 'UTF-8'); ?>">
                                                <div class="exam-step-title">
                                                    <?= htmlspecialchars($stepName, ENT_QUOTES, 'UTF-8'); ?>
                                                </div>
                                                <div class="exam-step-status">
                                                    <?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8'); ?>
                                                </div>
                                            </a>
                                        <?php else: ?>
                                            <div class="exam-step-title">
                                                <?= htmlspecialchars($stepName, ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                            <div class="exam-step-status">
                                                <?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div class="question-progress-meter">
                            <div class="progress rounded-pill">
                                <div id="questionProgressFill"
                                     class="progress-bar bg-primary"
                                     role="progressbar"
                                     style="width: <?= htmlspecialchars((string) $progressPercent, ENT_QUOTES, 'UTF-8'); ?>%;"
                                     aria-valuenow="<?= htmlspecialchars((string) $progressPercent, ENT_QUOTES, 'UTF-8'); ?>"
                                     aria-valuemin="0"
                                     aria-valuemax="100"></div>
                            </div>
                            <div id="questionProgressCounter" class="question-progress-counter mt-3">
                                <?php if ($totalQuestions > 0): ?>
                                    سوال <?= htmlspecialchars(UtilityHelper::englishToPersian((string) ($currentQuestionIndex + 1)), ENT_QUOTES, 'UTF-8'); ?> از <?= htmlspecialchars(UtilityHelper::englishToPersian((string) $totalQuestions), ENT_QUOTES, 'UTF-8'); ?>
                                <?php else: ?>
                                    بدون سوال
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card exam-details-card border-0">
                    <div class="card-body">
                        <?php if ($currentTool): ?>
                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                                <div>
                                    <div class="text-secondary small mb-1">آزمون فعال</div>
                                    <h2 class="h5 mb-0">
                                        <?= htmlspecialchars($currentTool['name'] ?? 'آزمون جاری', ENT_QUOTES, 'UTF-8'); ?>
                                    </h2>
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php if ($examDurationSeconds > 0 && !$currentToolIsCompleted): ?>
                                        <span id="examTimerBadge" class="badge rounded-pill align-self-center px-3 py-2 exam-timer-badge">
                                            زمان باقی‌مانده: <span id="examTimer"><?= htmlspecialchars($initialTimerText, ENT_QUOTES, 'UTF-8'); ?></span>
                                        </span>
                                    <?php endif; ?>
                                    <button type="button"
                                            id="openInstructionsButton"
                                            class="btn btn-outline-secondary rounded-pill"
                                            data-bs-toggle="modal"
                                            data-bs-target="#examInstructionsModal">
                                        مشاهده راهنما
                                    </button>
                                    <?php if ($currentToolIsCompleted): ?>
                                        <span class="badge bg-success-subtle text-success rounded-pill align-self-center px-3 py-2">
                                            این آزمون تکمیل شده است
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if ($currentToolIsDisc): ?>
                                <div class="alert alert-secondary rounded-20 mb-4" role="alert">
                                    در هر سوال، یکی از گزینه‌ها را به عنوان «بهترین توصیف» و گزینه‌ای دیگر را به عنوان «ضعیف‌ترین توصیف» انتخاب کنید. هر گزینه فقط می‌تواند در یکی از این دو نقش قرار بگیرد.
                                </div>
                            <?php endif; ?>

                            <?php if ($currentToolIsOptional && !$currentToolIsCompleted): ?>
                                <div class="alert alert-info rounded-20 mb-4" role="alert">
                                    پاسخ‌دهی به سوالات این آزمون اختیاری است؛ در صورت نیاز می‌توانید بدون انتخاب گزینه، به سوال بعدی بروید.
                                </div>
                            <?php endif; ?>

                            <div id="examLockedNotice" class="exam-locked-note <?= $questionsLocked ? '' : 'd-none'; ?>">
                                <div class="alert alert-warning rounded-20 mb-0" role="alert">
                                    برای شروع آزمون، ابتدا راهنمای آزمون را مطالعه کرده و روی دکمه «شروع آزمون» بزنید.
                                </div>
                            </div>

                            <?php
                                $formAction = UtilityHelper::baseUrl('tests/exams?evaluation_id=' . urlencode((string) $evaluationId) . ($currentToolId > 0 ? '&tool_id=' . urlencode((string) $currentToolId) : ''));
                                $questionsWrapperClass = 'exam-questions-wrapper';
                                if ($questionsLocked) {
                                    $questionsWrapperClass .= ' d-none';
                                }
                                $answersDisabled = $currentToolIsCompleted;
                            ?>
                            <form id="examQuestionsForm"
                                  class="<?= htmlspecialchars($questionsWrapperClass, ENT_QUOTES, 'UTF-8'); ?>"
                                  method="post"
                                  action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8'); ?>"
                                  data-current-index="<?= htmlspecialchars((string) $currentQuestionIndex, ENT_QUOTES, 'UTF-8'); ?>"
                                  data-total-questions="<?= htmlspecialchars((string) $totalQuestions, ENT_QUOTES, 'UTF-8'); ?>"
                                  data-allow-finish="<?= $allowFinish ? '1' : '0'; ?>"
                                  data-completed="<?= $currentToolIsCompleted ? '1' : '0'; ?>"
                                  data-ajax-url="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8'); ?>"
                                  data-questions-locked="<?= $questionsLocked ? '1' : '0'; ?>"
                                  data-optional="<?= $currentToolIsOptional ? '1' : '0'; ?>"
                                  data-required-count="<?= htmlspecialchars((string) $requiredQuestionsCount, ENT_QUOTES, 'UTF-8'); ?>"
                                                                    data-tracked-count="<?= htmlspecialchars((string) $trackedQuestionsCount, ENT_QUOTES, 'UTF-8'); ?>"
                                                                    data-duration-seconds="<?= htmlspecialchars((string) $examDurationSeconds, ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="tool_id" value="<?= htmlspecialchars((string) $currentToolId, ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="action" id="examActionInput" value="navigate_question">
                                <input type="hidden" name="direction" id="examDirectionInput" value="">
                                <input type="hidden" name="question_id" id="examQuestionIdInput" value="">
                                <input type="hidden" name="target_index" id="examTargetIndexInput" value="">
                                                                <input type="hidden" name="expired" id="examExpiredInput" value="0">

                                <?php if ($currentToolIsCompleted): ?>
                                    <div class="alert alert-success rounded-20 mb-4" role="alert">
                                        این آزمون قبلاً تکمیل شده است و امکان ویرایش پاسخ‌ها وجود ندارد.
                                    </div>
                                <?php endif; ?>

                                <div id="examFeedback" class="alert d-none mb-4" role="alert"></div>

                                <?php if (!empty($questions)): ?>
                                    <?php foreach ($questions as $index => $question): ?>
                                        <?php
                                            $questionId = (int) ($question['id'] ?? 0);
                                            if ($questionId <= 0) {
                                                continue;
                                            }
                                            $isActive = ($index === $currentQuestionIndex);
                                            $questionIndexLabel = UtilityHelper::englishToPersian((string) ($question['display_index'] ?? $index + 1));
                                            $questionTitle = trim((string) ($question['title'] ?? ''));
                                            $questionText = trim((string) ($question['text'] ?? ''));
                                            $questionDescription = trim((string) ($question['description'] ?? ''));
                                            $questionImage = $question['image_path'] ?? null;
                                            $isDescriptionOnly = !empty($question['is_description_only']);
                                            $answers = is_array($question['answers'] ?? null) ? $question['answers'] : [];
                                            $questionRequiresAnswer = !$currentToolIsOptional && !$isDescriptionOnly && !empty($answers);
                                            $selectedAnswerId = 0;
                                            if (!$currentToolIsDisc) {
                                                $selectedAnswerId = (int) ($selectedAnswers[$questionId] ?? 0);
                                            }

                                            if ($questionTitle !== '' && $questionText !== '') {
                                                $normalizedTitle = preg_replace('/\s+/u', ' ', $questionTitle);
                                                $normalizedText = preg_replace('/\s+/u', ' ', $questionText);
                                                if ($normalizedTitle === $normalizedText) {
                                                    $questionText = '';
                                                }
                                            }
                                        ?>
                                        <div class="question-card<?= $isActive ? '' : ' d-none'; ?>"
                                             data-question-index="<?= htmlspecialchars((string) $index, ENT_QUOTES, 'UTF-8'); ?>"
                                             data-question-id="<?= htmlspecialchars((string) $questionId, ENT_QUOTES, 'UTF-8'); ?>"
                                             data-question-disc="<?= $currentToolIsDisc ? '1' : '0'; ?>"
                                             data-required="<?= $questionRequiresAnswer ? '1' : '0'; ?>">
                                            <div class="question-index">
                                                سوال <?= htmlspecialchars($questionIndexLabel, ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                            <?php if ($questionTitle !== ''): ?>
                                                <div class="question-title">
                                                    <?= htmlspecialchars($questionTitle, ENT_QUOTES, 'UTF-8'); ?>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($questionText !== ''): ?>
                                                <div class="question-text">
                                                    <?= nl2br(htmlspecialchars($questionText, ENT_QUOTES, 'UTF-8')); ?>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($questionDescription !== ''): ?>
                                                <div class="question-description">
                                                    <?= nl2br(htmlspecialchars($questionDescription, ENT_QUOTES, 'UTF-8')); ?>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (!empty($questionImage)): ?>
                                                <?php
                                                    $rawImagePath = trim((string) $questionImage);
                                                    $isAbsoluteUrl = preg_match('#^(https?:)?//#i', $rawImagePath) === 1;
                                                    if ($isAbsoluteUrl) {
                                                        $imageUrl = $rawImagePath;
                                                    } else {
                                                        $normalizedPath = ltrim($rawImagePath, '/');
                                                        if (strpos($normalizedPath, 'public/uploads/') !== 0) {
                                                            if (strpos($normalizedPath, 'uploads/') === 0) {
                                                                $normalizedPath = 'public/' . $normalizedPath;
                                                            } elseif (strpos($normalizedPath, 'public/') !== 0) {
                                                                $normalizedPath = 'public/' . $normalizedPath;
                                                            }
                                                        }
                                                        $imageUrl = UtilityHelper::baseUrl($normalizedPath);
                                                    }
                                                ?>
                                                <div class="question-image">
                                                    <img src="<?= htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8'); ?>" class="img-fluid rounded-20" alt="تصویر سوال">
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($isDescriptionOnly): ?>
                                                <div class="text-secondary small">این بخش توضیحی است و سوالی ندارد.</div>
                                            <?php elseif (!empty($answers)): ?>
                                                <?php if ($currentToolIsDisc): ?>
                                                    <?php
                                                        $selectedBestId = (int) ($selectedAnswers[$questionId]['best'] ?? 0);
                                                        $selectedLeastId = (int) ($selectedAnswers[$questionId]['least'] ?? 0);
                                                    ?>
                                                    <ul class="answers-list disc-answers-list">
                                                        <?php foreach ($answers as $answer): ?>
                                                            <?php
                                                                $answerId = (int) ($answer['id'] ?? 0);
                                                                if ($answerId <= 0) {
                                                                    continue;
                                                                }
                                                                $answerText = trim((string) ($answer['text'] ?? ''));

                                                                $bestAttributes = [];
                                                                $leastAttributes = [];

                                                                if ($answersDisabled) {
                                                                    $bestAttributes[] = 'disabled';
                                                                    $leastAttributes[] = 'disabled';
                                                                }

                                                                if ($selectedBestId === $answerId) {
                                                                    $bestAttributes[] = 'checked';
                                                                }

                                                                if ($selectedLeastId === $answerId) {
                                                                    $leastAttributes[] = 'checked';
                                                                }

                                                                $bestAttrString = empty($bestAttributes) ? '' : ' ' . implode(' ', $bestAttributes);
                                                                $leastAttrString = empty($leastAttributes) ? '' : ' ' . implode(' ', $leastAttributes);

                                                                $bestInputId = 'disc-best-' . $questionId . '-' . $answerId;
                                                                $leastInputId = 'disc-least-' . $questionId . '-' . $answerId;

                                                                $bestLabelClasses = 'btn btn-sm btn-outline-success rounded-pill disc-choice-btn';
                                                                $leastLabelClasses = 'btn btn-sm btn-outline-danger rounded-pill disc-choice-btn';

                                                                if ($answersDisabled) {
                                                                    $bestLabelClasses .= ' disabled';
                                                                    $leastLabelClasses .= ' disabled';
                                                                }
                                                            ?>
                                                            <li class="disc-answer-row">
                                                                <div class="disc-answer-text">
                                                                    <?= htmlspecialchars($answerText !== '' ? $answerText : 'گزینه بدون متن', ENT_QUOTES, 'UTF-8'); ?>
                                                                </div>
                                                                <div class="disc-answer-actions">
                                                                    <input type="radio"
                                                                           class="btn-check disc-choice-input-best"
                                                                           name="answers[<?= htmlspecialchars((string) $questionId, ENT_QUOTES, 'UTF-8'); ?>][best]"
                                                                           id="<?= htmlspecialchars($bestInputId, ENT_QUOTES, 'UTF-8'); ?>"
                                                                           value="<?= htmlspecialchars((string) $answerId, ENT_QUOTES, 'UTF-8'); ?>"<?= $bestAttrString; ?>>
                                                                    <label class="<?= htmlspecialchars($bestLabelClasses, ENT_QUOTES, 'UTF-8'); ?>"
                                                                           for="<?= htmlspecialchars($bestInputId, ENT_QUOTES, 'UTF-8'); ?>"
                                                                           <?= $answersDisabled ? 'aria-disabled="true" tabindex="-1"' : ''; ?>>
                                                                        بهترین توصیف
                                                                    </label>

                                                                    <input type="radio"
                                                                           class="btn-check disc-choice-input-least"
                                                                           name="answers[<?= htmlspecialchars((string) $questionId, ENT_QUOTES, 'UTF-8'); ?>][least]"
                                                                           id="<?= htmlspecialchars($leastInputId, ENT_QUOTES, 'UTF-8'); ?>"
                                                                           value="<?= htmlspecialchars((string) $answerId, ENT_QUOTES, 'UTF-8'); ?>"<?= $leastAttrString; ?>>
                                                                    <label class="<?= htmlspecialchars($leastLabelClasses, ENT_QUOTES, 'UTF-8'); ?>"
                                                                           for="<?= htmlspecialchars($leastInputId, ENT_QUOTES, 'UTF-8'); ?>"
                                                                           <?= $answersDisabled ? 'aria-disabled="true" tabindex="-1"' : ''; ?>>
                                                                        ضعیف‌ترین توصیف
                                                                    </label>
                                                                </div>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php else: ?>
                                                    <ul class="answers-list">
                                                        <?php foreach ($answers as $answer): ?>
                                                            <?php
                                                                $answerId = (int) ($answer['id'] ?? 0);
                                                                if ($answerId <= 0) {
                                                                    continue;
                                                                }
                                                                $answerText = trim((string) ($answer['text'] ?? ''));
                                                                $isChecked = $selectedAnswerId > 0 && $selectedAnswerId === $answerId;
                                                                $radioAttributes = [];
                                                                if ($answersDisabled) {
                                                                    $radioAttributes[] = 'disabled';
                                                                }
                                                                if ($isChecked) {
                                                                    $radioAttributes[] = 'checked';
                                                                }
                                                                $radioAttrString = empty($radioAttributes) ? '' : ' ' . implode(' ', $radioAttributes);
                                                                $optionClasses = 'answer-option';
                                                                if ($answersDisabled) {
                                                                    $optionClasses .= ' disabled';
                                                                }
                                                            ?>
                                                            <li>
                                                                <label class="<?= htmlspecialchars($optionClasses, ENT_QUOTES, 'UTF-8'); ?>">
                                                                    <input type="radio"
                                                                           class="single-choice-input"
                                                                           name="answers[<?= htmlspecialchars((string) $questionId, ENT_QUOTES, 'UTF-8'); ?>]"
                                                                           value="<?= htmlspecialchars((string) $answerId, ENT_QUOTES, 'UTF-8'); ?>"<?= $radioAttrString; ?>>
                                                                    <span><?= htmlspecialchars($answerText !== '' ? $answerText : 'گزینه بدون متن', ENT_QUOTES, 'UTF-8'); ?></span>
                                                                </label>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <div class="text-secondary small">گزینه‌ای برای این سوال ثبت نشده است.</div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="exam-empty-state">
                                        هنوز سوالی برای این آزمون ثبت نشده است.
                                    </div>
                                <?php endif; ?>

                                <?php if ($totalQuestions > 0): ?>
                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mt-4">
                                        <div class="question-nav-buttons">
                                            <button type="button"
                                                    id="questionPrevButton"
                                                    class="btn btn-outline-primary rounded-pill px-4<?= $currentQuestionIndex > 0 ? '' : ' d-none'; ?>"
                                                    <?= $answersDisabled ? 'disabled' : ''; ?>>
                                                سوال قبلی
                                            </button>
                                            <button type="button"
                                                    id="questionNextButton"
                                                    class="btn btn-primary rounded-pill px-4<?= ($currentQuestionIndex + 1 === $totalQuestions) ? ' d-none' : ''; ?>"
                                                    <?= $answersDisabled ? 'disabled' : ''; ?>>
                                                سوال بعدی
                                            </button>
                                            <?php if ($allowFinish && !$currentToolIsCompleted): ?>
                                                <button type="submit"
                                                        id="examFinishButton"
                                                        class="btn btn-success rounded-pill px-4<?= ($currentQuestionIndex + 1 === $totalQuestions) ? '' : ' d-none'; ?>">
                                                    پایان آزمون
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </form>
                        <?php else: ?>
                            <div class="exam-empty-state">
                                <?= $allExamsCompleted ? 'تمام آزمون‌های این ارزیابی تکمیل شده‌اند.' : 'آزمون فعالی برای نمایش در دسترس نیست.'; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-12 text-end">
                <a href="<?= htmlspecialchars($calendarUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-secondary rounded-pill">
                    بازگشت به تقویم آموزشی
                </a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="examInstructionsModal" tabindex="-1" aria-labelledby="examInstructionsModalLabel" aria-hidden="true" data-autoshow="<?= $shouldAutoShowIntro ? '1' : '0'; ?>">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-24">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="examInstructionsModalLabel">راهنمای شروع آزمون</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">
                    پیش از شروع آزمون، لطفاً راهنمای زیر را با دقت مطالعه کنید. پس از مطالعه، با زدن دکمه «شروع آزمون» سوالات نمایش داده می‌شوند.
                </p>
                <?php if ($examIntroText !== ''): ?>
                    <div class="mb-3">
                        <?= nl2br(htmlspecialchars($examIntroText, ENT_QUOTES, 'UTF-8')); ?>
                    </div>
                <?php endif; ?>
                <ul class="mb-0 ps-4">
                    <li>هر سوال فقط یک پاسخ صحیح دارد. پاسخ مناسب را انتخاب کنید.</li>
                    <li>در صورت نیاز می‌توانید قبل از پایان آزمون پاسخ‌ها را تغییر دهید.</li>
                    <li>پس از فشردن دکمه «پایان آزمون» امکان تغییر پاسخ‌ها وجود نخواهد داشت.</li>
                </ul>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">بستن</button>
                <button type="button" id="startExamButton" class="btn btn-primary rounded-pill">شروع آزمون</button>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/home-footer.php'; ?>
