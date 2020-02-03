<?php
namespace Block;
class AlgoliaSearch extends \SCHLIX\cmsBlock
{
	public function Run()
	{
                $app_search = new \App\AlgoliaSearch();
                $this->loadTemplateFile('view.block',compact(array_keys(get_defined_vars())));
  	}
}
