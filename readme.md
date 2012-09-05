The SimpleImage PHP class
=========================

*By Cory LaViska for A Beautiful Site, LLC.
(http://www.abeautifulsite.net/)*

*Dual licensed under the MIT / GPLv2 licenses*

Overview
--------

This class makes image manipulation in PHP as simple as possible. The
examples are the best way to learn how to use it, but here it is in a
nutshell:

	try {
	    $img = new SimpleImage('image.jpg');
	    $img->flip('x')->rotate(90)->best_fit(320, 200)->sepia()->save('result.gif');
	} catch(Exception $e) {
		echo 'Error: ' . $e->getMessage();
	}

The two lines inside the _try_ block load **image.jpg**, flip it horizontally, rotate
it 90 degrees, shrink it to fit within a 320x200 box, apply a sepia
effect, convert it to a GIF, and save it to **result.gif**.

With this class, you can effortlessly:
-	Resize images (free resize, resize to width, resize to height, resize to fit)
-	Crop images
-	Flip/rotate/adjust orientation
-	Adjust brightness & contrast
-	Desaturate, colorize, pixelate, blur, etc.
-	Overlay one image onto another (watermarking)
-	Add text using a font of your choice
-	Convert between GIF, JPEG, and PNG formats
-	Strip EXIF data

Requirements
------------

This class requires the PHP GD library. Some methods (i.e. colorize and
pixelate) require a more recent version of PHP (5.2—5.3 or higher). The
rest can be used with any recent version of PHP + GD.

Usage
-----

### Loading

You can load an image when you instantiate a new SimpleImage object:

    $img = new SimpleImage('image.jpg');

Or you can load it later on:

    $img = new SimpleImage();
    $img->load('image.jpg');

### Saving

Images must be saved after you manipulate them. To save your changes to
the original file, simply call:

    $img->save();

Alternatively, you can specify a new filename:

    $img->save('new-image.jpg');

You can specify quality as a second parameter for JPEG and PNG images.
Use 0-100 for JPEG and 0-9 for PNG. (For PNG, this is actually the
compression level.)

    $img->save('new-image.jpg', 90);

### Converting Between Formats

When saving, the resulting image format is determined by the file
extension. For example, you can convert a JPEG to a GIF by doing this:

    $img = new SimpleImage('image.jpg');
    $img->save('image.gif');

### Stripping EXIF data

There is no built-in method for stripping EXIF data, partly because
there is currently no way to *prevent* EXIF data from being stripped
using the GD library. However, you can easily strip EXIF data simply by
loading and saving:

    $img = new SimpleImage('image.jpg');
    $img->save();

### Method Chaining

SimpleImage supports method chaining, so you can make multiple changes
and save the resulting image with just one line of code:

    $img = new SimpleImage('image.jpg');
    $img->flip('x')->rotate(90)->best_fit(320, 200)->desaturate()->invert()->save('result.jpg')

You can chain all of the methods below as well as the **load()**
and **save()** methods above.  (You cannot chain the constructor,
however, as this is not supported by PHP.)

### Error Handling

SimpleImage throws exceptions when things don't work right. You should
always load/manipulate/save images inside of a *try/catch* block to
handle them properly:

    try {
        $img = new SimpleImage('image.jpg');
        $img->flip('x')->save('flipped.jpg');
    } catch(Exception $e) {
        echo 'Error: ' . $e->getMessage();
    }

### Method Examples

Most methods have intelligent defaults so you don't need to pass in
every argument.  Check out **SimpleImage.class.php** for
required/optional parameters and valid ranges for certain arguments.

    // Flip the image horizontally (use y to flip vertically)
    $img->flip('x');
    
    // Rotate the image 90 degrees
    $img->rotate(90);
    
    // Adjust the orientation if needed (physically rotates/flips the image based on its EXIF 'Orientation' property)
    $img->auto_orient();

    // Resize the image to 320x200
    $img->resize(320, 200);
    
    // Shrink the image to the specified width while maintaining proportion (width)
    $img->fit_to_width(320);
    
    // Shrink the image to the specified height while maintaining proportion (height)
    $img->fit_to_height(200);
    
    // Shrink the image proportionally to fit inside a 500x500 box
    $img->best_fit(500, 500);
    
    // Crop a portion of the image from x1, y1 to x2, y2
    $img->crop(100, 100, 400, 400);
    
    // Trim the image to a square and resize to 100x100
    $img->square_crop(100);
    
    // Desaturate (grayscale)
    $img->desaturate();
    
    // Invert
    $img->invert();
    
    // Adjust Brightness (-255 to 255)
    $img->brightness(100);
    
    // Adjust Contrast (-100 to 100)
    $img->contrast(50);
    
    // Colorize red at 50% opacity
    $img->colorize('#FF0000', .5);
    
    // Edges filter
    $img->edges();
    
    // Emboss filter
    $img->emboss();

    // Mean removal filter  
    $img->mean_remove();  
  
    // Selective blur (one pass)  
    $img->blur();  
  
    // Gaussian blur (two passes)  
    $img->blur('gaussian', 2);  
  
    // Sketch filter  
    $img->sketch();  
  
    // Smooth filter (-10 to 10)  
    $img->smooth(5);  
  
    // Pixelate using 8px blocks  
    $img->pixelate(8);  
  
    // Sepia effect (simulated)  
    $img->sepia();  
  
    // Overlay watermark.png at 50% opacity at the bottom-right of the image with a 10 pixel horizontal and vertical margin
    $img->overlay('watermark.png', 'bottom right', .5, -10, -10);  
  
    // Add 32-point white text top-centered (plus 20px) on the image*  
    $img->text('Your Text', 'font.ttf', 32, '#FFFFFF', 'top', 0, 20);

* Valid positions are *center, top, right, bottom, left, top left, top
right, bottom left, bottom right*

### Utility Methods

The following methods are not chainable, because they return information about 
the image you're working with:
	
	// Get info about the original image (before any changes were made)
	// Returns width, height, orientation, exif (array), and mime
	$info = $img->get_original_info();
	
	// Get the current width
	$width = $img->get_width();
	
	// Get the current height
	$height = $img->get_height();
	
	// Get the current orientation (returns 'portrait', 'landscape', or 'square')
	$orientation = $img->get_orientation();

Differences from Version 1
--------------------------

SimpleImage has been completely overhauled since version 1. Here are the
most significant changes:

-   **Simple_Image** is now **SimpleImage**
-   For more efficient processing, the class now has public load and
    save methods. In version 1, these methods were automatically
    executed on every manipulation, which led to slower processing times
    and potential quality loss.
-   Because load and save are now public methods, it only made sense to
    support method chaining. You can now make as many modifications as
    you want to an image and save it in just one line of code.
-   A positioning bug in the text() method was fixed so positioning is
    100% accurate now. In version 1, the size of the text box was
    erroneously calculating points instead of pixels.
-   Arguments for many methods have been normalized. Opacity is always 0
    through 1 (like in CSS) instead of 0 through 100 and colors are
    consistently passed in as hex color codes instead of RGB.
-   A few methods were consolidated to use the public resize and crop
    methods instead of replicating the same functionality like in
    version 1. This helped reduce the size of version 2 to under 700
    lines of code (version 1 was 728 LOC).

Known Issues
------------

-   Need to fix **rotate()** when primary image is a transparent PNG
-   Need to fix **overlay()** when primary image is a transparent PNG
-   Need to fix **text()** when primary image is a transparent PNG.
