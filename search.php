<?php

require 'inc.bootstrap.php';

list($wiki) = requireParams('wiki');
rememberWiki($wiki);

if ( $search = trim(@$_GET['search']) ) {
	$results = wiki_search_articles($search);
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
					<a href="article.php?wiki=<?= urlencode(get_wiki()) ?>&title=<?= urlencode($item['name']) ?>">
						<?= html($item['title']) ?>
					</a>
				</div>
			</li>
		<? endforeach ?>
		<? if (!$results): ?>
			<li>No results...</li>
		<? endif ?>
	</ul>
<? endif ?>

<h2>Search an article</h2>

<div class="block-search"><? include 'tpl.search.php' ?></div>

<?php

include 'tpl.footer.php';
