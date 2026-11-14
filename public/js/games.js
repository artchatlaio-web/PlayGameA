const PlayGameEngine = {
  activeGame: null,
  canvas: null,
  ctx: null,
  loopId: null,
  gameId: 1,
  customSettings: {
    name: 'CyberGamer',
    hero: 'cyborg',
    outfitColor: '#38bdf8',
    controls: 'wasd'
  },

  init(slug, canvasId, gameId = 1) {
    this.stop();
    this.canvas = document.getElementById(canvasId);
    if (!this.canvas) return;
    this.ctx = this.canvas.getContext('2d');
    this.slug = slug;
    this.gameId = gameId;

    if (slug === 'cs-shooter') this.csShooter3D();
    else if (slug === 'football') this.football3D();
    else if (slug === 'worms') this.worms3D();
    else if (slug === 'racing') this.racing3D();
    else if (slug === 'math-battle') this.mathBattle3D();
    else if (slug === 'snake') this.snake3D();
    else if (slug === 'ping-pong') this.pingPong3D();
    else if (slug === 'memory') this.memory3D();
    else if (slug === 'platformer') this.platformer3D();
    else if (slug === 'reaction-speed') this.reactionSpeed3D();
    else if (slug === 'puzzle') this.puzzle3D();
    else if (slug === 'tic-tac-toe') this.ticTacToe3D();
  },

  stop() {
    if (this.loopId) cancelAnimationFrame(this.loopId);
    this.loopId = null;
    if (this.activeGame && this.activeGame.cleanup) {
      this.activeGame.cleanup();
    }
    this.activeGame = null;
  },

  restart() {
    this.stop();
    let cid = this.canvas ? this.canvas.id : 'game-canvas';
    this.init(this.slug, cid, this.gameId);
  },

  saveScore(score) {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!token) return;
    fetch('/score', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': token,
        'Accept': 'application/json'
      },
      body: JSON.stringify({ game_id: this.gameId, score: Math.round(score) })
    }).catch(() => {});
  },

  drawHUD(ctx, width, timeLeft, score, hp = null) {
    let m = Math.floor(timeLeft / 60);
    let s = timeLeft % 60;
    let timeStr = `${m < 10 ? '0' : ''}${m}:${s < 10 ? '0' : ''}${s}`;
    let name = this.customSettings.name || 'Player';
    let outfit = this.customSettings.outfitColor || '#38bdf8';

    ctx.fillStyle = 'rgba(6, 11, 25, 0.85)';
    ctx.fillRect(12, 12, 340, 42);
    ctx.strokeStyle = outfit;
    ctx.lineWidth = 1.5;
    ctx.strokeRect(12, 12, 340, 42);

    ctx.fillStyle = outfit;
    ctx.font = 'bold 15px sans-serif';
    ctx.textAlign = 'left';
    ctx.fillText(`👤 ${name}`, 22, 38);

    ctx.fillStyle = timeLeft < 30 ? '#ef4444' : '#38bdf8';
    ctx.fillText(`⏱ 5M: ${timeStr}`, 150, 38);

    ctx.fillStyle = '#facc15';
    ctx.fillText(`★ ${score}`, 275, 38);

    if (hp !== null) {
      ctx.fillStyle = 'rgba(6, 11, 25, 0.85)';
      ctx.fillRect(width - 150, 12, 138, 42);
      ctx.strokeStyle = hp > 30 ? '#10b981' : '#ef4444';
      ctx.strokeRect(width - 150, 12, 138, 42);

      ctx.fillStyle = hp > 30 ? '#10b981' : '#ef4444';
      ctx.fillText(`❤ Shield: ${Math.round(hp)}%`, width - 138, 38);
    }
  },

  drawTopRadarMap(ctx, width, playerX, playerY, mapW, mapH, dots = [], dir = 0) {
    let rw = 160, rh = 70;
    let rx = width / 2 - rw / 2, ry = 10;

    ctx.fillStyle = 'rgba(6, 11, 25, 0.9)';
    ctx.fillRect(rx, ry, rw, rh);
    ctx.strokeStyle = this.customSettings.outfitColor || '#38bdf8';
    ctx.lineWidth = 1.5;
    ctx.strokeRect(rx, ry, rw, rh);

    let sweep = (Date.now() / 900) % rw;
    ctx.fillStyle = 'rgba(56, 189, 248, 0.08)';
    ctx.fillRect(rx + sweep, ry, 6, rh);

    ctx.fillStyle = '#64748b';
    ctx.font = '9px sans-serif';
    ctx.textAlign = 'center';
    ctx.fillText('TACTICAL ARENA MAP', rx + rw / 2, ry + 11);

    dots.forEach(d => {
      let dx = rx + (d.x / mapW) * rw;
      let dy = ry + (d.y / mapH) * rh;
      ctx.fillStyle = d.color || '#ef4444';
      ctx.beginPath();
      ctx.arc(dx, dy, 3, 0, Math.PI * 2);
      ctx.fill();
    });

    let px = rx + (playerX / mapW) * rw;
    let py = ry + (playerY / mapH) * rh;

    ctx.strokeStyle = this.customSettings.outfitColor || '#38bdf8';
    ctx.lineWidth = 1.5;
    ctx.beginPath();
    ctx.moveTo(px, py);
    ctx.lineTo(px + Math.cos(dir) * 9, py + Math.sin(dir) * 9);
    ctx.stroke();

    ctx.fillStyle = this.customSettings.outfitColor || '#38bdf8';
    ctx.beginPath();
    ctx.arc(px, py, 4, 0, Math.PI * 2);
    ctx.fill();
  },

  csShooter3D() {
    const cvs = this.canvas;
    const ctx = this.ctx;
    cvs.width = 800;
    cvs.height = 480;

    let timeLeft = 300;
    let score = 0;
    let hp = 100;
    let gameOver = false;

    let timerInt = setInterval(() => {
      if (timeLeft > 0) timeLeft--;
      else {
        gameOver = true;
        this.saveScore(score);
        clearInterval(timerInt);
      }
    }, 1000);

    const map = [
      [1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1],
      [1,0,0,0,0,0,2,0,0,0,0,0,0,2,0,0,0,1],
      [1,0,2,2,0,0,2,0,2,2,0,0,0,2,0,2,0,1],
      [1,0,2,0,0,0,0,0,0,2,0,2,0,0,0,2,0,1],
      [1,0,0,0,2,2,2,0,0,0,0,2,2,0,0,0,0,1],
      [1,0,2,0,2,0,2,0,2,0,0,0,2,0,2,2,0,1],
      [1,0,2,0,0,0,0,0,2,0,0,0,0,0,0,2,0,1],
      [1,0,0,0,2,2,0,0,2,2,2,0,2,2,0,0,0,1],
      [1,0,2,0,0,0,0,0,0,0,0,0,0,2,0,2,0,1],
      [1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1]
    ];
    const mapW = map[0].length;
    const mapH = map.length;

    let px = 2.5, py = 2.5, dir = 0;
    let keys = {};
    let muzzle = 0;

    let enemies = [
      { x: 5.5, y: 1.5, hp: 60, alive: true },
      { x: 10.5, y: 3.5, hp: 60, alive: true },
      { x: 14.5, y: 2.5, hp: 60, alive: true },
      { x: 7.5, y: 6.5, hp: 60, alive: true },
      { x: 12.5, y: 7.5, hp: 60, alive: true },
      { x: 15.5, y: 8.2, hp: 60, alive: true }
    ];

    const onKeyD = e => keys[e.key.toLowerCase()] = true;
    const onKeyU = e => keys[e.key.toLowerCase()] = false;
    const onClick = () => {
      if (gameOver || hp <= 0) return;
      muzzle = 6;
      enemies.forEach(en => {
        if (!en.alive) return;
        let ex = en.x - px;
        let ey = en.y - py;
        let angle = Math.atan2(ey, ex) - dir;
        while (angle < -Math.PI) angle += Math.PI * 2;
        while (angle > Math.PI) angle -= Math.PI * 2;
        if (Math.abs(angle) < 0.28) {
          en.hp -= 35;
          if (en.hp <= 0) {
            en.alive = false;
            score += 150;
            setTimeout(() => {
              en.x = Math.random() * (mapW - 3) + 1.5;
              en.y = Math.random() * (mapH - 3) + 1.5;
              en.hp = 60;
              en.alive = true;
            }, 3000);
          }
        }
      });
    };

    window.addEventListener('keydown', onKeyD);
    window.addEventListener('keyup', onKeyU);
    cvs.addEventListener('click', onClick);

    this.activeGame = {
      cleanup: () => {
        clearInterval(timerInt);
        window.removeEventListener('keydown', onKeyD);
        window.removeEventListener('keyup', onKeyU);
        cvs.removeEventListener('click', onClick);
      }
    };

    const loop = () => {
      let isArrows = this.customSettings.controls === 'arrows';
      if (isArrows) {
        if (keys['arrowleft']) dir -= 0.04;
        if (keys['arrowright']) dir += 0.04;
      } else {
        if (keys['q'] || keys['arrowleft']) dir -= 0.04;
        if (keys['e'] || keys['arrowright']) dir += 0.04;
      }

      let speed = 0.045;
      let nx = px, ny = py;
      if (keys['w'] || (isArrows && keys['arrowup'])) { nx += Math.cos(dir) * speed; ny += Math.sin(dir) * speed; }
      if (keys['s'] || (isArrows && keys['arrowdown'])) { nx -= Math.cos(dir) * speed; ny -= Math.sin(dir) * speed; }
      if (!isArrows && keys['a']) { nx += Math.cos(dir - Math.PI / 2) * speed; ny += Math.sin(dir - Math.PI / 2) * speed; }
      if (!isArrows && keys['d']) { nx += Math.cos(dir + Math.PI / 2) * speed; ny += Math.sin(dir + Math.PI / 2) * speed; }

      if (map[Math.floor(ny)] && map[Math.floor(ny)][Math.floor(nx)] === 0) { px = nx; py = ny; }

      enemies.forEach(en => {
        if (!en.alive) return;
        let dist = Math.hypot(en.x - px, en.y - py);
        if (dist > 1.2) {
          en.x += ((px - en.x) / dist) * 0.016;
          en.y += ((py - en.y) / dist) * 0.016;
        } else {
          hp = Math.max(0, hp - 0.28);
          if (hp <= 0 && !gameOver) {
            gameOver = true;
            this.saveScore(score);
          }
        }
      });

      let ceilGrad = ctx.createLinearGradient(0, 0, 0, cvs.height / 2);
      ceilGrad.addColorStop(0, '#040714');
      ceilGrad.addColorStop(1, '#0e172a');
      ctx.fillStyle = ceilGrad;
      ctx.fillRect(0, 0, cvs.width, cvs.height / 2);

      let floorGrad = ctx.createLinearGradient(0, cvs.height / 2, 0, cvs.height);
      floorGrad.addColorStop(0, '#0f172a');
      floorGrad.addColorStop(1, '#020617');
      ctx.fillStyle = floorGrad;
      ctx.fillRect(0, cvs.height / 2, cvs.width, cvs.height / 2);

      const numRays = 180;
      const fov = Math.PI / 3;
      const stripW = cvs.width / numRays;

      for (let i = 0; i < numRays; i++) {
        let rayAngle = dir - fov / 2 + (i / numRays) * fov;
        let dist = 0;
        let hit = false;
        let wallType = 1;
        let cosA = Math.cos(rayAngle);
        let sinA = Math.sin(rayAngle);

        while (!hit && dist < 18) {
          dist += 0.04;
          let cx = Math.floor(px + cosA * dist);
          let cy = Math.floor(py + sinA * dist);
          if (cx < 0 || cx >= mapW || cy < 0 || cy >= mapH || (map[cy] && map[cy][cx] > 0)) {
            hit = true;
            wallType = map[cy] ? map[cy][cx] : 1;
          }
        }

        let correctDist = dist * Math.cos(rayAngle - dir);
        let wallH = (cvs.height / correctDist);
        let shade = Math.max(20, Math.min(230, 240 - correctDist * 13));

        let neonStripe = Math.sin((i + Date.now() / 200) * 0.2) > 0.6;
        if (neonStripe) {
          ctx.fillStyle = wallType === 2 ? '#ec4899' : (this.customSettings.outfitColor || '#38bdf8');
        } else {
          ctx.fillStyle = `rgb(${shade * 0.25}, ${shade * 0.45}, ${shade * 0.8})`;
        }
        ctx.fillRect(i * stripW, (cvs.height - wallH) / 2, stripW + 1, wallH);
      }

      enemies.forEach(en => {
        if (!en.alive) return;
        let ex = en.x - px;
        let ey = en.y - py;
        let dist = Math.hypot(ex, ey);
        let angle = Math.atan2(ey, ex) - dir;
        while (angle < -Math.PI) angle += Math.PI * 2;
        while (angle > Math.PI) angle -= Math.PI * 2;

        if (dist > 0.5 && Math.abs(angle) < fov) {
          let screenX = (cvs.width / 2) + Math.tan(angle) * (cvs.width / 2);
          let h = (cvs.height / dist) * 0.75;
          let w = h * 0.5;

          ctx.fillStyle = 'rgba(239, 68, 68, 0.2)';
          ctx.beginPath();
          ctx.arc(screenX, cvs.height / 2, w * 0.8, 0, Math.PI * 2);
          ctx.fill();

          ctx.fillStyle = '#ef4444';
          ctx.fillRect(screenX - w / 2, cvs.height / 2 - h * 0.3, w, h * 0.7);

          ctx.fillStyle = '#fef08a';
          ctx.fillRect(screenX - w * 0.3, cvs.height / 2 - h * 0.2, w * 0.6, h * 0.15);
        }
      });

      let outfit = this.customSettings.outfitColor || '#38bdf8';
      let gunX = cvs.width / 2 + 80;
      let gunY = cvs.height - 130;

      if (muzzle > 0) {
        let flash = ctx.createRadialGradient(gunX - 25, gunY - 40, 2, gunX - 25, gunY - 40, 35);
        flash.addColorStop(0, '#ffffff');
        flash.addColorStop(0.4, outfit);
        flash.addColorStop(1, 'transparent');
        ctx.fillStyle = flash;
        ctx.beginPath();
        ctx.arc(gunX - 25, gunY - 40, 35, 0, Math.PI * 2);
        ctx.fill();
        muzzle--;
      }

      ctx.fillStyle = '#1e293b';
      ctx.fillRect(gunX - 35, gunY, 70, 130);
      ctx.fillStyle = outfit;
      ctx.fillRect(gunX - 20, gunY + 20, 40, 6);
      ctx.fillStyle = '#0f172a';
      ctx.fillRect(gunX - 25, gunY - 30, 50, 40);

      ctx.strokeStyle = outfit;
      ctx.lineWidth = 2;
      ctx.beginPath();
      ctx.arc(cvs.width / 2, cvs.height / 2, 12, 0, Math.PI * 2);
      ctx.stroke();

      this.drawHUD(ctx, cvs.width, timeLeft, score, hp);

      let radarDots = enemies.filter(e => e.alive).map(e => ({ x: e.x, y: e.y, color: '#ef4444' }));
      this.drawTopRadarMap(ctx, cvs.width, px, py, mapW, mapH, radarDots, dir);

      if (gameOver) {
        ctx.fillStyle = 'rgba(2, 6, 23, 0.9)';
        ctx.fillRect(0, 0, cvs.width, cvs.height);
        ctx.fillStyle = hp > 0 ? '#10b981' : '#ef4444';
        ctx.font = 'bold 36px sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText(hp > 0 ? 'CYBER MISSION COMPLETED!' : 'SYSTEM ELIMINATED!', cvs.width / 2, cvs.height / 2 - 10);
        ctx.font = '18px sans-serif';
        ctx.fillStyle = '#fff';
        ctx.fillText(`Player: ${this.customSettings.name} | Score: ${score}`, cvs.width / 2, cvs.height / 2 + 30);
        return;
      }

      this.loopId = requestAnimationFrame(loop);
    };
    loop();
  },

  racing3D() {
    const cvs = this.canvas;
    const ctx = this.ctx;
    cvs.width = 800;
    cvs.height = 480;

    let timeLeft = 300;
    let score = 0;
    let speed = 0;
    let carX = 0;
    let roadPos = 0;
    let gameOver = false;

    let timerInt = setInterval(() => {
      if (timeLeft > 0) timeLeft--;
      else {
        gameOver = true;
        this.saveScore(score);
        clearInterval(timerInt);
      }
    }, 1000);

    let enemies = [
      { z: 500, x: -0.45, speed: 2, color: '#f43f5e' },
      { z: 900, x: 0.35, speed: 2.2, color: '#eab308' },
      { z: 1300, x: -0.1, speed: 1.8, color: '#a855f7' },
      { z: 1700, x: 0.45, speed: 2.4, color: '#10b981' }
    ];

    let keys = {};
    const onKeyD = e => keys[e.key.toLowerCase()] = true;
    const onKeyU = e => keys[e.key.toLowerCase()] = false;
    window.addEventListener('keydown', onKeyD);
    window.addEventListener('keyup', onKeyU);

    this.activeGame = {
      cleanup: () => {
        clearInterval(timerInt);
        window.removeEventListener('keydown', onKeyD);
        window.removeEventListener('keyup', onKeyU);
      }
    };

    const loop = () => {
      let isArrows = this.customSettings.controls === 'arrows';
      if (keys['w'] || (isArrows && keys['arrowup'])) speed = Math.min(240, speed + 1.8);
      else speed = Math.max(30, speed - 0.8);

      if ((!isArrows && keys['a']) || keys['arrowleft']) carX = Math.max(-1.4, carX - 0.04);
      if ((!isArrows && keys['d']) || keys['arrowright']) carX = Math.min(1.4, carX + 0.04);

      roadPos += speed * 0.16;
      score += Math.floor(speed * 0.025);

      ctx.fillStyle = '#050714';
      ctx.fillRect(0, 0, cvs.width, cvs.height / 2);

      ctx.fillStyle = '#090d1e';
      ctx.fillRect(0, cvs.height / 2, cvs.width, cvs.height / 2);

      const horizon = cvs.height / 2;
      for (let y = cvs.height; y > horizon; y -= 4) {
        let p = (y - horizon) / horizon;
        let w = cvs.width * p * 0.8;
        let x = cvs.width / 2 - (carX * 180 * p);

        let stripe = Math.sin((y + roadPos) * 0.12) > 0;
        ctx.fillStyle = stripe ? '#0f172a' : '#1e293b';
        ctx.fillRect(x - w / 2, y, w, 4);

        ctx.fillStyle = stripe ? '#ec4899' : (this.customSettings.outfitColor || '#38bdf8');
        ctx.fillRect(x - w / 2 - 10 * p, y, 10 * p, 4);
        ctx.fillRect(x + w / 2, y, 10 * p, 4);

        ctx.fillStyle = '#f8fafc';
        ctx.fillRect(x - 2 * p, y, 4 * p, 4);
      }

      enemies.forEach(en => {
        en.z -= (speed * 0.16 - en.speed);
        if (en.z <= 0) en.z = 2000;

        let scale = 320 / en.z;
        let ex = cvs.width / 2 + (en.x - carX * 0.45) * scale * cvs.width * 0.42;
        let ey = horizon + scale * horizon * 0.88;
        let ew = 96 * scale;
        let eh = 55 * scale;

        if (scale > 0.05 && scale < 2.5) {
          ctx.fillStyle = en.color;
          ctx.fillRect(ex - ew / 2, ey - eh, ew, eh);

          ctx.fillStyle = 'rgba(255,255,255,0.7)';
          ctx.fillRect(ex - ew * 0.35, ey - eh * 0.8, ew * 0.7, eh * 0.3);

          ctx.fillStyle = '#ef4444';
          ctx.fillRect(ex - ew * 0.4, ey - eh * 0.2, ew * 0.2, eh * 0.2);
          ctx.fillRect(ex + ew * 0.2, ey - eh * 0.2, ew * 0.2, eh * 0.2);
        }

        if (en.z < 85 && en.z > 20 && Math.abs(en.x - carX) < 0.48) {
          speed = Math.max(20, speed - 60);
          score = Math.max(0, score - 50);
        }
      });

      let playerY = cvs.height - 95;
      let playerW = 96, playerH = 55;
      let outfit = this.customSettings.outfitColor || '#38bdf8';

      ctx.fillStyle = outfit;
      ctx.fillRect(cvs.width / 2 - playerW / 2, playerY, playerW, playerH);

      ctx.fillStyle = '#ffffff';
      ctx.fillRect(cvs.width / 2 - playerW * 0.35, playerY + 8, playerW * 0.7, 20);

      ctx.fillStyle = '#ef4444';
      ctx.fillRect(cvs.width / 2 - playerW * 0.45, playerY + 44, 18, 6);
      ctx.fillRect(cvs.width / 2 + playerW * 0.25, playerY + 44, 18, 6);

      this.drawHUD(ctx, cvs.width, timeLeft, score);

      ctx.fillStyle = 'rgba(6, 11, 25, 0.85)';
      ctx.fillRect(12, 60, 140, 30);
      ctx.strokeStyle = outfit;
      ctx.strokeRect(12, 60, 140, 30);
      ctx.fillStyle = outfit;
      ctx.font = 'bold 15px sans-serif';
      ctx.fillText(`⚡ ${Math.round(speed)} KM/H`, 24, 81);

      let radarDots = enemies.map(e => ({ x: (e.x + 1.5) * 40, y: (e.z / 2000) * 80, color: e.color }));
      this.drawTopRadarMap(ctx, cvs.width, (carX + 1.5) * 40, 65, 120, 80, radarDots, -Math.PI / 2);

      if (gameOver) {
        ctx.fillStyle = 'rgba(2, 6, 23, 0.9)';
        ctx.fillRect(0, 0, cvs.width, cvs.height);
        ctx.fillStyle = '#10b981';
        ctx.font = 'bold 36px sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText('FINISH LINE REACHED!', cvs.width / 2, cvs.height / 2 - 10);
        ctx.font = '18px sans-serif';
        ctx.fillStyle = '#fff';
        ctx.fillText(`Driver: ${this.customSettings.name} | Score: ${score}`, cvs.width / 2, cvs.height / 2 + 30);
        return;
      }

      this.loopId = requestAnimationFrame(loop);
    };
    loop();
  },

  football3D() {
    const cvs = this.canvas;
    const ctx = this.ctx;
    cvs.width = 800;
    cvs.height = 480;

    let timeLeft = 300;
    let score1 = 0, score2 = 0;
    let ball = { x: 400, y: 240, z: 0, vx: 0, vy: 0, vz: 0 };
    let p1 = { x: 200, y: 240, r: 20 };
    let p2 = { x: 600, y: 240, r: 20 };
    let keys = {};
    let gameOver = false;

    let timerInt = setInterval(() => {
      if (timeLeft > 0) timeLeft--;
      else {
        gameOver = true;
        this.saveScore(score1 * 100);
        clearInterval(timerInt);
      }
    }, 1000);

    const onKeyD = e => keys[e.key.toLowerCase()] = true;
    const onKeyU = e => keys[e.key.toLowerCase()] = false;
    window.addEventListener('keydown', onKeyD);
    window.addEventListener('keyup', onKeyU);

    this.activeGame = {
      cleanup: () => {
        clearInterval(timerInt);
        window.removeEventListener('keydown', onKeyD);
        window.removeEventListener('keyup', onKeyU);
      }
    };

    const loop = () => {
      let isArrows = this.customSettings.controls === 'arrows';
      if (keys['w'] || (isArrows && keys['arrowup'])) p1.y = Math.max(50, p1.y - 4.2);
      if (keys['s'] || (isArrows && keys['arrowdown'])) p1.y = Math.min(cvs.height - 50, p1.y + 4.2);
      if ((!isArrows && keys['a']) || keys['arrowleft']) p1.x = Math.max(50, p1.x - 4.2);
      if ((!isArrows && keys['d']) || keys['arrowright']) p1.x = Math.min(cvs.width / 2 - 10, p1.x + 4.2);

      if (ball.y < p2.y) p2.y -= 2.8;
      else p2.y += 2.8;
      if (ball.x > 400) {
        if (ball.x < p2.x) p2.x -= 2.2;
        else p2.x += 2.2;
      }

      ball.x += ball.vx;
      ball.y += ball.vy;
      ball.z += ball.vz;
      ball.vz -= 0.32;
      if (ball.z <= 0) {
        ball.z = 0;
        ball.vz *= -0.7;
      }
      ball.vx *= 0.98;
      ball.vy *= 0.98;

      [p1, p2].forEach(p => {
        if (Math.hypot(p.x - ball.x, p.y - ball.y) < p.r + 14 && ball.z < 30) {
          let angle = Math.atan2(ball.y - p.y, ball.x - p.x);
          ball.vx = Math.cos(angle) * 8.5;
          ball.vy = Math.sin(angle) * 8.5;
          ball.vz = 5;
        }
      });

      if (ball.y < 40 || ball.y > cvs.height - 40) ball.vy *= -1;

      if (ball.x < 35 && ball.y > 170 && ball.y < 310) {
        score2++;
        ball.x = 400; ball.y = 240; ball.vx = 0; ball.vy = 0; ball.z = 10;
      } else if (ball.x > cvs.width - 35 && ball.y > 170 && ball.y < 310) {
        score1++;
        ball.x = 400; ball.y = 240; ball.vx = 0; ball.vy = 0; ball.z = 10;
      } else if (ball.x < 25 || ball.x > cvs.width - 25) {
        ball.vx *= -1;
      }

      ctx.fillStyle = '#064e3b';
      ctx.fillRect(0, 0, cvs.width, cvs.height);

      ctx.strokeStyle = '#10b981';
      ctx.lineWidth = 3;
      ctx.strokeRect(30, 30, cvs.width - 60, cvs.height - 60);
      ctx.beginPath();
      ctx.moveTo(cvs.width / 2, 30);
      ctx.lineTo(cvs.width / 2, cvs.height - 30);
      ctx.stroke();
      ctx.beginPath();
      ctx.arc(cvs.width / 2, cvs.height / 2, 60, 0, Math.PI * 2);
      ctx.stroke();

      let p1Grad = ctx.createRadialGradient(p1.x - 5, p1.y - 5, 2, p1.x, p1.y, p1.r);
      p1Grad.addColorStop(0, '#ffffff');
      p1Grad.addColorStop(0.5, this.customSettings.outfitColor || '#38bdf8');
      p1Grad.addColorStop(1, '#0284c7');
      ctx.fillStyle = p1Grad;
      ctx.beginPath();
      ctx.arc(p1.x, p1.y, p1.r, 0, Math.PI * 2);
      ctx.fill();

      let p2Grad = ctx.createRadialGradient(p2.x - 5, p2.y - 5, 2, p2.x, p2.y, p2.r);
      p2Grad.addColorStop(0, '#fca5a5');
      p2Grad.addColorStop(1, '#b91c1c');
      ctx.fillStyle = p2Grad;
      ctx.beginPath();
      ctx.arc(p2.x, p2.y, p2.r, 0, Math.PI * 2);
      ctx.fill();

      let ballGrad = ctx.createRadialGradient(ball.x - 3, ball.y - ball.z - 3, 1, ball.x, ball.y - ball.z, 11);
      ballGrad.addColorStop(0, '#ffffff');
      ballGrad.addColorStop(1, '#94a3b8');
      ctx.fillStyle = ballGrad;
      ctx.beginPath();
      ctx.arc(ball.x, ball.y - ball.z, 11, 0, Math.PI * 2);
      ctx.fill();

      this.drawHUD(ctx, cvs.width, timeLeft, score1 * 100);

      ctx.fillStyle = '#fff';
      ctx.font = 'bold 26px sans-serif';
      ctx.textAlign = 'center';
      ctx.fillText(`${score1} : ${score2}`, cvs.width / 2, 85);

      this.drawTopRadarMap(ctx, cvs.width, p1.x, p1.y, cvs.width, cvs.height, [
        { x: p2.x, y: p2.y, color: '#ef4444' },
        { x: ball.x, y: ball.y, color: '#ffffff' }
      ]);

      if (gameOver) {
        ctx.fillStyle = 'rgba(2, 6, 23, 0.9)';
        ctx.fillRect(0, 0, cvs.width, cvs.height);
        ctx.fillStyle = '#10b981';
        ctx.font = 'bold 36px sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText('MATCH COMPLETED!', cvs.width / 2, cvs.height / 2 - 10);
        return;
      }

      this.loopId = requestAnimationFrame(loop);
    };
    loop();
  },

  worms3D() {
    const cvs = this.canvas;
    const ctx = this.ctx;
    cvs.width = 800;
    cvs.height = 480;

    let timeLeft = 300;
    let score = 0;
    let length = 20;
    let head = { x: 400, y: 240, angle: 0, speed: 3.4 };
    let body = [];
    let food = [];
    let gameOver = false;

    let timerInt = setInterval(() => {
      if (timeLeft > 0) timeLeft--;
      else {
        gameOver = true;
        this.saveScore(score);
        clearInterval(timerInt);
      }
    }, 1000);

    for (let i = 0; i < 45; i++) {
      food.push({
        x: Math.random() * (cvs.width - 60) + 30,
        y: Math.random() * (cvs.height - 60) + 30,
        color: ['#f43f5e', '#a855f7', '#38bdf8', '#10b981', '#facc15'][Math.floor(Math.random() * 5)]
      });
    }

    const onMouseMove = e => {
      const rect = cvs.getBoundingClientRect();
      const scaleX = cvs.width / rect.width;
      const scaleY = cvs.height / rect.height;
      let mx = (e.clientX - rect.left) * scaleX;
      let my = (e.clientY - rect.top) * scaleY;
      head.angle = Math.atan2(my - head.y, mx - head.x);
    };
    cvs.addEventListener('mousemove', onMouseMove);

    this.activeGame = {
      cleanup: () => {
        clearInterval(timerInt);
        cvs.removeEventListener('mousemove', onMouseMove);
      }
    };

    const loop = () => {
      head.x += Math.cos(head.angle) * head.speed;
      head.y += Math.sin(head.angle) * head.speed;

      if (head.x < 15) head.x = cvs.width - 15;
      if (head.x > cvs.width - 15) head.x = 15;
      if (head.y < 15) head.y = cvs.height - 15;
      if (head.y > cvs.height - 15) head.y = 15;

      body.unshift({ x: head.x, y: head.y });
      if (body.length > length) body.pop();

      food.forEach((f, i) => {
        if (Math.hypot(head.x - f.x, head.y - f.y) < 20) {
          score += 20;
          length += 2;
          food[i] = {
            x: Math.random() * (cvs.width - 60) + 30,
            y: Math.random() * (cvs.height - 60) + 30,
            color: ['#f43f5e', '#a855f7', '#38bdf8', '#10b981', '#facc15'][Math.floor(Math.random() * 5)]
          };
        }
      });

      ctx.fillStyle = '#040714';
      ctx.fillRect(0, 0, cvs.width, cvs.height);

      food.forEach(f => {
        let grad = ctx.createRadialGradient(f.x, f.y, 2, f.x, f.y, 9);
        grad.addColorStop(0, '#ffffff');
        grad.addColorStop(0.7, f.color);
        grad.addColorStop(1, 'transparent');
        ctx.fillStyle = grad;
        ctx.beginPath();
        ctx.arc(f.x, f.y, 9, 0, Math.PI * 2);
        ctx.fill();
      });

      let outfit = this.customSettings.outfitColor || '#38bdf8';
      body.forEach((seg, idx) => {
        let r = Math.max(5, 13 - idx * 0.12);
        let grad = ctx.createRadialGradient(seg.x - r * 0.3, seg.y - r * 0.3, 1, seg.x, seg.y, r);
        grad.addColorStop(0, '#ffffff');
        grad.addColorStop(0.6, outfit);
        grad.addColorStop(1, '#0c4a6e');
        ctx.fillStyle = grad;
        ctx.beginPath();
        ctx.arc(seg.x, seg.y, r, 0, Math.PI * 2);
        ctx.fill();
      });

      this.drawHUD(ctx, cvs.width, timeLeft, score);

      let radarFood = food.slice(0, 12).map(f => ({ x: f.x, y: f.y, color: f.color }));
      this.drawTopRadarMap(ctx, cvs.width, head.x, head.y, cvs.width, cvs.height, radarFood, head.angle);

      if (gameOver) {
        ctx.fillStyle = 'rgba(2, 6, 23, 0.9)';
        ctx.fillRect(0, 0, cvs.width, cvs.height);
        ctx.fillStyle = '#10b981';
        ctx.font = 'bold 36px sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText('5 MINUTES SURVIVED!', cvs.width / 2, cvs.height / 2 - 10);
        return;
      }

      this.loopId = requestAnimationFrame(loop);
    };
    loop();
  },

  mathBattle3D() {
    const cvs = this.canvas;
    const ctx = this.ctx;
    cvs.width = 640;
    cvs.height = 440;

    let timeLeft = 300;
    let score = 0;
    let num1 = Math.floor(Math.random() * 15) + 2;
    let num2 = Math.floor(Math.random() * 15) + 2;
    let correct = num1 + num2;
    let options = [correct, correct + 3, Math.max(1, correct - 2), correct + 5].sort(() => Math.random() - 0.5);
    let rot = 0;
    let gameOver = false;

    let timerInt = setInterval(() => {
      if (timeLeft > 0) timeLeft--;
      else {
        gameOver = true;
        this.saveScore(score);
        clearInterval(timerInt);
      }
    }, 1000);

    const onClick = e => {
      if (gameOver) return;
      const rect = cvs.getBoundingClientRect();
      const scaleX = cvs.width / rect.width;
      const scaleY = cvs.height / rect.height;
      let mx = (e.clientX - rect.left) * scaleX;
      let my = (e.clientY - rect.top) * scaleY;

      options.forEach((opt, idx) => {
        let bx = 90 + (idx % 2) * 250;
        let by = 260 + Math.floor(idx / 2) * 80;
        if (mx > bx && mx < bx + 210 && my > by && my < by + 65) {
          if (opt === correct) score += 25;
          else score = Math.max(0, score - 15);

          num1 = Math.floor(Math.random() * 18) + 2;
          num2 = Math.floor(Math.random() * 18) + 2;
          correct = num1 + num2;
          options = [correct, correct + 3, Math.max(1, correct - 3), correct + 4].sort(() => Math.random() - 0.5);
        }
      });
    };
    cvs.addEventListener('click', onClick);

    this.activeGame = {
      cleanup: () => {
        clearInterval(timerInt);
        cvs.removeEventListener('click', onClick);
      }
    };

    const loop = () => {
      rot += 0.02;

      ctx.fillStyle = '#060a18';
      ctx.fillRect(0, 0, cvs.width, cvs.height);

      let cx = cvs.width / 2;
      let cy = 160;
      let cubeSize = 55;
      let cosR = Math.cos(rot);

      ctx.fillStyle = 'rgba(56, 189, 248, 0.15)';
      ctx.beginPath();
      ctx.moveTo(cx - cubeSize * cosR, cy - 30);
      ctx.lineTo(cx + cubeSize * cosR, cy - 30);
      ctx.lineTo(cx + cubeSize * cosR, cy + 30);
      ctx.lineTo(cx - cubeSize * cosR, cy + 30);
      ctx.closePath();
      ctx.fill();
      ctx.strokeStyle = this.customSettings.outfitColor || '#38bdf8';
      ctx.lineWidth = 2;
      ctx.stroke();

      ctx.fillStyle = '#fff';
      ctx.font = 'bold 44px sans-serif';
      ctx.textAlign = 'center';
      ctx.fillText(`${num1} + ${num2} = ?`, cx, cy + 14);

      options.forEach((opt, idx) => {
        let bx = 90 + (idx % 2) * 250;
        let by = 260 + Math.floor(idx / 2) * 80;

        let grad = ctx.createLinearGradient(bx, by, bx, by + 65);
        grad.addColorStop(0, 'rgba(30, 41, 59, 0.9)');
        grad.addColorStop(1, 'rgba(15, 23, 42, 0.9)');
        ctx.fillStyle = grad;
        ctx.fillRect(bx, by, 210, 65);

        ctx.strokeStyle = this.customSettings.outfitColor || '#38bdf8';
        ctx.lineWidth = 1.5;
        ctx.strokeRect(bx, by, 210, 65);

        ctx.fillStyle = '#f8fafc';
        ctx.font = 'bold 28px sans-serif';
        ctx.fillText(opt, bx + 105, by + 42);
      });

      this.drawHUD(ctx, cvs.width, timeLeft, score);

      if (gameOver) {
        ctx.fillStyle = 'rgba(2, 6, 23, 0.9)';
        ctx.fillRect(0, 0, cvs.width, cvs.height);
        ctx.fillStyle = '#10b981';
        ctx.font = 'bold 36px sans-serif';
        ctx.fillText('NEXUS SESSION COMPLETE!', cvs.width / 2, cvs.height / 2);
        return;
      }

      this.loopId = requestAnimationFrame(loop);
    };
    loop();
  },

  snake3D() {
    const cvs = this.canvas;
    const ctx = this.ctx;
    cvs.width = 500;
    cvs.height = 460;
    const grid = 18;

    let timeLeft = 300;
    let score = 0;
    let snake = [{ x: 8, y: 8 }, { x: 7, y: 8 }];
    let dx = 1, dy = 0;
    let food = { x: 14, y: 12 };
    let gameOver = false;

    let timerInt = setInterval(() => {
      if (timeLeft > 0) timeLeft--;
      else {
        gameOver = true;
        this.saveScore(score);
        clearInterval(timerInt);
      }
    }, 1000);

    const onKey = e => {
      const k = e.key.toLowerCase();
      let isArrows = this.customSettings.controls === 'arrows';
      if ((k === 'arrowup' || (!isArrows && k === 'w')) && dy === 0) { dx = 0; dy = -1; }
      else if ((k === 'arrowdown' || (!isArrows && k === 's')) && dy === 0) { dx = 0; dy = 1; }
      else if ((k === 'arrowleft' || (!isArrows && k === 'a')) && dx === 0) { dx = -1; dy = 0; }
      else if ((k === 'arrowright' || (!isArrows && k === 'd')) && dx === 0) { dx = 1; dy = 0; }
    };
    window.addEventListener('keydown', onKey);

    let moveInt = setInterval(() => {
      if (gameOver) return;
      let head = { x: snake[0].x + dx, y: snake[0].y + dy };

      if (head.x < 0 || head.x >= 22 || head.y < 0 || head.y >= 18) {
        gameOver = true;
        this.saveScore(score);
      }
      for (let s of snake) {
        if (s.x === head.x && s.y === head.y) {
          gameOver = true;
          this.saveScore(score);
        }
      }

      snake.unshift(head);
      if (head.x === food.x && head.y === food.y) {
        score += 30;
        food = { x: Math.floor(Math.random() * 20) + 1, y: Math.floor(Math.random() * 16) + 1 };
      } else {
        snake.pop();
      }

      ctx.fillStyle = '#060a16';
      ctx.fillRect(0, 0, cvs.width, cvs.height);

      let originX = 50, originY = 80;
      let outfit = this.customSettings.outfitColor || '#38bdf8';

      let fx = originX + food.x * grid;
      let fy = originY + food.y * grid;
      ctx.fillStyle = '#ef4444';
      ctx.fillRect(fx, fy - 6, grid - 2, grid - 2);

      snake.forEach((s, idx) => {
        let sx = originX + s.x * grid;
        let sy = originY + s.y * grid;

        ctx.fillStyle = idx === 0 ? outfit : '#0284c7';
        ctx.fillRect(sx, sy - 6, grid - 2, grid - 2);
      });

      this.drawHUD(ctx, cvs.width, timeLeft, score);

      if (gameOver) {
        clearInterval(moveInt);
        clearInterval(timerInt);
        ctx.fillStyle = 'rgba(2, 6, 23, 0.9)';
        ctx.fillRect(0, 0, cvs.width, cvs.height);
        ctx.fillStyle = '#ef4444';
        ctx.font = 'bold 32px sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText('VOXEL ARENA OVER', cvs.width / 2, cvs.height / 2);
      }
    }, 110);

    this.activeGame = {
      cleanup: () => {
        clearInterval(moveInt);
        clearInterval(timerInt);
        window.removeEventListener('keydown', onKey);
      }
    };
  },

  pingPong3D() {
    const cvs = this.canvas;
    const ctx = this.ctx;
    cvs.width = 640;
    cvs.height = 440;

    let timeLeft = 300;
    let score = 0;
    let padX = 320, padY = 220;
    let ball = { x: 320, y: 220, z: 0, vx: 3.5, vy: 2.5, vz: 4 };
    let gameOver = false;

    let timerInt = setInterval(() => {
      if (timeLeft > 0) timeLeft--;
      else {
        gameOver = true;
        this.saveScore(score);
        clearInterval(timerInt);
      }
    }, 1000);

    const onMove = e => {
      const rect = cvs.getBoundingClientRect();
      const scaleX = cvs.width / rect.width;
      const scaleY = cvs.height / rect.height;
      padX = (e.clientX - rect.left) * scaleX;
      padY = (e.clientY - rect.top) * scaleY;
    };
    cvs.addEventListener('mousemove', onMove);

    this.activeGame = {
      cleanup: () => {
        clearInterval(timerInt);
        cvs.removeEventListener('mousemove', onMove);
      }
    };

    const loop = () => {
      ball.x += ball.vx;
      ball.y += ball.vy;
      ball.z += ball.vz;

      if (ball.z > 300) ball.vz = -Math.abs(ball.vz);

      if (ball.z <= 0) {
        if (Math.hypot(ball.x - padX, ball.y - padY) < 65) {
          ball.vz = Math.abs(ball.vz) + 0.3;
          score += 25;
        } else {
          score = Math.max(0, score - 20);
          ball.vz = 4;
          ball.z = 250;
        }
      }

      if (ball.x < 120 || ball.x > cvs.width - 120) ball.vx *= -1;
      if (ball.y < 80 || ball.y > cvs.height - 60) ball.vy *= -1;

      ctx.fillStyle = '#050816';
      ctx.fillRect(0, 0, cvs.width, cvs.height);

      let scale = 1 - (ball.z / 350) * 0.7;
      let bR = Math.max(4, 18 * scale);
      ctx.fillStyle = '#ec4899';
      ctx.beginPath();
      ctx.arc(ball.x, ball.y, bR, 0, Math.PI * 2);
      ctx.fill();

      ctx.fillStyle = 'rgba(56, 189, 248, 0.25)';
      ctx.fillRect(padX - 45, padY - 30, 90, 60);
      ctx.strokeStyle = this.customSettings.outfitColor || '#38bdf8';
      ctx.lineWidth = 2;
      ctx.strokeRect(padX - 45, padY - 30, 90, 60);

      this.drawHUD(ctx, cvs.width, timeLeft, score);

      if (gameOver) {
        ctx.fillStyle = 'rgba(2, 6, 23, 0.9)';
        ctx.fillRect(0, 0, cvs.width, cvs.height);
        ctx.fillStyle = '#10b981';
        ctx.font = 'bold 36px sans-serif';
        ctx.fillText('TUNNEL SESSION COMPLETE', cvs.width / 2, cvs.height / 2);
        return;
      }

      this.loopId = requestAnimationFrame(loop);
    };
    loop();
  },

  memory3D() {
    const cvs = this.canvas;
    const ctx = this.ctx;
    cvs.width = 520;
    cvs.height = 480;

    let timeLeft = 300;
    let score = 0;
    let gameOver = false;

    let timerInt = setInterval(() => {
      if (timeLeft > 0) timeLeft--;
      else {
        gameOver = true;
        this.saveScore(score);
        clearInterval(timerInt);
      }
    }, 1000);

    const symbols = ['⭐', '⚡', '🚀', '🎮', '💎', '🔥', '🤖', '👑'];
    let deck = [...symbols, ...symbols].sort(() => Math.random() - 0.5).map((s, id) => ({
      id, symbol: s, revealed: false, matched: false
    }));
    let selected = [];

    const onClick = e => {
      if (gameOver) return;
      const rect = cvs.getBoundingClientRect();
      const scaleX = cvs.width / rect.width;
      const scaleY = cvs.height / rect.height;
      let mx = (e.clientX - rect.left) * scaleX;
      let my = (e.clientY - rect.top) * scaleY;

      deck.forEach((c, i) => {
        let col = i % 4;
        let row = Math.floor(i / 4);
        let x = 20 + col * 120;
        let y = 65 + row * 95;

        if (mx > x && mx < x + 105 && my > y && my < y + 85 && !c.revealed && selected.length < 2) {
          c.revealed = true;
          selected.push(c);
          if (selected.length === 2) {
            if (selected[0].symbol === selected[1].symbol) {
              selected[0].matched = true;
              selected[1].matched = true;
              score += 60;
              selected = [];
            } else {
              setTimeout(() => {
                selected.forEach(card => card.revealed = false);
                selected = [];
              }, 600);
            }
          }
        }
      });
    };
    cvs.addEventListener('click', onClick);

    this.activeGame = {
      cleanup: () => {
        clearInterval(timerInt);
        cvs.removeEventListener('click', onClick);
      }
    };

    const loop = () => {
      ctx.fillStyle = '#060a17';
      ctx.fillRect(0, 0, cvs.width, cvs.height);

      this.drawHUD(ctx, cvs.width, timeLeft, score);

      deck.forEach((c, i) => {
        let col = i % 4;
        let row = Math.floor(i / 4);
        let x = 20 + col * 120;
        let y = 65 + row * 95;

        ctx.fillStyle = c.revealed || c.matched ? 'rgba(56, 189, 248, 0.3)' : 'rgba(30, 41, 59, 0.6)';
        ctx.fillRect(x, y - 4, 105, 85);

        ctx.strokeStyle = c.matched ? '#10b981' : (this.customSettings.outfitColor || '#38bdf8');
        ctx.lineWidth = 1.5;
        ctx.strokeRect(x, y - 4, 105, 85);

        if (c.revealed || c.matched) {
          ctx.font = '38px sans-serif';
          ctx.textAlign = 'center';
          ctx.fillText(c.symbol, x + 52, y + 54);
        }
      });

      this.loopId = requestAnimationFrame(loop);
    };
    loop();
  },

  platformer3D() {
    const cvs = this.canvas;
    const ctx = this.ctx;
    cvs.width = 640;
    cvs.height = 420;

    let timeLeft = 300;
    let score = 0;
    let p = { x: 60, y: 280, w: 26, h: 36, vx: 0, vy: 0, grounded: false };
    let platforms = [
      { x: 0, y: 360, w: 640, h: 60 },
      { x: 130, y: 280, w: 110, h: 22 },
      { x: 300, y: 210, w: 130, h: 22 },
      { x: 480, y: 150, w: 120, h: 22 }
    ];
    let stars = [{ x: 180, y: 235 }, { x: 360, y: 165 }, { x: 535, y: 105 }];
    let keys = {};

    let timerInt = setInterval(() => {
      if (timeLeft > 0) timeLeft--;
      else {
        this.saveScore(score);
        clearInterval(timerInt);
      }
    }, 1000);

    const onKeyD = e => keys[e.key.toLowerCase()] = true;
    const onKeyU = e => keys[e.key.toLowerCase()] = false;
    window.addEventListener('keydown', onKeyD);
    window.addEventListener('keyup', onKeyU);

    this.activeGame = {
      cleanup: () => {
        clearInterval(timerInt);
        window.removeEventListener('keydown', onKeyD);
        window.removeEventListener('keyup', onKeyU);
      }
    };

    const loop = () => {
      let isArrows = this.customSettings.controls === 'arrows';
      if ((!isArrows && keys['a']) || keys['arrowleft']) p.vx = -3.8;
      else if ((!isArrows && keys['d']) || keys['arrowright']) p.vx = 3.8;
      else p.vx = 0;

      if ((keys['w'] || (isArrows && keys['arrowup']) || keys[' ']) && p.grounded) {
        p.vy = -11;
        p.grounded = false;
      }

      p.vy += 0.55;
      p.x += p.vx;
      p.y += p.vy;

      p.grounded = false;
      platforms.forEach(plat => {
        if (
          p.x + p.w > plat.x &&
          p.x < plat.x + plat.w &&
          p.y + p.h > plat.y &&
          p.y + p.h < plat.y + plat.h + 12 &&
          p.vy > 0
        ) {
          p.y = plat.y - p.h;
          p.vy = 0;
          p.grounded = true;
        }
      });

      stars.forEach((st, i) => {
        if (Math.hypot(p.x - st.x, p.y - st.y) < 26) {
          score += 100;
          stars.splice(i, 1);
          setTimeout(() => {
            stars.push({ x: Math.random() * 450 + 60, y: Math.random() * 180 + 100 });
          }, 2000);
        }
      });

      ctx.fillStyle = '#060a16';
      ctx.fillRect(0, 0, cvs.width, cvs.height);

      platforms.forEach(plat => {
        ctx.fillStyle = '#1e293b';
        ctx.fillRect(plat.x, plat.y, plat.w, plat.h);
        ctx.fillStyle = this.customSettings.outfitColor || '#38bdf8';
        ctx.fillRect(plat.x, plat.y, plat.w, 4);
      });

      stars.forEach(st => {
        ctx.fillStyle = '#facc15';
        ctx.beginPath();
        ctx.arc(st.x, st.y, 8, 0, Math.PI * 2);
        ctx.fill();
      });

      ctx.fillStyle = this.customSettings.outfitColor || '#38bdf8';
      ctx.fillRect(p.x, p.y, p.w, p.h);

      this.drawHUD(ctx, cvs.width, timeLeft, score);

      this.loopId = requestAnimationFrame(loop);
    };
    loop();
  },

  reactionSpeed3D() {
    const cvs = this.canvas;
    const ctx = this.ctx;
    cvs.width = 540;
    cvs.height = 380;

    let timeLeft = 300;
    let score = 0;
    let state = 'waiting';
    let startTime = 0;
    let result = 0;
    let timeoutId = null;
    let angle = 0;

    let timerInt = setInterval(() => {
      if (timeLeft > 0) timeLeft--;
      else {
        this.saveScore(score);
        clearInterval(timerInt);
      }
    }, 1000);

    const startWait = () => {
      state = 'ready';
      timeoutId = setTimeout(() => {
        state = 'click';
        startTime = Date.now();
      }, Math.random() * 2500 + 1500);
    };

    const onClick = () => {
      if (state === 'waiting') {
        startWait();
      } else if (state === 'ready') {
        clearTimeout(timeoutId);
        state = 'too-early';
      } else if (state === 'click') {
        result = Date.now() - startTime;
        let points = Math.max(10, 1000 - result);
        score += points;
        state = 'done';
      } else {
        startWait();
      }
    };
    cvs.addEventListener('click', onClick);

    this.activeGame = {
      cleanup: () => {
        clearInterval(timerInt);
        clearTimeout(timeoutId);
        cvs.removeEventListener('click', onClick);
      }
    };

    const loop = () => {
      angle += 0.03;

      ctx.fillStyle = '#050816';
      ctx.fillRect(0, 0, cvs.width, cvs.height);

      let cx = cvs.width / 2;
      let cy = cvs.height / 2 + 10;

      ctx.strokeStyle = state === 'click' ? '#10b981' : (state === 'ready' ? '#ef4444' : (this.customSettings.outfitColor || '#38bdf8'));
      ctx.lineWidth = 3;

      ctx.beginPath();
      ctx.ellipse(cx, cy, 90, 45, angle, 0, Math.PI * 2);
      ctx.stroke();

      let coreGrad = ctx.createRadialGradient(cx, cy, 5, cx, cy, 45);
      coreGrad.addColorStop(0, '#ffffff');
      coreGrad.addColorStop(1, state === 'click' ? '#10b981' : (state === 'ready' ? '#ef4444' : (this.customSettings.outfitColor || '#38bdf8')));
      ctx.fillStyle = coreGrad;
      ctx.beginPath();
      ctx.arc(cx, cy, 35, 0, Math.PI * 2);
      ctx.fill();

      ctx.fillStyle = '#fff';
      ctx.font = 'bold 22px sans-serif';
      ctx.textAlign = 'center';

      if (state === 'waiting') ctx.fillText('Click Core to Engage', cx, cy - 70);
      else if (state === 'ready') ctx.fillText('Charging... Wait for green!', cx, cy - 70);
      else if (state === 'click') ctx.fillText('DISCHARGE NOW!', cx, cy - 70);
      else if (state === 'too-early') ctx.fillText('Overload! Too early, click to retry', cx, cy - 70);
      else ctx.fillText(`${result} ms (+${Math.max(10, 1000 - result)} pts)!`, cx, cy - 70);

      this.drawHUD(ctx, cvs.width, timeLeft, score);

      this.loopId = requestAnimationFrame(loop);
    };
    loop();
  },

  puzzle3D() {
    const cvs = this.canvas;
    const ctx = this.ctx;
    cvs.width = 440;
    cvs.height = 460;
    const size = 4;
    const tileW = 100;

    let timeLeft = 300;
    let score = 0;
    let tiles = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 0].sort(() => Math.random() - 0.5);

    let timerInt = setInterval(() => {
      if (timeLeft > 0) timeLeft--;
      else {
        this.saveScore(score);
        clearInterval(timerInt);
      }
    }, 1000);

    const onClick = e => {
      const rect = cvs.getBoundingClientRect();
      const scaleX = cvs.width / rect.width;
      const scaleY = cvs.height / rect.height;
      let mx = (e.clientX - rect.left) * scaleX - 20;
      let my = (e.clientY - rect.top) * scaleY - 50;

      let col = Math.floor(mx / tileW);
      let row = Math.floor(my / tileW);
      let idx = row * size + col;
      let zeroIdx = tiles.indexOf(0);
      let zCol = zeroIdx % size;
      let zRow = Math.floor(zeroIdx / size);

      if ((Math.abs(col - zCol) === 1 && row === zRow) || (Math.abs(row - zRow) === 1 && col === zCol)) {
        tiles[zeroIdx] = tiles[idx];
        tiles[idx] = 0;
        score += 15;
      }
    };
    cvs.addEventListener('click', onClick);

    this.activeGame = {
      cleanup: () => {
        clearInterval(timerInt);
        cvs.removeEventListener('click', onClick);
      }
    };

    const loop = () => {
      ctx.fillStyle = '#060a16';
      ctx.fillRect(0, 0, cvs.width, cvs.height);

      this.drawHUD(ctx, cvs.width, timeLeft, score);

      tiles.forEach((val, i) => {
        if (val === 0) return;
        let col = i % size;
        let row = Math.floor(i / size);
        let x = 20 + col * tileW;
        let y = 55 + row * tileW;

        ctx.fillStyle = 'rgba(30, 41, 59, 0.9)';
        ctx.fillRect(x + 2, y + 2, tileW - 4, tileW - 10);

        ctx.strokeStyle = this.customSettings.outfitColor || '#38bdf8';
        ctx.lineWidth = 1.5;
        ctx.strokeRect(x + 2, y + 2, tileW - 4, tileW - 10);

        ctx.fillStyle = '#f8fafc';
        ctx.font = 'bold 30px sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText(val, x + tileW / 2, y + tileW / 2 + 5);
      });

      this.loopId = requestAnimationFrame(loop);
    };
    loop();
  },

  ticTacToe3D() {
    const cvs = this.canvas;
    const ctx = this.ctx;
    cvs.width = 400;
    cvs.height = 420;
    const cell = 120;

    let timeLeft = 300;
    let score = 0;
    let board = ['', '', '', '', '', '', '', '', ''];
    let turn = 'X';
    let winner = null;

    let timerInt = setInterval(() => {
      if (timeLeft > 0) timeLeft--;
      else {
        this.saveScore(score);
        clearInterval(timerInt);
      }
    }, 1000);

    const checkWin = () => {
      const lines = [
        [0, 1, 2], [3, 4, 5], [6, 7, 8],
        [0, 3, 6], [1, 4, 7], [2, 5, 8],
        [0, 4, 8], [2, 4, 6]
      ];
      for (let [a, b, c] of lines) {
        if (board[a] && board[a] === board[b] && board[a] === board[c]) {
          return board[a];
        }
      }
      return board.includes('') ? null : 'Draw';
    };

    const onClick = e => {
      if (winner) {
        board = ['', '', '', '', '', '', '', '', ''];
        turn = 'X';
        winner = null;
        return;
      }

      const rect = cvs.getBoundingClientRect();
      const scale = cvs.width / rect.width;
      let mx = (e.clientX - rect.left) * scale - 20;
      let my = (e.clientY - rect.top) * scale - 50;

      let col = Math.floor(mx / cell);
      let row = Math.floor(my / cell);
      let idx = row * 3 + col;

      if (!board[idx] && idx >= 0 && idx < 9) {
        board[idx] = turn;
        winner = checkWin();
        if (winner === 'X') score += 100;
        if (!winner) {
          turn = turn === 'X' ? 'O' : 'X';
          if (turn === 'O') {
            let empty = board.map((v, i) => v === '' ? i : null).filter(v => v !== null);
            if (empty.length > 0) {
              let aiChoice = empty[Math.floor(Math.random() * empty.length)];
              board[aiChoice] = 'O';
              winner = checkWin();
              if (winner === 'O') score = Math.max(0, score - 30);
              turn = 'X';
            }
          }
        }
      }
    };
    cvs.addEventListener('click', onClick);

    this.activeGame = {
      cleanup: () => {
        clearInterval(timerInt);
        cvs.removeEventListener('click', onClick);
      }
    };

    const loop = () => {
      ctx.fillStyle = '#060a16';
      ctx.fillRect(0, 0, cvs.width, cvs.height);

      this.drawHUD(ctx, cvs.width, timeLeft, score);

      ctx.strokeStyle = 'rgba(56, 189, 248, 0.3)';
      ctx.lineWidth = 3;
      for (let i = 1; i < 3; i++) {
        ctx.beginPath(); ctx.moveTo(20 + i * cell, 50); ctx.lineTo(20 + i * cell, 50 + 3 * cell); ctx.stroke();
        ctx.beginPath(); ctx.moveTo(20, 50 + i * cell); ctx.lineTo(20 + 3 * cell, 50 + i * cell); ctx.stroke();
      }

      board.forEach((val, i) => {
        if (!val) return;
        let col = i % 3;
        let row = Math.floor(i / 3);
        let cx = 20 + col * cell + cell / 2;
        let cy = 50 + row * cell + cell / 2;

        ctx.font = 'bold 56px sans-serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillStyle = val === 'X' ? (this.customSettings.outfitColor || '#38bdf8') : '#ec4899';
        ctx.fillText(val, cx, cy);
      });

      if (winner) {
        ctx.fillStyle = 'rgba(2, 6, 23, 0.88)';
        ctx.fillRect(0, 0, cvs.width, cvs.height);
        ctx.fillStyle = winner === 'Draw' ? '#f59e0b' : '#10b981';
        ctx.font = 'bold 34px sans-serif';
        ctx.fillText(winner === 'Draw' ? 'DRAW!' : `${winner} WINS!`, cvs.width / 2, cvs.height / 2);
        ctx.font = '16px sans-serif';
        ctx.fillStyle = '#fff';
        ctx.fillText('Click to play again', cvs.width / 2, cvs.height / 2 + 40);
      }

      this.loopId = requestAnimationFrame(loop);
    };
    loop();
  }
};
