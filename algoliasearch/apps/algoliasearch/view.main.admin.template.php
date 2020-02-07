<?php
if (!defined('SCHLIX_VERSION')) die('No Access');
?>
<!-- {top_menu} -->
<x-ui:schlix-data-explorer-blank data-schlix-controller="SCHLIX.CMS.AlgoliaSearchAdminController" >

    <x-ui:schlix-explorer-toolbar>
        <x-ui:schlix-explorer-toolbar-menu data-position="left">                
            <!-- {config} -->
            <x-ui:schlix-explorer-menu-command data-schlix-command="config" data-schlix-app-action="editconfig" fonticon="fas fa-cog" label="<?= ___('Configuration') ?>" />
            <!-- {end config -->
            <?php if($this->app->isConfigured()): ?>
                <x-ui:schlix-explorer-menu-command data-schlix-command="updateindex" data-schlix-app-action="updateindex" fonticon="fas fa-exclamation-circle" label="<?= ___('Manually Update Index') ?>" />
            <?php endif ?>
            <?= \SCHLIX\cmsHooks::output('getApplicationAdminExtraToolbarMenuItem', $this) ?>
        </x-ui:schlix-explorer-toolbar-menu>
        <!-- {help-about} -->
        <x-ui:schlix-explorer-toolbar-menu data-position="right">
            <x-ui:schlix-explorer-menu-folder fonticon="fa fa-question-circle" label="<?= ___('Help') ?>">
                <x-ui:schlix-explorer-menu-command data-schlix-command="help-about" data-schlix-app-action="help-about" fonticon="fas fas-cog" label="<?= ___('About') ?>" />
            </x-ui:schlix-explorer-menu-folder>
        </x-ui:schlix-explorer-toolbar-menu>
        <!-- {end help-about} -->

    </x-ui:schlix-explorer-toolbar>

    <div class="content">
        <?php if(!$this->app->isConfigured()): ?>
            <h3>Configuration:</h3>
            <ol class="configuration-steps">
                <li>
                    <p>
                        Sign up / Login at
                        <a href="https://www.algolia.com" target="_blank" rel="noreferer nofollow">https://www.algolia.com</a>.
                    </p>
                </li>
                <li>
                    <p>
                        On Algolia dashboard, go to <strong>API Keys</strong> page.
                    </p>
                </li>
                <li>
                    <p>
                        Copy-paste:
                        <code>Application ID</code>,
                        <code>Search-Only API Key</code>, and
                        <code>Admin API Key</code>
                        to respective fields in
                        <a href="<?= $this->createFriendlyAdminURL('action=editconfig'); ?>" data-schlix-command="config" data-schlix-app-action="editconfig" class="schlix-command-button"><i class="fas fa-cog " aria-hidden="true"></i> Configuration</a>.
                    </p>
                </li>
            </ol>
        <?php else: ?>
            <h4>Algolia Search configured, start using search by placing algoliasearch block or macro on appropriate location.</h4>
            <p>
                This app will automatically update search index once per day.<br />
                You can change the frequency at <strong>Settings > System Scheduler > Algoliasearch update index</strong>.<br />
                Alternatively you can update the index manually using Update Button above.
            </p>
            <p>
                Only publicly readable items are indexed.
            </p>
            <p>
                Log entry will be added each time search index updated.<br />
                You can view the log at <strong>Tools > Log Viewer</strong>.
            </p>
            <p>Learn more about Algolia Search at <a href="https://www.algolia.com" target="_blank" rel="noreferer nofollow">https://www.algolia.com</a>.</p>
        <?php endif; ?>
    </div>
    <!-- End Data Viewer -->
</x-ui:schlix-data-explorer-blank>
