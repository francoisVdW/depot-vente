<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.10.0
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 *
 *
 * @var \App\View\AppView $this
 * @var array $current_user
 */
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Datasource\ConnectionManager;
use Cake\Error\Debugger;
use Cake\Http\Exception\NotFoundException;

?>
<h1>Bienvenue sur le module de Dépôt/Vente</h1>
<p class="lead">
    <?= \Cake\Core\Configure::read('ui.organisation.name') ?>
</p>
<p class="lead">
    <?= \Cake\Core\Configure::read('ui.organisation.event_name') ?>
</p>
<?php if (!$current_user) : ?>
<div class="col-md-4 offset-md-4 p-4">
    <?=$this->Html->link('Connexion', ['controller' => 'Users', 'action' => 'login'], ['class' => 'btn btn-primary btn-block']) ?>
</div>

<?php endif;
