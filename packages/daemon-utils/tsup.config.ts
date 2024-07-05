import { defineConfig } from 'tsup';
export default defineConfig({
	clean: true,
	dts: true,
	entry: ['src/**/index.ts'],
	format: ['esm', 'cjs'],
	outDir: 'dist',
	sourcemap: true,
	splitting: false,
	bundle: true,
	skipNodeModulesBundle: true,
	target: 'es2022',
	tsconfig: 'tsconfig.build.json',
});
