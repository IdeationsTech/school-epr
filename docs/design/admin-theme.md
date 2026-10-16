# Super Admin reference theme

Super Admin uses the supplied charcoal reference: warm dark background, raised gray containers, deep soft shadows, teal/purple/coral/blue accents and light text. School workspace styling is separately scoped.

## One place to edit

`resources/css/admin-theme-tokens.css` is the source for all admin colors, radii, shadows, font stack and motion duration. `platform-theme.css` consumes tokens; keep raw theme colors out of its selectors. These tokens apply to admin sign-in and every provider page. Printed invoices retain their readable paper layout.

| Role | Default |
| --- | --- |
| Background | `#242021` |
| Card surface | `#2e2e2e` |
| Recessed inputs | `#242021` |
| Primary action / coral | `#ff764c` |
| Navigation / teal | `#00c4b0` |
| Purple accent | `#b94bea` |
| Blue accent | `#2f99f1` |
| Text / muted | `#ffffff` / `#aaaaaa` |
| Main card radius | `26px` |
| Controls / nested cards | `12px` / `14px` |

Change the primary color with one command in PowerShell (quote hex values):

```powershell
npm run theme:admin -- --accent "#ff764c"
```

Or change several colors together:

```powershell
npm run theme:admin -- --accent "#ff764c" --teal "#00c4b0" --purple "#b94bea" --blue "#2f99f1" --background "#242021" --surface "#2e2e2e"
```

Small purple action labels use white text while their icons retain the sampled purple. The command adjusts accent hover/focus/soft shades and chooses black or white primary-button text for contrast. Invalid options are rejected before writing. Use `--help` for all supported options. For radius, shadows, font and motion, edit their named CSS tokens directly. Check text contrast when customizing background or text colors.

Run `npm run build` after changing tokens for a built installation. With `npm run dev`, Vite updates the browser automatically. Commit/push token changes to share them across installations; this is build-time styling, not a per-school database preference.

## Interaction and responsive behavior

Main panels deepen their shadow on hover. Metric and action cards lift 5px; buttons lift 3px and change color. Active navigation has a teal border, inset indicator and persistent fill. Keyboard focus is visible independently of hover, touch has pressed feedback, and reduced-motion preferences disable movement. Small screens preserve the existing drawer and single-column forms, with smaller card padding.

## Exact uploaded reference correction

Background/card/accent values above are the dominant flat pixel colors sampled from the user’s 2000 × 2000 JPEG. Container and control borders, colored card-top lines, active-menu strokes and pale shadow highlights are removed. Containers use black-only outer shadows (main: 10px 14px 26px black at 65%, plus 0 3px 8px at 70%). Inputs use black inset depth; primary actions follow the reference dark button with orange lettering. Keyboard focus outlines remain for accessibility. The sampled flat colors match the supplied JPEG; raster shadow blur is reproduced with CSS rather than claimed as a pixel-identical browser rendering.
