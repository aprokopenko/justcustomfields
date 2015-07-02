<div class="wrap">
	<div class="icon32 icon32-posts-page" id="icon-edit"><br></div>
	<h2><?php _e('Just Custom Fields', JCF_TEXTDOMAIN); ?></h2>
	<p><?php _e('You should choose Custom Post Type first to configure fields:', JCF_TEXTDOMAIN); ?></p>
	<ul class="dotted-list jcf-bold">
	<?php foreach($post_types as $key => $obj) : ?>
		<li><a href="?page=just_custom_fields&amp;pt=<?php echo $key; ?>"><?php echo $obj->label; ?></a></li>
	<?php endforeach; ?>
	</ul>
	<div class="alignleft">
		<a class="button-primary" href="?page=just_custom_fields&amp;export"><?php _e('Export Fields', JCF_TEXTDOMAIN); ?></a>
		<a class="button-primary" href="?page=just_custom_fields&amp;import" ><?php _e('Import Fields', JCF_TEXTDOMAIN); ?></a>
	</div>
</div>
