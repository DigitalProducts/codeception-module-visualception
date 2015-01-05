<!DOCTYPE html>
<html>
<head>
    <title>VisualCeption Report</title>

    <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300,700' rel='stylesheet' type='text/css'>

    <link href="http://wordpress.ci.guj.de/tools/slider2/css/foundation.css" rel="stylesheet" type="text/css" />
    <link href="http://wordpress.ci.guj.de/tools/slider2/css/twentytwenty.css" rel="stylesheet" type="text/css" />

</head>

    <body style="margin:0; padding: 0; background-color: white">

        <div style="margin-bottom: 100px; padding-top: 50px; text-align: center; width: 100%; background-color: #db5179; height: 150px"><img src="http://drupal.eltern.de/sites/all/themes/cp_eltern/logo.png"></div>

        <div style="margin: 0 auto; width: 1200px">

            <p style="margin-bottom: 100px; font-family: verdana; line-height: 21px; color: #AAA; font-size: 12px">
                Dieser Report wurde mit VisualCeption erstellt. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.
            </p>

        <?php foreach ($failedTests as $failedTest): ?>

            <div class="twentytwenty-container">
                <img src='data:image/png;base64,<?php echo base64_encode($failedTest->getComparisonResult()->getExpectedImage()->getImageBlob()); ?>'/>
                <img id="<?php echo $failedTest->getIdentifier() ?>" src='data:image/png;base64,<?php echo base64_encode($failedTest->getComparisonResult()->getCurrentImage()->getImageBlob()); ?>'/>
            </div>

            <div style="width: 100%; text-align: center; border-bottom: 1px solid #AAA; padding-bottom: 75px; margin-top: 75px; margin-bottom: 75px" >
                <button style="border: 1px solid #999; background-color: #E6E6E6; text-decoration: none; border-radius: 2px; padding: .5em 1em;" onclick="updateExpectedImage('<?php echo $failedTest->getStorage()->getStorageFile($failedTest->getIdentifier()) . "', '" . $failedTest->getIdentifier(); ?>')">
                    Rechtes Bild &uumlbernehmen
                </button>
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

        <!--script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script-->
        <script src="http://wordpress.ci.guj.de/tools/slider2/js/jquery.event.move.js"></script>
        <script src="http://wordpress.ci.guj.de/tools/slider2/js/jquery.twentytwenty.js"></script>
        <script>
            $(window).load(function(){
                $(".twentytwenty-container[data-orientation!='vertical']").twentytwenty({default_offset_pct: 0.5});
                $(".twentytwenty-container[data-orientation='vertical']").twentytwenty({default_offset_pct: 0.5, orientation: 'vertical'});
            });
        </script>

    </body>
</html>