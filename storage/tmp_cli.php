<?php
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['REQUEST_URI'] = '/organizations/users';

require __DIR__ . '/../app/Helpers/autoload.php';
require __DIR__ . '/../app/Controllers/OrganizationController.php';

AuthHelper::startSession();

$organization = DatabaseHelper::fetchOne('SELECT * FROM organizations WHERE id = 2');

$_SESSION['user_id'] = 'org-2';
$_SESSION['user'] = [
    'id' => 'org-2',
    'name' => $organization['name'] ?? 'Test Org',
    'scope_type' => 'organization',
    'role_slug' => 'organization-owner',
    'organization_id' => $organization['id'] ?? 2,
    'organization_name' => $organization['name'] ?? 'Test Org',
    'organization' => $organization,
];

$controller = new OrganizationController();

ob_start();
$controller->organizationUsers();
$output = ob_get_clean();

file_put_contents(__DIR__ . '/tmp_output.html', $output);

echo "Rendered output to storage/tmp_output.html\n";
