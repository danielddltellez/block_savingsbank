<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once('../../config.php');
require_once('integrantes_form.php');

global $DB, $OUTPUT, $PAGE;

$courseid = required_param('courseid', PARAM_INT);
$blockid = required_param('blockid', PARAM_INT);
$viewpage = optional_param('viewpage', false, PARAM_BOOL);
$id = optional_param('id', 0, PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_savingsbank', $courseid);
}

require_login($course);

$PAGE->set_url('/blocks/savingsbank/integrantes.php', array('courseid' => $courseid, 'courseid' => $courseid, 'id'=>'$id', 'viewpage'=> $viewpage));
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('edithtml', 'block_savingsbank'));


$settingsnode = $PAGE->settingsnav->add('Caja de ahorro');

$sql="SELECT cor.id FROM {block_savingsbank_responsa} as cor WHERE cor.idusuario=? and cor.estatus=1";
$resp=$DB->get_records_sql($sql,array($USER->id));

if (count($resp)>0) {
    //Enlace a Mis comentarios
    $url = new moodle_url('/blocks/savingsbank/view.php', array('blockid' => $blockid, 'courseid' => $COURSE->id, 'id' => 0, 'viewpage' => 1));
    $editnode = $settingsnode->add('Seguimiento a Quejas', $url);
    $urlreport = new moodle_url('/blocks/savingsbank/reports.php', array('blockid' => $blockid, 'courseid' => $COURSE->id, 'id' => '0', 'viewpage' => '1'));
    $editnode = $settingsnode->add('Reportes', $urlreports);
    $editnode->make_active();
    //link lineamientos 
    $urlpoliticas = new moodle_url('/blocks/savingsbank/docs/LineamientosBQ.pdf');
    $editnode = $settingsnode->add('Lineamientos', $urlpoliticas);
}else{
    $site = get_site();
    echo $OUTPUT->header();
    echo 'No cuenta con los permisos suficientes!';
    echo $OUTPUT->continue_button('/my');
    echo $OUTPUT->footer();
    exit();
}
//Navegación en pestañas
//if (user_has_role_assignment($USER->id, 1) || is_siteadmin()) {
if (count($resp)>0) {
    $site = get_site();    
    $renderer = $PAGE->get_renderer('block_savingsbank');
    echo $OUTPUT->header();
    echo $renderer->navigation('integrantes', $blockid, $courseid, $id, $viewpage);
}

$integranteform = new integrantes_form();

$toform['blockid'] = $blockid;
$toform['courseid'] = $courseid;
$toform['viewpage'] = $viewpage;
$toform['id'] = $id;

$integranteform->set_data($toform);
$equipourl = new moodle_url('/blocks/savingsbank/integrantes.php', array('blockid' => $blockid, 'courseid' => $courseid, 'id' => $id, 'viewpage' => $viewpage));

if (optional_param('addsel', false, PARAM_BOOL)) {

    $fromform=$integranteform->get_data();
    if (!empty($fromform->ausers)) {
        $fecha = new DateTime();
        $fechacreacion=$fecha->getTimestamp();

        foreach ($fromform->ausers as $auser) {

        	$chstatus="SELECT id FROM {block_savingsbank_responsa} as cor WHERE cor.idusuario=?;";
            $userselecteds = $DB->get_records_sql($chstatus, array($auser));
            if (count($userselecteds)>0) {
            	foreach ($userselecteds as $uselected) {
                    $sql="UPDATE {block_savingsbank_responsa} SET estatus=1, fechamodificacion=?, idmodificador=? WHERE id=?";
                    if (!($last=$DB->execute($sql, array($fechacreacion, $USER->id, $uselected->id)))) {
            	        print_error('updateerror', 'block_savingsbank_responsa');
                    }
                }
            }else{
                $sql="INSERT INTO {block_savingsbank_responsa} (idusuario, idcategoria, fechacreacion, idmodificador, fechamodificacion, estatus) VALUES (?, 1, ?, ?, ?,1)";
                if (!($last=$DB->execute($sql, array($auser, $fechacreacion, $USER->id,$fechacreacion)))) {
            	    print_error('updateerror', 'block_savingsbank_responsa');
                }
            }
        }
        redirect($equipourl);
    }else{
        echo '<br>seleccione usuarios disponibles';
        echo $OUTPUT->continue_button($equipourl);
    }
    echo $OUTPUT->footer();
}else if (optional_param('removesel', false, PARAM_BOOL)) { //Intenta insertar nuevo registro de ciclos

    $fromform=$integranteform->get_data();
    if (!empty($fromform->susers)) {

    	$fecha = new DateTime();
        $fechamodificacion=$fecha->getTimestamp();

        foreach ($fromform->susers as $suser) {
        	$chstatus="SELECT id FROM {block_savingsbank_responsa} as cor WHERE cor.idusuario=?;";
            $userselecteds = $DB->get_records_sql($chstatus, array($suser));
            foreach ($userselecteds as $uselected) {
                $sql="UPDATE {block_savingsbank_responsa} SET estatus=0, fechamodificacion=?, idmodificador=? WHERE id=?";
                if (!($last=$DB->execute($sql, array($fechamodificacion, $USER->id, $uselected->id)))) {
            	    print_error('updateerror', 'block_savingsbank_responsa');
                }
            }
        }
        redirect($equipourl);
    }else{
        echo '<br>seleccione usuarios a remover';
        echo $OUTPUT->continue_button($equipourl);
    }
        echo $OUTPUT->footer();
}else{ //Despliega el formulario

    $integranteform->display();

    echo $OUTPUT->footer();
}
