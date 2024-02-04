<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * CashRegister Entity
 *
 * @property int $id
 * @property string|null $name
 * @property string $cash_fund
 * @property string|null $ip
 * @property string|null $state
 * @property int|null $user_id
 * @property string|null $user_title
 *
 * @property \App\Model\Entity\Sale[] $sales
 */
class CashRegister extends Entity
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
        'name' => true,
        'cash_fund' => true,
        'ip' => true,
        'state' => true,
        'user_id' => true,
        'user_title' => true,
        'sales' => true,
    ];

    /**
     * _getHtmlState()
     *
     * @return string
     */
    protected function _getHtmlState()
    {
        $state = \Cake\Core\Configure::read('cash_register.states.'.$this->state);
        return '<i class="'.$state['icon'].' fa-lg" aria-hidden="true" title="'.$state['label'].'"></i>';
    }

    /**
     * _getFund()
     *
     * @return string
     */
    protected function _getFund()
    {
        return number_format((float)$this->cash_fund, 2, '.', '');
    }
}
