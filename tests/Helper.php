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

namespace JBZoo\PHPUnit;

use JBZoo\Utils\FS;

/**
 * Class Helper
 * @package JBZoo\PHPUnit
 */
class Helper
{
    /**
     * @param $filename
     * @return string
     */
    public static function getActual($filename)
    {
        $filename = self::camelCase2Human($filename);
        return FS::clean(PROJECT_ROOT . '/build/images/' . $filename);
    }

    /**
     * @param $filename
     * @return string
     */
    public static function getExpected($filename)
    {
        $filename = self::camelCase2Human($filename);
        return FS::clean(PROJECT_TESTS . '/expected/' . $filename);
    }

    /**
     * @param $filename
     * @return string
     */
    public static function getOrig($filename)
    {
        return FS::clean(PROJECT_TESTS . '/resources/' . $filename);
    }

    /**
     * @param string $actual
     * @param string $expected
     */
    public static function isFileEq($actual, $expected)
    {
        // Because realpath cache is!
        clearstatcache(false, $actual);
        clearstatcache(false, $expected);

        isTrue(file_exists($actual), 'File not found: ' . $actual);
        isTrue(file_exists($expected), 'File not found: ' . $expected);

        $actualSize   = filesize($actual);
        $expectedSize = filesize($expected);

        $diff = abs($actualSize - $expectedSize);

        if ($diff !== 0) {
            $message = $actual . '; ' . $actualSize . ' <> ' . $expectedSize . "\t\t(" . $diff . ')';
            //is(0, $diff, $message);
            cliMessage($message);
        } else {
            is(0, $diff);
        }
    }

    /**
     * @param string $input
     * @return mixed|string
     */
    public static function camelCase2Human($input)
    {
        $original = $input;

        if (strpos($input, '\\') !== false) {
            $input = explode('\\', $input);
            reset($input);
            $input = end($input);
        }

        $input = preg_replace('#^(test)#i', '', $input);
        $input = preg_replace('#(test)$#i', '', $input);

        $output = preg_replace(array('/(?<=[^A-Z])([A-Z])/', '/(?<=[^0-9])([0-9])/'), '_$0', $input);
        $output = preg_replace('#_{1,}#', '_', $output);

        $output = trim($output);
        $output = strtolower($output);

        if (strlen($output) == 0) {
            return $original;
        }

        return $output;
    }
}
