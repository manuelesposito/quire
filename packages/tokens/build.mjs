import StyleDictionary from 'style-dictionary';

// Quire token pipeline. One W3C (DTCG) source ->
//   variables.css  (:root, light)            + typed tokens.ts
//   dark.css       ([data-theme="dark"])     — only the remapped semantic tokens
// Semantic tokens reference primitives via outputReferences, so dark.css just
// re-points the SAME semantic vars at different primitive stops.
const PREFIX = 'qr';

const light = new StyleDictionary({
  usesDtcg: true,
  source: ['src/primitive/**/*.json', 'src/semantic/**/*.json'],
  platforms: {
    css: {
      transformGroup: 'css', prefix: PREFIX, buildPath: 'dist/css/',
      files: [{ destination: 'variables.css', format: 'css/variables', options: { outputReferences: true } }],
    },
    ts: {
      transformGroup: 'js', prefix: PREFIX, buildPath: 'dist/ts/',
      files: [{ destination: 'tokens.ts', format: 'javascript/es6' }],
    },
  },
});
await light.buildAllPlatforms();

const dark = new StyleDictionary({
  usesDtcg: true,
  source: ['src/primitive/**/*.json', 'src/modes/dark/**/*.json'],
  platforms: {
    css: {
      transformGroup: 'css', prefix: PREFIX, buildPath: 'dist/css/',
      files: [{
        destination: 'dark.css', format: 'css/variables',
        filter: (token) => token.filePath.includes('/modes/dark/'),
        options: { outputReferences: true, selector: '[data-theme="dark"]' },
      }],
    },
  },
});
await dark.buildAllPlatforms();

console.log('✓ Quire tokens -> dist/css/variables.css (:root) + dist/css/dark.css ([data-theme=dark]) + dist/ts/tokens.ts');
