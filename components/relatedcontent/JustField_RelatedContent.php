<?php

namespace jcf\components\relatedcontent;

use jcf\core;

/**
 *    Fields group field.
 *    allow you to add "table" of fields
 */
class JustField_RelatedContent extends core\JustField {

	/**
	 * Class constructor
	 **/
	public function __construct() {
		$field_ops = array( 'classname' => 'field_relatedcontent' );
		parent::__construct( 'relatedcontent', __( 'Related Content', \JustCustomFields::TEXTDOMAIN ), $field_ops );
		add_action( 'wp_ajax_jcf_related_content_autocomplete', array( $this, 'ajax_autocomplete' ) );
	}

	/**
	 *    Draw field on post edit form
	 *    you can use $this->instance, $this->entry
	 */
	public function field() {
		if ( empty( $this->entry ) ) {
			$this->entry = array();
		}
		// add null element for etalon copy.
		$entries = array( '00' => '' ) + (array) $this->entry;

		// get posts data.
		$type       = $this->instance['input_type'];
		$post_type  = $this->instance['post_type'];
		$post_types = jcf_get_post_types( 'object' );

		if ( 'select' === $type ) {
			// get posts list.
			global $wpdb;

			if ( 'any' !== $post_type ) {
				$post_type_where = " post_type = '$post_type' ";
			} else {
				// get all post types.
				$post_type_where = "( post_type = '" . implode( "' OR post_type = '", array_keys( $post_types ) ) . "' )";
			}

			$query = "SELECT ID, post_title, post_status, post_type
				FROM $wpdb->posts
				WHERE $post_type_where AND (post_status = 'publish' OR post_status = 'draft')
				ORDER BY post_title";
			$posts = $wpdb->get_results( $query );

			$options = array();

			foreach ( $posts as $p ) {
				$draft                       = ( 'draft' === $p->post_status ) ? ' (DRAFT)' : '';
				$type_label                  = ( 'any' === $post_type ) ? ' / ' . $post_types[ $p->post_type ]->labels->singular_name : '';
				$options[ '' . $p->ID . '' ] = esc_attr( $p->post_title . $draft . $type_label );
			}
		} elseif ( 'autocomplete' === $type && ! empty( $this->entry[0] ) ) {
			global $wpdb;

			$query = "SELECT ID, post_title, post_status, post_type
				FROM $wpdb->posts
				WHERE ID IN(" . implode( ',', $this->entry ) . ")";
			$posts = $wpdb->get_results( $query );

			$options = array();

			foreach ( $posts as $p ) {
				$draft                       = ( 'draft' === $p->post_status ) ? ' (DRAFT)' : '';
				$type_label                  = ( 'any' === $post_type ) ? ' / ' . $post_types[ $p->post_type ]->labels->singular_name : '';
				$options[ '' . $p->ID . '' ] = esc_attr( $p->post_title . $draft . $type_label );
			}
		}
		?>
		<div id="jcf_field-<?php echo $this->id; ?>"
			 class="jcf_edit_field <?php echo $this->field_options['classname']; ?>">
			<?php echo $this->field_options['before_widget']; ?>
			<?php echo $this->field_options['before_title'] . esc_html( $this->instance['title'] ) . $this->field_options['after_title']; ?>

			<div class="jcf-relatedcontent-field">
				<?php foreach ( $entries as $key => $entry ) : ?>
					<div class="jcf-relatedcontent-row<?php if ( '00' === $key ) {
						echo ' jcf-hide';
					} ?>">
						<div class="jcf-relatedcontent-container">
							<p>
								<span class="drag-handle"><span class="dashicons dashicons-menu"></span></span>

								<?php if ( 'select' === $type ) : ?>
									<select id="<?php echo esc_attr( $this->get_field_id_l2( 'related_id', $key ) ); ?>"
											name="<?php echo $this->get_field_name_l2( 'related_id', $key ); ?>">
										<option value="">&nbsp;</option>

										<?php foreach ( $options as $val => $label ) : ?>
											<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $val, $entry ); ?>><?php echo esc_html( $label ); ?></option>
										<?php endforeach; ?>

									</select>

								<?php else : // input field for autocomplete.  ?>
									<input type="text" value="<?php echo @$options[ $entry ]; ?>"
										   id="<?php echo esc_attr( $this->get_field_id_l2( 'related_title', $key ) ); ?>"
										   name="<?php echo $this->get_field_name_l2( 'related_title', $key ); ?>"
										   alt="<?php echo esc_attr( $post_type ); ?>"/>
									<input type="hidden" value="<?php echo esc_attr( $entry ); ?>"
										   id="<?php echo esc_attr( $this->get_field_id_l2( 'related_id', $key ) ); ?>"
										   name="<?php echo $this->get_field_name_l2( 'related_id', $key ); ?>"/>
								<?php endif; ?>

								<a href="#" class="jcf_delete"><span class="dashicons dashicons-trash"></span></a>
							</p>
						</div>
						<div class="jcf-delete-layer">
							<input type="hidden"
								   id="<?php echo esc_attr( $this->get_field_id_l2( '__delete__', $key ) ); ?>"
								   name="<?php echo $this->get_field_name_l2( '__delete__', $key ); ?>" value=""/>
							<span class="jcf_tobe_removed"><?php esc_html_e( 'To be deleted', \JustCustomFields::TEXTDOMAIN ); ?></span>
							<a href="#"
							   class="jcf_cancel"><?php esc_html_e( 'UNDO', \JustCustomFields::TEXTDOMAIN ); ?></a><br/>
						</div>
					</div>
				<?php endforeach; ?>

				<?php if ( '' !== $this->instance['description'] ) : ?>
					<p class="howto<?php if ( count( $entries ) === 1 ) {
						echo ' jcf-hide'; } ?>"><?php echo esc_html( $this->instance['description'] ); ?></p>
				<?php endif; ?>

				<a href="#"
				   class="button button-small jcf_add_more"><?php esc_html_e( 'Add', \JustCustomFields::TEXTDOMAIN ); ?></a>
			</div>

			<?php echo $this->field_options['after_widget']; ?>
		</div>
		<?php
	}

	/**
	 * Draw form for edit field
	 */
	public function form() {
		// Defaults.
		$instance = wp_parse_args( (array) $this->instance, array(
			'title'       => '',
			'post_type'   => 'page',
			'input_type'  => 'autocomplete',
			'description' => __( 'Start typing entry Title to see the list.', \JustCustomFields::TEXTDOMAIN ),
		) );

		$title       = esc_attr( $instance['title'] );
		$description = esc_html( $instance['description'] );

		$post_types = jcf_get_post_types( 'object' );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', \JustCustomFields::TEXTDOMAIN ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
				   name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
				   value="<?php echo esc_attr( $title ); ?>"/>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'post_type' ) ); ?>"><?php esc_html_e( 'Post type:', \JustCustomFields::TEXTDOMAIN ); ?></label>
			<select name="<?php echo $this->get_field_name( 'post_type' ); ?>"
					id="<?php echo esc_attr( $this->get_field_id( 'post_type' ) ); ?>">
				<option value="any" <?php selected( 'any', $instance['post_type'] ); ?>><?php esc_html_e( 'All', \JustCustomFields::TEXTDOMAIN ); ?></option>
				<?php foreach ( $post_types as $pt_id => $pt ) : ?>
					<option value="<?php echo $pt_id; ?>" <?php selected( $pt_id, $instance['post_type'] ); ?>><?php echo esc_html( $pt->label ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'input_type' ) ); ?>"><?php _e( 'Input type:', \JustCustomFields::TEXTDOMAIN ); ?></label>
			<select name="<?php echo $this->get_field_name( 'input_type' ); ?>"
					id="<?php echo esc_attr( $this->get_field_id( 'input_type' ) ); ?>">
				<option value="autocomplete" <?php selected( 'autocomplete', $instance['input_type'] ); ?>><?php esc_html_e( 'Autocomplete', \JustCustomFields::TEXTDOMAIN ); ?></option>
				<option value="select" <?php selected( 'select', $instance['input_type'] ); ?>><?php esc_html_e( 'Dropdown list', \JustCustomFields::TEXTDOMAIN ); ?></option>
			</select>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'description' ) ); ?>"><?php esc_html_e( 'Description:', \JustCustomFields::TEXTDOMAIN ); ?></label>
			<textarea name="<?php echo $this->get_field_name( 'description' ); ?>"
					  id="<?php echo esc_attr( $this->get_field_id( 'description' ) ); ?>" cols="20" rows="2"
					  class="widefat"><?php echo esc_html_e( $description ); ?></textarea></p>
		<?php
	}

	/**
	 * Save field on post edit form
	 *
	 * @param array $_values Values.
	 *
	 * @return array
	 */
	public function save( $_values ) {
		$values = array();
		if ( empty( $_values ) ) {
			return $values;
		}

		// remove etalon element.
		if ( isset( $_values['00'] ) ) {
			unset( $_values['00'] );
		}

		// fill values.
		foreach ( $_values as $key => $params ) {
			if ( ! is_array( $params ) || ! empty( $params['__delete__'] ) || empty( $params['related_id'] ) ) {
				continue;
			}

			unset( $params['__delete__'] );
			$values[ $key ] = $params['related_id'];
		}
		$values = array_values( $values );

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
	public function update( $new_instance, $old_instance ) {
		$instance                = $old_instance;
		$instance['title']       = strip_tags( $new_instance['title'] );
		$instance['post_type']   = strip_tags( $new_instance['post_type'] );
		$instance['input_type']  = strip_tags( $new_instance['input_type'] );
		$instance['description'] = strip_tags( $new_instance['description'] );

		return $instance;
	}

	/**
	 * Custom get_field functions to add one more deep level
	 *
	 * @param mixed $field Field.
	 * @param mixed $number Number.
	 *
	 * @return mixed
	 */
	protected function get_field_id_l2( $field, $number ) {
		return $this->get_field_id( $number . '-' . $field );
	}

	/**
	 * Custom get_field functions to add one more deep level
	 *
	 * @param mixed $field Field.
	 * @param mixed $number Number.
	 *
	 * @return mixed
	 */
	protected function get_field_name_l2( $field, $number ) {
		return $this->get_field_name( $number . '][' . $field );
	}

	/**
	 *    Add custom scripts
	 */
	public function add_js() {
		wp_register_script(
			'jcf_related_content',
			plugins_url( '/related-content.js', __FILE__ ),
			array( 'jquery', 'jquery-ui-autocomplete', 'jquery-ui-sortable', 'jcf_edit_post' )
		);
		wp_enqueue_script( 'jcf_related_content' );

		// add text domain if not registered with another component.
		global $wp_scripts;

		if ( empty( $wp_scripts->registered['jcf_fields_group'] ) && empty( $wp_scripts->registered['jcf_uploadmedia'] ) ) {
			wp_localize_script( 'jcf_related_content', 'jcf_textdomain', jcf_get_language_strings() );
		}
	}

	/**
	 *    Add custom styles
	 */
	public function add_css() {
		wp_register_style( 'ui-autocomplete', plugins_url( '/assets/jquery-ui-1.8.14.autocomplete.css', __FILE__ ) );
		wp_enqueue_style( 'ui-autocomplete' );

		wp_register_style( 'jcf_related_content', plugins_url( '/related-content.css', __FILE__ ), array( 'jcf_edit_post' ) );
		wp_enqueue_style( 'jcf_related_content' );
	}

	/**
	 * Print field values inside the shortcode
	 *
	 * @param array $args shortcode args.
	 *
	 * @return mixed
	 */
	public function shortcode_value( $args ) {
		if ( empty( $this->entry ) ) {
			return;
		}

		$html = '<ul class="jcf-list">';
		foreach ( $this->entry as $key => $entry ) {
			$post_link  = get_permalink( $entry );
			$post_title = get_the_title( $entry );
			$html       .= '<li class="jcf-item jcf-item-i' . $key . '"><a href="' . $post_link . '">' . esc_html( $post_title ) . '</a></li>';
		}
		$html .= '</ul>';

		return $args['before_value'] . $html . $args['after_value'];
	}

	/**
	 * Autocomplete ajax callback
	 */
	public function ajax_autocomplete() {
		if ( empty( $_POST['term'] ) ) {
			die( '' );
		}

		$post_type  = $_POST['post_types'];
		$post_types = jcf_get_post_types( 'object' );

		if (  'any' !== $post_type ) {
			$post_type_where = " post_type = '" . $_POST['post_types'] . "' ";
		} else {
			// get all post types.
			$post_type_where = "( post_type = '" . implode( "' OR post_type = '", array_keys( $post_types ) ) . "' )";
		}

		global $wpdb;
		$query    = "SELECT ID, post_title, post_status, post_type
			FROM $wpdb->posts
			WHERE $post_type_where AND (post_status = 'publish' OR post_status = 'draft') AND post_title LIKE '%" . $_POST['term'] . "%'
			ORDER BY post_title";
		$posts    = $wpdb->get_results( $query );
		$response = array();

		foreach ( $posts as $p ) {
			$draft      = ( 'draft' === $p->post_status ) ? ' (DRAFT)' : '';
			$type_label = ( 'any' !== $_POST['post_types'] ) ? '' : ' / ' . $post_types[ $p->post_type ]->labels->singular_name;
			$response[] = array(
				'id'     => $p->ID,
				'label'  => $p->post_title . $draft . $type_label,
				'value'  => $p->post_title . $draft . $type_label,
				'type'   => $p->post_type,
				'status' => $p->post_status,
			);
		}
		$result = json_encode( $response );
		header( 'Content-Type: application/json; charset=' . get_bloginfo( 'charset' ) );
		echo $result;
		exit();
	}

}

?>