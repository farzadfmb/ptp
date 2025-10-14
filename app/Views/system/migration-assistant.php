<?php
/** @var array $appConfig */
/** @var array $dbConfig */
/** @var array $environmentInfo */
/** @var array $formData */
/** @var array $validationErrors */
/** @var string|null $flashSuccess */
/** @var string|null $flashError */
/** @var string $updateUrl */
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>راهنمای انتقال به هاست</title>
    <style>
        body {
            margin: 0;
            font-family: 'Tahoma', sans-serif;
            background: #f5f6fa;
            color: #1f2933;
            line-height: 1.6;
        }
        .page-wrapper {
            max-width: 980px;
            margin: 0 auto;
            padding: 32px 16px 64px;
        }
        h1, h2 {
            margin: 0 0 16px;
            font-weight: 600;
            color: #1a202c;
        }
        h1 {
            font-size: 28px;
        }
        h2 {
            font-size: 22px;
        }
        p {
            margin: 0 0 12px;
        }
        .card {
            background: #fff;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 12px 32px rgba(15, 23, 42, 0.08);
        }
        .alert {
            border-radius: 12px;
            padding: 16px 18px;
            margin-bottom: 24px;
            font-size: 15px;
        }
        .alert-success {
            background: #e6f4ea;
            border: 1px solid #34a85333;
            color: #0f5132;
        }
        .alert-error {
            background: #fde8e8;
            border: 1px solid #ff4d4f33;
            color: #7f1d1d;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 16px;
        }
        .info-item {
            background: #f1f5f9;
            border-radius: 12px;
            padding: 16px;
            font-size: 14px;
        }
        .info-label {
            display: block;
            color: #475569;
            margin-bottom: 4px;
            font-weight: 600;
        }
        .form-group {
            margin-bottom: 18px;
        }
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #1f2937;
        }
        input[type="text"],
        input[type="password"],
        input[type="url"] {
            width: 100%;
            padding: 12px 14px;
            border-radius: 10px;
            border: 1px solid #cbd5f5;
            font-size: 15px;
            transition: border-color .2s ease;
        }
        input:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }
        .form-text {
            font-size: 13px;
            color: #6b7280;
            margin-top: 6px;
        }
        .error-text {
            font-size: 13px;
            color: #dc2626;
            margin-top: 6px;
            display: block;
        }
        .button-row {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 24px;
        }
        button,
        .btn-secondary {
            border: none;
            border-radius: 999px;
            padding: 12px 28px;
            font-size: 15px;
            cursor: pointer;
            transition: transform .15s ease, box-shadow .15s ease;
        }
        button {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff;
        }
        button:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 22px rgba(99, 102, 241, 0.25);
        }
        .btn-secondary {
            background: #e2e8f0;
            color: #1f2937;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .btn-secondary:hover {
            background: #cbd5f5;
        }
        .warning-box {
            background: #fff7ed;
            border: 1px solid #f9731633;
            border-radius: 12px;
            padding: 18px;
            font-size: 14px;
            color: #9a3412;
        }
        code {
            font-family: 'JetBrains Mono', monospace;
            background: #e2e8f0;
            padding: 2px 6px;
            border-radius: 6px;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <h1>راهنمای تنظیم دامنه و پایگاه داده</h1>
        <p>این صفحه برای انتقال پروژه از محیط محلی به هاست طراحی شده است. مقادیر فعلی برای آدرس سامانه و اتصال پایگاه داده نمایش داده می‌شود و می‌توانید مقادیر جدید را برای سرور مقصد ذخیره کنید.</p>

        <div class="card warning-box">
            <strong>هشدار امنیتی:</strong>
            <p>پس از اتمام انتقال و اطمینان از عملکرد سایت، حتماً این فایل را از روی هاست حذف یا دسترسی به آن را محدود کنید تا شخص دیگری نتواند اطلاعات اتصال پایگاه داده را مشاهده یا تغییر دهد.</p>
        </div>

        <?php if (!empty($flashSuccess)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <?php if (!empty($flashError)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <div class="card">
            <h2>اطلاعات محیط اجرا</h2>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">آدرس تشخیص داده‌شده سایت</span>
                    <span><?= htmlspecialchars($environmentInfo['base_url_detected'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">آدرس تنظیم‌شده در فایل app.php</span>
                    <span><?= htmlspecialchars($environmentInfo['app_url_configured'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">مسیر پروژه</span>
                    <span dir="ltr" style="display:block; word-break: break-all;"><?= htmlspecialchars($environmentInfo['project_root'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">مسیر پوشه public</span>
                    <span dir="ltr" style="display:block; word-break: break-all;"><?= htmlspecialchars($environmentInfo['public_path'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Document Root وب‌سرور</span>
                    <span dir="ltr" style="display:block; word-break: break-all;"><?= htmlspecialchars($environmentInfo['document_root'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">اجرای فعلی در localhost</span>
                    <span><?= !empty($environmentInfo['is_localhost']) ? 'بله' : 'خیر'; ?></span>
                </div>
            </div>
        </div>

        <div class="card">
            <h2>ویرایش آدرس سایت و اتصال پایگاه داده</h2>
            <form action="<?= htmlspecialchars($updateUrl, ENT_QUOTES, 'UTF-8'); ?>" method="post">
                <?= csrf_field(); ?>
                <?php
                    $appUrlValue = $formData['app_url'] ?? ($appConfig['url'] ?? '');
                    $dbHostValue = $formData['db_host'] ?? ($dbConfig['host'] ?? '');
                    $dbNameValue = $formData['db_name'] ?? ($dbConfig['database'] ?? '');
                    $dbUserValue = $formData['db_user'] ?? ($dbConfig['username'] ?? '');
                    $dbPasswordValue = $formData['db_password'] ?? ($dbConfig['password'] ?? '');
                ?>
                <div class="form-group">
                    <label for="app_url">آدرس جدید سایت (Domain or Subdomain)</label>
                    <input type="url" id="app_url" name="app_url" value="<?= htmlspecialchars($appUrlValue, ENT_QUOTES, 'UTF-8'); ?>" placeholder="https://example.com">
                    <span class="form-text">در صورت خالی بودن این فیلد، سامانه به صورت خودکار آدرس را تشخیص می‌دهد.</span>
                    <?php if (!empty($validationErrors['app_url'])): ?>
                        <span class="error-text"><?= htmlspecialchars($validationErrors['app_url'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="db_host">آدرس سرور پایگاه داده</label>
                    <input type="text" id="db_host" name="db_host" value="<?= htmlspecialchars($dbHostValue, ENT_QUOTES, 'UTF-8'); ?>" placeholder="localhost یا آدرس سرور MySQL">
                    <?php if (!empty($validationErrors['db_host'])): ?>
                        <span class="error-text"><?= htmlspecialchars($validationErrors['db_host'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="db_name">نام پایگاه داده</label>
                    <input type="text" id="db_name" name="db_name" value="<?= htmlspecialchars($dbNameValue, ENT_QUOTES, 'UTF-8'); ?>" placeholder="نام دیتابیس روی سرور">
                    <?php if (!empty($validationErrors['db_name'])): ?>
                        <span class="error-text"><?= htmlspecialchars($validationErrors['db_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="db_user">نام کاربری پایگاه داده</label>
                    <input type="text" id="db_user" name="db_user" value="<?= htmlspecialchars($dbUserValue, ENT_QUOTES, 'UTF-8'); ?>" placeholder="نام کاربری MySQL">
                    <?php if (!empty($validationErrors['db_user'])): ?>
                        <span class="error-text"><?= htmlspecialchars($validationErrors['db_user'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="db_password">رمز عبور پایگاه داده</label>
                    <input type="password" id="db_password" name="db_password" value="<?= htmlspecialchars($dbPasswordValue, ENT_QUOTES, 'UTF-8'); ?>" placeholder="رمز عبور MySQL">
                    <span class="form-text">در صورت خالی بودن، رمز عبور فعلی حفظ می‌شود.</span>
                </div>

                <div class="button-row">
                    <button type="submit">ذخیره تنظیمات</button>
                    <a class="btn-secondary" href="<?= htmlspecialchars(UtilityHelper::baseUrl('/'), ENT_QUOTES, 'UTF-8'); ?>">بازگشت به سایت</a>
                </div>
            </form>
        </div>

        <div class="card">
            <h2>مکان فایل‌های تنظیمات</h2>
            <p>برای به‌روزرسانی دستی نیز می‌توانید مقادیر زیر را در فایل‌های ذکر شده تغییر دهید:</p>
            <ul>
                <li><strong>آدرس سایت:</strong> فایل <code>config/app.php</code> کلید <code>'url'</code></li>
                <li><strong>تنظیمات پایگاه داده:</strong> فایل <code>config/database.php</code> کلیدهای <code>'host'</code>، <code>'database'</code>، <code>'username'</code>، <code>'password'</code></li>
            </ul>
            <p>بعد از اعمال تغییرات، ترجیحاً مسیر <code>storage/tmp_*</code> را پاکسازی کنید تا اطلاعات کش شده قدیمی در هاست باقی نماند.</p>
        </div>
    </div>
</body>
</html>
