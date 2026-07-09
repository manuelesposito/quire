#!/usr/bin/env python3
"""Assemble the Quire plugin's assets from the packages (the one-way street).

The plugin ships self-contained CSS, but never owns it: tokens come from
@quire/tokens' build output, bridges from @quire/reskin-css. Run after any
token rebuild. Run from the repo root:

    python3 apps/delivery-plugin/assemble.py
"""
import pathlib
import shutil

ROOT = pathlib.Path(__file__).resolve().parents[2]
ASSETS = ROOT / 'apps/delivery-plugin/quire/assets'

SOURCES = {
    'variables.css':    ROOT / 'packages/tokens/dist/css/variables.css',
    'hooks.css':        ROOT / 'packages/reskin-css/src/hooks.css',
    'core-classic.css': ROOT / 'packages/reskin-css/src/core-classic.css',
    'login.css':        ROOT / 'packages/reskin-css/src/login.css',
}

ASSETS.mkdir(parents=True, exist_ok=True)
for name, src in SOURCES.items():
    if not src.exists():
        raise SystemExit(f'missing {src} — build @quire/tokens first')
    shutil.copy(src, ASSETS / name)
    print(f'{name}  <-  {src.relative_to(ROOT)}')
print('assembled', ASSETS.relative_to(ROOT))
