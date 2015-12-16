<?php

function requireParams($param1) {
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

function missingParams($params) {
	exit('Missing params: ' . html(implode(', ', $params)));
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

function wikia_get( $resource, $query = null, &$error = null, &$info = null ) {
	$url = wikia_url($resource, $query, $info);

	$ch = wikia_curl($url, 'GET', $info);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-agent: Mobile Wikia'));

	$response = wikia_response($ch, $error, $info);
	return $response;
}

function wikia_url( $resource, $query = null, $info = null ) {
	$wiki = get_wiki() ? get_wiki() : 'www';
	$url = 'http://' . $wiki . '.wikia.com/api/v1/' . $resource;
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
		$info['error'] = ($json = @json_decode($body)) ? $json : null;
	}

	$response = $success ? @json_decode($body) : false;

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
