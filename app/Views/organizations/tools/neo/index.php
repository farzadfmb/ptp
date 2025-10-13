<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../../Helpers/autoload.php';
}

$title = $title ?? 'تنظیمات آزمون NEO';
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

$neoTraits = is_array($neoTraits ?? null) ? $neoTraits : [];
$traitOptions = is_array($traitOptions ?? null) ? $traitOptions : [];

$inline_styles .= <<<'CSS'
    body {
        background: #f5f7fb;
    }
    .neo-card {
        border-radius: 24px;
        border: 1px solid #e6e9f2;
        background: #ffffff;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
    }
    .neo-table tbody td {
        vertical-align: top;
        white-space: normal;
        word-break: break-word;
    }
    .neo-table .table-actions {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .section-heading {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
    }
    .section-heading h3 {
        margin: 0;
        font-size: 1.15rem;
        font-weight: 700;
        color: #111827;
    }
    .section-heading p {
        margin: 4px 0 0;
        color: #6b7280;
    }
    .neo-table .table-actions .btn,
    .neo-table .table-actions button {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
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
                <div class="neo-card shadow-sm p-24 mb-24">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-16">
                        <div>
                            <h2 class="mb-6 text-gray-900">تنظیمات آزمون NEO</h2>
                            <p class="text-gray-500 mb-0">تیپ‌های شخصیتی پنج‌عاملی (NEO) مخصوص سازمان خود را مدیریت کنید.</p>
                        </div>
                        <div class="d-flex gap-10 flex-wrap">
                            <form action="<?= UtilityHelper::baseUrl('organizations/tools/neo-settings/seed'); ?>" method="post" class="d-inline-flex" onsubmit="return confirm('با انتخاب این گزینه، تیپ‌های آماده NEO ثبت یا به‌روزرسانی می‌شوند. ادامه می‌دهید؟');">
                                <?= csrf_field(); ?>
                                <button type="submit" class="btn btn-outline-success" title="افزودن تیپ‌های آماده NEO">
                                    اضافه کردن تیپ‌های آماده
                                </button>
                            </form>
                            <a href="<?= UtilityHelper::baseUrl('organizations/tools/neo-settings/create'); ?>" class="btn btn-main" title="افزودن تیپ شخصیتی جدید">
                                افزودن تیپ شخصیتی جدید
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($successMessage)): ?>
                <div class="col-12">
                    <div class="alert alert-success rounded-16 d-flex align-items-center gap-12" role="alert">
                        <ion-icon name="checkmark-circle-outline"></ion-icon>
                        <span><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($errorMessage)): ?>
                <div class="col-12">
                    <div class="alert alert-danger rounded-16 d-flex align-items-center gap-12" role="alert">
                        <ion-icon name="warning-outline"></ion-icon>
                        <span><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <div class="col-12">
                <div class="neo-card shadow-sm p-24">
                    <div class="section-heading mb-24">
                        <div>
                            <h3>تیپ‌های شخصیتی ثبت‌شده</h3>
                            <p>هر تیپ شامل توضیح کوتاه، محرک‌های کلیدی، سبک ارتباطی، تمرکز رشد و نشانه‌های استرس است.</p>
                        </div>
                        <a href="<?= UtilityHelper::baseUrl('organizations/tools/neo-settings/create'); ?>" class="btn btn-main rounded-pill px-20" title="افزودن تیپ NEO">
                            <ion-icon name="add-outline"></ion-icon>
                            افزودن تیپ
                        </a>
                    </div>

                    <div class="table-responsive rounded-16 border border-gray-100" style="direction: rtl;">
                        <table class="table align-middle mb-0 neo-table js-data-table">
                            <thead class="bg-gray-100 text-gray-700">
                                <tr>
                                    <th scope="col" class="text-nowrap">عملیات</th>
                                    <th scope="col" class="text-nowrap">تیپ شخصیتی</th>
                                    <th scope="col">توضیح کوتاه</th>
                                    <th scope="col">محرک‌های کلیدی</th>
                                    <th scope="col">سبک ارتباط</th>
                                    <th scope="col">تمرکز رشد</th>
                                    <th scope="col">نشانه‌های استرس</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($neoTraits)): ?>
                                    <?php foreach ($neoTraits as $trait): ?>
                                        <?php
                                            $traitId = (int) ($trait['id'] ?? 0);
                                            $editUrl = UtilityHelper::baseUrl('organizations/tools/neo-settings/edit?id=' . urlencode((string) $traitId));
                                            $deleteUrl = UtilityHelper::baseUrl('organizations/tools/neo-settings/delete');
                                        ?>
                                        <tr>
                                            <td class="text-nowrap" style="width: 120px;">
                                                <div class="table-actions">
                                                    <a href="<?= $editUrl; ?>" class="btn btn-sm btn-outline-main" title="ویرایش">
                                                        <ion-icon name="create-outline"></ion-icon>
                                                    </a>
                                                    <form action="<?= $deleteUrl; ?>" method="post" onsubmit="return confirm('آیا از حذف این تیپ شخصیتی اطمینان دارید؟');" class="d-inline-flex">
                                                        <?= csrf_field(); ?>
                                                        <input type="hidden" name="id" value="<?= htmlspecialchars((string) $traitId, ENT_QUOTES, 'UTF-8'); ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف">
                                                            <ion-icon name="trash-outline"></ion-icon>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                            <td class="fw-semibold text-primary text-nowrap">
                                                <?= htmlspecialchars((string) ($trait['trait_label'] ?? $trait['trait_code'] ?? '---'), ENT_QUOTES, 'UTF-8'); ?>
                                            </td>
                                            <td><?= nl2br(htmlspecialchars((string) ($trait['short_description'] ?? ''), ENT_QUOTES, 'UTF-8')); ?></td>
                                            <td><?= nl2br(htmlspecialchars((string) ($trait['key_drivers'] ?? ''), ENT_QUOTES, 'UTF-8')); ?></td>
                                            <td><?= nl2br(htmlspecialchars((string) ($trait['communication_style'] ?? ''), ENT_QUOTES, 'UTF-8')); ?></td>
                                            <td><?= nl2br(htmlspecialchars((string) ($trait['development_focus'] ?? ''), ENT_QUOTES, 'UTF-8')); ?></td>
                                            <td><?= nl2br(htmlspecialchars((string) ($trait['stress_signals'] ?? ''), ENT_QUOTES, 'UTF-8')); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <?php if (empty($neoTraits)): ?>
                            <div class="text-center py-32 text-gray-500">
                                هنوز تیپی برای آزمون NEO ثبت نشده است. از دکمه «افزودن تیپ» استفاده کنید.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php include __DIR__ . '/../../../layouts/organization-footer.php'; ?>
    </div>
</div>
