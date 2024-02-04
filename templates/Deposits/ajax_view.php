<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Deposit $deposit
 */
$this->setLayout('ajax');
?>
<div class="deposits view content" style="min-width: 480px">
    <h4>Dépot n° <?= $deposit->id ?></h4>
    <table class="table table-sm">
        <tr>
            <th>Déposant</th>
            <td><?= h($deposit->title) ?></td>
        </tr>
        <tr>
            <th>Téléphone</th>
            <td><?= h($deposit->phone) ?></td>
        </tr>
        <tr>
            <th>E-mail</th>
            <td><?= h($deposit->email) ?></td>
        </tr>
        <tr>
            <th>Création</th>
            <td><?= h($deposit->creator) ?></td>
        </tr>
    </table>
    <h4>Articles</h4>
    <?php if (!empty($deposit->items)) : ?>
    <div class="mt-3"  style="max-height: 300px;overflow-y: scroll">
        <table class="table table-sm table-striped">
            <thead>
            <tr>
                <th>N°</th>
                <th>Désignation</th>
                <th>&nbsp;</th>
                <th>N° fabricant</th>
                <th>Code barre</th>
                <th colspan="2" style="font-size: 90%">Mise en Vente</th>

            </tr>
            </thead>
            <tbody>
            <?php foreach ($deposit->items as $item) : ?>
            <tr>
                <td><?= $deposit->id. '-'. $item->id ?></td>
                <td><?= h($item->name) ?></td>
                <td><?= $item->iconColor ?></td>
                <td><?= h($item->mfr_part_no) ?></td>
                <td><?= $item->bar_code ?></td>
                <td class="text-right"><?= number_format($item->requested_price,2, ',' , '') ?> &euro;</td>
                <td><?= $item->iconHappyHour ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
            <tr>
                <th colspan="6">Total : <?= count($deposit->items) ?> article<?= count($deposit->items)>1 ? 's':'' ?></th>
            </tr>
            </tfoot>
        </table>
    </div>
    <?php endif; ?>
    <div class="text-center m-3"><button class="btn btn-outline-dark" onclick="closeModal(this)">&#10060; Fermer</button></div>
</div>


