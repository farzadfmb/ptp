<?php

// Load helpers
require_once '../app/Helpers/autoload.php';

// Serve existing public assets (e.g., uploaded images) directly
$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$baseDir = ($scriptDir === '/' || $scriptDir === '\\') ? '' : rtrim($scriptDir, '/');
$publicRoot = realpath(__DIR__);

if ($publicRoot !== false) {
	$relativePath = $requestUri;
	if ($baseDir !== '' && strpos($relativePath, $baseDir) === 0) {
		$relativePath = substr($relativePath, strlen($baseDir));
	}
	$relativePath = '/' . ltrim($relativePath, '/');

	if ($relativePath !== '/' && $relativePath !== '') {
		$candidatePath = realpath($publicRoot . $relativePath);
		if ($candidatePath !== false
			&& strpos($candidatePath, $publicRoot) === 0
			&& is_file($candidatePath)
			&& !preg_match('/\.php$/i', $candidatePath)
		) {
			$mimeType = 'application/octet-stream';
			if (function_exists('finfo_open')) {
				$finfo = finfo_open(FILEINFO_MIME_TYPE);
				if ($finfo) {
					$detected = finfo_file($finfo, $candidatePath);
					if (is_string($detected) && $detected !== '') {
						$mimeType = $detected;
					}
					finfo_close($finfo);
				}
			} elseif (function_exists('mime_content_type')) {
				$detected = mime_content_type($candidatePath);
				if (is_string($detected) && $detected !== '') {
					$mimeType = $detected;
				}
			}

			header('Content-Type: ' . $mimeType);
			header('Content-Length: ' . filesize($candidatePath));
			readfile($candidatePath);
			exit;
		}
	}
}

// Load Router
require_once '../app/Router.php';

// Load routes
$routes = require_once '../routes/web.php';

// Create router instance
$router = new Router();
$router->loadRoutes($routes);

// Dispatch the request
$router->dispatch();

