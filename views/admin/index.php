<?php include(JCF_ROOT . '/views/_header.php'); ?>

<div class="jcf_tab-content">
	<div class="jcf_inner-tab-content" >
		<div class="icon32 icon32-posts-page" id="icon-edit"><br></div>
		<p><?php _e('You should choose Custom Post Type first to configure fields for it:', \JustCustomFields::TEXTDOMAIN); ?></p>
		<div>
			<ul class="dotted-list jcf-bold">
				<?php foreach ( $post_types as $key => $obj ) : ?>
					<li>
						<a class="jcf_tile jcf_tile_<?php echo $key; ?>" href="?page=jcf_fieldset_index&amp;pt=<?php echo $key; ?>">
							<span class="jcf_tile_icon"><span class="dashicons <?php echo jcf_get_post_type_icon($obj); ?>"></span></span>
							<span class="jcf_tile_title"><?php echo $obj->label; ?>
								<span class="jcf_tile_info">
									<?php _e('Added Fieldsets: ', \JustCustomFields::TEXTDOMAIN); ?><?php echo $count_fields[$key]['fieldsets']; ?>
									<?php _e('Total Fields:  ', \JustCustomFields::TEXTDOMAIN); ?><?php echo $count_fields[$key]['fields']; ?>
								</span>
							</span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
</div>

<?php include(JCF_ROOT . '/views/_footer.php'); ?>
