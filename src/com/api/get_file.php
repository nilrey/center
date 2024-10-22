<?php
$filename = 'testfile.docx';
$filepath = $_SERVER["DOCUMENT_ROOT"] . "/com/api/{$filename}";
$attachment_location = $filepath;
// var_dump($filepath);
        if (file_exists($attachment_location)) {

            header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
            header("Cache-Control: public"); // needed for internet explorer
            header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
            header("Content-Transfer-Encoding: Binary");
            header("Content-Length:".filesize($attachment_location));
            header("Content-Disposition: attachment; filename={$filename}");
            readfile($attachment_location);
            die();        
        } else {
            // die("Error: File not found.");
        } 
?>