<?php

        $I->amOnPage("/VisualCeption/seeVisualChanges.php");
        $I->seeVisualChanges("block", "#theblock");

        // the test has to be called twice for comparison on the travis server
        $I->amOnPage("/VisualCeption/seeVisualChanges.php");
        $I->seeVisualChanges("block", "#theblock");
