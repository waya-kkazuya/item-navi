<!DOCTYPE html>
<html>
    <head>
        <style>
        body, html {
            margin: 0;
            padding: 0;
            width: 210mm;
            height: 297mm;
        }
        .page {
            display: flex;
            flex-direction: column;
            justify-content: center;
            width: 100%;
            height: 100%;
            border: 1px solid #000; /* デバッグ用の枠線 */
        }
        .row {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            height: 55mm;
            box-sizing: border-box; /* 枠線を含めたサイズ計算 */
            margin: 0; /* 余白をリセット */
            padding: 0; /* パディングをリセット */
        }
        .qrcode {
            width: 91mm; /* 画像の幅 */
            height: 55mm; /* 画像の高さ */
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0; /* 余白をリセット */
            padding: 0; /* パディングをリセット */
            border: 1px solid #000; /* デバッグ用の枠線 */
        }
        .qrcode img {
            max-width: 100%; /* 親要素の幅に合わせる */
            max-height: 100%; /* 親要素の高さに合わせる */
            object-fit: contain; /* 画像を縮小して親要素に収める */
        }
        </style>
    </head>
    <body>
        <div class="page">
            @foreach ($qrCodePairs as $pair)
                <div class="row">
                    @foreach ($pair as $qrCode)
                        <div class="qrcode">
                            <img src="{{ $qrCode }}" alt="QRコード">
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </body>
</html>
