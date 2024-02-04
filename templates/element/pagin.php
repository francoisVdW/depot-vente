<?php
/**
 * Created by FVdW  (c) 2019.
 *
 * param : short : default = false
 *
 * lab4vet @ raspberry
 * User: francois
 * Date: 28/10/2019
 * Time: 16:36
 */

if (isset($short)) $short = $short? true:false;
else $short = false;

if ($short) {
	$format = 'Page {{page}} de {{pages}}  (Total : {{count}})';
} else {
	$format = 'Page {{page}} de {{pages}} - Affiche {{current}} enregistrement(s) sur un total de {{count}}';
}

?>
<div class="row" id="pagin">
	<div class="col-5 nav">
		<ul class="pagination">
			<?php
			echo $this->Paginator->first(' <i class="fa fa-arrow-circle-left fa-lg" aria-hidden="true"></i> ',
				[
					'tag' => 'li',
					'currentTag' => 'a',
					'currentClass' => 'disabled',
					'escape' => false,
				],
				null,
				['class' => 'prev disabled']);
			echo $this->Paginator->prev(' <i class="fa fa-arrow-left" aria-hidden="true"></i> ',
				[
					'tag' => 'li',
					'currentTag' => 'a',
					'currentClass' => 'disabled',
					'escape' => false,
				],
				null,
				['class' => 'prev disabled']);
			echo '&nbsp;';
			echo $this->Paginator->numbers([
					'tag' => 'li',
					'separator' => 'span',
					'currentTag' => 'a',
					'currentClass' => 'active']);
            echo '&nbsp;';
			echo $this->Paginator->next(' <i class="fa fa-arrow-right" aria-hidden="true"></i> ',
				[
					'tag' => 'li',
					'currentTag' => 'a',
					'currentClass' => 'disabled',
					'escape' => false,
				],
				null,
				['class' => 'next disabled']);
			echo $this->Paginator->last(' <i class="fa fa-arrow-circle-right fa-lg" aria-hidden="true"></i> ',
				[
					'tag' => 'li',
					'currentTag' => 'a',
					'currentClass' => 'disabled',
					'escape' => false,
				],
				null,
				['class' => 'next disabled']);
			?>
		</ul>
	</div>
	<div class="col-7  text-right info">
		<?= $this->Paginator->counter($format); ?>
	</div>
</div>
<?php
// EoF
