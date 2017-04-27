<?php if ( $is_edit ) : ?> 
	<tr class="form-field">
		<td colspan = "2">
<?php endif; ?>
					
			<div class="postbox jcf-taxonomy-box">
				<h2 class="hndle"><span><?= $name; ?></span></h2>
				<div class="inside">
					<?= $content; ?>
				</div>
			</div>
					
<?php if ( $is_edit ) : ?> 
		</td>
	</tr>
<?php endif; ?>

