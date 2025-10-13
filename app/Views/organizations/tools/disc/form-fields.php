<?php
$discType = $discType ?? [];
$validationErrors = $validationErrors ?? [];
$formAction = $formAction ?? '#';
$submitLabel = $submitLabel ?? 'ذخیره';
$cancelUrl = $cancelUrl ?? UtilityHelper::baseUrl('organizations/tools/disc-settings');
$isEdit = $isEdit ?? false;
$scopeMeta = $scopeMeta ?? [];
$discTypeOptions = $discTypeOptions ?? [];

$currentScope = $discType['scope'] ?? ($discType['scope_key'] ?? 'primary');
$oldScope = old('scope', (string) ($discType['scope'] ?? $currentScope ?? 'primary'));
$isSecondaryScope = $oldScope === 'secondary';

$oldDiscType = old('disc_type', (string) ($discType['disc_type_code'] ?? ''));
$oldShortDescription = old('short_description', (string) ($discType['short_description'] ?? ''));
$oldGeneralTendencies = old('general_tendencies', (string) ($discType['general_tendencies'] ?? ''));
$oldWorkPreferences = old('work_preferences', (string) ($discType['work_preferences'] ?? ''));
$oldEffectivenessRequirements = old('effectiveness_requirements', (string) ($discType['effectiveness_requirements'] ?? ''));
$oldCompanionRequirements = old('companion_requirements', (string) ($discType['companion_requirements'] ?? ''));
$oldBehaviorOverview = old('behavior_overview', (string) ($discType['behavior_overview'] ?? ''));
$oldSecondaryDescription = old('secondary_description', (string) ($discType['secondary_description'] ?? $oldBehaviorOverview ?? ''));
?>

<form action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8'); ?>" method="post" class="disc-type-form text-start">
    <?= csrf_field(); ?>
    <?php if ($isEdit && !empty($discType['id'])): ?>
        <input type="hidden" name="id" value="<?= htmlspecialchars((string) $discType['id'], ENT_QUOTES, 'UTF-8'); ?>">
    <?php endif; ?>
    <input type="hidden" name="scope" value="<?= htmlspecialchars($oldScope, ENT_QUOTES, 'UTF-8'); ?>">

    <div class="row g-16">
        <div class="col-md-6">
            <label class="form-label fw-semibold">نوع DISC <span class="text-danger">*</span></label>
            <select name="disc_type" class="form-select" required>
                <option value="" disabled <?= $oldDiscType === '' ? 'selected' : ''; ?>>یک گزینه را انتخاب کنید</option>
                <?php foreach ($discTypeOptions as $option): ?>
                    <?php $code = (string) ($option['code'] ?? ''); ?>
                    <option value="<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8'); ?>" <?= $oldDiscType === $code ? 'selected' : ''; ?>>
                        <?= htmlspecialchars((string) ($option['label'] ?? $code), ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($validationErrors['disc_type'])): ?>
                <small class="text-danger mt-6"><?= htmlspecialchars($validationErrors['disc_type'], ENT_QUOTES, 'UTF-8'); ?></small>
            <?php endif; ?>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">بخش ثبت تیپ</label>
            <div class="form-control bg-light" readonly>
                <?= htmlspecialchars((string) ($scopeMeta[$oldScope]['label'] ?? ($scopeMeta['primary']['label'] ?? 'تیپ شخصیتی')), ENT_QUOTES, 'UTF-8'); ?>
            </div>
        </div>
        <?php if ($isSecondaryScope): ?>
            <div class="col-12">
                <div class="alert alert-info rounded-16 d-flex align-items-start gap-8" role="alert">
                    <ion-icon name="information-circle-outline" class="mt-2"></ion-icon>
                    <div>
                        <div class="fw-semibold mb-4">الگوی ثانویه در یک نگاه</div>
                        <p class="mb-0 small text-muted">شرح زیر به عنوان توضیح اصلی ثبت می‌شود و به‌صورت خودکار در بخش‌های دیگر این تیپ قرار می‌گیرد.</p>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">توضیحات الگوی ثانویه <span class="text-danger">*</span></label>
                <textarea name="secondary_description" class="form-control" rows="6" placeholder="توضیحاتی درباره نحوه بروز رفتار ثانویه" required><?= htmlspecialchars($oldSecondaryDescription, ENT_QUOTES, 'UTF-8'); ?></textarea>
                <?php if (!empty($validationErrors['secondary_description'])): ?>
                    <small class="text-danger mt-6"><?= htmlspecialchars($validationErrors['secondary_description'], ENT_QUOTES, 'UTF-8'); ?></small>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="col-12">
                <label class="form-label fw-semibold">توضیح کوتاه در مورد تیپ شخصیتی <span class="text-danger">*</span></label>
                <textarea name="short_description" class="form-control" rows="3" placeholder="جمع‌بندی کوتاه از این تیپ" required><?= htmlspecialchars($oldShortDescription, ENT_QUOTES, 'UTF-8'); ?></textarea>
                <?php if (!empty($validationErrors['short_description'])): ?>
                    <small class="text-danger mt-6"><?= htmlspecialchars($validationErrors['short_description'], ENT_QUOTES, 'UTF-8'); ?></small>
                <?php endif; ?>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">تمایلات و گرایش‌های عمومی <span class="text-danger">*</span></label>
                <textarea name="general_tendencies" class="form-control" rows="4" placeholder="رفتارها و گرایش‌های غالب این تیپ" required><?= htmlspecialchars($oldGeneralTendencies, ENT_QUOTES, 'UTF-8'); ?></textarea>
                <?php if (!empty($validationErrors['general_tendencies'])): ?>
                    <small class="text-danger mt-6"><?= htmlspecialchars($validationErrors['general_tendencies'], ENT_QUOTES, 'UTF-8'); ?></small>
                <?php endif; ?>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">ترجیحات ایشان در محیط کار <span class="text-danger">*</span></label>
                <textarea name="work_preferences" class="form-control" rows="4" placeholder="انتظارات و سبک عملکرد در محیط کاری" required><?= htmlspecialchars($oldWorkPreferences, ENT_QUOTES, 'UTF-8'); ?></textarea>
                <?php if (!empty($validationErrors['work_preferences'])): ?>
                    <small class="text-danger mt-6"><?= htmlspecialchars($validationErrors['work_preferences'], ENT_QUOTES, 'UTF-8'); ?></small>
                <?php endif; ?>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">ایشان برای کارآمدتر بودن نیاز دارد که <span class="text-danger">*</span></label>
                <textarea name="effectiveness_requirements" class="form-control" rows="4" placeholder="شرایط و نیازهای لازم برای عملکرد مطلوب" required><?= htmlspecialchars($oldEffectivenessRequirements, ENT_QUOTES, 'UTF-8'); ?></textarea>
                <?php if (!empty($validationErrors['effectiveness_requirements'])): ?>
                    <small class="text-danger mt-6"><?= htmlspecialchars($validationErrors['effectiveness_requirements'], ENT_QUOTES, 'UTF-8'); ?></small>
                <?php endif; ?>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">ایشان در کنار خود به افرادی نیاز دارد که <span class="text-danger">*</span></label>
                <textarea name="companion_requirements" class="form-control" rows="4" placeholder="هم‌تیمی‌ها یا نقش‌های مکمل مورد نیاز" required><?= htmlspecialchars($oldCompanionRequirements, ENT_QUOTES, 'UTF-8'); ?></textarea>
                <?php if (!empty($validationErrors['companion_requirements'])): ?>
                    <small class="text-danger mt-6"><?= htmlspecialchars($validationErrors['companion_requirements'], ENT_QUOTES, 'UTF-8'); ?></small>
                <?php endif; ?>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">توضیحات کلی الگوی رفتاری <span class="text-danger">*</span></label>
                <textarea name="behavior_overview" class="form-control" rows="6" placeholder="شرح کامل رفتارها، نقاط قوت و زمینه‌های توجه" required><?= htmlspecialchars($oldBehaviorOverview, ENT_QUOTES, 'UTF-8'); ?></textarea>
                <?php if (!empty($validationErrors['behavior_overview'])): ?>
                    <small class="text-danger mt-6"><?= htmlspecialchars($validationErrors['behavior_overview'], ENT_QUOTES, 'UTF-8'); ?></small>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="d-flex justify-content-end gap-12 mt-28">
        <button type="submit" class="btn btn-main rounded-pill px-32 d-flex align-items-center gap-8">
            <ion-icon name="save-outline"></ion-icon>
            <span><?= htmlspecialchars($submitLabel, ENT_QUOTES, 'UTF-8'); ?></span>
        </button>
        <a href="<?= htmlspecialchars($cancelUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-gray rounded-pill px-28">انصراف</a>
    </div>
</form>
