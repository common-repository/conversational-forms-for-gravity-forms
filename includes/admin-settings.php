<?php

GFForms::include_addon_framework();

class GFCFConversationalCore extends GFAddOn {

	protected $_version = GFCF_CORE_VERSION;
	protected $_min_gravityforms_version = '1.9';
	protected $_slug = 'gfcfconversational';
	protected $_path = 'gfcfconversational/gfcfconversational.php';
	protected $_full_path = __FILE__;
	protected $_title = 'Conversational Form Settings';
	protected $_short_title = 'Conversational Form';

	private static $_instance = null;

	/**
	 * Get an instance of this class.
	 *
	 * @return GFSimpleAddOn
	 */
	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new GFCFConversationalCore();
		}

		return self::$_instance;
	}

	/**
	 * Handles hooks and loading of language files.
	 */
	public function init() {
		parent::init();
		// add_filter( 'gform_submit_button', array( $this, 'form_submit_button' ), 10, 2 );
		// add_action( 'gform_after_submission', array( $this, 'after_submission' ), 10, 2 );
	}


	// # SCRIPTS & STYLES -----------------------------------------------------------------------------------------------

	/**
	 * Return the scripts which should be enqueued.
	 *
	 * @return array
	 */
	// public function scripts() {
	// 	$scripts = array(
	// 		array(
	// 			'handle'  => 'gfcf_admin_settings',
	// 			'src'     => GFCF_CORE_URL . '/js/admin.js',
	// 			'version' => $this->_version,
	// 			'deps'    => array( 'jquery', 'wp-color-picker' ),
	// 			'enqueue' => array(
	// 				array(
	// 					'admin_page' => array( 'form_settings' ),
	// 					'tab'        => 'gfcfconversational'
	// 				)
	// 			)
	// 		),

	// 	);

	// 	return array_merge( parent::scripts(), $scripts );
	// }

	/**
	 * Return the stylesheets which should be enqueued.
	 *
	 * @return array
	 */
	// public function styles() {
	// 	$styles = array(
	// 		array(
	// 			'handle'  => 'gfcf_admin_settings',
	// 			'src'     => GFCF_CORE_URL . '/css/admin.css',
	// 			'version' => $this->_version,
	// 			'enqueue' => array(
	// 				array( 'field_types' => array( 'text' ) )
	// 			)
	// 		)
	// 	);

	// 	return array_merge( parent::styles(), $styles );
	// }


	// # FRONTEND FUNCTIONS --------------------------------------------------------------------------------------------

	/**
	 * Add the text in the plugin settings to the bottom of the form if enabled for this form.
	 *
	 * @param string $button The string containing the input tag to be filtered.
	 * @param array $form The form currently being displayed.
	 *
	 * @return string
	 */
	function form_submit_button( $button, $form ) {
		$settings = $this->get_form_settings( $form );
		if ( isset( $settings['enabled'] ) && true == $settings['enabled'] ) {
			$text   = $this->get_plugin_setting( 'mytextbox' );
			$button = "<div>{$text}</div>" . $button;
		}

		return $button;
	}



	/**
	 * Configures the settings which should be rendered on the Form Settings > Simple Add-On tab.
	 *
	 * @return array
	 */
	public function form_settings_fields( $form ) {
		$site_url = site_url() . '/';
		$title_url = str_replace( ' ', '-', $form['title']);
		
		$title_url =  empty($form[ 'gfcfconversational' ][ 'permalink' ] ) ?
		$title_url : $form[ 'gfcfconversational' ][ 'permalink' ];

		$perview_button = "<button id='perviewLink' class='primary button large'>Preview</button> 
		<script>
		jQuery(document).ready(function($){
			var inputVal = $('#permalink').val();
			var permalinkDes = '';

			if( $('#gaddon-setting-row-permalink .gf_settings_description' ).length ){
				permalinkDes = $('#gaddon-setting-row-permalink .gf_settings_description').html();

			}
			else{
				permalinkDes = $('#gform_setting_permalink .gform-settings-description').html();
			}

			permalinkDes += inputVal;
			$('#gaddon-setting-row-permalink .gf_settings_description, #gform_setting_permalink .gform-settings-description').html(permalinkDes);
			
			$('#permalink').on('keyup',function(){

				$('#perviewLink').addClass('permalink-disabled');
				

				var updatedValue = this.value;
				var stringToArray = permalinkDes.split('/');
				permalinkDes = '';
				stringToArray.forEach(function(value, index){
					if(value === ''){
						value = '//';
					}
					if(index === stringToArray.length-1){
						value = '/'+ updatedValue;
					}
					permalinkDes += value;
				})
			
				$('#gaddon-setting-row-permalink .gf_settings_description, #gform_setting_permalink .gform-settings-description').html(permalinkDes);
			});

			$('#perviewLink').on('click', function(event){
				event.preventDefault();
				if( $(this).hasClass('permalink-disabled') === false ){
					var link = ''
					if( $('#gaddon-setting-row-permalink .gf_settings_description' ).length ){
						link = $('#gaddon-setting-row-permalink .gf_settings_description').html();
					}
					else{
						link = $('#gform_setting_permalink .gform-settings-description').html();
					}
				
					window.open(link, '_blank');
				} else{
					alert('Please click on save settings button to update the preview link');
				}
				
			});
		});
		</script>
			<style>
			#gform_setting_permalink #permalink{
				width: calc( 100% - 92px );

			}
			</style>
		";


		return array(
			array(
				'title'  => esc_html__( 'General Settings', 'simpleaddon' ),
				'fields' => array(
					array(
						'label'   => esc_html__( 'Enable Conversational Form Mode', 'simpleaddon' ),
						'type'    => 'checkbox',
						'name'    => 'enabled',
						// 'tooltip' => esc_html__( 'This is the tooltip', 'simpleaddon' ),
						'choices' => array(
							array(
								'label' => esc_html__( 'Enabled', 'simpleaddon' ),
								'name'  => 'enabled',
							),
						),
					),array(
						'label'             => esc_html__( 'Conversational Form Title', 'simpleaddon' ),
						'type'              => 'text',
						'name'              => 'title',
						// 'tooltip'           => esc_html__( 'This is the tooltip', 'simpleaddon' ),
						'class'             => 'medium',
						// 'feedback_callback' => array( $this, 'is_valid_setting' ),
					),
					array(
						'label'   => esc_html__( 'Message ', 'simpleaddon' ),
						'type'    => 'textarea',
						'name'    => 'message',
						'tooltip' => esc_html__( 'This content will display below the Conversational Form Title, above the form.', 'simpleaddon' ),
						'class'   => 'medium',
					),
					array(
						'label'             => esc_html__( 'Logo', 'simpleaddon' ),
						'type'              => 'text',
						'name'              => 'logo',
						'tooltip'           => esc_html__( 'This is a custom logo displayed above the form title.', 'simpleaddon' ),
						'class'             => 'medium',
						// 'feedback_callback' => array( $this, 'is_valid_setting' ),
					),
					array(
						'label'             => esc_html__( 'Permalink', 'simpleaddon' ),
						'type'              => 'text',
						'name'              => 'permalink',
						'tooltip'           => esc_html__( 'This is the URL for your Conversational Form.', 'simpleaddon' ),
						'class'             => 'medium',
						'value'             => $title_url,
						'after_input'            => $perview_button,
						'description'       => $site_url
						// 'feedback_callback' => array( $this, 'is_valid_setting' ),
					),					
				),
			),

			

			array(
				'title'  => esc_html__( 'Design Settings', 'simpleaddon' ),
				'fields' => array(
					array(
						'label'             => esc_html__( 'Layout', 'simpleaddon' ),
						'type'              => 'select',
						'name'              => 'layout',
						'tooltip'           => esc_html__( 'This is the type of layout you want for your form', 'simpleaddon' ),
						'class'             => 'medium gfcf_save_on_change',
						'choices' => array(
							array(
								'label' => esc_html__( 'Default', 'simpleaddon' ),
								'value'  => 'default',
							),
							array(
								'label' => esc_html__( 'One Page', 'simpleaddon' ),
								'value'  => 'onepage',
							),
						),
					),
					array(
						'label'             => esc_html__( 'Themes', 'simpleaddon' ),
						'type'              => 'select',
						'name'              => 'theme',
						'tooltip'           => esc_html__( 'This is the theme to design your Conversational Form .', 'simpleaddon' ),
						'class'             => 'medium gfcf_save_on_change',
						'choices' => array(
							array(
								'label' => esc_html__( 'Limeade', 'simpleaddon' ),
								'value'  => 'limeade',
							),
							array(
								'label' => esc_html__( 'Sunny Days', 'simpleaddon' ),
								'value'  => 'sunnydays',
							),
							array(
								'label' => esc_html__( 'Custom', 'simpleaddon' ),
								'value'  => 'custom',
								// 'name'  => 'custom',
							),
						),
					),

					array(
						'label'             => esc_html__( 'Background Color 1', 'simpleaddon' ),
						'type'              => 'text',
						'name'              => 'backgroundcolor1',
						'tooltip'           => esc_html__( 'Background color 1 it will be combined with background color 2.', 'simpleaddon' ),
						'class'             => 'medium gfcf-color-picker',
						'dependency' => array(
							'live'   => true,
							'field'  => 'theme', // for gf v<2.5
							'values' => array('custom') , // for gf v<2.5
							'fields' => array(
								array(
									'field'  => 'theme',
									'values' => array( 'custom' ),
								),
							),
						),
						// 'feedback_callback' => array( $this, 'is_valid_setting' ),
					),
					array(
						'label'             => esc_html__( 'Background Color 2', 'simpleaddon' ),
						'type'              => 'text',
						'name'              => 'backgroundcolor2',
						'tooltip'           => esc_html__( 'Background color 2 it will be combined with background color 1.', 'simpleaddon' ),
						'class'             => 'medium gfcf-color-picker',
						'dependency' => array(
							'live'   => true,
							'field'  => 'theme', // for gf v<2.5
							'values' => array('custom') , // for gf v<2.5
							'fields' => array(
								array(
									'field'  => 'theme',
									'values' => array( 'custom' ),
								),
							),
						),
					),
					array(
						'label'             => esc_html__( 'Background Color Style', 'simpleaddon' ),
						'type'              => 'select',
						'name'              => 'gradienttype',
						'tooltip'           => esc_html__( 'This is how would you like to arrange the background colors .', 'simpleaddon' ),
						'class'             => 'small',
						'choices' => array(
							array(
								'label' => esc_html__( 'Left to Right', 'simpleaddon' ),
								'value'  => 'right',
							),
							array(
								'label' => esc_html__( 'Top to Bottom', 'simpleaddon' ),
								'value'  => 'bottom',
							)
						),
						'dependency' => array(
							'live'   => true,
							'field'  => 'theme', // for gf v<2.5
							'values' => array('custom') , // for gf v<2.5
							'fields' => array(
								array(
									'field'  => 'theme',
									'values' => array( 'custom' ),
								),
							),
						),
					),
					
					array(
						'label'             => esc_html__( 'Header Color', 'simpleaddon' ),
						'type'              => 'text',
						'name'              => 'headercolor',
						'tooltip'           => esc_html__( 'This color will be added on header text', 'simpleaddon' ),
						'class'             => 'medium gfcf-color-picker',
						'dependency' => array(
							'live'   => true,
							'field'  => 'theme', // for gf v<2.5
							'values' => array('custom') , // for gf v<2.5
							'fields' => array(
								array(
									'field'  => 'theme',
									'values' => array( 'custom' ),
								),
							),
						),
					),

					array(
						'label'             => esc_html__( 'Button Color', 'simpleaddon' ),
						'type'              => 'text',
						'name'              => 'buttoncolor',
						'tooltip'           => esc_html__( 'This is color for submit button. Same color will be used for footer as well.', 'simpleaddon' ),
						'class'             => 'medium gfcf-color-picker',
						'dependency' => array(
							'live'   => true,
							'field'  => 'theme', // for gf v<2.5
							'values' => array('custom') , // for gf v<2.5
							'fields' => array(
								array(
									'field'  => 'theme',
									'values' => array( 'custom' ),
								),
							),
						),
					),
					array(
						'label'             => esc_html__( 'Button Text Color', 'simpleaddon' ),
						'type'              => 'text',
						'name'              => 'buttontextcolor',
						'tooltip'           => esc_html__( 'This is color for submit button text. Same color will be used for footer text as well.', 'simpleaddon' ),
						'class'             => 'medium gfcf-color-picker',
						'dependency' => array(
							'live'   => true,
							'field'  => 'theme', // for gf v<2.5
							'values' => array('custom') , // for gf v<2.5
							'fields' => array(
								array(
									'field'  => 'theme',
									'values' => array( 'custom' ),
								),
							),
						),
					),
					array(
						'label'             => esc_html__( 'Field Label', 'simpleaddon' ),
						'type'              => 'text',
						'name'              => 'inputlabelcolor',
						'tooltip'           => esc_html__( 'This is color for field labels.', 'simpleaddon' ),
						'class'             => 'medium gfcf-color-picker',
						'dependency' => array(
							'live'   => true,
							'field'  => 'theme', // for gf v<2.5
							'values' => array('custom') , // for gf v<2.5
							'fields' => array(
								array(
									'field'  => 'theme',
									'values' => array( 'custom' ),
								),
							),
						),
					),
					array(
						'label'             => esc_html__( 'Field Text Color', 'simpleaddon' ),
						'type'              => 'text',
						'name'              => 'inputcolor',
						'tooltip'           => esc_html__( 'Color of text for fields.', 'simpleaddon' ),
						'class'             => 'medium gfcf-color-picker',
						'dependency' => array(
							'live'   => true,
							'field'  => 'theme', // for gf v<2.5
							'values' => array('custom') , // for gf v<2.5
							'fields' => array(
								array(
									'field'  => 'theme',
									'values' => array( 'custom' ),
								),
							),
						),
					),
					
				),
			),
		);
	}


	/**
	 * Performing a custom action at the end of the form submission process.
	 *
	 * @param array $entry The entry currently being processed.
	 * @param array $form The form currently being processed.
	 */
	public function after_submission( $entry, $form ) {

		// Evaluate the rules configured for the custom_logic setting.
		// $result = $this->is_custom_logic_met( $form, $entry );

		// if ( $result ) {
			// Do something awesome because the rules were met.
		// }
	}


	// # HELPERS -------------------------------------------------------------------------------------------------------

	/**
	 * The feedback callback for the 'mytextbox' setting on the plugin settings page and the 'mytext' setting on the form settings page.
	 *
	 * @param string $value The setting value.
	 *
	 * @return bool
	 */
	public function is_valid_setting( $value ) {
		return strlen( $value ) < 10;
	}

}
