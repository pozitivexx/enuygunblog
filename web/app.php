<?php
define('debug_mode', false);
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
// production environment ('prod'). See
//
//   * http://symfony.com/doc/current/cookbook/configuration/front_controllers_and_kernel.html
//   * http://symfony.com/doc/current/cookbook/configuration/environments.html
if (debug_mode) calcTime('hadi başlayalım');

use Symfony\Component\HttpFoundation\Request;

if (debug_mode) calcTime('use');
/**
 * @var Composer\Autoload\ClassLoader
 */
$loader = require __DIR__.'/../app/autoload.php';
if (debug_mode) calcTime('autoload');
include_once __DIR__.'/../var/bootstrap.php.cache';
if (debug_mode) calcTime('bootstrap');

// If your web server provides APC support for PHP applications, uncomment these
// lines to use APC for class autoloading. This can improve application performance
// very significantly. See http://symfony.com/doc/current/components/class_loader/cache_class_loader.html#apcclassloader
//
// NOTE: The first argument of ApcClassLoader() is the prefix used to prevent
// cache key conflicts. In a real Symfony application, make sure to change
// it to a value that it's unique in the web server. A common practice is to use
// the domain name associated to the Symfony application (e.g. 'example_com').
//
// $apcLoader = new Symfony\Component\ClassLoader\ApcClassLoader(sha1(__FILE__), $loader);
// $loader->unregister();
// $apcLoader->register(true);

$kernel = new AppKernel('prod', false);
$kernel->loadClassCache();
if (debug_mode) calcTime('kernel');

// When using the HTTP Cache to improve application performance, the application
// kernel is wrapped by the AppCache class to activate the built-in reverse proxy.
// See http://symfony.com/doc/current/book/http_cache.html#symfony-reverse-proxy
$kernel = new AppCache($kernel);
if (debug_mode) calcTime('cache');

// If you use HTTP Cache and your application relies on the _method request parameter
// to get the intended HTTP method, uncomment this line.
// See http://symfony.com/doc/current/reference/configuration/framework.html#http-method-override
Request::enableHttpMethodParameterOverride();

$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
if (debug_mode) calcTime('response send');

$kernel->terminate($request, $response);
if (debug_mode) calcTime('terminate');

if (debug_mode) calcTime('toplam işlem: '.$debug_total_opr.' - total time: <b>'.$total_time.' ms</b>', 1);
if (debug_mode) echo "<div style='clear:both'>".$debug_result."</div>";
if (debug_mode && isset($_SESSION['queries'])) {
	$total_query_time=0;
	foreach($_SESSION['queries'] AS $key=>$val) $total_query_time+=$val['second'];
	print_rm($_SESSION['queries']);
	echo "time: ".$total_query_time;
}