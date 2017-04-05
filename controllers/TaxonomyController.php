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
		}
		
		add_action( $this->_taxonomy . '_edit_form_fields', array($this, 'actionRender'), 10, 2 );
		add_action( $this->_taxonomy . '_add_form_fields', array($this, 'actionRender'), 10, 2 );
		add_action( 'edit_terms', array($this, 'saveCustomFields'), 10, 3 );
		add_action( 'create_term', array($this, 'saveCustomFields'), 10, 3 );
	}
	
	/**
	 * Check if we are on Post edit screen (add or update)
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

	public function actionRender( $term = null )
	{
		$is_edit = false;
		$htmlFields = '';
		if ( !empty($term->term_id) ) $is_edit = true;

		$post_type = JustField::POSTTYPE_KIND_PREFIX_TAXONOMY . $this->_taxonomy;
		$model = new models\Fieldset();
		$fieldsets = $model->findByPostType($post_type);

		$field_model = new models\Field();

		if ( !empty($fieldsets) ) {

			// remove fieldsets without fields
			foreach ( $fieldsets as $f_id => $fieldset ) { 
				
				// if all fields disabled -> remove fieldset
				if ( empty($fieldset['fields']) ) continue;

				$htmlFields = '';
				foreach ($fieldset['fields'] as $field_id => $enabled) {
					if ( !$enabled ) continue;

					$params = array(
						'post_type' => $post_type,
						'field_id' => $field_id,
						'fieldset_id' => $fieldset['id']
					);

					$field_model->load($params) && $field_obj = core\JustFieldFactory::create($field_model);
					if ( !$field_obj ) continue;

					if ( $is_edit ) {
						$field_obj->setPostID($term->term_id);
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
					'is_edit' => $is_edit
				));
			}
		}
	}
	
	public function saveCustomFields( $term_id, $tt_id, $taxonomy = null  )
	{
		$post_type = empty($taxonomy) ? $tt_id :  $taxonomy;
		$post_type = JustField::POSTTYPE_KIND_PREFIX_TAXONOMY . $post_type;
		
		$fieldsets_model = new models\Fieldset();
		$field_model = new models\Field();
		$field_model->post_type = $post_type;

		$fieldsets = $fieldsets_model->findByPostType($taxonomy);

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
		do_action('jcf_admin_edit_post_scripts');
	}

	/**
	 * 	add custom styles to post edit page
	 */
	public function addStyles()
	{
		wp_register_style('jcf_edit_post', WP_PLUGIN_URL . '/just-custom-fields/assets/edit_post.css');
		wp_enqueue_style('jcf_edit_post');
		
		wp_register_style('jcf_edit_taxonomy', WP_PLUGIN_URL . '/just-custom-fields/assets/edit_taxonomy.css');
		wp_enqueue_style('jcf_edit_taxonomy');

		do_action('jcf_admin_edit_post_styles');
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

