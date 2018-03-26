<p align="center">
<a href="https://travis-ci.org/JayBizzle/Shortcodes"><img src="https://img.shields.io/travis/JayBizzle/Shortcodes/master.svg?style=flat-square" /></a>
<a href="https://packagist.org/packages/jaybizzle/Shortcodes"><img src="https://img.shields.io/packagist/dm/JayBizzle/Shortcodes.svg?style=flat-square" /></a>
<a href="https://scrutinizer-ci.com/g/JayBizzle/Shortcodes/?branch=master"><img src="https://img.shields.io/scrutinizer/g/JayBizzle/Shortcodes.svg?style=flat-square" /></a>
<a href="https://github.com/JayBizzle/Shortcodes"><img src="https://img.shields.io/badge/license-MIT-ff69b4.svg?style=flat-square" /></a>
<a href="https://packagist.org/packages/jaybizzle/Shortcodes"><img src="https://img.shields.io/packagist/v/jaybizzle/Shortcodes.svg?style=flat-square" /></a>
<a href="https://styleci.io/repos/95598948"><img src="https://styleci.io/repos/95598948/shield" /></a>
<a href="https://coveralls.io/github/JayBizzle/Shortcodes"><img src="https://img.shields.io/coveralls/JayBizzle/Shortcodes/master.svg?style=flat-square" /></a>
</p>

# Shortcodes

Shortcodes is a PHP library that will help parse WordPress/BBCode style shortcodes. It can turn something like this...

```bbcode
[style color=#FF0000]Red Text[/style]
```

into this...

```html
<span style="color:#FF0000;">Red Text</span>
```

The output is not predefined, it is up to you to define how the output is handles. See below for examples.

## Installation
```
composer require jaybizzle/shortcodes
```

## Getting started *** (WIP) ***
Let's take a simple example. We want to create a Shortcode for `video` elements. We want to be able to write something like this...
```bbcode
[video title="My Awesome Video" videoID=345 width=320 height=240]
```
and make it output something like...
```html
<video width="320" height="240" controls>
    <source src="/videos/video-345.mp4" type="video/mp4">
    <source src="/videos/video-345.ogg" type="video/ogg">
    Your browser does not support the video tag.
</video>
```

Firstly, we need to create a class that is going to handle the parsed Shortcode and it's attributes. We create a new class as follows...
```php
<?php

namespace App\Shortcodes;

use Jaybizzle\Shortcodes\Shortcode;

class VideoShortcode extends Shortcode
{
    public function parse()
    {
        // All shortcode attributes will be available in $this->attr
        // i.e. given the example above...
        // $this->attr['title']
        // $this->attr['videoID']
        // $this->attr['width']
        // $this->attr['height']
    }
}
```

Next, we need to add this Shortcode parse class to the Shortcodes library like this...
```php
<?php

namespace App\Libraries;

use App\Shortcodes\VideoShortcode;
use Jaybizzle\Shortcodes\Shortcodes;

class MyClass
{
    public function index()
    {
        $shortcodes = new Shortcodes
        $shortcodes->add('video', VideoClass::class);
    }
}
```
