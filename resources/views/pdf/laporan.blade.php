<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Times New Roman', serif; margin: 30px; font-size: 12pt; }
        .header { 
            text-align: center; 
            border-bottom: 3px double #000; 
            padding-bottom: 10px; 
            margin-bottom: 20px; 
        }
        .header h2 { margin: 0; font-size: 16pt; }
        .header p { margin: 0; font-size: 10pt; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
        .title-doc { text-align: center; font-weight: bold; text-decoration: underline; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>PERPUSTAKAAN DIGITAL KOLEKSI BUKU</h2>
        <p>Fakultas Vokasi - Universitas Airlangga [cite: 41, 55]</p>
        <p>Kampus B Jl. Dharmawangsa Dalam Surabaya | Telp: (031) 5033869 [cite: 42]</p>
    </div>

    <div class="title-doc">LAPORAN DATA KOLEKSI BUKU USER</div>

    <p>Yth. {{ $nama_user }},</p> <p>Berikut adalah daftar buku yang tersimpan dalam koleksi pribadi Anda di sistem kami:</p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Judul Buku</th>
                <th>Kategori</th>
            </tr>
        </thead>
        <tbody>
            @foreach($koleksi as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item['judul'] }}</td>
                <td>{{ $item['kategori'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p style="margin-top: 30px;">
        Demikian laporan ini dibuat untuk dipergunakan sebagaimana mestinya. 
        Terima kasih telah menggunakan layanan Koleksi Buku.
    </p>
</body>
</html>