<?php
/**
 * Cron script
 *
 * This file describes runs actions needed by the system occasionally that take a long time
 *
 * PHP version 5 and 7
 *
 * @author Patrick Boyd / problem@burningflipside.com
 * @copyright Copyright (c) 2015, Austin Artistic Reconstruction
 * @license http://www.apache.org/licenses/ Apache 2.0 License
 */

/**
 * This cron script recompiles the Browscap cache
 */
ini_set('memory_limit','-1');      // turn off memory limit for this script
set_time_limit(120);               // change to 2 minutes for this script

require('vendor/autoload.php');

use BrowscapPHP\Browscap;

date_default_timezone_set('America/Chicago');

$bc = new \BrowscapPHP\BrowscapUpdater('/var/php_cache/browser');
$adapter = new \WurflCache\Adapter\File([\WurflCache\Adapter\File::DIR => '/var/php_cache/browser']);
$bc->setCache($adapter);
//$browscap = new Browscap('/var/php_cache/browser');
//$browscap->updateCache();
$bc->update();
