INSERT OR REPLACE INTO pages (uid, pid, title, slug, doktype, is_siteroot, sorting, crdate, tstamp) VALUES
    (1, 0, 'Home', '/', 1, 1, 256, strftime('%s', 'now'), strftime('%s', 'now')),
    (2, 1, 'Fallbacks', '/fallbacks', 1, 0, 256, strftime('%s', 'now'), strftime('%s', 'now')),
    (3, 1, 'Nested', '/nested', 1, 0, 512, strftime('%s', 'now'), strftime('%s', 'now')),
    (4, 1, 'Uncached element', '/uncached-element', 1, 0, 768, strftime('%s', 'now'), strftime('%s', 'now')),
    (5, 1, 'No cache', '/no-cache', 1, 0, 1024, strftime('%s', 'now'), strftime('%s', 'now')),
    (6, 1, 'Not found', '/not-found', 1, 0, 1280, strftime('%s', 'now'), strftime('%s', 'now')),
    (7, 1, 'Broken', '/broken', 1, 0, 1536, strftime('%s', 'now'), strftime('%s', 'now'));

DELETE FROM sys_template;
INSERT INTO sys_template (uid, pid, root, clear, title, constants, config, sorting, crdate, tstamp) VALUES
    (1, 1, 1, 3, 'fluid-blocks E2E', '', '@import ''EXT:fluid_blocks_e2e/Configuration/TypoScript/setup.typoscript''', 256, strftime('%s', 'now'), strftime('%s', 'now'));

DELETE FROM tt_content;
INSERT INTO tt_content (uid, pid, CType, header, bodytext, sorting, crdate, tstamp) VALUES
    (1, 1, 'Script', 'first script', '', 256, strftime('%s', 'now'), strftime('%s', 'now')),
    (2, 1, 'Stage', 'Welcome <b>home</b>', '', 512, strftime('%s', 'now'), strftime('%s', 'now')),
    (3, 1, 'BodyClass', 'home', '', 768, strftime('%s', 'now'), strftime('%s', 'now')),
    (4, 1, 'Script', 'second script', '', 1024, strftime('%s', 'now'), strftime('%s', 'now')),
    (10, 3, 'Nested', 'Nested stage', '', 256, strftime('%s', 'now'), strftime('%s', 'now')),
    (11, 3, 'Subline', 'Subline from a later element', '', 512, strftime('%s', 'now'), strftime('%s', 'now')),
    (20, 4, 'Stage', 'Cached stage', '', 256, strftime('%s', 'now'), strftime('%s', 'now')),
    (21, 4, 'Uncached', 'uncached script', 'Uncached element body', 512, strftime('%s', 'now'), strftime('%s', 'now')),
    (22, 4, 'Script', 'cached script', '', 768, strftime('%s', 'now'), strftime('%s', 'now')),
    (30, 5, 'Stage', 'Stage without page cache', '', 256, strftime('%s', 'now'), strftime('%s', 'now')),
    (31, 5, 'Script', 'no cache script', '', 512, strftime('%s', 'now'), strftime('%s', 'now')),
    (40, 7, 'Stage', 'Stage of the broken page', '', 256, strftime('%s', 'now'), strftime('%s', 'now')),
    (41, 7, 'Script', 'broken page script', '', 512, strftime('%s', 'now'), strftime('%s', 'now')),
    (42, 7, 'NotFound', '', '', 768, strftime('%s', 'now'), strftime('%s', 'now'));
