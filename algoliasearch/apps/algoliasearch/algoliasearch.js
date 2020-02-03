var data = SCHLIX.Dom.get('algoliadata').dataset;


// Replace with your own values
const searchClient = algoliasearch(
  data.applicationId,
  data.searchOnlyKey
);

const search = instantsearch({
  indexName: data.indexName,
  searchClient,
  routing: true,
});

search.addWidgets([
  instantsearch.widgets.configure({
    hitsPerPage: data.hitsPerPage,
  })
]);

search.addWidgets([
  instantsearch.widgets.searchBox({
    container: '#search-box',
    placeholder: 'Search',
    autofocus: true
  })
]);

search.addWidgets([
  instantsearch.widgets.hits({
    container: '#hits',
    templates: {
      item: document.getElementById('hit-template').innerHTML,
      empty: `We didn't find any results for the search <em>"{{query}}"</em>`,
    },
  }),
  instantsearch.widgets.pagination({
      container: '#pagination'
  })
]);

search.start();

