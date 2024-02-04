<?php
/**
 * Created by FVdW.
 *
 * depot vente @ raspberry
 * User: francois
 * Date: 30/01/2021
 *
 * @copyright: 2021
 * @version $Revision: $
 *
 *
 * @var array $reject_reasons
 * @var int $cnt_sale
 * @var int $cnt_deposit
 * @var int $cnt_item
 * @var int $cnt_user
 * @var array $current_user  (set dans AppController::beforeRender()
 */


?>
<div class="row">
    <h2>Remise à zéro du dépôt/vente</h2>
    <div class="col-md-10 offset-md-1 mt-2">
        <table class="table">
            <thead>
            <tr>
                <th colspan="4" class="lead text-center">Etat du dépot/vente</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <th>Dépots</th>
                <td><?= $cnt_deposit ?></td>
                <th>Articles</th>
                <td><?= $cnt_item ?></td>
            </tr>
            <tr>
                <th>Ventes</th>
                <td><?= $cnt_sale ?></td>
                <th>Utilisateurs</th>
                <td><?= $cnt_user ?></td>
            </tr>
            </tbody>
        </table>
        <?php if (count($reject_reasons)) : ?>
        <div class="alert alert-warning">Remise à zéro refusée</div>
        <ul>
            <?php foreach($reject_reasons as $reason) :?>
            <li><?= $reason ?></li>
            <?php endforeach; ?>
        </ul>
        <?php else: ?>
            <div class="alert alert-info">Remise à zéro permise</div>
            <p>En confirmanant la remise à zéro, toutes les données concernant les dépôts, les ventes et la balance seront définitivement perdues.</p>
            <?= $this->Form->create(null) ?>
            <?= $this->Form->control('drop_users', ['type' => 'checkbox', 'label' => 'Effacer les utilisateurs (sauf vous-même)', 'autocomplete' => 'off', 'checked' => false])  ?>
            <?= $this->Form->control('reset_cash_register', ['type' => 'checkbox', 'label' => 'Remise à zéro des caisses', 'autocomplete' => 'off', 'checked' => false])  ?>
            <?= $this->Form->control('passwd', ['type' => 'password', 'label' => 'Entrez votre mot de passe', 'class' => 'form-control', 'autocomplete' => 'off', 'value' => '', 'required' => true])  ?>
            <?= $this->element('submit_btn', ['label' => 'Remise à zéro !']) ?>
            <?= $this->Form->end() ?>

        <?php endif; ?>
    </div>
</div>
