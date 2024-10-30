<?php

class GFCF_Core_Addons_Page
{
    public function __construct()
    {
        add_action('admin_menu', array( $this, 'register_menu' ));
    }

    public function register_menu()
    {

        add_submenu_page('edit.php?post_type=wpmgfcf', 'Add-Ons', 'Add-Ons', 'manage_options', 'wpm-gravity-conversational-addons', array( $this, 'show_addons' ));
        add_action('admin_enqueue_scripts', array( $this, 'add_scripts' ));


    }

    public function add_scripts()
    {
        wp_enqueue_style('wpm_gfcf_sidebar_admin_css', GFCF_CORE_URL.'/admin-menu/css/admin.css', '', GFCF_CORE_VERSION);
    }

    public function show_addons()
    {

        ?>
		 <div class="gfcf-wrapper">
				
			<div class="gfcf-container">
				<div class="gfcf-section">
					<div class="gfcf-section-heading">
						<h1 class="gfcf-heading-text"> Conversational Addons </h1>
						<p>Use these addons to extend the functionality of Conversational forms for Gravity Forms</p>
					</div>
					<div class="gfcf-row">
						<div class="gfcf-col-6">
							<div class="gfcf-add-on-blurb">
								<a href="https://gravityconversational.com/downloads/gravity-conversational-forms-styler/" target="_blank">

									<img src="<?php echo GFCF_CORE_URL ?>/admin-menu/images/conversational-styler.png" alt="">
								</a>
								<div class="gfcf-blurb-content">

								</div>
							</div>
						</div>
						<div class="gfcf-col-6">
							<div class="gfcf-add-on-blurb">
								<a href="https://gravityconversational.com/downloads/gravity-conversational-pro/" target="_blank">

									<img src="<?php echo GFCF_CORE_URL ?>/admin-menu/images/conversational-pro.png" alt="">
								</a>
								<div class="gfcf-blurb-content">

								</div>
							</div>
						</div>
						
					</div>
				</div>
			</div>

		 </div>
	<?php
    }



}

add_action('plugins_loaded', 'gfcf_addons_page');

function gfcf_addons_page()
{
    new GFCF_Core_Addons_Page();

}
