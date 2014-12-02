<?php


class Screenshot
{
    private $webDriver;

    public function __construct(\RemoteWebDriver $webDriver)
    {
        $this->webDriver = $webDriver;
    }

    public function takeScreenshot($jqueryIdentifier = "body")
    {
        $image = new \Imagick();
        $image->readimageblob($this->webDriver->takeScreenshot());

        $coords = $this->getCoordinates($jqueryIdentifier);

        $image->cropImage($coords['width'], $coords['height'], $coords['offset_x'], $coords['offset_y']);

        return $image;
    }

    private function getCoordinates($jqueryIdentifier)
    {
        $jQueryString = file_get_contents(__DIR__ . "/../jquery.js");
        $this->webDriver->executeScript($jQueryString);
        $this->webDriver->executeScript('jQuery.noConflict();');

        $imageCoords = array();

        $elementExists = (bool)$this->webDriver->executeScript('return jQuery( "' . $jqueryIdentifier . '" ).length > 0;');

        if (!$elementExists) {
            throw new \Exception("The element you want to examine ('" . $jqueryIdentifier . "') was not found.");
        }

        $imageCoords['offset_x'] = (string)$this->webDriver->executeScript('return jQuery( "' . $jqueryIdentifier . '" ).offset().left;');
        $imageCoords['offset_y'] = (string)$this->webDriver->executeScript('return jQuery( "' . $jqueryIdentifier . '" ).offset().top;');
        $imageCoords['width'] = (string)$this->webDriver->executeScript('return jQuery( "' . $jqueryIdentifier . '" ).width();');
        $imageCoords['height'] = (string)$this->webDriver->executeScript('return jQuery( "' . $jqueryIdentifier . '" ).height();');

        return $imageCoords;
    }
}