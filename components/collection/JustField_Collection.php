<?php

namespace jcf\components\collection;

use jcf\models;
use jcf\core;

/**
 * Class for Collection type
 *
 * @package default
 * @author Kirill Samojlenko
 */
class JustField_Collection extends core\JustField {

	/**
	 * Capability
	 *
	 * @var $compatibility
	 */
	public static $compatibility = '4.0+';

	/**
	 * Current collection field key
	 *
	 * @var $current_collection_field_key
	 */
	public static $current_collection_field_key = 0;

	/**
	 * Setting chosen by site administrator
	 *
	 * @var $field_width
	 */
	public static $field_width = array(
		'100' => '100%',
		'75'  => '75%',
		'50'  => '50%',
		'33'  => '33%',
		'25'  => '25%',
	);

	/**
	 * Class constructor
	 **/
	public function __construct() {
		$field_ops = array( 'classname' => 'field_collection' );
		parent::__construct( 'collection', __( 'Collection', 'jcf' ), $field_ops );
		add_action( 'wp_ajax_jcf_collection_add_new_field_group', array(
			$this,
			'ajax_return_collection_field_group',
		) );

		if ( isset( $_GET['page'] ) && strpos( $_GET['page'], 'jcf_' ) !== false ) {
			add_action( 'admin_print_scripts', array( $this, 'add_admin_page_js' ) );
		}
	}

	/**
	 * Draw field on post edit form
	 * you can use $this->instance, $this->entry
	 */
	public function field() {
		$params      = array(
			'post_type'     => $this->post_type,
			'fieldset_id'   => $this->fieldset_id,
			'collection_id' => $this->id,
		);
		$field_model = new models\Field();
		$field_model->load( $params );

		self::$current_collection_field_key = 0;

		if ( empty( $this->entry ) ) {
			$this->entry = array();
		}

		$entries = (array) $this->entry;
		?>
		<div id="jcf_field-<?php echo esc_attr( $this->id ); ?>"
			 class="jcf_edit_field <?php echo esc_attr( $this->field_options['classname'] ); ?>">
			<?php echo $this->field_options['before_widget']; ?>
			<?php echo $this->field_options['before_title'] . esc_html( $this->instance['title'] ) . $this->field_options['after_title']; ?>

			<?php if ( empty( $this->instance['fields'] ) ) : ?>
				<p class="error">Collection element has no fields registered. Please check component settings</p>
			<?php else : ?>
				<input type="hidden" name="<?php echo $this->get_field_name( 'empty' ); ?>" value="1">
				<div class="collection_fields">
					<div class="collection_field_group empty"></div>
					<?php foreach ( $entries as $key => $fields ) : ?>
						<div class="collection_field_group">
							<h3>
								<span class="dashicons dashicons-editor-justify"></span>
								<span class="collection_group_title">
										<?php
										$group_title = $this->instance['title'] . ' Item';

										foreach ( $this->instance['fields'] as $field_id => $field ) {
											if ( isset( $field['group_title'] ) ) {
												if ( isset( $fields[ $field['slug'] ] ) ) {
													$group_title = $group_title . ' : ' . esc_html( $fields[ $field['slug'] ] );
												}
												break;
											}
										}

										echo esc_html( $group_title );
										?>
									</span>
								<a href="#"
								   class="collection_undo_remove_group"><?php esc_html_e( 'UNDO', 'jcf' ); ?></a>
								<span class="dashicons dashicons-trash"></span>
							</h3>
							<div class="collection_field_group_entry">
								<?php foreach ( $this->instance['fields'] as $field_id => $field ) :
									if ( ! $field['enabled'] ) {
										continue;
									}
									?>
									<div class="collection_field_border jcf_collection_<?php echo( intval( $field['field_width'] ) ? $field['field_width'] : '100' ); ?>">
										<?php
										$field_model->field_id = $field_id;
										$field_obj             = core\JustFieldFactory::create( $field_model );
										if ( ! $field_obj ) {
											continue;
										}

										if ( isset( $fields[ $field['slug'] ] ) ) {
											$field_obj->entry = $fields[ $field['slug'] ];
										}

										$field_obj->is_post_edit                 = true;
										$field_obj->field_options['after_title'] = ':</label>';
										$field_obj->field();
										?>
									</div>
								<?php endforeach; ?>
								<div class="clr"></div>
							</div>
						</div>
						<?php
						self::$current_collection_field_key = self::$current_collection_field_key + 1;
					endforeach; ?>
					<div class="clr"></div>
					<input type="button"
						   value="<?php echo sprintf( __( 'Add %s Item', 'jcf' ), $this->instance['title'] ); ?>"
						   class="button button-large jcf_add_more_collection"
						   data-collection_id="<?php echo esc_attr( $this->id ); ?>"
						   data-fieldset_id="<?php echo esc_attr( $this->fieldset_id ); ?>"
						   data-post_type="<?php echo esc_attr( $field_model->post_type ); ?>"
						   name="jcf_add_more_collection">
					<div class="clr"></div>
				</div>
			<?php endif; ?>
			<?php echo $this->field_options['after_widget']; ?>
		</div>
		<?php
	}

	/**
	 * Draw form for edit field
	 */
	public function form() {
		// Defaults.
		$instance    = wp_parse_args( (array) $this->instance, array( 'title' => '', 'description' => '' ) );
		$description = esc_html( $instance['description'] );
		$title       = esc_attr( $instance['title'] );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'jcf' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
				   name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
				   value="<?php echo esc_attr( $title ); ?>"/>
		</p>
		<?php
	}

	/**
	 * Save field on post edit form
	 *
	 * @param array $_values Values.
	 *
	 * @return array
	 */
	function save( $_values ) {
		$values = array();

		// hidden input to prevent skiping this field if all entries deleted.
		unset( $_values['empty'] );

		foreach ( $_values as $_value ) {
			$item = array();

			foreach ( $this->instance['fields'] as $field_id => $field ) {
				$params      = array(
					'post_type'     => $this->post_type,
					'field_id'      => $field_id,
					'fieldset_id'   => $this->fieldset_id,
					'collection_id' => $this->id,
				);
				$field_model = new models\Field();
				$field_model->load( $params ) && $field_obj = core\JustFieldFactory::create( $field_model );
				if ( ! $field_obj ) {
					continue;
				}

				if ( isset( $_value[ $field_id ] ) ) {
					$item[ $field['slug'] ] = $field_obj->save( $_value[ $field_id ] );
				} else {
					$item[ $field['slug'] ] = $field_obj->save( array( 'val' => '' ) );
				}
			}
			$values[] = $item;
		}

		return $values;
	}

	/**
	 * Update instance (settings) for current field
	 *
	 * @param array $new_instance New instance.
	 * @param array $old_instance Old instance.
	 *
	 * @return array
	 */
	function update( $new_instance, $old_instance ) {
		$instance          = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );

		return $instance;
	}

	/**
	 * Add script for collection and custom scripts and styles from collection fields
	 */
	public function add_js() {
		wp_register_script(
			'jcf_collection_post_edit',
			jcf_plugin_url( 'components/collection/assets/collection_post_edit.js' ),
			array( 'jquery', 'jquery-ui-accordion', 'jquery-ui-sortable', 'jcf_edit_post' )
		);
		wp_enqueue_script( 'jquery-ui-accordion' );
		wp_enqueue_script( 'jcf_collection_post_edit' );

		if ( ! empty( $this->instance['fields'] ) ) {
			foreach ( $this->instance['fields'] as $field_id => $field ) {
				$params      = array(
					'post_type'     => $this->post_type,
					'field_id'      => $field_id,
					'fieldset_id'   => $this->fieldset_id,
					'collection_id' => $this->id,
				);
				$field_model = new models\Field();
				$field_model->load( $params ) && $field_obj = core\JustFieldFactory::create( $field_model );
				if ( ! $field_obj ) {
					continue;
				}

				if ( method_exists( $field_obj, 'add_js' ) ) {
					$field_obj->add_js();
				}
				if ( method_exists( $field_obj, 'add_css' ) ) {
					$field_obj->add_css();
				}
			}
		}
	}

	/**
	 * Adds Javascript for fields settings admin page
	 */
	public function add_admin_page_js() {
		wp_register_script(
			'jcf_collections',
			jcf_plugin_url( 'components/collection/assets/collection.js' ),
			array( 'jquery' )
		);
		wp_enqueue_script( 'jcf_collections' );
	}

	/**
	 * Add custom  styles from collection
	 */
	public function add_css() {
		wp_register_style(
			'jcf_collection',
			jcf_plugin_url( 'components/collection/assets/collection.css' ),
			array( 'thickbox', 'jcf_edit_post' )
		);
		wp_enqueue_style( 'jcf_collection' );
	}

	/**
	 * Get nice name for width attribute
	 *
	 * @param string $width_key Width key.
	 *
	 * @return string|null
	 */
	public static function get_width_alias( $width_key ) {
		if ( isset( self::$field_width[ $width_key ] ) ) {
			return self::$field_width[ $width_key ];
		}

		return null;
	}

	/**
	 * Print fields values from shortcode
	 *
	 * @param array $args shortcode args.
	 *
	 * @return mixed
	 */
	public function shortcode_value( $args ) {
		$fields = $this->get_collection_fields_settings();
		if ( empty( $fields ) ) {
			return '';
		}

		$shortcode_value = array();
		foreach ( $this->entry as $key => $entry_values ) {
			foreach ( $fields as $field_slug => $field_settings ) {
				if ( empty( $field_settings['enabled'] ) ) {
					continue;
				}

				$params      = array(
					'post_type'     => $this->post_type,
					'field_id'      => $field_settings['_id'],
					'field_type'    => isset( $field_settings['field_type'] ) ? $field_settings['field_type'] : '',
					'fieldset_id'   => '',
					'collection_id' => $this->id,
				);
				$field_model = new models\Field();
				$field_model->load( $params ) && $field_obj = core\JustFieldFactory::create( $field_model );
				if ( ! $field_obj ) {
					continue;
				}

				$field_obj->set_post_id( $this->post_id, $key );
				$shortcode_value[] = $field_obj->do_shortcode( $args );
				unset( $field_obj );
			}
		}

		return $args['before_value'] . implode( "\n", $shortcode_value ) . $args['after_value'];
	}

	/**
	 * Prepare the array of fields with "slug" as key
	 *
	 * @return array
	 */
	protected function get_collection_fields_settings() {
		if ( empty( $this->instance['fields'] ) || ! is_array( $this->instance['fields'] ) ) {
			return array();
		}

		$collection_fields = array();
		foreach ( $this->instance['fields'] as $field_id => $field ) {
			$field['_id']                        = $field_id;
			$collection_fields[ $field['slug'] ] = $field;
		}

		return $collection_fields;
	}

	/**
	 * Collections fields for edit post ajax callback
	 */
	public function ajax_return_collection_field_group() {
		$model                              = new models\Field();
		$model->field_id                    = $_POST['collection_id'];
		$model->fieldset_id                 = $_POST['fieldset_id'];
		$model->post_type                   = $_POST['post_type'];
		$model->collection_id               = false;
		$collection                         = core\JustFieldFactory::create( $model );
		self::$current_collection_field_key = $_POST['group_id'];

		header( 'Content-Type: text/html; charset=' . get_bloginfo( 'charset' ) );
		?>
		<div class="collection_field_group">
			<h3>
				<span class="dashicons dashicons-editor-justify"></span>
				<span class="collection_group_title">
					<?php echo esc_html( $collection->instance['title'] . ' Item' ); ?>
				</span>
				<a href="#"
				   class="collection_undo_remove_group"><?php esc_html_e( 'UNDO', 'jcf' ); ?></a>
				<span class="dashicons dashicons-trash"></span>
			</h3>
			<div class="collection_field_group_entry">
				<?php
				foreach ( $collection->instance['fields'] as $field_id => $field ) :
					if ( ! $field['enabled'] ) {
						continue;
					}

					$model->field_id      = $field_id;
					$model->collection_id = $collection->id;
					$model->fieldset_id   = $this->fieldset_id;
					$field_obj            = core\JustFieldFactory::create( $model );
					if ( ! $field_obj ) {
						continue;
					}

					$field_obj->set_slug( $field['slug'] );
					$field_obj->instance                     = $field;
					$field_obj->is_post_edit                 = true;
					$field_obj->field_options['after_title'] = ':</label>';
					?>
					<div class="collection_field_border jcf_collection_<?php echo( intval( $field['field_width'] ) ? $field['field_width'] : '100' ); ?>">
						<?php echo esc_html( $field_obj->field() ); ?>
					</div>
				<?php endforeach; ?>
				<div class="clr"></div>
			</div>
		</div>
		<?php
		exit();
	}

}
