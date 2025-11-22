<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'اطلاعات تماس';
$contactEntries = is_array($contactEntries ?? null) ? $contactEntries : [];
$availableDayOptions = $availableDayOptions ?? [];
$contactChannels = $contactChannels ?? [];
$priorityOptions = $priorityOptions ?? [];
$validationErrors = $validationErrors ?? [];
$oldInput = $oldInput ?? [];
$responseTimeOptions = $responseTimeOptions ?? [];
$totalContactCount = isset($totalContactCount) ? (int) $totalContactCount : count($contactEntries);
$activeContactCount = isset($activeContactCount) ? (int) $activeContactCount : 0;
$csrfToken = $csrfToken ?? AuthHelper::generateCsrfToken();
$editingEntryId = isset($editingEntryId) ? (int) $editingEntryId : 0;
$editingEntry = $editingEntry ?? null;
$isEditing = $editingEntryId > 0;
$inline_styles = ($inline_styles ?? '') . <<<'CSS'
    .contact-form-card {
        border-radius: 24px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 18px 36px rgba(15, 23, 42, 0.06);
    }
    .contact-list-card {
        border-radius: 24px;
        border: 1px solid #edf2f7;
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.04);
    }
    .contact-entry {
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 20px;
        background: #fff;
        transition: all 0.2s ease;
        height: 100%;
    }
    .contact-entry.is-inactive {
        opacity: 0.7;
        background: linear-gradient(135deg, #f8fafc, #fff);
    }
    .contact-entry:hover {
        border-color: #cbd5f5;
        box-shadow: 0 10px 20px rgba(15, 23, 42, 0.08);
    }
    .contact-entry-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #0f172a;
    }
    .contact-entry-meta {
        font-size: 0.92rem;
        color: #475569;
    }
    .contact-days-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 999px;
        background: rgba(59, 130, 246, 0.08);
        color: #1d4ed8;
        font-size: 0.82rem;
        margin-left: 6px;
    }
    .contact-channel-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 0.78rem;
    }
    .contact-channel-badge.phone { background: rgba(34, 197, 94, 0.12); color: #15803d; }
    .contact-channel-badge.mobile { background: rgba(20, 184, 166, 0.12); color: #0f766e; }
    .contact-channel-badge.email { background: rgba(59, 130, 246, 0.12); color: #1d4ed8; }
    .contact-channel-badge.ticket { background: rgba(249, 115, 22, 0.12); color: #c2410c; }
    .contact-channel-badge.inperson { background: rgba(139, 92, 246, 0.12); color: #6d28d9; }
    .contact-empty-state {
        border: 2px dashed #dbeafe;
        border-radius: 24px;
        padding: 36px;
        text-align: center;
        background: #f8fbff;
    }
    .contact-empty-state ion-icon {
        font-size: 48px;
        color: #93c5fd;
    }
    .day-selector .form-check {
        width: calc(33% - 8px);
        margin-bottom: 8px;
    }
    @media (max-width: 768px) {
        .day-selector .form-check {
            width: 100%;
        }
    }
CSS;

AuthHelper::startSession();
$navbarUser = $user ?? AuthHelper::getUser();

include __DIR__ . '/../layouts/organization-header.php';
include __DIR__ . '/../layouts/organization-sidebar.php';
include __DIR__ . '/../layouts/organization-navbar.php';

$flashMessages = ResponseHelper::getFlash();
$activeCount = $activeContactCount;
$totalEntries = $totalContactCount;
$latestUpdate = null;
foreach ($contactEntries as $entry) {
    if (!empty($entry['created_at'])) {
        $timestamp = strtotime($entry['created_at']);
        if ($timestamp && ($latestUpdate === null || $timestamp > $latestUpdate)) {
            $latestUpdate = $timestamp;
        }
    }
}
$visibleEntries = $contactEntries;
?>

<div class="page-content-wrapper">
    <div class="page-content">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h2 class="mb-1">اطلاعات تماس و پشتیبانی</h2>
                <p class="text-muted mb-0">واحدهای پاسخ‌گو، زمان‌بندی ارائه خدمات و کانال‌های ارتباطی خود را ثبت و مدیریت کنید.</p>
            </div>
            <div class="text-end">
                <div class="small text-muted">آخرین بروزرسانی:
                    <?php if ($latestUpdate !== null): ?>
                        <?= htmlspecialchars(UtilityHelper::englishToPersian(date('Y/m/d H:i', $latestUpdate)), ENT_QUOTES, 'UTF-8'); ?>
                    <?php else: ?>
                        <span class="text-secondary">ثبت نشده</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if (!empty($flashMessages)): ?>
            <?php foreach ($flashMessages as $type => $message): ?>
                <div class="alert alert-<?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8'); ?> rounded-16 border-0 mb-3">
                    <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <div class="row g-4 mb-4">
            <div class="col-12 col-lg-7">
                <div class="card contact-form-card">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h3 class="h5 fw-semibold mb-1">
                                    <?= $isEditing ? 'ویرایش اطلاعات تماس' : 'ثبت واحد تماس جدید'; ?>
                                </h3>
                                <p class="text-muted mb-0">
                                    <?= $isEditing
                                        ? 'اطلاعات کانال انتخاب شده را به‌روزرسانی کنید و در صورت نیاز تغییرات را ذخیره نمایید.'
                                        : 'برای هر کانال تماس نام واحد، زمان پاسخ‌گویی و وضعیت دسترس‌پذیری را وارد کنید.';
                                    ?>
                                </p>
                            </div>
                            <span class="badge bg-light text-secondary">ثبت در پایگاه داده سازمان</span>
                        </div>
                        <?php if ($isEditing && $editingEntry): ?>
                            <div class="alert alert-warning d-flex justify-content-between align-items-center rounded-3">
                                <div>
                                    در حال ویرایش: <strong><?= htmlspecialchars($editingEntry['team_name'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></strong>
                                </div>
                                <a href="<?= UtilityHelper::baseUrl('organizations/contact-info'); ?>" class="btn btn-sm btn-outline-dark rounded-pill">انصراف از ویرایش</a>
                            </div>
                        <?php endif; ?>
                        <form method="POST" action="<?= UtilityHelper::baseUrl('organizations/contact-info'); ?>" class="needs-validation" novalidate>
                            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="action" value="<?= $isEditing ? 'update' : 'add'; ?>">
                            <?php if ($isEditing): ?>
                                <input type="hidden" name="entry_id" value="<?= htmlspecialchars((string) $editingEntryId, ENT_QUOTES, 'UTF-8'); ?>">
                            <?php endif; ?>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">نام واحد یا تیم پاسخگو <span class="text-danger">*</span></label>
                                    <input type="text" name="team_name" class="form-control" value="<?= htmlspecialchars($oldInput['team_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: تیم فنی، میز خدمت سازمان">
                                    <?php if (!empty($validationErrors['team_name'])): ?>
                                        <div class="text-danger small mt-1"><?= htmlspecialchars($validationErrors['team_name'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">شماره تماس اصلی <span class="text-danger">*</span></label>
                                    <input type="text" name="contact_phone" class="form-control" value="<?= htmlspecialchars($oldInput['contact_phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="021-12345678 یا 0912...">
                                    <?php if (!empty($validationErrors['contact_phone'])): ?>
                                        <div class="text-danger small mt-1"><?= htmlspecialchars($validationErrors['contact_phone'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php else: ?>
                                        <div class="form-text">در صورت نبود تلفن، ایمیل را تکمیل کنید.</div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">ایمیل / صندوق پشتیبانی</label>
                                    <input type="email" name="contact_email" class="form-control" value="<?= htmlspecialchars($oldInput['contact_email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="support@example.com">
                                    <?php if (!empty($validationErrors['contact_email'])): ?>
                                        <div class="text-danger small mt-1"><?= htmlspecialchars($validationErrors['contact_email'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">کانال پاسخ‌گویی <span class="text-danger">*</span></label>
                                    <select name="support_channel" class="form-select">
                                        <?php foreach ($contactChannels as $value => $label): ?>
                                            <option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>" <?= (($oldInput['support_channel'] ?? 'phone') === $value) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (!empty($validationErrors['support_channel'])): ?>
                                        <div class="text-danger small mt-1"><?= htmlspecialchars($validationErrors['support_channel'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">سطح اولویت <span class="text-danger">*</span></label>
                                    <select name="priority_level" class="form-select">
                                        <?php foreach ($priorityOptions as $value => $label): ?>
                                            <option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>" <?= (($oldInput['priority_level'] ?? 'normal') === $value) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (!empty($validationErrors['priority_level'])): ?>
                                        <div class="text-danger small mt-1"><?= htmlspecialchars($validationErrors['priority_level'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">شروع پاسخ‌گویی <span class="text-danger">*</span></label>
                                    <select name="response_start" class="form-select">
                                        <option value="">انتخاب کنید</option>
                                        <?php foreach ($responseTimeOptions as $slot): ?>
                                            <option value="<?= htmlspecialchars($slot, ENT_QUOTES, 'UTF-8'); ?>" <?= (($oldInput['response_start'] ?? '') === $slot) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars(UtilityHelper::englishToPersian($slot), ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">پایان پاسخ‌گویی <span class="text-danger">*</span></label>
                                    <select name="response_end" class="form-select">
                                        <option value="">انتخاب کنید</option>
                                        <?php foreach ($responseTimeOptions as $slot): ?>
                                            <option value="<?= htmlspecialchars($slot, ENT_QUOTES, 'UTF-8'); ?>" <?= (($oldInput['response_end'] ?? '') === $slot) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars(UtilityHelper::englishToPersian($slot), ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (!empty($validationErrors['response_start'])): ?>
                                        <div class="text-danger small mt-1"><?= htmlspecialchars($validationErrors['response_start'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">منطقه پوشش یا ساختمان</label>
                                    <input type="text" name="support_region" class="form-control" value="<?= htmlspecialchars($oldInput['support_region'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: تهران، ساختمان مرکزی">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">روزهای فعال</label>
                                    <div class="d-flex flex-wrap gap-2 day-selector">
                                        <?php foreach ($availableDayOptions as $dayKey => $dayLabel): ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="available_days[]" value="<?= htmlspecialchars($dayKey, ENT_QUOTES, 'UTF-8'); ?>" id="day-<?= htmlspecialchars($dayKey, ENT_QUOTES, 'UTF-8'); ?>" <?= in_array($dayKey, $oldInput['available_days'] ?? [], true) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="day-<?= htmlspecialchars($dayKey, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <?= htmlspecialchars($dayLabel, ENT_QUOTES, 'UTF-8'); ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div class="col-12 d-flex flex-wrap gap-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" <?= !empty($oldInput['is_active']) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_active">این کانال فعال است</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="is_remote" name="is_remote" <?= !empty($oldInput['is_remote']) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_remote">پاسخگویی دورکاری / ۲۴ ساعته</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">یادداشت تکمیلی</label>
                                    <textarea name="notes" rows="3" class="form-control" placeholder="مثال: در ساعات غیراداری پیامک ارسال شود."><?= htmlspecialchars($oldInput['notes'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                </div>
                                <div class="col-12 d-flex flex-wrap gap-2">
                                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                                        <ion-icon name="<?= $isEditing ? 'create-outline' : 'add-circle-outline'; ?>" class="align-middle me-1"></ion-icon>
                                        <?= $isEditing ? 'ذخیره تغییرات' : 'ثبت اطلاعات تماس'; ?>
                                    </button>
                                    <?php if ($isEditing): ?>
                                        <a href="<?= UtilityHelper::baseUrl('organizations/contact-info'); ?>" class="btn btn-outline-secondary rounded-pill px-3">
                                            لغو و بازگشت
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                        <form method="POST" action="<?= UtilityHelper::baseUrl('organizations/contact-info'); ?>" class="mt-3">
                            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="action" value="reset">
                            <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill" onclick="return confirm('آیا از پاک‌سازی همه رکوردها مطمئن هستید؟');">
                                <ion-icon name="trash-outline" class="align-middle me-1"></ion-icon>
                                بازنشانی لیست
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-5">
                <div class="card contact-list-card mb-4">
                    <div class="card-body p-4">
                        <h3 class="h6 fw-semibold mb-3">نمای کلی</h3>
                        <div class="d-flex flex-wrap gap-3">
                            <div class="flex-fill p-3 rounded-3 bg-primary-subtle text-primary">
                                <div class="small text-uppercase text-primary-emphasis">کل کانال‌ها</div>
                                <div class="fs-3 fw-bold mt-1"><?= htmlspecialchars(UtilityHelper::englishToPersian((string) $totalEntries), ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="flex-fill p-3 rounded-3 bg-success-subtle text-success">
                                <div class="small text-uppercase">فعال</div>
                                <div class="fs-3 fw-bold mt-1"><?= htmlspecialchars(UtilityHelper::englishToPersian((string) $activeCount), ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="flex-fill p-3 rounded-3 bg-warning-subtle text-warning">
                                <div class="small text-uppercase">غیرفعال</div>
                                <div class="fs-3 fw-bold mt-1"><?= htmlspecialchars(UtilityHelper::englishToPersian((string) max($totalEntries - $activeCount, 0)), ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                        </div>
                        <hr>
                        <p class="text-muted small mb-1">پیشنهاد: برای هر واحد عملیاتی حداقل یک کانال پاسخ‌گویی فعال ثبت کنید و وضعیت دسترس‌پذیری را به‌روز نگه دارید.</p>
                    </div>
                </div>
                <div class="card contact-list-card">
                    <div class="card-body p-4">
                        <h3 class="h6 fw-semibold mb-3">راهنمای ثبت اطلاعات بهتر</h3>
                        <ul class="ps-3 text-muted small mb-0">
                            <li>برای تماس‌های حیاتی، اولویت «فوری» را انتخاب کنید تا در لیست مشخص باشند.</li>
                            <li>اگر پاسخ‌گویی در ساعات مشخص متوقف می‌شود، آن را در یادداشت درج کنید.</li>
                            <li>برای واحدهای پشتیبانی سراسری، تیک «پاسخگویی دورکاری» را فعال کنید.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="card contact-list-card">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="h5 mb-0">لیست کانال‌های ثبت شده</h3>
                    <span class="text-muted small">نمایش حداکثر ۱۲ کانال اخیر</span>
                </div>
                <?php if (empty($contactEntries)): ?>
                    <div class="contact-empty-state">
                        <ion-icon name="call-outline"></ion-icon>
                        <h4 class="mt-3">هنوز اطلاعاتی ثبت نشده است</h4>
                        <p class="text-muted mb-0">از فرم بالا برای معرفی واحدهای پاسخگو استفاده کنید.</p>
                    </div>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($visibleEntries as $entry): ?>
                            <?php
                                $channelKey = $entry['support_channel'] ?? 'phone';
                                $channelLabel = $contactChannels[$channelKey] ?? 'نامشخص';
                                $days = [];
                                foreach (($entry['available_days'] ?? []) as $dayKey) {
                                    $days[] = $availableDayOptions[$dayKey] ?? $dayKey;
                                }
                                $daysText = !empty($days) ? implode('، ', $days) : 'روز مشخص نشده';
                                $statusClass = !empty($entry['is_active']) ? 'bg-success text-white' : 'bg-secondary text-white';
                                $channelClass = 'contact-channel-badge ' . htmlspecialchars($channelKey, ENT_QUOTES, 'UTF-8');
                                $priority = $priorityOptions[$entry['priority_level'] ?? 'normal'] ?? 'اولویت نامشخص';
                                if (!empty($entry['response_start']) && !empty($entry['response_end'])) {
                                    $responseWindow = UtilityHelper::englishToPersian($entry['response_start']) . ' تا ' . UtilityHelper::englishToPersian($entry['response_end']);
                                } elseif (!empty($entry['response_hours'])) {
                                    $responseWindow = $entry['response_hours'];
                                } else {
                                    $responseWindow = '-';
                                }
                            ?>
                            <div class="col-12 col-md-6 col-xl-4">
                                <div class="contact-entry <?= empty($entry['is_active']) ? 'is-inactive' : ''; ?>">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <div class="contact-entry-title"><?= htmlspecialchars($entry['team_name'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></div>
                                            <div class="contact-entry-meta">اولویت: <?= htmlspecialchars($priority, ENT_QUOTES, 'UTF-8'); ?></div>
                                        </div>
                                        <span class="badge <?= $statusClass; ?> rounded-pill"><?= !empty($entry['is_active']) ? 'فعال' : 'غیرفعال'; ?></span>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2 mb-2">
                                            <span class="<?= $channelClass; ?>">
                                                <ion-icon name="call-outline"></ion-icon>
                                                <?= htmlspecialchars($channelLabel, ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                            <?php if (!empty($entry['is_remote'])): ?>
                                                <span class="badge bg-info-subtle text-info rounded-pill">پاسخگویی سراسری</span>
                                            <?php endif; ?>
                                    </div>
                                    <div class="mb-2">
                                        <div class="small text-muted">شماره تماس</div>
                                        <strong><?= htmlspecialchars($entry['contact_phone'] !== '' ? $entry['contact_phone'] : '---', ENT_QUOTES, 'UTF-8'); ?></strong>
                                    </div>
                                    <?php if (!empty($entry['contact_email'])): ?>
                                        <div class="mb-2">
                                            <div class="small text-muted">ایمیل</div>
                                            <strong><?= htmlspecialchars($entry['contact_email'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                        </div>
                                    <?php endif; ?>
                                    <div class="mb-2">
                                        <div class="small text-muted">ساعات پاسخ‌گویی</div>
                                        <strong><?= htmlspecialchars($responseWindow, ENT_QUOTES, 'UTF-8'); ?></strong>
                                    </div>
                                    <div class="mb-2">
                                        <div class="small text-muted">روزهای فعال</div>
                                        <span class="contact-days-chip"><?= htmlspecialchars($daysText, ENT_QUOTES, 'UTF-8'); ?></span>
                                    </div>
                                    <?php if (!empty($entry['support_region'])): ?>
                                        <div class="mb-2">
                                            <div class="small text-muted">منطقه پوشش</div>
                                            <span><?= htmlspecialchars($entry['support_region'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($entry['notes'])): ?>
                                        <div class="mb-2">
                                            <div class="small text-muted">توضیحات</div>
                                            <p class="mb-0"><?= nl2br(htmlspecialchars($entry['notes'], ENT_QUOTES, 'UTF-8')); ?></p>
                                        </div>
                                    <?php endif; ?>
                                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
                                        <div class="text-muted small">
                                            ثبت شده در <?= htmlspecialchars(UtilityHelper::englishToPersian(date('Y/m/d H:i', strtotime($entry['created_at'] ?? 'now'))), ENT_QUOTES, 'UTF-8'); ?>
                                        </div>
                                        <a href="<?= UtilityHelper::baseUrl('organizations/contact-info?edit=' . (int) ($entry['id'] ?? 0)); ?>" class="btn btn-outline-primary btn-sm rounded-pill">
                                            <ion-icon name="create-outline" class="align-middle me-1"></ion-icon>
                                            ویرایش
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/organization-footer.php'; ?>
