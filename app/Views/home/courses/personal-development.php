<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'دوره‌های توسعه فردی';
$additional_css = $additional_css ?? [];
$additional_js = $additional_js ?? [];
$inline_styles = $inline_styles ?? '';
$inline_scripts = $inline_scripts ?? '';

$courses = isset($courses) && is_array($courses) ? $courses : [];
$csrfToken = AuthHelper::generateCsrfToken();

$inline_styles .= <<<'CSS'
.course-hero-card {
    background: linear-gradient(135deg, #4c6ef5 0%, #7950f2 100%);
    border-radius: 24px;
    padding: 32px;
    color: #fff;
    position: relative;
    overflow: hidden;
    border: none;
    box-shadow: 0 16px 40px rgba(76, 110, 245, 0.25);
}
.course-hero-card::after {
    content: '';
    position: absolute;
    inset: auto -120px -120px auto;
    width: 280px;
    height: 280px;
    background: rgba(255, 255, 255, 0.12);
    border-radius: 50%;
}
.course-card {
    background: #fff;
    border-radius: 20px;
    overflow: hidden;
    border: 1px solid #e2e8f0;
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    display: flex;
    flex-direction: column;
    height: 100%;
}
.course-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 18px 40px rgba(99, 102, 241, 0.18);
    border-color: rgba(99, 102, 241, 0.4);
}
.course-cover {
    position: relative;
    height: 220px;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
}
.course-cover img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.course-body {
    padding: 24px;
    display: flex;
    flex-direction: column;
    flex: 1;
}
.course-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    color: #64748b;
    font-size: 0.86rem;
}
.course-meta span {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #f1f5f9;
    padding: 6px 12px;
    border-radius: 999px;
}
.course-progress {
    background: #f1f5f9;
    border-radius: 18px;
    padding: 16px;
    margin: 20px 0;
}
.course-progress .progress {
    height: 12px;
    border-radius: 999px;
    overflow: hidden;
}
.lesson-list {
    display: flex;
    flex-direction: column;
    gap: 14px;
}
.lesson-item {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    padding: 18px;
    display: flex;
    gap: 16px;
    align-items: flex-start;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}
.lesson-item:hover {
    border-color: rgba(99, 102, 241, 0.35);
    box-shadow: 0 8px 20px rgba(99, 102, 241, 0.18);
}
.lesson-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.15) 0%, rgba(129, 140, 248, 0.25) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #4f46e5;
    font-size: 24px;
    flex-shrink: 0;
}
.lesson-content {
    flex: 1;
    min-width: 0;
}
.lesson-actions {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 10px;
}
.lesson-status.badge {
    border-radius: 999px;
    font-size: 0.75rem;
    padding: 0.35rem 0.75rem;
}
.lesson-viewer-iframe {
    width: 100%;
    height: 70vh;
    border-radius: 16px;
    border: 1px solid #dee2e6;
    background: #fff;
}
.lesson-text-content {
    line-height: 1.8;
    color: #1e293b;
}
.lesson-pdf-controls {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 12px;
    margin-bottom: 1rem;
}
.lesson-pdf-controls .lesson-pdf-page-input {
    width: 80px;
    text-align: center;
}
.lesson-pdf-controls .lesson-pdf-page-label {
    font-weight: 600;
}
#lessonViewerModal.lesson-guard-active .modal-content,
#lessonViewerModal.lesson-guard-active .modal-body {
    user-select: none;
}
#lessonViewerModal.lesson-guard-active .modal-content ::selection,
#lessonViewerModal.lesson-guard-active .modal-body ::selection {
    background: transparent;
}
.lesson-actions .form-check {
    margin: 0;
}
.lesson-actions .form-check-label {
    font-size: 0.75rem;
}
.lesson-actions .lesson-complete-switch {
    cursor: pointer;
}
.course-exam-block {
    background: #f5f7ff;
    border: 1px dashed rgba(99, 102, 241, 0.4);
    border-radius: 16px;
    padding: 16px;
    margin-top: 16px;
}
.course-exam-block .exam-meta {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.course-exam-block .exam-meta span {
    font-size: 0.85rem;
    color: #475569;
}
CSS;

$inline_scripts .= <<<'JS'
const LESSON_STATUS_STYLES = {
    completed: { label: 'تکمیل شده', className: 'bg-success-subtle text-success' },
    in_progress: { label: 'در حال پیشرفت', className: 'bg-info-subtle text-info' },
    scheduled: { label: 'به زودی', className: 'bg-warning-subtle text-warning' },
    pending: { label: 'آماده شروع', className: 'bg-secondary-subtle text-secondary' }
};

const toPersianDigits = function (value) {
    const digits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    return String(value ?? '').replace(/[0-9]/g, function (digit) {
        return digits[parseInt(digit, 10)] ?? digit;
    });
};

document.addEventListener('DOMContentLoaded', function () {
    const pageWrapper = document.querySelector('.page-content-wrapper');
    const courseProgressEndpoint = pageWrapper ? pageWrapper.dataset.courseProgressEndpoint || '' : '';
    const courseProgressToken = pageWrapper ? pageWrapper.dataset.courseProgressToken || '' : '';

    const sendLessonProgressRequest = function (payload) {
        if (!courseProgressEndpoint || !courseProgressToken) {
            return Promise.resolve(null);
        }

        const params = new URLSearchParams();
        params.append('_token', courseProgressToken);
        params.append('enrollment_id', payload.enrollmentId);
        params.append('lesson_id', payload.lessonId);
        params.append('event', payload.event);
        if (payload.watchSeconds) {
            params.append('watch_seconds', String(payload.watchSeconds));
        }

        return fetch(courseProgressEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: params.toString()
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('network');
                }
                return response.json();
            })
            .then(function (json) {
                if (json && json.success && json.data) {
                    applyLessonProgressUpdate(json.data);
                    return json.data;
                }
                throw new Error(json && json.message ? json.message : 'failed');
            })
            .catch(function (error) {
                console.warn('Lesson progress update failed:', error);
                return null;
            });
    };

    const updateCourseProgressSummary = function (courseCard, progress) {
        if (!courseCard || !progress) {
            return;
        }

        const percentageValue = typeof progress.percentage === 'number' ? progress.percentage : parseInt(progress.percentage || 0, 10) || 0;
        const percentageElement = courseCard.querySelector('[data-progress-percentage-value]');
        if (percentageElement) {
            percentageElement.textContent = toPersianDigits(percentageValue);
        }

        const progressBar = courseCard.querySelector('[data-progress-bar]');
        if (progressBar) {
            progressBar.style.width = percentageValue + '%';
            progressBar.setAttribute('aria-valuenow', String(percentageValue));
        }

        const completedElement = courseCard.querySelector('[data-progress-completed-value]');
        if (completedElement) {
            completedElement.textContent = toPersianDigits(progress.completed_lessons ?? 0);
        }

        const inProgressElement = courseCard.querySelector('[data-progress-in-progress-value]');
        if (inProgressElement) {
            inProgressElement.textContent = toPersianDigits(progress.in_progress_lessons ?? 0);
        }

        const totalElement = courseCard.querySelector('[data-progress-total-value]');
        if (totalElement) {
            totalElement.textContent = toPersianDigits(progress.total_lessons ?? 0);
        }
    };

    const updateCourseExamBlock = function (courseCard, examMeta) {
        const examBlock = courseCard ? courseCard.querySelector('[data-course-exam]') : null;
        if (!examBlock) {
            return;
        }

        const messageElement = examBlock.querySelector('[data-exam-status]');
        const titleElement = examBlock.querySelector('[data-exam-title]');
        const detailsElement = examBlock.querySelector('[data-exam-meta-details]');
        const detailElement = examBlock.querySelector('[data-exam-status-detail]');
        const actionButton = examBlock.querySelector('[data-exam-button]');

        if (!examMeta) {
            if (messageElement) {
                messageElement.textContent = 'برای این دوره آزمونی تعریف نشده است.';
            }
            if (titleElement) {
                titleElement.textContent = '';
                titleElement.classList.add('d-none');
            }
            if (detailsElement) {
                detailsElement.textContent = '';
                detailsElement.classList.add('d-none');
            }
            if (detailElement) {
                detailElement.textContent = '';
                detailElement.classList.add('d-none');
            }
            if (actionButton) {
                actionButton.classList.add('disabled', 'btn-outline-secondary');
                actionButton.classList.remove('btn-success');
                actionButton.setAttribute('aria-disabled', 'true');
                actionButton.href = '#';
            }
            return;
        }

        if (messageElement) {
            messageElement.textContent = examMeta.status_message || 'برای این دوره آزمونی تعریف نشده است.';
        }

        if (titleElement) {
            const titleParts = [];
            if (examMeta.tool_name) {
                titleParts.push(examMeta.tool_name);
            }
            if (examMeta.evaluation_title) {
                titleParts.push(examMeta.evaluation_title);
            }
            if (titleParts.length > 0) {
                titleElement.textContent = titleParts.join(' • ');
                titleElement.classList.remove('d-none');
            } else {
                titleElement.textContent = '';
                titleElement.classList.add('d-none');
            }
        }

        if (detailsElement) {
            const details = [];
            if (examMeta.duration_minutes) {
                details.push('زمان آزمون: ' + toPersianDigits(examMeta.duration_minutes) + ' دقیقه');
            }
            if (examMeta.evaluation_date_display) {
                details.push('تاریخ برگزاری: ' + examMeta.evaluation_date_display);
            }
            detailsElement.textContent = details.join(' | ');
            detailsElement.classList.toggle('d-none', details.length === 0);
        }

        if (detailElement) {
            const detailText = examMeta.status_detail ? String(examMeta.status_detail) : '';
            detailElement.textContent = detailText;
            detailElement.classList.toggle('d-none', detailText === '');
        }

        if (actionButton) {
            if (examMeta.is_unlocked && examMeta.start_url) {
                actionButton.classList.remove('disabled', 'btn-outline-secondary');
                actionButton.classList.add('btn-success');
                actionButton.setAttribute('aria-disabled', 'false');
                actionButton.href = examMeta.start_url;
            } else {
                actionButton.classList.add('disabled', 'btn-outline-secondary');
                actionButton.classList.remove('btn-success');
                actionButton.setAttribute('aria-disabled', 'true');
                actionButton.href = '#';
            }
        }
    };

    const updateLessonRow = function (courseCard, lessonPayload) {
        if (!courseCard || !lessonPayload) {
            return;
        }

        const lessonItem = courseCard.querySelector('.lesson-item[data-lesson-id="' + lessonPayload.lesson_id + '"]');
        if (!lessonItem) {
            return;
        }

        const statusSpan = lessonItem.querySelector('[data-lesson-status]');
        if (statusSpan) {
            const status = LESSON_STATUS_STYLES[lessonPayload.progress_state] || LESSON_STATUS_STYLES.pending;
            statusSpan.textContent = status.label;
            statusSpan.className = 'badge lesson-status ' + status.className;
        }

        const watchSpan = lessonItem.querySelector('[data-lesson-watch]');
        if (watchSpan) {
            const watchTextElement = watchSpan.querySelector('[data-lesson-watch-text]');
            if (lessonPayload.watch_duration_display) {
                watchSpan.classList.remove('d-none');
                if (watchTextElement) {
                    watchTextElement.textContent = 'در حال تماشا: ' + lessonPayload.watch_duration_display;
                }
            } else {
                watchSpan.classList.add('d-none');
                if (watchTextElement) {
                    watchTextElement.textContent = '';
                }
            }
        }

        const lastSpan = lessonItem.querySelector('[data-lesson-last]');
        if (lastSpan) {
            const lastTextElement = lastSpan.querySelector('[data-lesson-last-text]');
            if (lessonPayload.last_watched_display) {
                lastSpan.classList.remove('d-none');
                if (lastTextElement) {
                    lastTextElement.textContent = 'آخرین مشاهده: ' + lessonPayload.last_watched_display;
                }
            } else {
                lastSpan.classList.add('d-none');
                if (lastTextElement) {
                    lastTextElement.textContent = '';
                }
            }
        }

        const completionSwitch = lessonItem.querySelector('[data-lesson-complete]');
        if (completionSwitch) {
            completionSwitch.checked = !!lessonPayload.is_completed;
        }
    };

    const applyLessonProgressUpdate = function (payload) {
        if (!payload) {
            return;
        }

        const courseCard = document.querySelector('.course-card[data-course-id="' + payload.course_id + '"]');
        if (courseCard && payload.progress) {
            updateCourseProgressSummary(courseCard, payload.progress);
        }

        if (courseCard) {
            updateCourseExamBlock(courseCard, payload.exam || null);
        }

        if (courseCard && payload.lesson) {
            updateLessonRow(courseCard, payload.lesson);
        }
    };

    const maybeMarkLessonViewed = function (button) {
        const courseId = button.getAttribute('data-course-id');
        const enrollmentId = button.getAttribute('data-enrollment-id');
        const lessonId = button.getAttribute('data-lesson-id');
        if (!courseId || !enrollmentId || !lessonId) {
            return;
        }

        sendLessonProgressRequest({
            courseId: courseId,
            enrollmentId: enrollmentId,
            lessonId: lessonId,
            event: 'viewed'
        });
    };

    const modalElement = document.getElementById('lessonViewerModal');
    if (!modalElement) {
        return;
    }

    const modal = new bootstrap.Modal(modalElement);
    const modalTitle = modalElement.querySelector('.lesson-viewer-title');
    const modalBody = modalElement.querySelector('#lessonViewerContainer');
    const downloadButton = modalElement.querySelector('#lessonViewerDownload');

    let guardActive = false;
    const blockedKeyCodes = ['KeyS', 'KeyP', 'KeyO', 'KeyU', 'KeyC', 'KeyA', 'KeyX'];
    const blockedKeyNames = ['s', 'p', 'o', 'u', 'c', 'a', 'x'];
    const blockedDevToolsCodes = ['KeyI', 'KeyJ', 'KeyC', 'KeyK'];
    const blockedDevToolsNames = ['i', 'j', 'c', 'k'];
    const macScreenshotCodes = ['Digit3', 'Digit4', 'Digit5'];
    const macScreenshotNames = ['3', '4', '5'];

    let currentPdfContext = null;

    const clearPdfContext = function () {
        currentPdfContext = null;
    };

    const refreshPdfControls = function () {
        if (!currentPdfContext) {
            return;
        }

        if (currentPdfContext.prevButton) {
            currentPdfContext.prevButton.disabled = currentPdfContext.currentPage <= 1;
        }

        if (currentPdfContext.nextButton) {
            if (currentPdfContext.totalPages) {
                currentPdfContext.nextButton.disabled = currentPdfContext.currentPage >= currentPdfContext.totalPages;
            } else {
                currentPdfContext.nextButton.disabled = currentPdfContext.currentPage <= 0;
            }
        }

        if (currentPdfContext.pageInput) {
            currentPdfContext.pageInput.value = String(currentPdfContext.currentPage);
        }

        if (currentPdfContext.pageLabel) {
            currentPdfContext.pageLabel.textContent = String(currentPdfContext.currentPage);
        }

        if (currentPdfContext.pageTotalLabel) {
            if (currentPdfContext.totalPages) {
                currentPdfContext.pageTotalLabel.textContent = 'از ' + String(currentPdfContext.totalPages);
            } else {
                currentPdfContext.pageTotalLabel.textContent = '';
            }
        }
    };

    const buildPdfViewerSrc = function (sourceUrl, page) {
        const parts = String(sourceUrl || '').split('#');
        const baseUrl = parts[0];
        const hash = parts.slice(1).join('#');
        const params = new URLSearchParams(hash);
        params.set('toolbar', '0');
        params.set('navpanes', '0');
        params.set('statusbar', '0');
        if (page && page > 0) {
            params.set('page', String(page));
        } else {
            params.delete('page');
        }
        const paramString = params.toString();
        return paramString !== '' ? baseUrl + '#' + paramString : baseUrl;
    };

    const navigateToPdfPage = function (page) {
        if (!currentPdfContext) {
            return;
        }

        let targetPage = parseInt(page, 10);
        if (Number.isNaN(targetPage) || targetPage < 1) {
            targetPage = 1;
        }

        if (currentPdfContext.totalPages && targetPage > currentPdfContext.totalPages) {
            targetPage = currentPdfContext.totalPages;
        }

        currentPdfContext.currentPage = targetPage;

        if (currentPdfContext.iframe) {
            currentPdfContext.iframe.src = buildPdfViewerSrc(currentPdfContext.source, currentPdfContext.currentPage);
        }

        refreshPdfControls();
    };

    const stepPdfPage = function (delta) {
        if (!currentPdfContext) {
            return;
        }

        navigateToPdfPage(currentPdfContext.currentPage + delta);
    };

    const blockContextMenu = function (event) {
        if (!guardActive) {
            return;
        }
        event.preventDefault();
    };

    const blockKeyboardShortcuts = function (event) {
        if (!guardActive) {
            return;
        }

        const keyCode = event.code || '';
        const keyName = (event.key || '').toLowerCase();

        if (keyCode === 'PrintScreen' || keyName === 'printscreen') {
            event.preventDefault();
            event.stopPropagation();
            return;
        }

        if ((event.ctrlKey || event.metaKey) && (blockedKeyCodes.includes(keyCode) || blockedKeyNames.includes(keyName))) {
            event.preventDefault();
            event.stopPropagation();
            return;
        }

        if ((event.ctrlKey || event.metaKey) && event.shiftKey && (blockedDevToolsCodes.includes(keyCode) || blockedDevToolsNames.includes(keyName))) {
            event.preventDefault();
            event.stopPropagation();
            return;
        }

        if ((event.ctrlKey || event.metaKey) && event.altKey && (blockedDevToolsCodes.includes(keyCode) || blockedDevToolsNames.includes(keyName))) {
            event.preventDefault();
            event.stopPropagation();
            return;
        }

        if (event.metaKey && event.shiftKey && (macScreenshotCodes.includes(keyCode) || macScreenshotNames.includes(keyName))) {
            event.preventDefault();
            event.stopPropagation();
            return;
        }

        if (keyName === 'f12') {
            event.preventDefault();
            event.stopPropagation();
        }
    };

    const blockPrintScreen = function (event) {
        if (!guardActive) {
            return;
        }

        const keyCode = event.code || '';
        const keyName = (event.key || '').toLowerCase();

        if (keyCode === 'PrintScreen' || keyName === 'printscreen') {
            event.preventDefault();
            event.stopPropagation();
            if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
                navigator.clipboard.writeText('').catch(function () {});
            }
        }
    };

    const enableGuards = function () {
        if (guardActive) {
            return;
        }
        guardActive = true;
        modalElement.classList.add('lesson-guard-active');
        document.addEventListener('contextmenu', blockContextMenu, true);
        document.addEventListener('keydown', blockKeyboardShortcuts, true);
        document.addEventListener('keyup', blockPrintScreen, true);
    };

    const disableGuards = function () {
        guardActive = false;
        modalElement.classList.remove('lesson-guard-active');
        document.removeEventListener('contextmenu', blockContextMenu, true);
        document.removeEventListener('keydown', blockKeyboardShortcuts, true);
        document.removeEventListener('keyup', blockPrintScreen, true);
    };

    const decodeBase64 = function (value) {
        if (!value) {
            return '';
        }
        try {
            const decoded = atob(value);
            const escaped = decoded.split('').map(function (char) {
                const hex = char.charCodeAt(0).toString(16).padStart(2, '0');
                return '%' + hex;
            }).join('');
            return decodeURIComponent(escaped);
        } catch (error) {
            return '';
        }
    };

    document.querySelectorAll('[data-lesson-viewer="1"]').forEach(function (button) {
        button.addEventListener('click', function () {
            if (button.disabled) {
                return;
            }

            const lessonTitle = button.getAttribute('data-lesson-title') || '';
            const lessonType = button.getAttribute('data-lesson-type') || '';
            const mediaUrl = button.getAttribute('data-media-url') || '';
            const viewerUrl = button.getAttribute('data-viewer-url') || '';
            const encodedText = button.getAttribute('data-text-content') || '';

            modalTitle.textContent = lessonTitle !== '' ? lessonTitle : 'نمایش محتوا';
            modalBody.innerHTML = '';
            downloadButton.classList.add('d-none');
            downloadButton.removeAttribute('href');
            clearPdfContext();
            disableGuards();

            if (lessonType === 'video' && viewerUrl) {
                const video = document.createElement('video');
                video.src = viewerUrl;
                video.controls = true;
                video.playsInline = true;
                video.className = 'w-100 rounded-4';
                modalBody.appendChild(video);

                if (mediaUrl) {
                    downloadButton.href = mediaUrl;
                    downloadButton.classList.remove('d-none');
                }
            } else if (lessonType === 'pdf') {
                const pdfSource = viewerUrl !== '' ? viewerUrl : mediaUrl;
                if (pdfSource !== '') {
                    const pdfWrapper = document.createElement('div');
                    pdfWrapper.className = 'lesson-pdf-wrapper';
                    const controls = document.createElement('div');
                    controls.className = 'lesson-pdf-controls';

                    const prevButton = document.createElement('button');
                    prevButton.type = 'button';
                    prevButton.className = 'btn btn-sm btn-outline-secondary';
                    prevButton.textContent = 'صفحه قبل';
                    prevButton.addEventListener('click', function () {
                        stepPdfPage(-1);
                    });

                    const pageInput = document.createElement('input');
                    pageInput.type = 'number';
                    pageInput.className = 'form-control form-control-sm lesson-pdf-page-input';
                    pageInput.value = '1';
                    pageInput.min = '1';
                    pageInput.setAttribute('inputmode', 'numeric');
                    pageInput.addEventListener('change', function () {
                        navigateToPdfPage(pageInput.value);
                    });
                    pageInput.addEventListener('keyup', function (event) {
                        if (event.key === 'Enter') {
                            navigateToPdfPage(pageInput.value);
                        }
                    });

                    const pageInfo = document.createElement('div');
                    pageInfo.className = 'd-flex align-items-center gap-2';
                    const pageLabelPrefix = document.createElement('span');
                    pageLabelPrefix.textContent = 'صفحه';
                    const pageLabel = document.createElement('span');
                    pageLabel.className = 'lesson-pdf-page-label';
                    pageLabel.textContent = '1';
                    const pageTotalLabel = document.createElement('span');
                    pageTotalLabel.className = 'text-secondary small';
                    pageInfo.appendChild(pageLabelPrefix);
                    pageInfo.appendChild(pageLabel);
                    pageInfo.appendChild(pageTotalLabel);

                    const nextButton = document.createElement('button');
                    nextButton.type = 'button';
                    nextButton.className = 'btn btn-sm btn-outline-secondary';
                    nextButton.textContent = 'صفحه بعد';
                    nextButton.addEventListener('click', function () {
                        stepPdfPage(1);
                    });

                    controls.appendChild(prevButton);
                    controls.appendChild(pageInput);
                    controls.appendChild(pageInfo);
                    controls.appendChild(nextButton);

                    const iframe = document.createElement('iframe');
                    iframe.className = 'lesson-viewer-iframe';
                    iframe.setAttribute('frameborder', '0');
                    iframe.setAttribute('allow', 'fullscreen');

                    pdfWrapper.appendChild(controls);
                    pdfWrapper.appendChild(iframe);
                    modalBody.appendChild(pdfWrapper);

                    currentPdfContext = {
                        source: pdfSource,
                        iframe: iframe,
                        currentPage: 1,
                        totalPages: null,
                        prevButton: prevButton,
                        nextButton: nextButton,
                        pageInput: pageInput,
                        pageLabel: pageLabel,
                        pageTotalLabel: pageTotalLabel
                    };

                    const pageCountAttr = button.getAttribute('data-page-count');
                    if (pageCountAttr) {
                        const parsedPageCount = parseInt(pageCountAttr, 10);
                        if (!Number.isNaN(parsedPageCount) && parsedPageCount > 0) {
                            currentPdfContext.totalPages = parsedPageCount;
                            pageInput.max = String(parsedPageCount);
                        }
                    }

                    navigateToPdfPage(1);
                    enableGuards();
                } else {
                    modalBody.innerHTML = '<div class="alert alert-warning mb-0">فایل این درس در دسترس نیست.</div>';
                }
            } else if (lessonType === 'ppt' && viewerUrl) {
                const iframe = document.createElement('iframe');
                iframe.src = viewerUrl;
                iframe.className = 'lesson-viewer-iframe';
                iframe.setAttribute('frameborder', '0');
                modalBody.appendChild(iframe);

                if (mediaUrl) {
                    downloadButton.href = mediaUrl;
                    downloadButton.classList.remove('d-none');
                }
            } else if (lessonType === 'text') {
                const textWrapper = document.createElement('div');
                textWrapper.className = 'lesson-text-content';
                const decoded = decodeBase64(encodedText);
                textWrapper.innerHTML = decoded !== '' ? decoded : '<p class="text-muted mb-0">محتوای متنی برای این درس ثبت نشده است.</p>';
                modalBody.appendChild(textWrapper);
            } else if (lessonType === 'link' && mediaUrl) {
                const linkWrapper = document.createElement('div');
                linkWrapper.innerHTML = '<p class="mb-3">برای مشاهده محتوای این درس روی لینک زیر کلیک کنید:</p>';
                const anchor = document.createElement('a');
                anchor.href = mediaUrl;
                anchor.target = '_blank';
                anchor.rel = 'noopener noreferrer';
                anchor.className = 'btn btn-primary';
                anchor.textContent = 'باز کردن لینک در صفحه جدید';
                linkWrapper.appendChild(anchor);
                modalBody.appendChild(linkWrapper);
            } else if (mediaUrl) {
                const iframe = document.createElement('iframe');
                iframe.src = viewerUrl || mediaUrl;
                iframe.className = 'lesson-viewer-iframe';
                iframe.setAttribute('frameborder', '0');
                modalBody.appendChild(iframe);
            } else {
                modalBody.innerHTML = '<div class="alert alert-warning mb-0">محتوای این درس در حال حاضر در دسترس نیست.</div>';
            }

            maybeMarkLessonViewed(button);
            modal.show();
        });
    });

    document.querySelectorAll('[data-lesson-complete]').forEach(function (input) {
        input.addEventListener('change', function () {
            if (input.disabled || input.dataset.pending === '1') {
                return;
            }

            const courseId = input.getAttribute('data-course-id');
            const enrollmentId = input.getAttribute('data-enrollment-id');
            const lessonId = input.getAttribute('data-lesson-id');

            if (!courseId || !enrollmentId || !lessonId) {
                return;
            }

            input.dataset.pending = '1';
            input.disabled = true;

            const eventType = input.checked ? 'complete' : 'incomplete';

            sendLessonProgressRequest({
                courseId: courseId,
                enrollmentId: enrollmentId,
                lessonId: lessonId,
                event: eventType
            }).then(function (data) {
                if (!data) {
                    input.checked = !input.checked;
                }
            }).finally(function () {
                input.disabled = false;
                input.dataset.pending = '0';
            });
        });
    });

    modalElement.addEventListener('hidden.bs.modal', function () {
        clearPdfContext();
        disableGuards();
        modalBody.innerHTML = '';
    });
});
JS;

AuthHelper::startSession();
$user = AuthHelper::getUser();
$navbarUser = $user;

include __DIR__ . '/../../layouts/home-header.php';
include __DIR__ . '/../../layouts/home-sidebar.php';
?>
<?php include __DIR__ . '/../../layouts/home-navbar.php'; ?>
<div class="page-content-wrapper"
    data-course-progress-endpoint="<?= htmlspecialchars(UtilityHelper::baseUrl('courses/lessons/progress'), ENT_QUOTES, 'UTF-8'); ?>"
    data-course-progress-token="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
    <div class="page-content">
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="course-hero-card">
                    <div class="position-relative" style="z-index: 2;">
                        <h1 class="h4 mb-2">دوره‌های توسعه فردی</h1>
                        <p class="mb-3 mb-md-4 opacity-85">در این بخش می‌توانید دوره‌هایی را که سازمان شما در اختیار شما قرار داده است مشاهده کنید، پیشرفت خود را دنبال نمایید و محتوای هر درس را به صورت آنلاین مشاهده کنید.</p>
                        <div class="d-flex flex-wrap gap-3 small">
                            <span class="d-inline-flex align-items-center gap-2 bg-white bg-opacity-10 rounded-pill px-3 py-2"><ion-icon name="calendar-outline"></ion-icon> <?= htmlspecialchars(UtilityHelper::getTodayDate(), ENT_QUOTES, 'UTF-8'); ?></span>
                            <span class="d-inline-flex align-items-center gap-2 bg-white bg-opacity-10 rounded-pill px-3 py-2"><ion-icon name="book-outline"></ion-icon> <?= htmlspecialchars(UtilityHelper::englishToPersian((string)count($courses)), ENT_QUOTES, 'UTF-8'); ?> دوره فعال</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <?php if (empty($courses)): ?>
                <div class="col-12">
                    <div class="card course-card text-center py-5">
                        <div class="card-body">
                            <div class="display-5 text-muted mb-3"><ion-icon name="school-outline"></ion-icon></div>
                            <h2 class="h5 mb-2">دوره‌ای برای شما ثبت نشده است</h2>
                            <p class="text-secondary mb-0">به محض اینکه سازمان شما دوره‌ای را برایتان فعال کند، از این بخش قابل مشاهده خواهد بود.</p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($courses as $index => $course):
                    $courseId = (int)($course['id'] ?? 0);
                    $courseTitle = trim((string)($course['title'] ?? 'بدون عنوان'));
                    $courseDescription = trim((string)($course['description'] ?? ''));
                    $instructor = trim((string)($course['instructor_name'] ?? ''));
                    $category = trim((string)($course['category'] ?? ''));
                    $durationHours = (int)($course['duration_hours'] ?? 0);
                    $enrollmentId = (int)($course['enrollment_id'] ?? 0);
                    $progress = isset($course['progress']) && is_array($course['progress']) ? $course['progress'] : ['percentage' => 0, 'total_lessons' => 0, 'completed_lessons' => 0, 'in_progress_lessons' => 0];
                    $lessons = isset($course['lessons']) && is_array($course['lessons']) ? $course['lessons'] : [];
                    $coverImageUrl = trim((string)($course['cover_image_url'] ?? ''));
                    $enrolledAt = trim((string)($course['enrolled_at_display'] ?? ''));
                    $completedAt = trim((string)($course['completed_at_display'] ?? ''));
                    $collapseId = 'courseLessons' . $courseId . '_' . $index;
                    $examMeta = isset($course['exam']) && is_array($course['exam']) ? $course['exam'] : null;
                    $examButtonHref = '#';
                    $examButtonClass = 'btn btn-outline-secondary rounded-pill disabled';
                    $examButtonAria = 'true';
                    $examStatusMessage = 'برای این دوره آزمونی تعریف نشده است.';
                    $examTitleText = '';
                    $examDetailsText = '';
                    $examStatusDetail = null;

                    if ($examMeta) {
                        if (!empty($examMeta['status_message'])) {
                            $examStatusMessage = $examMeta['status_message'];
                        }

                        $titleParts = [];
                        if (!empty($examMeta['tool_name'])) {
                            $titleParts[] = $examMeta['tool_name'];
                        }
                        if (!empty($examMeta['evaluation_title'])) {
                            $titleParts[] = $examMeta['evaluation_title'];
                        }
                        if (!empty($titleParts)) {
                            $examTitleText = implode(' • ', $titleParts);
                        }

                        if (!empty($examMeta['duration_minutes'])) {
                            $examDetailsText .= 'زمان آزمون: ' . UtilityHelper::englishToPersian((string)$examMeta['duration_minutes']) . ' دقیقه';
                        }
                        if (!empty($examMeta['evaluation_date_display'])) {
                            if ($examDetailsText !== '') {
                                $examDetailsText .= ' | ';
                            }
                            $examDetailsText .= 'تاریخ برگزاری: ' . $examMeta['evaluation_date_display'];
                        }

                        if (!empty($examMeta['is_unlocked']) && !empty($examMeta['start_url'])) {
                            $examButtonHref = $examMeta['start_url'];
                            $examButtonClass = 'btn btn-success rounded-pill';
                            $examButtonAria = 'false';
                        }
                        $examStatusDetail = !empty($examMeta['status_detail']) ? (string)$examMeta['status_detail'] : null;
                    }
                ?>
                <div class="col-12 col-xl-6">
                    <div class="course-card" data-course-id="<?= $courseId; ?>" data-enrollment-id="<?= $enrollmentId; ?>">
                        <div class="course-cover">
                            <?php if ($coverImageUrl !== ''): ?>
                                <img src="<?= htmlspecialchars($coverImageUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="کاور دوره">
                            <?php else: ?>
                                <ion-icon name="school-sharp"></ion-icon>
                            <?php endif; ?>
                        </div>
                        <div class="course-body">
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                <div>
                                    <h2 class="h5 mb-1 text-dark"><?= htmlspecialchars($courseTitle, ENT_QUOTES, 'UTF-8'); ?></h2>
                                    <?php if ($category !== ''): ?>
                                        <div class="small text-primary fw-semibold"><?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php endif; ?>
                                </div>
                                <span class="badge bg-primary-subtle text-primary"><?= htmlspecialchars(UtilityHelper::englishToPersian((string)($progress['total_lessons'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?> درس</span>
                            </div>
                            <?php if ($courseDescription !== ''): ?>
                                <p class="text-secondary small mb-3" style="line-height: 1.8;">
                                    <?= nl2br(htmlspecialchars($courseDescription, ENT_QUOTES, 'UTF-8')); ?>
                                </p>
                            <?php endif; ?>
                            <div class="course-meta mb-3">
                                <?php if ($instructor !== ''): ?>
                                    <span><ion-icon name="person-circle-outline"></ion-icon><?= htmlspecialchars($instructor, ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php endif; ?>
                                <?php if ($durationHours > 0): ?>
                                    <span><ion-icon name="time-outline"></ion-icon><?= htmlspecialchars(UtilityHelper::englishToPersian((string)$durationHours), ENT_QUOTES, 'UTF-8'); ?> ساعت محتوا</span>
                                <?php endif; ?>
                                <?php if ($enrolledAt !== ''): ?>
                                    <span><ion-icon name="calendar-number-outline"></ion-icon> ثبت نام: <?= htmlspecialchars($enrolledAt, ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php endif; ?>
                                <?php if ($completedAt !== ''): ?>
                                    <span><ion-icon name="checkbox-outline"></ion-icon> اتمام: <?= htmlspecialchars($completedAt, ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="course-progress">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-semibold text-dark">پیشرفت دوره</span>
                                    <span class="text-primary fw-semibold"><span data-progress-percentage-value><?= htmlspecialchars(UtilityHelper::englishToPersian((string)($progress['percentage'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?></span>٪</span>
                                </div>
                                <div class="progress bg-white">
                                    <div class="progress-bar bg-primary" data-progress-bar role="progressbar" style="width: <?= (int)($progress['percentage'] ?? 0); ?>%;" aria-valuenow="<?= (int)($progress['percentage'] ?? 0); ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="d-flex flex-wrap gap-3 small text-secondary mt-3">
                                    <span><ion-icon name="checkmark-circle-outline"></ion-icon> تکمیل شده: <span data-progress-completed-value><?= htmlspecialchars(UtilityHelper::englishToPersian((string)($progress['completed_lessons'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?></span></span>
                                    <span><ion-icon name="ellipse-outline"></ion-icon> در حال پیشرفت: <span data-progress-in-progress-value><?= htmlspecialchars(UtilityHelper::englishToPersian((string)($progress['in_progress_lessons'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?></span></span>
                                    <span><ion-icon name="list-outline"></ion-icon> کل دروس: <span data-progress-total-value><?= htmlspecialchars(UtilityHelper::englishToPersian((string)($progress['total_lessons'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?></span></span>
                                </div>
                            </div>
                            <div class="course-exam-block" data-course-exam="1">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                                    <div class="exam-meta">
                                        <span class="fw-semibold text-dark">آزمون دوره</span>
                                        <span class="small text-secondary <?= $examTitleText === '' ? 'd-none' : ''; ?>" data-exam-title><?= htmlspecialchars($examTitleText, ENT_QUOTES, 'UTF-8'); ?></span>
                                        <span class="small text-secondary <?= $examDetailsText === '' ? 'd-none' : ''; ?>" data-exam-meta-details><?= htmlspecialchars($examDetailsText, ENT_QUOTES, 'UTF-8'); ?></span>
                                        <span class="small text-secondary" data-exam-status><?= htmlspecialchars($examStatusMessage, ENT_QUOTES, 'UTF-8'); ?></span>
                                        <span class="small text-danger <?= empty($examStatusDetail) ? 'd-none' : ''; ?>" data-exam-status-detail><?= htmlspecialchars((string)($examStatusDetail ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                                    </div>
                                    <a href="<?= htmlspecialchars($examButtonHref, ENT_QUOTES, 'UTF-8'); ?>"
                                       class="<?= $examButtonClass; ?>"
                                       data-exam-button
                                       aria-disabled="<?= $examButtonAria; ?>">شروع آزمون</a>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="fw-semibold text-dark">لیست درس‌ها</span>
                                <button class="btn btn-sm btn-outline-primary rounded-pill" type="button" data-bs-toggle="collapse" data-bs-target="#<?= htmlspecialchars($collapseId, ENT_QUOTES, 'UTF-8'); ?>" aria-expanded="<?= $index === 0 ? 'true' : 'false'; ?>">
                                    مشاهده / پنهان کردن
                                </button>
                            </div>
                            <div class="collapse <?= $index === 0 ? 'show' : ''; ?>" id="<?= htmlspecialchars($collapseId, ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="lesson-list">
                                    <?php if (empty($lessons)): ?>
                                        <div class="text-center text-secondary small">درسی برای این دوره ثبت نشده است.</div>
                                    <?php else: ?>
                                        <?php foreach ($lessons as $lesson):
                                            $lessonId = (int)($lesson['id'] ?? 0);
                                            $lessonTitle = trim((string)($lesson['title'] ?? 'بدون عنوان'));
                                            $lessonState = (string)($lesson['progress_state'] ?? 'pending');
                                            $durationMinutes = (int)($lesson['duration_minutes'] ?? 0);
                                            $watchLabel = trim((string)($lesson['watch_duration_display'] ?? ''));
                                            $availableDisplay = trim((string)($lesson['available_at_display'] ?? ''));
                                            $lastDisplay = trim((string)($lesson['last_watched_display'] ?? ''));
                                            $switchId = 'lesson-complete-' . $courseId . '-' . $lessonId;
                                            $shortDescription = trim((string)($lesson['short_description'] ?? ''));
                                            $contentType = (string)($lesson['content_type'] ?? 'video');
                                            $isAvailable = (int)($lesson['is_available'] ?? 0) === 1;
                                            $isCompleted = (int)($lesson['is_completed'] ?? 0) === 1;
                                            $mediaUrl = trim((string)($lesson['media_url'] ?? ''));
                                            $viewerUrl = trim((string)($lesson['viewer_url'] ?? ''));
                                            $textContent = (string)($lesson['text_content'] ?? '');
                                            $base64Text = base64_encode($textContent);

                                            $statusMap = [
                                                'completed' => ['label' => 'تکمیل شده', 'class' => 'bg-success-subtle text-success'],
                                                'in_progress' => ['label' => 'در حال پیشرفت', 'class' => 'bg-info-subtle text-info'],
                                                'scheduled' => ['label' => 'به زودی', 'class' => 'bg-warning-subtle text-warning'],
                                                'pending' => ['label' => 'آماده شروع', 'class' => 'bg-secondary-subtle text-secondary'],
                                            ];

                                            $status = $statusMap[$lessonState] ?? $statusMap['pending'];

                                            $iconMap = [
                                                'video' => 'play-circle-outline',
                                                'pdf' => 'document-text-outline',
                                                'ppt' => 'easel-outline',
                                                'link' => 'link-outline',
                                                'text' => 'document-outline',
                                            ];
                                            $iconName = $iconMap[$contentType] ?? 'play-circle-outline';

                                            $buttonDisabled = !$isAvailable || ($contentType !== 'text' && $mediaUrl === '' && $viewerUrl === '');
                                        ?>
                                        <div class="lesson-item" data-lesson-item="1" data-course-id="<?= $courseId; ?>" data-enrollment-id="<?= $enrollmentId; ?>" data-lesson-id="<?= $lessonId; ?>">
                                            <div class="lesson-icon"><ion-icon name="<?= htmlspecialchars($iconName, ENT_QUOTES, 'UTF-8'); ?>"></ion-icon></div>
                                            <div class="lesson-content">
                                                <div class="d-flex flex-wrap justify-content-between gap-2 align-items-center mb-2">
                                                    <h3 class="h6 mb-0 text-dark"><?= htmlspecialchars($lessonTitle, ENT_QUOTES, 'UTF-8'); ?></h3>
                                                    <span class="badge lesson-status <?= htmlspecialchars($status['class'], ENT_QUOTES, 'UTF-8'); ?>" data-lesson-status><?= htmlspecialchars($status['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                </div>
                                                <?php if ($shortDescription !== ''): ?>
                                                    <p class="text-secondary small mb-2" style="line-height: 1.7;">
                                                        <?= nl2br(htmlspecialchars($shortDescription, ENT_QUOTES, 'UTF-8')); ?>
                                                    </p>
                                                <?php endif; ?>
                                                <div class="d-flex flex-wrap gap-3 small text-secondary">
                                                    <?php if ($durationMinutes > 0): ?>
                                                        <span><ion-icon name="time-outline"></ion-icon> مدت: <?= htmlspecialchars(UtilityHelper::englishToPersian((string)$durationMinutes), ENT_QUOTES, 'UTF-8'); ?> دقیقه</span>
                                                    <?php endif; ?>
                                                    <span class="lesson-meta-watch <?= $watchLabel === '' ? 'd-none' : ''; ?>" data-lesson-watch>
                                                        <ion-icon name="timer-outline"></ion-icon>
                                                        <span data-lesson-watch-text><?= $watchLabel !== '' ? 'در حال تماشا: ' . htmlspecialchars($watchLabel, ENT_QUOTES, 'UTF-8') : ''; ?></span>
                                                    </span>
                                                    <?php if (!$isAvailable && $availableDisplay !== ''): ?>
                                                        <span><ion-icon name="lock-closed-outline"></ion-icon> دسترسی از <?= htmlspecialchars($availableDisplay, ENT_QUOTES, 'UTF-8'); ?></span>
                                                    <?php endif; ?>
                                                    <span class="lesson-meta-last <?= $lastDisplay === '' ? 'd-none' : ''; ?>" data-lesson-last>
                                                        <ion-icon name="checkmark-done-outline"></ion-icon>
                                                        <span data-lesson-last-text><?= $lastDisplay !== '' ? 'آخرین مشاهده: ' . htmlspecialchars($lastDisplay, ENT_QUOTES, 'UTF-8') : ''; ?></span>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="lesson-actions">
                                                <button type="button" class="btn btn-sm <?= $buttonDisabled ? 'btn-outline-secondary' : 'btn-primary'; ?> rounded-pill"
                                                    data-lesson-viewer="1"
                                                    data-course-id="<?= $courseId; ?>"
                                                    data-enrollment-id="<?= $enrollmentId; ?>"
                                                    data-lesson-id="<?= $lessonId; ?>"
                                                    data-lesson-title="<?= htmlspecialchars($lessonTitle, ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-lesson-type="<?= htmlspecialchars($contentType, ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-media-url="<?= htmlspecialchars($mediaUrl, ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-viewer-url="<?= htmlspecialchars($viewerUrl, ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-text-content="<?= htmlspecialchars($base64Text, ENT_QUOTES, 'UTF-8'); ?>"
                                                    <?= $buttonDisabled ? 'disabled' : ''; ?>>
                                                    <?= $buttonDisabled ? 'در دسترس نیست' : 'مشاهده محتوا'; ?>
                                                </button>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input lesson-complete-switch" type="checkbox"
                                                        role="switch"
                                                        id="<?= htmlspecialchars($switchId, ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-lesson-complete="1"
                                                        data-course-id="<?= $courseId; ?>"
                                                        data-enrollment-id="<?= $enrollmentId; ?>"
                                                        data-lesson-id="<?= $lessonId; ?>"
                                                        <?= $isCompleted ? 'checked' : ''; ?>
                                                        <?= (!$isAvailable ? 'disabled' : ''); ?>>
                                                    <label class="form-check-label text-secondary" for="<?= htmlspecialchars($switchId, ENT_QUOTES, 'UTF-8'); ?>">تکمیل درس</label>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="lessonViewerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0">
                <h5 class="modal-title lesson-viewer-title fw-semibold"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
            </div>
            <div class="modal-body" id="lessonViewerContainer"></div>
            <div class="modal-footer border-0 d-flex justify-content-between">
                <a href="#" class="btn btn-outline-secondary d-none" id="lessonViewerDownload" download>دانلود فایل</a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">بستن</button>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/home-footer.php'; ?>
