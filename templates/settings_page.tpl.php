<div class="wrap">
	<div class="icon32 icon32-posts-page" id="icon-edit"><br></div>
	<h2><?php _e('Just Custom Fields', JCF_TEXTDOMAIN); ?></h2>
	<p><?php _e('You should choose Custom Post Type first to configure fields:', JCF_TEXTDOMAIN); ?></p>
	<ul class="dotted-list jcf-bold">
	<?php foreach($post_types as $key => $obj) : ?>
		<?php  $fieldsets_count = jcf_fieldsets_count($key); ?>
		<li>
			<a class="jcf_tile <?php echo $key; ?>" href="?page=just_custom_fields&amp;pt=<?php echo $key; ?>">
				<span class="jcf_tile_icon"></span>
				<span class="jcf_tile_title"><?php echo $obj->label; ?>
					<span class="jcf_tile_info"><?php _e('Added Fieldsets: ', JCF_TEXTDOMAIN); ?><?php echo $fieldsets_count['fieldsets']; ?><?php _e('Total Fields:  ', JCF_TEXTDOMAIN); ?><?php echo $fieldsets_count['fields']; ?></span>
				</span>
			</a>
		</li>
	<?php endforeach; ?>
	</ul>
</div>
