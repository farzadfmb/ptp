<?php
$title = 'امنیت و لاگ‌ها';
$user = (class_exists('AuthHelper') && AuthHelper::getUser()) ? AuthHelper::getUser() : ['name' => 'مدیر سیستم'];
$additional_js = [];

include __DIR__ . '/../../layouts/admin-header.php';
include __DIR__ . '/../../layouts/admin-sidebar.php';
?>

<div class="dashboard-main-wrapper">
    <?php include __DIR__ . '/../../layouts/admin-navbar.php'; ?>

    <div class="dashboard-body">
        <div class="row gy-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-end">
                        <h3 class="mb-4">مرکز امنیت و لاگ‌ها</h3>
                        <p class="text-gray-600 mb-0">
                            تمامی فعالیت‌های سیستمی، خطاها و رویدادهای امنیتی در این بخش ثبت و قابل بررسی هستند. با استفاده از فیلترها می‌توانید به سرعت رخداد موردنظر را پیدا کنید.
                        </p>
                        <div class="mt-12 d-flex flex-wrap gap-12">
                            <span class="badge bg-main-50 text-main-600 rounded-pill px-16 py-8">تعداد کل رویدادها: <?= UtilityHelper::englishToPersian($stats['total'] ?? 0); ?></span>
                            <span class="badge bg-success-50 text-success-700 rounded-pill px-16 py-8">امروز: <?= UtilityHelper::englishToPersian($stats['today'] ?? 0); ?></span>
                            <span class="badge bg-danger-50 text-danger-700 rounded-pill px-16 py-8">خطاها و بحرانی‌ها: <?= UtilityHelper::englishToPersian($stats['errors'] ?? 0); ?></span>
                            <span class="badge bg-indigo-50 text-indigo-700 rounded-pill px-16 py-8">کاربران فعال: <?= UtilityHelper::englishToPersian($stats['unique_users'] ?? 0); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($successMessage)): ?>
                <div class="col-12">
                    <div class="alert alert-success text-end mb-0" role="alert">
                        <i class="fas fa-check-circle ms-6"></i>
                        <?= $successMessage; ?>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (!empty($warningMessage)): ?>
                <div class="col-12">
                    <div class="alert alert-warning text-end mb-0" role="alert">
                        <i class="fas fa-info-circle ms-6"></i>
                        <?= $warningMessage; ?>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (!empty($infoMessage)): ?>
                <div class="col-12">
                    <div class="alert alert-info text-end mb-0" role="alert">
                        <i class="fas fa-lightbulb ms-6"></i>
                        <?= $infoMessage; ?>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (!empty($errorMessage)): ?>
                <div class="col-12">
                    <div class="alert alert-danger text-end mb-0" role="alert">
                        <i class="fas fa-exclamation-triangle ms-6"></i>
                        <?= $errorMessage; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent d-flex flex-wrap align-items-center justify-content-between gap-12">
                        <h5 class="mb-0">فیلتر رویدادها</h5>
                        <a href="<?= UtilityHelper::baseUrl('supperadmin/settings/security'); ?>" class="btn btn-light rounded-pill px-16">
                            <i class="fas fa-redo ms-6"></i>
                            بازنشانی
                        </a>
                    </div>
                    <div class="card-body">
                        <form method="get" class="row g-12" action="<?= UtilityHelper::baseUrl('supperadmin/settings/security'); ?>">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold text-end d-block">سطح رویداد</label>
                                <select name="level" class="form-select">
                                    <option value="">همه سطوح</option>
                                    <?php foreach ($levels as $key => $label): ?>
                                        <option value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?>" <?= $key === $levelFilter ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold text-end d-block">نوع درخواست</label>
                                <select name="method" class="form-select">
                                    <option value="">همه درخواست‌ها</option>
                                    <?php foreach (['GET', 'POST', 'PUT', 'PATCH', 'DELETE'] as $httpMethod): ?>
                                        <option value="<?= $httpMethod; ?>" <?= $httpMethod === $methodFilter ? 'selected' : ''; ?>><?= $httpMethod; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-end d-block">کلیدواژه</label>
                                <input type="text" name="keyword" class="form-control" value="<?= htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8'); ?>" placeholder="جستجو بر اساس مسیر، کاربر، آی‌پی یا نوع رویداد">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-main rounded-pill w-100">
                                    <i class="fas fa-search ms-6"></i>
                                    اعمال فیلتر
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent d-flex flex-wrap align-items-center justify-content-between gap-12">
                        <h5 class="mb-0">رویدادهای سیستمی ثبت‌شده</h5>
                        <span class="text-gray-500">صفحه <?= UtilityHelper::englishToPersian($logsPagination['current_page'] ?? 1); ?> از <?= UtilityHelper::englishToPersian($logsPagination['last_page'] ?? 1); ?></span>
                    </div>
                    <div class="table-responsive">
                        <table class="table text-end align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>زمان</th>
                                    <th>کاربر</th>
                                    <th>نوع</th>
                                    <th>مسیر/عملیات</th>
                                    <th>جزئیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($logsPagination['data'])): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-32 text-gray-500">هیچ لاگی مطابق با فیلترهای اعمال‌شده یافت نشد.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($logsPagination['data'] as $log): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-semibold text-gray-900"><?= htmlspecialchars($log['created_at_formatted'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                    <small class="text-gray-500"><?= htmlspecialchars($log['created_at_relative'], ENT_QUOTES, 'UTF-8'); ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-semibold text-gray-900"><?= htmlspecialchars($log['user_name'] ?? '---', ENT_QUOTES, 'UTF-8'); ?></span>
                                                    <small class="text-gray-500">ID: <?= UtilityHelper::englishToPersian($log['user_id'] ?? '---'); ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <?php
                                                $levelClass = 'bg-main-25 text-main-700';
                                                if (in_array($log['level'], ['error', 'critical'], true)) {
                                                    $levelClass = 'bg-danger-100 text-danger-700';
                                                } elseif ($log['level'] === 'warning') {
                                                    $levelClass = 'bg-warning-100 text-warning-800';
                                                }
                                                ?>
                                                <span class="badge rounded-pill <?= $levelClass; ?>">
                                                    <?= htmlspecialchars($log['level_label'], ENT_QUOTES, 'UTF-8'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-semibold text-gray-900"><?= htmlspecialchars($log['action'] ?? '---', ENT_QUOTES, 'UTF-8'); ?></span>
                                                    <small class="text-gray-500">مسیر: <?= htmlspecialchars($log['request_path'] ?? '---', ENT_QUOTES, 'UTF-8'); ?> | روش: <?= htmlspecialchars($log['request_method'] ?? '---', ENT_QUOTES, 'UTF-8'); ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if (empty($log['context_array'])): ?>
                                                    <span class="text-gray-400">-</span>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-outline-secondary rounded-pill px-16" type="button" data-bs-toggle="collapse" data-bs-target="#logContext<?= (int) $log['id']; ?>" aria-expanded="false" aria-controls="logContext<?= (int) $log['id']; ?>">
                                                        مشاهده جزئیات
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php if (!empty($log['context_array'])): ?>
                                            <tr class="table-active">
                                                <td colspan="5" class="p-0">
                                                    <div id="logContext<?= (int) $log['id']; ?>" class="collapse">
                                                        <div class="p-16 bg-gray-50">
                                                            <div class="row g-12">
                                                                <?php foreach ($log['context_array'] as $contextKey => $contextValue): ?>
                                                                    <div class="col-md-3">
                                                                        <div class="bg-white border rounded-12 px-12 py-10 h-100">
                                                                            <strong class="d-block text-gray-700 mb-4"><?= htmlspecialchars((string) $contextKey, ENT_QUOTES, 'UTF-8'); ?></strong>
                                                                            <pre class="mb-0 text-gray-600 small" dir="ltr" style="white-space: pre-wrap; word-break: break-word;">
<?= htmlspecialchars(is_scalar($contextValue) ? (string) $contextValue : json_encode($contextValue, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), ENT_QUOTES, 'UTF-8'); ?>
                                                                            </pre>
                                                                        </div>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if (($logsPagination['last_page'] ?? 1) > 1): ?>
                        <div class="card-footer bg-transparent d-flex justify-content-between align-items-center">
                            <?php
                            $currentPage = $logsPagination['current_page'];
                            $lastPage = $logsPagination['last_page'];
                            $queryString = $_GET;
                            ?>
                            <a class="btn btn-light rounded-pill px-16 <?= $currentPage <= 1 ? 'disabled' : ''; ?>" href="<?= $currentPage <= 1 ? 'javascript:void(0);' : UtilityHelper::baseUrl('supperadmin/settings/security?' . http_build_query(array_merge($queryString, ['page' => $currentPage - 1]))); ?>">
                                <i class="fas fa-chevron-right ms-6"></i>
                                صفحه قبل
                            </a>
                            <span class="text-gray-500">نمایش <?= UtilityHelper::englishToPersian($logsPagination['from'] ?? 0); ?> تا <?= UtilityHelper::englishToPersian($logsPagination['to'] ?? 0); ?> از <?= UtilityHelper::englishToPersian($logsPagination['total'] ?? 0); ?> رکورد</span>
                            <a class="btn btn-light rounded-pill px-16 <?= $currentPage >= $lastPage ? 'disabled' : ''; ?>" href="<?= $currentPage >= $lastPage ? 'javascript:void(0);' : UtilityHelper::baseUrl('supperadmin/settings/security?' . http_build_query(array_merge($queryString, ['page' => $currentPage + 1]))); ?>">
                                صفحه بعد
                                <i class="fas fa-chevron-left me-6"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-12 col-xl-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">رویدادهای امنیتی فایل لاگ</h5>
                        <span class="badge bg-indigo-50 text-indigo-700">آخرین <?= UtilityHelper::englishToPersian(count($recentSecurityEvents)); ?> مورد</span>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentSecurityEvents)): ?>
                            <p class="text-gray-500 text-end mb-0">هیچ رویداد امنیتی ثبت نشده است.</p>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($recentSecurityEvents as $event): ?>
                                    <div class="list-group-item py-12">
                                        <div class="d-flex align-items-center justify-content-between gap-12">
                                            <div class="text-end">
                                                <strong class="d-block text-gray-900"><?= htmlspecialchars($event['event'] ?? '---', ENT_QUOTES, 'UTF-8'); ?></strong>
                                                <small class="text-gray-500">کاربر: <?= UtilityHelper::englishToPersian($event['user_id'] ?? '---'); ?> | آی‌پی: <?= htmlspecialchars($event['ip'] ?? '---', ENT_QUOTES, 'UTF-8'); ?></small>
                                            </div>
                                            <div class="text-start text-gray-500">
                                                <span class="d-block"><?= htmlspecialchars($event['timestamp_formatted'] ?? '---', ENT_QUOTES, 'UTF-8'); ?></span>
                                                <small><?= htmlspecialchars($event['timestamp_relative'] ?? '', ENT_QUOTES, 'UTF-8'); ?></small>
                                            </div>
                                        </div>
                                        <?php if (!empty($event['details'])): ?>
                                            <pre class="bg-gray-50 border rounded-12 px-12 py-10 mt-10 mb-0 small" dir="ltr" style="white-space: pre-wrap; word-break: break-word;">
<?= htmlspecialchars(json_encode($event['details'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), ENT_QUOTES, 'UTF-8'); ?>
                                            </pre>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">لاگ‌های ذخیره‌شده در حالت اضطراری</h5>
                        <span class="badge bg-gray-100 text-gray-700">تا <?= UtilityHelper::englishToPersian(count($fallbackEvents)); ?> مورد</span>
                    </div>
                    <div class="card-body">
                        <?php if (empty($fallbackEvents)): ?>
                            <p class="text-gray-500 text-end mb-0">هیچ رویدادی در فایل اضطراری ثبت نشده است.</p>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($fallbackEvents as $event): ?>
                                    <div class="list-group-item py-12">
                                        <div class="d-flex align-items-center justify-content-between gap-12">
                                            <div class="text-end">
                                                <strong class="d-block text-gray-900">سطح: <?= htmlspecialchars($event['level'] ?? '---', ENT_QUOTES, 'UTF-8'); ?></strong>
                                                <small class="text-gray-500">عملیات: <?= htmlspecialchars($event['action'] ?? '---', ENT_QUOTES, 'UTF-8'); ?></small>
                                            </div>
                                            <div class="text-start text-gray-500">
                                                <span class="d-block"><?= htmlspecialchars($event['timestamp_formatted'] ?? '---', ENT_QUOTES, 'UTF-8'); ?></span>
                                                <small><?= htmlspecialchars($event['timestamp_relative'] ?? '', ENT_QUOTES, 'UTF-8'); ?></small>
                                            </div>
                                        </div>
                                        <?php if (!empty($event['context'])): ?>
                                            <pre class="bg-gray-50 border rounded-12 px-12 py-10 mt-10 mb-0 small" dir="ltr" style="white-space: pre-wrap; word-break: break-word;">
<?= htmlspecialchars(json_encode($event['context'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), ENT_QUOTES, 'UTF-8'); ?>
                                            </pre>
                                        <?php endif; ?>
                                        <?php if (!empty($event['error'])): ?>
                                            <small class="d-block text-danger-600 mt-6">خطا: <?= htmlspecialchars($event['error'], ENT_QUOTES, 'UTF-8'); ?></small>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../../layouts/admin-footer.php'; ?>
</div>
