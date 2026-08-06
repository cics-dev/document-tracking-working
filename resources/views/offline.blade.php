<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Server Offline</title>
  <style>
    /* Your existing CSS remains the same */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      color: #FFF8DC;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      position: relative;
      padding: 20px;
    }
    
    /* Logo background */
    body::before {
      content: '';
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-image: url('{{ asset('/assets/img/hd-logo.png') }}');
      background-repeat: no-repeat;
      background-position: center;
      background-size: 60%;
      opacity: 0.15; /* Semi-transparent */
      z-index: -1;
      pointer-events: none;
    }
    
    /* Cloud background styles */
    :root{
      --sky-top: #800020;      /* Maroon color for top */
      --sky-bottom: #5A0018;   /* Darker maroon for bottom */
      --sun: #fff8d6;
      --cloud-color: rgba(255,255,255,0.98);
    }

    /* Page reset */
    html,body{
      height:100%;
      margin:0;
      font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
      background: linear-gradient(180deg, var(--sky-top) 0%, var(--sky-bottom) 100%);
      overflow:hidden;
    }

    /* Scene container */
    .sky {
      position:fixed;
      width:100%;
      height:100vh;
      min-height:420px;
      overflow:hidden;
      user-select:none;
      -webkit-user-select:none;
      display:block;
      z-index: 0;
    }

    /* Sun */
    .sun {
      position:absolute;
      top:6%;
      right:8%;
      width:160px;
      height:160px;
      border-radius:50%;
      background: radial-gradient(circle at 30% 30%, var(--sun) 0%, rgba(255,248,200,0.6) 40%, rgba(255,220,90,0.08) 60%, rgba(255,220,90,0.00) 72%);
      filter: blur(8px);
      box-shadow: 0 30px 80px rgba(255,220,90,0.12), inset 0 -8px 20px rgba(255,240,180,0.14);
      pointer-events:none;
    }

    /* Cloud layer groups (parallax) */
    .cloud-layer{
      position:absolute;
      left:0;
      right:0;
      top:0;
      bottom:0;
      pointer-events:none;
      overflow:visible;
    }

    /* Each cloud will be an inline SVG element; we animate via CSS variable --duration and --direction and transform translateX */
    .cloud-bg {
      position:absolute;
      will-change: transform, opacity;
      opacity:0.98;
      transform: translateX(var(--start-x, -30vw)) translateY(0) scale(var(--scale,1));
      filter: drop-shadow(0 6px 10px rgba(0,0,0,0.06));
    }

    /* keyframe moves cloud across and wraps */
    @keyframes float-right {
      0%   { transform: translateX(-40vw) translateY(var(--y,0)) scale(var(--scale,1)); }
      100% { transform: translateX(120vw) translateY(calc(var(--y,0) + var(--drift,0px))) scale(var(--scale,1)); }
    }
    @keyframes float-left {
      0%   { transform: translateX(120vw) translateY(var(--y,0)) scale(var(--scale,1)); }
      100% { transform: translateX(-40vw) translateY(calc(var(--y,0) + var(--drift,0px))) scale(var(--scale,1)); }
    }

    /* apply animation; duration controlled with inline style (--dur) */
    .cloud-bg.anim-right { animation: float-right var(--dur, 70s) linear infinite; }
    .cloud-bg.anim-left  { animation: float-left  var(--dur, 70s) linear infinite; }

    /* slight blur for distant layer */
    .layer-far .cloud-bg { filter: blur(1.2px) drop-shadow(0 6px 12px rgba(0,0,0,0.04)); opacity:0.85; }
    .layer-mid .cloud-bg { opacity:0.96; }
    .layer-near .cloud-bg { filter: drop-shadow(0 10px 18px rgba(0,0,0,0.06)); opacity:1; }

    /* subtle vertical bobbing to mimic floating */
    @keyframes bob {
      0% { transform: translateY(0px); }
      50% { transform: translateY(-6px); }
      100% { transform: translateY(0px); }
    }
    .cloud-bg .puff { transform-origin:center; animation: bob calc(var(--dur,70s) / 10) ease-in-out infinite; }

    /* Accessibility: if user prefers reduced motion, pause animations */
    @media (prefers-reduced-motion: reduce) {
      .cloud-bg, .sun { animation: none !important; transition: none !important; }
    }

    /* Your existing styles */
    .container {
      text-align: center;
      z-index: 10;
      padding: 2rem;
      background: rgba(90, 0, 24, 0.7);
      border-radius: 15px;
      backdrop-filter: blur(5px);
      border: 2px solid #CFB53B;
      box-shadow: 0 0 25px rgba(207, 181, 59, 0.3);
      width: 100%;
      max-width: 500px;
    }
    
    /* Logo and text positioning */
    .logo-container {
      display: flex;
      justify-content: center;
      align-items: center;
      margin-bottom: 1rem;
    }
    
    .logo {
      width: 120px;
      margin-right: 20px;
    }
    
    h1 {
      font-size: 5.5rem;
      color: #FFD700;
      text-shadow: 5px 4px 0 #800020;
      letter-spacing: 5px;
      margin: 0;
      line-height: 1;
      position: relative;
      top: -8px;
    }
    
    /* Media query for desktop/laptop screens */
    @media (min-width: 992px) {
      .container {
        max-width: 700px;
        padding: 2.5rem;
      }
      
      h1 {
        font-size: 8rem;
      }
      
      p {
        font-size: 1.3rem;
      }
      
      .game-container {
        height: 500px;
      }
      
      .btn {
        padding: 18px 45px;
        font-size: 1.3rem;
      }
      
      /* Adjust logo background size for larger screens */
      body::before {
        background-size: 50%;
      }
    }
    
    /* Media query for very large screens */
    @media (min-width: 1200px) {
      .container {
        max-width: 800px;
      }
      
      .game-container {
        height: 550px;
      }
    }
    
    /* Media query for mobile screens */
    @media (max-width: 768px) {
      .container {
        padding: 1.5rem;
      }
      
      .logo-container {
        flex-direction: column;
      }
      
      .logo {
        margin-right: 0;
        margin-bottom: 15px;
        width: 100px;
      }
      
      h1 {
        font-size: 3.5rem;
      }
      
      p {
        font-size: 1rem;
      }
      
      .game-container {
        height: 400px;
      }
      
      .button-container {
        flex-direction: column;
        gap: 10px;
      }
      
      .button-container .btn {
        width: 100%;
      }
      
      /* Adjust logo background size for mobile */
      body::before {
        background-size: 80%;
      }
    }
    
    p {
      font-size: 1.2rem;
    }
    
    .game-container {
      width: 100%;
      height: 320px;
      margin: 1.5rem 0;
      position: relative;
      overflow: hidden;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 8px;
      border: 1px solid #CFB53B;
    }
    
    .human {
      width: 60px;
      height: 60px;
      position: absolute;
      bottom: 0;
      left: 30px;
      z-index: 2;
      background-image: url('{{ asset('/assets/img/run.gif') }}');
      background-size: contain;
      background-repeat: no-repeat;
      background-position: center;
      transition: bottom 0.1s ease;
    }
    
    .cactus {
      position: absolute;
      width: 20px;
      height: 45px;
      bottom: 0;
      right: -30px;
      background: #34C759;
      border-radius: 5px;
      z-index: 0; /* Start behind ground */
    }
    
    .cactus.active {
      z-index: 3; /* Move in front during gameplay */
    }
    
    .cactus::before {
      content: '';
      position: absolute;
      width: 15px;
      height: 15px;
      background: #34C759;
      border-radius: 50%;
      top: -8px;
      left: 2.5px;
    }
    
    .ground {
      position: absolute;
      left: 0;
      width: 100%;
      height: 34px;
      bottom: -8px;
      background-image: url('{{ asset('/assets/img/ground.png') }}');
      background-repeat: no-repeat;
      background-size: 100% 100%;
      background-position: center bottom;
      border-radius: 0 0 8px 8px;
      z-index: 1;
    }
    
    .cloud {
      position: absolute;
      background: rgba(255, 255, 255, 0.7);
      border-radius: 50%;
    }
    
    /* Moon displayed on the right and slowly moves right-to-left across the game area */
    .game-container::before {
      content: '';
      position: absolute;
      right: -120px;
      top: 10px;
      width: 90px;
      height: 90px;
      background-image: url('{{ asset('/assets/img/cloud.png') }}');
      background-size: contain;
      background-repeat: no-repeat;
      background-position: center;
      z-index: 0;
      pointer-events: none;
      animation: moonMove 30s linear infinite;
      opacity: 0.95;
    }

    @media (min-width: 992px) {
      .game-container::before {
      width: 130px;
      height: 130px;
      top: 8px;
      animation-duration: 40s;
      }
    }

    @keyframes moonMove {
      0% { right: -120px; }
      100% { right: 110%; }
    }
    
    .btn {
      background: linear-gradient(to bottom, #FFD700, #CFB53B);
      color: #800020;
      border: none;
      padding: 15px 40px;
      font-size: 1.2rem;
      font-weight: bold;
      border-radius: 50px;
      cursor: pointer;
      margin-top: 1rem;
      transition: all 0.3s;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    }
    
    .btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
    }
    
    .btn:active {
      transform: translateY(1px);
    }
    
    .btn-restart {
      background: linear-gradient(to bottom, #34C759, #2A9D4F);
      color: white;
    }
    
    .btn-start {
      background: linear-gradient(to bottom, #FFD700, #CFB53B);
      color: #800020;
    }
    
    .btn-retry {
      padding: 15px 30px;
    }
    
    .score {
      position: absolute;
      top: 10px;
      right: 10px;
      font-size: 1.2rem;
      font-weight: bold;
      color: #FFD700;
      z-index: 10;
    }
    
    .instructions {
      margin-top: 1.5rem;
      font-size: 0.9rem;
      color: #FFD700;
    }
    
    .move {
      animation: move 2s infinite linear;
    }
    
    @keyframes move {
      0% { right: -30px; }
      100% { right: 100%; }
    }
    
    .stars {
      position: absolute;
      width: 100%;
      height: 100%;
      pointer-events: none;
      z-index: 1;
    }
    
    .star {
      position: absolute;
      background: #FFD700;
      border-radius: 50%;
      animation: twinkle linear infinite;
    }
    
    @keyframes twinkle {
      0%, 100% { opacity: 0.2; }
      50% { opacity: 1; }
    }
    
    .button-container {
      display: flex;
      justify-content: center;
      gap: 20px;
      margin-top: 1rem;
    }
    
    /* Jump indicator */
    .jump-indicator {
      position: absolute;
      top: -25px;
      left: 50%;
      transform: translateX(-50%);
      font-size: 12px;
      color: #FFD700;
      font-weight: bold;
      text-shadow: 1px 1px 0 #800020;
      opacity: 0;
      transition: opacity 0.3s;
      background: rgba(90, 0, 24, 0.8);
      padding: 2px 6px;
      border-radius: 10px;
      white-space: nowrap;
    }
    
    .jump-indicator.show {
      opacity: 1;
    }
    
    /* Game over message */
    .game-over {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      color: #FFD700;
      font-size: 2rem;
      font-weight: bold;
      text-shadow: 2px 2px 0 #800020;
      background: rgba(90, 0, 24, 0.8);
      padding: 10px 20px;
      border-radius: 10px;
      z-index: 10;
    }
    
    /* Score summary styles */
    .score-summary {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background: rgba(90, 0, 24, 0.9);
      padding: 20px;
      border-radius: 15px;
      border: 2px solid #CFB53B;
      text-align: center;
      z-index: 20;
      width: 80%;
      max-width: 400px;
    }
    
    .score-summary h2 {
      color: #FFD700;
      margin-bottom: 15px;
      font-size: 1.8rem;
    }
    
    .score-summary p {
      margin: 10px 0;
      font-size: 1.2rem;
    }
    
    .highlight {
      color: #FFD700;
      font-weight: bold;
      font-size: 1.4rem;
      text-shadow: 1px 1px 0 #800020;
    }
    
    .best-score {
      color: #34C759;
      font-weight: bold;
      font-size: 1.3rem;
    }
    
    /* Game header inside game container - FIXED POSITIONING */
    .game-header {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 100%;
      text-align: center;
      z-index: 5;
      pointer-events: none;
    }
    
    .game-logo-container {
      display: flex;
      justify-content: center;
      align-items: center;
      margin-bottom: 10px;
    }
    
    .game-logo {
      width: 100px;
      margin-right: 20px;
    }
    
    .game-title {
      font-size: 6rem;
      color: #FFD700;
      text-shadow: 5px 4px 0 #800020;
      letter-spacing: 8px;
      margin: 0;
      line-height: 1;
    }
    
    .connection-message {
      font-size: 1.2rem;
      color: #FFF8DC;
      text-shadow: 1px 1px 0 #800020;
    }
    
    /* Hide game header when game starts */
    .game-started .game-header {
      display: none;
    }
  </style>
</head>
<body>
  <!-- Cloud Background -->
  <div class="sky" id="sky" aria-hidden="false" role="img" aria-label="Animated sky with moving clouds">
    <div class="sun" aria-hidden="true"></div>

    <!-- Far layer: slow, slightly transparent -->
    <div class="cloud-layer layer-far" id="layer-far" aria-hidden="true"></div>

    <!-- Mid layer -->
    <div class="cloud-layer layer-mid" id="layer-mid" aria-hidden="true"></div>

    <!-- Near layer: faster, larger -->
    <div class="cloud-layer layer-near" id="layer-near" aria-hidden="true"></div>
  </div>

  <!-- Your existing content -->
  <div class="stars" id="stars"></div>
  <div class="container">
    <div class="game-container" id="gameContainer">
      <!-- Game header inside the game container - centered -->
      <div class="game-header">
        <div class="game-logo-container">
          <img src="{{ asset('/assets/img/hd-logo.png') }}" alt="Logo" class="game-logo">
          <h1 class="game-title">OFFLINE</h1>
        </div>
        <p>🔌 Oops! It seems you've lost your internet connection.</p>
      </div>
      
      <div class="score">Score: <span id="score">0</span></div>
      <div class="human" id="human"></div>
      <div class="ground"></div>
      <!-- Cacti will be generated by JavaScript -->
    </div>
    
    <div class="instructions"><p>Press W, UP ARROW or CLICK to jump. Unlimited jumps in air!</p></div>
    
    <div class="button-container">
      <button class="btn btn-start" id="startButton" onclick="startGame()">
        START
      </button>
      <button class="btn btn-retry error_btn" onclick="checkConnection()">
        RECONNECT
      </button>
    </div>
  </div>

  <script>
    // Cloud background script
    (function createClouds(){
      const sky = document.getElementById('sky');

      // Reusable cloud SVG: rounded puffs composition for a fluffy look.
      const CLOUD_SVG = (w=420, h=160, fill='white') => {
        return `
          <svg viewBox="0 0 ${w} ${h}" width="${w}" height="${h}" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <defs>
              <filter id="soft">
                <feGaussianBlur stdDeviation="4" result="b"/>
                <feComponentTransfer in="b">
                  <feFuncA type="linear" slope="0.95"/>
                </feComponentTransfer>
              </filter>
            </defs>
            <g filter="url(#soft)" fill="${fill}" stroke="none">
              <ellipse class="puff" cx="${w*0.22}" cy="${h*0.56}" rx="${w*0.18}" ry="${h*0.26}"></ellipse>
              <ellipse class="puff" cx="${w*0.40}" cy="${h*0.40}" rx="${w*0.24}" ry="${h*0.32}"></ellipse>
              <ellipse class="puff" cx="${w*0.63}" cy="${h*0.48}" rx="${w*0.20}" ry="${h*0.28}"></ellipse>
              <ellipse class="puff" cx="${w*0.80}" cy="${h*0.62}" rx="${w*0.12}" ry="${h*0.20}"></ellipse>
              <rect x="${w*0.10}" y="${h*0.6}" width="${w*0.80}" height="${h*0.28}" rx="${h*0.14}" ry="${h*0.14}" class="puff"></rect>
            </g>
          </svg>
        `;
      };

      // Helper to create element from HTML string
      function makeElement(html){
        const tmp = document.createElement('div');
        tmp.innerHTML = html.trim();
        return tmp.firstChild;
      }

      // Layer configs
      const layers = [
        { el: document.getElementById('layer-far'), count: 5, scale:[0.6, 1.0], speed:[90, 160], yRange:[6,40], opacity:[0.6,0.9], directionChance:0.25, drift: [-10,10] },
        { el: document.getElementById('layer-mid'), count: 6, scale:[0.9,1.4], speed:[55,110], yRange:[18,60], opacity:[0.82,1.0], directionChance:0.45, drift: [-18,18] },
        { el: document.getElementById('layer-near'), count: 4, scale:[1.2,2.2], speed:[28,70], yRange:[40,78], opacity:[0.92,1.0], directionChance:0.6, drift: [-26,26] }
      ];

      // Random utilities
      const rnd = (min,max) => Math.random()*(max-min)+min;
      const rndInt = (min,max) => Math.floor(rnd(min,max+1));
      const choice = (arr) => arr[Math.floor(Math.random()*arr.length)];

      // Create clouds for each layer
      layers.forEach(layer => {
        for(let i=0;i<layer.count;i++){
          const svg = makeElement(CLOUD_SVG());
          const wrapper = document.createElement('div');
          wrapper.className = 'cloud-bg';
          const dir = Math.random() < layer.directionChance ? 'anim-left' : 'anim-right';
          wrapper.classList.add(dir);

          const scale = (rnd(layer.scale[0], layer.scale[1])).toFixed(2);
          const yPercent = rnd(layer.yRange[0], layer.yRange[1]).toFixed(1) + 'vh';
          const dur = rnd(layer.speed[0], layer.speed[1]).toFixed(1) + 's';
          const startX = (rnd(-40,120)).toFixed(1) + 'vw';
          const drift = rnd(layer.drift[0], layer.drift[1]).toFixed(1) + 'px';
          const opacity = (rnd(layer.opacity[0], layer.opacity[1])).toFixed(2);

          wrapper.style.setProperty('--scale', scale);
          wrapper.style.setProperty('--y', yPercent);
          wrapper.style.setProperty('--dur', dur);
          wrapper.style.setProperty('--start-x', startX);
          wrapper.style.setProperty('--drift', drift);
          wrapper.style.opacity = opacity;
          wrapper.style.top = yPercent;

          const leftOrRight = Math.random() < 0.5 ? '-40vw' : '120vw';
          wrapper.style.left = leftOrRight;

          wrapper.appendChild(svg);
          layer.el.appendChild(wrapper);

          const rot = rnd(-4,4).toFixed(2);
          svg.style.transform = `rotate(${rot}deg)`;
        }
      });

      // Optional: dynamically spawn a subtle extra cloud every 18-28s
      let spawnTimer = setInterval(()=>{
        const layer = choice(layers);
        const svg = makeElement(CLOUD_SVG());
        const wrapper = document.createElement('div');
        wrapper.className = 'cloud-bg ' + (Math.random() < layer.directionChance ? 'anim-left':'anim-right');

        const scale = (rnd(layer.scale[0], layer.scale[1])).toFixed(2);
        const yPercent = rnd(layer.yRange[0], layer.yRange[1]).toFixed(1) + 'vh';
        const dur = rnd(layer.speed[0], layer.speed[1]).toFixed(1) + 's';
        const drift = rnd(layer.drift[0], layer.drift[1]).toFixed(1) + 'px';
        const opacity = (rnd(layer.opacity[0], layer.opacity[1])).toFixed(2);

        wrapper.style.setProperty('--scale', scale);
        wrapper.style.setProperty('--y', yPercent);
        wrapper.style.setProperty('--dur', dur);
        wrapper.style.setProperty('--drift', drift);
        wrapper.style.opacity = opacity;
        wrapper.style.top = yPercent;
        wrapper.style.left = Math.random() < 0.5 ? '-40vw' : '120vw';
        svg.style.transform = `rotate(${rnd(-6,6).toFixed(2)}deg)`;

        layer.el.appendChild(wrapper);
        wrapper.appendChild(svg);

        const lifetime = parseFloat(dur) * 1000 + 12000;
        setTimeout(()=> wrapper.remove(), lifetime);
      }, rndInt(18000,28000));
    })();

    // Your existing game script
    function checkConnection() {
      if (navigator.onLine) {
        window.location.href = '/';
      } else {
        resetGame();
      }
    }
    
    // Create stars
    const stars = document.getElementById('stars');
    for (let i = 0; i < 80; i++) {
      const star = document.createElement('div');
      star.classList.add('star');
      star.style.width = Math.random() * 3 + 'px';
      star.style.height = star.style.width;
      star.style.left = Math.random() * 100 + '%';
      star.style.top = Math.random() * 100 + '%';
      star.style.animationDuration = Math.random() * 3 + 2 + 's';
      stars.appendChild(star);
    }
    
    // Game variables
    let score = 0;
    let totalJumps = 0;
    let gameActive = false; // Start with game inactive
    let gameStarted = false; // Track if game has started
    let jumpCount = 0;
    let bestScore = localStorage.getItem('bestScore') || 0;
    // REMOVED JUMP LIMIT - player can jump infinitely!
    let cactusSpeed = 2;
    const human = document.getElementById('human');
    const scoreDisplay = document.getElementById('score');
    const gameContainer = document.getElementById('gameContainer');
    const startButton = document.getElementById('startButton');
    
    // NEW: Track active cacti
    let activeCacti = 0;
    const MAX_CACTI = 10;
    
    // Physics variables for smooth jumping
    let isJumping = false;
    let humanBottom = 0;
    let jumpVelocity = 0;
    const gravity = 0.5;
    const jumpStrength = 12;
    const minJumpStrength = 4; // Minimum strength for very high jump counts
    
    let gameLoop;
    let cactusSpawnTimer;
    
    // Initialize game
    initGame();
    
    function initGame() {
      // Start with game inactive but human running
      gameActive = false;
      gameStarted = false;
      startButton.textContent = "START";
      startButton.classList.remove('btn-restart');
      startButton.classList.add('btn-start');
      
      // Remove game-started class to show the header
      gameContainer.classList.remove('game-started');
      
      // Start game loop for human animation
      startGameLoop();
      
      // Create cacti behind the ground
      createCactus();
    }
    
    function startGame() {
      if (!gameStarted) {
        // NEW: Reset game state when starting
        resetGame();
        
        gameActive = true;
        gameStarted = true;
        startButton.textContent = "RESTART";
        startButton.classList.remove('btn-start');
        startButton.classList.add('btn-restart');
        
        // Add game-started class to hide the header
        gameContainer.classList.add('game-started');
        
        // Move all cacti to front
        const cacti = document.querySelectorAll('.cactus');
        cacti.forEach(cactus => {
          cactus.classList.add('active');
        });
      } else {
        // If game is already started, restart it
        resetGame();
      }
    }
    
    function restartGame() {
      resetGame();
    }
    
    function startGameLoop() {
      gameLoop = requestAnimationFrame(updateGame);
    }
    
    function updateGame() {
      // Always update human animation even when game is not active
      if (isJumping) {
        jumpVelocity -= gravity;
        humanBottom += jumpVelocity;
        
        // Check if landed
        if (humanBottom <= 0) {
          humanBottom = 0;
          isJumping = false;
          jumpCount = 0;
          jumpVelocity = 0;
        }
        
        human.style.bottom = humanBottom + 'px';
      }
      
      gameLoop = requestAnimationFrame(updateGame);
    }
    
    // Handle jump
    document.addEventListener('keydown', function(e) {
      if ((e.key === 'w' || e.key === 'W' || e.key === 'ArrowUp' || e.key === ' ') && gameActive) {
        handleJump();
      }
    });
    
    // Touch support for mobile
    gameContainer.addEventListener('click', function() {
      if (gameActive) {
        handleJump();
      }
    });
    
    function handleJump() {
      // UNLIMITED JUMPS - No condition check needed!
      if (jumpCount === 0) {
        // First jump (from ground)
        jumpVelocity = jumpStrength;
        humanBottom = 1; // Start slightly above ground
      } else {
        // Unlimited jumps in air - strength decreases gradually but never reaches zero
        const strengthReduction = Math.min(jumpCount * 0.5, jumpStrength - minJumpStrength);
        jumpVelocity = Math.max(minJumpStrength, jumpStrength - strengthReduction);
      }
      
      isJumping = true;
      jumpCount++;
      totalJumps++;
    }
    
    function createCactus() {
      // NEW: Check if we've reached the maximum number of cacti
      if (activeCacti >= MAX_CACTI) {
        // Wait a bit before trying to spawn again
        cactusSpawnTimer = setTimeout(createCactus, 1000);
        return;
      }
      
      if (!gameActive) {
        // Create cacti behind the ground when game not active
        const cactus = document.createElement('div');
        cactus.classList.add('cactus', 'move');
        gameContainer.appendChild(cactus);
        
        // NEW: Track this cactus
        activeCacti++;
        
        // Adjust cactus speed based on score
        cactusSpeed = Math.max(0.8, 2 - (score * 0.02));
        cactus.style.animationDuration = `${cactusSpeed}s`;
        
        // Remove cactus when off screen
        const scoreCheck = setInterval(function() {
          const cactusPos = cactus.getBoundingClientRect();
          
          // Remove cactus when off screen
          if (cactusPos.left < -50) {
            cactus.remove();
            activeCacti--;
            clearInterval(scoreCheck);
          }
        }, 50);
        
        // Create next cactus
        const randomTime = Math.random() * 2500 + 1500;
        cactusSpawnTimer = setTimeout(createCactus, randomTime);
        return;
      }
      
      if (!gameActive) return;
      
      const cactus = document.createElement('div');
      cactus.classList.add('cactus', 'move', 'active');
      gameContainer.appendChild(cactus);
      
      // NEW: Track this cactus
      activeCacti++;
      
      // Adjust cactus speed based on score
      cactusSpeed = Math.max(0.8, 2 - (score * 0.02));
      cactus.style.animationDuration = `${cactusSpeed}s`;
      
      // REDUCED: Random time for next cactus - increased minimum and maximum time between cacti
      const randomTime = Math.random() * 2500 + 1500; // Increased from 1500+1000 to 2500+1500
      
      // Check for collision
      const checkCollision = setInterval(function() {
        if (!gameActive) {
          clearInterval(checkCollision);
          return;
        }
        
        const cactusPos = cactus.getBoundingClientRect();
        const humanPos = human.getBoundingClientRect();
        
        // Improved collision detection
        if (cactusPos.left < humanPos.right - 10 &&
            cactusPos.right > humanPos.left + 10 &&
            cactusPos.top < humanPos.bottom - 5 &&
            cactusPos.bottom > humanPos.top + 5) {
          gameOver();
          clearInterval(checkCollision);
        }
      }, 50);
      
      // Remove cactus when off screen and increase score
      let scored = false;
      const scoreCheck = setInterval(function() {
        if (!gameActive) {
          clearInterval(scoreCheck);
          return;
        }
        
        const cactusPos = cactus.getBoundingClientRect();
        const humanPos = human.getBoundingClientRect();
        
        // Award points when cactus passes the human
        if (!scored && cactusPos.right < humanPos.left) {
          scored = true;
          score += 2;
          scoreDisplay.textContent = score;
        }
        
        // Remove cactus when off screen
        if (cactusPos.left < -50) {
          cactus.remove();
          activeCacti--;
          clearInterval(scoreCheck);
        }
      }, 50);
      
      // Create next cactus
      cactusSpawnTimer = setTimeout(createCactus, randomTime);
    }
    
    function gameOver() {
      gameActive = false;
      gameStarted = false;
      cancelAnimationFrame(gameLoop);
      clearTimeout(cactusSpawnTimer);
      
      const cacti = document.querySelectorAll('.cactus');
      cacti.forEach(cactus => {
        cactus.style.animationPlayState = 'paused';
      });
      
      // Update best score if current score is higher
      if (score > bestScore) {
        bestScore = score;
        localStorage.setItem('bestScore', bestScore);
      }
      
      // Show score summary
      showScoreSummary();
    }
    
    function showScoreSummary() {
      const scoreSummary = document.createElement('div');
      scoreSummary.classList.add('score-summary');
      scoreSummary.innerHTML = `
        <h2>Game Over!</h2>
        <p>Total Score: <span class="highlight">${score}</span></p>
        <p>Total Jumps: <span class="highlight">${totalJumps}</span></p>
        <p>Best Score: <span class="best-score">${bestScore}</span></p>
        <button class="btn" onclick="resetGame()">Play Again</button>
      `;
      gameContainer.appendChild(scoreSummary);
    }
    
    function resetGame() {
      // Reset game state
      score = 0;
      totalJumps = 0;
      scoreDisplay.textContent = score;
      gameActive = false;
      gameStarted = false;
      isJumping = false;
      jumpCount = 0;
      humanBottom = 0;
      jumpVelocity = 0;
      cactusSpeed = 2;
      activeCacti = 0; // NEW: Reset cactus count
      
      // Clear any existing cactus spawn timer
      clearTimeout(cactusSpawnTimer);
      
      // Reset button to START
      startButton.textContent = "START";
      startButton.classList.remove('btn-restart');
      startButton.classList.add('btn-start');
      
      // Remove game-started class to show the header
      gameContainer.classList.remove('game-started');
      
      // Reset human position
      human.style.bottom = '0px';
      
      // Remove all cacti, game over message, and score summary
      const cacti = document.querySelectorAll('.cactus');
      cacti.forEach(cactus => cactus.remove());
      
      const gameOverMsg = document.querySelector('.game-over');
      if (gameOverMsg) {
        gameOverMsg.remove();
      }
      
      const scoreSummary = document.querySelector('.score-summary');
      if (scoreSummary) {
        scoreSummary.remove();
      }
      
      // Restart game loop
      if (gameLoop) {
        cancelAnimationFrame(gameLoop);
      }
      startGameLoop();
      
      // Start creating cacti again
      createCactus();
    }
    
    // Create some clouds for the game container
    for (let i = 0; i < 5; i++) {
      const cloud = document.createElement('div');
      cloud.classList.add('cloud');
      cloud.style.width = Math.random() * 50 + 30 + 'px';
      cloud.style.height = Math.random() * 20 + 15 + 'px';
      cloud.style.top = Math.random() * 50 + 10 + 'px';
      cloud.style.left = Math.random() * 100 + 'px';
      cloud.style.opacity = Math.random() * 0.5 + 0.2;
      cloud.style.animationDuration = Math.random() * 30 + 20 + 's';
      cloud.style.animationName = 'move';
      gameContainer.appendChild(cloud);
    }
  </script>
</body>
</html>