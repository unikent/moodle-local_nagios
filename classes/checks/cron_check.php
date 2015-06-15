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
 * @copyright  2015 University of Kent
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_nagios\checks;

/**
 * Check cron is running okay.
 */
class cron_check extends \local_nagios\base_check
{
    public function execute() {
        global $DB;

        $failedtasks = $DB->count_records_select('task_scheduled', 'faildelay > 0 AND disabled = 0');
        if ($failedtasks > 0) {
            $this->error("{$failedtasks} scheduled tasks failing.");
        }

        $failedtasks = $DB->count_records_select('task_adhoc', 'faildelay > 0');
        if ($failedtasks > 0) {
            $this->error("{$failedtasks} adhoc tasks failing.");
        }

        $tasks = $DB->count_records_select('task_adhoc');
        $this->set_perf_var('adhoc_tasks', $tasks);
        $this->set_perf_var('failed_adhoc_tasks', $failedtasks);
    }
}
