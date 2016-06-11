<?php

/**
 * Class VisualChangesCest
 */
class VisualChangesCest
{
    /**
     * @param FunctionalTester $I
     */
    public function _before(FunctionalTester $I)
    {
    }

    /**
     * @param FunctionalTester $I
     */
    public function _after(FunctionalTester $I)
    {
    }

    /**
     * @param FunctionalTester $I
     */
    public function seeVisualChanges(FunctionalTester $I)
    {
        $I->amOnPage('/search?q=' . mt_rand(0, 100000));
        $I->seeVisualChanges('diff');

        // the test has to be called twice for comparison on the travis server
        $I->amOnPage('/search?q=' . mt_rand(0, 100000));
        $I->seeVisualChanges('diff');
    }

    /**
     * @param FunctionalTester $I
     */
    public function dontSeeVisualChanges(FunctionalTester $I)
    {
        $I->amOnPage('/');
        $I->dontSeeVisualChanges('same');

        // the test has to be called twice for comparison on the travis server
        $I->amOnPage('/');
        $I->dontSeeVisualChanges('same');
    }
}
