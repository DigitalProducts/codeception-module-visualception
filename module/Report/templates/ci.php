<!DOCTYPE html>
<html>
<head>
    <title>VisualCeption Report</title>

    <script type="text/javascript"><?php @include(__DIR__."/helper/js/jquery.min.js"); ?></script>

    <style type="text/css"><?php @include(__DIR__."/helper/css/openSans.css"); ?></style>
    <style type="text/css"><?php @include(__DIR__."/helper/css/foundation.css"); ?></style>
    <style type="text/css"><?php @include(__DIR__."/helper/css/twentytwenty.css"); ?></style>

</head>

    <body style="margin:0; padding: 0; background-color: white">

        <div style="margin-bottom: 50px; padding-top: 50px; text-align: center; width: 100%; background-color: <?php echo $vars['color']; ?>; height: 150px">
            <?php if(file_exists($vars['logo']) || parse_url($vars['logo'] !== false) ): ?>
            <img src="<?php echo $vars['logo']; ?>">
            <? endif; ?>
        </div>

        <div style="margin: 0 auto; width: 1200px">

        <?php if(!empty($vars['text'])): ?>
            <p style="margin-bottom: 50px; font-family: verdana; line-height: 21px; color: #AAA; font-size: 12px">
                <?php echo htmlentities($vars['text']); ?>
                <br/>
                <small>Dieser Report wurde mit VisualCeption erstellt.</small>
            </p>
        <?php endif; ?>

        <?php if (!empty($failedTests)): ?>
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
        <?php else: ?>
            <div style="width: 100%; text-align: center; border-bottom: 1px solid #AAA; padding-bottom: 75px; margin-top: 75px; margin-bottom: 75px" >
                ALLE TESTS FEHLERFREI
            </div>
        <?php endif; ?>

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

        <script type="text/javascript"><?php @include(__DIR__."/helper/js/jquery.event.move.js"); ?></script>
        <script type="text/javascript"><?php @include(__DIR__."/helper/js/jquery.twentytwenty.js"); ?></script>
        <script>
            $(window).load(function(){
                $(".twentytwenty-container[data-orientation!='vertical']").twentytwenty({default_offset_pct: 0.5});
                $(".twentytwenty-container[data-orientation='vertical']").twentytwenty({default_offset_pct: 0.5, orientation: 'vertical'});
            });
        </script>

    </body>
</html>
