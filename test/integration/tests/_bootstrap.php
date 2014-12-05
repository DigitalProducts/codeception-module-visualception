<?php
// This is global bootstrap for autoloading

sleep(5);

include_once __DIR__."/../../../module/VisualCeption.php";
include_once __DIR__."/../../../module/ImageDeviationException.php";

include_once __DIR__."/../../../module/VisualCeptionReporter.php";

include_once __DIR__."/../../../module/Storage/Factory.php";
include_once __DIR__."/../../../module/Storage/Storage.php";
include_once __DIR__."/../../../module/Storage/FileStorage.php";
include_once __DIR__."/../../../module/Storage/RemoteStorage.php";

include_once __DIR__."/../../../module/Html/Manipulation.php";
include_once __DIR__."/../../../module/Html/Screenshot.php";

include_once __DIR__."/../../../module/Image/Comparison.php";
include_once __DIR__."/../../../module/Image/ComparisonResult.php";

include_once __DIR__."/../../../module/Report/Reporter.php";
include_once __DIR__."/../../../module/Report/FileReporter.php";
include_once __DIR__."/../../../module/Report/CIReporter.php";