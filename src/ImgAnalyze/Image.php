<?php

namespace Miloshavlicek\ImgAnalyzer;

use Nette\SmartObject;

/**
 * Class Image
 *
 * @package Miloshavlicek\ImgAnalyzer
 * @property-read int|null $width
 * @property-read int|null $height
 * @property-read int|null $pixelsCount
 * @property-read resource|null $resource
 */
class Image
{

    use SmartObject;

    /** @var resource|null */
    private $resource;

    /** @var string|null */
    private $width;

    /** @var string|null */
    private $height;

    const CROP_PCT = 0; // Crop image by percents
    const CROP_PIX = 1; // Crop image by pixels

    /**
     * @return int|null
     */
    public function getWidth(): ?int
    {
        return $this->width;
    }

    /**
     * @return int|null
     */
    public function getHeight(): ?int
    {
        return $this->height;
    }

    /**
     * @return int|null
     */
    public function getPixelsCount(): ?int
    {
        return $this->width * $this->height;
    }

    /**
     * @return resource|null
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @param string $image
     * @param array $crop
     * @param int $cropType
     */
    public function setImageFromString(string $image, array $crop = [], int $cropType = Image::CROP_PCT): void
    {
        $this->setImageFromResource(imagecreatefromstring($image), $crop, $cropType);
    }

    /**
     * @param $image
     * @param array $crop
     * @param int $cropType
     */
    public function setImageFromResource($image, array $crop = [], int $cropType = Image::CROP_PCT): void
    {
        $this->checkResourceParam($image);

        if (!empty($crop)) {
            $image = $this->cropImageByParams($image, $crop, $cropType);
        }

        $this->resource = $image;
        $this->width = imagesx($image);
        $this->height = imagesy($image);
    }

    /**
     * @param $image
     * @param array $crop array of values in percents/pixels depending on $cropType param [x,y,width,height]
     * @param int $cropType
     * @return resource
     * @throws \Exception
     */
    private function cropImageByParams($image, $crop = [], int $cropType = Image::CROP_PCT)
    {
        $this->checkResourceParam($image);

        if (!isset($crop[0]) || !isset($crop[1]) || !isset($crop[2]) || !isset($crop[3])) {
            throw new \Exception('Bad parameters $crop for cropImageByParams(...)');
        }

        if ($cropType === Image::CROP_PCT) {
            $imgWidth = imagesx($image);
            $imgHeight = imagesy($image);

            return $this->cropImage(
                $image,
                floor($crop[0] * 0.01 * $imgWidth),
                floor($crop[1] * 0.01 * $imgHeight),
                floor($crop[2] * 0.01 * $imgWidth),
                floor($crop[3] * 0.01 * $imgHeight)
            );
        } elseif ($cropType === Image::CROP_PIX) {
            return $this->cropImage($image, $crop[0], $crop[1], $crop[2], $crop[3]);
        } else {
            throw new \Exception('Bad parameter $cropType for cropImageByParams(...)');
        }
    }

    /**
     * @param resource $image
     * @param int $x in pixels
     * @param int $y in pixels
     * @param int $width in pixels
     * @param int $height in pixels
     * @return resource
     */
    private function cropImage($image, int $x, int $y, int $width, int $height)
    {
        $this->checkResourceParam($image);

        return imagecrop($image, ['x' => $x, 'y' => $y, 'width' => $width, 'height' => $height]);
    }

    /**
     * @param $param
     * @throws \Exception
     */
    private function checkResourceParam($param): void
    {
        if (is_string($param) || !is_resource($param)) {
            throw new \Exception(
                sprintf(
                    'Argument must be a valid resource type. %s given.',
                    gettype($param)
                )
            );
        }
    }
}
