<?php

require('../SimpleImage.class.php');

if( !is_dir('processed/') ) mkdir('processed/');

try {
	
	$img = new SimpleImage();
	
	$img->load('butterfly.jpg');
	$img->overlay('overlay.png', 'center center', .8);	
	$img->text('Butterfly', 'delicious.ttf', 32, '#00f', 'bottom', 0, -20);

	$img->output(100);
	
} catch(Exception $e) {
	
	echo '<span style="color: red;">' . $e->getMessage() . '</span>';
	
}