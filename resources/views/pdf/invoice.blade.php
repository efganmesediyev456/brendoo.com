<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <title>Faktura #{{ $item['no'] }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 14px;
            color: #333;
            margin: 40px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #666;
            padding: 8px;
            text-align: center;
        }
        th {
            background: #f0f0f0;
        }
        h2 {
            text-align: center;
        }
    </style>
</head>
<body>
    <h2>Faktura</h2>
    <table>
        <tr>
            <th>№</th>
            <th>Influser</th>
            <th>Tip</th>
            <th>Miqdar</th>
            <th>Status</th>
            <th>Tarix</th>
            <th>Əməliyyat</th>
        </tr>
        <tr>
            <td>{{ $item['no'] }}</td>
            <td>{{ $item['influser'] }}</td>
            <td>{{ $item['tip'] }}</td>
            <td>{{ $item['miqdar'] }}</td>
            <td>{{ $item['status'] }}</td>
            <td>{{ $item['tarix'] }}</td>
            <td>{{ $item['emeliyyat'] ?: '-' }}</td>
        </tr>
    </table>
</body>
</html>
