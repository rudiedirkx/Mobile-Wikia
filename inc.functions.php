<?php

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
		define('WIKIA_WIKI', $_GET['wiki']);
		return WIKIA_WIKI;
	}
}

function wiki_search_wikis( $search ) {
	$results = [];

	$response = wikia_get('Wikis/ByString', array(
		'string' => $search,
		'limit' => 10,
		'batch' => 1,
		'includeDomain' => 1,
	), $error, $info);
	foreach ( $response['items'] as $item ) {
		if ( $item && isset($item['domain']) ) {
			$name = preg_replace('#\.wikia\.com$#', '', $item['domain']);
			$results[] = [
				'source' => 'wikia',
				'title' => @$item['name'] ?: $name,
				'name' => $name,
			];
		}
	}

	return $results;
}

function wiki_search_articles( $search ) {
	$results = [];

	$response = wikia_get('Search/List', array(
		'expand' => 1,
		'limit' => 10,
		'query' => $search,
	), $error, $info);
	foreach ( $response['items'] as $item ) {
		if ( $item && isset($item['title']) ) {
			$title = $item['title'];
			$results[] = [
				'name' => ($p = strpos($title, '#')) ? substr($title, 0, $p) : $title,
				'title' => $title,
			];
		}
	}

	return $results;
}

function wiki_get_article( $name ) {
	$response = wiki_query(array(
		'titles' => $name,
		'prop' => 'revisions',
		'rvprop' => 'content',
		'rvparse' => 1,
	), $error, $info);

	if ( isset($response['normalized'][0]['to']) ) {
		return [
			'redirect' => $response['normalized'][0]['to'],
		];
	}

	$page = reset($response['pages']);

	if ( isset($page['revisions'][0]) ) {
		$content = $page['revisions'][0]['*'];

		if ( preg_match('#^Category:(.+)$#', $name, $match) ) {
			$articles = wiki_category_articles($match[1]);
			$content .= '<h2>Pages in <em>' . html($match[1]) . '</em></h2><ul>' . implode(array_map(function($item) {
				return '<li><a href="article.php?wiki=' . urlencode(get_wiki()) . '&title=' . urlencode($item['title']) . '">' . html($item['title']) . '</a></li>';
			}, $articles['categorymembers'])) . '</ul>';
		}

		$plain = trim(strip_tags($content));
		if ( preg_match('#^redirect (.+)$#i', $plain, $match) ) {
			return [
				'redirect' => $match[1],
			];
		}
		elseif ( $plain == 'redirect' ) {
			return [
				'redirect' => 'Category:' . $title,
			];
		}
	}
	else {
		$content = '<p>Page does not exist.</p>';
	}

	return [
		'title' => $name,
		'content' => $content,
	];
}

function wiki_markup( $html ) {
	$html = preg_replace('#</?noscript>#', '', $html);
	$html = preg_replace('#<(script)[\s\S]+?</\1>#', '', $html);
	$html = preg_replace_callback('#href="/wiki/([^"]+)#', function($match) {
		$titles = explode('?', $match[1]);
		$title = $titles[0];

		return 'href="article.php?wiki=' . get_wiki() . '&title=' . $title;
	}, $html);
	$html = preg_replace('# src="/#', ' src="http://' . urlencode(get_wiki()) . '.wikia.com/', $html);
	$html = preg_replace('# srcset=#', ' data-srcset=', $html);
	$html = preg_replace('# style=#', ' data-style=', $html);
	return $html;
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
	$url = 'https://' . $wiki . '.wikia.com/' . $resource;
	$query && $url .= '?' . http_build_query($query);
	return $url;
}

function wikia_curl( $url, $method, &$info = null ) {
	$GLOBALS['_requests'][] = "$method $url";

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
