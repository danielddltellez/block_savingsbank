<?php
require_once("{$CFG->libdir}/formslib.php");
require_once($CFG->dirroot.'/blocks/savingsbank/lib.php');
 
class seguimiento_form extends moodleform {
 
    function definition() {

    	global $DB, $USER;
 
        $mform =& $this->_form;
        $mform->addElement('header','displayinfo', 'Caja de ahorro');

 
        $mform->addElement('textarea', 'mensaje', 'Mensaje');
        $mform->setType('mensaje', PARAM_RAW);
        $mform->addRule('mensaje', null, 'required', null, 'client');
            
            // add date_time selector
            //$mform->addElement('date_time_selector', 'fechacreacion', 'Fecha');

        //Es administrador de reportes
        $sql1="SELECT cor.id FROM {block_savingsbank_responsa} as cor WHERE cor.idusuario=? and cor.estatus=1";
        $resp1=$DB->get_records_sql($sql1,array($USER->id));


        if(count($resp1)>0){
            $mform->addElement('selectyesno', 'visible', 'Publicar');
            $mform->setDefault('visible', 1);
            $mform->addRule('visible', null, 'required', null, 'client');
        }

        // add filename selection.
        $mform->addElement('filemanager', 'filename', 'Adjuntar archivo', null, 
            array('subdirs' => 0, 'maxfiles' => 1,'accepted_types' => '*'));
         
        
        // hidden elements
        $mform->addElement('hidden', 'blockid');
        $mform->addElement('hidden', 'courseid');
        $mform->addElement('hidden','id','0');
        $mform->addElement('hidden','idcomentario');
        $mform->addElement('hidden','idpadre','0');
        
        //$this->add_action_buttons();
        $this->add_action_buttons($cancel=true, $submitlabel='Enviar');
    }
}
