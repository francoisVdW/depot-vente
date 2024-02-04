<?php
/**
 * @var \App\View\AppView $this
 * @var string $msg
 * @var int $user_id
 * @var int $status  0 = afficher <form> | 1 = succes MaJ | -1 = Erreur de MaJ
 */
$this->setLayout('ajax');

$title = empty($user)? '' : $user->title;
?>
<div class="users form content">
    <h4>Supression de l'utilisateur <em><?= $title ?></em></h4>
    <?php
    if ($status == -2) {
        echo $this->element('flash/error', ['message' => $msg]);
    } elseif($status == 1) {
    ?>
        <script type="application/ecmascript" id="ijs_return">
            var t = elt("itbl_users");
            if (t) {
                var tr = elt("itr_<?= $user->id?>");
                if (tr) hide(tr);
            }
        </script>
        <?php
        echo $this->element('flash/success', ['message' => $msg]);
    } else {
        if($status == 1) {
            echo $this->element('flash/error', ['message' => $msg]);
        }
    ?>
    <div class="text-center p-3">
        <p class="lead">Confirmez la supression ...</p>
        <?php
        // form
        echo $this->Form->create($user, ['onsubmit' => 'ajaxSubmit(this);return false;']);
        ?>
        <div class="mt-2 mb-2 text-center sbmt" style="height:40px">
            <?= $this->Form->submit('Supprimer', ['class' => 'btn btn-primary']) ?>
        </div>
        <?php
        echo $this->Form->end();
        // /Form
        ?>
    </div>
    <?php } ?>
</div>
