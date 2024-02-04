<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Item Entity
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $bar_code
 * @property string|null $mfr_part_no
 * @property int $deposit_id
 * @property string|null $requested_price
 * @property string $sale_price
 * @property string $debt_amount
 * @property bool $happy_hour
 * @property int|null $sale_id
 * @property \Cake\I18n\FrozenTime|null $return_date
 * @property string|null $return_cause
 * @property \Cake\I18n\FrozenTime|null $created
 *
 * @property \App\Model\Entity\Deposit $deposit
 * @property \App\Model\Entity\Sale $sale
 */
class Item extends Entity
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
        'progress' => true,
        'bar_code' => true,
        'mfr_part_no' => true,
        'color' => true,
        'deposit_id' => true,
        'requested_price' => true,
        'sale_price' => true,
        'debt_amount' => true,
        'happy_hour' => true,
        'sale_id' => true,
        'return_date' => true,
        'return_cause' => true,
        'deposit' => true,
        'sale' => true,
        'created' => true,
        'creator_id' => true,
        'creator_title' => true,
    ];

/*
    protected function _getPrice()
    {
        return $this->calcSalePrice('NATURAL');
    }
*/

    /**
     * @param string $typ_price REQ|SALE|DB|null : REQ=requested_price | SALE=sale_price | DB=donnee DB | null=pas d'info prix
     * @param bool $happy_hour_period
     * @return array
     */
    public function flat($typ_price = 'REQ', $happy_hour_period = false)
    {
        $o = [
            'id' => $this->id,
            'progress' => $this->progress,
            'deposit_id' => $this->deposit_id,
            'name' => $this->name,
            'mfr_part_no' => $this->mfr_part_no,
            'bar_code' => $this->bar_code,
            'color' => $this->color,
            ];
        if ($typ_price=='REQ') {
            $o['requested_price'] = $this->requested_price;
            $o['happy_hour'] = $this->happy_hour;
        } elseif ($typ_price == 'SALE') {
            $o['price'] = $this->calcSalePrice($happy_hour_period);
            $o['display_price'] = (float)$this->requested_price != (float)$o['price']? sprintf('%.02f € / %.02f €', $this->requested_price, $o['price']) : number_format($o['price'],2, '.', '').' €';
            $o['happy_hour'] = $o['price'] != $this->requested_price? 1:0;
        } elseif ($typ_price == 'DB') {
            $o['price'] = $this->sale_price;
            $o['happy_hour'] = $o['price'] != $this->requested_price? 1:0;
        }
        return $o;
    }

    /**
     * _getRef()
     *
     * @return string
     */
    protected function _getRef()
    {
        return $this->deposit_id.'-'.$this->id;
    }

    /**
     * _getIconHappyHour()
     *
     * @return string
     */
    protected function _getIconHappyHour()
    {
        if ($this->happy_hour) {
            $hh_price = $this->calcSalePrice(true);
            return '<i class="fa fa-smile-o lead" aria-hidden="true" title="\'Happy hour\' demandé; prix réduit='.$hh_price.' €"></i>';
        } else {
            return '&nbsp;';
        }
    }

    /**
     * _getIconColor()
     *
     * @return string
     */
    protected function _getIconColor()
    {
        if ($this->color) {
            return '<i class="fa fa-circle lead" aria-hidden="true" style="color: '.$this->color.';"></i>';
        } else {
            return '&nbsp;';
        }
    }


    /**
     * _getIconProgress()
     *
     * @return string
     */
    protected function _getIconProgress()
    {
        $def = \Cake\Core\Configure::read('items.progresses.'.$this->progress);
        if (empty($def)) return $this->progress;
        return '<i class="'.$def['icon'].' lead" aria-hidden="true" title="'.$def['label'].'"></i>';
    }


    /**
     * _getReturnPrice()
     *
     * @return float|int
     */
    protected function _getReturnPrice()
    {
        $rate = \Cake\Core\Configure::read('ui.organisation.rate');
        $rate_app = (100 - $rate) / 100;
        if ($this->progress != 'SOLD') return 0;
        return round($this->sale_price * $rate_app, 2);
    }


    /**
     * @param bool $in_happy_hour_period
     *
     * @return float
     */
    public function calcSalePrice($in_happy_hour_period) {
        if ($this->happy_hour && $in_happy_hour_period) {
            // happy hour actif
            $hh_defs = \Cake\Core\Configure::read('ui.happy_hour');
            return (float)round($this->requested_price * (1 - ($hh_defs['rate']/100) ), 2);
        } else {
            return (float)$this->requested_price;
        }
    }

}
