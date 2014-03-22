# VisualCeption
Visual regression tests integrated in Codeception.

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

** Example Configuration **
```yaml
modules:
    enabled: [WebDriver, VisualCeption]
    
VisualCeption:
    referenceImageDir: /home/codeception/referenceImages/ # Path to the reference
    maximumDeviation: 5                                   # deviation in percent
```

* **referenceImageDir** VisualCeption uses an "old" image for calculating the deviation. These images have to be stored in the system. This is the corresponding directory.
* **maximumDeviation** When comparing two images the deviation will be calculated. If this deviation is greater than the maximum deviation the test will fail. 

## Usage

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

VisualCeption uses the WebDriver module for making the screenshots. As a consequence we are not able to take screenshots via google chrome as the crhomedriver does not allow full page screenshots.
