<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="flgr-flex">
<?php
foreach($counts as $answer => $count){
?>
  <div class="flgr-multi-choice-answer">
    <p>
      <span class="flgr-count"><?php echo esc_html(is_int($count) ? number_format($count, 0, '', ',') : $count); ?></span>
      <?php echo esc_html($answer); ?>
    </p>
  </div>
<?php
}
?>
</div>