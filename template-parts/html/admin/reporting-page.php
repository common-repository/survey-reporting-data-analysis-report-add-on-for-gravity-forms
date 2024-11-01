<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div id="fleek-reporting-plugin" data-flgr-reporting-plugin>
  <div class="flgr-plugin-container">
    <noscript>
      <div class="flgr-w-100 flgr-panel flgr-bg-red flgr-box">
        <p>This plugin will not function without javascript. Please enable it on your browser to use this plugin.</p>
      </div>
    </noscript>
    
    <div class="flgr-w-100 flgr-panel flgr-bg-light-blue flgr-box">
      <p>This plugin is still in development. Should you notice any issues then please <a href="#" class="flgr-link" data-modal-popup="contact-us-modal">reach out to us</a></p>
    </div>
    <div class="flgr-w-100 flgr-panel flgr-bg-white">
<?php if(empty($gravity_forms)): ?>
        <header class="flgr-settings-header">
          <h2>Report Criteria</h2>
        </header>

        <hr class="flgr-hr" />
        
        <div class="flgr-box">
          <p>There are currently no forms created, why not <a href="<?php echo esc_url(admin_url('admin.php?page=gf_edit_forms')); ?>">create one now</a></p>
        </div>
<?php  return false; ?>
<?php endif; ?>

      <script type='text/javascript'>
        const flgr_form_data = <?php echo wp_json_encode($gravity_forms); ?>;
      </script>

      <form action="" method="post" data-flgr-ajax-event="report_results">
        <?php wp_nonce_field('flgr_report_results','flgr_report_results_nonce');?>

        <header class="flgr-settings-header">
          <h2>Report Criteria</h2>
        </header>
        
        <hr class="flgr-hr" />
        
        <section class="flgr-settings-content">
          <div class="flgr-box flgr-box-2">
            <div class="flgr-flex flgr-flex-space-between">
              <div class="flgr-form-input">
                <label for="fldDateFrom">Date From:</label>
                <input type="date" name="fldDateFrom" id="fldDateFrom" value="<?php echo esc_html(date('Y-m-d', strtotime('-3 months'))); ?>" />
              </div>
              
              <div class="flgr-form-input">
                <label for="fldDateFrom">Date To:</label>
                <input type="date" name="fldDateTo" id="fldDateTo" max="<?php echo esc_html(date('Y-m-d')); ?>" value="<?php echo esc_html(date('Y-m-d')); ?>" />
              </div>
            </div>
          </div>

          <hr class="flgr-hr" />

          <div class="flgr-box" data-form-select style="display: none;">
            <label>Form:</label>
            <select name="fldFormId" data-flgr-form-select>
              <option value="">Please select the form</option>
<?php foreach($gravity_forms as $k => $v): ?>
              <option value="<?php echo esc_attr($v['id']); ?>"><?php echo esc_html($v['title']); ?></option>
<?php endforeach; ?>
            </select>
          </div>

          <hr class="flgr-hr" />
          
          <?php do_action('flgr_reporting_form_before_submit'); ?>
        </section>
  
        <footer class="flgr-settings-footer" data-flgr-toggle-display="settings-footer" style="display: none;">
          <input type="submit" value="Generate Report" disabled data-flgr-ajax-submit="generate_report" />
        </footer>
      </form>
    </div>

    <div class="flgr-w-100 flgr-panel flgr-bg-white" data-flgr-toggle-display="results" style="display: none;">
      <header class="flgr-settings-header flgr-flex flgr-flex-space-between">
        <h2>Gravity Forms Report Results</h2>

        <div class="fglr-actions">
          <?php do_action('flgr_reporting_report_result_actions'); ?>
        </div>
      </header>

      <?php do_action('flgr_reporting_report_result_after_header'); ?>
        
      <section class="flgr-settings-content">
        <div data-flgr-ajax-content="report_results"></div>
      </section>
    </div>
  </div>

  <div id="contact-us-modal" class="modal">
    <div class="modal-content">
      <span class="close-modal-btn" data-close-modal>&times;</span>
      
      <div class="flgr-w-100 flgr-panel flgr-bg-light-blue flgr-box" data-contact-us-modal-content>
        <h2>Contact Us</h2>

        <form action="" method="post">
          <input type="hidden" name="flgr_contact" value="1" />
          <input type="hidden" name="flgr_field_type" value="" data-hidden-field-type />
          <?php wp_nonce_field('flgr_contact','flgr_contact_nonce');?>

          <div class="flgr-form-input">
            <label>Gravity Form</label>
            <p class="flgr-helper-text">Please select the gravity form this issue is happening on (we will collect field data to replicate your setup)</p>
            <select name="fldFormId">
              <option value="">Please select the form</option>
<?php foreach($gravity_forms as $k => $v): ?>
              <option value="<?php echo esc_attr($v['id']); ?>"><?php echo esc_html($v['title']); ?></option>
<?php endforeach; ?>
            </select>
          </div>

          <div class="flgr-form-input">
            <label>Active Gravity Form Plugins</label>
            <p class="flgr-helper-text">Please confirm your active plugins (we will collect plugin data to replicate your setup)</p>
<?php foreach($plugins as $k => $v){ ?>
            <label>
              <input type="checkbox" name="plugins[]" value="<?php echo esc_attr($k); ?>"<?php echo (is_plugin_active($k) ? ' checked="checked"' : ''); ?> />
              <?php echo esc_html($v['Name']); ?>
            </label>
<?php } ?>
          </div>

          <div class="flgr-form-input">
            <label>Description</label>
            <p class="flgr-helper-text">Please provide a brief description of the issue</p>
            <textarea name="fldDescription" cols="30" rows="10" placeholder="E.g. After generating the report one of the fields return the value 'This field has responded with an unsuccessful response'"></textarea>
          </div>

          <div class="flgr-form-input">
            <label>Additional information we will collect</label>
            <p class="flgr-helper-text">We do not collect any personal data</p>

            <p>
              <strong>User agent:</strong>
              <span data-flgr-additional-info="user_agent"></span>
              <input type="hidden" name="user_agent" data-flgr-additional-input-info="user_agent" />
            </p>
            <input type="submit" value="Submit" data-flgr-ajax-submit="contact_us" />
          </div>
        </form>
      </div>
    </div>
  </div>
</div> <!-- #fleek-reporting-plugin -->