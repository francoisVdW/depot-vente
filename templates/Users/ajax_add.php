<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 * @var string $msg
 * @var int $status  0 = afficher <form> | 1 = succes MaJ | -1 = Erreur de MaJ
 */
$this->setLayout('ajax');

if($status == 1) {
    $tr = str_replace(["\n","\r", "'"], [' ','', "\\'"], $this->element('user_tr', ['user' => $user]));
    ?>
    <script type="application/ecmascript" id="ijs_return">
        var t = elt("itbl_users");
        if (t) {
            var ntr = document.createElement('tr');
            ntr.id = "itr_<?=$user->id ?>";
            ntr.innerHTML = '<?= $tr ?>';
            t.appendChild(ntr);
            activateModal("iModalEdit_<?=$user->id ?>");
            activateModal("iModalDelete_<?=$user->id ?>");
        }
    </script>
    <h4>Nouvel utilisateur</h4>
    <?php
    echo $this->element('flash/success', ['message' => $msg]);

} else {
?>
<div class="users form content">
    <h4>Nouvel utilisateur</h4>
    <?php
    if($status == -1) echo $this->element('flash/error', ['message' => $msg]);
    echo $this->Form->create($user, ['onsubmit' => 'ajaxSubmit(this);return false;']);
    echo $this->Form->control('last_name', ['label' => 'NOM', 'class' => 'form-control', 'autocomplete' => 'off']);
    echo $this->Form->control('first_name', ['label' => 'prénom', 'class' => 'form-control', 'autocomplete' => 'off']);
    echo $this->Form->control('username', ['label' => 'Nom de connexion', 'class' => 'form-control', 'autocomplete' => 'off']);
    echo $this->Form->control('password' , [
        'label' => 'Mot de passe',
        'type' => 'password',
        'class' => 'form-control',
        'autocomplete' => 'off',
        'value' => '']);
    ?>
    <div class="input">
        <label class="required">Role</label>
        <?= $this->Form->select('a_role', \Cake\Core\Configure::read('roles'), [
        'multiple' => 'checkbox',
        ]); ?>
    </div>
    <div class="mt-2 mb-2 text-center sbmt" style="height:40px">
        <?= $this->Form->submit('Enregistrer', ['class' => 'btn btn-primary']) ?>
    </div>
    <?= $this->Form->end() ?>
</div>
<?php
}
