<?php

require 'inc.bootstrap.php';

if ( $search = trim(@$_GET['search']) ) {
	$results = wiki_search_wikis($search);
}

$_title = 'Search wiki';
$search and $_title .= ': ' . $search;
include 'tpl.header.php';

$history = rememberWiki();

?>
<? if ($search): ?>
	<h2>Results</h2>

	<ul>
		<? foreach ($results as $item): ?>
			<li class="result-item">
				<div class="title">
					<a href="search.php?wiki=<?= urlencode($item['name']) ?>">
						<?= html($item['title'] ?: $item['name']) ?>
					</a>
				</div>
				<div class="machine-name">
					<?= html($item['name']) ?>
				</div>
			</li>
		<? endforeach ?>
	</ul>
<? endif ?>

<? if ($history): ?>
	<h2>Previous wikis</h2>

	<ul>
		<? foreach ($history as $wiki): ?>
			<li class="result-item">
				<div class="machine-name">
					<a href="search.php?wiki=<?= urlencode($wiki) ?>">
						<?= html($wiki) ?>
					</a>
				</div>
			</li>
		<? endforeach ?>
	</ul>
<? endif ?>

<h2>Search a wiki</h2>

<form method="get" action="">
	<p>Search a wiki: <input type="search" name="search" value="<?= html($search) ?>" /></p>
	<p><button>Search!</button></p>
</form>

<?php

include 'tpl.footer.php';
