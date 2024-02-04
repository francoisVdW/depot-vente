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
 */
?>
<div class="row">
    <div class="col-md-10 offset-md-1">
        <div class="alert alert-info mt-3">
            Cette page est réservée aux utilisateurs <em>administrateurs</em> de cette application.<br>
            Les paramètres de l'applications doivent être réglés avant le début de l'évènement.<br>
            <br>
            La gestion des caisses peut aussi être utilisée au cour de la vente en cas de blocage, pour changer l'utilisateur assigné à une caisse. <u>Attention</u> : cette opération doit être effectuée en dernier recours.
        </div>
    </div>
    <div class="col-md-4 offset-md-4 pt-5">
        <?= $this->Html->link('Configuration de l\'application', ['controller' => 'Settings', 'action' => 'index'], ['class' => 'btn btn-primary btn-block m-2']) ?>
        <?= $this->Html->link('Gestion des utilisateurs', ['controller' => 'Users', 'action' => 'index'], ['class' => 'btn btn-primary btn-block m-2']) ?>
        <?= $this->Html->link('Gestion des caisses', ['controller' => 'CashRegisters', 'action' => 'adminIndex'], ['class' => 'btn btn-primary btn-block m-2']) ?>
        <?= $this->Html->link('Remise à zéro du dépôt/vente', ['controller' => 'AdminFunctions', 'action' => 'reset'], ['class' => 'btn btn-danger btn-block m-2']) ?>
    </div>
</div>
