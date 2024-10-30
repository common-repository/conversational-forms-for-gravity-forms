<?php

class Gfcf_Core_License_Page{

	function __construct(){
		add_action('admin_menu',array($this,'register_menu') );
		add_action( 'admin_init', array( $this, 'setting_fields' ) );
	}

	public function register_menu(){

		add_submenu_page( 'edit.php?post_type=wpmgfcf', 'Licenses', 'Licenses', 'manage_options', 'wpm_gravity_conversational_licenses', array( $this, 'license_settings' ) );
	}

	public function license_settings(){

		?>
			<!-- Create a header in the default WordPress 'wrap' container -->
    <div class="wrap">

        <!-- Make a call to the WordPress function for rendering errors when settings are saved. -->
        <?php settings_errors(); ?>
        <!-- Create the form that will be used to render our options -->
        <form method="post" action="options.php">
            <?php settings_fields( 'wpm_gravity_conversational_licenses' ); ?>
            <?php do_settings_sections( 'wpm_gravity_conversational_licenses' ); ?>
            <?php submit_button(); ?>
        </form>

    </div><!-- /.wrap -->
	<?php
	}


	function setting_fields(){
		// If settings don't exist, create them.
		if ( false == get_option( 'wpm_gravity_conversational_licenses' ) ) {
			add_option( 'wpm_gravity_conversational_licenses' );
		}


		add_settings_section(
			'wpm_gravity_conversational_licenses_section',
			'Add-On Licenses',
			array( $this, 'section_callback' ),
			'wpm_gravity_conversational_licenses'
		);

		do_action('wpm_gravity_conversational_license_fields',$this);

		//register settings
		register_setting( 'wpm_gravity_conversational_licenses', 'wpm_gravity_conversational_licenses' );

	}

	public function section_callback() {

		echo '<h4> Licence Fields will automatically appear once you install addons for \'Conversational Forms for Gravity Forms\'. You can check all the available addons <a href="https://gravityconversational.com/downloads/addon-bundle/?utm_source=dashboard&utm_medium=licence-page&utm_campaign=styles_layout_plugin">here</a></h4>';
	}

}

new Gfcf_Core_License_Page();