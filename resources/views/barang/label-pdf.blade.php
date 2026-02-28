<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Label Barang</title>
    <style>
        @page {
            size: 210mm 210mm;
            margin: 0;
        }

        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
        }

        .sheet {
            position: relative;
            width: 210mm;
            height: 210mm;
        }

        .label {
            position: absolute;
            width: 38mm;
            height: 18mm;
            box-sizing: border-box;
            padding: 1.4mm 1.8mm;
            overflow: hidden;
        }

        .nama {
            font-size: 8.5pt;
            font-weight: bold;
            line-height: 1.2;
            margin: 0 0 0.8mm 0;
        }

        .harga {
            font-size: 7.5pt;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="sheet">
        @foreach($cells as $index => $cell)
            @php
                $row = intdiv($index, 5);
                $col = $index % 5;
                $left = 6.5 + ($col * 40);
                $top = 7 + ($row * 25);
            @endphp
            <div class="label" style="left: {{ $left }}mm; top: {{ $top }}mm;">
                @if($cell)
                    <p class="nama">{{ $cell->nama ?? '-' }}</p>
                    <p class="harga">Rp {{ number_format((float) ($cell->harga ?? 0), 0, ',', '.') }}</p>
                @endif
            </div>
        @endforeach
    </div>
</body>
</html>
