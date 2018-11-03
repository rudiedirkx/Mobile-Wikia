<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<meta name="theme-color" content="#333" />
	<title><?= @$_title ? html($_title) . ' - ' : '' ?>Wikia</title>
	<style><?= preg_replace('#\s*([\:\;\}\{\,])\s*#', '$1', file_get_contents(__DIR__ . '/style.css')) ?></style>
</head>

<body>

<p><a href="/">Index</a></p>

</body>

</html>
