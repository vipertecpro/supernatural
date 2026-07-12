# Prompt 15C Road Hero Assets

## Accepted asset plan

The hero uses original procedural geometry, shaders, CSS, and Web Audio. This avoids an ambiguous vehicle-model licence and keeps the runtime free of image, model, texture, HDRI, and audio downloads. Every scene input is registered in `resources/js/features/experience/road-hero/asset-manifest.ts`.

| Asset | Creator | Source | Licence | Commercial use | Modification | Attribution | Repository redistribution | File size / format | Selection reason |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| Archive Roadster | Bankai / this repository | Local source code | Project source terms; original work | Yes for this project | Yes | No external attribution | Yes | Generated Three.js geometry; no asset file | Original long-hood, fastback muscle-car silhouette without brand marks |
| Wet forest road | Bankai / this repository | Local source code | Project source terms; original work | Yes for this project | Yes | No external attribution | Yes | Generated geometry/materials | Repeating road segments, lane marks, shoulders, and reflections without textures |
| Forest silhouettes | Bankai / this repository | Local source code | Project source terms; original work | Yes for this project | Yes | No external attribution | Yes | Instanced generated geometry | Layered trees with bounded draw calls and tiered density |
| Fog, rain, clouds, and sky | Bankai / this repository | Local source code | Project source terms; original work | Yes for this project | Yes | No external attribution | Yes | Procedural particles/materials | No texture transfer, deterministic layout, theme interpolation |
| Archive entrance | Bankai / this repository | Local source code | Project source terms; original work | Yes for this project | Yes | No external attribution | Yes | Generated geometry/materials | Original illuminated monolith, not an official franchise symbol |
| Procedural soundtrack | Bankai / this repository | Local Web Audio synthesis | Project source terms; original work | Yes for this project | Yes | No external attribution | Yes | Runtime synthesis; no audio file | Engine, wind, road noise, radio static, drone, and signal are generated after opt-in |
| Static fallback composition | Bankai / this repository | Local JSX/CSS | Project source terms; original work | Yes for this project | Yes | No external attribution | Yes | CSS and semantic DOM | Preserves the road, car, fog, headlight, title, and actions without Canvas |
| Cinzel Decorative 700 | Natanael Gama via Fontsource | https://fontsource.org/fonts/cinzel-decorative | SIL Open Font License 1.1 | Yes | Yes under OFL | Licence notice retained by package | Yes under OFL | WOFF2/WOFF, 16/20 kB build files | Cinematic title with a clearly original treatment |
| Cormorant Garamond 500/600 | Christian Thalmann via Fontsource | https://fontsource.org/fonts/cormorant-garamond | SIL Open Font License 1.1 | Yes | Yes under OFL | Licence notice retained by package | Yes under OFL | WOFF2/WOFF, 24–32 kB build files | Editorial supporting copy |
| Special Elite 400 | Astigmatic via Fontsource | https://fontsource.org/fonts/special-elite | Apache License 2.0 | Yes | Yes | Licence notice retained by package | Yes | WOFF2/WOFF, 56/64 kB build files | Restrained case-file labels only |
| Instrument Sans 400/500/600 | Rodrigo Fuenzalida and Jordan Egstad via Fontsource | https://fontsource.org/fonts/instrument-sans | SIL Open Font License 1.1 | Yes | Yes under OFL | Licence notice retained by package | Yes under OFL | WOFF2/WOFF, 20–24 kB build files | Legible controls and body text |

Font licence files remain in the installed packages. No font is fetched from a third-party origin at runtime.

## Sources evaluated and rejected

| Candidate | Source | Decision |
| --- | --- | --- |
| Sketchfab 1960s/1967 muscle-car uploads | https://sketchfab.com/ | Rejected. Search results did not provide one exact candidate with sufficiently clear authorship, trademark treatment, repository redistribution terms, and production-ready size. No model was downloaded. |
| Khronos glTF Sample Assets car concepts and toy cars | https://github.khronos.org/glTF-Assets/ | Rejected for the hero. The catalogue provides per-model licence metadata, but the available vehicle styles do not meet the classic roadster direction and some samples carry model-specific restrictions. |
| Kenney Car Kit | https://kenney.nl/assets/car-kit | Rejected for visual fit. Kenney's CC0 model kits are rights-friendly, but the stylized vehicles do not meet the cinematic long-hood silhouette required here. |
| Poly Haven models, HDRIs, and textures | https://polyhaven.com/ | Rejected as unnecessary. CC0 terms are suitable, but procedural road, sky, fog, and forest assets avoid extra transfer cost and make light/dark interpolation controllable. |
| Exact Chevrolet Impala models | Public model libraries | Rejected. No rights-cleared exact model with compatible redistribution terms and acceptable branding risk was selected. |
| Television music, dialogue, engine recordings, or episode media | Television and streaming sources | Rejected outright as copyrighted source material outside the repository's rights boundary. |

## Runtime acceptance rule

The scene may reference only manifest entries. Any future binary or downloaded asset must add creator, source URL, licence, commercial-use, modification, attribution, redistribution, format, size, derivative status, and fallback metadata before use.
