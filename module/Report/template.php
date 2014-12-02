<!DOCTYPE html>
<html>
    <head>
        <title>VisualCeption Report</title>
    </head>
    <body>

        <img src="http://www.thewebhatesme.com/wp-content/uploads/visualception.png" />

        <?php foreach ($failedTests as $failedTest): ?>

            <div class="deviationimage">
                Deviation Image <br />
                <img src='data:image/png;base64,<?php echo base64_encode($failedTest->getComparisonResult()->getDeviationImage()->getImageBlob()); ?>' />
            </div>

            <div class="expectedimage">
                Expected Image <br />
                <img src='data:image/png;base64,<?php echo base64_encode($failedTest->getComparisonResult()->getExpectedImage()->getImageBlob()); ?>' />
            </div>

            <div class="currentimage">
                Current Image <br />
                <img src='data:image/png;base64,<?php echo base64_encode($failedTest->getComparisonResult()->getCurrentImage()->getImageBlob()); ?>' />
            </div>


        <?php endforeach; ?>

    </body>
</html>