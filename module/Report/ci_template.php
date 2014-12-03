<?php $i = 0; ?>
<!DOCTYPE html>
<html>
    <head>
        <title>VisualCeption Report</title>
    </head>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <body>

        <img src="http://www.thewebhatesme.com/wp-content/uploads/visualception.png" />

        <?php foreach ($failedTests as $failedTest): ?>
            <?php $i++; ?>
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
                <img id="<?php echo $failedTest->getIdentifier() ?>" src='data:image/png;base64,<?php echo base64_encode($failedTest->getComparisonResult()->getCurrentImage()->getImageBlob()); ?>' />
                <button onclick="updateExpectedImage('<?php echo $failedTest->getStorage()->getStorageFile($failedTest->getIdentifier()) . "', '" . $failedTest->getIdentifier(); ?>')">set as expected image</button>
            </div>
        <?php endforeach; ?>


    <script>
        function updateExpectedImage(imageUrl, identifier)
        {
            imageData = ($('#' + identifier).attr('src').replace(/^data:image\/(png|jpg);base64,/, ""));
            $.post( imageUrl, { 'image': imageData })
              .done(function( data ) {
                alert( "Updated." );
              });
        }
    </script>

    </body>
</html>