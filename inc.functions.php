<?php

// use rdx\wikiparser\Document;
// use rdx\wikiparser\Parser;
// use rdx\wikiparser\Linker;

// class MobileWikiaLinker extends Linker {
// 	public function articleURL( $article ) {
// 		return 'article.php?wiki=' . get_wiki() . '&title=' . ucfirst($article);
// 	}
// }

function wiki_parse( $content ) {
	return $content;

	$document = new Document(
		new Parser,
		new MobileWikiaLinker
	);

	$document->parseSimple($content, array(
		'Quote' => function($properties) {
			return '<blockquote><p>' . $properties[0] . '</p><p><em>' . $properties[1] . '</em></p></blockquote>';
		},
		'pic' => function($properties) {
			return ' &lt;' . $properties[1] . '&gt; ';
		},
		'Recipe' => function($properties) {
			$input = array();
			foreach (array('', '1', '2', '3', '4') as $name) {
				if ( isset($properties['item' . $name]) ) {
					$count = isset($properties['count' . $name]) ? ' x' . $properties['count' . $name] : '';
					$input[] = $properties['item' . $name] . $count;
				}
			}
			$input = array_merge($input, array_keys(array_filter($properties, function($value) {
				return $value === 'yes';
			})));
			return '<p>&lt;' . implode(' + ', $input) . ' = ' . $properties['result'] . '&gt;</p> ';
		},
	));

	return $document;
}

function get_url( $path, $query = array() ) {
	$query = $query ? '?' . http_build_query($query) : '';
	$path = $path ? $path . '.php' : basename($_SERVER['SCRIPT_NAME']);
	return $path . $query;
}

function do_redirect( $path, $query = array() ) {
	$url = get_url($path, $query);
	header('Location: ' . $url);
}

function requireParams( $param1 ) {
	$want = array_flip(func_get_args());
	$have = array_filter($_GET);
	$miss = array_diff_key($want, $have);
	if ( $miss ) {
		return missingParams(array_keys($miss));
	}

	$values = array();
	foreach ($want as $name => $foo) {
		$values[] = $have[$name];
	}
	return $values;
}

function missingParams( $params ) {
	exit('Missing params: ' . html(implode(', ', $params)));
}

function rememberWiki( $wiki = null ) {
	$current = (string) @$_COOKIE['wikia_history'];
	$wikis = array_filter(explode(',', $current));
	$wiki and $wikis[] = $wiki;
	$wikis = array_unique($wikis);
	sort($wikis);
	$new = implode(',', $wikis);
	$wiki and $new != $current and setcookie('wikia_history', $new, strtotime('+1 year'));
	return $wikis;
}

function html( $text ) {
	return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function get_wiki() {
	if ( defined('WIKIA_WIKI') ) {
		return WIKIA_WIKI;
	}

	if ( isset($_GET['wiki']) && is_string($_GET['wiki']) ) {
		if ( preg_match('#^[a-z][\w\d-]+$#i', $_GET['wiki']) ) {
			define('WIKIA_WIKI', strtolower($_GET['wiki']));
			return WIKIA_WIKI;
		}
	}
}

function wiki_category_articles( $category ) {
	$url = wikia_url('api.php', array(
		'action' => 'query',
		'format' => 'json',
		'list' => 'categorymembers',
		'cmtitle' => 'Category:' . $category,
	));

	$ch = wikia_curl($url, 'GET', $info);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-agent: Mobile Wikia'));

	$response = wikia_response($ch, $error, $info);
	return $response['query'];
}

function wiki_query( $query, &$error = null, &$info = null ) {
	$url = wikia_url('api.php', $query + array(
		'action' => 'query',
		'format' => 'json',
	));

	$ch = wikia_curl($url, 'GET', $info);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-agent: Mobile Wikia'));

	$response = wikia_response($ch, $error, $info);
	return $response['query'];
}

function wikia_get( $resource, $query = null, &$error = null, &$info = null ) {
	$url = wikia_url('api/v1/' . $resource, $query);

	$ch = wikia_curl($url, 'GET', $info);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-agent: Mobile Wikia'));

	$response = wikia_response($ch, $error, $info);
	return $response;
}

function wikia_url( $resource, $query = null ) {
	$wiki = get_wiki() ? get_wiki() : 'www';
	$url = 'http://' . $wiki . '.wikia.com/' . $resource;
	$query && $url .= '?' . http_build_query($query);
	return $url;
}

function wikia_curl( $url, $method = '', &$info = null ) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	return $ch;
}

function wikia_response( $ch, &$error = null, &$info = null ) {
	$result = curl_exec($ch);

	@list($header, $body) = explode("\r\n\r\n", $result, 2);

	// OMG HTTP 100 Continue... You suck!
	if ( is_int(strpos($header, '100 Continue')) ) {
		@list($header, $body) = explode("\r\n\r\n", $body, 2);
	}

	$info = curl_getinfo($ch) + ($info ?: array());
	curl_close($ch);

	$info['headers'] = wikia_http_headers($header);

	$code = $info['http_code'];
	$success = $code >= 200 && $code < 300;

	$error = $success ? false : $code;

	$info['response'] = $body;
	$info['error'] = '';
	if ( $error ) {
		$info['error'] = ($json = @json_decode($body, true)) ? $json : null;
	}

	$response = $success ? @json_decode($body, true) : false;

	return $response;
}

function wikia_http_headers( $header ) {
	$headers = array();
	foreach ( explode("\n", $header) AS $line ) {
		@list($name, $value) = explode(':', $line, 2);
		if ( ($name = trim($name)) && ($value = trim($value)) ) {
			$headers[strtolower($name)][] = urldecode($value);
		}
	}
	return $headers;
}
