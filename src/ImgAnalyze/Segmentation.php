<?php

namespace Miloshavlicek\ImgAnalyzer;

use Nette\SmartObject;

/**
 * Class ChangeAnalyzer
 *
 * @package Miloshavlicek\ImgAnalyzer
 * @property Image $image
 * @property int $rows
 * @property int $cols
 * @property-read array $segments
 * @property-read array $segmentsAll
 */
class Segmentation
{

    /** @var Image */
    private $image;

    /** @var int */
    private $rows = 1;

    /** @var int */
    private $cols = 1;

    /** @var array */
    private $segments = [[]];

    use SmartObject;

    /**
     * @return array
     */
    public function getSegments()
    {
        return $this->segments;
    }

    /**
     * Return one dimensional array
     *
     * @return array
     */
    public function getSegmentsAll()
    {
        $out = [];

        for ($i = 0; $i < count($this->segments); $i++) {
            for ($j = 0; $j < count($this->segments[$i]); $j++) {
                $out[] = $this->segments[$i][$j];
            }
        }

        return $out;
    }

    /**
     * @return callable
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @return int
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * @param int $rows
     */
    public function setRows($rows)
    {
        $this->rows = $rows;
    }

    /**
     * @return int
     */
    public function getCols()
    {
        return $this->cols;
    }

    /**
     * @param int $cols
     */
    public function setCols($cols)
    {
        $this->cols = $cols;
    }

    /**
     * @param Image $image
     */
    public function setImage(Image $image)
    {
        $this->image = $image;
    }

    public function cut()
    {
        if ($this->rows < 2 && $this->cols < 2) {
            $this->segments[0][0] = $this->image;
            return;
        }

        $imageWidth = $this->image->width;
        $imageHeight = $this->image->height;
        $imageResource = $this->image->resource;

        $segmentHeight = floor($imageHeight / $this->rows);
        $segmentWidth = floor($imageWidth / $this->cols);
        $segmentHeightResidue = $imageHeight % $this->rows;
        $segmentWidthResidue = $imageWidth % $this->cols;

        for ($i = 0; $i < $this->rows; $i++) {
            for ($j = 0; $j < $this->cols; $j++) {
                $segmentExtraWidth = ($j === $this->cols - 1 ? $segmentWidthResidue : 0);
                $segmentExtraHeight = ($i === $this->rows - 1 ? $segmentHeightResidue : 0);

                $outImg = new Image();
                $outImg->setImageFromResource(
                    $imageResource,
                    [
                        $j * $segmentWidth,
                        $i * $segmentHeight,
                        ($j + 1) * $segmentWidth + $segmentExtraWidth,
                        ($i + 1) * $segmentHeight + $segmentExtraHeight
                    ],
                    Image::CROP_PIX
                );

                $this->segments[$i][$j] = $outImg;
            }
        }
    }
}
