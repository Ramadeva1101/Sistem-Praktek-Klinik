<!DOCTYPE html>
<html>
<head>
    <title>Laporan Detail Obat</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .footer {
            margin-top: 20px;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Detail Obat</h2>
        <p>Tanggal: {{ $tanggal }}</p>
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
                <th>Status Pembayaran</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $record)
            <tr>
                <td>{{ $record->kode_pelanggan }}</td>
                <td>{{ $record->nama_pasien }}</td>
                <td>{{ $record->kode_obat }}</td>
                <td>{{ $record->nama_obat }}</td>
                <td>{{ $record->jumlah }}</td>
                <td>Rp {{ number_format($record->harga, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($record->total_harga, 0, ',', '.') }}</td>
                <td>{{ \Carbon\Carbon::parse($record->tanggal_kunjungan)->format('d/m/Y H:i') }}</td>
                <td>{{ $record->status_pembayaran }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Total Transaksi: {{ count($records) }}</p>
    </div>
</body>
</html>
