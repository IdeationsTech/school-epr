# Super Admin reference theme

Super Admin uses the supplied charcoal reference: warm dark background, raised gray containers, deep soft shadows, teal/purple/coral/blue accents and light text. School workspace styling is separately scoped.

## One place to edit

`resources/css/admin-theme-tokens.css` is the source for all admin colors, radii, shadows, font stack and motion duration. `platform-theme.css` consumes tokens; keep raw theme colors out of its selectors. These tokens apply to admin sign-in and every provider page. Printed invoices retain their readable paper layout.

| Role | Default |
| --- | --- |
| Background | `#242020` |
| Card surface | `#2d2d2d` |
| Recessed inputs | `#211f1f` |
| Primary action / coral | `#ff7951` |
| Navigation / teal | `#00c5b5` |
| Purple accent | `#bf4bea` |
| Blue accent | `#2f9bea` |
| Text / muted | `#f5f5f4` / `#b8b5b5` |
| Main card radius | `26px` |
| Controls / nested cards | `12px` / `14px` |

Change the primary color with one command in PowerShell (quote hex values):

```powershell
npm run theme:admin -- --accent "#ff7951"
```

Or change several colors together:

```powershell
npm run theme:admin -- --accent "#ff7951" --teal "#00c5b5" --purple "#bf4bea" --blue "#2f9bea" --background "#242020" --surface "#2d2d2d"
```

Purple links use a lighter text token for readability while icons retain the reference purple. The command adjusts accent hover/focus/soft shades and chooses black or white primary-button text for contrast. Invalid options are rejected before writing. Use `--help` for all supported options. For radius, shadows, font and motion, edit their named CSS tokens directly. Check text contrast when customizing background or text colors.

Run `npm run build` after changing tokens for a built installation. With `npm run dev`, Vite updates the browser automatically. Commit/push token changes to share them across installations; this is build-time styling, not a per-school database preference.

## Interaction and responsive behavior

Main panels deepen their shadow on hover. Metric and action cards lift 5px; buttons lift 3px and change color. Active navigation has a teal border, inset indicator and persistent fill. Keyboard focus is visible independently of hover, touch has pressed feedback, and reduced-motion preferences disable movement. Small screens preserve the existing drawer and single-column forms, with smaller card padding.
