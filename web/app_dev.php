<?php
define('debug_mode', 0);
if (debug_mode) {
	$last_time=microtime(true);
	$total_time=0;
	$debug_total_opr=0;
	$debug_result="";
	function calcTime($var="", $show=0) {
		$diff = microtime(true) - $GLOBALS['last_time'];
		$GLOBALS['last_time']=microtime(true);
		$GLOBALS['debug_total_opr']++;
		$sec = intval($diff);
		$micro = $diff - $sec;
		$final =  str_replace('0.', '.', sprintf('%.3f', $micro)); //strftime('%T', mktime(0, 0, $sec)) .
		$GLOBALS['total_time']+=$final;
		list($usec, $sec) = explode(' ', microtime());

		//print_r(debug_backtrace());

		//if ($final>0.001 || $show)
		$GLOBALS['debug_result'].="\n
				<p style='font-family: arial, sans-serif!IMPORTANT; font-size:15px;' align=left>".
			date('H:i:s', $sec) .":". $usec." (".($final>0.014?"<b>":"")."".($final)."".($final>0.014?"</b>":"").") $var
				</p>";
	}
}

// This is the front controller used when executing the application in the
// development environment ('dev'). See
//
//   * http://symfony.com/doc/current/cookbook/configuration/front_controllers_and_kernel.html
//   * http://symfony.com/doc/current/cookbook/configuration/environments.html

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;

// If you don't want to setup permissions the proper way, just uncomment the
// following PHP line. See:
// http://symfony.com/doc/current/book/installation.html#configuration-and-setup for more information
//umask(0000);

// This check prevents access to debug front controllers that are deployed by
// accident to production servers. Feel free to remove this, extend it, or make
// something more sophisticated.
if (isset($_SERVER['HTTP_CLIENT_IP'])
    || isset($_SERVER['HTTP_X_FORWARDED_FOR'])
    || !(in_array(@$_SERVER['REMOTE_ADDR'], ['127.0.0.1', 'fe80::1', '::1']) || php_sapi_name() === 'cli-server')
) {
    header('HTTP/1.0 403 Forbidden');
    exit('You are not allowed to access this file. Check '.basename(__FILE__).' for more information.');
}

/**
 * @var Composer\Autoload\ClassLoader $loader
 */
$loader = require __DIR__.'/../app/autoload.php';
Debug::enable();

$kernel = new AppKernel('dev', true);
$kernel->loadClassCache();
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);


if (debug_mode) calcTime('toplam işlem: '.$debug_total_opr.' - total time: <b>'.$total_time.' ms</b>', 1);
if (debug_mode) echo "<div style='clear:both'>".$debug_result."</div>";
if (debug_mode && isset($_SESSION['queries'])) {
	$total_query_time=0;
	foreach($_SESSION['queries'] AS $key=>$val) $total_query_time+=$val['second'];
	print_rm($_SESSION['queries']);
	echo "time: ".$total_query_time;
}