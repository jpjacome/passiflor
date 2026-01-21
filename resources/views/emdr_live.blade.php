<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EMDR - Live Session</title>
    <link rel="stylesheet" href="{{ asset('css/general.css') }}">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'quicksand', sans-serif;
            background: var(--color-4);
            color: var(--color-1);
            overflow: hidden;
        }
        #stimulation-area {
            position: fixed;
            inset: 0;
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
        .no-transition {
            transition: none !important;
        }
        
        .object-ball {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--color-1);
        }
        .object-bar {
            width: 8px;
            height: 80vh;
            background: linear-gradient(180deg, transparent, var(--color-2), transparent);
        }
        .object-butterfly {
            font-size: 48px;
        }
        
        .status {
            position: fixed;
            left: 12px;
            bottom: 12px;
            background: rgba(0, 0, 0, 0.6);
            padding: 8px 12px;
            border-radius: 8px;
            color: white;
            font-size: 12px;
            z-index: 120;
            backdrop-filter: blur(10px);
        }
        .status.connected { background: rgba(34, 197, 94, 0.6); }
        .status.error { background: rgba(239, 68, 68, 0.6); }
        .start-overlay {
            position: fixed;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 200;
            pointer-events: auto;
            background: var(--color-2);
            transition: opacity 1500ms ease, visibility 400ms ease;
            opacity: 1;
            visibility: visible;
        }
        .start-overlay-inner {
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 24px;
        }

        .start-logo {
            width: 220px;
            height: auto;
            filter: drop-shadow(0 8px 20px rgba(0,0,0,0.4));
        }

        .start-button {
            margin-top: 10px;
            pointer-events: auto;
            background: rgba(255,255,255,0.06);
            color: #fff;
            border: 2px solid rgba(255,255,255,0.12);
            padding: 16px 36px;
            font-size: 20px;
            border-radius: 12px;
            cursor: pointer;
            backdrop-filter: blur(6px);
            transition: transform 600ms ease, background 180ms ease, opacity 200ms ease, border 1s ease-in;
        }
        .start-button:hover{
            border: 2px solid var(--color-1);
            transform: scale(0.95);
        }

        .start-button:active { transform: scale(0.98); }

        .start-overlay.hidden {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div id="stimulation-area" class="bg-blue">
        <div id="moving-object">
            <div class="object-ball"></div>
        </div>
    </div>

    <div class="start-overlay" id="start-overlay">
        <div class="start-overlay-inner">
            <img src="{{ asset('imgs/logo4-ver.png') }}" alt="Logo" class="start-logo">
            <button id="start-btn" class="start-button">Iniciar</button>
        </div>
    </div>

    <script>
        const token = @json($token);
        const statusEl = document.getElementById('live-status');
        const stimArea = document.getElementById('stimulation-area');
        const movingObj = document.getElementById('moving-object');

        // Animation state
        let isRunning = false;
        let isPaused = false;
        let animationId = null;
        let lastTimestamp = null;
        
        // Movement state
        let position = 0;
        let direction = 1;
        let movementMode = 'horizontal';
        let vPosition = 0;
        let vDirection = 1;
        
        // Diagonal state
        let diagProgress = 0;
        let diagDir = 1;
        let diagStart = { x: 0, y: 0 };
        let diagDX = 0;
        let diagDY = 0;
        let diagLength = 1;
        
        // Figure-8 state
        let figTheta = 0;
        let figDir = 1;
        let figCenter = { x: 0, y: 0 };
        let figA = 150;
        let figB = 90;
        let prevFigTheta = 0;
        
        // Speed control
        const PASSES_PER_STEP = 0.5;
        let currentPassesPerSec = 0.5;
        let targetPassesPerSec = 0.5;
        let _passesTween = null;
        
        // Audio
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        let audioCtx = null;
        let masterGain = null;
        let currentSound = 'beep';
        
        // Sync tracking
        let lastSequence = 0;
        let lastAppliedState = null;

        // ===== ANIMATION HELPERS =====
        
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

        // ===== AUDIO SETUP =====
        
        function initAudio() {
            if (!audioCtx) {
                audioCtx = new AudioContext();
                masterGain = audioCtx.createGain();
                masterGain.gain.value = 0.6;
                masterGain.connect(audioCtx.destination);
            }
        }

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

        function playSound(side) {
            // Ensure audio context exists and attempt resume if suspended
            try { initAudio(); } catch (e) {}
            if (!audioCtx || currentSound === 'none') return;
            try {
                if (audioCtx.state === 'suspended') {
                    audioCtx.resume().catch(() => {});
                }
            } catch (e) {}
            const generator = SoundGenerators[currentSound];
            if (!generator) {
                console.warn('[emdr_live] Requested sound generator not found:', currentSound, ' — falling back to beep');
                SoundGenerators.beep(audioCtx, side);
            } else {
                console.log('[emdr_live] Playing sound:', currentSound, 'side:', side);
                generator(audioCtx, side);
            }
        }

        // ===== VISUAL UPDATES =====

        function applyObject(type, color) {
            let html = '';
            switch (type) {
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
                    html = '<div class="object-ball" style="width:30px;height:30px"></div>';
                    break;
            }
            
            movingObj.innerHTML = html;
            const elem = movingObj.firstElementChild;
            if (!elem) return;
            
            if (elem.classList.contains('object-butterfly')) {
                elem.style.color = color || '#ffffff';
                elem.style.background = '';
            } else if (elem.classList.contains('object-bar')) {
                elem.style.background = color ? `linear-gradient(180deg, transparent, ${color}, transparent)` : '';
            } else {
                elem.style.background = color || '#ffffff';
            }
        }

        function applyBackground(bg, custom) {
            if (bg === 'custom') {
                stimArea.className = '';
                stimArea.style.background = custom || '#ffffff';
            } else {
                stimArea.className = 'bg-' + bg;
                stimArea.style.background = '';
            }
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

        // ===== GEOMETRY INITIALIZATION =====

        function initializeGeometry(mode) {
            console.log('[emdr_live] Initializing geometry for mode:', mode);
            
            movementMode = mode;
            
            if (mode === 'vertical') {
                const centerX = Math.max(0, Math.round((window.innerWidth - movingObj.offsetWidth) / 2));
                movingObj.style.left = centerX + 'px';
                movingObj.style.transform = 'translateY(0)';
                vPosition = 0;
                vDirection = 1;
                movingObj.style.top = vPosition + 'px';
            }
            else if (mode === 'diag-rtl' || mode === 'diag-ltr') {
                const maxX = Math.max(0, window.innerWidth - movingObj.offsetWidth);
                const maxY = Math.max(0, window.innerHeight - movingObj.offsetHeight);
                
                let startX, startY, endX, endY;
                if (mode === 'diag-ltr') {
                    startX = 0; startY = 0;
                    endX = maxX; endY = maxY;
                } else {
                    startX = maxX; startY = 0;
                    endX = 0; endY = maxY;
                }
                
                diagStart.x = startX;
                diagStart.y = startY;
                diagDX = endX - startX;
                diagDY = endY - startY;
                diagLength = Math.hypot(diagDX, diagDY) || 1;
                diagProgress = 0;
                diagDir = 1;
                
                position = diagStart.x;
                vPosition = diagStart.y;
                movingObj.style.left = position + 'px';
                movingObj.style.top = vPosition + 'px';
                movingObj.style.transform = 'translateY(0)';
            }
            else if (mode === 'figure8') {
                const centerX = Math.max(0, Math.round((window.innerWidth - movingObj.offsetWidth) / 2));
                const centerY = Math.max(0, Math.round((window.innerHeight - movingObj.offsetHeight) / 2));
                figCenter.x = centerX;
                figCenter.y = centerY;
                // Use nearly full half-extent so figure-8 spans edge-to-edge (minus margins)
                figA = Math.max(80, Math.min(centerX - 20, Math.floor((window.innerWidth - movingObj.offsetWidth) / 2) - 20));
                figB = Math.max(60, Math.min(centerY - 20, Math.floor((window.innerHeight - movingObj.offsetHeight) / 2) - 20));
                figTheta = 0;
                figDir = 1;
                movingObj.style.left = (figCenter.x + figA * Math.sin(figTheta)) + 'px';
                movingObj.style.top = (figCenter.y + (figB / 2) * Math.sin(2 * figTheta)) + 'px';
                movingObj.style.transform = 'translateY(0)';
            }
            else {
                // Horizontal
                const centerY = Math.max(0, Math.round((window.innerHeight - movingObj.offsetHeight) / 2));
                movingObj.style.transform = 'translateY(-50%)';
                movingObj.style.top = centerY + 'px';
                position = 0;
                direction = 1;
                movingObj.style.left = position + 'px';
            }
        }

        // ===== ANIMATION LOOP =====

        function animate(ts) {
            if (!isRunning) return;
            
            if (!lastTimestamp) lastTimestamp = ts || performance.now();
            const now = ts || performance.now();
            const dt = Math.max(0, now - lastTimestamp) / 1000;
            lastTimestamp = now;

            if (movementMode === 'vertical') {
                const centerX = Math.max(0, Math.round((window.innerWidth - movingObj.offsetWidth) / 2));
                movingObj.style.left = centerX + 'px';
                const maxV = window.innerHeight - movingObj.offsetHeight;
                const pixelsPerSecV = Math.max(1, currentPassesPerSec * 2 * maxV);
                const deltaV = pixelsPerSecV * dt;
                vPosition += deltaV * vDirection;
                
                if (vPosition >= maxV) {
                    vPosition = maxV;
                    vDirection = -1;
                    playSound('right');
                } else if (vPosition <= 0) {
                    vPosition = 0;
                    vDirection = 1;
                    playSound('left');
                }
                movingObj.style.top = vPosition + 'px';
            }
            else if (movementMode === 'diag-rtl' || movementMode === 'diag-ltr') {
                const maxX = window.innerWidth - movingObj.offsetWidth;
                const maxY = window.innerHeight - movingObj.offsetHeight;
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
                
                position = diagStart.x + diagDX * diagProgress;
                vPosition = diagStart.y + diagDY * diagProgress;
                position = Math.max(0, Math.min(maxX, position));
                vPosition = Math.max(0, Math.min(maxY, vPosition));
                
                if (hitCorner) {
                    diagDir = -diagDir;
                    playSound('right');
                }
                
                movingObj.style.left = position + 'px';
                movingObj.style.top = vPosition + 'px';
            }
            else if (movementMode === 'figure8') {
                const angularVel = 2 * Math.PI * (currentPassesPerSec || 0.0001);
                figTheta += angularVel * dt * figDir;
                
                const x = figCenter.x + figA * Math.sin(figTheta);
                const y = figCenter.y + (figB / 2) * Math.sin(2 * figTheta);
                
                movingObj.style.left = Math.max(0, Math.min(window.innerWidth - movingObj.offsetWidth, x)) + 'px';
                movingObj.style.top = Math.max(0, Math.min(window.innerHeight - movingObj.offsetHeight, y)) + 'px';
            }
            else {
                // Horizontal
                const maxPosition = Math.max(1, window.innerWidth - movingObj.offsetWidth);
                const pixelsPerSec = Math.max(1, currentPassesPerSec * 2 * maxPosition);
                const deltaX = pixelsPerSec * dt;
                position += deltaX * direction;
                
                if (position >= maxPosition) {
                    position = maxPosition;
                    direction = -1;
                    playSound('right');
                } else if (position <= 0) {
                    position = 0;
                    direction = 1;
                    playSound('left');
                }
                movingObj.style.left = position + 'px';
            }

            animationId = requestAnimationFrame(animate);
        }

        // ===== STATE APPLICATION =====

        function detectChanges(oldState, newState) {
            if (!oldState) {
                return Object.keys(newState).reduce((acc, key) => {
                    acc[key] = true;
                    return acc;
                }, {});
            }
            
            const changed = {};
            for (let key in newState) {
                if (newState[key] !== oldState[key]) {
                    changed[key] = true;
                }
            }
            return changed;
        }

        function applyState(s) {
            if (!s) return;

            // Deep debug: log incoming state and current runtime state
            try {
                console.log('[emdr_live] applyState called', {
                    incomingSeq: s.sequence,
                    incomingDir: s.direction,
                    lastSeq: lastAppliedState && lastAppliedState.sequence,
                    lastDir: lastAppliedState && lastAppliedState.direction,
                    runtime: { movementMode, direction, position, currentPassesPerSec, isRunning }
                });
            } catch (e) {}

            const changed = detectChanges(lastAppliedState, s);

            // If direction differs from lastAppliedState but wasn't flagged (edge cases), force it
            if (!changed.direction && lastAppliedState && (s.direction !== lastAppliedState.direction)) {
                changed.direction = true;
                console.log('[emdr_live] Forced direction change detection', 'from', lastAppliedState.direction, 'to', s.direction);
            }

            console.log('[emdr_live] Applying state seq=' + s.sequence, 'changed=', Object.keys(changed));
            
            // Background
            if (changed.background || changed.customColor) {
                applyBackground(s.background || 'blue', s.customColor || '#ffffff');
            }
            
            // Object
            if (changed.objectType || changed.objectColor) {
                applyObject(s.objectType || 'ball', s.objectColor || '#ffffff');
            }
            
            // Audio
            if (changed.sound) {
                currentSound = s.sound || 'beep';
            }
            if (changed.volume) {
                try {
                    initAudio();
                    if (masterGain && audioCtx) {
                        const now = audioCtx.currentTime;
                        const target = (s.volume || 60) / 100;
                        masterGain.gain.cancelScheduledValues(now);
                        masterGain.gain.setValueAtTime(masterGain.gain.value, now);
                        masterGain.gain.linearRampToValueAtTime(target, now + 1.0);
                    }
                } catch (e) {
                    console.warn('Volume update failed', e);
                }
            }
            
            // Movement mode initialization
            if (changed.movementMode) {
                initializeGeometry(s.movementMode || 'horizontal');
            }

            // Direction changes (e.g. 'rtl' -> start at right and move left)
            if (changed.direction) {
                const dir = s.direction || 'rtl';
                console.log('[emdr_live] direction change to', dir, 'movementMode', movementMode, 'prevDir', direction, 'prevPos', position);
                if (movementMode === 'horizontal') {
                    const maxPosition = Math.max(0, window.innerWidth - movingObj.offsetWidth);
                    const targetX = dir === 'rtl' ? maxPosition : 0;
                    const curTop = parseFloat(getComputedStyle(movingObj).top) || Math.max(0, Math.round((window.innerHeight - movingObj.offsetHeight) / 2));

                    // Update runtime numeric state immediately so the animation loop uses the new values
                    direction = dir === 'rtl' ? -1 : 1;
                    position = targetX;

                    // Immediately set styles to avoid visual race
                    movingObj.style.left = position + 'px';
                    movingObj.style.top = curTop + 'px';

                    // Optional: animate a short smoothing transition to make the change less jarring
                    moveObjectTo(targetX, curTop, 250, () => {
                        console.log('[emdr_live] direction applied', 'dirVar=', direction, 'pos=', position);
                    });
                }
            }
            
            // Speed
            if (changed.speed) {
                const raw = parseInt(s.speed, 10) || 1;
                targetPassesPerSec = raw * PASSES_PER_STEP;
                
                // Only ramp if not paused and running
                if (!s.isPaused && s.isRunning) {
                    animatePassesTo(targetPassesPerSec, 1000);
                }
            }
            
            // Pause state
            if (changed.isPaused) {
                if (s.isPaused) {
                    animatePassesTo(0, 1000);
                } else if (s.isRunning) {
                    animatePassesTo(targetPassesPerSec, 1000);
                }
            }
            
            // Start/Stop
            if (changed.isRunning) {
                if (s.isRunning && !isRunning) {
                    console.log('[emdr_live] Starting animation');
                    isRunning = true;
                    lastTimestamp = null;
                    if (movingObj) movingObj.classList.add('no-transition');
                    animationId = requestAnimationFrame(animate);
                    animatePassesTo(targetPassesPerSec, 1000);
                    
                    // Resume audio context if suspended
                    try {
                        initAudio();
                        if (audioCtx && audioCtx.state === 'suspended') {
                            audioCtx.resume().catch(() => {});
                        }
                    } catch (e) {}
                }
                else if (!s.isRunning && isRunning) {
                    console.log('[emdr_live] Stopping animation');
                    animatePassesTo(0, 1000);
                    setTimeout(() => {
                        if (animationId) cancelAnimationFrame(animationId);
                        isRunning = false;
                        if (movingObj) movingObj.classList.remove('no-transition');
                    }, 1000);
                }
            }
            
            // record timestamps for debugging and snapshot applied state
            try {
                console.log('[emdr_live] before commit lastAppliedState (preview)', lastAppliedState && lastAppliedState.sequence);
            } catch (e) {}
            lastAppliedState = { ...s };
            try {
                console.log('[emdr_live] committed lastAppliedState seq=', lastAppliedState.sequence, 'dir=', lastAppliedState.direction);
            } catch (e) {}
        }

        // ===== POLLING =====

        // Start button to unlock audio (user gesture) and reapply last state
        const startOverlay = document.getElementById('start-overlay');
        const startBtn = document.getElementById('start-btn');
        if (startBtn) {
            startBtn.addEventListener('click', async () => {
                try { initAudio(); } catch (e) {}
                try {
                    if (audioCtx && audioCtx.state === 'suspended') await audioCtx.resume();
                } catch (e) {}
                // hide overlay
                if (startOverlay) {
                    // fade out
                    startOverlay.classList.add('hidden');
                    setTimeout(() => {
                        if (startOverlay) startOverlay.style.display = 'none';
                    }, 420);
                }
                // reapply last known state to start audio/timers if needed
                try { if (lastAppliedState) applyState(lastAppliedState); } catch (e) {}
            });
        }

        async function poll() {
            try {
                const res = await fetch(`/emdr/state/${encodeURIComponent(token)}?since=${lastSequence}`);
                
                if (res.status === 304) {
                    // No changes
                    statusEl.textContent = `Live • seq ${lastSequence}`;
                    statusEl.className = 'status connected';
                    return;
                }
                
                if (!res.ok) {
                    statusEl.textContent = 'Session not found';
                    statusEl.className = 'status error';
                    return;
                }
                
                const newState = await res.json();

                // Debug: log the incoming direction and last applied direction
                console.log('[emdr_live] polled seq', newState.sequence, 'dir', newState.direction, 'lastDir', (lastAppliedState && lastAppliedState.direction));

                // CRITICAL: Only apply if sequence is newer
                if (newState.sequence <= lastSequence) {
                    console.warn('[emdr_live] Ignoring out-of-order state', newState.sequence, 'vs', lastSequence);
                    return;
                }
                
                // Update sequence tracker
                lastSequence = newState.sequence;
                
                // Apply the new state
                applyState(newState);
                
                // Update status
                const time = new Date(newState.timestamp * 1000).toLocaleTimeString();
                statusEl.textContent = `Live • seq ${newState.sequence} • ${time}`;
                statusEl.className = 'status connected';
                
            } catch (err) {
                console.error('[emdr_live] Poll error', err);
                statusEl.textContent = 'Connection error';
                statusEl.className = 'status error';
            }
        }

        // Start polling immediately and every 400ms
        poll();
        setInterval(poll, 400);
    </script>
</body>
</html>