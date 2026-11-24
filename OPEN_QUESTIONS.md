# Open questions

Every question on this page is one only an outside human can answer, so each row names who owns it, what waits on the answer, and the default the code already ships. A question never holds a release: Pinstripe tags with the default in place, and the row is updated when the owner answers.

## Known open at 1.0

These are not 1.0 gates; Pinstripe 1.0 ships with the default shown until the owner answers.

| # | Question | Who can answer | What it blocks | Default built |
| --- | --- | --- | --- | --- |
| 1 | PS-19: run Psalm taint analysis, and stand up the GitLab tier it needs | the stack maintainer | a taint-analysis tier in the gate | not run; Pint, PHPStan and the banned-calls scan are the gate |
| 2 | PS-26: evaluate Mago and PHPCS 4 | the stack maintainer | adopting either tool in the presets | neither is used; Pint plus PHPStan |
| 3 | PS-18: add a core-and-adapter boundary check | the stack maintainer | a check that the core-and-adapter layout holds | no tooling; the layout is documented |
| 4 | PS-10: run a Rector pilot on a real app and record it in the docs | the stack maintainer | a real-app record of the Rector set | the Rector set is shipped and tested on fixtures only |
| 5 | Re-run the proof on the internal 82-app corpus (last run 2026-11-05, see docs/reference/proof.md) | the stack maintainer | a proof run that covers the presets since 2026-11-05 | the 2026-11-05 run stands |
| 6 | Decide the "Not banned yet" list: md5(), sha1(), rand(), mt_rand() (see README.md, "Not banned yet") | the stack maintainer | banning those calls, and the rule that would allow their safe uses | they stay advisory, not banned |
