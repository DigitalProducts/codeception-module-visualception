<?php
/**
 * Created by PhpStorm.
 * User: langn
 * Date: 14.10.14
 * Time: 13:25
 */

namespace Codeception\Module;

class ImageDeviationException extends \PHPUnit_Framework_ExpectationFailedException
{
    private $expectedImage;
    private $currentImage;
    private $deviationImage;

    public function __construct($message, $expectedImage, $currentImage, $deviationImage)
    {
        $this->deviationImage = $deviationImage;
        $this->currentImage = $currentImage;
        $this->expectedImage = $expectedImage;

        parent::__construct($message);
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