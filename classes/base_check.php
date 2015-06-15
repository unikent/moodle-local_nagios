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

namespace local_nagios;

defined('MOODLE_INTERNAL') || die();

/**
 * A base nagios check.
 */
abstract class base_check
{
    /** Our current status */
    protected $status;

    /** Our current set of messages */
    protected $messages = array();

    /** Our set of performance data */
    protected $perfdata = array();

    /**
     * Execute the check.
     */
    public abstract function execute();

    /**
     * Set an error.
     */
    public function error($message) {
        $this->status = 1;
        $this->messages[] = $message;
    }

    /**
     * Set a warning.
     */
    public function warning($message) {
        $this->status = $this->status === 1 ? 1 : 2;
        $this->messages[] = $message;
    }

    /**
     * Get the status code.
     */
    public function get_status() {
        return $this->status;
    }

    /**
     * Get our messages.
     */
    public function get_messages() {
        return $this->messages;
    }

    /**
     * Set a performance variable.
     */
    public function set_perf_var($name, $var) {
        $this->perfdata[$name] = $var;
    }

    /**
     * Get a performance variable.
     */
    public function get_perf_var($name) {
        if (isset($this->perfdata[$name])) {
            return $this->perfdata[$name];
        }

        return null;
    }

    /**
     * Return all perf vars.
     */
    public function get_perfdata() {
        return $this->perfdata;
    }
}