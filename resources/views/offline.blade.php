<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="theme-color" content="#f7f2e9">
<title>ZPPSU DTS · Tetris</title>
<style>
:root{
  color-scheme: light;
  --bg: #f7f2e9;
  --maroon: #7f3f35;
  --dark-maroon: #61302d;
  --light-maroon: #a45f43;
  --gold: #b8860b;
  --light-gold: #d4af37;
  --cream: #fff9ef;
  --panel: #ffffff;
  --line: rgba(127, 63, 53, 0.25);
  --text: #2c1e1c;
  --muted: #6d5552;
}
*{box-sizing:border-box;margin:0;padding:0}
html,body{width:100%;height:100%;overflow:hidden;overscroll-behavior:none}
body{
  font-family:system-ui,-apple-system,'Segoe UI',sans-serif;
  color:var(--text);
  background:
    radial-gradient(ellipse at 10% 0%, rgba(164,95,67,0.15) 0%, transparent 55%),
    radial-gradient(ellipse at 100% 90%, rgba(127,63,53,0.12) 0%, transparent 50%),
    var(--bg);
}
button,input{font:inherit}
button{cursor:pointer;touch-action:manipulation;user-select:none}
button:focus-visible,input:focus-visible{outline:3px solid var(--gold);outline-offset:3px}
button:disabled{opacity:.4;cursor:default}
[hidden]{display:none!important}

.app{
  height:100vh;
  height:100dvh;
  max-width:880px;
  margin:auto;
  padding:12px;
  padding-top:max(12px,env(safe-area-inset-top));
  padding-bottom:max(12px,env(safe-area-inset-bottom));
  padding-left:max(12px,env(safe-area-inset-left));
  padding-right:max(12px,env(safe-area-inset-right));
  display:grid;
  grid-template-rows:auto auto minmax(0,1fr) auto auto;
  gap:10px;
}
.header{display:flex;justify-content:space-between;align-items:center;gap:12px;min-width:0}
.brand{display:flex;align-items:center;gap:10px;min-width:0}
.brand-icon{width:44px;height:44px;object-fit:contain;display:block}
.brand h1{font-size:22px;letter-spacing:4px;line-height:1.1;color:var(--dark-maroon)}
.eyebrow{font-size:9px;letter-spacing:2px;color:var(--muted);margin-top:4px}
.identity{text-align:right;min-width:0;max-width:45%}
.player{font-size:12px;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.connection{font-size:10px;color:#2e7d32;display:block;margin-top:3px}
.connection.offline{color:var(--gold)}

.stats{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:8px}
.stat{
  background:var(--panel);
  border:1px solid var(--line);
  padding:9px 12px;
  border-radius:13px;
  min-width:0;
  min-height:82px;
  position:relative;
  box-shadow:0 2px 8px rgba(0,0,0,0.03);
  text-align:center;
  display:flex;
  flex-direction:column;
  align-items:center;
  justify-content:center;
  gap:2px;
}
.label{font-size:12px;text-transform:uppercase;letter-spacing:1.5px;color:var(--muted);font-weight:700}
.value{font-size:27px;line-height:1.2;font-weight:800;font-variant-numeric:tabular-nums;overflow:hidden;text-overflow:ellipsis;color:var(--dark-maroon)}
.level-progress{display:block;color:var(--dark-maroon);font-size:20px;font-weight:800;letter-spacing:.2px;white-space:nowrap}
.stat:first-child .value,.stat:nth-child(2) .value{color:var(--gold)}

.arena{
  min-height:0;
  min-width:0;
  display:grid;
  grid-template-columns:minmax(0,1fr) 190px;
  gap:12px;
  background:rgba(255,255,255,0.7);
  border:1px solid var(--line);
  border-radius:20px;
  padding:12px;
  box-shadow:0 4px 16px rgba(0,0,0,0.04);
}
.board-area{min-width:0;min-height:0;display:grid;place-items:center}
.board-wrap{
  position:relative;
  width:120px;
  height:240px;
  overflow:hidden;
  border-radius:5px;
  box-shadow:0 0 0 1px rgba(212,175,55,0.4), 0 12px 35px rgba(0,0,0,0.15);
  background:#1e1018; /* keep board dark for contrast */
}
#board{display:block;width:100%;height:100%;touch-action:none}
.sidebar{min-height:0;min-width:0;display:flex;flex-direction:column;gap:10px}
.panel{
  background:var(--panel);
  border:1px solid var(--line);
  border-radius:13px;
  padding:12px;
  min-width:0;
  box-shadow:0 2px 8px rgba(0,0,0,0.03);
}
.next{text-align:center;flex-shrink:0}
#next{width:76px;height:60px;display:block;margin:4px auto 0}
.rank-panel{flex:1;min-height:0;display:flex;flex-direction:column;gap:7px}
.rank-list{list-style:none;flex:1;min-height:0;display:flex;flex-direction:column;justify-content:center;gap:6px}
.rank-row{display:grid;grid-template-columns:18px minmax(0,1fr);column-gap:5px;font-size:11px}
.rank-number{grid-row:span 2;color:var(--gold);align-self:center}
.rank-name{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.rank-score{color:var(--muted);font-size:10px}
.rank-row.current .rank-name{color:var(--gold)}
.empty{font-size:11px;color:var(--muted);line-height:1.4}
.pager{display:flex;align-items:center;justify-content:space-between;gap:4px;font-size:9px;color:var(--muted)}
.pager button{border:1px solid var(--line);border-radius:6px;background:#ffffff;color:var(--text);width:30px;height:28px}
.help{font-size:10px;line-height:1.7;color:var(--muted)}

.controls{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:8px}
.control{
  height:64px;
  background:linear-gradient(var(--light-maroon), var(--maroon));
  border:1px solid rgba(255,255,255,0.4);
  border-bottom:3px solid #3a2522;
  border-radius:12px;
  color:#fff9ed;
  display:flex;
  flex-direction:column;
  align-items:center;
  justify-content:center;
  gap:1px;
  touch-action:none;
}
.control b{font-size:28px;line-height:1}
.control span{font-size:12px;letter-spacing:1.2px;text-transform:uppercase;font-weight:800;color:#fff9ed}
.control.rotate{background:linear-gradient(var(--maroon), var(--dark-maroon))}
.control.drop{background:linear-gradient(#4f9b67,#287044);border-color:#8ed3a1;color:#f0fff4}
.control.drop span{color:#f0fff4}
.control:active,.btn:active{filter:brightness(1.2)}

.actions{display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px}
.btn{
  border:1px solid var(--line);
  border-radius:10px;
  height:40px;
  background:var(--panel);
  color:var(--text);
  font-size:11px;
  font-weight:750;
  letter-spacing:.4px;
  padding:0 8px;
}
.btn.primary{
  background:linear-gradient(135deg, var(--light-gold), var(--gold));
  border-color: var(--gold);
  color:#fff;
}
.btn.secondary{
  background:rgba(0,0,0,0.03);
  color:var(--muted);
}

.overlay{
  position:absolute;
  inset:0;
  z-index:2;
  background:rgba(247,242,233,0.92);
  display:flex;
  align-items:center;
  justify-content:center;
  flex-direction:column;
  text-align:center;
  padding:10px;
  gap:9px;
}
.overlay h2{font-size:clamp(15px,3vw,27px);color:var(--dark-maroon)}
.overlay p{font-size:12px;color:var(--muted);max-width:100%;overflow-wrap:anywhere}
.overlay strong{font-size:30px;color:var(--dark-maroon)}
.overlay .btn{width:100%;max-width:180px}

.welcome{
  position:fixed;
  inset:0;
  z-index:10;
  background:rgba(247,242,233,0.85);
  backdrop-filter:blur(12px);
  display:grid;
  place-items:center;
  padding:16px;
}
.welcome-card{
  width:min(500px,100%);
  max-height:100%;
  overflow:auto;
  border:1px solid rgba(212,175,55,0.5);
  border-radius:28px;
  background:var(--panel);
  padding:38px 34px;
  text-align:center;
  box-shadow:0 24px 80px rgba(0,0,0,0.1);
}
.logo{width:96px;height:96px;object-fit:contain;margin-bottom:16px}
.welcome h2{font-size:34px;letter-spacing:4px;color:var(--dark-maroon)}
.welcome p{font-size:12px;line-height:1.6;color:var(--muted);margin:12px 0}
.welcome input{
  width:100%;
  background:#faf5ec;
  border:1px solid var(--line);
  border-radius:10px;
  padding:12px;
  color:var(--text);
  font-size:16px;
  text-align:center;
}
.welcome .btn{width:100%;margin-top:8px}
.error{font-size:11px;min-height:18px;color:#c62828;margin-top:5px}

@media(max-width:600px){
  .app{
    padding:8px;
    padding-top:max(8px,env(safe-area-inset-top));
    padding-bottom:max(8px,env(safe-area-inset-bottom));
    padding-left:max(8px,env(safe-area-inset-left));
    padding-right:max(8px,env(safe-area-inset-right));
    gap:7px;
  }
  .brand h1{font-size:18px}
  .eyebrow{font-size:7px;letter-spacing:1px}
  .brand-icon{width:36px;height:36px}
  .stat{padding:7px 9px}
  .value{font-size:20px}
  .arena{grid-template-columns:minmax(0,1fr) 104px;padding:8px;gap:8px;border-radius:15px}
  .panel{padding:8px}
  .sidebar{gap:7px}
  .label{font-size:8px;letter-spacing:1px}
  .help{display:none}
  .controls{gap:6px}
  .control{height:49px}
  .control b{font-size:23px}
  .control span{font-size:9px;letter-spacing:.8px}
  .rank-row{font-size:10px}
  .rank-score{font-size:9px}
  .rank-panel{gap:5px}
  .btn{font-size:10px}
  .stats{grid-template-columns:repeat(4,minmax(0,1fr))}
  .stats .stat:last-child{grid-column:span 2}
}

@media(max-height:550px) and (min-width:520px){
  .app{max-width:1000px;grid-template-columns:minmax(0,1fr) 210px;grid-template-rows:auto auto minmax(0,1fr);gap:7px}
  .header{grid-column:1/-1}
  .stats{grid-column:2;grid-row:2;grid-template-columns:1fr 1fr;gap:5px}
  .stat{padding:5px 8px}
  .value{font-size:18px}
  .arena{grid-column:1;grid-row:2/4;grid-template-columns:minmax(0,1fr) 115px;padding:7px;gap:7px}
  .help{display:none}
  .panel{padding:7px}
  .controls{grid-column:2;grid-row:3;align-self:start;grid-template-columns:repeat(3,1fr)}
  .control{height:47px}
  .control b{font-size:21px}
  .control span{font-size:8px;letter-spacing:.6px}
  .actions{grid-column:2;grid-row:3;align-self:end;gap:5px}
  .btn{font-size:9px;height:35px}
  .welcome-card{padding:24px 28px}
  .logo{width:72px;height:72px;margin-bottom:10px}
  .welcome h2{font-size:27px}
  .welcome p{margin:7px 0}
}

@media(max-height:650px) and (max-width:519px){
  .app{gap:5px}
  .stat{padding:4px 7px}
  .value{font-size:17px}
  .control{height:44px}
  .btn{height:34px}
  .brand-icon{width:32px;height:32px}
  .brand h1{font-size:16px}
  .panel{padding:6px}
  #next{height:44px;width:60px}
  .sidebar{gap:5px}
  .rank-list{gap:3px}
  .rank-panel{gap:3px}
  .pager button{height:24px}
  .rank-score{font-size:8px}
  .rank-row{font-size:9px}
}
</style>
</head>
<body>

<main class="app">
  <header class="header">
    <div class="brand">
      <img class="brand-icon"
        src="{{ asset('assets/img/zppsu-logo.png') }}"
        alt="ZPPSU logo">
      <div>
        <h1>TETRIS</h1>
        <div class="eyebrow">ZPPSU DTS · OFFLINE ARCADE</div>
      </div>
    </div>
    <div class="identity">
      <span class="player" id="playerNameDisplay">Guest player</span>
      <span class="connection offline" id="connectionStatus" role="status">Local mode</span>
    </div>
  </header>

  <section class="stats" aria-label="Game statistics">
    <div class="stat">
      <div class="label">Score</div>
      <div class="value" id="scoreDisplay">0</div>
    </div>
    <div class="stat">
      <div class="label">Best</div>
      <div class="value" id="bestDisplay">0</div>
    </div>
    <div class="stat">
      <div class="label">Lines</div>
      <div class="value" id="linesDisplay">0</div>
    </div>
    <div class="stat">
      <div class="label">Level</div>
      <div class="value" id="levelDisplay">1</div>
    </div>
    <div class="stat">
      <div class="label">Tiles</div>
      <div class="value level-progress" id="levelProgress">0 / 10</div>
    </div>
  </section>

  <section class="arena" aria-label="Tetris game">
    <div class="board-area" id="boardArea">
      <div class="board-wrap" id="boardWrap">
        <canvas id="board" width="360" height="720"
          aria-label="Tetris board: use arrow keys or the buttons below"></canvas>

        <div class="overlay" id="gameOverlay">
          <h2 id="overlayTitle">READY?</h2>
          <p id="overlayCopy">Build rows. Beat your best.</p>
          <strong id="finalScore" hidden>0</strong>
          <p id="finalBest" hidden></p>
          <button class="btn primary" id="overlayBtn">▶ Play</button>
        </div>
      </div>
    </div>

    <aside class="sidebar">
      <div class="panel next">
        <div class="label">Next piece</div>
        <canvas id="next" width="120" height="90" aria-label="Next piece preview"></canvas>
      </div>

      <div class="panel rank-panel">
        <div class="label">Top players</div>
        <ol class="rank-list" id="leaderboardList"></ol>
        <div class="pager">
          <button id="prevRank" aria-label="Previous players">‹</button>
          <span id="rankPage">1 / 1</span>
          <button id="nextRank" aria-label="Next players">›</button>
        </div>
      </div>

      <div class="panel help">
        ← → Move · ↑ Rotate<br>
        ↓ Soft drop · Space Drop<br>
        P Pause · Swipe or tap board
      </div>
    </aside>
  </section>

  <nav class="controls" aria-label="Game controls">
    <button class="control" data-action="left" aria-label="Move left">
      <b>←</b><span>Left</span>
    </button>
    <button class="control rotate" data-action="rotate" aria-label="Rotate piece">
      <b>↻</b><span>Rotate</span>
    </button>
    <button class="control" data-action="down" aria-label="Move down">
      <b>↓</b><span>Down</span>
    </button>
    <button class="control" data-action="right" aria-label="Move right">
      <b>→</b><span>Right</span>
    </button>
    <button class="control drop" data-action="drop" aria-label="Drop piece to bottom">
      <b>⇓</b><span>Drop</span>
    </button>
  </nav>

  <div class="actions">
    <button class="btn primary" id="startBtn">▶ Start</button>
    <button class="btn" id="pauseBtn" disabled>Ⅱ Pause</button>
    <button class="btn secondary" id="reconnectBtn">⟳ Reconnect</button>
  </div>
</main>

<div class="welcome" id="welcome" role="dialog"
  aria-modal="true" aria-labelledby="welcomeTitle">
  <form class="welcome-card" id="welcomeForm">
    <img class="logo"
      src="{{ asset('assets/img/zppsu-logo.png') }}"
      alt="ZPPSU logo"
      onerror="this.hidden=true">

    <h2 id="welcomeTitle">OFFLINE LET’S PLAY</h2>
    <p>
      Take a break with Tetris. You can play offline,
      and your best score is saved on this device.
    </p>

    @auth
      <input id="playerNameInput"
        value="{{ auth()->user()->name }}"
        maxlength="40"
        autocomplete="name"
        aria-label="Signed-in player"
        readonly>
    @else
      <input id="playerNameInput"
        maxlength="40"
        minlength="2"
        autocomplete="name"
        placeholder="Enter your name"
        aria-label="Player name"
        required>
    @endauth

    <div class="error" id="nameError" role="alert"></div>
    <button class="btn primary" type="submit">▶ Let’s play</button>
  </form>
</div>

<script>
(() => {
  'use strict';

  const $ = id => document.getElementById(id);
  const canvas = $('board');
  const ctx = canvas.getContext('2d');
  const preview = $('next').getContext('2d');

  const COLS = 10;
  const ROWS = 20;
  const SIZE = 36;
  const signedInName = @json(auth()->user()?->name);

  const storage = {
    get(key, fallback) {
      try {
        return JSON.parse(localStorage.getItem(key)) ?? fallback;
      } catch {
        return fallback;
      }
    },
    set(key, value) {
      try {
        localStorage.setItem(key, JSON.stringify(value));
      } catch {}
    }
  };

  // Preserve existing storage keys and the plain-text player name.
  let player = signedInName || '';

  try {
    player = player || localStorage.getItem('tetrisPlayerName') || '';
  } catch {}

  let best = Number(storage.get('tetrisOfflineBest', 0)) || 0;
  let board;
  let piece;
  let nextPiece;
  let score = 0;
  let lines = 0;
  let level = 1;
  let state = 'ready';
  let timer;
  let levelTransitionTimer;

  let ranking = [];
  let rankPage = 0;
  let rankPageSize = 3;
  let leaderboardRequest = 0;

  const shapes = [
    { matrix: [[1,1,1,1]], color: '#65d9ee' },
    { matrix: [[1,1],[1,1]], color: '#f5cf70' },
    { matrix: [[0,1,0],[1,1,1]], color: '#b798f5' },
    { matrix: [[0,1,1],[1,1,0]], color: '#76dba9' },
    { matrix: [[1,1,0],[0,1,1]], color: '#f17d98' },
    { matrix: [[1,0,0],[1,1,1]], color: '#81a9f8' },
    { matrix: [[0,0,1],[1,1,1]], color: '#f5ac70' }
  ];

  function randomPiece() {
    const shape = shapes[Math.floor(Math.random() * shapes.length)];

    return {
      matrix: shape.matrix.map(row => [...row]),
      color: shape.color,
      x: Math.floor((COLS - shape.matrix[0].length) / 2),
      y: 0
    };
  }

  function valid(matrix, x, y) {
    return matrix.every((row, r) =>
      row.every((cell, c) =>
        !cell || (
          x + c >= 0 &&
          x + c < COLS &&
          y + r >= 0 &&
          y + r < ROWS &&
          !board[y + r][x + c]
        )
      )
    );
  }

  function startTimer() {
    clearInterval(timer);

    timer = setInterval(
      () => move(0, 1),
      Math.max(90, 520 - (level - 1) * 38)
    );
  }

  function updateStats() {
    if (score > best) {
      best = score;
      storage.set('tetrisOfflineBest', best);
    }

    for (const [id, value] of [
      ['scoreDisplay', score],
      ['bestDisplay', best],
      ['linesDisplay', lines],
      ['levelDisplay', level]
    ]) {
      $(id).textContent = value.toLocaleString();
      $(id).title = String(value);
    }

    const levelLines = lines % 10;
    $('levelProgress').textContent = levelLines + ' / 10';
    $('levelProgress').title = 'Lines completed for level ' + level;
  }

  function tile(context, x, y, size, color, ghost = false) {
    context.fillStyle = color;
    context.globalAlpha = ghost ? .16 : 1;
    context.fillRect(x + 1, y + 1, size - 2, size - 2);

    context.globalAlpha = 1;
    context.strokeStyle = ghost ? color : '#ffffff30';
    context.lineWidth = 1;
    context.strokeRect(x + 1.5, y + 1.5, size - 3, size - 3);

    if (!ghost) {
      context.fillStyle = '#ffffff35';
      context.fillRect(x + 3, y + 3, size - 6, 3);

      context.fillStyle = '#00000016';
      context.fillRect(x + 3, y + size - 6, size - 6, 3);
    }
  }

  function draw() {
    ctx.clearRect(0, 0, 360, 720);
    ctx.strokeStyle = '#ffffff08';
    ctx.lineWidth = 1;

    for (let x = 0; x <= 360; x += SIZE) {
      ctx.beginPath();
      ctx.moveTo(x, 0);
      ctx.lineTo(x, 720);
      ctx.stroke();
    }

    for (let y = 0; y <= 720; y += SIZE) {
      ctx.beginPath();
      ctx.moveTo(0, y);
      ctx.lineTo(360, y);
      ctx.stroke();
    }

    board.forEach((row, r) => {
      row.forEach((color, c) => {
        if (color) {
          tile(ctx, c * SIZE, r * SIZE, SIZE, color);
        }
      });
    });

    if (!piece || state === 'over') return;

    let ghostY = piece.y;

    while (valid(piece.matrix, piece.x, ghostY + 1)) {
      ghostY++;
    }

    piece.matrix.forEach((row, r) => {
      row.forEach((cell, c) => {
        if (!cell) return;

        tile(
          ctx,
          (piece.x + c) * SIZE,
          (ghostY + r) * SIZE,
          SIZE,
          piece.color,
          true
        );

        tile(
          ctx,
          (piece.x + c) * SIZE,
          (piece.y + r) * SIZE,
          SIZE,
          piece.color
        );
      });
    });
  }

  function drawNext() {
    preview.clearRect(0, 0, 120, 90);

    const matrix = nextPiece.matrix;
    const size = 24;
    const offsetX = (120 - matrix[0].length * size) / 2;
    const offsetY = (90 - matrix.length * size) / 2;

    matrix.forEach((row, r) => {
      row.forEach((cell, c) => {
        if (cell) {
          tile(
            preview,
            offsetX + c * size,
            offsetY + r * size,
            size,
            nextPiece.color
          );
        }
      });
    });
  }

  function showOverlay(title, copy, button) {
    $('overlayTitle').textContent = title;
    $('overlayCopy').textContent = copy;
    $('overlayBtn').textContent = button;
    $('gameOverlay').hidden = false;
    $('finalScore').hidden = state !== 'over';
    $('finalBest').hidden = state !== 'over';
  }

  function showLevelSuccess(previousLevel) {
    clearInterval(timer);
    state = 'level-complete';
    showOverlay(
      'SUCCESS! LEVEL ' + previousLevel,
      '10 / 10 lines complete · Level ' + level + ' is ready',
      '▶ Next level'
    );
    $('pauseBtn').disabled = true;
    clearTimeout(levelTransitionTimer);
    levelTransitionTimer = setTimeout(continueLevel, 1400);
  }

  function continueLevel() {
    clearTimeout(levelTransitionTimer);
    if (state !== 'level-complete') return;
    state = 'playing';
    $('gameOverlay').hidden = true;
    $('pauseBtn').disabled = false;
    $('pauseBtn').textContent = 'Ⅱ Pause';
    updateStats();
    startTimer();
    draw();
  }

  function spawn() {
    piece = nextPiece;
    nextPiece = randomPiece();
    drawNext();

    if (!valid(piece.matrix, piece.x, piece.y)) {
      state = 'over';

      clearInterval(timer);
      stopHold();
      updateStats();

      $('finalScore').textContent = score.toLocaleString();
      $('finalBest').textContent = 'Best: ' + best.toLocaleString();

      showOverlay('GAME OVER', player, '↻ Play again');

      $('pauseBtn').disabled = true;
      $('startBtn').textContent = '↻ Restart';

      submitScore();
    }
  }

  function lock() {
    piece.matrix.forEach((row, r) => {
      row.forEach((cell, c) => {
        if (cell) {
          board[piece.y + r][piece.x + c] = piece.color;
        }
      });
    });

    const remaining = board.filter(row => row.some(cell => !cell));
    const cleared = ROWS - remaining.length;

    board = [
      ...Array.from({ length: cleared }, () => Array(COLS).fill(0)),
      ...remaining
    ];

    if (cleared) {
      score += [0, 100, 300, 600, 1000][cleared];
      lines += cleared;
      const previousLevel = level;
      level = Math.floor(lines / 10) + 1;

      updateStats();
      if (level > previousLevel) {
        showLevelSuccess(previousLevel);
      } else {
        startTimer();
      }
    }

    spawn();
  }

  function move(dx, dy) {
    if (state !== 'playing') return;

    if (valid(piece.matrix, piece.x + dx, piece.y + dy)) {
      piece.x += dx;
      piece.y += dy;
    } else if (dy === 1) {
      lock();
    }

    draw();
  }

  function rotate() {
    if (state !== 'playing') return;

    const rotated = piece.matrix[0].map((_, c) =>
      piece.matrix.map(row => row[c]).reverse()
    );

    for (const offset of [0, -1, 1, -2, 2]) {
      if (valid(rotated, piece.x + offset, piece.y)) {
        piece.matrix = rotated;
        piece.x += offset;
        break;
      }
    }

    draw();
  }

  function hardDrop() {
    if (state !== 'playing') return;

    while (valid(piece.matrix, piece.x, piece.y + 1)) {
      piece.y++;
    }

    lock();
    draw();
  }

  function startGame() {
    if (!player) {
      $('welcome').hidden = false;
      $('playerNameInput').focus();
      return;
    }

    clearInterval(timer);
    clearTimeout(levelTransitionTimer);
    stopHold();

    board = Array.from({ length: ROWS }, () => Array(COLS).fill(0));
    score = 0;
    lines = 0;
    level = 1;
    state = 'playing';

    nextPiece = randomPiece();

    spawn();
    updateStats();
    draw();
    startTimer();

    $('gameOverlay').hidden = true;
    $('startBtn').textContent = '↻ Restart';
    $('pauseBtn').disabled = false;
    $('pauseBtn').textContent = 'Ⅱ Pause';
  }

  function togglePause() {
    if (state === 'playing') {
      state = 'paused';

      clearInterval(timer);
      stopHold();

      showOverlay('PAUSED', 'Your next move can wait.', '▶ Resume');
      $('pauseBtn').textContent = '▶ Resume';
    } else if (state === 'paused') {
      state = 'playing';
      $('gameOverlay').hidden = true;
      $('pauseBtn').textContent = 'Ⅱ Pause';

      startTimer();
    }
  }

  function localScores() {
    const value = storage.get('tetrisOfflineScores', []);

    return Array.isArray(value)
      ? value.filter(entry =>
          entry &&
          typeof entry.name === 'string' &&
          Number.isFinite(Number(entry.score))
        )
      : [];
  }

  function setRanking(scores) {
    ranking = Array.isArray(scores)
      ? scores.filter(entry => entry && typeof entry.name === 'string')
      : [];

    rankPage = 0;
    renderRanking();
  }

  function renderRanking() {
    const pages = Math.max(1, Math.ceil(ranking.length / rankPageSize));
    rankPage = Math.min(rankPage, pages - 1);

    $('leaderboardList').replaceChildren();

    ranking
      .slice(rankPage * rankPageSize, (rankPage + 1) * rankPageSize)
      .forEach((entry, index) => {
        const row = document.createElement('li');

        row.className = 'rank-row' + (
          entry.is_current_user || entry.name === player ? ' current' : ''
        );

        for (const [className, value] of [
          ['rank-number', rankPage * rankPageSize + index + 1],
          ['rank-name', entry.name],
          ['rank-score', (Number(entry.score) || 0).toLocaleString()]
        ]) {
          const span = document.createElement('span');
          span.className = className;
          span.textContent = value;
          span.title = String(value);
          row.appendChild(span);
        }

        $('leaderboardList').appendChild(row);
      });

    if (!ranking.length) {
      const row = document.createElement('li');
      row.className = 'empty';
      row.textContent = 'Your next high score belongs here.';
      $('leaderboardList').appendChild(row);
    }

    $('rankPage').textContent = (rankPage + 1) + ' / ' + pages;
    $('prevRank').disabled = rankPage === 0;
    $('nextRank').disabled = rankPage === pages - 1;
  }

  function connection(online) {
    $('connectionStatus').textContent = online
      ? 'Leaderboard connected'
      : 'Offline · local scores';

    $('connectionStatus').classList.toggle('offline', !online);
  }

  async function requestScores(options = {}) {
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 6000);

    try {
      const response = await fetch('/offline/scores', {
        ...options,
        signal: controller.signal
      });

      if (!response.ok) throw new Error('Unavailable');

      return await response.json();
    } finally {
      clearTimeout(timeout);
    }
  }

  async function loadLeaderboard() {
    const request = ++leaderboardRequest;

    if (!navigator.onLine) {
      connection(false);
      setRanking(localScores());
      return;
    }

    try {
      const data = await requestScores({
        headers: { Accept: 'application/json' }
      });

      if (request !== leaderboardRequest) return;

      connection(true);
      setRanking(data.scores || []);
    } catch {
      if (request !== leaderboardRequest) return;

      connection(false);
      setRanking(localScores());
    }
  }

  async function submitScore() {
    const entries = localScores();
    const existing = entries.find(entry =>
      entry.name.toLowerCase() === player.toLowerCase()
    );

    if (existing) {
      existing.score = Math.max(Number(existing.score) || 0, score);
    } else {
      entries.push({ name: player, score });
    }

    entries.sort((a, b) =>
      b.score - a.score || a.name.localeCompare(b.name)
    );

    storage.set('tetrisOfflineScores', entries.slice(0, 10));
    setRanking(entries.slice(0, 10));

    const request = ++leaderboardRequest;

    if (!navigator.onLine) {
      connection(false);
      return;
    }

    try {
      const data = await requestScores({
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          'X-CSRF-TOKEN': document.querySelector(
            'meta[name="csrf-token"]'
          ).content
        },
        body: JSON.stringify({ name: player, score })
      });

      if (request !== leaderboardRequest) return;

      connection(true);
      setRanking(data.scores || []);
    } catch {
      if (request === leaderboardRequest) {
        connection(false);
      }
    }
  }

  // Fit the entire 10 × 20 board into the available screen space.
  function fitLayout() {
    const area = $('boardArea');

    const width = Math.max(
      0,
      Math.floor(Math.min(area.clientWidth, area.clientHeight / 2, 360))
    );

    $('boardWrap').style.width = width + 'px';
    $('boardWrap').style.height = width * 2 + 'px';

    const size = $('leaderboardList').clientHeight < 115 ? 1 : 3;

    if (size !== rankPageSize) {
      rankPageSize = size;
      rankPage = 0;
      renderRanking();
    }
  }

  new ResizeObserver(fitLayout).observe($('boardArea'));

  window.addEventListener('resize', fitLayout);
  window.visualViewport?.addEventListener('resize', fitLayout);

  const actions = {
    left: () => move(-1, 0),
    right: () => move(1, 0),
    down: () => move(0, 1),
    rotate,
    drop: hardDrop
  };

  let holdTimeout;
  let holdInterval;

  function stopHold() {
    clearTimeout(holdTimeout);
    clearInterval(holdInterval);
  }

  for (const button of document.querySelectorAll('[data-action]')) {
    button.addEventListener('pointerdown', event => {
      if (event.button !== 0) return;

      event.preventDefault();
      stopHold();

      button.setPointerCapture(event.pointerId);

      const action = button.dataset.action;
      actions[action]();

      if (['left', 'right', 'down'].includes(action)) {
        holdTimeout = setTimeout(() => {
          holdInterval = setInterval(actions[action], 85);
        }, 220);
      }
    });

    for (const name of [
      'pointerup',
      'pointercancel',
      'lostpointercapture'
    ]) {
      button.addEventListener(name, stopHold);
    }

    // Keyboard and assistive-technology activation.
    button.addEventListener('click', event => {
      if (event.detail === 0) {
        actions[button.dataset.action]();
      }
    });
  }

  let gesture = null;

  canvas.addEventListener('pointerdown', event => {
    if (event.button !== 0) return;

    canvas.setPointerCapture(event.pointerId);

    gesture = {
      id: event.pointerId,
      x: event.clientX,
      y: event.clientY,
      startX: event.clientX,
      startY: event.clientY,
      moved: false
    };
  });

  canvas.addEventListener('pointermove', event => {
    if (!gesture || gesture.id !== event.pointerId) return;

    const dx = event.clientX - gesture.x;
    const dy = event.clientY - gesture.y;

    if (
      Math.hypot(
        event.clientX - gesture.startX,
        event.clientY - gesture.startY
      ) > 10
    ) {
      gesture.moved = true;
    }

    const threshold = Math.max(12, canvas.clientWidth / 10);

    if (Math.abs(dx) >= threshold) {
      move(Math.sign(dx), 0);
      gesture.x = event.clientX;
      gesture.y = event.clientY;
    } else if (dy >= threshold) {
      move(0, 1);
      gesture.y = event.clientY;
    }
  });

  canvas.addEventListener('pointerup', event => {
    if (
      gesture &&
      gesture.id === event.pointerId &&
      !gesture.moved
    ) {
      rotate();
    }

    gesture = null;
  });

  canvas.addEventListener('pointercancel', () => {
    gesture = null;
  });

  document.addEventListener('keydown', event => {
    if (
      !$('welcome').hidden ||
      event.target.closest('input,textarea,button') ||
      event.ctrlKey ||
      event.metaKey ||
      event.altKey
    ) {
      return;
    }

    const action = {
      ArrowLeft: 'left',
      ArrowRight: 'right',
      ArrowDown: 'down',
      ArrowUp: 'rotate',
      ' ': 'drop'
    }[event.key];

    if (action) {
      event.preventDefault();

      if (!event.repeat || !['rotate', 'drop'].includes(action)) {
        actions[action]();
      }
    }

    if (event.key.toLowerCase() === 'p' && !event.repeat) {
      event.preventDefault();
      togglePause();
    }
  });

  $('startBtn').addEventListener('click', () => {
    startGame();
    $('startBtn').blur();
  });

  $('pauseBtn').addEventListener('click', () => {
    togglePause();
    $('pauseBtn').blur();
  });

  $('overlayBtn').addEventListener('click', () => {
    if (state === 'paused') {
      togglePause();
    } else if (state === 'level-complete') {
      continueLevel();
    } else {
      startGame();
    }
  });

  $('prevRank').addEventListener('click', () => {
    rankPage--;
    renderRanking();
  });

  $('nextRank').addEventListener('click', () => {
    rankPage++;
    renderRanking();
  });

  $('reconnectBtn').addEventListener('click', () => {
    if (navigator.onLine) {
      window.location.href = '/';
    } else {
      loadLeaderboard();
    }
  });

  $('welcomeForm').addEventListener('submit', event => {
    event.preventDefault();

    const name = String(
      signedInName || $('playerNameInput').value
    ).trim();

    if (!signedInName && name.length < 2) {
      $('nameError').textContent = 'Please enter at least 2 characters.';
      return;
    }

    player = name;

    try {
      localStorage.setItem('tetrisPlayerName', player);
    } catch {}

    $('playerNameDisplay').textContent = player;
    $('playerNameDisplay').title = player;
    $('welcome').hidden = true;
    $('playerNameInput').blur();

    startGame();
  });

  window.addEventListener('online', loadLeaderboard);
  window.addEventListener('offline', loadLeaderboard);

  document.addEventListener('visibilitychange', () => {
    if (document.hidden && state === 'playing') {
      togglePause();
    }
  });

  window.addEventListener('blur', () => {
    stopHold();

    if (state === 'playing') {
      togglePause();
    }
  });

  // Initial screen.
  board = Array.from({ length: ROWS }, () => Array(COLS).fill(0));
  nextPiece = randomPiece();

  $('playerNameInput').value = player;
  $('playerNameDisplay').textContent = player || 'Guest player';
  $('welcome').hidden = Boolean(signedInName && navigator.onLine);

  updateStats();
  drawNext();
  draw();
  setRanking(localScores());
  fitLayout();
  loadLeaderboard();
})();
</script>
</body>
</html>