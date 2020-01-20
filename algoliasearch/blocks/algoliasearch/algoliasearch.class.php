<?php
namespace Block;
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
class AlgoliaSearch extends \SCHLIX\cmsBlock
{
	public function Run()
	{
                $app_search = new \App\AlgoliaSearch();
                $this->loadTemplateFile('view.block',compact(array_keys(get_defined_vars())));
  	}
}
