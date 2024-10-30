<?php

/*
 * Plugin Name: Conversational forms for Gravity Forms - Core
 * Plugin URI: https://gravityconversational.com
 * Description: Show Gravity Forms as Conversational Forms
 * Author: Sushil Kumar
 * Author URI: https://gravityconversational.com
 * Version: 1.4
 * License: GPLv2
 */
// don't load directly
if (! defined('ABSPATH') ) {
    die('-1');
}

// set constants for plugin directory and plugin url
define('GFCF_CORE_DIR', WP_PLUGIN_DIR . '/' . basename(__DIR__));
define('GFCF_CORE_URL', plugins_url() . '/' . basename(__DIR__));
define('GFCF_CORE_VERSION', '1.4');
define('GFCF_CORE_STORE_URL', 'https://gravityconversational.com');


require_once GFCF_CORE_DIR . '/admin-menu/licenses.php';
require_once GFCF_CORE_DIR . '/admin-menu/addons.php';
require_once GFCF_CORE_DIR . '/admin-menu/welcome-page.php';
// Load conversation addon settings
add_action('gform_loaded', array( 'Gfcf_Conversational_Core', 'gfcf_admin_settings' ), 5);

// Allowed Fields
class Gfcf_Conversational_Core
{

    private $allowed_fields = array( 'text', 'number', 'email', 'website', 'phone', 'post_title', 'post_tags', 'quantity', 'post_custom_field', 'name', 'post_image', 'product', 'select', 'post_tags', 'post_custom_field', 'post_category', 'quantity', 'option', 'shipping', 'product', 'checkbox', 'post_tags', 'post_custom_field', 'post_category', 'quantity', 'option', 'consent', 'radio', 'post_tags', 'post_custom_field', 'post_category', 'option', 'shipping', 'product', 'name', 'address', 'multiselect', 'post_tags', 'post_custom_field', 'post_category', 'email', 'date' );
    // private $global_block_attributes = array();

    public function __construct()
    {
        add_action('gform_enqueue_scripts', array( $this, 'add_gravity_css_js' ), 100, 2);

        // add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10 );
        add_action('admin_enqueue_scripts', array( $this, 'add_conversational_css_js' ), 10);

        add_action('wp_enqueue_scripts', array( $this, 'add_conversational_css_js' ), 10);

        add_filter('template_include', array( $this, 'set_template' ), 999);

        add_filter('gform_form_post_get_meta', array( $this, 'modify_gform_field_data' ), 999, 1);

        add_filter('gform_submit_button', array( $this, 'modify_gform_submit_button' ), 10, 2);

        add_filter('gform_next_button', array( $this, 'modify_gform_submit_button' ), 10, 2);

        add_filter('gform_previous_button', array( $this, 'modify_gform_submit_button' ), 10, 2);

        add_filter('gform_progress_bar', array( $this, 'modify_gform_progress_bar' ), 10, 3);

        add_filter('gform_form_args', array( $this, 'modify_form_args' ), 10, 1);

        add_action('init', array( $this, 'create_block_gfcf_core_block_init' ));

        // register custom post type
        add_action('init', array( $this, 'conversational_cpt' ));

        add_action('admin_init', array( $this, 'coversation_admin_init' ));

        add_action('enqueue_block_editor_assets', array( $this, 'sidebar_plugin_script_enqueue' ));

        add_action('init', array( $this, 'sidebar_plugin_register' ));

        apply_filters('query_vars', array( $this, 'add_query_vars' ));

        register_activation_hook(__FILE__, array( $this, 'on_plugin_activate' ));
    }



    public function coversation_admin_init()
    {
        register_setting('permalink', 'gravity_conversation_field_slug');

        add_settings_section('gravity_conversation_field_slug_section', 'Gravity Conversation', array( $this, 'gravity_conversation_slug_section_callback' ), 'permalink');

        add_settings_field(
            'gravity_conversation_field_slug',
            'Conversation Slug',
            array( $this, 'gravity_conversation_setting_field_callback' ),
            'permalink',
            'gravity_conversation_field_slug_section',
        );

        // Sanitize and save the custom field value
        if (isset($_POST['gravity_conversation_field_slug']) ) {

            $slug = sanitize_text_field($_POST['gravity_conversation_field_slug']);

            // Convert spaces to hyphens
            $slug = str_replace(' ', '-', $slug);

            update_option('gravity_conversation_field_slug', $slug);
        }
    }

    public function gravity_conversation_slug_section_callback()
    {
    }

    public function gravity_conversation_setting_field_callback( $args )
    {

        $value = get_option('gravity_conversation_field_slug');

        $value = ! empty($value) ? $value : 'gravityconversational';

        echo '<input name="gravity_conversation_field_slug" id="gravity_conversation_field_slug" type="text" value="' . esc_attr($value) . '" class="regular-text code">';
    }


    public function on_plugin_activate()
    {
        $this->conversational_cpt();
        // Clear the permalinks after the post type has been registered.
        flush_rewrite_rules();
    }

    public function add_query_vars( $qvars )
    {
        $qvars[] = 'gfcf_block_attributes';

        return $qvars;
    }

    public function sidebar_plugin_script_enqueue()
    {
        $post_type = get_post_type();

        // is_admin doesn't work in gutenberg. used REST_REQUEST for this
        if ($post_type !== 'wpmgfcf' ) {
            return;
        }

        wp_enqueue_style('wpm_gfcf_sidebar');
        wp_enqueue_script('wpm_gfcf_sidebar');

        wp_enqueue_style('gfcf_fontawesome_backend', GFCF_CORE_URL . '/css/fontawesome.min.css', array(), GFCF_CORE_VERSION);
    }

    public function sidebar_plugin_register()
    {
        wp_register_style(
            'wpm_gfcf_sidebar',
            GFCF_CORE_URL . '/block/css/sidebar.css'
        );

        wp_register_script(
            'wpm_gfcf_sidebar',
            GFCF_CORE_URL . '/block/js/block.js',
            array( 'wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data' )
        );
    }

    /**
     * Create custom post type
     *
     * @return void
     */
    public function conversational_cpt()
    {

        $rewrite_slug = get_option('gravity_conversation_field_slug');

        $rewrite_slug = empty($rewrite_slug) ? 'gravityconversational' : $rewrite_slug;

        $labels = array(
        'name'               => _x('Gravity Conversational', 'post type general name', 'your-plugin-textdomain'),
        'singular_name'      => _x('Notification', 'post type singular name', 'your-plugin-textdomain'),
        'menu_name'          => _x('Gravity Conversational', 'admin menu', 'your-plugin-textdomain'),
        'name_admin_bar'     => _x('Gravity Conversational', 'add new on admin bar', 'your-plugin-textdomain'),
        'add_new'            => _x('Add New', 'wpmnotifications', 'your-plugin-textdomain'),
        'add_new_item'       => __('Add New Conversational Form', 'your-plugin-textdomain'),
        'new_item'           => __('New Conversational Form', 'your-plugin-textdomain'),
        'edit_item'          => __('Edit Conversational Form', 'your-plugin-textdomain'),
        'view_item'          => __('View Conversational Form', 'your-plugin-textdomain'),
        'all_items'          => __('All Conversational Form', 'your-plugin-textdomain'),
        'search_items'       => __('Search Conversational Form', 'your-plugin-textdomain'),
        'parent_item_colon'  => __('Parent Conversational:', 'your-plugin-textdomain'),
        'not_found'          => __('No Conversational Form found.', 'your-plugin-textdomain'),
        'not_found_in_trash' => __('No Conversational Form found in Trash.', 'your-plugin-textdomain'),
        );

        $args = array(
        'labels'                => $labels,
        'description'           => __('Conversational Gravity Forms.', 'your-plugin-textdomain'),
        'public'                => true,
        'publicly_queryable'    => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'query_var'             => true,
        'rewrite'               => array( 'slug' => $rewrite_slug ),
        'capability_type'       => 'post',
        'has_archive'           => true,
        'hierarchical'          => false,
        'menu_position'         => null,
        'show_in_rest'          => true,
        'rest_controller_class' => 'WP_REST_Posts_Controller',
        'supports'              => array( 'title', 'editor', 'custom-fields' ),
        'template'              => array( array( 'wpm-gfcf/core' ) ),
        'template_lock'         => true,
        );

        register_post_type('wpmgfcf', $args);
    }

    /**
     * Registers the block using the metadata loaded from the `block.json` file.
     * Behind the scenes, it registers also all assets so they can be enqueued
     * through the block editor in the corresponding context.
     *
     * @see https://developer.wordpress.org/reference/functions/register_block_type/
     */

    public function create_block_gfcf_core_block_init()
    {
        register_block_type(
            __DIR__ . '/block/build',
            array(
            'render_callback' => array( $this, 'gfcf_core_block_render_callback' ),
            )
        );
    }

    public function gfcf_core_block_render_callback( $block_attributes, $content )
    {
        $post_type = get_post_type();

        // is_admin doesn't work in gutenberg. used REST_REQUEST for this
        if (is_admin() || defined('REST_REQUEST') || $post_type !== 'wpmgfcf' ) {
            return;
        }

        // var_dump( $content );
        // var_dump($block_attributes);

        // $this->global_block_attributes = $block_attributes;

        $button_font_size      = $block_attributes['gfcfButtonFontSize'];
        $description_font_size = $block_attributes['gfcfDescriptionFontSize'];
        $choices_font_size     = $block_attributes['gfcfChoicesFontSize'];
        $field_font_size       = $block_attributes['gfcfInputFontSize'];
        $sub_label_font_size   = $block_attributes['gfcfSubLabelFontSize'];
        $label_font_size       = $block_attributes['gfcfLabelFontSize'];

        // Tab
        $button_font_size_tab      = $block_attributes['gfcfButtonFontSizeTab'];
        $description_font_size_tab = $block_attributes['gfcfDescriptionFontSizeTab'];
        $choices_font_size_tab     = $block_attributes['gfcfChoicesFontSizeTab'];
        $field_font_size_tab       = $block_attributes['gfcfInputFontSizeTab'];
        $sub_label_font_size_tab   = $block_attributes['gfcfSubLabelFontSizeTab'];
        $label_font_size_tab       = $block_attributes['gfcfLabelFontSizeTab'];

        // Mobile
        $button_font_size_phone      = $block_attributes['gfcfButtonFontSizePhone'];
        $description_font_size_phone = $block_attributes['gfcfDescriptionFontSizePhone'];
        $choices_font_size_phone     = $block_attributes['gfcfChoicesFontSizePhone'];
        $field_font_size_phone       = $block_attributes['gfcfInputFontSizePhone'];
        $sub_label_font_size_phone   = $block_attributes['gfcfSubLabelFontSizePhone'];
        $label_font_size_phone       = $block_attributes['gfcfLabelFontSizePhone'];

        $page_background_color   = $block_attributes['gfcfPageBackgroundColor'];
        $header_text_color       = $block_attributes['gfcfHeaderTextColor'];
        $header_background_color = $block_attributes['gfcfHeaderBackgroundColor'];
        $button_background_color = $block_attributes['gfcfButtonBackgroundColor'];
        $button_color            = $block_attributes['gfcfButtonColor'];
        $progress_color          = $block_attributes['gfcfFilledProgressColor'];
        $input_background_color  = $block_attributes['gfcfInputBackgroundColor'];
        $input_color             = $block_attributes['gfcfInputColor'];
        $input_border_color      = $block_attributes['gfcfInputBorderColor'];
        $field_sublabel_color    = $block_attributes['gfcfSublabelColor'];

        $input_padding  = $block_attributes['gfcfInputPadding'];
        $button_padding = $block_attributes['gfcfButtonPadding'];

        $input_padding_tab  = $block_attributes['gfcfInputPaddingTab'];
        $button_padding_tab = $block_attributes['gfcfButtonPaddingTab'];

        $input_padding_phone  = $block_attributes['gfcfInputPaddingPhone'];
        $button_padding_phone = $block_attributes['gfcfButtonPaddingPhone'];

        $input_animation  = $block_attributes['gfcfInputAnimation'];
        $button_animation = $block_attributes['gfcfButtonAnimation'];

        $heading_font_family = $block_attributes['gfcfHeaderFontFamily'];
        $body_font_family    = $block_attributes['gfcfBodyFontFamily'];

        $conversation_layout = $block_attributes['gfcfConversationLayout'];

        $landing_page_title       = $block_attributes['gfcfLandingTitle'];
        $landing_page_description = $block_attributes['gfcfLandingDescription'];
        $landing_page_url         = $block_attributes['gfcfLandingUrl'];

        $landing_page_btn  = $block_attributes['gfcfLandingBtn'];
        $landing_page_hide = $block_attributes['gfcfLandingHide'];

        $landing_page_fullwidth = $block_attributes['gfcfLandingDesignFullwidth'];

        $landing_page_fullwidth = $landing_page_fullwidth ? 'gfcf-landing-design-fullwidth' : '';

        $theme = $block_attributes['gfcfTheme'];

        $import_header_font_family = trim(str_replace(' ', '+', $heading_font_family));
        $import_body_font_family   = trim(str_replace(' ', '+', $body_font_family));

        echo '
			<style>
				@import url(\'https://fonts.googleapis.com/css?family=' . $import_header_font_family . '\');
				@import url(\'https://fonts.googleapis.com/css?family=' . $import_body_font_family . '\');
				:root {
					--gfcfPageBackgroundColor: ' . $page_background_color . ';
					--gfcfHeaderTextColor: ' . $header_text_color . ';
					--gfcfHeaderBackgroundColor: ' . $header_background_color . ';
					--gfcfButtonBackgroundColor: ' . $button_background_color . ';
					--gfcfButtonColor: ' . $button_color . ';
					--gfcfFilledProgressColor: ' . $progress_color . ';
					--gfcfInputBackgroundColor: ' . $input_background_color . ';
					--gfcfInputBorderColor: ' . $input_border_color . ';
					--gfcfInputColor: ' . $input_color . ';
					--gfcfSublabelColor: ' . $field_sublabel_color . ';

					--gfcfDescriptionFontSize: ' . $description_font_size . ';
					--gfcfInputFontSize: ' . $field_font_size . ';
					--gfcfChoicesFontSize: ' . $choices_font_size . ';
					--gfcfButtonFontSize: ' . $button_font_size . ';
					--gfcfSubLabelFontSize: ' . $sub_label_font_size . ';
					--gfcfLabelFontSize: ' . $label_font_size . ';
					
					--gfcfButtonFontSizeTab: ' . $button_font_size_tab . ';
					--gfcfChoicesFontSizeTab: ' . $choices_font_size_tab . ';
					--gfcfDescriptionFontSizeTab: ' . $description_font_size_tab . ';
					--gfcfInputFontSizeTab: ' . $field_font_size_tab . ';
					--gfcfSubLabelFontSizeTab: ' . $sub_label_font_size_tab . ';
					--gfcfLabelFontSizeTab: ' . $label_font_size_tab . ';

					--gfcfDescriptionFontSizePhone: ' . $description_font_size_phone . ';
					--gfcfInputFontSizePhone: ' . $field_font_size_phone . ';
					--gfcfChoicesFontSizePhone: ' . $choices_font_size_phone . ';
					--gfcfButtonFontSizePhone: ' . $button_font_size_phone . ';
					--gfcfSubLabelFontSizePhone: ' . $sub_label_font_size_phone . ';
					--gfcfLabelFontSizePhone: ' . $label_font_size_phone . ';

					--gfcfInputPadding: ' . $input_padding . ';
					--gfcfButtonPadding: ' . $button_padding . ';
                    
					--gfcfInputPaddingTab: ' . $input_padding_tab . ';
					--gfcfButtonPaddingTab: ' . $button_padding_tab . ';

					--gfcfInputPaddingPhone: ' . $input_padding_phone . ';
					--gfcfButtonPaddingPhone: ' . $button_padding_phone . ';

					--gfcfInputAnimation: ' . $input_animation . ';
					--gfcfButtonAnimation: ' . $button_animation . ';

					--gfcfHeaderFontFamily: ' . $heading_font_family . ';
					--gfcfBodyFontFamily: ' . $body_font_family . ';
					--gfcfTheme: ' . $theme . ';					
				};

				body.layout-default .wp-block-wpm-gfcf-core{
					background: ' . $page_background_color . ';
					min-height: 100vh;
				}

				</style>
				<script>
					let wrapperClassList = document.getElementsByClassName( "wpmgfcf-template-default" )[0].classList;
					
					for( var i=0; i<wrapperClassList.length; i++ ){
						var className = wrapperClassList[i];
						if( className.search( /^layout-/i ) !== -1 ){
							wrapperClassList.remove(className);
						};

					}

					wrapperClassList.add( `layout-' . $conversation_layout . '` );
							


 
				</script>
				';

        $page_html = '';

        if ($block_attributes['gfcfConversationLayout'] !== 'default' ) {
            $page_html = apply_filters('wpm_gfcf_pre_render', $page_html, $content, $block_attributes);
            return $page_html;
        }

        if ($landing_page_hide !== true ) {
            $page_html .= '
					<div class="gfcf-core-landing-page ' . $landing_page_fullwidth . '">
						<div class="gfcf-landing-container ">
							<img src="' . $landing_page_url . '" alt="" class="gfcf-landing-page-logo">
							<h1 class="gfcf-landing-page-title">' . $landing_page_title . '</h1>
							<p class="gfcf-landing-page-description">
								' . $landing_page_description . '
							</p>
							<div class="gfcf-landing-footer">

								<button class="gfcf-landing-page-btn">
								' . $landing_page_btn . '
								</button>
							
								<span class="gfcf-landing-enter-text"> Press Enter ↵</span>

							</div>
						</div>
					</div>';
        }

        $page_html .= do_shortcode($content);

        // $page_html .= '<div class="gfcf-typeform-footer-wrapper animate__animated animate__fadeIn">
        // <div class="gfcf-up-down-btn-container">
        // <span  class="gfcf-up-btn"><i class="fa fa-angle-up"></i></span><span  class="gfcf-down-btn"><i class="fa fa-angle-down"></i></span>
        // </div>
        // </div> ';

        return $page_html;
    }


    public function render_block_core_notice( $attr, $content )
    {
        $post_id   = get_the_ID();
        $post_meta = get_post_meta($post_id);
    }


    public function add_review_page( $review_page, $form, $entry )
    {
        // Enable the review page
        $review_page['is_enabled'] = true;

        if ($entry ) {
            // Populate the review page.
            $review_page['content'] = GFCommon::replace_variables('{all_fields}', $form, $entry);
        }

        return $review_page;
    }

    /**
     * Ajax always enabled for form
     */
    public function modify_form_args( $form_args )
    {
        $post_type = get_post_type();

        // is_admin doesn't work in gutenberg. used REST_REQUEST for this
        if ($post_type !== 'wpmgfcf' ) {
            return $form_args;
        }
        $form_args['ajax'] = true;

        return $form_args;
    }

    /**
     * Modify Progress Bar Classes
     */
    public function modify_gform_progress_bar( $progress_bar, $form, $confirmation_message )
    {
        $post_type = get_post_type();

        // is_admin doesn't work in gutenberg. used REST_REQUEST for this
        if ($post_type !== 'wpmgfcf' ) {
            return $progress_bar;
        }

        // add classes on step title
        $progress_bar = str_replace('class="gf_progressbar_title', 'class="gf_progressbar_title animate__animated animate__fadeInDown ', $progress_bar);

        // add classes on percentage bar
        $progress_bar = str_replace("class='gf_progressbar_percentage", "class='gf_progressbar_percentage animate__animated animate__slideInLeft ", $progress_bar);

        return $progress_bar;
    }
    /**
     * Modify Submit Buttion
     */
    public function modify_gform_submit_button( $button, $form )
    {
        $post_type = get_post_type();

        // is_admin doesn't work in gutenberg. used REST_REQUEST for this
        if ($post_type !== 'wpmgfcf' ) {
            return $button;
        }

        $button = str_replace(" class='", "class='animate__animated animate__fadeInUp animate__delay-1s ", $button);

        // $button = ;

        return $button;
    }

    /**
     * Adds missing IDs to field objects.
     *
     * @since 2.4.6.12
     *
     * @param GF_Field[] $fields
     * @param $next_field_id
     *
     * @return GF_Field[]
     */
    private static function add_missing_ids( $fields, $next_field_id )
    {
        foreach ( $fields as &$field ) {
            if (empty($field->id) ) {
                $field->id = $next_field_id++;
            }
            if (is_array($field->fields) ) {
                $field->fields = $this->add_missing_ids($field->fields, $next_field_id);
            }
        }
        return $fields;
    }

    public function modify_gform_field_data( $form )
    {
        $post_type = get_post_type();

        // is_admin doesn't work in gutenberg. used REST_REQUEST for this
        if ($post_type !== 'wpmgfcf' ) {
            return $form;
        }

        $placeholder_text = array(
        'email'  => 'name@example.com',
        'text'   => 'Type your answer here...',
        'select' => 'Select an option',
        'phone'  => '(101) 555-1234',
        );

        $new_form = $form;

        // always needs description above input.
        $new_form['descriptionPlacement'] = 'above';

        $fields = $this->remove_all_page_breaks($new_form['fields']);

        $form_fields_with_page_break = array();

        // page number.
        $page_number = 1;

        $multi_field_page = false;

        // adding class for animation
        foreach ( $fields as $fieldIndex => $field ) {
            $conditionalLogic = '';

            $add_field_classes = ' gfcf_enabled gfcf_animate_title gfcf_animate_description ';

            // if field has conditional logic add it on page
            if (count($form_fields_with_page_break) > 0 ) {

                $last_field = $form_fields_with_page_break[ count($form_fields_with_page_break) - 1 ];

                if ($last_field->type === 'page' && ! empty($field->conditionalLogic) ) {

                    $conditionalLogic = $field->conditionalLogic;

                    $field->conditionalLogic = '';

                    $last_field->conditionalLogic = $conditionalLogic;
                }
            }

            $form_fields_with_page_break[] = $field;

            // class added to modify html field via CSS becuase it doesn't have any identify selector
            if ($field->type === 'html' ) {
                $add_field_classes .= ' gfcf_html_field ';
            }

            // Class added when desc. is empty so we can design the lable accordingly.
            if (empty($field->description) ) {
                $add_field_classes .= ' gfcf_empty_description ';
            }

            if (empty($field->placeholder) ) {
                // var_dump(in_array( $field->type, $placeholder_text ));
                if (isset($placeholder_text[ $field->type ]) ) {
                    $field->placeholder = $placeholder_text[ $field->type ];
                } else {
                    $field->placeholder = 'Type your answer here...';
                }
            }

            // Class added when enhaced userface is enabled
            if ($field->enableEnhancedUI === 1 || $field->enableEnhancedUI === true ) {
                $add_field_classes .= ' gfcf_enabled_enhanced_interface ';
            }

            $field->descriptionPlacement = 'above';

            switch ( $field->type ) {
            case 'checkbox':
            case 'post_tags':
            case 'post_custom_field':
            case 'post_category':
            case 'quantity':
            case 'option':
            case 'consent':
                // radio
            case 'radio':
            case 'post_tags':
            case 'post_custom_field':
            case 'post_category':
            case 'option':
            case 'shipping':
            case 'product':
                $add_field_classes .= 'gfcf_animate_checked_mark gfcf_animate_unchecked_mark ';
                break;
            case 'address':
                $add_field_classes .= 'gfcf_animate_complex_container';
                break;
            default:
                $add_field_classes .= 'gfcf_animate_container ';
            }

            $field->cssClass .= $add_field_classes;

            // skip page break on last field.
            if ($fieldIndex === count($fields) - 1 ) {
                break;
            }

            // next button because we need to modify it's text in typeform
            // if not created in field don't have the array to modify in form object
            $next_button = array(
            'type'             => 'text',
            'text'             => 'Next Page',
            'imageUrl'         => '',
            'conditionalLogic' => array(),
            );

            $page_break = new GF_Field_Page();

            $page_break->pageNumber = $page_number;
            $page_break->nextButton = $next_button;

            /* Add review page break field to form. */
            if ($field->type === 'hidden' || $field->visibility === 'hidden' || $field->visibility === 'administrative' || $field->inputType === 'hidden' ) {
                continue;
            }

            // skip page break for fields
            $class_to_match   = 'gfcf_combine_fields';
            $next_field_class = '';
            $next_field_index = $fieldIndex + 1;
            $next_field_type  = '';

            // hidden field doesn't have option to add class name. so in case of checking next field we also need to check if it's a hidden field then don't add page break;
            $multi_field_page_not_allowed_fieds = array( 'hidden' );

            $current_field_classes = $field->cssClass;

            if ($next_field_index < count($fields) ) {
                $next_field_classes = $fields[ $next_field_index ]->cssClass;

                $next_field_type = $fields[ $next_field_index ]->type;
            }

            // when field type is section and next field has the matching class then don't show the page break.
            if ($field->type === 'section' && ! is_admin() && ! defined('REST_REQUEST') ) {

                if (in_array($next_field_type, $multi_field_page_not_allowed_fieds) || ( ! empty($next_field_classes) && strpos($next_field_classes, $class_to_match) !== false ) ) {
                    $multi_field_page = true;
                    continue;
                }
            }

            // when current field doesn't have any class then don't make it multi page.
            if (empty($current_field_classes) || strpos($current_field_classes, $class_to_match) === false ) {
                $multi_field_page = false;
            }

            // when page is multi field and next field has the class then don't add page break;

            if ($multi_field_page && ! empty($next_field_classes) && strpos($next_field_classes, $class_to_match) !== false ) {
                continue;
            }

            // when page is multi field and next field is hidden field then don't add page break;
            if ($multi_field_page && in_array($next_field_type, $multi_field_page_not_allowed_fieds) ) {
                continue;
            }

            $previous_field_classes = '';

            if ($fieldIndex > 0 ) {
                $previous_field_classes = $fields[ $fieldIndex - 1 ]->cssClass;
            }

            $form_fields_with_page_break[] = $page_break;

            ++$page_number;
        }

        $new_form['fields'] = $form_fields_with_page_break;
        // add missing but required properties to newly added fields
        $new_form = GFFormsModel::convert_field_objects($new_form);

        $next_field_id = GFFormsModel::get_next_field_id($new_form['fields']);

        $new_form['fields']              = $this->add_missing_ids($new_form['fields'], $next_field_id);
        $new_form['pagination']['type']  = 'percentage';
        $new_form['pagination']['style'] = 'blue';

        $new_form['lastPageButton'] = array(
        'type' => 'text',
        'text' => 'Previous',
        );

        return $new_form;
    }

    /**
     * Add settings in Gravity forms.
     */
    public static function gfcf_admin_settings()
    {
        if (! method_exists('GFForms', 'include_addon_framework') ) {
            return;
        }

        include_once GFCF_CORE_DIR . '/includes/admin-settings.php';

        GFAddOn::register('GFCFConversationalCore');
    }

    /**
     * Remove page break.
     */
    public function remove_all_page_breaks( $fields )
    {
        $fields_without_page_break = array();
        foreach ( $fields as $field ) {
            if ($field->type !== 'page' ) {
                $fields_without_page_break[] = $field;
            }
        }
        return $fields_without_page_break;
    }

    public function add_gravity_css_js( $form, $is_ajax )
    {
        $post_type = get_post_type();

        if ($post_type !== 'wpmgfcf' ) {
            return;
        }

        wp_enqueue_script('gfcf_template_js', GFCF_CORE_URL . '/js/template.js', array(), GFCF_CORE_VERSION);

        wp_enqueue_style('gfcf_public_css', GFCF_CORE_URL . '/css/public.css', array( 'gfcf_variable_css' ), GFCF_CORE_VERSION);
    }

    public function add_conversational_css_js()
    {
        $post_type = get_post_type();

        if ($post_type !== 'wpmgfcf' ) {
            return;
        }

        wp_enqueue_style('gfcf_variable_css', GFCF_CORE_URL . '/css/variables.css', array(), GFCF_CORE_VERSION, true);

        wp_enqueue_style('gfcf_animate_css', GFCF_CORE_URL . '/css/animate.css', array(), GFCF_CORE_VERSION);

        wp_enqueue_style('gfcf_fontawesome_css', GFCF_CORE_URL . '/css/fontawesome/fontawesome.min.css', array(), GFCF_CORE_VERSION);
    }


    public function set_template( $template )
    {
        $post_type = get_post_type();

        // is_admin doesn't work in gutenberg. used REST_REQUEST for this
        if (is_admin() || defined('REST_REQUEST') || ! class_exists('GFAPI') || $post_type !== 'wpmgfcf' ) {
            return $template;
        }

        // ob_start();
        $template = GFCF_CORE_DIR . '/includes/frontend-template.php';
        // $template = ob_get_clean();

        return $template;
    }
} // class ends here

// add_action( 'plugins_loaded', 'gfcf_conversational_core_callback');

function gfcf_conversational_core_callback()
{
    new Gfcf_Conversational_Core();
}

new Gfcf_Conversational_Core();
