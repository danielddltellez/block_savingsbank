<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once('../../config.php');
require_once('./view/view.php');
//require_once('./bootstrap-4/css/bootstrap.css');
global $DB, $OUTPUT, $USER;

// Check for all required variables.
$courseid = required_param('courseid', PARAM_INT);
$blockid = required_param('blockid', PARAM_INT); 
// Next look for optional variables.
$id = optional_param('id', 0, PARAM_INT);
$viewpage = optional_param('viewpage', false, PARAM_BOOL);

//Define formato para CSV
$where="";
$nombre=$_POST['xnombre'];
$estatus=$_POST['xestatus'];
$fechaini=$_POST['xfechainicio'];
$fechafin=$_POST['xfechafin'];


//print_r($_POST);

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

$settingsnode = $PAGE->settingsnav->add('Portal RH');

$sql="SELECT cor.id FROM {block_savingsbank_responsa} as cor WHERE cor.idusuario=? and cor.estatus=1";
$resp=$DB->get_records_sql($sql,array($USER->id));

if (count($resp)>0) {
    //Enlace a Mis comentarios
    $url = new moodle_url('/blocks/savingsbank/view.php', array('blockid' => $blockid, 'courseid' => $COURSE->id, 'id' => 0, 'viewpage' => 1));
    $editnode = $settingsnode->add('Seguimiento a solicitudes', $url);
    $urlreport = new moodle_url('/blocks/savingsbank/reports.php', array('blockid' => $blockid, 'courseid' => $COURSE->id, 'id' => '0', 'viewpage' => '1'));
    $editnode = $settingsnode->add('Reportes', $urlreports);
    $editnode->make_active();
    //link lineamientos 
    /*
    $urlpoliticas = new moodle_url('/blocks/savingsbank/docs/LineamientosBQ.pdf');
    $editnode = $settingsnode->add('Lineamientos', $urlpoliticas);
    */
}else{
    $site = get_site();
    echo $OUTPUT->header();
    echo 'No cuenta con los permisos suficientes!';
    echo $OUTPUT->continue_button('/my');
    echo $OUTPUT->footer();
    exit();
}

if($excel){
    $nombre=$_GET['xnombre'];
    $estatus=$_GET['xestatus'];
    $fechaini=$_GET['xfechainicio'];
    $fechafin=$_GET['xfechafin'];
    $shortname = 'Reporte_caja';
    header('Content-Disposition: attachment; filename='.$shortname.'.csv');
    header('Content-Type: text/csv; charset=UTF-16LE');
    print chr(0xFF).chr(0xFE);
    $sep="\t".chr(0);
    $line="\n".chr(0);

}else{
    //Navigación en pestañas
    $site = get_site();	
	$renderer = $PAGE->get_renderer('block_savingsbank');
    echo $OUTPUT->header();
  
	echo $renderer->navigation('reports', $blockid, $courseid, $id, $viewpage);
    echo $head;
    //$records=$DB->get_records_sql($sql);
    echo'<form method="POST" class="w3-container">
    <div class="w3-row-padding">
        <div class="w3-half">
            <label for="nombre">Nombre del usuario</label>
            <input type="text"  class="w3-select"  placeholder="Nombre..." name="xnombre"/>
        </div>
        <div class="w3-half">
            <select  class="w3-input"  name="xestatus">
                <option value="">Selecciona estatus</option>
                <option value="1">Nuevo</option>
                <option value="2">Atendido</option>
                <option value="3">Cancelado</option>
            </select>
        </div>
    </div>
    <br>
    <div class="w3-row-padding">
        <div class="w3-half">
            <label for="start">Fecha publicación inicio:</label>
            <input type="date"  class="w3-input"  placeholder="Fecha Desde:"  name="xfechainicio"/>
        </div>
        <div class="w3-half">
            <label for="end">Fecha publicación fin:</label>
            <input type="date"  class="w3-input"  laceholder="Hasta"  name="xfechafin"/>
        </div>
    </div>
    <br>
    <div class="w3-container">
        <button  class="w3-button w3-green" name="buscar" type="submit">Buscar</button>
    </div>
    <br>
    </form>';
}
/*
if ((!empty($_POST['xnombre']) || !empty($_GET['xnombre'])) && (empty($_POST['xestatus']) || empty($_GET['xestatus'])) && (empty($_POST['xfechainicio']) && empty($_POST['xfechafin']) || empty($_GET['xfechainicio']) && empty($_GET['xfechafin']))){
    $where="where co.id>0 and co.visible=1 and concat(u.firstname,' ',u.lastname) like '".$nombre."%' ORDER BY fechavisible ASC";
}else if (!empty($_POST['xestatus']) || !empty($_GET['xestatus'])){
    $where="where co.id>0 and co.visible=1 and  es.id = '".$estatus."%' ORDER BY fechavisible ASC";
}else if (!empty($_POST['xfechainicio']) && !empty($_POST['xfechafin']) || !empty($_GET['xfechainicio']) && !empty($_GET['xfechafin'])){
    $where="where co.id>0 and co.visible=1 and co.fechacreacion BETWEEN UNIX_TIMESTAMP('".$fechaini."') AND UNIX_TIMESTAMP('".$fechafin."') ORDER BY fechavisible ASC";
}else if ((!empty($_POST['xnombre']) || !empty($_GET['xnombre'])) && (!empty($_POST['xestatus']) || !empty($_GET['xestatus']))){
    $where="where co.id>0 and co.visible=1 and concat(u.firstname,' ',u.lastname) like '".$nombre."%' and  es.id = '".$estatus."%' ORDER BY fechavisible ASC";
}else{
    $where="where co.id>0 and co.visible=1 ORDER BY fechavisible ASC";
}*/
if ((!empty($nombre)) && (empty($estatus)) && (empty($fechaini) && empty($fechafin))){
    $where="where co.id>0 and co.visible=1 and concat(u.firstname,' ',u.lastname) like '".$nombre."%' ORDER BY fechavisible ASC";
}else if ((empty($nombre)) && (!empty($estatus)) && (empty($fechaini) && empty($fechafin))){
    $where="where co.id>0 and co.visible=1 and  es.id = '".$estatus."%' ORDER BY fechavisible ASC";
}else if ((empty($nombre)) && (empty($estatus)) && (!empty($fechaini) && !empty($fechafin))){
    $where="where co.id>0 and co.visible=1 and co.fechacreacion BETWEEN UNIX_TIMESTAMP('".$fechaini."') AND UNIX_TIMESTAMP('".$fechafin." 23:55:00') ORDER BY fechavisible ASC";
}else if ((!empty($nombre)) && (!empty($estatus)) && (empty($fechaini) && empty($fechafin))){
    $where="where co.id>0 and co.visible=1 and concat(u.firstname,' ',u.lastname) like '".$nombre."%' and  es.id = '".$estatus."%' ORDER BY fechavisible ASC";
}else if ((empty($nombre)) && (!empty($estatus)) && (!empty($fechaini) && !empty($fechafin))){
    $where="where co.id>0 and co.visible=1 and co.fechacreacion BETWEEN UNIX_TIMESTAMP('".$fechaini."') AND UNIX_TIMESTAMP('".$fechafin." 23:55:00') and  es.id = '".$estatus."%' ORDER BY fechavisible ASC";
}else if ((!empty($nombre)) && (!empty($estatus)) && (!empty($fechaini) && !empty($fechafin))){
    $where="where co.id>0 and co.visible=1 and concat(u.firstname,' ',u.lastname) like '".$nombre."%' and co.fechacreacion BETWEEN UNIX_TIMESTAMP('".$fechaini."') AND UNIX_TIMESTAMP('".$fechafin." 23:55:00') and  es.id = '".$estatus."%' ORDER BY fechavisible ASC";
}else{
    $where="where co.id>0 and co.visible=1 ORDER BY fechavisible ASC";
}
 
 

$sql="select co.id, u.id as userid, u.firstname, u.lastname, ca.id as idcat, ca.nombre as catname, co.mensaje, es.id as idestatus, es.nombre as estname, from_unixtime(co.fechacreacion,'%Y-%m-%d %H:%i') fechacreacion, from_unixtime(co.fechamodificacion,'%Y-%m-%d %H:%i') fechamodificacion, co.visible, from_unixtime(co.fechavisible,'%Y-%m-%d %H:%i') fechavisible FROM {block_savingsbank} as co
    LEFT JOIN {block_savingsbank_categoria} as ca ON co.idcategoria=ca.id
    LEFT JOIN {block_savingsbank_estatus} as es ON co.idestatus=es.id
    LEFT JOIN {user} as u ON co.idusuario=u.id
    $where";
$records=$DB->get_records_sql($sql,array());

if($excel){
    echo $sep . csv_quote('No.');
    echo $sep . csv_quote('Nombre');
    echo $sep . csv_quote('Comentario');
    echo $sep . csv_quote('Categoria');
    echo $sep . csv_quote('Estatus');
    echo $sep . csv_quote('Fecha Publicación');
    echo $sep . csv_quote('Fecha Respuesta');
    print $line;
    foreach($records as $record) {
        echo $sep . csv_quote($record->id);
        echo $sep . csv_quote($record->firstname.' '.$record->lastname);
        echo $sep . csv_quote($record->mensaje);
        echo $sep . csv_quote($record->catname);
        echo $sep . csv_quote($record->estname);
        echo $sep . csv_quote($record->fechacreacion);
        echo $sep . csv_quote($record->fechamodificacion);
       /* if ($record->idestatus==2) { //Si 
            echo $sep . csv_quote($record->fechamodificacion);
        }else{
            echo $sep . csv_quote('');
        }*/
        
        print $line;
    }
    
}else{
	
    print '<table id="completion-progress" class="generaltable flexible boxaligncenter" style="text-align:left">
    <thead><tr style="vertical-align:top">';
    print '<th scope="col" class="completion-sortchoice" style="font-size:13px">No.</th>';
    print '<th scope="col" class="completion-sortchoice" style="font-size:13px">Nombre</th>';
    print '<th scope="col" class="completion-identifyfield" style="font-size:13px">Comentario</th>';
    print '<th scope="col" class="completion-sortchoice" style="font-size:13px">Categoria</th>';
    print '<th scope="col" class="completion-identifyfield" style="font-size:13px">Estatus</th>';
    print '<th scope="col" class="completion-sortchoice" style="font-size:13px">Fecha Publicación</th>';
    print '<th scope="col" class="completion-identifyfield" style="font-size:13px">Fecha respuesta</th>';
   // print '<th scope="col" class="completion-identifyfield" style="font-size:13px">Descargar</th>';
    print '</tr>';
    print '</thead>';
    print '<tbody>';
    foreach($records as $record) {
        print '<tr><th scope="row" style="font-size:13px">'.$record->id.'</th>';
		print '<td style="font-size:13px">'.$record->firstname.' '.$record->lastname.'</td>'
        . '<td style="font-size:13px">'.$record->mensaje.'</td>'
        . '<td style="font-size:13px">'.$record->catname.'</td>'
        . '<td style="font-size:13px">'.$record->estname.'</td>'
        . '<td style="font-size:13px">'.$record->fechacreacion.'</td>'
        . '<td style="font-size:13px">'.$record->fechamodificacion.'</td>';
        /*
        if ($record->idestatus==2) { //Si 
            print '<td style="font-size:13px">'.$record->fechamodificacion.'</td>';
        }else{
            print '<td style="font-size:13px"></td>';
        }*/
       // print '<td style="font-size:13px"><a href="reportmsg.php?courseid='.$courseid.'&blockid='.$blockid.'&idcomentario='.$record->id.'&viewpage='.$viewpage.'&format=excelcsv" target="_blank"><i class="fa fa-download" aria-hidden="true"></i></a></td>';
        print '</tr>';
    }
    print '</tbody></table>';
    
    print '<ul><li><a href="reports.php?courseid='.$courseid.'&amp;blockid='.$blockid.'&ampid='.$id.'&ampviewpage='.$viewpage.'&xnombre='.$nombre.'&xestatus='.$estatus.'&xfechainicio='.$fechaini.'&xfechafin='.$fechafin.'&amp;format=excelcsv">Descargar reporte</a></li></ul>';
    
    echo $OUTPUT->footer();
}