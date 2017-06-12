<?php

namespace jcf\controllers;

use jcf\models;
use jcf\core;

class PostTypeController extends core\Controller {

	/**
	 * Init all wp-actions
	 */
	public function __construct() {
		parent::__construct();

		if ( $this->_is_post_edit() ) {
			add_action( 'admin_print_scripts', array( $this, 'add_scripts' ) );
			add_action( 'admin_print_styles', array( $this, 'add_styles' ) );
			add_action( 'admin_head', array( $this, 'add_media_uploader_js' ) );
		}

		add_action( 'add_meta_boxes', array( $this, 'action_render' ), 10, 1 );
		add_action( 'save_post', array( $this, 'save_post_ext' ), 10, 2 );

		/* init shortcode */
		add_shortcode( 'jcf-value', array( $this, 'jcf_shortcode' ) );
	}

	/**
	 * Check if we are on Post edit screen (add or update)
	 */
	protected function _is_post_edit() {
		$is_edit_post = isset( $_GET['post'] ) && isset( $_GET['action'] ) && $_GET['action'] == 'edit';

		$current_script = '';
		if ( ! empty( $_SERVER['REQUEST_URI'] ) ) {
			$current_script = $_SERVER['REQUEST_URI'];
		}
		if ( ! empty( $_SERVER['SCRIPT_NAME'] ) ) {
			$current_script = $_SERVER['SCRIPT_NAME'];
		}

		$is_add_post = strpos( $current_script, 'post-new.php' ) !== false;

		return $is_edit_post || $is_add_post;
	}

	/**
	 * Get fields by post type
	 *
	 * @param string $post_type post type.
	 */
	public function action_render( $post_type = '' ) {
		$model = new models\Fieldset();
		$fieldsets = $model->find_by_post_type( $post_type );

		$field_model = new models\Field();
		$fields = $field_model->find_by_post_type( $post_type );

		$visibility_model = new models\FieldsetVisibility();
		$visibility_rules = $visibility_model->find_by_post_type( $post_type );

		if ( ! empty( $fieldsets ) ) {
			/* remove fieldsets without fields */
			foreach ( $fieldsets as $f_id => $fieldset ) {
				/* if all fields disabled -> remove fieldset */
				if ( empty( $fieldset['fields'] ) ) {
					continue;
				}
				foreach ( $fieldset['fields'] as $field_id => $enabled ) {
					if ( ! $enabled || empty( $fields[ $field_id ] ) ) {
						continue;
					}
					$params = array(
						'post_type' => $post_type,
						'field_id' => $field_id,
						'fieldset_id' => $fieldset['id'],
					);
					$field_model->load( $params ) && $field_obj = core\JustFieldFactory::create( $field_model );
					if ( ! $field_obj ) {
						continue;
					}

					$field_obj->doAddJs();
					$field_obj->doAddCss();
				}

				$pos = isset( $fieldset['position'] )? $fieldset['position'] : models\Fieldset::POSITION_ADVANCED;
				$prio = isset( $fieldset['priority'] )? $fieldset['priority'] : models\Fieldset::PRIO_DEFAULT;

				add_meta_box( 'jcf_fieldset-' . $f_id, $fieldset['title'], array( $this, 'render_custom_field' ), $post_type, $pos, $prio, array( $fieldset ) );
			}

			wp_add_inline_script( 'jquery-core', 'var jcf_fieldsets_visibility_rules = ' . json_encode( $visibility_rules ) . ';', 'before' );
		}
	}

	/**
	 * 	Prepare and print fieldset html.
	 * 	- load each field class
	 * 	- print form from each class
	 *
	 * @param array $post post.
	 * @param array $box box.
	 */
	public function render_custom_field( $post = null, $box = null ) {
		$model = new models\Field();
		$fieldset = $box['args'][0];
		$fields = $model->find_by_post_type( $post->post_type );
		$this->_render( 'shortcodes/modal' );

		foreach ( $fieldset['fields'] as $field_id => $enabled ) {
			if ( ! $enabled || empty( $fields[ $field_id ] ) ) {
				continue;
			}

			$params = array(
				'post_type' => $post->post_type,
				'field_id' => $field_id,
				'fieldset_id' => $fieldset['id'],
			);
			$model->load( $params ) && $field_obj = core\JustFieldFactory::create( $model );
			if ( ! $field_obj ) {
				continue;
			}

			$field_obj->set_post_id( $post->ID );
			$field_obj->field();
		}
		unset( $field_obj );

		/* Use nonce for verification */
		global $jcf_noncename;

		if ( empty( $jcf_noncename ) ) {
			wp_nonce_field( plugin_basename( __FILE__ ), 'justcustomfields_noncename' );
			$jcf_noncename = true;
		}
	}

	/**
	 * Save values of custom fields for post
	 *
	 * @param int   $post_id post ID.
	 * @param array $post post.
	 *
	 * @return bool
	 */
	public function save_post_ext( $post_id = 0, $post = null ) {
		$fieldsets_model = new models\Fieldset();
		$field_model = new models\Field();
		$field_model->load( $_POST );
		/* do not save anything on autosave */
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		/*
		* Verify this came from the our screen and with proper authorization,
		* because save_post can be triggered at other times
		*/
		if ( empty( $_POST['justcustomfields_noncename'] ) || !wp_verify_nonce( $_POST['justcustomfields_noncename'], plugin_basename( __FILE__ ) ) ) {
			return;
		}

		/* check permissions */
		$permission = ('page' === $field_model->post_type) ? 'edit_page' : 'edit_post';

		if ( ! current_user_can( $permission, $post_id ) ) {
			return;
		}

		/*
		* OK, we're authenticated: we need to find and save the data
		* get fieldsets
		*/

		$fieldsets = $fieldsets_model->find_by_post_type( $field_model->post_type );

		/* create field class objects and call save function */
		foreach ( $fieldsets as $f_id => $fieldset ) {
			$field_model->fieldset_id = $fieldset['id'];

			foreach ( $fieldset['fields'] as $field_id => $tmp ) {
				$field_model->field_id = $field_id;
				$field_obj = core\JustFieldFactory::create( $field_model );
				$field_obj->set_post_id( $post->ID );
				$field_obj->do_save();
			}
		}

		return false;
	}

	/**
	 * Set value of shortcode
	 *
	 * @param array $args args.
	 *
	 * @return string
	 */
	public function jcf_shortcode( $args ) {
		$model = new models\Shortcodes();
		return $model->get_field_value( $args );
	}

	/**
	 * 	Add custom scripts to post edit page
	 */
	public function add_scripts() {
		wp_enqueue_script( 'jcf_edit_post' );
	}

	/**
	 * 	Add custom styles to post edit page
	 */
	public function add_styles() {
		wp_enqueue_style( 'jcf_edit_post' );
	}

	/**
	 * 	This add js script to the Upload Media wordpress popup
	 */
	public function add_media_uploader_js() {
		global $pagenow;

		if ( $pagenow !== 'media-upload.php' || empty( $_GET['jcf_media'] ) ) {
			return;
		}

		/* Gets the right label depending on the caller widget */
		switch ( $_GET['type'] ) {
			case 'image': $button_label = __( 'Select Picture', \JustCustomFields::TEXTDOMAIN );
				break;
			case 'file': $button_label = __( 'Select File', \JustCustomFields::TEXTDOMAIN );
				break;
			default: $button_label = __( 'Insert into Post', \JustCustomFields::TEXTDOMAIN );
				break;
		}

		/* Overrides the label when displaying the media uploader panels */
		?>
		<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery('#media-items').bind('DOMSubtreeModified', function() {
					jQuery('td.savesend input[type="submit"]').val("<?php echo esc_attr( $button_label ); ?>");
				});
			});
		</script>
		<?php
	}
}
