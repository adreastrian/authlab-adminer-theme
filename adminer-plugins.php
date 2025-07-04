<?php

return [
    new LoadDependencies, // Always load it first.
    new AdminerDatabaseHide([
        'information_schema',
        'mysql',
        'performance_schema',
        'sys',
    ]),
    new AdminerEditTextarea,
    new AdminerEnumOption,
    new AdminerEditForeign,
    new AdminerTablesFilter,
    // new AdminerFloatThead,
    // new AdminerMenuScroller,
    new AdminerJsonPreview,
    new AdminerCollations,
    new FixedFooter,
    new AutoLogin,
    new AdminerDarkSwitcher,
];
