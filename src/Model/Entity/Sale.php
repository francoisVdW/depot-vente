<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Sale Entity
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $state
 * @property int|null $invoice_cnt
 * @property string|null $invoice_num
 * @property string|null $customer_info
 * @property string|null $is_happy_hour
 * @property string|null $total_price
 * @property string $pay_chq
 * @property string $pay_cash
 * @property string|null $pay_other
 * @property int $cash_register_id
 * @property \Cake\I18n\FrozenTime|null $created
 * @property int|null $creator_id
 * @property string|null $creator_title
 *
 * @property \App\Model\Entity\CashRegister $cash_register
 * @property \App\Model\Entity\Creator $creator
 * @property \App\Model\Entity\Item[] $items
 */
class Sale extends Entity
{
    use \App\Model\Entity\Traits\RenderDateTimeTrait;

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
        'state' => true,
        'invoice_cnt' => true,
        'invoice_num' => true,
        'customer_info' => true,
        'is_happy_hour' => true,
        'total_price' => true,
        'pay_chq' => true,
        'pay_cash' => true,
        'pay_other' => true,
        'cash_register_id' => true,
        'created' => true,
        'creator_id' => true,
        'creator_title' => true,
        'cash_register' => true,
        'creator' => true,
        'items' => true,
    ];


    protected function _getStateIcon()
    {
        $state_def = \Cake\Core\Configure::read('sales.states.'.$this->state);
        if ($state_def) {
            return '<i class="'.$state_def['icon'].'" aria-hidden="true" title="'.$state_def['label'].'"></i>';
        } else {
            return '<code>'.$this->state.'</code>';
        }
    }


    protected function _getStateIconPlusLabel()
    {
        $state_def = \Cake\Core\Configure::read('sales.states.'.$this->state);
        if ($state_def) {
            return '<i class="'.$state_def['icon'].'" aria-hidden="true" title="'.$state_def['label'].'"></i>&nbsp;'.$state_def['label'];
        } else {
            return '<code>'.$this->state.'</code>';
        }

    }


    /**
     * _getSCreated()
     *
     * @return string
     */
    protected function _getSCreated()
    {
        return $this->created->format('d/m/Y H:i');
    }
}
