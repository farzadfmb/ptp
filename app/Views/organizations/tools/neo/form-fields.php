<?php
if (!isset($formAction)) {
    $formAction = UtilityHelper::baseUrl('organizations/tools/neo-settings');
}

$neoTrait = is_array($neoTrait ?? null) ? $neoTrait : [];
$traitOptions = is_array($traitOptions ?? null) ? $traitOptions : [];
$validationErrors = is_array($validationErrors ?? null) ? $validationErrors : [];
$submitLabel = $submitLabel ?? 'ثبت اطلاعات';
$isEdit = (bool) ($isEdit ?? false);
$selectedTraitCode = (string) ($neoTrait['trait_code'] ?? '');
$selectedTraitLabel = $traitOptions[$selectedTraitCode] ?? ($selectedTraitCode !== '' ? $selectedTraitCode : '');

$fields = [
    'short_description' => 'توضیح کوتاه',
    'key_drivers' => 'محرک‌های کلیدی',
    'communication_style' => 'سبک ارتباط',
    'development_focus' => 'تمرکز رشد',
    'stress_signals' => 'نشانه‌های استرس',
];
?>

<form action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8'); ?>" method="post" class="neo-type-form" novalidate>
    <?= csrf_field(); ?>

    <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= htmlspecialchars((string) ($neoTrait['id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-12 col-md-6">
            <label for="trait_code" class="form-label fw-semibold">تیپ شخصیتی *</label>
            <select name="trait_code" id="trait_code" class="form-select<?= isset($validationErrors['trait_code']) ? ' is-invalid' : ''; ?>">
                <option value="">انتخاب کنید...</option>
                <?php foreach ($traitOptions as $value => $label): ?>
                    <?php $isSelected = (string) ($neoTrait['trait_code'] ?? '') === (string) $value; ?>
                    <option value="<?= htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); ?>" <?= $isSelected ? 'selected' : ''; ?>>
                        <?= htmlspecialchars((string) $label, ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($validationErrors['trait_code'])): ?>
                <div class="invalid-feedback d-block"><?= htmlspecialchars($validationErrors['trait_code'], ENT_QUOTES, 'UTF-8'); ?></div>
            <?php else: ?>
                <small class="text-muted">یکی از تیپ‌های پنج‌عاملی را انتخاب کنید.</small>
            <?php endif; ?>
        </div>
        <div class="col-12 col-md-6">
            <label class="form-label fw-semibold">عنوان نمایش</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars((string) ($neoTrait['trait_label'] ?? $selectedTraitLabel), ENT_QUOTES, 'UTF-8'); ?>" readonly>
            <small class="text-muted">عنوان بر اساس تیپ انتخابی به صورت خودکار تعیین می‌شود.</small>
        </div>
    </div>

    <div class="row g-4 mt-1">
        <?php foreach ($fields as $fieldKey => $fieldLabel): ?>
            <div class="col-12">
                <label for="<?= htmlspecialchars($fieldKey, ENT_QUOTES, 'UTF-8'); ?>" class="form-label fw-semibold">
                    <?= htmlspecialchars($fieldLabel, ENT_QUOTES, 'UTF-8'); ?> *
                </label>
                <textarea
                    name="<?= htmlspecialchars($fieldKey, ENT_QUOTES, 'UTF-8'); ?>"
                    id="<?= htmlspecialchars($fieldKey, ENT_QUOTES, 'UTF-8'); ?>"
                    rows="4"
                    class="form-control<?= isset($validationErrors[$fieldKey]) ? ' is-invalid' : ''; ?>"
                ><?= htmlspecialchars((string) ($neoTrait[$fieldKey] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                <?php if (isset($validationErrors[$fieldKey])): ?>
                    <div class="invalid-feedback d-block"><?= htmlspecialchars($validationErrors[$fieldKey], ENT_QUOTES, 'UTF-8'); ?></div>
                <?php else: ?>
                    <small class="text-muted">توضیحات مرتبط با این بخش را وارد کنید.</small>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="d-flex flex-wrap gap-10 justify-content-between align-items-center mt-32">
        <div class="text-muted small">فیلدهای دارای * اجباری هستند.</div>
        <div class="d-flex gap-10">
            <button type="submit" class="btn btn-main rounded-pill px-24">
                <?= htmlspecialchars($submitLabel, ENT_QUOTES, 'UTF-8'); ?>
            </button>
        </div>
    </div>
</form>
