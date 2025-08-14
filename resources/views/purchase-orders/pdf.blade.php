<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order - {{ $purchaseOrder->po_number }}</title>
    <style>
        @page {
            size: A4;
            margin: 20mm 16mm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #222;
            margin: 0;
            padding: 0;
        }

        header {
            border-bottom: 2px solid #555;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        table.header-table {
            width: 100%;
            border-collapse: collapse;
        }

        table.header-table td {
            vertical-align: middle;
            border: none;
        }

        .company-info h2 {
            margin: 0;
            font-size: 18px;
        }

        .company-info p {
            margin: 2px 0;
            font-size: 11px;
            color: #555;
        }

        .logo img {
            height: 60px;
            width: auto;
            display: block;
        }

        .po-meta {
            text-align: right;
            font-size: 12px;
        }

        .box {
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 8px;
            background: #fafafa;
            margin-bottom: 10px;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        table.items th,
        table.items td {
            border: 1px solid #ccc;
            padding: 8px;
        }

        table.items th {
            background: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }

        table.items tbody tr:nth-child(even) {
            background: #fcfcfc;
        }

        /* Table headers akan repeat di setiap halaman */
        table.items thead {
            display: table-header-group;
        }

        /* Prevent row breaking */
        table.items tbody tr {
            page-break-inside: avoid;
        }

        .text-center {
            text-align: center;
        }

        .item-name {
            font-weight: bold;
            margin-bottom: 2px;
        }

        .item-code {
            font-size: 10px;
            color: #666;
        }

        .summary-box {
            float: right;
            width: 200px;
            margin-top: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background: #f9f9f9;
        }

        .summary-box table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary-box td {
            padding: 6px 8px;
            border-bottom: 1px solid #ddd;
            font-size: 11px;
        }

        .summary-box .label {
            font-weight: bold;
            text-align: right;
            width: 60%;
        }

        .summary-box .value {
            text-align: right;
        }

        table.signatures {
            width: 100%;
            margin-top: 60px;
            border-collapse: collapse;
        }

        table.signatures td {
            border: none;
            vertical-align: top;
            text-align: center;
            width: 33.33%;
            padding: 0 10px;
        }

        .signature-space {
            height: 80px;
        }

        .signature-line {
            border-top: 1px solid #000;
            padding-top: 4px;
            display: inline-block;
            min-width: 150px;
        }

        .signature-title {
            font-weight: bold;
            margin-bottom: 10px;
        }

        footer {
            border-top: 1px solid #ccc;
            padding-top: 4px;
            font-size: 10px;
            color: #555;
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }

        .notes-section {
            clear: both;
            margin: 40px 0;
            padding: 8px;
            background: #fffef7;
            border: 1px solid #e6e6e6;
            border-radius: 4px;
        }

        .notes-title {
            font-weight: bold;
            margin-bottom: 5px;
        }

        /* ✅ NEW: Style untuk notes dalam tabel */
        .item-notes {
            font-size: 10px;
            color: #666;
            font-style: italic;
            padding: 2px 4px;
            background-color: #f8f9fa;
            border-radius: 3px;
            display: inline-block;
            max-width: 120px;
            word-wrap: break-word;
        }

        .notes-stock-menipis {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .notes-manual {
            background-color: #e7f3ff;
            color: #004085;
            border: 1px solid #bee5eb;
        }
    </style>
</head>

<body>
    <header>
        <table class="header-table">
            <tr>
                <td style="width: 70%;">
                    <div class="logo">
                        <img src="{{ public_path('f1.png') }}" alt="PT FIBERONE Logo" onerror="this.style.display='none'">
                    </div>
                    <div class="company-info">
                        <h2>PT FIBERONE</h2>
                        <p>Griya Permata Hijau, Jl. Mpu Sedah No.01 Blok A, Gatak, Sumberejo</p>
                        <p>Kec. Klaten Sel., Kabupaten Klaten, Jawa Tengah 57422</p>
                        <p>Tel: 0815-6464-2022 • Email: info@fiberone.co.id</p>
                    </div>
                </td>
                <td style="width: 30%;" class="po-meta">
                    <strong>PURCHASE ORDER</strong><br>
                    No: {{ $purchaseOrder->po_number }}<br>
                    Tanggal: {{ $purchaseOrder->po_date->format('d M Y') }}<br>
                    @if ($purchaseOrder->expected_date)
                        Jatuh Tempo: {{ $purchaseOrder->expected_date->format('d M Y') }}<br>
                    @endif
                    Created by: {{ $purchaseOrder->createdBy->full_name }}
                </td>
            </tr>
        </table>
    </header>

    <main>
        <!-- Supplier Info -->
        <div class="box">
            <strong>Supplier:</strong><br>
            {{ $purchaseOrder->supplier->supplier_name }}<br>
            @if ($purchaseOrder->supplier->address)
                {!! nl2br($purchaseOrder->supplier->address) !!}<br>
            @endif
            @if ($purchaseOrder->supplier->phone || $purchaseOrder->supplier->email)
                Tel: {{ $purchaseOrder->supplier->phone ?? '-' }} • Email: {{ $purchaseOrder->supplier->email ?? '-' }}
            @endif
        </div>

        <!-- Items Table -->
        <table class="items">
            <thead>
                <tr>
                    <th style="width: 5%">No</th>
                    <th style="width: 30%">Nama Item</th>
                    <th class="text-center" style="width: 8%">Stock Gudang</th>
                    <th class="text-center" style="width: 8%">Stock Distribusi</th>
                    <th class="text-center" style="width: 8%">Stock Total</th>
                    <th class="text-center" style="width: 8%">Jumlah Order</th>
                    <th class="text-center" style="width: 8%">Satuan</th>
                    <th class="text-center" style="width: 25%">Reason</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($purchaseOrder->poDetails as $index => $detail)
                    @php
                        $stockGudang = $detail->item->qty_stock ?? 0;
                        $stockDistribusi = $detail->item->qty_ready ?? 0;
                        $stockTotal = $stockGudang + $stockDistribusi;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>
                            <div class="item-name">{{ $detail->item->item_name }}</div>
                            <div class="item-code">Code: {{ $detail->item->item_code }}</div>
                            @if ($detail->item->category)
                                <div class="item-code">Category: {{ $detail->item->category->category_name }}</div>
                            @endif
                        </td>
                        <td class="text-center">{{ number_format($stockGudang, 0) }}</td>
                        <td class="text-center">{{ number_format($stockDistribusi, 0) }}</td>
                        <td class="text-center" style="font-weight: bold; background-color: #f0f9ff; ">
                            {{ number_format($stockTotal, 0) }}
                        </td>
                        <td class="text-center">{{ number_format($detail->quantity_ordered, 0) }}</td>
                        <td class="text-center">{{ $detail->item->unit }}</td>
                        <td class="text-center">
                            @if ($detail->notes)
                                @php
                                    // Detect jenis notes berdasarkan content
                                    $isStockMenipis = str_contains(strtolower($detail->notes), 'stock menipis');
                                    $cssClass = $isStockMenipis ? 'notes-stock-menipis' : 'notes-manual';
                                @endphp
                                <span class="item-notes {{ $cssClass }}">
                                    {{ $detail->notes }}
                                </span>
                            @else
                                <span class="text-center" style="color: #999;">-</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Summary -->
        <div class="summary-box">
            <table>
                <tr>
                    <td class="label">Total Items:</td>
                    <td class="value">{{ $purchaseOrder->poDetails->count() }}</td>
                </tr>
                <tr>
                    <td class="label">Total Quantity:</td>
                    <td class="value">{{ number_format($purchaseOrder->poDetails->sum('quantity_ordered'), 0) }}</td>
                </tr>
                <tr>
                    <td class="label">Items with Notes:</td>
                    <td class="value">{{ $purchaseOrder->poDetails->whereNotNull('notes')->where('notes', '!=', '')->count() }}</td>
                </tr>
            </table>
        </div>
        <br>
        <br>
        <br>

        @if ($purchaseOrder->notes)
            <!-- Notes -->
            <div class="notes-section">
                <div class="notes-title">Catatan Purchase Order:</div>
                <div>{{ $purchaseOrder->notes }}</div>
            </div>
        @endif



        <!-- Signatures -->
        <table class="signatures">
            <tr>
                <td>
                    <div class="signature-title">Dibuat Oleh</div>
                    <div class="signature-space"></div>
                    <div class="signature-line">{{ $purchaseOrder->createdBy->full_name }}</div>
                    <div style="font-size: 10px; margin-top: 5px;">{{ $purchaseOrder->created_at->format('d/m/Y') }}
                    </div>
                </td>
                <td>
                    <div class="signature-title">Disetujui Oleh</div>
                    <div class="signature-space"></div>
                    <div class="signature-line">Manager</div>
                    <div style="font-size: 10px; margin-top: 5px;">Tanggal: ___________</div>
                </td>
                <td>
                    <div class="signature-title">Diterima Supplier</div>
                    <div class="signature-space"></div>
                    <div class="signature-line">{{ $purchaseOrder->supplier->supplier_name }}</div>
                    <div style="font-size: 10px; margin-top: 5px;">Tanggal: ___________</div>
                </td>
            </tr>
        </table>
    </main>

    <footer>
        <div>Barang dikirim dengan surat jalan & faktur resmi.</div>
        <div>Generated: {{ now()->format('d/m/Y H:i:s') }}</div>
    </footer>
</body>

</html>
