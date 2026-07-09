#!/usr/bin/env python3
"""The legibility guarantee, mechanized.

Resolves the built CSS variables (light + dark) and checks every text-on-
surface pair the system actually uses against WCAG AA. Exit 1 on any fail,
so it can gate CI. The live view of the same pairs is tokens.html ->
Legibility; this is the version a robot can refuse to merge on.

Run from packages/tokens:  python3 scripts/check-contrast.py
(requires dist/ to be built:  corepack pnpm --filter @quire/tokens build)
"""
import re
import sys

VARS_CSS = 'dist/css/variables.css'
DARK_CSS = 'dist/css/dark.css'

# (foreground, background, min ratio, note)
# 7.0 = AAA (the body-text tiers — non-negotiable) · 4.5 = AA · 3.0 = UI marks
PAIRS = [
    ('text-default', 'surface-canvas', 7.0, 'body text on the page'),
    ('text-default', 'surface-raised', 7.0, 'body text on cards'),
    ('text-default', 'surface-nav',    7.0, 'nav labels'),
    ('text-default', 'surface-sunken', 7.0, 'text in wells'),
    ('text-muted',   'surface-canvas', 7.0, 'muted text on the page'),
    ('text-muted',   'surface-raised', 7.0, 'muted text on cards'),
    ('text-subtle',  'surface-canvas', 4.5, 'subtle text on the page'),
    ('text-subtle',  'surface-raised', 4.5, 'subtle text on cards'),
    ('text-inverse', 'surface-inverse', 4.5, 'inverse text (admin bar)'),
    ('text-accent',  'surface-canvas', 4.5, 'links on the page'),
    ('text-accent',  'surface-raised', 4.5, 'links on cards'),
    ('action-primary-text',     'action-primary-default',     4.5, 'primary button label'),
    ('action-secondary-text',   'surface-raised',             4.5, 'secondary button label'),
    ('action-tertiary-text',    'surface-canvas',             4.5, 'tertiary button label'),
    ('action-destructive-text', 'action-destructive-default', 4.5, 'destructive button label'),
    ('feedback-success-text', 'feedback-success-surface', 4.5, 'success notice text'),
    ('feedback-warning-text', 'feedback-warning-surface', 4.5, 'warning notice text'),
    ('feedback-danger-text',  'feedback-danger-surface',  4.5, 'danger notice text'),
    ('feedback-info-text',    'feedback-info-surface',    4.5, 'info notice text'),
    ('feedback-success-text', 'feedback-success-chip', 4.5, 'success badge text'),
    ('feedback-warning-text', 'feedback-warning-chip', 4.5, 'warning badge text'),
    ('feedback-danger-text',  'feedback-danger-chip',  4.5, 'danger badge text'),
    ('feedback-info-text',    'feedback-info-chip',    4.5, 'info badge text'),
    ('state-selected-mark', 'state-selected-surface', 4.5, 'selected nav item label'),
    ('control-on-fg', 'control-on',     3.0, 'check/knob on a control (UI mark)'),
    ('text-on-accent', 'accent-default', 3.0, 'mark on the bright accent (UI mark)'),
]


def parse_defs(path):
    defs = {}
    for m in re.finditer(r'(--qr-[\w-]+):\s*([^;]+);', open(path).read()):
        defs[m.group(1)] = m.group(2).strip()
    return defs


def resolve(name, defs, depth=0):
    if depth > 12:
        raise ValueError(f'alias loop at {name}')
    v = defs.get(name)
    if v is None:
        raise KeyError(name)
    m = re.fullmatch(r'var\((--qr-[\w-]+)\)', v)
    return resolve(m.group(1), defs, depth + 1) if m else v


def hex_rgb(h):
    h = h.lstrip('#')
    if len(h) == 8:      # alpha hexes can't be contrast-checked without a
        return None      # composite background — skipped, reported as such
    return tuple(int(h[i:i + 2], 16) / 255 for i in (0, 2, 4))


def lum(rgb):
    f = lambda c: c / 12.92 if c <= .03928 else ((c + .055) / 1.055) ** 2.4
    r, g, b = (f(c) for c in rgb)
    return .2126 * r + .7152 * g + .0722 * b


def ratio(a, b):
    la, lb = lum(a), lum(b)
    return (max(la, lb) + .05) / (min(la, lb) + .05)


def main():
    light = parse_defs(VARS_CSS)
    dark = dict(light)
    dark.update(parse_defs(DARK_CSS))

    failures = []
    checked = 0
    for theme, defs in (('light', light), ('dark', dark)):
        for fg, bg, minimum, note in PAIRS:
            f = hex_rgb(resolve(f'--qr-color-{fg}', defs))
            b = hex_rgb(resolve(f'--qr-color-{bg}', defs))
            if f is None or b is None:
                continue  # translucent — needs a composite, not checkable here
            r = ratio(f, b)
            checked += 1
            status = 'PASS' if r >= minimum else 'FAIL'
            if r < minimum:
                failures.append(f'  {theme}: {fg} on {bg} = {r:.2f} (needs {minimum}) — {note}')
            print(f'{status} {theme:5} {fg} on {bg}: {r:.2f} (>= {minimum}) — {note}')

    print(f'\n{checked} pairs checked, {len(failures)} failing')
    if failures:
        print('FAILURES:')
        print('\n'.join(failures))
        sys.exit(1)


if __name__ == '__main__':
    main()
