<?php

       header('content-type: application/json; charset=utf-8');
       header("access-control-allow-origin: *");

       $path = $_SERVER["GENDATA_PATH"] . DIRECTORY_SEPARATOR . $_GET["userId"];
       mkdir( $path, 0777, true);
       $filename = $path . DIRECTORY_SEPARATOR . $_GET["imageId"] . ".png";
       if( $_REQUEST["image"] != "" ) {
             $result = file_put_contents($filename,  base64_decode($_REQUEST["image"]));
             echo "done";
             var_dump(error_get_last());
       }else{
             header("Content-type: image/png");

             if( file_exists($filename)) {
                echo file_get_contents($filename);
             }else{
                $img = ImageCreate(1,1 );
                $black = ImageColorAllocate($img, 0, 0, 0);
                ImageFill($img, 0, 0, ImageColorAllocate($img, 255, 255, 255));
                ImagePNG($img);
             }
       }
