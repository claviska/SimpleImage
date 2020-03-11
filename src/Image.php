<?php
/**
 * JBZoo Image
 *
 * This file is part of the JBZoo CCK package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package    Image
 * @license    MIT
 * @copyright  Copyright (C) JBZoo.com, All rights reserved.
 * @link       https://github.com/JBZoo/Image
 */

namespace JBZoo\Image;

use JBZoo\Utils\Arr;
use JBZoo\Utils\Filter as VarFilter;
use JBZoo\Utils\FS;
use JBZoo\Utils\Image as Helper;
use JBZoo\Utils\Sys;
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
    protected $_image;

    /**
     * @var int
     */
    protected $_quality = self::QUALITY;

    /**
     * @var string
     */
    protected $_filename;

    /**
     * @var array
     */
    protected $_exif = [];

    /**
     * @var int
     */
    protected $_width;

    /**
     * @var int
     */
    protected $_height;

    /**
     * @var string
     */
    protected $_orient;

    /**
     * @var string
     */
    protected $_mime;

    /**
     * Constructor
     *
     * @param string|null $filename
     * @param bool|null   $strict
     *
     * @throws Exception
     * @throws \JBZoo\Utils\Exception
     */
    public function __construct($filename = null, $strict = false)
    {
        Helper::checkGD();

        if (ctype_print($filename) && FS::isFile($filename)) {
            $this->loadFile($filename);

        } elseif (Helper::isGdRes($filename)) {
            $this->loadResource($filename);

        } elseif (is_string($filename) && $filename) {
            $this->loadString($filename, $strict);
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
        return [
            'filename' => $this->_filename,
            'width'    => $this->_width,
            'height'   => $this->_height,
            'mime'     => $this->_mime,
            'quality'  => $this->_quality,
            'exif'     => $this->_exif,
            'orient'   => $this->_orient,
        ];
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
        return $this->_height;
    }

    /**
     * Get the current image resource
     * @return mixed
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
     * @return $this
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
     * @return $this
     *
     * @throws Exception
     */
    public function saveAs($filename, $quality = null)
    {
        if (!$filename) {
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
        return imagepng($this->_image, $filename, round(9 * $quality / 100));
    }

    /**
     * @param string $filename
     * @param int    $quality
     * @return bool
     */
    protected function _saveJpeg($filename, $quality)
    {
        imageinterlace($this->_image, true);
        return imagejpeg($this->_image, $filename, round($quality));
    }

    /**
     * @param string $filename
     * @return bool
     */
    protected function _saveGif($filename)
    {
        return imagegif($this->_image, $filename);
    }

    /**
     * @param string $filename
     * @return bool
     */
    protected function _saveWebP($filename)
    {
        return imagewebp($this->_image, $filename);
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

        $format = strtolower(FS::ext($filename));
        if (!Helper::isSupportedFormat($format)) {
            $format = $this->_mime;
        }

        $filename = FS::clean($filename);

        // Create the image
        $result = $this->_renderImageByFormat($format, $filename, $quality);

        $this->loadFile($filename);
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

        $format = $format ?: $this->_mime;

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

        } elseif (Helper::isWebp($format)) {
            if ($this->_saveWebP($filename)) {
                $result = 'image/webp';
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
     * @return $this
     *
     * @throws Exception
     */
    public function loadFile($filename)
    {
        $cleanFilename = FS::clean($filename);
        if (!FS::isFile($cleanFilename)) {
            throw new Exception('Image file not forund: ' . $filename);
        }

        $this->cleanup();
        $this->_filename = $cleanFilename;
        $this->_loadMeta();

        return $this;
    }

    /**
     * Load an image
     *
     * @param string    $imageString Binary images
     * @param bool|null $strict
     * @return $this
     *
     * @throws Exception
     */
    public function loadString($imageString, $strict = false)
    {
        if (!$imageString) {
            throw new Exception('Image string is empty!');
        }

        $this->cleanup();
        $this->_loadMeta($imageString, $strict);

        return $this;
    }

    /**
     * Load image resource
     *
     * @param mixed $imageRes Image GD Resource
     * @return $this
     *
     * @throws Exception
     */
    public function loadResource($imageRes)
    {
        if (!Helper::isGdRes($imageRes)) {
            throw new Exception('Image is not GD resource!');
        }

        $this->cleanup();
        $this->_loadMeta($imageRes);

        return $this;
    }

    /**
     * Get meta data of image or base64 string
     *
     * @param null|string $image
     * @param bool|null   $strict
     *
     * @return $this
     * @throws Exception
     */
    protected function _loadMeta($image = null, $strict = false)
    {
        // Gather meta data
        if (null === $image && $this->_filename) {
            $imageInfo = getimagesize($this->_filename);
            $this->_image = $this->_imageCreate($imageInfo['mime']);

        } elseif (Helper::isGdRes($image)) {
            $this->_image = $image;
            $imageInfo = [
                '0'    => imagesx($this->_image),
                '1'    => imagesy($this->_image),
                'mime' => 'image/png',
            ];

        } else {
            if (!Sys::isFunc('getimagesizefromstring')) {
                throw new Exception('PHP 5.4 is required to use method getimagesizefromstring');
            }

            if ($strict) {
                $cleanedString = str_replace(' ', '+', preg_replace('#^data:image/[^;]+;base64,#', '', $image));
                if (base64_decode($cleanedString, true) === false) {
                    throw new Exception('Invalid image source.');
                }
            }

            $image = Helper::strToBin($image);
            $imageInfo = getimagesizefromstring($image);
            $this->_image = imagecreatefromstring($image);
        }

        // Set internal state
        $this->_mime = $imageInfo['mime'];
        $this->_width = $imageInfo['0'];
        $this->_height = $imageInfo['1'];
        $this->_exif = $this->_getExif();
        $this->_orient = $this->_getOrientation();

        // Prepare alpha chanel
        Helper::addAlpha($this->_image);

        return $this;
    }

    /**
     * Clean whole image object
     * @return $this
     */
    public function cleanup()
    {
        $this->_filename = null;

        $this->_mime = null;
        $this->_width = null;
        $this->_height = null;
        $this->_exif = [];
        $this->_orient = null;
        $this->_quality = self::QUALITY;

        $this->_destroyImage();

        return $this;
    }

    /**
     * Destroy image resource if not empty
     */
    protected function _destroyImage()
    {
        if (Helper::isGdRes($this->_image)) {
            imagedestroy($this->_image);
            $this->_image = null;
        }
    }

    /**
     * @return array
     */
    protected function _getExif()
    {
        $result = [];

        if ($this->_filename && Sys::isFunc('exif_read_data') && Helper::isJpeg($this->_mime)) {
            $result = exif_read_data($this->_filename);
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
            
        } elseif (Helper::isWebp($format)) {
            $result = imagecreatefromwebp($this->_filename);

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
     * @throws Exception
     */
    public function create($width, $height = null, $color = null)
    {
        $this->cleanup();

        $height = $height ?: $width;

        $this->_width = VarFilter::int($width);
        $this->_height = VarFilter::int($height);
        $this->_image = imagecreatetruecolor($this->_width, $this->_height);
        $this->_mime = 'image/png';
        $this->_exif = [];

        $this->_orient = $this->_getOrientation();

        if (null !== $color) {
            return $this->addFilter('fill', $color);
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
        $width = VarFilter::int($width);
        $height = VarFilter::int($height);

        // Generate new GD image
        $newImage = imagecreatetruecolor($width, $height);

        if ($this->isGif()) {
            // Preserve transparency in GIFs
            $transIndex = imagecolortransparent($this->_image);
            $palletsize = imagecolorstotal($this->_image);

            if ($transIndex >= 0 && $transIndex < $palletsize) {
                $trColor = imagecolorsforindex($this->_image, $transIndex);

                $red = VarFilter::int($trColor['red']);
                $green = VarFilter::int($trColor['green']);
                $blue = VarFilter::int($trColor['blue']);

                $transIndex = imagecolorallocate($newImage, $red, $green, $blue);

                imagefill($newImage, 0, 0, $transIndex);
                imagecolortransparent($newImage, $transIndex);
            }

        } else {
            // Preserve transparency in PNG
            Helper::addAlpha($newImage, false);
        }

        // Resize
        imagecopyresampled($newImage, $this->_image, 0, 0, 0, 0, $width, $height, $this->_width, $this->_height);

        // Update meta data
        $this->_replaceImage($newImage);
        $this->_width = $width;
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

        $width = $this->_width;
        $height = $this->_height;

        // Make width fit into new dimensions
        if ($this->_width > $maxWidth) {
            $width = $maxWidth;
            $height = $width * $aspectRatio;
        }

        // Make height fit into new dimensions
        if ($height > $maxHeight) {
            $height = $maxHeight;
            $width = $height / $aspectRatio;
        }

        return $this->resize($width, $height);
    }

    /**
     * Thumbnail.
     * This function attempts to get the image to as close to the provided dimensions as possible, and then crops the
     * remaining overflow (from the center) to get the image to be the size specified. Useful for generating thumbnails.
     *
     * @param int      $width
     * @param int|null $height    If omitted - assumed equal to $width
     * @param bool     $topIsZero Force top offset = 0
     *
     * @return $this
     */
    public function thumbnail($width, $height = null, $topIsZero = false)
    {
        $width = VarFilter::int($width);
        $height = VarFilter::int($height);

        // Determine height
        $height = $height ?: $width;

        // Determine aspect ratios
        $currentAspectRatio = $this->_height / $this->_width;
        $newAspectRatio = $height / $width;

        // Fit to height/width
        if ($newAspectRatio > $currentAspectRatio) {
            $this->fitToHeight($height);
        } else {
            $this->fitToWidth($width);
        }

        $left = floor(($this->_width / 2) - ($width / 2));
        $top = floor(($this->_height / 2) - ($height / 2));

        // Return trimmed image
        $right = $width + $left;
        $bottom = $height + $top;

        if ($topIsZero) {
            $bottom -= $top;
            $top = 0;
        }

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
        $width = $height / ($this->_height / $this->_width);

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
        $width = VarFilter::int($width);
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
        $left = VarFilter::int($left);
        $top = VarFilter::int($top);
        $right = VarFilter::int($right);
        $bottom = VarFilter::int($bottom);

        // Determine crop size
        if ($right < $left) {
            list($left, $right) = [$right, $left];
        }

        if ($bottom < $top) {
            list($top, $bottom) = [$bottom, $top];
        }

        $cropedW = $right - $left;
        $cropedH = $bottom - $top;

        // Perform crop
        $newImage = imagecreatetruecolor($cropedW, $cropedH);
        Helper::addAlpha($newImage);
        imagecopyresampled($newImage, $this->_image, 0, 0, $left, $top, $cropedW, $cropedH, $cropedW, $cropedH);

        // Update meta data
        $this->_replaceImage($newImage);
        $this->_width = $cropedW;
        $this->_height = $cropedH;

        return $this;
    }

    /**
     * @param $newImage
     */
    protected function _replaceImage($newImage)
    {
        $this->_destroyImage();

        $this->_image = $newImage;
        $this->_width = imagesx($this->_image);
        $this->_height = imagesy($this->_image);
    }

    /**
     * Rotates and/or flips an image automatically so the orientation will be correct (based on exif 'Orientation')
     *
     * @return $this
     * @codeCoverageIgnore
     * @throws Exception
     */
    public function autoOrient()
    {
        if (!Arr::key('Orientation', $this->_exif)) {
            return $this;
        }

        $orient = (int)$this->_exif['Orientation'];

        if ($orient === 2) { // Flip horizontal
            $this->addFilter('flip', 'x');

        } elseif ($orient === 3) { // Rotate 180 counterclockwise
            $this->addFilter('rotate', -180);

        } elseif ($orient === 4) { // Vertical flip
            $this->addFilter('flip', 'y');

        } elseif ($orient === 5) { // Rotate 90 clockwise and flip vertically
            $this->addFilter('flip', 'y');
            $this->addFilter('rotate', 90);

        } elseif ($orient === 6) { // Rotate 90 clockwise
            $this->addFilter('rotate', 90);

        } elseif ($orient === 7) { // Rotate 90 clockwise and flip horizontally
            $this->addFilter('flip', 'x');
            $this->addFilter('rotate', 90);

        } elseif ($orient === 8) { // Rotate 90 counterclockwise
            $this->addFilter('rotate', -90);
        }

        return $this;
    }

    /**
     * Overlay an image on top of another, works with 24-bit PNG alpha-transparency
     *
     * @param string|Image $overlay     An image filename or a Image object
     * @param string       $position    center|top|left|bottom|right|top left|top right|bottom left|bottom right
     * @param float|int    $opacity     Overlay opacity 0-1 or 0-100
     * @param int          $globOffsetX Horizontal offset in pixels
     * @param int          $globOffsetY Vertical offset in pixels
     *
     * @return $this
     * @throws Exception
     */
    public function overlay($overlay, $position = 'bottom right', $opacity = .4, $globOffsetX = 0, $globOffsetY = 0)
    {
        // Load overlay image
        if (!($overlay instanceof self)) {
            $overlay = new self($overlay);
        }

        // Convert opacity
        $opacity = Helper::opacity($opacity);
        $globOffsetX = VarFilter::int($globOffsetX);
        $globOffsetY = VarFilter::int($globOffsetY);

        // Determine position
        list($xOffset, $yOffset) = Helper::getInnerCoords(
            $position,
            [$this->_width, $this->_height],
            [$overlay->getWidth(), $overlay->getHeight()],
            [$globOffsetX, $globOffsetY]
        );

        // Perform the overlay
        Helper::imageCopyMergeAlpha(
            $this->_image,
            $overlay->getImage(),
            [$xOffset, $yOffset],
            [0, 0],
            [$overlay->getWidth(), $overlay->getHeight()],
            $opacity
        );

        return $this;
    }

    /**
     * Add filter to current image
     *
     * @param string|callable $filter
     * @return $this
     *
     * @throws Exception
     */
    public function addFilter($filter)
    {
        $args = func_get_args();
        $args[0] = $this->_image;

        $newImage = null;

        if (is_string($filter)) {

            $filterClass = __NAMESPACE__ . '\Filter';

            if (method_exists($filterClass, $filter)) {
                $newImage = call_user_func_array([$filterClass, $filter], $args);
            } else {
                throw new Exception('Undefined Image Filter: ' . $filter);
            }

        } elseif (is_callable($filter)) {
            $newImage = call_user_func_array($filter, $args);
        }

        if (Helper::isGdRes($newImage)) {
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
    public function getBase64($format = 'gif', $quality = null, $addMime = true)
    {
        list($mimeType, $binaryData) = $this->_renderBinary($format, $quality);

        $result = base64_encode($binaryData);

        if ($addMime) {
            $result = 'data:' . $mimeType . ';base64,' . $result;
        }

        return $result;
    }

    /**
     * Outputs image as binary data
     *
     * @param null|string $format  If omitted or null - format of original file will be used, may be gif|jpg|png
     * @param int|null    $quality Output image quality in percents 0-100
     * @return string
     *
     * @throws Exception
     */
    public function getBinary($format = null, $quality = null)
    {
        $result = $this->_renderBinary($format, $quality);

        return $result[1];
    }

    /**
     *
     * @param string $format
     * @param int    $quality
     * @return array
     * @throws Exception
     */
    protected function _renderBinary($format, $quality)
    {
        if (!$this->_image) {
            throw new Exception('Image resource not defined');
        }

        ob_start();
        $mimeType = $this->_renderImageByFormat($format, null, $quality);
        $imageData = ob_get_contents();
        ob_end_clean();

        return [$mimeType, $imageData];
    }

    /**
     * Get relative path to image
     *
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
     * Get full URL to image (if not CLI mode)
     *
     * @return string
     * @throws Exception
     */
    public function getUrl()
    {
        return Url::root() . '/' . $this->getPath();
    }
}
