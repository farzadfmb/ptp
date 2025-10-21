<?php
// Clear OPcache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ OPcache cleared\n";
} else {
    echo "⚠️ OPcache not enabled\n";
}

// Clear realpath cache
clearstatcache(true);
echo "✅ Stat cache cleared\n";

echo "\n✅ All caches cleared! Please refresh your browser.\n";
