<?php
	function e404( $msg = ''){
		header('HTTP/1.0 404 Not Found');
		die( $msg );
	}
	
	if(empty($_GET['image']) || empty($_GET['size'])){
		e404("no params");
	}
	
	$imgfile = $_GET['image'];
	$new_size = explode('x', $_GET['size']);
	$qty = !empty($_GET['qty'])? (int)$_GET['qty'] : 95;

	// get wp dir, include wp functions
	$dirname = str_replace('\\', '/', dirname(__FILE__));
	$wp_dir = array_shift( explode('/wp-content/', $dirname) );
	include_once($wp_dir . '/wp-config.php' );
	
	$cachedir = array_shift( explode('/plugins/', $dirname) ) . '/uploads/jcfupload';
	
	// check file extension
	$filetype = wp_check_filetype($imgfile);
	if( empty($filetype['ext']) ){
		e404( "no file extension in source file" );
	}
	$ext = $filetype['ext'];
	
	// check if thumb already exists:
	$hash = md5($imgfile.$new_size[0].'x'.$new_size[1]);
	$thumbfile = $cachedir . '/' . $hash . '.' . $ext;
	if( is_file($thumbfile) ){
		//$src = get_bloginfo('home') . '/' . basename($thumbfile);
		header('Content-Type: '.$filetype['type']);
		$imgcontent = file_get_contents($thumbfile);
		echo $imgcontent;
		exit;
	}

	// if no - need to generate the file
	
	// check directory exists
	if( !is_dir($cachedir) ){
		if( !mkdir( $cachedir, 0777 ) ){
			e404("can't create cache dir");
		}
		@chmod($cachedir, 0777);
	}
	$imgcontent = file_get_contents($imgfile);
	if(!$imgcontent){
		e404("can't read file");
	}

	// copy image content into temp filename
	$tmpfname = tempnam($cachedir, "tmp.");
	$tmpfname = str_replace('\\', '/', $tmpfname);
	
	$tmpfname .= '.'.$ext;
	$fp = fopen($tmpfname, "w");
	fwrite($fp, $imgcontent);
	fclose($fp);
	@chmod($tmpfname, 0777);
	
	// create file
	$thumb = image_resize($tmpfname, $new_size[0], $new_size[1], '', '', dirname($thumbfile), $qty);
	unlink($tmpfname);
	
	if( !is_string($thumb) && !empty($thumb->errors) ){
		// print original image
		header('Content-Type: '.$filetype['type']);
		echo $imgcontent;
		exit;
		e404("error resize");
	}
	
	// rename file to correct name
	@chmod($thumb, 0777);
	rename($thumb, $thumbfile);

	// print image
	header('Content-Type: '.$filetype['type']);
	$imgcontent = file_get_contents($thumbfile);
	echo $imgcontent;
	exit;
?>