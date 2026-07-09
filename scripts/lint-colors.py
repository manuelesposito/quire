#!/usr/bin/env python3
"""Tokens are law — the colour lint.

Scans the shared component layer and the app screens for raw colour values
(#hex, rgb(), rgba(), hsl()) that bypass the token system. DESIGN.md claims
the rule; this makes it true. Exit 1 on violations, so it can gate CI.

Scope: components.css + the app screens (the surfaces that claim to be
token-pure). The docs/reading pages (index, tokens, gallery, philosophy,
home, guide.css, workbench.*) are chrome for the guide itself and render
token VALUES by design — out of scope.

Allowlist: content stand-ins (fake product photos and the like) that would
be real images in production, plus true shadows pending elevation tokens.
Every entry needs a reason — an allowlist without reasons is just a leak.

Run from the repo root:  python3 scripts/lint-colors.py
"""
import re
import sys

FILES = [
    'packages/reskin-css/src/hooks.css',
    'packages/reskin-css/src/core-classic.css',
    'packages/reskin-css/src/login.css',
    'apps/docs/specimen/components.css',
    'apps/docs/specimen/nav.html',
    'apps/docs/specimen/orders.html',
    'apps/docs/specimen/settings.html',
    'apps/docs/specimen/dashboard.html',
    'apps/docs/specimen/products.html',
    'apps/docs/specimen/jetpack.html',
]

# (file substring, value regex, reason)
ALLOW = [
    ('components.css', r'#(C9A24A|A85F3C)',
     'media-thumb gradient: stand-in for a real image (would be <img> in production)'),
    ('components.css', r'rgba\(0,0,0,\.25\)',
     'toggle knob drop shadow — a true shadow; candidate for an elevation token'),
    ('products.html', r'#(B8B29E|3E3A2E|A9998A|372F27|9AA48F|2F362A|C0A488|423422|8F9AA4|272E36)',
     'pthumb tints: stand-ins for product photos (documented in the file)'),
]

PATTERN = re.compile(
    r'#[0-9A-Fa-f]{3,8}\b|rgba?\([^)]*\)|hsla?\([^)]*\)')


def allowed(path, value):
    return any(sub in path and re.fullmatch(rx, value) for sub, rx, _ in ALLOW)


def style_text(path, text):
    """For HTML, only CSS contexts count — content like order '#1042' is not a colour."""
    if not path.endswith('.html'):
        return text
    keep = []
    for m in re.finditer(r'<style[^>]*>(.*?)</style>|style="([^"]*)"', text, re.S):
        keep.append(m.group(1) or m.group(2) or '')
    return '\n'.join(keep)


def main():
    violations = []
    for path in FILES:
        try:
            text = open(path).read()
        except FileNotFoundError:
            violations.append((path, 0, '(file missing — update FILES)'))
            continue
        for i, line in enumerate(style_text(path, text).splitlines(), 1):
            for m in PATTERN.finditer(line):
                v = m.group(0)
                if allowed(path, v):
                    continue
                violations.append((path, i, v))

    if violations:
        print(f'{len(violations)} raw colour value(s) outside the tokens:')
        for path, line, v in violations:
            print(f'  {path}:{line}  {v}')
        sys.exit(1)
    print(f'clean — {len(FILES)} files, 0 raw colours outside the allowlist')


if __name__ == '__main__':
    main()
