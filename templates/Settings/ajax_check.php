<?php
/**
 * Created by FVdW.
 *
 * depot vente @ raspberry
 * User: francois
 * Date: 05/02/2021
 *
 * @copyright: 2021
 * @version $Revision: $
 *
 * @var array $errors
 */

$this->setLayout('ajax');
?>
    <h4>Contrôle du paramétrage</h4>
<?php
if (!count($errors)) {
    echo $this->element('flash/success', ['message' => 'Aucune erreur détectée']);
} else {
     if (count($errors) == 1) {
         $msg = 'Une erreur détectée';
     }  else {
         $msg = count($errors).' erreurs détectées';
     }
    echo $this->element('flash/error', ['message' => $msg]);
    ?>
    <table class="table">
        <?php
        foreach ($errors as $lbl => $comment) {
            echo '<tr><td>'.$lbl.'</td><td>'.$comment.'</td>';
        } ?>
    </table>
<?php
}
