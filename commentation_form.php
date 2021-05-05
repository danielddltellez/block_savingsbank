<?php
require_once("{$CFG->libdir}/formslib.php");
require_once($CFG->dirroot.'/blocks/savingsbank/lib.php');
 
class savingsbank_form extends moodleform {
 
    function definition() {

    	global $DB;
 
        $mform =& $this->_form;
        $mform->addElement('header','displayinfo', 'Caja de ahorro');

        //Obtiene Categorías principales
		$sql="SELECT id, nombre FROM {block_savingsbank_categoria} WHERE visible=? and idpadre is null ORDER BY id ASC";
        $cats = $DB->get_records_sql($sql, array(1));
		
		$optionsmain = array();
		$suboptions = array();
		foreach($cats as $cat){
			//Llena arreglo con categorias princiaples
			$optionsmain[$cat->id]=$cat->nombre;

			//Obtiene subcategorias por cada categoría principal
			$subsql="SELECT id, nombre FROM {block_savingsbank_categoria} WHERE visible=? and idpadre=?  ORDER BY id ASC";
			$subcats = $DB->get_records_sql($subsql, array(1, $cat->id));

			foreach($subcats as $subcat){
				//Llena arreglo con subcategorias (Segundo nivel)
				$suboptions[$cat->id][$subcat->id]=$subcat->nombre;
			}
		}

        //$attribs = array('size' => '6');
        //$hier = &$mform->addElement('hierselect', 'idcategoria', 'Categoría', $attribs); //Lista
//        $hier = &$mform->addElement('hierselect', 'idcategoria', 'Categoría', array('style' => 'display: grid;')); //Select
        $hier = &$mform->addElement('hierselect', 'idcategoria', 'Categoría/Subcategoría', null,' ');//Select
        $hier->setOptions(array($optionsmain, $suboptions));
        $mform->addRule('idcategoria', null, 'required');

        // add page title element.
        /*
        $mform->addElement('text', 'asunto', 'Asunto');
        $mform->setType('asunto', PARAM_RAW);
        $mform->addRule('asunto', null, 'required', null, 'client');
             
        $mform->addElement('textarea', 'mensaje', 'Mensaje');
        $mform->setType('mensaje', PARAM_RAW);
        $mform->addRule('mensaje', null, 'required', null, 'client');
        */  
        //Estatus
        $sql="SELECT id, nombre FROM {block_savingsbank_estatus} WHERE visible=? and id=1  ORDER BY id ASC";
        $estatus = $DB->get_records_sql($sql, array(1));
        
        $options = array();
        foreach($estatus as $estatu){
            $options[$estatu->id]=$estatu->nombre;
            $mform->addElement('hidden','idestatus', $estatu->id);
        }

        // add filename selection.
        /*$mform->addElement('filemanager', 'filename', 'Adjuntar archivo', null, 
            array('subdirs' => 0, 'maxfiles' => 1,'accepted_types' => '*'));*/

        $mform->addElement('hidden','visible',1);
/*
        $select = $mform->addElement('select', 'idestatus', 'Estatus', $options);
        $select->setSelected('1');
        $mform->addRule('idestatus', null, 'required', null, 'client');
*/
/*
        $mform->addElement('selectyesno', 'visible', 'Publicar');
        $mform->setDefault('visible', 1);
        $mform->addRule('visible', null, 'required', null, 'client');
*/
        

        // add filename selection.
       /*
        $mform->addElement('filemanager', 'filename', 'Imagen de artículo', null, 
            array('subdirs' => 0, 'maxfiles' => 1,'accepted_types' => array('.png','.jpg')));
         */
            
        // hidden elements
        $mform->addElement('hidden', 'blockid');
        $mform->addElement('hidden', 'courseid');
        $mform->addElement('hidden', 'id','0');
        
        //$this->add_action_buttons();
        $this->add_action_buttons($cancel=true, $submitlabel='Continuar');
    }
}
