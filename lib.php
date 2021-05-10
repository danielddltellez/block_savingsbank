<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function get_savingsbank_file($course_module_id, $component, $filearea, $itemid) {
    //$context = context_module::instance($course_module_id);
    $fs = get_file_storage();
    $files = $fs->get_area_files($course_module_id, $component, $filearea, $itemid, $sort = false, $includedirs = false);
    if (!count($files)) return false;
    return array_shift($files);
}

function block_savingsbank_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $DB, $CFG, $USER;
    //require_login($course, true, $cm);

    $filename = array_pop($args); //Lucius - Captura el nombre del artículo
    $itemid = array_pop($args); //Lucius - Captura el número de registro en la tabla
    $filepath = $args ? '/'.implode('/', $args).'/' : '/';

    if (! $file = get_savingsbank_file($context->id, 'block_savingsbank', $filearea, $itemid)) return false;
    send_stored_file($file);
}

function block_savingsbank_print_questions($questions, $urlform, $jefe = 0, $return = false) {
    global $OUTPUT, $USER, $DB;
    
    $blockid = required_param('blockid', PARAM_INT);
    $courseid = required_param('courseid', PARAM_INT);
    /*
	$display .= html_writer::start_tag('div', array('style'=> 'width: 100%; display: inline-block;'));

    //Imprime monedas acumuladas
    $display .= html_writer::start_tag('div', array('style'=> 'text-align:  left; width:40%; float: left;'));
    $display .= clean_text('<!-- p>Mis Comentarios<br></p -->');
    $display .= html_writer::end_tag('div');
    $display .= html_writer::end_tag('div');
	*/
	$display .= html_writer::start_tag('div', array('class'=>'w3-container'));
	$categoria="";

    $display .= html_writer::start_tag('table', array('id'=>'example','class'=>'display','style'=>'width:95% !important'));
        if(count($questions)>0){
            
        $esql="SELECT co.id, u.id as userid, u.firstname, u.lastname, ca.id as idcat, ca.nombre as catname, es.id as idestatus, es.nombre as estname, from_unixtime(co.fechacreacion,'%Y-%m-%d %H:%i') fechacreacion, from_unixtime(co.fechamodificacion,'%Y-%m-%d %H:%i') fechamodificacion, co.visible, from_unixtime(co.fechavisible,'%Y-%m-%d %H:%i') fechavisible FROM {block_savingsbank} as co
        LEFT JOIN {block_savingsbank_categoria} as ca ON co.idcategoria=ca.id
        LEFT JOIN {block_savingsbank_estatus} as es ON co.idestatus=es.id
        LEFT JOIN {user} as u ON co.idusuario=u.id
        WHERE u.id=? and co.idestatus =1 ORDER BY fechavisible ASC"; 

        
        $validacaja=$DB->get_records_sql($esql,array($USER->id));
        if(empty($validacaja)){
            if(empty($jefe)){
            block_savingsbank_print_buttom_new_question($urlform);
            }
        }

        $display .= html_writer::start_tag('thead');
        $display .= html_writer::start_tag('tr');
        $display .= html_writer::start_tag('th');
        $display .= clean_text('Folio');
        $display .= html_writer::end_tag('th');
        $display .= html_writer::start_tag('th');
        $display .= clean_text('Nombre');
        $display .= html_writer::end_tag('th');
        $display .= html_writer::start_tag('th');
        $display .= clean_text('Categoria');
        $display .= html_writer::end_tag('th');
        $display .= html_writer::start_tag('th');
        $display .= clean_text('Estatus');
        $display .= html_writer::end_tag('th');
        $display .= html_writer::start_tag('th');
        $display .= clean_text('Fecha publicación');
        $display .= html_writer::end_tag('th');
        $display .= html_writer::end_tag('tr');
        $display .= html_writer::end_tag('thead');
        $display .= html_writer::start_tag('tbody');

    
        foreach ($questions as $question){

            if($question->visible==0){ // Se muestra registro si el comentario es mio y no está publicado
                if($question->userid==$USER->id){
                    $display .= html_writer::start_tag('tr');
                    $display .= html_writer::start_tag('td');
                    $display .= clean_text($question->id);
                    $display .= html_writer::end_tag('td');
                        
                    $pageparam = array('blockid' => $blockid, 'courseid' => $courseid, 'idcomentario' => $question->id, 'idpadre' => 0, 'viewpage'=>1, 'id' => $question->id);
                    //Lucius - URL para modificar configuración de solicitud
                    $editurl = new moodle_url('/blocks/savingsbank/view.php', $pageparam);
                    $display .= html_writer::start_tag('td');
                    $display .= clean_text($question->firstname.' '.$question->lastname);
                    $display .= html_writer::link($editurl,html_writer::tag('i', '&nbsp;&nbsp;', array('class' => 'fa fa-pencil fa-fw fa-lg', 'aria-hidden' => 'true', 'title'=> 'Continuar editando')));
                    $display .= html_writer::end_tag('td');
                    $display .= html_writer::start_tag('td');
                    $display .= clean_text($question->catname);
                    $display .= html_writer::end_tag('td');
                    $display .= html_writer::start_tag('td');
                    $display .= clean_text($question->estname);
                    $display .= html_writer::end_tag('td');
                    $display .= html_writer::start_tag('td');
                    $display .= clean_text($question->fechavisible);
                    $display .= html_writer::end_tag('td');
                    $display .= html_writer::end_tag('tr');
                }
            }else{
                $display .= html_writer::start_tag('tr');
                $display .= html_writer::start_tag('td');
                $display .= clean_text($question->id);
                $display .= html_writer::end_tag('td');
                $pageparam = array('blockid' => $blockid, 'courseid' => $courseid, 'idcomentario' => $question->id, 'idpadre' => 0, 'viewpage'=>1);
                //Lucius - URL para modificar configuración de artículo
                $editurl = new moodle_url('/blocks/savingsbank/seguimiento.php', $pageparam);
                $display .= html_writer::start_tag('td');
                //$display .= clean_text($question->firstname.' '.$question->lastname);
                $display .= html_writer::link($editurl,clean_text($question->firstname.' '.$question->lastname, array('title'=> 'Ver seguimiento')));
                $display .= html_writer::end_tag('td');
                $display .= html_writer::start_tag('td');
                $display .= clean_text($question->catname);
                $display .= html_writer::end_tag('td');
                $display .= html_writer::start_tag('td');
                $display .= clean_text($question->estname);
                $display .= html_writer::end_tag('td');
                $display .= html_writer::start_tag('td');
                $display .= clean_text($question->fechavisible);
                $display .= html_writer::end_tag('td');
                $display .= html_writer::end_tag('tr');
            }
        }
    }

    $display .= html_writer::end_tag('tbody');
    $display .= html_writer::end_tag('table');
  

    $display .= html_writer::end_tag('div');
    
    if($return) {
        return $display;
    } else {
        echo $display;
    }
}

function block_savingsbank_print_question($question, $return = false) {
    global $OUTPUT, $USER, $DB, $CFG;
    
    //$url="";
    $display .= html_writer::start_tag('div', array('class'=>'col-md-12 articulo'));
   // $display .= $OUTPUT->heading('Portal RH');    
   /* $display .= html_writer::start_tag('div',array('style' => 'text-align: left'));
    $display .= clean_text('<strong>Folio: </strong>'.$question->id.'<br><strong>Categoría: </strong>'.$question->categoriapadre.'<br><strong>Subcategoria: </strong>'.$question->categoria.'<br><strong>Autor: </strong>'.$question->firstname.' '.$question->lastname.'<br><strong>Fecha:</strong> '.$question->fechavisible);
    $display .= html_writer::end_tag('div');
    $display .= html_writer::start_tag('div', array('style' => 'text-align: left'));
    $display .= clean_text('<strong>Estatus:</strong> '.$question->estatus);
    $display .= html_writer::end_tag('div');*/
    if($question->idpadre==1){
        $display .= html_writer::start_tag('div', array('style' => 'text-align: left'));
        $display .= clean_text('<p><b>Categoria: </b>'.$question->categoriapadre.'</p>');
        $display .= clean_text('<p><b>Sub Categoria: </b>'.$question->categoria.'</p>');
        $display .= clean_text('<p><b>Autor: </b>'.$question->firstname.' '.$question->lastname.'</p>');
        $display .= clean_text('<p><b>Fecha: </b>'.$question->fechacreacion.'</p>');
        $display .= clean_text('<p><b>Estatus: </b>'.$question->estatus.'</p>');
        $display .= clean_text('<p>La Caja de Ahorro es un beneficio opcional que te permite generar un ahorro voluntario decidiendo el % que deseas destinar y que te será entregado al final de año.</p>');
        $display .= clean_text('<p>Por medio del presente, confirmo que es mi voluntad <b>generar un ahorro voluntario</b> en la Caja de Ahorro, por lo que, manifiesto mi consentimiento para que se <b>retenga</b> de mi sueldo mensual el <b>'.$question->categoria.' </b>y conozco que el % determinado me será descontado de manera CATORCENAL.</p>');
        $display .= clean_text('<p>Doy mi consentimiento a la empresa '.$question->pagadoraprincipal.' y '.$question->pagadorasecundaria.'  para retener dicho % por concepto de Caja de Ahorro, como un beneficio adicional que le empresa me otorga.</p>');
        $display .= clean_text('<p>La cantidad total que se acumule por el total de estas retenciones, me será entregada en diciembre de cada año o al momento de la terminación laboral con la empresa.</p>');
        $display .= clean_text('<p>Te recordamos, que una vez enviado tu formato, la retención iniciará en la primera catorcena del siguiente mes a la fecha en que hayas enviado tu solicitud.</p>');
        $display .= html_writer::end_tag('div');
    }else if($question->idpadre==2){
        $display .= html_writer::start_tag('div', array('style' => 'text-align: left'));
        $display .= clean_text('<p><b>Categoria: </b>'.$question->categoriapadre.'</p>');
        $display .= clean_text('<p><b>Sub Categoria: </b>'.$question->categoria.'</p>');
        $display .= clean_text('<p><b>Autor: </b>'.$question->firstname.' '.$question->lastname.'</p>');
        $display .= clean_text('<p><b>Fecha: </b>'.$question->fechacreacion.'</p>');
        $display .= clean_text('<p><b>Estatus: </b>'.$question->estatus.'</p>');
        $display .= clean_text('<p>La Caja de Ahorro es un beneficio opcional que te permite generar un ahorro voluntario decidiendo el % que deseas destinar y que te será entregado al final de año.</p>');
        $display .= clean_text('<p>Por medio del presente, confirmo que es mi voluntad <b>generar un ahorro voluntario</b> en la Caja de Ahorro, por lo que, manifiesto mi consentimiento para que se <b>modifique</b> el % que se me retiene de mi sueldo mensual por el siguiente <b>'.$question->categoria.'</b> y conozco que el % determinado me será descontado de manera CATORCENAL.</p>');
        $display .= clean_text('<p>Doy mi consentimiento a la empresa '.$question->pagadoraprincipal.' y '.$question->pagadorasecundaria.' para retener dicho % por concepto de Caja de Ahorro, como un beneficio adicional que le empresa me otorga.</p>');
        $display .= clean_text('<p>La cantidad total que se acumule por el total de estas retenciones, me será entregada en diciembre de cada año o al momento de la terminación laboral con la empresa.</p>');
        $display .= clean_text('<p>Te recordamos, que una vez enviado tu formato, el cambio se aplicará en la primera catorcena del siguiente mes a la fecha en que hayas enviado tu solicitud.</p>');
        $display .= html_writer::end_tag('div');
    }else if($question->idpadre==3){
        $display .= html_writer::start_tag('div', array('style' => 'text-align: left'));
        $display .= clean_text('<p><b>Categoria: </b>'.$question->categoriapadre.'</p>');
        $display .= clean_text('<p><b>Sub Categoria: </b>'.$question->categoria.'</p>');
        $display .= clean_text('<p><b>Autor: </b>'.$question->firstname.' '.$question->lastname.'</p>');
        $display .= clean_text('<p><b>Fecha: </b>'.$question->fechacreacion.'</p>');
        $display .= clean_text('<p><b>Estatus: </b>'.$question->estatus.'</p>');
        $display .= clean_text('<p>La Caja de Ahorro es un beneficio opcional que te permite generar un ahorro voluntario decidiendo el % que deseas destinar y que te será entregado al final de año.</p>');
        $display .= clean_text('<p>Manifiesto mi consentimiento para que se <b>detenga la retención</b> del % actual de mi sueldo mensual por concepto de Caja de Ahorro, como un beneficio adicional que le empresa me otorga</p>');
        $display .= clean_text('<p>Doy mi consentimiento a la empresa '.$question->pagadoraprincipal.' y '.$question->pagadorasecundaria.' para retener dicho % por concepto de Caja de Ahorro, como un beneficio adicional que le empresa me otorga.</p>');
        $display .= clean_text('<p>Confirmo que sólo me será entregada en diciembre o al momento de la terminación laboral con la empresa la cantidad total que acumulé por el total de estas retenciones hasta el último día del mes de esta solicitud.</p>');
        $display .= clean_text('<p>Te recordamos, que una vez enviado tu formato, <b>el descuento se detendrá hasta la primera catorcena del siguiente mes</b> a la fecha en que hayas enviado tu solicitud.</p>');
        $display .= html_writer::end_tag('div');


    }else{

    }
    if($question->idestatus==2 || $question->idestatus==3){
        $display .= html_writer::start_tag('div', array('style' => 'text-align: left'));
        if($USER->id==$question->idusermodified){
        $display .= clean_text('<p><b>Autor: </b>'.$question->firstname.' '.$question->lastname.'</p>');
        }
        $display .= clean_text('<p><b>Fecha comentario: </b>'.$question->fechavisible.'</p>');
        $display .= clean_text('<p><b>Mensaje: </b><strong>'.$question->mensaje.'</strong></p>');
        $display .= html_writer::end_tag('div');
    }
    $display .= html_writer::end_tag('div');
    
    if($return) {
        return $display;
    } else {
        echo $display;
    }
}

function block_savingsbank_print_anwers($answers, $urlform, $urlclose, $urlback, $idestatus, $return = false) {
    global $OUTPUT, $USER, $DB, $CFG;
    
    $blockid = required_param('blockid', PARAM_INT);
    $courseid = required_param('courseid', PARAM_INT);

    $display .= html_writer::start_tag('div', array('style'=> 'width: 100%; display: inline-block;'));

    //Imprime monedas acumuladas
    $display .= html_writer::start_tag('div', array('style'=> 'text-align:  left; width:40%; float: left;'));
    $display .= clean_text('<!-- p>Mis solicitudes<br></p -->');

    $display .= html_writer::end_tag('div');
    $display .= html_writer::end_tag('div');
    
    $display .= html_writer::start_tag('div', array('class'=>'table-responsive'));
    $categoria="";

    $display .= html_writer::start_tag('table', array('class'=>'table table-hover table-striped'));

    $display .= html_writer::start_tag('tbody');

    //print_r($answers);

    if(count($answers)>0){
        block_savingsbank_print_buttom_new_answer($urlform, $urlclose, $urlback, $idestatus);
    }

    foreach ($answers as $answer){
        $url="";
        if(!(is_null($answer->contextid))){
            $file=get_savingsbank_file($answer->contextid, 'block_savingsbank', 'block_savingsbank_resp', $answer->id);
            $filename=$file->get_filename();

            $url=moodle_url::make_pluginfile_url($file->get_contextid(),$file->get_component(),$file->get_filearea(),$file->get_itemid(),$file->get_filepath(),$file->get_filename(),$forcedownload = true);
        }

        if($answer->visible==0){ // Se muestra registro si el comentario es mio y no está publicado
            if($answer->userid==$USER->id){
                $display .= html_writer::start_tag('thead');
                $display .= html_writer::start_tag('tr');
                $display .= html_writer::start_tag('th', array('scope'=>'col'));
                $display .= html_writer::start_tag('div');
                if($answer->isadmin==1){
                    $display .= clean_text('<br><strong>Fecha Creación:</strong> '.$answer->fechacreacion);    
                }else{
                    $display .= clean_text('<br>'.$answer->firstname.' '.$answer->lastname.'<br><strong>Fecha Creación:</strong> '.$answer->fechacreacion);
                }
                
                $display .= html_writer::end_tag('div');
                $display .= clean_text('Respuesta');
                $display .= html_writer::end_tag('th');
                $display .= html_writer::end_tag('tr');
                $display .= html_writer::end_tag('thead');
                
                $display .= html_writer::start_tag('tr');
                $pageparam = array('blockid' => $blockid, 'courseid' => $courseid, 'idcomentario' => $answer->idcomentario, 'id' => $answer->id, 'idpadre' => 0, 'viewpage'=>1);
                //Lucius - URL para modificar configuración de artículo
                $editurl = new moodle_url('/blocks/savingsbank/seguimiento.php', $pageparam);

                $display .= html_writer::start_tag('td');
                $display .= clean_text($answer->mensaje);
                $display .= html_writer::start_tag('div');
                if($answer->idestatus==1){ //Puede seguir editando la respuesta
                	$display .= html_writer::link($editurl,'Continuar editando '.html_writer::tag('i', '&nbsp;&nbsp;', array('class' => 'fa fa-pencil fa-fw fa-lg', 'aria-hidden' => 'true', 'title'=> 'Continuar editando')));
                }else{
                	$display .= html_writer::link('#','Continuar editando '.html_writer::tag('i', '&nbsp;&nbsp;', array('class' => 'fa fa-pencil fa-fw fa-lg', 'aria-hidden' => 'true', 'title'=> 'Continuar editando')));
                }
                $display .= html_writer::end_tag('div');
                $display .= html_writer::end_tag('td');
                $display .= html_writer::end_tag('tr');

                if($url!=""){
                    $display .= html_writer::start_tag('tr');
                    $display .= html_writer::start_tag('td');
                    $display .= clean_text('<strong>Archivos adjuntos:</strong> <a href="'.$url.'">'.$filename.'</a>');
                    $display .= html_writer::end_tag('td');
                    $display .= html_writer::end_tag('tr');
                    $url="";
                }
            }
        }else{
            $display .= html_writer::start_tag('thead');
            $display .= html_writer::start_tag('tr');
            $display .= html_writer::start_tag('th', array('scope'=>'col'));
            $display .= html_writer::start_tag('div');
            
            if($answer->isadmin==1){
                $display .= clean_text('<br><strong>Fecha Publicación:</strong> '.$answer->fechavisible);
            }else{
                $display .= clean_text('<br>'.$answer->firstname.' '.$answer->lastname.'<br><strong>Fecha Publicación:</strong> '.$answer->fechavisible);    
            }
            
            $display .= html_writer::end_tag('div');
            $display .= clean_text('Respuesta');
            $display .= html_writer::end_tag('th');
            $display .= html_writer::end_tag('tr');
            $display .= html_writer::end_tag('thead');

            $display .= html_writer::start_tag('tr');
            $display .= html_writer::start_tag('td');
            $display .= clean_text($answer->mensaje);
            $display .= html_writer::end_tag('td');
            $display .= html_writer::end_tag('tr');

            if($url!=""){
                $display .= html_writer::start_tag('tr');
                $display .= html_writer::start_tag('td');
                $display .= clean_text('<strong>Archivos adjuntos:</strong> <a href="'.$url.'">'.$filename.'</a>');
                $display .= html_writer::end_tag('td');
                $display .= html_writer::end_tag('tr');
                $url="";
            }
        }
    }

    $display .= html_writer::end_tag('tbody');
    $display .= html_writer::end_tag('table');
  

    $display .= html_writer::end_tag('div');
    
    if($return) {
        return $display;
    } else {
        echo $display;
    }
}

function block_savingsbank_print_buttom_new_question($urlform, $return = false) {

    $display .= html_writer::start_tag('tr');
    $display .= html_writer::start_tag('td');
    $display .= html_writer::link($urlform,html_writer::tag('button', 'Registrar solicitud', array('type' =>'button')));
    //$display .= html_writer::link($urlback,html_writer::tag('button', 'Regresar', array('type' =>'button')));
    $display .= html_writer::end_tag('td');
    $display .= html_writer::end_tag('tr');

    if($return) {
        return $display;
    } else {
        echo $display;
    }

}

function block_savingsbank_print_buttom_new_answer($urlform, $urlclose, $urlback, $idestatus, $return = false) {

    $display .= html_writer::start_tag('tr');
    $display .= html_writer::start_tag('td');
    if($idestatus==1){
        $display .= html_writer::link($urlform,html_writer::tag('button', 'Registrar respuesta', array('type' =>'button')));
        $display .= html_writer::link($urlclose,html_writer::tag('button', 'Terminar seguimiento', array('type' =>'button')));
    }
    $display .= html_writer::link($urlback,html_writer::tag('button', 'Regresar', array('type' =>'button')));
    $display .= html_writer::end_tag('td');
    $display .= html_writer::end_tag('tr');

    if($return) {
        return $display;
    } else {
        echo $display;
    }

}

function block_savingsbank_send_notification($idfolio,$emailu){
    global $USER, $DB;

    $sqlqn="SELECT co.id, co.idusuario, u.firstname, u.lastname,MAX(IF(f.shortname='pagadoraprincipal', d.data, NULL)) as pagadoraprincipal, MAX(IF(f.shortname='pagadorasecundaria', d.data, NULL)) as pagadorasecundaria, co.idcategoria, ca.nombre as categoria, ca.idpadre, (select nombre FROM {block_savingsbank_categoria}
    WHERE id=ca.idpadre) as categoriapadre, co.mensaje, co.idestatus, ce.nombre as estatus, from_unixtime(co.fechacreacion,'%Y-%m-%d %H:%i') fechacreacion, from_unixtime(co.fechavisible,'%Y-%m-%d %H:%i') fechavisible, co.visible
    FROM {block_savingsbank} as co 
    LEFT JOIN {user} as u ON co.idusuario=u.id 
    LEFT JOIN {block_savingsbank_estatus} as ce ON co.idestatus=ce.id 
    LEFT JOIN {block_savingsbank_categoria} as ca ON co.idcategoria=ca.id
    LEFT JOIN {user_info_data} d on d.userid=u.id
    LEFT JOIN {user_info_field} f on f.id=d.fieldid
    WHERE co.id=? GROUP BY co.id, u.id";
    $question = $DB->get_record_sql($sqlqn, array($idfolio));
  
    if($question->idpadre==1){
        $mensaje .= 'Colaborador: '.$question->firstname.' '.$question->lastname.'';
        $mensaje .= 'Categoria: '.$question->categoriapadre.'';
        $mensaje .= 'Sub Categoria: '.$question->categoria.'';
        /*$mensaje .= 'La Caja de Ahorro es un beneficio opcional que te permite generar un ahorro voluntario decidiendo el % que deseas destinar y que te será entregado al final de año.';
        $mensaje .= 'Por medio del presente, confirmo que es mi voluntad generar un ahorro voluntario en la Caja de Ahorro, por lo que, manifiesto mi consentimiento para que se retenga de mi sueldo mensual el '.$question->categoria.' y conozco que el % determinado me será descontado de manera CATORCENAL.';
        $mensaje .= 'Doy mi consentimiento a la empresa '.$question->pagadoraprincipal.' y '.$question->pagadorasecundaria.'  para retener dicho % por concepto de Caja de Ahorro, como un beneficio adicional que le empresa me otorga.';
        $mensaje .= 'La cantidad total que se acumule por el total de estas retenciones, me será entregada en diciembre de cada año o al momento de la terminación laboral con la empresa.';
        $mensaje .= 'Te recordamos, que una vez enviado tu formato, la retención iniciará en la primera catorcena del siguiente mes a la fecha en que hayas enviado tu solicitud.';
        */
    }else if($question->idpadre==2){
        $mensaje .= 'Colaborador: '.$question->firstname.' '.$question->lastname.'';
        $mensaje .= 'Categoria: '.$question->categoriapadre.'';
        $mensaje .= 'Sub Categoria: '.$question->categoria.'';
        /*$mensaje .= 'La Caja de Ahorro es un beneficio opcional que te permite generar un ahorro voluntario decidiendo el % que deseas destinar y que te será entregado al final de año.';
        $mensaje .= 'Por medio del presente, confirmo que es mi voluntad generar un ahorro voluntario en la Caja de Ahorro, por lo que, manifiesto mi consentimiento para que se modifique el % que se me retiene de mi sueldo mensual por el siguiente '.$question->categoria.' y conozco que el % determinado me será descontado de manera CATORCENAL.';
        $mensaje .= 'Doy mi consentimiento a la empresa '.$question->pagadoraprincipal.' y '.$question->pagadorasecundaria.' para retener dicho % por concepto de Caja de Ahorro, como un beneficio adicional que le empresa me otorga.';
        $mensaje .= 'La cantidad total que se acumule por el total de estas retenciones, me será entregada en diciembre de cada año o al momento de la terminación laboral con la empresa.';
        $mensaje .= 'Te recordamos, que una vez enviado tu formato, el cambio se aplicará en la primera catorcena del siguiente mes a la fecha en que hayas enviado tu solicitud.';
        */
    }else if($question->idpadre==3){
        $mensaje .= 'Colaborador: '.$question->firstname.' '.$question->lastname.'';
        $mensaje .= 'Categoria: '.$question->categoriapadre.'';
        $mensaje .= 'Sub Categoria: '.$question->categoria.'';
        /*$mensaje .= 'La Caja de Ahorro es un beneficio opcional que te permite generar un ahorro voluntario decidiendo el % que deseas destinar y que te será entregado al final de año.';
        $mensaje .= 'Manifiesto mi consentimiento para que se detenga la retención del % actual de mi sueldo mensual por concepto de Caja de Ahorro, como un beneficio adicional que le empresa me otorga';
        $mensaje .= 'Doy mi consentimiento a la empresa '.$question->pagadoraprincipal.' y '.$question->pagadorasecundaria.' para retener dicho % por concepto de Caja de Ahorro, como un beneficio adicional que le empresa me otorga.';
        $mensaje .= 'Confirmo que sólo me será entregada en diciembre o al momento de la terminación laboral con la empresa la cantidad total que acumulé por el total de estas retenciones hasta el último día del mes de esta solicitud.';
        $mensaje .= 'Te recordamos, que una vez enviado tu formato, el descuento se detendrá hasta la primera catorcena del siguiente mes a la fecha en que hayas enviado tu solicitud.';
        */

    }else{

    }
    
    /*$fechaap=date("d-m-Y H:i:s");
    $newlink = str_replace("&amp;", "&", $courseurl);*/
    $subjectNew="Nueva solicitud de caja de ahorro";
    $emailu=(string)$emailu;
    //$mensaje1= "Hola, \n\n se ha registrado una nueva solicitud con el folio No. $idfolio con fecha $fechaap ,ingresa por favor a darle seguimiento.\n\nLink:";

    $link = str_replace("&amp;", "&", $courseurl);
    $link = 'https://www.portal3i.mx/openlms/tripleI.php?key='.base64_encode("email=$emailu&courseid=1");
    $parametros=array();
    $clienteSOAP = new SoapClient('http://192.168.14.30:8080/svcELearning.svc?wsdl');
    try{
        //parametros de la llamada para envio notificacion por email
        $parametros['mensaje']= $mensaje."~$link~";
        $parametros['correo']="$emailu";
        $parametros['aplicacion']=$subjectNew;
        $parametros['idAplicacion']=(int)9; 
        $parametros['IdAmbiente']=(int)1;
        $parametros['IdTipoNotificacion']=(int)0;
        $result = $clienteSOAP->Notificacion($parametros);
        $statusfinal = $result->envioNotificacionUsuarioResult;

        //echo $statusfinal;
    }catch(SoapFault $e){
           // var_dump($e);
         //   exit();
    }




}
function block_savingsbank_send_notification_cancelacion($idfolio){
    global $USER, $DB;

    $sqlqn="SELECT co.id, co.idusuario, u.firstname, u.lastname,MAX(IF(f.shortname='pagadoraprincipal', d.data, NULL)) as pagadoraprincipal, MAX(IF(f.shortname='pagadorasecundaria', d.data, NULL)) as pagadorasecundaria, co.idcategoria, ca.nombre as categoria, ca.idpadre, (select nombre FROM {block_savingsbank_categoria}
    WHERE id=ca.idpadre) as categoriapadre, co.mensaje, co.idestatus, ce.nombre as estatus, from_unixtime(co.fechacreacion,'%Y-%m-%d %H:%i') fechacreacion, from_unixtime(co.fechavisible,'%Y-%m-%d %H:%i') fechavisible, co.visible
    FROM {block_savingsbank} as co 
    LEFT JOIN {user} as u ON co.idusuario=u.id 
    LEFT JOIN {block_savingsbank_estatus} as ce ON co.idestatus=ce.id 
    LEFT JOIN {block_savingsbank_categoria} as ca ON co.idcategoria=ca.id
    LEFT JOIN {user_info_data} d on d.userid=u.id
    LEFT JOIN {user_info_field} f on f.id=d.fieldid
    WHERE co.id=? GROUP BY co.id, u.id";
    $question = $DB->get_record_sql($sqlqn, array($idfolio));
    
    $mensaje = 'Colaborador: '.$question->firstname.' '.$question->lastname.'
                Categoria: '.$question->categoriapadre.'
                Sub Categoria: '.$question->categoria.'
                El folio '.$idfolio.' ha sido cancelado';
    /*
    $fechaap=date("d-m-Y H:i:s");
    $newlink = str_replace("&amp;", "&", $courseurl);*/
    $subjectNew="Cancelación de solicitud de caja de ahorro";
    $sqladmin="SELECT sr.id, u.email as correoelectronico
    from {block_savingsbank_responsa} sr
    join {user} u on u.id=sr.idusuario where sr.estatus=?";
    $respadmin = $DB->get_records_sql($sqladmin, array(1));
    
    

    //$mensaje1= "Hola, \n\n se ha registrado una nueva solicitud con el folio No. $idfolio con fecha $fechaap ,ingresa por favor a darle seguimiento.\n\nLink:";

       // $link = str_replace("&amp;", "&", $courseurl);

        foreach($respadmin as $values){
            $ids=$values->id;
            $emailu=$values->correoelectronico;
            $emailu=(string)$emailu;
            $link = 'https://www.portal3i.mx/openlms/tripleI.php?key='.base64_encode("email=$emailu&courseid=1");
            $parametros=array();
            $clienteSOAP = new SoapClient('http://192.168.14.30:8080/svcELearning.svc?wsdl');
        try{
            //parametros de la llamada para envio notificacion por email
            $parametros['mensaje']= $mensaje."~$link~";
            $parametros['correo']="$emailu";
            $parametros['aplicacion']=$subjectNew;
            $parametros['idAplicacion']=(int)9; 
            $parametros['IdAmbiente']=(int)1;
            $parametros['IdTipoNotificacion']=(int)0;

            $result = $clienteSOAP->Notificacion($parametros);
            $statusfinal = $result->envioNotificacionUsuarioResult;
        }catch(SoapFault $e){
           //  var_dump($e);
        }
    }
    


}
function block_savingsbank_send_notification_aprobacion($idfolio,$idestatus){
    global $DB;

    $sqlqn="SELECT co.id, co.idusuario, u.firstname, u.lastname, u.email as emailcolaborador, MAX(IF(f.shortname='pagadoraprincipal', d.data, NULL)) as pagadoraprincipal, MAX(IF(f.shortname='pagadorasecundaria', d.data, NULL)) as pagadorasecundaria, co.idcategoria, ca.nombre as categoria, ca.idpadre, (select nombre FROM {block_savingsbank_categoria}
    WHERE id=ca.idpadre) as categoriapadre, co.mensaje, co.idestatus, ce.nombre as estatus, from_unixtime(co.fechacreacion,'%Y-%m-%d %H:%i') fechacreacion, from_unixtime(co.fechavisible,'%Y-%m-%d %H:%i') fechavisible, co.visible
    FROM {block_savingsbank} as co 
    LEFT JOIN {user} as u ON co.idusuario=u.id 
    LEFT JOIN {block_savingsbank_estatus} as ce ON co.idestatus=ce.id 
    LEFT JOIN {block_savingsbank_categoria} as ca ON co.idcategoria=ca.id
    LEFT JOIN {user_info_data} d on d.userid=u.id
    LEFT JOIN {user_info_field} f on f.id=d.fieldid
    WHERE co.id=? GROUP BY co.id, u.id";
    $question = $DB->get_record_sql($sqlqn, array($idfolio));
    if($idestatus==2){

        $mensaje .= 'Tu folio '.$idfolio.' ha sido aprobado por recursos humanos.';

    }else if($idestatus==3){

        $mensaje .= 'Tu folio '.$idfolio.' ha sido cancelado por recursos humanos.';


    }else{
        
    }
    
    $fechaap=date("d-m-Y H:i:s");
    $newlink = str_replace("&amp;", "&", $courseurl);
    $subjectNew="Se atendio la solicitud";
    $emailu=$question->emailcolaborador;
    $link = str_replace("&amp;", "&", $courseurl);
    $link = 'https://www.portal3i.mx/openlms/tripleI.php?key='.base64_encode("email=$emailu&courseid=1");
    //$mensaje1= "Hola, \n\n se ha registrado una nueva solicitud con el folio No. $idfolio con fecha $fechaap ,ingresa por favor a darle seguimiento.\n\nLink:";
    $parametros=array();
    $clienteSOAP = new SoapClient('http://192.168.14.30:8080/svcELearning.svc?wsdl');
    try{
        //parametros de la llamada para envio notificacion por email
        $parametros['mensaje']= $mensaje."~$link~";
        $parametros['correo']="$emailu";
        $parametros['aplicacion']=$subjectNew;
        $parametros['idAplicacion']=(int)9; 
        $parametros['IdAmbiente']=(int)1;
        $parametros['IdTipoNotificacion']=(int)0;

        $result = $clienteSOAP->Notificacion($parametros);
        $statusfinal = $result->envioNotificacionUsuarioResult;
    }catch(SoapFault $e){
        // var_dump($e);
    }


}
