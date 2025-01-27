<?php
/**
 * Flight: An extensible micro-framework.
 *
 * @copyright   Copyright (c) 2013, Mike Cao <mike@mikecao.com>
 * @license     MIT, http://flightphp.com/license
 */

require_once __DIR__.'/core/Loader.php';

\flight\core\Loader::autoload(true, array(dirname(__DIR__), APP_DIR, API_DIR));
