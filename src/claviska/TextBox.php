<?php

namespace claviska;

trait TextBox {

    /**
    * Adds text with a line break to the image.
    *
    * @param string $text The desired text.
    * @param array $options
    *    An array of options.
    *       - fontFile* (string) - The TrueType (or compatible) font file to use.
    *       - size (integer) - The size of the font in pixels (default 12).
    *       - color (string|array) - The text color (default black).
    *       - anchor (string) - The anchor point: 'center', 'top', 'bottom', 'left', 'right', 'top left', 'top right', 'bottom left', 'bottom right' (default 'center').
    *       - xOffset (integer) - The horizontal offset in pixels (default 0).
    *       - yOffset (integer) - The vertical offset in pixels (default 0).
    *       - shadow (array) - Text shadow params.
    *          - x* (integer) - Horizontal offset in pixels.
    *          - y* (integer) - Vertical offset in pixels.
    *          - color* (string|array) - The text shadow color.
    *       - $calcuateOffsetFromEdge (bool) - Calculate Offset referring to the edges of the image (default true).
    *       - width (int) - Width of text box (Default image width).
    *       - justify (string) - The justify: 'left', 'right', 'center', 'justify' (default 'left').
    *       - $leading (float) - Increase/decrease spacing between lines of text (default 0).
    *       - $opacity (float) - The opacity level of the text 0-1 (default 1).
    * @throws \Exception
    * @return \claviska\SimpleImage
    */
    public function textBox($text, $options) {
        // default width of image
        $maxWidth = $this->getWidth();
        // Default options
        $options = array_merge([
            'fontFile' => null,
            'size' => 12,
            'color' => 'black',
            'anchor' => 'center',
            'xOffset' => 0,
            'yOffset' => 0,
            'shadow' => null,
            'calcuateOffsetFromEdge' => true,
            'width' => $maxWidth,
            'justify' => 'left',
            'leading' => 0,
            'opacity' => 1,
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
        $maxWidth = $options['width'];
        $leading = $options['leading'];
        $leading = self::keepWithin($leading, ($fontSizePx * -1), $leading);
        $opacity = $options['opacity'];
        
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

        list($lines, $isLastLine, $lastLineHeight) = self::textSeparateLines($text, $fontFile, $fontSize, $maxWidth);

        $maxHeight = (count($lines) - 1) * ($fontSizePx * 1.2 + $leading) + $lastLineHeight;

        $imageText = new SimpleImage();
        $imageText->fromNew($maxWidth, $maxHeight);

        // FOR CENTER, LEFT, RIGHT
        if ($justify <> 'justify'):
            foreach ($lines as $key => $line):
                if( $justify == 'top' ) $line = trim($line); // If is justify = 'center'
                $imageText->text($line, array(
                    'fontFile' => $fontFile,
                    'size' => $fontSizePx,
                    'color' => $color,
                    'anchor' => $justify,
                    'xOffset' => 0,
                    'yOffset' => $key * ($fontSizePx * 1.2 + $leading),
                    'shadow' => $shadow,
                    'calcuateOffsetFromEdge' => true,
                    )
                );
            endforeach;

        // FOR JUSTIFY
        else:
            foreach ($lines as $keyLine => $line):
                // Check if there are spaces at the beginning of the sentence
                $spaces = 0;
                if (preg_match("/^\s+/", $line, $match)):
                    // Count spaces
                    $spaces = strlen($match[0]); 
                    $line = ltrim($line);
                endif;

                // Separate words
                $words = preg_split("/\s+/", $line); 
                // Include spaces with the first word
                $words[0] = str_repeat(" ", $spaces) . $words[0];

                // Calculates the space occupied by all words
                $wordsSize = array();
                foreach ($words as $key => $word):
                    $wordBox = imagettfbbox($fontSize, 0, $fontFile, $word);
                    $wordWidth = abs($wordBox[4] - $wordBox[0]);
                    $wordsSize[$key] = $wordWidth;
                endforeach;
                $wordsSizeTotal = array_sum($wordsSize);

                // Calculates the required space between words
                $countWords = count($words);
                $wordSpacing = 0;
                if ($countWords > 1):
                    $wordSpacing = ($maxWidth - $wordsSizeTotal) / ($countWords - 1);
                    $wordSpacing = round($wordSpacing, 3);
                endif;

                $xOffsetJustify = 0;
                foreach ($words as $key => $word):
                    if ($isLastLine[$keyLine] == true):
                        if ($key < (count($words) - 1)) continue;
                        $word = $line;
                    endif;
                    $imageText->text($word, array(
                        'fontFile' => $fontFile,
                        'size' => $fontSizePx,
                        'color' => $color,
                        'anchor' => 'top left',
                        'xOffset' => $xOffsetJustify,
                        'yOffset' => $keyLine * ($fontSizePx * 1.2 + $leading),
                        'shadow' => $shadow,
                        'calcuateOffsetFromEdge' => true,
                        )
                    );
                    // Calculate offSet for next word
                    $xOffsetJustify += $wordsSize[$key] + $wordSpacing;
                endforeach;
            endforeach;

        endif;

        $this->overlay($imageText, $anchor, $opacity, $xOffset, $yOffset, $calcuateOffsetFromEdge);

        return $this;
    }

    /**
    * Receives a text and breaks into LINES.
    *
    * @param integer $text  
    * @param string $fontFile 
    * @param int $fontSize 
    * @param int $maxWidth 
    * @return array
    */
    private function textSeparateLines($text, $fontFile, $fontSize, $maxWidth) {
        $words = self::textSeparateWords($text);
        $countWords = count($words) - 1;
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
            $lineBox = imagettfbbox($fontSize, 0, $fontFile, $lines[$lineKey] . $word);
            if (abs($lineBox[4] - $lineBox[0]) < $maxWidth):
                $lines[$lineKey] .= $word . ' ';
            else :
                $lineKey++;
                $lines[$lineKey] = $word . ' ';
            endif;
        endfor;
        $isLastLine[$lineKey] = true;
        // Exclude space of right
        $lines = array_map('rtrim', $lines);
        // Calculate height of last line
        $boxFull = imagettfbbox($fontSize, 0, $fontFile, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890');
        $lineBox = imagettfbbox($fontSize, 0, $fontFile, $lines[$lineKey]);
        // Height of last line = ascender of $boxFull + descender of $lineBox 
        $lastLineHeight = abs($lineBox[1]) + abs($boxFull[5]);

        return array($lines, $isLastLine, $lastLineHeight);
    }

    /**
    * Receives a text and breaks into WORD / SPACE / NEW LINE.
    *
    * @param integer $text  
    * @return array
    */
    private function textSeparateWords($text) {
        // Normalizes line break 
        $text = preg_replace('/(\r\n|\n|\r)/', PHP_EOL, $text);
        $text = explode(PHP_EOL, $text);
        $newText = array();
        foreach ($text as $key => $line):
                $newText = array_merge($newText, explode(' ', $line), [PHP_EOL]);
        endforeach;

        return $newText;
    }
} // End trait
