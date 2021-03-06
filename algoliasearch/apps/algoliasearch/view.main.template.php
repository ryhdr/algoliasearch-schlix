<?php
if (!defined('SCHLIX_VERSION')) die('No Access');
global $HTMLHeader;

$HTMLHeader->JAVASCRIPT_SCHLIX_UI();
$HTMLHeader->CSS('https://cdn.jsdelivr.net/npm/instantsearch.css@7.3.1/themes/algolia-min.css');
$HTMLHeader->Javascript_External('https://cdn.jsdelivr.net/npm/algoliasearch@3.35.1/dist/algoliasearchLite.min.js');
$HTMLHeader->Javascript_External('https://cdn.jsdelivr.net/npm/instantsearch.js@4.0.0/dist/instantsearch.production.min.js');
if ($this->isConfigured())
    $HTMLHeader->Javascript($this->getURLofScript('algoliasearch.js'));
$HTMLHeader->CSS($this->getURLofScript('algoliasearch.css'));
?>

<div class="app-page-main app-<?= $this->app_name; ?>" id="app-<?= ___h($this->app_name); ?>-app-page-main">
    <div class="content">
        <h1>
            <img class="algoliasearch-logo" src="<?= $this->getURLofScript('algoliasearch_logo.png') ?>" />
            <?= ___h($this->getApplicationDescription()) ?>
        </h1>
        <?php if ($this->isConfigured()): ?>
            <div id="algoliadata" data-index-name="<?= ___h($index_name) ?>"
                 data-application-id="<?= ___h($application_id) ?>"
                 data-search-only-key="<?= ___h($search_only_key) ?>"
                 data-hits-per-page="<?= ___h($hits_per_page) ?>"></div>
            <header>
                <div id="search-box"></div>
            </header>

            <main>
                <div id="hits"></div>
                <div id="pagination"></div>
            </main>

            <script type="text/html" id="hit-template">
                <div class="hit">
                    <a href="{{ link }}">
                        {{#helpers.highlight}}{ "attribute": "title" }{{/helpers.highlight}}
                    </a>
                    {{#url_media_file}}
                        <div class="image">
                            <img src="{{url_media_file}}" alt="{{title}}" />
                        </div>
                        <div class="text">
                            <p>
                                {{#helpers.highlight}}{ "attribute": "summary" }{{/helpers.highlight}}
                            </p>
                            <p>
                                {{#helpers.highlight}}{ "attribute": "description" }{{/helpers.highlight}}
                            </p>
                        </div>
                    {{/url_media_file}}
                    {{^url_media_file}}
                        <p class="summary">
                            {{#helpers.highlight}}{ "attribute": "summary" }{{/helpers.highlight}}
                        </p>
                        <p class="description">
                            {{#helpers.highlight}}{ "attribute": "description" }{{/helpers.highlight}}
                        </p>
                    {{/url_media_file}}
                </div>
            </script>
        <?php else: ?>
            <p>
                Search is not yet configured, if you're the site owner please follow configuration instruction on the app.
            </p>
        <?php endif; ?>
    </div>
</div>