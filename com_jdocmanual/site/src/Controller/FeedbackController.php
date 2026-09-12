<?php

/**
 * @package     Jdocmanual
 * @subpackage  Site
 *
 * @copyright   (C) 2023 - 2026 Clifford E Ford. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @link        https://jdocmanual.org/
 */

namespace Cefjdemos\Component\Jdocmanual\Site\Controller;

use Joomla\CMS\Factory;
use Joomla\CMS\Response\JsonResponse;
use Joomla\Database\ParameterType;
use Cefjdemos\Component\Jdocmanual\Administrator\Controller\ContentController as AdmincontentController;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Jdocmanual Content Controller
 * Used by the Site Javascript but uses the admin controller.
 *
 * @since  4.0.0
 */
class FeedbackController extends AdmincontentController
{
    /**
     * Records either: a like/dislike amd returns an existing comment;
     * or a comment and returns a thank you message.
     * @return never
     */
    public function likeitornot()
    {
        $likeitornot = $this->app->input->get('likeitornot', '', 'WORD');
        $manual = $this->app->input->get('manual', '', 'CMD');
        $language = $this->app->input->get('language', '', 'CMD');
        $path = $this->app->input->get('path', '', 'STRING');
        // limit the string length or the db throws a fatal error
        $comment = substr($this->app->input->get('comment', '', 'STRING'), 0, 255);
        $session_id = $this->app->getSession()->getToken();

        // Let like/dislike be stage 1 and comment be stage 2
        if ($likeitornot === 'like' || $likeitornot === 'dislike' || $likeitornot == 'comment') {
            $stage = 1;
        } else {
            // likeitornot set to alldone
            $stage = 2;
        }

        // If there is already a record replace it.
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->createQuery();
        $query->select($db->quoteName(array('id', 'comment')))
            ->from($db->quoteName('#__jdm_feedback'))
            ->where($db->quoteName('session_id') . ' = ' . $db->quote($session_id))
            ->where($db->quoteName('manual') . ' = ' . $db->quote($manual))
            ->where($db->quoteName('language') . ' = ' . $db->quote($language))
            ->where($db->quoteName('path') . ' = ' . $db->quote($path));
        $db->setQuery($query);
        $row = $db->loadObject();

        $query = $db->createQuery();
        if (!empty($row->id)) {
            $query->update($db->quoteName('#__jdm_feedback'))
            ->where($db->quoteName('id') . ' = ' . $row->id);
            if ($likeitornot === 'like' || $likeitornot === 'dislike') {
                // But return the stored comment
                $comment = $row->comment;
            }
        } else {
            $query->insert('#__jdm_feedback');
        }

        // If likeitornot is not like or dislike do not change it.
        if ($stage === 1) {
            $query->set($db->quoteName('likeitornot') . ' = :likeitornot')
            ->bind(':likeitornot', $likeitornot, ParameterType::STRING);
        } else {
            // Only set the comment if no like or dislike
            $query->set($db->quoteName('comment') . ' = :comment')
            ->bind(':comment', $comment, ParameterType::STRING);
        }
        $query->set($db->quoteName('session_id') . ' = :session_id')
            ->set($db->quoteName('manual') . ' = :manual')
            ->set($db->quoteName('language') . ' = :language')
            ->set($db->quoteName('path') . ' = :path')
            ->bind(':session_id', $session_id, ParameterType::STRING)
            ->bind(':manual', $manual, ParameterType::STRING)
            ->bind(':language', $language, ParameterType::STRING)
            ->bind(':path', $path, ParameterType::STRING);
        $db->setQuery($query);
        $db->execute();

        if ($stage === 1) {
            // If there was no previous comment don't change the label
            if (empty($row->id) || empty($comment)) {
                $comment_label = "Would you like to comment on this article?";
            } else {
                $comment_label = "Would you like to change your comment on this article?";
            }
        } else {
            // And update the comment sent back to the user
            $comment_label = "Your comment has been saved:\n";
        }

        echo json_encode(['stage' => $stage, 'comment' => $comment, 'comment_label' => $comment_label]);
        exit();
    }
}
