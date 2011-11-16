<?php
/*

  The PHP Simple Image class - v1.0
  
    By Cory LaViska - http://abeautifulsite.net/
  
  License
  
    This software is dual-licensed under the GNU General Public License and 
    the MIT License and is copyright A Beautiful Site, LLC.

*/

class Simple_Image {
	
	
	// Loads an image into a resource variable and gets the appropriate image information
	private function load($src) {
		
		$info = getimagesize($src);
		if( !$info ) return false;
		
		switch( $info['mime'] ) {
			
			case 'image/gif':
				$image = imagecreatefromgif($src);
			break;
			
			case 'image/jpeg':
				$image = imagecreatefromjpeg($src);
			break;
			
			case 'image/png':
				$image = imagecreatefrompng($src);
			break;
			
			default:
				// Unsupported image type
				return false;
			break;
			
		}
		
		return array($image, $info);
		
	}
	
	
	// Saves an image resource to file
	private function save($image, $filename, $type, $quality = null) {
		
		switch( $type ) {
			
			case 'image/gif':
				return imagegif($image, $filename);
			break;
			
			case 'image/jpeg':
				if( $quality == null ) $quality = 85;
				if( $quality < 0 ) $quality = 0;
				if( $quality > 100 ) $quality = 100;
				return imagejpeg($image, $filename, $quality);
			break;
			
			case 'image/png':
				if( $quality == null ) $quality = 9;
				if( $quality > 9 ) $quality = 9;
				if( $quality < 1 ) $quality = 0;
				return imagepng($image, $filename, $quality);
			break;
			
			default:
				// Unsupported image type
				return false;
			break;
			
		}
		
	}
	
	
	// Same as PHP's imagecopymerge() function, except preserves alpha-transparency in 24-bit PNGs
    private function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct){
		
		$cut = imagecreatetruecolor($src_w, $src_h);
		imagecopy($cut, $dst_im, 0, 0, $dst_x, $dst_y, $src_w, $src_h);
		imagecopy($cut, $src_im, 0, 0, $src_x, $src_y, $src_w, $src_h);
		return imagecopymerge($dst_im, $cut, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct);
		
    }
	
	
	// Converts a hex color value to its RGB equivalent
	private function hex2rgb($hex_color) {
		
		if( $hex_color[0] == '#' ) $hex_color = substr($hex_color, 1);
		if( strlen($hex_color) == 6 ) {
			list($r, $g, $b) = array(
				$hex_color[0] . $hex_color[1],
				$hex_color[2] . $hex_color[3],
				$hex_color[4] . $hex_color[5]
			);
		} elseif( strlen($hex_color) == 3 ) {
			list($r, $g, $b) = array(
				$hex_color[0] . $hex_color[0],
				$hex_color[1] . $hex_color[1],
				$hex_color[2] . $hex_color[2]
			);
		} else {
			return false;
		}
		
	    return array(
			'r' => hexdec($r),
			'g' => hexdec($g),
			'b' => hexdec($b)
		);
		
	}
	
	
	// Convert an image from one type to another; output type is determined by $dest's file extension
	static function convert($src, $dest, $quality = null) {
		
		$img = new Simple_Image;
		list($original, $info) = $img->load($src);
		
		switch( strtolower(preg_replace('/^.*\./', '', $dest)) ) {
			
			case 'gif':
				return $img->save($original, $dest, 'image/gif');
			break;
			
			case 'jpg':
			case 'jpeg':
				return $img->save($original, $dest, 'image/jpeg', $quality);
			break;
			
			case 'png':
				return $img->save($original, $dest, 'image/png', $quality);
			break;
			
			default:
				// Unsupported image type
				return false;
			break;
			
		}
		
		
	}
	
	
	// Flip an image horizontally or vertically
	static function flip($src, $dest, $direction, $quality = null) {
		
		$img = new Simple_Image;
		list($original, $info) = $img->load($src);
		
		$new = imagecreatetruecolor($info[0], $info[1]);
		
		switch( strtolower($direction) ) {
			
			case 'v':
			case 'vertical':
			case 'y':
				for ($y = 0; $y < $info[1]; $y++) imagecopy($new, $original, 0, $y, 0, $info[1] - $y - 1, $info[0], 1);
			break;
			
			case 'h':
			case 'horizontal':
			case 'x':
				for ($x = 0; $x < $info[0]; $x++) imagecopy($new, $original, $x, 0, $info[0] - $x - 1, 0, 1, $info[1]);
			break;
			
		}
		
		return $img->save($new, $dest, $info['mime'], $quality);
		
	}
	
	
	// Rotate an image
	static function rotate($src, $dest, $angle = 270, $bg_color = 0, $quality = null) {
		
		$img = new Simple_Image;
		list($original, $info) = $img->load($src);
		
		// Determine angle
		$angle = strtolower($angle);
		if( $angle == 'cw' || $angle == 'clockwise' ) $angle = 270;
		if( $angle == 'ccw' || $angle == 'counterclockwise' ) $angle = 90;
		
		$rgb = $img->hex2rgb($bg_color);
		$bg_color = imagecolorallocate($original, $rgb['r'], $rgb['g'], $rgb['b']);
		
		$new = imagerotate($original, $angle, $bg_color);
		
		return $img->save($new, $dest, $info['mime'], $quality);
		
	}
	
	
	// Convert an image from color to grayscale ("desaturate")
	static function grayscale($src, $dest, $quality = null) {
		
		$img = new Simple_Image;
		list($original, $info) = $img->load($src);
		
		imagefilter($original, IMG_FILTER_GRAYSCALE);
		
		return $img->save($original, $dest, $info['mime'], $quality);
		
	}
	
	
	// Invert image colors
	static function invert($src, $dest, $quality = null) {
		
		$img = new Simple_Image;
		list($original, $info) = $img->load($src);
		
		imagefilter($original, IMG_FILTER_NEGATE);
		
		return $img->save($original, $dest, $info['mime'], $quality);
		
	}
	
	
	// Adjust image brightness
	static function brightness($src, $dest, $level, $quality = null) {
		
		$img = new Simple_Image;
		list($original, $info) = $img->load($src);
		
		imagefilter($original, IMG_FILTER_BRIGHTNESS, $level);
		
		return $img->save($original, $dest, $info['mime'], $quality);
		
	}	
	
	
	// Adjust image contrast
	static function contrast($src, $dest, $level, $quality = null) {
		
		$img = new Simple_Image;
		list($original, $info) = $img->load($src);
		
		imagefilter($original, IMG_FILTER_CONTRAST, $level);
		
		return $img->save($original, $dest, $info['mime'], $quality);
		
	}
	
	
	// Colorize an image (requires PHP 5.2.5+)
	static function colorize($src, $dest, $red, $green, $blue, $alpha, $quality = null) {
		
		$img = new Simple_Image;
		list($original, $info) = $img->load($src);
		
		imagefilter($original, IMG_FILTER_COLORIZE, $red, $green, $blue, $alpha);
		
		return $img->save($original, $dest, $info['mime'], $quality);
		
	}
	
	
	// Highlight image edges
	static function edgedetect($src, $dest, $quality = null) {
		
		$img = new Simple_Image;
		list($original, $info) = $img->load($src);
		
		imagefilter($original, IMG_FILTER_EDGEDETECT);
		
		return $img->save($original, $dest, $info['mime'], $quality);
		
	}
	
	
	// Emboss an image
	static function emboss($src, $dest, $quality = null) {
		
		$img = new Simple_Image;
		list($original, $info) = $img->load($src);
		
		imagefilter($original, IMG_FILTER_EMBOSS);
		
		return $img->save($original, $dest, $info['mime'], $quality);
		
	}
	
	
	// Blur an image
	static function blur($src, $dest, $level = 1, $quality = null) {
		
		$img = new Simple_Image;
		list($original, $info) = $img->load($src);
		
		for( $i = 0; $i < $level; $i++ ) imagefilter($original, IMG_FILTER_GAUSSIAN_BLUR);
		
		return $img->save($original, $dest, $info['mime'], $quality);
		
	}
	
	
	// Create a sketch effect
	static function sketch($src, $dest, $level = 1, $quality = null) {
		
		$img = new Simple_Image;
		list($original, $info) = $img->load($src);
		
		for( $i = 0; $i < $level; $i++ ) imagefilter($original, IMG_FILTER_MEAN_REMOVAL);
		
		return $img->save($original, $dest, $info['mime'], $quality);
		
	}
	
	
	// Make image smoother
	static function smooth($src, $dest, $level, $quality = null) {
		
		$img = new Simple_Image;
		list($original, $info) = $img->load($src);
		
		imagefilter($original, IMG_FILTER_SMOOTH, $level);
		
		return $img->save($original, $dest, $info['mime'], $quality);
		
	}
	
	
	// Make image pixelized (requires PHP 5.3+)
	static function pixelate($src, $dest, $block_size, $advanced_pix = false, $quality = null) {
		
		$img = new Simple_Image;
		list($original, $info) = $img->load($src);
		
		imagefilter($original, 11, $block_size, $advanced_pix);
		
		return $img->save($original, $dest, $info['mime'], $quality);
		
	}
	
	
	// Produce a sepia-like effect
	static function sepia($src, $dest, $quality = null) {
		
		$img = new Simple_Image;
		list($original, $info) = $img->load($src);
		
		imagefilter($original, IMG_FILTER_GRAYSCALE);
		imagefilter($original, IMG_FILTER_COLORIZE, 90, 60, 30);
		
		return $img->save($original, $dest, $info['mime'], $quality);
		
	}
	
	
	// Resize an image to the specified dimensions
	static function resize($src, $dest, $new_width, $new_height, $resample = true, $quality = null) {
		
		$img = new Simple_Image;
		list($original, $info) = $img->load($src);
		
		$new = imagecreatetruecolor($new_width, $new_height);
		
		// Preserve alphatransparency in PNGs
		imagealphablending($new, false);
		imagesavealpha($new, true);
		
		if( $resample ) {
			imagecopyresampled($new, $original, 0, 0, 0, 0, $new_width, $new_height, $info[0], $info[1]);
		} else {
			imagecopyresized($new, $original, 0, 0, 0, 0, $new_width, $new_height, $info[0], $info[1]);
		}
		
		return $img->save($new, $dest, $info['mime'], $quality);
		
	}
	
	
	// Proportionally scale an image to fit the specified width
	static function resize_to_width($src, $dest, $new_width, $resample = true, $quality = null) {
		
		$img = new Simple_Image;
		list($original, $info) = $img->load($src);
		
		// Determine aspect ratio
		$aspect_ratio = $info[1] / $info[0];
		
		// Adjust height proportionally to new width
		$new_height = $new_width * $aspect_ratio;
		
		$new = imagecreatetruecolor($new_width, $new_height);
		
		// Preserve alphatransparency in PNGs
		imagealphablending($new, false);
		imagesavealpha($new, true);
		
		if( $resample ) {
			imagecopyresampled($new, $original, 0, 0, 0, 0, $new_width, $new_height, $info[0], $info[1]);
		} else {
			imagecopyresized($new, $original, 0, 0, 0, 0, $new_width, $new_height, $info[0], $info[1]);
		}
		
		return $img->save($new, $dest, $info['mime'], $quality);
		
	}
	
	
	// Proportionally scale an image to fit the specified height
	static function resize_to_height($src, $dest, $new_height, $resample = true, $quality = null) {
		
		$img = new Simple_Image;
		list($original, $info) = $img->load($src);
		
		// Determine aspect ratio
		$aspect_ratio = $info[1] / $info[0];
		
		// Adjust height proportionally to new width
		$new_width = $new_height / $aspect_ratio;
		
		$new = imagecreatetruecolor($new_width, $new_height);
		
		// Preserve alphatransparency in PNGs
		imagealphablending($new, false);
		imagesavealpha($new, true);
		
		if( $resample ) {
			imagecopyresampled($new, $original, 0, 0, 0, 0, $new_width, $new_height, $info[0], $info[1]);
		} else {
			imagecopyresized($new, $original, 0, 0, 0, 0, $new_width, $new_height, $info[0], $info[1]);
		}
		
		return $img->save($new, $dest, $info['mime'], $quality);
		
	}
	
	
	// Proportionally shrink an image to fit within a specified width/height
	static function shrink_to_fit($src, $dest, $max_width, $max_height, $resample = true, $quality = null) {
		
		$img = new Simple_Image;
		list($original, $info) = $img->load($src);
		
		// Determine aspect ratio
		$aspect_ratio = $info[1] / $info[0];
		
		// Make width fit into new dimensions
		if( $info[0] > $max_width ) {
			$new_width = $max_width;
			$new_height = $new_width * $aspect_ratio;
		} else {
			$new_width = $info[0];
			$new_height = $info[1];
		}
	   
		// Make height fit into new dimensions
		if( $new_height > $max_height ) {
			$new_height = $max_height;
			$new_width = $new_height / $aspect_ratio;
		}
		
		$new = imagecreatetruecolor($new_width, $new_height);
		
		// Preserve alphatransparency in PNGs
		imagealphablending($new, false);
		imagesavealpha($new, true);
		
		if( $resample ) {
			imagecopyresampled($new, $original, 0, 0, 0, 0, $new_width, $new_height, $info[0], $info[1]);
		} else {
			imagecopyresized($new, $original, 0, 0, 0, 0, $new_width, $new_height, $info[0], $info[1]);
		}
		
		return $img->save($new, $dest, $info['mime'], $quality);
		
	}	
	
	
	// Crop an image and optionally resize the resulting piece
	static function crop($src, $dest, $x1, $y1, $x2, $y2, $new_width = null, $new_height = null, $resample = true, $quality = null) {
		
		$img = new Simple_Image;
		list($original, $info) = $img->load($src);
		
		// Crop size
		if( $x2 < $x1 ) list($x1, $x2) = array($x2, $x1);
		if( $y2 < $y1 ) list($y1, $y2) = array($y2, $y1);
		$crop_width = $x2 - $x1;
		$crop_height = $y2 - $y1;
		
		if( $new_width == null ) $new_width = $crop_width;
		if( $new_height == null ) $new_height = $crop_height;
		
		$new = imagecreatetruecolor($new_width, $new_height);
		
		// Preserve alphatransparency in PNGs
		imagealphablending($new, false);
		imagesavealpha($new, true);
		
		// Create the new image
		if( $resample ) {
			imagecopyresampled($new, $original, 0, 0, $x1, $y1, $new_width, $new_height, $crop_width, $crop_height);
		} else {
			imagecopyresized($new, $original, 0, 0, $x1, $y1, $new_width, $new_height, $crop_width, $crop_height);
		}
		
		return $img->save($new, $dest, $info['mime'], $quality);
		
	}
	
	
	// Trim the edges of a portrait or landscape image to make it square and optionally resize the resulting image
	static function square_crop($src, $dest, $new_size = null, $quality = null) {
		
		$img = new Simple_Image;
		list($original, $info) = $img->load($src);
		
		// Calculate measurements
		if( $info[0] > $info[1] ) {
			// For landscape images
			$x_offset = ($info[0] - $info[1]) / 2;
			$y_offset = 0;
			$square_size = $info[0] - ($x_offset * 2);
		} else {
			// For portrait and square images
			$x_offset = 0;
			$y_offset = ($info[1] - $info[0]) / 2;
			$square_size = $info[1] - ($y_offset * 2);
		}
		
		if( $new_size == null ) $new_size = $square_size;
		
		// Resize and crop
		$new = imagecreatetruecolor($new_size, $new_size);
		
		// Preserve alphatransparency in PNGs
		imagealphablending($new, false);
		imagesavealpha($new, true);
		
		imagecopyresampled($new, $original, 0, 0, $x_offset, $y_offset, $new_size, $new_size, $square_size, $square_size);
		
		return $img->save($new, $dest, $info['mime'], $quality);
		
	}
	
	
	// Overlay an image on top of another image with opacity; works with 24-big PNG alpha-transparency
	static function watermark($src, $dest, $watermark_src, $position = 'center', $opacity = 50, $margin = 0, $quality = null) {
		
		$img = new Simple_Image;
		list($original, $info) = $img->load($src);
		list($watermark, $watermark_info) = $img->load($watermark_src);
		
		switch( strtolower($position) ) {
			
			case 'top-left':
			case 'left-top':
				$x = 0 + $margin;
				$y = 0 + $margin;
			break;
			
			case 'top-right':
			case 'right-top':
				$x = $info[0] - $watermark_info[0] - $margin;
				$y = 0 + $margin;
			break;
			
			case 'top':
			case 'top-center':
			case 'center-top':
				$x = ($info[0] / 2) - ($watermark_info[0] / 2);
				$y = 0 + $margin;
			break;
			
			case 'bottom-left':
			case 'left-bottom':
				$x = 0 + $margin;
				$y = $info[1] - $watermark_info[1] - $margin;
			break;
			
			case 'bottom-right':
			case 'right-bottom':
				$x = $info[0] - $watermark_info[0] - $margin;
				$y = $info[1] - $watermark_info[1] - $margin;
			break;
			
			case 'bottom':
			case 'bottom-center':
			case 'center-bottom':
				$x = ($info[0] / 2) - ($watermark_info[0] / 2);
				$y = $info[1] - $watermark_info[1] - $margin;
			break;
			
			case 'left':
			case 'center-left':
			case 'left-center':
				$x = 0 + $margin;
				$y = ($info[1] / 2) - ($watermark_info[1] / 2);
			break;
			
			case 'right':
			case 'center-right':
			case 'right-center':
				$x = $info[0] - $watermark_info[0] - $margin;
				$y = ($info[1] / 2) - ($watermark_info[1] / 2);
			break;
			
			case 'center':
			default:
				$x = ($info[0] / 2) - ($watermark_info[0] / 2);
				$y = ($info[1] / 2) - ($watermark_info[1] / 2);
			break;
			
		}
		
		$img->imagecopymerge_alpha($original, $watermark, $x, $y, 0, 0, $watermark_info[0], $watermark_info[1], $opacity);  
		
		return $img->save($original, $dest, $info['mime'], $quality);
		
	}
	
	
	// Adds text on top of an image with optional shadow
	static function text($src, $dest, $text, $font_file, $size = '12', $color = '#000000', $position = 'center', $margin = 0, $shadow_color = null, $shadow_offset_x, $shadow_offset_y, $quality = null) {
		
		// This method could be improved to support the text angle
		$angle = 0;
		
		$img = new Simple_Image;
		list($original, $info) = $img->load($src);
		
		$rgb = $img->hex2rgb($color);
		$color = imagecolorallocate($original, $rgb['r'], $rgb['g'], $rgb['b']);
		
		// Determine text size
		$box = imagettfbbox($size, $angle, $font_file, $text);
		
		// Horizontal
		$text_width = abs($box[6] - $box[2]);
		$text_height = abs($box[7] - $box[3]);
		
		
		switch( strtolower($position) ) {
			
			case 'top-left':
			case 'left-top':
				$x = 0 + $margin;
				$y = 0 + $size + $margin;
			break;
			
			case 'top-right':
			case 'right-top':
				$x = $info[0] - $text_width - $margin;
				$y = 0 + $size + $margin;
			break;
			
			case 'top':
			case 'top-center':
			case 'center-top':
				$x = ($info[0] / 2) - ($text_width / 2);
				$y = 0 + $size + $margin;
			break;
			
			case 'bottom-left':
			case 'left-bottom':
				$x = 0 + $margin;
				$y = $info[1] - $text_height - $margin + $size;
			break;
			
			case 'bottom-right':
			case 'right-bottom':
				$x = $info[0] - $text_width - $margin;
				$y = $info[1] - $text_height - $margin + $size;
			break;
			
			case 'bottom':
			case 'bottom-center':
			case 'center-bottom':
				$x = ($info[0] / 2) - ($text_width / 2);
				$y = $info[1] - $text_height - $margin + $size;
			break;
			
			case 'left':
			case 'center-left':
			case 'left-center':
				$x = 0 + $margin;
				$y = ($info[1] / 2) - (($text_height / 2) - $size);
			break;
			
			case 'right';
			case 'center-right':
			case 'right-center':
				$x = $info[0] - $text_width - $margin;
				$y = ($info[1] / 2) - (($text_height / 2) - $size);
			break;
			
			case 'center':
			default:
				$x = ($info[0] / 2) - ($text_width / 2);
				$y = ($info[1] / 2) - (($text_height / 2) - $size);
			break;
			
		}		
		
		if( $shadow_color ){
			$rgb = $img->hex2rgb($shadow_color);
			$shadow_color = imagecolorallocate($original, $rgb['r'], $rgb['g'], $rgb['b']);
			imagettftext($original, $size, $angle, $x + $shdow_offset_x, $y + $shadow_offset_y, $shadow_color, $font_file, $text);
		}
		
		imagettftext($original, $size, $angle, $x, $y, $color, $font_file, $text);
		
		return $img->save($original, $dest, $info['mime'], $quality);
		
	}
	
	
}

// Require GD library
if( !extension_loaded('gd') ) throw new Exception('Required extension GD is not loaded.');

?>