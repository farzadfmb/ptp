<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'سازنده گزارش پیشرفته';
$user = $user ?? null;
$organization = $organization ?? null;
$builderStateJson = $builderStateJson ?? htmlspecialchars(json_encode(['pages' => []], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
$componentLibraryJson = $componentLibraryJson ?? htmlspecialchars(json_encode([], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
$templateOptionsJson = $templateOptionsJson ?? htmlspecialchars(json_encode([], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
$builderDatasetsJson = $builderDatasetsJson ?? htmlspecialchars(json_encode(new stdClass(), JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
$successMessage = $successMessage ?? null;
$errorMessage = $errorMessage ?? null;

$additional_css = $additional_css ?? [];
$additional_js = $additional_js ?? [];
$inline_styles = $inline_styles ?? '';
$inline_scripts = $inline_scripts ?? '';

$projectRoot = dirname(__DIR__, 4);

$builderCssRelative = 'public/assets/css/certificate-builder.css';
$builderCssPath = $projectRoot . '/' . $builderCssRelative;
$builderCssVersion = is_file($builderCssPath) ? (string) filemtime($builderCssPath) : (string) time();

$builderJsRelative = 'public/assets/js/certificate-builder.js';
$builderJsPath = $projectRoot . '/' . $builderJsRelative;
$builderJsVersion = is_file($builderJsPath) ? (string) filemtime($builderJsPath) : (string) time();

$additional_css[] = $builderCssRelative . '?v=' . $builderCssVersion;
$additional_js[] = $builderJsRelative . '?v=' . $builderJsVersion;

include __DIR__ . '/../../layouts/organization-header.php';
include __DIR__ . '/../../layouts/organization-sidebar.php';
include __DIR__ . '/../../layouts/organization-navbar.php';
?>

<div class="page-content-wrapper certificate-builder-wrapper">
    <div class="page-content">
        <div class="certificate-builder-card card border-0 shadow-sm rounded-24 mb-0">
            <div class="card-body p-24">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-12 mb-16">
                    <div>
                        <h2 class="mb-6">سازنده گزارش پیشرفته</h2>
                        <p class="text-muted mb-0">صفحات گزارش را طراحی کنید، قالب تعیین کنید و بلوک‌های اطلاعاتی را با کشیدن و رها کردن بچینید.</p>
                    </div>
                    <div class="d-flex flex-wrap gap-10">
                        <a href="<?= UtilityHelper::baseUrl('organizations/reports/certificate-preview'); ?>" target="_blank" class="btn btn-outline-main d-inline-flex align-items-center gap-6">
                            <ion-icon name="eye-outline"></ion-icon>
                            پیش‌نمایش گزارش
                        </a>
                        <a href="<?= UtilityHelper::baseUrl('organizations/reports/certificate-settings'); ?>" class="btn btn-outline-secondary d-inline-flex align-items-center gap-6">
                            <ion-icon name="contract-outline"></ion-icon>
                            بازگشت به تنظیمات
                        </a>
                        <button form="certificate-builder-form" type="submit" class="btn btn-main d-inline-flex align-items-center gap-6">
                            <ion-icon name="save-outline"></ion-icon>
                            ذخیره تغییرات
                        </button>
                    </div>
                </div>

                <?php if (!empty($successMessage)): ?>
                    <div class="alert alert-success rounded-16" role="alert">
                        <?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errorMessage)): ?>
                    <div class="alert alert-danger rounded-16" role="alert">
                        <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <form id="certificate-builder-form" method="post" action="<?= UtilityHelper::baseUrl('organizations/reports/certificate-builder'); ?>">
                    <?= csrf_field(); ?>
                    <input type="hidden" name="builder_state" id="builder-state-input" value="<?= $builderStateJson; ?>">

                <div id="certificate-builder-root"
                    class="certificate-builder-grid"
                    data-initial-state="<?= $builderStateJson; ?>"
                    data-component-library="<?= $componentLibraryJson; ?>"
                data-template-options="<?= $templateOptionsJson; ?>"
                data-datasets="<?= $builderDatasetsJson; ?>"
                data-upload-endpoint="<?= UtilityHelper::baseUrl('organizations/reports/certificate-builder/upload-image'); ?>">

                        <aside class="builder-panel builder-panel-pages">
                            <div class="builder-panel-heading">
                                <h5 class="mb-0">صفحات</h5>
                                <p class="text-muted small mb-0">صفحات گواهی را مدیریت کنید</p>
                            </div>
                            <div class="builder-panel-body" data-role="page-list"></div>
                            <div class="builder-panel-divider"></div>
                            <div class="builder-panel-body builder-page-layout-controls" data-role="page-layout-controls"></div>
                            <div class="builder-panel-footer">
                                <button type="button" class="btn btn-outline-main w-100" data-action="add-page">
                                    <ion-icon name="add-circle-outline"></ion-icon>
                                    افزودن صفحه جدید
                                </button>
                            </div>
                        </aside>

                        <main class="builder-canvas" data-role="builder-canvas">
                            <div class="builder-canvas-header">
                                <div data-role="page-meta"></div>
                                <div data-role="page-template"></div>
                            </div>
                            <div class="builder-canvas-body" data-role="drop-zone">
                                <div class="builder-drop-placeholder" data-role="drop-placeholder">
                                    عناصر را از کتابخانه بکشید و در این بخش رها کنید.
                                </div>
                                <div class="builder-elements" data-role="canvas-elements"></div>
                            </div>
                        </main>

                        <aside class="builder-panel builder-panel-tools">
                            <div class="builder-panel-heading">
                                <h5 class="mb-0">کتابخانه ابزارها</h5>
                                <p class="text-muted small mb-0">برای افزودن عنصر، آن را بکشید و رها کنید</p>
                            </div>
                            <div class="builder-panel-body">
                                <div class="builder-component-list" data-role="component-list"></div>
                            </div>
                            <div class="builder-panel-footer text-muted small">
                                برای ویرایش تنظیمات هر آیتم، روی دکمه <span class="text-main fw-semibold">تنظیمات</span> در کارت آن کلیک کنید یا دوبار روی کارت بزنید.
                            </div>
                        </aside>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="builder-element-settings-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-20">
            <div class="modal-header">
                <h5 class="modal-title" data-role="element-settings-modal-title">تنظیمات آیتم</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
            </div>
            <div class="modal-body" data-role="element-settings-modal-body"></div>
            <div class="modal-footer gap-2">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">بستن</button>
                <button type="button" class="btn btn-main" data-role="element-settings-apply">ذخیره و بستن</button>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/organization-footer.php'; ?>
