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
 * Output rendering for the plugin.
 *
 * @package    local_nagios
 * @author     Skylar Kelty <S.Kelty@kent.ac.uk>
 * @copyright  2014 University of Kent
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Implements the plugin renderer
 *
 * @copyright  2014 University of Kent
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_nagios_renderer extends plugin_renderer_base {
    /**
     * This function will render a table with all the nagios tasks.
     *
     * @return string HTML to output.
     */
    public function nagios_checks_table() {
        global $CFG, $DB;

        $checks = $DB->get_records('nrpe_checks');

        if (empty($checks)) {
            return \html_writer::tag('p', get_string('nocheck', 'local_nagios'));
        }

        $table = new html_table();
        $table->head  = array(
            get_string('id', 'local_nagios'),
            get_string('component', 'tool_task'),
            get_string('toggle', 'local_nagios'),
            get_string('name'),
            get_string('enabled', 'local_nagios')
        );
        $table->attributes['class'] = 'admintable generaltable';

        $data = array();
        foreach ($checks as $check) {
            $configureurl = new moodle_url('/local/nagios/index.php', array(
                'action' => 'toggle',
                'check' => $check->id,
                'sesskey' => sesskey()
            ));

            $editlink = $this->action_icon($configureurl, new pix_icon('t/edit', get_string('toggletask', 'local_nagios')));

            $idcell = new html_table_cell($check->id);
            $idcell->header = true;

            $component = $check->component;
            list($type, $plugin) = core_component::normalize_component($component);
            if ($type === 'core') {
                $componentcell = new html_table_cell(get_string('corecomponent', 'tool_task'));
            } else {
                if ($plugininfo = core_plugin_manager::instance()->get_plugin_info($component)) {
                    $plugininfo->init_display_name();
                    $componentcell = new html_table_cell($plugininfo->displayname);
                } else {
                    $componentcell = new html_table_cell($component);
                }
            }

            $row = new html_table_row(array(
                $idcell,
                $componentcell,
                new html_table_cell($editlink),
                new html_table_cell($check->classname),
                new html_table_cell($check->enabled)
            ));

            $data[] = $row;
        }

        $table->data = $data;
        return html_writer::table($table);
    }
}
