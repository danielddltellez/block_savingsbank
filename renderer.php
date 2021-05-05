<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Block XP renderer.
 *
 * @package    block_xp
 * @copyright  2014 Frédéric Massart
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Block XP renderer class.
 *
 * @package    block_xp
 * @copyright  2014 Frédéric Massart
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 class block_savingsbank_renderer extends plugin_renderer_base {
	 /**
     * Outputs the navigation.
     *
     * @param block_monedas_manager $manager The manager.
     * @param string $page The page we are on.
     * @return string The navigation.
     */
    public function navigation($page, $blockid, $courseid, $id, $viewpage) {
        $tabs = array();
		
		$tabs[] = new tabobject(
            'reports',
            new moodle_url('/blocks/savingsbank/reports.php', array('blockid'=>$blockid, 'courseid' => $courseid, 'id'=>$id, 'viewpage'=>$viewpage)),
            'Reportes'
        );
        /*
		$tabs[] = new tabobject(
            'userstotal',
            new moodle_url('/blocks/monedas/userstotal.php', array('blockid'=>$blockid, 'courseid' => $courseid, 'id'=>$id, 'viewpage'=>$viewpage)),
            'Puntos individuales'
        );
        $tabs[] = new tabobject(
            'teamtotal',
            new moodle_url('/blocks/monedas/teamtotal.php', array('blockid'=>$blockid, 'courseid' => $courseid, 'id'=>$id, 'viewpage'=>$viewpage)),
            'Puntos en equipo'
        );
        */
		$tabs[] = new tabobject(
            'integrantes',
            new moodle_url('/blocks/savingsbank/integrantes.php', array('blockid'=>$blockid, 'courseid' => $courseid, 'id'=>'0', 'viewpage'=>$viewpage)),
            'Administradores'
        );	

        return $this->tabtree($tabs, $page);
    }
 }