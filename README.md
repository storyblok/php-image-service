<div align="center">
    <h1 align="center">Storyblok Image Service</h1>
    <p align="center">Co-created with <a href="https://sensiolabs.com/">SensioLabs</a>, the creators of Symfony.</p>
</div>

| Branch    | PHP                                                                                                                                                                    | Code Coverage                                                                                                                      |
|-----------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------|------------------------------------------------------------------------------------------------------------------------------------|
| `master`  | [![PHP](https://github.com/storyblok/php-image-service/actions/workflows/ci.yaml/badge.svg)](https://github.com/storyblok/php-image-service/actions/workflows/ci.yaml) | [![codecov](https://codecov.io/gh/storyblok/php-image-service/graph/badge.svg)](https://codecov.io/gh/storyblok/php-image-service) |

> [!WARNING]
> This package is currently **experimental**. Breaking changes may be introduced with any release until a stable version is reached. Use with caution in production environments.

## Installation

```bash
composer require storyblok/php-image-service
```

## Usage

```php
use Storyblok\ImageService\Image;
use Storyblok\ImageService\Domain\Format;
use Storyblok\ImageService\Domain\Quality;

$image = new Image('https://a.storyblok.com/f/287488/1400x900/2fc896c892/image.jpg');

// Chain multiple operations
$url = $image
    ->resize(800, 600)
    ->format(Format::Webp)
    ->quality(new Quality(80))
    ->toString();
```

## Fluent Interface

The `Image` class supports a fluent interface, allowing you to chain multiple operations together. Each method returns a new `Image` instance, making the original instance immutable.

```php
use Storyblok\ImageService\Image;
use Storyblok\ImageService\Domain\Angle;
use Storyblok\ImageService\Domain\Format;
use Storyblok\ImageService\Domain\Quality;
use Storyblok\ImageService\Domain\Brightness;

$image = new Image('https://a.storyblok.com/f/287488/1400x900/2fc896c892/image.jpg');

// Chain as many operations as needed
$url = $image
    ->crop(100, 50, 800, 600)
    ->resize(400, 300)
    ->flipX()
    ->rotate(Angle::DEG_90)
    ->brightness(new Brightness(10))
    ->quality(new Quality(80))
    ->format(Format::Webp)
    ->grayscale()
    ->toString();

// The original image remains unchanged (immutability)
$original = new Image('https://a.storyblok.com/f/287488/1400x900/2fc896c892/image.jpg');
$resized = $original->resize(800, 600);
$withQuality = $resized->quality(new Quality(80));

// Each variable holds a different state:
// $original    -> original URL
// $resized     -> resized URL
// $withQuality -> resized + quality URL
```

## Available Operations

### Resize

Resize an image to specific dimensions. If only width or height is provided, the other dimension will be calculated to maintain the aspect ratio.

```php
use Storyblok\ImageService\Image;

$image = new Image('https://a.storyblok.com/f/287488/1400x900/2fc896c892/image.jpg');

// Resize to specific dimensions
$image->resize(800, 600);

// Resize by width only (height auto-calculated)
$image->resize(800, 0);

// Resize by height only (width auto-calculated)
$image->resize(0, 600);
```

### Fit-In

Fit the image within a given width and height while maintaining aspect ratio. The dimensions cannot exceed the original image dimensions.

```php
$image->fitIn(800, 600);
```

### Crop

Crop the image by specifying the top-left and bottom-right coordinates.

```php
// Crop from position (100, 100) to (500, 400)
$image->crop(100, 100, 500, 400);

// Crop from top-left (0, 0) to specific point
$image->crop(0, 0, 500, 400);
```

### Format

Convert the image to a different format.

```php
use Storyblok\ImageService\Domain\Format;

$image->format(Format::Webp);
$image->format(Format::Jpeg);
$image->format(Format::Png);
$image->format(Format::Avif);
```

### Quality

Set the image quality (0-100).

```php
use Storyblok\ImageService\Domain\Quality;

$image->quality(new Quality(80));
```

### Blur

Apply a blur effect with radius (0-150) and sigma (0-150).

```php
use Storyblok\ImageService\Domain\Blur;

// Apply blur with radius and sigma
$image->blur(new Blur(10, 5));

// Radius only (sigma must be 0 when radius is 0)
$image->blur(new Blur(10, 0));
```

### Brightness

Adjust image brightness (-100 to 100).

```php
use Storyblok\ImageService\Domain\Brightness;

// Increase brightness
$image->brightness(new Brightness(50));

// Decrease brightness
$image->brightness(new Brightness(-30));
```

### Rotate

Rotate the image by a specific angle. Only 0, 90, 180, and 270 degrees are supported.

```php
use Storyblok\ImageService\Domain\Angle;

$image->rotate(Angle::DEG_90);
$image->rotate(Angle::DEG_180);
$image->rotate(Angle::DEG_270);
```

### Flip

Flip the image horizontally or vertically.

```php
// Flip horizontally
$image->flipX();

// Flip vertically
$image->flipY();

// Flip both
$image->flipX()->flipY();
```

### Grayscale

Convert the image to grayscale.

```php
$image->grayscale();
```

### Focal Point

Set a focal point for smart cropping.

```php
use Storyblok\ImageService\Domain\FocalPoint;

// Create from coordinates
$image->focalPoint(new FocalPoint(100, 100, 300, 300));

// Create from string (e.g., from Storyblok asset focus field)
$image->focalPoint(FocalPoint::fromString('719x153:720x154'));
```

### Rounded Corners

Apply rounded corners to the image.

```php
use Storyblok\ImageService\Domain\RoundedCorner;

// Simple rounded corners with radius
$image->roundedCorners(new RoundedCorner(20));

// With ellipsis for elliptical corners
$image->roundedCorners(new RoundedCorner(20, 10));

// With custom background color (RGB)
$image->roundedCorners(new RoundedCorner(20, null, 255, 0, 0));

// With transparent background
$image->roundedCorners(new RoundedCorner(20, null, 255, 255, 255, true));
```

### Fill

Set a fill color for fit-in operations.

```php
use Storyblok\ImageService\Domain\HexCode;
use Storyblok\ImageService\Domain\Transparent;

// Fill with hex color
$image->fitIn(800, 600)->fill(new HexCode('#FF0000'));
$image->fitIn(800, 600)->fill(new HexCode('FF0000'));
$image->fitIn(800, 600)->fill(new HexCode('#F00'));

// Fill with transparent
$image->fitIn(800, 600)->fill(new Transparent());
```

### No Upscale

Prevent the image from being upscaled beyond its original dimensions.

```php
$image->resize(2000, 2000)->noUpscale();
```

## Limitations

The following limitations apply:

- **URL Format**: The image URL must contain dimensions in the format `/{width}x{height}/` (e.g., `/1400x900/`). URLs without this pattern will throw an `InvalidArgumentException`.
- **Rotation Angles**: Only 0, 90, 180, and 270 degree rotations are supported. Arbitrary angles are not available.
- **Fit-In Dimensions**: When using `fitIn()`, the width and height cannot exceed the original image dimensions.
- **Blur Constraints**: The blur sigma cannot be set to a value greater than 0 when the radius is 0.
- **Quality Range**: Quality must be between 0 and 100.
- **Brightness Range**: Brightness must be between -100 and 100.
- **Blur Range**: Both radius and sigma must be between 0 and 150.
- **RGB Values**: For rounded corners, RGB values must be between 0 and 255.

## License

This project is licensed under the MIT License. Please see [License File](LICENSE) for more information.
