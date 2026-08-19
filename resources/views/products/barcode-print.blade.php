<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stiker Barcode - {{ $product->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; background: #f5f5f5; }
        .stiker { background: white; border: 2px dashed #ccc; border-radius: 12px; padding: 20px 24px; text-align: center; width: 320px; }
        .instansi { font-size: 10px; color: #666; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px; }
        .nama-barang { font-size: 14px; font-weight: bold; color: #111; margin-bottom: 12px; }
        .barcode-wrap { margin: 8px 0; }
        .kode-text { font-size: 11px; font-family: monospace; color: #333; margin-top: 6px; letter-spacing: 2px; }
        .barcode-wrap svg { max-width: 100%; height: auto; display: block; margin: 0 auto; }
        .btn-print { margin-top: 16px; padding: 8px 24px; background: #111; color: white; border: none; border-radius: 20px; cursor: pointer; font-size: 13px; }
        @media print {
            body { background: white; }
            .btn-print { display: none; }
            .stiker { border: 1px solid #000; }
        }
    </style>
</head>
<body>
    <div class="stiker">
        <div class="instansi">Diskominfo Kab. Garut</div>
        <div class="nama-barang">{{ $product->name }}</div>
        <div class="barcode-wrap">
            <svg id="barcode-stiker"></svg>
        </div>
        <div class="kode-text">{{ $product->barcode }}</div>
        <button class="btn-print" onclick="window.print()">🖨️ Cetak Stiker</button>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <script>
        JsBarcode("#barcode-stiker", "{{ $product->barcode }}", {
            format: "CODE128", width: 1.6, height: 80, displayValue: false
        });
    </script>
</body>
</html>
