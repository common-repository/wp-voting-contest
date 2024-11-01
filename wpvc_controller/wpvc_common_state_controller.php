<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( ! class_exists( 'Wpvc_Common_State_Controller' ) ) {
	/**
	 * Common State Controller.
	 */
	class Wpvc_Common_State_Controller {
		/**
		 * Custom field default state.
		 */
		public static function wpvc_new_custom_fields_state() {
			$settings = array(
				'field_name'        => '',
				'field_description' => '',
				'required'          => 'N',
				'required_text'     => '',
				'values'            => '',
				'show_form'         => 'N',
				'show_label_single' => 'N',
				'show_val_single'   => 'N',
				'show_label_both'   => 'N',
				'show_val_grid'     => 'N',
				'show_val_list'     => 'N',
				'show_val_modal'    => 'N',
				'form_type'         => 'TEXT',
				'text_field_type'   => 'text',
				'text_area_row'     => '3',
				'value_placement'   => 'Start',
				'upload_field'      => 'Upload',
				'upload_icon'       => 'camera_alt',
				'datepicker_only'   => 'MM/dd/yyyy',
			);
			return $settings;
		}
		/**
		 * Custom field Registration default state.
		 */
		public static function wpvc_new_reg_custom_fields_state() {
			$settings = array(
				'field_name'        => '',
				'field_description' => '',
				'required'          => 'N',
				'required_text'     => '',
				'values'            => '',
				'form_type'         => 'TEXT',
				'show_form'         => 'Y',
				'text_field_type'   => 'text',
				'text_area_row'     => '3',
				'value_placement'   => 'Start',
				'upload_field'      => 'Upload',
				'upload_icon'       => 'camera_alt',
				'datepicker_only'   => 'MM/dd/yyyy',
			);
			return $settings;
		}
		/**
		 * Custom field Taxonomy default state.
		 */
		public static function wpvc_new_taxonomy_state() {
			$settings = array(
				'category_name'             => '',
				'category_name_error'       => false,
				'category_slug_error'       => false,
				'imgcontest'                => 'photo',
				'votes_starttime'           => '',
				'votes_expiration'          => '',
				'tax_activationcount'       => '',
				'middle_custom_navigation'  => '',
				'vote_contest_entry_person' => '',
				'show_description'          => 'list',
				'editenablecontestant'      => '',
				'votecount'                 => '',
				'termdisplay'               => '',
				'list_grid_hide'            => '',
				'total_vote_count'          => '',
				'top_ten_count'             => '',
				'tax_hide_photos_live'      => '',
				'musicfileenable'           => '',
				'contest_rules'             => '',
				'authordisplay'             => '',
				'authornamedisplay'         => '',
				'datepicker_only'           => 'MM-dd-yyyy HH:mm',
				'vote_count_per_cat'        => '1',
				'color'                     => '',
				'style'                     => '',
			);
			return apply_filters( 'cat_extension_rest', $settings );
		}
		/**
		 * Translation default state.
		 */
		public static function wpvc_translations_state() {

			$default = file_get_contents( WPVC_VIEWS . 'translation.json' );

			$settings = array(
				'language'       => get_locale(),
				'default_values' => json_decode( $default, true ),
				'current_values' => json_decode( $default, true ),
			);

			return $settings;
		}
	}
} else {
	die( '<h2>' . esc_html( __( 'Failed to load Voting Common State Controller', 'voting-contest' ) ) . '</h2>' );
}


return new Wpvc_Common_State_Controller();
