<?php
namespace App;

class AlgoliaSearch_Admin extends \SCHLIX\cmsAdmin_Basic {

    public function __construct() {
        // Data: Item
        $methods = array('standard_main_app' => 'Main Page',);

        parent::__construct('basic', $methods);      
    }
    
    private function isConfigsChanged($config_names, $original, $updated) {
        foreach($config_names as $name) {
            if ($original[$name] != $updated[$name]) {
                return true;
            }
        }
        return false;
    }

    public function getSaveConfigValidationErrorList($datavalues) {
        global $SystemConfig;
        $app_name = $this->app->getFullApplicationName();
        $original = $SystemConfig->get($app_name);
        $this->update_index_after_save_config = $this->isConfigsChanged([
            'str_application_id',
            'str_index_name',
            'array_enabled_apps',
            'int_value_max_length'
        ], $original, $datavalues);
        return [];
    }

    public function forceRefreshMenuLinks() {
        $this->app->config(NULL, false); // refresh configs cache
        if ($this->update_index_after_save_config) {
            $this->app->initIndex();
            $this->app->updateIndex();
        }
    }

    public function Run() {
        switch (fget_alphanumeric('action')) {
            case 'updateindex' :
                $this->app->updateIndex();
                $this->returnToMainAdminApplication();
                break;
            default: return parent::Run();
        }
        return true;
    }

}
