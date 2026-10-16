# Release Notes for Entry Anchors

## 1.0.1 - 2026-10-16

### Changed
- The field type is called "Anchor Text" (was "Anchor Link"): the field is the block's title, with an anchor to link to. Existing fields keep working, only the name in the field type list changes
- Choosing an anchor in the "URL/Anchor" dropdown fills the Link field's label with the anchor's title (as in the dropdown) instead of leaving `#anchor` as the label. A label the editor typed is kept

## 1.0.0 - 2026-10-14

### Changed
- Anchor Link fields have two subtle copy buttons: the link to the block after the title, the anchor on the anchor line
- README screenshots (in `docs/images`, left out of the Composer package)

### Removed
- The `copyFormat` setting; a `copyFormat` in `config/entry-anchors.php` is ignored

## 1.0.0-beta.4 - 2026-10-14

### Changed
- The anchor editor below the title has an "Apply" and a "Cancel" button: the anchor only changes when the edit is applied (Enter applies, Escape cancels)
- The copy button shows an anchor icon when it copies only the anchor (`copyFormat: 'anchor'`), a link icon when it copies the full URL

### Fixed
- The copy button's icon wasn't shown

## 1.0.0-beta.3 - 2026-10-14

### Added
- The Anchor Link field stores the anchor next to the title: it follows the title while the block isn't published yet and is kept after that, so links keep working when the title changes. Editors can change it below the title.
- `anchor_id()` Twig function: the id for a block's heading, from the stored anchor and unique on the page

### Changed
- Use `anchor_id(block.blockTitle)` for heading ids instead of a slug of the title. Values from before (only a title) get the slug of that title as anchor, so their ids don't change.

## 1.0.0-beta.2 - 2026-10-14

### Added
- Anchor dropdown below the URL input of Link fields whose URL type allows anchors, with the anchors of the page the field is on

### Changed
- The URL link type is called "URL/Anchor" on Link fields whose URL type allows anchors

### Fixed
- The link type select of a Link field stays top-aligned when the anchor dropdown wraps below the entry select

## 1.0.0-beta.1 - 2026-10-14

### Added
- Anchor Link field type, with a copy button for the link to the block
- Anchor dropdown on Link fields with the "Entry" link type and the "URL Suffix" advanced field
- `copyFormat`, `maxDepth` and `fields` settings
- Installing takes over the Anchor Link fields of the Statik base install's module
- Plugin icon
