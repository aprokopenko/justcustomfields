<?php
	
	/**
	 *  add fieldset form process callback
	 */
	function jcf_ajax_add_fieldset(){
		$title = strip_tags(trim($_POST['title']));
		if( empty($title) ){
			jcf_ajax_reposnse( array('status' => "0", 'error'=>__('Title field is required.', JCF_TEXTDOMAIN)) );
		}
		
		$slug = preg_replace('/[^a-z0-9\-\_\s]/i', '', $title);
		$trimed_slug = trim($slug);
		if( $trimed_slug == '' ){
			$slug = 'jcf-fieldset-'.rand(0,10000);
		}
		else{
			$slug = sanitize_title( $title );
		}

		$fieldsets = jcf_fieldsets_get();
		// check exists
		if( isset($fieldsets[$slug]) ){
			jcf_ajax_reposnse( array('status' => "0", 'error'=>__('Such fieldset already exists.', JCF_TEXTDOMAIN)) );
		}

		// create fiedlset
		$fieldset = array(
			'id' => $slug,
			'title' => $title,
			'fields' => array(),
		);

		jcf_fieldsets_update($slug, $fieldset);
		
		/**
		 * @author Kirill Samojlenko 
		 * remove $jcf_settings['fieldsets'][$post_type]
		 * we dont have variables $jcf_settings and $post_type in this function
		 */
		jcf_ajax_reposnse( array('status' => "1" ) );// 
	}
	
	/**
	 *  delete fieldset link process callback
	 */
	function jcf_ajax_delete_fieldset(){
		$f_id = $_POST['fieldset_id'];
		if( empty($f_id) ){
			jcf_ajax_reposnse( array('status' => "0", 'error'=>__('Wrong params passed.', JCF_TEXTDOMAIN)) );
		}

		jcf_fieldsets_update($f_id, NULL);

		jcf_ajax_reposnse( array('status' => "1") );
	}
	
	/**
	 * change fieldset link process callback
	 */
	function jcf_ajax_change_fieldset(){
		$f_id = $_POST['fieldset_id'];
		$fieldset = jcf_fieldsets_get($f_id);
		ob_start(); ?>
		<div class="jcf_edit_fieldset">
			<h3 class="header"><?php echo __('Edit Fieldset:', JCF_TEXTDOMAIN) . ' ' . $fieldset['title']; ?></h3>
			<div class="jcf_inner_content">
				<form action="#" method="post" id="jcform_edit_fieldset">
					<fieldset>
						<input type="hidden" name="fieldset_id" value="<?php echo $fieldset['id']; ?>" />
						
						<p><label for="jcf_edit_fieldset_title"><?php _e('Title:', JCF_TEXTDOMAIN); ?></label> <input class="widefat" id="jcf_edit_fieldset_title" type="text" value="<?php echo esc_attr($fieldset['title']); ?>" /></p>
						
						<div class="field-control-actions">
							<h4>
								<a href="#" class="visibility_toggle" >
									<?php _e('Visibility rules', JCF_TEXTDOMAIN); ?>
									<span class="<?php echo !empty($fieldset['visibility_rules']) ? 'dashicons-arrow-up-alt2' : 'dashicons-arrow-down-alt2' ?> dashicons-before"></span>
								</a>
							</h4>
							<div id="visibility" class="<?php echo !empty($fieldset['visibility_rules']) ? '' : 'hidden' ?>">
								<?php if( !empty($fieldset['visibility_rules']) ): ?>
									<?php echo jcf_get_visibility_rules_html($fieldset['visibility_rules']); ?>
								<?php else: ?>
									<?php jcf_ajax_add_visibility_rules_form(); ?>
								<?php endif; ?>
							</div>
							<br class="clear"/>
							<div class="alignleft">
								<a href="#remove" class="field-control-remove"><?php _e('Delete', JCF_TEXTDOMAIN); ?></a> |
								<a href="#close" class="field-control-close"><?php _e('Close', JCF_TEXTDOMAIN); ?></a>
							</div>
							<div class="alignright">
								<?php echo print_loader_img(); ?>
								<input type="submit" value="<?php _e('Save', JCF_TEXTDOMAIN); ?>" class="button-primary" name="savefield">
							</div>
							<br class="clear"/>
						</div>
					</fieldset>
				</form>
			</div>
		</div>

		<?php
		$html = ob_get_clean();
		jcf_ajax_reposnse($html, 'html');
	}
	
	/**
	 * add form for new rule functions callback
	 */
	function jcf_ajax_add_visibility_rules_form() {
		global $jcf_post_type;
		$taxonomies = get_taxonomies( array('object_type' => (array)$jcf_post_type, 'show_ui' => true), 'objects' );
		$add_rule = !empty($_POST['add_rule']) ? $_POST['add_rule'] : false;

		if(!empty($_POST['edit_rule'])){
			$rule_id = $_POST['rule_id'] - 1;
			$fieldset_id = $_POST['fieldset_id'];
			$fieldset = jcf_fieldsets_get($fieldset_id);
			$edit_rule = $_POST['edit_rule']; 
			$visibility_rule = $fieldset['visibility_rules'][$rule_id];
			if($visibility_rule['based_on'] == 'taxonomy'){
				$terms = get_terms($visibility_rule['rule_taxonomy'], array('hide_empty' => false));
			}
			else{
				$templates = get_page_templates();
			}
		}

		ob_start();
		?>
		<fieldset id="fieldset_visibility_rules">
			<legend><?php !empty($edit_rule) ? _e('Edit rule', JCF_TEXTDOMAIN) : _e('Add rule', JCF_TEXTDOMAIN)  ?></legend>

			<?php // Status for fieldset ?>
			<div class="visibility-options">
				<p><?php _e('You are about to set the visibility option for this fieldset', JCF_TEXTDOMAIN); ?></p>
				<input type="radio" name="visibility_option" id="visibility-option-hide" value="hide" <?php echo (!empty($edit_rule) ? checked( $visibility_rule['visibility_option'], 'hide' ) : 'checked' );  ?> />
				<label for="visibility-option-hide"><?php _e('Hide fieldset', JCF_TEXTDOMAIN); ?></label>
				<br class="clear"/>
				<input type="radio" name="visibility_option" id="visibility-option-show" value="show" <?php checked( $visibility_rule['visibility_option'], 'show' ); ?> />
				<label for="visibility-option-show"><?php _e('Show fieldset', JCF_TEXTDOMAIN); ?></label>
			</div>
			
			<?php // Condition fields for rule ?>
			<div class="join-condition <?php echo ( (!empty($add_rule) || $rule_id != 0) ? '' : 'hidden' ); ?>" >
				<p>
					<label for="rule-join-condition"><?php _e('Join condition with previous rules with operator:', JCF_TEXTDOMAIN); ?></label>
					<br />
					<select name="join_condition" id="rule-join-condition">
						<option value="and" <?php selected($visibility_rule['join_condition'], 'and'); ?> ><?php _e('AND', JCF_TEXTDOMAIN); ?></option>
						<option value="or" <?php selected($visibility_rule['join_condition'], 'or'); ?> ><?php _e('OR', JCF_TEXTDOMAIN); ?></option>
					</select>
				</p>
			</div>

			<?php if($jcf_post_type != 'page'): // Form for post types wich are not page ?>
				<p><?php _e('Based on', JCF_TEXTDOMAIN); ?> <strong><?php _e('Taxonomy terms', JCF_TEXTDOMAIN); ?></strong></p>
				<input type="hidden" name="based_on" value="taxonomy" />
				<?php jcf_ajax_get_taxonomies_html($taxonomies, $visibility_rule['rule_taxonomy'], $terms, $visibility_rule['rule_taxonomy_terms']); ?>
			<?php else: // Form for post type wich is page ?>
				<p>
				<label for="rule-based-on"><?php _e('Based on:', JCF_TEXTDOMAIN); ?></label><br />
				<select name="based_on" id="rule-based-on">
					<option value="" disabled="disabled" <?php echo !empty($edit_rule) ? '' : 'selected'; ?> ><?php _e('Choose option', JCF_TEXTDOMAIN); ?></option>
					<option value="page_template" <?php selected( $visibility_rule['based_on'], 'page_tempalate' ); ?> >Page template</option>
					<?php if(!empty($taxonomies)):?>
						<option value="taxonomy" <?php selected( $visibility_rule['based_on'], 'taxonomy' ); ?> >Taxonomy</option>
					<?php endif; ?>	
				</select>
				</p>
				
				<div class="rules-options">
					<?php if($visibility_rule['based_on'] == 'taxonomy'): //Taxonomy options for post type page based on taxonomy ?>
						<?php jcf_ajax_get_taxonomies_html($taxonomies, $visibility_rule['rule_taxonomy'], $terms, $visibility_rule['rule_taxonomy_terms']); ?>
					<?php elseif($visibility_rule['based_on'] == 'page_template'): //Page template options ?>
						<?php jcf_ajax_get_page_templates_html($templates, $visibility_rule['rule_templates']); ?>
					<?php endif;?>
				</div>

			<?php endif; ?>

			<?php // From buttons ?>
			<?php if( !empty($edit_rule) ): ?>
				<input type="button" class="update_rule_btn button" data-rule_id="<?php echo $_POST['rule_id'];?>" name="update_rule" value="<?php _e('Update rule', JCF_TEXTDOMAIN); ?>"/>
			<?php else: ?>
				<input type="button" class="save_rule_btn button" name="save_rule" value="<?php _e('Save rule', JCF_TEXTDOMAIN); ?>"/>
			<?php endif;?>
			<?php if( $edit_rule || $add_rule ):?>
				<input type="button" class="cancel_rule_btn button" name="cancel_rule" value="<?php _e('Cancel', JCF_TEXTDOMAIN); ?>" />
			<?php endif;?>

		</fieldset>

		<?php
		$html = ob_get_clean();
		if(!empty($add_rule) || !empty($edit_rule)){
			jcf_ajax_reposnse($html, 'html');
		}
		else{
			echo $html;
		}
	}
	
	/**
	 * get base options for visibility rules functions callback
	 */
	function jcf_ajax_get_rule_options() {
		$rule = $_POST['rule'];
		global $jcf_post_type;
		ob_start();

		if( $rule == 'page_template' ) {
			$templates = get_page_templates(); 
			jcf_ajax_get_page_templates_html($templates);
		} 
		else { 
			$taxonomies = get_taxonomies( array('object_type' => (array)$jcf_post_type, 'show_ui' => true), 'objects' );
			jcf_ajax_get_taxonomies_html($taxonomies);
		} 

		$html = ob_get_clean();
		jcf_ajax_reposnse($html, 'html');
	}
	
	/**
	 * Get taxonomy terms options functions callback
	 */
	function jcf_ajax_get_taxonomy_terms() {
		$taxonomy = $_POST['taxonomy'];
		$terms = get_terms($taxonomy, array('hide_empty' => false));
		ob_start();
		jcf_ajax_get_taxonomy_terms_html($terms);
		$html = ob_get_clean();
		jcf_ajax_reposnse($html, 'html');
	}
	
	/**
	 * Get html for templates options for adding visibility rules
	 * @param array $templates
	 * @param array $current
	 */
	function jcf_ajax_get_page_templates_html($templates, $current = array()) {
		ob_start();
		?>
		<?php if( !empty($templates) ): ?>
			<div class="templates-options">
				<p>
					<p><?php _e('Choose templates:', JCF_TEXTDOMAIN); ?></p>
					<ul class="visibility-list-items">
					<?php $i=1; foreach( $templates as $name => $slug ): ?>
						<li>
							<input type="checkbox" name="rule_templates" value="<?php echo $slug; ?>" id="rule_taxonomy_term_<?php echo $i; ?>"
								<?php checked(in_array($slug, $current), true ); ?>/>
							<label for="rule_taxonomy_term_<?php echo $i; ?>"><?php echo $name; ?></label>
						</li>
					<?php $i++; endforeach; ?>
					</ul>
					<br class="clear">
				</p>
			</div>
		<?php else:?>
			<p><?php _e('No available templates', JCF_TEXTDOMAIN); ?></p>
		<?php endif;

		$html = ob_get_clean();
		echo $html;
	}

	/**
	 * Get html for taxonomies options for adding visibility rules
	 * @param array $taxonomies
	 * @param array $current
	 * @param array $terms
	 * @param array $current_term
	 */
	function jcf_ajax_get_taxonomies_html( $taxonomies = array(), $current_tax = array(), $terms = array(), $current_term = array() ) {
		ob_start();
		?>
		<?php if( !empty($taxonomies) ): ?>
			<div class="taxonomy-options">
				<p>
					<label for="rule-taxonomy"><?php _e('Choose taxonomy:', JCF_TEXTDOMAIN); ?></label>
					<br class="clear"/>
					<select name="rule_taxonomy" id="rule-taxonomy">
						<option value="" disabled="disabled" <?php selected(empty($current_tax)); ?> ><?php _e('Choose taxonomy', JCF_TEXTDOMAIN); ?></option>
						<?php foreach( $taxonomies as $slug => $taxonomy ): ?>
							<option value="<?php echo $slug; ?>" <?php selected($current_tax, $slug); ?> ><?php echo $taxonomy->labels->singular_name; ?></option>
						<?php	endforeach; ?>
					</select>
				</p>
				<div class="taxonomy-terms-options">
					<?php if(!empty($terms)) :?>
						<?php jcf_ajax_get_taxonomy_terms_html($terms, $current_term); ?>
					<?php endif;?>
				</div>
			</div>
		<?php else: ?>
			<p><?php _e('No available taxonomies', JCF_TEXTDOMAIN); ?></p>
		<?php endif;

		$html = ob_get_clean();
		echo $html;
	}

	/**
	 * Get html for taxonomy terms options for adding visibility rules
	 * @param array $terms
	 * @param array $current_term
	 */
	function jcf_ajax_get_taxonomy_terms_html($terms, $current_term = array()) {
		ob_start();
		?>
		<?php if( !empty($terms) ): ?>
			<p>
				<p><?php _e('Choose terms:', JCF_TEXTDOMAIN); ?></p>
				<?php if( count($terms) <= 20 ) :?>
					<ul class="visibility-list-items">
					<?php $i=1; foreach( $terms as $term ): ?>
						<li>
							<input type="checkbox" name="rule_taxonomy_terms" value="<?php echo $term->term_id; ?>"
								<?php checked(in_array($term->term_id, $current_term), true ); ?>
								   id="rule_taxonomy_term_<?php echo $term->term_id; ?>" />
							<label for="rule_taxonomy_term_<?php echo $term->term_id; ?>"><?php echo $term->name; ?></label>
						</li>
					<?php $i++; endforeach; ?>
					</ul>
				<?php else: ?>
					<p>
						<input type="text" id="new-term" name="newterm" class="newterm form-input-tip" size="16" autocomplete="on" value="">
						<input type="button" class="button termadd" value="Add">
					</p>
				<?php endif; ?>
				<br class="clear">
			</p>
		<?php else: ?>
			<p><?php _e('No available terms', JCF_TEXTDOMAIN); ?></p>
		<?php endif;
		$html = ob_get_clean();
		echo $html;
	}

	/**
	 * Get table with added visibility rules
	 * @param array $visibility_rules
	 * @return html
	 */
	function jcf_get_visibility_rules_html($visibility_rules){
		ob_start(); 
		?>

			<div class="rules">
				<?php if(!empty($visibility_rules)): ?>
				<table class="wp-list-table widefat fixed fieldset-visibility-rules">
					<thead>
						<tr>
							<th style="width: 10%;">№</th>
							<th><?php _e('Rule', JCF_TEXTDOMAIN); ?></th>
							<th style="width: 20%;"><?php _e('Options', JCF_TEXTDOMAIN); ?></th>
						</tr>
					</thead>
					<tbody>
					<?php	foreach($visibility_rules as $key => $rule): ?>
						<?php	
							$rule_text = '';
							$rule_text .= ucfirst($rule['visibility_option']);
							$rule_text .= ' when ';
							if($rule['based_on'] == 'taxonomy'){
								$term_text = '';
								if(!empty($rule['rule_taxonomy_terms'])){
									foreach($rule['rule_taxonomy_terms'] as $key_term => $term) {
										$term_obj = get_term_by('id', $term, $rule['rule_taxonomy']);
										$term_text .= ($key_term != 0 ? ', ' . $term_obj->name : $term_obj->name);
									}
								}
								$tax = get_taxonomy($rule['rule_taxonomy']);
								$rule_text .=  '<strong>'.$tax->labels->singular_name.'</strong>';
								$rule_text .=  ' in ';
								$rule_text .= '<strong>' . $term_text . '</strong>';
							}
							else{
								$templates = get_page_templates();
								$tpl_text = '';
								foreach($rule['rule_templates'] as $key_tpl => $template) {
									$tpl_name = array_search($template, $templates);
									$tpl_text .= ($key_tpl != 0 ? ', ' . $tpl_name : $tpl_name);
								}
								$rule_text .= '<strong>'.ucfirst(str_replace('_', ' ', $rule['based_on'] )).'</strong>';
								$rule_text .=  ' in ';
								$rule_text .= '<strong>'.$tpl_text.'<strong>';
							}
						?>
						
						<tr class="visibility_rule_<?php echo $key+1; ?>">
							<td><?php echo ($key+1); ?></td>
							<td>
								<?php if($key != 0):?>
									<strong><?php echo strtoupper($rule['join_condition']); ?></strong><br/>
								<?php endif;?>
								<?php echo $rule_text; ?>
							</td>
							<td>
								<a href="#" class="dashicons-before dashicons-edit edit-rule" data-rule_id="<?php echo $key+1; ?>"></a>
								<a href="#" class="dashicons-before dashicons-no remove-rule" data-rule_id="<?php echo $key+1; ?>"></a><?php ?>
							</td>
						</tr>
					<?php	endforeach; ?>
					</tbody>
				</table>
				<?php endif; ?>
				<p><input type="button" class="add_rule_btn button" name="add_rule" value="<?php _e('Add rule', JCF_TEXTDOMAIN); ?>"/></p>
			</div>

		<?php 
		$rules = ob_get_clean(); 
		return $rules;
	}

	// autocomplete for input
	function jcf_ajax_visibility_autocomplete(){
		global $wpdb;
		$taxonomy = $_POST['taxonomy'];
		$term = $_POST['term'];
		$query = "SELECT t.term_id, t.name
			FROM wp_terms AS t
			LEFT JOIN wp_term_taxonomy AS tt ON t.term_id = tt.term_id
			WHERE t.name LIKE '%$term%' AND tt.taxonomy = '$taxonomy'";
		$terms = $wpdb->get_results($query);

		$response = array();
		foreach($terms as $p){
			$response[] = array(
				'id' => $p->term_id,
				'label' => $p->name,
				'value' => $p->name,
			);
		}
		$json = json_encode($response);
		
		header( "Content-Type: application/json" );
		echo $json;
		exit();
	}

	/**
	 * Save rules for visibility functions callback
	 */
	function jcf_ajax_save_visibility_rules(){
		$data = $_POST;
		if(!empty($data['rule_id'])){
			jcf_fieldsets_update($data['fieldset_id'], array('rules' => array('update' => $data['rule_id'], 'data' => $data['visibility_rules'])));
		}
		else{
			jcf_fieldsets_update($data['fieldset_id'], array('rules' => $data['visibility_rules']));
		}
		$fieldset = jcf_fieldsets_get($data['fieldset_id']);
		$resp = jcf_get_visibility_rules_html($fieldset['visibility_rules']);
		jcf_ajax_reposnse($resp, 'html');
	}

	/**
	 * Delete rule for visibility functions callback
	 */
	function jcf_ajax_delete_visibility_rule(){
		$data = $_POST;

		jcf_fieldsets_update($data['fieldset_id'], array('rules' => array('remove' => $data['rule_id'])));
		$fieldset = jcf_fieldsets_get($data['fieldset_id']);
		$resp = jcf_get_visibility_rules_html($fieldset['visibility_rules']);
		jcf_ajax_reposnse($resp, 'html');
	}

	/**
	 * Update rule functions callback
	 */
	function  jcf_ajax_update_visibility_rule(){
		$data = $_POST;

		jcf_fieldsets_update($data['fieldset_id'], array('rules' => array('update' => $data['rule_id'])));
		$fieldset = jcf_fieldsets_get($data['fieldset_id']);
		$resp = jcf_get_visibility_rules_html($fieldset['visibility_rules']);
		jcf_ajax_reposnse($resp, 'html');
		
	}

	/**
	 * save fieldset functions callback
	 */
	function jcf_ajax_update_fieldset(){
		$f_id = $_POST['fieldset_id'];
		$fieldset = jcf_fieldsets_get($f_id);

		if(empty($fieldset)){
			jcf_ajax_reposnse( array('status' => "0", 'error'=>__('Wrong data passed.', JCF_TEXTDOMAIN)) );
		}

		$title = strip_tags(trim($_POST['title']));
		if( empty($title) ){
			jcf_ajax_reposnse( array('status' => "0", 'error'=>__('Title field is required.', JCF_TEXTDOMAIN)) );
		}

		$fieldset['title'] = $title;
		jcf_fieldsets_update($f_id, $fieldset);
		jcf_ajax_reposnse( array('status' => "1", 'title' => $title) );
	}

	/**
	 * fields order change callback
	 */
	function jcf_ajax_order_fieldsets(){
		$order  = explode(',' ,trim($_POST['fieldsets_order'], ','));
		if(!empty($_POST['fieldsets_order'])){
			jcf_fieldsets_order($order);
		}

		$resp = array('status' => '1');
		jcf_ajax_reposnse($resp, 'json');
	}
	
	/**
	 *  add field form show callback
	 */
	function jcf_ajax_add_field(){
		
		$field_type =  $_POST['field_type'];
		$fieldset_id = $_POST['fieldset_id'];
		$collection_id = (isset($_POST['collection_id'])?$_POST['collection_id']:'');
		
		$field_obj = jcf_init_field_object($field_type, $fieldset_id, $collection_id);
		
		$html = $field_obj->do_form();
		jcf_ajax_reposnse($html, 'html');
		
	}

	/**
	 * save field from the form callback
	 */
	function jcf_ajax_save_field(){

		$field_type =  $_POST['field_id'];
		$fieldset_id = $_POST['fieldset_id'];
		$collection_id = (isset($_POST['collection_id'])?$_POST['collection_id']:'');
		
		$field_obj = jcf_init_field_object($field_type, $fieldset_id, $collection_id);
		
		$resp = $field_obj->do_update();
		if(isset($resp['id_base']) && $resp['id_base'] == 'collection'){
			ob_start();
			Just_Field_Collection::settings_row($resp['id'],$fieldset_id);
			$resp["collection_fields"] = ob_get_clean();
		}
		jcf_ajax_reposnse($resp, 'json');

	}
	
	/**
	 * delete field processor callback
	 */
	function jcf_ajax_delete_field(){
		$field_id = $_POST['field_id'];
		$fieldset_id = $_POST['fieldset_id'];
		$collection_id = (isset($_POST['collection_id'])?$_POST['collection_id']:'');
		if($collection_id){
			$field_obj = jcf_init_field_object($collection_id, $fieldset_id);
			$field_obj->delete_field($field_id);
		} else {
			$field_obj = jcf_init_field_object($field_id, $fieldset_id);
			$field_obj->do_delete();			
		}
		
		$resp = array('status' => '1');
		jcf_ajax_reposnse($resp, 'json');
	}
	
	/**
	 * edit field show form callback
	 */
	function jcf_ajax_edit_field(){
		$field_id = $_POST['field_id'];
		$fieldset_id = $_POST['fieldset_id'];
		$collection_id = (isset($_POST['collection_id'])?$_POST['collection_id']:'');
		
		$field_obj = jcf_init_field_object($field_id, $fieldset_id,$collection_id);
		$html = $field_obj->do_form();
		jcf_ajax_reposnse($html, 'html');
	}
	
	/**
	 * fields order change callback
	 */
	function jcf_ajax_fields_order(){
		$fieldset_id = $_POST['fieldset_id'];
		$order  = trim($_POST['fields_order'], ',');
		
		$fieldset = jcf_fieldsets_get($fieldset_id);
		$new_fields = explode(',', $order);
		
		$fieldset['fields'] = array();
		foreach($new_fields as $field_id){
			$fieldset['fields'][$field_id] = $field_id;
		}
		
		jcf_fieldsets_update($fieldset_id, $fieldset);
		
		$resp = array('status' => '1');
		jcf_ajax_reposnse($resp, 'json');
	}
	
	/**
	 * print response (encode to json if needed) callback
	 */
	function jcf_ajax_reposnse( $resp, $format = 'json' ){
		if( $format == 'json' ){
			$resp = json_encode($resp);
			header( "Content-Type: application/json" );
		}
		echo $resp;
		exit();
	}

	/**
	 * export fields from form callback
	 */
	function jcf_ajax_export_fields_form(){
		$jcf_read_settings = jcf_get_read_settings();
		if( $jcf_read_settings != JCF_CONF_SOURCE_DB ){
			$jcf_settings = jcf_get_all_settings_from_file();
		}
		else{
			$jcf_settings = jcf_get_all_settings_from_db();
		}

		$post_types = !empty($jcf_settings['post_types']) ? $jcf_settings['post_types'] : jcf_get_post_types();
		$fieldsets =$jcf_settings['fieldsets'];
		$field_settings = $jcf_settings['field_settings'];
		$registered_fields = jcf_get_registered_fields();

		// load template
		header('Content-Type: text/html; charset=utf-8');
		include( JCF_ROOT . '/templates/export.tpl.php' );
		exit();
	}

	/**
	 * export fields callback
	 */
	function jcf_ajax_export_fields(){
		if( $_POST['export_fields'] && !empty($_POST['export_data']) ) {
			$export_data = $_POST['export_data'];
			$export_data = json_encode($export_data);
			$filename = 'jcf_export' . date('Ymd-his') . '.json';
			header('Content-Type: text/json; charset=utf-8');
			header("Content-Disposition: attachment;filename=" . $filename);
			header("Content-Transfer-Encoding: binary ");
			echo $export_data;
			exit();
		}
	}

	/**
	 * import fields callback
	 */
	function jcf_ajax_import_fields(){
		if( !empty($_POST['action']) && $_POST['action'] == 'jcf_import_fields' ){
			if(!empty($_FILES['import_data']['name']) ){
				$path_info = pathinfo($_FILES['import_data']['name']);

				if( $path_info['extension'] == 'json'){
					$uploaddir = get_home_path() . "wp-content/uploads/";
					$uploadfile = $uploaddir . basename($_FILES['import_data']['name']);

					if ( is_readable($_FILES['import_data']['tmp_name']) ){
						$post_types = jcf_get_settings_from_file($_FILES['import_data']['tmp_name']);
						unlink($_FILES['import_data']['tmp_name']);
						if( empty($post_types) ){
							$notice = array('error', __('<strong>Import FAILED!</strong> File do not contain fields settings data..', JCF_TEXTDOMAIN));
						}
					}
					else{
						$notice = array('error', __('<strong>Import FAILED!</strong> Can\'t read uploaded file.', JCF_TEXTDOMAIN));
					}
				}
				else{
					$notice = array('error', __('<strong>Import FAILED!</strong> Please upload correct file format.', JCF_TEXTDOMAIN));
				}
			}
			else{
				$notice = array('error', __('<strong>Import FAILED!</strong> Import file is missing.', JCF_TEXTDOMAIN));
			}
		}
		if( !empty($notice) )
			jcf_add_admin_notice($notice[0], $notice[1]);
		
		header('Content-Type: text/html; charset=utf-8');
		include( JCF_ROOT . '/templates/import.tpl.php' );
		exit();
	}

	/**
	 * check file callback
	 */
	function jcf_ajax_check_file(){
		$jcf_read_settings = $_POST['jcf_read_settings'];
		if($jcf_read_settings == JCF_CONF_SOURCE_FS_THEME OR $jcf_read_settings == JCF_CONF_SOURCE_FS_GLOBAL){
			$file = jcf_get_settings_file_path($jcf_read_settings);
			
			if($jcf_read_settings == JCF_CONF_SOURCE_FS_THEME){
				$msg = __("The settings will be written to your theme folder.\nIn case you have settings there, they will be overwritten.\nPlease confirm that you want to continue.", JCF_TEXTDOMAIN);
			}
			else{
				$msg = __("The settings will be written to folder wp-conten/jcf-settings.\nIn case you have settings there, they will be overwritten.\nPlease confirm that you want to continue.", JCF_TEXTDOMAIN);
			}
			
			if( file_exists( $file ) ) {
				$resp = array('status' => '1', 'msg' => $msg);
			}
			else{
				$resp = array('status' => '1', 'file' => '1');
			}
		}
		else{
			$resp = array('status' => '1');
		}
		jcf_ajax_reposnse($resp, 'json');
	}
