<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../../Helpers/autoload.php';
}

$title = $title ?? 'تنظیمات آزمون MBTI';
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

$mbtiTypes = $mbtiTypes ?? [];
$successMessage = $successMessage ?? null;
$errorMessage = $errorMessage ?? null;

if (!function_exists('renderMbtiMatrix')) {
    function renderMbtiMatrix(string $selectedCode): string
    {
        $matrix = [
            ['ISTJ', 'ISFJ', 'INFJ', 'INTJ'],
            ['ISTP', 'ISFP', 'INFP', 'INTP'],
            ['ESTP', 'ESFP', 'ENFP', 'ENTP'],
            ['ESTJ', 'ESFJ', 'ENFJ', 'ENTJ'],
        ];

        $selected = strtoupper(trim($selectedCode));
        $html = '<div class="mbti-chart-grid">';

        foreach ($matrix as $row) {
            $html .= '<div class="mbti-chart-row">';
            foreach ($row as $cell) {
                $isActive = $cell === $selected;
                $classes = 'mbti-chart-cell' . ($isActive ? ' is-active' : '');
                $html .= '<span class="' . $classes . '">' . htmlspecialchars($cell, ENT_QUOTES, 'UTF-8') . '</span>';
            }
            $html .= '</div>';
        }

        $html .= '</div>';
        return $html;
    }
}

$inline_styles .= <<<'CSS'
    body {
        background: #f5f7fb;
    }
    .mbti-types-table tbody tr td {
        vertical-align: middle;
    }
    .mbti-types-table .table-actions {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .mbti-types-table .table-actions .btn,
    .mbti-types-table .table-actions button {
        width: 40px;
        height: 40px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }
    .mbti-types-table .table-actions ion-icon {
        font-size: 18px;
    }
    .mbti-chart-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 6px;
        direction: ltr;
    }
    .mbti-chart-row {
        display: contents;
    }
    .mbti-chart-cell {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 6px;
        border-radius: 12px;
        background-color: #f2f4f7;
        font-weight: 600;
        font-size: 0.85rem;
        color: #475467;
        min-width: 48px;
    }
    .mbti-chart-cell.is-active {
        background: linear-gradient(125deg, #7c3aed, #c084fc);
        color: #ffffff;
        box-shadow: 0 6px 16px rgba(124, 58, 237, 0.3);
    }
    .mbti-category-badge {
        background-color: rgba(30, 64, 175, 0.08);
        color: #1d4ed8;
        border-radius: 9999px;
        padding: 6px 12px;
        font-size: 0.82rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin: 2px;
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
                                <h2 class="mb-6 text-gray-900">تنظیمات آزمون MBTI</h2>
                                <p class="text-gray-500 mb-0">تیپ‌های شخصیتی سازمان را تعریف، ویرایش یا حذف کنید.</p>
                            </div>
                            <div class="d-flex gap-10 flex-wrap">
                                <a href="<?= UtilityHelper::baseUrl('organizations/tools/mbti-settings/create'); ?>" class="btn btn-main" title="افزودن تیپ شخصیتی">
                                    افزودن تیپ شخصیتی
                                    <span class="visually-hidden">افزودن تیپ شخصیتی</span>
                                </a>
                                <form action="<?= UtilityHelper::baseUrl('organizations/tools/mbti-settings/seed'); ?>" method="post" class="d-inline-flex" onsubmit="return confirm('افزودن خودکار تیپ‌ها ممکن است برخی دسته‌ها را در صورت تکمیل نشدن با ۵ ویژگی تنظیم کند. آیا ادامه می‌دهید؟');">
                                    <?= csrf_field(); ?>
                                    <button type="submit" class="btn btn-outline-main" title="افزودن تیپ‌های آماده">
                                        افزودن تیپ شخصیت آماده
                                    </button>
                                </form>
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
                            <table class="table align-middle mb-0 mbti-types-table js-data-table">
                                <thead class="bg-gray-100 text-gray-700">
                                    <tr>
                                        <th scope="col" class="no-sort no-search nowrap">عملیات</th>
                                        <th scope="col" class="nowrap">کد تیپ شخصیت</th>
                                        <th scope="col">عنوان تیپ شخصیت</th>
                                        <th scope="col" class="text-nowrap">دسته بندی</th>
                                        <th scope="col" class="text-nowrap">جدول MBTI</th>
                                        <th scope="col" class="text-nowrap">تعریف ویژگی</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($mbtiTypes)): ?>
                                        <?php foreach ($mbtiTypes as $type): ?>
                                            <?php
                                                $typeId = (int) ($type['id'] ?? 0);
                                                $typeCode = trim((string) ($type['type_code'] ?? ''));
                                                $typeTitle = trim((string) ($type['title'] ?? ''));
                                                $categories = is_array($type['categories'] ?? null) ? $type['categories'] : [];
                                                $categoriesHtml = '';
                                                foreach ($categories as $category) {
                                                    $categoryLabel = trim((string) $category);
                                                    if ($categoryLabel === '') {
                                                        continue;
                                                    }
                                                    $categoriesHtml .= '<span class="mbti-category-badge">' . htmlspecialchars($categoryLabel, ENT_QUOTES, 'UTF-8') . '</span>';
                                                }
                                                if ($categoriesHtml === '') {
                                                    $categoriesHtml = '<span class="text-gray-400">—</span>';
                                                }
                                            ?>
                                            <tr>
                                                <td class="nowrap" style="width: 10%;">
                                                    <div class="table-actions">
                                                        <a href="<?= UtilityHelper::baseUrl('organizations/tools/mbti-settings/edit?id=' . urlencode((string) $typeId)); ?>" class="btn btn-sm btn-outline-main" title="ویرایش">
                                                            <ion-icon name="create-outline"></ion-icon>
                                                            <span class="visually-hidden">ویرایش</span>
                                                        </a>
                                                        <form action="<?= UtilityHelper::baseUrl('organizations/tools/mbti-settings/delete'); ?>" method="post" onsubmit="return confirm('آیا از حذف این تیپ شخصیتی اطمینان دارید؟');" class="d-inline-flex">
                                                            <?= csrf_field(); ?>
                                                            <input type="hidden" name="id" value="<?= htmlspecialchars((string) $typeId, ENT_QUOTES, 'UTF-8'); ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف">
                                                                <ion-icon name="trash-outline"></ion-icon>
                                                                <span class="visually-hidden">حذف</span>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                                <td class="fw-semibold text-primary"><?= htmlspecialchars($typeCode, ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= htmlspecialchars($typeTitle, ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= $categoriesHtml; ?></td>
                                                <td><?= renderMbtiMatrix($typeCode); ?></td>
                                                <td class="text-center">
                                                    <a href="<?= UtilityHelper::baseUrl('organizations/tools/mbti-settings/features?id=' . urlencode((string) $typeId)); ?>" class="btn btn-outline-info rounded-pill px-20 py-8" title="تعریف ویژگی‌ها">
                                                        تعریف ویژگی‌ها
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            <?php if (empty($mbtiTypes)): ?>
                                <div class="text-center py-32 text-gray-500">
                                    تیپ شخصیتی‌ای برای نمایش وجود ندارد. برای ایجاد اولین تیپ از دکمه «افزودن تیپ شخصیتی» استفاده کنید.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include __DIR__ . '/../../../layouts/organization-footer.php'; ?>
    </div>
</div>
