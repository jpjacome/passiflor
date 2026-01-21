<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BLS - Bilateral Stimulation (Admin)</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/general.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'quicksand', sans-serif;
            overflow: hidden;
            background: var(--color-4);
            color: var(--color-1);
        }

        #stimulation-area {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            transition: background 0.3s ease;
        }

        #stimulation-area.bg-dark { background: rgb(0, 0, 0); }
        #stimulation-area.bg-light { background: var(--color-1); }
        #stimulation-area.bg-blue { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        #stimulation-area.bg-green { background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%); }
        #stimulation-area.bg-sunset { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }

        #moving-object {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            transition: all 0.05s linear;
            z-index: 10;
        }

        /* Disable transitions while animating to avoid trailing/ghosting */
        .no-transition {
            transition: none !important;
        }

        .object-ball {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            /* Use a flat color by default; JS will apply the picker color on load */
            background: var(--color-1);
        }

        .object-bar {
            width: 8px;
            height: 80vh;
            background: linear-gradient(180deg, transparent, var(--color-2), transparent);
            box-shadow: 0 0 20px rgba(133,55,32,0.5);
        }

        .object-butterfly {
            font-size: 48px;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.3));
        }

        /* Custom background controls (hidden until 'custom' selected) */
        .custom-controls {
            display: none;
            gap: 8px;
            align-items: center;
        }
        .custom-controls.active {
            display: flex;
        }
        /* Object color controls (hidden until used) */
        .object-controls {
            gap: 8px;
            align-items: center;
        }
        .object-controls.active {
            display: flex;
        }
        /* Direction presets: five equal squares for movement patterns */
        .direction-presets {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        .dir-square {
            width: 48px;
            height: 48px;
            border: 2px solid var(--color-3);
            background: var(--color-2);
            color: var(--color-1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            cursor: pointer;
            transition: transform 0.12s ease, box-shadow 0.12s ease;
        }
        .dir-square:hover { transform: translateY(-3px); }
        .dir-square.selected { box-shadow: 0 0 0 3px rgba(0,0,0,0.08) inset; }
        .dir-square i { font-size: 20px; display: inline-block; transition: transform 0.12s ease; transform: rotate(0deg); }
        .dir-square[data-direction="rtl"] i { transform: rotate(90deg); }
        .dir-square[data-direction="diag-rtl"] i { transform: rotate(45deg); }
        .dir-square[data-direction="diag-ltr"] i { transform: rotate(-45deg); }
        .dir-square[data-direction="vertical"] i { transform: rotate(0deg); }
        .dir-square[data-direction="figure8"] i { transform: rotate(0deg); }
        /* Custom dropdown with icons (replaces native option visuals) */
        .custom-dropdown {
            position: relative;
            display: inline-block;
        }
        .custom-toggle {
            display: inline-flex;
            width: 95%;
            align-items: center;
            gap: 8px;
            padding: 10px 12px;
            border: 2px solid var(--color-3);
            border-radius: 8px;
            background: var(--color-2);
            color: var(--color-1);
            cursor: pointer;
        }
        .custom-options {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: var(--color-2);
            border: 1px solid var(--color-3);
            border-radius: 8px;
            margin-top: 8px;
            max-height: 220px;
            overflow: auto;
            display: none;
            z-index: 300;
        }
        .custom-options.active { display: block; }
        .custom-options.up {
            top: auto;
            bottom: 100%;
            margin-top: 0;
            margin-bottom: 8px;
        }
        .custom-options li {
            list-style: none;
            padding: 8px 10px;
            display: flex;
            gap: 8px;
            align-items: center;
            cursor: pointer;
        }
        .custom-options li:hover { background: rgba(0,0,0,0.06); }
        /* file upload removed - only color picker is used */

        #controls {
            display: flex;
            gap: 2rem;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--color-2);
            backdrop-filter: blur(10px);
            border-top: 1px solid var(--color-1);
            transform: translateY(0);
            transition: transform 0.3s ease;
            z-index: 100;
            flex-direction: column;
        }

        #controls.hidden {
            transform: translateY(100%);
        }

        .control-group {
            padding: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
        }
            .data{
                border-top: 1px solid var(--color-1);
            }

        .control-item {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .control-item .container {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .control-item label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #aaa;
        }

        button, select, input[type="range"] {
            padding: 10px 16px;
            border: 2px solid var(--color-3);
            border-radius: 8px;
            background: var(--color-2);
            color: var(--color-1);
            cursor: pointer;
            font-family: 'quicksand', sans-serif;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        button:hover, select:hover {
            background: var(--color-2);
            transform: translateY(-2px);
        }

        button.active {
            background: var(--color-3);
        }

        button.primary {
            background: var(--color-3);
            font-weight: 600;
            font-size: 16px;
        }

        button.primary:hover {
            background: var(--color-3);
        }

        input[type="range"] {
            -webkit-appearance: none;
            appearance: none;
            height: 6px;
            background: var(--color-1);
            outline: none;
            padding: 0;
        }

        input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 18px;
            height: 18px;
            background: var(--color-3);
            cursor: pointer;
            border-radius: 50%;
        }

        input[type="range"]::-moz-range-thumb {
            width: 18px;
            height: 18px;
            background: var(--color-2);
            cursor: pointer;
            border-radius: 50%;
            border: none;
        }

        .button-group {
            display: flex;
            gap: 10px;
        }

        .button-group button {
            flex: 1;
        }

        #toggle-controls {
            padding: 8px 12px;
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 8px;
            color: var(--color-1);
            cursor: pointer;
            transition: all 0.15s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        #toggle-controls:hover {
            opacity: 0.95;
            transform: translateY(-1px);
        }

        /* small floating button that appears when controls are hidden */
        .mini-toggle {
            position: fixed;
            right: 12px;
            bottom: 12px;
            width: 44px;
            height: 44px;
            padding: 6px;
            border-radius: 50%;
            background: var(--color-3);
            color: var(--color-1);
            border: none;
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 102;
            box-shadow: 0 6px 18px rgba(0,0,0,0.15);
            cursor: pointer;
        }
        .mini-toggle i { font-size: 18px; }
        .mini-toggle:focus { outline: none; box-shadow: 0 0 0 3px rgba(0,0,0,0.06) inset; }

        .value-display {
            display: inline-block;
            height: 100%;
            min-width: 40px;
            text-align: center;
            color: var(--color-1);
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .control-group {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <x-admin-header />

    <div class="dashboard-wrapper">
        <div class="page-header">
            <h1 class="page-title"><i class="ph ph-wave-square"></i> Bilateral Stimulation (EMDR)</h1>
            <a href="{{ route('admin.therapies.index') }}" class="btn-create"><i class="ph ph-arrow-left"></i> Volver a Terapias</a>
        </div>
    </div>

    <div id="stimulation-area" class="bg-blue">
        <div id="moving-object">
            <div class="object-ball"></div>
        </div>
    </div>

    <div id="controls">
        <div class="control-group">
            <div class="control-item" style="z-index:10;">
                <label>Start/Stop</label>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <button id="start-stop" class="primary">▶ Start</button>
                    <div style="display:flex;flex-direction:column;gap:8px;">
                        <button id="toggle-controls" type="button" class="secondary" title="Hide controls"><i class="ph ph-gear"></i> Hide Controls</button>
                        <button id="publish-live-btn" class="secondary" title="Publish live link"><i class="ph ph-share-network"></i> Publish</button>
                        <!-- Publish link shown in Metadata section -->
                    </div>
                </div>
            </div>

            <div class="control-item">
                <label>Speed: <span class="value-display" id="speed-value">1</span></label>
                <input type="range" id="speed" min="1" max="10" step="1" value="1">

                <div class="control-item">
                    <label>Pause Between: <span class="value-display" id="pause-value">0</span>ms</label>
                    <input type="range" id="pause" min="0" max="2000" step="100" value="0">
                </div>
            </div>

            <div class="control-item">
                <label>Sound Type</label>
                <select id="sound-select">
                    <option value="none" data-icon="speaker-none">None</option>
                    <option value="hihat" data-icon="music-note">Hi‑Hat</option>
                    <option value="heartbeat" data-icon="heartbeat">Heartbeat</option>
                    <option value="beep" data-icon="megaphone">Beep</option>
                    <option value="bassguitar" data-icon="guitar">Bass Guitar</option>
                    <option value="softbell" data-icon="bell">Soft Bell</option>
                    <option value="softflame" data-icon="fire">Soft Flame</option>
                    <option value="lippop" data-icon="microphone">Lip Pop</option>
                    <option value="badminton" data-icon="badminton">Badminton</option>
                </select><!-- custom dropdown to render icons per option -->
                <div class="control-item">
                    <label>Volume</label>
                    <input type="range" id="volume" min="0" max="100" value="60">
                </div>
            </div>

            <div class="control-item">
                <label>Object Type</label>
                <select id="object-type">
                    <option value="ball" data-icon="beach-ball">Ball</option>
                    <option value="bar" data-icon="rectangle">Bar</option>
                    <option value="butterfly" data-icon="butterfly">Butterfly</option>
                    <option value="dot" data-icon="dot">Dot</option>
                </select>
                <!-- custom dropdown for object-type with icons -->
                <div class="object-controls" id="object-controls">
                    <input type="color" id="object-color" value="#ffffff" title="Choose object color">
                    <button id="clear-object-color" type="button">Clear</button>
                </div>

                <div class="control-item">
                    <label>Direction</label>
                    <div class="direction-presets" id="direction-presets" role="group" aria-label="Direction presets">
                        <div class="dir-square" data-direction="rtl" title="Right → Left" aria-label="Right to left"><i class="ph ph-arrows-vertical" aria-hidden="true"></i></div>
                        <div class="dir-square" data-direction="diag-rtl" title="Diagonal ↘" aria-label="Diagonal down-right"><i class="ph ph-arrows-vertical" aria-hidden="true"></i></div>
                        <div class="dir-square" data-direction="diag-ltr" title="Diagonal ↙" aria-label="Diagonal down-left"><i class="ph ph-arrows-vertical" aria-hidden="true"></i></div>
                        <div class="dir-square" data-direction="vertical" title="Up / Down" aria-label="Up and down"><i class="ph ph-arrows-vertical" aria-hidden="true"></i></div>
                        <div class="dir-square" data-direction="figure8" title="Figure eight" aria-label="Figure eight"><i class="ph ph-infinity" aria-hidden="true"></i></div>
                    </div>
                </div>
            </div>

            <div class="control-item">
                <label>Background</label>
                <select id="background">
                    <option value="dark">Dark</option>
                    <option value="light">Light</option>
                    <option value="blue">Blue Gradient</option>
                    
                    <option value="green">Green Gradient</option>
                    <option value="sunset">Sunset Gradient</option>
                    <option value="custom">Custom</option>
                </select>
                <!-- custom dropdown for background (no icons) -->
                <div class="custom-controls" id="custom-controls">
                    <input type="color" id="custom-color" value="#ffffff" title="Choose background color">
                    <button id="clear-custom" type="button">Clear</button>
                </div>
            </div>
        </div>
        <!-- Additional metadata/control group -->
        <div class="control-group data" id="meta-controls">
            <div class="control-item">
                <label>Therapist</label>
                <div id="therapist" style="padding:8px;">{{ session('therapist_name') ?? '' }}</div>
                <label>Patient</label>
                <div id="patient" style="padding:8px;">{{ session('patient_name') ?? '' }}</div>
                <div id="publish-link-area" style="display:none; margin-top:8px;">
                    <label style="font-size:13px;opacity:0.9;margin-bottom:6px;display:block;">Link</label>
                    <input id="publish-link-input" readonly style="min-width:320px;padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,0.06);background:var(--color-2);color:var(--color-1);">
                </div>
            </div>

            <div class="control-item">
                <div class="container">
                    <label>Time</label>
                    <div class="value-display" id="unpaused-time">00:00</div>
                </div>
                <div class="container">
                    <label>Passes</label>
                    <div style="display:flex;gap:8px;align-items:center;">
                        <div class="value-display" id="passes-count">0</div>
                        <input type="number" id="passes-limit" min="0" placeholder="limit" style="width:80px;padding:6px;border:2px solid var(--color-3);border-radius:6px;background:var(--color-2);color:var(--color-1);">
                    </div>
                    
                    <label>Sets</label>
                    <div class="value-display" id="sets-count">0</div>
                </div>
                    <div class="container">
                        <button type="button" id="reset-meta" class="primary">Reset</button>
                    </div>
            </div>
        </div>

        </div>
    </div>

    <button id="mini-toggle-controls" class="mini-toggle" aria-label="Show controls" title="Show controls" type="button"><i class="ph ph-gear"></i></button>

    <script>
        // Audio Context for stereo sound
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        let audioCtx = null;
        let currentSound = 'beep';
        let masterGain = null;
        let volumeLevel = 60; // 0-100 default

        // State
        let isRunning = false;
        let animationId = null;
        let position = 0;
        let direction = 1;
        let movementMode = 'horizontal'; // 'horizontal' | 'vertical' | other presets
        let vPosition = 0;
        let vDirection = 1;
        // diagonal progress along the chosen diagonal (0..1)
        let diagProgress = 0;
        let diagDir = 1; // 1 forward, -1 backward
        let diagStart = { x: 0, y: 0 };
        let diagDX = 0;
        let diagDY = 0;
        let diagLength = 1;
        // figure‑8 state
        let figTheta = 0;
        let figA = 150; // horizontal amplitude (px)
        let figB = 90;  // vertical amplitude (px)
        let figCenter = { x: 0, y: 0 };
        let figDir = 1;
        let prevFigTheta = 0;
        // speed expressed in passes-per-second: UI raw step maps to 0.5 passes/sec per step
        // raw=1 -> 0.5 passes/sec, raw=2 ->1.0 passes/sec, raw=3 ->1.5 passes/sec, etc.
        const PASSES_PER_STEP = 0.5;
        let targetPassesPerSec = 0.5; // desired passes/sec from UI
        let currentPassesPerSec = 0.5; // smoothed value used by animation
        let _passesTween = null;
        let lastTimestamp = null;
        let pauseDuration = 0;
        let isPaused = false;

        // Elements
        const stimArea = document.getElementById('stimulation-area');
        const movingObj = document.getElementById('moving-object');
        const startStopBtn = document.getElementById('start-stop');
        const speedSlider = document.getElementById('speed');
        const speedValue = document.getElementById('speed-value');
        const soundSelect = document.getElementById('sound-select');
        const soundIcon = document.getElementById('sound-icon');
        const soundCustomToggle = document.getElementById('sound-custom-toggle');
        const soundCustomOptions = document.getElementById('sound-custom-options');
        const soundCustomLabel = document.getElementById('sound-custom-label');
        const soundCustomIcon = document.getElementById('sound-custom-icon');
        const volumeControl = document.getElementById('volume');
        const objectType = document.getElementById('object-type');
        const objectTypeIcon = document.getElementById('object-type-icon');
        const objectCustomToggle = document.getElementById('object-custom-toggle');
        const objectCustomOptions = document.getElementById('object-custom-options');
        const objectCustomLabel = document.getElementById('object-custom-label');
        const objectCustomIcon = document.getElementById('object-custom-icon');
        const objectControls = document.getElementById('object-controls');
        const objectColor = document.getElementById('object-color');
        const clearObjectColor = document.getElementById('clear-object-color');
        const background = document.getElementById('background');
        const backgroundCustomToggle = document.getElementById('background-custom-toggle');
        const backgroundCustomOptions = document.getElementById('background-custom-options');
        const backgroundCustomLabel = document.getElementById('background-custom-label');
        const therapistInput = document.getElementById('therapist');
        const patientInput = document.getElementById('patient');
        const unpausedTimeDisplay = document.getElementById('unpaused-time');
        const passesDisplay = document.getElementById('passes-count');
        const passesLimitInput = document.getElementById('passes-limit');
        const setsDisplay = document.getElementById('sets-count');
        const pauseSlider = document.getElementById('pause');
        const pauseValue = document.getElementById('pause-value');
        const toggleControls = document.getElementById('toggle-controls');
        const controls = document.getElementById('controls');
        const customControls = document.getElementById('custom-controls');
        const customColor = document.getElementById('custom-color');
        const clearCustom = document.getElementById('clear-custom');

        // Initialize target/current passes per second from slider: raw * PASSES_PER_STEP
        if (typeof speedSlider !== 'undefined' && speedSlider) {
            const s = parseInt(speedSlider.value, 10) || parseInt(speedSlider.min, 10) || 1;
            targetPassesPerSec = s * PASSES_PER_STEP;
            currentPassesPerSec = targetPassesPerSec;
            if (speedValue) speedValue.textContent = String(s);
        }

        // Sync control UI to actual runtime/DOM defaults (background, sound, volume, object icon)
        (function syncControls() {
            try {
                // Background: prefer class "bg-..." on stimArea, otherwise custom style
                let bg = 'blue';
                if (stimArea && stimArea.classList) {
                    const c = Array.from(stimArea.classList).find(cl => cl.indexOf('bg-') === 0);
                    if (c) bg = c.replace('bg-', '');
                }
                if (stimArea && stimArea.style && stimArea.style.background && stimArea.style.background.trim() !== '') {
                    // If inline background exists, treat as custom
                    bg = 'custom';
                    try { customColor.value = stimArea.style.background || '#ffffff'; } catch (e) {}
                    try { customControls.classList.add('active'); } catch (e) {}
                }
                try { if (background) background.value = bg; } catch (e) {}

                // Sound: reflect runtime currentSound
                try {
                    if (soundSelect && typeof currentSound !== 'undefined') {
                        // fallback to 'none' if option missing
                        const opt = Array.from(soundSelect.options).find(o => o.value === currentSound);
                        soundSelect.value = opt ? currentSound : (Array.from(soundSelect.options)[0] && Array.from(soundSelect.options)[0].value);
                        updateIcon(soundSelect, soundIcon);
                        try { soundCustomLabel.textContent = soundSelect.selectedOptions[0] && soundSelect.selectedOptions[0].textContent || ''; } catch (e) {}
                    }
                } catch (e) {}

                // Volume
                try { if (volumeControl) volumeControl.value = String(volumeLevel || 60); } catch (e) {}

                // Object-type icon
                try { updateIcon(objectType, objectTypeIcon); } catch (e) {}
            } catch (e) {
                console.warn('syncControls failed', e);
            }
        })();

        // Helper: apply color to the current moving object element
        function applyColorToElement(elem, color) {
            if (!elem) return;
            // Use a flat color for the ball/dot (no gradient)
            if (elem.classList.contains('object-ball')) {
                elem.style.background = color;
            } else if (elem.classList.contains('object-bar')) {
                elem.style.background = `linear-gradient(180deg, transparent, ${color}, transparent)`;
            } else if (elem.classList.contains('object-butterfly')) {
                elem.style.color = color;
                elem.style.filter = 'none';
            } else {
                elem.style.background = color;
            }
        }

        // Update a small icon next to a select based on the selected option's data-icon
        function updateIcon(selectElem, iconElem) {
            if (!selectElem || !iconElem) return;
            const opt = selectElem.selectedOptions && selectElem.selectedOptions[0];
            const name = opt && opt.dataset && opt.dataset.icon ? opt.dataset.icon : '';
            if (name) {
                iconElem.className = `ph ph-${name}`;
            } else {
                iconElem.className = 'ph';
            }
        }

        // Build a custom dropdown UI from a native select. Keeps native select in sync.
        function buildCustomDropdown(selectEl, toggleEl, optionsContainer, labelEl, iconEl) {
            if (!selectEl || !toggleEl || !optionsContainer) return;
            // hide native select visually but keep it for value
            selectEl.style.display = 'none';
            // populate options
            optionsContainer.innerHTML = '';
            Array.from(selectEl.options).forEach(opt => {
                const li = document.createElement('li');
                li.tabIndex = 0;
                li.dataset.value = opt.value;
                const iconName = opt.dataset.icon || '';
                // optionally render an icon in each item only if the select option defines a data-icon
                let i = null;
                if (iconName && iconEl !== null) {
                    i = document.createElement('i');
                    i.className = `ph ph-${iconName}`;
                    i.setAttribute('aria-hidden', 'true');
                }
                const span = document.createElement('span');
                span.textContent = opt.textContent;
                if (i) li.appendChild(i);
                li.appendChild(span);
                li.addEventListener('click', () => {
                    selectEl.value = opt.value;
                    selectEl.dispatchEvent(new Event('change', { bubbles: true }));
                    // update toggle text and icon
                    if (labelEl) labelEl.textContent = opt.textContent;
                    if (iconEl) iconEl.className = iconName ? `ph ph-${iconName}` : 'ph';
                    optionsContainer.classList.remove('active');
                });
                optionsContainer.appendChild(li);
            });
            // helper to close other custom dropdowns so only one remains open
            function closeAllCustomDropdowns(except) {
                document.querySelectorAll('.custom-options.active').forEach(el => {
                    if (el === except) return;
                    el.classList.remove('active');
                    el.classList.remove('up');
                    el.style.maxHeight = '';
                });
            }

            // toggle behaviour with overflow-aware opening (up or down)
            toggleEl.addEventListener('click', (e) => {
                e.stopPropagation();
                // close any other open custom dropdowns first
                closeAllCustomDropdowns(optionsContainer);

                const isActive = optionsContainer.classList.contains('active');
                if (isActive) {
                    optionsContainer.classList.remove('active');
                    optionsContainer.classList.remove('up');
                    optionsContainer.style.maxHeight = '';
                    return;
                }

                // estimate desired height (fallback when scrollHeight isn't available)
                const optionCount = Math.max(1, optionsContainer.children.length);
                const desiredHeight = Math.min(optionCount * 40, 400);

                const rect = toggleEl.getBoundingClientRect();
                const spaceBelow = window.innerHeight - rect.bottom - 8;
                const spaceAbove = rect.top - 8;

                if (spaceBelow < desiredHeight && spaceAbove > spaceBelow) {
                    optionsContainer.classList.add('active');
                    optionsContainer.classList.add('up');
                    const maxH = Math.max(80, Math.min(spaceAbove - 16, desiredHeight));
                    optionsContainer.style.maxHeight = `${maxH}px`;
                } else {
                    optionsContainer.classList.add('active');
                    optionsContainer.classList.remove('up');
                    const maxH = Math.max(80, Math.min(spaceBelow - 16, desiredHeight));
                    optionsContainer.style.maxHeight = `${maxH}px`;
                }
            });
            // close on outside click — remove classes and reset maxHeight
            document.addEventListener('click', () => {
                optionsContainer.classList.remove('active');
                optionsContainer.classList.remove('up');
                optionsContainer.style.maxHeight = '';
            });
            // initialize label/icon from select value
            const initOpt = selectEl.selectedOptions && selectEl.selectedOptions[0];
            if (initOpt) {
                if (labelEl) labelEl.textContent = initOpt.textContent;
                const iconName = initOpt.dataset.icon || '';
                if (iconEl && iconName) iconEl.className = `ph ph-${iconName}`;
            }
        }

        // Smoothly animate currentPassesPerSec to target over durationMs milliseconds
        function animatePassesTo(target, durationMs = 1000) {
            if (_passesTween) cancelAnimationFrame(_passesTween);
            const start = performance.now();
            const from = currentPassesPerSec;
            const delta = target - from;
            function step(ts) {
                const t = Math.min(1, (ts - start) / durationMs);
                currentPassesPerSec = from + delta * t;
                if (t < 1) {
                    _passesTween = requestAnimationFrame(step);
                } else {
                    currentPassesPerSec = target;
                    _passesTween = null;
                }
            }
            _passesTween = requestAnimationFrame(step);
        }

        // Smoothly move the object to a target x,y over duration (ms)
        function moveObjectTo(targetX, targetY, duration = 400, cb) {
            const startLeft = parseFloat(getComputedStyle(movingObj).left || 0);
            const startTop = parseFloat(getComputedStyle(movingObj).top || (window.innerHeight - movingObj.offsetHeight) / 2);
            const dx = targetX - startLeft;
            const dy = targetY - startTop;
            const start = performance.now();
            function step(ts) {
                const t = Math.min(1, (ts - start) / duration);
                movingObj.style.left = (startLeft + dx * t) + 'px';
                movingObj.style.top = (startTop + dy * t) + 'px';
                if (t < 1) requestAnimationFrame(step);
                else {
                    if (typeof cb === 'function') cb();
                }
            }
            requestAnimationFrame(step);
        }

        // Initialize audio context
        function initAudio() {
            if (!audioCtx) {
                audioCtx = new AudioContext();
                // create master gain controlled by volume slider
                masterGain = audioCtx.createGain();
                masterGain.gain.value = (volumeLevel || 60) / 100;
                masterGain.connect(audioCtx.destination);
            }
        }

        // ---- Metadata counters and timer ----
        let unpausedSeconds = 0;
        let unpausedInterval = null;
        let bounceCount = 0;
        let passesCount = 0;
        let setsCount = 0;
        let maxPasses = 0;

        function formatTime(seconds) {
            const m = Math.floor(seconds / 60).toString().padStart(2, '0');
            const s = Math.floor(seconds % 60).toString().padStart(2, '0');
            return `${m}:${s}`;
        }

        function updateMetaDisplays() {
            if (unpausedTimeDisplay) unpausedTimeDisplay.textContent = formatTime(unpausedSeconds);
            if (passesDisplay) passesDisplay.textContent = String(passesCount);
            if (setsDisplay) setsDisplay.textContent = String(setsCount);
        }

        function checkPassLimitAndStop() {
            if (maxPasses > 0 && passesCount >= maxPasses && isRunning) {
                // smoothly stop similar to user stop
                animatePassesTo(0, 1000);
                startStopBtn.textContent = '▶ Start';
                startStopBtn.classList.remove('active');
                setTimeout(() => {
                    if (animationId) cancelAnimationFrame(animationId);
                    isRunning = false;
                    setsCount++;
                    updateMetaDisplays();
                    // stop timer but preserve elapsed time (only Reset clears it)
                    stopUnpausedTimer();
                    if (movingObj && movingObj.classList) movingObj.classList.remove('no-transition');
                    // publish final state after stop
                    try { if (liveToken) publishState(liveToken); } catch(e) { console.warn('publish after pass-limit stop failed', e); }
                }, 1000);
            }
        }

        function startUnpausedTimer(reset = false) {
            if (reset) unpausedSeconds = 0;
            if (unpausedInterval) clearInterval(unpausedInterval);
            unpausedInterval = setInterval(() => {
                if (isRunning && !isPaused) {
                    unpausedSeconds++;
                    updateMetaDisplays();
                }
            }, 1000);
            updateMetaDisplays();
        }

        function stopUnpausedTimer() {
            if (unpausedInterval) {
                clearInterval(unpausedInterval);
                unpausedInterval = null;
            }
        }

        function stopAndResetUnpausedTimer() {
            stopUnpausedTimer();
            unpausedSeconds = 0;
            updateMetaDisplays();
        }

        function startInternalPause(duration) {
            isPaused = true;
            // publish pause start
            try { if (liveToken) publishState(liveToken); } catch(e) { console.warn('publish on internal pause start failed', e); }
            // pause timer and reset unpaused time
            if (unpausedInterval) { clearInterval(unpausedInterval); unpausedInterval = null; }
            updateMetaDisplays();
            // smoothly ramp movement to 0, then restore after duration
            animatePassesTo(0, 1000);
            setTimeout(() => {
                isPaused = false;
                if (isRunning) {
                    // ramp back to target
                    animatePassesTo(targetPassesPerSec, 1000);
                    startUnpausedTimer(false);
                }
                // publish pause end
                try { if (liveToken) publishState(liveToken); } catch(e) { console.warn('publish on internal pause end failed', e); }
            }, duration);
        }

        // Sound generation functions
        const SoundGenerators = {
            hihat: (ctx, side) => {
                const noise = ctx.createBufferSource();
                const buffer = ctx.createBuffer(1, ctx.sampleRate * 0.1, ctx.sampleRate);
                const data = buffer.getChannelData(0);
                for (let i = 0; i < data.length; i++) {
                    data[i] = Math.random() * 2 - 1;
                }
                noise.buffer = buffer;

                const filter = ctx.createBiquadFilter();
                filter.type = 'highpass';
                filter.frequency.value = 2000;

                const gain = ctx.createGain();
                gain.gain.setValueAtTime(0.3, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.1);

                const panner = ctx.createStereoPanner();
                panner.pan.value = side === 'left' ? -1 : 1;

                noise.connect(filter);
                filter.connect(gain);
                gain.connect(panner);
                panner.connect(masterGain || ctx.destination);

                noise.start();
                noise.stop(ctx.currentTime + 0.1);
            },

            heartbeat: (ctx, side) => {
                const osc1 = ctx.createOscillator();
                const osc2 = ctx.createOscillator();
                const gain = ctx.createGain();
                const panner = ctx.createStereoPanner();

                osc1.type = 'sine';
                osc1.frequency.value = 80;
                osc2.type = 'sine';
                osc2.frequency.value = 60;

                panner.pan.value = side === 'left' ? -1 : 1;

                // First thump
                gain.gain.setValueAtTime(0.4, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.15);

                osc1.connect(gain);
                osc2.connect(gain);
                gain.connect(panner);
                panner.connect(masterGain || ctx.destination);

                osc1.start();
                osc2.start();
                osc1.stop(ctx.currentTime + 0.15);
                osc2.stop(ctx.currentTime + 0.15);

                // Second thump (delayed)
                setTimeout(() => {
                    const osc3 = ctx.createOscillator();
                    const osc4 = ctx.createOscillator();
                    const gain2 = ctx.createGain();
                    const panner2 = ctx.createStereoPanner();

                    osc3.type = 'sine';
                    osc3.frequency.value = 80;
                    osc4.type = 'sine';
                    osc4.frequency.value = 60;

                    panner2.pan.value = side === 'left' ? -1 : 1;

                    gain2.gain.setValueAtTime(0.3, ctx.currentTime);
                    gain2.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.12);

                    osc3.connect(gain2);
                    osc4.connect(gain2);
                    gain2.connect(panner2);
                    panner2.connect(masterGain || ctx.destination);

                    osc3.start();
                    osc4.start();
                    osc3.stop(ctx.currentTime + 0.12);
                    osc4.stop(ctx.currentTime + 0.12);
                }, 150);
            },

            beep: (ctx, side) => {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                const panner = ctx.createStereoPanner();

                osc.type = 'sine';
                osc.frequency.value = 440;
                panner.pan.value = side === 'left' ? -1 : 1;

                gain.gain.setValueAtTime(0.3, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.1);

                osc.connect(gain);
                gain.connect(panner);
                panner.connect(masterGain || ctx.destination);

                osc.start();
                osc.stop(ctx.currentTime + 0.1);
            },

            bassguitar: (ctx, side) => {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                const panner = ctx.createStereoPanner();

                osc.type = 'triangle';
                osc.frequency.setValueAtTime(110, ctx.currentTime);
                osc.frequency.exponentialRampToValueAtTime(108, ctx.currentTime + 0.3);

                panner.pan.value = side === 'left' ? -1 : 1;

                gain.gain.setValueAtTime(0.5, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.3);

                osc.connect(gain);
                gain.connect(panner);
                panner.connect(masterGain || ctx.destination);

                osc.start();
                osc.stop(ctx.currentTime + 0.3);
            },

            softbell: (ctx, side) => {
                const oscillators = [523.25, 659.25, 783.99].map(freq => {
                    const osc = ctx.createOscillator();
                    osc.type = 'sine';
                    osc.frequency.value = freq;
                    return osc;
                });

                const gain = ctx.createGain();
                const panner = ctx.createStereoPanner();

                panner.pan.value = side === 'left' ? -1 : 1;

                gain.gain.setValueAtTime(0.15, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 1.5);

                oscillators.forEach(osc => {
                    osc.connect(gain);
                    osc.start();
                    osc.stop(ctx.currentTime + 1.5);
                });

                gain.connect(panner);
                panner.connect(masterGain || ctx.destination);
            },

            softflame: (ctx, side) => {
                const noise = ctx.createBufferSource();
                const buffer = ctx.createBuffer(1, ctx.sampleRate * 0.3, ctx.sampleRate);
                const data = buffer.getChannelData(0);
                
                for (let i = 0; i < data.length; i++) {
                    data[i] = (Math.random() * 2 - 1) * Math.exp(-i / (ctx.sampleRate * 0.1));
                }
                noise.buffer = buffer;

                const filter = ctx.createBiquadFilter();
                filter.type = 'bandpass';
                filter.frequency.value = 800;
                filter.Q.value = 0.5;

                const gain = ctx.createGain();
                gain.gain.setValueAtTime(0.15, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.3);

                const panner = ctx.createStereoPanner();
                panner.pan.value = side === 'left' ? -1 : 1;

                noise.connect(filter);
                filter.connect(gain);
                gain.connect(panner);
                panner.connect(masterGain || ctx.destination);

                noise.start();
                noise.stop(ctx.currentTime + 0.3);
            },

            lippop: (ctx, side) => {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                const panner = ctx.createStereoPanner();

                osc.type = 'sine';
                osc.frequency.setValueAtTime(150, ctx.currentTime);
                osc.frequency.exponentialRampToValueAtTime(80, ctx.currentTime + 0.05);

                panner.pan.value = side === 'left' ? -1 : 1;

                gain.gain.setValueAtTime(0.4, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.05);

                osc.connect(gain);
                gain.connect(panner);
                panner.connect(masterGain || ctx.destination);

                osc.start();
                osc.stop(ctx.currentTime + 0.05);
            },

            badminton: (ctx, side) => {
                const noise = ctx.createBufferSource();
                const buffer = ctx.createBuffer(1, ctx.sampleRate * 0.08, ctx.sampleRate);
                const data = buffer.getChannelData(0);
                
                for (let i = 0; i < data.length; i++) {
                    data[i] = Math.random() * 2 - 1;
                }
                noise.buffer = buffer;

                const filter = ctx.createBiquadFilter();
                filter.type = 'bandpass';
                filter.frequency.value = 3000;
                filter.Q.value = 2;

                const gain = ctx.createGain();
                gain.gain.setValueAtTime(0.25, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.08);

                const panner = ctx.createStereoPanner();
                panner.pan.value = side === 'left' ? -1 : 1;

                noise.connect(filter);
                filter.connect(gain);
                gain.connect(panner);
                panner.connect(masterGain || ctx.destination);

                noise.start();
                noise.stop(ctx.currentTime + 0.08);
            }
        };

        // Play selected sound
        function playSound(side) {
            if (currentSound === 'none' || !audioCtx) return;
            
            const generator = SoundGenerators[currentSound];
            if (generator) {
                generator(audioCtx, side);
            }
        }

        // Animation loop
        function animate(ts) {
            if (!isRunning) return;

            if (!lastTimestamp) lastTimestamp = ts || performance.now();
            const now = ts || performance.now();
            const dt = Math.max(0, now - lastTimestamp) / 1000; // seconds
            lastTimestamp = now;

            if (isPaused) {
                animationId = requestAnimationFrame(animate);
                return;
            }

            if (movementMode === 'vertical') {
                // Ensure object is horizontally centered while moving vertically
                const centerX = Math.max(0, Math.round((window.innerWidth - movingObj.offsetWidth) / 2));
                movingObj.style.left = centerX + 'px';

                const maxV = window.innerHeight - movingObj.offsetHeight;
                // compute pixels/sec from passes/sec: one full pass = down+up = 2*maxV distance
                const pixelsPerSecV = Math.max(1, currentPassesPerSec * 2 * maxV);
                const deltaV = pixelsPerSecV * dt;
                vPosition += deltaV * vDirection;

                if (vPosition >= maxV) {
                    vPosition = maxV;
                    vDirection = -1;
                    playSound('right');
                    bounceCount++;
                    if (bounceCount % 2 === 0) {
                        passesCount++;
                        updateMetaDisplays();
                        checkPassLimitAndStop();
                    }
                    if (pauseDuration > 0) startInternalPause(pauseDuration);
                } else if (vPosition <= 0) {
                    vPosition = 0;
                    vDirection = 1;
                    playSound('left');
                    bounceCount++;
                    if (bounceCount % 2 === 0) {
                        passesCount++;
                        updateMetaDisplays();
                        checkPassLimitAndStop();
                    }
                    if (pauseDuration > 0) startInternalPause(pauseDuration);
                }

                movingObj.style.top = vPosition + 'px';
            } else if (movementMode && movementMode.indexOf('diag') === 0) {
                // Move along diagonal using normalized progress so x/y remain perfectly synchronized
                // diagStart, diagDX, diagDY, diagLength, diagProgress, diagDir are used
                const maxX = window.innerWidth - movingObj.offsetWidth;
                const maxY = window.innerHeight - movingObj.offsetHeight;

                // step along the diagonal: use passes/sec -> progress/sec mapping
                // progress goes 0->1->0 per full pass, so progressPerSec = 2 * passesPerSec
                const progressPerSec = 2 * (currentPassesPerSec || 0.0001);
                const deltaProgress = progressPerSec * dt * diagDir;
                diagProgress += deltaProgress;

                let hitCorner = false;
                if (diagProgress >= 1) {
                    diagProgress = 1;
                    hitCorner = true;
                } else if (diagProgress <= 0) {
                    diagProgress = 0;
                    hitCorner = true;
                }

                // compute absolute positions from progress
                position = diagStart.x + diagDX * diagProgress;
                vPosition = diagStart.y + diagDY * diagProgress;

                // clamp to bounds just in case
                position = Math.max(0, Math.min(maxX, position));
                vPosition = Math.max(0, Math.min(maxY, vPosition));

                if (hitCorner) {
                    // reverse travel direction along diagonal
                    diagDir = -diagDir;
                    // reflect progress slightly to avoid sticking
                    // play sound and bookkeeping
                    playSound('right');
                    bounceCount++;
                    if (bounceCount % 2 === 0) {
                        passesCount++;
                        updateMetaDisplays();
                        checkPassLimitAndStop();
                    }
                    if (pauseDuration > 0) startInternalPause(pauseDuration);
                }

                movingObj.style.left = position + 'px';
                movingObj.style.top = vPosition + 'px';
            } else if (movementMode === 'figure8') {
                // Horizontal figure‑8 using Gerono-like parametric form
                // Map passes/sec to angular velocity: loops per second = passesPerSec, angularVel = 2π * loops/sec
                const angularVel = 2 * Math.PI * (currentPassesPerSec || 0.0001);
                figTheta += angularVel * dt * figDir;
                // keep theta bounded
                if (figTheta > Math.PI * 1000 || figTheta < -Math.PI * 1000) figTheta = figTheta % (Math.PI * 2);

                // detect crossings of multiples of PI (loop endpoints) to count bounces/passes
                try {
                    const prevBucket = Math.floor(prevFigTheta / Math.PI);
                    const curBucket = Math.floor(figTheta / Math.PI);
                    if (prevBucket !== curBucket) {
                        // crossed a multiple of PI
                        bounceCount++;
                        // play a side sound for feedback
                        playSound(bounceCount % 2 === 0 ? 'right' : 'left');
                        if (bounceCount % 2 === 0) {
                            passesCount++;
                            updateMetaDisplays();
                            checkPassLimitAndStop();
                        }
                        if (pauseDuration > 0) startInternalPause(pauseDuration);
                    }
                } catch (err) {}
                prevFigTheta = figTheta;

                // x = A * sin(t), y = (B/2) * sin(2t)
                const x = figCenter.x + figA * Math.sin(figTheta);
                const y = figCenter.y + (figB / 2) * Math.sin(2 * figTheta);

                movingObj.style.left = Math.max(0, Math.min(window.innerWidth - movingObj.offsetWidth, x)) + 'px';
                movingObj.style.top = Math.max(0, Math.min(window.innerHeight - movingObj.offsetHeight, y)) + 'px';
            } else {
                const maxPosition = Math.max(1, window.innerWidth - movingObj.offsetWidth);
                // compute pixels/sec from passes/sec: one full pass = left->right->left = 2*maxPosition distance
                const pixelsPerSec = Math.max(1, currentPassesPerSec * 2 * maxPosition);
                const deltaX = pixelsPerSec * dt;
                position += deltaX * direction;

                if (position >= maxPosition) {
                    position = maxPosition;
                    direction = -1;
                    playSound('right');
                    bounceCount++;
                    if (bounceCount % 2 === 0) {
                        passesCount++;
                        updateMetaDisplays();
                        checkPassLimitAndStop();
                    }
                    if (pauseDuration > 0) startInternalPause(pauseDuration);
                } else if (position <= 0) {
                    position = 0;
                    direction = 1;
                    playSound('left');
                    bounceCount++;
                    if (bounceCount % 2 === 0) {
                        passesCount++;
                        updateMetaDisplays();
                        checkPassLimitAndStop();
                    }
                    if (pauseDuration > 0) startInternalPause(pauseDuration);
                }

                movingObj.style.left = position + 'px';
            }

            animationId = requestAnimationFrame(animate);
        }

        // Start/Stop (with smooth 1s speed transitions)
        startStopBtn.addEventListener('click', () => {
            initAudio();
                if (!isRunning) {
                    // start
                    isRunning = true;
                    startStopBtn.textContent = '⏸ Pause';
                    startStopBtn.classList.add('active');
                    // begin animation loop and timer (do not reset elapsed time on resume)
                    lastTimestamp = null;
                    if (movingObj && movingObj.classList) movingObj.classList.add('no-transition');
                    animationId = requestAnimationFrame(animate);
                    startUnpausedTimer(false);
                    // ramp passes/sec up to target over 1s
                    animatePassesTo(targetPassesPerSec, 1000);

                } else {
                    // stop with smooth ramp down over 1s, then stop
                    // immediately mark not running so live viewers can react
                    isRunning = false;
                    try { if (liveToken) publishState(liveToken); } catch(e) { console.warn('publish on immediate stop failed', e); }
                    // ramp passes/sec to 0
                    animatePassesTo(0, 1000);
                    startStopBtn.textContent = '▶ Start';
                    startStopBtn.classList.remove('active');
                    // after ramp completes, cancel animation and mark a set
                    setTimeout(() => {
                        if (animationId) cancelAnimationFrame(animationId);
                        // isRunning already false
                        setsCount++;
                        updateMetaDisplays();
                        // stop timer but do not reset elapsed time (reset only via Reset button)
                        stopUnpausedTimer();
                        if (movingObj && movingObj.classList) movingObj.classList.remove('no-transition');
                        // publish the final stopped state so live viewers see the pause
                        try { if (liveToken) publishState(liveToken); } catch (e) { console.warn('publish after stop failed', e); }
                    }, 1000);
                }
        });

        // Speed control (smooth 1s transition of passes/sec)
        // Map raw slider to passes/sec using PASSES_PER_STEP
        speedSlider.addEventListener('input', (e) => {
            const raw = parseInt(e.target.value, 10) || 1;
            // update displayed slider value (show raw UI value)
            speedValue.textContent = String(raw);
            // compute target passes/sec and tween
            targetPassesPerSec = raw * PASSES_PER_STEP;
            animatePassesTo(targetPassesPerSec, 1000);
        });

        // Pause control
        pauseSlider.addEventListener('input', (e) => {
            pauseDuration = parseInt(e.target.value);
            pauseValue.textContent = pauseDuration;
        });

        // Sound selection (dropdown)
        soundSelect.addEventListener('change', (e) => {
            initAudio();
            currentSound = e.target.value;
            // keep native icon placeholder (if present) in sync
            updateIcon(soundSelect, soundIcon);
        });

        // Volume control (smooth 1s gain ramp)
        volumeControl.addEventListener('input', (e) => {
            volumeLevel = parseInt(e.target.value, 10) || 0;
            if (masterGain && audioCtx) {
                const now = audioCtx.currentTime;
                const target = volumeLevel / 100;
                try { masterGain.gain.cancelScheduledValues(now); } catch (err) {}
                masterGain.gain.setValueAtTime(masterGain.gain.value, now);
                masterGain.gain.linearRampToValueAtTime(target, now + 1.0);
            }
        });

        // Passes limit input handling
        if (passesLimitInput) {
            passesLimitInput.addEventListener('input', (e) => {
                const v = parseInt(e.target.value, 10);
                maxPasses = Number.isFinite(v) && v > 0 ? v : 0;
                // if already exceeded, stop immediately
                if (maxPasses > 0 && passesCount >= maxPasses) checkPassLimitAndStop();
            });
        }

        // Reset metadata: time, passes and sets
        const resetMetaBtn = document.getElementById('reset-meta');
        if (resetMetaBtn) {
            resetMetaBtn.addEventListener('click', () => {
                // stop the unpaused timer and reset counters
                try { stopUnpausedTimer(); } catch (e) {}
                unpausedSeconds = 0;
                passesCount = 0;
                setsCount = 0;
                // update displays immediately
                try { updateMetaDisplays(); } catch (e) {}
            });
        }

        // Object type
        objectType.addEventListener('change', (e) => {
            const type = e.target.value;
            let html = '';
            
            switch(type) {
                case 'ball':
                    html = '<div class="object-ball"></div>';
                    break;
                case 'bar':
                    html = '<div class="object-bar"></div>';
                    break;
                case 'butterfly':
                    html = '<div class="object-butterfly"><i class="ph ph-butterfly" style="font-size:48px"></i></div>';
                    break;
                case 'dot':
                    html = '<div class="object-ball" style="width: 30px; height: 30px;"></div>';
                    break;
            }
            
            movingObj.innerHTML = html;
            // show object color controls and apply current color to new element
            if (objectControls) objectControls.classList.add('active');
            const elem = movingObj.firstElementChild;
            if (elem && objectColor && objectColor.value) {
                applyColorToElement(elem, objectColor.value);
            }
            // update object type icon next to the select
            updateIcon(objectType, objectTypeIcon);
        });

        // Background
        background.addEventListener('change', (e) => {
            const val = e.target.value;
            if (val === 'custom') {
                customControls.classList.add('active');
                stimArea.className = '';
                stimArea.style.backgroundImage = '';
                stimArea.style.background = customColor.value || '#ffffff';
            } else {
                customControls.classList.remove('active');
                stimArea.style.backgroundImage = '';
                stimArea.style.background = '';
                stimArea.className = 'bg-' + val;
            }
        });

        customColor.addEventListener('input', (e) => {
            if (background.value !== 'custom') background.value = 'custom';
            customControls.classList.add('active');
            stimArea.className = '';
            stimArea.style.backgroundImage = '';
            stimArea.style.background = e.target.value;
        });

        // Object color picker
        if (objectColor) {
            objectColor.addEventListener('input', (e) => {
                const color = e.target.value;
                const elem = movingObj.firstElementChild;
                if (elem) applyColorToElement(elem, color);
                if (objectControls) objectControls.classList.add('active');
            });
        }

        if (clearObjectColor) {
            clearObjectColor.addEventListener('click', () => {
                if (objectColor) objectColor.value = '#ffffff';
                const elem = movingObj.firstElementChild;
                if (elem) {
                    // remove inline styles to revert to CSS defaults
                    elem.style.background = '';
                    elem.style.color = '';
                    elem.style.filter = '';
                }
            });
        }

        clearCustom.addEventListener('click', () => {
            customColor.value = '#ffffff';
            customControls.classList.remove('active');
            background.value = 'dark';
            stimArea.className = 'bg-dark';
            stimArea.style.backgroundImage = '';
            stimArea.style.background = '';
        });

        // Toggle controls
        function updateMiniVisibility() {
            const mini = document.getElementById('mini-toggle-controls');
            if (!mini) return;
            if (controls.classList.contains('hidden')) {
                mini.style.display = 'inline-flex';
            } else {
                mini.style.display = 'none';
            }
        }

        toggleControls.addEventListener('click', () => {
            controls.classList.toggle('hidden');
            toggleControls.innerHTML = controls.classList.contains('hidden') ? '<i class="ph ph-gear"></i> Show Controls' : '<i class="ph ph-gear"></i> Hide Controls';
            updateMiniVisibility();
        });

        // mini toggle to unhide controls
        const miniToggle = document.getElementById('mini-toggle-controls');
        if (miniToggle) {
            miniToggle.addEventListener('click', () => {
                controls.classList.remove('hidden');
                toggleControls.innerHTML = '<i class="ph ph-gear"></i> Hide Controls';
                updateMiniVisibility();
            });
        }
        // initialize mini visibility
        updateMiniVisibility();

        // --- Live publishing helpers ---
        let liveToken = null;
        let stateSequence = 0;

function debounce(fn, wait) {
    let t = null;
    return function(...args) {
        clearTimeout(t);
        t = setTimeout(() => fn.apply(this, args), wait);
    };
}

function gatherState() {
    // Read the selected direction directly from DOM
    const dirEl = document.querySelector('.direction-presets .dir-square.selected');
    const direction = dirEl ? dirEl.dataset.direction : 'rtl';
    
    return {
        // Control state
        isRunning: !!isRunning,
        isPaused: !!isPaused,
        
        // Movement config (logical only, NO internal positions)
        movementMode: movementMode || 'horizontal',
        direction: direction,
        speed: speedSlider ? parseInt(speedSlider.value, 10) : 1,
        pause: pauseSlider ? parseInt(pauseSlider.value, 10) : 0,
        
        // Visual config
        objectType: objectType ? objectType.value : 'ball',
        objectColor: objectColor ? objectColor.value : '#ffffff',
        background: background ? background.value : 'blue',
        customColor: customColor ? customColor.value : '#ffffff',
        
        // Audio config
        sound: soundSelect ? soundSelect.value : 'beep',
        volume: volumeControl ? parseInt(volumeControl.value, 10) : 60,
        
        // Sync metadata - CRITICAL for ordering
        sequence: ++stateSequence,
        timestamp: Math.floor(Date.now() / 1000)
    };
}

async function publishState(token) {
    if (!token) return;
    
    const state = gatherState();
    
    // Debug log
    console.log('[emdr admin] publishing state seq=' + state.sequence, state);
    
    try {
        const res = await fetch('/admin/emdr/state/' + encodeURIComponent(token), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(state)
        });
        
        const result = await res.json();
        console.log('[emdr admin] publish response', result);
        
        if (!result.success) {
            console.warn('[emdr admin] publish failed', result);
        }
    } catch (err) {
        console.warn('[emdr admin] publish error', err);
    }
}

// Debounced version for control changes
const schedulePublish = debounce(() => {
    if (liveToken) publishState(liveToken);
}, 150);

// Wire control change events to scheduling publish
[speedSlider, pauseSlider, volumeControl, objectType, objectColor, background, customColor, soundSelect, passesLimitInput].forEach(el => {
    if (!el) return;
    el.addEventListener('input', schedulePublish);
    el.addEventListener('change', schedulePublish);
});

// Direction presets click handling (visual selection + runtime update + publish)
const dirPresetsEl = document.querySelector('.direction-presets');
if (dirPresetsEl) {
    // ensure default selected is right-to-left if none selected
    const defaultEl = dirPresetsEl.querySelector('.dir-square[data-direction="rtl"]');
    if (defaultEl && !dirPresetsEl.querySelector('.dir-square.selected')) defaultEl.classList.add('selected');

    dirPresetsEl.addEventListener('click', (ev) => {
        const el = ev.target.closest('.dir-square');
        if (!el) return;
        // Visual selection
        dirPresetsEl.querySelectorAll('.dir-square').forEach(d => d.classList.remove('selected'));
        el.classList.add('selected');

        const dir = el.dataset.direction;
            if (dir === 'vertical') {
            const centerX = Math.max(0, Math.round((window.innerWidth - movingObj.offsetWidth) / 2));
            const startY = 0;
            movingObj.style.transform = 'translateY(0)';
            movingObj.style.left = centerX + 'px';
            moveObjectTo(centerX, startY, 400, () => {
                movementMode = 'vertical';
                vPosition = startY;
                vDirection = 1;
                if (!isRunning) movingObj.style.top = startY + 'px';
                try { if (liveToken) publishState(liveToken); } catch(e) {}
            });
        }
        else if (dir === 'diag-rtl' || dir === 'diag-ltr') {
            const maxX = Math.max(0, window.innerWidth - movingObj.offsetWidth);
            const maxY = Math.max(0, window.innerHeight - movingObj.offsetHeight);
            let startX, startY, endX, endY;
            if (dir === 'diag-ltr') {
                startX = 0; startY = 0;
                endX = maxX; endY = maxY;
            } else {
                startX = maxX; startY = 0;
                endX = 0; endY = maxY;
            }
            diagStart.x = startX; diagStart.y = startY;
            diagDX = endX - startX; diagDY = endY - startY;
            diagLength = Math.hypot(diagDX, diagDY) || 1;
            diagProgress = 0; diagDir = 1;
            moveObjectTo(startX, startY, 400, () => {
                movementMode = dir;
                position = startX;
                vPosition = startY;
                if (!isRunning) {
                    movingObj.style.left = position + 'px';
                    movingObj.style.top = vPosition + 'px';
                }
                try { if (liveToken) publishState(liveToken); } catch(e) {}
            });
        } else if (dir === 'figure8') {
            const centerX = Math.max(0, Math.round((window.innerWidth - movingObj.offsetWidth) / 2));
            const centerY = Math.max(0, Math.round((window.innerHeight - movingObj.offsetHeight) / 2));
            figCenter.x = centerX; figCenter.y = centerY;
                    // Use nearly full half-extent so figure-8 spans edge-to-edge (minus margins)
                    figA = Math.max(80, Math.min(centerX - 20, Math.floor((window.innerWidth - movingObj.offsetWidth) / 2) - 20));
                    figB = Math.max(60, Math.min(centerY - 20, Math.floor((window.innerHeight - movingObj.offsetHeight) / 2) - 20));
            figTheta = 0; figDir = 1;
            moveObjectTo(centerX + figA * Math.sin(figTheta), centerY + (figB / 2) * Math.sin(2 * figTheta), 300, () => {
                movementMode = 'figure8';
                if (!isRunning) {
                    movingObj.style.left = (figCenter.x + figA * Math.sin(figTheta)) + 'px';
                    movingObj.style.top = (figCenter.y + (figB / 2) * Math.sin(2 * figTheta)) + 'px';
                }
                try { if (liveToken) publishState(liveToken); } catch(e) {}
            });
        } else {
            // horizontal preset - first move object to vertical center, then enable horizontal mode
            const centerY = Math.max(0, Math.round((window.innerHeight - movingObj.offsetHeight) / 2));
            const curLeft = parseFloat(getComputedStyle(movingObj).left) || position || 0;
            moveObjectTo(curLeft, centerY, 400, () => {
                movementMode = 'horizontal';
                position = Math.max(0, Math.min(window.innerWidth - movingObj.offsetWidth, parseFloat(getComputedStyle(movingObj).left) || 0));
                movingObj.style.transform = 'translateY(-50%)';
                movingObj.style.top = centerY + 'px';
                direction = dir === 'rtl' ? -1 : 1;
                if (!isRunning) {
                    position = curLeft;
                    movingObj.style.left = position + 'px';
                }
                try { if (liveToken) publishState(liveToken); } catch(e) {}
            });
        }
        
        // NOTE: publishing moved into callbacks to ensure movementMode is updated
    });
}

// Ensure start/stop publishes immediately (no debounce)
const originalStartStopHandler = startStopBtn.onclick;
startStopBtn.addEventListener('click', () => {
    setTimeout(() => {
        if (liveToken) publishState(liveToken);
    }, 10);
});

// Publish link generation UI handlers
const publishBtn = document.getElementById('publish-live-btn');
// moved link display to metadata area
const publishArea = document.getElementById('publish-link-area');
const publishInput = document.getElementById('publish-link-input');

function genToken() {
    try {
        return (crypto && crypto.randomUUID) ? crypto.randomUUID() : ('tk_' + Math.random().toString(36).slice(2, 10));
    } catch (e) {
        return ('tk_' + Math.random().toString(36).slice(2, 10));
    }
}

if (publishBtn) {
    publishBtn.addEventListener('click', async () => {
        if (!liveToken) {
            liveToken = genToken();
            stateSequence = 0; // Reset sequence for new session
        }
        
        const url = window.location.origin + '/emdr/live/' + encodeURIComponent(liveToken);
        publishInput.value = url;
        if (publishArea) publishArea.style.display = 'inline-flex';
        
        // Publish initial state immediately
        await publishState(liveToken);
    });
}
// --- End live publishing helpers ---

// --- End live publishing helpers ---
    </script>
</body>
</html>
