<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'برنامه‌های توسعه فردی';
$user = (class_exists('AuthHelper') && AuthHelper::getUser()) ? AuthHelper::getUser() : [
    'name' => 'کاربر سازمان',
    'email' => 'organization@example.com',
];
$additional_css = $additional_css ?? [];
$additional_js = $additional_js ?? [];
$inline_styles = $inline_styles ?? '';
$inline_scripts = $inline_scripts ?? '';

$developmentPrograms = $developmentPrograms ?? [];
$successMessage = $successMessage ?? null;
$errorMessage = $errorMessage ?? null;

$additional_css[] = 'https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css';
$additional_css[] = 'https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css';
$additional_js[] = 'https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js';
$additional_js[] = 'https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js';
$additional_js[] = 'https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js';
$additional_js[] = 'https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js';
$additional_js[] = 'public/assets/js/datatables-init.js';

$inline_styles .= <<<'CSS'
    body {
        background: #f5f7fb;
    }
    .development-programs-table tbody tr td {
        vertical-align: middle;
    }
    .development-programs-table .table-actions {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .development-programs-table .table-actions .btn,
    .development-programs-table .table-actions button {
        width: 40px;
        height: 40px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }
    .development-programs-table .table-actions ion-icon {
        font-size: 18px;
    }
CSS;

include __DIR__ . '/../../layouts/organization-header.php';
include __DIR__ . '/../../layouts/organization-sidebar.php';

$navbarUser = $user;
?>

<?php include __DIR__ . '/../../layouts/organization-navbar.php'; ?>

<div class="page-content-wrapper">
    <div class="page-content">
        <div class="row g-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-24 h-100">
                    <div class="card-body p-24">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-16 mb-24">
                            <div>
                                <h2 class="mb-6 text-gray-900">برنامه‌های توسعه فردی</h2>
                                <p class="text-gray-500 mb-0">لیست دوره‌های توسعه فردی سازمان را مشاهده و مدیریت کنید.</p>
                            </div>
                            <div class="d-flex gap-10 flex-wrap">
                                <a href="<?= UtilityHelper::baseUrl('organizations/development-programs/create'); ?>" class="btn btn-main" title="افزودن دوره آموزشی">
                                    افزودن دوره آموزشی
                                    <span class="visually-hidden">افزودن دوره توسعه فردی جدید</span>
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

                        <div class="table-responsive rounded-16 border border-gray-100" style="direction: rtl;">
                            <table class="table align-middle mb-0 development-programs-table js-data-table mt-5">
                                <thead class="bg-gray-100 text-gray-700">
                                    <tr>
                                        <th scope="col" class="no-sort no-search nowrap text-start">عملیات</th>
                                        <th scope="col" class="text-start">کد دوره</th>
                                        <th scope="col" class="text-start">شایستگی</th>
                                        <th scope="col" class="text-start">نام دوره آموزشی</th>
                                        <th scope="col" class="text-start">ساعت دوره</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($developmentPrograms)): ?>
                                        <?php foreach ($developmentPrograms as $program): ?>
                                            <?php
                                                $programId = (int) ($program['id'] ?? 0);
                                                $courseCode = trim((string) ($program['course_code'] ?? ''));
                                                $courseTitle = trim((string) ($program['course_title'] ?? ''));
                                                $competencyCode = trim((string) ($program['competency_code'] ?? ''));
                                                $competencyTitle = trim((string) ($program['competency_title'] ?? ''));
                                                $courseHoursValue = isset($program['course_hours']) ? (int) $program['course_hours'] : 0;

                                                $competencyDisplay = '—';
                                                if ($competencyCode !== '' && $competencyTitle !== '') {
                                                    $competencyDisplay = $competencyCode . ' - ' . $competencyTitle;
                                                } elseif ($competencyTitle !== '') {
                                                    $competencyDisplay = $competencyTitle;
                                                } elseif ($competencyCode !== '') {
                                                    $competencyDisplay = $competencyCode;
                                                }

                                                $courseHoursDisplay = $courseHoursValue > 0
                                                    ? UtilityHelper::englishToPersian((string) $courseHoursValue)
                                                    : '—';
                                            ?>
                                            <tr>
                                                <td class="nowrap" style="width: 12%;">
                                                    <div class="table-actions">
                                                        <a href="<?= UtilityHelper::baseUrl('organizations/development-programs/edit?id=' . urlencode((string) $programId)); ?>" class="btn btn-sm btn-outline-main" title="ویرایش">
                                                            <ion-icon name="create-outline"></ion-icon>
                                                            <span class="visually-hidden">ویرایش</span>
                                                        </a>
                                                        <form action="<?= UtilityHelper::baseUrl('organizations/development-programs/delete'); ?>" method="post" onsubmit="return confirm('آیا از حذف این دوره آموزشی اطمینان دارید؟');" class="d-inline-flex">
                                                            <?= csrf_field(); ?>
                                                            <input type="hidden" name="id" value="<?= htmlspecialchars((string) $programId, ENT_QUOTES, 'UTF-8'); ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف">
                                                                <ion-icon name="trash-outline"></ion-icon>
                                                                <span class="visually-hidden">حذف</span>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                                <td><?= htmlspecialchars($courseCode !== '' ? $courseCode : '—', ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= htmlspecialchars($competencyDisplay, ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= htmlspecialchars($courseTitle !== '' ? $courseTitle : '—', ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= htmlspecialchars($courseHoursDisplay, ENT_QUOTES, 'UTF-8'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            <?php if (empty($developmentPrograms)): ?>
                                <div class="text-center py-32 text-gray-500">
                                    دوره‌ای برای نمایش وجود ندارد. برای ثبت اولین دوره از دکمه «افزودن دوره آموزشی» استفاده کنید.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include __DIR__ . '/../../layouts/organization-footer.php'; ?>
    </div>
</div>
