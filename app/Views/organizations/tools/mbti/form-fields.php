<?php
$mbtiType = $mbtiType ?? [];
$validationErrors = $validationErrors ?? [];
$formAction = $formAction ?? '#';
$submitLabel = $submitLabel ?? 'ذخیره';
$cancelUrl = $cancelUrl ?? UtilityHelper::baseUrl('organizations/tools/mbti-settings');
$isEdit = $isEdit ?? false;

$storedCategories = $mbtiType['categories'] ?? ($mbtiType['categories_list'] ?? []);
if (is_string($storedCategories) && trim($storedCategories) !== '') {
    $decoded = json_decode($storedCategories, true);
    if (is_array($decoded)) {
        $storedCategories = $decoded;
    }
}
if (!is_array($storedCategories)) {
    $storedCategories = [];
}

$defaultCategories = '';
if (!empty($storedCategories)) {
    $defaultCategories = implode(', ', array_map(static fn($category) => trim((string) $category), $storedCategories));
}

$oldTypeCode = old('type_code', (string) ($mbtiType['type_code'] ?? ''));
$oldTitle = old('title', (string) ($mbtiType['title'] ?? ''));
$oldSummary = old('summary', (string) ($mbtiType['summary'] ?? ''));
$oldFunctions = old('functions', (string) ($mbtiType['cognitive_functions'] ?? ($mbtiType['functions'] ?? '')));
$oldCategories = old('categories', $defaultCategories);
$oldDescription = old('description', (string) ($mbtiType['description'] ?? ''));
?>

<form action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8'); ?>" method="post" class="mbti-type-form text-start">
    <?= csrf_field(); ?>
    <?php if ($isEdit && !empty($mbtiType['id'])): ?>
        <input type="hidden" name="id" value="<?= htmlspecialchars((string) $mbtiType['id'], ENT_QUOTES, 'UTF-8'); ?>">
    <?php endif; ?>

    <div class="row g-16">
        <div class="col-md-4">
            <label class="form-label fw-semibold">کد تیپ شخصیتی <span class="text-danger">*</span></label>
            <input type="text" name="type_code" class="form-control" value="<?= htmlspecialchars($oldTypeCode, ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: ENFP" maxlength="4" required>
            <?php if (!empty($validationErrors['type_code'])): ?>
                <small class="text-danger mt-6"><?= htmlspecialchars($validationErrors['type_code'], ENT_QUOTES, 'UTF-8'); ?></small>
            <?php endif; ?>
        </div>
        <div class="col-md-8">
            <label class="form-label fw-semibold">عنوان تیپ شخصیتی <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($oldTitle, ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: الهام‌بخش خلاق" maxlength="191" required>
            <?php if (!empty($validationErrors['title'])): ?>
                <small class="text-danger mt-6"><?= htmlspecialchars($validationErrors['title'], ENT_QUOTES, 'UTF-8'); ?></small>
            <?php endif; ?>
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold">توصیف مختصر</label>
            <textarea name="summary" class="form-control" placeholder="جمع‌بندی کوتاه از ویژگی‌های این تیپ" rows="3" maxlength="600"><?= htmlspecialchars($oldSummary, ENT_QUOTES, 'UTF-8'); ?></textarea>
            <?php if (!empty($validationErrors['summary'])): ?>
                <small class="text-danger mt-6"><?= htmlspecialchars($validationErrors['summary'], ENT_QUOTES, 'UTF-8'); ?></small>
            <?php endif; ?>
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold">کارکردها (Cognitive Functions)</label>
            <textarea name="functions" class="form-control" placeholder="کارکردهای غالب، فرعی و ..." rows="4"><?= htmlspecialchars($oldFunctions, ENT_QUOTES, 'UTF-8'); ?></textarea>
            <?php if (!empty($validationErrors['functions'])): ?>
                <small class="text-danger mt-6"><?= htmlspecialchars($validationErrors['functions'], ENT_QUOTES, 'UTF-8'); ?></small>
            <?php endif; ?>
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold">دسته‌بندی‌ها</label>
            <input type="text" name="categories" class="form-control" value="<?= htmlspecialchars($oldCategories, ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: دیپلمات، درون‌گرا، شهودی">
            <small class="text-muted d-block mt-6">دسته‌بندی‌ها را با ویرگول، نقطه‌ویرگول یا خط جدید از هم جدا کنید.</small>
            <?php if (!empty($validationErrors['categories'])): ?>
                <small class="text-danger mt-6"><?= htmlspecialchars($validationErrors['categories'], ENT_QUOTES, 'UTF-8'); ?></small>
            <?php endif; ?>
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold">توضیح تیپ شخصیتی</label>
            <textarea name="description" class="form-control" placeholder="شرح کامل تیپ، نقاط قوت و زمینه‌های رشد" rows="6"><?= htmlspecialchars($oldDescription, ENT_QUOTES, 'UTF-8'); ?></textarea>
            <?php if (!empty($validationErrors['description'])): ?>
                <small class="text-danger mt-6"><?= htmlspecialchars($validationErrors['description'], ENT_QUOTES, 'UTF-8'); ?></small>
            <?php endif; ?>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-12 mt-28">
        <button type="submit" class="btn btn-main rounded-pill px-32 d-flex align-items-center gap-8">
            <ion-icon name="save-outline"></ion-icon>
            <span><?= htmlspecialchars($submitLabel, ENT_QUOTES, 'UTF-8'); ?></span>
        </button>
        <a href="<?= htmlspecialchars($cancelUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-gray rounded-pill px-28">انصراف</a>
    </div>
</form>
