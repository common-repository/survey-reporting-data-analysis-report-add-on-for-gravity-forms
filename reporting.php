<?php
/*
 * Plugin Name:       Survey Reporting & Data Analysis Report Add-On for Gravity Forms
 * Description:       This plugin extends the Gravity Forms plugin and adds a reporting tool onto any existing forms.
 * Version:           1.0.7
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Fleek Marketing
 * Author URI:        https://fleek.marketing/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       gravityforms-reporting
 */

/**
 * @package     Fleek Gravity Reporting (flgr)
 * @copyright   Copyright (c) 2022, Fleek Marketing
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * 
 *    KKK     KKKeep   IIIt     SSSSSSuper    SSSSSSimple
 *    KKK    KKKeep    IIIt    SSSSSSSuper   SSSSSSSimple
 *    KKK  KKKKeep     IIIt    SSSuper       SSSimple
 *    KKKKKKeep        IIIt    SSSSSSuper    SSSSSSimple
 *    KKKKKKeep        IIIt    SSSSSSSuper   SSSSSSSimple
 *    KKK  KKKKeep     IIIt        SSSuper       SSSimple
 *    KKK    KKKeep    IIIt    SSSSSSSuper   SSSSSSSimple
 *    KKK     KKKeep   IIIt   SSSSSSuper     SSSSSSimple
 * 
 *    IF YOU DEVELOP ON THIS PLUGIN THEN PLEASE FOLLOW THE KISS PRINCIPLE
 *    
 */
/* Exit if accessed directly */
if(!defined('ABSPATH')){
  exit();
}

/**
 * Set constants for the plugin
 */
define('FLEEK_GRAVITY_REPORTING', '1.0.7'); /* the current plugin version */
define('FLEEK_GRAVITY_PLUGIN_DIR', plugin_dir_path( __FILE__ )); /* plugin directory path */
define('FLEEK_GRAVITY_PLUGIN_URL', plugin_dir_url( __FILE__ )); /* plugin directory url */
define('FLEEK_GRAVITY_PLUGIN_BASENAME', plugin_basename( __FILE__ )); /* the plugin basename */

/**
 * Include pro version if available
 */
if(is_dir(FLEEK_GRAVITY_PLUGIN_DIR . '/pro/') && is_file(FLEEK_GRAVITY_PLUGIN_DIR . '/pro/init.php')){
  require_once(FLEEK_GRAVITY_PLUGIN_DIR . '/pro/init.php');
}

/**
 * Load all the required libraries
 */
require_once(__DIR__ . '/load.php');

/**
 * Register activation & deactivation hooks
 */
register_activation_hook(__FILE__, 'fleek_gravity_reporting_activatation');
register_deactivation_hook(__FILE__, 'fleek_gravity_reporting_deactivatation');

/**
 * Activation function is currently not in use but left here for future use
 */
function fleek_gravity_reporting_activatation(){
  
}

/**
 * Deactivation hook is currently not in use but left here for future use
 */
function fleek_gravity_reporting_deactivatation(){
  
}

/**
 * Initialise the plugin functionality
 */
\Fleek\Gravity\AJAX\Submit::init();
add_action('init', function(){
  \Fleek\Gravity\Admin\Setup::init();
}, 15);