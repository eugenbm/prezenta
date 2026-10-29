<?php /** @var array $user @var string $type @var ?array $contract @var string $fullName */ ?>
<div class="row justify-content-center">
  <div class="col-lg-9">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
      <h1 class="h3 mb-0">Contract de voluntariat</h1>
      <?= role_badge($type) ?>
    </div>

    <?php if ($contract): ?>
      <div class="alert alert-success d-flex align-items-start gap-2" role="alert">
        <i class="bi bi-check-circle-fill fs-5"></i>
        <div>
          <strong>Contract semnat.</strong>
          Ați semnat contractul pe <?= e(date('d.m.Y H:i', strtotime($contract['signed_at']))) ?>.
          Îl puteți descărca mai jos sau îl puteți semna din nou dacă este nevoie.
        </div>
      </div>
      <div class="mb-4">
        <a href="<?= route_url('?route=applicant/contract/download') ?>" download
           class="btn btn-primary js-download" data-filename="contract-voluntariat.pdf">
          <i class="bi bi-download me-1"></i>Descarcă contractul (PDF)
        </a>
      </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-body">
        <p class="text-muted mb-4">
          Contractul se completează automat cu numele dumneavoastră la punctul 1.2
          (<strong><?= e($fullName) ?></strong>) și cu semnătura pe care o desenați mai jos
          cu degetul (pe telefon/tabletă) sau cu mouse-ul. Restul contractului rămâne neschimbat.
        </p>

        <div class="mb-4">
          <a href="<?= route_url('?route=applicant/contract/preview') ?>" target="_blank" rel="noopener"
             class="btn btn-outline-primary">
            <i class="bi bi-file-earmark-text me-1"></i>Citește contractul înainte de a semna
          </a>
        </div>

        <form method="post" action="<?= route_url('?route=applicant/contract') ?>" id="contractForm">
          <?= csrf_field() ?>
          <input type="hidden" name="signature" id="signatureInput">

          <label class="form-label fw-semibold">Semnătura dumneavoastră</label>
          <div class="signature-wrap">
            <canvas id="signaturePad" class="signature-pad"></canvas>
            <span class="signature-hint" id="signatureHint">Semnați aici</span>
          </div>

          <div class="d-flex gap-2 mt-3 flex-wrap">
            <button type="button" class="btn btn-outline-secondary" id="clearSignature">
              <i class="bi bi-eraser me-1"></i>Șterge
            </button>
            <button type="submit" class="btn btn-success" id="submitSignature" disabled>
              <i class="bi bi-pencil-square me-1"></i><?= $contract ? 'Semnează din nou' : 'Semnează contractul' ?>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<style>
  .signature-wrap { position: relative; width: 100%; max-width: 520px; }
  .signature-pad {
    width: 100%; aspect-ratio: 155 / 58; display: block;
    border: 2px dashed #b9a99a; border-radius: 10px; background: #fff;
    touch-action: none; cursor: crosshair;
  }
  .signature-hint {
    position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
    color: #c3b4a6; font-style: italic; pointer-events: none; user-select: none;
  }
</style>

<script>
(function () {
  var canvas = document.getElementById('signaturePad');
  var hint = document.getElementById('signatureHint');
  var input = document.getElementById('signatureInput');
  var submitBtn = document.getElementById('submitSignature');
  var clearBtn = document.getElementById('clearSignature');
  var form = document.getElementById('contractForm');
  var ctx = canvas.getContext('2d');
  var drawing = false, hasDrawn = false, last = null;

  function fillWhite() {
    ctx.save();
    ctx.setTransform(1, 0, 0, 1, 0, 0);
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    ctx.restore();
  }

  function resize() {
    // Potrivește rezoluția internă cu dimensiunea afișată (pentru linii clare),
    // apoi reumple fundalul alb (JPEG nu are transparență).
    var ratio = Math.max(window.devicePixelRatio || 1, 1);
    var rect = canvas.getBoundingClientRect();
    canvas.width = Math.round(rect.width * ratio);
    canvas.height = Math.round(rect.height * ratio);
    ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
    ctx.lineJoin = 'round';
    ctx.lineCap = 'round';
    ctx.lineWidth = 2.5;
    ctx.strokeStyle = '#13235b';
    fillWhite();
    hasDrawn = false;
    updateState();
  }

  function updateState() {
    submitBtn.disabled = !hasDrawn;
    hint.style.display = hasDrawn ? 'none' : '';
  }

  function pos(e) {
    var rect = canvas.getBoundingClientRect();
    var p = e.touches ? e.touches[0] : e;
    return { x: p.clientX - rect.left, y: p.clientY - rect.top };
  }

  function start(e) { e.preventDefault(); drawing = true; last = pos(e); }
  function move(e) {
    if (!drawing) return;
    e.preventDefault();
    var p = pos(e);
    ctx.beginPath();
    ctx.moveTo(last.x, last.y);
    ctx.lineTo(p.x, p.y);
    ctx.stroke();
    last = p;
    if (!hasDrawn) { hasDrawn = true; updateState(); }
  }
  function end() { drawing = false; }

  canvas.addEventListener('pointerdown', start);
  canvas.addEventListener('pointermove', move);
  window.addEventListener('pointerup', end);

  clearBtn.addEventListener('click', function () { fillWhite(); hasDrawn = false; updateState(); });

  form.addEventListener('submit', function (e) {
    if (!hasDrawn) { e.preventDefault(); return; }
    input.value = canvas.toDataURL('image/jpeg', 0.92);
  });

  window.addEventListener('resize', function () {
    // Re-dimensionează fără a păstra desenul (schimbarea rezoluției l-ar distorsiona).
    if (!hasDrawn) resize();
  });
  resize();

  // Descărcare ca fișier (fetch -> blob), consecvent inclusiv pe iOS Safari.
  function downloadPdf(url, filename) {
    if (!window.fetch || !window.URL || !window.URL.createObjectURL) { window.location = url; return; }
    fetch(url, { credentials: 'same-origin' })
      .then(function (r) { if (!r.ok) { throw new Error('HTTP ' + r.status); } return r.blob(); })
      .then(function (blob) {
        var objUrl = window.URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = objUrl; a.download = filename || 'contract.pdf';
        document.body.appendChild(a); a.click(); a.remove();
        setTimeout(function () { window.URL.revokeObjectURL(objUrl); }, 4000);
      })
      .catch(function () { window.location = url; });
  }
  document.querySelectorAll('.js-download').forEach(function (el) {
    el.addEventListener('click', function (e) {
      e.preventDefault();
      downloadPdf(el.getAttribute('href'), el.getAttribute('data-filename'));
    });
  });
})();
</script>
