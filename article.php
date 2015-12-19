<?php

require 'inc.bootstrap.php';

list(, $title) = requireParams('wiki', 'title');

$response = wiki_query(array(
	'titles' => $title,
	'prop' => 'revisions',
	'rvprop' => 'content',
), $error, $info);
$page = reset($response['pages']);
$content = $page['revisions'][0]['*'];

include 'tpl.header.php';

?>
<p class="to-wikia">
	<a href="http://<?= urlencode(get_wiki()) ?>.wikia.com/wiki/<?= urlencode(str_replace(' ', '_', $title)) ?>">
		Go to Wikia
	</a>
</p>

<div class="inline-search"><? include 'tpl.search.php' ?></div>

<h1><?= html($title) ?></h1>

<?= wiki_parse($content)->render() ?>

<hr />

<details>
	<summary>Debug</summary>
	<pre><?= html(print_r($response, 1)) ?></pre>
</details>

<?php

include 'tpl.footer.php';
