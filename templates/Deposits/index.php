<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Deposit[]|\Cake\Collection\CollectionInterface $deposits
 */

$this->prepend('script', $this->Html->script(['axios']));
?>
<div class="deposits index content">
    <h3>Liste des dépôts</h3>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>&nbsp;</th>
                <th style="width: 30px">N°</th>
                <th>Déposant</th>
                <th>Tél</th>
                <th>e-mail</th>
                <th>Articles</th>
                <th class="actions" style="width: 130px">&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($deposits as $deposit): ?>
            <tr>
                <td><?= $deposit->progressIcon ?></td>
                <td><?= $deposit->id ?></td>
                <td id="i_title_<?= $deposit->id ?>"><?= h($deposit->title) ?></td>
                <td id="i_tel_<?= $deposit->id ?>"><?= $deposit->numTel ?></td>
                <td id="i_email_<?= $deposit->id ?>"><?= $deposit->email ?></td>
                <td><?= $deposit->item_cnt ?></td>
                <td class="actions" style="width:120px">
                    <?= $this->Modal->ajaxModal('<i class="fa fa-search" aria-hidden="true"></i>', ['controller'=>'Deposits', 'action' => 'ajaxView', $deposit->id],['class' => 'btn btn-outline-dark btn-sm', 'title' => 'Voir le dépôt', 'escape' => false]) ?>
                    <?= $this->Modal->ajaxModal('<i class="fa fa-pencil-square-o" aria-hidden="true"></i>', ['controller'=>'Deposits', 'action' => 'ajaxEdit', $deposit->id],['class' => 'btn btn-outline-dark btn-sm', 'title' => 'Modifier les informations du déposant', 'escape' => false]) ?>
                    <?php
                    if ($deposit->progress == 'EDIT') {
                        echo $this->Html->link('<i class="fa fa-list-alt" aria-hidden="true"></i>', ['controller' => 'Items', 'action' => 'register', $deposit->id], ['escape' => false, 'class' => 'btn btn-warning btn-sm', 'title' => 'Continuer la saisie des articles']);
                    } else {
                        echo $this->Html->link('<i class="fa fa-file-pdf-o" aria-hidden="true"></i>', ['controller' => 'Deposits', 'action' => 'pdf', $deposit->id, "depot_{$deposit->id}.pdf"], ['escape' => false, 'class' => 'btn btn-outline-dark btn-sm', 'target'=>'_blank', 'title' => 'Document PDF']);
                    }?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="text-center">
        <?= $this->Html->link('Nouveau dépot', ['action' => 'add'], ['class' => 'btn btn-primary', 'escape' => false]) ?>
    </div>
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

saveDeposit = function (frm, add) {
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
