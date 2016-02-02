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

require '../../autoload.php';

if (!is_dir('processed/')) {
    mkdir('processed/');
}

try {
    // Create an image from scratch
    $img = new SimpleImage(null, 500, 200, '#FFCC00');
    $img->text('Dynamically Created Image', 'delicious.ttf');
    $img->save('processed/created-image.png');
    // If you use create function instead of loading image
    // you have to define output extension
    $img->output('png');
} catch (\Exception $e) {
    echo '<span style="color: red;">' . $e->getMessage() . '</span>';
}