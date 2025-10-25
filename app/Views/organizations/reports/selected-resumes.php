<?php
if (!isset($organizationData) || !isset($userData)) {
    http_response_code(403);
    exit('دسترسی غیرمجاز');
}

if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../../Helpers/autoload.php';
}

$title = 'رزومه‌های منتخب';
$organizationId = (int) ($organizationData['id'] ?? 0);
$selectedResumes = $selectedResumes ?? [];

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

$inline_styles .= "\n    body { background: #f4f6fb; }\n    .card { border-radius: 20px; border: 1px solid rgba(148,163,184,0.18); box-shadow: 0 22px 32px rgba(15,23,42,0.05); }\n";

include __DIR__ . '/../../layouts/organization-header.php';
include __DIR__ . '/../../layouts/organization-sidebar.php';

$navbarUser = $user;
?>

<?php include __DIR__ . '/../../layouts/organization-navbar.php'; ?>

<div class="page-content-wrapper">
    <div class="page-content">
        <div class="card border-0 shadow-sm rounded-24 mb-24">
            <div class="card-body p-24">
                        <?php if (empty($selectedResumes)): ?>
                            <div class="alert alert-info border-0 d-flex align-items-center" role="alert">
                                <div class="d-flex gap-3 align-items-center">
                                    <ion-icon name="information-circle-outline" class="fs-3"></ion-icon>
                                    <div>
                                        <strong>اطلاعاتی یافت نشد</strong><br>
                                        هیچ رزومه‌ای به عنوان منتخب انتخاب نشده است.
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table id="selectedResumesTable" class="table table-striped table-bordered" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>ردیف</th>
                                            <th>عنوان ارزیابی</th>
                                            <th>نام و نام خانوادگی</th>
                                            <th>کد ملی</th>
                                            <th>کد پرسنلی</th>
                                            <th>پست سازمانی</th>
                                            <th>محل خدمت</th>
                                            <th>تاریخ انتخاب</th>
                                            <th>انتخاب شده توسط</th>
                                            <th>عملیات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($selectedResumes as $index => $resume): ?>
                                            <tr>
                                                <td><?= $index + 1; ?></td>
                                                <td>
                                                    <strong><?= htmlspecialchars($resume['evaluation_title'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></strong>
                                                    <?php if (!empty($resume['evaluation_date'])): ?>
                                                        <br>
                                                        <small class="text-muted">
                                                            <ion-icon name="calendar-outline"></ion-icon>
                                                            <?= htmlspecialchars($resume['evaluation_date'], ENT_QUOTES, 'UTF-8'); ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="user-avatar">
                                                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                                                <?= mb_substr($resume['first_name'] ?? 'ک', 0, 1, 'UTF-8'); ?>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold">
                                                                <?= htmlspecialchars(trim(($resume['first_name'] ?? '') . ' ' . ($resume['last_name'] ?? '')), ENT_QUOTES, 'UTF-8'); ?>
                                                            </div>
                                                            <?php if (!empty($resume['username'])): ?>
                                                                <small class="text-muted">@<?= htmlspecialchars($resume['username'], ENT_QUOTES, 'UTF-8'); ?></small>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="font-monospace"><?= htmlspecialchars($resume['national_code'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></span>
                                                </td>
                                                <td>
                                                    <span class="font-monospace"><?= htmlspecialchars($resume['personnel_code'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></span>
                                                </td>
                                                <td><?= htmlspecialchars($resume['organization_post'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= htmlspecialchars($resume['service_location'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td>
                                                    <small class="text-muted">
                                                        <ion-icon name="time-outline"></ion-icon>
                                                        <?= htmlspecialchars($resume['selected_at'] ?? '-', ENT_QUOTES, 'UTF-8'); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <?php if (!empty($resume['selected_by_name'])): ?>
                                                        <small>
                                                            <ion-icon name="person-outline"></ion-icon>
                                                            <?= htmlspecialchars($resume['selected_by_name'], ENT_QUOTES, 'UTF-8'); ?>
                                                        </small>
                                                    <?php else: ?>
                                                        <small class="text-muted">-</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-2 flex-wrap">
                                                        <a href="<?= UtilityHelper::baseUrl('organizations/reports/self-assessment/certificate?evaluation_id=' . ($resume['evaluation_id'] ?? 0) . '&evaluatee_id=' . ($resume['evaluatee_id'] ?? 0)); ?>" 
                                                           class="btn btn-sm btn-outline-primary" 
                                                           title="مشاهده گواهی"
                                                           target="_blank">
                                                            <ion-icon name="ribbon-outline"></ion-icon>
                                                            گواهی
                                                        </a>
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-danger remove-selection-btn" 
                                                                data-resume-id="<?= (int)($resume['id'] ?? 0); ?>"
                                                                data-evaluatee-name="<?= htmlspecialchars(trim(($resume['first_name'] ?? '') . ' ' . ($resume['last_name'] ?? '')), ENT_QUOTES, 'UTF-8'); ?>"
                                                                title="حذف از لیست منتخب">
                                                            <ion-icon name="close-circle-outline"></ion-icon>
                                                            حذف
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$inline_scripts .= <<<'SCRIPT'

    // Initialize DataTable for selected resumes
    if ($('#selectedResumesTable').length > 0) {
        var table = $('#selectedResumesTable').DataTable({
            language: {
                "sProcessing": "در حال پردازش...",
                "sLengthMenu": "نمایش _MENU_ ردیف",
                "sZeroRecords": "هیچ رکوردی یافت نشد",
                "sInfo": "نمایش _START_ تا _END_ از _TOTAL_ ردیف",
                "sInfoEmpty": "نمایش 0 تا 0 از 0 ردیف",
                "sInfoFiltered": "(فیلتر شده از _MAX_ ردیف)",
                "sSearch": "جستجو:",
                "oPaginate": {
                    "sFirst": "ابتدا",
                    "sPrevious": "قبلی",
                    "sNext": "بعدی",
                    "sLast": "انتها"
                }
            },
            order: [[7, 'desc']],
            pageLength: 25,
            responsive: true
        });

        // Handle remove selection button
        document.querySelectorAll('.remove-selection-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var resumeId = this.getAttribute('data-resume-id');
                var evaluateeName = this.getAttribute('data-evaluatee-name');
                
                if (!resumeId || resumeId === '0') {
                    alert('خطا: شناسه رزومه یافت نشد');
                    return;
                }
                
                if (confirm('آیا مطمئن هستید که می‌خواهید "' + evaluateeName + '" را از لیست رزومه‌های منتخب حذف کنید؟')) {
                    var originalHTML = this.innerHTML;
                    this.disabled = true;
                    this.innerHTML = '<ion-icon name="hourglass-outline"></ion-icon> در حال حذف...';
                    
                    var self = this;
                    var row = this.closest('tr');
                    
                    fetch(window.location.origin + '/organizations/reports/selected-resumes/remove', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'resume_id=' + resumeId
                    })
                    .then(function(response) {
                        return response.json();
                    })
                    .then(function(data) {
                        if (data.success) {
                            table.row(row).remove().draw();
                            alert('رزومه با موفقیت از لیست منتخب حذف شد.');
                            
                            if (table.rows().count() === 0) {
                                window.location.reload();
                            }
                        } else {
                            alert('خطا: ' + (data.message || 'عملیات ناموفق بود'));
                            self.innerHTML = originalHTML;
                            self.disabled = false;
                        }
                    })
                    .catch(function(error) {
                        alert('خطا در ارتباط با سرور');
                        self.innerHTML = originalHTML;
                        self.disabled = false;
                    });
                }
            });
        });
    }

SCRIPT;

include __DIR__ . '/../../layouts/organization-footer.php';
?>