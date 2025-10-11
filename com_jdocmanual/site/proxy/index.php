<?php

/**
 * @package     Jdocmanual
 * @subpackage  Site
 *
 * @copyright   (C) 2023 Clifford E Ford. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

 /**
 * Constant that is checked in included files to prevent direct access.
 * define() is used rather than "const" to not error for PHP 5.2 and lower
 */
define('_JEXEC', 1);

// Get the query string
//https://help.joomla.org/proxy?keyref=Help50:Modules&lang=en
//http://localhost/proxy/index.php?keyref=Help50:Modules&lang=en

$keyref = filter_input(INPUT_GET, 'keyref');
$lang = filter_input(INPUT_GET, 'lang');

// Get the keyref to filename table
require_once __DIR__ . '/key-index.php';

// break the keyref at the first colon
list ($version, $key) = explode(':', $keyref, 2);

// Check the $version is Help4x or Help5x or
if (
    !(strpos($version, 'Help4') === 0 ||
    strpos($version, 'Help5') === 0 ||
    strpos($version, 'Help6') === 0)
) {
    echo "\nThere is no Help data available here for Joomla version {$version}\n";
    exit();
}

// Compose the file path for the requested file
$filename = __DIR__ . '/' . $lang . '/' . $key_index[$key];
if (is_file($filename)) {
    // Send it
    echo file_get_contents($filename);
    exit;
}

// If the requested file language was not English try the English version
$filename = __DIR__ . '/en/' . $key_index[$key];
if (is_file($filename)) {
    // Send it
    $contents = file_get_contents($filename);
    // Add a message after the <main> tag.
    // This should be translated into the selected language.
    $msg = "<main>\n";
    $msg .= '<div class="language-substitute">';
    $msg .= "\nThe page you requested was not available in your preferred language.\n";
    $msg .= "This is the English version.\n";
    $msg .= "</div>\n";

    $contents = str_replace('<main>', $msg, $contents);
    echo $contents;

    exit;
}

// Otherwise send an error message
$msg =<<<EOF
<!DOCTYPE html>
<html lang="en" dir="ltr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Joomla! Help Screens</title>
        <link rel="stylesheet" href="https://help.joomla.org/media/css/help.css">
        <link href="/jdm4/media/templates/administrator/atum/css/vendor/fontawesome-free/fontawesome.css" rel="stylesheet" data-asset-name="fontawesome" />
        <link rel="stylesheet" href="/jdm4/proxy/help.css">
    </head>
    <body>
        <main>
        The Help page you requested is missing!
        <p>{$key}</p>
        </main>
    </body>
</html>
EOF;

echo $msg;
