<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Deposit $deposit
 */
?>
<div class="row">
    <div class="col-md-9">
        <h3>Dépôt N° <?= $deposit->id ?></h3>
        <table class="table table-sm mb-3">
            <tr>
                <th style="max-width: 15%;text-align: right">Déposant</th>
                <td><?= $deposit->title ?></td>
                <th style="text-align: right">Téléphone</th>
                <td><?= $deposit->numTel ?>&nbsp;</td>
            </tr>
            <tr>
                <th style="text-align: right">E-mail</th>
                <td><?= $deposit->email ?>&nbsp;</td>
                <th style="max-width: 15%;text-align: right">Création</th>
                <td><?= $deposit->creator ?></td>
            </tr>
        </table>
        <h3>Liste des articles</h3>
        <table class="table table-sm table-striped">
            <thead>
            <tr>
                <th>N°</th>
                <th>Désignation</th>
                <th>&nbsp;</th>
                <th>N° fabricant</th>
                <th>Code Barre</th>
                <th style="width: 90px">Prix vente</th>
                <th>&nbsp;</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($deposit->items as $item) : ?>
            <tr>
                <td><?= $item->ref ?></td>
                <td style="font-size: 90%"><?= h($item->name) ?></td>
                <td><?= $item->iconColor ?></td>
                <td><?= h($item->bar_code) ?></td>
                <td><?= h($item->mfr_part_no) ?></td>
                <td><?= $item->requested_price ?> €</td>
                <td><?= $item->iconHappyHour ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="col-md-3">
        <div class="alert alert-info"><p class="lead"><i class="fa fa-info-circle fa-lg" aria-hidden="true"></i> Le dépôt est clôturé.</p><p>Imprimez le bon de dépôt.</p><p>Faites signer l'exemplaire "<em>organisateur</em>" par le déposant et remettez-lui l'exemplaire "<em>déposant</em>".</p></div>
        <?= $this->Html->link('<i class="fa fa-file-pdf-o" aria-hidden="true"></i> Bon de dépot n° '.$deposit->id, ['controller' => 'Deposits', 'action' => 'pdf', $deposit->id, "depot_{$deposit->id}.pdf"], ['escape' => false, 'class' => 'btn btn-primary btn-block', 'target'=>'_blank', 'title' => 'Document PDF']); ?>
    </div>
    <div class="col-md-12 text-center">
        <?= $this->Html->link('Liste des dépôts', ['action' => 'index'], ['class' => 'btn btn-outline-primary', 'escape' => false]) ?>
        &nbsp;
        <?= $this->Html->link('Nouveau dépôt', ['action' => 'add'], ['class' => 'btn btn-primary', 'escape' => false]) ?>
    </div>

</div>
