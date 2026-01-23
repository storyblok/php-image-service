<div align="center">
    <img src="assets/php-image-service-github-repository.png" alt="Storyblok PHP Image Service" align="center" />
    <h1 align="center">Storyblok PHP Image Service</h1>
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

$image = new Image('https://a.storyblok.com/f/287488/1400x900/2fc896c892/image.jpg');

// Chain multiple operations
$url = $image
    ->resize(800, 600)
    ->format('webp')
    ->quality(80)
    ->toString();
```

## Fluent Interface

The `Image` class supports a fluent interface, allowing you to chain multiple operations together. Each method returns a new `Image` instance, making the original instance immutable.

```php
use Storyblok\ImageService\Image;

$image = new Image('https://a.storyblok.com/f/287488/1400x900/2fc896c892/image.jpg');

// Chain as many operations as needed
$url = $image
    ->crop(100, 50, 800, 600)
    ->resize(400, 300)
    ->flipX()
    ->rotate(90)
    ->brightness(10)
    ->quality(80)
    ->format('webp')
    ->grayscale()
    ->toString();

// The original image remains unchanged (immutability)
$original = new Image('https://a.storyblok.com/f/287488/1400x900/2fc896c892/image.jpg');
$resized = $original->resize(800, 600);
$withQuality = $resized->quality(80);

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

Fit the image within a given width and height while maintaining aspect ratio.

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

Convert the image to a different format. Supported formats: `webp`, `jpeg`, `png`, `avif`.

```php
$image->format('webp');
$image->format('jpeg');
$image->format('png');
$image->format('avif');
```

### Quality

Set the image quality (0-100).

```php
$image->quality(80);
```

### Blur

Apply a blur effect with radius (0-150) and optional sigma (0-150).

```php
// Apply blur with radius only
$image->blur(10);

// Apply blur with radius and sigma
$image->blur(10, 5);
```

### Brightness

Adjust image brightness (-100 to 100).

```php
// Increase brightness
$image->brightness(50);

// Decrease brightness
$image->brightness(-30);
```

### Rotate

Rotate the image by a specific angle. Only 0, 90, 180, and 270 degrees are supported.

```php
$image->rotate(90);
$image->rotate(180);
$image->rotate(270);
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

Set a focal point for smart cropping using a string in format `x1xy1:x2xy2`.

```php
// Set focal point coordinates
$image->focalPoint('100x100:300x300');

// Use value from Storyblok asset focus field
$image->focalPoint('719x153:720x154');
```

### Rounded Corners

Apply rounded corners to the image.

```php
// Simple rounded corners with radius
$image->roundedCorners(20);

// With ellipsis for elliptical corners
$image->roundedCorners(20, 10);

// With custom background color (RGB)
$image->roundedCorners(20, null, 255, 0, 0);

// With transparent background
$image->roundedCorners(20, null, 255, 255, 255, true);
```

### Fill

Set a fill color for fit-in operations. Accepts `transparent` or a hex color code.

```php
// Fill with hex color
$image->fitIn(800, 600)->fill('#FF0000');
$image->fitIn(800, 600)->fill('FF0000');
$image->fitIn(800, 600)->fill('#F00');

// Fill with transparent
$image->fitIn(800, 600)->fill('transparent');
```

### No Upscale

Prevent the image from being upscaled beyond its original dimensions.

```php
$image->resize(2000, 2000)->noUpscale();
```

### Get Image Metadata

Retrieve information about the image.

```php
$image = new Image('https://a.storyblok.com/f/287488/1400x900/2fc896c892/my-image.jpg');

// Get dimensions
$image->getWidth();     // 1400
$image->getHeight();    // 900

// Get file info
$image->getName();      // "my-image"
$image->getExtension(); // "jpg"

// Extension updates when format changes
$formatted = $image->format('webp');
$formatted->getExtension(); // "webp"
```

## Limitations

The following limitations apply:

- **URL Format**: The image URL must contain dimensions in the format `/{width}x{height}/` (e.g., `/1400x900/`). URLs without this pattern will throw an `InvalidArgumentException`.
- **Rotation Angles**: Only 0, 90, 180, and 270 degree rotations are supported. Arbitrary angles are not available.
- **Blur Constraints**: The blur sigma cannot be set to a value greater than 0 when the radius is 0.
- **Quality Range**: Quality must be between 0 and 100.
- **Brightness Range**: Brightness must be between -100 and 100.
- **Blur Range**: Both radius and sigma must be between 0 and 150.
- **RGB Values**: For rounded corners, RGB values must be between 0 and 255.
- **Format Values**: Format must be one of `webp`, `jpeg`, `png`, or `avif`.

## License

This project is licensed under the MIT License. Please see [License File](LICENSE) for more information.
