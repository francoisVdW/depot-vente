<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Deposit Entity
 *
 * @property int $id
 * @property string|null $title
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $progress
 * @property string|null $ip
 * @property \Cake\I18n\FrozenTime|null $created
 * @property int|null $creator_id
 * @property string|null $creator_title
 *
 * @property \App\Model\Entity\Item[] $items
 */
class Deposit extends Entity
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
        'title' => true,
        'phone' => true,
        'email' => true,
        'progress' => true,
        'ip' => true,
        'created' => true,
        'creator_id' => true,
        'creator_title' => true,
        'creator' => true,
        'items' => true,
    ];



    /**
     * _getProgressIcon()
     *
     * @return string
     */
    protected function _getProgressIcon()
    {
        $progress_def = \Cake\Core\Configure::read('deposits.progresses.'.$this->progress);
        if ($progress_def) {
            return '<i class="'.$progress_def['icon'].'" aria-hidden="true" title="'.$progress_def['label'].'"></i>';
        } else {
            return '<code>'.$this->progress.'</code>';
        }
    }


    /**
     * _getSCreated()
     *
     * @return string
     */
    protected function _getSCreated()
    {
        return $this->fmtDateTime($this->created);
    }

    /**
     * _getCreator()
     *
     */
    protected function _getCreator()
    {
        $s =  $this->fmtDateTime($this->created);
        if ($this->creator_title) $s .= ' par '.$this->creator_title;
        return $s;
    }


    /**
     * _getNumTel()
     *
     * @return string|string[]|null
     */
    protected function _getNumTel()
    {
        if (strlen($this->phone) == 10) {
            return preg_replace('/(\d\d)(\d\d)(\d\d)(\d\d)(\d\d)/', '$1 $2 $3 $4 $5', $this->phone);
        } else {
            return $this->phone;
        }
    }
}
