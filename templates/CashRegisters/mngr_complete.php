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
 */
// Dtm si une caisse est verouillée pour utilisateur courant
$may_balance = true;

?>
<div class="cashRegister content">
    <h2>Clôture des ventes</h2>
    <p>&hellip;En vue de constituer la balance.</p>
    <?php if (!count($cashRegisters)) : ?>
        <i>Aucune caisse configurée !</i>
    <?php else : ?>
        <table class="table">
        <?php foreach($cashRegisters as $cashRegister) :
            $user = $action = '&nbsp;';
            switch($cashRegister->state) {
                case 'CLOSED':
                    $action = $this->Html->link('Clôturer', ['controller' => 'CashRegisters', 'action' => 'mngrSetComplete', $cashRegister->id], ['class' => 'btn btn-primary']);
                    $may_balance = false;
                    break;
                case 'SALE IN PROGRESS':
                    $user=  $cashRegister->user_title;
                    $may_balance = false;
                    break;
                case 'OPEN':
                    $user = $cashRegister->user_title;
                    $action = $this->Html->link('Clôturer', ['controller' => 'CashRegisters', 'action' => 'mngrSetComplete', $cashRegister->id], ['class' => 'btn btn-primary']);
                    $may_balance = false;
                    break;
                case 'COMPLETED':
                    $action = '<i>Caisse clôturée</i>';
                    break;
                default:
                    $err_msg = __METHOD__."() valeur de \$cashRegister->state non reconnue [{$cashRegister->state}]";
                    \Cake\Log\Log::error($err_msg);
                    if (\Cake\Core\Configure::read('debug')) {
                        trigger_error($err_msg, E_USER_ERROR);
                    }
                    $action = '<p>Etat de la caisse indéterminé : contactez l\'adim.</p>';
                    $may_balance = false;

                    break;
            }

            ?>
            <tr>
                <td style="width: 30px;text-align: center"><?= $cashRegister->htmlState ?></td>
                <th>Caisse <?= $cashRegister->name ?></th>
                <td><?= $user ?></td>
                <td><?= $action ?></td>
            </tr>
        <?php endforeach; ?>
        </table>
            <?php if (!$may_balance): ?>
                <div class="alert alert-info">
                    <i class="fa fa-info-circle fa-lg" aria-hidden="true"></i>&nbsp;
                    La balance des paiements/restitutions n'est possible que si toutes les caisses sont clôturées : Plus aucune vente n'est alors possible.
                </div>
            <?php endif; ?>
            <div class="text-center">
                <?php if ($may_balance) : ?>
                <?= $this->Html->link('Générer la balance', ['controller' => 'Deposits', 'action' => 'mngrBalance', 'balance.pdf'], ['class' => 'btn btn-primary btn-lg', 'target' => '_blank']) ?>
                &nbsp;
                <?php endif; ?>
                <?= $this->Html->link('Générer le bilan des caisses', ['controller' => 'CashRegisters', 'action' => 'mngrCashRegistersBalance', 'bilan_caisses.pdf'], ['class' => 'btn btn-primary btn-lg', 'target' => '_blank']) ?>
            </div>
    <?php endif; ?>
</div>
