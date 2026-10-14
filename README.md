# Entry Anchors

Link to a block on a page. Editors give a block an anchor title, and Link fields get a dropdown with the anchors of the linked entry.

- **Anchor Link field**: a plain text field for a block's title. The front end renders the title with its slug as id, and the
  copy button next to the field copies the link to the block.
- **Anchor dropdown on Link fields**: when an editor picks an entry in a Link field, a dropdown next to it lists the anchors on
  that entry (the Anchor Link fields on the entry and its nested entries, in page order). The chosen anchor is stored in the
  Link field's URL suffix as `#slug`, so `link.url` ends with it. No template changes needed.

## Requirements

Craft CMS 5.10 or later, PHP 8.2 or later.

## Installation

```bash
composer require statikbe/craft-entry-anchors
php craft plugin/install entry-anchors
```

## Usage

### Anchor Link field

Create a field of the "Anchor Link" type (e.g. "Title (Anchor)") and add it to the entry types of your blocks. Render the title
with its slug as id:

```twig
{% if block.blockTitle|length %}
    <h2 id="{{ block.blockTitle|slugify }}">{{ block.blockTitle }}</h2>
{% endif %}
```

The slug must be Craft's `ElementHelper::generateSlug()`, the same as the anchors in the dropdown. The Statik base install has a
`|slugify` filter for this.

### Anchor dropdown on Link fields

Enable "URL Suffix" under "Advanced fields" in the settings of a Link field with the "Entry" link type. The dropdown appears
next to the entry select:

- picking another entry loads its anchors and clears the anchor;
- a saved anchor that no longer exists on the entry stays selected, marked "not found";
- typing `#something` in the URL suffix selects it in the dropdown, query params (`?p=1`) are left alone;
- switching to another link type clears the anchor.

The dropdown is added to Craft's Link field markup by JS. If a Craft update changes that markup, the dropdown stays away and
the anchor can still be typed in the URL suffix.

## Configuration

Create `config/entry-anchors.php`:

```php
<?php

return [
    // What the copy button of an Anchor Link field copies: 'url' (page URL + #slug) or 'anchor' (only #slug)
    'copyFormat' => 'url',
    // How deep to look into nested entries for anchors (content builder → row → cell → FAQ items, ...)
    'maxDepth' => 3,
    // Handles of the Matrix fields to look into for anchors (the handle in the field layout), null for every Matrix field
    'fields' => null,
];
```

## Coming from the Statik module

Installing the plugin takes over the fields of the module's Anchor Link type (`modules\statik\fields\AnchorLink`). Their
content stays as it is. Remove the field type, the anchor dropdown and their assets from the module in the same deploy.
