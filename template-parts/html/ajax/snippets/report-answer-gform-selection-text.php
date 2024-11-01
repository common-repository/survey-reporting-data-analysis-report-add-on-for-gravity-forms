<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="flgr-multiple-free-text-answers">
<?php 
foreach($entries as $key => $entry){ 
  $answer = $entry[$field['id']];
?>
  <div class="flgr-free-text-answer" data-entry-id="<?php echo esc_attr($entry['id']); ?>">
    <?php foreach($field['inputs'] as $input){ 
            if(!empty($input['isHidden'])) continue;
    ?>
      <span class="flgr-free-text-answer-label"><?php echo esc_html($input['label']); ?>:</span>
      <?php echo esc_html($entry[$input['id']]); ?>
      <br />
    <?php } ?>
    
    <a href="<?php echo esc_url(admin_url('admin.php?page=gf_entries&view=entry&id=' . $field['formId'] . '&lid=' . $entry['id'])); ?>" class="flgr-link-to-entry" target="_blank"><span class="dashicons dashicons-admin-site"></span></a>
  </div>
<?php 
} 
?>
</div>