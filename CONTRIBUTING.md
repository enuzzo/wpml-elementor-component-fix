# Contributing

Read [AGENTS.md](AGENTS.md) for repository invariants and [the development guide](docs/development.md) for the local workflow and validation commands.

Please include PHP, WordPress, WPML, WPML String Translation, Elementor and Elementor Pro versions when reporting an issue. Explain whether the property is an explicit instance override or an inherited default, and whether it is missing from a newly generated XLIFF export or fails only after import.

Use a draft page to reproduce the issue. A tiny synthetic `escaped-html` override example is more useful than a complete client export. Remove domains, personal information, tokens and client content. Do not upload proprietary WPML or Elementor source.

Run `php tests/run.php` and `python3 scripts/build.py`. PHP 7.4-compatible production code is required. Add a regression case for any supported contract change, and keep the native handler's identifiers and update behavior authoritative.

Pull requests and issue discussions are welcome. Please describe actual results separately from hypotheses.
