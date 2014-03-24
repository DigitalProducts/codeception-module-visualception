<?php

namespace Codeception\Module;

class VisualCeption extends \Codeception\Module
{

    private $referenceImageDir;

    private $maximumDeviation = 0;

    public function __construct ($config)
    {
        $result = parent::__construct($config);
        $this->init();
        return $result;
    }

    public function _before (\Codeception\TestCase $test)
    {
        $this->test = $test;
    }

    private function init ()
    {
        if (array_key_exists('maximumDeviation', $this->config)) {
            $this->maximumDeviation = $this->config["maximumDeviation"];
        }

        if (array_key_exists('referenceImageDir', $this->config)) {
            $this->referenceImageDir = $this->config["referenceImageDir"];
        } else {
            throw new \RuntimeException("Reference image dir was not set, but is mandatory.");
        }

        if (! is_dir($this->referenceImageDir)) {
            mkdir($this->referenceImageDir, 0777);
        }
    }

    private function getCoordinates ($elementId)
    {
        $webDriver = $this->getModule("WebDriver")->webDriver;
        if (is_null($elementId)) {
            $elementId = 'body';
        }

        $jQueryString = file_get_contents(__DIR__."/jquery.js");
        $webDriver->executeScript($jQueryString);
        $webDriver->executeScript('jQuery.noConflict();');

        $imageCoords = array ();
        $imageCoords['offset_x'] = (string) $webDriver->executeScript('var element = jQuery( "' . $elementId . '" );var offset = element.offset();return offset.left;');
        $imageCoords['offset_y'] = (string) $webDriver->executeScript('var element = jQuery( "' . $elementId . '" );var offset = element.offset();return offset.top;');
        $imageCoords['width'] = (string) $webDriver->executeScript('var element = jQuery( "' . $elementId . '" );return element.width();');
        $imageCoords['height'] = (string) $webDriver->executeScript('var element = jQuery( "' . $elementId . '" );return element.height();');

        return $imageCoords;
    }

    private function getScreenshotName ($identifier)
    {
        $caseName = str_replace('Cept.php', '', $this->test->getFileName());
        return $caseName . $identifier . "-element.png";
    }

    private function getScreenshotPath ($identifier)
    {
        $debugDir = \Codeception\Configuration::logDir() . 'debug/tmp/';
        if (! is_dir($debugDir)) {
            mkdir($debugDir, 0777);
        }
        return $debugDir . $this->getScreenshotName($identifier);
    }

    private function getExpectedScreenshotPath ($identifier)
    {
        return $this->referenceImageDir . $this->getScreenshotName($identifier);
    }

    private function createScreenshot ($identifier, $coords)
    {
        $webDriverModule = $this->getModule("WebDriver");
        $webDriver = $webDriverModule->webDriver;

        $screenshotPath = \Codeception\Configuration::logDir() . 'debug/' . "fullscreenshot.tmp.png";
        $elementPath = $this->getScreenshotPath($identifier);

        $webDriver->takeScreenshot($screenshotPath);

        $screenshotImage = imagecreatefrompng($screenshotPath);
        $elementImage =\imagecreatetruecolor($coords['width'], $coords['height']);

        list ($current_width, $current_height) =\getimagesize($screenshotPath);
        imagecopy($elementImage, $screenshotImage, 0, 0, $coords['offset_x'], $coords['offset_y'], $current_width, $current_height);

        $result =\imagepng($elementImage, $elementPath, 0);

        unlink($screenshotPath);

        return $elementPath;
    }

    public function compareScreenshot ($identifier, $elementID = null)
    {
        $coords = $this->getCoordinates($elementID);
        $currentImagePath = $this->createScreenshot($identifier, $coords);

        $compareResult = $this->compare($identifier);

        unlink($this->getScreenshotPath($identifier));

        $this->debug($compareResult);

        if ($compareResult[1] > $this->maximumDeviation) {
            $compareScreenshotPath = $this->getDeviationScreenshotPath($identifier);
            $compareResult[0]->writeImage($compareScreenshotPath);
            $this->assertTrue(false, "The deviation of the taken screenshot is too high. See $compareScreenshotPath for a deviation screenshot.");
        }
    }

    private function getDeviationScreenshotPath ($identifier)
    {
        $debugDir = \Codeception\Configuration::logDir() . 'debug/';
        return $debugDir . 'compare.' . $this->getScreenshotName($identifier);
    }

    private function compare ($identifier)
    {
        $currentImagePath = $this->getScreenshotPath($identifier);
        $expectedImagePath = $this->getExpectedScreenshotPath($identifier);

        if (! file_exists($expectedImagePath)) {
            copy($currentImagePath, $expectedImagePath);
            return array (null, 0);
        } else {
            return $this->compareImages($expectedImagePath, $currentImagePath);
        }
    }

    private function compareImages ($image1, $image2)
    {
        $imagick1 = new \Imagick($image1);
        $imagick2 = new \Imagick($image2);

        $result = $imagick1->compareImages($imagick2, \Imagick::METRIC_MEANSQUAREERROR);
        $result[0]->setImageFormat("png");

        $this->debug($result);

        return $result;
    }
}