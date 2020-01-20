<?php
namespace App;

/**
 * Algolia Search - Admin class
 * 
 * 
 *
 * @copyright 2019 Roy Hadrianoro
 *
 * @license MIT
 *
 * @version 1.0
 * @package algoliasearch
 * @author  Roy Hadrianoro <roy.hadrianoro@schlix.com>
 * @link    https://www.schlix.com
 */
class AlgoliaSearch_Admin extends \SCHLIX\cmsAdmin_Basic {

    public function __construct() {
        // Data: Item
        $methods = array('standard_main_app' => 'Main Page',);

        parent::__construct('basic', $methods);      
    }

    public function supportedApplications() {
        return $this->app->supportedApplications();
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
