# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-10-07

### Added

- `block:set` and `block:push` write blocks from content element and plugin templates.
- `block:get` prints a block in the page template, in any render order, with a fallback.
- Slots are filled before the page is cached, so cached pages contain the final HTML.
- Mails sent with `FluidEmail` fill their own slots and never see the blocks of the page.
- An error page rendered as a sub-request starts with its own empty blocks.
- Support for TYPO3 13.4 and 14.3 on PHP 8.2 and newer.
