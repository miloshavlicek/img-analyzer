<?php

namespace Miloshavlicek\ImgAnalyzer;

use Nette\SmartObject;

/**
 * Class Histogram
 *
 * @package Miloshavlicek\ImgAnalyzer
 * @property Image $image
 */
class Histogram
{

    /** @var Image */
    private $image;

    /** @var bool */
    private $analyzed = false;

    /** @var array */
    private $histogram = [];

    use SmartObject;

    /**
     * @return Image
     */
    public function getImage(): Image
    {
        return $this->image;
    }

    /**
     * @param float $min 0 - 100 percents
     * @param float $max 0 - 100 percents
     * @return float percents 0 - 1
     */
    public function getPercentageByLimits(float $min, float $max = 100): float
    {
        !$this->analyzed && $this->analyze();

        $counterAll = 0;

        $counterLimitBottom = $min / 100 * 255;
        $counterLimitTop = $max / 100 * 255;

        $counter = 0;

        for ($i=0; $i<255; $i++) {
            if ($i >= $counterLimitBottom && $i <= $counterLimitTop) {
                $counter += $this->histogram[$i];
            }
            $counterAll += $this->histogram[$i];
        }

        return $counterAll > 0 ? $counter / $counterAll : 0;
    }

    /**
     * @return bool
     */
    public function isDark(): bool
    {
        return $this->getPercentageByLimits(0, 50);
    }

    /**
     * @param Image $image
     */
    public function setImage(Image $image)
    {
        $this->image = $image;
        $this->analyzed = false;
    }

    /**
     * @param bool $require
     */
    public function analyze(bool $require = false): void
    {
        if (!$require && $this->analyzed) {
            return;
        }

        $x = $this->image->width;
        $y = $this->image->height;
        $n = $this->image->pixelsCount;
        $resource = $this->image->resource;

        for ($i=0; $i<$x; $i++) {
            for ($j=0; $j<$y; $j++) {
                // get the rgb value for current pixel
                $rgb = ImageColorAt($resource, $i, $j);

                // extract each value for r, g, b
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                // get the Value from the RGB value
                $V = round(($r + $g + $b) / 3);

                // add the point to the histogram
                empty($this->histogram[$V]) && $this->histogram[$V] = 0;
                $this->histogram[$V] += $V / $n;
            }
        }

        for ($i=0; $i<255; $i++) {
            if (empty($this->histogram[$i])) {
                $this->histogram[$i] = 0;
            }
        }

        ksort($this->histogram);

        $this->analyzed = true;
    }

    private function getHistogramMax(): float
    {
        $max = 0;
        for ($i=0; $i<255; $i++) {
            if (!empty($this->histogram[$i]) && $this->histogram[$i] > $max) {
                $max = $this->histogram[$i];
            }
        }
        return $max;
    }

    /**
     * @param int $maxHeight
     * @param int $barWidth
     * @return string
     */
    public function generateHtml($maxHeight = 300, $barWidth = 2): string
    {
        !$this->analyzed && $this->analyze();

        $pixelSrc = 'data:image/gif;base64,R0lGODlhAQABAIAAAAUEBAAAACwAAAAAAQABAAACAkQBADs=';

        $html = "<div style='width: " . (256 * $barWidth) . "px; border: 1px solid'>";

        $counterAll = 0;

        $histogramMax = $this->getHistogramMax();

        for ($i=0; $i<255; $i++) {
            $val = !empty($this->histogram[$i]) ? $this->histogram[$i] : 0;
            $counterAll += $val;

            $h = ($val / $histogramMax) * $maxHeight;

            $html .= sprintf('<img src="%s" width="%d" height="%d" border="0">', $pixelSrc, $barWidth, $h);
        }
        $html .= "</div>";

        return $html;
    }
}
