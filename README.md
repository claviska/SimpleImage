# JBZoo / Image

[![Coverage Status](https://coveralls.io/repos/JBZoo/Image/badge.svg)](https://coveralls.io/github/JBZoo/Image)    [![Psalm Coverage](https://shepherd.dev/github/JBZoo/Image/coverage.svg)](https://shepherd.dev/github/JBZoo/Image)    [![PHP Strict Types](https://img.shields.io/badge/strict__types-%3D1-brightgreen)](https://www.php.net/manual/en/language.types.declarations.php#language.types.declarations.strict)    
[![Stable Version](https://poser.pugx.org/jbzoo/image/version)](https://packagist.org/packages/jbzoo/image)    [![Latest Unstable Version](https://poser.pugx.org/jbzoo/image/v/unstable)](https://packagist.org/packages/jbzoo/image)    [![Dependents](https://poser.pugx.org/jbzoo/image/dependents)](https://packagist.org/packages/jbzoo/image/dependents?order_by=downloads)    [![GitHub Issues](https://img.shields.io/github/issues/jbzoo/image)](https://github.com/JBZoo/Image/issues)    [![Total Downloads](https://poser.pugx.org/jbzoo/image/downloads)](https://packagist.org/packages/jbzoo/image/stats)    [![GitHub License](https://img.shields.io/github/license/jbzoo/image)](https://github.com/JBZoo/Image/blob/master/LICENSE)



Package provides object-oriented way to manipulate with images as simple as possible.


### Install
```sh
composer require jbzoo/image
```

### Example

```php
use JBZoo\Image\Image;

$img = (new Image('./example/source-image.jpg'))
    ->addFilter('flip', 'x')
    ->addFilter('text', 'Some text', './res/font.ttf')
    ->thumbnail(320, 240)
    ->saveAs('./example/dist-image.png');
```

That block loads `source-image.jpg`, flip it horizontally, rotate it 90 degrees clockwise,
shrink it to fit within a 320x240 box, apply a sepia effect, convert it to a PNG, and save it to `dist-image.png` with other format!


With this class, you can effortlessly:
 * Resize images (free resize, resize to width, resize to height, resize to fit)
 * Crop images
 * Flip/rotate/adjust orientation
 * Adjust brightness & contrast
 * Desaturate, colorize, pixelate, blur, etc.
 * Overlay one image onto another (watermarking)
 * Add text using a font of your choice
 * Convert between GIF, JPEG, PNG and WEBP formats
 * Strip EXIF data (Just save it!)


### Usage
```php
use JBZoo\Image\Image;
use JBZoo\Image\Filter;
use JBZoo\Image\Exception;

try { // Error handling

    $img = (new Image('./some-path/image.jpg'))     // You can load an image when you instantiate a new Image object
        ->loadFile('./some-path/another-path.jpg')  // Load another file (replace internal state)

        // Saving
        ->save()   // Images must be saved after you manipulate them. To save your changes to the original file.
        ->save(90) // Specify quality (0 to 100)

        // Save as new file
        ->saveAs('./some-path/new-image.jpg')     // Alternatively, you can specify a new filename
        ->saveAs('./some-path/new-image.jpg', 90) // You can specify quality as a second parameter in percents within range 0-100
        ->saveAs('./some-path/new-image.png')     // Or convert it into another format by extention (gif|jpeg|png|webp)

        // Resizing
        ->resize(320, 200)          // Resize the image to 320x200
        ->thumbnail(100, 75)        // Trim the image and resize to exactly 100x75 (crop CENTER if needed)
        ->thumbnail(100, 75, true)  // Trim the image and resize to exactly 100x75 (crop TOP if needed)
        ->fitToWidth(320)           // Shrink the image to the specified width while maintaining proportion (width)
        ->fitToHeight(200)          // Shrink the image to the specified height while maintaining proportion (height)
        ->bestFit(500, 500)         // Shrink the image proportionally to fit inside a 500x500 box
        ->crop(100, 100, 400, 400)  // Crop a portion of the image from left, top, right, bottom

        // Filters
        ->addFilter('sepia')                        // Sepia effect (simulated)
        ->addFilter('grayscale')                    // Grayscale
        ->addFilter('desaturate', 50)               // Desaturate
        ->addFilter('pixelate', 8)                  // Pixelate using 8px blocks
        ->addFilter('edges')                        // Edges filter
        ->addFilter('emboss')                       // Emboss filter
        ->addFilter('invert')                       // Invert colors
        ->addFilter('blur', Filter::BLUR_SEL)       // Selective blur (one pass)
        ->addFilter('blur', Filter::BLUR_GAUS, 2)   // Gaussian blur (two passes)
        ->addFilter('brightness', 100)              // Adjust Brightness (-255 to 255)
        ->addFilter('contrast', 50)                 // Adjust Contrast (-100 to 100)
        ->addFilter('colorize', '#FF0000', .5)      // Colorize red at 50% opacity
        ->addFilter('meanRemove')                   // Mean removal filter
        ->addFilter('smooth', 5)                    // Smooth filter (-10 to 10)
        ->addFilter('opacity', .5)                  // Change opacity
        ->addFilter('rotate', 90)                   // Rotate the image 90 degrees clockwise
        ->addFilter('flip', 'x')                    // Flip the image horizontally
        ->addFilter('flip', 'y')                    // Flip the image vertically
        ->addFilter('flip', 'xy')                   // Flip the image horizontally and vertically
        ->addFilter('fill', '#fff')                 // Fill image with white color

        // Custom filter handler
        ->addFilter(function ($image, $blockSize) {
            imagefilter($image, IMG_FILTER_PIXELATE, $blockSize, true);
        }, 2) // $blockSize = 2

        // Overlay watermark.png at 50% opacity at the bottom-right of the image with a 10 pixel horz and vert margin
        ->overlay('./image/watermark.png', 'bottom right', .5, -10, -10)

        // Other
        ->create(200, 100, '#000') // Create empty image 200x100 with black background
        ->setQuality(95)           // Set new internal quality state
        ->autoOrient()             // Adjust the orientation if needed (physically rotates/flips the image based on its EXIF 'Orientation' property)
    ;

} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage();
}

```


### Methods to create Image objects
```php
// Filename
$img = new Image('./path/to/image.png');

// Base64 format
$img = new Image('data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==');

// Image string
$img = new Image('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==');

// Some binary data
$imgBin = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==');
$img = new Image($imgBin);

// Resource
$imgRes = imagecreatefromjpeg('./some-image.jpeg');
$img = new Image($imgRes);
```


### Other utility methods
```php
$img = new Image($_SERVER['DOCUMENT_ROOT'] . '/resources/butterfly.jpg');

$img->getBase64();                  // Get base64 as string (format from inner state)
$img->getBase64('gif');             // Convert to GIF and get base64 as string
$img->getBase64('jpeg', 85);        // Convert to JPEG (q=85%) and get base64 as string
$img->getBase64('png', 100, false); // Get only base64 without mime header

$img->getBinary();              // Get clean binary data (format from inner state)
$img->getBinary('jpeg', 85);    // Binary in JPEG format with quality 85%

$img->getHeight();      // Height in px
$img->getWidth();       // Width in px
$img->cleanup();        // Full cleanup of internal state of object
$img->getImage();       // Get GD Image resource

$img->isGif();          // Check format
$img->isJpeg();         // Check format
$img->isPng();          // Check format

$img->isPortrait();     // Check orientation
$img->isLandscape();    // Check orientation
$img->isSquare();       // Check orientation

$img->getUrl();         // Get full url to image     - http://site.com/resources/butterfly.jpg
$img->getPath();        // Get relative url to image - /resources/butterfly.jpg

$imgInfo = $img->getInfo(); // Get array of all properties

// It will be something like that ...
$imgInfo = [
    "filename" => "/<full_path>/resources/butterfly.jpg",
    "width"    => 640,
    "height"   => 478,
    "mime"     => "image/jpeg",
    "quality"  => 95,
    "exif"     => [
        "FileName"      => "butterfly.jpg",
        "FileDateTime"  => 1454653291,
        "FileSize"      => 280448,
        "FileType"      => 2,
        "MimeType"      => "image/jpeg",
        "SectionsFound" => "",
        "COMPUTED"      => [
            "html"    => 'width="640" height="478"',
            "Height"  => 478,
            "Width"   => 640,
            "IsColor" => 1,
        ],
    ],
    "orient"   => "landscape",
];
```


### Add text on image (filter)
```php
$img = new Image('./resources/butterfly.jpg');
$img->addFilter(
    'text',                             // Filter name
    'Some image description',           // Text to render on image
    './resources/font.ttf'              // TTF font file
    [                                   // Additionals params
        'font-size'      => 48,                       // Font size in px
        'color'          => array('#ff7f00', '#f00'), // Or one color as string

        // Stroke
        'stroke-color'   => array('#f00', '#ff7f00'), // Or one color as string
        'stroke-size'    => 3,                        // Stroke size in px
        'stroke-spacing' => 5,                        // Letter spacing in px (only for stroke mode)

        // Position of text
        'offset-x'       => -140,       // X offset in px
        'offset-y'       => 100,        // Y offset in px
        'position'       => 't',        // top|t|Helper::TOP| ... More details in the method Helper::position()

        // Experimental
        'angle'          => 0,          // Angle for each letter
    ])
    ->saveAs('./dist/new-file.png');    // Save it to new file
```


### Unit testing and Code Quality
```sh
make update
make test-all
```


### License
MIT


## See Also

- [CI-Report-Converter](https://github.com/JBZoo/CI-Report-Converter) - The tool converts different error reporting standards for deep compatibility with popular CI systems.
- [Composer-Diff](https://github.com/JBZoo/Composer-Diff) - See what packages have changed after `composer update`.
- [Composer-Graph](https://github.com/JBZoo/Composer-Graph) - Dependency graph visualization for composer.json (PHP + Composer) based on mermaid-js.
- [Mermaid-PHP](https://github.com/JBZoo/Mermaid-PHP) - Generate diagrams and flowcharts with the help of the mermaid script language.
- [Utils](https://github.com/JBZoo/Utils) - Collection of useful PHP functions, mini-classes, and snippets for every day.
- [Data](https://github.com/JBZoo/Data) - Extended implementation of ArrayObject. Use files as config/array. 
- [Retry](https://github.com/JBZoo/Retry) - Tiny PHP library providing retry/backoff functionality with multiple backoff strategies and jitter support.
- [SimpleTypes](https://github.com/JBZoo/SimpleTypes) - Converting any values and measures - money, weight, exchange rates, length, ...
