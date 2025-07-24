@extends('layouts.print')

@section('title', 'QR Labels Print')

@push('styles')
<style>
    /* Label container grid */
    .label-container {
        display: grid;
        grid-template-columns: repeat({{ $printConfig['labels_per_row'] }}, 1fr);
        gap: 2mm;
        page-break-inside: avoid;
        margin: 0;
        padding: 0;
    }

    /* Base QR label styles - Enhanced dengan logo */
    .qr-label {
        width: {{ $dimensions['width'] }};
        height: {{ $dimensions['height'] }};
        border: 1px solid #000;
        padding: 1mm;
        display: flex;
        flex-direction: row;
        align-items: center;
        font-family: 'Courier New', 'Consolas', monospace;
        page-break-inside: avoid;
        line-height: 1;
        gap: 1.5mm;
        position: relative;
        background: white;
    }

    /* Logo container - positioned at top right */
    .logo-container {
        position: absolute;
        top: 0.5mm;
        right: 0.5mm;
        z-index: 2;
    }

    .company-logo {
        display: block;
        border: none;
        opacity: 0.8;
    }

    /* QR Code Container */
    .qr-code-container {
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1;
    }

    /* Content Container */
    .content-container {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        text-align: left;
        min-width: 0;
        padding-right: 8mm; /* Space for logo */
    }

    /* QR code image */
    .qr-code {
        display: block;
        border: none;
    }

    /* Kondisi badge styling */
    .kondisi-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.5px 2px;
        border-radius: 2px;
        font-weight: bold;
        margin-top: 0.5mm;
    }

    .kondisi-good {
        background-color: #d4edda;
        color: #155724;
        border: 0.5px solid #c3e6cb;
    }

    .kondisi-no-good {
        background-color: #f8d7da;
        color: #721c24;
        border: 0.5px solid #f5c6cb;
    }

    /* Size-specific adjustments */
    .qr-label.sfp {
        font-size: 6px;
        line-height: 0.9;
        padding: 0.5mm;
        gap: 1mm;
    }

    .qr-label.sfp .qr-code {
        width: 10mm;
        height: 10mm;
    }

    .qr-label.sfp .company-logo {
        width: 6mm;
        height: auto;
        max-height: 4mm;
    }

    .qr-label.sfp .kondisi-badge {
        font-size: 4px;
        padding: 0.2px 1px;
    }

    .qr-label.small {
        font-size: 8px;
        line-height: 1;
    }

    .qr-label.small .qr-code {
        width: 15mm;
        height: 15mm;
    }

    .qr-label.small .company-logo {
        width: 8mm;
        height: auto;
        max-height: 6mm;
    }

    .qr-label.small .kondisi-badge {
        font-size: 6px;
    }

    .qr-label.medium {
        font-size: 10px;
        line-height: 1.1;
    }

    .qr-label.medium .qr-code {
        width: 18mm;
        height: 18mm;
    }

    .qr-label.medium .company-logo {
        width: 10mm;
        height: auto;
        max-height: 8mm;
    }

    .qr-label.medium .kondisi-badge {
        font-size: 8px;
    }

    .qr-label.large {
        font-size: 12px;
        line-height: 1.2;
    }

    .qr-label.large .qr-code {
        width: 22mm;
        height: 22mm;
    }

    .qr-label.large .company-logo {
        width: 12mm;
        height: auto;
        max-height: 10mm;
    }

    .qr-label.large .kondisi-badge {
        font-size: 10px;
    }

    /* Item information */
    .item-info {
        text-align: left;
        line-height: 1.1;
        margin-bottom: 1px;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    /* Serial number styling */
    .serial-number {
        font-weight: bold;
        font-size: 1em;
    }

    /* Item name styling */
    .item-name {
        font-size: 0.9em;
        color: #333;
    }

    /* PO number styling */
    .po-number {
        font-size: 0.8em;
        color: #666;
    }

    /* SFP specific - very compact */
    .qr-label.sfp .item-info {
        font-size: 5px;
        line-height: 0.9;
        margin-bottom: 0.5px;
    }

    .qr-label.sfp .serial-number {
        font-size: 5px;
    }

    .qr-label.sfp .item-name {
        font-size: 4px;
    }

    /* Print controls */
    .print-controls {
        background: #f8f9fa;
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 8px;
        border: 1px solid #dee2e6;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .print-controls h1 {
        font-size: 20px;
        font-weight: bold;
        margin: 0;
        color: #333;
    }

    .print-controls .btn {
        padding: 8px 16px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        transition: all 0.2s;
    }

    .btn-primary {
        background: #007bff;
        color: white;
    }

    .btn-primary:hover {
        background: #0056b3;
    }

    .btn-secondary {
        background: #6c757d;
        color: white;
    }

    .btn-secondary:hover {
        background: #545b62;
    }

    .print-info {
        background: white;
        padding: 15px;
        border-radius: 6px;
        border: 1px solid #dee2e6;
        margin-bottom: 20px;
    }

    .print-info p {
        margin: 0;
        font-size: 14px;
        color: #666;
    }

    @media print {
        .label-container {
            page-break-after: auto;
        }

        .qr-label {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        .no-print {
            display: none !important;
        }
    }

    @media screen and (max-width: 600px) {
        .label-container {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>
@endpush

@section('content')
<!-- Print Controls (Hidden when printing) -->
<div class="no-print print-controls">
    <div>
        <h1>QR Labels Preview</h1>
    </div>
    <div style="display: flex; gap: 10px;">
        <button class="btn btn-primary" onclick="window.print();">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/>
            </svg>
            Print
        </button>
        <button class="btn btn-secondary" onclick="window.close();">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8 2.146 2.854Z"/>
            </svg>
            Close
        </button>
    </div>
</div>

<!-- Print Information (Hidden when printing) -->
<div class="no-print print-info">
    <p>
        <strong>Configuration:</strong>
        {{ ucfirst($printConfig['label_size']) }} Labels ({{ $dimensions['width'] }} × {{ $dimensions['height'] }}) •
        {{ $printConfig['labels_per_row'] }} per row •
        {{ $itemDetails->count() }} total items

        @if($printConfig['include_item_name'])
            • Item names included
        @endif

        @if($printConfig['include_serial'])
            • Serial numbers included
        @endif

        @if($printConfig['include_po'])
            • PO numbers included
        @endif

        • With company logo • Kondisi included
    </p>
</div>

<!-- QR Labels Grid -->
<div class="label-container">
    @foreach($itemDetails as $item)
        <div class="qr-label {{ $printConfig['label_size'] }}">
            <!-- Company Logo (Top Right) -->
            <div class="logo-container">
                <img class="company-logo" src="{{ asset('f1.png') }}" alt="Company Logo">
            </div>

            <!-- QR Code Container (Left Side) -->
            <div class="qr-code-container">
                <img class="qr-code" src="{{ asset('storage/qr-codes/item-details/' . $item->qr_code) }}" alt="QR Code">
            </div>

            <!-- Content Container (Right Side) -->
            <div class="content-container">
                <!-- Serial Number (always shown if enabled) -->
                @if($printConfig['include_serial'])
                    <div class="item-info serial-number">{{ $item->serial_number }}</div>
                    <div class="item-info serial-number">{{ $item->item_detail_id }}</div>
                @endif

                <!-- Item Name (conditional based on size) -->
                @if($printConfig['include_item_name'])
                    @if($printConfig['label_size'] !== 'sfp')
                        <div class="item-info item-name">
                            {{ Str::limit($item->item->item_name, $printConfig['label_size'] === 'small' ? 25 : ($printConfig['label_size'] === 'medium' ? 35 : 45)) }}
                        </div>
                    @else
                        <!-- For SFP, show very short item code instead -->
                        <div class="item-info item-name">{{ Str::limit($item->item->item_code, 10) }}</div>
                    @endif
                @endif

                <!-- PO Number (only for medium+ sizes) -->
                @if($printConfig['include_po'] && $item->goodsReceivedDetail && in_array($printConfig['label_size'], ['medium', 'large']))
                    <div class="item-info po-number">
                        PO: {{ $item->goodsReceivedDetail->goodsReceived->purchaseOrder->po_number ?? 'N/A' }}
                    </div>
                @endif

                <!-- Kondisi Badge -->
                @if($item->kondisi)
                    <div class="kondisi-badge {{ $item->kondisi == 'good' ? 'kondisi-good' : 'kondisi-no-good' }}">
                        {{ $item->kondisi == 'good' ? 'GOOD' : 'NO GOOD' }}
                    </div>
                @endif
            </div>
        </div>
    @endforeach
</div>

<!-- Print Footer (Hidden when printing) -->
<div class="no-print" style="margin-top: 30px; text-align: center; color: #666; font-size: 12px;">
    <p>Generated on {{ now()->format('d/m/Y H:i:s') }} • LogistiK Admin System</p>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-adjust logo and QR code sizes
    function adjustSizes() {
        const labels = document.querySelectorAll('.qr-label');
        labels.forEach(label => {
            const logo = label.querySelector('.company-logo');
            const qrCode = label.querySelector('.qr-code');

            // Logo error handling
            if (logo) {
                logo.onload = function() {
                    console.log('Logo loaded successfully');
                };
                logo.onerror = function() {
                    console.error('Failed to load logo: f1.png');
                    // Hide logo container if image fails
                    logo.parentElement.style.display = 'none';
                    // Adjust content padding when logo is hidden
                    const contentContainer = label.querySelector('.content-container');
                    if (contentContainer) {
                        contentContainer.style.paddingRight = '1mm';
                    }
                };
            }

            // QR Code error handling
            if (qrCode) {
                qrCode.onload = function() {
                    console.log('QR code loaded successfully');
                };
                qrCode.onerror = function() {
                    console.error('Failed to load QR code:', qrCode.src);
                };
            }
        });
    }

    adjustSizes();

    // Print debugging
    const labelCount = document.querySelectorAll('.qr-label').length;
    const labelsPerRow = {{ $printConfig['labels_per_row'] }};
    console.log(`Printing ${labelCount} labels in ${labelsPerRow} columns with logo`);

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
            e.preventDefault();
            window.print();
        }
        if (e.key === 'Escape') {
            if (window.opener) {
                window.close();
            } else {
                history.back();
            }
        }
    });

    // Print event handlers
    window.addEventListener('beforeprint', function() {
        console.log('Starting print process with logo and kondisi...');

        // Ensure all elements are properly sized before printing
        const qrCodes = document.querySelectorAll('.qr-code');
        const logos = document.querySelectorAll('.company-logo');

        console.log(`QR Codes: ${qrCodes.length}, Logos: ${logos.length}`);
    });

    window.addEventListener('afterprint', function() {
        console.log('Print dialog closed');
    });
});
</script>
@endpush
