<?php
namespace Fleek\Gravity\AJAX;

class Submit {
  public static function init(){
    add_action('wp_ajax_flgr_get_answers', [__CLASS__, 'handle_field_answers']);
    add_action('wp_ajax_nopriv_flgr_get_answers', [__CLASS__, 'handle_field_answers']);

    add_action('wp_ajax_flgr_contact', [__CLASS__, 'handle_contact_us']);
    add_action('wp_ajax_nopriv_flgr_contact', [__CLASS__, 'handle_contact_us']);
  }

  /**
   * Handles retrieving data from a form's answer field
   *
   * @return void
   */
  public static function handle_field_answers(){
    $response = [
      'success' => false,
      'message' => 'Nonce is missing or invalid',
    ];

    if(
      !isset($_POST['flgr_report_results_nonce'])
      || !wp_verify_nonce($_POST['flgr_report_results_nonce'], 'flgr_report_results')
    ){
      exit(json_encode($response));
    }

    //Validate and sanitize data
    $safe_data = \Fleek\Gravity\Common\Security::validate_sanitize_submission('flgr_get_answers', $_POST);

    //Get Gravity Form
    $form = \Fleek\Gravity\GravityForm\Data::get_report_form($safe_data['fldFormId']);
    if(empty($form)){
      $response['message'] = 'Form not found';
      exit(json_encode($response));
    }

    //Get field data
    $safe_data['form'] = $form;
    $response = \Fleek\Gravity\GravityForm\Data::get_answer_data_within_viewport($safe_data);

    exit(json_encode($response));
  }

  /**
   * Handles the contact us modal popup
   *
   * @return void
   */
  public static function handle_contact_us(){
    $response = [
      'success' => false,
      'message' => 'Nonce is missing or invalid',
    ];

    if(
      !isset($_POST['flgr_contact_nonce'])
      || !wp_verify_nonce($_POST['flgr_contact_nonce'], 'flgr_contact')
    ){
      exit(json_encode($response));
    }

    //Validate and sanitize data
    $safe_data = \Fleek\Gravity\Common\Security::validate_sanitize_submission('flgr_contact', $_POST);

    //Get active plugins
    $plugins = get_plugins();
    $active_plugins = [];
    foreach($plugins as $k => $v){
      if(in_array($k, $safe_data['plugins'])){
        $active_plugins[] = $v;
      }
    }

    //Get form
    $gravity_form = \Fleek\Gravity\GravityForm\Data::get_report_form($safe_data['fldFormId']);

    //Setup data
    $safe_data['gravity_form'] = $gravity_form;
    $safe_data['active_plugins'] = $active_plugins;
    $description = $safe_data['fldDescription'];

    //Email support
    unset($safe_data['flgr_contact'], $safe_data['plugins'], $safe_data['fldFormId'], $safe_data['flgr_AJAX'], $safe_data['fldDescription']);
    $emailer = [
      'to' => 'support@fleek.marketing',
      'subject' => 'A user has reported an issue with Fleek Gravity Form Reporting Plugin',
      'message' => 'User Description: ' . $description . '. Debug details: ' . print_r($safe_data, true),
      'headers' => [
        'Content-Type: text/html; charset=UTF-8',
      ],
    ];
    $response['success'] = wp_mail($emailer['to'], $emailer['subject'], $emailer['message'], $emailer['headers']);

    if(!$response['success']){
      $response['message'] = 'The feedback form was not sent. This form utilises the wp_mail functionality so perhaps your mail server is not setup. We recommend using SMTP if possible.';
    }

    exit(json_encode($response));
  }
}