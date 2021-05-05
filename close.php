<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once('../../config.php');
require_once($CFG->dirroot.'/blocks/savingsbank/lib.php');

global $OUTPUT, $DB, $PAGE;

$courseid = required_param('courseid', PARAM_INT);
$blockid = required_param('blockid', PARAM_INT);
$idcomentario=required_param('idcomentario', PARAM_INT);

$delete=optional_param('delete', false, PARAM_BOOL);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
   ​print_error('invalidcourse', 'block_savingsbank', $courseid);
}
 
require_login($course);

$PAGE->set_url('/blocks/savingsbank/seguimiento.php', array('id' => $courseid));
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('edithtml', 'block_savingsbank'));

$settingsnode = $PAGE->settingsnav->add('Caja de ahorro');

//Es administrador de reportes
$sql="SELECT cor.id FROM {block_savingsbank_responsa} as cor WHERE cor.idusuario=? and cor.estatus=1";
$resp=$DB->get_records_sql($sql,array($USER->id));

if (count($resp)>0) {
    $editnode = $settingsnode->add('Seguimiento a Quejas', $editurl);
    $editnode->make_active();
    //if (user_has_role_assignment($USER->id, 1) || is_siteadmin()) {
        //Enlace a Reportes
        $urlreports = new moodle_url('/blocks/savingsbank/reports.php', array('courseid' => $courseid, 'blockid'=>$blockid, id=>$id, 'viewpage'=>$viewpage));
        $editnode = $settingsnode->add('Reportes', $urlreports);
    //}
}else{

    $editurl = new moodle_url('/blocks/savingsbank/seguimiento.php', array('id' => $id, 'courseid' => $courseid, 'blockid' => $blockid, 'viewpage' => 1));
    $editnode = $settingsnode->add(get_string('mycomments', 'block_savingsbank'), $editurl);
    $editnode->make_active();

    $urlform = new moodle_url('/blocks/savingsbank/view.php', array('courseid' => $courseid, 'blockid' => $blockid));
    $editnode = $settingsnode->add('Nuevo comentario', $urlform);
}
//link lineamientos 
    /*
    $urlpoliticas = new moodle_url('/blocks/savingsbank/docs/LineamientosBQ.pdf');
    $editnode = $settingsnode->add('Lineamientos', $urlpoliticas);
*/
echo $OUTPUT->header();

$urlcontinue  = new moodle_url('/blocks/savingsbank/view.php', array('blockid' => $blockid, 'courseid' => $courseid, 'id' => '0', 'viewpage' => '1'));
$urldelete  = new moodle_url('/blocks/savingsbank/close.php', array('blockid' => $blockid, 'courseid' => $courseid, 'id' => '0', 'viewpage' => '1', 'idcomentario' => $idcomentario, 'delete' =>1));
$urlcancel  = new moodle_url('/blocks/savingsbank/seguimiento.php', array('blockid' => $blockid, 'courseid' => $courseid, 'idcomentario' => $idcomentario, 'idpadre' => 0, 'viewpage' => 1));


$sql="SELECT co.id, co.idusuario, u.firstname, u.lastname, co.idcategoria, co.mensaje, co.idestatus, ce.nombre as estatus, from_unixtime(co.fechacreacion,'%d-%m-%Y') fechacreacion, co.visible, from_unixtime(co.fechavisible,'%Y-%m-%d %H:%i') fechavisible FROM {block_savingsbank} as co, {user} as u, {block_savingsbank_estatus} as ce WHERE co.idusuario=u.id and co.id=? and co.idestatus=ce.id";
$question = $DB->get_record_sql($sql, array($idcomentario));
    
block_savingsbank_print_question($question);

if($delete==0){
    echo $OUTPUT->confirm('Al finalizar el seguimiento no podrá realizar más comentarios', $urldelete, $urlcancel);
}else{
    $fecha = new DateTime();
    $fechamodificacion=$fecha->getTimestamp();

	if (!$DB->execute("UPDATE {block_savingsbank} SET 	idestatus=2, fechamodificacion=$fechamodificacion where id=$idcomentario")) {
        print_error('updateerror', 'block_savingsbank');
    }else{
    	redirect($urlcontinue);
    }
	//echo "Deleted";
}


echo $OUTPUT->footer();
?>
