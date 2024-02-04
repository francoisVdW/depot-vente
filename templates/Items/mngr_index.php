<?php
/**
 * Created by FVdW.
 *
 * depot vente @ raspberry
 * User: francois
 * Date: 25/01/2021
 *
 * @copyright: 2021
 * @version $Revision: $
 *
 *
 *
 * @var $items
 * @var bool $compteted  : vente terminée
 */

$this->prepend('script', $this->Html->script(['axios']));

?>
<h3>Liste des articles</h3>
<?= $this->element('search_frm',['controls' => [
    'id' => ['label' => 'N°', 'placeholder'=> 'Numéro de l\'article'],
    'name' => ['label' => 'Description', 'placeholder'=> '...ou code barre ou réf. fabricant'],
]]) ?>
<?php if (count($items)) : ?>
<table class="table table-striped table-sm" id="itbl_items">
    <thead>
    <tr>
        <th></th>
        <th style="width: 25px;text-align: right"><?= $this->Paginator->sort('deposit_id', 'Dép.') ?></th>
        <th style="width: 40px;text-align: left"><?= $this->Paginator->sort('id', 'N°') ?></th>
        <th>Désignation</th>
        <th>&nbsp;</th>
        <th>N° fabricant</th>
        <th>Code Barre</th>
        <th style="width: 80px;font-size: 80%">Mise en vente</th>
        <th>&nbsp;</th>
        <th style="width: 80px;font-size: 80%">Vendu</th>
        <th style="width: 80px;font-size: 80%">Déposant</th>
        <th>&nbsp;</th>
        <th>&nbsp;</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach($items as $item) : ?>
    <tr>
        <td class="text-center" id="itd_prg_<?= $item->id ?>"><?= $item->iconProgress ?></td>
        <td style="text-align: right"><?= $item->deposit_id ?></td>
        <td style="text-align: left"><?= $item->id ?></td>
        <td><?= $item->name ?></td>
        <td class="text-center"><?= $item->iconColor ?></td>
        <td style="font-size: 90%"><?= $item->mfr_part_no ?></td>
        <td style="font-size: 90%"><?= $item->bar_code ?></td>
        <td class="text-right"><?= number_format($item->requested_price, 2, '.', '') ?> &euro;</td>
        <td class="text-center"><?= $item->iconHappyHour ?></td>
        <?php if ($item->progress == 'RETURNED') : ?>
            <td colspan="2" style="font-size:90%"><i>Vente annulée : </i><span><?=$item->return_cause ?></span></td>
        <?php else: ?>
        <td class="text-right"><?= $item->progress == 'SOLD'? number_format($item->sale_price, 2, '.',''). '€': '&nbsp;' ?></td>
        <td class="text-right"><?= $item->progress == 'SOLD'? number_format($item->returnPrice, 2, '.', ''). '€': '&nbsp;' ?></td>
        <?php endif; ?>
        <td id="itd_act_<?= $item->id ?>">
            <?= $this->Modal->ajaxModal('<i class="fa fa-search" aria-hidden="true"></i>', ['controller'=>'Deposits', 'action' => 'ajaxView', $item->deposit_id],['class' => 'btn btn-outline-dark btn-sm', 'title' => 'Voir le dépôt', 'escape' => false]) ?>
            &nbsp;
            <?= $this->Html->link('<i class="fa fa-file-pdf-o" aria-hidden="true"></i>', ['controller' => 'Deposits', 'action' => 'pdf', $item->deposit_id, "depot_{$item->deposit_id}.pdf"], ['escape' => false, 'class' => 'btn btn-outline-dark btn-sm', 'target'=>'_blank', 'title' => 'Bon dépôt PDF']) ?>
            &nbsp;
            <?php if ($item->progress == 'SOLD' && !$compteted) :?>
                <?= $this->Modal->ajaxModal('<i class="fa fa-undo" aria-hidden="true"></i>', ['controller'=>'Items', 'action' => 'ajaxReturn', $item->id],['class' => 'btn btn-outline-dark btn-sm return', 'title' => 'Retour : annuler la vente', 'escape' => false]) ?>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?= $this->element('pagin') ?>
<script type="application/ecmascript">
    var elt = function(dom_id) { return document.getElementById(dom_id);}
    var hasClass = function (el, className) {
        return el.classList ? el.classList.contains(className) : new RegExp('\\b'+ className+'\\b').test(el.className);
    }

    var addClass = function (el, className) {
        if (el.classList) el.classList.add(className);
        else if (!hasClass(el, className)) el.className += ' ' + className;
    }

    var removeClass = function (el, className) {
        if (el.classList) el.classList.remove(className);
        else el.className = el.className.replace(new RegExp('\\b'+ className+'\\b', 'g'), '');
    }

    ajax_running = false;
    ajaxReturnItem = function (frm_id) {
        let frm = document.getElementById(frm_id);
        let frm_data = new FormData(frm);

        axios.post(frm.action, frm_data)
            .then(function (response) {
                ajax_running = false;
                let div = elt("idiv_modal_cont");
                if (div) {
                    div.outerHTML = response.data;
                    let s = elt("ijs_return");
                    if(s) eval(s.innerHTML);
                }
            })
            .catch(function (error) {
                ajax_running = false;
                if (typeof error.response != "undefined") {
                    if (error.response.status == 403) {
                        alert("Votre session a expiré, vous devez vous re-connecter !");
                        window.location.reload(false);
                    } else {
                        console.log("catch...", error.response);
                    }
                } else {
                    console.log("catch...", error);
                }
            });

    }

    ajdBtnReturnedItem = function(id, icon) {
        let td = elt("itd_prg_"+id);
        let children = td.childNodes;
        for (let i = 0; i < children.length; i++) {
            if (children[i].tagName == "I") {
                children[i].outerHTML = icon;
                break;
            }
        }
        td = elt("itd_act_"+id);
        children = td.childNodes;
        for (let i = 0; i < children.length; i++) {
            if (hasClass(children[i], "return")) {
                children[i].outerHTML = "";
                break;
            }
        }
    }
</script>


<?php else : ?>
<i>Aucun article trouvé</i>
<?php endif;
