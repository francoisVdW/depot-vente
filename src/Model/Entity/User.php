<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * User Entity
 *
 * @property int $id
 * @property string|null $username
 * @property string|null $last_name
 * @property string|null $first_name
 * @property string|null $roles_json
 * @property \Cake\I18n\FrozenTime|null $created
 * @property \Cake\I18n\FrozenTime|null $deleted
 */
class User extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        'username' => true,
        'password' => true,
        'last_name' => true,
        'first_name' => true,
        'roles_json' => true,
        'created' => true,
    ];


    /**
     * _getTitle()
     *
     * @return string
     */
    protected function _getTitle()
    {
        return trim($this->first_name. ' '.$this->last_name);
    }


    /**
     * _getARoles()
     *
     * @return mixed
     */
    protected function _getARoles()
    {
        return json_decode($this->roles_json, true);
    }


    /**
     * _getRoles()
     *
     * @return string
     */
    protected function _getRoles()
    {
        $role_defs = \Cake\Core\Configure::read('roles');
        $a_roles = json_decode($this->roles_json, true);
        $a = [];
        foreach ($a_roles as $r) {
            $a[] = array_key_exists($r, $role_defs)? $role_defs[$r] : $r;
        }
        return join(', ', $a);
    }
}
