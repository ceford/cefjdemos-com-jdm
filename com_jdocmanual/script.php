<?php

/**
 * @package     jdocmanual
 * @subpackage  Administrator
 *
 * @copyright   (C) 2023 - 2026 Clifford E Ford. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @link        https://jdocmanual.org/
 */

use Joomla\Filesystem\Folder;
use Joomla\Filesystem\File;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

class com_jdocmanualInstallerScript
{
    /**
     * method to run after an install/update/uninstall method
     *
     * @return void
     */
    public function postflight($type, $parent)
    {
        $this->deleteUnexistingFiles();

        return true;
    }

    private function deleteUnexistingFiles()
    {
        $folders = [
        '/administrator/components/com_jdocmanual/src/View/Sources/',
        '/administrator/components/com_jdocmanual/src/View/Source/',
        '/administrator/components/com_jdocmanual/src/View/ZZZManual/',
        '/administrator/components/com_jdocmanual/tmpl/sources/',
        '/administrator/components/com_jdocmanual/tmpl/source/',
        '/administrator/components/com_jdocmanual/tmpl/zzzmanual/',

        '/components/com_jdocmanual/src/View/Jdmpage',
        '/components/com_jdocmanual/src/View/UnusedManual',
        '/components/com_jdocmanual/tmpl/manual',
        '/components/com_jdocmanual/tmpl/jdmpage',
        '/Jdmimages',
        ];  // overwrite this line with your files to delete

        if (!empty($folders)) {
            foreach ($folders as $folder) {
                if (is_dir(JPATH_ROOT . $folder)) {
                    // Deletes an entire directory if exists. If the directory
                    // is not empty, it deletes its contents first.
                    Folder::delete(JPATH_ROOT . $folder);
                }
            }
        }
        $files = [
            '/administrator/components/com_jdocmanual/src/Controller/SourcesController.php',
            '/administrator/components/com_jdocmanual/src/Controller/SourceController.php',
            '/administrator/components/com_jdocmanual/src/Helper/SourcesHelper.php',
            '/administrator/components/com_jdocmanual/src/Model/SourcesModel.php',
            '/administrator/components/com_jdocmanual/src/Model/SourceModel.php',
            '/administrator/components/com_jdocmanual/src/Table/SourceTable.php',

             '/components/com_jdocmanual/src/Model/ManualModel.php',
             '/components/com_jdocmanual/src/Model/UnusedManualModel.php',
             '/components/com_jdocmanual/tmpl/manuals/select_manual.php',
             '/components/com_jdocmanual/tmpl/manuals/select_page_language.php',
             '/components/com_jdocmanual/tmpl/manuals/select-menu-language.php',
        ];

        foreach ($files as $file) {
            if (is_file(JPATH_ROOT . $file)) {
                File::delete(JPATH_ROOT . $file);
            }
        }
    }
}
