<?php
// Add custom Theme Functions here

add_action('admin_head', 'my_custom_css');
function my_custom_css() {
  echo '<style>body.wp-admin #flatsome-notice { display: none!important; }</style>';
}