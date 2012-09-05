<?php

require('../SimpleImage.class.php');

try {
	
	$img = new SimpleImage();
	
	// Convert to GIF
	$img->load('butterfly.jpg')->save('butterfly-convert-to-gif.gif');
	
	// Strip exif data (just load and save)
	$img->load('butterfly.jpg')->save('butterfly-strip-exif.jpg');
	
	// Flip horizontal
	$img->load('butterfly.jpg')->flip('x')->save('butterfly-flip-horizontal.jpg');
	
	// Flip vertical
	$img->load('butterfly.jpg')->flip('y')->save('butterfly-flip-vertical.jpg');
	
	// Flip both
	$img->load('butterfly.jpg')->flip('x')->flip('y')->save('butterfly-flip-both.jpg');
	
	// Rotate 90
	$img->load('butterfly.jpg')->rotate(90)->save('butterfly-rotate-90.jpg');
	
	// Auto-orient
	$img->load('butterfly.jpg')->auto_orient()->save('butterfly-auto-orient.jpg');
	
	// Resize
	$img->load('butterfly.jpg')->resize(320, 239)->save('butterfly-resize.jpg');

	// Fit to width
	$img->load('butterfly.jpg')->fit_to_width(100)->save('butterfly-fit-to-width.jpg');

	// Fit to height
	$img->load('butterfly.jpg')->fit_to_height(100)->save('butterfly-fit-to-height.jpg');
	
	// Best fit
	$img->load('butterfly.jpg')->best_fit(100, 400)->save('butterfly-best-fit.jpg');
	
	// Crop
	$img->load('butterfly.jpg')->crop(160, 110, 460, 360)->save('butterfly-crop.jpg');
	
	// Square crop
	$img->load('butterfly.jpg')->square_crop(75)->save('butterfly-square-crop.jpg');
	
	// Desaturate
	$img->load('butterfly.jpg')->desaturate()->save('butterfly-desaturate.jpg');
	
	// Invert
	$img->load('butterfly.jpg')->invert()->save('butterfly-invert.jpg');
	
	// Brighten
	$img->load('butterfly.jpg')->brightness(100)->save('butterfly-brighten.jpg');
	
	// Darken
	$img->load('butterfly.jpg')->brightness(-100)->save('butterfly-darken.jpg');
	
	// Contrast
	$img->load('butterfly.jpg')->contrast(-50)->save('butterfly-contrast.jpg');
	
	// Colorize
	$img->load('butterfly.jpg')->colorize('#F00', .5)->save('butterfly-colorize.jpg');
	
	// Edge Detect
	$img->load('butterfly.jpg')->edges()->save('butterfly-edges.jpg');
	
	// Mean Removal
	$img->load('butterfly.jpg')->mean_remove()->save('butterfly-mean-remove.jpg');
	
	// Emboss
	$img->load('butterfly.jpg')->emboss()->save('butterfly-emboss.jpg');
	
	// Selective Blur
	$img->load('butterfly.jpg')->blur('selective', 10)->save('butterfly-blur-selective.jpg');
	
	// Gaussian Blur
	$img->load('butterfly.jpg')->blur('gaussian', 10)->save('butterfly-blur-gaussian.jpg');
	
	// Sketch
	$img->load('butterfly.jpg')->sketch()->save('butterfly-sketch.jpg');

	// Smooth
	$img->load('butterfly.jpg')->smooth(6)->save('butterfly-smooth.jpg');

	// Pixelate
	$img->load('butterfly.jpg')->pixelate(8)->save('butterfly-pixelate.jpg');

	// Sepia
	$img->load('butterfly.jpg')->sepia(8)->save('butterfly-sepia.jpg');

	// Overlay
	$img->load('butterfly.jpg')->overlay('overlay.png', 'bottom right', .8)->save('butterfly-overlay.jpg');
	
	// Text
	$img->load('butterfly.jpg')->text('Butterfly', 'delicious.ttf', 32, '#FFFFFF', 'bottom', 0, -20)->save('butterfly-text.jpg');
	
	echo '<span style="color: green;">All images processed and created in /example/</span>';
	
} catch(Exception $e) {
	
	echo '<span style="color: red;">' . $e->getMessage() . '</span>';
	
}