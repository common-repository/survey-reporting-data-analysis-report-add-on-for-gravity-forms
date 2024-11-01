<?php
namespace Fleek\Gravity\Common;

class Setup {
  protected static $debug = true;

  /**
   * Checks to see if debugging in enabled or disabled
   *
   * @return void
   */
  public static function get_debug(){
    return self::$debug;
  }

  /**
   * Checks to see if the Gravity Forms plugin is active
   *
   * @return void
   */
  public static function check_gravity_forms_plugin_active(){
    if(!function_exists('is_plugin_active')){
      require_once(ABSPATH . '/wp-admin/includes/plugin.php');
    }
    if(!is_plugin_active('gravityforms/gravityforms.php')){
      //The gravity forms plugin isn't active so this plugin can't work
      return false;
    }

    return true;
  }
}