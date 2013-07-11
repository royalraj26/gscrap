gscrap
======

<<<<<<< HEAD
A wrapper class for functions coded by http://google-scraper.squabbel.com/ to scrap google.
=======
A wrapper class for functions coded by Justone at http://google-scraper.squabbel.com/ to scrap google.
>>>>>>> 2599999a1eacb5f4b44c13dd7cbced8ccd432630

Usage
=====
Example :
<<<<<<< HEAD
=======
```php
>>>>>>> 2599999a1eacb5f4b44c13dd7cbced8ccd432630
<?php
    $google = new gscrap();
    //set keywords
    $google->main_keyword="inurl:forum";
    $google->extra_keywords="atom";
<<<<<<< HEAD
	//only get the main output
=======
  //only get the main output
>>>>>>> 2599999a1eacb5f4b44c13dd7cbced8ccd432630
    $google->showAll=false;
	//If you have license from seo-proxies.com
    $google->$isProxyAvailable=true; //default is true
	/* then set
	$google->pwd="YOUR PASSWORD"
	$google->uid ="YOUR UID"
	*/
    echo $google->getData();
?>
<<<<<<< HEAD
=======
```
>>>>>>> 2599999a1eacb5f4b44c13dd7cbced8ccd432630
