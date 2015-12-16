<?php

require 'inc.bootstrap.php';

list(, $id, $machineName) = requireParams('wiki', 'id', 'name');

$response = wikia_get('Articles/AsSimpleJson', array(
	'id' => $id,
), $error, $info);
$title = $response->sections[0]->title;

include 'tpl.header.php';

?>
<p class="to-wikia">
	<a href="http://<?= urlencode(get_wiki()) ?>.wikia.com/wiki/<?= urlencode($machineName) ?>">
		Go to Wikia
	</a>
</p>

<div class="inline-search"><? include 'tpl.search.php' ?></div>

<? foreach ($response->sections as $section): ?>
	<? include 'tpl.article-section.php' ?>
<? endforeach ?>
