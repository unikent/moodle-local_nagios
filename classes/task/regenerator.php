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

namespace local_connect\task;

/**
 * Regenerate Nagios's check list.
 */
class regenerator extends \core\task\adhoc_task
{
    public function get_component() {
        return 'local_nagios';
    }

    public function execute() {
        $data = (array)$this->get_custom_data();

        // Sync Enrollments.
        $enrolments = \local_connect\enrolment::get_by("userid", $data['userid'], true);
        foreach ($enrolments as $enrolment) {
            $enrolment->create_in_moodle();
        }

        // Sync Group Enrollments.
        $enrolments = \local_connect\group_enrolment::get_by("userid", $data['userid'], true);
        foreach ($enrolments as $enrolment) {
            $enrolment->create_in_moodle();
        }
    }

    /**
     * Setter for $customdata.
     * @param mixed $customdata (anything that can be handled by json_encode)
     */
    public function set_custom_data($customdata) {
        if (empty($customdata['userid'])) {
            throw new \moodle_exception("User ID cannot be empty!");
        }

        return parent::set_custom_data($customdata);
    }
}
