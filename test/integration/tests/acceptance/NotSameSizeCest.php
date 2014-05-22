<?php

class NotSameSizeCest
{

    /**
     * Comparing a div, that change it's size
     */
    public function seeVisualChangesAfterSizeChanges(WebGuy $I, $scenario)
    {
        $I->amOnPage("/VisualCeption/notSameSize.php");
        $I->seeVisualChanges("getRedDiv", "div");

        $I->wait(1);

        // the test has to be called twice for comparison on the travis server
        $I->amOnPage("/VisualCeption/notSameSize.php");
        $I->seeVisualChanges("getRedDiv", "div");
    }
}