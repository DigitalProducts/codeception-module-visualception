<?php

class TimeComparisonCest
{

    /**
     * Coparing a div that renders the current time
     */
    public function compareTimeString (WebGuy $I, $scenario)
    {
        $I->amOnPage("/VisualCeption/time.php");
        $I->compareScreenshot("the-time", "#thetime");

        // the test has to be called twice for comparison on the travis server
        $I->amOnPage("/VisualCeption/time.php");
        $I->compareScreenshot("the-time", "#thetime");
    }
}