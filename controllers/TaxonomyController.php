<?php

namespace jcf\controllers;

use jcf\models;
use jcf\core;
use jcf\core\JustField;

/**
 * 	Taxonomy controller
 */
class TaxonomyController extends core\Controller {

	/**
	 * Taxonomy var
	 *
	 * @var $_taxonomy
	 */
	protected $_taxonomy = null;

	/**
	 * Init all wp-actions
	 */
	public function __construct() {
		parent::__construct();

		if ( $this->_is_taxonomy_edit() ) {
			add_action( 'admin_print_scripts', array( $this, 'add_scripts' ) );
			add_action( 'admin_print_styles', array( $this, 'add_styles' ) );
			add_action( 'admin_head', array( $this, 'add_media_uploader_js' ) );

			add_action( $this->_taxonomy . '_edit_form_fields', array( $this, 'action_render' ), 10, 2 );
			add_action( $this->_taxonomy . '_add_form_fields', array( $this, 'action_render' ), 10, 2 );
		}

		add_action( 'edit_terms', array( $this, 'save_custom_fields' ), 10, 3 );
		add_action( 'create_term', array( $this, 'save_custom_fields' ), 10, 3 );

		add_action( 'wp_ajax_jcf_ajax_get_taxonomy_custom_fields', array( $this, 'ajax_render_fields' ) );
	}

	/**
	 * Check if we are on Taxonomy edit screen (add or update)
	 */
	protected function _is_taxonomy_edit() {
		$is_edit_taxonomy = false;
		if ( ! empty( $_GET['taxonomy'] ) ) {
			$is_edit_taxonomy = true;
			$this->_taxonomy  = $_GET['taxonomy'];
		}

		$current_script = '';
		if ( ! empty( $_SERVER['REQUEST_URI'] ) ) {
			$current_script = $_SERVER['REQUEST_URI'];
		}
		if ( ! empty( $_SERVER['SCRIPT_NAME'] ) ) {
			$current_script = $_SERVER['SCRIPT_NAME'];
		}

		$is_add_term = false;
		if ( strpos( $current_script, 'term.php' ) !== false
		     || strpos( $current_script, 'edit-tags.php' ) !== false
		) {
			$is_add_term = true;
		}

		return $is_edit_taxonomy || $is_add_term;
	}

	/**
	 * Taxonomy form render hook
	 *
	 * @param \WP_Term|null $term term.
	 */
	public function action_render( $term = null ) {
		$is_edit = false;
		if ( ! empty( $term->term_id ) ) {
			$is_edit = true;
		}

		$post_type = JustField::POSTTYPE_KIND_PREFIX_TAXONOMY . $this->_taxonomy;
		$model     = new models\Fieldset();
		$fieldsets = $model->find_by_post_type( $post_type );

		if ( ! empty( $fieldsets ) ) {
			print '<div id="jcf_taxonomy_fields">';

			$this->_render_fieldsets( $fieldsets, $post_type, $is_edit ? $term->term_id : null );

			print '</div>';
		}
	}

	/**
	 * Print taxonomy custom fields
	 *
	 * @param array  $fieldsets Fieldsets settings.
	 * @param string $post_type Post type ID.
	 * @param int    $term_id Object ID (for edit mode).
	 */
	protected function _render_fieldsets( $fieldsets, $post_type, $term_id ) {
		$field_model = new models\Field();

		foreach ( $fieldsets as $f_id => $fieldset ) {

			foreach ( $fieldset['fields'] as $field_id => $enabled ) {
				if ( ! $enabled ) {
					unset( $fieldset['fields'][ $field_id ] );
				}
			}

			/* if all fields disabled -> remove fieldset */
			if ( empty( $fieldset['fields'] ) ) {
				continue;
			}

			$html_fields = '';
			foreach ( $fieldset['fields'] as $field_id => $enabled ) {
				$params = array(
					'post_type'   => $post_type,
					'field_id'    => $field_id,
					'fieldset_id' => $fieldset['id'],
				);

				$field_model->load( $params ) && $field_obj = core\JustFieldFactory::create( $field_model );
				if ( ! $field_obj ) {
					continue;
				}

				if ( $term_id ) {
					$field_obj->setPostId( $term_id );
				}

				$field_obj->doAddJs();
				$field_obj->doAddCss();

				$field_obj->field_options['after_title'] = ': </label>';
				ob_start();
				$field_obj->field();
				$html_fields .= ob_get_clean();
			}

			$this->_render( 'fieldsets/_taxonomy_meta_box', array(
				'name'    => $fieldset['title'],
				'content' => $html_fields,
				'is_edit' => (int) $term_id,
			) );
		}
	}

	/**
	 * Render taxonomy custom fields on ajax request.
	 *
	 * Called after tag created, because the page does not refreshed automatically
	 * and we need to reset all fields data.
	 */
	public function ajax_render_fields() {
		$post_type = JustField::POSTTYPE_KIND_PREFIX_TAXONOMY . $_POST['taxonomy'];

		$fieldsets_model = new models\Fieldset();
		$fieldsets       = $fieldsets_model->find_by_post_type( $post_type );

		if ( empty( $fieldsets ) ) {
			exit();
		}

		header( 'Content-Type: text/html; charset=' . get_bloginfo( 'charset' ) );
		$this->_render_fieldsets( $fieldsets, $post_type, null );
		exit;
	}

	/**
	 * Save custom fields to term meta.
	 *
	 * @param int         $term_id Term ID.
	 * @param string      $tt_id TT ID.
	 * @param string|null $taxonomy Taxonomy.
	 *
	 * @return bool|void
	 */
	public function save_custom_fields( $term_id, $tt_id, $taxonomy = null ) {
		$post_type       = empty( $taxonomy ) ? $tt_id : $taxonomy;
		$post_type       = JustField::POSTTYPE_KIND_PREFIX_TAXONOMY . $post_type;
		$fieldsets_model = new models\Fieldset();
		$fieldsets       = $fieldsets_model->find_by_post_type( $post_type );
		if ( empty( $fieldsets ) ) {
			return;
		}

		$field_model            = new models\Field();
		$field_model->post_type = $post_type;

		/* Create field class objects and call save function */
		foreach ( $fieldsets as $f_id => $fieldset ) {
			$field_model->fieldset_id = $fieldset['id'];

			foreach ( $fieldset['fields'] as $field_id => $tmp ) {
				$field_model->field_id = $field_id;

				$field_obj = core\JustFieldFactory::create( $field_model );
				$field_obj->setPostId( $term_id );
				$field_obj->do_save();
			}
		}

		return false;
	}

	/**
	 *    Add custom scripts to post edit page
	 */
	public function add_scripts() {
		wp_register_script(
			'jcf_edit_taxonomy',
			jcf_plugin_url( 'assets/edit_taxonomy.js' ),
			array( 'jquery' )
		);
		wp_enqueue_script( 'jcf_edit_taxonomy' );
	}

	/**
	 *    Add custom styles to post edit page
	 */
	public function add_styles() {
		wp_enqueue_style( 'jcf_edit_post' );

		wp_register_style( 'jcf_edit_taxonomy', WP_PLUGIN_URL . '/just-custom-fields/assets/edit_taxonomy.css' );
		wp_enqueue_style( 'jcf_edit_taxonomy' );
	}

	/**
	 *    This add js script to the Upload Media wordpress popup
	 */
	public function add_media_uploader_js() {
		global $pagenow;

		if ( $pagenow !== 'media-upload.php' || empty( $_GET ['jcf_media'] ) ) {
			return;
		}

		/* Gets the right label depending on the caller widget */
		switch ( $_GET ['type'] ) {
			case 'image':
				$button_label = __( 'Select Picture', \JustCustomFields::TEXTDOMAIN );
				break;
			case 'file':
				$button_label = __( 'Select File', \JustCustomFields::TEXTDOMAIN );
				break;
			default:
				$button_label = __( 'Insert into Post', \JustCustomFields::TEXTDOMAIN );
				break;
		}

		/* Overrides the label when displaying the media uploader panels */
		?>
		<script type="text/javascript">
			jQuery(document).ready(function () {
				jQuery('#media-items').bind('DOMSubtreeModified', function () {
					jQuery('td.savesend input[type="submit"]').val("<?php echo esc_attr( $button_label ); ?>");
				});
			});
		</script>
		<?php
	}
}

