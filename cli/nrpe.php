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
 * This is the script Nagios runs.
 *
 * @package    local_nagios
 * @author     Skylar Kelty <S.Kelty@kent.ac.uk>
 * @copyright  2015 University of Kent
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);
define('NRPE_SCRIPT', true);

require(dirname(__FILE__) . '/../../../config.php');

$exitstatus = 0;
$messages = array();
$perfdata = array();

// Go through, and execute, all known NRPE checks.
$tasks = $DB->get_records('nrpe_checks', array(
    'enabled' => 1
));
foreach ($tasks as $task) {
    $class = $task->classname;

    if (!class_exists($class)) {
        continue;
    }

    $obj = new $class();
    $obj->execute();
    $status = $obj->get_status();

    if ($status == 1 || ($status == 2 && $exitstatus == 0)) {
        $exitstatus = $status;
    }

    $messages = array_merge($messages, $obj->get_messages());

    $objperf = $obj->get_perfdata();
    foreach ($objperf as $name => $var) {
        $perfdata[] = "{$name}={$var}";
    }
}

if (empty($messages)) {
    switch ($exitstatus) {
        case 1:
            $messages[] = 'UNK_ERR';
        break;

        case 2:
            $messages[] = 'UNK_WARN';
        break;

        case 0:
        default:
            $messages[] = 'OK';
        break;
    }
}

if ($exitstatus > 0) {
    echo 'NOK ';
}

$perfstr = '';
if (!empty($perfdata)) {
    $perfstr = ' | ';
    $perfstr .= implode(',', $perfdata);
}

echo implode(', ', $messages) . $perfstr;

exit($exitstatus);