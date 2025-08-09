<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order - {{ $purchaseOrder->po_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* A4 Page Setup */
        @page {
            size: A4;
            margin: 15mm 20mm 15mm 20mm;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            line-height: 1.3;
            color: #333;
            background: #fff;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: #fff;
            padding: 15mm 20mm;
            box-sizing: border-box;
        }

        /* Print/Download Controls - Hide on print */
        .print-controls {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            display: flex;
            gap: 10px;
            print-display: none;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-print {
            background: #2563eb;
            color: white;
        }

        .btn-print:hover {
            background: #1d4ed8;
        }

        .btn-download {
            background: #059669;
            color: white;
        }

        .btn-download:hover {
            background: #047857;
        }

        .btn-back {
            background: #6b7280;
            color: white;
        }

        .btn-back:hover {
            background: #4b5563;
        }

        /* Header - Only on first page */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20mm;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 15mm;
        }

        .company-info {
            flex: 1;
            display: flex;
            align-items: flex-start;
            gap: 15mm;
        }

        .company-logo {
            width: 60px;
            height: 60px;
            object-fit: contain;
        }

        .company-details {
            flex: 1;
        }

        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 3mm;
        }

        .company-tagline {
            font-size: 10px;
            color: #6b7280;
            margin-bottom: 8mm;
            font-style: italic;
        }

        .company-address {
            font-size: 9px;
            color: #374151;
            line-height: 1.3;
            max-width: 70mm;
        }

        .company-address strong {
            color: #1f2937;
            font-weight: 600;
        }

        .po-title {
            text-align: center;
            flex-shrink: 0;
            min-width: 50mm;
        }

        .po-title h1 {
            font-size: 20px;
            font-weight: 900;
            color: #1e40af;
            margin-bottom: 5mm;
            letter-spacing: 0.5px;
        }

        .po-number {
            font-size: 12px;
            font-weight: 700;
            color: #dc2626;
            margin-bottom: 8mm;
            background: #fef2f2;
            padding: 3mm 8mm;
            border-radius: 4mm;
            border: 1px solid #fecaca;
            display: inline-block;
        }

        /* PO Info Grid */
        .po-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20mm;
            margin-bottom: 15mm;
        }

        .info-section h3 {
            font-size: 12px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 8mm;
            padding-bottom: 3mm;
            border-bottom: 1px solid #e5e7eb;
        }

        .info-row {
            display: flex;
            margin-bottom: 4mm;
        }

        .info-label {
            font-weight: 600;
            width: 35mm;
            color: #374151;
        }

        .info-value {
            color: #6b7280;
            flex: 1;
        }

        /* Status Badge yang lebih menarik */
        .status-badge {
            display: inline-block;
            padding: 2mm 4mm;
            border-radius: 12px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 2px solid transparent;
            text-align: center;
            min-width: 15mm;
        }

        .status-pending {
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            color: #92400e;
            border-color: #f59e0b;
        }

        .status-partial {
            background: linear-gradient(135deg, #fed7aa, #fdba74);
            color: #c2410c;
            border-color: #ea580c;
        }

        .status-completed {
            background: linear-gradient(135deg, #dcfce7, #bbf7d0);
            color: #166534;
            border-color: #22c55e;
        }

        .status-draft {
            background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
            color: #374151;
            border-color: #6b7280;
        }

        .status-approved {
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            color: #1e40af;
            border-color: #3b82f6;
        }

        .status-cancelled {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            color: #991b1b;
            border-color: #ef4444;
        }

        /* Items Section */
        .items-section {
            margin-bottom: 15mm;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 8mm;
            padding: 5mm 0;
            border-bottom: 2px solid #2563eb;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10mm;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .items-table th {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            font-weight: bold;
            padding: 5mm 4mm;
            text-align: center;
            border: 1px solid #1e40af;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .items-table td {
            padding: 4mm 3mm;
            border: 1px solid #d1d5db;
            vertical-align: middle;
            font-size: 10px;
        }

        .items-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .items-table tbody tr:hover {
            background-color: #e0f2fe;
        }

        .item-name {
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 2mm;
            font-size: 11px;
        }

        .item-code {
            font-size: 9px;
            color: #6b7280;
            margin-bottom: 1mm;
            font-family: 'Courier New', monospace;
            background: #f3f4f6;
            padding: 1mm 2mm;
            border-radius: 2mm;
            display: inline-block;
        }

        .item-category {
            font-size: 8px;
            color: #059669;
            font-style: italic;
        }

        .item-description {
            margin-top: 1mm;
            padding-top: 1mm;
            border-top: 1px dotted #e5e7eb;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        /* Progress Bar untuk tracking barang diterima */
        .progress-container {
            margin-top: 2mm;
        }

        .progress-bar {
            width: 100%;
            height: 4mm;
            background-color: #e5e7eb;
            border-radius: 2mm;
            overflow: hidden;
            border: 1px solid #d1d5db;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #10b981, #059669);
            transition: width 0.3s ease;
            position: relative;
        }

        .progress-fill::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg,
                transparent 25%, rgba(255,255,255,0.2) 25%,
                rgba(255,255,255,0.2) 50%, transparent 50%,
                transparent 75%, rgba(255,255,255,0.2) 75%);
            background-size: 4px 4px;
        }

        .progress-text {
            font-size: 8px;
            color: #059669;
            font-weight: 600;
            margin-top: 1mm;
            text-align: center;
        }

        .progress-pending {
            color: #dc2626;
        }

        .progress-partial {
            color: #d97706;
        }

        .progress-complete {
            color: #059669;
        }

        /* Summary Table */
        .summary {
            margin-top: 10mm;
            display: flex;
            justify-content: flex-end;
        }

        .summary-table {
            width: 70mm;
            border-collapse: collapse;
        }

        .summary-table td {
            padding: 3mm 5mm;
            border: 1px solid #d1d5db;
            font-size: 10px;
        }

        .summary-table .label {
            background-color: #f8fafc;
            font-weight: 600;
            text-align: right;
            width: 35mm;
        }

        .summary-table .value {
            text-align: right;
            font-weight: 600;
        }

        .total-row {
            background-color: #2563eb !important;
            color: white !important;
            font-weight: bold !important;
        }

        /* Notes Section */
        .notes-section {
            margin-top: 15mm;
            margin-bottom: 15mm;
        }

        .notes-content {
            background-color: #f8fafc;
            border: 1px solid #d1d5db;
            border-radius: 3mm;
            padding: 8mm;
            min-height: 20mm;
            font-style: italic;
            color: #6b7280;
            font-size: 10px;
        }

        /* Signatures - Only on last page */
        .signatures {
            margin-top: 20mm;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20mm;
        }

        .signature-box {
            text-align: center;
        }

        .signature-title {
            font-weight: 600;
            margin-bottom: 20mm;
            color: #374151;
            font-size: 10px;
        }

        .signature-line {
            border-bottom: 1px solid #000;
            margin-bottom: 5mm;
            height: 1px;
        }

        .signature-name {
            font-size: 10px;
            color: #6b7280;
        }

        .signature-date {
            font-size: 9px;
            color: #9ca3af;
            margin-top: 2mm;
        }

        /* Footer */
        .footer {
            margin-top: 15mm;
            text-align: center;
            font-size: 9px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 8mm;
        }

        /* Page Break Control */
        .page-break {
            page-break-before: always;
        }

        .avoid-break {
            page-break-inside: avoid;
        }

        /* Continuation Header for subsequent pages */
        .continuation-header {
            display: none;
            margin-bottom: 10mm;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5mm;
        }

        .continuation-title {
            font-size: 16px;
            font-weight: bold;
            color: #1e40af;
            text-align: center;
        }

        .continuation-po {
            font-size: 12px;
            color: #6b7280;
            text-align: center;
            margin-top: 3mm;
        }

        /* Print-specific styles */
        @media print {
            body {
                font-size: 10px;
            }

            .print-controls {
                display: none !important;
            }

            .page {
                margin: 0;
                padding: 15mm 20mm;
                min-height: auto;
            }

            .page-break {
                page-break-before: always;
            }

            .avoid-break {
                page-break-inside: avoid;
            }

            /* Show continuation header on subsequent pages */
            .continuation-header {
                display: block;
            }

            /* Hide main header on continuation pages */
            .page:not(:first-child) .header {
                display: none;
            }

            .page:not(:first-child) .po-info {
                display: none;
            }

            /* Ensure table headers repeat */
            .items-table thead {
                display: table-header-group;
            }

            .items-table tfoot {
                display: table-footer-group;
            }

            /* Adjust spacing for print */
            .items-table th,
            .items-table td {
                padding: 2mm;
                font-size: 9px;
            }

            .signature-title {
                margin-bottom: 15mm;
            }
        }

        /* Table row break control */
        .items-table tbody tr {
            page-break-inside: avoid;
        }

        /* Ensure summary doesn't orphan */
        .summary-section {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
    <!-- Print/Download Controls -->
    <div class="print-controls">
        <button onclick="printPO()" class="btn btn-print">
            <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                <path d="M5 1a2 2 0 0 0-2 2v1h10V3a2 2 0 0 0-2-2H5zM4 4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v1H4V4z"/>
                <path d="M0 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-1v-2a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2H2a2 2 0 0 1-2-2V7zm2.5 1a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                <path d="M5 11a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1v-3z"/>
            </svg>
            Print
        </button>
        <button onclick="downloadPDF()" class="btn btn-download">
            <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
            </svg>
            Download PDF
        </button>
        <a href="{{ route('purchase-orders.show', $purchaseOrder->po_id) }}" class="btn btn-back">
            <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
            </svg>
            Kembali
        </a>
    </div>

    <div class="page">
        <!-- Main Header (First Page Only) -->
        <div class="header">
            <div class="company-info">
                <img src="{{ asset('f1.png') }}" alt="PT FiberOne Logo" class="company-logo" onerror="this.style.display='none'">
                <div class="company-details">
                    <div class="company-name">PT FIBERONE</div>
                    <div class="company-tagline">Solusi Fiber Optik & Telekomunikasi Terdepan</div>
                    <div class="company-address">
                        <div style="margin-bottom: 2mm;"><strong>Alamat:</strong></div>
                        <div>Griya Permata Hijau, Jl. Mpu Sedah No.01 Blok A</div>
                        <div>Gatak, Sumberejo, Kec. Klaten Sel.</div>
                        <div style="margin-bottom: 2mm;">Kabupaten Klaten, Jawa Tengah 57422</div>
                        <div><strong>Phone:</strong> 0815-6464-2022</div>
                    </div>
                </div>
            </div>
            <div class="po-title">
                <h1>PURCHASE ORDER</h1>
                <div class="po-number">{{ $purchaseOrder->po_number }}</div>
            </div>
        </div>

        <!-- PO Information (First Page Only) -->
        <div class="po-info">
            <div class="info-section">
                <h3>Informasi Purchase Order</h3>
                <div class="info-row">
                    <span class="info-label">Nomor PO:</span>
                    <span class="info-value">{{ $purchaseOrder->po_number }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tanggal PO:</span>
                    <span class="info-value">{{ $purchaseOrder->po_date->format('d/m/Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tanggal Diharapkan:</span>
                    <span class="info-value">{{ $purchaseOrder->expected_date?->format('d/m/Y') ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="info-value">
                        <span class="status-badge status-{{ $purchaseOrder->status }}">
                            {{ $purchaseOrder->getStatusInfo()['text'] }}
                        </span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Dibuat Oleh:</span>
                    <span class="info-value">{{ $purchaseOrder->createdBy->full_name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tanggal Dibuat:</span>
                    <span class="info-value">{{ $purchaseOrder->created_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>

            <div class="info-section">
                <h3>Informasi Supplier</h3>
                <div class="info-row">
                    <span class="info-label">Nama Supplier:</span>
                    <span class="info-value">{{ $purchaseOrder->supplier->supplier_name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Kode Supplier:</span>
                    <span class="info-value">{{ $purchaseOrder->supplier->supplier_code }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Contact Person:</span>
                    <span class="info-value">{{ $purchaseOrder->supplier->contact_person ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Telepon:</span>
                    <span class="info-value">{{ $purchaseOrder->supplier->phone ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value">{{ $purchaseOrder->supplier->email ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Alamat:</span>
                    <span class="info-value">{{ $purchaseOrder->supplier->address ?? '-' }}</span>
                </div>
            </div>
        </div>

        <!-- Continuation Header (Hidden on first page, shown on subsequent pages when printed) -->
        <div class="continuation-header">
            <div class="continuation-title">PURCHASE ORDER (Lanjutan)</div>
            <div class="continuation-po">{{ $purchaseOrder->po_number }}</div>
        </div>

        <!-- Items Section -->
        <div class="items-section">
            <h2 class="section-title">Detail Items</h2>
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 8%">No</th>
                        <th style="width: 50%">Detail Produk</th>
                        <th style="width: 12%">Satuan</th>
                        <th style="width: 15%">Qty Pesanan</th>
                        <th style="width: 15%">Qty Diterima</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $itemsPerPage = 15; // Adjust based on your A4 page capacity
                        $totalItems = $purchaseOrder->poDetails->count();
                        $pageCount = ceil($totalItems / $itemsPerPage);
                    @endphp

                    @foreach($purchaseOrder->poDetails as $index => $detail)
                        @if($index > 0 && $index % $itemsPerPage == 0)
                            </tbody>
                        </table>
                    </div>

                    <!-- Page Break -->
                    <div class="page-break"></div>

                    <div class="page">
                        <!-- Continuation Header -->
                        <div class="continuation-header">
                            <div class="continuation-title">PURCHASE ORDER (Lanjutan)</div>
                            <div class="continuation-po">{{ $purchaseOrder->po_number }} - Halaman {{ ceil(($index + 1) / $itemsPerPage) }}</div>
                        </div>

                        <div class="items-section">
                            <table class="items-table">
                                <thead>
                                    <tr>
                                        <th style="width: 8%">No</th>
                                        <th style="width: 50%">Detail Produk</th>
                                        <th style="width: 12%">Satuan</th>
                                        <th style="width: 15%">Qty Pesanan</th>
                                        <th style="width: 15%">Qty Diterima</th>
                                    </tr>
                                </thead>
                                <tbody>
                        @endif

                        <tr class="avoid-break">
                            <td class="text-center" style="font-weight: 600; color: #1f2937;">{{ $index + 1 }}</td>
                            <td>
                                <div class="item-name">{{ $detail->item->item_name }}</div>
                                <div class="item-code">{{ $detail->item->item_code }}</div>
                                @if($detail->item->category)
                                    <div class="item-category">Kategori: {{ $detail->item->category->category_name }}</div>
                                @endif

                                @if($detail->notes)
                                    <div class="item-description">
                                        <small style="color: #6b7280; font-style: italic;">{{ $detail->notes }}</small>
                                    </div>
                                @endif

                                @if($detail->quantity_received > 0)
                                    <div class="progress-container">
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: {{ min(100, ($detail->quantity_received / $detail->quantity_ordered) * 100) }}%"></div>
                                        </div>
                                        <div class="progress-text
                                            @if($detail->quantity_received >= $detail->quantity_ordered) progress-complete
                                            @elseif($detail->quantity_received > 0) progress-partial
                                            @else progress-pending @endif">
                                            @if($detail->quantity_received >= $detail->quantity_ordered)
                                                ✓ SELESAI ({{ number_format(($detail->quantity_received / $detail->quantity_ordered) * 100, 0) }}%)
                                            @elseif($detail->quantity_received > 0)
                                                ⏳ SEBAGIAN ({{ number_format(($detail->quantity_received / $detail->quantity_ordered) * 100, 0) }}%)
                                            @else
                                                ⭕ BELUM DITERIMA
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <div class="progress-container">
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: 0%"></div>
                                        </div>
                                        <div class="progress-text progress-pending">
                                            ⭕ BELUM DITERIMA
                                        </div>
                                    </div>
                                @endif
                            </td>
                            <td class="text-center" style="font-weight: 600; color: #374151;">{{ $detail->item->unit }}</td>
                            <td class="text-center" style="font-weight: 600; color: #1f2937; font-size: 11px;">
                                {{ number_format($detail->quantity_ordered, 0) }}
                                <div style="font-size: 8px; color: #6b7280; margin-top: 1mm;">Target</div>
                            </td>
                            <td class="text-center" style="font-weight: 600; font-size: 11px;">
                                <span style="color: {{ $detail->quantity_received >= $detail->quantity_ordered ? '#059669' : ($detail->quantity_received > 0 ? '#d97706' : '#dc2626') }};">
                                    {{ number_format($detail->quantity_received, 0) }}
                                </span>
                                <div style="font-size: 8px; color: #6b7280; margin-top: 1mm;">
                                    @if($detail->quantity_received >= $detail->quantity_ordered)
                                        Lengkap
                                    @elseif($detail->quantity_received > 0)
                                        Kurang {{ number_format($detail->quantity_ordered - $detail->quantity_received, 0) }}
                                    @else
                                        Menunggu
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Summary Section (Always at the end) -->
            <div class="summary-section avoid-break">
                <div class="summary">
                    <table class="summary-table">
                        <tr>
                            <td class="label">Total Item:</td>
                            <td class="value">{{ $purchaseOrder->poDetails->count() }} item</td>
                        </tr>
                        <tr>
                            <td class="label">Total Quantity:</td>
                            <td class="value">{{ number_format($purchaseOrder->poDetails->sum('quantity_ordered'), 0) }}</td>
                        </tr>
                        <tr>
                            <td class="label">Total Diterima:</td>
                            <td class="value" style="color: {{ $purchaseOrder->poDetails->sum('quantity_received') >= $purchaseOrder->poDetails->sum('quantity_ordered') ? '#059669' : '#d97706' }};">
                                {{ number_format($purchaseOrder->poDetails->sum('quantity_received'), 0) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="label">Progress Keseluruhan:</td>
                            <td class="value" style="color: #1f2937; font-weight: bold;">
                                {{ number_format($purchaseOrder->getCompletionPercentage(), 1) }}%
                            </td>
                        </tr>
                        @php
                            $totalOrdered = $purchaseOrder->poDetails->sum('quantity_ordered');
                            $totalReceived = $purchaseOrder->poDetails->sum('quantity_received');
                            $remaining = $totalOrdered - $totalReceived;
                        @endphp
                        @if($remaining > 0)
                        <tr>
                            <td class="label">Sisa Yang Menunggu:</td>
                            <td class="value" style="color: #dc2626; font-weight: bold;">
                                {{ number_format($remaining, 0) }} item
                            </td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        @if($purchaseOrder->notes)
        <!-- Notes Section -->
        <div class="notes-section avoid-break">
            <h3 style="font-size: 12px; margin-bottom: 5mm; color: #1f2937;">Catatan:</h3>
            <div class="notes-content">
                {{ $purchaseOrder->notes }}
            </div>
        </div>
        @endif

        <!-- Signatures (Always on last page) -->
        <div class="signatures avoid-break">
            <div class="signature-box">
                <div class="signature-title">Dibuat Oleh</div>
                <div class="signature-line"></div>
                <div class="signature-name">{{ $purchaseOrder->createdBy->full_name }}</div>
                <div class="signature-date">{{ $purchaseOrder->created_at->format('d/m/Y') }}</div>
            </div>

            <div class="signature-box">
                <div class="signature-title">Disetujui Oleh</div>
                <div class="signature-line"></div>
                <div class="signature-name">Manager Procurement</div>
                <div class="signature-date">Tanggal: ___/___/______</div>
            </div>

            <div class="signature-box">
                <div class="signature-title">Diterima Supplier</div>
                <div class="signature-line"></div>
                <div class="signature-name">{{ $purchaseOrder->supplier->supplier_name }}</div>
                <div class="signature-date">Tanggal: ___/___/______</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Dokumen ini dicetak secara otomatis pada {{ now()->format('d/m/Y H:i:s') }}</p>
            <p>Purchase Order ID: {{ $purchaseOrder->po_id }} | PT FiberOne - Procurement Department</p>
        </div>
    </div>

    <script>
        // Print function
        function printPO() {
            window.print();
        }

        // Download PDF function
        function downloadPDF() {
            try {
                const downloadUrl = `{{ url('purchase-orders/' . $purchaseOrder->po_id . '/download-pdf') }}`;
                const link = document.createElement('a');
                link.href = downloadUrl;
                link.download = `PO_{{ $purchaseOrder->po_number }}_{{ now()->format('Y-m-d') }}.pdf`;
                link.target = '_blank';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            } catch (error) {
                console.log('PDF route not available, using browser print dialog');
                window.print();
            }
        }

        // Keyboard shortcuts
        document.addEventListener('DOMContentLoaded', function() {
            document.addEventListener('keydown', function(e) {
                // Ctrl + P for print
                if (e.ctrlKey && e.key === 'p') {
                    e.preventDefault();
                    printPO();
                }
                // Ctrl + D for download
                if (e.ctrlKey && e.key === 'd') {
                    e.preventDefault();
                    downloadPDF();
                }
            });

            // Handle logo loading error
            const logo = document.querySelector('.company-logo');
            if (logo) {
                logo.onerror = function() {
                    this.style.display = 'none';
                    const companyInfo = document.querySelector('.company-info');
                    if (companyInfo) {
                        companyInfo.style.gap = '0px';
                    }
                };
            }
        });

        // Print page calculation and optimization
        function calculateOptimalItemsPerPage() {
            // Calculate based on A4 dimensions and content
            const a4Height = 297; // mm
            const margins = 30; // top + bottom margins
            const headerHeight = 60; // approximate header height
            const itemRowHeight = 12; // approximate row height in mm
            const summaryHeight = 40; // summary section height
            const signatureHeight = 60; // signature section height

            const availableHeight = a4Height - margins - headerHeight - summaryHeight - signatureHeight;
            return Math.floor(availableHeight / itemRowHeight);
        }

        // Dynamic pagination for very large orders
        window.addEventListener('beforeprint', function() {
            console.log('Preparing document for print...');

            // Add page numbers to continuation headers
            const continuationHeaders = document.querySelectorAll('.continuation-header');
            continuationHeaders.forEach((header, index) => {
                const pageNum = index + 2; // Start from page 2
                const poElement = header.querySelector('.continuation-po');
                if (poElement && !poElement.textContent.includes('Halaman')) {
                    poElement.textContent += ` - Halaman ${pageNum}`;
                }
            });
        });

        window.addEventListener('afterprint', function() {
            console.log('Print dialog closed');
        });
    </script>
</body>
</html>
