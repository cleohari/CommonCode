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
require('libs/browscap-php/src/phpbrowscap/Browscap.php');

use phpbrowscap\Browscap;

date_default_timezone_set('America/Chicago');

$browscap = new Browscap('/var/php_cache/browser');
$browscap->updateCache();

