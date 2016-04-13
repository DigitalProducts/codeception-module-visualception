<?php

namespace Codeception\Module;
use Codeception\Module\ImageDeviationException;
use Codeception\TestCase;

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

    /**
     * This var represents the directory where the taken images are stored
     * @var string
     */
    private $currentImageDir;

    /**
     * @var TestCase
     */
    private $test;

    private $maximumDeviation = 0;

    private $webDriver = null;

    private $webDriverModule = null;

    /**
     * Create an object from VisualCeption Class
     *
     * @param array $config
     * @return result
     */
    public function __construct($config)
    {
        $result = parent::__construct($config);
        $this->init();
        return $result;
    }

    /**
     * Event hook before a test starts
     *
     * @param \Codeception\TestCase $test
     * @throws \Exception
     */
    public function _before(\Codeception\TestCase $test)
    {
        $webDriverModule = NULL;
        foreach ($this->getModules() as $module) {
            if ($module instanceof WebDriver) {
                $webDriverModule = $module;
            }
        }
        if (!$webDriverModule) {
            throw new \Exception("VisualCeption uses the WebDriver. Please be sure that this module is activated.");
        }

        $this->webDriverModule = $webDriverModule;
        $this->webDriver = $this->webDriverModule->webDriver;

        $jQueryString = file_get_contents(__DIR__ . "/jquery.js");
        $this->webDriver->executeScript($jQueryString);
        $this->webDriver->executeScript('jQuery.noConflict();');

        $this->test = $test;
    }

    public function getReferenceImageDir()
    {
        return $this->referenceImageDir;
    }

    /**
     * Compare the reference image with a current screenshot, identified by their indentifier name
     * and their element ID.
     *
     * @param string $identifier Identifies your test object
     * @param string $elementID DOM ID of the element, which should be screenshotted
     * @param string|array $excludeElements Element name or array of Element names, which should not appear in the screenshot
     */
    public function seeVisualChanges($identifier, $elementID = null, $excludeElements = array())
    {
        $environment = $this->test->getScenario()->current('env');
        if ($environment) {
            $identifier = $identifier . '.' . $environment;
        }

        $excludeElements = (array)$excludeElements;

        $deviationResult = $this->getDeviation($identifier, $elementID, $excludeElements);

        if (!is_null($deviationResult["deviationImage"])) {

            // used for assertion counter in codeception / phpunit
            $this->assertTrue(true);

            if ($deviationResult["deviation"] <= $this->maximumDeviation) {
                $compareScreenshotPath = $this->getDeviationScreenshotPath($identifier);
                $deviationResult["deviationImage"]->writeImage($compareScreenshotPath);

                throw new ImageDeviationException("The deviation of the taken screenshot is too low (" . $deviationResult["deviation"] . "%).\nSee $compareScreenshotPath for a deviation screenshot.",
                    $this->getExpectedScreenshotPath($identifier),
                    $this->getScreenshotPath($identifier),
                    $compareScreenshotPath);
            }
        }
    }

    /**
     * Compare the reference image with a current screenshot, identified by their indentifier name
     * and their element ID.
     *
     * @param string $identifier identifies your test object
     * @param string $elementID DOM ID of the element, which should be screenshotted
     * @param string|array $excludeElements string of Element name or array of Element names, which should not appear in the screenshot
     */
    public function dontSeeVisualChanges($identifier, $elementID = null, $excludeElements = array())
    {
        $environment = $this->test->getScenario()->current('env');
        if ($environment) {
            $identifier = $identifier . '.' . $environment;
        }

        $excludeElements = (array)$excludeElements;

        $deviationResult = $this->getDeviation($identifier, $elementID, $excludeElements);

        if (!is_null($deviationResult["deviationImage"])) {

            // used for assertion counter in codeception / phpunit
            $this->assertTrue(true);

            if ($deviationResult["deviation"] > $this->maximumDeviation) {
                $compareScreenshotPath = $this->getDeviationScreenshotPath($identifier);
                $deviationResult["deviationImage"]->writeImage($compareScreenshotPath);

                throw new ImageDeviationException("The deviation of the taken screenshot is too hight (" . $deviationResult["deviation"] . "%).\nSee $compareScreenshotPath for a deviation screenshot.",
                    $this->getExpectedScreenshotPath($identifier),
                    $this->getScreenshotPath($identifier),
                    $compareScreenshotPath);
            }
        }
    }

    /**
     * Hide an element to set the visibility to hidden
     *
     * @param $elementSelector String of jQuery Element selector, set visibility to hidden
     */
    private function hideElement($elementSelector)
    {
        $this->webDriver->executeScript('
            if( jQuery("' . $elementSelector . '").length > 0 ) {
                jQuery( "' . $elementSelector . '" ).css("visibility","hidden");
            }
        ');
        $this->debug("set visibility of element '$elementSelector' to 'hidden'");
    }

    /**
     * Show an element to set the visibility to visible
     *
     * @param $elementSelector String of jQuery Element selector, set visibility to visible
     */
    private function showElement($elementSelector)
    {
        $this->webDriver->executeScript('
            if( jQuery("' . $elementSelector . '").length > 0 ) {
                jQuery( "' . $elementSelector . '" ).css("visibility","visible");
            }
        ');
        $this->debug("set visibility of element '$elementSelector' to 'visible'");
    }

    /**
     * Compares the two images and calculate the deviation between expected and actual image
     *
     * @param string $identifier Identifies your test object
     * @param string $elementID DOM ID of the element, which should be screenshotted
     * @param array $excludeElements Element names, which should not appear in the screenshot
     * @return array Includes the calculation of deviation in percent and the diff-image
     */
    private function getDeviation($identifier, $elementID, array $excludeElements = array())
    {
        $coords = $this->getCoordinates($elementID);
        $this->createScreenshot($identifier, $coords, $excludeElements);

        $compareResult = $this->compare($identifier);

        $deviation = round($compareResult[1] * 100, 2);

        $this->debug("The deviation between the images is ". $deviation . " percent");

        return array (
            "deviation" => $deviation,
            "deviationImage" => $compareResult[0],
            "currentImage" => $compareResult['currentImage'],
        );
    }

    /**
     * Initialize the module and read the config.
     * Throws a runtime exception, if the
     * reference image dir is not set in the config
     *
     * @throws \RuntimeException
     */
    private function init()
    {
        if (array_key_exists('maximumDeviation', $this->config)) {
            $this->maximumDeviation = $this->config["maximumDeviation"];
        }

        if (array_key_exists('saveCurrentImageIfFailure', $this->config)) {
            $this->saveCurrentImageIfFailure = (boolean) $this->config["saveCurrentImageIfFailure"];
        }

        if (array_key_exists('referenceImageDir', $this->config)) {
            $this->referenceImageDir = $this->config["referenceImageDir"];
        } else {
            $this->referenceImageDir = \Codeception\Configuration::dataDir() . 'VisualCeption/';
        }

        if (!is_dir($this->referenceImageDir)) {
            $this->debug("Creating directory: $this->referenceImageDir");
            mkdir($this->referenceImageDir, 0777, true);
        }

        if (array_key_exists('currentImageDir', $this->config)) {
            $this->currentImageDir = $this->config["currentImageDir"];
        }else{
            $this->currentImageDir = \Codeception\Configuration::logDir() . 'debug/tmp/';
        }
    }

    /**
     * Find the position and proportion of a DOM element, specified by it's ID.
     * The method inject the
     * JQuery Framework and uses the "noConflict"-mode to get the width, height and offset params.
     *
     * @param string $elementId DOM ID of the element, which should be screenshotted
     * @return array coordinates of the element
     */
    private function getCoordinates($elementId)
    {
        if (is_null($elementId)) {
            $elementId = 'body';
        }

        $jQueryString = file_get_contents(__DIR__ . "/jquery.js");
        $this->webDriver->executeScript($jQueryString);
        $this->webDriver->executeScript('jQuery.noConflict();');

        $imageCoords = array();

        $elementExists = (bool)$this->webDriver->executeScript('return jQuery( "' . $elementId . '" ).length > 0;');

        if (!$elementExists) {
            throw new \Exception("The element you want to examine ('" . $elementId . "') was not found.");
        }

        $imageCoords['offset_x'] = (string)$this->webDriver->executeScript('return jQuery( "' . $elementId . '" ).offset().left;');
        $imageCoords['offset_y'] = (string)$this->webDriver->executeScript('return jQuery( "' . $elementId . '" ).offset().top;');
        $imageCoords['width'] = (string)$this->webDriver->executeScript('return jQuery( "' . $elementId . '" ).width();');
        $imageCoords['height'] = (string)$this->webDriver->executeScript('return jQuery( "' . $elementId . '" ).height();');

        return $imageCoords;
    }

    /**
     * Generates a screenshot image filename
     * it uses the testcase name and the given indentifier to generate a png image name
     *
     * @param string $identifier identifies your test object
     * @return string Name of the image file
     */
    private function getScreenshotName($identifier)
    {
        $caseName = str_replace('Cept.php', '', $this->test->getFileName());

        $search = array('/', '\\');
        $replace = array('.', '.');
        $caseName = str_replace($search, $replace, $caseName);

        return $caseName . '.' . $identifier . '.png';
    }

    /**
     * Returns the temporary path including the filename where a the screenshot should be saved
     * If the path doesn't exist, the method generate it itself
     *
     * @param string $identifier identifies your test object
     * @return string Path an name of the image file
     * @throws \RuntimeException if debug dir could not create
     */
    private function getScreenshotPath($identifier)
    {
        $debugDir = $this->currentImageDir;
        if (!is_dir($debugDir)) {
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
    private function getExpectedScreenshotPath($identifier)
    {
        return $this->referenceImageDir . $this->getScreenshotName($identifier);
    }

    /**
     * Generate the screenshot of the dom element
     *
     * @param string $identifier identifies your test object
     * @param array $coords Coordinates where the DOM element is located
     * @param array $excludeElements List of elements, which should not appear in the screenshot
     * @return string Path of the current screenshot image
     */
    private function createScreenshot($identifier, array $coords, array $excludeElements = array())
    {
        $screenShotDir = \Codeception\Configuration::logDir() . 'debug/';

        if( !is_dir($screenShotDir)) {
            mkdir($screenShotDir, 0777, true);
        }
        $screenshotPath = $screenShotDir . 'fullscreenshot.tmp.png';
        $elementPath = $this->getScreenshotPath($identifier);

        $this->hideElementsForScreenshot($excludeElements);
        $this->webDriver->takeScreenshot($screenshotPath);
        $this->resetHideElementsForScreenshot($excludeElements);

        $screenShotImage = new \Imagick();
        $screenShotImage->readImage($screenshotPath);
        $screenShotImage->cropImage($coords['width'], $coords['height'], $coords['offset_x'], $coords['offset_y']);
        $screenShotImage->writeImage($elementPath);

        unlink($screenshotPath);

        return $elementPath;
    }

    /**
     * Hide the given elements with CSS visibility = hidden. Wait a second after hiding
     *
     * @param array $excludeElements Array of strings, which should be not visible
     */
    private function hideElementsForScreenshot(array $excludeElements)
    {
        foreach ($excludeElements as $element) {
            $this->hideElement($element);
        }
        $this->webDriverModule->wait(1);
    }

    /**
     * Reset hiding the given elements with CSS visibility = visible. Wait a second after reset hiding
     *
     * @param array $excludeElements array of strings, which should be visible again
     */
    private function resetHideElementsForScreenshot(array $excludeElements)
    {
        foreach ($excludeElements as $element) {
            $this->showElement($element);
        }
        $this->webDriverModule->wait(1);
    }

    /**
     * Returns the image path including the filename of a deviation image
     *
     * @param $identifier identifies your test object
     * @return string Path of the deviation image
     */
    private function getDeviationScreenshotPath ($identifier, $alternativePrefix = '')
    {
        $debugDir = \Codeception\Configuration::logDir() . 'debug/';
        $prefix = ( $alternativePrefix === '') ? 'compare' : $alternativePrefix;
        return $debugDir . $prefix . $this->getScreenshotName($identifier);
    }


    /**
     * Compare two images by its identifiers.
     * If the reference image doesn't exists
     * the image is copied to the reference path.
     *
     * @param $identifier identifies your test object
     * @return array Test result of image comparison
     */
    private function compare($identifier)
    {
        $expectedImagePath = $this->getExpectedScreenshotPath($identifier);
        $currentImagePath = $this->getScreenshotPath($identifier);

        if (!file_exists($expectedImagePath)) {
            $this->debug("Copying image (from $currentImagePath to $expectedImagePath");
            copy($currentImagePath, $expectedImagePath);
            return array (null, 0, 'currentImage' => null);
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
    private function compareImages($image1, $image2)
    {
        $this->debug("Trying to compare $image1 with $image2");

        $imagick1 = new \Imagick($image1);
        $imagick2 = new \Imagick($image2);

        $imagick1Size = $imagick1->getImageGeometry();
        $imagick2Size = $imagick2->getImageGeometry();

        $maxWidth = max($imagick1Size['width'], $imagick2Size['width']);
        $maxHeight = max($imagick1Size['height'], $imagick2Size['height']);

        $imagick1->extentImage($maxWidth, $maxHeight, 0, 0);
        $imagick2->extentImage($maxWidth, $maxHeight, 0, 0);

        try {
            $result = $imagick1->compareImages($imagick2, \Imagick::METRIC_MEANSQUAREERROR);
            $result[0]->setImageFormat('png');
            $result['currentImage'] = clone $imagick2;
            $result['currentImage']->setImageFormat('png');
        }
        catch (\ImagickException $e) {
            $this->debug("IMagickException! could not campare image1 ($image1) and image2 ($image2).\nExceptionMessage: " . $e->getMessage());
            $this->fail($e->getMessage() . ", image1 $image1 and image2 $image2.");
        }
        return $result;
    }
}
