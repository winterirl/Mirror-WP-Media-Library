# Mirror-WP-Media-Library
This script makes a local copy of the contents of a Wordpress Media Library XML File.
It mirrors the directory paths on the original server from where the export was made.
Re-Linking to the WPDB will be necessary after uploading these copied images via SFTP.

--- THIS IS INTENDED TO BE RUN INSIDE A LOCAL PHP ENVIRONMENT, NOT AS A PLUGIN INSIDE OF WORDPRESS ----

This script assumes a properly formatted UTF-8 XML file. 
Wordpress may occasionally export with invalid characters. 
The XMLReader will not handle them, however, it WILL point out the line these invalid characters are on.
Manual editing of the XML file will be necessary in that case. 
As long as the <wp:attachment_url> field is valid it will be fine, we do not care about the metadata and that is where isseus are likely to be.

This script does temporarily override the php execution time limit, but temporarily unlimiting memory usage in php.ini is recommended. 
