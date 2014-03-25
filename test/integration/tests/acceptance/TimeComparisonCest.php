<?php

class TimeComparisonCest
{

    /**
     * Coparing a div that renders the current time
     */
    public function compareTimeString (WebGuy $I, $scenario)
    {
        $I->amOnPage("/VisualCeption/seeVisualChanges.php");
        $I->seeVisualChanges("block", "#theblock");

        // the test has to be called twice for comparison on the travis server
        $I->amOnPage("/VisualCeption/time.php");
        $I->seeVisualChanges("block", "#theblock");
    }
}