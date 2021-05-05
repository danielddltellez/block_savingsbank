<?php
 
require_once('../../config.php');
require_once('seguimiento_form.php');

global $DB, $OUTPUT, $PAGE, $USER, $CFG;
 
// Check for all required variables.
$courseid = required_param('courseid', PARAM_INT);
$blockid = required_param('blockid', PARAM_INT);
$idcomentario = required_param('idcomentario', PARAM_INT);
 
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

$settingsnode = $PAGE->settingsnav->add('Caja de ahorro');

//Es administrador de reportes
$sql="SELECT cor.id FROM {block_savingsbank_responsa} as cor WHERE cor.idusuario=? and cor.estatus=1";
$resp=$DB->get_records_sql($sql,array($USER->id));
$admin=0;

if (count($resp)>0) {
    $admin=1;
    $editnode = $settingsnode->add('Seguimiento a Quejas', $editurl);
    $editnode->make_active();
    //Enlace a Reportes
    $urlreports = new moodle_url('/blocks/savingsbank/reports.php', array('courseid' => $courseid, 'blockid'=>$blockid, id=>$id, 'viewpage'=>$viewpage));
    $editnode = $settingsnode->add('Reportes', $urlreports);
}else{

    $editurl = new moodle_url('/blocks/savingsbank/seguimiento.php', array('id' => $id, 'courseid' => $courseid, 'blockid' => $blockid, 'viewpage' => 1));
    $editnode = $settingsnode->add(get_string('mycomments', 'block_savingsbank'), $editurl);
    $editnode->make_active();

    $urlform = new moodle_url('/blocks/savingsbank/view.php', array('courseid' => $courseid, 'blockid' => $blockid));
    $editnode = $settingsnode->add('Nueva Queja', $urlform);
}
//link lineamientos 
/*
    $urlpoliticas = new moodle_url('/blocks/savingsbank/docs/LineamientosBQ.pdf');
    $editnode = $settingsnode->add('Lineamientos', $urlpoliticas);*/

$seguimiento = new seguimiento_form();
$toform['blockid'] = $blockid;
$toform['courseid'] = $courseid;
$toform['id'] = $id;
$toform['idcomentario'] = $idcomentario;
$toform['idpadre'] = $idpadre;
$seguimiento->set_data($toform);
$asunto='Notificacion';

echo $OUTPUT->header();

$courseurl = new moodle_url('/blocks/savingsbank/seguimiento.php', array('blockid' => $blockid, 'courseid' => $courseid, 'id' => '0', 'viewpage' => '1', 'idcomentario'=> $idcomentario, 'idpadre'=>$idpadre));
$urlback = new moodle_url('/blocks/savingsbank/view.php', array('blockid' => $blockid, 'courseid' => $courseid, 'id' => '0', 'viewpage' => '1'));
$urlclose = new moodle_url('/blocks/savingsbank/close.php', array('blockid' => $blockid, 'courseid' => $courseid, 'id' => '0', 'viewpage' => '1', 'idcomentario' => $idcomentario));

if($seguimiento->is_cancelled()) {//Lucius - is_cancelled() es una función definida en formslib.php
    //Lucius - Define a donde redireccionar cuando se cancela la operación.

    $sql="SELECT resp.id, resp.idcomentario, resp.idpadre, resp.idusuario as userid, u.firstname, u.lastname, resp.mensaje,  from_unixtime(resp.fechacreacion,'%d-%m-%Y %H:%i') fechacreacion, from_unixtime(resp.fechamodificacion,'%d-%m-%Y %H:%i') fechamodificacion, resp.visible, from_unixtime(resp.fechavisible,'%d-%m-%Y %H:%i') fechavisible FROM {block_savingsbank_resp} as resp
        LEFT JOIN {user} as u ON resp.idusuario=u.id
        WHERE resp.idcomentario=?"; 

    $answers=$DB->get_records_sql($sql,array($idcomentario));

    if(count($answers)==0){
        $courseurl = new moodle_url('/blocks/savingsbank/view.php', array('blockid' => $blockid, 'courseid' => $courseid, 'id' => '0', 'viewpage' => '1'));
        redirect($courseurl);
    }else{
        redirect($courseurl);
    }
    
}else if ($fromform=$seguimiento->get_data()) { //Intenta guardar

    $fecha = new DateTime();
    $contextblock = CONTEXT_BLOCK::instance($blockid);

    if ($fromform->id!=0) {//Lucius - Aquí actualiza registro en respuestas
        $fromform->fechavisible=$fecha->getTimestamp();
        $fromform->fechamodificacion=$fecha->getTimestamp();
        if (!$DB->update_record('block_savingsbank_resp', $fromform)) {
            print_error('updateerror', 'block_savingsbank_resp');
        }else { //Lucius - Aquí debe de actualizar registro en files
            /*if ($draftitemid = file_get_submitted_draft_itemid('filename')) {
                file_save_draft_area_files($draftitemid, $contextblock->id, 'block_savingsbank', 'block_savingsbank_resp',
                   $id, array('subdirs' => false, 'maxfiles' => 1, 'accepted_types' => array('.png','.jpg','.pdf','.doc','.docx','.ppt','.pptx','.xls','.xlxs')));
            }*/
        }

        if($fromform->visible==1 && $admin>0){ //envía correo
            $sql="SELECT co.idusuario, u.email FROM {block_savingsbank} as co, {user} as u WHERE co.idusuario=u.id and co.id=?";
            $question = $DB->get_record_sql($sql, array($idcomentario));
            $newlink = str_replace("&amp;", "&", $courseurl);
            //$pos=strpos($newlink, '?');
            //$newlink=substr($newlink,0,$pos);
            $newlink = 'https://www.portal3i.mx/openlms/tripleI.php?key='.base64_encode("email=$question->email&courseid=1");

            //echo $newlink;

            $mensaje= "Hola, \n\n Tu solicitud tuvo una actualización en los comentarios, ingresa a través del link para conocerlos.\n\nLink:";

            //block_savingsbank_send_mail($asunto, $fromform->mensaje, $question->idusuario, $question->email);

            block_savingsbank_send_mail($asunto, $mensaje, $question->idusuario, $question->email, $newlink);
        }
    }else{ //Crea registro con respuesta
        $recordcomentario = new stdClass();
        $recordcomentario->idusuario = $USER->id;
        $recordcomentario->idcomentario = $fromform->idcomentario;
        $recordcomentario->idpadre = $fromform->idpadre;
        $recordcomentario->mensaje = $fromform->mensaje;
        $recordcomentario->fechacreacion = $fecha->getTimestamp();
        $recordcomentario->fechavisible=$fecha->getTimestamp();
        $recordcomentario->fechamodificacion=$fecha->getTimestamp();
        if($admin>0){
            $recordcomentario->visible = $fromform->visible;
        }else{
            $recordcomentario->visible = 1;
        }
    //Registra seguimiento
        if (!($last=$DB->insert_record('block_savingsbank_resp', $recordcomentario))) {
            print_error('inserterror', 'block_savingsbank_resp');
        }else{
            if ($draftitemid = file_get_submitted_draft_itemid('filename')) {
                print_r($draftitemid);
                file_save_draft_area_files($draftitemid, $contextblock->id, 'block_savingsbank', 'block_savingsbank_resp', $last, array('subdirs' => false, 'maxfiles' => 1, 'accepted_types' => array('.png','.jpg','.pdf','.doc','.docx','.ppt','.pptx','.xls','.xlxs')));
            }
        }

        if($recordcomentario->visible==1 && $admin>0){//Envía correo

            $sql="SELECT co.idusuario, u.email FROM {block_savingsbank} as co, {user} as u WHERE co.idusuario=u.id and co.id=?";
            $question = $DB->get_record_sql($sql, array($idcomentario));
            $newlink = str_replace("&amp;", "&", $courseurl);
            //$pos=strpos($newlink, '?');
            //$newlink=substr($newlink,0,$pos);
            $newlink = 'https://www.portal3i.mx/openlms/tripleI.php?key='.base64_encode("email=$question->email&courseid=1");

            //echo $newlink;

            $mensaje= "Hola, \n\n Tu solicitud tuvo una actualización en los comentarios, ingresa a través del link para conocerlos.\n\n Link: ";
            //block_savingsbank_send_mail($asunto, $fromform->mensaje, $question->idusuario, $question->email);
            block_savingsbank_send_mail($asunto, $mensaje, $question->idusuario, $question->email, $newlink);
        }
    }
    redirect($courseurl);

}else {

    $contextblock = CONTEXT_BLOCK::instance($blockid);

    //$sqlqn="SELECT co.id, co.idusuario, u.firstname, u.lastname, co.idcategoria, ca.nombre as categoria, ca.idpadre, (select nombre FROM {block_savingsbank_categoria} WHERE id=ca.idpadre) as categoriapadre, co.asunto, co.mensaje, co.idestatus, ce.nombre as estatus, from_unixtime(co.fechacreacion,'%Y-%m-%d %H:%i') fechacreacion, from_unixtime(co.fechavisible,'%Y-%m-%d %H:%i') fechavisible, co.visible, co.iddoc, f.contextid FROM {block_savingsbank} as co, {user} as u, {block_savingsbank_estatus} as ce, {block_savingsbank_categoria} as ca, {files} as f WHERE co.idusuario=u.id and co.id=? and co.idcategoria=ca.id and co.idestatus=ce.id and co.id=f.itemid AND f.filename !='.' AND f.contextid!=1";

    $sqlqn="SELECT co.id, co.idusuario, u.firstname, u.lastname, co.idcategoria, ca.nombre as categoria, ca.idpadre, (select nombre FROM {block_savingsbank_categoria} WHERE id=ca.idpadre) as categoriapadre, co.mensaje, co.idestatus, ce.nombre as estatus, from_unixtime(co.fechacreacion,'%Y-%m-%d %H:%i') fechacreacion, from_unixtime(co.fechavisible,'%Y-%m-%d %H:%i') fechavisible, co.visible, f.contextid FROM {block_savingsbank} as co LEFT JOIN {user} as u ON co.idusuario=u.id LEFT JOIN {block_savingsbank_estatus} as ce ON co.idestatus=ce.id LEFT JOIN {block_savingsbank_categoria} as ca ON co.idcategoria=ca.id LEFT JOIN {files} as f ON co.id=f.itemid and f.component='block_savingsbank' and f.filearea='block_savingsbank' and f.filename <>'.' WHERE co.id=?";

	if ($viewpage and $id=='0') { //Muestra el seguimientode la queja

		$urlform = new moodle_url('/blocks/savingsbank/seguimiento.php', array('blockid' => $blockid, 'courseid' => $courseid, 'id' => '0', 'viewpage' => '0', 'idcomentario'=> $idcomentario, 'idpadre'=>$idpadre));

        $question = $DB->get_record_sql($sqlqn, array($idcomentario));
    
        block_savingsbank_print_question($question);

        //Muestra cada uno de los comentarios
        //$sql="SELECT resp.id, resp.idcomentario, co.idestatus, resp.idpadre, resp.idusuario as userid, (SELECT IF(userid IN (SELECT idusuario FROM {block_savingsbank_responsa}), 1, 0)) isadmin , u.firstname, u.lastname, resp.mensaje,  from_unixtime(resp.fechacreacion,'%Y-%m-%d %H:%i') fechacreacion, from_unixtime(resp.fechamodificacion,'%Y-%m-%d %H:%i') fechamodificacion, resp.visible, from_unixtime(resp.fechavisible,'%Y-%m-%d %H:%i') fechavisible, f.contextid FROM {block_savingsbank_resp} as resp LEFT JOIN {user} as u ON resp.idusuario=u.id LEFT JOIN {block_savingsbank} as co ON resp.idcomentario=co.id LEFT JOIN {files} as f ON resp.id=f.itemid  WHERE resp.idcomentario=? and f.filename !='.' AND f.contextid!=1 ORDER BY fechavisible ASC"; 

        $sql="SELECT resp.id, resp.idcomentario, co.idestatus, resp.idpadre, resp.idusuario as userid, (SELECT IF(resp.idusuario IN (SELECT idusuario FROM {block_savingsbank_responsa}), 1, 0)) isadmin , u.firstname, u.lastname, resp.mensaje,  from_unixtime(resp.fechacreacion,'%Y-%m-%d %H:%i') fechacreacion, from_unixtime(resp.fechamodificacion,'%Y-%m-%d %H:%i') fechamodificacion, resp.visible, from_unixtime(resp.fechavisible,'%Y-%m-%d %H:%i') fechavisible, f.contextid FROM {block_savingsbank_resp} as resp LEFT JOIN {user} as u ON resp.idusuario=u.id LEFT JOIN {block_savingsbank} as co ON resp.idcomentario=co.id LEFT JOIN {files} as f ON resp.id=f.itemid and f.component='block_savingsbank' and f.filearea='block_savingsbank_resp' and f.filename <>'.' WHERE resp.idcomentario=? ORDER BY fechavisible ASC";

		$answers=$DB->get_records_sql($sql,array($idcomentario));
        block_savingsbank_print_anwers($answers, $urlform, $urlclose, $urlback, $question->idestatus);

        if(count($answers)==0){
        	$seguimiento->display();
        }else{
        	block_savingsbank_print_buttom_new_answer($urlform, $urlclose, $urlback, $question->idestatus);
        }

	}else if($id) {//Edita comentarios a una queja
        $contextblock = CONTEXT_BLOCK::instance($blockid);
        $question = $DB->get_record_sql($sqlqn, array($idcomentario));
    
        block_savingsbank_print_question($question);

        $comentarioedit = $DB->get_record('block_savingsbank_resp', array('id' => $id));
        $draftitemid = file_get_submitted_draft_itemid('filename');
        file_prepare_draft_area($draftitemid, $contextblock->id, 'block_savingsbank', 'block_savingsbank_resp', $id,
                        array('subdirs' => false, 'maxfiles' => 1, 'accepted_types' => array('.png','.jpg','.pdf','.doc','.docx','.ppt','.pptx','.xls','.xlxs')));
        $comentarioedit->filename = $draftitemid;

        $seguimiento->set_data($comentarioedit);
        $seguimiento->display();

	}else {//Muestra formulario para nuevo comentario
        $contextblock = CONTEXT_BLOCK::instance($blockid);
        $question = $DB->get_record_sql($sqlqn, array($idcomentario));
    
        block_savingsbank_print_question($question);
        $seguimiento->display();
    }
}

echo $OUTPUT->footer();
?>
