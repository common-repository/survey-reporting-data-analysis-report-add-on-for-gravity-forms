<?php
namespace Fleek\Gravity\GravityForm;

use DateTime;
use GF_Query;
use GF_Query_Condition;
use GF_Query_Column;
use GF_Query_Literal;

class Data {
  public static $errors = [];

  /**
   * Returns a Gravity Form using the GFAPI
   *
   * @param [int] $form_id - The ID of the form
   * @return mixed The form meta array or false
   */
  public static function get_report_form($form_id){
    if(
      empty($form_id) ||
      !\Fleek\Gravity\Common\Setup::check_gravity_forms_plugin_active()
    ){
      return false;
    }

    return \GFAPI::get_form($form_id);
  }

  /**
   * This function will retrieve the data output for an answer via AJAX.
   *
   * @param array $args - Required data expected: fldDateFrom(yyyy-mm-dd),fldDateTo(yyyy-mm-dd),fldFormId,fldFieldId. Optional data: fldSearchCriteria,query_and_fields
   * @return array
   */
  public static function get_answer_data_within_viewport($args){
    $response = [];
    $limit = 100;

    //Init vars
    $gf_query = new GF_Query(
      $args['form']['id'],
      null,
      null,
      [
        'page_size' => $limit
      ]
    );
    $query_and_fields = [];
    $query_or_fields = [];
    $query_operators = [
      '=' => GF_Query_Condition::EQ,
      '!=' => GF_Query_Condition::NEQ,
      'CONTAINS' => GF_Query_Condition::LIKE,
      '>' => GF_Query_Condition::GT,
      '<' => GF_Query_Condition::LT,
    ];

    //Default filters
    if(empty($args['query_and_fields'])){
      $start_date = new DateTime($args['fldDateFrom']);
      $query_and_fields[] = new \GF_Query_Condition(
        new GF_Query_Column('date_created'),
        GF_Query_Condition::GTE,
        new GF_Query_Literal(get_gmt_from_date($start_date->format('Y-m-d') . ' 00:00:00'))
      );
      $end_date = new DateTime($args['fldDateTo']);
      $query_and_fields[] = new \GF_Query_Condition(
        new GF_Query_Column('date_created'),
        GF_Query_Condition::LTE,
        new GF_Query_Literal(get_gmt_from_date($end_date->format('Y-m-d') . ' 23:59:59'))
      );
      $query_and_fields[] = new \GF_Query_Condition(
        new GF_Query_Column('status'),
        $query_operators['='],
        new GF_Query_Literal('active')
      );
    } else {
      $query_and_fields = $args['query_and_fields'];
    }
    
    //Go through all the fields to find the current field requested via AJAX, then apply the filters and retrieve the result
    foreach($args['form']['fields'] as $k => $field){
      if($field['id'] != $args['fldFieldId']){
        continue;
      }

      $gf_query->limit($limit); //Reset the limit to the intiial limit

      // $total_count = 0;
      switch($field['type']){
        default:
          $response['html'] = 'This field is not yet supported. If this is a standard form field then it will likely be added soon. However, if you know this is an additional field from a plugin then please feel free to <a href="#" class="flgr-link" data-modal-popup="contact-us-modal" data-hidden-field-type="' . $field['type'] . '">contact the plugin developer</a> and request the field type: ' . $field['type'] . ' to be added. Thank you for using this plugin!';
        break;

        case "text":
        case "textarea":
        case "email":
        case "time":
        case "phone":
        case "fileupload":
        case "date":
        case "website":
        case "list":
        case "multiselect":
          //Filter to ensure this field has a value
          $query_and_fields[] = new \GF_Query_Condition(
            new GF_Query_Column($field['id']),
            $query_operators['!='],
            new GF_Query_Literal(""),
          );
          $query_and_fields = call_user_func_array(['\GF_Query_Condition', '_and'], $query_and_fields);
          $gf_query->where($query_and_fields);
          $entries = $gf_query->get();

          if(empty($entries)){
            $response['no_entries'] = true;
            break;
          }

          ob_start();
          include(FLEEK_GRAVITY_PLUGIN_DIR . '/template-parts/html/ajax/snippets/report-answer-free-text.php');
          $response['html'] = ob_get_clean();

          $return_entries = [];
          foreach($entries as $k => $entry){
            $return_entries[$entry['id']] = $entry[$field['id']];
          }
        break;
  
        case "hidden":
        case "number":
        case "select":
        case "radio":
          $gf_query->limit(10000); //Set the limit much higher since it has to loop through entries and add them up - will work up to 10,000 rows and then put 1,000+ for every over 1000
          //Filter to ensure this field has a value
          $query_and_fields[] = new \GF_Query_Condition(
            new GF_Query_Column($field['id']),
            $query_operators['!='],
            new GF_Query_Literal(""),
          );
          $query_and_fields = call_user_func_array(['\GF_Query_Condition', '_and'], $query_and_fields);
          $gf_query->where($query_and_fields);
          $entries = $gf_query->get();

          $counts = [];
          foreach($entries as $k => $entry){
            $answer = $entry[$field['id']];
            $return_entries[$entry['id']] = $answer;
            $counts[$answer] = (!isset($counts[$answer]) ? 1 : $counts[$answer]+1);
            if($counts[$answer] > 1000){
              $counts[$answer] = '1,000+';
            }
          }

          if(empty($counts)){
            $response['no_entries'] = true;
          }

          ob_start();
          include(FLEEK_GRAVITY_PLUGIN_DIR . '/template-parts/html/ajax/snippets/report-answer-counter.php');
          $response['html'] = ob_get_clean();
        break;

        case "name":
        case "address":
          //Filter to ensure this field has a value
          $name_query_or_fields = [];
          foreach($field['inputs'] as $input){
            if(!empty($input['isHidden'])) continue;
            $name_query_or_fields[] = new \GF_Query_Condition(
              new GF_Query_Column($input['id']),
              $query_operators['!='],
              new GF_Query_Literal(""),
            );
          }
          $query_and_fields[] = call_user_func_array(['\GF_Query_Condition', '_or'], $name_query_or_fields);
          $gf_query->where(call_user_func_array(['\GF_Query_Condition', '_and'], $query_and_fields));
          $entries = $gf_query->get();

          if(empty($entries)){
            $response['no_entries'] = true;
            break;
          }

          foreach($entries as $k => $entry){
            foreach($field['inputs'] as $input){
              if(!empty($entry[$input['id']])){
                $entries[$k][$field['id']][] = $entry[$input['id']];
              }
            }
          }

          $implode = ' ';
          if($field['type'] == "address"){
            $implode = '<br>';
          }

          ob_start();
          include(FLEEK_GRAVITY_PLUGIN_DIR . '/template-parts/html/ajax/snippets/report-answer-gform-selection-text.php');
          $response['html'] = ob_get_clean();
        break;

        case "checkbox":
          $counts = [];
          //Filter to go through each value and count how many of each value is found
          foreach($field['inputs'] as $input){
            $checkbox_query_and_fields = $query_and_fields;
            $checkbox_query_and_fields[] = new \GF_Query_Condition(
              new GF_Query_Column($input['id']),
              $query_operators['='],
              new GF_Query_Literal($input['label']),
            );

            $gf_query->where(call_user_func_array(['\GF_Query_Condition', '_and'], $checkbox_query_and_fields));
            $entries = $gf_query->get();
            if($gf_query->total_found > 0){
              $counts[$input['label']] = $gf_query->total_found;
            }
            foreach($entries as $k => $entry){
              $return_entries[$entry['id']][] = $entry[$input['id']];
            }
          }

          if(empty($counts)){
            $response['no_entries'] = true;
          }
          
          ob_start();
          include(FLEEK_GRAVITY_PLUGIN_DIR . '/template-parts/html/ajax/snippets/report-answer-counter.php');
          $response['html'] = ob_get_clean();
        break;
      }
    }

    if(!empty($response['no_entries'])){
      $response['html'] = '<p>No entries match this criteria</p>';
      $response['success'] = true;
    } else if(!empty($response['html'])){
      $response['success'] = true;
    }

    return $response;
  }
}