<?php
/**
 * Created by FVdW.
 *
 * depot vente @ raspberry
 * User: francois
 * Date: 29/12/2020
 *
 * @var string $status OK|ERROR|NONE
 * @var int $item_id
 * @var string $msg
 *
 * @copyright: 2020
 * @version $Revision: $
 */
$this->layout = 'ajax';
if (empty($msg)) $msg = '';

echo json_encode([
    'status' => $status,
    'msg' => $msg,
    'item_id' => $item_id,
]);
