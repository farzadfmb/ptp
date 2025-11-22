<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'مرکز پشتیبانی سازمان';
$contactEntries = is_array($contactEntries ?? null) ? $contactEntries : [];
$latestUpdate = $latestUpdate ?? null;
$organizationName = ($organizationName ?? '') !== '' ? $organizationName : 'سازمان شما';
$inline_styles = ($inline_styles ?? '') . <<<'CSS'
    .support-table thead th {
        white-space: nowrap;
    }
    @media (max-width: 768px) {
        .support-table thead {
            display: none;
        }
        .support-table tbody tr {
            display: block;
            margin-bottom: 16px;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 12px 16px;
            box-shadow: 0 8px 16px rgba(15, 23, 42, 0.08);
        }
        .support-table tbody td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
            border: none;
        }
        .support-table tbody td::before {
            content: attr(data-label);
            font-weight: 600;
            color: #475569;
            margin-left: 12px;
        }
        .support-table tbody td:last-child {
            border-bottom: none;
        }
    }
CSS;

AuthHelper::startSession();
$user = AuthHelper::getUser();
$navbarUser = $user;

include __DIR__ . '/../../layouts/home-header.php';
include __DIR__ . '/../../layouts/home-sidebar.php';
include __DIR__ . '/../../layouts/home-navbar.php';

$lastUpdatedLabel = $latestUpdate !== null
    ? UtilityHelper::englishToPersian(date('Y/m/d H:i', $latestUpdate))
    : null;
?>

<div class="page-content-wrapper">
    <div class="page-content">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                    <div>
                        <h3 class="h5 mb-1">پشتیبانی <?= htmlspecialchars($organizationName, ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p class="text-muted mb-0">در این بخش فقط اطلاعات تماس پشتیبانی سازمان شما نمایش داده می‌شود.</p>
                    </div>
                    <div class="text-muted small">
                        آخرین بروزرسانی:
                        <?php if ($lastUpdatedLabel): ?>
                            <?= htmlspecialchars($lastUpdatedLabel, ENT_QUOTES, 'UTF-8'); ?>
                        <?php else: ?>
                            <span class="text-secondary">ثبت نشده</span>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (empty($contactEntries)): ?>
                    <div class="border border-dashed rounded-4 p-4 text-center text-muted">
                        <ion-icon name="call-outline" style="font-size:48px;"></ion-icon>
                        <p class="mt-3 mb-1">برای این سازمان هنوز اطلاعات پشتیبانی ثبت نشده است.</p>
                        <small>در صورت نیاز با مدیر سازمان خود تماس بگیرید.</small>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle support-table">
                            <thead>
                                <tr>
                                    <th class="text-nowrap">نام پشتیبان</th>
                                    <th class="text-nowrap">شماره تماس</th>
                                    <th class="text-nowrap">ایمیل</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($contactEntries as $entry): ?>
                                    <tr>
                                        <td data-label="نام پشتیبان">
                                            <?= htmlspecialchars($entry['team_name'] ?? '-', ENT_QUOTES, 'UTF-8'); ?>
                                        </td>
                                        <td data-label="شماره تماس">
                                            <?php if (!empty($entry['contact_phone'])): ?>
                                                <?= htmlspecialchars($entry['contact_phone'], ENT_QUOTES, 'UTF-8'); ?>
                                            <?php else: ?>
                                                <span class="text-muted">ثبت نشده</span>
                                            <?php endif; ?>
                                        </td>
                                        <td data-label="ایمیل">
                                            <?php if (!empty($entry['contact_email'])): ?>
                                                <?= htmlspecialchars($entry['contact_email'], ENT_QUOTES, 'UTF-8'); ?>
                                            <?php else: ?>
                                                <span class="text-muted">ثبت نشده</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/home-footer.php'; ?>
