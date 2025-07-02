<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Goods Received - {{ $goodsReceived->receive_number }}</title>
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
            background: #10b981;
            color: white;
        }

        .btn-print:hover {
            background: #059669;
            transform: translateY(-2px);
        }

        .btn-download {
            background: #3b82f6;
            color: white;
        }

        .btn-download:hover {
            background: #2563eb;
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
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 3px solid #10b981;
            position: relative;
        }

        .company-info {
            flex: 1;
            display: flex;
            align-items: flex-start;
            gap: 15px;
            max-width: 60%;
        }

        .company-logo {
            width: 70px;
            height: 70px;
            object-fit: contain;
            flex-shrink: 0;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 5px;
            background: #fff;
        }

        .company-details {
            flex: 1;
            min-width: 0;
        }

        .company-name {
            font-size: 22px;
            font-weight: 900;
            color: #10b981;
            margin-bottom: 3px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .company-tagline {
            font-size: 10px;
            color: #4b5563;
            margin-bottom: 8px;
            font-style: italic;
            font-weight: 500;
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

        .gr-title {
            text-align: center;
            flex-shrink: 0;
            min-width: 200px;
            padding-left: 20px;
        }

        .gr-title h1 {
            font-size: 26px;
            font-weight: 900;
            color: #10b981;
            margin-bottom: 8px;
            letter-spacing: 1px;
        }

        .gr-number {
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

        /* GR Info Grid */
        .gr-info {
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

        .status-partial {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-complete {
            background-color: #d1fae5;
            color: #065f46;
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
            border-bottom: 2px solid #10b981;
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
            background-color: #10b981 !important;
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

            .gr-title h1 {
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

        /* Progress Bar for Stock Allocation */
        .progress-bar {
            width: 100%;
            height: 8px;
            background-color: #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 4px;
        }

        .progress-fill-stock {
            height: 100%;
            background-color: #3b82f6;
            transition: width 0.3s ease;
        }

        .progress-fill-ready {
            height: 100%;
            background-color: #10b981;
            transition: width 0.3s ease;
        }

        .allocation-info {
            font-size: 10px;
            color: #059669;
            margin-top: 2px;
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
        <button onclick="printGR()" class="btn btn-print">
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
        <a href="{{ route('goods-received.show', $goodsReceived->gr_id) }}" class="btn btn-back">
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
            <div class="gr-title">
                <h1>GOODS RECEIVED</h1>
                <div class="gr-number">{{ $goodsReceived->receive_number }}</div>
                <div class="qr-code">
                    <div>QR CODE</div>
                    <small>{{ $goodsReceived->receive_number }}</small>
                </div>
            </div>
        </div>

        <!-- GR Information -->
        <div class="gr-info">
            <div class="info-section">
                <h3>Informasi Penerimaan Barang</h3>
                <div class="info-row">
                    <span class="info-label">Nomor GR:</span>
                    <span class="info-value">{{ $goodsReceived->receive_number }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tanggal Terima:</span>
                    <span class="info-value">{{ $goodsReceived->receive_date->format('d/m/Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Jam Terima:</span>
                    <span class="info-value">{{ $goodsReceived->receive_date->format('H:i') }} WIB</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="info-value">
                        <span class="status-badge status-{{ $goodsReceived->status }}">
                            {{ $goodsReceived->getStatusInfo()['text'] }}
                        </span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Diterima Oleh:</span>
                    <span class="info-value">{{ $goodsReceived->receivedBy->full_name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Dibuat:</span>
                    <span class="info-value">{{ $goodsReceived->created_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>

            <div class="info-section">
                <h3>Informasi Purchase Order</h3>
                <div class="info-row">
                    <span class="info-label">Nomor PO:</span>
                    <span class="info-value">{{ $goodsReceived->purchaseOrder->po_number }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tanggal PO:</span>
                    <span class="info-value">{{ $goodsReceived->purchaseOrder->po_date->format('d/m/Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Supplier:</span>
                    <span class="info-value">{{ $goodsReceived->supplier->supplier_name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Kode Supplier:</span>
                    <span class="info-value">{{ $goodsReceived->supplier->supplier_code }}</span>
                </div>
                @if($goodsReceived->supplier->contact_person)
                <div class="info-row">
                    <span class="info-label">Contact Person:</span>
                    <span class="info-value">{{ $goodsReceived->supplier->contact_person }}</span>
                </div>
                @endif
                @if($goodsReceived->supplier->phone)
                <div class="info-row">
                    <span class="info-label">Telepon:</span>
                    <span class="info-value">{{ $goodsReceived->supplier->phone }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Items Section -->
        <div class="items-section">
            <h2 class="section-title">Detail Items yang Diterima</h2>
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 5%">No</th>
                        <th style="width: 30%">Nama Item</th>
                        <th style="width: 8%">Unit</th>
                        <th style="width: 10%">Qty Terima</th>
                        <th style="width: 12%">Alokasi Stok</th>
                        <th style="width: 12%">Siap Pakai</th>
                        <th style="width: 12%">Harga Satuan</th>
                        <th style="width: 11%">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($goodsReceived->grDetails as $index => $detail)
                        @php
                            $splitInfo = $detail->getSplitInfo();
                        @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>
                            <div class="item-name">{{ $detail->item->item_name }}</div>
                            <div class="item-code">{{ $detail->item->item_code }}</div>
                            @if($detail->item->category)
                                <div class="item-code">Kategori: {{ $detail->item->category->category_name }}</div>
                            @endif

                            @if($detail->batch_number)
                                <div class="item-code">Batch: {{ $detail->batch_number }}</div>
                            @endif
                            @if($detail->expiry_date)
                                <div class="item-code">Exp: {{ $detail->expiry_date->format('d/m/Y') }}</div>
                            @endif
                        </td>
                        <td class="text-center">{{ $detail->item->unit }}</td>
                        <td class="text-right">{{ number_format($detail->quantity_received, 2) }}</td>
                        <td class="text-center">
                            <div class="text-right">{{ number_format($detail->quantity_to_stock, 2) }}</div>
                            <div class="progress-bar">
                                <div class="progress-fill-stock" style="width: {{ $splitInfo['stock_percentage'] }}%"></div>
                            </div>
                            <div class="allocation-info">{{ number_format($splitInfo['stock_percentage'], 1) }}%</div>
                        </td>
                        <td class="text-center">
                            <div class="text-right">{{ number_format($detail->quantity_to_ready, 2) }}</div>
                            <div class="progress-bar">
                                <div class="progress-fill-ready" style="width: {{ $splitInfo['ready_percentage'] }}%"></div>
                            </div>
                            <div class="allocation-info">{{ number_format($splitInfo['ready_percentage'], 1) }}%</div>
                        </td>
                        <td class="text-right">Rp {{ number_format($detail->unit_price, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($detail->getTotalValue(), 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Summary -->
            <div class="summary">
                <table class="summary-table">
                    <tr>
                        <td class="label">Total Item:</td>
                        <td class="value">{{ $goodsReceived->grDetails->count() }} item</td>
                    </tr>
                    <tr>
                        <td class="label">Total Quantity:</td>
                        <td class="value">{{ number_format($summaryInfo['total_quantity'], 2) }}</td>
                    </tr>
                    <tr>
                        <td class="label">Total ke Stok:</td>
                        <td class="value">{{ number_format($summaryInfo['total_to_stock'], 2) }}</td>
                    </tr>
                    <tr>
                        <td class="label">Total Siap Pakai:</td>
                        <td class="value">{{ number_format($summaryInfo['total_to_ready'], 2) }}</td>
                    </tr>
                    <tr class="total-row">
                        <td class="label">GRAND TOTAL:</td>
                        <td class="value">Rp {{ number_format($summaryInfo['total_value'], 0, ',', '.') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Notes Section -->
        @if($goodsReceived->notes)
        <div class="notes-section">
            <h3 class="section-title">Catatan</h3>
            <div class="notes-content">
                {{ $goodsReceived->notes }}
            </div>
        </div>
        @endif

        <!-- Terms & Conditions -->
        <div class="notes-section">
            <h3 class="section-title">Ketentuan Penerimaan</h3>
            <div class="notes-content">
                <ul style="margin-left: 20px; color: #374151; font-style: normal;">
                    <li>Barang telah diperiksa sesuai dengan spesifikasi yang diminta.</li>
                    <li>Quantity yang diterima sesuai dengan yang tercantum dalam dokumen ini.</li>
                    <li>Kondisi barang dalam keadaan baik dan tidak ada kerusakan.</li>
                    <li>Alokasi stok dan siap pakai sudah disesuaikan dengan kebutuhan operasional.</li>
                    <li>Dokumen ini merupakan bukti sah penerimaan barang dari supplier.</li>
                    <li>Jika terdapat ketidaksesuaian, harap hubungi bagian procurement dalam 24 jam.</li>
                </ul>
            </div>
        </div>

        <!-- Signatures -->
        <div class="signatures">
            <div class="signature-box">
                <div class="signature-title">Diterima Oleh</div>
                <div class="signature-line"></div>
                <div class="signature-name">{{ $goodsReceived->receivedBy->full_name }}</div>
                <div class="signature-date">{{ $goodsReceived->receive_date->format('d/m/Y') }}</div>
            </div>

            <div class="signature-box">
                <div class="signature-title">Diperiksa Oleh</div>
                <div class="signature-line"></div>
                <div class="signature-name">Quality Control</div>
                <div class="signature-date">Tanggal: ___/___/______</div>
            </div>

            <div class="signature-box">
                <div class="signature-title">Diserahkan Oleh</div>
                <div class="signature-line"></div>
                <div class="signature-name">{{ $goodsReceived->supplier->supplier_name }}</div>
                <div class="signature-date">Tanggal: ___/___/______</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Dokumen ini dicetak secara otomatis pada {{ now()->format('d/m/Y H:i:s') }}</p>
            <p>Goods Received ID: {{ $goodsReceived->gr_id }} | PT FiberOne - Warehouse Department</p>
        </div>
    </div>

    <script>
        // Print function
        function printGR() {
            window.print();
        }

        // Download PDF function
        function downloadPDF() {
            // Show loading
            document.getElementById('loading').style.display = 'block';

            // Option 1: If you have PDF generation route
            try {
                const downloadUrl = `{{ url('goods-received/' . $goodsReceived->gr_id . '/download-pdf') }}`;

                // Create temporary link
                const link = document.createElement('a');
                link.href = downloadUrl;
                link.download = `GR_{{ $goodsReceived->receive_number }}_{{ now()->format('Y-m-d') }}.pdf`;
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
                    printGR();
                }

                // Ctrl + D for download
                if (e.ctrlKey && e.key === 'd') {
                    e.preventDefault();
                    downloadPDF();
                }

                // Escape to go back
                if (e.key === 'Escape') {
                    window.location.href = '{{ route("goods-received.show", $goodsReceived->gr_id) }}';
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

        // Print-specific optimizations
        window.addEventListener('beforeprint', function() {
            // Hide any elements that shouldn't be printed
            const printControls = document.querySelector('.print-controls');
            if (printControls) {
                printControls.style.display = 'none';
            }
        });

        window.addEventListener('afterprint', function() {
            // Restore elements after printing
            const printControls = document.querySelector('.print-controls');
            if (printControls) {
                printControls.style.display = 'flex';
            }
        });

        // Auto-resize for different paper sizes
        function adjustForPaperSize() {
            const container = document.querySelector('.container');
            const printMedia = window.matchMedia('print');

            if (printMedia.matches) {
                // Adjust for print
                container.style.maxWidth = 'none';
                container.style.padding = '0';
            }
        }

        // Call on load and when print media query changes
        adjustForPaperSize();
        window.matchMedia('print').addListener(adjustForPaperSize);
    </script>
</body>
</html>
