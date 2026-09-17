<?php
// install.php - Serves the raw bash installer script for one-line installation
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

$scriptPath = __DIR__ . '/install.sh';
if (file_exists($scriptPath)) {
    readfile($scriptPath);
} else {
    echo "#!/usr/bin/env bash\n";
    echo "echo 'Error: install.sh not found on server'\n";
    echo "exit 1\n";
}
exit;
