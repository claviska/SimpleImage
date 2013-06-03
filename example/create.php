<?php

require('../SimpleImage.class.php');

if( !is_dir('processed/') ) mkdir('processed/');

try {
	
	// Create an image from scratch
	$img = new SimpleImage();
	$img->create(500, 200, '#FFCC00')->text('Dynamically Created Image', 'delicious.ttf');

  $img->save('processed/created-image.png');

  // If you use create function instead of loading image
  // you have to define output extension
  $img->output('png');
	
} catch(Exception $e) {
	
	echo '<span style="color: red;">' . $e->getMessage() . '</span>';
	
}