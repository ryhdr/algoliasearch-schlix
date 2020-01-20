<?php
namespace App;
/**
 * Algolia Search - Main Class
 * 
 * 
 * 
 * @copyright 2019 Roy Hadrianoro
 *
 * @license MIT
 *
 * @package algoliasearch
 * @version 1.0
 * @author  Roy Hadrianoro <roy.hadrianoro@schlix.com>
 * @link    https://www.schlix.com
 */
class AlgoliaSearch extends \SCHLIX\cmsApplication_Basic {
    /**
     * Constructor
     */
    public function __construct() {
        require(__DIR__ . '/vendor/autoload.php');
        global $SystemConfig;

        parent::__construct("Algolia Search");
        $this->has_versioning = false;
        $this->disable_frontend_runtime = false;

        $this->application_id = $SystemConfig->get($this->app_name, 'str_application_id');
        $api_key = $SystemConfig->get($this->app_name, 'str_api_key');
        $this->search_only_key = $SystemConfig->get($this->app_name, 'str_search_only_key');
        if ($this->isConfigured()) {
            $this->client = \Algolia\AlgoliaSearch\SearchClient::create(
                $this->application_id,
                $api_key
            );
        }
        $this->hits_per_page = (int) $SystemConfig->get($this->app_name, 'int_hits_per_page');
        if(!is_int($this->hits_per_page) || $this->hits_per_page <= 0) {
            $this->hits_per_page = 5;
            $SystemConfig->set($this->app_name, 'int_hits_per_page', $this->hits_per_page);
        }
        $this->value_max_length = (int) $SystemConfig->get($this->app_name, 'int_value_max_length');
        if(!is_int($this->value_max_length) || $this->value_max_length <= 0) {
            $this->value_max_length = 5000;
            $SystemConfig->set($this->app_name, 'int_value_max_length', $this->value_max_length);
        }
    }

    /**
     * Return true when application is configured and ready to use
     * @return boolean
     */
    public function isConfigured() {
        return !empty($this->application_id) && !empty($this->search_only_key);
    }

    /**
     * Get current index name
     * @return string
     * @global \SCHLIX\cmsConfigRegistry $SystemConfig
     */
    public function getIndexName() {
        global $SystemConfig;
        return $SystemConfig->get($this->app_name, 'str_index_name');
    }

    /**
     * Update index from all records.
     * Caveat:
     *  Because it's not possible to find deleted records,
     *  this method will actually initialize a new index with same settings.
     * @global \SCHLIX\cmsConfigRegistry $SystemConfig
     * @global \SCHLIX\cmsLogger $SystemLog
     */
    public function updateIndex() {
        if (!$this->isConfigured())
            return;

        global $SystemConfig;
        global $SystemLog;
        
        $apps = $this->supportedApplications();
        $default_app_array = array('html', 'blog');
        $enabled_apps_array = $SystemConfig->get($this->app_name, 'array_enabled_apps');
        if (___c($enabled_apps_array) == 0 || !is_array($enabled_apps_array))
            $enabled_apps_array = $default_app_array;

        $existing_index_name = $this->getIndexName();
        $index_name = 'schlixcms-' . time();
        if ($index_name == $existing_index_name) // probably never happens...
            $index_name .= '0';
        $index = $this->client->initIndex($index_name);
        if ($existing_index_name) {
            $this->client->copySettings(
              $existing_index_name,
              $index_name
            );
        }

        $current_time = sanitize_string(get_current_datetime());
        $invalid_date = sanitize_string(NULL_DATE);
        $count = 0;
        if ($apps) {
            foreach ($apps as $appname) {
                if (in_array($appname, $enabled_apps_array)) {
                    $temp_app_name = '\\App\\' . $appname;
                    $temp_app = new $temp_app_name;
                    if (method_exists($temp_app, 'getAllItems')) {
                        $batch = [];
                        $sql_criteria = [];
                        if ($temp_app->itemColumnExists('status')) {
                            $sql_criteria[] = "status > 0";
                        }
                        if ($temp_app->itemColumnExists('date_available')) {
                            $sql_criteria[] = "(date_available IS NULL OR date_available < {$current_time})";
                        }
                        if ($temp_app->itemColumnExists('date_expiry')) {
                            $sql_criteria[] = "((date_expiry IS NULL OR date_expiry = {$invalid_date}) OR date_expiry >= {$current_time})";
                        }
                        $all_items = $temp_app->getAllItems('*', join(' AND ', $sql_criteria), 0, 0, 'id', 'ASC');
                        foreach ($all_items as $the_item) {
                            $indexed_attributes = [
                                'id', 'virtual_filename', 'title', 'summary', 'description_alternative_title', 
                                'summary_secondary_headline', 'description', 'description_secondary_headline', 
                                'date_created', 'date_modified', 'date_available',
                                'meta_key', 'meta_description', 'tags'
                            ];
                            foreach ($the_item as $key => $value) {
                                if(in_array($key, $indexed_attributes)) {
                                    // TODO: filter out macro keywords when possible
                                    $attributes[$key] = mb_strimwidth(strip_tags($value), 0, $this->value_max_length, '..', 'utf-8');
                                }
                            }
                            $attributes['link'] = $temp_app->createFriendlyURL("action=viewitem&id={$the_item['id']}");
                            $attributes['objectID'] = $appname . '-' . $attributes['id'];
                            $batch[] = $attributes;
                        }
                        try {
                            $index->saveObjects($batch);
                            $count += ___c($batch);
                        } catch (\Algolia\AlgoliaSearch\Exceptions\BadRequestException $e) {
                            $SystemLog->error($e->getMessage(), $this->app_name);
                        }
                    }
                }
            }
        }
        $old_index_name = $SystemConfig->get($this->app_name, 'str_old_index_name');
        $SystemConfig->set($this->app_name, 'str_old_index_name', $existing_index_name);
        $SystemConfig->set($this->app_name, 'str_index_name', $index_name);
        if ($old_index_name) {
            $old_index = $this->client->initIndex($old_index_name);
            $old_index->delete();
            $SystemLog->info("Index '$old_index_name' deleted. ", $this->app_name);
        }
        $SystemLog->info("Index '$index_name' added $count records. ", $this->app_name);
    }

    /**
     * Update index from cronscheduler
     */
    public function processRunUpdateIndex() {
        $Algoliasearch = new \App\AlgoliaSearch();
        if ($Algoliasearch->isConfigured()) {
            echo "Algoliasearch: updating index".$Algoliasearch->getIndexName();
            $Algoliasearch->updateIndex();
            echo "Algoliasearch: finished updating index".$Algoliasearch->getIndexName();
        } else {
            echo "Algoliasearch: app not configured, exiting..";
        }
    }

    /**
     * Return array of supported applications
     * @return array
     */
    public function supportedApplications() {
        return ['html', 'blog', 'gallery'];
    }
        
    /**
     * View Main Page
     */
    public function viewMainPage() {
        $this->loadTemplateFile('view.main', null);
    }
            
    //_______________________________________________________________________________________________________________//
    public function Run($command) {
        switch ($command['action']) {
            default: return parent::Run($command);
        }
        return true;
    }

}

?>