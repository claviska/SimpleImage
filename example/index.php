<?php
namespace	abeautifulsite;
use			Exception;

require '../src/abeautifulsite/SimpleImage.php';

if (!is_dir('processed/')) {
	mkdir('processed/');
}

try {
	//
	// WARNING: This will create a lot of images in the /processed folder
	//

	$img = new SimpleImage();

	// Create from scratch
	$img->create(200, 100, '#f00')->save('processed/create-from-scratch.gif');

	// Convert to GIF
	$img->load('butterfly.jpg')->save('processed/butterfly-convert-to-gif.gif');

	// Strip exif data (just load and save)
	$img->load('butterfly.jpg')->save('processed/butterfly-strip-exif.jpg');

	// Flip horizontal
	$img->load('butterfly.jpg')->flip('x')->save('processed/butterfly-flip-horizontal.jpg');

	// Flip vertical
	$img->load('butterfly.jpg')->flip('y')->save('processed/butterfly-flip-vertical.jpg');

	// Flip both
	$img->load('butterfly.jpg')->flip('x')->flip('y')->save('processed/butterfly-flip-both.jpg');

	// Rotate 90
	$img->load('butterfly.jpg')->rotate(90)->save('processed/butterfly-rotate-90.jpg');

	// Auto-orient
	$img->load('butterfly.jpg')->auto_orient()->save('processed/butterfly-auto-orient.jpg');

	// Resize
	$img->load('butterfly.jpg')->resize(320, 239)->save('processed/butterfly-resize.jpg');

	// Adaptive resize
	$img->load('butterfly.jpg')->adaptive_resize(100, 75)->save('processed/butterfly-adaptive-resize.jpg');

	// Fit to width
	$img->load('butterfly.jpg')->fit_to_width(100)->save('processed/butterfly-fit-to-width.jpg');

	// Fit to height
	$img->load('butterfly.jpg')->fit_to_height(100)->save('processed/butterfly-fit-to-height.jpg');

	// Best fit
	$img->load('butterfly.jpg')->best_fit(100, 400)->save('processed/butterfly-best-fit.jpg');

	// Crop
	$img->load('butterfly.jpg')->crop(160, 110, 460, 360)->save('processed/butterfly-crop.jpg');

	// Desaturate
	$img->load('butterfly.jpg')->desaturate()->save('processed/butterfly-desaturate.jpg');

	// Invert
	$img->load('butterfly.jpg')->invert()->save('processed/butterfly-invert.jpg');

	// Brighten
	$img->load('butterfly.jpg')->brightness(100)->save('processed/butterfly-brighten.jpg');

	// Darken
	$img->load('butterfly.jpg')->brightness(-100)->save('processed/butterfly-darken.jpg');

	// Contrast
	$img->load('butterfly.jpg')->contrast(-50)->save('processed/butterfly-contrast.jpg');

	// Colorize
	$img->load('butterfly.jpg')->colorize('#F00', .5)->save('processed/butterfly-colorize.jpg');

	// Edge Detect
	$img->load('butterfly.jpg')->edges()->save('processed/butterfly-edges.jpg');

	// Mean Removal
	$img->load('butterfly.jpg')->mean_remove()->save('processed/butterfly-mean-remove.jpg');

	// Emboss
	$img->load('butterfly.jpg')->emboss()->save('processed/butterfly-emboss.jpg');

	// Selective Blur
	$img->load('butterfly.jpg')->blur('selective', 10)->save('processed/butterfly-blur-selective.jpg');

	// Gaussian Blur
	$img->load('butterfly.jpg')->blur('gaussian', 10)->save('processed/butterfly-blur-gaussian.jpg');

	// Sketch
	$img->load('butterfly.jpg')->sketch()->save('processed/butterfly-sketch.jpg');

	// Smooth
	$img->load('butterfly.jpg')->smooth(6)->save('processed/butterfly-smooth.jpg');

	// Pixelate
	$img->load('butterfly.jpg')->pixelate(8)->save('processed/butterfly-pixelate.jpg');

	// Sepia
	$img->load('butterfly.jpg')->sepia(8)->save('processed/butterfly-sepia.jpg');

	// Overlay
	$img->load('butterfly.jpg')->overlay('overlay.png', 'bottom right', .8)->save('processed/butterfly-overlay.jpg');

	// Text
	$img->load('butterfly.jpg')->text('Butterfly', __DIR__.'/delicious.ttf', 32, '#FFFFFF', 'bottom', 0, -20)->save('processed/butterfly-text.jpg');

	echo '<span style="color: green;">All processed images are saved in /example/processed</span>';

} catch (Exception $e) {
	echo '<span style="color: red;">'.$e->getMessage().'</span>';
}