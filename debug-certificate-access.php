<?php
// Debug script to check certificate access
require_once __DIR__ . '/app/Helpers/autoload.php';

AuthHelper::startSession();

echo "<pre style='background:#f0f0f0; padding:20px; font-family:monospace;'>";
echo "🔍 <b>DEBUG: Certificate Access Check</b>\n";
echo "═══════════════════════════════════════\n\n";

if (isset($_SESSION['user'])) {
    echo "✅ Session exists\n\n";
    
    $user = $_SESSION['user'];
    
    echo "📋 User Info:\n";
    echo "  - Username: " . ($user['username'] ?? 'N/A') . "\n";
    echo "  - Account Source: " . ($user['account_source'] ?? 'N/A') . "\n";
    echo "  - Organization ID: " . ($user['organization_id'] ?? 'N/A') . "\n";
    
    $accountSource = strtolower(trim($user['account_source'] ?? ''));
    $isOrgOwner = ($accountSource === 'organizations');
    
    echo "\n🔐 Access Flags:\n";
    echo "  - Is Org Owner: " . ($isOrgOwner ? '✅ YES' : '❌ NO') . "\n";
    
    if (isset($user['organization_user_flags']) && is_array($user['organization_user_flags'])) {
        $flags = $user['organization_user_flags'];
        $isSystemAdmin = ((int)($flags['is_system_admin'] ?? 0) === 1);
        echo "  - Is System Admin: " . ($isSystemAdmin ? '✅ YES' : '❌ NO') . "\n";
    } else {
        echo "  - Is System Admin: ❌ NO (no flags)\n";
    }
    
    if (isset($user['permissions']) && is_array($user['permissions'])) {
        $perms = $user['permissions'];
        $hasReportsSettingsManage = in_array('reports_settings_manage', $perms, true);
        echo "  - Has reports_settings_manage: " . ($hasReportsSettingsManage ? '✅ YES' : '❌ NO') . "\n";
        
        echo "\n📜 All Permissions:\n";
        if (!empty($perms)) {
            foreach ($perms as $p) {
                echo "    • $p\n";
            }
        } else {
            echo "    (no permissions)\n";
        }
    } else {
        echo "  - Has reports_settings_manage: ❌ NO (no permissions)\n";
    }
    
    echo "\n";
    echo "═══════════════════════════════════════\n";
    echo "💡 <b>Can access preview mode?</b>\n";
    
    $canPreview = false;
    if ($isOrgOwner) {
        echo "✅ YES - You are organization owner\n";
        $canPreview = true;
    } elseif (isset($user['organization_user_flags']['is_system_admin']) && (int)$user['organization_user_flags']['is_system_admin'] === 1) {
        echo "✅ YES - You are system admin\n";
        $canPreview = true;
    } elseif (isset($user['permissions']) && in_array('reports_settings_manage', $user['permissions'], true)) {
        echo "✅ YES - You have reports_settings_manage permission\n";
        $canPreview = true;
    } else {
        echo "❌ NO - You don't have preview access\n";
        echo "\n📝 To get access, you need ONE of:\n";
        echo "  1. Be organization owner (account_source = 'organizations')\n";
        echo "  2. Have is_system_admin flag = 1\n";
        echo "  3. Have 'reports_settings_manage' permission\n";
    }
    
    echo "\n═══════════════════════════════════════\n";
    if ($canPreview) {
        echo "🎯 <b>Try this URL:</b>\n";
        echo '<a href="' . UtilityHelper::baseUrl('organizations/reports/self-assessment/certificate?evaluation_id=12&evaluatee_id=8&preview=1') . '" style="color:blue;">';
        echo UtilityHelper::baseUrl('organizations/reports/self-assessment/certificate?evaluation_id=12&evaluatee_id=8&preview=1');
        echo '</a>';
    } else {
        echo "⚠️  You need to complete all exams to view the certificate\n";
        echo "   OR get one of the permissions listed above\n";
    }
    
} else {
    echo "❌ No session found - Please login first\n";
}

echo "</pre>";
?>
