<?php

function wpsmm_settings_init() {
    register_setting('wpsmm', 'wpsmm_options');
    add_settings_section(
        'wpsmm_section_main',
        __('Store User Sessions in Memcached?', 'wpsmm'),
        'wpsmm_section_main_cb',
        'wpsmm'
    );
    add_settings_field('wpsmm_field_active',
        __('Use Memcached', 'wpsmm'),
        'wpsmm_add_checkbox',
        'wpsmm',
        'wpsmm_section_main', [
            'label_for' => 'wpsmm_field_active',
            'class' => 'wpsmm_row',
        ]
    );
    add_settings_field('wpsmm_field_server',
        __('Memcached Server', 'wpsmm'),
        'wpsmm_add_text',
        'wpsmm',
        'wpsmm_section_main', [
            'label_for' => 'wpsmm_field_server',
            'class' => 'wpsmm_row',
            'placeholder' => 'localhost',
        ]
    );
    add_settings_field('wpsmm_field_port',
        __('Memcached Port', 'wpsmm'),
        'wpsmm_add_text',
        'wpsmm',
        'wpsmm_section_main', [
            'label_for' => 'wpsmm_field_port',
            'class' => 'wpsmm_row',
            'placeholder' => '11211',
        ]
    );
}
 
add_action('admin_init', 'wpsmm_settings_init');
 
function wpsmm_section_main_cb($args) {
    ?><p id="<?php echo esc_attr($args['id']); ?>"><?php echo esc_html__('Sessions are stored in wp_options table by default.', 'wpsmm'); ?></p><?php
}
function wpsmm_add_checkbox($args) {
	$options = get_option('wpsmm_options');
	?><input type="checkbox" id="<?php echo esc_attr($args['label_for']); ?>" 
		name="wpsmm_options[<?php echo esc_attr($args['label_for']); ?>]" 
		value="1" <?php echo (empty($options[$args['label_for']])) ? '' : 'checked'; ?>><?php
}
function wpsmm_add_text($args) {
	$options = get_option('wpsmm_options');
	?><input type="text" id="<?php echo esc_attr($args['label_for']); ?>" 
		name="wpsmm_options[<?php echo esc_attr($args['label_for']); ?>]" 
		placeholder="<?php echo esc_attr($args['placeholder']); ?>" 
		value="<?php echo esc_attr($options[$args['label_for']]); ?>"
		><?php
}
 
function wpsmm_options_page() {
    add_options_page(
		'Session Management',
		'Session Management',
		'manage_options',
		'wpsmm',
		'wpsmm_options_page_html'
    );
}
 
add_action('admin_menu', 'wpsmm_options_page');
 
function wpsmm_options_page_html() {
    if (!current_user_can('manage_options')) { return; }
    if (isset($_GET['settings-updated'])) {
		$options = get_option('wpsmm_options');
		if (!empty($options['wpsmm_field_active'])) {
			if (!empty($options['wpsmm_field_server']) && !empty($options['wpsmm_field_port'])) {
				if (!class_exists('Memcached')) {
					wpsmm_admin_notice('Memcached PHP extension not installed. Memcached will not be used.', 'error');
				} else {
					wpsmm_admin_notice('Memcached storage activated!', 'success');
				}
			} else {
				wpsmm_admin_notice('Memcached server and host not specified. Memcached will not be used.', 'error');
			}
		} else {
			wpsmm_admin_notice('Memcached storage activated!', 'success');
		}
    }
    ?><div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php

            settings_fields('wpsmm');
            do_settings_sections('wpsmm');
            submit_button('Save Settings');

            ?>
        </form>
    </div><?php
}

function wpsmm_admin_notice($message, $type='error') {
	?><div class="notice notice-<?php echo $type; ?> is-dismissible">
	  <p><?php echo __($message, 'wpsmm'); ?></p>
	</div><?php
}

?>