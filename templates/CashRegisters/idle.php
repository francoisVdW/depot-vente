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
 * @var $cashRegister
 * @var array $hh_info
 */

// Reload auto de la page (1 minute)
$this->Html->scriptStart(['block' => true]);
?>
var tmr = 0;

var decTimer = function() {
    if (tmr++ < 60) {
        let pc = 100 - (tmr / 0.6);
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

?>
<h2>Caisse <?= $cashRegister->name ?></h2>
<p class="lead">Caisse prête pour une nouvelle vente</p>
<?php if ($hh_info['active']) : ?>
    <p>
        <i class="fa fa-smile-o fa-lg <?= $hh_info['in']? 'fa-spin text-success' : '' ?>" aria-hidden="true"></i>
        <?= $hh_info['comment'] ?>
    </p>
<?php endif ?>
<div class="row">
    <div class="col-md-6 offset-md-3 mb-5">
        <div class="mb-5">
            <div id="idiv_tmr" class="bg-primary" style="height: 4px; width:100%"></div>
        </div>
    <?php
        switch($cashRegister->state) {
            case 'CLOSED':
                echo $this->Html->link('Re-ouvrir', ['controller' => 'CashRegisters', 'action' => 'open', $cashRegister->id], ['class' => 'btn btn-primary btn-block']);
                break;
            case 'SALE IN PROGRESS':
            case 'OPEN':
                $found = false;
                foreach ($cashRegister->sales as $sale) {
                    if ($sale->state == 'SALE IN PROGRESS') {
                        // cas impossible car traite dans controlleur reprise de la vente...
                        echo $this->Html->link('Reprendre la vente '.$sale->id, ['controller' => 'Sales', 'action' => 'proceed', $sale->id], ['class' => 'btn btn-warning  btn-block']);
                        $found = true;
                        break;
                    }
                    if ($sale->state == 'NEW') {
                        // reprise de la vente...
                        echo $this->Html->link('Re-Commencer la vente '.$sale->id, ['controller' => 'Sales', 'action' => 'proceed', $sale->id], ['class' => 'btn btn-primary  btn-block']);
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    echo $this->Html->link('Nouvelle vente', ['controller' => 'Sales', 'action' => 'newSale', $cashRegister->id], ['class' => 'btn btn-primary  btn-block']);
                    echo '&nbsp;';
                    echo $this->Html->link('Fermer', ['controller' => 'CashRegisters', 'action' => 'close', $cashRegister->id], ['class' => 'btn btn-primary  btn-block']);
                }
                break;
            case 'COMPLETED':
                echo '<i>Caisse clôturée</i>';
                break;
            default:
                $err_msg = __METHOD__."() valeur de \$cashRegister->state non reconnue [{$cashRegister->state}]";
                \Cake\Log\Log::error($err_msg);
                if (\Cake\Core\Configure::read('debug')) {
                    trigger_error($err_msg, E_USER_ERROR);
                }
                echo '<p>Etat de la caisse indéterminé : contactez l\'adim.</p>';
    }
    ?>
    </div>
</div>
