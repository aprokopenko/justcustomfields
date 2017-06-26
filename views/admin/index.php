<?php

/**
 * Admin view
 *
 * @package default
 * @author Alexander Prokopenko
 */
include( JCF_ROOT . '/views/_header.php' ); ?>

<div class="jcf_tab-content">
	<div class="jcf_inner-tab-content">
		<div class="icon32 icon32-posts-page" id="icon-edit"><br></div>
		<p><?php esc_html_e( 'You can choose Post Type or Taxonomy to configure custom fields for it:', 'jcf' ); ?></p>

		<h3><?php esc_html_e( 'Post Types', 'jcf' ); ?></h3>
		<div>
			<ul class="dotted-list jcf-bold">
				<?php foreach ( $post_types as $key => $obj ) : ?>
					<li>
						<a class="jcf_tile jcf_tile_<?php echo esc_attr( $key ); ?>"
						   href="?page=jcf_fieldset_index&amp;pt=<?php echo esc_attr( $key ); ?>">
							<span class="jcf_tile_icon"><span
										class="dashicons <?php echo jcf_get_post_type_icon( $obj ); ?>"></span></span>
							<span class="jcf_tile_title"><?php echo esc_attr( $obj->label ); ?>
								<span class="jcf_tile_info">
									<?php esc_html_e( 'Added Fieldsets: ', 'jcf' ); ?><?php echo esc_html( $count_fields[ $key ]['fieldsets'] ); ?>
									<?php esc_html_e( 'Total Fields:  ', 'jcf' ); ?><?php echo esc_html( $count_fields[ $key ]['fields'] ); ?>
								</span>
							</span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>

		<br/>
		<h3><?php esc_html_e( 'Taxonomies', 'jcf' ); ?></h3>
		<div>
			<ul class="dotted-list jcf-bold">
				<?php foreach ( $taxonomies as $tax_key => $tax_obj ) : ?>
					<li>
						<a class="jcf_tile jcf_tile_<?php echo esc_attr( $tax_key ); ?>"
						   href="?page=jcf_fieldset_index&amp;pt=<?php echo esc_attr( $tax_key ); ?>">
							<span class="jcf_tile_icon"><span
										class="dashicons <?php echo jcf_get_post_type_icon( $tax_obj ); ?>"></span></span>
							<span class="jcf_tile_title"><?php echo esc_attr( $tax_obj->label ); ?>
								<span class="jcf_tile_info">
									<?php esc_html_e( 'Added Fieldsets: ', 'jcf' ); ?><?php echo esc_html( $count_fields[ $tax_key ]['fieldsets'] ); ?>
									<?php esc_html_e( 'Total Fields:  ', 'jcf' ); ?><?php echo esc_html( $count_fields[ $tax_key ]['fields'] ); ?>
								</span>
							</span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
</div>

<?php include( JCF_ROOT . '/views/_footer.php' ); ?>
