# ImageMagick Thumbnailer

## Requirements

ImageMagick Thumbnailer has following Requirements:

- phpBB Version greater or equal 3.2.4
- php [Imagick library](http://php.net/manual/en/imagick.installation.php) installed

## Features

- Uses the php library IMagick instead of the command line version of ImageMagick
- You can control the image quality of the generated thumbnail via acp setting page

## Installation

Copy the extension to phpBB/ext/canonknipser/imthumbnailer

Go to "ACP" > "Customise" > "Extensions" and enable the "ImageMagick Thumbnailer" extension.

## License

[GPLv2](license.txt)

## recent Changes

please visit the [Changelog](CHANGELOG.md) for a complete list of Changes 

### 1.0.1

- Improved error handling
- validation issues fixed
- rotate thumbnails according to their exif information 

### 1.0.0

- Initial Version