moodle-local_nagios
===================

Nagios plugin for Moodle NRPE checks.

This plugin aggregates NRPE checks from other plugins and allows administrators to configure individual checks through a web interface.

Example plugin:
```
class cache_check extends \local_nagios\base_check
{
    public function execute() {
        $instance = \cache_config::instance();
        $stores = $instance->get_all_stores();
        foreach ($stores as $name => $details) {
            $class = $details['class'];
            $store = new $class($details['name'], $details['configuration']);
            if (!$store->is_ready()) {
                $this->error("Could not communicate with Cache '{$name}'!");
            }
        }
    }
}
```
