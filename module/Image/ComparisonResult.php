<?php

class ComparisonResult
{
    private $deviation;
    private $comparionImage;
    private $expectedImage;
    private $currentImage;

    public function __construct($deviation, \Imagick $expectedImage, \Imagick $currentImage,  \Imagick $comparisonImage)
    {
        $this->currentImage = $currentImage;
        $this->expectedImage = $expectedImage;

        $this->deviation = $deviation;
        $this->comparionImage = $comparisonImage;
    }

    public function getDeviation()
    {
        return $this->deviation;
    }

    /**
     * @return Imagick
     */
    public function getDeviationImage()
    {
        return $this->comparionImage;
    }

    /**
     * @return Imagick
     */
    public function getExpectedImage()
    {
        return $this->expectedImage;
    }

    /**
     * @return Imagick
     */
    public function getCurrentImage()
    {
        return $this->currentImage;
    }
}