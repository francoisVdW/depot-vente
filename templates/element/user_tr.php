<?php
/**
 * Created by FVdW.
 *
 * depot vente @ raspberry
 * User: francois
 * Date: 31/01/2021
 *
 * @copyright: 2021
 * @version $Revision: $
 *
 * @var \Cake\ORM\Entity $user
 */
$modal_edit = $this->Modal->ajaxModal(
    '<i class="fa fa-edit" aria-hidden="true"></i>',
    ['controller' => 'Users', 'action' => 'ajaxEdit', $user->id],
    ['class' => 'btn btn-outline-primary btn-sm', 'id' => 'iModalEdit_'.$user->id, 'title' => 'Modifier']
);
$modal_delete = $this->Modal->ajaxModal(
    '<i class="fa fa-trash text-danger"></i>',
    ['controller' => 'Users', 'action' => 'ajaxDelete', $user->id],
    ['class' => 'btn btn-outline-primary btn-sm', 'id' => 'iModalDelete_'.$user->id, 'title' => 'Supprimer']
);
?>
<td><?= h($user->last_name) ?></td>
<td><?= h($user->first_name) ?></td>
<td><?= h($user->username) ?></td>
<td><?= $user->roles ?></td>
<td class="actions">
    <?= $modal_edit ?>
    &nbsp;
    <?= $modal_delete ?>
</td>
