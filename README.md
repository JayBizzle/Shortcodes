<p align="center">
<a href="https://github.com/JayBizzle/Shortcodes/actions"><img src="https://img.shields.io/github/actions/workflow/status/JayBizzle/Shortcodes/tests.yml?branch=master&style=flat-square" /></a>
<a href="https://packagist.org/packages/jaybizzle/Shortcodes"><img src="https://img.shields.io/packagist/dm/JayBizzle/Shortcodes.svg?style=flat-square" /></a>
<a href="https://github.com/JayBizzle/Shortcodes"><img src="https://img.shields.io/badge/license-MIT-ff69b4.svg?style=flat-square" /></a>
<a href="https://packagist.org/packages/jaybizzle/Shortcodes"><img src="https://img.shields.io/packagist/v/jaybizzle/Shortcodes.svg?style=flat-square" /></a>
</p>

# Shortcodes

A PHP library for parsing WordPress/BBCode style shortcodes. Define your own shortcode tags and transform them into whatever output you need.

```bbcode
[style color=#FF0000]Red Text[/style]
```

becomes...

```html
<span style="color:#FF0000;">Red Text</span>
```

## Requirements

- PHP 8.1 or higher

## Installation

```bash
composer require jaybizzle/shortcodes
```

## Quick Start

### 1. Create a Shortcode Class

Each shortcode needs a handler class that extends `Shortcode` and implements a `parse()` method.

```php
<?php

namespace App\Shortcodes;

use Jaybizzle\Shortcodes\Shortcode;

class BoldShortcode extends Shortcode
{
    public static $shortcode = 'bold';

    public function parse()
    {
        return "<strong>{$this->content}</strong>";
    }
}
```

### 2. Register and Parse

```php
use App\Shortcodes\BoldShortcode;
use Jaybizzle\Shortcodes\Shortcodes;

$shortcodes = new Shortcodes;
$shortcodes->add(BoldShortcode::class);

echo $shortcodes->parse('This is [bold]important[/bold] text.');
// Output: This is <strong>important</strong> text.
```

## Defining Shortcodes

Every shortcode class must:
- Extend `Jaybizzle\Shortcodes\Shortcode`
- Set a static `$shortcode` property (the tag name)
- Implement a `parse()` method that returns a string

### Accessing Attributes

Attributes passed to a shortcode are available via `$this->attributes` or as magic properties via `$this->attributeName`.

```php
class VideoShortcode extends Shortcode
{
    public static $shortcode = 'video';

    public function parse()
    {
        $width = $this->attributes['width'] ?? 640;
        $height = $this->attributes['height'] ?? 480;
        $src = $this->attributes['src'] ?? '';

        return "<video width=\"{$width}\" height=\"{$height}\" controls>
            <source src=\"{$src}\" type=\"video/mp4\">
        </video>";
    }
}
```

```bbcode
[video src="/videos/demo.mp4" width=320 height=240]
```

### Accessing Content

Content between opening and closing tags is available via `$this->content`.

```php
class AlertShortcode extends Shortcode
{
    public static $shortcode = 'alert';

    public function parse()
    {
        $type = $this->attributes['type'] ?? 'info';

        return "<div class=\"alert alert-{$type}\">{$this->content}</div>";
    }
}
```

```bbcode
[alert type="warning"]Please read the documentation.[/alert]
```

### Magic Properties

You can access attributes directly as properties on the shortcode instance. Accessing an attribute that doesn't exist will throw an exception.

```php
class LinkShortcode extends Shortcode
{
    public static $shortcode = 'link';

    public function parse()
    {
        return "<a href=\"{$this->url}\">{$this->content}</a>";
    }
}
```

```bbcode
[link url="https://example.com"]Click here[/link]
```

## Shortcode Syntax

The following shortcode formats are all supported:

```bbcode
[tag]                          Self-closing (no attributes)
[tag /]                        Self-closing (explicit)
[tag]content[/tag]             With content
[tag foo=bar]                  Unquoted attribute
[tag foo="bar"]                Double-quoted attribute
[tag foo='bar']                Single-quoted attribute
[tag foo=bar baz="qux"]       Multiple attributes
[tag foo bar]                  Positional attributes (numeric keys)
[[tag]]                        Escaped (outputs [tag] literally)
```

## Registering Shortcodes

### Single Registration

```php
$shortcodes->add(BoldShortcode::class);
```

### Bulk Registration

```php
$shortcodes->add([
    BoldShortcode::class,
    VideoShortcode::class,
    AlertShortcode::class,
    LinkShortcode::class,
]);
```

Tag names with shared prefixes (e.g. `image` and `image-url`) are handled automatically — longer tag names are always matched first regardless of registration order.

## API Reference

### `add(string|array $classes): void`

Register one or more shortcode handler classes.

```php
$shortcodes->add(BoldShortcode::class);
$shortcodes->add([BoldShortcode::class, LinkShortcode::class]);
```

### `remove(string $tag): void`

Remove a registered shortcode by tag name.

```php
$shortcodes->add(BoldShortcode::class);
$shortcodes->remove('bold');

// [bold] tags will no longer be parsed
```

### `removeAll(): void`

Remove all registered shortcodes.

```php
$shortcodes->removeAll();
```

### `parse(string $content): string`

Parse content and replace all registered shortcode tags with their output.

```php
$result = $shortcodes->parse('Hello [bold]world[/bold]!');
```

### `stripShortcodes(string $content): string`

Remove all registered shortcode tags from the content without replacing them.

```php
$shortcodes->add(BoldShortcode::class);

$result = $shortcodes->stripShortcodes('Hello [bold]world[/bold]!');
// Output: Hello  world !
```

### `stripShortcode(string $shortcode, string $content): string`

Remove a specific shortcode tag from the content. Does not require the shortcode to be registered.

```php
$result = $shortcodes->stripShortcode('bold', 'Hello [bold]world[/bold] and [link url=#]click[/link]!');
// Output: Hello  world  and [link url=#]click[/link]!
```

### `getShortcode(string $shortcode, string $content): array`

Extract attributes from all instances of a specific shortcode tag in the content. Does not require the shortcode to be registered.

```php
$attributes = $shortcodes->getShortcode('video', 'Watch [video id=1] and [video id=2]');
// Returns: [['id' => '1'], ['id' => '2']]
```

### `getShortcodes(string $content): array`

Extract attributes from all registered shortcode tags found in the content.

```php
$shortcodes->add([VideoShortcode::class, ImageShortcode::class]);

$all = $shortcodes->getShortcodes('[video id=1] and [image src="photo.jpg"]');
// Returns: ['video' => [['id' => '1']], 'image' => [['src' => 'photo.jpg']]]
```

## Full Example

```php
<?php

use Jaybizzle\Shortcodes\Shortcode;
use Jaybizzle\Shortcodes\Shortcodes;

class StyleShortcode extends Shortcode
{
    public static $shortcode = 'style';

    public function parse()
    {
        $color = $this->attributes['color'] ?? 'inherit';
        $size = $this->attributes['size'] ?? 'inherit';

        return "<span style=\"color:{$color}; font-size:{$size};\">{$this->content}</span>";
    }
}

$shortcodes = new Shortcodes;
$shortcodes->add(StyleShortcode::class);

$input = 'This is [style color=#FF0000]red[/style] and [style color=#0000FF size=20px]large blue[/style] text.';
$output = $shortcodes->parse($input);

// Output: This is <span style="color:#FF0000; font-size:inherit;">red</span> and <span style="color:#0000FF; font-size:20px;">large blue</span> text.
```

## License

MIT
