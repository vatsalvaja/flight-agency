<div id="wings-global-loader" class="wings-loader-overlay active">
    <div class="wings-loader-card">
        <div class="wings-airplane-animation">
            <svg class="wings-loader-svg" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                <!-- Gradients and filters for premium glows -->
                <defs>
                    <radialGradient id="radar-glow" cx="50%" cy="50%" r="50%">
                        <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.15" />
                        <stop offset="100%" stop-color="#3b82f6" stop-opacity="0" />
                    </radialGradient>
                    <linearGradient id="plane-glow" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#3b82f6" />
                        <stop offset="100%" stop-color="#60a5fa" />
                    </linearGradient>
                </defs>

                <!-- Soft inner radar glow -->
                <circle cx="50" cy="50" r="40" fill="url(#radar-glow)" />

                <!-- Concentric flight grids (radar style) -->
                <circle cx="50" cy="50" r="40" class="wings-grid-ring outer" fill="none" stroke-width="1" />
                <circle cx="50" cy="50" r="28" class="wings-grid-ring middle" fill="none" stroke-width="0.75" />
                <circle cx="50" cy="50" r="16" class="wings-grid-ring inner" fill="none" stroke-width="0.5" />

                <!-- Compass Cardinal Markings -->
                <!-- North/South/East/West dotted crosslines -->
                <line x1="50" y1="10" x2="50" y2="90" class="wings-grid-line" stroke-width="0.5" stroke-dasharray="1 3" />
                <line x1="10" y1="50" x2="90" y2="50" class="wings-grid-line" stroke-width="0.5" stroke-dasharray="1 3" />

                <!-- Compass Tick Marks -->
                <line x1="50" y1="8" x2="50" y2="13" class="wings-tick-mark" stroke-width="1.25" />
                <line x1="50" y1="87" x2="50" y2="92" class="wings-tick-mark" stroke-width="1.25" />
                <line x1="8" y1="50" x2="13" y2="50" class="wings-tick-mark" stroke-width="1.25" />
                <line x1="87" y1="50" x2="92" y2="50" class="wings-tick-mark" stroke-width="1.25" />

                <!-- Dynamic Sweeping Radar line -->
                <circle class="wings-loader-dash" cx="50" cy="50" r="40" fill="none" stroke-width="2" stroke-linecap="round" />

                <!-- Orbiting Jet Plane -->
                <g class="wings-airplane-rotator">
                    <!-- The plane is placed at the top (50, 10), rotated 90 degrees to face the direction of flight -->
                    <g transform="translate(50, 10) rotate(90)">
                        <!-- Centering a 24x24 material design jet icon -->
                        <g transform="translate(-12, -12)">
                            <path d="M21 16v-2l-8-5V3.5c0-.83-.67-1.5-1.5-1.5S10 2.67 10 3.5V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z" class="wings-plane-path" fill="url(#plane-glow)" />
                        </g>
                    </g>
                </g>
            </svg>
        </div>
        <div class="wings-loader-content">
            <h4 class="wings-loader-title">Wings Operations</h4>
            <p id="wings-loader-status" class="wings-loader-status">Preparing flight details...</p>
        </div>
    </div>
</div>
