<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title><?= @$_title ? html($_title) . ' - ' : '' ?>Wikia</title>
	<style>
	html {
		font-size: 20px;
		max-width: 800px;
	}
	h1, h2, h3 {
		margin-top: 0;
	}
	input, textarea, select {
		padding: 6px;
	}
	button {
		padding: 4px 12px;
	}
	.result-item + .result-item {
		margin-top: .5em;
	}
	.article-title {
		margin-bottom: .25em;
	}
	.to-wikia {
		margin-top: 0;
	}
	.inline-search p {
		display: inline-block;
	}
	.inline-search input {
		width: 6em;
	}
	img[src^="data:"][data-image-key] {
		display: none;
	}
	.editsection {
		display: none;
	}
	</style>
</head>

<body>

</body>

</html>
