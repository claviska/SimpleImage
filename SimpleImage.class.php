<?php
/*
 * @package		SimpleImage class
 * @version		2.1
 * @author		Cory LaViska for A Beautiful Site, LLC. (http://www.abeautifulsite.net/)
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com> - merging of forks, namespace support, PhpDoc editing
 * @license		This software is dual-licensed under the GNU General Public License and the MIT License
 * @copyright	A Beautiful Site, LLC.
 */
namespace	SimpleImage;
use			\Exception;
/**
 * Class SimpleImage
 * This class makes image manipulation in PHP as simple as possible.
 * @package SimpleImage
 */
class SimpleImage {

	private $image, $filename, $original_info, $width, $height;

	/**
	 * Create instance and load an image
	 *
	 * @param string		$filename	Path to image file
	 *
	 * @return SimpleImage
	 * @throws \Exception
	 */
	function __construct ($filename = null) {
		if ($filename) {
			$this->load($filename);
		}
		return $this;
	}
	/**
	 * Destroy image resource
	 */
	function __destruct () {
		if ($this->image) {
			imagedestroy($this->image);
		}
	}
	/**
	 * Load an image
	 *
	 * @param string		$filename	Path to image file
	 *
	 * @return SimpleImage
	 * @throws \Exception
	 */
	function load ($filename) {
		// Require GD library
		if (!extension_loaded('gd')) {
			throw new Exception('Required extension GD is not loaded.');
		}
		$this->filename	= $filename;
		$info			= getimagesize($this->filename);
		switch ($info['mime']) {
			case 'image/gif':
				$this->image = imagecreatefromgif($this->filename);
				break;
			case 'image/jpeg':
				$this->image = imagecreatefromjpeg($this->filename);
				break;
			case 'image/png':
				$this->image = imagecreatefrompng($this->filename);
				break;
			default:
				throw new Exception('Invalid image: '.$this->filename);
				break;
		}
		$this->original_info = array(
			'width'       => $info[0],
			'height'      => $info[1],
			'orientation' => $this->get_orientation(),
			'exif'        => function_exists('exif_read_data') && $info['mime'] === 'image/jpeg' ? $this->exif = @exif_read_data($this->filename) : null,
			'format'      => preg_replace('/^image\//', '', $info['mime']),
			'mime'        => $info['mime']
		);
		$this->width	= $info[0];
		$this->height	= $info[1];
		imagesavealpha($this->image, true);
		imagealphablending($this->image, true);
		return $this;
	}
	/**
	 * Create an image from scratch
	 *
	 * @param int			$width	Image width (required)
	 * @param int|null		$height	If omitted - assumed equal to $width
	 * @param null|string	$color	Hex color string
	 *
	 * @return SimpleImage
	 */
	function create ($width, $height = null, $color = null) {
		$height					= $height ?: $width;
		$this->width			= $width;
		$this->height			= $height;
		$this->image			= imagecreatetruecolor($width, $height);
		$this->original_info	= array(
			'width'       => $width,
			'height'      => $height,
			'orientation' => $this->get_orientation(),
			'exif'        => null,
			'format'      => 'png',
			'mime'        => 'image/png'
		);
		if ($color) {
			$this->fill($color);
		}
		return $this;
	}
	/**
	 * Fill image with color
	 *
	 * @param string		$color	Hex color string
	 *
	 * @return SimpleImage
	 */
	function fill ($color = '#000000') {
		$rgb		= $this->hex2rgb($color);
		$fill_color	= imagecolorallocate($this->image, $rgb['r'], $rgb['g'], $rgb['b']);
		imagefilledrectangle($this->image, 0, 0, $this->width, $this->height, $fill_color);
		return $this;
	}
	/**
	 * Save an image
	 *
	 * The resulting format will be determined by the file extension.
	 *
	 * @param null|string		$filename	If omitted - original file will be overwritten
	 * @param null|int			$quality	Output image quality 0-9 for png, 0-100 fo jpg
	 *
	 * @return SimpleImage
	 * @throws \Exception
	 */
	function save ($filename = null, $quality = null) {
		$filename	= $filename ?: $this->filename;
		// Determine format via file extension (fall back to original format)
		$format		= $this->file_ext($filename) ?: $this->original_info['format'];
		// Determine output format
		switch ($format) {
			case 'gif':
				$result		= imagegif($this->image, $filename);
				break;
			case 'jpg':
			case 'jpeg':
				$quality	= $quality ?: 85;
				$quality	= $this->keep_within($quality, 0, 100);
				$result		= imagejpeg($this->image, $filename, $quality);
				break;
			case 'png':
				$quality	= $quality ?: 9;
				$quality	= $this->keep_within($quality, 0, 9);
				$result		= imagepng($this->image, $filename, $quality);
				break;
			default:
				throw new Exception('Unsupported format');
		}
		if (!$result) {
			throw new Exception('Unable to save image: '.$filename);
		}
		return $this;
	}
	/**
	 * Get info about the original image
	 *
	 * @return array <pre> array(
	 * 	width		=> 320,
	 * 	height		=> 200,
	 * 	orientation	=> ['portrait', 'landscape', 'square'],
	 * 	exif		=> array(...),
	 * 	mime		=> ['image/jpeg', 'image/gif', 'image/png'],
	 * 	format		=> ['jpeg', 'gif', 'png']
	 * )</pre>
	 */
	function get_original_info () {
		return $this->original_info;
	}
	/**
	 * Get the current width
	 *
	 * @return int
	 */
	function get_width () {
		return imagesx($this->image);
	}
	/**
	 * Get the current height
	 *
	 * @return int
	 */
	function get_height () {
		return imagesy($this->image);
	}
	/**
	 * Get the current orientation
	 *
	 * @return string	portrait|landscape|square
	 */
	function get_orientation () {
		if (imagesx($this->image) > imagesy($this->image)) {
			return 'landscape';
		}
		if (imagesx($this->image) < imagesy($this->image)) {
			return 'portrait';
		}
		return 'square';
	}
	/**
	 * Flip an image horizontally or vertically
	 *
	 * @param string		$direction	x|y
	 *
	 * @return SimpleImage
	 */
	function flip ($direction) {
		$new	= imagecreatetruecolor($this->width, $this->height);
		imagealphablending($new, false);
		imagesavealpha($new, true);
		switch (strtolower($direction)) {
			case 'y':
				for ($y = 0; $y < $this->height; $y++) {
					imagecopy($new, $this->image, 0, $y, 0, $this->height - $y - 1, $this->width, 1);
				}
				break;
			default:
				for ($x = 0; $x < $this->width; $x++) {
					imagecopy($new, $this->image, $x, 0, $this->width - $x - 1, 0, 1, $this->height);
				}
				break;
		}
		$this->image = $new;
		return $this;
	}
	/**
	 * Rotate an image
	 *
	 * @param int			$angle		0-360
	 * @param string		$bg_color	Hex color string for background
	 *
	 * @return SimpleImage
	 */
	function rotate ($angle, $bg_color = '#000000') {
		$rgb			= $this->hex2rgb($bg_color);
		$bg_color		= imagecolorallocate($this->image, $rgb['r'], $rgb['g'], $rgb['b']);
		$new			= imagerotate($this->image, -($this->keep_within($angle, -360, 360)), $bg_color);
		imagesavealpha($new, true);
		imagealphablending($new, true);
		$this->width	= imagesx($new);
		$this->height	= imagesy($new);
		$this->image	= $new;
		return $this;
	}
	/**
	 * Rotates and/or flips an image automatically so the orientation will be correct (based on exif 'Orientation')
	 *
	 * @return SimpleImage
	 */
	function auto_orient () {
		// Adjust orientation
		switch ($this->original_info['exif']['Orientation']) {
			case 1:	// Do nothing
				break;
			case 2:	// Flip horizontal
				$this->flip('x');
				break;
			case 3:	// Rotate 180 counterclockwise
				$this->rotate(-180);
				break;
			case 4:	// vertical flip
				$this->flip('y');
				break;
			case 5:	// Rotate 90 clockwise and flip vertically
				$this->flip('y');
				$this->rotate(90);
				break;
			case 6:	// Rotate 90 clockwise
				$this->rotate(90);
				break;
			case 7:	// Rotate 90 clockwise and flip horizontally
				$this->flip('x');
				$this->rotate(90);
				break;
			case 8:	// Rotate 90 counterclockwise
				$this->rotate(-90);
				break;
		}
		return $this;
	}
	/**
	 * Resize an image to the specified dimensions
	 *
	 * @param int	$width
	 * @param int	$height
	 *
	 * @return SimpleImage
	 */
	function resize ($width, $height) {
		$new			= imagecreatetruecolor($width, $height);
		imagealphablending($new, false);
		imagesavealpha($new, true);
		imagecopyresampled($new, $this->image, 0, 0, 0, 0, $width, $height, $this->width, $this->height);
		$this->width	= $width;
		$this->height	= $height;
		$this->image	= $new;
		return $this;
	}
	/**
	 * Fit to width (proportionally resize to specified width)
	 *
	 * @param int			$width
	 *
	 * @return SimpleImage
	 */
	function fit_to_width ($width) {
		$aspect_ratio	= $this->height / $this->width;
		$height			= $width * $aspect_ratio;
		return $this->resize($width, $height);
	}
	/**
	 * Fit to height (proportionally resize to specified height)
	 *
	 * @param int			$height
	 *
	 * @return SimpleImage
	 */
	function fit_to_height ($height) {
		$aspect_ratio	= $this->height / $this->width;
		$width			= $height / $aspect_ratio;
		return $this->resize($width, $height);
	}
	/**
	 * Best fit (proportionally resize to fit in specified width/height)
	 *
	 * Shrink the image proportionally to fit inside a $width x $height box
	 *
	 * @param int			$max_width
	 * @param int			$max_height
	 *
	 * @return	SimpleImage
	 */
	function best_fit ($max_width, $max_height) {
		// If it already fits, there's nothing to do
		if ($this->width <= $max_width && $this->height <= $max_height) {
			return $this;
		}
		// Determine aspect ratio
		$aspect_ratio	= $this->height / $this->width;
		// Make width fit into new dimensions
		if ($this->width > $max_width) {
			$width	= $max_width;
			$height	= $width * $aspect_ratio;
		} else {
			$width	= $this->width;
			$height	= $this->height;
		}
		// Make height fit into new dimensions
		if ($height > $max_height) {
			$height	= $max_height;
			$width	= $height / $aspect_ratio;
		}
		return $this->resize($width, $height);
	}
	/**
	 * Crop an image
	 *
	 * @param int			$x1	Left
	 * @param int			$y1	Top
	 * @param int			$x2	Right
	 * @param int			$y2	Bottom
	 *
	 * @return SimpleImage
	 */
	function crop ($x1, $y1, $x2, $y2) {
		// Determine crop size
		if ($x2 < $x1) {
			list($x1, $x2) = array($x2, $x1);
		}
		if ($y2 < $y1) {
			list($y1, $y2) = array($y2, $y1);
		}
		$crop_width		= $x2 - $x1;
		$crop_height	= $y2 - $y1;
		$new			= imagecreatetruecolor($crop_width, $crop_height);
		imagealphablending($new, false);
		imagesavealpha($new, true);
		imagecopyresampled($new, $this->image, 0, 0, $x1, $y1, $crop_width, $crop_height, $crop_width, $crop_height);
		$this->width	= $crop_width;
		$this->height	= $crop_height;
		$this->image	= $new;
		return $this;
	}
	/**
	 * Crop an image from center
	 *
	 * @param int			$width
	 * @param int			$height
	 *
	 * @return SimpleImage
	 */
	function crop_center ($width, $height) {
		$this->img	= $this->smart_crop($width);
		$left		= ($this->width / 2) - ($width / 2);
		$top		= ($this->height / 2) - ($height / 2);
		return $this->crop($left, $top, $width + $left, $height + $top);
	}
	/**
	 * Smart crop (great for thumbnails)
	 *
	 * Trims and resize to the specified $width and $height
	 *
	 * @param int			$width
	 * @param int|null		$height	If omitted - assumed equal to $width
	 *
	 * @return SimpleImage
	 */
	function smart_crop ($width, $height = null) {
		$height					= $height ?: $width;
		$aspect_ratio			= $this->width / $this->height;
		$aspect_ratio_required	= $width / $height;
		if ($aspect_ratio < $aspect_ratio_required) {
			// Cut height to achieve desired ratio
			$newHeight	= $this->height * $aspect_ratio / $aspect_ratio_required;
			$x_offset	= 0;
			$y_offset	= ($this->height - $newHeight) / 2;
			// Trim to correct ratio
			$this->crop($x_offset, $y_offset, $this->width, $y_offset + $newHeight);
		} elseif ($aspect_ratio > $aspect_ratio_required) {
			// Cut width to achieve desired ratio
			$newWidth	= $this->width / $aspect_ratio * $aspect_ratio_required;
			$y_offset	= 0;
			$x_offset	= ($this->width - $newWidth) / 2;
			// Trim to correct ratio
			$this->crop($x_offset, $y_offset, $x_offset + $newWidth, $this->height);
		}
		// Resize
		$this->resize($width, $height);
		return $this;
	}
	/**
	 * Desaturate (grayscale)
	 *
	 * @return SimpleImage
	 */
	function desaturate () {
		imagefilter($this->image, IMG_FILTER_GRAYSCALE);
		return $this;
	}
	/**
	 * Invert
	 *
	 * @return SimpleImage
	 */
	function invert () {
		imagefilter($this->image, IMG_FILTER_NEGATE);
		return $this;
	}
	/**
	 * Brightness
	 *
	 * @param int			$level	Darkest = -255, lightest = 255
	 *
	 * @return SimpleImage
	 */
	function brightness ($level) {
		imagefilter($this->image, IMG_FILTER_BRIGHTNESS, $this->keep_within($level, -255, 255));
		return $this;
	}
	/**
	 * Contrast
	 *
	 * @param int			$level	Min = -100, max = 100
	 *
	 * @return SimpleImage
	 *
	 */
	function contrast ($level) {
		imagefilter($this->image, IMG_FILTER_CONTRAST, $this->keep_within($level, -100, 100));
		return $this;
	}
	/**
	 * Colorize (requires PHP 5.2.5+)
	 *
	 * @param string		$color		Hex color string
	 * @param float|int		$opacity	0-1
	 *
	 * @return SimpleImage
	 */
	function colorize ($color, $opacity) {
		$rgb   = $this->hex2rgb($color);
		$alpha = $this->keep_within(127 - (127 * $opacity), 0, 127);
		imagefilter($this->image, IMG_FILTER_COLORIZE, $this->keep_within($rgb['r'], 0, 255), $this->keep_within($rgb['g'], 0, 255), $this->keep_within($rgb['b'], 0, 255), $alpha);
		return $this;
	}
	/**
	 * Edge Detect
	 *
	 * @return SimpleImage
	 */
	function edges () {
		imagefilter($this->image, IMG_FILTER_EDGEDETECT);
		return $this;
	}
	/**
	 * Emboss
	 *
	 * @return SimpleImage
	 */
	function emboss () {
		imagefilter($this->image, IMG_FILTER_EMBOSS);
		return $this;
	}
	/**
	 * Mean Remove
	 *
	 * @return SimpleImage
	 */
	function mean_remove () {
		imagefilter($this->image, IMG_FILTER_MEAN_REMOVAL);
		return $this;
	}
	/**
	 * Blur
	 *
	 * @param string		$type	selective|gaussian
	 * @param int			$passes	Number of times to apply the filter
	 *
	 * @return SimpleImage
	 */
	function blur ($type = 'selective', $passes = 1) {
		switch (strtolower($type)) {
			case 'gaussian':
				$type = IMG_FILTER_GAUSSIAN_BLUR;
				break;
			default:
				$type = IMG_FILTER_SELECTIVE_BLUR;
				break;
		}
		for ($i = 0; $i < $passes; $i++) {
			imagefilter($this->image, $type);
		}
		return $this;
	}
	/**
	 * Sketch
	 *
	 * @return SimpleImage
	 */
	function sketch () {
		imagefilter($this->image, IMG_FILTER_MEAN_REMOVAL);
		return $this;
	}
	/**
	 * Smooth
	 *
	 * @param int			$level	Min = -10, max = 10
	 *
	 * @return SimpleImage
	 */
	function smooth ($level) {
		imagefilter($this->image, IMG_FILTER_SMOOTH, $this->keep_within($level, -10, 10));
		return $this;
	}
	/**
	 * Pixelate (requires PHP 5.3+)
	 *
	 * @param int			$block_size	Size in pixels of each resulting block
	 *
	 * @return SimpleImage
	 */
	function pixelate ($block_size = 10) {
		imagefilter($this->image, IMG_FILTER_PIXELATE, $block_size, true);
		return $this;
	}
	/**
	 * Sepia
	 *
	 * @return SimpleImage
	 */
	function sepia () {
		imagefilter($this->image, IMG_FILTER_GRAYSCALE);
		imagefilter($this->image, IMG_FILTER_COLORIZE, 100, 50, 0);
		return $this;
	}
	/**
	 * Overlay
	 *
	 * Overlay an image on top of another, works with 24-bit PNG alpha-transparency
	 *
	 * @param string		$overlay_file
	 * @param string		$position		center|top|left|bottom|right|top left|top right|bottom left|bottom right
	 * @param float|int		$opacity		Overlay opacity 0-1
	 * @param int			$x_offset		Horizontal offset in pixels
	 * @param int			$y_offset		Vertical offset in pixels
	 *
	 * @return SimpleImage
	 */
	function overlay ($overlay_file, $position = 'center', $opacity = 1, $x_offset = 0, $y_offset = 0) {
		// Load overlay image
		$overlay	= new SimpleImage($overlay_file);
		// Convert opacity
		$opacity	= $opacity * 100;
		// Determine position
		switch (strtolower($position)) {
			case 'top left':
				$x	= 0 + $x_offset;
				$y	= 0 + $y_offset;
				break;
			case 'top right':
				$x	= $this->width - $overlay->width + $x_offset;
				$y	= 0 + $y_offset;
				break;
			case 'top':
				$x	= ($this->width / 2) - ($overlay->width / 2) + $x_offset;
				$y	= 0 + $y_offset;
				break;
			case 'bottom left':
				$x	= 0 + $x_offset;
				$y	= $this->height - $overlay->height + $y_offset;
				break;
			case 'bottom right':
				$x	= $this->width - $overlay->width + $x_offset;
				$y	= $this->height - $overlay->height + $y_offset;
				break;
			case 'bottom':
				$x	= ($this->width / 2) - ($overlay->width / 2) + $x_offset;
				$y	= $this->height - $overlay->height + $y_offset;
				break;
			case 'left':
				$x	= 0 + $x_offset;
				$y	= ($this->height / 2) - ($overlay->height / 2) + $y_offset;
				break;
			case 'right':
				$x	= $this->width - $overlay->width + $x_offset;
				$y	= ($this->height / 2) - ($overlay->height / 2) + $y_offset;
				break;
			case 'center':
			default:
				$x	= ($this->width / 2) - ($overlay->width / 2) + $x_offset;
				$y	= ($this->height / 2) - ($overlay->height / 2) + $y_offset;
				break;
		}
		$this->imagecopymerge_alpha($this->image, $overlay->image, $x, $y, 0, 0, $overlay->width, $overlay->height, $opacity);
		return $this;
	}
	/**
	 * Add text to an image
	 *
	 * @param string		$text
	 * @param string		$font_file
	 * @param float|int		$font_size
	 * @param string		$color
	 * @param string		$position
	 * @param int			$x_offset
	 * @param int			$y_offset
	 *
	 * @return SimpleImage
	 * @throws \Exception
	 */
	function text ($text, $font_file, $font_size = 12, $color = '#000000', $position = 'center', $x_offset = 0, $y_offset = 0) {
		// todo - this method could be improved to support the text angle
		$angle		= 0;
		$rgb		= $this->hex2rgb($color);
		$color		= imagecolorallocate($this->image, $rgb['r'], $rgb['g'], $rgb['b']);
		// Determine textbox size
		$box		= imagettfbbox($font_size, $angle, $font_file, $text);
		if (!$box) {
			throw new Exception('Unable to load font: '.$font_file);
		}
		$box_width	= abs($box[6] - $box[2]);
		$box_height	= abs($box[7] - $box[1]);
		// Determine position
		switch (strtolower($position)) {
			case 'top left':
				$x	= 0 + $x_offset;
				$y	= 0 + $y_offset + $box_height;
				break;
			case 'top right':
				$x	= $this->width - $box_width + $x_offset;
				$y	= 0 + $y_offset + $box_height;
				break;
			case 'top':
				$x	= ($this->width / 2) - ($box_width / 2) + $x_offset;
				$y	= 0 + $y_offset + $box_height;
				break;
			case 'bottom left':
				$x	= 0 + $x_offset;
				$y	= $this->height - $box_height + $y_offset + $box_height;
				break;
			case 'bottom right':
				$x	= $this->width - $box_width + $x_offset;
				$y	= $this->height - $box_height + $y_offset + $box_height;
				break;
			case 'bottom':
				$x	= ($this->width / 2) - ($box_width / 2) + $x_offset;
				$y	= $this->height - $box_height + $y_offset + $box_height;
				break;
			case 'left':
				$x	= 0 + $x_offset;
				$y	= ($this->height / 2) - (($box_height / 2) - $box_height) + $y_offset;
				break;
			case 'right';
				$x	= $this->width - $box_width + $x_offset;
				$y	= ($this->height / 2) - (($box_height / 2) - $box_height) + $y_offset;
				break;
			case 'center':
			default:
				$x	= ($this->width / 2) - ($box_width / 2) + $x_offset;
				$y	= ($this->height / 2) - (($box_height / 2) - $box_height) + $y_offset;
				break;
		}
		imagettftext($this->image, $font_size, $angle, $x, $y, $color, $font_file, $text);
		return $this;
	}
	/**
	 * Outputs image without saving
	 *
	 * @param null|string	$format		If omitted or null - format of original file will be used, may be gif|jpg|png
	 * @param int|null		$quality	Output image quality 0-9 for png, 0-100 fo jpg
	 *
	 * @throws \Exception
	 */
	function output ($format = null, $quality = null) {
		switch (strtolower($format)) {
			case 'gif':
				$mimetype	= 'image/gif';
				break;
			case 'jpeg':
			case 'jpg':
				$mimetype	= 'image/jpeg';
				break;
			case 'png':
				$mimetype	= 'image/png';
				break;
			default:
				$info		= getimagesize($this->filename);
				$mimetype	= $info['mime'];
				unset($info);
				break;
		}
		// Output the image
		header('Content-Type: '.$mimetype);
		switch ($mimetype) {
			case 'image/gif':
				imagegif($this->image);
				break;
			case 'image/jpeg':
				$quality	= $this->keep_within($quality ?: 85, 0, 100);
				imagejpeg($this->image, null, $quality);
				break;
			case 'image/png':
				$quality	= $this->keep_within($quality ?: 9, 0, 9);
				imagepng($this->image, null, $quality);
				break;
			default:
				throw new Exception('Unsupported image format: '.$this->filename);
				break;
		}
		// Since no more output can be sent, call the destructor to free up memory
		$this->__destruct();
	}
	/**
	 * Outputs image as data base64 to use as img src
	 *
	 * @param null|string	$format		If omitted or null - format of original file will be used, may be gif|jpg|png
	 * @param int|null		$quality	Output image quality 0-9 for png, 0-100 fo jpg
	 *
	 * @return string
	 * @throws \Exception
	 */
	function outputBase64 ($format = null, $quality = null) {
		switch (strtolower($format)) {
			case 'gif':
				$mimetype	= 'image/gif';
				break;
			case 'jpeg':
			case 'jpg':
				$mimetype	= 'image/jpeg';
				break;
			case 'png':
				$mimetype	= 'image/png';
				break;
			default:
				$info		= getimagesize($this->filename);
				$mimetype	= $info['mime'];
				unset($info);
				break;
		}
		ob_start();
		// Output the image
		switch ($mimetype) {
			case 'image/gif':
				imagegif($this->image);
				break;
			case 'image/jpeg':
				$quality	= $this->keep_within($quality ?: 85, 0, 100);
				imagejpeg($this->image, null, $quality);
				break;
			case 'image/png':
				$quality	= $this->keep_within($quality ?: 9, 0, 9);
				imagepng($this->image, null, $quality);
				break;
			default:
				throw new Exception('Unsupported image format: '.$this->filename);
				break;
		}
		$image_data	= ob_get_contents();
		ob_end_clean();
		// Returns formatted string for img src
		return 'data:'.$mimetype.';base64,'.base64_encode($image_data);
	}
	/**
	 * Same as PHP's imagecopymerge() function, except preserves alpha-transparency in 24-bit PNGs
	 *
	 * @param $dst_im
	 * @param $src_im
	 * @param $dst_x
	 * @param $dst_y
	 * @param $src_x
	 * @param $src_y
	 * @param $src_w
	 * @param $src_h
	 * @param $pct
	 *
	 * @link http://www.php.net/manual/en/function.imagecopymerge.php#88456
	 */
	private function imagecopymerge_alpha ($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct) {
		$pct		/= 100;
		// Get image width and height
		$w			= imagesx($src_im);
		$h			= imagesy($src_im);
		// Turn alpha blending off
		imagealphablending($src_im, false);
		// Find the most opaque pixel in the image (the one with the smallest alpha value)
		$minalpha	= 127;
		for ($x = 0; $x < $w; $x++) {
			for ($y = 0; $y < $h; $y++) {
				$alpha	= (imagecolorat($src_im, $x, $y) >> 24) & 0xFF;
				if ($alpha < $minalpha) {
					$minalpha	= $alpha;
				}
			}
		}
		// Loop through image pixels and modify alpha for each
		for ($x = 0; $x < $w; $x++) {
			for ($y = 0; $y < $h; $y++) {
				// Get current alpha value (represents the TANSPARENCY!)
				$colorxy		= imagecolorat($src_im, $x, $y);
				$alpha			= ($colorxy >> 24) & 0xFF;
				// Calculate new alpha
				if ($minalpha !== 127) {
					$alpha	= 127 + 127 * $pct * ($alpha - 127) / (127 - $minalpha);
				} else {
					$alpha	+= 127 * $pct;
				}
				// Get the color index with new alpha
				$alphacolorxy	= imagecolorallocatealpha($src_im, ($colorxy >> 16) & 0xFF, ($colorxy >> 8) & 0xFF, $colorxy & 0xFF, $alpha);
				// Set pixel with the new color + opacity
				if (!imagesetpixel($src_im, $x, $y, $alphacolorxy)) {
					return;
				}
			}
		}
		imagesavealpha($dst_im, true);
		imagealphablending($dst_im, true);
		imagesavealpha($src_im, true);
		imagealphablending($src_im, true);
		imagecopy($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);
	}
	/**
	 * Ensures $value is always within $min and $max range.
	 *
	 * If lower, $min is returned. If higher, $max is returned.
	 *
	 * @param int|float		$value
	 * @param int|float		$min
	 * @param int|float		$max
	 *
	 * @return int|float
	 */
	private function keep_within ($value, $min, $max) {
		if ($value < $min) {
			return $min;
		}
		if ($value > $max) {
			return $max;
		}
		return $value;
	}
	/**
	 * Returns the file extension of the specified file
	 *
	 * @param string	$filename
	 *
	 * @return string
	 */
	private function file_ext ($filename) {
		if (!preg_match('/\./', $filename)) {
			return '';
		}
		return preg_replace('/^.*\./', '', $filename);
	}
	/**
	 * Converts a hex color value to its RGB equivalent
	 *
	 * @param string		$hex_color
	 *
	 * @return array|bool
	 */
	private function hex2rgb ($hex_color) {
		if ($hex_color[0] == '#') {
			$hex_color = substr($hex_color, 1);
		}
		if (strlen($hex_color) == 6) {
			list($r, $g, $b) = array(
				$hex_color[0].$hex_color[1],
				$hex_color[2].$hex_color[3],
				$hex_color[4].$hex_color[5]
			);
		} elseif (strlen($hex_color) == 3) {
			list($r, $g, $b) = array(
				$hex_color[0].$hex_color[0],
				$hex_color[1].$hex_color[1],
				$hex_color[2].$hex_color[2]
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
}