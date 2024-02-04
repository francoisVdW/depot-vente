<?php
/**
 * @version $Rev: $
 *
 * @var \App\View\AppView $this
 * @var array $params
 * @var string $message
 */

if (!isset($params['escape']) || $params['escape'] !== false) {
    $message = h($message);
}
?>
<div class="info alert alert-info" role="alert" >
	<i class="fa fa-info-circle fa-lg" aria-hidden="true"></i> &nbsp; <?= $message ?>
    <?php if ($this->getLayout() != 'ajax') : ?>
        <button type="button" class="close" onclick="this.parentNode.classList.add('hidden')" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    <?php endif; ?>
</div>
<?php
// EoF
