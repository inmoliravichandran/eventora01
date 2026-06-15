<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// If user is already logged in, redirect
if (isset($_SESSION['user_id'])) {
    header("Location: services.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>✨ Register | Eventora - Create Account</title>
    <style>
        *,
        *:after,
        *:before {
            box-sizing: border-box;
        }

        :root {
            --cord: hsl(210, 0%, calc((40 + (var(--on, 0) * 50)) * 1%));
            --opening: hsl(
                50,
                calc((10 + (var(--on, 0) * 80)) * 1%),
                calc((20 + (var(--on, 0) * 70)) * 1%)
            );
            --feature: #0a0a0a;
            --accent: 210;
            --tongue: #e06952;
            --base-top: hsl(
                var(--accent),
                0%,
                calc((40 + (var(--on, 0) * 40)) * 1%)
            );
            --base-side: hsl(
                var(--accent),
                0%,
                calc((20 + (var(--on, 0) * 40)) * 1%)
            );
            --post: hsl(
                var(--accent),
                0%,
                calc((20 + (var(--on, 0) * 40)) * 1%)
            );
            --b-1: hsla(
                45,
                calc((0 + (var(--on, 0) * 0)) * 1%),
                calc((50 + (var(--on, 0) * 50)) * 1%),
                0.85
            );
            --b-2: hsla(
                45,
                calc((0 + (var(--on, 0) * 0)) * 1%),
                calc((20 + (var(--on, 0) * 30)) * 1%),
                0.25
            );
            --b-3: hsla(
                45,
                calc((0 + (var(--on, 0) * 0)) * 1%),
                calc((20 + (var(--on, 0) * 30)) * 1%),
                0.5
            );
            --b-4: hsla(
                45,
                calc((0 + (var(--on, 0) * 0)) * 1%),
                calc((20 + (var(--on, 0) * 30)) * 1%),
                0.25
            );
            --l-1: hsla(
                45,
                calc((0 + (var(--on, 0) * 20)) * 1%),
                calc((50 + (var(--on, 0) * 50)) * 1%),
                0.85
            );
            --l-2: hsla(
                45,
                calc((0 + (var(--on, 0) * 20)) * 1%),
                calc((50 + (var(--on, 0) * 50)) * 1%),
                0.85
            );
            --shade-hue: 320;
            --t-1: hsl(
                var(--shade-hue),
                calc((0 + (var(--on, 0) * 20)) * 1%),
                calc((30 + (var(--on, 0) * 60)) * 1%)
            );
            --t-2: hsl(
                var(--shade-hue),
                calc((0 + (var(--on, 0) * 20)) * 1%),
                calc((20 + (var(--on, 0) * 35)) * 1%)
            );
            --t-3: hsl(
                var(--shade-hue),
                calc((0 + (var(--on, 0) * 20)) * 1%),
                calc((10 + (var(--on, 0) * 20)) * 1%)
            );
            --glow-color: hsl(320, 40%, 45%);
            --glow-color-dark: hsl(320, 40%, 35%);
        }

        body {
            min-height: 100vh;
            display: grid;
            place-items: center;
            background: #121921;
            margin: 0;
            font-family: system-ui, -apple-system, 'Segoe UI', sans-serif;
        }

        .container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8vmin;
            flex-wrap: wrap;
            padding: 2rem;
        }

        .register-card {
            background: rgba(18, 25, 33, 0.92);
            backdrop-filter: blur(12px);
            padding: 2.5rem 2rem;
            border-radius: 28px;
            min-width: 360px;
            opacity: 0;
            transform: scale(0.8) translateY(20px);
            pointer-events: none;
            transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
            border: 2px solid transparent;
            box-shadow: 0 0 0px rgba(255, 255, 255, 0);
        }

        .register-card.active {
            opacity: 1;
            transform: scale(1) translateY(0);
            pointer-events: all;
            border-color: var(--glow-color);
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.1),
                        0 0 30px var(--glow-color),
                        inset 0 0 15px rgba(255, 255, 255, 0.05);
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .header h2 {
            color: white;
            font-size: 2rem;
            margin: 0 0 0.5rem 0;
            text-shadow: 0 0 8px var(--glow-color);
        }

        .header p {
            color: #aaa;
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            color: #ccc;
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
            letter-spacing: 0.5px;
        }

        .form-group input {
            width: 100%;
            padding: 0.85rem 1rem;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            color: #fff;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--glow-color);
            box-shadow: 0 0 12px var(--glow-color);
            background: rgba(255, 255, 255, 0.1);
        }

        .form-group input::placeholder {
            color: #5a6a7a;
        }

        .password-strength {
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .strength-bar {
            flex: 1;
            height: 4px;
            background: rgba(255,255,255,0.15);
            border-radius: 4px;
            overflow: hidden;
        }
        .strength-fill {
            width: 0%;
            height: 100%;
            transition: width 0.2s;
        }
        .strength-text {
            color: #aaa;
            font-size: 0.7rem;
            min-width: 60px;
        }

        .btn-submit {
            width: 100%;
            padding: 0.9rem;
            background: linear-gradient(135deg, var(--glow-color), var(--glow-color-dark));
            border: none;
            border-radius: 40px;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 0.5rem;
            font-family: inherit;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            filter: brightness(1.05);
            box-shadow: 0 6px 20px rgba(0,0,0,0.4), 0 0 20px var(--glow-color);
        }

        .form-footer {
            margin-top: 1.5rem;
            text-align: center;
            font-size: 0.85rem;
        }

        .form-footer a {
            color: var(--glow-color);
            text-decoration: none;
            font-weight: 500;
            transition: 0.2s;
        }
        .form-footer a:hover {
            text-shadow: 0 0 6px var(--glow-color);
        }

        .demo-note {
            background: rgba(255,215,0,0.12);
            border-radius: 14px;
            padding: 0.7rem;
            margin-top: 1.2rem;
            text-align: center;
            font-size: 0.75rem;
            color: #ffd966;
            cursor: pointer;
            transition: 0.2s;
        }
        .demo-note:hover {
            background: rgba(255,215,0,0.25);
        }

        .login-link {
            margin-top: 1.2rem;
            text-align: center;
            color: #888;
            font-size: 0.85rem;
        }
        .login-link a {
            color: var(--glow-color);
            text-decoration: none;
            font-weight: 600;
        }

        .toast {
            position: fixed;
            bottom: 25px;
            left: 50%;
            transform: translateX(-50%);
            background: #1e2a3a;
            backdrop-filter: blur(8px);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 60px;
            font-size: 0.85rem;
            z-index: 2000;
            border-left: 4px solid var(--glow-color);
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            pointer-events: none;
            white-space: nowrap;
            font-weight: 500;
        }

        .radio-controls {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
        }

        .lamp {
            display: none;
            height: 40vmin;
            overflow: visible !important;
        }

        .cord { stroke: var(--cord); }
        .cord--rig { display: none; }
        .lamp__tongue { fill: var(--tongue); }
        .lamp__hit { cursor: pointer; opacity: 0; }
        .lamp__feature { fill: var(--feature); }
        .lamp__stroke { stroke: var(--feature); }
        .lamp__mouth, .lamp__light { opacity: var(--on, 0); }
        .shade__opening { fill: var(--opening); }
        .shade__opening-shade { opacity: calc(1 - var(--on, 0)); }
        .post__body { fill: var(--post); }
        .base__top { fill: var(--base-top); }
        .base__side { fill: var(--base-side); }
        .top__body { fill: var(--t-3); }

        @media (max-width: 680px) {
            .container { gap: 4vmin; }
            .register-card { min-width: 300px; padding: 1.8rem; }
        }
    </style>
</head>
<body>
    <form class="radio-controls">
        <input type="radio" id="on" name="status" value="on" />
        <label for="on">On</label>
        <input type="radio" id="off" name="status" value="off" checked />
        <label for="off">Off</label>
    </form>

    <div class="container">
        <svg class="lamp" viewBox="0 0 333 484" fill="none" xmlns="http://www.w3.org/2000/svg">
            <g class="lamp__shade shade">
                <ellipse class="shade__opening" cx="165" cy="220" rx="130" ry="20" />
                <ellipse class="shade__opening-shade" cx="165" cy="220" rx="130" ry="20" fill="url(#opening-shade)" />
            </g>
            <g class="lamp__base base">
                <path class="base__side" d="M165 464c44.183 0 80-8.954 80-20v-14h-22.869c-14.519-3.703-34.752-6-57.131-6-22.379 0-42.612 2.297-57.131 6H85v14c0 11.046 35.817 20 80 20z" />
                <path d="M165 464c44.183 0 80-8.954 80-20v-14h-22.869c-14.519-3.703-34.752-6-57.131-6-22.379 0-42.612 2.297-57.131 6H85v14c0 11.046 35.817 20 80 20z" fill="url(#side-shading)" />
                <ellipse class="base__top" cx="165" cy="430" rx="80" ry="20" />
                <ellipse cx="165" cy="430" rx="80" ry="20" fill="url(#base-shading)" />
            </g>
            <g class="lamp__post post">
                <path class="post__body" d="M180 142h-30v286c0 3.866 6.716 7 15 7 8.284 0 15-3.134 15-7V142z" />
                <path d="M180 142h-30v286c0 3.866 6.716 7 15 7 8.284 0 15-3.134 15-7V142z" fill="url(#post-shading)" />
            </g>
            <g class="lamp__cords cords">
                <path class="cord cord--rig" d="M124 187.033V347" stroke-width="6" stroke-linecap="round" />
                <path class="cord cord--rig" d="M124 187.023s17.007 21.921 17.007 34.846c0 12.925-11.338 23.231-17.007 34.846-5.669 11.615-17.007 21.921-17.007 34.846 0 12.925 17.007 34.846 17.007 34.846" stroke-width="6" stroke-linecap="round" />
                <path class="cord cord--rig" d="M124 187.017s-21.259 17.932-21.259 30.26c0 12.327 14.173 20.173 21.259 30.26 7.086 10.086 21.259 17.933 21.259 30.26 0 12.327-21.259 30.26-21.259 30.26" stroke-width="6" stroke-linecap="round" />
                <path class="cord cord--rig" d="M124 187s29.763 8.644 29.763 20.735-19.842 13.823-29.763 20.734c-9.921 6.912-29.763 8.644-29.763 20.735S124 269.939 124 269.939" stroke-width="6" stroke-linecap="round" />
                <path class="cord cord--rig" d="M124 187.029s-10.63 26.199-10.63 39.992c0 13.794 7.087 26.661 10.63 39.992 3.543 13.331 10.63 26.198 10.63 39.992 0 13.793-10.63 39.992-10.63 39.992" stroke-width="6" stroke-linecap="round" />
                <line class="cord cord--dummy" x1="124" y2="348" x2="124" y1="190" stroke-width="6" stroke-linecap="round" />
            </g>
            <path class="lamp__light" d="M290.5 193H39L0 463.5c0 11.046 75.478 20 165.5 20s167-11.954 167-23l-42-267.5z" fill="url(#light)" />
            <g class="lamp__top top">
                <path class="top__body" fill-rule="evenodd" clip-rule="evenodd" d="M164.859 0c55.229 0 100 8.954 100 20l29.859 199.06C291.529 208.451 234.609 200 164.859 200S38.189 208.451 35 219.06L64.859 20c0-11.046 44.772-20 100-20z" />
                <path class="top__shading" fill-rule="evenodd" clip-rule="evenodd" d="M164.859 0c55.229 0 100 8.954 100 20l29.859 199.06C291.529 208.451 234.609 200 164.859 200S38.189 208.451 35 219.06L64.859 20c0-11.046 44.772-20 100-20z" fill="url(#top-shading)" />
            </g>
            <g class="lamp__face face">
                <g class="lamp__mouth">
                    <path d="M165 178c19.882 0 36-16.118 36-36h-72c0 19.882 16.118 36 36 36z" fill="#141414" />
                    <clipPath id="mouth" x="129" y="142" width="72" height="36">
                        <path d="M165 178c19.882 0 36-16.118 36-36h-72c0 19.882 16.118 36 36 36z" fill="#141414" />
                    </clipPath>
                    <g clip-path="url(#mouth)">
                        <circle class="lamp__tongue" cx="179.4" cy="172.6" r="18" />
                    </g>
                </g>
                <g class="lamp__eyes">
                    <path class="lamp__eye lamp__stroke" d="M115 135c0-5.523-5.82-10-13-10s-13 4.477-13 10" stroke-width="4" stroke-linecap="round" />
                    <path class="lamp__eye lamp__stroke" d="M241 135c0-5.523-5.82-10-13-10s-13 4.477-13 10" stroke-width="4" stroke-linecap="round" />
                </g>
            </g>
            <defs>
                <linearGradient id="opening-shade" x1="35" y1="220" x2="295" y2="220" gradientUnits="userSpaceOnUse"><stop /><stop offset="1" stop-color="var(--shade)" stop-opacity="0" /></linearGradient>
                <linearGradient id="base-shading" x1="85" y1="444" x2="245" y2="444" gradientUnits="userSpaceOnUse"><stop stop-color="var(--b-1)" /><stop offset="0.8" stop-color="var(--b-2)" stop-opacity="0" /></linearGradient>
                <linearGradient id="side-shading" x1="119" y1="430" x2="245" y2="430" gradientUnits="userSpaceOnUse"><stop stop-color="var(--b-3)" /><stop offset="1" stop-color="var(--b-4)" stop-opacity="0" /></linearGradient>
                <linearGradient id="post-shading" x1="150" y1="288" x2="180" y2="288" gradientUnits="userSpaceOnUse"><stop stop-color="var(--b-1)" /><stop offset="1" stop-color="var(--b-2)" stop-opacity="0" /></linearGradient>
                <linearGradient id="light" x1="165.5" y1="218.5" x2="165.5" y2="483.5" gradientUnits="userSpaceOnUse"><stop stop-color="var(--l-1)" stop-opacity=".2" /><stop offset="1" stop-color="var(--l-2)" stop-opacity="0" /></linearGradient>
                <linearGradient id="top-shading" x1="56" y1="110" x2="295" y2="110" gradientUnits="userSpaceOnUse"><stop stop-color="var(--t-1)" stop-opacity=".8" /><stop offset="1" stop-color="var(--t-2)" stop-opacity="0" /></linearGradient>
            </defs>
            <circle class="lamp__hit" cx="124" cy="347" r="66" fill="#C4C4C4" fill-opacity=".1" />
        </svg>

        <div class="register-card" id="registerCard">
            <div class="header">
                <h2>✨ Create Account</h2>
                <p>Join Eventora and start planning amazing events</p>
            </div>

            <form id="registerForm">
                <div class="form-group">
                    <label>👤 Full Name</label>
                    <input type="text" id="regName" placeholder="Alex Johnson" required>
                </div>
                <div class="form-group">
                    <label>📧 Email Address</label>
                    <input type="email" id="regEmail" placeholder="hello@eventora.com" required>
                </div>
                <div class="form-group">
                    <label>🔒 Password (min 6 characters)</label>
                    <input type="password" id="regPassword" placeholder="Create a strong password" required>
                    <div class="password-strength">
                        <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                        <span class="strength-text" id="strengthText">Weak</span>
                    </div>
                </div>
                <div class="form-group">
                    <label>✓ Confirm Password</label>
                    <input type="password" id="regConfirm" placeholder="Confirm your password" required>
                </div>
                <button type="submit" class="btn-submit">Create Account →</button>
                <div class="form-footer">
                    By signing up, you agree to our <a href="#">Terms of Service</a>
                </div>
                <div class="demo-note" id="demoFill">
                    🚀 Quick demo: Fill with random test account details
                </div>
                <div class="login-link">
                    Already have an account? <a href="login.php">Sign in →</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://unpkg.co/gsap@3/dist/gsap.min.js"></script>
    <script src="https://unpkg.com/gsap@3/dist/Draggable.min.js"></script>
    <script src="https://assets.codepen.io/16327/MorphSVGPlugin3.min.js"></script>
    <script>
        // Lamp Interaction (same as login)
        const { gsap, gsap: { registerPlugin, set, to, timeline }, MorphSVGPlugin, Draggable } = window;
        registerPlugin(MorphSVGPlugin);
        const AUDIO = { CLICK: new Audio("https://assets.codepen.io/605876/click.mp3") };
        const ON = document.querySelector("#on");
        const OFF = document.querySelector("#off");
        const REGISTER_CARD = document.querySelector(".register-card");
        let startX, startY;
        const PROXY = document.createElement("div");
        const CORDS = gsap.utils.toArray(".cords path");
        const CORD_DURATION = 0.1;
        const HIT = document.querySelector(".lamp__hit");
        const DUMMY_CORD = document.querySelector(".cord--dummy");
        const ENDX = DUMMY_CORD.getAttribute("x2");
        const ENDY = DUMMY_CORD.getAttribute("y2");
        const RESET = () => { set(PROXY, { x: ENDX, y: ENDY }); };
        RESET();
        const STATE = { ON: false };
        gsap.set([".cords", HIT], { x: -10 });
        gsap.set(".lamp__eye", { rotate: 180, transformOrigin: "50% 50%", yPercent: 50 });
        const CORD_TL = timeline({
            paused: true,
            onStart: () => {
                STATE.ON = !STATE.ON;
                set(document.documentElement, { "--on": STATE.ON ? 1 : 0 });
                const hue = gsap.utils.random(0, 359);
                set(document.documentElement, { "--shade-hue": hue });
                const glowColor = `hsl(${hue}, 40%, 45%)`;
                const glowColorDark = `hsl(${hue}, 40%, 35%)`;
                set(document.documentElement, { "--glow-color": glowColor, "--glow-color-dark": glowColorDark });
                set(".lamp__eye", { rotate: STATE.ON ? 0 : 180 });
                set([DUMMY_CORD, HIT], { display: "none" });
                set(CORDS[0], { display: "block" });
                AUDIO.CLICK.play();
                if (STATE.ON) {
                    ON.setAttribute("checked", true);
                    OFF.removeAttribute("checked");
                    REGISTER_CARD.classList.add("active");
                } else {
                    ON.removeAttribute("checked");
                    OFF.setAttribute("checked", true);
                    REGISTER_CARD.classList.remove("active");
                }
            },
            onComplete: () => {
                set([DUMMY_CORD, HIT], { display: "block" });
                set(CORDS[0], { display: "none" });
                RESET();
            },
        });
        for (let i = 1; i < CORDS.length; i++) {
            CORD_TL.add(to(CORDS[0], { morphSVG: CORDS[i], duration: CORD_DURATION, repeat: 1, yoyo: true }));
        }
        Draggable.create(PROXY, {
            trigger: HIT, type: "x,y",
            onPress: (e) => { startX = e.x; startY = e.y; },
            onDrag: function () { set(DUMMY_CORD, { attr: { x2: this.x, y2: Math.max(400, this.y) } }); },
            onRelease: function (e) {
                const DISTX = Math.abs(e.x - startX), DISTY = Math.abs(e.y - startY), TRAVELLED = Math.sqrt(DISTX*DISTX + DISTY*DISTY);
                to(DUMMY_CORD, { attr: { x2: ENDX, y2: ENDY }, duration: CORD_DURATION, onComplete: () => { if (TRAVELLED > 50) CORD_TL.restart(); else RESET(); } });
            },
        });
        gsap.set(".lamp", { display: "block" });

        function showToast(msg, isError = false) {
            const existing = document.querySelector('.toast');
            if (existing) existing.remove();
            const div = document.createElement('div');
            div.className = 'toast';
            div.style.background = isError ? '#b91c1cdd' : '#1e2a3add';
            div.style.borderLeftColor = isError ? '#ff8888' : `var(--glow-color, #a855f7)`;
            div.innerText = msg;
            document.body.appendChild(div);
            setTimeout(() => div.remove(), 2800);
        }

        // Password strength
        const pwdInput = document.getElementById('regPassword');
        const strengthFill = document.getElementById('strengthFill');
        const strengthText = document.getElementById('strengthText');
        
        function updateStrength() {
            const val = pwdInput.value;
            let score = 0;
            if (val.length >= 6) score += 20;
            if (val.length >= 10) score += 20;
            if (/[a-z]/.test(val)) score += 15;
            if (/[A-Z]/.test(val)) score += 15;
            if (/[0-9]/.test(val)) score += 15;
            if (/[^a-zA-Z0-9]/.test(val)) score += 15;
            score = Math.min(100, score);
            strengthFill.style.width = score + '%';
            if (score < 30) { strengthFill.style.backgroundColor = '#ef4444'; strengthText.innerText = 'Weak'; }
            else if (score < 60) { strengthFill.style.backgroundColor = '#f59e0b'; strengthText.innerText = 'Medium'; }
            else { strengthFill.style.backgroundColor = '#10b981'; strengthText.innerText = 'Strong'; }
        }
        pwdInput.addEventListener('input', updateStrength);

        // Confirm password validation
        const confirmInput = document.getElementById('regConfirm');
        confirmInput.addEventListener('input', function() {
            const pwd = pwdInput.value;
            if (this.value && this.value !== pwd) {
                this.style.borderColor = '#ef4444';
            } else {
                this.style.borderColor = 'rgba(255, 255, 255, 0.1)';
            }
        });

        // Registration logic - connect to PHP backend database endpoint
        document.getElementById('registerForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const name = document.getElementById('regName').value.trim();
            const email = document.getElementById('regEmail').value.trim();
            const pwd = pwdInput.value;
            const confirm = confirmInput.value;
            
            if (!name || !email || !pwd || !confirm) return showToast('Please fill all fields', true);
            if (!email.includes('@')) return showToast('Valid email required', true);
            if (pwd.length < 6) return showToast('Password must be at least 6 characters', true);
            if (pwd !== confirm) return showToast('Passwords do not match', true);
            
            // Show loading state
            const btn = e.target.querySelector('.btn-submit');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';
            btn.disabled = true;

            const formData = new FormData();
            formData.append('name', name);
            formData.append('email', email);
            formData.append('password', pwd);
            formData.append('phone', ''); // optional

            try {
                const response = await fetch('../backend/register.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    showToast('✅ Account created successfully! Redirecting to login...');
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 1500);
                } else {
                    showToast(`❌ ${result.message}`, true);
                }
            } catch (err) {
                showToast('❌ Connection error. Please try again.', true);
            } finally {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        });

        // Demo fill - creates a random regular user
        document.getElementById('demoFill')?.addEventListener('click', () => {
            const random = Math.floor(Math.random() * 10000);
            document.getElementById('regName').value = `Tester${random}`;
            document.getElementById('regEmail').value = `test${random}@eventora.com`;
            pwdInput.value = 'TestPass123';
            confirmInput.value = 'TestPass123';
            updateStrength();
            showToast('🎭 Demo data filled! Pull cord to toggle light and click Create Account.');
        });
    </script>
</body>
</html>
