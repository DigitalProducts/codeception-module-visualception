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

            if (is_file( $currentImagePath )) {
                return true;
            }

            // @TODO: complete the test, if current.* image is written
            $scenario->incomplete();
            throw $exception;
        }
    }
}