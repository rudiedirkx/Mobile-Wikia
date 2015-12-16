<?php

require 'inc.bootstrap.php';

requireParams('wiki');

if ( $search = trim(@$_GET['search']) ) {
	$response = wikia_get('Search/List', array(
		'expand' => 1,
		'limit' => 10,
		'query' => $search,
	), $error, $info);
	$results = $response->items;
}

include 'tpl.header.php';

?>
<? if ($search): ?>
	<h2>Results</h2>

	<ul>
		<? foreach ($results as $item): ?>
			<li class="result-item">
				<div class="title">
					<a href="article.php?wiki=<?= urlencode(get_wiki()) ?>&id=<?= html($item->id) ?>&name=<?= html(basename($item->url)) ?>">
						<?= html($item->title) ?>
					</a>
				</div>
			</li>
		<? endforeach ?>
	</ul>
<? endif ?>

<h2>Search an article</h2>

<div class="block-search"><? include 'tpl.search.php' ?></div>

<?php

include 'tpl.footer.php';
