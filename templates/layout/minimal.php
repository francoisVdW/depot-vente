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
        <?= $this->Html->image('logo.png', ['alt' => 'Dépôt / Vente', 'class' => 'rounded']) ?>
    </nav>
    <main class="main flex-fill">
        <div class="container" style="min-height: 400px">
            <?= $this->Flash->render() ?>
            <?= $this->fetch('content') ?>
        </div>
    </main>
    <footer class="bg-primary text-white pt-3 pb-3">
        &nbsp;
    </footer>
</div>
    <?= $this->fetch('script') ?>
</body>
</html>
