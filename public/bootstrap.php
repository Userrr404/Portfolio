<?php
/**
 * GLOBAL BOOTSTRAP — loads everything required for each page
 */

require_once CONFIG_FILE;

// Core (app engine + base controller)
require_once APP_FILE;
require_once CONTROLLER_FILE;

// Logger + error handler
require_once LOGGER_FILE;
require_once ERROR_HANDLER_FILE;
