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

if ($cashRegister) {
    $title = <<< TITLE
<h4>Suppression de la caisse &laquo;{$cashRegister->name}&raquo;</h4>
TITLE;
} else {
    $title = "Supression d'une caisse";
}
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
            var e = elt("iCashRegister_<?= $cashRegister->id?>");
            if (e) hide(e);
        </script>
        <?php
        echo $title;
        echo $this->element('flash/success', ['message' => $msg]);
    } else {
        echo $title;
        if ($status == -1) echo $this->element('flash/error', ['message' => $msg]);
            // Form
        echo $this->Form->create($cashRegister, ['onsubmit' =>'ajaxSave(this);return false'])
        ?>
            <p class="lead">Confirmez la supression de la caisse &laquo;<?= $cashRegister->name ?>&raquo;</p>
            <div class="mt-2 mb-2 text-center sbmt" style="height:40px">
                <?= $this->Form->submit('Supprimer !', ['class' => 'btn btn-primary']) ?>
            </div>
            <?= $this->Form->end() ?>
<?php  } ?>
</div>
