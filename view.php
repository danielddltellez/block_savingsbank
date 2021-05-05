<?php
 
require_once('../../config.php');
require_once('commentation_form.php');
require_once('./view/view.php');


global $DB, $OUTPUT, $PAGE, $USER;
 
// Check for all required variables.
$courseid = required_param('courseid', PARAM_INT);
$blockid = required_param('blockid', PARAM_INT);

// Next look for optional variables.
$id = optional_param('id', 0, PARAM_INT);
$viewpage = optional_param('viewpage', false, PARAM_BOOL);
 
 
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
   ​print_error('invalidcourse', 'block_savingsbank', $courseid);
}
 
require_login($course);

$PAGE->set_url('/blocks/savingsbank/view.php', array('id' => $courseid));
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('edithtml', 'block_savingsbank'));

//$settingsnode = $PAGE->settingsnav->add(get_string('simplehtmlsettings', 'block_savingsbank'));
$settingsnode = $PAGE->settingsnav->add('Caja de ahorro');
$editurl = new moodle_url('/blocks/savingsbank/view.php', array('id' => $id, 'courseid' => $courseid, 'blockid' => $blockid, 'viewpage' => 1));

//Es administrador de reportes
$sql="SELECT cor.id FROM {block_savingsbank_responsa} as cor WHERE cor.idusuario=? and cor.estatus=1";
$resp=$DB->get_records_sql($sql,array($USER->id));
if (count($resp)>0) {
    $editnode = $settingsnode->add('Seguimiento a solicitud', $editurl);
    $editnode->make_active();
    //if (user_has_role_assignment($USER->id, 1) || is_siteadmin()) {
        //Enlace a Reportes
        $urlreports = new moodle_url('/blocks/savingsbank/reports.php', array('courseid' => $courseid, 'blockid'=>$blockid, id=>$id, 'viewpage'=>$viewpage));
        $editnode = $settingsnode->add('Reportes', $urlreports);
    //}
}else{
    $editnode = $settingsnode->add(get_string('mycomments', 'block_savingsbank'), $editurl);
    $editnode->make_active();
    
    $urlform = new moodle_url('/blocks/savingsbank/view.php', array('courseid' => $courseid, 'blockid' => $blockid));
    //$editnode = $settingsnode->add('Nueva solicitud', $urlform);
    
}
//link lineamientos 
   /* $urlpoliticas = new moodle_url('/blocks/savingsbank/docs/LineamientosBQ.pdf');
    $editnode = $settingsnode->add('Lineamientos', $urlpoliticas);*/

$savingsbank = new savingsbank_form();    
$toform['blockid'] = $blockid;
$toform['courseid'] = $courseid;
$toform['id'] = $id;
$savingsbank->set_data($toform);

echo $OUTPUT->header();
echo $head;
$courseurl = new moodle_url('/blocks/savingsbank/view.php', array('blockid' => $blockid, 'courseid' => $courseid, 'id' => '0', 'viewpage' => '1'));

if($savingsbank->is_cancelled()) {//Lucius - is_cancelled() es una función definida en formslib.php
    //Lucius - Define a donde redireccionar cuando se cancela la operación.
    redirect($courseurl);
    
}else if ($fromform=$savingsbank->get_data()) { //Intenta guardar

	$fecha = new DateTime();
    $contextblock = CONTEXT_BLOCK::instance($blockid);
	if ($fromform->id!=0) {//Lucius - Aquí actualiza registro en respuesta
		$fromform->fechavisible=$fecha->getTimestamp();
        $fromform->fechamodificacion=$fecha->getTimestamp();
		if($fromform->visible==0){
        	$fromform->idestatus = 3;
        }
        $idcat=$fromform->idcategoria[1];
        $fromform->idcategoria=$idcat;

        if (!$DB->update_record('block_savingsbank', $fromform)) {
            print_error('updateerror', 'block_savingsbank');
        }else { //Lucius - Aquí debe de actualizar registro en files
           /* if ($draftitemid = file_get_submitted_draft_itemid('filename')) {
                file_save_draft_area_files($draftitemid, $contextblock->id, 'block_savingsbank', 'block_savingsbank',
                   $id, array('subdirs' => false, 'maxfiles' => 1, 'accepted_types' => array('.png','.jpg','.pdf','.doc','.docx','.ppt','.pptx','.xls','.xlxs')));
            }*/
            $sqladmin="SELECT u.id , u.email as correoelectronico
            from {block_savingsbank_responsa} sr
            join {user} u on u.id=sr.idusuario where sr.estatus=?";
            $respadmin = $DB->get_record_sql($sqladmin, array(1));
            foreach($respadmin as $values){
            $idadmin=$values->id;
            $correadmin=$values->correoelectronico;
            block_savingsbank_send_notification($last, $correadmin);
            }
        }

    }else{ //Crea un nuevo registro con comentario
        
        $recordcomentario = new stdClass();
        $recordcomentario->idusuario = $USER->id;
        $recordcomentario->idcategoria = $fromform->idcategoria[1];
        /*$recordcomentario->asunto = $fromform->asunto;
        $recordcomentario->mensaje = $fromform->mensaje;
        */
        $recordcomentario->fechacreacion = $fecha->getTimestamp();
        $recordcomentario->fechavisible=$fecha->getTimestamp();
        $recordcomentario->fechamodificacion=$fecha->getTimestamp();
        $recordcomentario->visible = $fromform->visible;
        $recordcomentario->idusermodified = $USER->id;
        if($fromform->visible==2){
        	$recordcomentario->idestatus = 3;
        }else{
        	$recordcomentario->idestatus = $fromform->idestatus;
        }  

        //print_r($recordcomentario);      
        //Registra Queja
        if (!($last=$DB->insert_record('block_savingsbank', $recordcomentario))) {
            print_error('inserterror', 'block_savingsbank');
        }else{
          /*  if ($draftitemid = file_get_submitted_draft_itemid('filename')) {
                file_save_draft_area_files($draftitemid, $contextblock->id, 'block_savingsbank', 'block_savingsbank', $last, array('subdirs' => false, 'maxfiles' => 1, 'accepted_types' => array('.png','.jpg','.pdf','.doc','.docx','.ppt','.pptx','.xls','.xlxs')));
            }*/
            $sqladmin="SELECT sr.id, u.email as correoelectronico
            from {block_savingsbank_responsa} sr
            join {user} u on u.id=sr.idusuario where sr.estatus=?";
            $respadmin = $DB->get_records_sql($sqladmin, array(1));
        
            foreach($respadmin as $valores){

            block_savingsbank_send_notification($last,$valores->correoelectronico);
            }
           // block_savingsbank_send_notification($last,$categoriapadre);
        }
        
    }

    redirect($courseurl);

}else {
	if ($viewpage and $id=='0') { //Muestra todas las solicitud

        if (count($resp)>0) { //El usuario es administrador
            $sql="SELECT co.id, u.id as userid, u.firstname, u.lastname,MAX(IF(f.shortname='pagadoraprincipal', d.data, NULL)) as pagadoraprincipal, MAX(IF(f.shortname='pagadorasecundaria', d.data, NULL)) as pagadorasecundaria, ca.id as idcat, ca.nombre as catname,es.id as idestatus, es.nombre as estname, from_unixtime(co.fechacreacion,'%Y-%m-%d %H:%i') fechacreacion, from_unixtime(co.fechamodificacion,'%Y-%m-%d %H:%i') fechamodificacion, co.visible, from_unixtime(co.fechavisible,'%Y-%m-%d %H:%i') fechavisible FROM {block_savingsbank} as co
            LEFT JOIN {block_savingsbank_categoria} as ca ON co.idcategoria=ca.id
            LEFT JOIN {block_savingsbank_estatus} as es ON co.idestatus=es.id
            LEFT JOIN {user} as u ON co.idusuario=u.id
            LEFT JOIN {user_info_data} d on d.userid=u.id
			LEFT JOIN {user_info_field} f on f.id=d.fieldid
            WHERE co.id>?  GROUP BY co.id, u.id ORDER BY fechavisible ASC";
            $questions=$DB->get_records_sql($sql,array(0));
        }else{ //El usuario es un colaborador

		    $sql="SELECT co.id, u.id as userid, u.firstname, u.lastname,MAX(IF(f.shortname='pagadoraprincipal', d.data, NULL)) as pagadoraprincipal, MAX(IF(f.shortname='pagadorasecundaria', d.data, NULL)) as pagadorasecundaria, ca.id as idcat, ca.nombre as catname, es.id as idestatus, es.nombre as estname, from_unixtime(co.fechacreacion,'%Y-%m-%d %H:%i') fechacreacion, from_unixtime(co.fechamodificacion,'%Y-%m-%d %H:%i') fechamodificacion, co.visible, from_unixtime(co.fechavisible,'%Y-%m-%d %H:%i') fechavisible FROM {block_savingsbank} as co
		    LEFT JOIN {block_savingsbank_categoria} as ca ON co.idcategoria=ca.id
		    LEFT JOIN {block_savingsbank_estatus} as es ON co.idestatus=es.id
		    LEFT JOIN {user} as u ON co.idusuario=u.id
            LEFT JOIN {user_info_data} d on d.userid=u.id
			LEFT JOIN {user_info_field} f on f.id=d.fieldid
		    WHERE u.id=? GROUP BY co.id, u.id  ORDER BY fechavisible ASC"; 
            $questions=$DB->get_records_sql($sql,array($USER->id));
        }
        
        $jefe=count($resp);
        block_savingsbank_print_questions($questions, $urlform, $jefe);
        if(empty($questions)){
                if(count($resp)>0){

                echo '<h1>Aun no se cuenta con registros</h1>';

                }else{
                block_savingsbank_print_buttom_new_question($urlform);
                }
            
  
        }

	}else if($id) {//Edita una queja

        $contextblock = CONTEXT_BLOCK::instance($blockid);
		$comentarioedit = $DB->get_record('block_savingsbank', array('id' => $id));

        $draftitemid = file_get_submitted_draft_itemid('filename');
        file_prepare_draft_area($draftitemid, $contextblock->id, 'block_savingsbank', 'block_savingsbank', $id,
                        array('subdirs' => false, 'maxfiles' => 1, 'accepted_types' => array('.png','.jpg','.pdf','.doc','.docx','.ppt','.pptx','.xls','.xlxs')));
        $comentarioedit->filename = $draftitemid;

        $savingsbank->set_data($comentarioedit);
        $savingsbank->display();

	}else {//Muestra formulario para nueva queja
        $savingsbank->display();
    }
}

echo $OUTPUT->footer();
?>
