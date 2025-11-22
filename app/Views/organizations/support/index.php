<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'پشتیبانی سازمان';
$contactEntries = is_array($contactEntries ?? null) ? $contactEntries : [];
$availableDayOptions = $availableDayOptions ?? [];
$contactChannels = $contactChannels ?? [];
$priorityOptions = $priorityOptions ?? [];
$channelStats = is_array($channelStats ?? null) ? $channelStats : [];
$supportHighlights = $supportHighlights ?? ['total' => 0, 'active' => 0, 'inactive' => 0, 'remote' => 0];
$latestUpdate = $latestUpdate ?? null;

AuthHelper::startSession();
$navbarUser = $user ?? AuthHelper::getUser();

include __DIR__ . '/../layouts/organization-header.php';
include __DIR__ . '/../layouts/organization-sidebar.php';
include __DIR__ . '/../layouts/organization-navbar.php';

$lastUpdatedLabel = $latestUpdate !== null
    ? UtilityHelper::englishToPersian(date('Y/m/d H:i', $latestUpdate))
    : null;
?>

<div class="page-content-wrapper">
    <div class="page-content">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h2 class="mb-1">مرکز پشتیبانی</h2>
                <p class="text-muted mb-0">به‌روزشده‌ترین اطلاعات تماس تیم‌های پشتیبان سازمان شما.</p>
            </div>
            <div class="text-end">
                <div class="small text-muted">آخرین بروزرسانی:
                    <?php if ($lastUpdatedLabel): ?>
                        <?= htmlspecialchars($lastUpdatedLabel, ENT_QUOTES, 'UTF-8'); ?>
                    <?php else: ?>
                        <span class="text-secondary">ثبت نشده</span>
                    <?php endif; ?>
                </div>
                <a href="<?= UtilityHelper::baseUrl('organizations/contact-info'); ?>" class="btn btn-outline-primary btn-sm rounded-pill mt-2">
                    مدیریت کانال‌ها
                </a>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <div class="text-muted small">کل کانال‌ها</div>
                        <div class="fs-3 fw-bold mt-1">
                            <?= htmlspecialchars(UtilityHelper::englishToPersian((string) ($supportHighlights['total'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <div class="text-muted small">کانال‌های فعال</div>
                        <div class="fs-3 fw-bold text-success mt-1">
                            <?= htmlspecialchars(UtilityHelper::englishToPersian((string) ($supportHighlights['active'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <div class="text-muted small">کانال‌های غیرفعال</div>
                        <div class="fs-3 fw-bold text-warning mt-1">
                            <?= htmlspecialchars(UtilityHelper::englishToPersian((string) ($supportHighlights['inactive'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <div class="text-muted small">کانال‌های دورکاری</div>
                        <div class="fs-3 fw-bold text-info mt-1">
                            <?= htmlspecialchars(UtilityHelper::englishToPersian((string) ($supportHighlights['remote'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($channelStats)): ?>
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body">
                    <h3 class="h6 fw-semibold mb-3">کانال‌ها بر اساس نوع</h3>
                    <div class="row g-3">
                        <?php foreach ($channelStats as $channelKey => $stat): ?>
                            <div class="col-12 col-md-6 col-lg-3">
                                <div class="border rounded-4 p-3 h-100">
                                    <div class="text-muted small mb-1"><?= htmlspecialchars($stat['label'] ?? $channelKey, ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="d-flex align-items-baseline gap-2">
                                        <span class="fs-4 fw-bold">
                                            <?= htmlspecialchars(UtilityHelper::englishToPersian((string) ($stat['count'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                        <span class="text-success small">
                                            <?= htmlspecialchars(UtilityHelper::englishToPersian((string) ($stat['active'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?> فعال
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="h5 mb-0">لیست کانال‌های پاسخگو</h3>
                    <span class="text-muted small">نمایش تمامی کانال‌های ثبت شده</span>
                </div>
                <?php if (empty($contactEntries)): ?>
                    <div class="border border-dashed rounded-4 p-4 text-center text-muted">
                        <ion-icon name="chatbubbles-outline" style="font-size:48px;"></ion-icon>
                        <p class="mt-3 mb-1">اطلاعات پشتیبانی برای این سازمان ثبت نشده است.</p>
                        <small>
                            از بخش «اطلاعات تماس» می‌توانید واحدهای پشتیبان را ثبت کنید.
                        </small>
                    </div>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($contactEntries as $entry): ?>
                            <?php
                                $channelKey = $entry['support_channel'] ?? 'phone';
                                $channelLabel = $contactChannels[$channelKey] ?? 'نامشخص';
                                $priorityLabel = $priorityOptions[$entry['priority_level'] ?? 'normal'] ?? 'اولویت نامشخص';
                                $days = [];
                                foreach (($entry['available_days'] ?? []) as $dayKey) {
                                    $days[] = $availableDayOptions[$dayKey] ?? $dayKey;
                                }
                                $daysText = !empty($days) ? implode('، ', $days) : 'روز مشخص نشده';
                                $responseWindow = !empty($entry['response_hours'])
                                    ? $entry['response_hours']
                                    : (!empty($entry['response_start']) && !empty($entry['response_end'])
                                        ? $entry['response_start'] . ' تا ' . $entry['response_end']
                                        : '-');
                            ?>
                            <div class="col-12 col-md-6 col-xl-4">
                                <div class="h-100 border rounded-4 p-3 <?= empty($entry['is_active']) ? 'bg-light-subtle' : ''; ?>">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <div class="fw-bold"><?= htmlspecialchars($entry['team_name'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></div>
                                            <div class="text-muted small">اولویت: <?= htmlspecialchars($priorityLabel, ENT_QUOTES, 'UTF-8'); ?></div>
                                        </div>
                                        <span class="badge <?= !empty($entry['is_active']) ? 'bg-success' : 'bg-secondary'; ?> rounded-pill">
                                            <?= !empty($entry['is_active']) ? 'فعال' : 'غیرفعال'; ?>
                                        </span>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2 mb-3">
                                        <span class="badge bg-primary-subtle text-primary rounded-pill"><?= htmlspecialchars($channelLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php if (!empty($entry['is_remote'])): ?>
                                            <span class="badge bg-info-subtle text-info rounded-pill">پاسخگویی دورکاری</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mb-2">
                                        <div class="small text-muted">ساعات پاسخ‌گویی</div>
                                        <strong><?= htmlspecialchars(UtilityHelper::englishToPersian($responseWindow), ENT_QUOTES, 'UTF-8'); ?></strong>
                                    </div>
                                    <div class="mb-2">
                                        <div class="small text-muted">روزهای فعال</div>
                                        <span><?= htmlspecialchars($daysText, ENT_QUOTES, 'UTF-8'); ?></span>
                                    </div>
                                    <?php if (!empty($entry['contact_phone'])): ?>
                                        <div class="mb-2">
                                            <div class="small text-muted">شماره تماس</div>
                                            <strong><?= htmlspecialchars($entry['contact_phone'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($entry['contact_email'])): ?>
                                        <div class="mb-2">
                                            <div class="small text-muted">ایمیل / سامانه</div>
                                            <strong><?= htmlspecialchars($entry['contact_email'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($entry['support_region'])): ?>
                                        <div class="mb-2">
                                            <div class="small text-muted">منطقه پوشش</div>
                                            <span><?= htmlspecialchars($entry['support_region'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($entry['notes'])): ?>
                                        <div class="mb-2">
                                            <div class="small text-muted">یادداشت</div>
                                            <p class="mb-0"><?= nl2br(htmlspecialchars($entry['notes'], ENT_QUOTES, 'UTF-8')); ?></p>
                                        </div>
                                    <?php endif; ?>
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
