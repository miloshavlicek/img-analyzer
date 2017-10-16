<?php

namespace Miloshavlicek\ImgAnalyzer;

use Nette\SmartObject;

/**
 * Class HistogramDiff
 * @package Miloshavlicek\ImgAnalyzer
 * @property float $step
 * @property float $shift
 */
class HistogramDiff
{

    /** @var array */
    private $histograms = [];

    /** @var float Step of diff in percents */
    private $step = 10;

    /** @var float|null Shift of diff in percents */
    private $shift;

    use SmartObject;

    /**
     * @param Histogram $histogram
     */
    public function addHistogram(Histogram $histogram): void
    {
        $this->histograms[] = $histogram;
    }

    /**
     * @return float
     */
    public function getStep(): float
    {
        return $this->step;
    }

    /**
     * @param float $step
     */
    public function setStep(float $step): void
    {
        $this->step = $step;
    }

    /**
     * @return float
     */
    public function getShift(): float
    {
        if ($this->shift === null) {
            return $this->step / 2;
        }
        return $this->shift;
    }

    /**
     * @param float|null $shift
     */
    public function setShift(?float $shift = null): void
    {
        $this->shift = $shift;
    }

    /**
     * @return array
     */
    private function getHistogramsParts(): array
    {
        $out = [];
        $i = 0;
        foreach ($this->histograms as $histogram) {
            $out[$i] = [];
            for ($j = 0; $j < ceil(100 / $this->getShift()); $j++) {
                $limitBottom = $j * $this->getShift();
                $limitTop = $limitBottom + $this->getStep();
                if ($limitTop > 100) {
                    $limitTop = 100;
                }

                $out[$i][$j] = (object)[
                    'size' => $limitTop - $limitBottom,
                    'value' => $histogram->getPercentageByLimits($limitBottom, $limitTop)
                ];
            }
            $i++;
        }

        return $out;
    }

    /**
     * @return float
     */
    public function getDiffSum(): float
    {
        $parts = $this->getHistogramsParts();

        // Process diffs
        $sum = 0;
        $diffs = [];
        for ($i = 1; $i < count($parts); $i++) {
            $diffs[$i - 1] = [];
            $partsSize = [];
            foreach ($parts[$i - 1] as $partKey => $part) {
                $partsSize[] = $parts[$i - 1][$partKey]->size;
                $prevValue = $parts[$i - 1][$partKey]->value;
                $oneValue = $parts[$i][$partKey]->value;

                $diffs[$i - 1][$partKey] = abs($prevValue - $oneValue) * $parts[$i - 1][$partKey]->size;
            }

            $sum += array_sum($diffs[$i - 1]) / (array_sum($partsSize) / count($partsSize));
        }

        return count($diffs) ? $sum / (count($diffs)) : 0;
    }
}
