<?php

/**
 * JBZoo Toolbox - Image
 *
 * This file is part of the JBZoo Toolbox project.
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
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Image
{
    public const LANDSCAPE = 'landscape';
    public const PORTRAIT  = 'portrait';
    public const SQUARE    = 'square';
    public const QUALITY   = 95;

    public const FLIP_HORIZONTAL                           = 2;
    public const FLIP_180_COUNTERCLOCKWISE                 = 3;
    public const FLIP_VERTICAL                             = 4;
    public const FLIP_ROTATE_90_CLOCKWISE_AND_VERTICALLY   = 5;
    public const FLIP_ROTATE_90_CLOCKWISE                  = 6;
    public const FLIP_ROTATE_90_CLOCKWISE_AND_HORIZONTALLY = 7;
    public const FLIP_ROTATE_90_COUNTERCLOCKWISE           = 8;

    public const DEFAULT_MIME = 'image/png';

    /**
     * GD Resource or bin data
     * @var resource|null
     */
    protected $image;

    /**
     * @var int
     */
    protected $quality = self::QUALITY;

    /**
     * @var string|null
     */
    protected $filename;

    /**
     * @var array
     */
    protected $exif = [];

    /**
     * @var int
     */
    protected $width = 0;

    /**
     * @var int
     */
    protected $height = 0;

    /**
     * @var string|null
     */
    protected $orient;

    /**
     * @var string|null
     */
    protected $mime;

    /**
     * Image constructor.
     *
     * @param resource|string|null $filename
     * @param bool                 $strict
     *
     * @throws Exception
     * @throws \JBZoo\Utils\Exception
     */
    public function __construct($filename = null, bool $strict = false)
    {
        Helper::checkGD();

        if ($filename && is_string($filename) && ctype_print($filename) && FS::isFile($filename)) {
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
            'filename' => $this->filename,
            'width'    => $this->width,
            'height'   => $this->height,
            'mime'     => $this->mime,
            'quality'  => $this->quality,
            'exif'     => $this->exif,
            'orient'   => $this->orient,
        ];
    }

    /**
     * Get the current width
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Get the current height
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Get the current image resource
     * @return mixed
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param int $newQuality
     * @return $this
     */
    public function setQuality($newQuality)
    {
        $this->quality = Helper::quality($newQuality);
        return $this;
    }

    /**
     * Save an image
     * The resulting format will be determined by the file extension.
     *
     * @param int|null $quality Output image quality in percents 0-100
     * @return $this
     * @throws Exception
     */
    public function save(?int $quality = null): Image
    {
        $quality = $quality ?: $this->quality;

        if ($this->filename) {
            $this->internalSave($this->filename, $quality);
            return $this;
        }

        throw new Exception('Filename is not defined');
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
            $this->internalSave($filename, $quality);
        } else {
            throw new Exception("Target directory \"{$dir}\" not exists");
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isGif(): bool
    {
        return $this->mime && Helper::isGif($this->mime);
    }

    /**
     * @return bool
     */
    public function isPng(): bool
    {
        return $this->mime && Helper::isPng($this->mime);
    }

    /**
     * @return bool
     */
    public function isWebp(): bool
    {
        return $this->mime && Helper::isWebp($this->mime);
    }

    /**
     * @return bool
     */
    public function isJpeg(): bool
    {
        return $this->mime && Helper::isJpeg($this->mime);
    }

    /**
     * @param string $filename
     * @param int    $quality
     * @return bool
     */
    protected function savePng($filename, $quality)
    {
        if ($this->image) {
            return imagepng($this->image, $filename ?: null, (int)round(9 * $quality / 100));
        }

        throw new Exception('Image resource ins not defined');
    }

    /**
     * @param string $filename
     * @param int    $quality
     * @return bool
     */
    protected function saveJpeg($filename, $quality)
    {
        if ($this->image) {
            imageinterlace($this->image, 1);
            return imagejpeg($this->image, $filename ?: null, (int)round($quality));
        }

        throw new Exception('Image resource ins not defined');
    }

    /**
     * @param string $filename
     * @return bool
     */
    protected function saveGif($filename)
    {
        if ($this->image) {
            return imagegif($this->image, $filename ?: null);
        }

        throw new Exception('Image resource ins not defined');
    }

    /**
     * @param string $filename
     * @return bool
     */
    protected function saveWebP($filename)
    {
        if ($this->image) {
            return imagewebp($this->image, $filename ?: null);
        }

        throw new Exception('Image resource ins not defined');
    }

    /**
     * Save image to file
     *
     * @param string   $filename
     * @param int|null $quality
     * @return bool
     *
     * @throws Exception
     */
    protected function internalSave(string $filename, ?int $quality): bool
    {
        $quality = $quality ?: $this->quality;
        $quality = Helper::quality($quality);

        $format = strtolower(FS::ext($filename));
        if (!Helper::isSupportedFormat($format)) {
            $format = $this->mime;
        }

        $filename = FS::clean($filename);

        // Create the image
        $result = $this->renderImageByFormat($format, $filename, $quality);

        $this->loadFile($filename);
        $this->quality = $quality;

        return true;
    }

    /**
     * Render image resource as binary
     *
     * @param string|null $format
     * @param string      $filename
     * @param int         $quality
     * @return bool|string
     *
     * @throws Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function renderImageByFormat(?string $format, $filename, $quality)
    {
        if (!$this->image) {
            throw new Exception('Image resource not defined');
        }

        $format = $format ?: $this->mime;

        $result = false;
        if (Helper::isJpeg($format)) {
            if ($this->saveJpeg($filename, $quality)) {
                $result = 'image/jpeg';
            }
        } elseif (Helper::isPng($format)) {
            if ($this->savePng($filename, $quality)) {
                $result = 'image/png';
            }
        } elseif (Helper::isGif($format)) {
            if ($this->saveGif($filename)) {
                $result = 'image/gif';
            }
        } elseif (Helper::isWebp($format)) {
            if ($this->saveWebP($filename)) {
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
            throw new Exception('Image file not found: ' . $filename);
        }

        $this->cleanup();
        $this->filename = $cleanFilename;
        $this->loadMeta();

        return $this;
    }

    /**
     * Load an image
     *
     * @param string|null $imageString Binary images
     * @param bool        $strict
     * @return $this
     *
     * @throws Exception
     */
    public function loadString(?string $imageString, bool $strict = false)
    {
        if (!$imageString) {
            throw new Exception('Image string is empty!');
        }

        $this->cleanup();
        $this->loadMeta($imageString, $strict);

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
        $this->loadMeta($imageRes);

        return $this;
    }

    /**
     * Get meta data of image or base64 string
     *
     * @param resource|string|null $image
     * @param bool|null            $strict
     *
     * @return $this
     * @throws Exception
     */
    protected function loadMeta($image = null, $strict = false)
    {
        // Gather meta data
        if (null === $image && $this->filename) {
            if ($imageInfo = getimagesize($this->filename)) {
                $this->image = $this->imageCreate($imageInfo['mime'] ?? null);
            }
        } elseif (is_resource($image) && Helper::isGdRes($image)) {
            $this->image = $image;
            $imageInfo = [
                '0'    => imagesx($this->image),
                '1'    => imagesy($this->image),
                'mime' => self::DEFAULT_MIME,
            ];
        } elseif (is_string($image)) {
            if ($strict) {
                $cleanedString = str_replace(' ', '+', (string)preg_replace('#^data:image/[^;]+;base64,#', '', $image));
                if (base64_decode($cleanedString, true) === false) {
                    throw new Exception('Invalid image source.');
                }
            }

            $imageBin = Helper::strToBin($image);
            $imageInfo = getimagesizefromstring($imageBin);
            $this->image = imagecreatefromstring($imageBin) ?: null;
        } else {
            throw new Exception('Undefined format of source. Only "resource|string" are expected');
        }

        // Set internal state
        if (is_array($imageInfo)) {
            $this->mime = $imageInfo['mime'] ?? null;
            $this->width = $imageInfo['0'] ?? null;
            $this->height = $imageInfo['1'] ?? null;
        }
        $this->exif = $this->getExif();
        $this->orient = $this->getOrientation();

        // Prepare alpha chanel
        Helper::addAlpha($this->image);

        return $this;
    }

    /**
     * Clean whole image object
     * @return $this
     */
    public function cleanup()
    {
        $this->filename = null;

        $this->mime = null;
        $this->width = 0;
        $this->height = 0;
        $this->exif = [];
        $this->orient = null;
        $this->quality = self::QUALITY;

        $this->destroyImage();

        return $this;
    }

    /**
     * Destroy image resource if not empty
     */
    protected function destroyImage(): void
    {
        if ($this->image && Helper::isGdRes($this->image)) {
            imagedestroy($this->image);
            $this->image = null;
        }
    }

    /**
     * @return array
     */
    protected function getExif(): array
    {
        $result = [];

        if ($this->filename && Sys::isFunc('exif_read_data') && Helper::isJpeg($this->mime)) {
            $result = exif_read_data($this->filename) ?: [];
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
    protected function imageCreate(string $format)
    {
        if (!$this->filename) {
            throw new Exception('Filename is undefined');
        }

        if (Helper::isJpeg($format)) {
            $result = imagecreatefromjpeg($this->filename);
        } elseif (Helper::isPng($format)) {
            $result = imagecreatefrompng($this->filename);
        } elseif (Helper::isGif($format)) {
            $result = imagecreatefromgif($this->filename);
        } elseif (Helper::isWebp($format)) {
            $result = imagecreatefromwebp($this->filename);
        } else {
            throw new Exception("Invalid image: {$this->filename}");
        }

        if (!$result) {
            throw new Exception("Can't create new image resource by filename: {$this->filename}; format: {$format}");
        }

        return $result;
    }

    /**
     * Get the current orientation
     * @return string   portrait|landscape|square
     */
    protected function getOrientation()
    {
        if ($this->width > $this->height) {
            return self::LANDSCAPE;
        }

        if ($this->width < $this->height) {
            return self::PORTRAIT;
        }

        return self::SQUARE;
    }

    /**
     * @return bool
     */
    public function isPortrait()
    {
        return $this->orient === self::PORTRAIT;
    }

    /**
     * @return bool
     */
    public function isLandscape()
    {
        return $this->orient === self::LANDSCAPE;
    }

    /**
     * @return bool
     */
    public function isSquare()
    {
        return $this->orient === self::SQUARE;
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

        $this->width = VarFilter::int($width);
        $this->height = VarFilter::int($height);
        if ($newImageRes = imagecreatetruecolor($this->width, $this->height)) {
            $this->image = $newImageRes;
        } else {
            throw new Exception("Can't create empty image resource");
        }

        $this->mime = self::DEFAULT_MIME;
        $this->exif = [];

        $this->orient = $this->getOrientation();

        if (null !== $color) {
            return $this->addFilter('fill', $color);
        }

        return $this;
    }

    /**
     * Resize an image to the specified dimensions
     *
     * @param float $width
     * @param float $height
     * @return $this
     */
    public function resize(float $width, float $height)
    {
        $width = VarFilter::int($width);
        $height = VarFilter::int($height);

        // Generate new GD image
        if (!$newImage = imagecreatetruecolor($width, $height)) {
            throw new Exception("Can't create new image resource");
        }

        if (!$this->image) {
            throw new Exception('Image resource in not defined');
        }

        if ($this->isGif()) {
            // Preserve transparency in GIFs
            $transIndex = imagecolortransparent($this->image);
            $palletSize = imagecolorstotal($this->image);

            if ($transIndex >= 0 && $transIndex < $palletSize) {
                $trColor = imagecolorsforindex($this->image, $transIndex);

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
        imagecopyresampled($newImage, $this->image, 0, 0, 0, 0, $width, $height, $this->width, $this->height);

        // Update meta data
        $this->replaceImage($newImage);
        $this->width = $width;
        $this->height = $height;

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
        if ($this->width <= $maxWidth && $this->height <= $maxHeight) {
            return $this;
        }

        // Determine aspect ratio
        $aspectRatio = $this->height / $this->width;

        $width = $this->width;
        $height = $this->height;

        // Make width fit into new dimensions
        if ($this->width > $maxWidth) {
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
        $currentAspectRatio = $this->height / $this->width;
        $newAspectRatio = $height / $width;

        // Fit to height/width
        if ($newAspectRatio > $currentAspectRatio) {
            $this->fitToHeight($height);
        } else {
            $this->fitToWidth($width);
        }

        $left = (int)floor(($this->width / 2) - ($width / 2));
        $top = (int)floor(($this->height / 2) - ($height / 2));

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
        $width = $height / ($this->height / $this->width);

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
        $height = $width * ($this->height / $this->width);

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
            [$left, $right] = [$right, $left];
        }

        if ($bottom < $top) {
            [$top, $bottom] = [$bottom, $top];
        }

        $croppedW = $right - $left;
        $croppedH = $bottom - $top;

        // Perform crop
        $newImage = imagecreatetruecolor($croppedW, $croppedH);
        Helper::addAlpha($newImage);

        if (is_resource($newImage) && is_resource($this->image)) {
            imagecopyresampled($newImage, $this->image, 0, 0, $left, $top, $croppedW, $croppedH, $croppedW, $croppedH);
        } else {
            throw new Exception("Can't crop image, image resource is undefined");
        }

        // Update meta data
        $this->replaceImage($newImage);
        $this->width = $croppedW;
        $this->height = $croppedH;

        return $this;
    }

    /**
     * @param resource $newImage
     */
    protected function replaceImage($newImage): void
    {
        if (!$this->isSameResource($this->image, $newImage)) {
            $this->destroyImage();

            $this->image = $newImage;
            $this->width = imagesx($this->image);
            $this->height = imagesy($this->image);
        }
    }

    /**
     * Rotates and/or flips an image automatically so the orientation will be correct (based on exif 'Orientation')
     *
     * @return $this
     * @throws Exception
     */
    public function autoOrient()
    {
        if (!Arr::key('Orientation', $this->exif)) {
            return $this;
        }

        $orient = (int)$this->exif['Orientation'];

        if ($orient === self::FLIP_HORIZONTAL) {
            $this->addFilter('flip', 'x');
        } elseif ($orient === self::FLIP_180_COUNTERCLOCKWISE) {
            $this->addFilter('rotate', -180);
        } elseif ($orient === self::FLIP_VERTICAL) {
            $this->addFilter('flip', 'y');
        } elseif ($orient === self::FLIP_ROTATE_90_CLOCKWISE_AND_VERTICALLY) {
            $this->addFilter('flip', 'y');
            $this->addFilter('rotate', 90);
        } elseif ($orient === self::FLIP_ROTATE_90_CLOCKWISE) {
            $this->addFilter('rotate', 90);
        } elseif ($orient === self::FLIP_ROTATE_90_CLOCKWISE_AND_HORIZONTALLY) {
            $this->addFilter('flip', 'x');
            $this->addFilter('rotate', 90);
        } elseif ($orient === self::FLIP_ROTATE_90_COUNTERCLOCKWISE) {
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
        [$xOffset, $yOffset] = Helper::getInnerCoords(
            $position,
            [$this->width, $this->height],
            [$overlay->getWidth(), $overlay->getHeight()],
            [$globOffsetX, $globOffsetY]
        );

        // Perform the overlay
        Helper::imageCopyMergeAlpha(
            $this->image,
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
     * @param string|callable $filterName
     * @param mixed           $arguments
     * @return $this
     */
    public function addFilter($filterName, $arguments = null)
    {
        $args = func_get_args();
        $args[0] = $this->image;

        $newImage = null;

        if (is_string($filterName)) {
            if (method_exists(Filter::class, $filterName)) {
                /** @var \Closure $filterFunc */
                $filterFunc = [Filter::class, $filterName];
                $newImage = call_user_func_array($filterFunc, $args);
            } else {
                throw new Exception("Undefined Image Filter: {$filterName}");
            }
        } elseif (is_callable($filterName)) {
            $newImage = call_user_func_array($filterName, $args);
        }

        if (Helper::isGdRes($newImage)) {
            $this->replaceImage($newImage);
        }

        return $this;
    }

    /**
     * Outputs image as data base64 to use as img src
     *
     * @param string|null $format  If omitted or null - format of original file will be used, may be gif|jpg|png
     * @param int|null    $quality Output image quality in percents 0-100
     * @param bool        $addMime
     * @return string
     *
     * @throws Exception
     */
    public function getBase64(?string $format = 'gif', ?int $quality = null, bool $addMime = true)
    {
        [$mimeType, $binaryData] = $this->renderBinary($format, $quality);

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
        $result = $this->renderBinary($format, $quality);

        return $result[1];
    }

    /**
     * @param string|null $format
     * @param int|null    $quality
     * @return array
     * @throws Exception
     */
    protected function renderBinary(?string $format, ?int $quality)
    {
        if (!$this->image) {
            throw new Exception('Image resource not defined');
        }

        ob_start();
        $mimeType = $this->renderImageByFormat($format, '', (int)$quality);
        $imageData = ob_get_clean();

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
        if (!$this->filename) {
            throw new Exception('File not find!');
        }

        return Url::pathToRel($this->filename);
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

    /**
     * @param resource|null $resource1
     * @param resource|null $resource2
     * @return bool
     */
    protected function isSameResource($resource1 = null, $resource2 = null): bool
    {
        if (!$resource1 || !$resource2) {
            return false;
        }

        if (Helper::isGdRes($resource1) && Helper::isGdRes($resource2)) {
            return (int)$resource1 === (int)$resource2 && (int)$resource1 > 0;
        }

        return false;
    }
}
