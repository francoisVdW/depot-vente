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
 * @var array $hh_info
 */
// Dtm si une caisse est verouillée pour utilisateur courant
$user_has_lock = false;
foreach($cashRegisters as $cr) {
    if ($cr->user_id == $current_user['id']) {
        if (in_array($cr->state, ['OPEN', 'SALE IN PROGRESS'])) {
            // l'utilisateur courant a verouillé une caisse
            $user_has_lock = $cr->id;
            break;
        }
    }
}
?>
<div class="cashRegister index content">
    <h2>Caisses</h2>
    <?php if (!count($cashRegisters)) : ?>
    <i>Aucune caisse confifgurée !</i>
    <?php else : ?>
        <p><?php if ($hh_info['in']) {
        echo '<i class="fa fa-smile-o fa-lg fa-spin text-success" aria-hidden="true"></i> '.$hh_info['comment'];
        } else {
            echo '<i class="fa fa-smile-o fa-lg" aria-hidden="true"></i> '.$hh_info['comment'];
        }
    ?>
    <table class="table">
    <?php foreach($cashRegisters as $cashRegister) : ?>
        <tr>
            <td style="width: 30px;text-align: center"><?= $cashRegister->htmlState ?></td>
            <th>Caisse <?= $cashRegister->name ?></th>
            <td>
                <?php
                    switch($cashRegister->state) {
                        case 'CLOSED':
                            if ($user_has_lock == $cashRegister->id) echo $this->Html->link('re-ouvrir', ['controller' => 'Sales', 'action' => 'open', $cashRegister->id], ['class' => 'btn btn-primary']);
                            elseif (!$user_has_lock) echo $this->Html->link('ouvrir', ['controller' => 'CashRegisters', 'action' => 'open', $cashRegister->id], ['class' => 'btn btn-primary']);
                            else echo $cashRegister->user_title;
                            break;
                        case 'SALE IN PROGRESS':
                        case 'OPEN':
                            if ($user_has_lock == $cashRegister->id) {
                                $found = false;
                                foreach ($cashRegister->sales as $sale) {
                                    if ($sale->state == 'SALE IN PROGRESS') {
                                        // cas impossible car traite dans controlleur reprise de la vente...
                                        echo $this->Html->link('Reprise de la vente', ['controller' => 'Sales', 'action' => 'proceed', $sale->id], ['class' => 'btn btn-warning']);
                                        $found = true;
                                        break;
                                    }
                                    if ($sale->state == 'NEW') {
                                        // reprise de la vente...
                                        echo $this->Html->link('Commencer la vente', ['controller' => 'Sales', 'action' => 'proceed', $sale->id], ['class' => 'btn btn-primary']);
                                        $found = true;
                                        break;
                                    }
                                }
                                if (!$found) {
                                    echo $this->Html->link('Accèder à la caisse '.$cashRegister->name, ['controller' => 'CashRegisters', 'action' => 'idle', $cashRegister->id], ['class' => 'btn btn-primary']);
                                }
                            } else {
                                echo $cashRegister->user_title;
                            }
                            break;
                        case 'COMPLETED':
                            echo '<span>Caisse clôturée</i>';
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
            </td>
        </tr>
    <?php endforeach; ?>
    </table>
    <?php endif; ?>
</div>
