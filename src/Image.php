<?php

/**
 * JBZoo Toolbox - Image.
 *
 * This file is part of the JBZoo Toolbox project.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT
 * @copyright  Copyright (C) JBZoo.com, All rights reserved.
 * @see        https://github.com/JBZoo/Image
 */

declare(strict_types=1);

namespace JBZoo\Image;

use JBZoo\Utils\Filter as VarFilter;
use JBZoo\Utils\FS;
use JBZoo\Utils\Image as Helper;
use JBZoo\Utils\Sys;
use JBZoo\Utils\Url;

use function JBZoo\Utils\isStrEmpty;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
final class Image
{
    public const LANDSCAPE = 'landscape';
    public const PORTRAIT  = 'portrait';
    public const SQUARE    = 'square';

    public const FLIP_HORIZONTAL                           = 2;
    public const FLIP_180_COUNTERCLOCKWISE                 = 3;
    public const FLIP_VERTICAL                             = 4;
    public const FLIP_ROTATE_90_CLOCKWISE_AND_VERTICALLY   = 5;
    public const FLIP_ROTATE_90_CLOCKWISE                  = 6;
    public const FLIP_ROTATE_90_CLOCKWISE_AND_HORIZONTALLY = 7;
    public const FLIP_ROTATE_90_COUNTERCLOCKWISE           = 8;

    public const DEFAULT_QUALITY = 95;
    public const DEFAULT_MIME    = 'image/png';

    /** GD Resource or bin data. */
    private ?\GdImage $image   = null;
    private int       $quality = self::DEFAULT_QUALITY;
    private array     $exif    = [];
    private int       $width   = 0;
    private int       $height  = 0;
    private ?string   $filename;
    private ?string   $orient;
    private ?string   $mime;

    public function __construct(\GdImage|string|null $filename = null, bool $strict = false)
    {
        Helper::checkGD();

        $this->orient   = null;
        $this->filename = null;
        $this->mime     = null;

        if (
            $filename !== ''
            && \is_string($filename)
            && \ctype_print($filename)
            && FS::isFile($filename)
        ) {
            $this->loadFile($filename);
        } elseif ($filename instanceof \GdImage) {
            $this->loadResource($filename);
        } elseif (!isStrEmpty($filename)) {
            $this->loadString($filename, $strict);
        }
    }

    /**
     * Destroy image resource.
     */
    public function __destruct()
    {
        $this->cleanup();
    }

    public function getInfo(): array
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
     * Get the current width.
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * Get the current height.
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * Get the current image resource.
     */
    public function getImage(): ?\GdImage
    {
        return $this->image;
    }

    public function setQuality(int $newQuality): self
    {
        $this->quality = Helper::quality($newQuality);

        return $this;
    }

    /**
     * Save an image. The resulting format will be determined by the file extension.
     * @param null|int $quality Output image quality in percents 0-100
     */
    public function save(?int $quality = null): self
    {
        $quality ??= $this->quality;

        if ($this->filename !== null && $this->filename !== '') {
            $this->internalSave($this->filename, $quality);

            return $this;
        }

        throw new Exception('Filename is not defined');
    }

    /**
     * Save an image. The resulting format will be determined by the file extension.
     * @param string   $filename If omitted - original file will be overwritten
     * @param null|int $quality  Output image quality in percents 0-100
     */
    public function saveAs(string $filename, ?int $quality = null): self
    {
        if (isStrEmpty($filename)) {
            throw new Exception('Empty filename to save image');
        }

        $dir = FS::dirName($filename);
        if (\realpath($dir) !== false && \is_dir($dir)) {
            $this->internalSave($filename, $quality);
        } else {
            throw new Exception("Target directory \"{$dir}\" not exists");
        }

        return $this;
    }

    public function isGif(): bool
    {
        return Helper::isGif($this->mime);
    }

    public function isPng(): bool
    {
        return Helper::isPng($this->mime);
    }

    public function isWebp(): bool
    {
        return Helper::isWebp($this->mime);
    }

    public function isJpeg(): bool
    {
        return Helper::isJpeg($this->mime);
    }

    /**
     * Load an image.
     * @param string $filename Path to image file
     */
    public function loadFile(string $filename): self
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
     * Load an image.
     * @param null|string $imageString Binary images
     */
    public function loadString(?string $imageString, bool $strict = false): self
    {
        if ($imageString === null || $imageString === '') {
            throw new Exception('Image string is empty!');
        }

        $this->cleanup();
        $this->loadMeta($imageString, $strict);

        return $this;
    }

    /**
     * Load image resource.
     * @param null|\GdImage|string $imageRes Image GD Resource
     */
    public function loadResource(\GdImage|string|null $imageRes = null): self
    {
        if (!$imageRes instanceof \GdImage) {
            throw new Exception('Image is not GD resource!');
        }

        $this->cleanup();
        $this->loadMeta($imageRes);

        return $this;
    }

    /**
     * Clean whole image object.
     */
    public function cleanup(): self
    {
        $this->filename = null;

        $this->mime    = null;
        $this->width   = 0;
        $this->height  = 0;
        $this->exif    = [];
        $this->orient  = null;
        $this->quality = self::DEFAULT_QUALITY;

        $this->destroyImage();

        return $this;
    }

    public function isPortrait(): bool
    {
        return $this->orient === self::PORTRAIT;
    }

    public function isLandscape(): bool
    {
        return $this->orient === self::LANDSCAPE;
    }

    public function isSquare(): bool
    {
        return $this->orient === self::SQUARE;
    }

    /**
     * Create an image from scratch.
     * @param int               $width  Image width
     * @param null|int          $height If omitted - assumed equal to $width
     * @param null|array|string $color  Hex color string, array(red, green, blue) or array(red, green, blue, alpha).
     *                                  Where red, green, blue - integers 0-255, alpha - integer 0-127
     */
    public function create(int $width, ?int $height = null, array|string|null $color = null): self
    {
        $this->cleanup();

        $height = (int)$height === 0 ? $width : $height;

        $this->width  = VarFilter::int($width);
        $this->height = VarFilter::int($height);

        $newImageRes = \imagecreatetruecolor($this->width, $this->height);
        if ($newImageRes !== false) {
            $this->image = $newImageRes;
        } else {
            throw new Exception("Can't create empty image resource");
        }

        $this->mime = self::DEFAULT_MIME;
        $this->exif = [];

        $this->orient = $this->getOrientation();

        if ($color !== null) {
            return $this->addFilter('fill', $color);
        }

        return $this;
    }

    /**
     * Resize an image to the specified dimensions.
     * @phan-suppress PhanPossiblyFalseTypeArgumentInternal
     */
    public function resize(float $width, float $height): self
    {
        $width  = VarFilter::int($width);
        $height = VarFilter::int($height);

        // Generate new GD image
        $newImage = \imagecreatetruecolor($width, $height);
        if ($newImage === false) {
            throw new Exception("Can't create new image resource");
        }

        if ($this->image === null) {
            throw new Exception('Image resource in not defined');
        }

        if ($this->isGif()) {
            // Preserve transparency in GIFs
            $transIndex = \imagecolortransparent($this->image);
            $palletSize = \imagecolorstotal($this->image);

            if ($transIndex > 0 && $transIndex < $palletSize) {
                $trColor = \imagecolorsforindex($this->image, $transIndex);

                $red   = 0;
                $green = 0;
                $blue  = 0;

                $colorsTypeCount = 3;

                if (\count($trColor) >= $colorsTypeCount) {
                    $red   = VarFilter::int($trColor['red']);
                    $green = VarFilter::int($trColor['green']);
                    $blue  = VarFilter::int($trColor['blue']);
                }

                $transIndex = (int)\imagecolorallocate($newImage, $red, $green, $blue);

                \imagefill($newImage, 0, 0, $transIndex);
                \imagecolortransparent($newImage, $transIndex);
            }
        } else {
            // Preserve transparency in PNG
            Helper::addAlpha($newImage, false);
        }

        // Resize
        \imagecopyresampled($newImage, $this->image, 0, 0, 0, 0, $width, $height, $this->width, $this->height);

        // Update meta data
        $this->replaceImage($newImage);
        $this->width  = $width;
        $this->height = $height;

        return $this;
    }

    /**
     * Best fit (proportionally resize to fit in specified width/height)
     * Shrink the image proportionally to fit inside a $width x $height box.
     */
    public function bestFit(int $maxWidth, int $maxHeight): self
    {
        // If it already fits, there's nothing to do
        if ($this->width <= $maxWidth && $this->height <= $maxHeight) {
            return $this;
        }

        // Determine aspect ratio
        $aspectRatio = $this->height / $this->width;

        $width  = $this->width;
        $height = $this->height;

        // Make width fit into new dimensions
        if ($this->width > $maxWidth) {
            $width  = $maxWidth;
            $height = $width * $aspectRatio;
        }

        // Make height fit into new dimensions
        if ($height > $maxHeight) {
            $height = $maxHeight;
            $width  = $height / $aspectRatio;
        }

        return $this->resize($width, $height);
    }

    /**
     * Thumbnail.
     * This function attempts to get the image to as close to the provided dimensions as possible, and then crops the
     * remaining overflow (from the center) to get the image to be the size specified. Useful for generating thumbnails.
     * @param null|int $height    If omitted - assumed equal to $width
     * @param bool     $topIsZero Force top offset = 0
     */
    public function thumbnail(int $width, ?int $height = null, bool $topIsZero = false): self
    {
        $width  = VarFilter::int($width);
        $height = VarFilter::int($height);

        // Determine height
        $height = $height === 0 ? $width : $height;

        // Determine aspect ratios
        $currentAspectRatio = $this->height / $this->width;
        $newAspectRatio     = $height / $width;

        // Fit to height/width
        if ($newAspectRatio > $currentAspectRatio) {
            $this->fitToHeight($height);
        } else {
            $this->fitToWidth($width);
        }

        $left = (int)\floor(($this->width / 2) - ($width / 2));
        $top  = (int)\floor(($this->height / 2) - ($height / 2));

        // Return trimmed image
        $right  = $width + $left;
        $bottom = $height + $top;

        if ($topIsZero) {
            $bottom -= $top;
            $top = 0;
        }

        return $this->crop($left, $top, $right, $bottom);
    }

    /**
     * Fit to height (proportionally resize to specified height).
     */
    public function fitToHeight(int $height): self
    {
        $height = VarFilter::int($height);
        $width  = $height / ($this->height / $this->width);

        return $this->resize($width, $height);
    }

    /**
     * Fit to width (proportionally resize to specified width).
     */
    public function fitToWidth(int $width): self
    {
        $width  = VarFilter::int($width);
        $height = $width * ($this->height / $this->width);

        return $this->resize($width, $height);
    }

    /**
     * Crop an image.
     * @param int $left   Left
     * @param int $top    Top
     * @param int $right  Right
     * @param int $bottom Bottom
     */
    public function crop(int $left, int $top, int $right, int $bottom): self
    {
        $left   = VarFilter::int($left);
        $top    = VarFilter::int($top);
        $right  = VarFilter::int($right);
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
        $newImage = \imagecreatetruecolor($croppedW, $croppedH);
        if ($newImage === false) {
            throw new Exception("Can't crop image, imagecreatetruecolor() failed");
        }

        Helper::addAlpha($newImage);

        if ($this->image instanceof \GdImage) {
            \imagecopyresampled($newImage, $this->image, 0, 0, $left, $top, $croppedW, $croppedH, $croppedW, $croppedH);
        } else {
            throw new Exception("Can't crop image, image resource is undefined");
        }

        // Update meta data
        $this->replaceImage($newImage);
        $this->width  = $croppedW;
        $this->height = $croppedH;

        return $this;
    }

    /**
     * Rotates and/or flips an image automatically so the orientation will be correct (based on exif 'Orientation').
     */
    public function autoOrient(): self
    {
        if (!\array_key_exists('Orientation', $this->exif)) {
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
     * Overlay an image on top of another, works with 24-bit PNG alpha-transparency.
     * @param Image|string $overlay     An image filename or an Image object
     * @param string       $position    center|top|left|bottom|right|top left|top right|bottom left|bottom right
     * @param float        $opacity     Overlay opacity 0-1 or 0-100
     * @param int          $globOffsetX Horizontal offset in pixels
     * @param int          $globOffsetY Vertical offset in pixels
     */
    public function overlay(
        self|string $overlay,
        string $position = 'bottom right',
        float $opacity = .4,
        int $globOffsetX = 0,
        int $globOffsetY = 0,
    ): self {
        // Load overlay image
        if (!$overlay instanceof self) {
            $overlay = new self($overlay);
        }

        // Convert opacity
        $opacity     = Helper::opacity($opacity);
        $globOffsetX = VarFilter::int($globOffsetX);
        $globOffsetY = VarFilter::int($globOffsetY);

        // Determine position
        $offsetCoords = Helper::getInnerCoords(
            $position,
            [$this->width, $this->height],
            [$overlay->getWidth(), $overlay->getHeight()],
            [$globOffsetX, $globOffsetY],
        );

        $xOffset = (int)($offsetCoords[0] ?? null);
        $yOffset = (int)($offsetCoords[1] ?? null);

        if ($this->image === null) {
            throw new Exception("Can't overlay image, image resource is undefined");
        }

        $overlayImage = $overlay->getImage();
        if ($overlayImage === null) {
            throw new Exception("Can't overlay image, overlay image resource is undefined");
        }

        // Perform the overlay
        Helper::imageCopyMergeAlpha(
            $this->image,
            $overlayImage,
            [$xOffset, $yOffset],
            [0, 0],
            [$overlay->getWidth(), $overlay->getHeight()],
            $opacity,
        );

        return $this;
    }

    /**
     * Add filter to current image.
     */
    public function addFilter(mixed $filter): self
    {
        $args    = \func_get_args();
        $args[0] = $this->image;

        if (\is_string($filter)) {
            if (\method_exists(Filter::class, $filter)) {
                /** @var \Closure $filterFunc */
                $filterFunc = [Filter::class, $filter];
                $newImage   = $filterFunc(...$args);
            } else {
                throw new Exception("Undefined Image Filter: {$filter}");
            }
        } elseif (\is_callable($filter)) {
            $newImage = $filter(...$args);
        } else {
            throw new Exception('Undefined filter type');
        }

        if ($newImage instanceof \GdImage) {
            $this->replaceImage($newImage);
        }

        return $this;
    }

    /**
     * Outputs image as data base64 to use as img src.
     *
     * @param null|string $format  If omitted or null - format of original file will be used, may be gif|jpg|png
     * @param null|int    $quality Output image quality in percents 0-100
     */
    public function getBase64(?string $format = 'gif', ?int $quality = null, bool $addMime = true): string
    {
        [$mimeType, $binaryData] = $this->renderBinary($format, $quality);

        $result = \base64_encode($binaryData);

        if ($addMime) {
            $result = 'data:' . $mimeType . ';base64,' . $result;
        }

        return $result;
    }

    /**
     * Outputs image as binary data.
     *
     * @param null|string $format  If omitted or null - format of original file will be used, may be gif|jpg|png
     * @param null|int    $quality Output image quality in percents 0-100
     */
    public function getBinary(?string $format = null, ?int $quality = null): string
    {
        $result = $this->renderBinary($format, $quality);

        return $result[1];
    }

    /**
     * Get relative path to image.
     */
    public function getPath(): string
    {
        if ($this->filename === null || $this->filename === '') {
            throw new Exception('Filename is empty');
        }

        return Url::pathToRel($this->filename);
    }

    /**
     * Get full URL to image (if not CLI mode).
     */
    public function getUrl(): string
    {
        $rootPath = Url::root();
        $relPath  = $this->getPath();

        return "{$rootPath}/{$relPath}";
    }

    private function savePng(string $filename, int $quality = self::DEFAULT_QUALITY): bool
    {
        if ($this->image !== null) {
            return \imagepng(
                $this->image,
                $filename === '' ? null : $filename,
                (int)\round(9 * $quality / 100),
            );
        }

        throw new Exception('Image resource ins not defined');
    }

    private function saveJpeg(string $filename, int $quality = self::DEFAULT_QUALITY): bool
    {
        if ($this->image !== null) {
            // imageinterlace($this->image, true);
            return \imagejpeg(
                $this->image,
                $filename === '' ? null : $filename,
                (int)\round($quality),
            );
        }

        throw new Exception('Image resource ins not defined');
    }

    private function saveGif(string $filename): bool
    {
        if ($this->image !== null) {
            return \imagegif(
                $this->image,
                $filename === '' ? null : $filename,
            );
        }

        throw new Exception('Image resource ins not defined');
    }

    private function saveWebP(string $filename, int $quality = self::DEFAULT_QUALITY): bool
    {
        if (!\function_exists('\imagewebp')) {
            throw new Exception('Function imagewebp() is not available. Rebuild your ext-gd for PHP');
        }

        if ($this->image !== null) {
            return \imagewebp(
                $this->image,
                $filename === '' ? null : $filename,
                (int)\round($quality),
            );
        }

        throw new Exception('Image resource ins not defined');
    }

    /**
     * Save image to file.
     */
    private function internalSave(string $filename, ?int $quality): bool
    {
        $quality = $quality > 0 ? $quality : $this->quality;
        $quality = Helper::quality($quality);

        $format = \strtolower(FS::ext($filename));
        if (!Helper::isSupportedFormat($format)) {
            $format = $this->mime;
        }

        $filename = FS::clean($filename);

        // Create the image
        if ($this->renderImageByFormat($format, $filename, $quality) !== null) {
            $this->loadFile($filename);
            $this->quality = $quality;

            return true;
        }

        return false;
    }

    /**
     * Render image resource as binary.
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function renderImageByFormat(
        ?string $format,
        string $filename,
        int $quality = self::DEFAULT_QUALITY,
    ): ?string {
        if ($this->image === null) {
            throw new Exception('Image resource not defined');
        }

        $format = $format === null || $format === '' ? $this->mime : $format;

        $result = null;
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
            throw new Exception("Undefined format: {$format}");
        }

        return $result;
    }

    /**
     * Get metadata of image or base64 string.
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function loadMeta(\GdImage|string|null $image = null, bool $strict = false): self
    {
        // Gather meta data
        if ($image === null && $this->filename !== null && $this->filename !== '') {
            $imageInfo = \getimagesize($this->filename);
            if ($imageInfo !== false) {
                $this->image = $this->imageCreate((string)($imageInfo['mime'] ?? ''));
            }
        } elseif ($image instanceof \GdImage) {
            $this->image = $image;
            $imageInfo   = [
                '0'    => \imagesx($this->image),
                '1'    => \imagesy($this->image),
                'mime' => self::DEFAULT_MIME,
            ];
        } elseif (\is_string($image)) {
            if ($strict) {
                $cleanedString = \str_replace(
                    ' ',
                    '+',
                    (string)\preg_replace('#^data:image/[^;]+;base64,#', '', $image),
                );

                if (\base64_decode($cleanedString, true) === false) {
                    throw new Exception('Invalid image source.');
                }
            }

            $imageBin = Helper::strToBin($image);
            if ($imageBin !== null) {
                $imageInfo = \getimagesizefromstring($imageBin);
                if ($imageInfo === false) {
                    throw new Exception('Invalid image source. Can\'tget image info from string');
                }

                $newImage    = \imagecreatefromstring($imageBin);
                $this->image = $newImage !== false ? $newImage : null;
            }
        } else {
            throw new Exception('Undefined format of source. Only "resource|string" are expected');
        }

        // Set internal state
        if (isset($imageInfo) && \is_array($imageInfo)) {
            $this->mime   = $imageInfo['mime'] ?? null;
            $this->width  = (int)($imageInfo['0'] ?? 0);
            $this->height = (int)($imageInfo['1'] ?? 0);
        }
        $this->exif   = $this->getExif();
        $this->orient = $this->getOrientation();

        // Prepare alpha chanel
        if ($this->image !== null) {
            Helper::addAlpha($this->image);
        } else {
            throw new Exception('Image resource not defined');
        }

        return $this;
    }

    /**
     * Destroy image resource if not empty.
     */
    private function destroyImage(): void
    {
        if ($this->image instanceof \GdImage) {
            \imagedestroy($this->image);
            $this->image = null;
        }
    }

    private function getExif(): array
    {
        $result = [];

        if (
            $this->filename !== ''
            && $this->filename !== null
            && Sys::isFunc('exif_read_data')
            && Helper::isJpeg($this->mime)
        ) {
            $exif   = \exif_read_data($this->filename);
            $result = $exif === false ? [] : $exif;
        }

        return $result;
    }

    /**
     * Create image resource.
     */
    private function imageCreate(?string $format): \GdImage
    {
        if ($this->filename === '' || $this->filename === null) {
            throw new Exception('Filename is undefined');
        }

        if (Helper::isJpeg($format)) {
            $result = \imagecreatefromjpeg($this->filename);
        } elseif (Helper::isPng($format)) {
            $result = \imagecreatefrompng($this->filename);
        } elseif (Helper::isGif($format)) {
            $result = \imagecreatefromgif($this->filename);
        } elseif (\function_exists('imagecreatefromwebp') && Helper::isWebp($format)) {
            $result = \imagecreatefromwebp($this->filename);
        } else {
            throw new Exception("Invalid image: {$this->filename}");
        }

        if ($result === false) {
            throw new Exception("Can't create new image resource by filename: {$this->filename}; format: {$format}");
        }

        return $result;
    }

    /**
     * Get the current orientation.
     */
    private function getOrientation(): string
    {
        if ($this->width > $this->height) {
            return self::LANDSCAPE;
        }

        if ($this->width < $this->height) {
            return self::PORTRAIT;
        }

        return self::SQUARE;
    }

    private function replaceImage(\GdImage $newImage): void
    {
        if (!self::isSameResource($this->image, $newImage)) {
            $this->destroyImage();
            $this->image  = $newImage;
            $this->width  = \imagesx($this->image);
            $this->height = \imagesy($this->image);
        }
    }

    private function renderBinary(?string $format, ?int $quality): array
    {
        if ($this->image === null) {
            throw new Exception('Image resource not defined');
        }

        \ob_start();
        $mimeType  = $this->renderImageByFormat($format, '', (int)$quality);
        $imageData = \ob_get_clean();

        return [$mimeType, $imageData];
    }

    private static function isSameResource(?\GdImage $image1 = null, ?\GdImage $image2 = null): bool
    {
        if ($image1 === null || $image2 === null) {
            return false;
        }

        return \spl_object_id($image1) === \spl_object_id($image2);
    }
}
