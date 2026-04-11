# Flag Assets

This folder is the new home for tournament flag icons.

Recommended approach:

- Store one flag per file using SVG where possible.
- Use ISO alpha-2 codes for sovereign states, for example `de.svg`, `mx.svg`, `us.svg`.
- Use short custom slugs only where needed, for example `tbd.svg`. For the home nations in this project we use `gb-eng.svg` and `gb-sct.svg`.
- Keep the asset itself clean and high contrast; the UI will place it inside a fixed square container.

Resolver order used by the app:

1. `img/flags/<code-or-slug>.svg`
2. `img/flags/<code-or-slug>.png`

The app now resolves flags from this folder directly.
