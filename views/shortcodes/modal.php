<?php
/**
 * Shortcode view. Modal section.
 */
?>

<div class="jcf_shortcodes_tooltip">
	<div class="jcf_inner_box">
		<h3 class="header"><?php esc_html_e( 'Usage guidelines for field ', 'jcf' ); ?>
			<span class="field-name"></span>
			<a href="#" class="jcf_shortcodes_tooltip-close"><span class="media-modal-icon"></span></a>
		</h3>
		<div class="jcf_inner_content">
			<fieldset class="shortcode_usage">
				<legend><?php esc_html_e( 'Inside the Editor', 'jcf' ); ?></legend>
				<span class="fieldset-description"><?php esc_html_e( 'To insert the value into your post editor, please copy and paste the code examples below to your editor.', 'jcf' ); ?></span>

				<span class="jcf-relative"><input type="text" readonly="readonly"
												  class="jcf-shortcode jcf-shortcode-value" value=""/><a href="#"
																										 class="copy-to-clipboard"
																										 title="Copy to clipboard"></a></span><br/>
				<small><?php esc_html_e( 'optional parameters: class="myclass" id="myid" post_id="123" label="yes"', 'jcf' ); ?></small>
				<br/><br/>
			</fieldset>
			<fieldset class="template_usage">
				<legend><?php esc_html_e( 'Inside your Templates ', 'jcf' ); ?></legend>
				<span class="fieldset-description"><?php esc_html_e( 'To print the value or label inside your template (for example in single.php) please use the examples below:', 'jcf' ); ?></span>

				<span class="jcf-relative"><input type="text" readonly="readonly"
												  class="jcf-shortcode jcf-template-value" value=""/><a href="#"
																										class="copy-to-clipboard"
																										title="Copy to clipboard"></a></span><br/>
				<small><?php esc_html_e( 'optional parameters: class="myclass" id="myid" post_id="123" label="yes"', 'jcf' ); ?></small>
				<br/><br/>
			</fieldset>
		</div>
	</div>
</div>

