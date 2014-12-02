<?php

namespace Codeception\Module;
use Codeception\Module\ImageDeviationException;

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
    private $maximumDeviation = 0;

    /**
     * @var \RemoteWebDriver
     */
    private $webDriver = null;
    private $webDriverModule = null;

    /**
     * @var \Storage
     */
    private $storageStrategy;

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

        if (array_key_exists('currentImageDir', $this->config)) {
            $this->currentImageDir = $this->config["currentImageDir"];
        }else{
            $this->currentImageDir = \Codeception\Configuration::logDir() . 'debug/tmp/';
        }

        if (array_key_exists('storageStrategy', $this->config)) {
            // @todo create a factory and use it
            switch($this->config['storageStrategy']) {
                case "RemoteStorage":
                    $this->storageStrategy = new \RemoteStorage($this->config);
                    break;
                case "FileStorage":
                    $this->storageStrategy = new \FileStorage($this->config);
                    break;
                default:
                    throw new \Exception('The given storage strategy is not supported.');
            }
        }else{
            $this->storageStrategy = new \FileStorage($this->config);
        }
    }

    /**
     * Event hook before a test starts
     *
     * @param \Codeception\TestCase $test
     * @throws \Exception
     */
    public function _before(\Codeception\TestCase $test)
    {
        if (!$this->hasModule("WebDriver")) {
            throw new \Exception("VisualCeption uses the WebDriver. Please be sure that this module is activated.");
        }

        $this->webDriverModule = $this->getModule("WebDriver");
        $this->webDriver = $this->webDriverModule->webDriver;

        $jQueryString = file_get_contents(__DIR__ . "/jquery.js");
        $this->webDriver->executeScript($jQueryString);
        $this->webDriver->executeScript('jQuery.noConflict();');

        $this->test = $test;
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
        $comparisonResult = $this->getVisualChanges($identifier, $elementID, (array)$excludeElements);

        if($comparisonResult->getDeviation() <= $this->maximumDeviation ) {
            $this->assertTrue(true);
            throw new ImageDeviationException("The deviation of the taken screenshot is too low (" . $comparisonResult->getDeviation() . "%)",
                $comparisonResult, $this->storageStrategy);
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
        $comparisonResult = $this->getVisualChanges($identifier, $elementID, (array)$excludeElements);

        if($comparisonResult->getDeviation() > $this->maximumDeviation ) {
            $this->assertTrue(true);
            throw new ImageDeviationException("The deviation of the taken screenshot is too high (" . $comparisonResult->getDeviation() . "%)",
                $comparisonResult, $this->storageStrategy);
        }
    }

    private function getVisualChanges($identifier, $elementId, array $excludedElements)
    {
        $expectedImage = $this->storageStrategy->getImage($identifier);
        $currentImage = $this->getCurrentImage($excludedElements, $elementId);
        return $this->getComparisonResult($expectedImage, $currentImage);
    }

    private function getComparisonResult(\Imagick $expectedImage, \Imagick $currentImage)
    {
        try {
            $imageCompare = new \Comparison();
            return $imageCompare->compare($expectedImage, $currentImage);
        } catch (\ImagickException $e) {
            $this->debug("IMagickException! could not compare images.\nExceptionMessage: " . $e->getMessage());
            $this->fail($e->getMessage());
        }
    }

    private function getCurrentImage(array $excludedElements, $elementId)
    {
        $htmlManipulator = new \Manipulation($this->webDriver);
        $htmlManipulator->hideElements($excludedElements);

        $this->debug('hide');

        $htmlScreenshot = new \Screenshot($this->webDriver);



        return $htmlScreenshot->takeScreenshot($elementId);
    }
}