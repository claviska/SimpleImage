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

$dir = realpath(__DIR__ . '/../../../');

require_once $dir . '/vendor/autoload.php';

$resultDir = realpath($dir . '/tests/fixtures/result');

try {
    // Create an image from scratch
    $img = new Image(null, 500, 200, '#FFCC00');
    $img->text('Dynamically Created Image', 'delicious.ttf');
    $img->save($resultDir . '/created-image.png');

    // If you use create function instead of loading image
    // you have to define output extension
    $img->output('png');

    echo 'ok';
    die(0);

} catch (Exception $e) {
    echo $e->getMessage();
    die(1);
}
