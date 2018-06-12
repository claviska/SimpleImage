<?php
require '../src/claviska/SimpleImage.php';

// Ignore notices
error_reporting(E_ALL & ~E_NOTICE);

try {
  // Create a new SimpleImage object
  $image = new \claviska\SimpleImage();

  // Manipulate it
  $image
    ->fromFile('parrot.jpg')              // load parrot.jpg
    ->autoOrient()                        // adjust orientation based on exif data
    ->resizeProportionally(450)           // proportinoally resize to a square
    // ->resizeProportionally(300, 600, 'aqua|0.5')// proportinoally resize to fit inside a 250x400 box
    ->scale(0.5)
    ->overlay('flag.png', 'bottom right') // add a watermark image
    ->toScreen('image/png');                         // output to the screen

} catch(Exception $err) {
  // Handle errors
  echo $err->getMessage();
}
