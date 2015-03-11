<?php
namespace   abeautifulsite;
use         Exception;

require '../src/abeautifulsite/SimpleImage.php';

try {
    // Flip the image and output it directly to the browser
    $img = new SimpleImage('butterfly.jpg');
    $img->flip('x')->output('png');
} catch(Exception $e) {
    echo '<span style="color: red;">' . $e->getMessage() . '</span>';
}