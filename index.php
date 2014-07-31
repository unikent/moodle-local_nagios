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


require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('nagiosmanager');

$renderer = $PAGE->get_renderer('local_nagios');
$action = optional_param('action', '', PARAM_ALPHA);
$check = optional_param('check', '', PARAM_INT);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'local_nagios'));

if ($action == 'toggle' && !empty($check)) {
    require_sesskey();

    $current = $DB->get_field('nrpe_checks', 'enabled', array(
        'id' => $check
    )) == 1;

    $DB->set_field('nrpe_checks', 'enabled', !$current, array(
        'id' => $check
    ));

    echo $OUTPUT->notification(get_string('success'), 'notifysuccess');
    echo \html_writer::empty_tag('br');
}

echo $renderer->nagios_checks_table();

echo $OUTPUT->footer();