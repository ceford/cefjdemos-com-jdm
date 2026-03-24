<?php

/**
 * @package     Jdocmanual
 * @subpackage  Administrator
 *
 * @copyright   (C) 2023 - 2026 Clifford E Ford. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @link        https://jdocmanual.org/
 */

namespace Cefjdemos\Component\Jdocmanual\Administrator\Model;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;
use Cefjdemos\Component\Jdocmanual\Administrator\Helper\BuildHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Methods for a list of manual sources.
 *
 * @since  1.0
 */
class FeedbackModel extends ListModel
{
    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @since   1.0
     * @see     \Joomla\CMS\MVC\Controller\BaseController
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                    'id', 'a.id',
                    'manual', 'a.manual',
                    'language', 'a.language',
                    'heading', 'a.heading',
                    'filename', 'a.filename',
                    'likeitornot', 'a.likeitornot',
                    'date_created', 'a.date_created',
            );
        }

        parent::__construct($config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string  $ordering   An optional ordering field.
     * @param   string  $direction  An optional direction (asc|desc).
     *
     * @return  void
     *
     * @since   1.0
     */
    protected function populateState($ordering = 'a.id', $direction = 'desc')
    {
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $manual = $this->getUserStateFromRequest($this->context . '.filter.manual', 'filter_manual');
        if (!empty($manual)) {
            $this->setState('filter.manual', $manual);
        }

        $language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language');
        if (!empty($language)) {
            $this->setState('filter.language', $language);
        }

        $likeitornot = $this->getUserStateFromRequest($this->context . '.filter.likeitornot', 'filter_likeitornot');
        //if (!empty($likeitornot)) {
            $this->setState('filter.likeitornot', $likeitornot);
        //}

        //$date_created = $this->getUserStateFromRequest($this->context . '.filter.date_created', 'date_created', '');
        //$this->setState('filter.date_created', $date_created);

        // List state information.
        parent::populateState($ordering, $direction);
    }

    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param   string  $id  A prefix for the store id.
     *
     * @return  string  A store id.
     *
     * @since   1.0
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.manual');
        $id .= ':' . $this->getState('filter.language');
        $id .= ':' . $this->getState('filter.likeitornot');
        //$id .= ':' . $this->getState('filter.date_created');

        return parent::getStoreId($id);
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return  \Joomla\Database\DatabaseQuery
     *
     * @since   1.0
     */
    protected function getListQuery()
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.*'
            )
        );
        $query->from($db->quoteName('#__jdm_feedback') . ' AS a');

        // Filter by created date - not used yet, needs to be from to
        $date_created = (string) $this->getState('filter.date_created');

        // Filter by Manual
        $manual = (string) $this->getState('filter.manual');
        if (!empty($manual)) {
            $query->where($db->quoteName('a.manual') . ' = :manual')
                ->bind(':manual', $manual, ParameterType::STRING);
        }

        // Filter by Language
        $language = (string) $this->getState('filter.language');
        if (!empty($language)) {
            $query->where($db->quoteName('a.language') . ' = :language')
                ->bind(':language', $language, ParameterType::STRING);
        }

        // Filter by likeitornot
        $likeitornot = (string) $this->getState('filter.likeitornot');
        if (!empty($likeitornot)) {
            $query->where($db->quoteName('a.likeitornot') . ' = :likeitornot')
                ->bind(':likeitornot', $likeitornot, ParameterType::STRING);
        }

        // Filter by search in title.
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            $search = trim($search);
            $search = '%' . str_replace(' ', '%', $search) . '%';
            $query->where($db->quoteName('a.heading') . ' LIKE :search
            OR ' . $db->quoteName('a.filename') . ' LIKE :search
            OR ' . $db->quoteName('a.comment') . ' LIKE :search')
            ->bind(':search', $search, ParameterType::STRING);
        }

        // Add the list ordering clause.
        $orderCol  = $this->state->get('list.ordering', 'a.id');
        $orderDirn = $this->state->get('list.direction', 'ASC');
        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

        return $query;
    }
}
