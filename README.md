gscrap
======

A wrapper class for functions coded by Justone at http://google-scraper.squabbel.com/ to scrap google.


Usage
=====
Example :
```php
<?php
    $google = new gscrap();
    //set keywords
    $google->main_keyword="inurl:forum";
    $google->extra_keywords="atom";
  //only get the main output
    $google->showAll=false;
    //If you have license from seo-proxies.com
    $google->$isProxyAvailable=true; //default is true
	/* then set
	$google->pwd="YOUR PASSWORD"
	$google->uid ="YOUR UID"
	*/
	/*
	If $google->getliHTML=true; then Output will be li items just as in from google
	*/
    echo $google->getData();
?>
```



