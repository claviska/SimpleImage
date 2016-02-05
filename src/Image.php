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

use JBZoo\Utils\Filter;
use JBZoo\Utils\FS;
use JBZoo\Utils\Vars;

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
     * @param string|null $filename
     * @throws Exception
     */
    public function __construct($filename = null)
    {
        Helper::checkSystem();

        if (null !== $filename) {
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
     * @param int $newQuality
     * @return $this
     */
    public function setQuality($newQuality)
    {
        $this->_quality = Filter::int($newQuality);
        return $this;
    }

    /**
     * Save an image
     * The resulting format will be determined by the file extension.
     *
     * @param null|int $quality Output image quality in percents 0-100
     * @return Image
     *
     * @throws Exception
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
            throw new Exception('Empty filename to save image'); // @codeCoverageIgnore
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
     * @param string $filename
     * @param int    $quality
     * @return bool
     * @throws Exception
     */
    protected function _save($filename, $quality)
    {
        $quality = Vars::limit($quality, 0, 100);
        $quality = $quality ?: $this->_quality;

        $format = FS::ext($filename) ?: $this->_mime;
        $format = strtolower($format);

        $filename = FS::clean($filename);

        // Create the image
        $result = false;
        if (Helper::isJpeg($format)) {
            $result = $this->_saveJpeg($filename, $quality);

        } elseif (Helper::isPng($format)) {
            $result = $this->_savePng($filename, $quality);

        } elseif (Helper::isGif($format)) {
            $result = $this->_saveGif($filename);
        }

        if (!$result) {
            throw new Exception('Unable to save image: ' . $filename);
        }

        $this->open($filename);
        $this->_quality = $quality;

        return $result;
    }

    /**
     * Load an image
     *
     * @param string $filename Path to image file
     *
     * @return Image
     * @throws Exception
     */
    public function open($filename)
    {
        $this->cleanup();

        $this->_filename = FS::clean($filename);
        if (!file_exists($this->_filename)) {
            throw new Exception('File not found: ' . $filename);
        }

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
        if (null !== $this->_image && get_resource_type($this->_image) === 'gd') {
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

        $this->_width  = Filter::int($width);
        $this->_height = Filter::int($height);
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
        $width  = Filter::int($width);
        $height = Filter::int($height);

        // Generate new GD image
        $newImage = imagecreatetruecolor($width, $height);

        if ($this->isGif()) {
            // Preserve transparency in GIFs
            $transIndex = imagecolortransparent($this->_image);
            $palletsize = imagecolorstotal($this->_image);

            if ($transIndex >= 0 && $transIndex < $palletsize) {
                $trColor = imagecolorsforindex($this->_image, $transIndex);

                $red   = Filter::int($trColor['red']);
                $green = Filter::int($trColor['green']);
                $blue  = Filter::int($trColor['blue']);

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
        $this->_destroyImage();
        $this->_image  = $newImage;
        $this->_width  = $width;
        $this->_height = $height;

        return $this;
    }

    /**
     * Fill image with color
     *
     * @param string $color     Hex color string, array(red, green, blue) or array(red, green, blue, alpha).
     *                          Where red, green, blue - integers 0-255, alpha - integer 0-127
     * @return $this
     */
    public function fill($color = '#000')
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
        $width  = Filter::int($width);
        $height = Filter::int($height);

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
        $height = Filter::int($height);
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
        $width  = Filter::int($width);
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
        $left   = Filter::int($left);
        $top    = Filter::int($top);
        $right  = Filter::int($right);
        $bottom = Filter::int($bottom);

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
        $this->_destroyImage();
        $this->_image  = $newImage;
        $this->_width  = $cropedW;
        $this->_height = $cropedH;

        return $this;
    }

    /**
     * Flip an image horizontally or vertically
     *
     * @param string $direction x|y
     * @return $this
     */
    public function flip($direction)
    {
        $newImage = imagecreatetruecolor($this->_width, $this->_height);
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);

        switch (strtolower($direction)) {
            case 'y':
                for ($y = 0; $y < $this->_height; $y++) {
                    imagecopy($newImage, $this->_image, 0, $y, 0, $this->_height - $y - 1, $this->_width, 1);
                }
                break;
            default:
                for ($x = 0; $x < $this->_width; $x++) {
                    imagecopy($newImage, $this->_image, $x, 0, $this->_width - $x - 1, 0, 1, $this->_height);
                }
                break;
        }

        $this->_destroyImage();
        $this->_image = $newImage;

        return $this;
    }
}
