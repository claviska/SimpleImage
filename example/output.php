<?php

require('../SimpleImage.class.php');

if( !is_dir('processed/') ) mkdir('processed/');

try {
	
	// Flip the image and output it directly to the browser
	$img = new SimpleImage();
	$img->load('butterfly.jpg')->flip('x')->output('png');
	
} catch(Exception $e) {
	
	echo '<span style="color: red;">' . $e->getMessage() . '</span>';
	
}