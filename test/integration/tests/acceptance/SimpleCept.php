<?php

$I = new WebGuy($scenario);

$I->amOnPage("/VisualCeption/seeVisualChanges.php");
$I->seeVisualChanges("SimpleBlock", "#theblock");

// the test has to be called twice for comparison on the travis server
$I->wait(2);
$I->amOnPage("/VisualCeption/seeVisualChanges.php");
$I->seeVisualChanges("SimpleBlock", "#theblock");