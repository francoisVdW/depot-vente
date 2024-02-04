<?php
/**
 * Created by FVdW.
 *
 * depot vente @ raspberry
 * User: francois
 * Date: 20/01/2021
 *
 * @copyright: 2021
 * @version $Revision: $
 *
 *
 * @var \Cake\ORM\Entity $sale
 */
$ttl = 0;
foreach($sale->items as $item) {
    if ($item->progress == 'LOCKED') {
        $ttl += $item->sale_price;
    }
}

?>
<h3>Caisse <?= $sale->cash_register->name ?>  ~  Vente n°<?= $sale->id ?></h3>
<div class="row">
    <div class="col-md-8">
        <p class="lead">Liste des articles</p>
        <?= $this->element('sale_items_list', ['items' => $sale->items]) ?>
    </div>
    <div class="col-md-4 sales form ">
            <?= $this->Form->create($sale) ?>
            <?= $this->Form->control('id', ['type' =>'hidden']) ?>
        <fieldset>
            <legend>Facture <?= empty($sale->invoice_num)? '' : '&laquo;'.$sale->invoice_num.'&raquo;' ?></legend>
            <?php
                echo $this->Form->control('customer_info', ['label' => 'Nom et adresse du client', 'class'=>'form-control', 'autocomplete' => 'off']);
            ?>
        </fieldset>
        <?= $this->element('submit_btn', ['label' => empty($sale->invoice_num)? 'Préparer la facture':'Mettre à jour la facture', 'abort_url' => false]) ?>
        <?= $this->Form->end() ?>
    </div>
    <div class="col-md-12 text-center">
        <?= $this->Html->link('En attente', ['controller'=>'CashRegisters', 'action'=>'idle', $sale->cash_register->id], ['class' => 'btn btn-lg btn-primary', 'escape'=>false]) ?>
        &nbsp;
        <?= $this->Html->link('Nouvelle vente', ['action'=>'newSale', $sale->cash_register_id], ['class' => 'btn btn-lg btn-primary', 'escape'=>false]) ?>
        <?php if ($sale->invoice_num) : ?>
        &nbsp;
        <?= $this->Html->link('Facture', ['action'=>'invoicePdf', $sale->id, 'facture'.$sale->invoice_cnt.'.pdf'], ['class' => 'btn btn-lg btn-outline-primary', 'target'=>'_blank', 'escape'=>false]) ?>
        <?php endif; ?>
    </div>
</div>
