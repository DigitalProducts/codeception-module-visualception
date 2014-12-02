<?php


class Manipulation
{
    private $webDriver;

    public function __construct(\RemoteWebDriver $webDriver)
    {
        $this->webDriver = $webDriver;
        $this->injectJQuery();
    }

    /**
     * This function injects jQuery into the website
     */
    private function injectJQuery()
    {
        $jQueryString = file_get_contents(__DIR__ . "/../jquery.js");
        $this->webDriver->executeScript($jQueryString);
        $this->webDriver->executeScript('jQuery.noConflict();');
    }

    /**
     * This function hides the given divs from the website.
     *
     * @param array $elements
     */
    public function hideElements(array $elements)
    {
        foreach ($elements as $element) {
            $this->hideElement($element);
        }
        $this->webDriver->wait(1);
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
    }

    /**
     * Reset hiding the given elements with CSS visibility = visible. Wait a second after reset hiding
     *
     * @param array $excludeElements array of strings, which should be visible again
     */
    private function showElements(array $elements)
    {
        foreach ($elements as $element) {
            $this->showElement($element);
        }
        $this->webDriver->wait(1);
    }
}