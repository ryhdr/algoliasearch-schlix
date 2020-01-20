<?php
/**
 * Algolia Search - Main page view template. Lists both categories and items with parent_id = 0 and category_id = 0 
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
if (!defined('SCHLIX_VERSION')) die('No Access');
$index_name = ___h($app_search->getIndexName());
$value = ___h(urldecode(\SCHLIX\cmsHttpInputFilter::string_noquotes_notags($_GET[$index_name], 'query', 255)));
?>
<div class="block-algoliasearch nice-search" id="<?= ___h($this->block_name) ?>">
    <form action="<?= $app_search->createFriendlyURL('') ?>" method="get">
        <x-ui:input-group>
            <x-ui:textbox placeholder="<?= ___('Search'); ?>" name="<?= $index_name ?>[query]" id="<?= $this->block_name.'_query' ?>" value="<?= $value ?>" />
            <x-ui:input-addon-button>
                <x-ui:button-info type="button"><i class="fa fa-search"></i></x-ui:button-info>
            </x-ui:input-addon-button>
        </x-ui:input-group>
    </form>
</div>
