<?php

/**
 * Created by FVdW.
 *
 * depot vente @ raspberry
 * User: francois
 * Date: 18/01/2021
 *
 * @copyright: 2021
 * @version $Revision: $
 *
 * @var $cashRegisters
 * @var array $current_user
 * @var array $items_stats
 * @var array $deposits_stats
 * @var array $hh_info
 */


$happy_hrs = print_r($hh_info, true);

// Reload auto de la page (2 minutes)
$this->Html->scriptStart(['block' => true]);
?>
var tmr = 0;

var decTimer = function() {
    if (tmr++ < 120) {
        let pc = 100 - (tmr / 1.2);
        let div = document.getElementById("idiv_tmr");
        div.style.width = pc+"%";
        setTimeout(decTimer, 1000);
    } else {
        window.location.reload(true);
    }
}

function ready(fn) {
    if (document.readyState != 'loading'){
        fn();
    } else {
        document.addEventListener('DOMContentLoaded', fn);
    }
}

ready(decTimer);
<?php
$this->Html->scriptEnd();

$item_progress_defs = \Cake\Core\Configure::read('items.progresses');
$sale_states_defs   = \Cake\Core\Configure::read('sales.states');

if (empty($items_stats)) {
    $sale_rate = 0;
} else {
    if (empty($items_stats['ON SALE'])) $items_stats['ON SALE'] = 0;
    if (empty($items_stats['SOLD'])) $items_stats['SOLD'] = 0;
    $qt = ($items_stats['ON SALE']+$items_stats['SOLD']) / 100;
    $sale_rate = round($items_stats['SOLD'] / $qt);
}
?>
<div class="cashRegister row">
    <div class="col-12 mt-2 mb-3">
        <div class="progress" style="height: 20px;">
            <div id="idiv_tmr" role="progressbar" class="progress-bar bg-info" style="width:100%"></div>
        </div>
    </div>
    <div class="col-md-7">
        <h4 class="mt-1">Caisses</h4>
        <?php if (!count($cashRegisters)) : ?>
            <i>Aucune caisse confifgurée !</i>
        <?php else : ?>
            <table class="table">
                <?php foreach($cashRegisters as $cashRegister) : ?>
                    <tr>
                        <td style="width: 30px;text-align: center"><?= $cashRegister->htmlState ?></td>
                        <th>Caisse <?= $cashRegister->name ?></th>
                        <td><?= in_array($cashRegister->state, ['SALE IN PROGRESS', 'OPEN'])? $cashRegister->user_title : '&nbsp' ?></td>
                        <td><span style="font-size: 90%">Fond caisse :</span> <?= number_format($cashRegister->cash_fund, 2, '.', '') ?> €</td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <?php if ($hh_info['active']) : ?>
            <div>
                <h4>Happy hours <i class="fa fa-smile-o <?= $hh_info['in'] ? 'fa-spin text-success':'' ?>" aria-hidden="true"></i></h4>
                <table class="table">
                    <tr><th>Début</th><td><?= $hh_info['fmt_start'] ?></td></tr>
                    <tr><th>Fin</th><td><?= $hh_info['fmt_end'] ?></td></tr>
                    <tr><td colspan="2"><?= $hh_info['comment'] ?>&nbsp;</td></tr>
                </table>
            </div>
            <?php endif; ?>
            <h4 class="mt-2">Dépôts</h4>
            <table class="table">
                <tr><th>En cours</th><td class="text-right"><?= empty($deposits_stats['EDIT'])? '0': $deposits_stats['EDIT'] ?></td></tr>
                <tr><th>Finalisé</th><td class="text-right"><?= empty($deposits_stats['CLOSED'])? '0': $deposits_stats['CLOSED'] ?></td></tr>
            </table>
        <?php endif; ?>
    </div>
    <div class="col-md-5 bg-light">
        <h4 class="mt-1">Articles</h4>
        <table class="table">
            <?php foreach ($item_progress_defs as $progress => $item_progress_def) :
                $icon = $item_progress_def['icon'];
                if (empty($items_stats[$progress])) {
                    $icon = str_replace('fa-spin', '', $icon);
                    $count = '0';
                } else {
                    $count = $items_stats[$progress];
                }
                ?>
                <tr>
                    <td style="width:30px;" class="text-center"><i class="<?= $icon ?> fa-lg" aria-hidden="true" title="<?= $item_progress_def['label'] ?>"></i></td>
                    <th><?= $item_progress_def['label'] ?></th>
                    <td style="width:30px; text-align: right"><?= $count ?></td>
                </tr>
            <?php endforeach;  ?>
        </table>
        <p class="lead mt-1">Ratio articles déposés/vendus</p>
        <div class="ml-3 mr-3">
            <?php if ($sale_rate) :?>
                <div class="progress" style="height: 20px;background-color: #ffffff">
                    <div role="progressbar" class="progress-bar bg-success" style="width:<?= $sale_rate ?>%;font-weight: bold">
                        <?= $sale_rate ?> %
                    </div>
                </div>
            <?php else: ?>
                <p><em>Aucune vente</em></p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php
