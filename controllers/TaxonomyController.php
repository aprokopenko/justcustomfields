<?php

namespace jcf\controllers;

use jcf\models;
use jcf\core;
use jcf\core\JustField;

class TaxonomyController extends core\Controller
{
	protected $_taxonomy = null;

	/**
	 * Init all wp-actions
	 */
	public function __construct()
	{
		parent::__construct();

		if ( $this->_isTaxonomyEdit() ) {
			add_action('admin_print_scripts', array( $this, 'addScripts' ));
			add_action('admin_print_styles', array( $this, 'addStyles' ));
			add_action('admin_head', array( $this, 'addMediaUploaderJs' ));

			add_action( $this->_taxonomy . '_edit_form_fields', array($this, 'actionRender'), 10, 2 );
			add_action( $this->_taxonomy . '_add_form_fields', array($this, 'actionRender'), 10, 2 );
		}

		add_action( 'edit_terms', array($this, 'saveCustomFields'), 10, 3 );
		add_action( 'create_term', array($this, 'saveCustomFields'), 10, 3 );

		add_action( 'wp_ajax_jcf_ajax_get_taxonomy_custom_fields', array($this, 'ajaxRenderFields') );
	}
	
	/**
	 * Check if we are on Taxonomy edit screen (add or update)
	 */
	protected function _isTaxonomyEdit()
	{
		$is_edit_taxonomy = false;
		if ( !empty($_GET['taxonomy']) ) {
			$is_edit_taxonomy = true;
			$this->_taxonomy = $_GET['taxonomy'];
		}

		$current_script = '';
		if ( !empty($_SERVER['REQUEST_URI']) ) {
			$current_script = $_SERVER['REQUEST_URI'];
		}
		if ( !empty($_SERVER['SCRIPT_NAME']) ) {
			$current_script = $_SERVER['SCRIPT_NAME'];
		}

		$is_add_term = false;
		if (strpos($current_script, 'term.php') !== FALSE
			|| strpos($current_script, 'edit-tags.php') !== FALSE
		) {
			$is_add_term = true;
		}

		return $is_edit_taxonomy || $is_add_term;
	}

	/**
	 * Taxonomy form render hook
	 *
	 * @param \WP_Term|null $term
	 */
	public function actionRender( $term = null )
	{
		$is_edit = false;
		if ( !empty($term->term_id) ) $is_edit = true;

		$post_type = JustField::POSTTYPE_KIND_PREFIX_TAXONOMY . $this->_taxonomy;
		$model = new models\Fieldset();
		$fieldsets = $model->findByPostType($post_type);

		if ( !empty($fieldsets) ) {
			print '<div id="jcf_taxonomy_fields">';

			$this->_renderFieldsets($fieldsets, $post_type, $is_edit? $term->term_id : null);

			print '</div>';
		}
	}

	/**
	 * Print taxonomy custom fields
	 *
	 * @param array  $fieldsets  Fieldsets settings
	 * @param string $post_type  Post type ID
	 * @param int    $term_id    Object ID (for edit mode)
	 */
	protected function _renderFieldsets( $fieldsets, $post_type, $term_id )
	{
		$field_model = new models\Field();

		foreach ( $fieldsets as $f_id => $fieldset ) {

			foreach ( $fieldset['fields'] as $field_id => $enabled ) {
				if ( !$enabled ) {
					unset($fieldset['fields'][$field_id]);
				}
			}

			// if all fields disabled -> remove fieldset
			if ( empty($fieldset['fields']) ) continue;

			$htmlFields = '';
			foreach ($fieldset['fields'] as $field_id => $enabled) {
				$params = array(
					'post_type' => $post_type,
					'field_id' => $field_id,
					'fieldset_id' => $fieldset['id']
				);

				$field_model->load($params) && $field_obj = core\JustFieldFactory::create($field_model);
				if ( !$field_obj ) continue;

				if ( $term_id ) {
					$field_obj->setPostID($term_id);
				}

				$field_obj->doAddJs();
				$field_obj->doAddCss();

				$field_obj->fieldOptions['after_title'] = ': </label>';
				ob_start();
				$field_obj->field();
				$htmlFields .= ob_get_clean();
			}

			$this->_render('fieldsets/_taxonomy_meta_box', array(
				'name' => $fieldset['title'],
				'content' => $htmlFields,
				'is_edit' => (int)$term_id,
			));
		}
	}

	/**
	 * Render taxonomy custom fields on ajax request.
	 *
	 * Called after tag created, because the page does not refreshed automatically
	 * and we need to reset all fields data.
	 */
	public function ajaxRenderFields()
	{
		$post_type = JustField::POSTTYPE_KIND_PREFIX_TAXONOMY . $_POST['taxonomy'];

		$fieldsets_model = new models\Fieldset();
		$fieldsets = $fieldsets_model->findByPostType($post_type);

		if ( empty($fieldsets) ) {
			exit();
		}

		header("Content-Type: text/html; charset=" . get_bloginfo('charset'));
		$this->_renderFieldsets($fieldsets, $post_type, null);
		exit;
	}

	/**
	 * Save custom fields to term meta.
	 *
	 * @param int     $term_id
	 * @param string  $tt_id
	 * @param string|null $taxonomy
	 *
	 * @return bool|void
	 */
	public function saveCustomFields( $term_id, $tt_id, $taxonomy = null  )
	{
		$post_type = empty($taxonomy) ? $tt_id :  $taxonomy;
		$post_type = JustField::POSTTYPE_KIND_PREFIX_TAXONOMY . $post_type;
		
		$fieldsets_model = new models\Fieldset();
		$fieldsets = $fieldsets_model->findByPostType($post_type);
		if ( empty($fieldsets) ) {
			return;
		}

		$field_model = new models\Field();
		$field_model->post_type = $post_type;

		// create field class objects and call save function
		foreach ( $fieldsets as $f_id => $fieldset ) {
			$field_model->fieldset_id = $fieldset['id'];

			foreach ( $fieldset['fields'] as $field_id => $tmp ) {
				$field_model->field_id = $field_id;

				$field_obj = core\JustFieldFactory::create($field_model);
				$field_obj->setPostID($term_id);
				$field_obj->doSave();
			}
		}

		return false;
	}
	
	/**
	 * 	add custom scripts to post edit page
	 */
	public function addScripts()
	{
		wp_register_script(
			'jcf_edit_taxonomy',
			jcf_plugin_url('assets/edit_taxonomy.js'),
			array( 'jquery' )
		);
		wp_enqueue_script('jcf_edit_taxonomy');
	}

	/**
	 * 	add custom styles to post edit page
	 */
	public function addStyles()
	{
		wp_enqueue_style('jcf_edit_post');
		
		wp_register_style('jcf_edit_taxonomy', WP_PLUGIN_URL . '/just-custom-fields/assets/edit_taxonomy.css');
		wp_enqueue_style('jcf_edit_taxonomy');
	}
	
	/**
	 * 	this add js script to the Upload Media wordpress popup
	 */
	public function addMediaUploaderJs()
	{
		global $pagenow;

		if ( $pagenow != 'media-upload.php' || empty($_GET ['jcf_media']) )
			return;

		// Gets the right label depending on the caller widget
		switch ( $_GET ['type'] )
		{
			case 'image': $button_label = __('Select Picture', \JustCustomFields::TEXTDOMAIN);
				break;
			case 'file': $button_label = __('Select File', \JustCustomFields::TEXTDOMAIN);
				break;
			default: $button_label = __('Insert into Post', \JustCustomFields::TEXTDOMAIN);
				break;
		}

		// Overrides the label when displaying the media uploader panels
		?>
		<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery('#media-items').bind('DOMSubtreeModified', function() {
					jQuery('td.savesend input[type="submit"]').val("<?php echo $button_label; ?>");
				});
			});
		</script>
		<?php
	}
}

