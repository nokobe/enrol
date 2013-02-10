<?php

$config_title = "Set Your Title";
$config_admin_users = array("{name}" => "{md5PasswordHash}", ...);
$config_default_session_size = 8;
$config_max_enrolments_per_line = 8;
$config_sessions_data_file = "sessions.xml";
$config_notices_file = "notices.txt";
$config_log_file = "event-log.txt";
$config_force_admin_auth = 1;	# require admin users to authenticate immediately on login

# HEADER STYLE 1 - re-print header every N sessions
$config_page_header_frequency = 0; // how often to RE-print the table header (0 == never)

# HEADER STYLE 2 - re-print header after each weekly message
$config_print_weekly_title = TRUE; // if TRUE, will print a message before each new week of sessions
$config_print_header_after_weekly_title = TRUE; // this goes well with weekly_title(ON) and header_freqency(0)

/* Colours */

$config_colour_me = "lightgreen";
$config_colour_them ="lightgrey";
$config_colour_none = "white";
$config_colour_table_header = "lightblue";

/* Technical */

$config_show_session_id = FALSE;	/* can be useful to turn on for diagnostics */
$config_on_production = TRUE;
$config_debug = FALSE;
?>
