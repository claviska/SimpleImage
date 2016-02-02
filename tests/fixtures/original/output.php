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

try {
    // Flip the image and output it directly to the browser
    $img = new Image('butterfly.jpg');
    $img->flip('x')->output('png');

    echo 'ok';
    die(0);

} catch (Exception $e) {
    echo $e->getMessage();
    die(1);
}
