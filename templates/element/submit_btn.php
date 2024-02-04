<?php
/**
 * Created by PhpStorm.
 * User: francois
 * Date: 26/02/2019  Time: 16:07
 *
 * @version $Rev: 18 $
 *
 * Affiche les 2 boutons Enregistrer/Créer - Annuler à placer en fin de <form>
 *
 * NB : utilise vanilla JS
 * @var int $entity_id
 * @var string $label
 * @var string|false $abort_action
 * @var array|false $abort_url
 * @var string $abort_label
 *
 *
 * Attend 3 paramètre : [$entity_id]
 *                      [$label] : defaut = "Créer"
 *                      [$abort_action|$abort_url] :
 * 							abort_action: index|view default = 'view'
 *                      	abort_url : array avec tous les elements pour la redirection
 *                          Si abort_url === false | abort_action === false -> pas de btn abort
 * 						[$abort_label] : defaut = "Abandonner"
 *
 * Ex Appel
 * <?= $this->element('submit_btn', ['entity_id'=>$article->id, 'abort_action' => 'index']);
 * param entity_id
 */

$show_abort = true;
if (
    (isset($abort_action)  && $abort_action === false && empty($abort_url)) ||
    (isset($abort_url) && $abort_url === false && empty($abort_action))
    ) {
    $show_abort = false;
}

if (empty($entity_id)) {
    $entity_id = false;
}

$img_dom_id = 'iimg_wait_' . ($entity_id ? 'e'.$entity_id : sprintf("%05X",rand(99,99999)));
$span_dom_id = 'span_' . ($entity_id? 'e'.$entity_id : sprintf("%05X",rand(99,99999)));

if (empty($label)) {
    $label = $entity_id? 'Enregistrer' : 'Créer';
}
if (empty($abort_label)) {
    $abort_label = 'Abandonner';
}

if (empty($abort_url)) {
	if (!$entity_id) {
		$abort_url = ['action' => 'index'];
	} else {
		if (!empty($abort_action) && $abort_action == 'index') {
			$abort_url = ['action' => 'index'];
		} else {
			$abort_url = ['action' => 'view', $entity_id];
		}
	}
}
?>
<div class="text-center" style="padding:10px 0 30px 0;height: 65px">
    <span id="<?= $span_dom_id ?>">
        <?= $this->Form->button($label, ['class' => 'btn btn-primary btn-submit', 'onclick' => 'if(form.reportValidity()){document.querySelector("#'.$span_dom_id.'").style.display="none";document.querySelector("#'.$img_dom_id.'").style.display="inline"}']) ?>
        &nbsp;&nbsp;
        <?php if ($show_abort) {
            echo $this->Html->link('<i class="fa fa-window-close" aria-hidden="true"></i> '.$abort_label, $abort_url, ['class' => 'btn btn-outline-danger', 'escape' => false]);
        } ?>
    </span>
    <?= $this->Html->image('wait.gif', ['style' => 'display:none', 'id'=>$img_dom_id]) ?>
</div>
