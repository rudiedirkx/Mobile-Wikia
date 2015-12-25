<?php

require 'inc.bootstrap.php';

list(, $title) = requireParams('wiki', 'title');

$response = wiki_query(array(
	'titles' => $title,
	'prop' => 'revisions',
	'rvprop' => 'content',
	'rvparse' => 1,
), $error, $info);
$page = reset($response['pages']);
$content = $page['revisions'][0]['*'];

$html = $content;
$html = preg_replace('#</?noscript>#', '', $html);
$html = preg_replace('#<(script)[\s\S]+?</\1>#', '', $html);

include 'tpl.header.php';

?>
<h1><?= html(get_wiki()) ?></h1>

<p class="to-wikia">
	<a href="http://<?= urlencode(get_wiki()) ?>.wikia.com/wiki/<?= urlencode(str_replace(' ', '_', $title)) ?>">
		Go to Wikia
	</a>
</p>

<div class="inline-search"><? include 'tpl.search.php' ?></div>

<h1><?= html($title) ?></h1>

<?= $html ?>

<hr />

<details>
	<summary>Debug</summary>
	<pre><?= html(print_r($response, 1)) ?></pre>
</details>

<?php

include 'tpl.footer.php';
