# Release Notes for Entry Anchors

## Unreleased

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
