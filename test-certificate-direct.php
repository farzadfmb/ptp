<?php
// Direct test of certificate.php view
require_once __DIR__ . '/app/Helpers/autoload.php';

// Set minimal required variables
$title = 'تست گواهی';
$orgName = 'سازمان تست';
$orgLogoUrl = UtilityHelper::baseUrl('public/assets/images/logo/logo.png');
$fullName = 'محمد محمدی';
$evaluationTitle = 'ارزیابی تست';
$competencyModelDisplay = 'مدل شایستگی تست';
$evaluationDateDisplay = '1403/07/28';

// Mock data
$dateMeta = ['display' => '1403/07/28'];
$certificateSettings = [
    'title_ribbon_text' => 'گواهی پایان دوره',
    'statement_text' => 'گزارش بازخورد',
    'template_key' => 'classic',
    'show_org_logo' => 1,
    'show_signatures' => 1,
    'enable_decorations' => 1,
    'pdf_mode' => 'simple',
    'extra_footer_text' => null,
];

$competencyModelImagePath = null;
$competencies = [];
$chartData = [];
$toolScores = [];
$radarData = [];
$barChartData = [];
$dimensionResults = [];
$dimensionTableData = [];
$featureResults = [];
$featureTableData = [];

echo "<!-- DIRECT TEST: If you see this comment and the page below, certificate.php is loading correctly -->\n";
echo "<script>console.log('🧪 DIRECT TEST: certificate.php loaded via test file');</script>\n";

// Include the actual view
include __DIR__ . '/app/Views/organizations/reports/certificate.php';
?>
