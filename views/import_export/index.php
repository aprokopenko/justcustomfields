<?php include(JCF_ROOT . '/views/_header.php'); ?>

<div class="jcf_tab-content">
	<div class="jcf_inner-tab-content" >
		<div class="jcf_columns jcf_width40p jcf_mrgr20">
			<div class="card pressthis">
				<h3 class="header"><?php _e('Import', \JustCustomFields::TEXTDOMAIN); ?></h3>
				<div class="jcf_inner_content offset0">
					<p>
						<?php _e('If you have Just Custom Fields configuration file you can import some specific settings from it to your current WordPress installation.<br/><br/>Please choose your configuration file and press "Import" button' , \JustCustomFields::TEXTDOMAIN); ?>
					</p>
					<div>
						<div class="icon32 icon32-posts-page" id="icon-edit"><br></div>
						<form action="<?php get_permalink(); ?>" method="post" id="jcf_import_fields" enctype="multipart/form-data" >
							<input type="hidden" name ="action" value="jcf_import_fields_form" />
							<p><?php _e('Add file to import:', \JustCustomFields::TEXTDOMAIN); ?>
								<input type="file"
									   id="import_data_file" name="import_data"
									   accept=".json"
								/><br />
								<small><?php _e('file extention: .json', \JustCustomFields::TEXTDOMAIN); ?></small>
							</p>
							<div>
								<input type="submit" class="button-primary" name="import-btn" value="<?php _e('Import', \JustCustomFields::TEXTDOMAIN); ?>" />
							</div>
						</form>
						<div id="res"></div>
						<iframe id="hiddenframe" name="hiddenframe" style="width:0px; height:0px; border:0px"></iframe>
					</div>
				</div>
			</div>
		</div>
		<div class="jcf_columns jcf_width40p">
			<div class="card pressthis">
				<h3 class="header"><?php _e('Export', \JustCustomFields::TEXTDOMAIN); ?></h3>
				<div class="jcf_inner_content offset0">
					<p>
					<?php _e('You can export specific field settings and move them to another site if needed. Just click "Export Wizard" button to start.' , \JustCustomFields::TEXTDOMAIN); ?></p>
					<a class="button-primary" id="export-button" href="#"><?php _e('Export Wizard', \JustCustomFields::TEXTDOMAIN); ?></a><br /><br />
				</div>
			</div>
		</div>
	</div>
</div>

<?php include(JCF_ROOT . '/views/_footer.php'); ?>


