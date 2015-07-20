<?php
/**
 * SQL no inject: possible to remove sql injection
 *
 * Copyright (C) 2014 nawawi jamili <nawawi@rutweb.com>
 *
 * This file is distributed under the terms of the GNU General Public
 * License (GPL). Copies of the GPL can be obtained from:
 * http://www.gnu.org/licenses/gpl.html
 * 
 */

/*
    Usage:
    1) include this file at the top of your PHP scripts
    2) set auto_prepend to this file
    3) use your imagination

    Additional:
    - This file contains useful functions that can use in your code.
*/

/**
 * Determine if a variable is set and is NULL or empty.
 *
 * The following variable are considered to be true:<br/>
 * 1) It has been assigned the constant NULL<br />
 * 2) It has not been set to any value yet<br />
 * 3) It has not exist yet<br />
 * 4) It has been assigned to empty value<br />
 *
 * @uses is_null()
 * @param string $str The variable being evaluated.
 * @return bool Return TRUE if match, FALSE otherwise.
 */
function _null($str) {
    return ( @is_null($str) || "$str"=="" ? true : false );
}

/**
 * Check whether a variable is an array and elements in an array is not empty.
 *
 * @uses is_array()
 * @uses empty()
 * @param array $array The variable being evaluated.
 * @return bool Return TRUE if match, FALSE otherwise.
 */
function _array($array) {
    return ( @is_array($array) && !empty($array) ? true : false );
}

/**
 * recursive function for array_map
 *
 * @param function $func The input string.
 * @param array $arr array string.
 * @return array processed array string.
 */
if ( !function_exists('array_map_recursive') ) {
    function array_map_recursive($func, $arr) {
	    $new = array();
	    foreach($arr as $key => $value) {
		    $new[$key] = (_array($value) ? array_map_recursive($func, $value) : ( _array($func) ? call_user_func_array($func, $value) : $func($value) ) );
	    }
	    return $new;
    }
}

/**
 * Remove HTML tags, including invisible text such as style and
 * script code, and embedded objects.
 *
 * @param string $text The input string.
 * @param bool $decode decoding first.
 * @return string Returns the stripped string.
 */
function _strip_html_tags($text, $decode = false) {
    if ( $decode ) {
        $text = htmlspecialchars_decode($text);
    }
	$text = preg_replace(
			array(
				'#<!-.*?-\s*>#s',
				'#<\s*head[^>]*?>.*?<\s*/\s*head\s*>#si',
				'#<\s*script[^>]*?>.*?<\s*/\s*script\s*>#si',
				'#<\s*style[^>]*?>.*?<\s*/\s*style\s*>#si',
				'#<\s*object[^>]*?>.*?<\s*/\s*object\s*>#si',
				'#<\s*embed[^>]*?>.*?<\s*/\s*embed\s*>#si',
				'#<\s*applet[^>]*?>.*?<\s*/\s*applet\s*>#si',
				'#<\s*noscript[^>]*?>.*?<\s*/\s*noscript\s*>#si',
				'#\n#si', '#\r#si',
				'#<\s*noembed[^>]*?>.*?<\s*/\s*noembed\s*>#si'
			),
			array(' ',' ',' ',' ',' ',' ',' ', ' ', ' ', '\1','\1'),
			$text
		);
	do {
		$count = 0;
		$text = preg_replace('/(<)([^>]*?<)/' , '&lt;$2' , $text , -1 , $count);
	} while ($count > 0);
	$text = strip_tags($text);
	$text = str_replace('>' , '&gt;' , $text);
	return trim($text);
}

/**
 * Turn register globals off.
 *
 * @access private
 * @return null Will return null if register_globals PHP directive was disabled
 */
function _unregister_globals() {
    if ( !@ini_get('register_globals') ) return null;
	$skip = array('GLOBALS', '_GET', '_POST', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES');
	$input = array_merge($_GET, $_POST, $_COOKIE, $_SERVER, $_ENV, $_FILES, isset($_SESSION) && is_array($_SESSION) ? $_SESSION : array());
	foreach ( $input as $k => $v ) {
		if ( !in_array($k, $skip) && isset($GLOBALS[$k]) ) {
			$GLOBALS[$k] = null;
			unset($GLOBALS[$k]);
		}
	}
    unset($input);
}

/**
 * htmlspecialchars wrapper for default options ENT_QUOTES, UTF-8 and no double encode
 *
 * @param string $text The input string.
 * @return string Returns the encoded string.
 */

function _escape_html($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8', false);
}

/**
 * protect string from xss attack
 *
 * @param string $data The input string.
 * @return string Returns the encoded string.
 */
function _escape_xss($data) {
    if ( _array($data) ) {
        return array_map_recursive('_escape_html',$data);
    }
    return ( !_null($data) ? _escape_html($data) : null );
}

/**
 * escape sql query
 *
 * @param string $text The input string.
 * @return string Returns the modified string.
 */
function _query_escape($text) {
    $text = _remove_sql_inject($text);
	$sstr = addslashes($text);
	$pat[0] = "/\\\\/";
	$rep[0] = "\\\\\\\\";
	$pat[1] = "/\\'/";
	$rep[1] = "\'";
	$pat[2] = '/\\"/';
	$rep[2] = '\"';
	$sstr = preg_replace($pat, $rep, $sstr);
	return $sstr;
}

/**
 * remove any character other than unsigned number
 *
 * @param string $text The input string.
 * @return string Returns the modified string.
 */
function _num_escape($str) {
    if ( preg_match("/^(\d+)/", $str, $mm) ) {
        return $mm[1];
    }
    return preg_replace("/[^0-9]/", "", $str);
}

/**
 * remove any character other than unsigned number or decimal
 *
 * @param string $text The input string.
 * @return string Returns the modified string.
 */
function _dec_escape($str) {
    if ( preg_match("/^(\d+(\.\d+)?)/", $str, $mm) ) {
        return $mm[1];
    }
    return preg_replace("/[^0-9.]/", "", $str);
}

/**
 * remove any character other than common date format
 *
 * @param string $text The input string.
 * @return string Returns the modified string.
 */
function _date_escape($str) {
    return preg_replace("/[^0-9.\/-]/", "", $str);
}

/**
 * remove any character other than A-Z and space
 *
 * @param string $text The input string.
 * @return string Returns the modified string.
 */
function _str_escape($str) {
    return preg_replace("/[^a-zA-Z ]/", "", $str);
}

/**
 * remove any sql injection pattern from string
 *
 * @param string $str The input string.
 * @return string Returns the stripped string.
 */

function _remove_sql_inject($str) {
    $str = urldecode($str);
    // add more pattern
    $pat[] = "/'\s+AND\s+extractvalue.*/i";
    $pat[] = "/'\s+and\(.*/i";
    $pat[] = "/select\s+.*?\s+from.*/i";
    $pat[] = "/(rand|user|version|database)\(.*/i";
    $pat[] = "/union\(.*/i";
    $pat[] = "/CONCAT\(.*/i";
    $pat[] = "/CONCAT_WS\(.*/i";
    $pat[] = "/ORDER\s+BY.*/i";
    $pat[] = "/UNION\s+SELECT.*/i";
    $pat[] = "/'\s+union\s+select\+.*/i";
    $pat[] = "/GROUP_CONCAT.*/i";
    $pat[] = "/delete\s+from.*/i";
    $pat[] = "/update\s+.*?\s+set=.*/i";
    $pat[] = "/'\s+and\s+\S+\(.*/i";
    $pat[] = "/'\s+and\s+\S+\s+\(.*/i";
    return preg_replace($pat,"", $str);
}

/** end functions **/

/* setting */
define('SQL_INJECT_PROTECT', true);
define('XSS_PROTECT', true);
define('UNREGISTER_GLOBALS', true);

if ( defined('SQL_INJECT_PROTECT') && SQL_INJECT_PROTECT) {
    if ( !empty($_GET) ) $_GET = array_map_recursive('_remove_sql_inject', $_GET);
    if ( !empty($_POST) ) $_POST = array_map_recursive('_remove_sql_inject', $_POST);
    if ( !empty($_REQUEST) ) $_REQUEST = array_map_recursive('_remove_sql_inject', $_REQUEST);
    if ( !empty($_COOKIE) ) $_COOKIE = array_map_recursive('_remove_sql_inject', $_COOKIE);
}

if ( defined('XSS_PROTECT') && XSS_PROTECT) {
    if ( !empty($_GET) ) $_GET = array_map_recursive('_escape_html', $_GET);
    if ( !empty($_POST) ) $_POST = array_map_recursive('_escape_html', $_POST);
    if ( !empty($_REQUEST) ) $_REQUEST = array_map_recursive('_escape_html', $_REQUEST);
    if ( !empty($_COOKIE) ) $_COOKIE = array_map_recursive('_escape_html', $_COOKIE);
}

if ( defined('UNREGISTER_GLOBALS') && UNREGISTER_GLOBALS) {
    _unregister_globals();
}


