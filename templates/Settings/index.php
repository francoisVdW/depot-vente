<?php
/**
 * Created by FVdW.
 *
 * depot vente @ raspberry
 * User: francois
 * Date: 04/02/2021
 *
 * @copyright: 2021
 * @version $Revision: $
 *
 * @var $settings
 */

$this->prepend('script', $this->Html->script(['axios']));
$bool_opts = ['Y' => 'Oui', 'N' => 'Non'];

?>
<div class="row">
    <div class="col-md-12">
        <h3>Paramètres de l'application</h3>
    </div>
    <div class="col-md-3">
        <p>Configuration du dépôt/vente : ajustez les paramètres avant la mise en route de l'opération.</p>
        <p>
            <?= $this->Modal->ajaxModal('Vérifier le paramétrage', ['controller'=>'Settings', 'action' => 'ajaxCheck'],['class' => 'btn btn-primary btn-block', 'title' => 'Vérifier la cohérence des paramètres', 'escape' => false]) ?>
        </p>
        <p>
            <?= $this->Html->link('Adminstration', ['controller' => 'admin-functions', 'action' => 'home'], ['class' => 'btn btn-primary btn-block', 'title' => 'Retour à la page d\'adminstration']) ?>
        </p>
        <div class="alert alert-info mt-5">
            <i class="fa fa-info-circle" aria-hidden="true"></i> Le bon de dépôt utilise une page de fond ou <i>template</i> : un document PDF. <br>
            Pour générer vos bons de dépôts, remplacez le fichier <code>pdf_template.pdf</code>
            situé sous <code>vendor/vdw/tcpdf</code> par celui contenant le logo et nom de votre association.
        </div>
    </div>
    <div class="col-md-9 settings">
        <table class="table table-hover">
            <thead>
            <tr>
                <th>Paramètre</th>
                <th>Valeur</th>
                <th>&nbsp;</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach($settings as $set) : ?>
                <tr>
                    <th><?= $set['name'] ?></th>
                    <td id="itd_<?= $set->id ?>">
                        <?= $set->fmtValue ?>
                    </td>
                    <td>
                        <?= $this->Modal->ajaxModal('<i class="fa fa-edit" title="modifier"></i>', ['controller'=>'Settings', 'action' =>'ajaxEdit', $set->id], ['class' => 'btn btn-sm btn-outline-dark ml-1']) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
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

saveSetting = function (frm, add) {
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
