<?php

class TimeComparisonCest
{

    /**
     * Coparing a div that renders the current time
     */
    public function seeVisualChanges (WebGuy $I, $scenario)
    {
        $I->amOnPage("/VisualCeption/seeVisualChanges.php");
        $I->seeVisualChanges("block", "#theblock");

        $I->wait(1);

        // the test has to be called twice for comparison on the travis server
        $I->amOnPage("/VisualCeption/seeVisualChanges.php");
        $I->seeVisualChanges("block", "#theblock");
    }

    public function dontSeeVisualChanges (WebGuy $I, $scenario)
    {
        $I->amOnPage("/VisualCeption/dontSeeVisualChanges.php");
        $I->dontSeeVisualChanges("block2", "#theblock");

        $I->wait(1);

        // the test has to be called twice for comparison on the travis server
        $I->amOnPage("/VisualCeption/dontSeeVisualChanges.php");
        $I->dontSeeVisualChanges("block2", "#theblock");
    }

    public function seeVisualChangesAndHideElement (WebGuy $I, $scenario)
    {
        $I->amOnPage("/VisualCeption/seeVisualChanges.php");
        $I->seeVisualChanges("body", "body", "#intro");

        // the test has to be called twice for comparison on the travis server
        $I->amOnPage("/VisualCeption/seeVisualChanges.php");
        $I->dontSeeVisualChanges("body", "body", array("#intro"));
    }

    public function dontSeeVisualChangesAndHideElement (WebGuy $I, $scenario)
    {
        $I->amOnPage("/VisualCeption/seeVisualChanges.php");
        $I->dontSeeVisualChanges("body", "body", "#theblock");

        // the test has to be called twice for comparison on the travis server
        $I->amOnPage("/VisualCeption/seeVisualChanges.php");
        $I->dontSeeVisualChanges("body", "body", array("#theblock"));
    }
}