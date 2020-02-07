<?php
namespace App;
class AlgoliaSearch extends \SCHLIX\cmsApplication_Basic {
    /**
     * Constructor
     */
    public function __construct() {
        require(__DIR__ . '/vendor/autoload.php');

        parent::__construct("Algolia Search");
        $this->has_versioning = false;
        $this->disable_frontend_runtime = false;

        $this->initIndex();
    }

    /**
     * Initialize search index.
     */
    public function initIndex() {
        if ($this->isConfigured() && !$this->index) {
            $this->client = \Algolia\AlgoliaSearch\SearchClient::create(
                $this->config('str_application_id'),
                $this->config('str_api_key')
            );
            $this->index_name = $this->config('str_index_name');
            $this->index = $this->client->initIndex($this->index_name);
            if (!$this->index->exists()) {
                $this->setIndexSettings();
                $this->updateIndex();
            }
        }
    }

    /**
     * Set index settings for searchableAttributes and highlight.
     */
    private function setIndexSettings() {
        $this->index->setSettings([
            'searchableAttributes' => [
                'title',
                'virtual_filename,meta_key,tags,summary,description',
                'description_alternative_title,summary_secondary_headline,description_secondary_headline,meta_description'
            ],
            'attributesToHighlight' => [
                'title', 'summary', 'description'
            ]
        ]);
    }
    
    
    /**
     * Set config to default when $invalidCheck true.
     * @global \SCHLIX\cmsConfigRegistry $SystemConfig
     * @param string $name
     * @param mixed $default
     * @param string|function $invalidCheck
     */
    private function configDefault($name, $default, $invalidCheck) {
        global $SystemConfig;
        switch ($invalidCheck) {
            case 'presence':
                $result = !$this->_configs[$name];
                break;
            case 'numgt0':
                $result = (int) $this->_configs[$name] <= 0;
            case 'num':
                $result = $result || is_null($this->_configs[$name]) || !is_numeric($this->_configs[$name]);
                break;
            default:
                $result = $invalidCheck($this->_configs[$name]);
        }
        if ($result) {
            $this->_configs[$name] = $default;
            $SystemConfig->set($this->app_name, $name, $this->_configs[$name]);
        }
    }

    /**
     * Get config value.
     * @global \SCHLIX\cmsConfigRegistry $SystemConfig
     * @param string $name
     * @param boolean $use_cache
     * @return mixed
     */
    public function config($name, $use_cache = true) {
        global $SystemConfig;
        if(!$this->_configs || !$use_cache) {
            $SystemConfig->clearCache($this->app_name);
            $this->_configs = $SystemConfig->get($this->app_name);

            $this->configDefault('int_hits_per_page', 5, 'numgt0');
            $this->configDefault('int_value_max_length', 2000, 'numgt0');
            $this->configDefault('str_index_name', 'schlixcms', 'presence');
            $this->configDefault('array_enabled_apps', ['html', 'blog'], function($v){
                return ___c($v) == 0 || !is_array($v) || count(array_diff($v, $this->supportedApplications())) > 0;
            });
        }

        return ($name) ? $this->_configs[$name] : $this->_configs;
    }

    /**
     * Return true when application is configured and ready to use
     * @return boolean
     */
    public function isConfigured() {
        return !empty($this->config('str_application_id')) && !empty($this->config('str_search_only_key'));
    }

    /**
     * Convert value according to type so it's acceptable for indexing.
     * @param mixed $value
     * @return mixed [converted $value]
     */
    private function getValueForIndexing($value) {
        switch (gettype($value)) {
            case 'string':
                // TODO: filter out macro keywords when possible
                $value = strip_tags($value);
                if((int) $this->config('int_value_max_length') > 0) {
                    return mb_strimwidth($value, 0, $this->config('int_value_max_length'), '..', 'utf-8');
                }
            default:
                return $value;
        }
    }

    /**
     * Return array of indexed atttributes.
     * @return array
     */
    private function getIndexedAttributes() {
        return [
            'id', 'virtual_filename', 'title', 'summary', 'description_alternative_title',
            'summary_secondary_headline', 'description', 'description_secondary_headline',
            'meta_key', 'meta_description', 'tags', 'url_media_file'
        ];
    }

    /**
     * Get indexed attributes for specified items.
     * @param array $item
     * @return array
     */
    private function getItemAttributes($item) {
        $attributes = [];
        foreach ($item as $key => $value) {
            if(in_array($key, $this->getIndexedAttributes())) {
                $attributes[$key] = $this->getValueForIndexing($value);
            }
        }
        return $attributes;
    }

    /**
     * Get all app items which available for indexing.
     * @param object $app
     * @param int $timestamp
     * @return array
     */
    private function getItemsForApp($app, $timestamp) {
        if (method_exists($app, 'getAllItems')) {
            $current_time = date('Y-m-d H:i:s', $timestamp);
            $current_time_str = sanitize_string($current_time);
            $invalid_date_str = sanitize_string(NULL_DATE);
            $sql_criteria_arr = [];
            if ($app->itemColumnExists('status')) {
                $sql_criteria_arr[] = "status > 0";
            }
            if ($app->itemColumnExists('date_available')) {
                $sql_criteria_arr[] = "(date_available IS NULL OR date_available < {$current_time_str})";
            }
            if ($app->itemColumnExists('date_expiry')) {
                $sql_criteria_arr[] = "((date_expiry IS NULL OR date_expiry = {$invalid_date_str}) OR date_expiry >= {$current_time_str})";
            }
            if ($app->itemColumnExists('permission_read')) {
                $public_permission = sanitize_string('s:8:"everyone";');
                $sql_criteria_arr[] = "(permission_read IS NULL OR permission_read = {$public_permission})";
            }
            $sql_criteria = implode(' AND ', $sql_criteria_arr);
            return $app->getAllItems('*', $sql_criteria, 0, 0, 'id', 'ASC');
        }
        return [];
    }

    /**
     * Save records to Algolia, default split per 1000 records.
     * @global \SCHLIX\cmsLogger $SystemLog
     * @param array $records
     * @param int $batch_num
     * @return int [number of records]
     */
    private function addRecords($records, $batch_num = 1000) {
        global $SystemLog;

        $count = 0;
        foreach (array_chunk($records, $batch_num) as $batch) {
            try {
                $this->index->saveObjects($batch);
                $count += ___c($batch);
            } catch (\Algolia\AlgoliaSearch\Exceptions\BadRequestException $e) {
                $SystemLog->error($e->getMessage(), $this->app_name);
            }
        }
        return $count;
    }

    /**
     * Get items for app then add them to Algolia.
     * @param string $app_name
     * @param int $timestamp
     * @return int [number of records]
     */
    private function addRecordsForApp($app_name, $timestamp) {
        $app_class_name = '\\App\\' . $app_name;
        $app = new $app_class_name;
        $items = $this->getItemsForApp($app, $timestamp);
        $records = [];

        foreach ($items as $item) {
            $attributes = array_merge([
                'link' => $app->createFriendlyURL("action=viewitem&id={$item['id']}"),
                'objectID' => $app_name . '-' . $item['id'],
                'index_unix_timestamp' => $timestamp
            ], $this->getItemAttributes($item));
            if($attributes['url_media_file']) {
                if (method_exists($app, 'getGalleryImage')) {
                    $attributes['url_media_file'] = $app->getGalleryImage('image_small', $item['url_media_file'], '');
                } elseif (method_exists($app, 'getBlogImage')) {
                    $attributes['url_media_file'] = $app->getBlogImage('image_small', $item['url_media_file']);
                }
            }
            $records[] = $attributes;
        }
        $count = $this->addRecords($records);

        return $count;
    }

    /**
     * Delete old records.
     * @global \SCHLIX\cmsLogger $SystemLog
     * @param int $timestamp
     */
    private function deleteOldRecords($timestamp) {
        global $SystemLog;
        $params = [
            'filters' => 'index_unix_timestamp < ' . ($timestamp - 1)
        ];
        try {
            $this->index->deleteBy($params);
        } catch (\Algolia\AlgoliaSearch\Exceptions\BadRequestException $e) {
            $SystemLog->error($e->getMessage(), $this->app_name);
        }
    }

    /**
     * Update index from all records.
     * @global \SCHLIX\cmsLogger $SystemLog
     * @return array ['success' => [bool], 'message' => [string]]
     */
    public function updateIndex() {
        if (!$this->isConfigured())
            return;

        global $SystemLog;
        
        $apps = $this->config('array_enabled_apps');

        $timestamp = time();
        $count = 0;
        foreach ($apps as $app_name) {
            $count += $this->addRecordsForApp($app_name, $timestamp);
        }
        $this->deleteOldRecords($timestamp);

        $message = "[$this->index_name] $count items indexed.";
        $SystemLog->info($message, $this->app_name);
        return ['success' => true, 'message' => $message];
    }

    /**
     * Update index from cronscheduler
     */
    public function processRunUpdateIndex() {
        $Algoliasearch = new \App\AlgoliaSearch();
        echo "Updating index ".$Algoliasearch->index_name."..";
        if ($Algoliasearch->isConfigured()) {
            $result = $Algoliasearch->updateIndex();
            echo "Done: ".$result['message'];
        } else {
            echo "App not configured, exiting..";
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
        $this->loadTemplateFile('view.main', [
            'application_id'  => $this->config('str_application_id'),
            'search_only_key' => $this->config('str_search_only_key'),
            'hits_per_page'   => $this->config('int_hits_per_page'),
            'index_name'      => $this->index_name
        ]);
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