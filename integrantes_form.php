<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once("{$CFG->libdir}/formslib.php");
require_once($CFG->dirroot.'/blocks/savingsbank/lib.php');

class integrantes_form extends moodleform {
    
    function definition() {
		global $DB;

        $selecteds='';
        $members='';

        $i=0;

        $sql="SELECT distinct(cor.idusuario) idusuario, u.firstname, u.lastname FROM {user} as u, {block_savingsbank_responsa} as cor WHERE u.id=cor.idusuario and cor.estatus=1 ORDER BY u.firstname ASC;";
        $userselected = $DB->get_records_sql($sql);
        $options2 = array();
        if(count($userselected)>0){
            foreach($userselected as $user){
                $options2[$user->idusuario]=$user->firstname.' '.$user->lastname;
                $i++;
                if ($i==1) {
                    $selecteds=$user->idusuario;
                }else{
                    $selecteds=$selecteds.",".$user->idusuario;
                }
            }
        }

        if ($selecteds!="") {
            
            $sql="SELECT u.id, u.firstname, u.lastname, u.deleted, u.suspended FROM {user} as u WHERE u.id NOT IN($selecteds) and u.deleted=0 and u.suspended=0 ORDER BY firstname ASC;";
            $users = $DB->get_records_sql($sql);
        
            $options = array();

            foreach($users as $user){
                $options[$user->id]=$user->firstname.' '.$user->lastname;
            }
        }else{
            $sql="SELECT u.id, u.firstname, u.lastname FROM {user} as u WHERE u.id>0 and u.deleted=0 and u.suspended=0 ORDER BY firstname ASC;";
            $users = $DB->get_records_sql($sql);
        
            $options = array();
            foreach($users as $user){
                $options[$user->id]=$user->firstname.' '.$user->lastname;
            }
        }

        $mform =& $this->_form;
        
        $mform->addElement('header','displayinfo', 'Integrantes del equipo', null, false);

//////////////
        $objs = array();
        $objs[0] =& $mform->createElement('select', 'susers', get_string('selected', 'bulkusers'), $options2, 'size="15" style="width: 30%;"');
        $objs[0]->setMultiple(true);
        $objs[1] =& $mform->createElement('select', 'ausers', get_string('available', 'bulkusers'), $options, 'size="15" style="width: 30%;"');
        $objs[1]->setMultiple(true);

        $grp =& $mform->addElement('group', 'usersgrp', get_string('users', 'bulkusers'), $objs, array(' ', '<br />'), false);
        $mform->addHelpButton('buttonsgrp', 'selectedlist', 'bulkusers');

        $renderer =& $mform->defaultRenderer();
        $template = '<label class="qflabel" style="vertical-align:top">{label}</label> {element}';
        $renderer->setGroupElementTemplate($template, 'usersgrp');

        $objs = array();
        $objs[] =& $mform->createElement('submit', 'removesel', get_string('removesel', 'bulkusers'));
        $objs[] =& $mform->createElement('submit', 'addsel', get_string('addsel', 'bulkusers'));
        $grp =& $mform->addElement('group', 'buttonsgrp', get_string('selectedlist', 'bulkusers'), $objs, null, false);

        $mform->addElement('hidden', 'blockid');
        $mform->addElement('hidden', 'courseid');
        $mform->addElement('hidden', 'viewpage');
        $mform->addElement('hidden', 'id');

    }
}
