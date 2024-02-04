<?php
/**
 * Created by FVdW.
 *
 * depot vente @ raspberry
 * User: francois
 * Date: 31/01/2021
 *
 * @copyright: 2021
 * @version $Revision: $
 *
 * @var \Cake\ORM\Entity $cashRegister
 */
if (in_array($cashRegister->state, ['OPEN','SALE IN PROGRESS'])) {
    $rel_user = $cashRegister->user_title;
    $rel_ip = $cashRegister->ip;
    $btn_chg_user = $this->Modal->ajaxModal('<i class="fa fa-user" aria-hidden="true"></i>', ['controller'=>'CashRegisters', 'action' => 'ajaxSetUser', $cashRegister->id],['class' => 'btn btn-outline-primary btn-sm', 'title' => 'Changer l\'utilistateur', 'escape' => false]).'&nbsp;';
} else {
    $btn_chg_user = '';
    $rel_user = $rel_ip = '&nbsp;';
}
if (in_array($cashRegister->state, ['OPEN','CLOSED'])) {
    $btn_chg_fund = $this->Modal->ajaxModal('<i class="fa fa-money" aria-hidden="true"></i>', ['controller'=>'CashRegisters', 'action' => 'ajaxSetFund', $cashRegister->id],['class' => 'btn btn-outline-primary btn-sm', 'title' => 'Assigner le fond de caisse', 'escape' => false]).'&nbsp;';
} else {
    $btn_chg_fund = '';
}
if (in_array($cashRegister->state, ['COMPLETED','CLOSED'])) {
    $btn_delete = $this->Modal->ajaxModal('<i class="fa fa-trash text-danger" aria-hidden="true"></i>', ['controller'=>'CashRegisters', 'action' => 'ajaxDelete', $cashRegister->id],['class' => 'btn btn-outline-primary btn-sm', 'title' => 'Supprimler la caisse', 'escape' => false]).'&nbsp;';
} else {
    $btn_delete = '';
}
?>
<td style="width: 30px;text-align: center"><?= $cashRegister->htmlState ?></td>
<th id="i_name_<?= $cashRegister->id ?>">Caisse &laquo;<?= $cashRegister->name ?>&raquo;</th>
<td id="i_fund_<?= $cashRegister->id ?>" style="text-align: right;"><?= $cashRegister->fund ?> &euro;</td>
<td id="i_user_<?= $cashRegister->id ?>"><?= $rel_user ?></td>
<td id="i_ip_<?= $cashRegister->id ?>"><?= $rel_ip ?></td>
<td>
    <?= $btn_chg_fund ?>
    <?= $btn_chg_user ?>
    <?= $btn_delete ?>
</td>
