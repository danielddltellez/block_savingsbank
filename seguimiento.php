<?php
 
require_once('../../config.php');
//require_once('seguimiento_form.php');
require_once('lib.php');
require_once('./view/view.php');


global $DB, $OUTPUT, $PAGE, $USER, $CFG;
 
// Check for all required variables.
$courseid = required_param('courseid', PARAM_INT);
$blockid = required_param('blockid', PARAM_INT);
$idcomentario = required_param('idcomentario', PARAM_INT);
require_once('./modal/modal.php');

// Next look for optional variables.
$id = optional_param('id', 0, PARAM_INT);
$viewpage = optional_param('viewpage', false, PARAM_BOOL);
$idpadre = optional_param('idpadre', 0, PARAM_INT); 
 
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
   ​print_error('invalidcourse', 'block_savingsbank', $courseid);
}
 
require_login($course);

$PAGE->set_url('/blocks/savingsbank/seguimiento.php', array('id' => $courseid));
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('edithtml', 'block_savingsbank'));

$settingsnode = $PAGE->settingsnav->add('Portal RH');

//Es administrador de reportes
$sql="SELECT cor.id FROM {block_savingsbank_responsa} as cor WHERE cor.idusuario=? and cor.estatus=1";
$resp=$DB->get_records_sql($sql,array($USER->id));
$admin=0;

if (count($resp)>0) {
    $admin=1;
    $editnode = $settingsnode->add('Seguimiento a solicitudes', $editurl);
    $editnode->make_active();
    //Enlace a Reportes
    $urlreports = new moodle_url('/blocks/savingsbank/reports.php', array('courseid' => $courseid, 'blockid'=>$blockid, id=>$id, 'viewpage'=>$viewpage));
    $editnode = $settingsnode->add('Reportes', $urlreports);
}else{
    $editurl = new moodle_url('/blocks/savingsbank/view.php', array('courseid' => $courseid, 'blockid' => $blockid, 'id' => 0,'viewpage' => 1));

    //$editurl = new moodle_url('/blocks/savingsbank/seguimiento.php', array('id' => $id, 'courseid' => $courseid, 'blockid' => $blockid,'idcomentario' => $idcomentario, 'viewpage' => 1));
    $editnode = $settingsnode->add(get_string('mycomments', 'block_savingsbank'), $editurl);
    $editnode->make_active();
    
    $urlform = new moodle_url('/blocks/savingsbank/view.php', array('courseid' => $courseid, 'blockid' => $blockid));
    //$editnode = $settingsnode->add('Nueva Queja', $urlform);
    
}
//link lineamientos 
/*
    $urlpoliticas = new moodle_url('/blocks/savingsbank/docs/LineamientosBQ.pdf');
    $editnode = $settingsnode->add('Lineamientos', $urlpoliticas);*/
/*
$seguimiento = new seguimiento_form();
$toform['blockid'] = $blockid;
$toform['courseid'] = $courseid;
$toform['id'] = $id;
$toform['idcomentario'] = $idcomentario;
$toform['idpadre'] = $idpadre;
$seguimiento->set_data($toform);
$asunto='Notificacion';
*/
echo $OUTPUT->header();

echo $head;

$courseurl = new moodle_url('/blocks/savingsbank/seguimiento.php', array('blockid' => $blockid, 'courseid' => $courseid, 'id' => '0', 'viewpage' => '1', 'idcomentario'=> $idcomentario, 'idpadre'=>$idpadre));
$urlback = new moodle_url('/blocks/savingsbank/view.php', array('blockid' => $blockid, 'courseid' => $courseid, 'id' => '0', 'viewpage' => '1'));
$urlclose = new moodle_url('/blocks/savingsbank/close.php', array('blockid' => $blockid, 'courseid' => $courseid, 'id' => '0', 'viewpage' => '1', 'idcomentario' => $idcomentario));


$sqlqn="SELECT co.id, co.idusuario, u.firstname, u.lastname,MAX(IF(f.shortname='pagadoraprincipal', d.data, NULL)) as pagadoraprincipal, MAX(IF(f.shortname='pagadorasecundaria', d.data, NULL)) as pagadorasecundaria, co.idcategoria, ca.nombre as categoria, ca.idpadre, (select nombre FROM {block_savingsbank_categoria}
WHERE id=ca.idpadre) as categoriapadre, co.mensaje, co.idestatus, ce.nombre as estatus, from_unixtime(co.fechacreacion,'%Y-%m-%d %H:%i') fechacreacion, from_unixtime(co.fechavisible,'%Y-%m-%d %H:%i') fechavisible, co.visible, co.idusermodified
FROM {block_savingsbank} as co 
LEFT JOIN {user} as u ON co.idusuario=u.id 
LEFT JOIN {block_savingsbank_estatus} as ce ON co.idestatus=ce.id 
LEFT JOIN {block_savingsbank_categoria} as ca ON co.idcategoria=ca.id
LEFT JOIN {user_info_data} d on d.userid=u.id
LEFT JOIN {user_info_field} f on f.id=d.fieldid
WHERE co.id=? GROUP BY co.id, u.id";
$question = $DB->get_record_sql($sqlqn, array($idcomentario));
    
block_savingsbank_print_question($question);
echo'<div>';
if($admin==1){
    if($question->idestatus==1){
    echo'<input type="button"  class="w3-btn w3-border" onclick="document.getElementById(\'atendido\').style.display=\'block\'" value="Registrar respuesta" style="background-color: #a2a3a1;">';
    }// echo'<a href="'.$urlclose.'"><button type="button" class="w3-btn w3-border" onclick="document.getElementById(\'newnivel\').style.display=\'block\'">Atendido</button></a>';
    echo'<a href="'.$urlback.'"><button type="button" class="w3-btn w3-border">Regresar</button></a>';
    echo $modalatendido;
}else{
    if($question->idestatus==1){
    echo'<input type="button"  class="w3-btn w3-border" onclick="document.getElementById(\'cancelacion\').style.display=\'block\'" value="¿Deseas cancelar la solicitud?" style="background-color: #a2a3a1;">';
    }
    echo'<a href="'.$urlback.'"><button type="button" class="w3-btn w3-border">Regresar</button></a>';
    echo $modalcancelacion;

}
echo'</div>';



echo $OUTPUT->footer();
?>
