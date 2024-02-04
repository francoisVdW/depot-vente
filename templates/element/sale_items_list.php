<?php
/**
 * Created by FVdW.
 *
 * depot vente @ raspberry
 * User: francois
 * Date: 20/01/2021
 *
 * @copyright: 2021
 * @version $Revision: $
 *
 *
 * @var array $items
 * @var bool $chk_locked default = false
 */
if (empty($chk_locked)) $chk_locked = false;


$item_count = $ttl = 0;
foreach($items as $item) {
    if ($chk_locked && $item->progress != 'LOCKED') continue;
    $item_count++;
    $ttl += $item->sale_price;
}
?>
<table class="table table-striped table-sm">
    <thead>
    <tr>
        <th>N°</th>
        <th>Designation</th>
        <th style="width: 20px">&nbsp;</th>
        <th>N° fabricant</th>
        <th class="text-right">Prix</th>
        <th>&nbsp;</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach($items as $item):?>
        <tr style="<?= $chk_locked && $item->progress != 'LOCKED'? 'background-color:#FF4040': '' ?>">
            <td><?= $item->deposit_id.'-'.$item->id ?></td>
            <td><?= h($item->name) ?></td>
            <td><?= $item->icon_color ?></td>
            <td><?= h($item->mfr_part_no) ?></td>
            <td class="text-right"><?= number_format($item->sale_price, 2, '.','') ?> &euro;</td>
            <td><?= $item->requested_price!=$item->sale_price? '<i class="fa fa-smile-o lead" aria-hidden="true"><i>': '&nbsp;' ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot>
    <tr>
        <td colspan="4" class="text-right lead"><?= $item_count ?> Article<?= $item_count>1? 's':'' ?></td>
        <td class="text-right lead"><?= number_format($ttl, 2, '.', '') ?> &euro;</td>
        <td>&nbsp;</td>
    </tr>
    </tfoot>
</table>

