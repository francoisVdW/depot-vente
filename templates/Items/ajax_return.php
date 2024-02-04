<?php
/**
 * Created by FVdW.
 *
 * depot vente @ raspberry
 * User: francois
 * Date: 29/12/2020
 *
 * @var string $status OK|ERROR|NONE|FATAL
 * @var string $msg
 * @var \Cake\ORM\Entity $item
 * @var array $cash_registers_opts
 *
 *
 * @copyright: 2020
 * @version $Revision: $
 */
$this->layout = 'ajax';

define('FORM_DOM_ID', 'ifrm_return');

$form_opts = [
    'onsubmit' => 'return false;',
    'id' => FORM_DOM_ID,
];

?>
<div id="idiv_modal_cont">
    <h4>Annulation de la vente &nbsp; </h4>
    <?php
    if ($status == 'FATAL'):
        echo $this->element('flash/error', ['message' => $msg, 'params' => ['escape' => false]]);
    elseif ($status == 'SUCCESS'):
        echo $this->element('flash/success', ['message' => $msg, 'params' => ['escape' => false]]);
    ?>
        <script type="application/ecmascript" id="ijs_return">
            ajdBtnReturnedItem(<?= $item->id ?>, '<?= trim(str_replace(["\n", "\r"], ' ', $item->iconProgress)) ?>');
        </script>
    <?php else: ?>
        <p class="lead">Article : <?= $item->ref ?></p>
        <p><?= $item->name ?></p>
    <?php
        if ($status == 'ERROR'):
            echo $this->element('flash/error', ['message' => $msg, 'escape' => false]);
        endif;
        echo $this->Form->create($item, $form_opts);
        echo $this->Form->control('id', ['type' => 'hidden']);
        echo $this->Form->control('type', ['label'=> 'Type de retour', 'options' => ['RETURN' => 'Retour', 'DEFECT' => 'Défectueux'], 'class' => 'form-control']);
        echo $this->Form->control('return_cause', ['label' => 'Motif (si défectueux)', 'type'=>'text', 'class' => 'form-control', 'autocomplete' => 'off']);
        echo $this->Form->control('cash_register_id', ['label'=> 'Caisse à débiter', 'options' => $cash_registers_opts, 'class' => 'form-control']);
        ?>
        <div class="text-center p-3"><a href="javascript:void" onclick="ajaxReturnItem('<?= FORM_DOM_ID ?>');return false;" class="btn btn-primary"><i class="fa fa-undo" aria-hidden="true"></i> Annuler la vente</a></div>
        <?php
        echo $this->Form->end();
    endif;
    ?>
</div>
<?php
