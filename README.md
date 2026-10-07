# Fluid Blocks

[![tests](https://img.shields.io/github/actions/workflow/status/wazum/fluid-blocks/tests.yml?branch=main&style=for-the-badge&logo=githubactions&logoColor=white&label=tests&labelColor=242733)](https://github.com/wazum/fluid-blocks/actions/workflows/tests.yml) [![TYPO3 13.4 and 14.3](https://img.shields.io/badge/TYPO3-13.4%20%7C%2014.3-ffb997?style=for-the-badge&logo=typo3&logoColor=white&labelColor=242733)](https://get.typo3.org) [![PHP 8.2 or newer](https://img.shields.io/badge/PHP-8.2%2B-c3b1e1?style=for-the-badge&logo=php&logoColor=white&labelColor=242733)](https://www.php.net) [![GPL-2.0-or-later licence](https://img.shields.io/badge/licence-GPL--2.0--or--later-ffc6d9?style=for-the-badge&logo=gnu&logoColor=white&labelColor=242733)](LICENSE)

Send HTML from a content element or plugin template to a named slot in the page template. The slot can be anywhere in the page template, before or after the content that fills it.

## Why

In TYPO3 the page template is rendered first, and content elements and plugins are rendered as separate Fluid views inside it. So they cannot change the stage, the breadcrumb, the body class or any other part of the page template.

TYPO3 already has tools for some of this: the page title (`PageTitleProviderInterface`), meta tags (`MetaTagManagerRegistry`), and scripts and styles (`f:asset.script`, `f:asset.css`). Use those for these cases. Fluid Blocks is for everything else: any HTML, written in Fluid, without PHP.

## Install

    composer require wazum/fluid-blocks

TYPO3 13.4 and 14.3, PHP 8.2 or newer. There is nothing to configure. The `block` namespace works in every template.

## Use

Content element or plugin template:

    <block:set name="stage">
        <h1>{data.header}</h1>
    </block:set>

    <block:push name="footerLinks">
        <a href="{link}">{data.header}</a>
    </block:push>

Page template:

    <header>
        <block:get name="stage"><h1>{page.title}</h1></block:get>
    </header>

    <f:for each="{content.main}" as="element">…</f:for>

    <footer>
        <block:get name="footerLinks" />
    </footer>

    <div class="{block:get(name: 'bodyClass', default: 'page')}">

## ViewHelpers

| ViewHelper | Arguments | What it does |
|---|---|---|
| `block:set` | `name` | Writes its children to the block. Replaces what was there, so the last writer wins. Prints nothing. |
| `block:push` | `name` | Adds its children to the end of the block, in render order. Prints nothing. |
| `block:get` | `name`, `default` | Prints the block. If nothing was written, it prints its children, or `default` when it has no children. |

Block names can use letters, digits, `.`, `-` and `_`. Other names throw an exception.

## How it works

If the block already exists, `get` prints it directly. If the slot comes before the content that fills it, it prints a placeholder (an HTML comment). After TYPO3 has rendered the page, and before it stores the page in the cache, the extension replaces every placeholder with its block or its fallback. TYPO3 uses the same idea for its USER_INT markers. Cached pages contain the final HTML, so they cost nothing extra.

## Good to know

- Do not pass the output of `get` to a ViewHelper that changes it, for example to crop or escape it. The placeholder would change and could not be replaced. The extension logs a warning with the block name when this happens.
- A slot inside a block is filled too, up to three levels deep.
- Blocks written by an uncached content element or plugin (USER_INT, COA_INT) come too late for the cached page. They do not show up in a slot.
- In a `FluidEmail` slots work in any order too. Each mail has its own blocks, apart from the page that sends it. This needs TYPO3 to render the mail when it is sent, which is the default. If code reads the mail body earlier, a slot that comes before its content keeps its placeholder.
- In other renders, for example on the command line or in the backend, slots print their fallback right away.

## License

GPL-2.0-or-later. Wolfgang Klinger <wolfgang@wazum.com>
