<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="flgr-multiple-free-text-answers">
<?php 
foreach($entries as $key => $entry){ 
  $answer = $entry[$field['id']];

  switch($field['type']){
    default:
?>
  <div class="flgr-free-text-answer flgr-free-text-type-<?php echo esc_attr($field['type']); ?>" data-entry-id="<?php echo esc_attr($entry['id']); ?>">
    <?php echo esc_html($answer); ?>
    <a href="<?php echo esc_url(admin_url('admin.php?page=gf_entries&view=entry&id=' . $field['formId'] . '&lid=' . $entry['id'])); ?>" class="flgr-link-to-entry" target="_blank"><span class="dashicons dashicons-admin-site"></span></a>
  </div>
<?php
    break;

    case 'fileupload':
    case 'website':
?>
  <div class="flgr-free-text-answer flgr-free-text-type-<?php echo esc_attr($field['type']); ?>" data-entry-id="<?php echo esc_attr($entry['id']); ?>">
    <a href="<?php echo esc_url($answer); ?>" target="_blank"><?php echo esc_url($answer); ?></a>
    <a href="<?php echo esc_url(admin_url('admin.php?page=gf_entries&view=entry&id=' . $field['formId'] . '&lid=' . $entry['id'])); ?>" class="flgr-link-to-entry" target="_blank"><span class="dashicons dashicons-admin-site"></span></a>
  </div>
<?php
    break;

    case 'email':
?>
  <div class="flgr-free-text-answer flgr-free-text-type-<?php echo esc_attr($field['type']); ?>" data-entry-id="<?php echo esc_attr($entry['id']); ?>">
    <a href="mailto:<?php echo esc_html($answer); ?>"><?php echo esc_html($answer); ?></a>
    <a href="<?php echo esc_url(admin_url('admin.php?page=gf_entries&view=entry&id=' . $field['formId'] . '&lid=' . $entry['id'])); ?>" class="flgr-link-to-entry" target="_blank"><span class="dashicons dashicons-admin-site"></span></a>
  </div>
<?php
    break;

    case 'phone':
?>
  <div class="flgr-free-text-answer flgr-free-text-type-<?php echo esc_attr($field['type']); ?>" data-entry-id="<?php echo esc_attr($entry['id']); ?>">
    <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $answer)); ?>"><?php echo esc_html($answer); ?></a>
    <a href="<?php echo esc_url(admin_url('admin.php?page=gf_entries&view=entry&id=' . $field['formId'] . '&lid=' . $entry['id'])); ?>" class="flgr-link-to-entry" target="_blank"><span class="dashicons dashicons-admin-site"></span></a>
  </div>
<?php
    break;

    case 'date':
?>
  <div class="flgr-free-text-answer flgr-free-text-type-<?php echo esc_attr($field['type']); ?>" data-entry-id="<?php echo esc_attr($entry['id']); ?>">
    <?php echo esc_html(date(get_option('date_format'), strtotime($answer))); ?>
    <a href="<?php echo esc_url(admin_url('admin.php?page=gf_entries&view=entry&id=' . $field['formId'] . '&lid=' . $entry['id'])); ?>" class="flgr-link-to-entry" target="_blank"><span class="dashicons dashicons-admin-site"></span></a>
  </div>
<?php
    break;

    case 'time':
?>
  <div class="flgr-free-text-answer flgr-free-text-type-<?php echo esc_attr($field['type']); ?>" data-entry-id="<?php echo esc_attr($entry['id']); ?>">
    <?php echo esc_html(date(get_option('time_format'), strtotime($answer))); ?>
    <a href="<?php echo esc_url(admin_url('admin.php?page=gf_entries&view=entry&id=' . $field['formId'] . '&lid=' . $entry['id'])); ?>" class="flgr-link-to-entry" target="_blank"><span class="dashicons dashicons-admin-site"></span></a>
  </div>
<?php
    break;

    case 'list':
      $answer = unserialize($answer);
      if(is_array($answer[0])){
        $headers = [];
        foreach($answer as $k => $row){
          foreach($row as $column => $value){
            $headers[$column] = $column;
          }
        }
?>
  <div class="flgr-free-text-answer flgr-free-text-type-<?php echo esc_attr($field['type']); ?>" data-entry-id="<?php echo esc_attr($entry['id']); ?>">
    <table>
      <thead>
        <tr>
<?php   foreach($headers as $header){ ?>
          <th><?php echo esc_html($header); ?></th>
<?php } ?>
        </tr>
      </thead>
      <tbody>
<?php
        foreach($answer as $k => $row){
?>
        <tr>   
<?php
          foreach($row as $column => $value){
?>
          <td><?php echo esc_html($value); ?></td>
<?php
          }
?>
        </tr>
<?php
        }
?>
      </tbody>
    </table>
    <a href="<?php echo esc_url(admin_url('admin.php?page=gf_entries&view=entry&id=' . $field['formId'] . '&lid=' . $entry['id'])); ?>" class="flgr-link-to-entry" target="_blank"><span class="dashicons dashicons-admin-site"></span></a>
  </div>
<?php

      } else {
?>
  <div class="flgr-free-text-answer flgr-free-text-type-<?php echo esc_attr($field['type']); ?>" data-entry-id="<?php echo esc_attr($entry['id']); ?>">
    <?php echo wp_kses_post(implode('<br>', $answer)); ?>
    <a href="<?php echo esc_url(admin_url('admin.php?page=gf_entries&view=entry&id=' . $field['formId'] . '&lid=' . $entry['id'])); ?>" class="flgr-link-to-entry" target="_blank"><span class="dashicons dashicons-admin-site"></span></a>
  </div>
<?php
      }
    break;

    case 'multiselect':
      $answer = json_decode($answer);
?>
  <div class="flgr-free-text-answer flgr-free-text-type-<?php echo esc_attr($field['type']); ?>" data-entry-id="<?php echo esc_attr($entry['id']); ?>">
    <?php echo wp_kses_post(implode('<br>', $answer)); ?>
    <a href="<?php echo esc_url(admin_url('admin.php?page=gf_entries&view=entry&id=' . $field['formId'] . '&lid=' . $entry['id'])); ?>" class="flgr-link-to-entry" target="_blank"><span class="dashicons dashicons-admin-site"></span></a>
  </div>
<?php
    break;
  }
}
?>
</div>