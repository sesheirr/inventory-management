<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Mutasi Barang — Diskominfo Kabupaten Garut</title>
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
        }
        .toolbar-title { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 13px; }
        .toolbar-actions { display: flex; align-items: center; gap: 8px; }
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
        }
        .btn-primary { background: #2563eb; color: #ffffff; }
        .btn-emerald { background: #059669; color: #ffffff; }
        .btn-ghost { background: rgba(255,255,255,0.12); color: #f8fafc; }

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
        .kop-logo { width: 68px; height: 68px; object-fit: contain; }
        .kop-text { flex: 1; text-align: center; }
        .kop-gov { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #334155; }
        .kop-dinas { font-size: 17px; font-weight: 800; text-transform: uppercase; color: #0f172a; margin-top: 2px; }
        .kop-addr { font-size: 10px; color: #475569; margin-top: 3px; }

        .doc-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 14px;
        }
        .doc-title { font-size: 14px; font-weight: 800; text-transform: uppercase; }
        .doc-meta { text-align: right; font-size: 10px; color: #475569; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 22px; }
        th, td { border: 1px solid #cbd5e1; padding: 7px 8px; text-align: left; vertical-align: top; }
        thead th { background: #0f1b38; color: #ffffff; font-size: 9.5px; font-weight: 700; text-transform: uppercase; }
        tbody tr:nth-child(even) { background: #f8fafc; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: 700; text-transform: uppercase; }
        .badge-approved { background: #dcfce7; color: #166534; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-rejected { background: #ffe4e6; color: #9f1239; }

        @media print {
            body { background: #ffffff; padding: 0; }
            .toolbar { display: none !important; }
            .sheet { box-shadow: none; padding: 10px 14px; max-width: 100%; }
            @page { size: A4 landscape; margin: 10mm; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <div class="toolbar-title">
            <i class="bi bi-file-earmark-pdf-fill" style="color: #60a5fa; font-size: 16px;"></i>
            <span>Laporan Riwayat Mutasi Barang — Diskominfo Kabupaten Garut</span>
        </div>
        <div class="toolbar-actions">
            <a href="{{ route('mutations.index') }}" class="btn btn-ghost">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <button type="button" id="downloadMutationPdfBtn" class="btn btn-emerald">
                <i class="bi bi-download"></i> Unduh File PDF
            </button>
            <button type="button" onclick="window.print()" class="btn btn-primary">
                <i class="bi bi-printer-fill"></i> Cetak / Simpan PDF
            </button>
        </div>
    </div>

    <div id="pdfMutationContent" class="sheet">
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
                <div class="doc-title">Laporan Riwayat Mutasi & Perpindahan Barang Inventaris</div>
                <div style="font-size: 10px; color: #475569; margin-top: 2px;">
                    Total Data Tercatat: <strong>{{ $mutations->count() }} Transaksi Mutasi</strong>
                </div>
            </div>
            <div class="doc-meta">
                <div>Tanggal Cetak: <strong>{{ now()->translatedFormat('d F Y, H:i') }} WIB</strong></div>
                <div>Dicetak Oleh: <strong>{{ auth()->user()?->name ?? 'Petugas' }}</strong></div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 34px; text-align: center;">No</th>
                    <th style="width: 90px;">Tanggal</th>
                    <th>Nama Barang</th>
                    <th style="width: 95px;">Jenis Mutasi</th>
                    <th style="width: 55px; text-align: center;">Jml</th>
                    <th>Ruangan Asal &rarr; Tujuan</th>
                    <th style="width: 115px;">Diajukan Oleh</th>
                    <th style="width: 90px; text-align: center;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($mutations as $idx => $mut)
                    <tr>
                        <td style="text-align: center; font-weight: 700;">{{ $idx + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($mut->mutation_date)->format('d/m/Y') }}</td>
                        <td>
                            <div style="font-weight: 700;">{{ $mut->product?->name ?? 'Barang Dihapus' }}</div>
                            <div style="font-size: 9.5px; color: #64748b;">{{ $mut->note ?: '-' }}</div>
                        </td>
                        <td style="text-transform: uppercase; font-weight: 700; font-size: 9.5px;">
                            {{ str_replace('_', ' ', $mut->type) }}
                        </td>
                        <td style="text-align: center; font-weight: 800;">{{ $mut->quantity }}</td>
                        <td>
                            {{ $mut->fromRoom?->name ?? '-' }} &rarr; {{ $mut->toRoom?->name ?? '-' }}
                        </td>
                        <td>{{ $mut->user?->name ?? 'Sistem' }}</td>
                        <td style="text-align: center;">
                            @if($mut->status === 'approved')
                                <span class="badge badge-approved">Disetujui</span>
                            @elseif($mut->status === 'rejected')
                                <span class="badge badge-rejected">Ditolak</span>
                            @else
                                <span class="badge badge-pending">Pending</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 20px; color: #64748b;">Tidak ada data mutasi barang.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script>
        document.getElementById('downloadMutationPdfBtn')?.addEventListener('click', function () {
            const element = document.getElementById('pdfMutationContent');
            if (typeof html2pdf !== 'undefined' && element) {
                html2pdf()
                    .set({
                        margin: [8, 8, 8, 8],
                        filename: 'Laporan-Mutasi-Diskominfo-Garut-{{ now()->format("Ymd-His") }}.pdf',
                        image: { type: 'jpeg', quality: 0.96 },
                        html2canvas: { scale: 2, useCORS: true },
                        jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' }
                    })
                    .from(element)
                    .save();
            } else {
                window.print();
            }
        });
    </script>
</body>
</html>
