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

        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            background: #f8fafc;
        }

        .container {
            max-width: 210mm;
            margin: 0 auto;
            padding: 20px;
            background: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        /* Print/Download Controls */
        .print-controls {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-print {
            background: #2563eb;
            color: white;
        }

        .btn-print:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
        }

        .btn-download {
            background: #059669;
            color: white;
        }

        .btn-download:hover {
            background: #047857;
            transform: translateY(-2px);
        }

        .btn-back {
            background: #6b7280;
            color: white;
        }

        .btn-back:hover {
            background: #4b5563;
            transform: translateY(-2px);
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 20px;
        }

        .company-info {
            flex: 1;
            display: flex;
            align-items: flex-start;
            gap: 20px;
        }

        .company-logo {
            width: 80px;
            height: 80px;
            object-fit: contain;
        }

        .company-details {
            flex: 1;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 5px;
        }

        .company-tagline {
            font-size: 11px;
            color: #6b7280;
            margin-bottom: 10px;
            font-style: italic;
        }

        .company-address {
            font-size: 9px;
            color: #374151;
            line-height: 1.4;
            max-width: 300px;
        }

        .company-address strong {
            color: #1f2937;
            font-weight: 600;
        }

        .po-title {
            text-align: center;
            flex-shrink: 0;
            min-width: 200px;
            padding-left: 20px;
        }

        .po-title h1 {
            font-size: 26px;
            font-weight: 900;
            color: #1e40af;
            margin-bottom: 8px;
            letter-spacing: 1px;
        }

        .po-number {
            font-size: 14px;
            font-weight: 700;
            color: #dc2626;
            margin-bottom: 12px;
            background: #fef2f2;
            padding: 4px 12px;
            border-radius: 6px;
            border: 1px solid #fecaca;
            display: inline-block;
        }

        .qr-code {
            width: 70px;
            height: 70px;
            border: 2px dashed #d1d5db;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            color: #9ca3af;
            margin: 0 auto;
            border-radius: 6px;
            background: #f9fafb;
        }

        .qr-code small {
            font-size: 7px;
            margin-top: 2px;
            font-weight: 500;
        }

        /* PO Info Grid */
        .po-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .info-section h3 {
            font-size: 14px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #e5e7eb;
        }

        .info-row {
            display: flex;
            margin-bottom: 8px;
        }

        .info-label {
            font-weight: bold;
            width: 120px;
            color: #374151;
        }

        .info-value {
            color: #6b7280;
            flex: 1;
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-draft {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-pending {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .status-approved {
            background-color: #d1fae5;
            color: #065f46;
        }

        .status-completed {
            background-color: #dcfce7;
            color: #166534;
        }

        .status-cancelled {
            background-color: #fee2e2;
            color: #991b1b;
        }

        /* Items Table */
        .items-section {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 15px;
            padding: 10px 0;
            border-bottom: 2px solid #2563eb;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .items-table th {
            background-color: #f8fafc;
            color: #374151;
            font-weight: bold;
            padding: 12px 8px;
            text-align: left;
            border: 1px solid #d1d5db;
            font-size: 11px;
        }

        .items-table td {
            padding: 10px 8px;
            border: 1px solid #d1d5db;
            vertical-align: top;
        }

        .items-table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .item-name {
            font-weight: bold;
            color: #1f2937;
        }

        .item-code {
            font-size: 10px;
            color: #6b7280;
            margin-top: 2px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        /* Summary */
        .summary {
            margin-top: 20px;
            display: flex;
            justify-content: flex-end;
        }

        .summary-table {
            width: 300px;
            border-collapse: collapse;
        }

        .summary-table td {
            padding: 8px 12px;
            border: 1px solid #d1d5db;
        }

        .summary-table .label {
            background-color: #f8fafc;
            font-weight: bold;
            text-align: right;
            width: 150px;
        }

        .summary-table .value {
            text-align: right;
            font-weight: bold;
        }

        .total-row {
            background-color: #2563eb !important;
            color: white !important;
        }

        /* Notes */
        .notes-section {
            margin-top: 30px;
            margin-bottom: 30px;
        }

        .notes-content {
            background-color: #f8fafc;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 15px;
            min-height: 60px;
            font-style: italic;
            color: #6b7280;
        }

        /* Signatures */
        .signatures {
            margin-top: 50px;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 40px;
        }

        .signature-box {
            text-align: center;
        }

        .signature-title {
            font-weight: bold;
            margin-bottom: 50px;
            color: #374151;
        }

        .signature-line {
            border-bottom: 1px solid #000;
            margin-bottom: 10px;
            height: 1px;
        }

        .signature-name {
            font-size: 11px;
            color: #6b7280;
        }

        .signature-date {
            font-size: 10px;
            color: #9ca3af;
            margin-top: 5px;
        }

        /* Footer */
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }

        /* Print Styles */
        @media print {
            body {
                font-size: 11px;
                background: #fff;
            }

            .container {
                padding: 0;
                max-width: none;
                box-shadow: none;
            }

            .print-controls {
                display: none !important;
            }

            .page-break {
                page-break-before: always;
            }

            .items-table th,
            .items-table td {
                padding: 6px 4px;
                font-size: 10px;
            }

            .header {
                margin-bottom: 20px;
                padding-bottom: 15px;
            }

            .company-logo {
                width: 60px;
                height: 60px;
            }

            .company-name {
                font-size: 20px;
            }

            .po-title h1 {
                font-size: 24px;
            }

            .company-address {
                font-size: 8px;
            }

            .signatures {
                margin-top: 30px;
                gap: 30px;
            }

            .signature-title {
                margin-bottom: 40px;
            }
        }

        /* Progress Bar for Received Items */
        .progress-bar {
            width: 100%;
            height: 8px;
            background-color: #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 4px;
        }

        .progress-fill {
            height: 100%;
            background-color: #10b981;
            transition: width 0.3s ease;
        }

        .received-info {
            font-size: 10px;
            color: #059669;
            margin-top: 2px;
        }

        /* QR Code placeholder */
        .qr-code {
            width: 80px;
            height: 80px;
            border: 2px dashed #d1d5db;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            color: #9ca3af;
            margin: 0 auto;
            border-radius: 8px;
        }

        /* Loading state */
        .loading {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 20px;
            border-radius: 8px;
            z-index: 2000;
        }
    </style>
</head>
<body>
    <!-- Print/Download Controls -->
    <div class="print-controls">
        <button onclick="printPO()" class="btn btn-print">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path d="M5 1a2 2 0 0 0-2 2v1h10V3a2 2 0 0 0-2-2H5zM4 4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v1H4V4z"/>
                <path d="M0 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-1v-2a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2H2a2 2 0 0 1-2-2V7zm2.5 1a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                <path d="M5 11a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1v-3z"/>
            </svg>
            Print
        </button>
        <button onclick="downloadPDF()" class="btn btn-download">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
            </svg>
            Download PDF
        </button>
        <a href="{{ route('purchase-orders.show', $purchaseOrder->po_id) }}" class="btn btn-back">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
            </svg>
            Kembali
        </a>
    </div>

    <!-- Loading indicator -->
    <div id="loading" class="loading">
        <div>Generating PDF...</div>
    </div>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="company-info">
                <img src="{{ asset('f1.png') }}" alt="PT FiberOne Logo" class="company-logo" onerror="this.style.display='none'">
                <div class="company-details">
                    <div class="company-name">PT FIBERONE</div>
                    <div class="company-tagline">Solusi Fiber Optik & Telekomunikasi Terdepan</div>
                    <div class="company-address">
                        <div style="margin-bottom: 4px;"><strong>Address:</strong></div>
                        <div style="margin-bottom: 2px;">Griya Permata Hijau, Jl. Mpu Sedah No.01 Blok A</div>
                        <div style="margin-bottom: 2px;">Gatak, Sumberejo, Kec. Klaten Sel.</div>
                        <div style="margin-bottom: 4px;">Kabupaten Klaten, Jawa Tengah 57422</div>
                        <div><strong>Phone:</strong> 0815-6464-2022</div>
                    </div>
                </div>
            </div>
            <div class="po-title">
                <h1>PURCHASE ORDER</h1>
                <div class="po-number">{{ $purchaseOrder->po_number }}</div>
            </div>
        </div>

        <!-- PO Information -->
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

        <!-- Items Section -->
        <div class="items-section">
            <h2 class="section-title">Detail Items</h2>
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 5%">No</th>
                        <th style="width: 35%">Nama Item</th>
                        <th style="width: 10%">Unit</th>
                        <th style="width: 10%">Qty Order</th>
                        <th style="width: 10%">Qty Terima</th>
                        <th style="width: 15%">Harga Satuan</th>
                        <th style="width: 15%">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchaseOrder->poDetails as $index => $detail)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>
                            <div class="item-name">{{ $detail->item->item_name }}</div>
                            <div class="item-code">{{ $detail->item->item_code }}</div>
                            @if($detail->item->category)
                                <div class="item-code">Kategori: {{ $detail->item->category->category_name }}</div>
                            @endif

                            @if($detail->quantity_received > 0)
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: {{ ($detail->quantity_received / $detail->quantity_ordered) * 100 }}%"></div>
                                </div>
                                <div class="received-info">
                                    Progress: {{ number_format(($detail->quantity_received / $detail->quantity_ordered) * 100, 1) }}%
                                </div>
                            @endif
                        </td>
                        <td class="text-center">{{ $detail->item->unit }}</td>
                        <td class="text-right">{{ number_format($detail->quantity_ordered, 2) }}</td>
                        <td class="text-right">
                            {{ number_format($detail->quantity_received, 2) }}
                            @if($detail->quantity_received > 0)
                                <div style="font-size: 10px; color: #059669;">
                                    ✓ {{ number_format(($detail->quantity_received / $detail->quantity_ordered) * 100, 0) }}%
                                </div>
                            @endif
                        </td>
                        <td class="text-right">Rp {{ number_format($detail->unit_price, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($detail->total_price, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Summary -->
            <div class="summary">
                <table class="summary-table">
                    <tr>
                        <td class="label">Total Item:</td>
                        <td class="value">{{ $purchaseOrder->poDetails->count() }} item</td>
                    </tr>
                    <tr>
                        <td class="label">Total Quantity:</td>
                        <td class="value">{{ number_format($purchaseOrder->poDetails->sum('quantity_ordered'), 2) }}</td>
                    </tr>
                    <tr>
                        <td class="label">Total Diterima:</td>
                        <td class="value">{{ number_format($purchaseOrder->poDetails->sum('quantity_received'), 2) }}</td>
                    </tr>
                    <tr>
                        <td class="label">Subtotal:</td>
                        <td class="value">Rp {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="label">PPN (11%):</td>
                        <td class="value">Rp {{ number_format($purchaseOrder->total_amount * 0.11, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="total-row">
                        <td class="label">GRAND TOTAL:</td>
                        <td class="value">Rp {{ number_format($purchaseOrder->total_amount * 1.11, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Signatures -->
        <div class="signatures">
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
                <div class="signature-title">Diterima Oleh Supplier</div>
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
            // Show loading
            document.getElementById('loading').style.display = 'block';

            // Option 1: If you have PDF generation route
            try {
                const downloadUrl = `{{ url('purchase-orders/' . $purchaseOrder->po_id . '/download-pdf') }}`;

                // Create temporary link
                const link = document.createElement('a');
                link.href = downloadUrl;
                link.download = `PO_{{ $purchaseOrder->po_number }}_{{ now()->format('Y-m-d') }}.pdf`;
                link.target = '_blank';

                // Trigger download
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

            } catch (error) {
                // Option 2: Fallback to browser print to PDF
                console.log('PDF route not available, using browser print dialog');
                window.print();
            }

            // Hide loading
            setTimeout(() => {
                document.getElementById('loading').style.display = 'none';
            }, 2000);
        }

        // Auto focus for accessibility
        document.addEventListener('DOMContentLoaded', function() {
            // Add keyboard shortcuts
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
        });

        // Handle image loading error
        document.addEventListener('DOMContentLoaded', function() {
            const logo = document.querySelector('.company-logo');
            if (logo) {
                logo.onerror = function() {
                    // Hide logo and adjust layout
                    this.style.display = 'none';

                    // Adjust company info layout when logo is missing
                    const companyInfo = document.querySelector('.company-info');
                    if (companyInfo) {
                        companyInfo.style.gap = '0px';
                    }

                    // Make company name more prominent
                    const companyName = document.querySelector('.company-name');
                    if (companyName) {
                        companyName.style.fontSize = '24px';
                        companyName.style.marginBottom = '5px';
                    }
                };

                // Test if logo loads
                logo.onload = function() {
                    console.log('Logo loaded successfully');
                };
            }
        });
    </script>
</body>
</html>
