#!/usr/bin/env python3
"""Flatten the token sources into Figma-variable payloads.

Part of the code -> Figma mirror (see ../FIGMA.md). Figma is a generated
REVIEW surface, never a second source of truth: this script reads
src/{primitive,semantic}/*.json + src/modes/dark/color.json and emits four
JSON payloads that a Claude session pushes into the "Quire — Tokens" Figma
file via the Figma MCP (variables API).

Run from packages/tokens:  python3 scripts/figma-payloads.py <outdir>

Emits:
  fig-prim-colors.json  primitive colours          -> COLOR vars, scopes []
  fig-prim-dims.json    space/radius/size/font     -> FLOAT/STRING vars
  fig-sem-colors.json   semantic colours           -> COLOR vars, Light+Dark
  fig-sem-alias.json    semantic space/radius/text -> alias vars, both modes

Not representable as variables (skipped): duration/easing (motion),
shadows (mirrored as the Elevation/* effect styles instead).
"""
import json
import glob
import re
import sys


def flat(d, prefix=()):
    out = {}
    for k, v in d.items():
        if isinstance(v, dict) and '$value' in v:
            out[prefix + (k,)] = v['$value']
        elif isinstance(v, dict):
            out.update(flat(v, prefix + (k,)))
    return out


def css(path):
    return '--qr-' + '-'.join(path)


def name(path):
    return '/'.join(path)


def ref_to_name(v):
    m = re.fullmatch(r'\{([^}]+)\}', v.strip()) if isinstance(v, str) else None
    return m.group(1).replace('.', '/') if m else None


def px(v):
    v = str(v)
    if v in ('0', '0px'):
        return 0.0
    m = re.fullmatch(r'(-?[\d.]+)px', v)
    return float(m.group(1)) if m else None


def leaf_scopes(p):
    last = p[-1]
    if p[1] == 'text' or last == 'text':
        return ['TEXT_FILL']
    if last == 'border' or p[1] == 'border':
        return ['STROKE_COLOR']
    return ['FRAME_FILL', 'SHAPE_FILL']


def main(outdir):
    prim, sem = {}, {}
    for f in glob.glob('src/primitive/*.json'):
        prim.update(flat(json.load(open(f))))
    for f in glob.glob('src/semantic/*.json'):
        sem.update(flat(json.load(open(f))))
    dark = flat(json.load(open('src/modes/dark/color.json')))

    prim_colors = [{'name': name(p), 'hex': v, 'css': css(p)}
                   for p, v in prim.items() if p[0] == 'color']

    floats, strings = [], []
    scopes_by_kind = {'space': ['GAP'], 'radius': ['CORNER_RADIUS'],
                      'size': ['WIDTH_HEIGHT'], 'border-width': ['STROKE_FLOAT']}
    for p, v in prim.items():
        k = p[0]
        if k in scopes_by_kind:
            val = px(v)
            if val is not None:
                floats.append({'name': name(p), 'value': val,
                               'scopes': scopes_by_kind[k], 'css': css(p)})
        elif k == 'font':
            sub = p[1]
            if sub == 'family':
                fam = re.sub(r"^'([^']+)'.*|^([A-Za-z -]+),.*",
                             lambda m: m.group(1) or m.group(2), v).strip()
                strings.append({'name': name(p), 'value': fam,
                                'scopes': ['FONT_FAMILY'], 'css': css(p)})
            elif sub == 'size':
                floats.append({'name': name(p), 'value': px(v),
                               'scopes': ['FONT_SIZE'], 'css': css(p)})
            elif sub == 'weight':
                floats.append({'name': name(p), 'value': float(v),
                               'scopes': ['FONT_WEIGHT'], 'css': css(p)})
            elif sub == 'line-height':
                floats.append({'name': name(p), 'value': float(v),
                               'scopes': ['LINE_HEIGHT'], 'css': css(p)})
            elif sub == 'letter-spacing':
                # LETTER_SPACING scope is FLOAT-only in Figma; these are em
                # strings, so they land hidden from pickers (scopes []).
                strings.append({'name': name(p), 'value': str(v),
                                'scopes': ['LETTER_SPACING'], 'css': css(p)})

    sem_colors = []
    for p, v in sem.items():
        if p[0] != 'color':
            continue
        light = {'ref': ref_to_name(v)} if ref_to_name(v) else {'hex': v}
        dv = dark.get(p)
        dmode = ({'ref': ref_to_name(dv)} if ref_to_name(dv) else {'hex': dv}) if dv else light
        sem_colors.append({'name': name(p), 'light': light, 'dark': dmode,
                           'scopes': leaf_scopes(p), 'css': css(p)})

    sem_alias = []
    for p, v in sem.items():
        k = p[0]
        if k in ('color', 'elevation', 'motion'):
            continue
        r = ref_to_name(v)
        if not r:
            continue
        if k == 'space':
            sc = ['GAP']
        elif k == 'radius':
            sc = ['CORNER_RADIUS']
        elif k == 'text':
            sc = {'family': ['FONT_FAMILY'], 'size': ['FONT_SIZE'],
                  'weight': ['FONT_WEIGHT'], 'line-height': ['LINE_HEIGHT'],
                  'letter-spacing': ['LETTER_SPACING']}.get(p[-1], [])
        else:
            sc = []
        sem_alias.append({'name': name(p), 'ref': r, 'scopes': sc, 'css': css(p)})

    json.dump(prim_colors, open(f'{outdir}/fig-prim-colors.json', 'w'))
    json.dump({'floats': floats, 'strings': strings},
              open(f'{outdir}/fig-prim-dims.json', 'w'))
    json.dump(sem_colors, open(f'{outdir}/fig-sem-colors.json', 'w'))
    json.dump(sem_alias, open(f'{outdir}/fig-sem-alias.json', 'w'))
    print(f'prim colors {len(prim_colors)} | floats {len(floats)} | '
          f'strings {len(strings)} | sem colors {len(sem_colors)} | '
          f'sem alias {len(sem_alias)}')


if __name__ == '__main__':
    main(sys.argv[1] if len(sys.argv) > 1 else '.')
