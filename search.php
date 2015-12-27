<?php

require 'inc.bootstrap.php';

requireParams('wiki');

if ( $search = trim(@$_GET['search']) ) {
	$response = wikia_get('Search/List', array(
		'expand' => 1,
		'limit' => 10,
		'query' => $search,
	), $error, $info);
	$results = $response['items'];
}

$_title = 'Search article';
$search and $_title .= ': ' . $search;
include 'tpl.header.php';

?>
<h1><?= html(get_wiki()) ?></h1>

<? if ($search): ?>
	<h2>Results</h2>

	<ul>
		<? foreach ($results as $item): ?>
			<li class="result-item">
				<div class="title">
					<a href="article.php?wiki=<?= urlencode(get_wiki()) ?>&title=<?= urlencode($item['title']) ?>">
						<?= html($item['title']) ?>
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
