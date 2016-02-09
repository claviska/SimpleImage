<?php
/**
 * JBZoo Image
 *
 * This file is part of the JBZoo CCK package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package   Image
 * @license   MIT
 * @copyright Copyright (C) JBZoo.com,  All rights reserved.
 * @link      https://github.com/JBZoo/Image
 */

namespace JBZoo\Image;

use JBZoo\Utils\Filter as VarFilter;
use JBZoo\Utils\FS;
use JBZoo\Utils\Url;

/**
 * Class Image
 * @package JBZoo\Image
 */
class Image
{
    const LANDSCAPE = 'landscape';
    const PORTRAIT  = 'portrait';
    const SQUARE    = 'square';
    const QUALITY   = 95;

    /**
     * GD Resource
     * @var mixed
     */
    protected $_image = null;

    /**
     * @var int
     */
    protected $_quality = self::QUALITY;

    /**
     * @var string|null
     */
    protected $_filename = null;

    /**
     * @var array
     */
    protected $_exif = array();

    /**
     * @var int
     */
    protected $_width = null;

    /**
     * @var int
     */
    protected $_height = null;

    /**
     * @var string
     */
    protected $_orient = null;

    /**
     * @var string
     */
    protected $_mime = null;

    /**
     * Constructor
     *
     * @param string|null $filename
     *
     * @throws Exception
     */
    public function __construct($filename = null)
    {
        Helper::checkGD();

        if ($filename) {
            $this->open($filename);
        }
    }

    /**
     * Destroy image resource
     */
    public function __destruct()
    {
        $this->cleanup();
    }

    /**
     * @return array
     */
    public function getInfo()
    {
        return array(
            'filename' => $this->_filename,
            'width'    => $this->_width,
            'height'   => $this->_height,
            'mime'     => $this->_mime,
            'quality'  => $this->_quality,
            'exif'     => $this->_exif,
            'orient'   => $this->_orient,
        );
    }

    /**
     * Get the current width
     * @return int
     */
    public function getWidth()
    {
        return $this->_width;
    }

    /**
     * Get the current height
     * @return int
     */
    public function getHeight()
    {
        return $this->_width;
    }

    /**
     * Get the current image resource
     * @return int
     */
    public function getImage()
    {
        return $this->_image;
    }

    /**
     * @param int $newQuality
     * @return $this
     */
    public function setQuality($newQuality)
    {
        $this->_quality = Helper::quality($newQuality);
        return $this;
    }

    /**
     * Save an image
     * The resulting format will be determined by the file extension.
     *
     * @param null|int $quality Output image quality in percents 0-100
     * @return Image
     */
    public function save($quality = null)
    {
        $quality = $quality ?: $this->_quality;

        $this->_save($this->_filename, $quality);

        return $this;
    }

    /**
     * Save an image
     * The resulting format will be determined by the file extension.
     *
     * @param string   $filename If omitted - original file will be overwritten
     * @param null|int $quality  Output image quality in percents 0-100
     * @return Image
     *
     * @throws Exception
     */
    public function saveAs($filename, $quality = null)
    {
        if (strlen($filename) === 0) {
            throw new Exception('Empty filename to save image');
        }

        $dir = FS::dirname($filename);
        if (is_dir($dir)) {
            $this->_save($filename, $quality);
        } else {
            throw new Exception('Target directory "' . $dir . '" not exists');
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isGif()
    {
        return Helper::isGif($this->_mime);
    }

    /**
     * @return bool
     */
    public function isPng()
    {
        return Helper::isPng($this->_mime);
    }

    /**
     * @return bool
     */
    public function isJpeg()
    {
        return Helper::isJpeg($this->_mime);
    }

    /**
     * @param string $filename
     * @param int    $quality
     * @return bool
     */
    protected function _savePng($filename, $quality)
    {
        $result = imagepng($this->_image, $filename, round(9 * $quality / 100));
        return $result;
    }

    /**
     * @param string $filename
     * @param int    $quality
     * @return bool
     */
    protected function _saveJpeg($filename, $quality)
    {
        imageinterlace($this->_image, true);
        $result = imagejpeg($this->_image, $filename, round($quality));
        return $result;
    }

    /**
     * @param string $filename
     * @return bool
     */
    protected function _saveGif($filename)
    {
        $result = imagegif($this->_image, $filename);
        return $result;
    }

    /**
     * Save image to file
     *
     * @param string $filename
     * @param int    $quality
     * @return bool
     *
     * @throws Exception
     */
    protected function _save($filename, $quality)
    {
        $quality = $quality ?: $this->_quality;
        $quality = Helper::quality($quality);

        $format = FS::ext($filename) ?: $this->_mime;
        $format = strtolower($format);

        $filename = FS::clean($filename);

        // Create the image
        $result = $this->_renderImageByFormat($format, $filename, $quality);

        if (!$result) {
            throw new Exception('Unable to save image: ' . $filename); // @codeCoverageIgnore
        }

        $this->open($filename);
        $this->_quality = $quality;

        return $result;
    }

    /**
     * Render image resource as binary
     *
     * @param string $format
     * @param string $filename
     * @param int    $quality
     * @return bool|string
     *
     * @throws Exception
     */
    protected function _renderImageByFormat($format, $filename, $quality)
    {
        if (!$this->_image) {
            throw new Exception('Image resource not defined');
        }

        $result = false;
        if (Helper::isJpeg($format)) {
            if ($this->_saveJpeg($filename, $quality)) {
                $result = 'image/jpeg';
            }

        } elseif (Helper::isPng($format)) {
            if ($this->_savePng($filename, $quality)) {
                $result = 'image/png';
            }

        } elseif (Helper::isGif($format)) {
            if ($this->_saveGif($filename)) {
                $result = 'image/gif';
            }

        } else {
            throw new Exception('Undefined format: ' . $format);
        }

        return $result;
    }

    /**
     * Load an image
     *
     * @param string $filename Path to image file
     * @return Image
     *
     * @throws Exception
     */
    public function open($filename)
    {
        $cleanFilename = FS::clean($filename);
        if (!file_exists($cleanFilename)) {
            throw new Exception('Image file not forund: ' . $filename);
        }

        $this->cleanup();
        $this->_filename = $cleanFilename;
        $this->_loadMeta();

        return $this;
    }

    /**
     * Get meta data of image or base64 string
     *
     * @return Image
     * @throws Exception
     */
    protected function _loadMeta()
    {
        //gather meta data
        $info = getimagesize($this->_filename);

        $this->_mime   = $info['mime'];
        $this->_width  = $info[0];
        $this->_height = $info[1];
        $this->_image  = $this->_imageCreate($info['mime']);
        $this->_exif   = $this->_getExif();
        $this->_orient = $this->_getOrientation();

        imagesavealpha($this->_image, true);
        imagealphablending($this->_image, true);

        return $this;
    }

    /**
     * Clean whole image object
     * @return $this
     */
    public function cleanup()
    {
        $this->_filename = null;

        $this->_mime    = null;
        $this->_width   = null;
        $this->_height  = null;
        $this->_exif    = array();
        $this->_orient  = null;
        $this->_quality = self::QUALITY;

        $this->_destroyImage();

        return $this;
    }

    /**
     * Destroy image resource if not empty
     */
    protected function _destroyImage()
    {
        if (is_resource($this->_image) && get_resource_type($this->_image) === 'gd') {
            imagedestroy($this->_image);
            $this->_image = null;
        }
    }

    /**
     * @return array
     */
    protected function _getExif()
    {
        $result = array();

        if (function_exists('exif_read_data')) {
            if (Helper::isJpeg($this->_mime)) {
                $result = exif_read_data($this->_filename);
            }
        }

        return $result;
    }

    /**
     * Create image resource
     *
     * @param string $format
     * @return resource
     *
     * @throws Exception
     */
    protected function _imageCreate($format)
    {
        if (Helper::isJpeg($format)) {
            $result = imagecreatefromjpeg($this->_filename);

        } elseif (Helper::isPng($format)) {
            $result = imagecreatefrompng($this->_filename);

        } elseif (Helper::isGif($format)) {
            $result = imagecreatefromgif($this->_filename);

        } else {
            throw new Exception('Invalid image: ' . $this->_filename); // @codeCoverageIgnore
        }

        return $result;
    }

    /**
     * Get the current orientation
     * @return string   portrait|landscape|square
     */
    protected function _getOrientation()
    {
        if ($this->_width > $this->_height) {
            return self::LANDSCAPE;
        }

        if ($this->_width < $this->_height) {
            return self::PORTRAIT;
        }

        return self::SQUARE;
    }

    /**
     * @return bool
     */
    public function isPortrait()
    {
        return $this->_orient === self::PORTRAIT;
    }

    /**
     * @return bool
     */
    public function isLandscape()
    {
        return $this->_orient === self::LANDSCAPE;
    }

    /**
     * @return bool
     */
    public function isSquare()
    {
        return $this->_orient === self::SQUARE;
    }

    /**
     * Create an image from scratch
     *
     * @param int         $width  Image width
     * @param int|null    $height If omitted - assumed equal to $width
     * @param null|string $color  Hex color string, array(red, green, blue) or array(red, green, blue, alpha).
     *                            Where red, green, blue - integers 0-255, alpha - integer 0-127
     * @return $this
     */
    public function create($width, $height = null, $color = null)
    {
        $this->cleanup();

        $height = $height ? $height : $width;

        $this->_width  = VarFilter::int($width);
        $this->_height = VarFilter::int($height);
        $this->_image  = imagecreatetruecolor($this->_width, $this->_height);
        $this->_mime   = 'image/png';
        $this->_exif   = array();

        $this->_orient = $this->_getOrientation();

        if (null !== $color) {
            return $this->fill($color);
        }

        return $this;
    }

    /**
     * Resize an image to the specified dimensions
     *
     * @param int $width
     * @param int $height
     * @return $this
     */
    public function resize($width, $height)
    {
        $width  = VarFilter::int($width);
        $height = VarFilter::int($height);

        // Generate new GD image
        $newImage = imagecreatetruecolor($width, $height);

        if ($this->isGif()) {
            // Preserve transparency in GIFs
            $transIndex = imagecolortransparent($this->_image);
            $palletsize = imagecolorstotal($this->_image);

            if ($transIndex >= 0 && $transIndex < $palletsize) {
                $trColor = imagecolorsforindex($this->_image, $transIndex);

                $red   = VarFilter::int($trColor['red']);
                $green = VarFilter::int($trColor['green']);
                $blue  = VarFilter::int($trColor['blue']);

                $transIndex = imagecolorallocate($newImage, $red, $green, $blue);

                imagefill($newImage, 0, 0, $transIndex);
                imagecolortransparent($newImage, $transIndex);
            }

        } else {
            // Preserve transparency in PNGs (benign for JPEGs)
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
        }

        // Resize
        imagecopyresampled($newImage, $this->_image, 0, 0, 0, 0, $width, $height, $this->_width, $this->_height);

        // Update meta data
        $this->_replaceImage($newImage);
        $this->_width  = $width;
        $this->_height = $height;

        return $this;
    }

    /**
     * Best fit (proportionally resize to fit in specified width/height)
     * Shrink the image proportionally to fit inside a $width x $height box
     *
     * @param int $maxWidth
     * @param int $maxHeight
     * @return $this
     */
    public function bestFit($maxWidth, $maxHeight)
    {
        // If it already fits, there's nothing to do
        if ($this->_width <= $maxWidth && $this->_height <= $maxHeight) {
            return $this;
        }

        // Determine aspect ratio
        $aspectRatio = $this->_height / $this->_width;

        // Make width fit into new dimensions
        if ($this->_width > $maxWidth) {
            $width  = $maxWidth;
            $height = $width * $aspectRatio;
        } else {
            $width  = $this->_width;
            $height = $this->_height;
        }

        // Make height fit into new dimensions
        if ($height > $maxHeight) {
            $height = $maxHeight;
            $width  = $height / $aspectRatio;
        }

        return $this->resize($width, $height);
    }

    /**
     * Fill image with color
     *
     * @param string $color     Hex color string, array(red, green, blue) or array(red, green, blue, alpha).
     *                          Where red, green, blue - integers 0-255, alpha - integer 0-127
     * @return $this
     */
    public function fill($color = '#000000')
    {
        $rgba      = Helper::normalizeColor($color);
        $fillColor = imagecolorallocatealpha($this->_image, $rgba['r'], $rgba['g'], $rgba['b'], $rgba['a']);

        imagealphablending($this->_image, false);
        imagesavealpha($this->_image, true);
        imagefilledrectangle($this->_image, 0, 0, $this->_width, $this->_height, $fillColor);

        return $this;
    }

    /**
     * Thumbnail.
     * This function attempts to get the image to as close to the provided dimensions as possible, and then crops the
     * remaining overflow (from the center) to get the image to be the size specified. Useful for generating thumbnails.
     *
     * @param int      $width
     * @param int|null $height If omitted - assumed equal to $width
     *
     * @return $this
     */
    public function thumbnail($width, $height = null)
    {
        $width  = VarFilter::int($width);
        $height = VarFilter::int($height);

        // Determine height
        $height = $height ?: $width;

        // Determine aspect ratios
        $currentAspectRatio = $this->_height / $this->_width;
        $newAspectRatio     = $height / $width;

        // Fit to height/width
        if ($newAspectRatio > $currentAspectRatio) {
            $this->fitToHeight($height);
        } else {
            $this->fitToWidth($width);
        }

        $left = floor(($this->_width / 2) - ($width / 2));
        $top  = floor(($this->_height / 2) - ($height / 2));

        // Return trimmed image
        $right  = $width + $left;
        $bottom = $height + $top;

        return $this->crop($left, $top, $right, $bottom);
    }

    /**
     * Fit to height (proportionally resize to specified height)
     *
     * @param int $height
     * @return $this
     */
    public function fitToHeight($height)
    {
        $height = VarFilter::int($height);
        $width  = $height / ($this->_height / $this->_width);

        return $this->resize($width, $height);
    }

    /**
     * Fit to width (proportionally resize to specified width)
     *
     * @param int $width
     * @return $this
     */
    public function fitToWidth($width)
    {
        $width  = VarFilter::int($width);
        $height = $width * ($this->_height / $this->_width);

        return $this->resize($width, $height);
    }

    /**
     * Crop an image
     *
     * @param int $left   Left
     * @param int $top    Top
     * @param int $right  Right
     * @param int $bottom Bottom
     *
     * @return $this
     */
    public function crop($left, $top, $right, $bottom)
    {
        $left   = VarFilter::int($left);
        $top    = VarFilter::int($top);
        $right  = VarFilter::int($right);
        $bottom = VarFilter::int($bottom);

        // Determine crop size
        if ($right < $left) {
            list($left, $right) = array($right, $left);
        }

        if ($bottom < $top) {
            list($top, $bottom) = array($bottom, $top);
        }

        $cropedW = $right - $left;
        $cropedH = $bottom - $top;

        // Perform crop
        $newImage = imagecreatetruecolor($cropedW, $cropedH);
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
        imagecopyresampled($newImage, $this->_image, 0, 0, $left, $top, $cropedW, $cropedH, $cropedW, $cropedH);

        // Update meta data
        $this->_replaceImage($newImage);
        $this->_width  = $cropedW;
        $this->_height = $cropedH;

        return $this;
    }

    /**
     * Flip an image horizontally or vertically
     *
     * @param string $direction x|y|yx|xy
     * @return $this
     */
    public function flip($direction)
    {
        $newImage = imagecreatetruecolor($this->_width, $this->_height);
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);

        $direction = Helper::direction($direction);

        if ($direction === 'y') {
            for ($y = 0; $y < $this->_height; $y++) {
                imagecopy($newImage, $this->_image, 0, $y, 0, $this->_height - $y - 1, $this->_width, 1);
            }

        } elseif ($direction === 'x') {
            for ($x = 0; $x < $this->_width; $x++) {
                imagecopy($newImage, $this->_image, $x, 0, $this->_width - $x - 1, 0, 1, $this->_height);
            }

        } elseif ($direction === 'xy') {
            $this->flip('x');
            $this->flip('y');

        } elseif ($direction === 'yx') {
            $this->flip('y');
            $this->flip('x');
        }

        return $this;
    }

    /**
     * @param $newImage
     */
    protected function _replaceImage($newImage)
    {
        $this->_destroyImage();

        $this->_image  = $newImage;
        $this->_width  = imagesx($this->_image);
        $this->_height = imagesy($this->_image);
    }

    /**
     * Rotate an image
     *
     * @param int          $angle   -360 < x < 360
     * @param string|array $bgColor Hex color string, array(red, green, blue) or array(red, green, blue, alpha).
     *                              Where red, green, blue - integers 0-255, alpha - integer 0-127
     * @return $this
     */
    public function rotate($angle, $bgColor = '#000000')
    {
        // Perform the rotation
        $rgba     = Helper::normalizeColor($bgColor);
        $bgColor  = imagecolorallocatealpha($this->_image, $rgba['r'], $rgba['g'], $rgba['b'], $rgba['a']);
        $newImage = imagerotate($this->_image, -(Helper::rotate($angle)), $bgColor);

        imagesavealpha($newImage, true);
        imagealphablending($newImage, true);

        // Update meta data
        $this->_width  = imagesx($newImage);
        $this->_height = imagesy($newImage);
        $this->_replaceImage($newImage);

        return $this;
    }

    /**
     * Rotates and/or flips an image automatically so the orientation will be correct (based on exif 'Orientation')
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function autoOrient()
    {
        if (!isset($this->_exif['Orientation'])) {
            return $this;

        } elseif ($this->_exif['Orientation'] == 2) { // Flip horizontal
            $this->flip('x');

        } elseif ($this->_exif['Orientation'] == 3) { // Rotate 180 counterclockwise
            $this->rotate(-180);

        } elseif ($this->_exif['Orientation'] == 4) { // Vertical flip
            $this->flip('y');

        } elseif ($this->_exif['Orientation'] == 5) { // Rotate 90 clockwise and flip vertically
            $this->flip('y');
            $this->rotate(90);

        } elseif ($this->_exif['Orientation'] == 6) { // Rotate 90 clockwise
            $this->rotate(90);

        } elseif ($this->_exif['Orientation'] == 7) { // Rotate 90 clockwise and flip horizontally
            $this->flip('x');
            $this->rotate(90);

        } elseif ($this->_exif['Orientation'] == 8) { // Rotate 90 counterclockwise
            $this->rotate(-90);
        }

        return $this;
    }

    /**
     * Overlay an image on top of another, works with 24-bit PNG alpha-transparency
     *
     * @param string    $overlay     An image filename or a Image object
     * @param string    $position    center|top|left|bottom|right|top left|top right|bottom left|bottom right
     * @param float|int $opacity     Overlay opacity 0-1 or 0-100
     * @param int       $globOffsetX Horizontal offset in pixels
     * @param int       $globOffsetY Vertical offset in pixels
     *
     * @return $this
     */
    public function overlay($overlay, $position = 'bottom right', $opacity = .4, $globOffsetX = 0, $globOffsetY = 0)
    {
        // Load overlay image
        if (!($overlay instanceof self)) {
            $overlay = new self($overlay);
        }

        // Convert opacity
        $opacity     = Helper::opacity($opacity);
        $globOffsetX = VarFilter::int($globOffsetX);
        $globOffsetY = VarFilter::int($globOffsetY);

        // Determine position
        switch (strtolower($position)) {
            case 'top left':
                $xOffset = 0 + $globOffsetX;
                $yOffset = 0 + $globOffsetY;
                break;

            case 'top right':
                $xOffset = $this->_width - $overlay->getWidth() + $globOffsetX;
                $yOffset = 0 + $globOffsetY;
                break;

            case 'top':
                $xOffset = ($this->_width / 2) - ($overlay->getWidth() / 2) + $globOffsetX;
                $yOffset = 0 + $globOffsetY;
                break;

            case 'bottom left':
                $xOffset = 0 + $globOffsetX;
                $yOffset = $this->_height - $overlay->getHeight() + $globOffsetY;
                break;

            case 'bottom right':
                $xOffset = $this->_width - $overlay->getWidth() + $globOffsetX;
                $yOffset = $this->_height - $overlay->getHeight() + $globOffsetY;
                break;

            case 'bottom':
                $xOffset = ($this->_width / 2) - ($overlay->getWidth() / 2) + $globOffsetX;
                $yOffset = $this->_height - $overlay->getHeight() + $globOffsetY;
                break;

            case 'left':
                $xOffset = 0 + $globOffsetX;
                $yOffset = ($this->_height / 2) - ($overlay->getHeight() / 2) + $globOffsetY;
                break;

            case 'right':
                $xOffset = $this->_width - $overlay->getWidth() + $globOffsetX;
                $yOffset = ($this->_height / 2) - ($overlay->getHeight() / 2) + $globOffsetY;
                break;

            case 'center':
            default:
                $xOffset = ($this->_width / 2) - ($overlay->getWidth() / 2) + $globOffsetX;
                $yOffset = ($this->_height / 2) - ($overlay->getHeight() / 2) + $globOffsetY;
                break;
        }

        // Perform the overlay
        Helper::imageCopyMergeAlpha(
            $this->_image,
            $overlay->getImage(),
            $xOffset,
            $yOffset,
            0,
            0,
            $overlay->getWidth(),
            $overlay->getHeight(),
            $opacity
        );

        return $this;
    }

    /**
     * Add filter to current image
     *
     * @param string|callable  $filter
     * @param array|int|string $args
     * @return $this
     *
     * @throws Exception
     */
    public function addFilter($filter, $args = array())
    {
        $args     = (array)$args;
        $newImage = null;

        if (is_string($filter)) {

            $filterClass = __NAMESPACE__ . '\Filter';

            if (method_exists($filterClass, $filter)) {
                array_unshift($args, $this->_image);
                $newImage = call_user_func_array(array($filterClass, $filter), $args);

            } else {
                throw new Exception('Undefined Image Filter: ' . $filter);
            }

        } elseif (is_callable($filter)) {
            array_unshift($args, $this->_image);
            $newImage = call_user_func_array($filter, $args);
        }

        if (is_resource($newImage) && get_resource_type($newImage) === 'gd') {
            $this->_replaceImage($newImage);
        }

        return $this;
    }

    /**
     * Outputs image as data base64 to use as img src
     *
     * @param null|string $format  If omitted or null - format of original file will be used, may be gif|jpg|png
     * @param int|null    $quality Output image quality in percents 0-100
     * @return string
     *
     * @throws Exception
     */
    public function getBase64($format = 'gif', $quality = null)
    {
        if (!$this->_image) {
            throw new Exception('Image resource not defined');
        }

        // Output the image
        ob_start();
        $mimetype  = $this->_renderImageByFormat($format, null, $quality);
        $imageData = ob_get_contents();
        ob_end_clean();

        return 'data:' . $mimetype . ';base64,' . base64_encode($imageData);
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getPath()
    {
        if (!$this->_filename) {
            throw new Exception('File not find!');
        }

        return Url::pathToRel($this->_filename);
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return Url::root() . '/' . $this->getPath();
    }
}
