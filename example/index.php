<?php
namespace abeautifulsite;
use Exception;

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
    $img->create(200, 100, '#08c')->save('processed/create-from-scratch.png');

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

    // Thumbnail
    $img->load('butterfly.jpg')->thumbnail(100, 75)->save('processed/butterfly-thumbnail.jpg');

    // Fit to width
    $img->load('butterfly.jpg')->fit_to_width(100)->save('processed/butterfly-fit-to-width.jpg');

    // Fit to height
    $img->load('butterfly.jpg')->fit_to_height(100)->save('processed/butterfly-fit-to-height.jpg');

    // Best fit
    $img->load('butterfly.jpg')->best_fit(100, 400)->save('processed/butterfly-best-fit.jpg');

    // Crop
    $img->load('butterfly.jpg')->crop(160, 110, 460, 360)->save('processed/butterfly-crop.jpg');

    // Border
    $img->load('butterfly.jpg')->border(2, '#000')->save('processed/butterfly-border.jpg');

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
    $img->load('butterfly.jpg')->colorize('#08c', .75)->save('processed/butterfly-colorize.jpg');

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

    // Change opacity
    $img->load('butterfly.jpg')->opacity(.5)->save('processed/butterfly-opacity.png');

    // Text
    $img->load('butterfly.jpg')->text('Butterfly', __DIR__.'/delicious.ttf', 32, '#FFFFFF', 'bottom', 0, -20)->save('processed/butterfly-text.jpg');

    // Text with multiple colors
    $img->load('butterfly.jpg')->text('Butterfly', __DIR__.'/delicious.ttf', 32, ['#F00', '#FF7F00', '#FF0', '#0F0', '#0FF', '#00F'], 'bottom', 0, -20, null, null, null, 3)->save("processed/butterfly-text-stroke-multi-colored-text.jpg");

    // Text with stroke
    $img->load('butterfly.jpg')->text('Butterfly', __DIR__.'/delicious.ttf', 32, '#FFFFFF', 'bottom', 0, -20, '#000', 2)->save('processed/butterfly-text-with-stroke.jpg');

    // Text with multiple colored stroke
    $img->load('butterfly.jpg')->text('Butterfly', __DIR__.'/delicious.ttf', 32, '#000', 'bottom', 0, -20, ['#F00', '#FF7F00', '#FF0', '#0F0', '#0FF', '#00F'], 2, null, 3)->save("processed/butterfly-text-with-stroke-multi-colored-stroke.jpg");

    // Right align text
    $img->load('butterfly.jpg')->text('Lorem Ipsum', __DIR__.'/delicious.ttf', 32, '#FFFFFF', 'top right', 0, 0, null, null, 'right')->save('processed/butterfly-right-align-text.jpg');

    // Resizing GIFs with transparency
    $img->load('basketball.gif')->resize(50, 50)->save('processed/basketball-resize.gif');

    // Manipulate base64 gif string and save as png (requires PHP 5.4+)
    if( version_compare(PHP_VERSION, '5.4.0') >= 0 ) {
        $base64 = 'data:image/gif;base64,R0lGODlhEAAQAOZeAHBwcKCgoOraIvDw8Mu9Hi4rBvPFJvTKJpyRF/bXJfPAJ/jeJU5IC0BAQOq0J+ewKPnmJffZJVc6FvPCJruuGz46CYyDFPXPJpCQkH10EqugGSYaC+GoKAAAALCwsNicKUlGQvbUJtGaKWBgYODg4LWBJDUnF/jhJa9+JXFgSl4/GUxHQjwpEdadKUxDOEM9NmNdVcaPJ9rLIPbRJtifKUEwGuSsKNmhKV1XDXdPG6p2JXhRG1U5FiMdFlpWU76DJ8DAwMuVKtDQ0GlFFrR8JEQtELF8KO24J3VNGT0zKG1lEIxgHdugKXBNGykbCzw3Mt6kKNOcKfXMJt6mKG5LG2JAFTgwJW5fSlNNRenSI/THJo5iIPC8J/rpJf///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAAF4ALAAAAAAQABAAAAfZgF6CXiQAIBsbSSNAg40YTlstDw8cMVUrA40jKjcKWiEhMwZHRCYkggE8UxMLXa6uCVw/T14DPVETWQwarhoWXRFcOwEBTVwJCB0VrhUdFF0HNC8AKAoQAhkErggMsA5FDSIHr+SvCwoS4QcUAuVdBBYR6AAlBhYZ5QIFBBcOLB45uJwogOAVAYIQJgSB4cXKhwMCcBRQwqCCNilcqATw4mEIhwtdBFCQ0QXCBS5GQAwCgISJgQgLFiQw4ECHi0yDAtRY8sHGAyglJPjA2WgAhgZXUmABIKRRIAA7';
        $img->load_base64($base64)->resize(32,32)->save('processed/smiley-base64.png');
    }

    echo '<span style="color: green;">All processed images are saved in /example/processed</span>';

} catch (Exception $e) {
    echo '<span style="color: red;">'.$e->getMessage().'</span>';
}
