import StyleDictionary from 'style-dictionary';

// Quire token pipeline: one W3C (DTCG) source -> CSS custom properties + typed TS.
// Semantic tokens reference primitives via outputReferences, so the CSS stays tiered.
const sd = new StyleDictionary({
  usesDtcg: true,
  source: ['src/**/*.json'],
  platforms: {
    css: {
      transformGroup: 'css',
      prefix: 'qr',
      buildPath: 'dist/css/',
      options: { outputReferences: true },
      files: [{ destination: 'variables.css', format: 'css/variables' }],
    },
    ts: {
      transformGroup: 'js',
      prefix: 'qr',
      buildPath: 'dist/ts/',
      files: [{ destination: 'tokens.ts', format: 'javascript/es6' }],
    },
  },
});

await sd.buildAllPlatforms();
console.log('✓ Quire tokens built -> dist/css/variables.css, dist/ts/tokens.ts');
