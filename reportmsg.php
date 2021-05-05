<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once('../../config.php');
//require_once('./bootstrap-4/css/bootstrap.css');
global $DB, $OUTPUT, $USER;

// Check for all required variables.
$courseid = required_param('courseid', PARAM_INT);
$blockid = required_param('blockid', PARAM_INT);
$idcomentario = required_param('idcomentario', PARAM_INT);
// Next look for optional variables.
$id = optional_param('id', 0, PARAM_INT);
$viewpage = optional_param('viewpage', false, PARAM_BOOL);

//Define formato para CSV

$format = optional_param('format','',PARAM_ALPHA);
$excel = $format == 'excelcsv';
$csv = $format == 'csv' || $excel;

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_savingsbank', $courseid);
}

$leftcols = 1;
//Función para excel
function csv_quote($value) {
    global $excel;
    if ($excel) {
        return core_text::convert('"'.str_replace('"',"'",$value).'"','UTF-8','UTF-16LE');
    } else {
        return '"'.str_replace('"',"'",$value).'"';
    }
}

require_login($course);

$PAGE->set_url('/blocks/savingsbank/reports.php', array('id' => $courseid));
$PAGE->set_pagelayout('report');
$PAGE->set_heading(get_string('edithtml', 'block_savingsbank'));

$settingsnode = $PAGE->settingsnav->add('Comentarios');

$sql="SELECT cor.id FROM {block_savingsbank_responsa} as cor WHERE cor.idusuario=? and cor.estatus=1";
$resp=$DB->get_records_sql($sql,array($USER->id));

if (count($resp)>0) {
    //Enlace a Mis comentarios
    $url = new moodle_url('/blocks/savingsbank/view.php', array('blockid' => $blockid, 'courseid' => $COURSE->id, 'id' => 0, 'viewpage' => 1));
    $editnode = $settingsnode->add('Seguimiento a comentarios', $url);
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
    $shortname = 'Reporte_de_seguimiento';
    header('Content-Disposition: attachment; filename='.$shortname.'.csv');
    
    header('Content-Type: text/csv; charset=UTF-16LE');
    print chr(0xFF).chr(0xFE);
    $sep="\t".chr(0);
    $line="\n".chr(0);

//Comentario original
$sql="SELECT co.id, co.idusuario, u.firstname, u.lastname, co.idcategoria, co.asunto, co.mensaje, co.idestatus, ce.nombre as estatus, from_unixtime(co.fechacreacion,'%Y-%m-%d %H:%i') fechacreacion, from_unixtime(co.fechavisible,'%Y-%m-%d %H:%i') fechavisible, co.visible, co.iddoc FROM {block_savingsbank} as co, {user} as u, {block_savingsbank_estatus} as ce WHERE co.idusuario=u.id and co.id=? and co.idestatus=ce.id";
$question = $DB->get_record_sql($sql, array($idcomentario));

echo $sep . csv_quote('Asunto: ');
echo $sep . csv_quote($question->asunto);
print $line;
echo $sep . csv_quote('Autor: ');
echo $sep . csv_quote($question->firstname.' '.$question->lastname);
print $line;
echo $sep . csv_quote('Fecha: ');
echo $sep . csv_quote($question->fechavisible);
print $line;
echo $sep . csv_quote('Mensaje: ');
echo $sep . csv_quote($question->mensaje);
print $line;
echo $sep . csv_quote('Estatus: ');
echo $sep . csv_quote($question->estatus);
print $line;
print $line;
$sql="SELECT resp.id, resp.idcomentario, co.idestatus, resp.idpadre, resp.idusuario as userid, u.firstname, u.lastname, resp.mensaje,  from_unixtime(resp.fechacreacion,'%Y-%m-%d %H:%i') fechacreacion, from_unixtime(resp.fechamodificacion,'%Y-%m-%d %H:%i') fechamodificacion, resp.visible, from_unixtime(resp.fechavisible,'%Y-%m-%d %H:%i') fechavisible FROM {block_savingsbank_resp} as resp
		LEFT JOIN {user} as u ON resp.idusuario=u.id
        LEFT JOIN {block_savingsbank} as co ON resp.idcomentario=co.id
		WHERE resp.idcomentario=? ORDER BY fechavisible ASC";

$records=$DB->get_records_sql($sql,array($idcomentario));

foreach($records as $record) {
	if($record->visible==1){
  		echo $sep . csv_quote($record->firstname.' '.$record->lastname);
  		print $line;
        echo $sep . csv_quote('Fecha Publicación: ');
        echo $sep . csv_quote($record->fechavisible);
        print $line;
        echo $sep . csv_quote('Respuesta: ');
        echo $sep . csv_quote($record->mensaje);
        print $line;
   	}        
    print $line;
}