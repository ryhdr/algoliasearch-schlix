<?php
if (!defined('SCHLIX_VERSION')) die('No Access');

$app_list = $this->app->supportedApplications();
?>
<!-- {top_menu} -->
<schlix-config:data-editor data-schlix-controller="SCHLIX.CMS.AlgoliaSearchAdminController" type="config">

        <x-ui:schlix-config-save-result />
        <x-ui:schlix-editor-form id="form-edit-config" method="post" data-config-action="save" action="<?= $this->createFriendlyAdminURL('action=saveconfig') ?>" autocomplete="off">
            <schlix-config:action-buttons />
            <x-ui:csrf />

            <x-ui:schlix-tab-container>
                <!-- tab -->
                <x-ui:schlix-tab id="tab_general" fonticon="far fa-file" label="<?= ___('General') ?>"> 
                    <!--content -->
                        
                    <schlix-config:app_alias />
                    <schlix-config:app_description />
                    <schlix-config:checkbox config-key='bool_disable_app' label='<?= ___('Disable application') ?>' />

                    <schlix-config:textbox config-key='str_application_id' label='<?= ___('Application ID') ?>' />
                    <schlix-config:textbox config-key='str_search_only_key' label='<?= ___('Search-Only API Key') ?>' />
                    <schlix-config:textbox config-key='str_api_key' label='<?= ___('Admin API Key') ?>' />
                    <schlix-config:textbox config-key='str_index_name' label='<?= ___('Index Name') ?>' />
                    <schlix-config:textbox config-key='int_hits_per_page' label='<?= ___('Hits per Page') ?>' type="number" />
                    <schlix-config:checkboxgroup config-key="array_enabled_apps" label="<?=  ___('Enable for the following applications') ?>">
                        <?php foreach ($app_list as $enabled_app): ?>
                            <schlix-config:option value='<?= $enabled_app ?>'><?= $enabled_app ?></schlix-config:option>
                        <?php endforeach ?>
                    </schlix-config:checkboxgroup>
                    <schlix-config:textbox config-key='int_value_max_length' label='<?= ___('Value Max. Length') ?>' type="number" />
                    <div class="help-text">
                        <?= ___('Determine text maximum length to be indexed. Set blank to index everything.') ?><br />
                        <?= ___('There\'s size limit for each record determined by your selected Plan.') ?>
                        <a href="https://www.algolia.com/doc/faq/basics/is-there-a-size-limit-for-my-index-records/" target="_blank" rel="noreferer nofollow">
                            <?= ___('Click here for more information.') ?>
                        </a>
                    </div>
                </x-ui:schlix-tab>
                <!-- tab -->
                <?= \SCHLIX\cmsHooks::output('getApplicationAdminExtraEditConfigTab', $this) ?>
                <!-- end -->
            </x-ui:schlix-tab-container>
            
        </x-ui:schlix-editor-form>
</schlix-config:data-editor>     