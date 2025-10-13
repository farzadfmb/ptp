<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../../Helpers/autoload.php';
}

$title = $title ?? 'تعریف ویژگی‌های تیپ شخصیتی MBTI';
$user = (class_exists('AuthHelper') && AuthHelper::getUser()) ? AuthHelper::getUser() : [
    'name' => 'کاربر سازمان',
    'email' => 'organization@example.com',
];
$additional_css = $additional_css ?? [];
$additional_js = $additional_js ?? [];
$inline_styles = $inline_styles ?? '';
$inline_scripts = $inline_scripts ?? '';

$mbtiType = $mbtiType ?? [];
$featureCategories = $featureCategories ?? [];
$featuresByCategory = $featuresByCategory ?? [];
$activeTabKey = isset($activeTabKey) ? (string) $activeTabKey : '';
$successMessage = $successMessage ?? flash('success');
$errorMessage = $errorMessage ?? flash('error');

$categoryKeys = array_keys($featureCategories);
$defaultCategoryKey = isset($categoryKeys[0]) ? (string) $categoryKeys[0] : '';
if ($activeTabKey === '' || !array_key_exists($activeTabKey, $featureCategories)) {
    $activeTabKey = $defaultCategoryKey;
}

$mbtiTypeId = (int) ($mbtiType['id'] ?? 0);
$typeCode = trim((string) ($mbtiType['type_code'] ?? ''));
$typeTitle = trim((string) ($mbtiType['title'] ?? ''));
$categoriesList = $mbtiType['categories_list'] ?? [];

$inline_styles .= <<<'CSS'
    body {
        background: #f5f7fb;
    }
    .card-rounded {
        border-radius: 24px;
        border: 1px solid #e4e9f2;
        background: #ffffff;
    }
    .mbti-summary-card {
        background: linear-gradient(140deg, #f1f5ff, #fff);
        border-radius: 20px;
        padding: 20px;
    }
    .mbti-summary-card h3 {
        font-size: 1.75rem;
        font-weight: 800;
        letter-spacing: 2px;
    }
    .mbti-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 9999px;
        background: rgba(124, 58, 237, 0.08);
        padding: 6px 14px;
        font-size: 0.82rem;
        color: #6b21a8;
        margin: 2px;
    }
    .feature-tabs .nav-link {
        border-radius: 16px;
        font-weight: 600;
        color: #475467;
        border: 1px solid transparent;
        transition: all .2s ease;
    }
    .feature-tabs .nav-link.active {
        color: #1d4ed8;
        border-color: rgba(29, 78, 216, 0.2);
        box-shadow: 0 10px 30px rgba(37, 99, 235, 0.12);
        background: #ffffff;
    }
    .feature-action-bar {
        background: #f8fafc;
        border-radius: 16px;
        padding: 16px;
        border: 1px dashed rgba(148, 163, 184, 0.5);
    }
    .feature-table tbody td {
        vertical-align: middle;
    }
    .feature-table .table-actions {
        display: inline-flex;
        gap: 6px;
    }
    .feature-empty-state {
        border-radius: 16px;
        border: 1px dashed rgba(148, 163, 184, 0.5);
        padding: 24px;
        color: #64748b;
        background: rgba(241, 245, 249, 0.6);
    }
CSS;

$inline_scripts .= <<<'JS'
    document.addEventListener('DOMContentLoaded', function () {
        var featureTabList = document.querySelectorAll('#mbtiFeatureTabs button[data-bs-toggle="tab"]');
        featureTabList.forEach(function (triggerEl) {
            triggerEl.addEventListener('shown.bs.tab', function (event) {
                var categoryKey = event.target.getAttribute('data-category');
                if (!categoryKey) {
                    return;
                }

                var url = new URL(window.location.href);
                url.searchParams.set('tab', categoryKey);
                window.history.replaceState({}, '', url.toString());
            });
        });
    });
JS;

include __DIR__ . '/../../../layouts/organization-header.php';
include __DIR__ . '/../../../layouts/organization-sidebar.php';

$navbarUser = $user;
?>

<?php include __DIR__ . '/../../../layouts/organization-navbar.php'; ?>

<div class="page-content-wrapper">
    <div class="page-content">
        <div class="row g-4">
            <div class="col-12">
                <div class="card card-rounded shadow-sm border-0 p-24 mb-24">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-16">
                        <div class="mbti-summary-card flex-grow-1">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-12 mb-12">
                                <div>
                                    <span class="badge bg-indigo-100 text-indigo-700 rounded-pill px-12 py-6">تیپ شخصیتی</span>
                                    <h3 class="mt-12 mb-8 text-indigo-900">
                                        <?= htmlspecialchars($typeCode !== '' ? $typeCode : '---', ENT_QUOTES, 'UTF-8'); ?>
                                    </h3>
                                </div>
                                <div class="text-end">
                                    <p class="text-gray-600 mb-4">عنوان تیپ</p>
                                    <p class="fs-5 fw-semibold text-gray-800 mb-0">
                                        <?= htmlspecialchars($typeTitle !== '' ? $typeTitle : 'بدون عنوان', ENT_QUOTES, 'UTF-8'); ?>
                                    </p>
                                </div>
                            </div>
                            <div class="d-flex flex-wrap align-items-center gap-8">
                                <?php if (!empty($categoriesList)): ?>
                                    <?php foreach ($categoriesList as $category): ?>
                                        <?php $categoryLabel = trim((string) $category); ?>
                                        <?php if ($categoryLabel === '') { continue; } ?>
                                        <span class="mbti-tag">
                                            <ion-icon name="pricetag-outline"></ion-icon>
                                            <?= htmlspecialchars($categoryLabel, ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="text-gray-400">دسته‌بندی ثبت نشده است.</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="d-flex gap-10 flex-wrap">
                            <a href="<?= UtilityHelper::baseUrl('organizations/tools/mbti-settings'); ?>" class="btn btn-outline-gray rounded-pill px-24 d-flex align-items-center gap-8" title="بازگشت به فهرست">
                                بازگشت به فهرست
                                <ion-icon name="arrow-undo-outline"></ion-icon>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <?php if (!empty($successMessage)): ?>
                    <div class="alert alert-success rounded-20 d-flex align-items-center gap-12" role="alert">
                        <ion-icon name="checkmark-circle-outline"></ion-icon>
                        <span><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errorMessage)): ?>
                    <div class="alert alert-danger rounded-20 d-flex align-items-center gap-12" role="alert">
                        <ion-icon name="warning-outline"></ion-icon>
                        <span><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-12">
                <div class="card card-rounded shadow-sm border-0">
                    <div class="card-body p-24">
                        <div class="feature-tabs">
                            <ul class="nav nav-pills mb-24 gap-12" id="mbtiFeatureTabs" role="tablist">
                                <?php foreach ($featureCategories as $categoryKey => $meta): ?>
                                    <?php
                                        $categoryLabel = (string) ($meta['label'] ?? $categoryKey);
                                        $categoryDescription = (string) ($meta['description'] ?? '');
                                        $isActive = $categoryKey === $activeTabKey;
                                        $tabId = 'tab-' . preg_replace('/[^a-z0-9_\-]/i', '-', $categoryKey);
                                    ?>
                                    <li class="nav-item" role="presentation">
                                        <button
                                            class="nav-link <?= $isActive ? 'active' : ''; ?>"
                                            id="<?= htmlspecialchars($tabId, ENT_QUOTES, 'UTF-8'); ?>-tab"
                                            data-bs-toggle="tab"
                                            data-bs-target="#<?= htmlspecialchars($tabId, ENT_QUOTES, 'UTF-8'); ?>"
                                            type="button"
                                            role="tab"
                                            aria-controls="<?= htmlspecialchars($tabId, ENT_QUOTES, 'UTF-8'); ?>"
                                            aria-selected="<?= $isActive ? 'true' : 'false'; ?>"
                                            data-category="<?= htmlspecialchars($categoryKey, ENT_QUOTES, 'UTF-8'); ?>"
                                        >
                                            <?= htmlspecialchars($categoryLabel, ENT_QUOTES, 'UTF-8'); ?>
                                        </button>
                                    </li>
                                <?php endforeach; ?>
                            </ul>

                            <div class="tab-content" id="mbtiFeatureTabsContent">
                                <?php foreach ($featureCategories as $categoryKey => $meta): ?>
                                    <?php
                                        $categoryLabel = (string) ($meta['label'] ?? $categoryKey);
                                        $categoryDescription = (string) ($meta['description'] ?? '');
                                        $tabId = 'tab-' . preg_replace('/[^a-z0-9_\-]/i', '-', $categoryKey);
                                        $isActive = $categoryKey === $activeTabKey;
                                        $features = $featuresByCategory[$categoryKey] ?? [];
                                        $createUrl = UtilityHelper::baseUrl('organizations/tools/mbti-settings/features/create?id=' . urlencode((string) $mbtiTypeId) . '&category=' . urlencode($categoryKey));
                                    ?>
                                    <div
                                        class="tab-pane fade <?= $isActive ? 'show active' : ''; ?>"
                                        id="<?= htmlspecialchars($tabId, ENT_QUOTES, 'UTF-8'); ?>"
                                        role="tabpanel"
                                        aria-labelledby="<?= htmlspecialchars($tabId, ENT_QUOTES, 'UTF-8'); ?>-tab"
                                    >
                                        <div class="feature-action-bar d-flex flex-wrap align-items-center justify-content-between gap-12 mb-16">
                                            <div>
                                                <h5 class="mb-6 text-gray-900 fw-semibold"><?= htmlspecialchars($categoryLabel, ENT_QUOTES, 'UTF-8'); ?></h5>
                                                <?php if ($categoryDescription !== ''): ?>
                                                    <p class="text-gray-500 mb-0 small"><?= htmlspecialchars($categoryDescription, ENT_QUOTES, 'UTF-8'); ?></p>
                                                <?php endif; ?>
                                            </div>
                                            <a href="<?= $createUrl; ?>" class="btn btn-main rounded-pill px-20 d-flex align-items-center gap-8">
                                                <ion-icon name="add-outline"></ion-icon>
                                                افزودن ویژگی
                                            </a>
                                        </div>

                                        <?php if (!empty($features)): ?>
                                            <div class="table-responsive">
                                                <table class="table feature-table align-middle">
                                                    <thead class="bg-gray-100 text-gray-700">
                                                        <tr>
                                                            <th scope="col" class="text-nowrap">عملیات</th>
                                                            <th scope="col" class="text-nowrap">ویژگی</th>
                                                            <th scope="col" class="text-nowrap">ترتیب نمایش</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($features as $feature): ?>
                                                            <?php
                                                                $featureId = (int) ($feature['id'] ?? 0);
                                                                $featureText = trim((string) ($feature['feature_text'] ?? ''));
                                                                $sortOrderValue = isset($feature['sort_order']) ? (int) $feature['sort_order'] : 0;
                                                                $editUrl = UtilityHelper::baseUrl('organizations/tools/mbti-settings/features/edit?id=' . urlencode((string) $featureId));
                                                            ?>
                                                            <tr>
                                                                <td class="text-nowrap" style="width: 140px;">
                                                                    <div class="table-actions">
                                                                        <a href="<?= $editUrl; ?>" class="btn btn-sm btn-outline-primary rounded-pill px-12" title="ویرایش ویژگی">
                                                                            <ion-icon name="create-outline"></ion-icon>
                                                                        </a>
                                                                        <form
                                                                            action="<?= UtilityHelper::baseUrl('organizations/tools/mbti-settings/features/delete'); ?>"
                                                                            method="post"
                                                                            class="d-inline-flex"
                                                                            onsubmit="return confirm('آیا از حذف این ویژگی مطمئن هستید؟');"
                                                                        >
                                                                            <?= csrf_field(); ?>
                                                                            <input type="hidden" name="id" value="<?= htmlspecialchars((string) $featureId, ENT_QUOTES, 'UTF-8'); ?>">
                                                                            <input type="hidden" name="mbti_type_id" value="<?= htmlspecialchars((string) $mbtiTypeId, ENT_QUOTES, 'UTF-8'); ?>">
                                                                            <input type="hidden" name="category" value="<?= htmlspecialchars($categoryKey, ENT_QUOTES, 'UTF-8'); ?>">
                                                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-12" title="حذف ویژگی">
                                                                                <ion-icon name="trash-outline"></ion-icon>
                                                                            </button>
                                                                        </form>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <?= nl2br(htmlspecialchars($featureText !== '' ? $featureText : '—', ENT_QUOTES, 'UTF-8')); ?>
                                                                </td>
                                                                <td class="text-center text-gray-600 fw-semibold">
                                                                    <?= htmlspecialchars(UtilityHelper::englishToPersian((string) $sortOrderValue), ENT_QUOTES, 'UTF-8'); ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php else: ?>
                                            <div class="feature-empty-state text-center">
                                                <ion-icon name="sparkles-outline" class="fs-3 mb-8"></ion-icon>
                                                <p class="mb-0">هیچ ویژگی برای این دسته ثبت نشده است. برای ایجاد اولین ویژگی روی «افزودن ویژگی» کلیک کنید.</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include __DIR__ . '/../../../layouts/organization-footer.php'; ?>
    </div>
</div>
