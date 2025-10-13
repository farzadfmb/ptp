<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../../Helpers/autoload.php';
}

$title = $title ?? 'ویرایش تیپ شخصیتی MBTI';
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
$errorMessage = $errorMessage ?? flash('error');
$successMessage = $successMessage ?? flash('success');
$mbtiType = $mbtiType ?? [];

if (!isset($mbtiType['categories_list'])) {
    $categoriesRaw = $mbtiType['categories'] ?? '';
    $categoriesList = [];
    if (is_string($categoriesRaw) && trim($categoriesRaw) !== '') {
        $decoded = json_decode($categoriesRaw, true);
        if (is_array($decoded)) {
            $categoriesList = $decoded;
        }
    }
    $mbtiType['categories_list'] = $categoriesList;
}

$inline_styles .= <<<'CSS'
    body {
        background: #f5f7fb;
    }
    .mbti-type-form label,
    .mbti-type-form small {
        text-align: right;
        display: block;
    }
    .mbti-type-form .form-control,
    .mbti-type-form textarea {
        text-align: right;
        direction: rtl;
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
                            <div class="text-start flex-grow-1">
                                <h2 class="mb-6 text-gray-900">ویرایش تیپ شخصیتی MBTI</h2>
                                <p class="text-gray-500 mb-0">اطلاعات تیپ شخصیتی انتخاب شده را بروزرسانی کنید.</p>
                            </div>
                            <div class="d-flex gap-10 flex-wrap">
                                <a href="<?= UtilityHelper::baseUrl('organizations/tools/mbti-settings'); ?>" class="btn btn-outline-gray rounded-pill px-24 d-flex align-items-center gap-8" title="بازگشت">
                                    بازگشت به لیست
                                    <ion-icon name="arrow-undo-outline"></ion-icon>
                                    <span class="visually-hidden">بازگشت به تنظیمات MBTI</span>
                                </a>
                            </div>
                        </div>

                        <?php if (!empty($successMessage)): ?>
                            <div class="alert alert-success rounded-16 text-start d-flex align-items-center gap-12" role="alert">
                                <ion-icon name="checkmark-circle-outline"></ion-icon>
                                <span><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($errorMessage)): ?>
                            <div class="alert alert-danger rounded-16 text-start d-flex align-items-center gap-12" role="alert">
                                <ion-icon name="warning-outline"></ion-icon>
                                <span><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php
                            $formAction = UtilityHelper::baseUrl('organizations/tools/mbti-settings/update');
                            $submitLabel = 'به‌روزرسانی تیپ شخصیتی';
                            $cancelUrl = UtilityHelper::baseUrl('organizations/tools/mbti-settings');
                            $isEdit = true;

                            include __DIR__ . '/form-fields.php';
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <?php include __DIR__ . '/../../../layouts/organization-footer.php'; ?>
    </div>
</div>
