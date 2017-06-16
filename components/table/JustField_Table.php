<?php

namespace jcf\components\table;

use jcf\core;

/**
 * Class for select multiple list type
 *
 * @package default
 * @author Sergey Samoylov
 */
class JustField_Table extends core\JustField {

	public function __construct() {
		$field_ops = array( 'classname' => 'field_table' );
		parent::__construct( 'table', __( 'Table', \JustCustomFields::TEXTDOMAIN ), $field_ops );
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
		$entries = (array) $this->entry;

		// get fields.
		$columns = $this->parse_columns_options();

		if ( ! empty( $columns ) ) {
			// generate th headings and row to be cloned.
			$count_cols    = count( $columns );
			$table_headers = '<th class="jcf_option_column">Options</th>';
			$clone_row     = '<td>
							<span class="drag-handle" ><span class="dashicons dashicons-menu"></span></span>
							<span class="jcf_delete_row jcf_delete_table_row" ><span class="dashicons dashicons-trash"></span></span>
						</td>';
			foreach ( $columns as $col_name => $col_title ) {
				$table_headers .= '<th>' . $col_title . '</th>';
				$clone_row     .= '<td><input type="text" value=""
									id="' . $this->get_field_id_l2( $col_name, '00' ) . '"
									name="' . $this->get_field_name_l2( $col_name, '00' ) . '"></td>';
			}

			// generate rows html.
			$rows = '';
			$rows .= '<tr class="no-rows' . ( ! empty( $entries ) ? ' hide' : '' ) . '"><td colspan="' . ( $count_cols + 1 ) . '" align="center"><i>'
			         . __( 'No data yet.', \JustCustomFields::TEXTDOMAIN )
			         . '</i></td></tr>';

			foreach ( $entries as $key => $entry ) {
				$rows .= '<tr><td>
							<span class="drag-handle" ><span class="dashicons dashicons-menu"></span></span>
							<span class="jcf_delete_row jcf_delete_table_row" ><span class="dashicons dashicons-trash"></span></span>
						</td>';

				foreach ( $columns as $col_name => $col_title ) {
					$rows .= '<td><input type="text" value="' . ( ! empty( $entry[ $col_name ] ) ? esc_attr( $entry[ $col_name ] ) : '' ) . '"
						id="' . $this->get_field_id_l2( $col_name, $key ) . '"
						name="' . $this->get_field_name_l2( $col_name, $key ) . '">
					</td>';
				}

				$rows .= '</tr>';
			}
		}
		?>
		<div id="jcf_field-<?php echo esc_attr( $this->id ); ?>"
			 class="jcf_edit_field <?php echo esc_attr( $this->field_options['classname'] ); ?>">
			<?php echo $this->field_options['before_widget']; ?>
			<?php echo $this->field_options['before_title'] . esc_html( $this->instance['title'] ) . $this->field_options['after_title']; ?>

			<?php if ( ! empty( $columns ) ) : ?>
				<div class="jcf-table">
					<table class="sortable wp-list-table widefat fixed">
						<thead>
						<tr class="table-header">
							<?php echo esc_attr( $table_headers ); ?>
						</tr>
						</thead>

						<?php echo esc_attr( $rows ); ?>

						<tr class="clone hide"><?php echo esc_attr( $clone_row ); ?></tr>
					</table>
					<p><a href="#"
						  class="button button-small jcf_add_row jcf_add_table_row"><?php esc_html_e( 'Add row', \JustCustomFields::TEXTDOMAIN ); ?></a>
					</p>
				</div>
			<?php else : ?>
				<p><?php esc_html_e( 'Wrong columns configuration. Please check widget settings.', \JustCustomFields::TEXTDOMAIN ); ?></p>
			<?php endif; ?>

			<?php if ( '' !== $this->instance['description'] ) : ?>
				<p class="howto"><?php echo esc_html( $this->instance['description'] ); ?></p>
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
		$instance = wp_parse_args( (array) $this->instance, array(
			'title'       => '',
			'columns'     => '',
			'description' => '',
		) );

		$title       = esc_attr( $instance['title'] );
		$columns     = esc_html( $instance['columns'] );
		$description = esc_html( $instance['description'] );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', \JustCustomFields::TEXTDOMAIN ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
				   name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
				   value="<?php echo esc_attr( $title ); ?>"/>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'fields' ) ); ?>"><?php esc_html_e( 'Columns:', \JustCustomFields::TEXTDOMAIN ); ?></label>
			<textarea name="<?php echo $this->get_field_name( 'columns' ); ?>"
					  id="<?php echo esc_attr( $this->get_field_id( 'columns' ) ); ?>" cols="20" rows="4"
					  class="widefat"><?php echo esc_html( $columns ); ?></textarea>
			<br/>
			<small><?php _e( 'Format: %colname|%coltitle<br/><i>Example: username|User name', \JustCustomFields::TEXTDOMAIN ); ?></i></small>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'description' ) ); ?>"><?php _e( 'Description:', \JustCustomFields::TEXTDOMAIN ); ?></label>
			<textarea name="<?php echo $this->get_field_name( 'description' ); ?>"
					  id="<?php echo esc_attr( $this->get_field_id( 'description' ) ); ?>" cols="20" rows="2"
					  class="widefat"><?php echo esc_attr( $description ); ?></textarea></p>
		<?php
	}

	/**
	 *  Save field on post edit form
	 *
	 * @param array $_values values.
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
			if ( ! is_array( $params ) || ! empty( $params['__delete__'] ) ) {
				continue;
			}

			unset( $params['__delete__'] );
			$values[ $key ] = $params;
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
		$instance['columns']     = strip_tags( $new_instance['columns'] );
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
		global $wp_version;

		wp_register_script( 'jcf_table', jcf_plugin_url( 'components/table/table.js' ), array(
			'jquery',
			'jquery-ui-sortable',
			'jcf_edit_post',
		) );
		wp_enqueue_script( 'jcf_table' );

		// add text domain if not registered with another component.
		global $wp_scripts;
		wp_localize_script( 'jcf_table', 'jcf_textdomain', jcf_get_language_strings() );
	}

	/**
	 *    Add custom styles
	 */
	public function add_css() {
		wp_register_style( 'jcf_table', jcf_plugin_url( 'components/table/table.css' ), array( 'jcf_edit_post' ) );
		wp_enqueue_style( 'jcf_table' );
	}

	/**
	 * Parse columns from settings
	 *
	 * @return array
	 */
	protected function parse_columns_options() {
		$columns  = array();
		$_columns = explode( "\n", $this->instance['columns'] );
		foreach ( $_columns as $line ) {
			$line = trim( $line );
			if ( strpos( $line, '|' ) !== false ) {
				$col_name                = explode( '|', $line );
				$columns[ $col_name[0] ] = $col_name[1];
			} elseif ( ! empty( $line ) ) {
				$columns[ $line ] = $line;
			}
		}

		return $columns;
	}

	/**
	 * Print field values inside the shortcode
	 *
	 * @param array $args shortcode args.
	 *
	 * @return mixed
	 */
	public function shortcode_value( $args ) {
		$columns = $this->parse_columns_options();
		if ( empty( $columns ) || empty( $this->entry ) ) {
			return '';
		}

		$count_cols    = count( $columns );
		$thead_columns = '';
		$html          = $rows = '';
		foreach ( $this->entry as $key => $entry ) {
			$rows .= '<tr class="jcf-table-row jcf-table-row-i' . $key . '">';

			foreach ( $columns as $col_name => $col_title ) {
				if ( $key == 0 ) {
					$thead_columns .= '<th class="jcf-table-cell jcf-table-cell-' . esc_attr( $col_name ) . '">' . esc_html( $col_title ) . '</th>';
				}
				$rows .= '<td class="jcf-table-cell jcf-table-cell-' . esc_attr( $col_name ) . '">' . esc_html( $entry[ $col_name ] ) . '</td>';
			}
			$rows .= '</tr>';
		}
		$html .= '<table class="jcf-table">';
		$html .= '<thead><tr>' . $thead_columns . '</tr></thead>';
		$html .= $rows;
		$html .= '</table>';

		return $args['before_value'] . $html . $args['after_value'];
	}

}
