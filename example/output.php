<?php
function __autoload($class)  
{  
  $filename = str_replace('\\', '/', $class) . '.php';  
  @require_once 'classes/'.$filename;  
}

if( !is_dir('processed/') ) mkdir('processed/');

try {
	
	// Flip the image and output it directly to the browser
	$img = new Simple\Image\Obj();
	$img->load('butterfly.jpg')->flip('x')->output('png');
	
} catch(Exception $e) {
	
	echo '<span style="color: red;">' . $e->getMessage() . '</span>';
	
}