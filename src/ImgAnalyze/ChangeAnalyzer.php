<?php

namespace Miloshavlicek\ImgAnalyzer;

use Nette\SmartObject;

/**
 * Class ChangeAnalyzer
 *
 * @package Miloshavlicek\ImgAnalyzer
 * @property callable $imageReceiver
 * @property int $diffCount
 * @property int $segmentationCols
 * @property int $segmentationRows
 */
class ChangeAnalyzer
{

    /** @var callable */
    private $imageReceiver;

    /** @var integer */
    private $diffCount = 3;

    /** @var int */
    private $segmentationCols = 1;

    /** @var int */
    private $segmentationRows = 1;

    /** @var int */
    private $diffStep = 10;

    /** @var int */
    private $diffShift = 5;

    use SmartObject;

    /**
     * @return callable
     */
    public function getImageReceiver()
    {
        return $this->imageReceiver;
    }

    /**
     * @return int
     */
    public function getSegmentationCols()
    {
        return $this->segmentationCols;
    }

    /**
     * @param int $segmentationCols
     */
    public function setSegmentationCols($segmentationCols)
    {
        $this->segmentationCols = $segmentationCols;
    }

    /**
     * @return int
     */
    public function getSegmentationRows()
    {
        return $this->segmentationRows;
    }

    /**
     * @param int $segmentationRows
     */
    public function setSegmentationRows($segmentationRows)
    {
        $this->segmentationRows = $segmentationRows;
    }

    /**
     * @param callable $callback
     */
    public function setImageReceiver(callable $callback)
    {
        $this->imageReceiver = $callback;
    }

    /**
     * @return int
     */
    public function getDiffCount()
    {
        return $this->diffCount;
    }

    /**
     * @param int $diffCount
     */
    public function setDiffCount(int $diffCount)
    {
        $this->diffCount = $diffCount;
    }

    /**
     * @return float
     */
    public function analyze()
    {
        $diffs = []; // array of diffs by segments
        for ($i=0; $i < $this->diffCount; $i++) {
            $segmentation = new Segmentation();
            $segmentation->image = call_user_func($this->imageReceiver);
            $segmentation->cols = $this->segmentationCols;
            $segmentation->rows = $this->segmentationRows;
            $segmentation->cut();
            $segmentsAll = $segmentation->segmentsAll;

            for ($j=0; $j < count($segmentsAll); $j++) {
                if (empty($diffs[$j])) {
                    $diffs[$j] = new HistogramDiff();
                    $diffs[$j]->step = $this->diffStep;
                    $diffs[$j]->shift = $this->diffShift;
                }

                $histogram = new Histogram();
                $histogram->setImage($segmentsAll[$j]);
                $diffs[$j]->addHistogram($histogram);
            }
        }

        $out = [];
        foreach ($diffs as $diffsOne) {
            $out[] = $diffsOne->getDiffSum();
        }

        return $out;
    }
}
