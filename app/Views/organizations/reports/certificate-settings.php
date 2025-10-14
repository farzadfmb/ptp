<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

AuthHelper::startSession();

$title = $title ?? 'تنظیمات گواهی دوره';
$existing = $existing ?? null;
$successMessage = $successMessage ?? null;
$errorMessage = $errorMessage ?? null;
$validationErrors = $_SESSION['validation_errors'] ?? [];
unset($_SESSION['validation_errors']);

$values = [
    'title_ribbon_text' => $existing['title_ribbon_text'] ?? 'گواهی پایان دوره',
    'statement_text' => $existing['statement_text'] ?? 'گزارش بازخورد',
  'template_key' => $existing['template_key'] ?? 'classic',
    'show_org_logo' => (int)($existing['show_org_logo'] ?? 1),
    'show_signatures' => (int)($existing['show_signatures'] ?? 1),
    'enable_decorations' => (int)($existing['enable_decorations'] ?? 1),
    'pdf_mode' => $existing['pdf_mode'] ?? 'simple',
    'extra_footer_text' => $existing['extra_footer_text'] ?? '',
  'enable_second_page' => (int)($existing['enable_second_page'] ?? 0),
  'second_page_image_path' => $existing['second_page_image_path'] ?? null,
  'second_page_image_width_mm' => $existing['second_page_image_width_mm'] ?? '',
  'second_page_image_height_mm' => $existing['second_page_image_height_mm'] ?? '',
  'second_page_title_ribbon_text' => $existing['second_page_title_ribbon_text'] ?? 'جزئیات ارزیابی‌شونده',
  // Page 3 defaults
  'enable_third_page' => (int)($existing['enable_third_page'] ?? 0),
  'third_page_title_ribbon_text' => $existing['third_page_title_ribbon_text'] ?? 'فهرست گواهی',
  'third_page_image_path' => $existing['third_page_image_path'] ?? null,
  'third_page_image_width_mm' => $existing['third_page_image_width_mm'] ?? '',
  'third_page_image_height_mm' => $existing['third_page_image_height_mm'] ?? '',
  'third_page_items_json' => $existing['third_page_items_json'] ?? '[]',
  // Page 4 defaults
  'enable_fourth_page' => (int)($existing['enable_fourth_page'] ?? 0),
  'fourth_page_title_ribbon_text' => $existing['fourth_page_title_ribbon_text'] ?? 'متن تکمیلی',
  'fourth_page_text' => $existing['fourth_page_text'] ?? '',
  'fourth_page_text_align' => $existing['fourth_page_text_align'] ?? '',
  'fourth_page_image_path' => $existing['fourth_page_image_path'] ?? null,
  'fourth_page_image_width_mm' => $existing['fourth_page_image_width_mm'] ?? '',
  'fourth_page_image_height_mm' => $existing['fourth_page_image_height_mm'] ?? '',
  // Page 5 defaults
  'enable_fifth_page' => (int)($existing['enable_fifth_page'] ?? 0),
  'fifth_page_title_ribbon_text' => $existing['fifth_page_title_ribbon_text'] ?? 'مدل شایستگی',
  'fifth_page_text' => $existing['fifth_page_text'] ?? '',
  'fifth_page_text_align' => $existing['fifth_page_text_align'] ?? '',
  // Page 6 defaults
  'enable_sixth_page' => (int)($existing['enable_sixth_page'] ?? 0),
  'sixth_page_title_ribbon_text' => $existing['sixth_page_title_ribbon_text'] ?? 'شایستگی‌ها و تعاریف',
  'sixth_page_text' => $existing['sixth_page_text'] ?? '',
  'sixth_page_text_align' => $existing['sixth_page_text_align'] ?? '',
  // Page 7 defaults
  'enable_seventh_page' => (int)($existing['enable_seventh_page'] ?? 0),
  'seventh_page_title_ribbon_text' => $existing['seventh_page_title_ribbon_text'] ?? 'متن و تصویر',
  'seventh_page_text' => $existing['seventh_page_text'] ?? '',
  'seventh_page_image_path' => $existing['seventh_page_image_path'] ?? null,
  'seventh_page_text_align' => $existing['seventh_page_text_align'] ?? '',
  // Page 8 defaults
  'enable_eighth_page' => (int)($existing['enable_eighth_page'] ?? 0),
  'eighth_page_title_ribbon_text' => $existing['eighth_page_title_ribbon_text'] ?? 'ابزارهای ارزیابی',
  // Page 9 defaults
  'enable_ninth_page' => (int)($existing['enable_ninth_page'] ?? 0),
  'ninth_page_title_ribbon_text' => $existing['ninth_page_title_ribbon_text'] ?? 'نتایج تکمیلی',
  'ninth_page_text' => $existing['ninth_page_text'] ?? '',
  'ninth_page_text_align' => $existing['ninth_page_text_align'] ?? '',
  'ninth_page_items_json' => $existing['ninth_page_items_json'] ?? '[]',
  // Page 10 defaults (MBTI intro)
  'enable_tenth_page' => (int)($existing['enable_tenth_page'] ?? 0),
  'tenth_page_title_ribbon_text' => $existing['tenth_page_title_ribbon_text'] ?? 'معرفی آزمون MBTI',
  'tenth_page_text' => $existing['tenth_page_text'] ?? '',
  'tenth_page_text_align' => $existing['tenth_page_text_align'] ?? '',
  // Page 11 defaults (MBTI results)
  'enable_eleventh_page' => (int)($existing['enable_eleventh_page'] ?? 0),
  'eleventh_page_title_ribbon_text' => $existing['eleventh_page_title_ribbon_text'] ?? 'نتایج آزمون MBTI',
  'eleventh_page_text' => $existing['eleventh_page_text'] ?? '',
  'eleventh_page_text_align' => $existing['eleventh_page_text_align'] ?? '',
  // Page 13 defaults (DISC results)
  'enable_thirteenth_page' => (int)($existing['enable_thirteenth_page'] ?? 0),
  'thirteenth_page_title_ribbon_text' => $existing['thirteenth_page_title_ribbon_text'] ?? 'نتایج آزمون DISC',
  'thirteenth_page_text' => $existing['thirteenth_page_text'] ?? '',
  'thirteenth_page_text_align' => $existing['thirteenth_page_text_align'] ?? '',
  // Page 15 defaults (Analytical thinking)
  'enable_fifteenth_page' => (int)($existing['enable_fifteenth_page'] ?? 0),
  'fifteenth_page_title_ribbon_text' => $existing['fifteenth_page_title_ribbon_text'] ?? 'نتایج تفکر تحلیلی',
  'fifteenth_page_text' => $existing['fifteenth_page_text'] ?? '',
  'fifteenth_page_text_align' => $existing['fifteenth_page_text_align'] ?? '',
];

include __DIR__ . '/../../layouts/organization-header.php';
include __DIR__ . '/../../layouts/organization-sidebar.php';
include __DIR__ . '/../../layouts/organization-navbar.php';
?>

<div class="page-content-wrapper">
  <div class="page-content">
    <div class="card border-0 shadow-sm rounded-24">
      <div class="card-body p-24">
        <h2 class="mb-16">تنظیمات گواهی دوره</h2>

        <?php foreach ([['type' => 'success','msg' => $successMessage],['type' => 'danger','msg' => $errorMessage]] as $a): ?>
          <?php if (!empty($a['msg'])): ?>
            <div class="alert alert-<?= htmlspecialchars($a['type'], ENT_QUOTES, 'UTF-8'); ?> rounded-16" role="alert">
              <?= htmlspecialchars((string)$a['msg'], ENT_QUOTES, 'UTF-8'); ?>
            </div>
          <?php endif; ?>
        <?php endforeach; ?>

  <form method="post" enctype="multipart/form-data" action="<?= UtilityHelper::baseUrl('organizations/reports/certificate-settings'); ?>">
          <input type="hidden" name="_token" value="<?= htmlspecialchars(AuthHelper::generateCsrfToken(), ENT_QUOTES, 'UTF-8'); ?>">

          <div class="row g-16">
            <div class="col-md-6">
              <label class="form-label">قالب گواهی</label>
              <select class="form-select" name="template_key">
                <option value="classic" <?= ($values['template_key']==='classic' ? 'selected' : ''); ?>>کلاسیک (پیشنهادی)</option>
                <option value="minimal" <?= ($values['template_key']==='minimal' ? 'selected' : ''); ?>>مینیمال (سبک و مناسب PDF)</option>
                <option value="bordered" <?= ($values['template_key']==='bordered' ? 'selected' : ''); ?>>قاب‌دار (خوانایی عالی)</option>
              </select>
              <div class="form-text">تمام قالب‌ها برای خروجی PDF بهینه شده‌اند.</div>
            </div>
            <div class="col-md-6"></div>
            <div class="col-12"><hr></div>
            <div class="col-12"><h6 class="text-muted">پیش‌نمایش کوتاه قالب‌ها</h6>
              <ul class="small text-muted mb-16">
                <li>کلاسیک: پس‌زمینه تزئینی و روبان گرادیانی</li>
                <li>مینیمال: بدون تزئینات، خطوط ساده، بیشترین پایداری PDF</li>
                <li>قاب‌دار: حاشیه مشخص و قاب داخلی، تمرکز بر محتوا</li>
              </ul>
            </div>
              <label class="form-label">عنوان روی روبان</label>
              <input type="text" name="title_ribbon_text" class="form-control" value="<?= htmlspecialchars($values['title_ribbon_text'], ENT_QUOTES, 'UTF-8'); ?>">
              <?php if (!empty($validationErrors['title_ribbon_text'])): ?>
                <div class="text-danger small mt-2"><?= htmlspecialchars((string)$validationErrors['title_ribbon_text'], ENT_QUOTES, 'UTF-8'); ?></div>
              <?php endif; ?>
            </div>
            <div class="col-md-6">
              <label class="form-label">متن معرفی (بالای نام)</label>
              <input type="text" name="statement_text" class="form-control" value="<?= htmlspecialchars($values['statement_text'], ENT_QUOTES, 'UTF-8'); ?>">
              <?php if (!empty($validationErrors['statement_text'])): ?>
                <div class="text-danger small mt-2"><?= htmlspecialchars((string)$validationErrors['statement_text'], ENT_QUOTES, 'UTF-8'); ?></div>
              <?php endif; ?>
            </div>
            <div class="col-md-4">
              <label class="form-label">نمایش لوگوی سازمان</label>
              <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" name="show_org_logo" id="show_org_logo" <?= $values['show_org_logo'] ? 'checked' : ''; ?>>
                <label class="form-check-label" for="show_org_logo">نمایش</label>
              </div>
            </div>
            <div class="col-md-4">
              <label class="form-label">نمایش امضاها</label>
              <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" name="show_signatures" id="show_signatures" <?= $values['show_signatures'] ? 'checked' : ''; ?>>
                <label class="form-check-label" for="show_signatures">نمایش</label>
              </div>
            </div>
            <div class="col-md-4">
              <label class="form-label">تزئینات پس‌زمینه</label>
              <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" name="enable_decorations" id="enable_decorations" <?= $values['enable_decorations'] ? 'checked' : ''; ?>>
                <label class="form-check-label" for="enable_decorations">فعال</label>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label">حالت PDF</label>
              <select class="form-select" name="pdf_mode">
                <option value="simple" <?= $values['pdf_mode']==='simple' ? 'selected' : ''; ?>>ساده (پایدار)</option>
                <option value="full" <?= $values['pdf_mode']==='full' ? 'selected' : ''; ?>>کامل (ظاهر زیبا)</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">متن اضافی پاورقی</label>
              <input type="text" name="extra_footer_text" class="form-control" value="<?= htmlspecialchars($values['extra_footer_text'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="اختیاری">
              <?php if (!empty($validationErrors['extra_footer_text'])): ?>
                <div class="text-danger small mt-2"><?= htmlspecialchars((string)$validationErrors['extra_footer_text'], ENT_QUOTES, 'UTF-8'); ?></div>
              <?php endif; ?>
            </div>

            <div class="col-12 mt-2">
              <hr>
              <h5 class="mt-2 mb-12">صفحه دوم</h5>
            </div>
            <div class="col-md-6">
              <label class="form-label">عنوان روی روبان (صفحه دوم)</label>
              <input type="text" name="second_page_title_ribbon_text" class="form-control" value="<?= htmlspecialchars($values['second_page_title_ribbon_text'], ENT_QUOTES, 'UTF-8'); ?>">
              <?php if (!empty($validationErrors['second_page_title_ribbon_text'])): ?>
                <div class="text-danger small mt-2"><?= htmlspecialchars((string)$validationErrors['second_page_title_ribbon_text'], ENT_QUOTES, 'UTF-8'); ?></div>
              <?php endif; ?>
            </div>
            <div class="col-md-4">
              <label class="form-label">فعال‌سازی صفحه دوم</label>
              <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" name="enable_second_page" id="enable_second_page" <?= $values['enable_second_page'] ? 'checked' : ''; ?>>
                <label class="form-check-label" for="enable_second_page">فعال</label>
              </div>
            </div>
            <div class="col-md-8">
              <label class="form-label">تصویر اختیاری برای صفحه دوم</label>
              <input type="file" name="second_page_image" accept="image/*" class="form-control" />
              <?php if (!empty($values['second_page_image_path'])): ?>
                <div class="mt-2 d-flex align-items-center gap-12">
                  <img src="<?= UtilityHelper::baseUrl('public/' . ltrim($values['second_page_image_path'],'/')); ?>" alt="Second page" style="max-height:80px; border-radius:8px; border:1px solid #e2e8f0;" />
                  <span class="text-muted small">تصویر فعلی</span>
                </div>
                <div class="form-check mt-2">
                  <input class="form-check-input" type="checkbox" name="remove_second_page_image" id="remove_second_page_image">
                  <label class="form-check-label" for="remove_second_page_image">حذف تصویر فعلی</label>
                </div>
              <?php endif; ?>
            </div>
            <div class="col-md-4">
              <label class="form-label">عرض تصویر (میلی‌متر)</label>
              <input type="number" step="0.5" min="1" max="280" name="second_page_image_width_mm" class="form-control" value="<?= htmlspecialchars((string)$values['second_page_image_width_mm'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثلاً 180">
              <?php if (!empty($validationErrors['second_page_image_width_mm'])): ?>
                <div class="text-danger small mt-2"><?= htmlspecialchars((string)$validationErrors['second_page_image_width_mm'], ENT_QUOTES, 'UTF-8'); ?></div>
              <?php endif; ?>
            </div>
            <div class="col-md-4">
              <label class="form-label">ارتفاع تصویر (میلی‌متر)</label>
              <input type="number" step="0.5" min="1" max="180" name="second_page_image_height_mm" class="form-control" value="<?= htmlspecialchars((string)$values['second_page_image_height_mm'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثلاً 100">
              <?php if (!empty($validationErrors['second_page_image_height_mm'])): ?>
                <div class="text-danger small mt-2"><?= htmlspecialchars((string)$validationErrors['second_page_image_height_mm'], ENT_QUOTES, 'UTF-8'); ?></div>
              <?php endif; ?>
              <div class="form-text">اگر خالی بماند، اندازه تصویر به‌صورت خودکار بر اساس اندازه اصلی محدود می‌شود.</div>
            </div>

            <div class="col-12 mt-4">
              <hr>
              <h5 class="mt-2 mb-12">صفحه سوم (فهرست گواهی)</h5>
            </div>
            <div class="col-md-4">
              <label class="form-label">فعال‌سازی صفحه سوم</label>
              <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" name="enable_third_page" id="enable_third_page" <?= $values['enable_third_page'] ? 'checked' : ''; ?>>
                <label class="form-check-label" for="enable_third_page">فعال</label>
              </div>
            </div>
            <div class="col-12 mt-4">
              <hr>
              <h5 class="mt-2 mb-12">ترتیب صفحات گزارش</h5>
              <?php
                  $order = [];
                  // حداقل بر اساس صفحات فعال
                  if (!empty($values['enable_second_page'])) { $order[] = 'details'; }
                  $tpItems = json_decode((string)($values['third_page_items_json'] ?? '[]'), true) ?: [];
                  if (!empty($values['enable_third_page'])) { $order[] = 'toc'; }
      if (!empty($values['enable_fourth_page'])) { $order[] = 'page4'; }
    if (!empty($values['enable_fifth_page'])) { $order[] = 'page5'; }
    if (!empty($values['enable_sixth_page'])) { $order[] = 'page6'; }
  if (!empty($values['enable_seventh_page'])) { $order[] = 'page7'; }
  if (!empty($values['enable_eighth_page'])) { $order[] = 'page8'; }
  if (!empty($values['enable_ninth_page'])) { $order[] = 'page9'; }
                  if (!empty($values['enable_tenth_page'])) { $order[] = 'page10'; }
  if (!empty($values['enable_eleventh_page'])) { $order[] = 'page11'; }
  if (!empty($values['enable_thirteenth_page'])) { $order[] = 'page13'; }
  if (!empty($values['enable_fifteenth_page'])) { $order[] = 'page15'; }
                  // اگر رکوردی ذخیره شده باشد از دیتابیس بخوانیم (نیاز به existing)
                  if (!empty($existing['page_order_json'])) {
                      $saved = json_decode((string)$existing['page_order_json'], true);
                      if (is_array($saved)) { $order = $saved; }
                  }
      // نرمال‌سازی: حذف تکراری‌ها/غیرفعال‌ها و افزودن صفحات فعال جدید
      $allowed = ['details','toc','page4','page5','page6','page7','page8','page9','page10','page11','page13','page15'];
      $order = array_values(array_unique(array_intersect($order, $allowed)));
      $enabledMap = [
        'details' => !empty($values['enable_second_page']),
        'toc' => !empty($values['enable_third_page']),
        'page4' => !empty($values['enable_fourth_page']),
        'page5' => !empty($values['enable_fifth_page']),
        'page6' => !empty($values['enable_sixth_page']),
        'page7' => !empty($values['enable_seventh_page']),
        'page8' => !empty($values['enable_eighth_page']),
        'page9' => !empty($values['enable_ninth_page']),
        'page10' => !empty($values['enable_tenth_page']),
        'page11' => !empty($values['enable_eleventh_page']),
        'page13' => !empty($values['enable_thirteenth_page']),
        'page15' => !empty($values['enable_fifteenth_page']),
      ];
      foreach ($allowed as $slug) {
        if ($enabledMap[$slug] && !in_array($slug, $order, true)) { $order[] = $slug; }
      }
      // گزینه‌ها
      $labels = [
        'details' => 'صفحه جزئیات کاربر (صفحه دوم)',
        'toc' => 'فهرست گواهی (صفحه سوم)',
        'page4' => 'صفحه چهارم (متن + تصویر)',
        'page5' => 'صفحه پنجم (متن + تصویر مدل)',
        'page6' => 'صفحه ششم (متن + جدول شایستگی‌ها)',
        'page7' => 'صفحه هفتم (متن + تصویر)',
        'page8' => 'صفحه هشتم (جدول ابزارها)',
        'page9' => 'صفحه نهم (متن + جدول نتایج)',
        'page10' => 'صفحه دهم (معرفی MBTI)',
        'page11' => 'صفحه یازدهم (نتایج MBTI)',
        'page13' => 'صفحه سیزدهم (نتایج DISC)',
        'page15' => 'صفحه پانزدهم (تفکر تحلیلی)'
      ];
              ?>
              <div id="page-order-list" class="d-flex flex-column gap-8">
                <?php foreach ($order as $slug): if (!isset($labels[$slug])) continue; ?>
                  <div class="border rounded-3 p-12 d-flex justify-content-between align-items-center">
                    <div>
                      <input type="hidden" name="page_order[]" value="<?= htmlspecialchars($slug, ENT_QUOTES, 'UTF-8'); ?>">
                      <?= htmlspecialchars($labels[$slug], ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                    <div class="btn-group">
                      <button class="btn btn-sm btn-outline-secondary" type="button" onclick="moveOrderItem(this, -1)">بالا</button>
                      <button class="btn btn-sm btn-outline-secondary" type="button" onclick="moveOrderItem(this, 1)">پایین</button>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
              <div class="form-text mt-2">با دکمه‌ها ترتیب صفحات را تنظیم کنید. صفحات فعال به صورت خودکار نمایش داده می‌شوند.</div>
            </div>
            <div class="col-md-8">
              <label class="form-label">عنوان روی روبان (صفحه سوم)</label>
              <input type="text" name="third_page_title_ribbon_text" class="form-control" value="<?= htmlspecialchars($values['third_page_title_ribbon_text'], ENT_QUOTES, 'UTF-8'); ?>">
              <?php if (!empty($validationErrors['third_page_title_ribbon_text'])): ?>
                <div class="text-danger small mt-2"><?= htmlspecialchars((string)$validationErrors['third_page_title_ribbon_text'], ENT_QUOTES, 'UTF-8'); ?></div>
              <?php endif; ?>
            </div>
            <div class="col-md-8">
              <label class="form-label">تصویر اختیاری برای صفحه سوم</label>
              <input type="file" name="third_page_image" accept="image/*" class="form-control" />
              <?php if (!empty($values['third_page_image_path'])): ?>
                <div class="mt-2 d-flex align-items-center gap-12">
                  <img src="<?= UtilityHelper::baseUrl('public/' . ltrim($values['third_page_image_path'],'/')); ?>" alt="Third page" style="max-height:80px; border-radius:8px; border:1px solid #e2e8f0;" />
                  <span class="text-muted small">تصویر فعلی</span>
                </div>
                <div class="form-check mt-2">
                  <input class="form-check-input" type="checkbox" name="remove_third_page_image" id="remove_third_page_image">
                  <label class="form-check-label" for="remove_third_page_image">حذف تصویر فعلی</label>
                </div>
              <?php endif; ?>
            </div>
            <div class="col-md-4">
              <label class="form-label">عرض تصویر (میلی‌متر)</label>
              <input type="number" step="0.5" min="1" max="280" name="third_page_image_width_mm" class="form-control" value="<?= htmlspecialchars((string)$values['third_page_image_width_mm'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثلاً 180">
              <?php if (!empty($validationErrors['third_page_image_width_mm'])): ?>
                <div class="text-danger small mt-2"><?= htmlspecialchars((string)$validationErrors['third_page_image_width_mm'], ENT_QUOTES, 'UTF-8'); ?></div>
              <?php endif; ?>
            </div>
            <div class="col-md-4">
              <label class="form-label">ارتفاع تصویر (میلی‌متر)</label>
              <input type="number" step="0.5" min="1" max="180" name="third_page_image_height_mm" class="form-control" value="<?= htmlspecialchars((string)$values['third_page_image_height_mm'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثلاً 100">
              <?php if (!empty($validationErrors['third_page_image_height_mm'])): ?>
                <div class="text-danger small mt-2"><?= htmlspecialchars((string)$validationErrors['third_page_image_height_mm'], ENT_QUOTES, 'UTF-8'); ?></div>
              <?php endif; ?>
              <div class="form-text">اگر خالی بماند، اندازه تصویر به‌صورت خودکار بر اساس اندازه اصلی محدود می‌شود.</div>
            </div>
            <div class="col-12">
              <label class="form-label">آیتم‌های فهرست (عنوان و شماره/برچسب صفحه)</label>
              <?php $items = json_decode((string)$values['third_page_items_json'] ?: '[]', true) ?: []; ?>
              <div class="table-responsive">
                <table class="table table-bordered align-middle">
                  <thead>
                    <tr>
                      <th style="width:60%">عنوان آیتم</th>
                      <th style="width:30%">صفحه</th>
                      <th style="width:10%">عملیات</th>
                    </tr>
                  </thead>
                  <tbody id="tp-items-body">
                    <?php if (empty($items)): ?>
                      <tr>
                        <td><input type="text" name="third_page_items_title[]" class="form-control" placeholder="مثلاً: خلاصه گزارش" /></td>
                        <td><input type="text" name="third_page_items_page[]" class="form-control" placeholder="مثلاً: صفحه ۴" /></td>
                        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTpRow(this)">حذف</button></td>
                      </tr>
                    <?php else: ?>
                      <?php foreach ($items as $it): ?>
                        <tr>
                          <td><input type="text" name="third_page_items_title[]" class="form-control" value="<?= htmlspecialchars((string)($it['title'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" /></td>
                          <td><input type="text" name="third_page_items_page[]" class="form-control" value="<?= htmlspecialchars((string)($it['page'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" /></td>
                          <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTpRow(this)">حذف</button></td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
              <button type="button" class="btn btn-sm btn-outline-primary" onclick="addTpRow()">افزودن ردیف</button>
            </div>
            <div class="col-12 mt-4">
              <hr>
              <h5 class="mt-2 mb-12">صفحه چهارم (متن + تصویر)</h5>
            </div>
            <div class="col-md-4">
              <label class="form-label">فعال‌سازی صفحه چهارم</label>
              <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" name="enable_fourth_page" id="enable_fourth_page" <?= $values['enable_fourth_page'] ? 'checked' : ''; ?>>
                <label class="form-check-label" for="enable_fourth_page">فعال</label>
              </div>
            </div>
            <div class="col-md-8">
              <label class="form-label">عنوان روی روبان (صفحه چهارم)</label>
              <input type="text" name="fourth_page_title_ribbon_text" class="form-control" value="<?= htmlspecialchars($values['fourth_page_title_ribbon_text'], ENT_QUOTES, 'UTF-8'); ?>">
              <?php if (!empty($validationErrors['fourth_page_title_ribbon_text'])): ?>
                <div class="text-danger small mt-2"><?= htmlspecialchars((string)$validationErrors['fourth_page_title_ribbon_text'], ENT_QUOTES, 'UTF-8'); ?></div>
              <?php endif; ?>
            </div>
            <div class="col-12">
              <label class="form-label">متن صفحه چهارم</label>
              <textarea name="fourth_page_text" class="form-control" rows="6" placeholder="متن آزاد..."><?= htmlspecialchars($values['fourth_page_text'], ENT_QUOTES, 'UTF-8'); ?></textarea>
              <?php if (!empty($validationErrors['fourth_page_text'])): ?>
                <div class="text-danger small mt-2"><?= htmlspecialchars((string)$validationErrors['fourth_page_text'], ENT_QUOTES, 'UTF-8'); ?></div>
              <?php endif; ?>
              <div class="mt-2">
                <label class="form-label">چیدمان متن</label>
                <select name="fourth_page_text_align" class="form-select" style="max-width:220px;">
                  <?php $opts = ['right' => 'راست‌چین', 'center' => 'وسط‌چین', 'justify' => 'کشیده (Justify)', 'left' => 'چپ‌چین']; $v = $values['fourth_page_text_align'] ?? ''; foreach ($opts as $k=>$lbl): ?>
                  <option value="<?= htmlspecialchars($k, ENT_QUOTES, 'UTF-8'); ?>" <?= ($v===$k?'selected':''); ?>><?= htmlspecialchars($lbl, ENT_QUOTES, 'UTF-8'); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-8">
              <label class="form-label">تصویر اختیاری برای صفحه چهارم</label>
              <input type="file" name="fourth_page_image" accept="image/*" class="form-control" />
              <?php if (!empty($values['fourth_page_image_path'])): ?>
                <div class="mt-2 d-flex align-items-center gap-12">
                  <img src="<?= UtilityHelper::baseUrl('public/' . ltrim($values['fourth_page_image_path'],'/')); ?>" alt="Fourth page" style="max-height:80px; border-radius:8px; border:1px solid #e2e8f0;" />
                  <span class="text-muted small">تصویر فعلی</span>
                </div>
                <div class="form-check mt-2">
                  <input class="form-check-input" type="checkbox" name="remove_fourth_page_image" id="remove_fourth_page_image">
                  <label class="form-check-label" for="remove_fourth_page_image">حذف تصویر فعلی</label>
                </div>
              <?php endif; ?>
            </div>
            <div class="col-md-4">
              <label class="form-label">عرض تصویر (میلی‌متر)</label>
              <input type="number" step="0.5" min="1" max="280" name="fourth_page_image_width_mm" class="form-control" value="<?= htmlspecialchars((string)$values['fourth_page_image_width_mm'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثلاً 180">
              <?php if (!empty($validationErrors['fourth_page_image_width_mm'])): ?>
                <div class="text-danger small mt-2"><?= htmlspecialchars((string)$validationErrors['fourth_page_image_width_mm'], ENT_QUOTES, 'UTF-8'); ?></div>
              <?php endif; ?>
            </div>
            <div class="col-md-4">
              <label class="form-label">ارتفاع تصویر (میلی‌متر)</label>
              <input type="number" step="0.5" min="1" max="180" name="fourth_page_image_height_mm" class="form-control" value="<?= htmlspecialchars((string)$values['fourth_page_image_height_mm'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثلاً 100">
              <?php if (!empty($validationErrors['fourth_page_image_height_mm'])): ?>
                <div class="text-danger small mt-2"><?= htmlspecialchars((string)$validationErrors['fourth_page_image_height_mm'], ENT_QUOTES, 'UTF-8'); ?></div>
              <?php endif; ?>
              <div class="form-text">اگر خالی بماند، اندازه تصویر به‌صورت خودکار بر اساس اندازه اصلی محدود می‌شود.</div>
            </div>
            <div class="col-12 mt-4">
              <hr>
              <h5 class="mt-2 mb-12">صفحه پنجم (متن + تصویر مدل شایستگی)</h5>
            </div>
            <div class="col-md-4">
              <label class="form-label">فعال‌سازی صفحه پنجم</label>
              <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" name="enable_fifth_page" id="enable_fifth_page" <?= $values['enable_fifth_page'] ? 'checked' : ''; ?> />
                <label class="form-check-label" for="enable_fifth_page">فعال</label>
              </div>
            </div>
            <div class="col-md-8">
              <label class="form-label">عنوان روی روبان (صفحه پنجم)</label>
              <input type="text" name="fifth_page_title_ribbon_text" class="form-control" value="<?= htmlspecialchars($values['fifth_page_title_ribbon_text'], ENT_QUOTES, 'UTF-8'); ?>">
              <?php if (!empty($validationErrors['fifth_page_title_ribbon_text'])): ?>
                <div class="text-danger small mt-2"><?= htmlspecialchars((string)$validationErrors['fifth_page_title_ribbon_text'], ENT_QUOTES, 'UTF-8'); ?></div>
              <?php endif; ?>
            </div>
            <div class="col-12">
              <label class="form-label">متن صفحه پنجم</label>
              <textarea name="fifth_page_text" class="form-control" rows="6" placeholder="متن آزاد..."><?= htmlspecialchars($values['fifth_page_text'], ENT_QUOTES, 'UTF-8'); ?></textarea>
              <div class="mt-2">
                <label class="form-label">چیدمان متن</label>
                <select name="fifth_page_text_align" class="form-select" style="max-width:220px;">
                  <?php $opts = ['right' => 'راست‌چین', 'center' => 'وسط‌چین', 'justify' => 'کشیده (Justify)', 'left' => 'چپ‌چین']; $v = $values['fifth_page_text_align'] ?? ''; foreach ($opts as $k=>$lbl): ?>
                  <option value="<?= htmlspecialchars($k, ENT_QUOTES, 'UTF-8'); ?>" <?= ($v===$k?'selected':''); ?>><?= htmlspecialchars($lbl, ENT_QUOTES, 'UTF-8'); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="col-12 mt-4">
              <hr>
              <h5 class="mt-2 mb-12">صفحه ششم (متن + جدول شایستگی‌ها)</h5>
            </div>
            <div class="col-md-4">
              <label class="form-label">فعال‌سازی صفحه ششم</label>
              <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" name="enable_sixth_page" id="enable_sixth_page" <?= $values['enable_sixth_page'] ? 'checked' : ''; ?> />
                <label class="form-check-label" for="enable_sixth_page">فعال</label>
              </div>
            </div>
            <div class="col-md-8">
              <label class="form-label">عنوان روی روبان (صفحه ششم)</label>
              <input type="text" name="sixth_page_title_ribbon_text" class="form-control" value="<?= htmlspecialchars($values['sixth_page_title_ribbon_text'], ENT_QUOTES, 'UTF-8'); ?>" />
              <?php if (!empty($validationErrors['sixth_page_title_ribbon_text'])): ?>
                <div class="text-danger small mt-2"><?= htmlspecialchars((string)$validationErrors['sixth_page_title_ribbon_text'], ENT_QUOTES, 'UTF-8'); ?></div>
              <?php endif; ?>
            </div>
            <div class="col-12">
              <label class="form-label">متن صفحه ششم</label>
              <textarea name="sixth_page_text" class="form-control" rows="6" placeholder="متن آزاد..."><?= htmlspecialchars($values['sixth_page_text'], ENT_QUOTES, 'UTF-8'); ?></textarea>
              <div class="mt-2">
                <label class="form-label">چیدمان متن</label>
                <select name="sixth_page_text_align" class="form-select" style="max-width:220px;">
                  <?php $opts = ['right' => 'راست‌چین', 'center' => 'وسط‌چین', 'justify' => 'کشیده (Justify)', 'left' => 'چپ‌چین']; $v = $values['sixth_page_text_align'] ?? ''; foreach ($opts as $k=>$lbl): ?>
                  <option value="<?= htmlspecialchars($k, ENT_QUOTES, 'UTF-8'); ?>" <?= ($v===$k?'selected':''); ?>><?= htmlspecialchars($lbl, ENT_QUOTES, 'UTF-8'); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-12 mt-4">
              <hr>
              <h5 class="mt-2 mb-12">صفحه هفتم (متن + تصویر)</h5>
            </div>
            <div class="col-md-4">
              <label class="form-label">فعال‌سازی صفحه هفتم</label>
              <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" name="enable_seventh_page" id="enable_seventh_page" <?= $values['enable_seventh_page'] ? 'checked' : ''; ?> />
                <label class="form-check-label" for="enable_seventh_page">فعال</label>
              </div>
            </div>
            <div class="col-md-8">
              <label class="form-label">عنوان روی روبان (صفحه هفتم)</label>
              <input type="text" name="seventh_page_title_ribbon_text" class="form-control" value="<?= htmlspecialchars($values['seventh_page_title_ribbon_text'], ENT_QUOTES, 'UTF-8'); ?>" />
            </div>
            <div class="col-12">
              <label class="form-label">متن صفحه هفتم</label>
              <textarea name="seventh_page_text" class="form-control" rows="6" placeholder="متن آزاد..."><?= htmlspecialchars($values['seventh_page_text'], ENT_QUOTES, 'UTF-8'); ?></textarea>
              <div class="mt-2">
                <label class="form-label">چیدمان متن</label>
                <select name="seventh_page_text_align" class="form-select" style="max-width:220px;">
                  <?php $opts = ['right' => 'راست‌چین', 'center' => 'وسط‌چین', 'justify' => 'کشیده (Justify)', 'left' => 'چپ‌چین']; $v = $values['seventh_page_text_align'] ?? ''; foreach ($opts as $k=>$lbl): ?>
                  <option value="<?= htmlspecialchars($k, ENT_QUOTES, 'UTF-8'); ?>" <?= ($v===$k?'selected':''); ?>><?= htmlspecialchars($lbl, ENT_QUOTES, 'UTF-8'); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-8">
              <label class="form-label">تصویر اختیاری برای صفحه هفتم</label>
              <input type="file" name="seventh_page_image" accept="image/*" class="form-control" />
              <?php if (!empty($values['seventh_page_image_path'])): ?>
                <div class="mt-2 d-flex align-items-center gap-12">
                  <img src="<?= UtilityHelper::baseUrl('public/' . ltrim($values['seventh_page_image_path'],'/')); ?>" alt="Seventh page" style="max-height:80px; border-radius:8px; border:1px solid #e2e8f0;" />
                  <span class="text-muted small">تصویر فعلی</span>
                </div>
                <div class="form-check mt-2">
                  <input class="form-check-input" type="checkbox" name="remove_seventh_page_image" id="remove_seventh_page_image">
                  <label class="form-check-label" for="remove_seventh_page_image">حذف تصویر فعلی</label>
                </div>
              <?php endif; ?>
            </div>
            <div class="col-12 mt-4">
              <hr>
              <h5 class="mt-2 mb-12">صفحه هشتم (جدول ابزارها)</h5>
            </div>
            <div class="col-md-4">
              <label class="form-label">فعال‌سازی صفحه هشتم</label>
              <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" name="enable_eighth_page" id="enable_eighth_page" <?= $values['enable_eighth_page'] ? 'checked' : ''; ?> />
                <label class="form-check-label" for="enable_eighth_page">فعال</label>
              </div>
            </div>
            <div class="col-md-8">
              <label class="form-label">عنوان روی روبان (صفحه هشتم)</label>
              <input type="text" name="eighth_page_title_ribbon_text" class="form-control" value="<?= htmlspecialchars($values['eighth_page_title_ribbon_text'], ENT_QUOTES, 'UTF-8'); ?>" />
              <?php if (!empty($validationErrors['eighth_page_title_ribbon_text'])): ?>
                <div class="text-danger small mt-2"><?= htmlspecialchars((string)$validationErrors['eighth_page_title_ribbon_text'], ENT_QUOTES, 'UTF-8'); ?></div>
              <?php endif; ?>
            </div>
            <div class="col-12 mt-4">
              <hr>
              <h5 class="mt-2 mb-12">صفحه نهم (متن + جدول نتایج)</h5>
            </div>
            <div class="col-md-4">
              <label class="form-label">فعال‌سازی صفحه نهم</label>
              <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" name="enable_ninth_page" id="enable_ninth_page" <?= $values['enable_ninth_page'] ? 'checked' : ''; ?> />
                <label class="form-check-label" for="enable_ninth_page">فعال</label>
              </div>
            </div>
            <div class="col-md-8">
              <label class="form-label">عنوان روی روبان (صفحه نهم)</label>
              <input type="text" name="ninth_page_title_ribbon_text" class="form-control" value="<?= htmlspecialchars($values['ninth_page_title_ribbon_text'], ENT_QUOTES, 'UTF-8'); ?>" />
              <?php if (!empty($validationErrors['ninth_page_title_ribbon_text'])): ?>
                <div class="text-danger small mt-2"><?= htmlspecialchars((string)$validationErrors['ninth_page_title_ribbon_text'], ENT_QUOTES, 'UTF-8'); ?></div>
              <?php endif; ?>
            </div>
            <div class="col-12">
              <label class="form-label">متن صفحه نهم</label>
              <textarea name="ninth_page_text" class="form-control" rows="6" placeholder="متن آزاد..."><?= htmlspecialchars($values['ninth_page_text'], ENT_QUOTES, 'UTF-8'); ?></textarea>
              <div class="mt-2">
                <label class="form-label">چیدمان متن</label>
                <select name="ninth_page_text_align" class="form-select" style="max-width:220px;">
                  <?php $opts = ['right' => 'راست‌چین', 'center' => 'وسط‌چین', 'justify' => 'کشیده (Justify)', 'left' => 'چپ‌چین']; $v = $values['ninth_page_text_align'] ?? ''; foreach ($opts as $k=>$lbl): ?>
                  <option value="<?= htmlspecialchars($k, ENT_QUOTES, 'UTF-8'); ?>" <?= ($v===$k?'selected':''); ?>><?= htmlspecialchars($lbl, ENT_QUOTES, 'UTF-8'); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-12">
              <label class="form-label">ردیف‌های جدول (امتیاز - توضیحات - نتیجه)</label>
              <?php $rows9 = json_decode((string)$values['ninth_page_items_json'] ?: '[]', true) ?: []; ?>
              <div class="table-responsive">
                <table class="table table-bordered align-middle">
                  <thead>
                    <tr>
                      <th style="width:15%">امتیاز</th>
                      <th style="width:55%">توضیحات</th>
                      <th style="width:30%">نتیجه</th>
                    </tr>
                  </thead>
                  <tbody id="np-items-body">
                    <?php if (empty($rows9)): ?>
                      <tr>
                        <td><input type="text" name="ninth_page_items_score[]" class="form-control" placeholder="مثلاً: 85" /></td>
                        <td><input type="text" name="ninth_page_items_description[]" class="form-control" placeholder="توضیح..." /></td>
                        <td><input type="text" name="ninth_page_items_result[]" class="form-control" placeholder="نتیجه..." /></td>
                      </tr>
                    <?php else: ?>
                      <?php foreach ($rows9 as $r): ?>
                        <tr>
                          <td><input type="text" name="ninth_page_items_score[]" class="form-control" value="<?= htmlspecialchars((string)($r['score'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" /></td>
                          <td><input type="text" name="ninth_page_items_description[]" class="form-control" value="<?= htmlspecialchars((string)($r['description'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" /></td>
                          <td><input type="text" name="ninth_page_items_result[]" class="form-control" value="<?= htmlspecialchars((string)($r['result'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" /></td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
              <button type="button" class="btn btn-sm btn-outline-primary" onclick="addNpRow()">افزودن ردیف</button>
              <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeNpRow()">حذف آخرین ردیف</button>
            </div>

            <div class="col-12 mt-4">
              <hr>
              <h5 class="mt-2 mb-12">صفحه دهم (معرفی MBTI)</h5>
            </div>
            <div class="col-md-4">
              <label class="form-label">فعال‌سازی صفحه دهم</label>
              <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" name="enable_tenth_page" id="enable_tenth_page" <?= $values['enable_tenth_page'] ? 'checked' : ''; ?> />
                <label class="form-check-label" for="enable_tenth_page">فعال</label>
              </div>
            </div>
            <div class="col-md-8">
              <label class="form-label">عنوان روی روبان (صفحه دهم)</label>
              <input type="text" name="tenth_page_title_ribbon_text" class="form-control" value="<?= htmlspecialchars($values['tenth_page_title_ribbon_text'], ENT_QUOTES, 'UTF-8'); ?>" />
              <?php if (!empty($validationErrors['tenth_page_title_ribbon_text'])): ?>
                <div class="text-danger small mt-2"><?= htmlspecialchars((string)$validationErrors['tenth_page_title_ribbon_text'], ENT_QUOTES, 'UTF-8'); ?></div>
              <?php endif; ?>
            </div>
            <div class="col-12">
              <label class="form-label">متن صفحه دهم</label>
              <textarea name="tenth_page_text" class="form-control" rows="6" placeholder="توضیحات کلی درباره MBTI..."><?= htmlspecialchars($values['tenth_page_text'], ENT_QUOTES, 'UTF-8'); ?></textarea>
              <div class="mt-2">
                <label class="form-label">چیدمان متن</label>
                <select name="tenth_page_text_align" class="form-select" style="max-width:220px;">
                  <?php $opts = ['right' => 'راست‌چین', 'center' => 'وسط‌چین', 'justify' => 'کشیده (Justify)', 'left' => 'چپ‌چین']; $v = $values['tenth_page_text_align'] ?? ''; foreach ($opts as $k=>$lbl): ?>
                  <option value="<?= htmlspecialchars($k, ENT_QUOTES, 'UTF-8'); ?>" <?= ($v===$k?'selected':''); ?>><?= htmlspecialchars($lbl, ENT_QUOTES, 'UTF-8'); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="col-12 mt-4">
              <hr>
              <h5 class="mt-2 mb-12">صفحه یازدهم (نتایج MBTI)</h5>
            </div>
            <div class="col-md-4">
              <label class="form-label">فعال‌سازی صفحه یازدهم</label>
              <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" name="enable_eleventh_page" id="enable_eleventh_page" <?= $values['enable_eleventh_page'] ? 'checked' : ''; ?> />
                <label class="form-check-label" for="enable_eleventh_page">فعال</label>
              </div>
            </div>
            <div class="col-md-8">
              <label class="form-label">عنوان روی روبان (صفحه یازدهم)</label>
              <input type="text" name="eleventh_page_title_ribbon_text" class="form-control" value="<?= htmlspecialchars($values['eleventh_page_title_ribbon_text'], ENT_QUOTES, 'UTF-8'); ?>" />
              <?php if (!empty($validationErrors['eleventh_page_title_ribbon_text'])): ?>
                <div class="text-danger small mt-2"><?= htmlspecialchars((string)$validationErrors['eleventh_page_title_ribbon_text'], ENT_QUOTES, 'UTF-8'); ?></div>
              <?php endif; ?>
            </div>
            <div class="col-12">
              <label class="form-label">متن صفحه یازدهم</label>
              <textarea name="eleventh_page_text" class="form-control" rows="6" placeholder="جمع‌بندی و توضیح نتایج MBTI..."><?= htmlspecialchars($values['eleventh_page_text'], ENT_QUOTES, 'UTF-8'); ?></textarea>
              <div class="mt-2">
                <label class="form-label">چیدمان متن</label>
                <select name="eleventh_page_text_align" class="form-select" style="max-width:220px;">
                  <?php $opts = ['right' => 'راست‌چین', 'center' => 'وسط‌چین', 'justify' => 'کشیده (Justify)', 'left' => 'چپ‌چین']; $v = $values['eleventh_page_text_align'] ?? ''; foreach ($opts as $k=>$lbl): ?>
                  <option value="<?= htmlspecialchars($k, ENT_QUOTES, 'UTF-8'); ?>" <?= ($v===$k?'selected':''); ?>><?= htmlspecialchars($lbl, ENT_QUOTES, 'UTF-8'); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="col-12 mt-4">
              <hr>
              <h5 class="mt-2 mb-12">صفحه سیزدهم (نتایج DISC)</h5>
            </div>
            <div class="col-md-4">
              <label class="form-label">فعال‌سازی صفحه سیزدهم</label>
              <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" name="enable_thirteenth_page" id="enable_thirteenth_page" <?= $values['enable_thirteenth_page'] ? 'checked' : ''; ?> />
                <label class="form-check-label" for="enable_thirteenth_page">فعال</label>
              </div>
            </div>
            <div class="col-md-8">
              <label class="form-label">عنوان روی روبان (صفحه سیزدهم)</label>
              <input type="text" name="thirteenth_page_title_ribbon_text" class="form-control" value="<?= htmlspecialchars($values['thirteenth_page_title_ribbon_text'], ENT_QUOTES, 'UTF-8'); ?>" />
              <?php if (!empty($validationErrors['thirteenth_page_title_ribbon_text'])): ?>
                <div class="text-danger small mt-2"><?= htmlspecialchars((string)$validationErrors['thirteenth_page_title_ribbon_text'], ENT_QUOTES, 'UTF-8'); ?></div>
              <?php endif; ?>
            </div>
            <div class="col-12">
              <label class="form-label">متن صفحه سیزدهم</label>
              <textarea name="thirteenth_page_text" class="form-control" rows="6" placeholder="توضیحات درباره نتایج DISC..."><?= htmlspecialchars($values['thirteenth_page_text'], ENT_QUOTES, 'UTF-8'); ?></textarea>
              <div class="mt-2">
                <label class="form-label">چیدمان متن</label>
                <select name="thirteenth_page_text_align" class="form-select" style="max-width:220px;">
                  <?php $opts = ['right' => 'راست‌چین', 'center' => 'وسط‌چین', 'justify' => 'کشیده (Justify)', 'left' => 'چپ‌چین']; $v = $values['thirteenth_page_text_align'] ?? ''; foreach ($opts as $k=>$lbl): ?>
                  <option value="<?= htmlspecialchars($k, ENT_QUOTES, 'UTF-8'); ?>" <?= ($v===$k?'selected':''); ?>><?= htmlspecialchars($lbl, ENT_QUOTES, 'UTF-8'); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="col-12 mt-4">
              <hr>
              <h5 class="mt-2 mb-12">صفحه پانزدهم (تفکر تحلیلی)</h5>
            </div>
            <div class="col-md-4">
              <label class="form-label">فعال‌سازی صفحه پانزدهم</label>
              <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" name="enable_fifteenth_page" id="enable_fifteenth_page" <?= $values['enable_fifteenth_page'] ? 'checked' : ''; ?> />
                <label class="form-check-label" for="enable_fifteenth_page">فعال</label>
              </div>
            </div>
            <div class="col-md-8">
              <label class="form-label">عنوان روی روبان (صفحه پانزدهم)</label>
              <input type="text" name="fifteenth_page_title_ribbon_text" class="form-control" value="<?= htmlspecialchars($values['fifteenth_page_title_ribbon_text'], ENT_QUOTES, 'UTF-8'); ?>" />
              <?php if (!empty($validationErrors['fifteenth_page_title_ribbon_text'])): ?>
                <div class="text-danger small mt-2"><?= htmlspecialchars((string)$validationErrors['fifteenth_page_title_ribbon_text'], ENT_QUOTES, 'UTF-8'); ?></div>
              <?php endif; ?>
            </div>
            <div class="col-12">
              <label class="form-label">متن صفحه پانزدهم</label>
              <textarea name="fifteenth_page_text" class="form-control" rows="6" placeholder="توضیحات درباره نتایج تفکر تحلیلی..."><?= htmlspecialchars($values['fifteenth_page_text'], ENT_QUOTES, 'UTF-8'); ?></textarea>
              <div class="mt-2">
                <label class="form-label">چیدمان متن</label>
                <select name="fifteenth_page_text_align" class="form-select" style="max-width:220px;">
                  <?php $opts = ['right' => 'راست‌چین', 'center' => 'وسط‌چین', 'justify' => 'کشیده (Justify)', 'left' => 'چپ‌چین']; $v = $values['fifteenth_page_text_align'] ?? ''; foreach ($opts as $k=>$lbl): ?>
                  <option value="<?= htmlspecialchars($k, ENT_QUOTES, 'UTF-8'); ?>" <?= ($v===$k?'selected':''); ?>><?= htmlspecialchars($lbl, ENT_QUOTES, 'UTF-8'); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>

          <div class="mt-24 d-flex gap-12">
            <button type="submit" class="btn btn-main rounded-pill px-24">ذخیره تنظیمات</button>
            <a href="<?= UtilityHelper::baseUrl('organizations/reports/self-assessment'); ?>" class="btn btn-outline-secondary rounded-pill px-24">بازگشت</a>
          </div>
        </form>
      </div>
    </div>

    <?php include __DIR__ . '/../../layouts/organization-footer.php'; ?>
  </div>
</div>

<script>
  function addTpRow(){
    const tbody = document.getElementById('tp-items-body');
    const tr = document.createElement('tr');
    tr.innerHTML = '<td><input type="text" name="third_page_items_title[]" class="form-control" placeholder="مثلاً: نتیجه‌گیری" /></td>'+
                   '<td><input type="text" name="third_page_items_page[]" class="form-control" placeholder="مثلاً: صفحه ۵" /></td>'+
                   '<td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTpRow(this)">حذف</button></td>';
    tbody.appendChild(tr);
  }
  function removeTpRow(btn){
    const tr = btn.closest('tr');
    if (!tr) return;
    const tbody = tr.parentNode;
    tbody.removeChild(tr);
    if (!tbody.children.length) { addTpRow(); }
  }
  function moveOrderItem(btn, dir){
    const item = btn.closest('.border.rounded-3');
    const list = document.getElementById('page-order-list');
    if (!item || !list) return;
    if (dir < 0 && item.previousElementSibling) {
      list.insertBefore(item, item.previousElementSibling);
    } else if (dir > 0 && item.nextElementSibling) {
      list.insertBefore(item.nextElementSibling, item);
    }
    // ترتیب جدید را در ورودی‌های hidden حفظ می‌کنیم (قبلاً وجود دارند)
  }

  function addNpRow(){
    const tbody = document.getElementById('np-items-body');
    const tr = document.createElement('tr');
    tr.innerHTML = '<td><input type="text" name="ninth_page_items_score[]" class="form-control" placeholder="مثلاً: 85" /></td>'+
                   '<td><input type="text" name="ninth_page_items_description[]" class="form-control" placeholder="توضیح..." /></td>'+
                   '<td><input type="text" name="ninth_page_items_result[]" class="form-control" placeholder="نتیجه..." /></td>';
    tbody.appendChild(tr);
  }
  function removeNpRow(){
    const tbody = document.getElementById('np-items-body');
    if (tbody && tbody.lastElementChild) {
      tbody.removeChild(tbody.lastElementChild);
      if (!tbody.children.length) { addNpRow(); }
    }
  }
</script>
