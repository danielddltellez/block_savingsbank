<?php
    class block_savingsbank extends block_base {
        function init() {
            $this->title = get_string('comments', 'block_savingsbank');
        }

        function get_content() {
            global $COURSE, $DB, $USER;
            if ($this->content !== NULL) {
                    return $this->content;
                }
    
                $this->content = new stdClass;
//                $this->content->text = $this->config->text;
//                $this->content->footer = 'Pie de página aquí...';
                //$this->content->footer = $this->config->footer;

                //Es administrador de reportes
                $sql="SELECT cor.id FROM {block_savingsbank_responsa} as cor WHERE cor.idusuario=? and cor.estatus=1";
                $resp=$DB->get_records_sql($sql,array($USER->id));
                //print_r($resp);
                if (count($resp)>0) {
                    $url = new moodle_url('/blocks/savingsbank/view.php', array('blockid' => $this->instance->id, 'courseid' => $COURSE->id, 'id' => 0, 'viewpage' => 1));
                    $this->content->text .= html_writer::start_tag('li');
                    $this->content->text .= html_writer::link($url, 'Seguimiento a solicitudes');
                    $this->content->text .= html_writer::end_tag('li');
                    //Repórtes
                    $urlreport = new moodle_url('/blocks/savingsbank/reports.php', array('blockid' => $this->instance->id, 'courseid' => $COURSE->id, 'id' => '0', 'viewpage' => '1'));
                    $this->content->text .= html_writer::start_tag('li');
                    $this->content->text .= html_writer::link($urlreport, 'Administración');
                    $this->content->text .= html_writer::end_tag('li');
                }else{
                    $url = new moodle_url('/blocks/savingsbank/view.php', array('blockid' => $this->instance->id, 'courseid' => $COURSE->id, 'id' => 0, 'viewpage' => 1));
                    $this->content->text .= html_writer::start_tag('li');
                    $this->content->text .= html_writer::link($url, get_string('addpage', 'block_savingsbank'));
                    $this->content->text .= html_writer::end_tag('li');
                    $this->content->text .= html_writer::start_tag('br');
                    $this->content->text .= html_writer::end_tag('br');


                    //Crea nueva queja
                    /*
                    $urladmin = new moodle_url('/blocks/savingsbank/view.php', array('blockid' => $this->instance->id, 'courseid' => $COURSE->id));
                    $this->content->text .= html_writer::start_tag('li');
                    $this->content->text .= html_writer::link($urladmin, 'Nueva solicitud');
                    $this->content->text .= html_writer::end_tag('li');
                    */
                }
                /*
                    $urlpoliticas = new moodle_url('/blocks/savingsbank/docs/LineamientosBQ.pdf');
                    $this->content->text .= html_writer::start_tag('li');
                    $this->content->text .= html_writer::link($urlpoliticas, 'Lineamientos', array('target' => '_blank'));
                    $this->content->text .= html_writer::end_tag('li');
                */
    
                return $this->content;
        }


        public function specialization() {
            if (isset($this->config)) {
                if (empty($this->config->title)) {
                    $this->title = get_string('defaulttitle', 'block_savingsbank');            
                } else {
                    $this->title = $this->config->title;
                }
 
                if (empty($this->config->text)) {
                    $this->config->text = get_string('defaulttext', 'block_savingsbank');
                }    
            }
        }


        function instance_allow_config() {
            return true;
        }
    }
