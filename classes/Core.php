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
class Core
{
    /**
     * Regenerate the list of NRPE checks.
     */
    public static function regenerate_list() {
        global $DB;

        $old = $DB->get_records('nrpe_checks');
        $new = self::get_all_checks();

        // Delete the old entries.
        foreach ($old as $r) {
            // Does this check still exist?
            if (isset($new[$r->component]) && in_array($r->classname, $new[$r->component])) {
                // Delete from $new.
                $index = array_search($r->classname, $new[$r->component]);
                unset($new[$r->component][$index]);
                continue;
            }

            $DB->delete_records('nrpe_checks', array(
                'id' => $r->id
            ));
        }

        // Create the new entries.
        $records = array();
        foreach ($new as $component => $checks) {
            foreach ($checks as $classname) {
                $records[] = array(
                    'component' => $component,
                    'classname' => $classname,
                    'enabled' => 1
                );
            }
        }

        $DB->insert_records('nrpe_checks', $records);
    }

    /**
     * Generate a list of all checks in the system.
     */
    private static function get_all_checks() {
        $checks = array();

        // Go through every plugin and see if we have a db/nagios.php file.
        $types = \core_component::get_plugin_types();
        foreach ($types as $type => $fulltype) {
            $plugs = \core_component::get_plugin_list($type);

            foreach ($plugs as $plug => $fullplug) {
                $component = clean_param($type.'_'.$plug, PARAM_COMPONENT);

                if (!is_readable($fullplug . '/db/nagios.php')) {
                    continue;
                }

                $nagios = array();
                include($fullplug . '/db/nagios.php');

                if (!empty($nagios)) {
                    $checks[$component] = array();

                    foreach ($nagios as $check) {
                        if (isset($check['classname'])) {
                            $classname = $check['classname'];
                            if (strpos($classname, '/') !== 0) {
                                $classname = '\\' . $classname;
                            }

                            $checks[$component][] = $classname;
                        }
                    }
                }
            }
        }

        return $checks;
    }
}