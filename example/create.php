<?php

require('../SimpleImage.class.php');

if( !is_dir('processed/') ) mkdir('processed/');

try {
	
	// Flip the image and output it directly to the browser
	$img = new SimpleImage();
	$img->create(500, 200, '#FFCC00')->text("Dynamicly Created Image", 'delicious.ttf');

  $img->save('processed/created-image.png');

  $img->output('png');
	
} catch(Exception $e) {
	
	echo '<span style="color: red;">' . $e->getMessage() . '</span>';
	
}