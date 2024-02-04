<?php
/**
 * Created by PhpStorm.
 * User: francois
 * Date: 26/01/2019
 * Time: 14:41
 *
 * Affiche <form> filtre les records pour action index (par Ex)
 * Attend 1 paramètre : $controls = array( 'field_name' => options[] ]
 *
 * Ex Appel
 * <?= $this->element('search_frm',['controls' => [
 * 		'q' => ['label' => 'Mot clé'],
 * 		's' => ['label' => 'Etat', 'options' => \Cake\Core\Configure::read('Conference.status'), 'empty' => true, 'multiple'=>true],
 * 	]]);
 */
if (!isset($cols)) $cols = 2;
else $cols = $cols == 2? 2 : 1;


if (!empty($controls)):
	$form_options = ['valueSources' => 'query'];
	// $mid = ceil(count($controls)/2);

	if (empty($_isSearch)) {
		$fa_icon = 'fa-plus-square';
		$display = 'hidden';
	} else {
		$fa_icon = 'fa-minus-square';
		$display = '';
	}
    $fa_icon = 'fa-plus-square';

	$action = $this->request->getParam('action');
	if (empty($action)) $action = 'index';

?>
<fieldset class="search-fs">
	<legend>
		&nbsp;<a href="javascript:void(0)" onclick="document.getElementById('i_search_frm').classList.toggle('hidden');"><i class="fa <?= $fa_icon ?>"></i> Filtrer par&hellip;</a>
	</legend>
	<div id="i_search_frm" class="<?= $display ?>" >
	<?= $this->Form->create(null, $form_options) ?>
		<div class="row">
		<div class="<?= $cols==2? 'col-md-9' : 'col-md-8' ?> form-group row">
			<?php foreach($controls as $name => $options) :
				if (empty($options['class'])) {
					$options['class'] = 'form-control col-md-8';
				} else {
					$classes = explode(' ', $options['class']);
					$classes[] = 'form-control';
					$classes[] = 'col-md-8';
					array_unique($classes);
					$options['class'] = join(' ', $classes);
				}
				if (empty($options['style'])) $options['style'] = '';
				elseif(substr($options['style'],-1) != ';') $options['style'] .= ';';
				$options['style'] .= 'display:inline';
				$options['label'] = ['class' => 'col-md-4', 'text' => $options['label']];
				$options['div'] = false;
				if (!empty($options['options'])) {
					if (!array_key_exists('', $options['options'])) {
						// place en 1ere position l'option "vide"
						$options['options'][''] = '';
						$options['default'] = '';
					}
				}
				// < input >
				?>
				<div class="<?= $cols==2? 'col-md-6' : 'col-md-12' ?>">
					<?= $this->Form->control($name, $options) ?>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="<?= $cols==2? 'col-md-3' : 'col-md-4' ?> text-center actions">
			<div style="padding:0 5px 0 0">
				<?= $this->Form->button('Chercher', ['type' => 'submit', 'class'=>'btn btn-block btn-primary', 'id'=>'isbmt_search']) ?>
				<?= $this->Html->link('Remise à zéro', ['action' => $action], ['class' => 'btn btn-block btn-outline-dark']) ?>
			</div>
		</div>
		</div>
	<?= $this->Form->end() ?>
	</div>
</fieldset>
<?php
else:
	echo '<!-- element search_frm : manque arg controls=[] -->';
endif;
