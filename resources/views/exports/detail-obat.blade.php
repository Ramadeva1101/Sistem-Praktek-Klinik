<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Detail Obat</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .subtitle {
            font-size: 16px;
            margin-bottom: 5px;
        }
        .date {
            font-size: 12px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10px;
        }
        th {
            background-color: #E2EFDA;
            padding: 8px;
            text-align: center;
            border: 1px solid #000;
            font-weight: bold;
        }
        td {
            padding: 6px;
            text-align: center;
            border: 1px solid #000;
        }
        .total {
            margin-top: 20px;
            text-align: right;
            font-weight: bold;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: right;
            font-size: 10px;
            padding: 10px 0;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">LAPORAN DETAIL OBAT</div>
        <div class="subtitle">Bamboomedia</div>
        <div class="date">Periode: {{ $periode_awal }} s/d {{ $periode_akhir }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Kode Pelanggan</th>
                <th>Nama Pasien</th>
                <th>Kode Obat</th>
                <th>Nama Obat</th>
                <th>Jumlah</th>
                <th>Harga</th>
                <th>Total Harga</th>
                <th>Tanggal Kunjungan</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $record)
            <tr>
                <td>{{ $record->kode_pelanggan }}</td>
                <td>{{ $record->nama_pasien }}</td>
                <td>{{ $record->kode_obat }}</td>
                <td>{{ $record->nama_obat }}</td>
                <td style="text-align: right;">{{ $record->jumlah }}</td>
                <td style="text-align: right;">Rp {{ number_format($record->harga, 0, ',', '.') }}</td>
                <td style="text-align: right;">Rp {{ number_format($record->total_harga, 0, ',', '.') }}</td>
                <td>{{ $record->tanggal_kunjungan->format('d/m/Y H:i') }}</td>
                <td>{{ $record->status_pembayaran }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total">
        Total Keseluruhan: Rp {{ number_format($records->sum('total_harga'), 0, ',', '.') }}
    </div>

    <div class="footer">
        Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
