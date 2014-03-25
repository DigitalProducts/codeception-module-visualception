<?php

namespace Codeception\Module;

/**
 * Class VisualCeption
 *
 * @copyright Copyright (c) 2014 G+J Digital Products GmbH
 * @license MIT license, http://www.opensource.org/licenses/mit-license.php
 * @package Codeception\Module
 *
 * @author Nils Langner <langner.nils@guj.de>
 * @author Torsten Franz
 * @author Sebastian Neubert
 */
class VisualCeption extends \Codeception\Module
{

    private $referenceImageDir;

    private $maximumDeviation = 0;

    /**
     * Create an object from VisualCeption Class
     *
     * @param array $config
     * @return result
     */
    public function __construct ($config)
    {
        $result = parent::__construct($config);
        $this->init();
        return $result;
    }

    /**
     * Event hook before a test starts
     *
     * @param \Codeception\TestCase $test
     */
    public function _before (\Codeception\TestCase $test)
    {
        $this->test = $test;
    }

    private function getDeviation ($identifier, $elementID)
    {
        $coords = $this->getCoordinates($elementID);
        $this->createScreenshot($identifier, $coords);

        $compareResult = $this->compare($identifier);

        unlink($this->getScreenshotPath($identifier));

        $deviation = round($compareResult[1] * 100, 2);
        return array ("deviation" => $deviation, "deviationImage" => $compareResult[0]);
    }

    /**
     * Compare the reference image with a current screenshot, identified by their indentifier name
     * and their element ID.
     *
     * @param string $identifier identifies your test object
     * @param string $elementID DOM ID of the element, which should be screenshotted
     */
    public function dontseeVisualChanges ($identifier, $elementID = null)
    {
        $deviationResult = $this->getDeviation($identifier, $elementID);
        if (! is_null($deviationResult["deviationImage"])) {
            if ($deviationResult["deviation"] > $this->maximumDeviation) {
                $compareScreenshotPath = $this->getDeviationScreenshotPath($identifier);
                $deviationResult["deviationImage"]->writeImage($compareScreenshotPath);
                $this->assertTrue(false, "The deviation of the taken screenshot is too high (" . $deviationResult["deviation"] . "%).\nSee $compareScreenshotPath for a deviation screenshot.");
            }
        }
    }

    /**
     * Compare the reference image with a current screenshot, identified by their indentifier name
     * and their element ID.
     *
     * @param string $identifier identifies your test object
     * @param string $elementID DOM ID of the element, which should be screenshotted
     */
    public function seeVisualChanges ($identifier, $elementID = null)
    {
        $deviationResult = $this->getDeviation($identifier, $elementID);
        if (! is_null($deviationResult["deviationImage"])) {
            if ($deviationResult["deviation"] <= $this->maximumDeviation) {
                $compareScreenshotPath = $this->getDeviationScreenshotPath($identifier);
                $deviationResult["deviationImage"]->writeImage($compareScreenshotPath);
                $this->assertTrue(false, "The deviation of the taken screenshot is too low (" . $deviationResult["deviation"] . "%).\nSee $compareScreenshotPath for a deviation screenshot.");
            }
        }
    }

    /**
     * Initialize the module and read the config.
     * Throws a runtime exception, if the
     * reference image dir is not set in the config
     *
     * @throws \RuntimeException
     */
    private function init ()
    {
        if (array_key_exists('maximumDeviation', $this->config)) {
            $this->maximumDeviation = $this->config["maximumDeviation"];
        }

        if (array_key_exists('referenceImageDir', $this->config)) {
            $this->referenceImageDir = $this->config["referenceImageDir"];
        } else {
            $this->referenceImageDir = \Codeception\Configuration::dataDir() . 'VisualCeption/';
        }

        if (! is_dir($this->referenceImageDir)) {
            $this->debug("Creating directory: $this->referenceImageDir");
            mkdir($this->referenceImageDir, 0777, true);
        }
    }

    /**
     * Find the position and proportion of a DOM element, specified by it's ID.
     * The method inject the
     * JQuery Framework and uses the "noConflict"-mode to get the width, height and offset params.
     *
     * @param $elementId DOM ID of the element, which should be screenshotted
     * @return array coordinates of the element
     */
    private function getCoordinates ($elementId)
    {
        $webDriver = $this->getModule("WebDriver")->webDriver;
        if (is_null($elementId)) {
            $elementId = 'body';
        }

        $jQueryString = file_get_contents(__DIR__ . "/jquery.js");
        $webDriver->executeScript($jQueryString);
        $webDriver->executeScript('jQuery.noConflict();');

        $imageCoords = array ();
        $imageCoords['offset_x'] = (string) $webDriver->executeScript('var element = jQuery( "' . $elementId . '" );var offset = element.offset();return offset.left;');
        $imageCoords['offset_y'] = (string) $webDriver->executeScript('var element = jQuery( "' . $elementId . '" );var offset = element.offset();return offset.top;');
        $imageCoords['width'] = (string) $webDriver->executeScript('var element = jQuery( "' . $elementId . '" );return element.width();');
        $imageCoords['height'] = (string) $webDriver->executeScript('var element = jQuery( "' . $elementId . '" );return element.height();');

        return $imageCoords;
    }

    /**
     * Generates a screenshot image filename
     * it uses the testcase name and the given indentifier to generate a png image name
     *
     * @param string $identifier identifies your test object
     * @return string Name of the image file
     */
    private function getScreenshotName ($identifier)
    {
        $caseName = str_replace('Cept.php', '', $this->test->getFileName());
        return $caseName . '.' . $identifier . '.png';
    }

    /**
     * Returns the temporary path including the filename where a the screenshot should be saved
     * If the path doesn't exist, the method generate it itself
     *
     * @param string $identifier identifies your test object
     * @return string Path an name of the image file
     */
    private function getScreenshotPath ($identifier)
    {
        $debugDir = \Codeception\Configuration::logDir() . 'debug/tmp/';
        if (! is_dir($debugDir)) {
            $created = mkdir($debugDir, 0777, true);
            if ($created) {
                $this->debug("Creating directory: $debugDir");
            } else {
                throw new \RuntimeException("Unable to create temporary screenshot dir ($debugDir)");
            }
        }
        return $debugDir . $this->getScreenshotName($identifier);
    }

    /**
     * Returns the reference image path including the filename
     *
     * @param string $identifier identifies your test object
     * @return string Name of the reference image file
     */
    private function getExpectedScreenshotPath ($identifier)
    {
        return $this->referenceImageDir . $this->getScreenshotName($identifier);
    }

    /**
     * Generate the screenshot of the dom element
     *
     * @param string $identifier identifies your test object
     * @param array $coords Coordinates where the DOM element is located
     * @return string Path of the current screenshot image
     */
    private function createScreenshot ($identifier, array $coords)
    {
        $webDriverModule = $this->getModule("WebDriver");
        $webDriver = $webDriverModule->webDriver;

        $screenshotPath = \Codeception\Configuration::logDir() . 'debug/' . "fullscreenshot.tmp.png";
        $elementPath = $this->getScreenshotPath($identifier);

        $webDriver->takeScreenshot($screenshotPath);

        $screenShotImage = new \Imagick();
        $screenShotImage->readImage($screenshotPath);
        $screenShotImage->cropImage($coords['width'], $coords['height'], $coords['offset_x'], $coords['offset_y']);
        $screenShotImage->writeImage($elementPath);

        unlink($screenshotPath);

        return $elementPath;
    }

    /**
     * Returns the image path including the filename of a deviation image
     *
     * @param $identifier identifies your test object
     * @return string Path of the deviation image
     */
    private function getDeviationScreenshotPath ($identifier)
    {
        $debugDir = \Codeception\Configuration::logDir() . 'debug/';
        return $debugDir . 'compare.' . $this->getScreenshotName($identifier);
    }

    /**
     * Compare two images by its identifiers.
     * If the reference image doesn't exists
     * the image is copied to the reference path.
     *
     * @param $identifier identifies your test object
     * @return array Test result of image comparison
     */
    private function compare ($identifier)
    {
        $currentImagePath = $this->getScreenshotPath($identifier);
        $expectedImagePath = $this->getExpectedScreenshotPath($identifier);

        if (! file_exists($expectedImagePath)) {
            $this->debug("Copying image (from $currentImagePath to $expectedImagePath");
            copy($currentImagePath, $expectedImagePath);
            return array (null, 0);
        } else {
            return $this->compareImages($expectedImagePath, $currentImagePath);
        }
    }

    /**
     * Compares to images by given file path
     *
     * @param $image1 Path to the exprected reference image
     * @param $image2 Path to the current image in the screenshot
     * @return array Result of the comparison
     */
    private function compareImages ($image1, $image2)
    {
        $imagick1 = new \Imagick($image1);
        $imagick2 = new \Imagick($image2);

        $result = $imagick1->compareImages($imagick2, \Imagick::METRIC_MEANSQUAREERROR);
        $result[0]->setImageFormat("png");

        return $result;
    }
}
