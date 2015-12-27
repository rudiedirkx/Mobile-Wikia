<form method="get" action="search.php">
	<input type="hidden" name="wiki" value="<?= html(get_wiki()) ?>" />
	<p class="input">
		<label for="search">Search:</label>
		<input type="search" id="search" name="search" placeholder="Search articles..." value="<?= html(@$search) ?>" />
	</p>
	<p class="submit">
		<button>Search</button>
	</p>
</form>
