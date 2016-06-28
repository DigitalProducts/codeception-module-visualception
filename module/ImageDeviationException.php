<?php

namespace Codeception\Module;

class ImageDeviationException extends \PHPUnit_Framework_ExpectationFailedException
{
    private $failedTest;
    private $expectedImage;
    private $currentImage;
    private $deviationImage;

    public function __construct($message, $test, $expectedImage, $currentImage, $deviationImage)
    {
        $this->failedTest = $test;
        $this->deviationImage = $deviationImage;
        $this->currentImage = $currentImage;
        $this->expectedImage = $expectedImage;

        parent::__construct($message);
    }

    public function getFailedTest( )
    {
        return $this->failedTest;
    }

    public function getDeviationImage( )
    {
        return $this->deviationImage;
    }

    public function getCurrentImage()
    {
        return $this->currentImage;
    }

    public function getExpectedImage()
    {
        return $this->expectedImage;
    }
}
