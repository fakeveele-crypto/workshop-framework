<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sertifikat {{ $nama }}</title>
    <style>
        /* Pengaturan Kertas dan Margin */
        @page { 
            margin: 0; 
        }
        
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            width: 100%;
            font-family: 'Helvetica', 'Arial', sans-serif;
        }

        /* Container Utama */
        .certificate-container {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: block;
            box-sizing: border-box;
        }

        /* Bingkai/Border Biru Tua */
        .border-frame {
            position: absolute;
            top: 30px;
            left: 30px;
            right: 30px;
            bottom: 30px;
            border: 12px solid #1a5f7a;
            padding: 40px;
            box-sizing: border-box;
            text-align: center;
            background-color: #ffffff;
        }

        /* Konten Sertifikat */
        .title {
            font-size: 50px;
            font-weight: bold;
            color: #1a5f7a;
            margin-top: 40px;
            text-transform: uppercase;
        }

        .subtitle {
            font-size: 20px;
            margin-top: 10px;
            color: #555;
            letter-spacing: 2px;
        }

        .presented-to {
            font-size: 18px;
            margin-top: 40px;
            font-style: italic;
        }

        .user-name {
            font-size: 45px;
            font-weight: bold;
            color: #000;
            margin: 25px 0;
            text-decoration: underline;
            text-transform: capitalize;
        }

        .description {
            font-size: 17px;
            line-height: 1.6;
            margin: 0 60px;
            color: #333;
        }

        .app-name {
            font-weight: bold;
            color: #1a5f7a;
        }

        /* Tanda Tangan / Footer */
        .footer {
            position: absolute;
            bottom: 60px;
            right: 80px;
            text-align: right;
        }

        .date {
            font-size: 16px;
            margin-bottom: 50px;
        }

        .signature-name {
            font-size: 18px;
            font-weight: bold;
            border-top: 1px solid #000;
            padding-top: 5px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="certificate-container">
        <div class="border-frame">
            <div class="title">Sertifikat Apresiasi</div>
            <div class="subtitle">Digital Achievement Award</div>

            <p class="presented-to">Sertifikat ini diberikan dengan bangga kepada:</p>
            <div class="user-name">{{ $nama }}</div> <p class="description">
                Atas dedikasi dan kontribusi aktifnya dalam mengelola data literasi serta 
                mengoptimalkan fitur pada sistem aplikasi <span class="app-name">Koleksi Buku Digital Valerina</span>. 
                Prestasi ini merupakan bentuk pengakuan atas semangat belajar dalam pengembangan teknologi web.
            </p>

            <div class="footer">
                <p class="date">Surabaya, {{ $tanggal }}</p>
                <div class="signature-name">Admin Koleksi Buku</div>
            </div>
        </div>
    </div>
</body>
</html>