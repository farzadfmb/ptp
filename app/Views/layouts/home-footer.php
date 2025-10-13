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

<?php foreach ($additional_js as $js): ?>
    <?php $jsSrc = preg_match('/^https?:\/\//u', $js) ? $js : UtilityHelper::baseUrl($js); ?>
    <script src="<?= htmlspecialchars($jsSrc, ENT_QUOTES, 'UTF-8'); ?>"></script>
<?php endforeach; ?>

<?php if ($inline_scripts !== ''): ?>
    <script><?= $inline_scripts; ?></script>
<?php endif; ?>

</body>
</html>
