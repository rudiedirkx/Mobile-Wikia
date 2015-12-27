<?php

require 'inc.bootstrap.php';

if ( $search = trim(@$_GET['search']) ) {
	$response = wikia_get('Search/CrossWiki', array(
		'expand' => 1,
		'limit' => 10,
		'query' => $search,
	), $error, $info);
	$results = $response['items'];

	foreach ( $results as &$item ) {
		$item['machine_name'] = substr(
			$item['url'],
			$p1 = strpos($item['url'], '//') + 2,
			strpos($item['url'], '.') - $p1
		);
		unset($item);
	}
}

$_title = 'Search wiki';
$search and $_title .= ': ' . $search;
include 'tpl.header.php';

?>
<? if ($search): ?>
	<h2>Results</h2>

	<ul>
		<? foreach ($results as $item): ?>
			<li class="result-item">
				<div class="title">
					<a href="search.php?wiki=<?= urlencode($item['machine_name']) ?>">
						<?= html($item['title']) ?>
					</a>
				</div>
				<div class="machine-name">
					<?= html($item['machine_name']) ?>
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
