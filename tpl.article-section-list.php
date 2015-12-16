<ul>
	<? foreach ($content->elements as $element): ?>
		<li>
			<p><?= html($element->text) ?></p>
			<? if ($element->elements): ?>
				<? include 'tpl.article-section-list.php' ?>
			<? endif ?>
		</li>
	<? endforeach ?>
</ul>
