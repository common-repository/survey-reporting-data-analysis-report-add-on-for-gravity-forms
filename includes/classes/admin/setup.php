<?php
namespace Fleek\Gravity\Admin;

class Setup {
  /**
   * Initiate hooks to setup the plugin within the admin area
   */
  public static function init(){
    if(!\Fleek\Gravity\Common\Setup::check_gravity_forms_plugin_active()){
      //The gravity forms plugin isn't active so this plugin can't work
      //Need to output an error notification to the user
      add_action('admin_notices', function(){
        echo '<div class="notice notice-error"><p><strong>Survey Reporting & Data Analysis Report Add-On for Gravity Forms plugin</strong> requires the <strong>Gravity Forms plugin</strong> to be active.</p></div>';
      });
      return false;
    }

    //Queue styles & scripts
    add_action('admin_head', __CLASS__ . '::enqueue_scripts_styles');

    //Add navigation
    add_filter('gform_addon_navigation', __CLASS__ . '::add_menu_item');
  }

  /**
   * Queues the styles and scripts for this plugin
   */
  public static function enqueue_scripts_styles(){
    //Styles
    wp_register_style('fleek_reporting_plugin_CSS', FLEEK_GRAVITY_PLUGIN_URL . '/assets/css/fleek-reporting-plugin.css', [], filemtime(FLEEK_GRAVITY_PLUGIN_DIR . '/assets/css/fleek-reporting-plugin.css'));
    wp_enqueue_style('fleek_reporting_plugin_CSS');
    
    //Scripts
    wp_register_script('fleek_reporting_plugin_JS', FLEEK_GRAVITY_PLUGIN_URL . '/assets/js/fleek-reporting-plugin.js', [], filemtime(FLEEK_GRAVITY_PLUGIN_DIR . '/assets/js/fleek-reporting-plugin.js'), true);
    wp_enqueue_script('fleek_reporting_plugin_JS');
  }

  /**
   * Add the reporting page to the gravity forms navigation 
   * utilising https://docs.gravityforms.com/gform_addon_navigation/
   *
   * @param array $menu_items Current list of menu items to be filtered
   * @return array $menu_items List of filtered menu items
   */
  public static function add_menu_item($menu_items){
    $menu_items[] = [
      "name" => "fleek_gravity_reporting",
      "label" => "Reporting",
      "callback" => __CLASS__ . '::report_page',
      "permission" => "edit_posts"
    ];
    return $menu_items;
  }

  /**
   * Outputs the Gravity Forms report generation page
   */
  public static function report_page(){
    //Get the current gravity forms
    $gravity_forms = \GFAPI::get_forms();
    $plugins = get_plugins();

    require(FLEEK_GRAVITY_PLUGIN_DIR . '/template-parts/html/admin/reporting-page.php');
  }
}