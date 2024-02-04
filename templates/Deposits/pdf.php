<?php
/**
 * Created by FVdW.
 *
 * depot vente @ raspberry
 * User: francois
 * Date: 12/11/2021
 *
 * @param string $pdf_name
 *
 * @copyright: 2021
 * @version $Revision: $
 */
$this->setLayout('minimal');
echo $this->element('pdf_spool', ['pdf_name' => $pdf_name]);
