<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 * @var string $msg
 * @var int $status  0 = afficher <form> | 1 = succes MaJ | -1 = Erreur de MaJ
 */
$this->setLayout('ajax');

$title = empty($user)? '' : $user->title;
?>
<div class="users form content">
    <?php
    if ($status == -2) {
        // Erreur fatale
        echo $this->element('flash/error', ['message' => $msg]);
    } elseif($status == 1) {
        $tr = str_replace(["\n","\r", "'"], [' ','', "\\'"], $this->element('user_tr', ['user' => $user]));
        ?>
        <script type="application/ecmascript" id="ijs_return">
            var e = elt("itr_<?= $user->id?>");
            if (e) e.innerHTML = '<?= $tr ?>';
            activateModal("iModalEdit_<?=$user->id ?>");
            activateModal("iModalDelete_<?=$user->id ?>");
        </script>
        <h4>Modification utilisateur <em><?= $title ?></em></h4>
        <?php
        echo $this->element('flash/success', ['message' => $msg]);
    } else {
        ?>
    <h4>Modification utilisateur <em><?= $title ?></em></h4>
    <?php
        if ($status == -1) echo $this->element('flash/error', ['message' => $msg]);
        // Form
        echo $this->Form->create($user, ['onsubmit' => 'ajaxSubmit(this);return false;']);
        echo $this->Form->control('last_name', ['label' => 'NOM', 'class' => 'form-control', 'autocomplete' => 'off']);
        echo $this->Form->control('first_name', ['label' => 'prénom', 'class' => 'form-control', 'autocomplete' => 'off']);
        echo $this->Form->control('username', ['label' => 'Nom de connexion', 'class' => 'form-control', 'autocomplete' => 'off']);
        echo $this->Form->control('password' , [
            'label' => 'Mot de passe <a href="javascript:void(0)" onclick="togglePwd()" class="btn btn-sm btn-outline-dark"><i class="fa fa-edit fa-sm"></i></a>',
            'type' => 'password',
            'disabled' => true,
            'class' => 'form-control hidden',
            'autocomplete' => 'off',
            'placeholder' => 'Nouveau mot de passe',
            'value' => '',
            'escape' => false]);
        ?><div class="input">
            <label class="required">Role</label>
            <?= $this->Form->select('a_role', \Cake\Core\Configure::read('roles'), [
                'multiple' => 'checkbox',
            ]); ?>
        </div>
        <div class="mt-2 mb-2 text-center sbmt" style="height:40px">
            <?= $this->Form->submit('Enregistrer', ['class' => 'btn btn-primary']) ?>
        </div>
        <?php
        echo $this->Form->end();
        // /Form
    }
    ?>
</div>
