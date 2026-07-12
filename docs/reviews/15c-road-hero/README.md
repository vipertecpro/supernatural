# Prompt 15C Visual Review

These PNG files are direct captures of the locally rendered, rights-clear procedural scene.

## Required evidence

| State | File |
| --- | --- |
| Desktop dark initial | `desktop-dark-initial.png` |
| Desktop dark mid-scroll | `desktop-dark-mid-scroll.png` |
| Desktop dark final | `desktop-dark-final.png` |
| Desktop light initial | `desktop-light-initial.png` |
| Desktop light mid-scroll | `desktop-light-mid-scroll.png` |
| Mobile dark initial | `mobile-dark-initial.png` |
| Mobile dark mid-scroll | `mobile-dark-mid-scroll.png` |
| Reduced-mode fallback | `reduced-mode-fallback.png` |
| WebGL-disabled fallback | `webgl-disabled-fallback.png` |
| Sound control | `sound-control-active.png` |
| 320px reflow | `mobile-320-reflow.png` |

Video recording was unavailable. `motion-00-0ms.png` through `motion-05-2000ms.png` are the six-frame timestamp-like substitute. The in-app browser's synthesized mobile wheel input jumps more coarsely than a physical touch scroll, so the mobile mid-scroll capture represents the later scroll chapter rather than an exact 50% position.

## Review outcome

The screenshots prove a themed road, original vehicle, layered forest, fog/weather, entrance, semantic copy, controls, appearance variants, responsive composition, and intentional fallbacks. The result is ready for owner review, not final visual sign-off. The next pass should refine vehicle bodywork and headlight volumes and lift the darkest forest/road mids.

The sound-control capture records the browser harness's off state. Automated Web Audio activation could not be confirmed in this browser session; a manual audible check remains required.

## Validation record

- `php artisan test --compact`: 317 passed, 1,726 assertions.
- `vendor/bin/phpstan analyse`: passed with zero errors.
- `vendor/bin/pint --format agent`, `vendor/bin/pint`, and `vendor/bin/pint --test`: passed; the agent formatter normalized one test quote.
- `npm run lint`, `npm run lint:check`, `npm run format`, `npm run format:check`, `npm run types:check`: passed.
- `npm run build`: passed; Vite retained the explicit 500 kB shared renderer warning.
- `composer validate --strict`, `composer audit --no-interaction`, and `npm audit`: passed with no advisories.
- `php artisan route:list -v`: passed with 312 routes; config/route cache and optimize clear passed.
- The first ESLint pass exposed React 19 immutability diagnostics around imperative R3F frame state. Camera/scene mutations were moved to `useFrame` callback state and the two intentional external mutable runtimes received narrow file-level explanations. The final lint pass is clean.
- A hydration mismatch appeared when the scene read client appearance during SSR hydration. The scene now hydrates from light and synchronizes appearance on the next animation frame; the final fresh-tab browser log has zero errors or warnings.
