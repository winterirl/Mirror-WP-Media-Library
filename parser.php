<?php
/**
 * This script makes a local copy of the contents of a Wordpress Media Library XML File.
 * It mirrors the directory paths on the original server from where the export was made.
 * Re-Linking to the WPDB will be necessary after uploading these copied images via SFTP.
 * 
 * This script assumes a properly formatted UTF-8 XML file. 
 * Wordpress may occasionally export with invalid characters. 
 * The XMLReader will not handle them, however, it WILL point out the line these invalid characters are on.
 * Manual editing of the XML file will be necessary in that case. 
 * As long as the <wp:attachment_url> field is valid it will be fine, we do not care about the metadata and that is where isseus are likely to be.
 * 
 * This script does temporarily override the php execution time limit, but temporarily unlimiting memory usage in php.ini is recommended.
 * 
 * This script can be run multiple times without re-downloading & overwriting anything. 
 * If an error occures due to corrupted XML, fix the error and re-run this script. 
 * We're only using the attachment_url node so any non-UTF characters in a wp metadata node can be deleted without issues.
 */

//XML File Path - SET HERE ------------------
$xmlFile = 'path_to_WP_export_file.xml';
//-------------------------------------------

//Error Reporting - These three lines can be deleted if error reporting is not wanted. 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//Logging - These two lines can also be deleted if logging is not wanted.
$logFile = 'progress.log';
file_put_contents($logFile, "Starting processing of XML file: " . $xmlFile . "\n");

//Execution time limit override - this is necessary for large media libraries
set_time_limit(0);

//DOM Status Output
echo 'XML File is ' . $xmlFile . '<br>';
echo "Starting to load the XML file...<br>";

//Create XMLReader object from file path defined above
$xmlReader = new XMLReader();
if (!$xmlReader->open($xmlFile)) {
    $error = 'Failed to open XML file.';
    echo $error . "<br>";
    file_put_contents($logFile, $error . "\n", FILE_APPEND);
    exit();
}

while ($xmlReader->read()) {
    //Only process element nodes. XMLReader won't ignore the other nodes per se, but we do not need to execute this code if the node isn't attachment_url.
    if ($xmlReader->nodeType == XMLReader::ELEMENT && $xmlReader->localName == 'attachment_url') {
        $imageUrl = $xmlReader->readString();

        $parsedUrl = parse_url($imageUrl);
        $localPath = $_SERVER['DOCUMENT_ROOT'] . $parsedUrl['path'];

        //Skip over files that exist, but output what we're doing into the DOM
        if (file_exists($localPath)) {
            $message = "Skipping existing file: " . basename($localPath);
            echo $message . "<br>";
            file_put_contents($logFile, $message . "\n", FILE_APPEND);
            continue;
        }

        /**
         * Creates folders to mirror original wp-content/uploads directory. 
         * This also grants completely unlimited permissions, as this was originally intended to run in a local environment not accessible to the internet.
         */
        $directory = dirname($localPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        //Save the file in the proper directory
        $downloaded = file_put_contents($localPath, file_get_contents($imageUrl));

        //Output status of the above file saving into the DOM
        if ($downloaded) {
            $message = "Successfully downloaded: " . basename($localPath);
            echo $message . "<br>";
        } else {
            $message = "<strong style='color: red;'>Failed to download: " . basename($localPath) . "</strong>";
            echo $message . "<br>";
        }

        //Save status to log. Can be deleted if logging is not needed.
        file_put_contents($logFile, strip_tags($message) . "\n", FILE_APPEND);
    }
}

//Close XMLReader
$xmlReader->close();
file_put_contents($logFile, "Image processing completed.\n", FILE_APPEND);

//Confirm completion in DOM
echo "Image processing completed.";

?>
