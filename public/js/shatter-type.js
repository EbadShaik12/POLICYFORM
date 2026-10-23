/**
 * Shatter Type — Interactive Crystalline Glass Headline Component
 * Inspired by React Bits Pro: A headline cut into glass shards that assemble,
 * crack under cursor, and shatter on click with realistic physics and sound.
 */

function initShatterType() {
  const wrapper = document.getElementById('heroShatterWrapper');
  const textElement = document.getElementById('shatterHeadline');
  const canvas = document.getElementById('shatterCanvas');
  const crackSvg = document.getElementById('shatterCrackSvg');
  const hintPill = document.getElementById('shatterHintPill');

  if (!wrapper || !textElement || !canvas) return;

  const ctx = canvas.getContext('2d');
  let isShattered = false;
  let isAssembling = false;
  let shards = [];
  let animFrameId = null;
  let audioCtx = null;
  let dpr = window.devicePixelRatio || 1;
  let currentWidth = 0;
  let currentHeight = 0;

  // Synthesize crystalline glass shatter sound via Web Audio API
  function playGlassSound() {
    try {
      const AudioCtxClass = window.AudioContext || window.webkitAudioContext;
      if (!AudioCtxClass) return;
      if (!audioCtx) audioCtx = new AudioCtxClass();
      if (audioCtx.state === 'suspended') audioCtx.resume();

      const now = audioCtx.currentTime;

      // High crystalline resonant ping
      const osc1 = audioCtx.createOscillator();
      const gain1 = audioCtx.createGain();
      const filter1 = audioCtx.createBiquadFilter();

      filter1.type = 'highpass';
      filter1.frequency.setValueAtTime(2200, now);

      osc1.type = 'triangle';
      osc1.frequency.setValueAtTime(3200, now);
      osc1.frequency.exponentialRampToValueAtTime(700, now + 0.35);

      gain1.gain.setValueAtTime(0.18, now);
      gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.38);

      osc1.connect(filter1);
      filter1.connect(gain1);
      gain1.connect(audioCtx.destination);

      osc1.start(now);
      osc1.stop(now + 0.4);

      // Low crack sound
      const osc2 = audioCtx.createOscillator();
      const gain2 = audioCtx.createGain();
      osc2.type = 'sawtooth';
      osc2.frequency.setValueAtTime(600, now);
      osc2.frequency.exponentialRampToValueAtTime(80, now + 0.15);

      gain2.gain.setValueAtTime(0.12, now);
      gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.18);

      osc2.connect(gain2);
      gain2.connect(audioCtx.destination);

      osc2.start(now);
      osc2.stop(now + 0.2);
    } catch (e) {
      // Audio autoplay gracefully handled if user hasn't interacted yet
    }
  }

  // Resize canvas and SVG to match container bounds precisely
  function syncDimensions() {
    const rect = wrapper.getBoundingClientRect();
    if (!rect.width || !rect.height) return;

    dpr = window.devicePixelRatio || 1;
    currentWidth = rect.width;
    currentHeight = rect.height;

    canvas.width = Math.round(rect.width * dpr);
    canvas.height = Math.round(rect.height * dpr);
    canvas.style.width = `${rect.width}px`;
    canvas.style.height = `${rect.height}px`;

    if (crackSvg) {
      crackSvg.setAttribute('viewBox', `0 0 ${Math.round(rect.width)} ${Math.round(rect.height)}`);
    }
  }

  window.addEventListener('resize', syncDimensions);
  syncDimensions();
  if (document.fonts && document.fonts.ready) {
    document.fonts.ready.then(syncDimensions);
  }

  // 1. Crack under Cursor Interaction
  wrapper.addEventListener('mousemove', (e) => {
    if (isShattered || isAssembling) return;

    const rect = wrapper.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;

    drawGlassCracks(x, y, rect.width, rect.height);
    textElement.classList.add('is-cracked');
  });

  wrapper.addEventListener('mouseleave', () => {
    if (!isShattered && crackSvg) {
      crackSvg.classList.remove('active');
      textElement.classList.remove('is-cracked');
    }
  });

  function drawGlassCracks(originX, originY, width, height) {
    if (!crackSvg) return;

    // Generate crystalline branching cracks from cursor origin
    const rays = 8;
    let paths = '';
    const maxRadius = Math.min(width, height) * 0.55;

    for (let i = 0; i < rays; i++) {
      const angle = (i * (Math.PI * 2) / rays) + (Math.sin(i * 1.8) * 0.25);
      let currX = originX;
      let currY = originY;

      let d = `M ${currX.toFixed(1)} ${currY.toFixed(1)}`;
      const segments = 3;
      const stepLen = maxRadius / segments;

      for (let s = 1; s <= segments; s++) {
        const segAngle = angle + (Math.random() - 0.5) * 0.45;
        currX += Math.cos(segAngle) * (stepLen * (0.8 + Math.random() * 0.4));
        currY += Math.sin(segAngle) * (stepLen * (0.8 + Math.random() * 0.4));
        d += ` L ${currX.toFixed(1)} ${currY.toFixed(1)}`;

        // Secondary branching crack
        if (s === 2) {
          const branchAngle = segAngle + (Math.random() > 0.5 ? 0.7 : -0.7);
          const bx = currX + Math.cos(branchAngle) * (stepLen * 0.55);
          const by = currY + Math.sin(branchAngle) * (stepLen * 0.55);
          d += ` M ${currX.toFixed(1)} ${currY.toFixed(1)} L ${bx.toFixed(1)} ${by.toFixed(1)} M ${currX.toFixed(1)} ${currY.toFixed(1)}`;
        }
      }

      // Outer glow fracture + bright crystalline white core
      paths += `<path d="${d}" stroke="rgba(21, 94, 239, 0.75)" stroke-width="2" stroke-linecap="round" fill="none" />`;
      paths += `<path d="${d}" stroke="rgba(255, 255, 255, 0.95)" stroke-width="1" stroke-linecap="round" fill="none" />`;
    }

    crackSvg.innerHTML = paths;
    crackSvg.classList.add('active');
  }

  // Render multi-line wrapped text onto offscreen canvas accurately
  function renderTextSnapshot(width, height) {
    const textCanvas = document.createElement('canvas');
    textCanvas.width = width * dpr;
    textCanvas.height = height * dpr;
    const tCtx = textCanvas.getContext('2d');
    tCtx.scale(dpr, dpr);

    const style = window.getComputedStyle(textElement);
    tCtx.font = `${style.fontWeight} ${style.fontSize} ${style.fontFamily}`;
    tCtx.fillStyle = style.color || '#15345B';
    tCtx.textBaseline = 'top';

    const words = textElement.textContent.trim().split(/\s+/);
    const lineHeight = parseFloat(style.lineHeight) || 54;
    const paddingLeft = parseFloat(style.paddingLeft) || 0;
    const paddingTop = parseFloat(style.paddingTop) || 0;

    let line = '';
    let y = paddingTop;

    for (let i = 0; i < words.length; i++) {
      const testLine = line ? line + ' ' + words[i] : words[i];
      const metrics = tCtx.measureText(testLine);
      if (metrics.width > (width - paddingLeft - 10) && line !== '') {
        tCtx.fillText(line, paddingLeft, y);
        line = words[i];
        y += lineHeight;
      } else {
        line = testLine;
      }
    }
    if (line) {
      tCtx.fillText(line, paddingLeft, y);
    }

    return textCanvas;
  }

  // 2. Shatter on Click Interaction
  function handleShatterTrigger(e) {
    if (isShattered || isAssembling) return;

    syncDimensions();
    const rect = wrapper.getBoundingClientRect();
    const clickX = e && e.clientX ? (e.clientX - rect.left) : (rect.width / 2);
    const clickY = e && e.clientY ? (e.clientY - rect.top) : (rect.height / 2);

    shatterText(clickX, clickY, rect.width, rect.height);
  }

  wrapper.addEventListener('click', handleShatterTrigger);
  if (hintPill) {
    hintPill.addEventListener('click', (e) => {
      e.stopPropagation();
      handleShatterTrigger(e);
    });
  }

  function shatterText(originX, originY, width, height) {
    isShattered = true;
    playGlassSound();

    if (crackSvg) {
      crackSvg.classList.remove('active');
      crackSvg.innerHTML = '';
    }
    textElement.classList.add('is-hidden');

    const textCanvas = renderTextSnapshot(width, height);

    // Generate irregular triangular crystalline glass shards
    const numCols = 8;
    const numRows = 4;
    const cellW = width / numCols;
    const cellH = height / numRows;
    shards = [];

    for (let r = 0; r < numRows; r++) {
      for (let c = 0; c < numCols; c++) {
        const x0 = c * cellW;
        const y0 = r * cellH;

        const jitter = 7;
        const p1 = { x: x0 + (Math.random() - 0.5) * jitter, y: y0 + (Math.random() - 0.5) * jitter };
        const p2 = { x: x0 + cellW + (Math.random() - 0.5) * jitter, y: y0 + (Math.random() - 0.5) * jitter };
        const p3 = { x: x0 + cellW + (Math.random() - 0.5) * jitter, y: y0 + cellH + (Math.random() - 0.5) * jitter };
        const p4 = { x: x0 + (Math.random() - 0.5) * jitter, y: y0 + cellH + (Math.random() - 0.5) * jitter };

        const triangleA = [p1, p2, p4];
        const triangleB = [p2, p3, p4];

        [triangleA, triangleB].forEach(pts => {
          const centerX = (pts[0].x + pts[1].x + pts[2].x) / 3;
          const centerY = (pts[0].y + pts[1].y + pts[2].y) / 3;

          const dx = centerX - originX;
          const dy = centerY - originY;
          const dist = Math.max(15, Math.sqrt(dx * dx + dy * dy));

          const force = (1 - Math.min(1, dist / (width * 1.1))) * 18 + (Math.random() * 5);
          const angle = Math.atan2(dy, dx) + (Math.random() - 0.5) * 0.45;

          shards.push({
            origPts: pts.map(p => ({ x: p.x, y: p.y })),
            x: 0,
            y: 0,
            vx: Math.cos(angle) * force,
            vy: Math.sin(angle) * force - 3.5,
            rot: 0,
            vRot: (Math.random() - 0.5) * 0.22,
            centerX,
            centerY,
            textCanvas
          });
        });
      }
    }

    animateShatter(width, height);

    // Automatic magnetic re-assembly after 2.4s
    setTimeout(() => {
      assembleText(width, height);
    }, 2400);
  }

  function animateShatter(width, height) {
    if (animFrameId) cancelAnimationFrame(animFrameId);

    function frame() {
      ctx.save();
      ctx.setTransform(1, 0, 0, 1, 0, 0);
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      ctx.scale(dpr, dpr);

      for (let i = 0; i < shards.length; i++) {
        const s = shards[i];

        if (!isAssembling) {
          s.x += s.vx;
          s.y += s.vy;
          s.vy += 0.32; // gentle gravity
          s.vx *= 0.96; // air drag
          s.rot += s.vRot;
          s.vRot *= 0.98;
        }

        ctx.save();
        ctx.translate(s.centerX + s.x, s.centerY + s.y);
        ctx.rotate(s.rot);
        ctx.translate(-s.centerX, -s.centerY);

        // Clip to shard triangle
        ctx.beginPath();
        ctx.moveTo(s.origPts[0].x, s.origPts[0].y);
        ctx.lineTo(s.origPts[1].x, s.origPts[1].y);
        ctx.lineTo(s.origPts[2].x, s.origPts[2].y);
        ctx.closePath();

        ctx.save();
        ctx.clip();
        ctx.drawImage(s.textCanvas, 0, 0, width, height);

        // Glass crystalline tint
        ctx.fillStyle = 'rgba(21, 94, 239, 0.08)';
        ctx.fill();
        ctx.restore();

        // Glass specular edge
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.92)';
        ctx.lineWidth = 1.2;
        ctx.stroke();

        ctx.restore();
      }

      ctx.restore();

      if (isShattered || isAssembling) {
        animFrameId = requestAnimationFrame(frame);
      }
    }

    animFrameId = requestAnimationFrame(frame);
  }

  // 3. Magnetic Re-Assembly
  function assembleText(width, height) {
    isAssembling = true;
    const startTime = performance.now();
    const duration = 750;

    shards.forEach(s => {
      s.startX = s.x;
      s.startY = s.y;
      s.startRot = s.rot;
    });

    function assembleFrame(now) {
      const elapsed = now - startTime;
      const progress = Math.min(1, elapsed / duration);
      // Ease out cubic
      const ease = 1 - Math.pow(1 - progress, 3);

      ctx.save();
      ctx.setTransform(1, 0, 0, 1, 0, 0);
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      ctx.scale(dpr, dpr);

      for (let i = 0; i < shards.length; i++) {
        const s = shards[i];
        s.x = s.startX * (1 - ease);
        s.y = s.startY * (1 - ease);
        s.rot = s.startRot * (1 - ease);

        ctx.save();
        ctx.translate(s.centerX + s.x, s.centerY + s.y);
        ctx.rotate(s.rot);
        ctx.translate(-s.centerX, -s.centerY);

        ctx.beginPath();
        ctx.moveTo(s.origPts[0].x, s.origPts[0].y);
        ctx.lineTo(s.origPts[1].x, s.origPts[1].y);
        ctx.lineTo(s.origPts[2].x, s.origPts[2].y);
        ctx.closePath();

        ctx.save();
        ctx.clip();
        ctx.drawImage(s.textCanvas, 0, 0, width, height);
        ctx.restore();

        // Sparkling assembly edges
        ctx.strokeStyle = `rgba(255, 255, 255, ${0.95 * (1 - ease)})`;
        ctx.lineWidth = 1.2;
        ctx.stroke();

        ctx.restore();
      }

      ctx.restore();

      if (progress < 1) {
        animFrameId = requestAnimationFrame(assembleFrame);
      } else {
        if (animFrameId) cancelAnimationFrame(animFrameId);
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        textElement.classList.remove('is-hidden');
        textElement.classList.remove('is-cracked');
        isShattered = false;
        isAssembling = false;
      }
    }

    animFrameId = requestAnimationFrame(assembleFrame);
  }

  // Expose global manual trigger for testing / verification
  window.triggerShatter = () => handleShatterTrigger();
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initShatterType);
} else {
  initShatterType();
}
