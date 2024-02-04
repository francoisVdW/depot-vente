<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Setting Entity
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $key
 * @property string|null $data
 * @property string|null $data_date
 * @property string|null $data_numeric
 * @property \Cake\I18n\FrozenTime|null $modified
 * @property int|null $modifier_id
 * @property string|null $modifier_title
 *
 * @property \App\Model\Entity\Modifier $modifier
 */
class Setting extends Entity
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
        'key_name' => false,    // ne jamais modifier : la table ext figée
        'name' => false,        // ne jamais modifier : la table ext figée
        'data' => true,
        'data_date' => true,
        'data_numeric' => true,
        'modified' => true,
        'modifier_id' => true,
        'modifier_title' => true,
        'modifier' => true,
    ];


    /**
     * _getValue()
     *
     * @return bool|string|float|null
     */
    protected function _getValue()
    {
        switch($this->type) {
            case 'BOOL':
                return $this->data_numeric? true : false;
            case 'INT':
                return $this->data_numeric;
            case 'STRING':
            case 'TEXT':
                return $this->data;
            case 'DATE':
                if (!empty($this->data_date)) {
                    $da = $this->data_date;
                    if ($da->timezoneName == 'UTC') {
                        // Ajustement TZ Europe/Paris
                        $da = $da->setTimeZone('Europe/Paris')->addHours(-1);
                    }
                } else {
                    $da = null;
                }
                return $da;
            case 'JSON_OPTIONS':
                if (empty($this->data)) return [];
                return json_decode($this->data, true);
            default:
                $err_msg = __METHOD__."() valeur du champ type non reconnue [{$this->type}]";
                \Cake\Log\Log::error($err_msg);
                if (\Cake\Core\Configure::read('debug')) {
                	trigger_error($err_msg, E_USER_ERROR);
                }
                return '';
        }
    }

    /**
     * _getFmtValue()
     *
     * @return string
     */
    protected function _getFmtValue()
    {
        $v = $this->_getValue();
        if ($this->type == 'BOOL') return $v? 'Oui':'Non';
        if ($this->type == 'DATE') return $this->fmtDateTime($v);
        if ($this->type == 'TEXT') return str_replace(["\n", "\r"], [' ', ''],nl2br($v));
        if ($this->type == 'JSON_OPTIONS') {
            $s = '<ul>';
            foreach($v as $k => $label) {
                $s .= '<li data-value="'.$k.'">'.$label.'</li>';
            }
            $s .= '</ul>';
            return $s;
        };
        return $v;
    }

}
