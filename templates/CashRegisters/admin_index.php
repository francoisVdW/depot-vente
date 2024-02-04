<?php
/**
 * Created by FVdW.
 *
 * depot vente @ raspberry
 * User: francois
 * Date: 05/02/2021
 *
 * @copyright: 2021
 * @version $Revision: $
 *
 * @var $cashRegisters
 *
 */

$this->prepend('script', $this->Html->script(['axios']));

?>
<div class="cashRegister index content row">
    <div class="col-md-12">
        <h2>Gestion des caisses</h2>
    </div>
    <div class="col-md-4">
        <div class="alert alert-info">
            Depuis cet écran, créez les "caisses" (points de vente, ...) que vous voulez mettre en place pour les ventes.<br>
            Pour effectuer une vente, un utilisateur ayant le profil <em>Vente</em> (ou <em>Gestion</em>) devra se connecter à l'application, puis depuis la page "Ventes" selectionner la caisse sur laquelle il veut opérer.

        </div>
        <?= $this->Modal->ajaxModal('<i class="fa fa-plus" aria-hidden="true"></i> Ajouter une caisse', ['controller'=>'CashRegisters', 'action' => 'ajaxAdd'],['class' => 'btn btn-primary btn-block', 'title' => 'Modifier', 'escape' => false]) ?>
    </div>
    <div class="col-md-8">
        <table class="table">
            <thead>
            <tr>
                <th colspan="2">Nom de la caisse</th>
                <th style="width: 80px">Fond</th>
                <th>Utilisateur</th>
                <th>IP</th>
                <th>&nbsp;</th>
            </tr>
            </thead>
            <tbody id="iCashRegisters">
            <?php foreach($cashRegisters as $cashRegister) :  ?>
                <tr id="iCashRegister_<?= $cashRegister->id ?>">
                    <?= $this->element('cash_register_tr', ['cashRegister' => $cashRegister]) ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <div class="text-center">
            <?= $this->Modal->ajaxModal('<i class="fa fa-plus" aria-hidden="true"></i> Ajouter une caisse', ['controller'=>'CashRegisters', 'action' => 'ajaxAdd'],['class' => 'btn btn-primary', 'title' => 'Modifier', 'escape' => false]) ?>
        </div>
    </div>
</div>
<?php
// ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
$this->Html->scriptStart(['block' => true]);  ?>
var elt = function(id) {return document.getElementById(id);}

_ajaxError = function(error) {
    ajax_running = false;
    if (typeof error.response != "undefined") {
        if (error.response.status == 403) {
            alert("Votre session a expiré, vous devez vous re-connecter !");
            window.location.reload(false);
        } else {
            alert("Erreur : "+error.response.status)
            console.log("catch...", error.response);
        }
    } else {
        alert("Erreur indéterminée !")
        console.log("catch...", error);
    }
}

var ajax_running = false;

ajaxSave = function (frm) {
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
