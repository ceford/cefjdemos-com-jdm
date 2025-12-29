#!/usr/bin/env php
<?php

// Script provided by ChatGPT - but needs fixes to not process min files.

// Set paths
$jsDir  = __DIR__ . '/com_jdocmanual/media/js';
$cssDir = __DIR__ . '/com_jdocmanual/media/css';

function runCommand(string $cmd): void {
    echo "[cmd] $cmd\n";
    $exitCode = 0;
    passthru($cmd, $exitCode);
    if ($exitCode !== 0) {
        echo "Error: command failed with code $exitCode\n";
        exit($exitCode);
    }
}

function shouldProcess($dir, string $source, string $target): bool {
    if (!file_exists($dir . '/'  . $target)) {
        return true;
    }
    return filemtime($source) > filemtime($dir . '/' . $target);
}

function minifyAndGzip(string $file, string $type, $dir): void {
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    $base = pathinfo($file, PATHINFO_FILENAME);
    $minFile = "$base.min.$ext";
    $gzFile  = "$minFile.gz";

    if (shouldProcess($dir, $file, $minFile)) {
        echo "Minifying: $file → $minFile\n";
        if ($type === 'js') {
            runCommand("uglifyjs $file -o $dir/$minFile");
        } elseif ($type === 'css') {
            runCommand("cleancss -o $dir/$minFile $file");
        }
    }

    if (shouldProcess($dir, $minFile, $gzFile)) {
        echo "Gzipping: $minFile → $gzFile\n";
        runCommand("gzip -k -9 -f $dir/$minFile");
    }
}

function processDirectory(string $dir, string $type): void {
    $files = glob("$dir/*.$type");

    foreach ($files as $file) {
        if (preg_match('/\.min\./', basename($file))) {
            continue;
        }

        minifyAndGzip($file, $type, $dir);
    }
}

processDirectory($jsDir, 'js');
processDirectory($cssDir, 'css');

echo "✔ Done\n";
