<?php
namespace Fleek\Gravity\Common;

class Security {
  /**
   * Validates and sanitizes data for a submission
   *
   * @param string $type - Type of submission
   * @param array $unsafe_data - Data to be validated and sanitized
   * @return array
   */
  public static function validate_sanitize_submission($type, $unsafe_data){
    $safe_data = [];

    switch($type){
      case 'flgr_get_answers':
        //Validate data exists
        foreach([
          'fldFormId' => 'Gravity Form field',
          'fldFieldId' => 'Field',
          'fldDateFrom' => 'Date from',
          'fldDateTo' => 'Date to',
        ] as $required_field => $field_name){
          if(empty($unsafe_data[$required_field])){
            exit(json_encode([
              'success' => false,
              'message' => 'Field "' . $field_name . '" is missing',
            ]));
          }
        }

        //Sanitize data
        $safe_data['fldFormId'] = sanitize_text_field($unsafe_data['fldFormId']);
        $safe_data['fldFieldId'] = sanitize_text_field($unsafe_data['fldFieldId']);
        $safe_data['flgr_get_answers'] = sanitize_text_field($unsafe_data['flgr_get_answers']);
        $safe_data['fldDateFrom'] = sanitize_text_field(date('Y-m-d', strtotime($unsafe_data['fldDateFrom'])));
        $safe_data['fldDateTo'] = sanitize_text_field(date('Y-m-d', strtotime($unsafe_data['fldDateTo'])));

        //Validate date are in correct format
        if(
          !preg_match('/^\d{4}-\d{2}-\d{2}$/', $safe_data['fldDateFrom']) 
          || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $safe_data['fldDateTo'])
        ){
          exit(json_encode([
            'success' => false,
            'message' => 'Invalid date format'
          ]));
        }
      break;

      case 'flgr_contact':
        //Validate data exists
        foreach([
          'fldFormId' => 'Gravity Form field',
          'fldDescription' => 'Description',
        ] as $required_field => $field_name){
          if(empty($unsafe_data[$required_field])){
            exit(json_encode([
              'success' => false,
              'message' => 'Field "' . $field_name . '" is missing',
            ]));
          }
        }

        //Sanitize data
        $safe_data['fldFormId'] = sanitize_text_field($unsafe_data['fldFormId']);
        $safe_data['plugins'] ??= map_deep($unsafe_data['plugins'], 'sanitize_text_field');
        $safe_data['fldDescription'] = sanitize_text_field($unsafe_data['fldDescription']);
      break;

      default:
        exit(json_encode([
          'success' => false,
          'message' => 'Invalid submission type'
        ]));
    }
    
    return $safe_data;
  }

  /**
   * Sanitizes an array of data
   * 
   * @param array $unsafe_data
   * @return array
   */
  public static function handle_sanitization_old($unsafe_data){
    $safe_data = [];
    foreach($unsafe_data as $k => $v){
      if(in_array($k, [ //Text fields
        'fldFormId', 
        'fldFieldId',
        'flgr_nonce',
        'flgr_get_answers',
        'flgr_contact',
      ])){
        $safe_data[$k] = sanitize_text_field($v);
      } elseif(in_array($k, [ //Date fields
        'fldDateFrom', 
        'fldDateTo', 
      ])){
        $safe_data[$k] = date('Y-m-d', strtotime($v));
      } elseif(in_array($k, [ //Advanced array fields
        'fldSearchCriteria',
      ])){
        $safe_data[$k] = [];
        foreach($v['field'] as $k2 => $v2){
          $or_condition_field = $v['field'][$k2];
          $or_condition_operator = $v['operator'][$k2];
          $or_condition_value = $v['value'][$k2];

          if(count($or_condition_field) >= 1){
            foreach($or_condition_field as $k3 => $v3){
              $safe_data[$k]['field'][$k2][$k3] = sanitize_text_field($v3);
            }
          }
          if(count($or_condition_operator) >= 1){
            foreach($or_condition_operator as $k3 => $v3){
              $safe_data[$k]['operator'][$k2][$k3] = sanitize_text_field($v3);
            }
          }
          if(count($or_condition_value) >= 1){
            foreach($or_condition_value as $k3 => $v3){
              $safe_data[$k]['value'][$k2][$k3] = sanitize_text_field($v3);
            }
          }
        }
      }
    }

    return $safe_data;
  }
}