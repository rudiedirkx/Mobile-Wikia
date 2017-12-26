<?php

require 'inc.bootstrap.php';

list($wiki, $title) = requireParams('wiki', 'title');
rememberWiki($wiki);

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
if ( isset($page['revisions'][0]) ) {
	$content = $page['revisions'][0]['*'];
}
else {
	$content = '<p>Page does not exist.</p>';
}

$html = $content;
$html = preg_replace('#</?noscript>#', '', $html);
$html = preg_replace('#<(script)[\s\S]+?</\1>#', '', $html);
$html = preg_replace_callback('#href="/wiki/([^"]+)#', function($match) {
	$titles = explode('?', $match[1]);
	$title = $titles[0];

	return 'href="article.php?wiki=' . get_wiki() . '&title=' . $title;
}, $html);
$html = preg_replace('# src="/#', ' src="http://' . urlencode(get_wiki()) . '.wikia.com/', $html);
$html = preg_replace('# srcset=#', ' data-srcset=', $html);
// $html = preg_replace('# style=".+?"#', '', $html);

if ( preg_match('#^REDIRECT (.+)$#', trim(strip_tags($html)), $match) ) {
	do_redirect('article', array(
		'wiki' => get_wiki(),
		'title' => $match[1],
	));
	exit;
}

$_title = $title;
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
	<pre><? /*= html(print_r($response, 1))*/ ?></pre>
</details>

<?php

include 'tpl.footer.php';
