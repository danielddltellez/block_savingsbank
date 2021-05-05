<?php
require_once '../../config.php';
require_once('lib.php');

if ($CFG->forcelogin) {
    require_login();
}
global $USER, $DB, $COURSE;
$fecha = new DateTime();
//$idusarioupdate=$USER->id;

if(!empty($_POST['userid']) && !empty($_POST['idcomentario']) && !empty($_POST['comentarios']) && !empty($_POST['idestatus'])){

    $updatecaja = new stdClass();
    $updatecaja-> id = $_POST['idcomentario'];
    $updatecaja-> mensaje  = $_POST['comentarios'];
    $updatecaja-> fechamodificacion  = $fecha->getTimestamp();
    $updatecaja-> idestatus  = $_POST['idestatus'];
    $updatecaja-> fechavisible  = $fecha->getTimestamp();
    $updatecaja-> idusermodified = $_POST['userid'];
    try{
      $resultupdateevaluador  = $DB->update_record('block_savingsbank', $updatecaja, $bulk=false);
      block_savingsbank_send_notification_aprobacion($_POST['idcomentario'], $_POST['idestatus']);
      echo 'Se atendio la solicitud';

    } catch(\Throwable $e) {
        // PHP 7 
        echo $e->error;
    } 
}
//header("Location:".$_SERVER['HTTP_REFERER']);

?>