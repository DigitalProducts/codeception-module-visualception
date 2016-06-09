<?php

namespace Codeception\Module;

use Codeception\Configuration;
use Codeception\Exception\ElementNotFound;
use Codeception\Module;
use Codeception\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverElement;
use WebDriverBy;

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
class VisualCeption extends Module
{
    /**
     * @var string
     */
    private $referenceImageDir;

    /**
     * This var represents the directory where the taken images are stored
     * @var string
     */
    private $currentImageDir;

    /**
     * Codeception TestCase
     * @var TestCase
     */
    private $test;

    /**
     * Maximum deviation for screenshots comparison
     * @var int
     */
    private $maximumDeviation = 0;

    /**
     * @var RemoteWebDriver
     */
    private $remoteWebDriver;

    /**
     * @var WebDriver
     */
    private $webDriverModule;


    private $saveCurrentImageIfFailure;


    /**
     * Initialize the module and read the config.
     * Throws a runtime exception, if the
     * reference image dir is not set in the config
     *
     * @throws \RuntimeException
     * @throws \InvalidElementStateException
     */
    public function _initialize()
    {
        if (array_key_exists('maximumDeviation', $this->config)) {
            $this->maximumDeviation = $this->config['maximumDeviation'];
        }

        if (array_key_exists('saveCurrentImageIfFailure', $this->config)) {
            $this->saveCurrentImageIfFailure = (boolean)$this->config['saveCurrentImageIfFailure'];
        }

        if (array_key_exists('referenceImageDir', $this->config)) {
            $this->referenceImageDir = $this->config['referenceImageDir'];
        } else {
            $this->referenceImageDir = Configuration::dataDir() . 'VisualCeption/';
        }

        if (!is_dir($this->referenceImageDir) && !@mkdir($this->referenceImageDir, 0777, true)) {
            throw new \InvalidElementStateException('Unable to create screenshot directory');
        }

        if (array_key_exists('currentImageDir', $this->config)) {
            $this->currentImageDir = $this->config['currentImageDir'];
        } else {
            $this->currentImageDir = Configuration::logDir() . 'debug/tmp/';
        }
    }


    /**
     * Event hook before a test starts
     *
     * @param \Codeception\TestCase $test
     * @throws \InvalidElementStateException
     */
    public function _before(\Codeception\TestCase $test)
    {
        $webDriverModule = null;
        foreach ($this->getModules() as $module) {
            if ($module instanceof WebDriver) {
                $webDriverModule = $module;
            }
        }
        if (!$webDriverModule) {
            throw new \InvalidElementStateException('VisualCeption uses the WebDriver. Please be sure that this module is activated.');
        }

        $this->webDriverModule = $webDriverModule;
        $this->remoteWebDriver = $this->webDriverModule->webDriver;

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
     * @throws \Codeception\Module\ImageDeviationException
     */
    public function seeVisualChanges($identifier, $elementID = null)
    {
        $environment = $this->test->getScenario()->current('env');
        if ($environment) {
            $identifier = $identifier . '.' . $environment;
        }

        $deviationResult = $this->getDeviation($identifier, $elementID);

        if ($deviationResult['deviationImage'] !== null) {

            // used for assertion counter in codeception / phpunit
            $this->assertTrue(true);

            if ($deviationResult['deviation'] <= $this->maximumDeviation) {
                $compareScreenshotPath = $this->getDeviationScreenshotPath($identifier);
                $deviationResult['deviationImage']->writeImage($compareScreenshotPath);

                throw new ImageDeviationException(
                    'The deviation of the taken screenshot is too low (' . $deviationResult['deviation'] . '%).'
                    . PHP_EOL
                    . 'See ' . $compareScreenshotPath . ' for a deviation screenshot.',
                    $this->getExpectedScreenshotPath($identifier),
                    $this->getScreenshotPath($identifier),
                    $compareScreenshotPath
                );
            }
        }
    }


    /**
     * Compare the reference image with a current screenshot, identified by their indentifier name
     * and their element ID.
     *
     * @param string $identifier identifies your test object
     * @param string $elementID DOM ID of the element, which should be screenshotted
     * @throws \Codeception\Module\ImageDeviationException
     */
    public function dontSeeVisualChanges($identifier, $elementID = null)
    {
        $environment = $this->test->getScenario()->current('env');
        if ($environment) {
            $identifier = $identifier . '.' . $environment;
        }

        $deviationResult = $this->getDeviation($identifier, $elementID);

        if (!is_null($deviationResult['deviationImage'])) {

            // used for assertion counter in codeception / phpunit
            $this->assertTrue(true);

            if ($deviationResult['deviation'] > $this->maximumDeviation) {
                $compareScreenshotPath = $this->getDeviationScreenshotPath($identifier);
                $deviationResult['deviationImage']->writeImage($compareScreenshotPath);

                throw new ImageDeviationException(
                    'The deviation of the taken screenshot is too low (' . $deviationResult['deviation'] . '%).'
                    . PHP_EOL
                    . 'See ' . $compareScreenshotPath . ' for a deviation screenshot.',
                    $this->getExpectedScreenshotPath($identifier),
                    $this->getScreenshotPath($identifier),
                    $compareScreenshotPath
                );
            }
        }
    }


    /**
     * Compares the two images and calculate the deviation between expected and actual image
     *
     * @param string $identifier Identifies your test object
     * @param string $selector DOM ID of the element, which should be screenshotted
     * @return array Includes the calculation of deviation in percent and the diff-image
     */
    private function getDeviation($identifier, $selector)
    {
        $coords = $this->getCoordinates($selector);
        $this->createScreenshot($identifier, $coords);

        $compareResult = $this->compare($identifier);

        $deviation = round($compareResult[1] * 100, 2);

        $this->debug('The deviation between the images is ' . $deviation . ' percent');

        return [
            'deviation' => $deviation,
            'deviationImage' => $compareResult[0],
            'currentImage' => $compareResult['currentImage'],
        ];
    }


    /**
     * Find the position and proportion of a DOM element, specified by it's ID.
     * The method inject the
     * JQuery Framework and uses the "noConflict"-mode to get the width, height and offset params.
     *
     * @param string $selector DOM ID/class of the element, which should be screenshotted
     * @return array coordinates of the element
     * @throws \Codeception\Exception\ElementNotFound
     */
    private function getCoordinates($selector = 'body')
    {
        try {
            $this->webDriverModule->waitForElementVisible($selector, 10);

            /** @var WebDriverElement|null $element */
            $element = $this->remoteWebDriver->findElement(WebDriverBy::cssSelector($selector));
        } catch (\Exception $e) {
            throw new ElementNotFound('Element ' . $selector . ' could not be located by WebDriver');
        }

        $elementSize = $element->getSize();
        $elementLocation = $element->getLocation();
        $imageCoords['x'] = $elementLocation->getX();
        $imageCoords['y'] = $elementLocation->getY();
        $imageCoords['width'] = $elementSize->getWidth();
        $imageCoords['height'] = $elementSize->getHeight();

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
        $className = preg_replace('/(Cept|Cest)\.php/', '', basename($this->test->getFileName()));

        return $className . '.' . $identifier . '.png';
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
                $this->debug("Creating directory: $debugDir}");
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
    private function createScreenshot($identifier, array $coords)
    {
        $screenShotDir = Configuration::logDir() . 'debug/';

        if (!is_dir($screenShotDir)) {
            mkdir($screenShotDir, 0777, true);
        }
        $screenshotPath = $screenShotDir . 'fullscreenshot.tmp.png';
        $elementPath = $this->getScreenshotPath($identifier);

        $this->remoteWebDriver->takeScreenshot($screenshotPath);

        $screenShotImage = new \Imagick();
        $screenShotImage->readImage($screenshotPath);
        $screenShotImage->cropImage($coords['width'], $coords['height'], $coords['x'], $coords['y']);
        $screenShotImage->writeImage($elementPath);

        unlink($screenshotPath);

        return $elementPath;
    }


    /**
     * Returns the image path including the filename of a deviation image
     *
     * @param string $identifier identifies your test object
     * @return string Path of the deviation image
     */
    private function getDeviationScreenshotPath($identifier, $alternativePrefix = '')
    {
        $debugDir = Configuration::logDir() . 'debug/';
        $prefix = ($alternativePrefix === '') ? 'compare' : $alternativePrefix;
        return $debugDir . $prefix . $this->getScreenshotName($identifier);
    }


    /**
     * Compare two images by its identifiers.
     * If the reference image doesn't exists
     * the image is copied to the reference path.
     *
     * @param string $identifier identifies your test object
     * @return array Test result of image comparison
     */
    private function compare($identifier)
    {
        $expectedImagePath = $this->getExpectedScreenshotPath($identifier);
        $currentImagePath = $this->getScreenshotPath($identifier);

        if (!file_exists($expectedImagePath)) {
            $this->debug("Copying image (from $currentImagePath to $expectedImagePath");
            copy($currentImagePath, $expectedImagePath);
            return [null, 0, 'currentImage' => null];
        } else {
            return $this->compareImages($expectedImagePath, $currentImagePath);
        }
    }


    /**
     * Compares to images by given file path
     *
     * @param string $image1 Path to the exprected reference image
     * @param string $image2 Path to the current image in the screenshot
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
        } catch (\ImagickException $e) {
            $this->debug(
                "IMagickException! could not campare image1 ($image1) and image2 ($image2)."
                . PHP_EOL
                . 'ExceptionMessage: '
                . $e->getMessage()
            );
            $this->fail($e->getMessage() . ", image1 $image1 and image2 $image2.");
        }
        return $result;
    }
}
