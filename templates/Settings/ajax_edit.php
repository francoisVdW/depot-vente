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
 *
 * @var App\Model\Entity\ $setting
 * @var int $status   -2|-1|0|1
 * @var string $msg
 */
$this->setLayout('ajax');
$bool_opts = ['1' => 'Oui', '0' => 'Non'];

?>
<div class="settings form content">
    <?php
    if ($status == -2) {
        echo $this->element('flash/error', ['message' => $msg]);
    } elseif($status == 1) {
        ?>
        <script type="application/ecmascript" id="ijs_return">
            var e = elt("itd_<?= $setting->id?>");
            if (e) e.innerHTML = '<?= $setting->fmtValue ?>';
        </script>
        <h4>Modification du paramètre <em></h4>
        <?php
        echo $this->element('flash/success', ['message' => $msg]);
    } else {
        ?>
        <h4>Modification du paramètre <em></h4>
        <p class="lead"><em><?= $setting->name ?></em></p>
        <?php
        if ($status == -1) echo $this->element('flash/error', ['message' => $msg]);
        // Form
        echo $this->Form->create($setting, ['onsubmit' => 'saveSetting(this);return false;']);
        echo $this->form->hidden('type', ['value' => $setting->type]);
        $opt =  ['label' => 'Valeur', 'class' => 'form-control'];
        switch ($setting->type) {
            case 'STRING':
                $opt['type'] = 'text';
                $opt['maxlength'] = 200;
                echo $this->Form->control('data', $opt);
                break;
            case 'TEXT':
                echo $this->Form->control('data', $opt);
                break;
            case 'INT':
                $opt['type'] = 'number';
                $opt['step'] = 1;
                echo $this->Form->control('data_numeric', $opt);
                break;
            case 'BOOL':
                $opt['type'] = 'select';
                $opt['options'] = $bool_opts;
                echo $this->Form->control('data_numeric', $opt);
                break;
            case 'DATE':
                $opt['type'] = 'datetime';
                echo $this->Form->control('data_date', $opt);
                break;
            case 'JSON_OPTIONS':
                if ($setting->key_name == 'colors') {
                    $values = array_keys($setting->value);
                    ?>
<div class="input">
    <label >Sélectionnez les couleurs utilisables pour ce dépôt/vente (ou aucune)</label>
    <?= $this->Form->select(
        'colors',
        \Cake\Core\Configure::read('items.available_colors'),
        ['multiple' => 'checkbox', 'escape' => false, 'value' => $values]
    ); ?>
</div>
                <?php
                }
                break;
        }
        ?>
        <div class="mt-2 mb-2 text-center sbmt" style="height:40px">
            <?= $this->Form->submit('Enregistrer', ['class' => 'btn btn-primary']) ?>
        </div>
        <?php
        echo $this->Form->end();
    }
    ?>
</div>
