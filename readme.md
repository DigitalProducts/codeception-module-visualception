# VisualCeption
Visual regression tests integrated in [Codeception](http://codeception.com/).

This module can be used to compare the current representation of a website element with an expeted. It was written on the shoulders of codeception and integrates in a very easy way.

**Example**
![](http://www.thewebhatesme.com/VisualCeption/compare.png)

## How it works

VisualCeption uses a combination of the "make a screenshot" feature in webdriver, imagick and jquery to compare visual elements on a website. This comparison is done in four steps:

1. **Take a screenshot** of the full page using webdriver.
2. **Calculate the position** and size of the selected element using jquery.
3. **Crop the element** out of the full screenshot using imagick.
4. **Compare the element** with an older version of the screenshot that has been proofed as valid using imagick. If no previous image exists the current image will be used fur future comparions. As an effect of this approach the test has to be **run twice** before it works.
5. If the deviation is too high **throw an exception** that is caught by Codeception.

## Documenation todo
* phantom css
* example image

## Requirements

VisualCeption needs the following components to run:

* **Codeception** VisualCeption is a module for [Codeception](http://codeception.com/). It will need a running version of this tool.
* **Imagick** For comparing two images VisualCeption is using the imagick library for php. For more information visit [php.net](http://www.php.net/manual/de/book.imagick.php) or the [installation guide](http://www.php.net/manual/en/imagick.setup.php).
* **WebDriver module** This tool does only work with the webdriver module in Codeception the moment.

## Installation

### Bootstrap
Add the module to <code>_bootstrap.php</code>. 

<code>include_once "/path/to/module/VisualCeption.php";</code>

### Configuration

To use the VisualCeption module you have to configure it. 

**Example Configuration**
```yaml
modules:
    enabled: [WebDriver, VisualCeption]
    
VisualCeption:
    referenceImageDir: /home/codeception/referenceImages/ # Path to the reference folder
    maximumDeviation: 5                                   # deviation in percent
```

* **referenceImageDir** VisualCeption uses an "old" image for calculating the deviation. These images have to be stored in the system. This is the corresponding directory.
* **maximumDeviation** When comparing two images the deviation will be calculated. If this deviation is greater than the maximum deviation the test will fail. 

## Usage

VisualCeption is really easy to use. There is only one method that will be added to your WebGuy <code>compareScreenshot</code>. This will be used to name the screenshot and identify the elements that has to be screenshot.  

```php
$I->compareScreenshot( "uniqueIdentifier", "elementId" );
```

* **uniqueIdentifier** For comparing the images it is important to have a stable name. This is the corresponding name.
* **elementId** It is possible to only compare a special div container. The element id can be passed. *You can use all locators that can be used in jQuery*. 

**Example Usage**
```php
$I->compareScreenshot( "subNavigation", "#subNav" );
```

## Restriction

VisualCeption uses the WebDriver module for making the screenshots. As a consequence we are not able to take screenshots via google chrome as the chromedriver does not allow full page screenshots.
