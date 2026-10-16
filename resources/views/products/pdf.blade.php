<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Inventaris Barang — Diskominfo Kabupaten Garut</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-diskominfo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            color: #0f172a;
            background: #e2e8f0;
            padding: 24px 16px;
            font-size: 11px;
            line-height: 1.45;
        }
        .toolbar {
            max-width: 1040px;
            margin: 0 auto 16px auto;
            background: #0f1b38;
            color: #ffffff;
            padding: 12px 18px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.25);
        }
        .toolbar-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            font-size: 13px;
        }
        .toolbar-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .btn-primary { background: #2563eb; color: #ffffff; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-emerald { background: #059669; color: #ffffff; }
        .btn-emerald:hover { background: #047857; }
        .btn-ghost { background: rgba(255,255,255,0.12); color: #f8fafc; }
        .btn-ghost:hover { background: rgba(255,255,255,0.2); }

        .sheet {
            max-width: 1040px;
            margin: 0 auto;
            background: #ffffff;
            padding: 32px 36px;
            border-radius: 16px;
            box-shadow: 0 12px 32px rgba(15, 23, 42, 0.08);
        }
        .kop-surat {
            display: flex;
            align-items: center;
            gap: 18px;
            padding-bottom: 14px;
            border-bottom: 3px double #0f172a;
            margin-bottom: 18px;
        }
        .kop-logo {
            width: 68px;
            height: 68px;
            object-fit: contain;
            flex-shrink: 0;
        }
        .kop-text {
            flex: 1;
            text-align: center;
        }
        .kop-gov {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #334155;
        }
        .kop-dinas {
            font-size: 17px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: #0f172a;
            margin-top: 2px;
        }
        .kop-addr {
            font-size: 10px;
            color: #475569;
            margin-top: 3px;
        }

        .doc-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 14px;
            gap: 12px;
        }
        .doc-title {
            font-size: 14px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
        }
        .doc-sub {
            font-size: 10.5px;
            color: #475569;
            margin-top: 2px;
        }
        .doc-meta {
            text-align: right;
            font-size: 10px;
            color: #475569;
        }

        .kpi-strip {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 8px;
            margin-bottom: 16px;
        }
        .kpi-box {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 8px 10px;
            background: #f8fafc;
        }
        .kpi-label {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.04em;
        }
        .kpi-val {
            font-size: 15px;
            font-weight: 800;
            color: #0f172a;
            margin-top: 2px;
            font-variant-numeric: tabular-nums;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 22px;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 7px 8px;
            text-align: left;
            vertical-align: top;
        }
        thead th {
            background: #0f1b38;
            color: #ffffff;
            font-size: 9.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        tbody tr:nth-child(even) {
            background: #f8fafc;
        }
        .mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 10px;
            font-weight: 700;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge-active { background: #dcfce7; color: #166534; }
        .badge-low { background: #fef3c7; color: #92400e; }
        .badge-out { background: #ffe4e6; color: #9f1239; }
        .badge-inactive { background: #e2e8f0; color: #334155; }

        .signature-grid {
            display: flex;
            justify-content: space-between;
            margin-top: 24px;
            page-break-inside: avoid;
        }
        .sig-box {
            width: 240px;
            text-align: center;
            font-size: 10.5px;
        }
        .sig-space {
            height: 58px;
        }
        .sig-name {
            font-weight: 700;
            text-decoration: underline;
        }

        @media print {
            body { background: #ffffff; padding: 0; }
            .toolbar { display: none !important; }
            .sheet { box-shadow: none; padding: 10px 14px; max-width: 100%; border-radius: 0; }
            @page { size: A4 landscape; margin: 10mm; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <div class="toolbar-title">
            <i class="bi bi-file-earmark-pdf-fill" style="color: #60a5fa; font-size: 16px;"></i>
            <span>Pratinjau Laporan PDF Resmi — Inventaris Diskominfo Kabupaten Garut</span>
        </div>
        <div class="toolbar-actions">
            <a href="{{ route('products.index') }}" class="btn btn-ghost">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <button type="button" id="downloadPdfBtn" class="btn btn-emerald">
                <i class="bi bi-download"></i> Unduh File PDF
            </button>
            <button type="button" onclick="window.print()" class="btn btn-primary">
                <i class="bi bi-printer-fill"></i> Cetak / Simpan PDF
            </button>
        </div>
    </div>

    <div id="pdfReportContent" class="sheet">
        <div class="kop-surat">
            <img src="{{ asset('images/logo-diskominfo.png') }}" alt="Logo Diskominfo Garut" class="kop-logo">
            <div class="kop-text">
                <div class="kop-gov">Pemerintah Kabupaten Garut</div>
                <div class="kop-dinas">Dinas Komunikasi dan Informatika</div>
                <div class="kop-addr">Jl. Pembangunan No. 181, Sukagalih, Kec. Tarogong Kidul, Kabupaten Garut, Jawa Barat 44151</div>
            </div>
        </div>

        <div class="doc-header">
            <div>
                <div class="doc-title">Laporan Daftar Inventaris Barang & Aset TIK</div>
                <div class="doc-sub">
                    Filter Kategori: <strong>{{ $filterMeta['category'] ?? 'Semua Kategori' }}</strong> •
                    Lokasi: <strong>{{ $filterMeta['room'] ?? 'Semua Ruangan' }}</strong>
                    @if(!empty($filterMeta['search']))
                        • Kata Kunci: <strong>"{{ $filterMeta['search'] }}"</strong>
                    @endif
                </div>
            </div>
            <div class="doc-meta">
                <div>Tanggal Cetak: <strong>{{ now()->translatedFormat('d F Y, H:i') }} WIB</strong></div>
                <div>Dicetak Oleh: <strong>{{ auth()->user()?->name ?? 'Petugas Inventaris' }}</strong></div>
            </div>
        </div>

        <div class="kpi-strip">
            <div class="kpi-box">
                <div class="kpi-label">Total Jenis Barang</div>
                <div class="kpi-val">{{ number_format($summary['total_items'] ?? 0) }}</div>
            </div>
            <div class="kpi-box">
                <div class="kpi-label">Total Kuantitas Unit</div>
                <div class="kpi-val">{{ number_format($summary['total_units'] ?? 0) }} Unit</div>
            </div>
            <div class="kpi-box">
                <div class="kpi-label">Barang Aktif</div>
                <div class="kpi-val" style="color: #15803d;">{{ number_format($summary['active_count'] ?? 0) }}</div>
            </div>
            <div class="kpi-box">
                <div class="kpi-label">Stok Menipis (1-5)</div>
                <div class="kpi-val" style="color: #b45309;">{{ number_format($summary['low_stock_count'] ?? 0) }}</div>
            </div>
            <div class="kpi-box">
                <div class="kpi-label">Stok Habis (0)</div>
                <div class="kpi-val" style="color: #be123c;">{{ number_format($summary['out_of_stock_count'] ?? 0) }}</div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 34px; text-align: center;">No</th>
                    <th style="width: 110px;">Kode / Barcode</th>
                    <th>Nama Barang & Spesifikasi</th>
                    <th style="width: 140px;">Kategori</th>
                    <th style="width: 165px;">Lokasi Ruangan</th>
                    <th style="width: 65px; text-align: center;">Stok</th>
                    <th style="width: 95px; text-align: center;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $idx => $item)
                    <tr>
                        <td style="text-align: center; font-weight: 700;">{{ $idx + 1 }}</td>
                        <td>
                            <div class="mono">{{ $item->barcode ?: '-' }}</div>
                            @if($item->kode_barang && $item->kode_barang !== $item->barcode)
                                <div style="font-size: 9px; color: #64748b;">{{ $item->kode_barang }}</div>
                            @endif
                        </td>
                        <td>
                            <div style="font-weight: 700; color: #0f172a;">{{ $item->name }}</div>
                            @if($item->subcategory || $item->edition)
                                <div style="font-size: 9.5px; color: #334155;">
                                    {{ collect([$item->subcategory, $item->edition])->filter()->implode(' • ') }}
                                </div>
                            @endif
                        </td>
                        <td>{{ $item->category_name }}</td>
                        <td>{{ $item->room_name ?: 'Belum Ditentukan' }}</td>
                        <td style="text-align: center; font-weight: 800; font-variant-numeric: tabular-nums;">
                            {{ $item->stock ?? 0 }}
                        </td>
                        <td style="text-align: center;">
                            @if(($item->stock ?? 0) <= 0 || $item->status === 'out_of_stock')
                                <span class="badge badge-out">Stok Habis</span>
                            @elseif(($item->stock ?? 0) <= 5)
                                <span class="badge badge-low">Stok Menipis</span>
                            @elseif($item->status === 'inactive')
                                <span class="badge badge-inactive">Tidak Aktif</span>
                            @else
                                <span class="badge badge-active">Aktif</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 24px; color: #64748b;">
                            Tidak ada data barang yang sesuai dengan filter laporan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="signature-grid">
            <div class="sig-box">
                <div>Mengetahui,</div>
                <div>Kasubag Umum & Kepegawaian</div>
                <div class="sig-space"></div>
                <div class="sig-name">Pejabat Penanggung Jawab Aset</div>
                <div>Diskominfo Kabupaten Garut</div>
            </div>
            <div class="sig-box">
                <div>Garut, {{ now()->translatedFormat('d F Y') }}</div>
                <div>Petugas Pengelola Inventaris</div>
                <div class="sig-space"></div>
                <div class="sig-name">{{ auth()->user()?->name ?? 'Administrator Inventaris' }}</div>
                <div>Role: {{ strtoupper(auth()->user()?->role ?? 'PETUGAS') }}</div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('downloadPdfBtn')?.addEventListener('click', function () {
            const btn = this;
            const origHtml = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Menyiapkan PDF...';
            btn.disabled = true;

            const element = document.getElementById('pdfReportContent');
            if (typeof html2pdf !== 'undefined' && element) {
                html2pdf()
                    .set({
                        margin: [8, 8, 8, 8],
                        filename: 'Laporan-Inventaris-Diskominfo-Garut-{{ now()->format("Ymd-His") }}.pdf',
                        image: { type: 'jpeg', quality: 0.96 },
                        html2canvas: { scale: 2, useCORS: true },
                        jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' }
                    })
                    .from(element)
                    .save()
                    .finally(() => {
                        btn.innerHTML = origHtml;
                        btn.disabled = false;
                    });
            } else {
                window.print();
                btn.innerHTML = origHtml;
                btn.disabled = false;
            }
        });

        if (new URLSearchParams(window.location.search).has('autoprint')) {
            window.addEventListener('load', function () {
                setTimeout(function () { window.print(); }, 300);
            });
        }
    </script>
</body>
</html>
