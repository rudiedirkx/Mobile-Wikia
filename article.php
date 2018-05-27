<?php

require 'inc.bootstrap.php';

list($wiki, $title) = requireParams('wiki', 'title');
rememberWiki($wiki);

$from = @$_GET['from'];

$article = wiki_get_article($title);
if ( isset($article['redirect']) ) {
	do_redirect('article', array(
		'wiki' => get_wiki(),
		'title' => $response['normalized'][0]['to'],
	));
	exit;
}


$_title = $article['title'];
include 'tpl.header.php';

?>
<h1><?= html(get_wiki()) ?></h1>

<div class="inline-search"><? include 'tpl.search.php' ?></div>

<h1 class="article-title"><?= html($title) ?></h1>

<p class="to-wikia">
	<a href="http://<?= urlencode(get_wiki()) ?>.wikia.com/wiki/<?= urlencode(str_replace(' ', '_', $title)) ?>">
		Go to Wikia
	</a>
	<? if ($from): ?>
		| Redirected from <em><?= html($from) ?></em>
	<? endif ?>
</p>

<?= wiki_markup($article['content']) ?>

<? if (WIKIA_DEBUG): ?>
	<hr />

	<details>
		<summary>Debug</summary>
		<pre><?= html(print_r($response, 1)) ?></pre>
	</details>
<? endif ?>

<?php

include 'tpl.footer.php';
