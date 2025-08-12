<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Transaction Report - {{ $dateFrom }} to {{ $dateTo }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #333;
        }

        .report-title {
            font-size: 16px;
            color: #666;
            margin-top: 5px;
        }

        .report-period {
            font-size: 12px;
            color: #888;
            margin-top: 5px;
        }

        .stats-container {
            display: table;
            width: 100%;
            margin: 20px 0;
        }

        .stat-card {
            display: table-cell;
            width: 25%;
            padding: 15px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            text-align: center;
            vertical-align: top;
        }

        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }

        .stat-label {
            font-size: 10px;
            color: #666;
            margin-top: 5px;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin: 25px 0 10px 0;
            padding-bottom: 5px;
            border-bottom: 1px solid #ddd;
            color: #333;
        }

        .breakdown-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        .breakdown-table th,
        .breakdown-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .breakdown-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .breakdown-table td.number {
            text-align: right;
        }

        .percentage {
            color: #28a745;
            font-weight: bold;
        }

        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #666;
            text-align: center;
        }

        .damage-section {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }

        .damage-highlight {
            color: #d63031;
            font-weight: bold;
        }

        .flex-container {
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
        }

        .flex-item {
            flex: 1;
            margin: 0 10px;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="company-name">LogistiK System</div>
        <div class="report-title">Transaction Report</div>
        <div class="report-period">
            Period: {{ Carbon\Carbon::parse($dateFrom)->format('d M Y') }} - {{ Carbon\Carbon::parse($dateTo)->format('d M Y') }}
            @if($transactionType)
                | Type: {{ App\Models\Transaction::getTransactionTypes()[$transactionType] ?? $transactionType }}
            @endif
        </div>
        <div class="report-period">
            Generated on: {{ now()->format('d M Y H:i:s') }} by {{ auth()->user()->full_name }}
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="section-title">Executive Summary</div>

    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-value">{{ number_format($stats['totals']['transactions']) }}</div>
            <div class="stat-label">Total Transactions</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ number_format($stats['totals']['pending']) }}</div>
            <div class="stat-label">Pending</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ number_format($stats['totals']['approved']) }}</div>
            <div class="stat-label">Approved</div>
        </div>
        <div class="stat-card">
            <div class="stat-value percentage">{{ $stats['totals']['approval_rate'] }}%</div>
            <div class="stat-label">Approval Rate</div>
        </div>
    </div>

    <!-- Transaction Type Breakdown -->
    <div class="section-title">Transaction Type Breakdown</div>

    <table class="breakdown-table">
        <thead>
            <tr>
                <th>Transaction Type</th>
                <th class="number">Count</th>
                <th class="number">Percentage</th>
            </tr>
        </thead>
        <tbody>
            @php $total = $stats['totals']['transactions']; @endphp
            @foreach($stats['breakdowns']['by_type'] as $type => $count)
            <tr>
                <td>{{ App\Models\Transaction::getTransactionTypes()[$type] ?? $type }}</td>
                <td class="number">{{ number_format($count) }}</td>
                <td class="number">{{ $total > 0 ? round(($count / $total) * 100, 1) : 0 }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Status Breakdown -->
    <div class="section-title">Status Distribution</div>

    <table class="breakdown-table">
        <thead>
            <tr>
                <th>Status</th>
                <th class="number">Count</th>
                <th class="number">Percentage</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stats['breakdowns']['by_status'] as $status => $count)
            <tr>
                <td>{{ App\Models\Transaction::getStatuses()[$status] ?? ucfirst($status) }}</td>
                <td class="number">{{ number_format($count) }}</td>
                <td class="number">{{ $total > 0 ? round(($count / $total) * 100, 1) : 0 }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Damage Analysis (if applicable) -->
    @if(isset($stats['damage_analysis']) && $stats['damage_analysis'] && $stats['damage_analysis']['total_damaged'] > 0)
    <div class="damage-section">
        <div class="section-title" style="border: none; margin-top: 0;">🚨 Damage Analysis</div>

        <div style="margin: 15px 0;">
            <strong>Total Damaged Items:</strong>
            <span class="damage-highlight">{{ number_format($stats['damage_analysis']['total_damaged']) }}</span>
            items
            <br>
            <strong>Total Repair Estimate:</strong>
            <span class="damage-highlight">Rp {{ number_format($stats['damage_analysis']['total_repair_estimate']) }}</span>
        </div>

        <!-- Damage by Level -->
        <table class="breakdown-table" style="margin-top: 15px;">
            <thead>
                <tr>
                    <th>Damage Level</th>
                    <th class="number">Count</th>
                    <th class="number">% of Damaged</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stats['damage_analysis']['by_level'] as $level => $count)
                <tr>
                    <td>{{ App\Models\Transaction::getDamageLevels()[$level] ?? ucfirst($level) }}</td>
                    <td class="number">{{ number_format($count) }}</td>
                    <td class="number">
                        {{ $stats['damage_analysis']['total_damaged'] > 0 ? round(($count / $stats['damage_analysis']['total_damaged']) * 100, 1) : 0 }}%
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Damage by Reason -->
        @if(count($stats['damage_analysis']['by_reason']) > 0)
        <div style="margin-top: 15px;">
            <strong>Top Damage Reasons:</strong>
            <ul style="margin: 5px 0; padding-left: 20px;">
                @foreach(array_slice($stats['damage_analysis']['by_reason'], 0, 5, true) as $reason => $data)
                <li style="margin: 3px 0;">
                    {{ ucfirst(str_replace('_', ' ', $reason)) }}:
                    <strong>{{ $data['count'] }} items</strong>
                    @if($data['total_estimate'] > 0)
                        (Est: Rp {{ number_format($data['total_estimate']) }})
                    @endif
                </li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>
    @endif

    <!-- User Performance (if available) -->
    @if(isset($stats['user_performance']) && $stats['user_performance']->count() > 0)
    <div class="section-title">Top User Performance</div>

    <table class="breakdown-table">
        <thead>
            <tr>
                <th>User</th>
                <th>Level</th>
                <th class="number">Transactions</th>
                <th class="number">Approved</th>
                <th class="number">Success Rate</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stats['user_performance']->take(10) as $user)
            <tr>
                <td>{{ $user->full_name }}</td>
                <td>{{ $user->level_name }}</td>
                <td class="number">{{ number_format($user->total_transactions) }}</td>
                <td class="number">{{ number_format($user->approved_count) }}</td>
                <td class="number percentage">{{ $user->success_rate }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- Key Insights -->
    <div class="section-title">Key Insights</div>

    <div style="background-color: #f8f9fa; padding: 15px; border-left: 4px solid #007bff;">
        <ul style="margin: 0; padding-left: 20px;">
            <li>
                <strong>Approval Efficiency:</strong>
                {{ $stats['totals']['approval_rate'] }}% of transactions are approved
                @if($stats['totals']['approval_rate'] >= 80)
                    - <span style="color: #28a745;">Excellent performance</span>
                @elseif($stats['totals']['approval_rate'] >= 60)
                    - <span style="color: #ffc107;">Good performance</span>
                @else
                    - <span style="color: #dc3545;">Needs improvement</span>
                @endif
            </li>

            @if($stats['totals']['pending'] > 0)
            <li>
                <strong>Pending Items:</strong>
                {{ number_format($stats['totals']['pending']) }} transactions awaiting approval
            </li>
            @endif

            @if(isset($stats['damage_analysis']) && $stats['damage_analysis'] && $stats['damage_analysis']['total_damaged'] > 0)
            <li>
                <strong>Damage Impact:</strong>
                {{ number_format($stats['damage_analysis']['total_damaged']) }} items damaged with
                Rp {{ number_format($stats['damage_analysis']['total_repair_estimate']) }} repair estimate
            </li>
            @endif

            @php
                $mostActiveType = collect($stats['breakdowns']['by_type'])->sortDesc()->keys()->first();
                $mostActiveTypeLabel = App\Models\Transaction::getTransactionTypes()[$mostActiveType] ?? $mostActiveType;
            @endphp
            <li>
                <strong>Most Active Transaction Type:</strong>
                {{ $mostActiveTypeLabel }}
                ({{ number_format($stats['breakdowns']['by_type'][$mostActiveType]) }} transactions)
            </li>
        </ul>
    </div>

    <!-- Recommendations -->
    <div class="section-title">Recommendations</div>

    <div style="background-color: #fff3cd; padding: 15px; border-left: 4px solid #ffc107;">
        <ul style="margin: 0; padding-left: 20px;">
            @if($stats['totals']['approval_rate'] < 80)
            <li>Consider reviewing approval processes to improve efficiency</li>
            @endif

            @if($stats['totals']['pending'] > 10)
            <li>Address pending approvals to improve workflow</li>
            @endif

            @if(isset($stats['damage_analysis']) && $stats['damage_analysis'] && $stats['damage_analysis']['total_damaged'] > 5)
            <li>Implement preventive measures to reduce damage incidents</li>
            <li>Review handling procedures for high-damage items</li>
            @endif

            <li>Monitor transaction patterns for operational optimization</li>
            <li>Regular training for users with low approval rates</li>
        </ul>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div>
            This report was automatically generated by LogistiK System on {{ now()->format('d M Y H:i:s') }}
        </div>
        <div style="margin-top: 5px;">
            Report Period: {{ $stats['period']['days'] ?? 0 }} days |
            Filters Applied: {{ $stats['filters_applied']['transaction_type'] ? 'Type: ' . $stats['filters_applied']['transaction_type'] : 'All Types' }}
        </div>
    </div>
</body>
</html>
