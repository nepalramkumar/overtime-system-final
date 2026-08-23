<!DOCTYPE html>
<html lang="ne">
<head>
    <meta charset="UTF-8">
    <title>Repair Expense - {{ $employee->name ?? 'N/A' }}</title>
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }

        * {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.4;
            background-color: white;
        }

        .bill-header {
            text-align: center;
            margin-bottom: 10px;
        }

        .bill-header h2 {
            font-size: 15px;
        }

        .bill-box {
            width: 40%;
        }

        .bill-info {
            margin-bottom: 10px;
        }

        .bill-info-item strong {
            font-size: 12px;
            color: #333;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 5px;
            font-size: 12px;
        }

        th,
        td {
            border: 1px solid #e0e0e0;
            padding: 6px;
            text-align: center;
        }

        th {
            background-color: #f5f7fa;
            color: #333;
            font-weight: 600;
        }

        td.particulars {
            text-align: left;
        }

        .bill-summary {
            margin-top: 40px;
        }
    </style>
</head>
<body>
    <div class="bill-header">
        <h2>Repair Expense Ledger — FY {{ $expense->fy_year }}</h2>
    </div>

    <div class="bill-box">
        <div class="bill-info">
            <div class="bill-info-item">
                <strong>Name:</strong>
                <span>{{ $employee->name ?? 'N/A' }}({{ $employee->vehicle_no ?? '' }})</span>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>SN</th>
                    <th>Date</th>
                    <th>Particulars</th>
                    <th>Bill Amount</th>
                    <th>Balance Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>{{ $openingDateBs }}</td>
                    <td class="particulars">Balance</td>
                    <td>0</td>
                    <td>{{ number_format($openingBalance, 0) }}</td>
                </tr>
                @foreach($ledgerRows as $i => $row)
                <tr>
                    <td>{{ $i + 2 }}</td>
                    <td>{{ $row['date'] }}</td>
                    <td class="particulars">{{ $row['particulars'] }}</td>
                    <td>{{ number_format($row['bill_amount'], 0) }}</td>
                    <td>{{ number_format($row['balance'], 0) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="bill-summary">
            <p><strong>Signature:</strong> ______________________</p>
        </div>
    </div>
</body>
</html>