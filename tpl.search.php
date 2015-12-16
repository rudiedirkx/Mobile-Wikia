<form method="get" action="search.php">
	<input type="hidden" name="wiki" value="<?= html(get_wiki()) ?>" />
	<p>Search: <input type="search" name="search" value="<?= html(@$search) ?>" /></p>
	<p><button>Search!</button></p>
</form>
