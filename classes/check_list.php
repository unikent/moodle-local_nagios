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
 * NRPE check aggregator.
 *
 * @package    local_nagios
 * @author     Skylar Kelty <S.Kelty@kent.ac.uk>
 * @copyright  2014 University of Kent
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_nagios;

defined('MOODLE_INTERNAL') || die();

/**
 * The list of NRPE Checks.
 */
class check_list
{
    /**
     * Check our list, see if we need to make updates.
     */
    public static function check_valid() {
        global $DB;

        $versions = get_config('local_nagios', 'version_info');
        if (!$versions) {
            self::schedule_regen();
        }

        $versions = json_decode($versions, true);

        // Check through plugins.
        $plugins = $DB->get_records('config_plugins', array(
            'name' => 'version'
        ));

        foreach ($plugins as $plugin) {
            if (!isset($versions[$plugin->plugin]) || $versions[$plugin->plugin] != $plugin->value) {
                self::schedule_regen();
                return;
            }

            unset($versions[$plugin->plugin]);
        }

        // Has anything been uninstalled?
        if (count($versions) > 0) {
            self::schedule_regen();
        }
    }

    /**
     * Schedule a list regeneration.
     */
    private static function schedule_regen() {
        global $DB;

        // Do we already have a task scheduled?
        if ($DB->record_exists('task_adhoc', array('component' => 'local_nagios'))) {
            return;
        }

        $task = new \local_nagios\task\regenerator();
        \core\task\manager::queue_adhoc_task($task);
    }
}