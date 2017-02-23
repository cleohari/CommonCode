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

require('libs/browscap-php/src/phpbrowscap/Browscap.php');

use phpbrowscap\Browscap;

date_default_timezone_set('America/Chicago');

$browscap = new Browscap('/var/php_cache/browser');
$browscap->updateCache();
