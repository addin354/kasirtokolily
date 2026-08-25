@props([
    'inputId' => 'barcode',
])

<div class="d-flex flex-wrap gap-2 align-items-center mt-2">
    <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-barcode-camera-{{ $inputId }}" aria-expanded="false">
        Scan dengan kamera
    </button>
</div>
<div
    id="barcode-qr-reader-{{ $inputId }}"
    class="mt-2 rounded border bg-dark overflow-hidden"
    style="display: none; max-width: 400px;"
></div>

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
(function () {
    var inputId = @json($inputId);
    var input = document.getElementById(inputId);
    var btn = document.getElementById('btn-barcode-camera-' + inputId);
    var readerEl = document.getElementById('barcode-qr-reader-' + inputId);
    if (!input || !btn || !readerEl) return;

    var html5QrCode = null;
    var scanning = false;

    function stopCamera() {
        if (!html5QrCode || !scanning) return;
        scanning = false;
        html5QrCode.stop().catch(function () {});
        html5QrCode.clear().catch(function () {});
        readerEl.style.display = 'none';
        btn.setAttribute('aria-expanded', 'false');
        btn.textContent = 'Scan dengan kamera';
    }

    btn.addEventListener('click', function () {
        if (scanning) {
            stopCamera();
            return;
        }
        if (typeof Html5Qrcode === 'undefined') {
            alert('Library kamera tidak dimuat. Periksa koneksi internet.');
            return;
        }
        readerEl.style.display = 'block';
        btn.setAttribute('aria-expanded', 'true');
        btn.textContent = 'Matikan kamera';

        if (!readerEl.querySelector('.scanner-laser-overlay')) {
            var laserLine = document.createElement('div');
            laserLine.className = 'scanner-laser-overlay';
            readerEl.appendChild(laserLine);
        }

        html5QrCode = new Html5Qrcode(readerEl.id);
        scanning = true;
        html5QrCode.start(
            { facingMode: 'environment' },
            {
                fps: 15,
                qrbox: function(w, h) {
                    var minEdge = Math.min(w, h);
                    return {
                        width: Math.floor(minEdge * 0.85),
                        height: Math.floor(minEdge * 0.50)
                    };
                },
                videoConstraints: {
                    facingMode: { ideal: 'environment' },
                    focusMode: { ideal: 'continuous' }
                }
            },
            function (decodedText) {
                input.value = decodedText;
                html5QrCode.stop().then(function () {
                    scanning = false;
                    readerEl.style.display = 'none';
                    btn.setAttribute('aria-expanded', 'false');
                    btn.textContent = 'Scan dengan kamera';
                    input.focus();
                }).catch(function () {
                    scanning = false;
                    readerEl.style.display = 'none';
                    btn.textContent = 'Scan dengan kamera';
                    input.focus();
                });
            },
            function () {}
        ).catch(function (err) {
            scanning = false;
            readerEl.style.display = 'none';
            btn.setAttribute('aria-expanded', 'false');
            btn.textContent = 'Scan dengan kamera';
            alert('Tidak bisa membuka kamera: ' + (err && err.message ? err.message : String(err)));
        });
    });
})();
</script>
@endpush
