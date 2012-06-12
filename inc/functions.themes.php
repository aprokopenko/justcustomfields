<?php
	
	/**
	 *	print image from Just Custom Field > Upload Media (type "image")
	 *	@param  $post_id	int		The Post ID you want to get data
	 *	@param  $slug		string	Custom Field Slug
	 *	@param  $args		array	Params for styling
	 */
	function just_custom_image( $post_id, $slug, $args = array() ){
		// defaults
		$args = wp_parse_args($args, array(
			'before' => '',
			'after' => '',
			'echo' => true,
			'alt' => '',
			'width' => '',
			'height' => '',
		));
		
		$images = get_post_meta($post_id, $slug, true);
		if( !empty($images[0]['image']) ){
			if( !empty($images[0]['title']) && empty($args['alt']) ){
				$args['alt'] = $images[0]['title'];
			}
			
			$html = '';
			// below html
			$html .= $args['before'];
			
			// width/height
			$width = '';
			if( !empty($args['width']) ){
				$width = ' width="'.$args['width'].'" ';
			}
			$height = '';
			if( !empty($args['height']) ){
				$height = ' height="'.$args['height'].'" ';
			}
			
			// <img> tag
			$html .= '<img src="'.$images[0]['image'].'" alt="'.esc_attr($args['alt']).'" '.$width.$height.' />';
			// after html
			$html .= $args['after'];
			
			if( $args['echo'] ){
				echo $html;
			}
			
			return $html;
		}
		
		return false;
	}
	
	/**
	 *	print list of images from Just Custom Field > Upload Media (type "image")
	 *	@param  $post_id	int		The Post ID you want to get data
	 *	@param  $slug		string	Custom Field Slug
	 *	@param  $args		array	Params for styling
	 */
	function just_custom_images_list( $post_id, $slug, $args = array() ){
		// defaults
		$args = wp_parse_args($args, array(
			'before' => '<ul class="images-list">',
			'after' => '</ul>',
			'before_item' => '<li>',
			'after_item' => '</li>',
			'echo' => true,
			'width' => '',
			'height' => '',
		));
		
		$images = get_post_meta($post_id, $slug, true);
		if( !empty($images[0]['image']) ){
			
			$html = '';
			// below html
			$html .= $args['before'];

			foreach($images as $img){
				$alt = '';
				if( !empty($img['title']) ){
					$alt = $img['title'];
				}
								
				// width/height
				$width = '';
				if( !empty($args['width']) ){
					$width = ' width="'.$args['width'].'" ';
				}
				$height = '';
				if( !empty($args['height']) ){
					$height = ' height="'.$args['height'].'" ';
				}
				
				// <img> tag
				$html .= $args['before_item'];
				$html .= '<img src="'.$img['image'].'" alt="'.esc_attr($alt).'" '.$width.$height.' />';
				$html .= $args['after_item'];
			}
			
			// after html
			$html .= $args['after'];

			if( $args['echo'] ){
				echo $html;
			}
			
			return $html;
		}
		
		return false;		
	}
	
