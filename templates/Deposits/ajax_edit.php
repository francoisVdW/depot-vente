<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Deposit $deposit
 * @var string[] $eregs
 * @var int $status
 * @var string $msg
 */
$this->setLayout('ajax');
?>
<div class="deposits form content">
    <?php
    if ($status == -2) {
        // Erreur fatale
        echo $this->element('flash/error', ['message' => $msg]);
    } elseif($status == 1) {


    ?>
    <script type="application/ecmascript" id="ijs_return">
        var e = elt("i_title_<?= $deposit->id?>");
        if (e) e.innerHTML = '<?= h($deposit->title) ?>';
        e = elt("i_tel_<?= $deposit->id?>");
        if (e) e.innerHTML = '<?= $deposit->numTel ?>';
        e = elt("i_email_<?= $deposit->id?>");
        if (e) e.innerHTML = '<?= $deposit->email ?>';
    </script>
    <h4>Modification du déposant <em><?= $deposit->title ?></em></h4>
    <?php
    echo $this->element('flash/success', ['message' => $msg]);
    } else {
    ?>
    <h4>Modification du déposant <em><?= $deposit->title ?></em></h4>
    <?php
        if ($status == -1) echo $this->element('flash/error', ['message' => $msg]);
        // Form
        echo $this->Form->create($deposit, ['onsubmit' =>'saveDeposit(this);return false'])
    ?>
        <fieldset>
            <legend>Dépot n° <?= $deposit->id ?></legend>
            <?php
            echo $this->Form->control('title', ['label' => 'Nom prénom', 'class' => 'form-control', /*'pattern' => $eregs['title']*/ ]);
            echo $this->Form->control('phone', ['label' => 'Téléphone', 'class' => 'form-control', /*'pattern' => $eregs['phone']*/ ]);
            echo $this->Form->control('email', ['label' => 'E-mail', 'class' => 'form-control']);
            ?>
        </fieldset>
        <div class="mt-2 mb-2 text-center sbmt" style="height:40px">
            <?= $this->Form->submit('Enregistrer', ['class' => 'btn btn-primary']) ?>
        </div>

        <?= $this->Form->end() ?>
    </div>
</div>
<?php
    }
