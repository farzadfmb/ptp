<?php
$title = 'ایجاد سازمان جدید';
$user = (class_exists('AuthHelper') && AuthHelper::getUser()) ? AuthHelper::getUser() : ['name' => 'مدیر سیستم', 'email' => 'admin@example.com'];
$additional_js = [];

include __DIR__ . '/../../layouts/admin-header.php';
include __DIR__ . '/../../layouts/admin-sidebar.php';

AuthHelper::startSession();
$oldInput = $_SESSION['old_input'] ?? [];

$validationErrors = $validationErrors ?? [];
$successMessage = $successMessage ?? null;
$errorMessage = $errorMessage ?? null;
?>

<div class="dashboard-main-wrapper">
    <?php include __DIR__ . '/../../layouts/admin-navbar.php'; ?>

    <div class="dashboard-body">
        <div class="row gy-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body pb-0 text-end">
                        <div class="mb-16">
                            <h3 class="mb-4">ایجاد سازمان جدید</h3>
                            <p class="text-gray-500 mb-0">اطلاعات سازمان مورد نظر را تکمیل و ثبت کنید.</p>
                        </div>
                        <?php if (!empty($successMessage)): ?>
                            <div class="alert alert-success rounded-12 text-end" role="alert">
                                <i class="fas fa-check-circle ms-6"></i>
                                <?= $successMessage; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($errorMessage)): ?>
                            <div class="alert alert-danger rounded-12 text-end" role="alert">
                                <i class="fas fa-exclamation-triangle ms-6"></i>
                                <?= $errorMessage; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($validationErrors) && empty($errorMessage)): ?>
                            <div class="alert alert-warning rounded-12 text-end" role="alert">
                                <i class="fas fa-info-circle ms-6"></i>
                                لطفاً خطاهای مشخص شده در فرم را بررسی کنید.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <form action="<?= UtilityHelper::baseUrl('supperadmin/organizations'); ?>" method="post" enctype="multipart/form-data" class="card">
                    <div class="card-body text-end">
                        <?= csrf_field(); ?>

                        <div class="mb-20">
                            <h5 class="mb-8">مشخصات سازمان</h5>
                            <p class="text-13 text-gray-500 mb-0">اطلاعات پایه سازمان را وارد کنید.</p>
                        </div>

                        <div class="row g-16">
                            <div class="col-xxl-3 col-sm-6">
                                <label class="form-label fw-semibold text-end d-block">کد <span class="text-danger">*</span></label>
                                <input type="text" name="code" class="form-control" value="<?= old('code'); ?>" placeholder="مثال: ORG-1001" required>
                                <?php if (!empty($validationErrors['code'])): ?>
                                    <small class="text-danger d-block mt-6 text-end"><?= $validationErrors['code']; ?></small>
                                <?php endif; ?>
                            </div>

                            <div class="col-xxl-3 col-sm-6">
                                <label class="form-label fw-semibold text-end d-block">نام سازمان <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="<?= old('name'); ?>" placeholder="مثال: سازمان آلفا" required>
                                <?php if (!empty($validationErrors['name'])): ?>
                                    <small class="text-danger d-block mt-6 text-end"><?= $validationErrors['name']; ?></small>
                                <?php endif; ?>
                            </div>

                            <div class="col-xxl-3 col-sm-6">
                                <label class="form-label fw-semibold text-end d-block">نام لاتین <span class="text-danger">*</span></label>
                                <input type="text" name="latin_name" class="form-control" value="<?= old('latin_name'); ?>" placeholder="مثال: Alpha Organization" required>
                                <?php if (!empty($validationErrors['latin_name'])): ?>
                                    <small class="text-danger d-block mt-6 text-end"><?= $validationErrors['latin_name']; ?></small>
                                <?php endif; ?>
                            </div>

                            <div class="col-xxl-3 col-sm-6">
                                <label class="form-label fw-semibold text-end d-block">ساب‌دومین <span class="text-gray-400">(اختیاری)</span></label>
                                <div class="input-group">
                                    <input type="text" name="subdomain" class="form-control" value="<?= old('subdomain'); ?>" placeholder="مثال: alpha" >
                                    <span class="input-group-text">.<?= parse_url(UtilityHelper::baseUrl(), PHP_URL_HOST); ?></span>
                                </div>
                                <?php if (!empty($validationErrors['subdomain'])): ?>
                                    <small class="text-danger d-block mt-6 text-end"><?= $validationErrors['subdomain']; ?></small>
                                <?php endif; ?>
                            </div>

                            <div class="col-xxl-3 col-sm-6">
                                <label class="form-label fw-semibold text-end d-block">کد سازمان <span class="text-danger">*</span></label>
                                <input type="text" name="organization_code" class="form-control" value="<?= old('organization_code'); ?>" placeholder="مثال: 120045" required>
                                <?php if (!empty($validationErrors['organization_code'])): ?>
                                    <small class="text-danger d-block mt-6 text-end"><?= $validationErrors['organization_code']; ?></small>
                                <?php endif; ?>
                            </div>

                            <div class="col-xxl-3 col-sm-6">
                                <label class="form-label fw-semibold text-end d-block">نام کاربری مدیر <span class="text-danger">*</span></label>
                                <input type="text" name="username" class="form-control" value="<?= old('username'); ?>" placeholder="مثال: alpha.admin" >
                                <?php if (!empty($validationErrors['username'])): ?>
                                    <small class="text-danger d-block mt-6 text-end"><?= $validationErrors['username']; ?></small>
                                <?php endif; ?>
                            </div>

                            <div class="col-xxl-3 col-sm-6">
                                <label class="form-label fw-semibold text-end d-block">رمز عبور <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control" placeholder="حداقل ۸ کاراکتر" required>
                                <?php if (!empty($validationErrors['password'])): ?>
                                    <small class="text-danger d-block mt-6 text-end"><?= $validationErrors['password']; ?></small>
                                <?php endif; ?>
                            </div>

                            <div class="col-xxl-3 col-sm-6">
                                <label class="form-label fw-semibold text-end d-block">واحد ارزیابی‌شونده <span class="text-gray-400">(اختیاری)</span></label>
                                <input type="text" name="evaluation_unit" class="form-control" value="<?= old('evaluation_unit'); ?>" placeholder="مثال: شعبه مرکزی" >
                                <?php if (!empty($validationErrors['evaluation_unit'])): ?>
                                    <small class="text-danger d-block mt-6 text-end"><?= $validationErrors['evaluation_unit']; ?></small>
                                <?php endif; ?>
                            </div>

                            <div class="col-xxl-3 col-sm-6">
                                <label class="form-label fw-semibold text-end d-block">مبلغ اعتبار سازمان <span class="text-gray-400">(اختیاری)</span></label>
                                <input type="text" name="credit_amount" class="form-control" value="<?= old('credit_amount'); ?>" placeholder="مثال: 2500000">
                                <small class="text-12 text-gray-500 d-block mt-6 text-end">مبلغ را به ریال وارد کنید. امکان استفاده از اعداد فارسی و کاما وجود دارد.</small>
                                <?php if (!empty($validationErrors['credit_amount'])): ?>
                                    <small class="text-danger d-block mt-6 text-end"><?= $validationErrors['credit_amount']; ?></small>
                                <?php endif; ?>
                            </div>

                            <div class="col-xxl-3 col-sm-6">
                                <label class="form-label fw-semibold text-end d-block">مبلغ آزمون به ازای هر نفر <span class="text-gray-400">(اختیاری)</span></label>
                                <input type="text" name="exam_fee_per_participant" class="form-control" value="<?= old('exam_fee_per_participant'); ?>" placeholder="مثال: 150000">
                                <small class="text-12 text-gray-500 d-block mt-6 text-end">مبلغ را به ریال وارد کنید.</small>
                                <?php if (!empty($validationErrors['exam_fee_per_participant'])): ?>
                                    <small class="text-danger d-block mt-6 text-end"><?= $validationErrors['exam_fee_per_participant']; ?></small>
                                <?php endif; ?>
                            </div>
                        </div>

                        <hr class="my-24">

                        <div class="row g-16">
                            <div class="col-lg-4">
                                <div class="border border-gray-100 rounded-16 p-16 h-100 text-end">
                                    <h6 class="mb-12">تنظیمات عملکرد</h6>
                                    <div class="form-check form-switch mb-12 text-end">
                                        <input class="form-check-input" type="checkbox" role="switch" id="closeWashup" name="close_washup_after_confirmation" value="1" <?= old('close_washup_after_confirmation') === '1' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="closeWashup">بستن washup پس از تأیید نهایی</label>
                                    </div>
                                    <div class="form-check form-switch mb-12 text-end">
                                        <input class="form-check-input" type="checkbox" role="switch" id="enableRegion" name="enable_region_area" value="1" <?= old('enable_region_area') === '1' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="enableRegion">فعال‌سازی منطقه و محله</label>
                                    </div>
                                    <div class="form-check form-switch mb-12 text-end">
                                        <input class="form-check-input" type="checkbox" role="switch" id="allowNotes" name="allow_competency_notes" value="1" <?= old('allow_competency_notes') === '1' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="allowNotes">امکان درج توضیحات شایستگی فرد</label>
                                    </div>
                                    <div class="form-check form-switch text-end">
                                        <input class="form-check-input" type="checkbox" role="switch" id="deactivateOrg" name="deactivate_organization" value="1" <?= old('deactivate_organization') === '1' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="deactivateOrg">غیرفعال کردن سازمان پس از ایجاد</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-8">
                                <div class="border border-gray-100 rounded-16 p-16 h-100 text-end">
                                    <h6 class="mb-12">محدوده‌های امتیازدهی</h6>
                                    <div class="row g-16">
                                        <div class="col-sm-6">
                                            <label class="form-label text-end d-block">محدوده امتیاز ۱</label>
                                            <input type="text" name="score_range_1" class="form-control" value="<?= old('score_range_1'); ?>" placeholder="مثال: 0-20">
                                            <?php if (!empty($validationErrors['score_range_1'])): ?>
                                                <small class="text-danger d-block mt-6 text-end"><?= $validationErrors['score_range_1']; ?></small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-sm-6">
                                            <label class="form-label text-end d-block">محدوده امتیاز ۲</label>
                                            <input type="text" name="score_range_2" class="form-control" value="<?= old('score_range_2'); ?>" placeholder="مثال: 21-40">
                                            <?php if (!empty($validationErrors['score_range_2'])): ?>
                                                <small class="text-danger d-block mt-6 text-end"><?= $validationErrors['score_range_2']; ?></small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-sm-6">
                                            <label class="form-label text-end d-block">محدوده امتیاز ۳</label>
                                            <input type="text" name="score_range_3" class="form-control" value="<?= old('score_range_3'); ?>" placeholder="مثال: 41-60">
                                            <?php if (!empty($validationErrors['score_range_3'])): ?>
                                                <small class="text-danger d-block mt-6 text-end"><?= $validationErrors['score_range_3']; ?></small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-sm-6">
                                            <label class="form-label text-end d-block">محدوده امتیاز ۴</label>
                                            <input type="text" name="score_range_4" class="form-control" value="<?= old('score_range_4'); ?>" placeholder="مثال: 61-80">
                                            <?php if (!empty($validationErrors['score_range_4'])): ?>
                                                <small class="text-danger d-block mt-6 text-end"><?= $validationErrors['score_range_4']; ?></small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-sm-6">
                                            <label class="form-label text-end d-block">محدوده امتیاز ۵</label>
                                            <input type="text" name="score_range_5" class="form-control" value="<?= old('score_range_5'); ?>" placeholder="مثال: 81-100">
                                            <?php if (!empty($validationErrors['score_range_5'])): ?>
                                                <small class="text-danger d-block mt-6 text-end"><?= $validationErrors['score_range_5']; ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-24">

                        <div class="row g-16">
                            <div class="col-lg-6">
                                <label class="form-label fw-semibold text-end d-block">لوگوی سازمان</label>
                                <input type="file" name="logo" class="form-control" accept="image/*">
                                <small class="text-12 text-gray-500 d-block mt-6 text-end">فرمت‌های مجاز: JPG, PNG, GIF, WEBP</small>
                                <?php if (!empty($validationErrors['logo'])): ?>
                                    <small class="text-danger d-block mt-6 text-end"><?= $validationErrors['logo']; ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="col-lg-6">
                                <label class="form-label fw-semibold text-end d-block">لوگوی صفحه اول گزارش</label>
                                <input type="file" name="report_cover_logo" class="form-control" accept="image/*">
                                <small class="text-12 text-gray-500 d-block mt-6 text-end">تصویر مخصوص کاور گزارش‌ها</small>
                                <?php if (!empty($validationErrors['report_cover_logo'])): ?>
                                    <small class="text-danger d-block mt-6 text-end"><?= $validationErrors['report_cover_logo']; ?></small>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-32">
                            <button type="submit" class="btn btn-main rounded-pill px-32">
                                <i class="fas fa-save ms-6"></i>
                                ثبت سازمان
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../../layouts/admin-footer.php'; ?>
</div>

<?php unset($_SESSION['old_input']); ?>
