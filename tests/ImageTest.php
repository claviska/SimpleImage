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

use JBZoo\Image\Exception;
use JBZoo\Image\Image;

/**
 * Class ImageTest
 * @package JBZoo\PHPUnit
 */
class ImageTest extends PHPUnit
{
    protected $_class = '\JBZoo\Image\Image';

    public function testCreateInstance()
    {
        $img = new Image();
        isClass($this->_class, $img);
    }

    public function testOpen()
    {
        $original = Helper::getOrig('butterfly.jpg');

        $img = new Image($original);
        isClass($this->_class, $img->loadFile($original));
    }

    /**
     * @expectedException \JBZoo\Image\Exception
     */
    public function testOpenUndefined()
    {
        $img = new Image();
        $img->loadFile('undefined.jpg');
    }

    public function testCleanup()
    {
        $original = Helper::getOrig('butterfly.jpg');

        $img = new Image($original);
        isClass($this->_class, $img->loadFile($original));

        $img->cleanup();
        isCount(1, array_filter($img->getInfo()));
    }

    public function testGetInfoJpeg()
    {
        $original = Helper::getOrig('butterfly.jpg');

        $img  = new Image($original);
        $info = $img->getInfo();

        is(640, $info['width']);
        is(478, $info['height']);
        is('image/jpeg', $info['mime']);
        is('landscape', $info['orient']);
        isTrue(is_array($info['exif']));
        isTrue($img->isJpeg());
    }

    public function testGetInfoPng()
    {
        $original = Helper::getOrig('butterfly.png');

        $img  = new Image($original);
        $info = $img->getInfo();

        is(640, $info['width']);
        is(478, $info['height']);
        is('image/png', $info['mime']);
        is('landscape', $info['orient']);
        isEmpty($info['exif']);
        isTrue($img->isPng());
    }

    public function testGetInfoGif()
    {
        $original = Helper::getOrig('butterfly.gif');

        $img  = new Image($original);
        $info = $img->getInfo();

        is(478, $info['width']);
        is(640, $info['height']);
        is('image/gif', $info['mime']);
        is('portrait', $info['orient']);
        isEmpty($info['exif']);

        isTrue($img->isGif());
    }

    public function testOrientation()
    {
        $img = new Image(Helper::getOrig('butterfly.gif'));
        isTrue($img->isPortrait());

        $img = new Image(Helper::getOrig('butterfly.jpg'));
        isTrue($img->isLandscape());

        $img = new Image(Helper::getOrig('basketball.gif'));
        isTrue($img->isSquare());
    }

    public function testSave()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = Helper::getActual(__FUNCTION__ . '.jpg');
        $original = Helper::getOrig('butterfly.jpg');

        if (copy($original, $actual)) {
            $img  = new Image($actual);
            $info = $img->save(1)
                ->getInfo();

            is(1, $info['quality']);
            is($actual, $info['filename']);
            //isNotEmpty($info['exif']);
            Helper::isFileEq($actual, $excepted);

        } else {
            isTrue(false, 'Can\'t copy original file!');
        }
    }

    public function testConvertToGif()
    {
        $original = Helper::getOrig('butterfly.jpg');
        $actual   = Helper::getActual(__FUNCTION__ . '.gif');
        $excepted = Helper::getExpected(__FUNCTION__ . '.gif');

        $img = new Image();
        $img->loadFile($original)
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testConvertToJpg()
    {
        $original   = Helper::getOrig('butterfly.jpg');
        $excepted   = Helper::getExpected(__FUNCTION__ . '.jpg');
        $actualJpg  = Helper::getActual(__FUNCTION__ . '.jpg');
        $actualJpeg = Helper::getActual(__FUNCTION__ . '.jpeg');

        $img = new Image();
        $img->loadFile($original)
            ->saveAs($actualJpg);

        Helper::isFileEq($actualJpg, $excepted);

        $img = new Image();
        $img->loadFile($original)->saveAs($actualJpeg)->setQuality(100);
        Helper::isFileEq($actualJpeg, $excepted);
    }

    public function testConvertToPng()
    {
        $original = Helper::getOrig('butterfly.jpg');
        $excepted = Helper::getExpected(__FUNCTION__ . '.png');
        $actual   = Helper::getActual(__FUNCTION__ . '.png');

        $img = new Image();
        $img->loadFile($original)
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testConvertToUndefindFormat()
    {
        $original = Helper::getOrig('butterfly.jpg');
        $actual   = Helper::getActual(__FUNCTION__ . '.qwerty');

        $img = new Image();
        $img->loadFile($original)
            ->saveAs($actual);
    }

    /**
     * @expectedException \JBZoo\Image\Exception
     */
    public function testConvertToUndefindPath()
    {
        $original = Helper::getOrig('butterfly.jpg');
        $actual   = Helper::getActual('qwerty/' . __FUNCTION__ . '.png');

        $img = new Image();
        $img->loadFile($original)
            ->saveAs($actual);
    }

    public function testCreateFromScratchOnlyWidth()
    {
        $actual   = Helper::getActual(__FUNCTION__ . '.png');
        $excepted = Helper::getExpected(__FUNCTION__ . '.png');

        $img = new Image();
        $img->create(200)
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testCreateFromScratchWidthAndHeight()
    {
        $actual   = Helper::getActual(__FUNCTION__ . '.png');
        $excepted = Helper::getExpected(__FUNCTION__ . '.png');

        $img = new Image();
        $img->create(200, 100)
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testCreateFromScratchFull()
    {
        $actual   = Helper::getActual(__FUNCTION__ . '.png');
        $excepted = Helper::getExpected(__FUNCTION__ . '.png');

        $img = new Image();
        $img->create(200, 100, array(0, 136, 204, 64))
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testGetBase64()
    {
        $original = Helper::getOrig('smile.gif');

        $img = new Image($original);
        isContain('data:image/gif;base64,R0lGODlhEAAQAMYAAHB', $img->getBase64());
        isContain('data:image/gif;base64,R0lGODlhEAAQAMYAAHB', $img->getBase64(null));
        isContain('data:image/gif;base64,R0lGODlhEAAQAMYAAHB', $img->getBase64('gif'));
        isContain('data:image/png;base64,iVBORw0KGgoAAAANSUh', $img->getBase64('png'));
        isContain('data:image/jpeg;base64,/9j/4AAQSkZJRgABAQ', $img->getBase64('jpeg'));
        isContain('data:image/jpeg;base64,/9j/4AAQSkZJRgABAQ', $img->getBase64('jpg'));

        isContain('R0lGODlhEAAQAMYAAHB', $img->getBase64(null, null, false));
        isContain('R0lGODlhEAAQAMYAAHB', $img->getBase64('gif', null, false));
        isContain('iVBORw0KGgoAAAANSUh', $img->getBase64('png', null, false));
        isContain('/9j/4AAQSkZJRgABAQ', $img->getBase64('jpeg', null, false));
        isContain('/9j/4AAQSkZJRgABAQ', $img->getBase64('jpg', null, false));
    }

    public function testGetBinary()
    {
        $original = Helper::getOrig('smile.gif');

        $img = new Image($original);

        isContain('47494638396110001000c60000707070a0a0a0eada22f0f0f0cbbd1e2e2b06f3', bin2hex($img->getBinary()));
        isContain('47494638396110001000c60000707070a0a0a0eada22f0f0f0cbbd1e2e2b06f3', bin2hex($img->getBinary(null)));
        isContain('47494638396110001000c60000707070a0a0a0eada22f0f0f0cbbd1e2e2b06f3', bin2hex($img->getBinary('gif')));
        isContain('89504e470d0a1a0a0000000d4948445200000010000000100803000000282d0f', bin2hex($img->getBinary('png')));
        isContain('ffd8ffe000104a46494600010100000100010000fffe003a43524541544f523a', bin2hex($img->getBinary('jpeg')));
        isContain('ffd8ffe000104a46494600010100000100010000fffe003a43524541544f523a', bin2hex($img->getBinary('jpg')));
    }

    /**
     * @expectedException \JBZoo\Image\Exception
     */
    public function testSaveUndefined()
    {
        $img = new Image();
        $img->save();
    }

    /**
     * @expectedException \JBZoo\Image\Exception
     */
    public function testToBase64Undefined()
    {
        $img = new Image();
        $img->getBase64();
    }

    /**
     * @expectedException \JBZoo\Image\Exception
     */
    public function testSaveAsUndefined()
    {
        $img = new Image();
        $img->saveAs('');
    }

    public function testGetPath()
    {
        $_SERVER['DOCUMENT_ROOT'] = __DIR__;
        $_SERVER['HTTP_HOST']     = 'test.dev';
        $_SERVER['SERVER_PORT']   = 80;
        $_SERVER['REQUEST_URI']   = '/test.php?foo=bar';
        $_SERVER['QUERY_STRING']  = 'foo=bar';
        $_SERVER['PHP_SELF']      = '/test.php';

        $original = Helper::getOrig('butterfly.jpg');

        $img = new Image($original);
        isSame('resources/butterfly.jpg', $img->getPath());
        isSame('http://test.dev/resources/butterfly.jpg', $img->getUrl());
    }

    /**
     * @expectedException \JBZoo\Image\Exception
     */
    public function testGetPathUndefined()
    {
        $_SERVER['DOCUMENT_ROOT'] = __DIR__;
        $_SERVER['HTTP_HOST']     = 'test.dev';
        $_SERVER['SERVER_PORT']   = 80;
        $_SERVER['REQUEST_URI']   = '/test.php?foo=bar';
        $_SERVER['QUERY_STRING']  = 'foo=bar';
        $_SERVER['PHP_SELF']      = '/test.php';

        $img = new Image();
        isSame('', $img->getUrl());
    }

    /**
     * @requires PHP 5.4
     */
    public function testOpenAsString()
    {
        $imgStr = 'R0lGODlhEAAQAOZeAHBwcKCgoOraIvDw8Mu9Hi4rBvPFJvTKJpyRF/bXJfPAJ/jeJU5IC0B'
            . 'AQOq0J+ewKPnmJffZJVc6FvPCJruuGz46CYyDFPXPJpCQkH10EqugGSYaC+GoKAAAALCwsNicKUlGQvbUJtGaKWBgYODg4LWB'
            . 'JDUnF/jhJa9+JXFgSl4/GUxHQjwpEdadKUxDOEM9NmNdVcaPJ9rLIPbRJtifKUEwGuSsKNmhKV1XDXdPG6p2JXhRG1U5FiMdF'
            . 'lpWU76DJ8DAwMuVKtDQ0GlFFrR8JEQtELF8KO24J3VNGT0zKG1lEIxgHdugKXBNGykbCzw3Mt6kKNOcKfXMJt6mKG5LG2JAFT'
            . 'gwJW5fSlNNRenSI/THJo5iIPC8J/rpJf///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'
            . 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAAF4ALAAAAAAQABAAAAfZ'
            . 'gF6CXiQAIBsbSSNAg40YTlstDw8cMVUrA40jKjcKWiEhMwZHRCYkggE8UxMLXa6uCVw/T14DPVETWQwarhoWXRFcOwEBTVwJC'
            . 'B0VrhUdFF0HNC8AKAoQAhkErggMsA5FDSIHr+SvCwoS4QcUAuVdBBYR6AAlBhYZ5QIFBBcOLB45uJwogOAVAYIQJgSB4cXKhw'
            . 'MCcBRQwqCCNilcqATw4mEIhwtdBFCQ0QXCBS5GQAwCgISJgQgLFiQw4ECHi0yDAtRY8sHGAyglJPjA2WgAhgZXUmABIKRRIAA7';

        $base64Gif = 'data:image/gif;base64,' . $imgStr;
        $base64Jpeg = 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEASABIAAD/4QBYRXhpZgAATU0AKgAAAAgAAgESAAMAAAABAAEAAIdpA'
            . 'AQAAAABAAAAJgAAAAAAA6ABAAMAAAABAAEAAKACAAQAAAABAAAAEKADAAQAAAABAAAAEAAAAAD/7QA4UGhvdG9zaG9wIDMuMA'
            . 'A4QklNBAQAAAAAAAA4QklNBCUAAAAAABDUHYzZjwCyBOmACZjs+EJ+/+IMWElDQ19QUk9GSUxFAAEBAAAMSExpbm8CEAAAbW50'
            . 'clJHQiBYWVogB84AAgAJAAYAMQAAYWNzcE1TRlQAAAAASUVDIHNSR0IAAAAAAAAAAAAAAAAAAPbWAAEAAAAA0y1IUCAgAAAAAA'
            . 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAARY3BydAAAAVAAAAAzZGVzYwAAAYQAAABsd3RwdAAA'
            . 'AfAAAAAUYmtwdAAAAgQAAAAUclhZWgAAAhgAAAAUZ1hZWgAAAiwAAAAUYlhZWgAAAkAAAAAUZG1uZAAAAlQAAABwZG1kZAAAAs'
            . 'QAAACIdnVlZAAAA0wAAACGdmlldwAAA9QAAAAkbHVtaQAAA/gAAAAUbWVhcwAABAwAAAAkdGVjaAAABDAAAAAMclRSQwAABDwA'
            . 'AAgMZ1RSQwAABDwAAAgMYlRSQwAABDwAAAgMdGV4dAAAAABDb3B5cmlnaHQgKGMpIDE5OTggSGV3bGV0dC1QYWNrYXJkIENvbX'
            . 'BhbnkAAGRlc2MAAAAAAAAAEnNSR0IgSUVDNjE5NjYtMi4xAAAAAAAAAAAAAAASc1JHQiBJRUM2MTk2Ni0yLjEAAAAAAAAAAAAA'
            . 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAFhZWiAAAAAAAADzUQABAAAAARbMWFlaIAAAAAAAAAAAAA'
            . 'AAAAAAAABYWVogAAAAAAAAb6IAADj1AAADkFhZWiAAAAAAAABimQAAt4UAABjaWFlaIAAAAAAAACSgAAAPhAAAts9kZXNjAAAA'
            . 'AAAAABZJRUMgaHR0cDovL3d3dy5pZWMuY2gAAAAAAAAAAAAAABZJRUMgaHR0cDovL3d3dy5pZWMuY2gAAAAAAAAAAAAAAAAAAA'
            . 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAZGVzYwAAAAAAAAAuSUVDIDYxOTY2LTIuMSBEZWZhdWx0IFJHQiBjb2xv'
            . 'dXIgc3BhY2UgLSBzUkdCAAAAAAAAAAAAAAAuSUVDIDYxOTY2LTIuMSBEZWZhdWx0IFJHQiBjb2xvdXIgc3BhY2UgLSBzUkdCAA'
            . 'AAAAAAAAAAAAAAAAAAAAAAAAAAAGRlc2MAAAAAAAAALFJlZmVyZW5jZSBWaWV3aW5nIENvbmRpdGlvbiBpbiBJRUM2MTk2Ni0y'
            . 'LjEAAAAAAAAAAAAAACxSZWZlcmVuY2UgVmlld2luZyBDb25kaXRpb24gaW4gSUVDNjE5NjYtMi4xAAAAAAAAAAAAAAAAAAAAAA'
            . 'AAAAAAAAAAAAB2aWV3AAAAAAATpP4AFF8uABDPFAAD7cwABBMLAANcngAAAAFYWVogAAAAAABMCVYAUAAAAFcf521lYXMAAAAA'
            . 'AAAAAQAAAAAAAAAAAAAAAAAAAAAAAAKPAAAAAnNpZyAAAAAAQ1JUIGN1cnYAAAAAAAAEAAAAAAUACgAPABQAGQAeACMAKAAtAD'
            . 'IANwA7AEAARQBKAE8AVABZAF4AYwBoAG0AcgB3AHwAgQCGAIsAkACVAJoAnwCkAKkArgCyALcAvADBAMYAywDQANUA2wDgAOUA'
            . '6wDwAPYA+wEBAQcBDQETARkBHwElASsBMgE4AT4BRQFMAVIBWQFgAWcBbgF1AXwBgwGLAZIBmgGhAakBsQG5AcEByQHRAdkB4Q'
            . 'HpAfIB+gIDAgwCFAIdAiYCLwI4AkECSwJUAl0CZwJxAnoChAKOApgCogKsArYCwQLLAtUC4ALrAvUDAAMLAxYDIQMtAzgDQwNP'
            . 'A1oDZgNyA34DigOWA6IDrgO6A8cD0wPgA+wD+QQGBBMEIAQtBDsESARVBGMEcQR+BIwEmgSoBLYExATTBOEE8AT+BQ0FHAUrBT'
            . 'oFSQVYBWcFdwWGBZYFpgW1BcUF1QXlBfYGBgYWBicGNwZIBlkGagZ7BowGnQavBsAG0QbjBvUHBwcZBysHPQdPB2EHdAeGB5kH'
            . 'rAe/B9IH5Qf4CAsIHwgyCEYIWghuCIIIlgiqCL4I0gjnCPsJEAklCToJTwlkCXkJjwmkCboJzwnlCfsKEQonCj0KVApqCoEKmA'
            . 'quCsUK3ArzCwsLIgs5C1ELaQuAC5gLsAvIC+EL+QwSDCoMQwxcDHUMjgynDMAM2QzzDQ0NJg1ADVoNdA2ODakNww3eDfgOEw4u'
            . 'DkkOZA5/DpsOtg7SDu4PCQ8lD0EPXg96D5YPsw/PD+wQCRAmEEMQYRB+EJsQuRDXEPURExExEU8RbRGMEaoRyRHoEgcSJhJFEm'
            . 'QShBKjEsMS4xMDEyMTQxNjE4MTpBPFE+UUBhQnFEkUahSLFK0UzhTwFRIVNBVWFXgVmxW9FeAWAxYmFkkWbBaPFrIW1hb6Fx0X'
            . 'QRdlF4kXrhfSF/cYGxhAGGUYihivGNUY+hkgGUUZaxmRGbcZ3RoEGioaURp3Gp4axRrsGxQbOxtjG4obshvaHAIcKhxSHHscox'
            . 'zMHPUdHh1HHXAdmR3DHeweFh5AHmoelB6+HukfEx8+H2kflB+/H+ogFSBBIGwgmCDEIPAhHCFIIXUhoSHOIfsiJyJVIoIiryLd'
            . 'IwojOCNmI5QjwiPwJB8kTSR8JKsk2iUJJTglaCWXJccl9yYnJlcmhya3JugnGCdJJ3onqyfcKA0oPyhxKKIo1CkGKTgpaymdKd'
            . 'AqAio1KmgqmyrPKwIrNitpK50r0SwFLDksbiyiLNctDC1BLXYtqy3hLhYuTC6CLrcu7i8kL1ovkS/HL/4wNTBsMKQw2zESMUox'
            . 'gjG6MfIyKjJjMpsy1DMNM0YzfzO4M/E0KzRlNJ402DUTNU01hzXCNf02NzZyNq426TckN2A3nDfXOBQ4UDiMOMg5BTlCOX85vD'
            . 'n5OjY6dDqyOu87LTtrO6o76DwnPGU8pDzjPSI9YT2hPeA+ID5gPqA+4D8hP2E/oj/iQCNAZECmQOdBKUFqQaxB7kIwQnJCtUL3'
            . 'QzpDfUPARANER0SKRM5FEkVVRZpF3kYiRmdGq0bwRzVHe0fASAVIS0iRSNdJHUljSalJ8Eo3Sn1KxEsMS1NLmkviTCpMcky6TQ'
            . 'JNSk2TTdxOJU5uTrdPAE9JT5NP3VAnUHFQu1EGUVBRm1HmUjFSfFLHUxNTX1OqU/ZUQlSPVNtVKFV1VcJWD1ZcVqlW91dEV5JX'
            . '4FgvWH1Yy1kaWWlZuFoHWlZaplr1W0VblVvlXDVchlzWXSddeF3JXhpebF69Xw9fYV+zYAVgV2CqYPxhT2GiYfViSWKcYvBjQ2'
            . 'OXY+tkQGSUZOllPWWSZedmPWaSZuhnPWeTZ+loP2iWaOxpQ2maafFqSGqfavdrT2una/9sV2yvbQhtYG25bhJua27Ebx5veG/R'
            . 'cCtwhnDgcTpxlXHwcktypnMBc11zuHQUdHB0zHUodYV14XY+dpt2+HdWd7N4EXhueMx5KnmJeed6RnqlewR7Y3vCfCF8gXzhfU'
            . 'F9oX4BfmJ+wn8jf4R/5YBHgKiBCoFrgc2CMIKSgvSDV4O6hB2EgITjhUeFq4YOhnKG14c7h5+IBIhpiM6JM4mZif6KZIrKizCL'
            . 'lov8jGOMyo0xjZiN/45mjs6PNo+ekAaQbpDWkT+RqJIRknqS45NNk7aUIJSKlPSVX5XJljSWn5cKl3WX4JhMmLiZJJmQmfyaaJ'
            . 'rVm0Kbr5wcnImc951kndKeQJ6unx2fi5/6oGmg2KFHobaiJqKWowajdqPmpFakx6U4pammGqaLpv2nbqfgqFKoxKk3qamqHKqP'
            . 'qwKrdavprFys0K1ErbiuLa6hrxavi7AAsHWw6rFgsdayS7LCszizrrQltJy1E7WKtgG2ebbwt2i34LhZuNG5SrnCuju6tbsuu6'
            . 'e8IbybvRW9j74KvoS+/796v/XAcMDswWfB48JfwtvDWMPUxFHEzsVLxcjGRsbDx0HHv8g9yLzJOsm5yjjKt8s2y7bMNcy1zTXN'
            . 'tc42zrbPN8+40DnQutE80b7SP9LB00TTxtRJ1MvVTtXR1lXW2Ndc1+DYZNjo2WzZ8dp22vvbgNwF3IrdEN2W3hzeot8p36/gNu'
            . 'C94UThzOJT4tvjY+Pr5HPk/OWE5g3mlucf56noMui86Ubp0Opb6uXrcOv77IbtEe2c7ijutO9A78zwWPDl8XLx//KM8xnzp/Q0'
            . '9ML1UPXe9m32+/eK+Bn4qPk4+cf6V/rn+3f8B/yY/Sn9uv5L/tz/bf///8AAEQgAEAAQAwEiAAIRAQMRAf/EAB8AAAEFAQEBAQ'
            . 'EBAAAAAAAAAAABAgMEBQYHCAkKC//EALUQAAIBAwMCBAMFBQQEAAABfQECAwAEEQUSITFBBhNRYQcicRQygZGhCCNCscEVUtHw'
            . 'JDNicoIJChYXGBkaJSYnKCkqNDU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6g4SFhoeIiYqSk5SVlpeYmZ'
            . 'qio6Slpqeoqaqys7S1tre4ubrCw8TFxsfIycrS09TV1tfY2drh4uPk5ebn6Onq8fLz9PX29/j5+v/EAB8BAAMBAQEBAQEBAQEA'
            . 'AAAAAAABAgMEBQYHCAkKC//EALURAAIBAgQEAwQHBQQEAAECdwABAgMRBAUhMQYSQVEHYXETIjKBCBRCkaGxwQkjM1LwFWJy0Q'
            . 'oWJDThJfEXGBkaJicoKSo1Njc4OTpDREVGR0hJSlNUVVZXWFlaY2RlZmdoaWpzdHV2d3h5eoKDhIWGh4iJipKTlJWWl5iZmqKj'
            . 'pKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uLj5OXm5+jp6vLz9PX29/j5+v/bAEMAAgICAgICAwICAwQDAwMEBQ'
            . 'QEBAQFBwUFBQUFBwgHBwcHBwcICAgICAgICAoKCgoKCgsLCwsLDQ0NDQ0NDQ0NDf/bAEMBAgICAwMDBgMDBg0JBwkNDQ0NDQ0N'
            . 'DQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDf/dAAQAAf/aAAwDAQACEQMRAD8A+p/Euq658cdb0n'
            . 'xl4u8P3/jDwl4g12XTdL0aCeNdJ0bRVEgj1O8t3YR3cs6osjs6u0Yl8uIgLiSTRr+++DcHiL4gfD3w7qfgvSfBOqzW+oaHJsTS'
            . 'fEej2qJLPc2tqh8uN2iZvs86okglQIzNH5kZk8ReG/E3wG8RjSn1NvDml2Mtxa6Hql/Zy33h3UdJuZfNtrS6MUsP2S+s8+TE5k'
            . 'QyDcwSVWCx1tN8N6z8bL/UfDei6rL4puvEUS6Xrev2trJZ+H9E0bcftMFn88im5kRm+XzpZ5JSjSGOCMCP+M8YuN3xu3Bz5/aa'
            . 'W9rycntOv/Lrk5NG77dOe9v0im8H9UW3suXX4f5fv5r/ADv5H//Z';

        $bin = base64_decode($imgStr, true);

        $actualClean      = Helper::getActual(__FUNCTION__ . '_clean.gif');
        $actualBase64Gif  = Helper::getActual(__FUNCTION__ . '_base64.gif');
        $actualBase64Jpeg = Helper::getActual(__FUNCTION__ . '_base64.jpg');
        $actualBin        = Helper::getActual(__FUNCTION__ . '_bin.gif');
        $excepted         = Helper::getExpected(__FUNCTION__ . '.gif');

        $img = new Image($imgStr);
        $img->saveAs($actualClean);
        Helper::isFileEq($actualClean, $excepted);

        $img = new Image($base64Gif);
        $img->saveAs($actualBase64Gif);
        Helper::isFileEq($actualBase64Gif, $excepted);

        $img = new Image($base64Gif, true);
        $img->saveAs($actualBase64Gif);
        Helper::isFileEq($actualBase64Gif, $excepted);

        $img = new Image($base64Jpeg, true);
        $img->saveAs($actualBase64Jpeg);
        Helper::isFileEq($actualBase64Jpeg, $excepted);

        $img = new Image($bin);
        $img->saveAs($actualBin);
        Helper::isFileEq($actualBin, $excepted);
    }

    public function testUnsupportedFormat()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.png');
        $actual   = Helper::getActual(__FUNCTION__ . '.tmp');
        $original = Helper::getOrig('1x1.tmp');

        if (copy($original, $actual)) {
            $img  = new Image($actual);
            $info = $img
                ->thumbnail(100, 200)
                ->save()
                ->getInfo();

            is('image/gif', $info['mime']);
            is(100, $info['width']);
            is(200, $info['height']);

            Helper::isFileEq($actual, $excepted);

        } else {
            fail('Can\'t copy original file!');
        }
    }

    public function testOpenImageResource()
    {
        $original = Helper::getOrig('butterfly.jpg');
        $actual   = Helper::getActual(__FUNCTION__ . '.jpg');
        $excepted = Helper::getExpected(__FUNCTION__ . '.jpg');

        $imgRes = imagecreatefromjpeg($original);

        $img = new Image($imgRes);
        $img->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    /**
     * @expectedException \JBZoo\Image\Exception
     */
    public function testLoadStringUndefined()
    {
        $img = new Image();
        $img->loadString('');
    }

    /**
     * @expectedException \JBZoo\Image\Exception
     */
    public function testLoadResourceUndefined()
    {
        $img = new Image();
        $img->loadResource('');
    }

    public function testPHP53_getimagesizefromstring()
    {
        try {
            $img = new Image();
            $img->loadString('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==');
        } catch (Exception $e) {
            isTrue(strpos(PHP_VERSION, '5.3') === 0);
        }
    }

    /**
     * @requires PHP 5.4
     */
    public function testInvalidImageString()
    {
        try {
            $img = new Image();
            $img->loadString('__INVALID__', true);
        } catch(Exception $e) {
            isTrue($e->getMessage() === 'Invalid image source.');
        }

        try {
            $img = new Image('__INVALID__', true);
        } catch(Exception $e) {
            isTrue($e->getMessage() === 'Invalid image source.');
        }
    }

    public function testGetWidthAndHeight()
    {
        $original = Helper::getOrig('butterfly.png');

        $img  = new Image($original);

        is(640, $img->getWidth());
        is(478, $img->getHeight());
    }
}
