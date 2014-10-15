<?php

use Codeception\Module\ImageDeviationException;

class WriteCurrentImageCest
{

    /**
     * fail the test. lookup is current image is written
     */
    public function writeCurrentImageFile(WebGuy $I, $scenario)
    {
        $I->amOnPage("/VisualCeption/seeVisualChanges.php");
        $I->dontSeeVisualChanges("currentImageIdentifier", "#theblock");

        $I->wait(2);

        // the test has to be called twice for comparison on the travis server
        // expect failing the test

        $I->amOnPage("/VisualCeption/seeVisualChanges.php");
        try
        {
            $I->dontSeeVisualChanges("currentImageIdentifier", "#theblock");
        }
        catch (ImageDeviationException $exception)
        {
            $currentImagePath = $exception->getCurrentImage();

            if (!is_file( $exception->getCurrentImage() )) {
                throw new \PHPUnit_Framework_ExpectationFailedException("The screenshot was not saved successfully.");
            }
        }
    }
}