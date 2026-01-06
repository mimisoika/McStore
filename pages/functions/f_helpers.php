<?php
// functions/f_helpers.php
function getBaseDir() {
    $baseDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    if ($baseDir === '/' || $baseDir === '.') {
        $baseDir = '';
    }
    return $baseDir;
}

function url_path($path) {
    $baseDir = getBaseDir();
    $path = ltrim($path, '/');
    return $baseDir ? $baseDir . '/' . $path : $path;
}
?>