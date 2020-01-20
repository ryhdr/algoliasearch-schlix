<?php
namespace Macro;
/**
 * Algolia Search - macro class
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

class AlgoliaSearch extends \SCHLIX\cmsMacro {
    protected static $has_this_macro_been_called;

    private function processText($text)
    {
        global $Blocks;

        $regex = '/{algoliasearch}/i';

        preg_match_all($regex, $text, $matches);
        $references = $matches[0];
        $count = ___c($references);
        if ($count > 0) {
            for ($i = 0; $i < $count; $i++) {
                $reference = $references[$i];
                // display block
                ob_start();
                $Blocks->displaySingleBlock('algoliasearch');
                $block_output = ob_get_contents();
                ob_end_clean();
                // replace references with output
                $text = str_replace($reference, $block_output, $text);
            }
        }
        return $text;
    }

    /*
     * Run the macro
     * @param array|string $data
     * @param object $caller_object
     * @param string $caller_function
     * @param array $extra_info
     * @return bool
     */
    public function Run(&$data, $caller_object, $caller_function, $extra_info = NULL) {
        if (is_array($data)) // don't enable it for block (string)
        {
            if (array_key_exists('summary', $data))
                $data['summary'] = $this->processText ($data['summary']);
            if (array_key_exists('description', $data))
                $data['description'] = $this->processText ($data['description']);
        }
    }
}
            