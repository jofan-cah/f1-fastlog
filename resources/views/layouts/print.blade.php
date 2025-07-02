<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Print - LogistiK Admin')</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">

    <!-- Print Specific Styles -->
    <style>
        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            width: 100%;
            height: 100%;
            font-family: 'Arial', 'Helvetica', sans-serif;
            font-size: 12px;
            line-height: 1.2;
            color: #000;
            background: #fff;
        }

        /* Print-specific styles */
        @media print {
            html, body {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                height: 100% !important;
                overflow: visible !important;
            }

            /* Hide elements that shouldn't print */
            .no-print,
            .no-print * {
                display: none !important;
                visibility: hidden !important;
            }

            /* Remove any shadows, backgrounds for clean print */
            * {
                background: transparent !important;
                box-shadow: none !important;
                text-shadow: none !important;
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
            }

            /* Page settings */
            @page {
                margin: 5mm;
                size: A4;
            }

            /* Avoid page breaks inside important elements */
            .page-break-avoid {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            /* Force page breaks */
            .page-break {
                page-break-before: always !important;
                break-before: page !important;
            }
        }

        /* Screen preview styles */
        @media screen {
            body {
                background: #f5f5f5;
                padding: 20px;
            }

            .print-container {
                background: white;
                max-width: 210mm; /* A4 width */
                margin: 0 auto;
                padding: 10mm;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
                border-radius: 4px;
            }

            .no-print {
                background: #f8f9fa;
                padding: 15px;
                margin-bottom: 20px;
                border-radius: 8px;
                border: 1px solid #dee2e6;
            }
        }

        /* Common print elements */
        .print-header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .print-header h1 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .print-header .subtitle {
            font-size: 12px;
            color: #666;
        }

        .print-date {
            text-align: right;
            font-size: 10px;
            margin-bottom: 10px;
        }

        /* Table styles for print */
        .print-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .print-table th,
        .print-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            text-align: left;
            font-size: 10px;
        }

        .print-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        /* QR Label specific styles */
        .qr-label-container {
            display: grid;
            gap: 2mm;
            page-break-inside: avoid;
        }

        .qr-label {
            border: 1px solid #000;
            padding: 1mm;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            page-break-inside: avoid;
            font-family: 'Courier New', 'Consolas', monospace;
        }

        .qr-code-text {
            font-weight: bold;
            word-break: break-all;
            line-height: 1;
        }

        .qr-info {
            margin-top: 1px;
            font-size: smaller;
            line-height: 1;
        }

        /* Utility classes */
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .font-mono { font-family: 'Courier New', 'Consolas', monospace; }
        .mb-1 { margin-bottom: 4px; }
        .mb-2 { margin-bottom: 8px; }
        .mb-3 { margin-bottom: 12px; }
        .mb-4 { margin-bottom: 16px; }
        .mt-1 { margin-top: 4px; }
        .mt-2 { margin-top: 8px; }
        .mt-3 { margin-top: 12px; }
        .mt-4 { margin-top: 16px; }
    </style>

    @stack('styles')
</head>
<body>
    <div class="print-container">
        @yield('content')
    </div>

    @stack('scripts')

    <!-- Auto print functionality -->
    <script>
        // Print controls for screen view
        document.addEventListener('DOMContentLoaded', function() {
            // Add print button functionality
            const printButtons = document.querySelectorAll('[data-print]');
            printButtons.forEach(button => {
                button.addEventListener('click', function() {
                    window.print();
                });
            });

            // Add close button functionality
            const closeButtons = document.querySelectorAll('[data-close]');
            closeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    if (window.opener) {
                        window.close();
                    } else {
                        history.back();
                    }
                });
            });

            // Auto print if specified
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('auto_print') === 'true') {
                setTimeout(() => {
                    window.print();
                }, 500);
            }
        });

        // Print event listeners
        window.addEventListener('beforeprint', function() {
            console.log('Preparing to print...');
        });

        window.addEventListener('afterprint', function() {
            console.log('Print dialog closed');
            // Optionally close window after print
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('close_after_print') === 'true') {
                setTimeout(() => {
                    window.close();
                }, 1000);
            }
        });
    </script>
</body>
</html>
