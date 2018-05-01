<?php

require 'inc.bootstrap.php';

if ( $search = trim(@$_GET['search']) ) {
	$response = wikia_get('Wikis/ByString', array(
		'string' => $search,
		'limit' => 10,
		'batch' => 1,
		'includeDomain' => 1,
	), $error, $info);
	$results = array_filter($response['items']);

	foreach ( $results as &$item ) {
		$item['machine_name'] = preg_replace('#\.wikia\.com$#', '', $item['domain']);
		unset($item);
	}
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
					<a href="search.php?wiki=<?= urlencode($item['machine_name']) ?>">
						<?= html($item['name'] ?: $item['machine_name']) ?>
					</a>
				</div>
				<div class="machine-name">
					<?= html($item['machine_name']) ?>
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
