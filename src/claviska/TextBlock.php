<?php

namespace claviska;

trait TextBlock {

    /* --------------------------------------------------------------------------------- */
    /* --------------------------------------------------------------------------------- */
    public function textBlock($text, $options, &$boundary = null) {
        // default width of image
        $maxWidth = $this->getWidth();
        // Default options
        $options = array_merge([
            'fontFile' => null,
            'size' => 16,
            'color' => 'black',
            'anchor' => 'center',
            'xOffset' => 0,
            'yOffset' => 0,
            'shadow' => null,
            'calcuateOffsetFromEdge' => true,
            'opacity' => 1,
            'leading' => 0,
            'justify' => 'top left',
            'maxWidth' => $maxWidth,
        ], $options);

        // Extract and normalize options
        $fontFile = $options['fontFile'];
        $fontSize = $fontSizePx = $options['size'];
        $fontSize = ($fontSize / 96) * 72; // Convert px to pt (72pt per inch, 96px per inch)
        $color = $options['color'];
        $anchor = $options['anchor'];
        $xOffset = $options['xOffset'];
        $yOffset = $options['yOffset'];
        $shadow = $options['shadow'];
        $calcuateOffsetFromEdge = $options['calcuateOffsetFromEdge'];
        $angle = 0;
        $opacity = $options['opacity'];
        $leading = $options['leading'];

        $justify = $options['justify'];
        if ($justify == 'right'):
            $justify = 'top right';
        elseif ($justify == 'center'):
            $justify = 'top';
        elseif ($justify == 'justify'):
            $justify = 'justify';
        else :
            $justify = 'top left';
        endif;

        $maxWidth = $options['maxWidth'];

        list($lines, $isLastLine) = self::textSeparateLines($text, $fontFile, $fontSize, $maxWidth);

        $maxHeight = (count($lines) + 1) * ($fontSizePx + $leading) * 1.2;

        $imageText = new SimpleImage();
        $imageText->fromNew($maxWidth + 10, $maxHeight + 10);
        // $imageText->fromNew($maxWidth+10, $maxHeight+10, 'green|0.2');

        // FOR CENTER, LEFT, RIGHT
        if ($justify <> 'justify'):
            foreach ($lines as $key => $line):
                if( $justify == 'top' ) $line = trim($line); // If is justify = 'center'
                $imageText->text($line, array(
                    'fontFile' => $fontFile,
                    'size' => $fontSizePx,
                    'color' => $color,
                    'anchor' => $justify,
                    'xOffset' => 5,
                    'yOffset' => 5 + $key * ($fontSizePx + $leading) * 1.2,
                    'shadow' => $shadow,
                    'calcuateOffsetFromEdge' => true,
                ), $box);
                // $imageText->rectangle($box['x1'], $box['y1'], $box['x2'], $box['y2'], 'black|0.5', 1); // for developer test
            endforeach;

        // FOR JUSTIFY
        else:
            foreach ($lines as $keyLine => $line):
                // check if there are spaces at the beginning of the sentence
                $spaces = 0;
                if (preg_match("/^\s+/", $line, $match)):
                    $spaces = strlen($match[0]); // count spaces
                    $line = ltrim($line);
                endif;
                $words = preg_split("/\s+/", $line); // separate words
                // include spaces on first word
                $words[0] = str_repeat(" ", $spaces) . $words[0];

                $wordsSize = array();
                foreach ($words as $key => $word):
                    $wordBox = imagettfbbox($fontSize, 0, $fontFile, $word);
                    $wordWidth = abs($wordBox[4] - $wordBox[0]);
                    $wordsSize[$key] = $wordWidth;
                endforeach;
                $countWords = count($words);
                $wordSizeTotal = array_sum($wordsSize);
                $wordSpacing = 0;
                if ($countWords > 1):
                    $wordSpacing = ($maxWidth - $wordSizeTotal) / ($countWords - 1);
                    $wordSpacing = round($wordSpacing, 3);
                endif;

                $xOffsetJustify = 0;
                foreach ($words as $key => $word):
                    if ($isLastLine[$keyLine] == true):
                        if ($key < array_key_last($words)) continue;
                        $word = $line;
                    endif;
                    $imageText->text($word, array(
                        'fontFile' => $fontFile,
                        'size' => $fontSizePx,
                        'color' => $color,
                        'anchor' => 'top left',
                        'xOffset' => 5 + $xOffsetJustify,
                        'yOffset' => 5 + $keyLine * ($fontSizePx + $leading) * 1.2,
                        'shadow' => $shadow,
                        'calcuateOffsetFromEdge' => true,
                    ), $box);
                    // $imageText->rectangle($box['x1'], $box['y1'], $box['x2'], $box['y2'], 'black|0.5', 1); // for developer test
                    $xOffsetJustify += $wordsSize[$key] + $wordSpacing;
                endforeach;

            endforeach;

        endif;
        $imageText->image = imagecropauto($imageText->image, IMG_CROP_SIDES);
        $imageTextCanvas = new SimpleImage();
        $imageTextCanvas
            ->fromNew($maxWidth, $imageText->getHeight())
            ->overlay($imageText, 'top');
        $imageText = $imageTextCanvas;

        // $imageText->border('red|0.5'); // for developer test
        $this->overlay($imageText, $anchor, $opacity, $xOffset, $yOffset, $calcuateOffsetFromEdge);

        return $this;
    }


    /* --------------------------------------------------------------------------------- */
    // Recebe um texto e quebra em LINHAS, retorna um array 
    /* --------------------------------------------------------------------------------- */
    private function textSeparateLines($text, $fontFile, $fontSize, $maxWidth) {
        $words = self::textSeparateWords($text);
        $countWords = count($words);
        $lines[0] = '';
        $lineKey = 0;
        $isLastLine = [];
        for ($i = 0; $i < $countWords; $i++):
            $word = $words[$i];
            $isLastLine[$lineKey] = false;
            if ($word === PHP_EOL):
                $isLastLine[$lineKey] = true;
                $lineKey++;
                $lines[$lineKey] = '';
                continue;
            endif;
            $lineWidth = imagettfbbox($fontSize, 0, $fontFile, $lines[$lineKey] . $word);
            // var_dump($lineWidth);
            if (abs($lineWidth[4] - $lineWidth[0]) < $maxWidth):
                $lines[$lineKey] .= $word . ' ';
            else :
                $lineKey++;
                $lines[$lineKey] = $word . ' ';
            endif;
        endfor;
        $isLastLine[$lineKey] = true;
        // exclude space of right
        $lines = array_map('rtrim', $lines);

        return array($lines, $isLastLine);
    }


    /* --------------------------------------------------------------------------------- */
    // Recebe um texto e quebra em PALAVRA/ESPAÇO/NOVA LINHA, retorna um array 
    /* --------------------------------------------------------------------------------- */
    private function textSeparateWords($text) {
        $text = preg_replace('/(\r\n|\n|\r)/', PHP_EOL, $text);
        $text = explode(PHP_EOL, $text);
        $newText = array();
        foreach ($text as $key => $line):
            if ( $line === '' ):
                $newText = array_merge($newText, [PHP_EOL]);
            else:
                $newText = array_merge($newText, [PHP_EOL], explode(' ', $line));
            endif;
        endforeach;

        if ($newText[0] == PHP_EOL) array_shift($newText);
        return $newText;
    }
} // end trait