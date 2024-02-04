<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         0.10.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 *
 * @var \App\View\AppView $this
 * @var array $current_user
 * @var array $authorizations
 *
 *
 * @version $Rev: $
 */

$home_url = $this->Url->build('/');

/**
 * Prepend `CSS` block
 */
$css_sources = [
    'bootstrap.min',
    'depot_vente.css',
//    'https://cdn.jsdelivr.net/fontawesome/4.7.0/css/font-awesome.min.css',
    'font-awesome.min.css',
];
$this->prepend('css', $this->Html->css($css_sources));


/**
 * Prepend `script` block : Bootstrap scripts
 */
if (\Cake\Core\Configure::read('debug')) {
    $js_sources = ['bootstrap-native'];
} else {
    $js_sources = ['bootstrap-native.min'];
}

$this->prepend('script', $this->Html->script($js_sources));
if (!empty($this->viewVars['page']) &&  $this->viewVars['page'] == 'home') {
    // page "home"
    $menu_href_prefix = '';
} else {
    // toutes les autres pages
    $menu_href_prefix = \Cake\Routing\Router::url(['controller' =>'pages', 'action' =>'display', 'home']);
}
$base = Cake\Routing\Router::url('/');


// ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
$this->Html->scriptStart(['block' => true]);  ?>

var elt = function(dom_id) { return document.getElementById(dom_id);}
var hasClass = function (el, className) { if(typeof el == "object") return el.classList.contains(className); return false; }
var addClass = function (el, className) { if(typeof el == "object") el.classList.add(className) }
var removeClass = function (el, className) { if(typeof el == "object") el.classList.remove(className) }
var toggleClass = function (el, className) { if(typeof el == "object") el.classList.toggle(className) }
var show = function(el) { removeClass(el, "hidden") }
var hide = function(el) { addClass(el, "hidden") }
var toggle = function(el) { toggleClass(el, "hidden") }

<?php $this->Html->scriptEnd();
// ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#ffffff">
    <title>Dépôt / Vente</title>
    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
</head>
<body>
<div class="d-flex flex-column sticky-footer-wrapper min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <a class="navbar-brand" href="<?= $menu_href_prefix ?>#top"><?= $this->Html->image('logo.png', ['alt' => 'Dépôt / Vente', 'class' => 'rounded']) ?></a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarColor01" aria-controls="navbarColor01" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarColor01">
            <ul class="navbar-nav mr-auto">
                <?php if ($authorizations['Deposits']) : ?>
                <li class="nav-item">
                    <?= $this->Html->link('Dépôts', ['controller' => 'deposits'], ['class' => 'nav-link']) ?>
                </li>
                <?php endif;
                if ($authorizations['CashRegisters']) : ?>
                <li class="nav-item">
                    <?= $this->Html->link('Ventes', ['controller' => 'CashRegisters', 'action' => 'index'], ['class' => 'nav-link']) ?>
                </li>
                <?php endif;
                if ($authorizations['MNGR']) : ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Gestion
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                        <li>
                            <?= $this->Html->link('Liste des articles', ['controller' => 'Items', 'action' => 'mngrIndex'], ['class' => 'nav-link', 'escape' => false]) ?>
                        </li>
                        <li>
                            <?= $this->Html->link('Liste des dépôts', ['controller' => 'Deposits', 'action' => 'mngrIndex'], ['class' => 'nav-link', 'escape' => false]) ?>
                        </li>
                        <li>
                            <?= $this->Html->link('Liste des ventes', ['controller' => 'Sales', 'action' => 'index'], ['class' => 'nav-link', 'escape' => false]) ?>
                        </li>
                        <li>
                            <?= $this->Html->link('Tableau de bord', ['controller' => 'dashboard' /* voir routes.php */], ['class' => 'nav-link', 'escape' => false]) ?>
                        <li>
                            <?= $this->Html->link('Clôture des ventes', ['controller' => 'CashRegisters', 'action' => 'mngrComplete'], ['class' => 'nav-link', 'escape' => false]) ?>
                        </li>
                    </ul>
                </li>
            <?php endif; ?>
            </ul>
            <span>
            </span>
            <ul class="navbar-nav">
                <?php if ($current_user) : ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-user" aria-hidden="true"></i> <?= trim($current_user['first_name'].' '.$current_user['last_name']) ?>
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                        <li>
                            <?= $this->Html->link('<i class="fa fa-sign-out" aria-hidden="true"></i> Quitter', ['controller' => 'Users', 'action' => 'logout'], ['class' => 'nav-link', 'escape' => false]) ?>
                        </li>
                    </ul>
                </li>
                <?php else: ?>
                <li class="nav-item">
                    <?= $this->Html->link('<i class="fa fa-sign-in" aria-hidden="true"></i> Connexion', ['controller' => 'Users', 'action' => 'login'], ['class' => 'nav-link', 'escape' => false]) ?>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
    <main class="main flex-fill">
        <a name="top"></a>
        <div class="container" style="min-height: 400px">
            <?= $this->Flash->render() ?>
            <?= $this->fetch('content') ?>
        </div>
    </main>
    <footer class="bg-primary text-white pt-3 pb-3">
        <div class=" row" style="font-size:90%">
            <div class="col-md-4 text-center">
                <div>
                    <?php if ($authorizations['ADMIN']) : ?>
                    <?= $this->Html->link('Admin', ['controller'=>'AdminFunctions', 'action' => 'home']) ?>
                    <?php endif; ?>
                    <p><span class="badge badge-light"><?= $_SERVER['REMOTE_ADDR'] ?></span></p>
                </div>
            </div>
            <div class="col-md-4 text-center">
                <a href="https://www.cakephp.org" target="_blank"><?= $this->Html->image('cakephp.jpg', ['alt'=>'CakePHP', 'class'=>'mb-2', 'style' => 'border-radius:.25rem;max-height:24px']) ?></a>
            </div>
            <div class="col-md-4 text-center">
                Design : François Van de Weerdt &nbsp; <span class="badge badge-light" style="font-size:105%">contact@fvdw.fr</span>
            </div>
        </div>
    </footer>
</div>
    <?= $this->fetch('script') ?>
</body>
</html>
