# Project Rules Index

Before planning or editing, find the row whose globs match the file's path and read that rule file.

| Applies to | Rule file |
| --- | --- |
| modules/** | .ai/rules/modules.md |
| modules/MoonLaunch/** | .ai/rules/moon-launch.md |
| modules/MoonOrbit/** | .ai/rules/moon-orbit.md |
| modules/**/MoonShine/** | .ai/rules/moon-shine.md |
| modules/MoonLaunch/Traits/** | .ai/rules/traits.md |
| tests/**/*.php, modules/**/tests/**/*.php | .ai/rules/tests.md |
| config/filesystems.php | .ai/rules/config.md |

Rules are in English and compacted: a rule must state a trap that fails silently or a fix that is
easy to get wrong. If you cannot name the breakage a rule prevents, delete the rule.