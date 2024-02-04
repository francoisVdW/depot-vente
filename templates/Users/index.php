<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User[]|\Cake\Collection\CollectionInterface $users
 */

$this->prepend('script', $this->Html->script(['axios']));
?>
<div class="users index content row">
    <div class="col-md-12">
        <h3>Liste des utilisateurs</h3>
    </div>
    <div class="col-md-4">
        <div class="alert alert-info">
            Liste des utilisateurs du Dépôt/Vente.<br>
            Chaque utilisateur doit avoir au moins un rôle:
            <ul style="font-size: 90%">
                <li><strong>Administrateur</strong> : utilisateur ayant tous les droits sur l'application : Il faut au moins un administrateur pour configurer l'application. Les adminstateurs gèrent les utilisteurs</li>
                <li><strong>Gestion</strong> : utilisateur qui a les droits pour enregistrer les dépôts, effectuer des ventes mais aussi voir les tableaux de ventes, effectuer les retours et clôturer le dépôt/vente</li>
                <li><strong>Dépôt</strong> : utilisateur qui a les droits pour enregistrer les dépôts.</li>
                <li><strong>Vente</strong> : utilisateur qui a les droits pour effectuer les ventes.</li>
            </ul>
        </div>
        <p>
            <?= $this->Html->link('Adminstration', ['controller' => 'admin-functions', 'action' => 'home'], ['class' => 'btn btn-primary btn-block', 'title' => 'Retour à la page d\'adminstration']) ?>
        </p>
        <p>
            <?= $this->Modal->ajaxModal('<i class="fa fa-plus" aria-hidden="true"></i> Nouvel utilisateur', ['controller'=>'Users', 'action' => 'ajaxAdd'],['class' => 'btn btn-primary btn-block', 'title' => 'Ajouter un utilisateur', 'escape' => false]) ?>
        </p>
    </div>
    <div class="col-md-8">
        <table class="table table-striped" id="itbl_users">
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('last_name', 'Nom') ?></th>
                    <th><?= $this->Paginator->sort('first_name', 'Prenom') ?></th>
                    <th><?= $this->Paginator->sort('username', 'Nom de connexion') ?></th>
                    <th>Profil</th>
                    <th class="actions"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr id="itr_<?= $user->id ?>">
                    <?= $this->element('user_tr', ['user' => $user]) ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?= $this->element('pagin') ?>
        <div class="text-center">
            <?= $this->Modal->ajaxModal('<i class="fa fa-plus" aria-hidden="true"></i> Nouvel utilisateur', ['controller'=>'Users', 'action' => 'ajaxAdd'],['class' => 'btn btn-primary', 'title' => 'Ajouter un utilisateur', 'escape' => false]) ?>
        </div>
    </div>
</div>
<?php
// ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
$this->Html->scriptStart(['block' => true]);  ?>
var togglePwd = function() {
    let pwd = elt("password");
    if (pwd.disabled) {
        pwd.disabled = false;
        removeClass(pwd, 'hidden');
    } else {
        pwd.disabled = true;
        addClass(pwd, 'hidden');
    }
}

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

ajaxSubmit = function (frm, add) {
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
