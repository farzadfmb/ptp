<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../../Helpers/autoload.php';
}

$formAction = $formAction ?? '#';
$submitLabel = $submitLabel ?? 'ثبت';
$cancelUrl = $cancelUrl ?? UtilityHelper::baseUrl('organizations/tools/mbti-settings');
$selectedCategoryKey = isset($selectedCategoryKey) ? (string) $selectedCategoryKey : '';
$featureCategories = $featureCategories ?? [];
$oldInput = $oldInput ?? [];
$validationErrors = $validationErrors ?? [];
$isEdit = $isEdit ?? false;
$featureRecord = $featureRecord ?? [];
$mbtiType = $mbtiType ?? [];

$mbtiTypeId = (int) ($mbtiType['id'] ?? 0);
$featureId = $isEdit ? (int) ($featureRecord['id'] ?? 0) : 0;
$featureTextValue = (string) ($oldInput['feature_text'] ?? '');
$sortOrderValue = (string) ($oldInput['sort_order'] ?? '');

$categoryKeys = array_keys($featureCategories);
$defaultCategoryKey = isset($categoryKeys[0]) ? (string) $categoryKeys[0] : '';
if ($selectedCategoryKey === '' || !array_key_exists($selectedCategoryKey, $featureCategories)) {
    $selectedCategoryKey = $defaultCategoryKey;
}

$categoryError = $validationErrors['category'] ?? null;
$featureTextError = $validationErrors['feature_text'] ?? null;
$sortOrderError = $validationErrors['sort_order'] ?? null;
?>

<form action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8'); ?>" method="post" class="mbti-feature-form">
    <?= csrf_field(); ?>
    <input type="hidden" name="mbti_type_id" value="<?= htmlspecialchars((string) $mbtiTypeId, ENT_QUOTES, 'UTF-8'); ?>">
    <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= htmlspecialchars((string) $featureId, ENT_QUOTES, 'UTF-8'); ?>">
    <?php endif; ?>

    <div class="row g-16">
        <div class="col-12 col-lg-4">
            <label for="feature-category" class="form-label fw-semibold">دسته‌بندی ویژگی</label>
            <select
                id="feature-category"
                name="category"
                class="form-select rounded-16"
                required
            >
                <?php foreach ($featureCategories as $categoryKey => $meta): ?>
                    <?php $categoryLabel = (string) ($meta['label'] ?? $categoryKey); ?>
                    <option value="<?= htmlspecialchars($categoryKey, ENT_QUOTES, 'UTF-8'); ?>" <?= $categoryKey === $selectedCategoryKey ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($categoryLabel, ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($categoryError)): ?>
                <div class="form-text text-danger"><?= htmlspecialchars($categoryError, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php else: ?>
                <div class="form-text text-muted">انتخاب دسته‌بندی برای گروه‌بندی مناسب ویژگی‌ها.</div>
            <?php endif; ?>
        </div>

        <div class="col-12 col-lg-8">
            <label for="feature-sort-order" class="form-label fw-semibold">ترتیب نمایش (اختیاری)</label>
            <input
                type="number"
                step="1"
                id="feature-sort-order"
                name="sort_order"
                class="form-control rounded-16"
                value="<?= htmlspecialchars($sortOrderValue, ENT_QUOTES, 'UTF-8'); ?>"
                placeholder="مثلاً 10"
            >
            <?php if (!empty($sortOrderError)): ?>
                <div class="form-text text-danger"><?= htmlspecialchars($sortOrderError, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php else: ?>
                <div class="form-text text-muted">هرچه عدد کوچکتر باشد، بالاتر نمایش داده می‌شود.</div>
            <?php endif; ?>
        </div>

        <div class="col-12">
            <label for="feature-text" class="form-label fw-semibold">متن ویژگی</label>
            <textarea
                id="feature-text"
                name="feature_text"
                class="form-control rounded-20"
                rows="6"
                placeholder="ویژگی مورد نظر را با توضیح کافی بنویسید..."
                required
                style="text-align: right; direction: rtl;"
            ><?= htmlspecialchars($featureTextValue, ENT_QUOTES, 'UTF-8'); ?></textarea>
            <?php if (!empty($featureTextError)): ?>
                <div class="form-text text-danger"><?= htmlspecialchars($featureTextError, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php else: ?>
                <div class="form-text text-muted">تا حداکثر ۱۰۰۰ کاراکتر را می‌توانید وارد کنید.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-12 justify-content-end mt-24">
        <a href="<?= htmlspecialchars($cancelUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-gray rounded-pill px-24" title="بازگشت">
            انصراف
        </a>
        <button type="submit" class="btn btn-main rounded-pill px-24">
            <?= htmlspecialchars($submitLabel, ENT_QUOTES, 'UTF-8'); ?>
        </button>
    </div>
</form>
