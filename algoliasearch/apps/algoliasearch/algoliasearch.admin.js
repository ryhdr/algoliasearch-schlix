/**
 * Algolia Search - Javascript admin controller class
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
SCHLIX.CMS.AlgoliaSearchAdminController = class extends SCHLIX.CMS.BaseController  {  
    /**
     * Constructor
     */
    constructor ()
    {
        super("algoliasearch");
    };

 
    runCommand (command, evt)
    {
        switch (command)
        {
            case 'config':
                this.redirectToCMSCommand("editconfig");
                return true;
                break;
            case 'updateindex':
                if (confirm('Manually update index?\n( To avoid problem please don\'t update too frequently )' ))
                    this.redirectToCMSCommand("updateindex");
                return true;
                break;
            default:
                return super.runCommand(command, evt);
                break;
        }
    }
};


