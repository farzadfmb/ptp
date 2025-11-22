        </div>
        <?php
        $footerSiteName = 'پرتال ارزیابی';
        try {
            $footerSettings = DatabaseHelper::fetchOne('SELECT site_name FROM system_settings ORDER BY id ASC LIMIT 1');
            if (!empty($footerSettings['site_name'])) {
                $footerSiteName = $footerSettings['site_name'];
            }
        } catch (Exception $exception) {
            // ignore
        }
        ?>
        <footer class="page-footer text-center py-3 text-muted small">
            <span>© <?= date('Y'); ?> <?= htmlspecialchars($footerSiteName, ENT_QUOTES, 'UTF-8'); ?> — تمامی حقوق محفوظ است.</span>
        </footer>
    </div>

    <a href="javascript:;" class="back-to-top">
        <ion-icon name="arrow-up-outline"></ion-icon>
    </a>

    <div class="overlay nav-toggle-icon"></div>
    <div class="switcher-wrapper" id="layout-switcher" hidden></div>

    <?php
    AuthHelper::startSession();
    $homeFooterUser = AuthHelper::getUser();
    $supportPhone = '';
    $supportPageUrl = UtilityHelper::baseUrl('home/support');

    if (is_array($homeFooterUser)) {
        $organizationId = (int) ($homeFooterUser['organization_id'] ?? 0);
        if ($organizationId > 0) {
            try {
                $contactQuery = <<<SQL
SELECT contact_phone
FROM organization_contact_channels
WHERE organization_id = :organization_id
  AND contact_phone IS NOT NULL
  AND contact_phone <> ''
ORDER BY is_active DESC,
         priority_level = 'high' DESC,
         priority_level = 'normal' DESC,
         created_at DESC
LIMIT 1
SQL;

                $contactRow = DatabaseHelper::fetchOne(
                    $contactQuery,
                    ['organization_id' => $organizationId]
                );
                if (!empty($contactRow['contact_phone'])) {
                    $supportPhone = UtilityHelper::persianToEnglish(trim((string) $contactRow['contact_phone']));
                }
            } catch (Exception $exception) {
                $supportPhone = '';
            }
        }
    }
    ?>

    <button type="button"
            id="floatingSupportButton"
            class="floating-support-button"
            data-phone="<?= htmlspecialchars($supportPhone, ENT_QUOTES, 'UTF-8'); ?>"
            data-support-url="<?= htmlspecialchars($supportPageUrl, ENT_QUOTES, 'UTF-8'); ?>"
            aria-label="تماس با پشتیبانی">
        <ion-icon name="call-outline"></ion-icon>
    </button>
</div>

<?php
$themeBase = UtilityHelper::baseUrl('public/themes/dashkote');
$assetBase = UtilityHelper::baseUrl('public/assets');
?>

<script src="<?= $themeBase; ?>/js/jquery.min.js"></script>
<script src="<?= $themeBase; ?>/plugins/simplebar/js/simplebar.min.js"></script>
<script src="<?= $themeBase; ?>/plugins/metismenu/js/metisMenu.min.js"></script>
<script src="<?= $themeBase; ?>/plugins/perfect-scrollbar/js/perfect-scrollbar.js"></script>
<script src="<?= $themeBase; ?>/js/bootstrap.bundle.min.js"></script>
<script src="<?= $themeBase; ?>/js/main.js"></script>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
<script src="<?= $assetBase; ?>/js/persian-font-enforcer.js"></script>

<style>
    .floating-support-button {
        position: fixed;
        bottom: 24px;
        right: 24px;
        width: 56px;
        height: 56px;
        border-radius: 50%;
        border: none;
        background: linear-gradient(135deg, #2563eb, #38bdf8);
        color: #fff;
        box-shadow: 0 12px 24px rgba(37, 99, 235, 0.35);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        z-index: 1050;
    }
    .floating-support-button:hover {
        background: linear-gradient(135deg, #1d4ed8, #0ea5e9);
    }
    .floating-support-button:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.4);
    }
    @media (max-width: 575px) {
        .floating-support-button {
            width: 62px;
            height: 62px;
            bottom: 20px;
            right: 16px;
        }
    }
</style>

<script>
    (function () {
        const button = document.getElementById('floatingSupportButton');
        if (!button) {
            return;
        }

        const supportPhone = button.getAttribute('data-phone') || '';
        const supportUrl = button.getAttribute('data-support-url') || window.location.href;

        const sanitizePhone = (value) => {
            return value.replace(/[^0-9+]/g, '');
        };

        const isMobileDevice = () => {
            const ua = navigator.userAgent || navigator.vendor || window.opera;
            const mobileRegex = /android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini/i;
            return mobileRegex.test(ua.toLowerCase()) || window.matchMedia('(max-width: 768px)').matches;
        };

        button.addEventListener('click', () => {
            const isMobile = isMobileDevice();
            if (isMobile && supportPhone) {
                window.location.href = 'tel:' + sanitizePhone(supportPhone);
                return;
            }

            window.location.href = supportUrl;
        });
    })();
</script>

<?php foreach ($additional_js as $js): ?>
    <?php $jsSrc = preg_match('/^https?:\/\//u', $js) ? $js : UtilityHelper::baseUrl($js); ?>
    <script src="<?= htmlspecialchars($jsSrc, ENT_QUOTES, 'UTF-8'); ?>"></script>
<?php endforeach; ?>

<?php if ($inline_scripts !== ''): ?>
    <script><?= $inline_scripts; ?></script>
<?php endif; ?>

</body>
</html>
