<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Sale $sale
 */
$this->setLayout('ajax');
$cnt_articles = empty($sale->items)? 0: count($sale->items);
?>
<div class="deposits view content" style="min-width: 480px">
    <h4><?= $sale->name ?></h4>
    <?php
    if (!empty($status)) :
        if ($status == 1 && !empty($msg)) : ?>
            <div class="alert alert-success"><?= $msg ?></div>
        <?php elseif ($status == -1 && !empty($msg)) : ?>
            <div class="alert alert-danger"><?= $msg ?></div>
        <?php
        endif;
    endif;
    ?>
    <table class="table table-sm">
        <tr>
            <th>Date</th>
            <td><?= $sale->created->format('d/m/Y H:i') ?> par <?= $sale->creator_title ?></td>
        </tr>
        <tr>
            <th>Etat</th>
            <td><?= $sale->stateIconPlusLabel ?></td>
        </tr>
        <tr>
            <th>Paiement</th>
            <td>
                <strong>Total : <?= number_format($sale->total_price, 2,',','') ?> &euro;</strong>
                <ul>
                    <li>Espèce : <?= number_format($sale->pay_cash, 2,',','') ?> &euro;</li>
                    <li>Chèque : <?= number_format($sale->pay_chq, 2,',','') ?> &euro;</li>
                    <li>Autre : <?= number_format($sale->pay_other, 2,',','') ?> &euro;</li>
                </ul>
            </td>
        </tr>
        <tr>
            <th>Facture</th>
            <td>
                <?php if ($sale->invoice_num) : ?>
                    <strong><?= $sale->invoice_num ?></strong>
                    <div id="idiv_cstm_info">
                        <div class="alert alert-secondary" style="min-height: 70px">
                            <?= nl2br($sale->customer_info) ?>
                        </div>
                        <div class="text-center">
                            <button class="btn btn-outline-primary" onclick="hide(elt('idiv_cstm_info'));show(elt('ifrm_cstm_info'))">Modifier</button>
                            &nbsp;
                            <?= $this->Html->link('Imprimer', ['controller' => 'Sales', 'action' => 'invoicePdf', $sale->id], ['target' => '_blank', 'esape' => false, 'class' => 'btn btn-outline-primary']) ?>
                        </div>
                    </div>
                <?php elseif ($cnt_articles) : ?>
                    <div class="sbmt text-center" id="idiv_prepare">
                        <button class="btn btn-primary" onclick="hide(elt('idiv_prepare'));show(elt('ifrm_cstm_info'))">Préparer la facture</button>
                        <p style="font-size: 90%">Entrez l'adresse de facturation</p>
                    </div>
                <?php endif; ?>
                <?php
                echo $this->Form->create($sale, [
                    'url' => ['controller' => 'Sales', 'action' => 'ajaxEditCstmInfo', $sale->id],
                    'onsubmit' =>'saveCstmInfo(this);return false',
                    'id' => 'ifrm_cstm_info' ,
                    'class' => 'hidden']);
                echo $this->Form->textarea('customer_info', ['class' => 'form-control', 'style'=>'min-height: 70px']);
                ?>
                <div class="sbmt text-center">
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                    &nbsp;
                    <?php if ($sale->invoice_num) : ?>
                        <button class="btn btn-outline-danger" onclick="show(elt('idiv_cstm_info'));hide(elt('ifrm_cstm_info')); return false">Abandonner</button>
                    <?php else : ?>
                        <button class="btn btn-outline-danger" onclick="show(elt('idiv_prepare'));hide(elt('ifrm_cstm_info'));return false">Abandonner</button>
                    <?php endif ?>
                </div>
                <?php
                echo $this->Form->end();
                ?>
            </td>
        </tr>
    </table>
    <h4><?= $cnt_articles ?> Article<?= $cnt_articles > 1? 's':'' ?></h4>
    <?php if ($cnt_articles) : ?>
    <div class="mt-3"  style="max-height: 300px;overflow-y: scroll">
        <table class="table table-sm table-striped">
            <thead>
            <tr>
                <th>N°</th>
                <th>Désignation</th>
                <th>Prix de Vente</th>
                <th>&nbsp;</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($sale->items as $item) : ?>
            <tr>
                <td><?= $item->deposit_id. '-'. $item->id ?></td>
                <td><?= $item->name ?></td>
                <td><?= number_format($item->sale_price, 2, ',', '') ?></td>
                <td><?= $item->happy_hour && $item->requested_price > $item->sale_price? '<i class="fa fa-smile-o lead" aria-hidden="true" title="\'Happy hour\' appliqué"></i>' : '' ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
    <div class="text-center m-3"><button class="btn btn-outline-dark" onclick="closeModal(this)">&#10060; Fermer</button></div>
</div>
