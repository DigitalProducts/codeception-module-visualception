<?php

class TimeComparisonCest
{

    /**
     * Comparing a div that renders the current time
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
        $I->seeVisualChanges("hideTheIntro", "body", "#intro");

        $I->wait(1);

        // the test has to be called twice for comparison on the travis server
        $I->amOnPage("/VisualCeption/seeVisualChanges.php");
        $I->seeVisualChanges("hideTheIntro", "body", array("#intro"));
    }

    public function dontSeeVisualChangesAndHideElement (WebGuy $I, $scenario)
    {
        $I->amOnPage("/VisualCeption/seeVisualChanges.php");
        $I->dontSeeVisualChanges("hideTheBlock", "body", "#theblock");

        $I->wait(1);

        // the test has to be called twice for comparison on the travis server
        $I->amOnPage("/VisualCeption/seeVisualChanges.php");
        $I->dontSeeVisualChanges("hideTheBlock", "body", array("#theblock"));
    }
}