<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         3.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 *
 * Add FVdW : creator/modifier title and id
 */
namespace App\Model\Behavior;

use Cake\Database\Type;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Http\Session;
use Cake\I18n\Time;
use Cake\ORM\Behavior;
use DateTime;
use UnexpectedValueException;


/**
 * Class TimestampBehavior
 */

class TimeStampOwnerBehavior extends Behavior
{

    /**
     * Default config
     *
     * These are merged with user-provided config when the behavior is used.
     *
     * events - an event-name keyed array of which fields to update, and when, for a given event
     * possible values for when a field will be updated are "always", "new" or "existing", to set
     * the field value always, only when a new record or only when an existing record.
     *
     * refreshTimestamp - if true (the default) the timestamp used will be the current time when
     * the code is executed, to set to an explicit date time value - set refreshTimetamp to false
     * and call setTimestamp() on the behavior class before use.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'implementedFinders' => [],
        'implementedMethods' => [
            'timestamp' => 'timestamp',
            'touch' => 'touch'
        ],
        'events' => [
            'Model.beforeSave' => [
                'created' => 'new',
                'modified' => 'always'
            ]
        ],
        'refreshTimestamp' => true,
		'setOwner' => true,
    ];

    protected $owner = false;

    /**
     * Current timestamp
     *
     * @var \Cake\I18n\Time
     */
    protected $_ts;

    /**
     * Initialize hook
     *
     * If events are specified - do *not* merge them with existing events,
     * overwrite the events to listen on
     *
     * @param array $config The config for this behavior.
     * @return void
     */
    public function initialize(array $config) :void
    {
        if (isset($config['events'])) {
            $this->setConfig('events', $config['events'], false);
        }
        $this->owner = false;
    }


	/**
	 * getCurentUser()
	 * @author FVdW
	 *
	 * @return array|bool
	 */
	private function getCurentUser()
	{

		$session = new  \Cake\Http\Session();
		$user_info = $session->read('Auth.User');
		if (empty($user_info)) {
			return false;
		} else {
			$r = [];
			$r['id'] = $user_info['id'];
			if (!empty($user_info['last_name'])) {
				if(!empty($user_info['first_name'])) {
					$r['title'] = trim($user_info['first_name'] . ' ' . $user_info['last_name']);
				} else {
					$r['title'] = $user_info['last_name'];
				}
			} else {
				$r['title'] = $user_info['username'];
			}
			return $r;
		}
	}


    /**
     * There is only one event handler, it can be configured to be called for any event
     *
     * @param \Cake\Event\Event $event Event instance.
     * @param \Cake\Datasource\EntityInterface $entity Entity instance.
     * @throws \UnexpectedValueException if a field's when value is misdefined
     * @return bool Returns true irrespective of the behavior logic, the save will not be prevented.
     * @throws \UnexpectedValueException When the value for an event is not 'always', 'new' or 'existing'
     */
    public function handleEvent(Event $event, EntityInterface $entity)
    {
        $events = $this->_config['events'];
        $eventName = $event->getName();

        $new = $entity->isNew() !== false;
        $refresh = $this->_config['refreshTimestamp'];

        if (!$this->owner) {
            $this->owner = $this->getCurentUser();
        }

        foreach ($events[$eventName] as $field => $when) {
            if (!in_array($when, ['always', 'new', 'existing'])) {
                throw new UnexpectedValueException(
                    sprintf('When should be one of "always", "new" or "existing". The passed value "%s" is invalid', $when)
                );
            }
            if ($when === 'always' ||
                ($when === 'new' && $new) ||
                ($when === 'existing' && !$new)
            ) {
                $this->_updateField($entity, $field, $refresh);
            }
            // Add FVDW
			// If entity has in addition of created/modified the creator/modifier _id & _title
			// set these fields with logged user's informations
			if ($this->owner) {
            	if ($new) {
					if ($entity->isAccessible('creator_id') && empty($entity->creator_id)) $entity->set('creator_id', $this->owner['id']);
					if ($entity->isAccessible('creator_title') && empty($entity->creator_title)) $entity->set('creator_title', $this->owner['title']);
				} else {
					if ($entity->isAccessible('modifier_id') && empty($entity->modifier_id)) $entity->set('modifier_id', $this->owner['id']);
					if ($entity->isAccessible('modifier_title') && empty($entity->modifier_title)) $entity->set('modifier_title', $this->owner['title']);
				}
            }
            // End FVdW
        }
        return true;
    }

    /**
     * implementedEvents
     *
     * The implemented events of this behavior depend on configuration
     *
     * @return array
     */
    public function implementedEvents() : array
    {
        return array_fill_keys(array_keys($this->_config['events']), 'handleEvent');
    }

    /**
     * Get or set the timestamp to be used
     *
     * Set the timestamp to the given DateTime object, or if not passed a new DateTime object
     * If an explicit date time is passed, the config option `refreshTimestamp` is
     * automatically set to false.
     *
     * @param \DateTime|null $ts Timestamp
     * @param bool $refreshTimestamp If true timestamp is refreshed.
     * @return \Cake\I18n\Time
     */
    public function timestamp(DateTime $ts = null, $refreshTimestamp = false)
    {
        if ($ts) {
            if ($this->_config['refreshTimestamp']) {
                $this->_config['refreshTimestamp'] = false;
            }
            $this->_ts = new Time($ts);
        } elseif ($this->_ts === null || $refreshTimestamp) {
            $this->_ts = new Time();
        }

        return $this->_ts;
    }

    /**
     * Touch an entity
     *
     * Bumps timestamp fields for an entity. For any fields configured to be updated
     * "always" or "existing", update the timestamp value. This method will overwrite
     * any pre-existing value.
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity instance.
     * @param string $eventName Event name.
     * @return bool true if a field is updated, false if no action performed
     */
    public function touch(EntityInterface $entity, $eventName = 'Model.beforeSave')
    {
        $events = $this->_config['events'];
        if (empty($events[$eventName])) {
            return false;
        }

        $return = false;
        $refresh = $this->_config['refreshTimestamp'];

        foreach ($events[$eventName] as $field => $when) {
            if (in_array($when, ['always', 'existing'])) {
                $return = true;
                $entity->setDirty($field, false);
                $this->_updateField($entity, $field, $refresh);
            }
        }

        return $return;
    }

    /**
     * Update a field, if it hasn't been updated already
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity instance.
     * @param string $field Field name
     * @param bool $refreshTimestamp Whether to refresh timestamp.
     * @return void
     */
    protected function _updateField($entity, $field, $refreshTimestamp)
    {
        if ($entity->isDirty($field)) {
            return;
        }

        $ts = $this->timestamp(null, $refreshTimestamp);

        $columnType = $this->table()->getSchema()->getColumnType($field);
        if (!$columnType) {
            return;
        }

        /** @var \Cake\Database\Type\DateTimeType $type */
        $type = Type::build($columnType);

        if (!$type instanceof Type\DateTimeType) {
            deprecationWarning('TimestampBehavior support for column types other than DateTimeType will be removed in 4.0.');
            $entity->set($field, (string)$ts);

            return;
        }

        $class = $type->getDateTimeClassName();

        $entity->set($field, new $class($ts));
    }

}
// EoF
