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
<h4>Ajout d'une caisse</h4>
TITLE;

?>

<div class="deposits form content">
    <?php
    if ($status == -2) {
        // Erreur fatale
        echo $title;
        echo $this->element('flash/error', ['message' => $msg, 'escape' => false]);
    } elseif($status == 1) {
    ?>
        <script type="application/ecmascript" id="ijs_return">
            var tbody = elt("iCashRegisters");
            if (tbody) {
                var tr = document.createElement("TR");
                tr.setAttribute("id", "iCahsRegister_<?= $cashRegister->id ?>");
                tr.innerHTML = '<?= str_replace(["\n", "\r"], [' ', ''], $this->element('cash_register_tr', ['cashRegister' => $cashRegister])) ?>';
                tbody.appendChild(tr);
            }
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
            <fieldset>
                <?php
                echo $this->Form->control('name', ['label' => 'Nom de la caisse', 'class' => 'form-control', 'maxlength' => 10, 'placeholder' => 'par ex : "3" ou "n°5" ou "principale" (10 caractères max)' ]);
                echo $this->Form->control('cash_fund', ['label' => 'Fond de caisse', 'class' => 'form-control', 'min' => 0, 'max' => 1000, 'step' => '0.1' ]);
                ?>
            </fieldset>
            <div class="mt-2 mb-2 text-center sbmt" style="height:40px">
                <?= $this->Form->submit('Créer', ['class' => 'btn btn-primary']) ?>
            </div>
            <?= $this->Form->end() ?>
<?php  } ?>
</div>
