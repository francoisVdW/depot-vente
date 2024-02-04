<?php
/**
 * Created by FVdW.
 *
 * depot vente @ raspberry
 * User: francois
 * Date: 29/12/2020
 *
 * @var string $status OK|ERROR|NONE
 * @var \Cake\ORM\Entity $item
 * @var array $item_errors
 * @var string $msg
 *
 * @copyright: 2020
 * @version $Revision: $
 */
$this->setLayout('ajax');
if ($item) unset($item->created);
$errors = [];
if (count($item_errors)) foreach($item_errors as $field_name => $a_err) {
    $errors[$field_name] = join(', ', $a_err);
}
if (empty($msg)) $msg = '';

echo json_encode([
    'item' => $item,
    'status' => $status,
    'errors' => $errors,
    'msg' => $msg,
]);
