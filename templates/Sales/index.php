<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Sale[]|\Cake\Collection\CollectionInterface $sales
 */

$this->prepend('script', $this->Html->script(['axios']));
?>
<div class="sales index content">

    <h3>Liste des ventes</h3>
    <table class="table table-striped">
        <thead>
            <tr>
                <th style="width: 30px">&nbsp;</th>
                <th>N°</th>
                <th style="width: 60px">Montant</th>
                <th>Facture</th>
                <th>date</th>
                <th class="actions" style="width: 130px">&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sales as $sale): ?>
            <tr>
                <td><?= $sale->stateIcon ?></td>
                <td><?= $sale->name ?></td>
                <td style="text-align: right"><?= number_format($sale->total_price, 2,',','') ?> &euro;</td>
                <td><?= $sale->invoice_num? $sale->invoice_num : '&nbsp;' ?></td>
                <td><?= $sale->created->format('d/m/Y H:i') ?></td>
                <td class="actions" style="width:120px">
                    <?= $this->Modal->ajaxModal('<i class="fa fa-search" aria-hidden="true"></i>', ['controller'=>'Sales', 'action' => 'ajaxView', $sale->id],['class' => 'btn btn-outline-dark btn-sm', 'title' => 'Voir le dépôt', 'escape' => false]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?= $this->element('pagin') ?>
</div>
<?php
// ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
$this->Html->scriptStart(['block' => true]);  ?>

_ajaxError = function(error) {
    ajax_running = false;
    if (typeof error.response != "undefined") {
        if (error.response.status == 403) {
            alert("Votre session a expiré, vous devez vous re-connecter !");
            window.location.reload(false);
        } else {
            alert("Erreur : "+error.response.status)
            console.log("catch...", error.message);
        }
    } else {
        alert("Erreur indéterminée !")
        console.log("catch...", error);
    }
}

var ajax_running = false;

saveCstmInfo = function (frm) {
    if (ajax_running) return;
    ajax_running = true;
    let l = document.querySelectorAll("#modal_container_ajax .sbmt");
    if (l.length)
        l[0].innerHTML = '<i class="fa fa-spinner fa-spin fa-2x"></i>';
    let frm_data = new FormData(frm);
    axios.post(frm.action, frm_data)
        .then(function (response) {
            ajax_running = false;
            let div = elt("modal_container_ajax");
            if (div) {
                div.innerHTML = response.data;
                let s = elt("ijs_return");
                if (s) eval(s.innerHTML);
            }
        })
        .catch(function (error) { _ajaxError(error); });
}
<?php $this->Html->scriptEnd();
// ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
