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
        '/components/com_jdocmanual/src/View/Jdmpage',
        '/components/com_jdocmanual/tmpl/jdmpage',
        ];  // overwrite this line with your files to delete

        if (empty($folders)) {
            return;
        }

        foreach ($folders as $folder) {
            if (is_dir(JPATH_ROOT . $folder)) {
                // Deletes an entire directory if exists. If the directory
                // is not empty, it deletes its contents first.
                Folder::delete(JPATH_ROOT . $folder);
            }
        }
    }
}
