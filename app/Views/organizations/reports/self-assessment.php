<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}
$title = $title ?? 'نتایج خود ارزیابی کاربران';
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
$additional_js[] = 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js';
$additional_js[] = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';

$selfAssessmentRows = isset($selfAssessmentRows) && is_array($selfAssessmentRows) ? $selfAssessmentRows : [];
$selfAssessmentSummary = isset($selfAssessmentSummary) && is_array($selfAssessmentSummary) ? $selfAssessmentSummary : [];
$evaluationOptions = isset($evaluationOptions) && is_array($evaluationOptions) ? $evaluationOptions : [];
$selectedEvaluationId = (int) ($selectedEvaluationId ?? 0);
$searchQuery = (string) ($searchQuery ?? '');
$successMessage = $successMessage ?? null;
$errorMessage = $errorMessage ?? null;
$warningMessage = $warningMessage ?? null;
$infoMessage = $infoMessage ?? null;

$tableOptions = [
    'paging' => true,
    'pageLength' => 25,
    'lengthChange' => true,
    'responsive' => true,
    'order' => [[3, 'desc']]
];
$tableOptionsAttr = htmlspecialchars(json_encode($tableOptions, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');

$inline_styles .= "\n    body { background: #f4f6fb; }\n    .sa-list .card { border-radius: 20px; border: 1px solid rgba(148,163,184,0.18); box-shadow: 0 22px 32px rgba(15,23,42,0.05); }\n    .sa-filters .form-select, .sa-filters .form-control { border-radius: 999px; }\n    .sa-table-wrapper { overflow-x: auto; }\n";

include __DIR__ . '/../../layouts/organization-header.php';
include __DIR__ . '/../../layouts/organization-sidebar.php';

$navbarUser = $user;
?>

<?php include __DIR__ . '/../../layouts/organization-navbar.php'; ?>

<div class="page-content-wrapper">
    <div class="page-content sa-list">
        <div class="card border-0 shadow-sm rounded-24 mb-24">
            <div class="card-body p-24">
                <?php $showTopHeader = $showTopHeader ?? false; ?>
                <?php if ($showTopHeader): ?>
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-16 mb-16">
                        <div>
                            <h2 class="mb-6 text-gray-900">نتایج خود ارزیابی کاربران</h2>
                            <p class="text-gray-500 mb-0">لیست ارزیابی‌شوندگان با وضعیت تکمیل آزمون‌ها. می‌توانید گواهی را برای موارد تکمیل‌شده مشاهده کنید.</p>
                        </div>
                        <div class="d-flex flex-wrap align-items-center gap-8">
                            <a href="<?= UtilityHelper::baseUrl('organizations/reports/certificate-settings'); ?>" class="btn btn-outline-main rounded-pill px-20 d-flex align-items-center gap-8">
                                <ion-icon name="settings-outline"></ion-icon>
                                تنظیمات گواهی
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <?php foreach ([['type' => 'success', 'message' => $successMessage], ['type' => 'error', 'message' => $errorMessage], ['type' => 'warning', 'message' => $warningMessage], ['type' => 'info', 'message' => $infoMessage]] as $alert): ?>
                    <?php if (!empty($alert['message'])): ?>
                        <div class="alert alert-<?= htmlspecialchars($alert['type'], ENT_QUOTES, 'UTF-8'); ?> rounded-16 d-flex align-items-center gap-12 mb-16" role="alert">
                            <ion-icon name="information-circle-outline"></ion-icon>
                            <span><?= htmlspecialchars((string) $alert['message'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <form method="get" action="<?= UtilityHelper::baseUrl('organizations/reports/self-assessment'); ?>" class="sa-filters mb-16">
                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-md-4">
                            <input type="text" name="search" value="<?= htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" placeholder="جستجو نام/نام‌کاربری/کد ملی/عنوان ارزیابی..." />
                        </div>
                        <div class="col-12 col-md-4">
                            <select name="evaluation_id" class="form-select">
                                <?php foreach ($evaluationOptions as $opt): $val = (int) ($opt['value'] ?? 0); $label = (string) ($opt['label'] ?? ''); ?>
                                    <option value="<?= (int) $val; ?>" <?= $val === $selectedEvaluationId ? 'selected' : ''; ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-4 text-start text-md-end">
                            <button type="submit" class="btn btn-main rounded-pill px-20">اعمال فیلتر</button>
                        </div>
                    </div>
                </form>

                <div class="sa-table-wrapper">
                    <table class="table table-striped align-middle w-100 js-data-table" id="self-assessment-table" data-datatable-options="<?= $tableOptionsAttr; ?>">
                        <thead>
                            <tr>
                                <th>نام</th>
                                <th>نام‌کاربری</th>
                                <th>کد ملی</th>
                                <th>عنوان ارزیابی</th>
                                <th>تاریخ ارزیابی</th>
                                <th>مدل شایستگی</th>
                                <th>میانگین امتیاز</th>
                                <th>آخرین ثبت امتیاز</th>
                                <th class="text-center">اقدامات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($selfAssessmentRows)): ?>
                                <?php foreach ($selfAssessmentRows as $row): ?>
                                    <?php
                                        $fullName = trim((string) ($row['first_name'] ?? '') . ' ' . (string) ($row['last_name'] ?? ''));
                                        if ($fullName === '') {
                                            $fullName = (string) ($row['username'] ?? 'ارزیابی‌شونده');
                                        }
                                        $evaluationId = (int) ($row['evaluation_id'] ?? 0);
                                        $evaluateeId = (int) ($row['evaluatee_id'] ?? 0);
                                        $viewable = !empty($row['exam_results_viewable']);
                                        $isSelected = !empty($row['is_selected']);
                                        $isVisibleToUser = !empty($row['is_visible_to_user']);
                                        $avg = $row['average_score'];
                                        $avgDisplay = ($avg !== null && $avg !== '') ? UtilityHelper::englishToPersian((string) $avg) : '-';
                                        $lastDisplay = (string) ($row['last_scored_at_display'] ?? '—');
                                        $certUrl = UtilityHelper::baseUrl('organizations/reports/self-assessment/certificate?evaluation_id=' . urlencode((string) $evaluationId) . '&evaluatee_id=' . urlencode((string) $evaluateeId));
                                        $certPreviewUrl = $certUrl . '&preview=1';
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars((string) ($row['username'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars(UtilityHelper::englishToPersian((string) ($row['national_code'] ?? '')), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars((string) ($row['evaluation_title'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars((string) ($row['evaluation_date_display'] ?? '—'), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars((string) ($row['competency_model'] ?? '—'), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= $avgDisplay; ?></td>
                                        <td><?= htmlspecialchars($lastDisplay, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="text-center">
                                            <?php if ($evaluationId > 0 && $evaluateeId > 0): ?>
                                                <?php if ($viewable): ?>
                                                    <div class="d-flex gap-6 justify-content-center flex-wrap">
                                                        <a href="<?= htmlspecialchars($certUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-success btn-sm rounded-pill px-16 d-inline-flex align-items-center gap-6" title="مشاهده گزارش">
                                                            <ion-icon name="ribbon-outline"></ion-icon>
                                                            گزارش
                                                        </a>
                                                        <button type="button" class="btn btn-primary btn-sm rounded-pill px-16 d-inline-flex align-items-center gap-6 download-cert-btn" 
                                                                data-evaluation-id="<?= (int) $evaluationId; ?>" 
                                                                data-evaluatee-id="<?= (int) $evaluateeId; ?>" 
                                                                title="دانلود گزارش به صورت PDF">
                                                            <ion-icon name="download-outline"></ion-icon>
                                                            دانلود
                                                        </button>
                                                        <button type="button" class="btn <?= $isSelected ? 'btn-info' : 'btn-outline-info'; ?> btn-sm rounded-pill px-16 d-inline-flex align-items-center gap-6 select-resume-btn"
                                                                data-evaluation-id="<?= (int) $evaluationId; ?>" 
                                                                data-evaluatee-id="<?= (int) $evaluateeId; ?>"
                                                                data-evaluatee-name="<?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'); ?>"
                                                                data-is-selected="<?= $isSelected ? '1' : '0'; ?>"
                                                                title="<?= $isSelected ? 'حذف از رزومه منتخب' : 'انتخاب برای رزومه منتخب'; ?>">
                                                            <ion-icon name="<?= $isSelected ? 'star' : 'star-outline'; ?>"></ion-icon>
                                                            <?= $isSelected ? 'منتخب شده' : 'رزومه منتخب'; ?>
                                                        </button>
                                                        <button type="button" class="btn <?= $isVisibleToUser ? 'btn-warning' : 'btn-outline-warning'; ?> btn-sm rounded-pill px-16 d-inline-flex align-items-center gap-6 show-to-user-btn"
                                                                data-evaluation-id="<?= (int) $evaluationId; ?>" 
                                                                data-evaluatee-id="<?= (int) $evaluateeId; ?>"
                                                                data-evaluatee-name="<?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'); ?>"
                                                                data-is-visible="<?= $isVisibleToUser ? '1' : '0'; ?>"
                                                                title="نمایش نتایج برای کاربر">
                                                            <ion-icon name="<?= $isVisibleToUser ? 'eye' : 'eye-outline'; ?>"></ion-icon>
                                                            <?= $isVisibleToUser ? 'قابل مشاهده' : 'نمایش برای کاربر'; ?>
                                                        </button>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="d-inline-block" tabindex="0" data-bs-toggle="tooltip" title="برای دریافت گواهی، باید همه آزمون‌ها و WashUp تکمیل شده باشند.">
                                                        <a href="#" class="btn btn-outline-secondary btn-sm rounded-pill px-16 disabled d-inline-flex align-items-center gap-6" tabindex="-1" aria-disabled="true">
                                                            <ion-icon name="ribbon-outline"></ion-icon>
                                                            گواهی
                                                        </a>
                                                    </span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-16">رکوردی یافت نشد.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$selectResumeUrl = UtilityHelper::baseUrl('organizations/reports/self-assessment/select-resume');
$unselectResumeUrl = UtilityHelper::baseUrl('organizations/reports/self-assessment/unselect-resume');
$toggleVisibilityUrl = UtilityHelper::baseUrl('organizations/reports/self-assessment/toggle-visibility');
?>

<script>
var SELECT_RESUME_URL = '<?= $selectResumeUrl; ?>';
var UNSELECT_RESUME_URL = '<?= $unselectResumeUrl; ?>';
var TOGGLE_VISIBILITY_URL = '<?= $toggleVisibilityUrl; ?>';
</script>

<?php
$inline_scripts .= <<<'SCRIPT'
    document.addEventListener('DOMContentLoaded', function () {
        // Handle certificate download buttons
        document.querySelectorAll('.download-cert-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var evaluationId = this.getAttribute('data-evaluation-id');
                var evaluateeId = this.getAttribute('data-evaluatee-id');
                
                if (!evaluationId || !evaluateeId) {
                    alert('خطا: اطلاعات گواهی یافت نشد');
                    return;
                }
                
                // Show loading state
                var originalHTML = this.innerHTML;
                this.disabled = true;
                this.innerHTML = '<ion-icon name="hourglass-outline"></ion-icon> در حال دانلود...';
                
                // Build certificate URL with auto download
                var certUrl = window.location.origin + window.location.pathname.replace('/self-assessment', '/self-assessment/certificate');
                certUrl += '?evaluation_id=' + evaluationId + '&evaluatee_id=' + evaluateeId + '&auto_download=1';
                
                console.log('Opening certificate URL:', certUrl);
                
                // Open in new window
                var downloadWindow = window.open(certUrl, '_blank');
                
                var self = this;
                
                // Listen for message from certificate page when download is complete
                var messageHandler = function(event) {
                    // Check origin for security
                    if (event.origin !== window.location.origin) return;
                    
                    if (event.data === 'certificate_download_complete') {
                        console.log('Download complete, closing window');
                        
                        // Close the download window
                        if (downloadWindow && !downloadWindow.closed) {
                            downloadWindow.close();
                        }
                        
                        // Restore button
                        self.innerHTML = originalHTML;
                        self.disabled = false;
                        
                        // Remove event listener
                        window.removeEventListener('message', messageHandler);
                    }
                };
                
                window.addEventListener('message', messageHandler);
                
                // Fallback: restore button after 30 seconds if no message received
                setTimeout(function() {
                    if (self.disabled) {
                        self.innerHTML = originalHTML;
                        self.disabled = false;
                        window.removeEventListener('message', messageHandler);
                    }
                }, 30000);
            });
        });

        // Handle select resume button (toggle functionality)
        document.querySelectorAll('.select-resume-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var evaluationId = this.getAttribute('data-evaluation-id');
                var evaluateeId = this.getAttribute('data-evaluatee-id');
                var evaluateeName = this.getAttribute('data-evaluatee-name');
                var isSelected = this.getAttribute('data-is-selected') === '1';
                
                if (!evaluationId || !evaluateeId) {
                    alert('خطا: اطلاعات کاربر یافت نشد');
                    return;
                }
                
                var confirmMessage = isSelected 
                    ? 'آیا می‌خواهید "' + evaluateeName + '" را از لیست رزومه‌های منتخب حذف کنید؟'
                    : 'آیا می‌خواهید "' + evaluateeName + '" را به عنوان رزومه منتخب انتخاب کنید؟';
                
                if (confirm(confirmMessage)) {
                    var originalHTML = this.innerHTML;
                    var originalClass = this.className;
                    this.disabled = true;
                    this.innerHTML = '<ion-icon name="hourglass-outline"></ion-icon> در حال ثبت...';
                    
                    var self = this;
                    var url = isSelected ? UNSELECT_RESUME_URL : SELECT_RESUME_URL;
                    
                    console.log('Sending request to:', url);
                    console.log('Data:', {evaluation_id: evaluationId, evaluatee_id: evaluateeId});
                    
                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'evaluation_id=' + evaluationId + '&evaluatee_id=' + evaluateeId
                    })
                    .then(function(response) {
                        console.log('Response status:', response.status);
                        console.log('Response headers:', response.headers);
                        if (!response.ok) {
                            throw new Error('HTTP error! status: ' + response.status);
                        }
                        return response.text();
                    })
                    .then(function(text) {
                        console.log('Response text:', text);
                        try {
                            var data = JSON.parse(text);
                            console.log('Parsed data:', data);
                        } catch(e) {
                            console.error('JSON parse error:', e);
                            throw new Error('Invalid JSON response: ' + text.substring(0, 100));
                        }
                        
                        if (data.success) {
                            if (isSelected) {
                                // Changed from selected to unselected
                                alert('رزومه با موفقیت از لیست منتخب حذف شد.');
                                self.innerHTML = '<ion-icon name="star-outline"></ion-icon> رزومه منتخب';
                                self.className = 'btn btn-outline-info btn-sm rounded-pill px-16 d-inline-flex align-items-center gap-6 select-resume-btn';
                                self.setAttribute('data-is-selected', '0');
                                self.setAttribute('title', 'انتخاب برای رزومه منتخب');
                            } else {
                                // Changed from unselected to selected
                                alert('رزومه با موفقیت به عنوان منتخب انتخاب شد.');
                                self.innerHTML = '<ion-icon name="star"></ion-icon> منتخب شده';
                                self.className = 'btn btn-info btn-sm rounded-pill px-16 d-inline-flex align-items-center gap-6 select-resume-btn';
                                self.setAttribute('data-is-selected', '1');
                                self.setAttribute('title', 'حذف از رزومه منتخب');
                            }
                            self.disabled = false;
                        } else {
                            alert('خطا: ' + (data.message || 'عملیات ناموفق بود'));
                            self.innerHTML = originalHTML;
                            self.className = originalClass;
                            self.disabled = false;
                        }
                    })
                    .catch(function(error) {
                        console.error('Fetch error:', error);
                        alert('خطا در ارتباط با سرور: ' + error.message);
                        self.innerHTML = originalHTML;
                        self.className = originalClass;
                        self.disabled = false;
                    });
                }
            });
        });

        // Handle show to user button (toggle visibility)
        document.querySelectorAll('.show-to-user-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var evaluationId = this.getAttribute('data-evaluation-id');
                var evaluateeId = this.getAttribute('data-evaluatee-id');
                var evaluateeName = this.getAttribute('data-evaluatee-name');
                var isVisible = this.getAttribute('data-is-visible') === '1';
                
                if (!evaluationId || !evaluateeId) {
                    alert('خطا: اطلاعات کاربر یافت نشد');
                    return;
                }
                
                var action = isVisible ? 'غیرفعال' : 'فعال';
                var confirmMsg = isVisible 
                    ? 'آیا می‌خواهید نتایج ارزیابی برای "' + evaluateeName + '" غیرفعال شود؟\n\nکاربر دیگر نمی‌تواند گواهی و نتایج خود را مشاهده کند.'
                    : 'آیا می‌خواهید نتایج ارزیابی برای "' + evaluateeName + '" فعال شود؟\n\nکاربر می‌تواند گواهی و نتایج خود را مشاهده کند.';
                
                if (confirm(confirmMsg)) {
                    // Show loading
                    var originalHTML = this.innerHTML;
                    var originalClass = this.className;
                    this.disabled = true;
                    this.innerHTML = '<ion-icon name="hourglass-outline"></ion-icon> در حال ' + action + '‌سازی...';
                    
                    var self = this;
                    var newVisibleValue = isVisible ? 0 : 1;
                    
                    // Send AJAX request
                    fetch(TOGGLE_VISIBILITY_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'evaluation_id=' + evaluationId + '&evaluatee_id=' + evaluateeId + '&visible=' + newVisibleValue
                    })
                    .then(function(response) {
                        return response.json();
                    })
                    .then(function(data) {
                        if (data.success) {
                            if (isVisible) {
                                // Changed from visible to hidden
                                alert('نتایج با موفقیت برای کاربر غیرفعال شد.');
                                self.innerHTML = '<ion-icon name="eye-outline"></ion-icon> نمایش برای کاربر';
                                self.className = 'btn btn-outline-warning btn-sm rounded-pill px-16 d-inline-flex align-items-center gap-6 show-to-user-btn';
                                self.setAttribute('data-is-visible', '0');
                                self.setAttribute('title', 'فعال‌سازی نمایش برای کاربر');
                            } else {
                                // Changed from hidden to visible
                                alert('نتایج با موفقیت برای کاربر فعال شد.');
                                self.innerHTML = '<ion-icon name="eye"></ion-icon> قابل مشاهده';
                                self.className = 'btn btn-warning btn-sm rounded-pill px-16 d-inline-flex align-items-center gap-6 show-to-user-btn';
                                self.setAttribute('data-is-visible', '1');
                                self.setAttribute('title', 'غیرفعال‌سازی نمایش برای کاربر');
                            }
                            self.disabled = false;
                        } else {
                            alert('خطا: ' + (data.message || 'عملیات ناموفق بود'));
                            self.innerHTML = originalHTML;
                            self.className = originalClass;
                            self.disabled = false;
                        }
                    })
                    .catch(function(error) {
                        console.error('Fetch error:', error);
                        alert('خطا در ارتباط با سرور: ' + error.message);
                        self.innerHTML = originalHTML;
                        self.className = originalClass;
                        self.disabled = false;
                    });
                }
            });
        });
    });
SCRIPT;
?>

<?php include __DIR__ . '/../../layouts/organization-footer.php'; ?>
