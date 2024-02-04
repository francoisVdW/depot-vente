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
        <?= $this->element('sale_items_list', ['items' => $sale->items, 'chk_locked' => true]) ?>
    </div>
    <div class="col-md-4 sales form ">
            <?= $this->Form->create($sale) ?>
            <?= $this->Form->control('id', ['type' =>'hidden']) ?>
        <fieldset>
            <legend>Paiement</legend>
            <div>
                <label for="ttl">Total à payer</label>
                <p class="form-control"><?= number_format($ttl, 2, '.', '') ?> &euro;</p>
            </div>
            <?php
                echo $this->Form->control('pay_cash', ['label' => 'En espèce', 'class' => 'form-control pay', 'step'=>'0.01', 'min'=>0, 'max'=>$ttl, 'autocomplete' => 'off']);
                echo $this->Form->control('pay_chq', ['label' => 'Par Chèque', 'class' => 'form-control pay', 'step'=>'0.01', 'min'=>0, 'max'=>$ttl, 'autocomplete' => 'off']);
                echo $this->Form->control('pay_other', ['label' => 'Autre',    'class' => 'form-control pay', 'step'=>'0.01', 'min'=>0, 'max'=>$ttl, 'autocomplete' => 'off']);
            ?>
        </fieldset>
        <?= $this->element('submit_btn', ['label' => 'Payer !', 'abort_url' => ['controller' => 'Sales', 'action'=>'proceed', $sale->id], 'abort_label' => 'Retour']) ?>
        <?= $this->Form->end() ?>
    </div>
</div>
