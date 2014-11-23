# InstagramDownload class

How many times you tried download that booty pic or awesome #nofilter or #travel photo on Instagram but had to go through hoops to save that file? This class can give you the download URL of Instagram photos and videos.

I wrote this class mainly for my [online Instagram photo and video downloader] [1], but I thought share this piece for your own extensions.

  - Validates Instagram URL (domain validation, URL path validation)
  - Uses OG properties to detect the image and video URL
  - (Somewhat) verbose error reporting
  

### Requirements
* PHP 5
* CURL
* Unicorn blood


### Thanks to:
* [MetaData][2] - Meta data parsing regex and curl class.

### Usage

```php
<?php
require_once 'InstagramDownload.class.php';
$url = 'http://instagram.com/p/tmwAlCGygb/';

$client = new InstagramDownload($url);
$url = $client->downloadUrl(); //Returns download URL.
$url = $client->downloadUrl(TRUE); //Returns download URL, with query parameters that downloads the image directly to browser.

$error = $client->getError(); // Returns error message's ($client->error_code) text error, if an error occurred.

$type = $client->type(); // Returns (string) 'image' or (string) video if an image or video was sucessfully extracted

```


[1]:http://downloadgram.com
[2]:https://github.com/baj84/MetaData

