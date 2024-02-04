<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Deposit $deposit
 * @var string[] $eregs
 */
?>
<div class="row">
    <div class="col-md-3">
        <div class="alert-dark alert-dismissible p-2 rounded mt-3">Pour commencer, il est nécessaire d'enregister les coordonnées du déposant</div>
    </div>
    <div class="col-md-9 deposits form content">
            <?= $this->Form->create($deposit) ?>
        <fieldset>
            <legend>Nouveau Dépot</legend>
            <?php
                echo $this->Form->control('title', ['label' => 'Nom prénom', 'class' => 'form-control', /*'pattern' => $eregs['title']*/ ]);
                echo $this->Form->control('phone', ['label' => 'Téléphone', 'class' => 'form-control', /*'pattern' => $eregs['phone']*/ ]);
                echo $this->Form->control('email', ['label' => 'E-mail', 'class' => 'form-control']);
            ?>
        </fieldset>
        <?= $this->element('submit_btn') ?>
        <?= $this->Form->end() ?>
    </div>
</div>
