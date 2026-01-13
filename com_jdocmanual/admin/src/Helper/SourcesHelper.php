<?php

/**
 * @package     Jdocmanual
 * @subpackage  Cli
 *
 * @copyright   Copyright (C) 2025 Clifford E Ford. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Cefjdemos\Component\Jdocmanual\Administrator\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Utilities for managing sources.
 */
class SourcesHelper
{
    /**
     * Unpublish articles that have been deleted from the sources but are still in the database.
     *
     * @param $db       A database connection.
     *
     * @return  $array  A list of published manuals.
     */
    public function unpublishDeleted() {
        // Get the the 'manuals' path from the component parameters.
        $params = ComponentHelper::getParams('com_jdocmanual');
        $directory = $params->get('gfmfiles_path');

        $txtFiles = $this->findMenuIndexes($directory);

        $menuFiles = $this->getMenuFiles($txtFiles);

        $summary = $this->checkDatabase($menuFiles);

        return $summary;
    }

    /**
    * Recursively traverses a directory and returns an array of menu-index.txt files.
    *
    * @param string $directory The starting directory.
    *
    * @return array List of .md files with their full paths.
    */
    protected function findMenuIndexes($directory) {
        $txtFiles = [];

        // Ensure the directory ends with a slash
        $directory = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        // Open the directory
        if ($handle = opendir($directory)) {
            while (false !== ($entry = readdir($handle))) {
                // Skip the current and parent directory entries
                if ($entry === '.' || $entry === '..') {
                    continue;
                }

                $path = $directory . $entry;

                // If it's a directory, recursively call the function
                if (is_dir($path)) {
                    $txtFiles = array_merge($txtFiles, $this->findMenuIndexes($path));
                } elseif (pathinfo($path, PATHINFO_FILENAME) === 'menu-index') {
                    // Add it to the list
                    $txtFiles[] = $path;
                }
            }
            closedir($handle);
        }

        return $txtFiles;
    }

    /**
    * Read each menu-index.txt file and make a list of articles.
    *
    * @param string $directory The starting directory.
    *
    * @return array List of menu-index.txt files.
    */
    protected function getMenuFiles($txtFiles)
    {
        $files = [];
        foreach ($txtFiles as $file) {
            $contents = file_get_contents($file);
            $lines = explode(PHP_EOL, $contents);
            foreach ($lines as $line) {
                if (empty(trim($line))) {
                    continue;
                }
                // skip any files starting with a semi-colon
                if (strpos($line, ';') === 0) {
                    continue;
                }
                // skip headings
                if (strpos($line, 'heading') === 0) {
                    continue;
                }
                $files[] = str_replace('=', '/', trim($line));
            }
        }
        sort($files, SORT_STRING);

        return $files;
    }

    /**
    * List all articles and unpublish any that are not in the menu-index.txt files
    *
    * @param array $menuFiles The list of articles in the menus.
    *
    * @return string A confirmation message
    */
    protected function checkDatabase($menuFiles)
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        $query = $db->getQuery(true);
        $query->select($db->quoteName(array('id', 'manual', 'language', 'heading', 'filename')))
            ->from($db->quoteName('#__jdm_articles'))
            ->where($db->quoteName('state') . ' = 1');
        $db->setQuery($query);
        $rows = $db->loadObjectList();

        $count = 0;
        $dud_count = 0;
        $duds = [];
        $msg = '';
        if (!empty($rows)) {
            // output data of each row
            foreach ($rows as $row) {
                $test = $row->manual . "/" . $row->heading. "/" . $row->filename;
                if (in_array($test, $menuFiles, true)) {
                    //echo "Test: Good result!" . PHP_EOL;
                } else {
                    $msg .= "Not in menu: id: " . $row->id . " - Data: " . $row->language . " " . $row->manual . "/" . $row->heading. "/" . $row->filename . PHP_EOL;
                    $dud_count += 1;
                    $duds[] = $row->id;
                }
                $count += 1;
            }
        } else {
            $msg .= "There are no published records not in the menu-index.txt files!" . PHP_EOL;
            return $msg;
        }

        $dudlist = implode(', ', $duds);

        if (!empty($dudlist)) {
            $query = $db->getQuery(true);
            $query->update($db->quoteName('#__jdm_articles'))
                ->set($db->quoteName('state') . ' = 0')
                ->where($db->quoteName('id') . ' IN (' . $dudlist . ')');
            $db->setQuery($query);
            $msg .= "Execution of update query:" . PHP_EOL;
            $msg .= $db->execute();
        }

        $msg .= $count . " Records processed." . PHP_EOL;
        $msg .= $dud_count . " Deleted records detected." . PHP_EOL;
        if ($dud_count) {
            $msg .= $dudlist . PHP_EOL;
            $msg .= "Dud records have been unpublished. Rerun the Smart Search Index!" . PHP_EOL;
        }
        return $msg;
    }
}
