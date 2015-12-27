<?php

require 'inc.bootstrap.php';

list(, $title) = requireParams('wiki', 'title');

$response = wiki_query(array(
	'titles' => $title,
	'prop' => 'revisions',
	'rvprop' => 'content',
	'rvparse' => 1,
), $error, $info);
if ( isset($response['normalized'][0]['to']) ) {
	do_redirect('article', array(
		'wiki' => get_wiki(),
		'title' => $response['normalized'][0]['to'],
	));
	exit;
}
$page = reset($response['pages']);
$content = $page['revisions'][0]['*'];

$html = $content;
$html = preg_replace('#</?noscript>#', '', $html);
$html = preg_replace('#<(script)[\s\S]+?</\1>#', '', $html);
$html = preg_replace('#href="/wiki/([^"]+)#', 'href="article.php?wiki=' . get_wiki() . '&title=$1', $html);
// $html = preg_replace('# style=".+?"#', '', $html);

include 'tpl.header.php';

?>
<h1><?= html(get_wiki()) ?></h1>

<div class="inline-search"><? include 'tpl.search.php' ?></div>

<h1 class="article-title"><?= html($title) ?></h1>

<p class="to-wikia">
	<a href="http://<?= urlencode(get_wiki()) ?>.wikia.com/wiki/<?= urlencode(str_replace(' ', '_', $title)) ?>">
		Go to Wikia
	</a>
</p>

<?= $html ?>

<hr />

<details>
	<summary>Debug</summary>
	<pre><?= html(print_r($response, 1)) ?></pre>
</details>

<?php

include 'tpl.footer.php';
