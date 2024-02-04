<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 *
 * @version $Rev: $
 */
?>
<div class="row">
	<div class="col-md-6 offset-md-3 p-4">
		<div class="card">
			<article class="card-body">
				<h4 class="card-title mb-4 mt-1">Connexion</h4>
				<?= $this->Form->create(null) ?>
					<div class="form-group">
                        <?= $this->Form->control('username', ['label'=>'Nom de connexion', 'class'=>'form-control',  'autocomplete'=>'off']) ?>
					</div> <!-- form-group// -->
					<div class="form-group">
                        <?= $this->Form->control('password', ['label'=>'Mot de passe', 'class'=>'form-control', 'type'=>'password', 'autocomplete'=>'off']) ?>
					</div> <!-- form-group// -->
					<div class="form-group mt-4">
                        <?= $this->Form->submit('Envoyer', ['class'=>'btn btn-primary btn-block']) ?>
					</div> <!-- form-group// -->
				<?= $this->Form->end(); ?>
			</article>
		</div> <!-- card.// -->
	</div>
</div>
<?php
//EoF
