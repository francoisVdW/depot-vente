<?php
/**
 * Created by FVdW.
 *
 * depot vente @ raspberry
 * User: francois
 * Date: 06/02/2021
 *
 * @copyright: 2021
 * @version $Revision: $
 *
 * @var App\Model\Entity\ $cashRegister
 * @var int $status  -2|-1|0|1
 * @var string $msg
 */

$this->setLayout('ajax');

$title = <<< TITLE
<h4>Ajustement du fond de caisse pour &laquo;{$cashRegister->name}&raquo;</h4>
TITLE;

?>

<div class="deposits form content">
    <?php
    if ($status == -2) {
        // Erreur fatale
        echo $title;
        echo $this->element('flash/error', ['message' => $msg]);
    } elseif($status == 1) {
    ?>
        <script type="application/ecmascript" id="ijs_return">
            var e = elt("i_user_<?= $cashRegister->id?>");
            if (e) e.innerHTML = "<?= $cashRegister->user_title ?>";
            e = elt("i_ip_<?= $cashRegister->id?>");
            if (e) e.innerHTML = "";
        </script>
        <?php
        echo $title;
        echo $this->element('flash/success', ['message' => $msg]);
    } else {
        echo $title;
        if ($status == -1) echo $this->element('flash/error', ['message' => $msg]);
        ?>
        <div class="alert alert-warning p-2">
            <i class="fa fa-exclamation-circle fa-lg" aria-hidden="true"></i>
            <strong>Attention</strong>  : le changement d'utilisateur d'une caisse peut provoquer<br>
            des incohérences dans la base de données.<br>
            Assurez-vous que la caisse &laquo;<?= $cashRegister->name ?>&raquo; est effectivement<br>
            bloquée malgré un rafraîchisssement de la page.
        </div>
        <?php
        // Form
        echo $this->Form->create($cashRegister, ['onsubmit' =>'ajaxSave(this);return false'])
        ?>
            <fieldset>
                <?php
                echo $this->Form->control('user_id', ['label' => 'Nouvel utilisateur', 'class' => 'form-control' ]);
                ?>
            </fieldset>
            <div class="mt-2 mb-2 text-center sbmt" style="height:40px">
                <?= $this->Form->submit('Enregistrer', ['class' => 'btn btn-primary']) ?>
            </div>
            <?= $this->Form->end() ?>
<?php  } ?>
</div>
