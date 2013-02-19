<?php
require_once 'includes/Audit.Class.php';
require_once 'includes/config.inc.php';
require_once 'includes/datetime.php';
require_once 'includes/functions.php';
require_once 'includes/toolbox.php';
require_once 'includes/SessionMgr.Class.php';
require_once 'includes/Config.Class.php';
require_once 'includes/ManageSessions.Class.php';
require_once 'includes/ENV.Class.php';
require_once 'includes/Logger.php';
require_once 'includes/exception_handler.php';

#
# System Config
#

$c = new ConfigClass; /* system configuration */
$c->set('version', "version 3.0.5");
$c->set('index', 'enrol.php');
$c->set('base', getBaseURL($_SERVER));
$c->set('logfmt_date', 'd/m/Y:G:i:s O');

#
# Application Config
#

$u = new ConfigClass;
$u->set('title', $config_title);
$u->set('mysquarecolor', $config_colour_me);
$u->set('yoursquarecolor', $config_colour_them);
$u->set('nonesquarecolor', $config_colour_none);
$u->set('tableheadercolor', $config_colour_table_header);
$u->set('default_session_size', $config_default_session_size);
$u->set('admin_users', $config_admin_users);
$u->set('show_session_id', $config_show_session_id);
$u->set('max_enrolments_per_line', $config_max_enrolments_per_line);
$u->set('on_production', $config_on_production);
$u->set('forceAdminAuth', $config_force_admin_auth);
$u->set('logLevel', $config_log_level);

$u->set('sessions_file', "data/".$config_sessions_data_file);
$u->set('notices_file', "data/".$config_notices_file);
$u->set('announcements_file', "data/".$config_announcements_file);
$u->set('event_log', "data/".$config_log_file);
$u->set('audit_log', "data/".$config_audit_file);

date_default_timezone_set("Australia/Melbourne");

error_reporting(E_ALL);

Logger::setLogLevel($u->get('logLevel'));
Logger::addFileLogger($u->get('event_log'));

Audit::setAuditFile($u->get('audit_log'));

?>
