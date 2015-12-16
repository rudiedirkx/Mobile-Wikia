<?php
$tag = 'h' . (int) $section->level;
?>

<<?= $tag ?>><?= html($section->title) ?></<?= $tag ?>>

<? foreach ($section->content as $content): ?>
	<? include 'tpl.article-section-' . basename($content->type) . '.php' ?>
<? endforeach ?>
