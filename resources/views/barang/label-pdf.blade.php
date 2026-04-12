<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Label Barang</title>
    <style>
        @page {
            size: 210mm 165mm;
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', 'DejaVu Sans', Arial, sans-serif;
            background: white;
        }

        .sheet {
            position: relative;
            width: 210mm;
            height: 165mm;
            background: white;
        }

        .label {
            position: absolute;
            width: 38mm;
            height: 18mm;
            background: linear-gradient(135deg, #ffffff 0%, #f9f9f9 100%);
            border-radius: 1.5mm;
            box-shadow: inset 0 0 2mm rgba(0,0,0,0.02);
            padding: 1mm 1.3mm;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            gap: 0.4mm;
        }

        .barcode-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 7.5mm;
            flex-shrink: 0;
            background: white;
            border: 0.5px solid #e8e8e8;
            border-radius: 0.8mm;
            padding: 0.3mm 0.5mm;
        }

        .barcode-wrapper img {
            height: 100%;
            max-width: 100%;
            object-fit: contain;
            display: block;
        }

        .label-content {
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 0.3mm;
            flex: 1;
            min-height: 0;
        }

        .divider {
            height: 0.5px;
            background: linear-gradient(90deg, transparent, #ddd, transparent);
            margin: 0.2mm 0;
        }

        .nama {
            font-size: 6.8pt;
            font-weight: 600;
            line-height: 1.2;
            margin: 0;
            color: #1a1a1a;
            text-align: center;
            white-space: normal;
            word-wrap: break-word;
            max-height: 1.8em;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .harga-wrapper {
            text-align: center;
            padding-top: 0.1mm;
        }

        .harga {
            font-size: 6.5pt;
            font-weight: 700;
            margin: 0;
            color: #d32f2f;
            letter-spacing: 0.3px;
            text-shadow: 0 0.5px 1px rgba(255,255,255,0.5);
        }

        .price-label {
            font-size: 4pt;
            color: #999;
            font-weight: 400;
            margin: 0;
            letter-spacing: 0.2px;
        }
    </style>
</head>
<body>
    <div class="sheet">
        @foreach($cells as $index => $cell)
            @php
                $row = intdiv($index, 5);
                $col = $index % 5;
                $left = 2.5 + ($col * 40);
                $top = 1 + ($row * 20);
            @endphp
            <div class="label" style="left: {{ $left }}mm; top: {{ $top }}mm;">
                @if($cell)
                    <div class="barcode-wrapper">
                        @if(isset($cell->barcode_data_uri) && $cell->barcode_data_uri)
                            <img src="{{ $cell->barcode_data_uri }}" alt="Barcode">
                        @endif
                    </div>

                    <div class="divider"></div>

                    <div class="label-content">
                        <p class="nama">{{ $cell->nama ?? '-' }}</p>
                        <div class="harga-wrapper">
                            <p class="price-label">Harga</p>
                            <p class="harga">Rp {{ number_format((float) ($cell->harga ?? 0), 0, ',', '.') }}</p>
                        </div>
                    </div>
                @else
                    <div style="width: 100%; height: 100%;"></div>
                @endif
            </div>
        @endforeach
    </div>
</body>
</html>
