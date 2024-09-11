<!DOCTYPE html>
<html>
<head>
    <style>
        .page {
            width: 210mm;
            height: 297mm;
            position: relative;
        }
        .qrcode {
            position: absolute;
            width: 91mm; /* ここで画像の幅を指定 */
            height: 55mm; /* ここで画像の高さを指定 */
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .qrcode img {
            max-width: 100%; /* 親要素の幅に合わせる */
            max-height: 100%; /* 親要素の高さに合わせる */
            object-fit: contain; /* 画像を縮小して親要素に収める */
        }
        /* 各QRコードの位置を指定 */
        .qrcode:nth-child(1) { left: 14mm; top: 11mm; }
        .qrcode:nth-child(2) { left: 14mm; top: 66mm; }
        .qrcode:nth-child(3) { left: 14mm; top: 121mm; }
        .qrcode:nth-child(4) { left: 14mm; top: 176mm; }
        .qrcode:nth-child(5) { left: 14mm; top: 231mm; }
        .qrcode:nth-child(6) { left: 105mm; top: 11mm; }
        .qrcode:nth-child(7) { left: 105mm; top: 66mm; }
        .qrcode:nth-child(8) { left: 105mm; top: 121mm; }
        .qrcode:nth-child(9) { left: 105mm; top: 176mm; }
        .qrcode:nth-child(10) { left: 105mm; top: 231mm; }
    </style>
</head>
<body>
    <div class="page">
        @foreach ($qrCodes as $qrCode)
            <div class="qrcode">
                <img src="{{ $qrCode }}" alt="QR Code">
            </div>
        @endforeach
    </div>
</body>
</html>
