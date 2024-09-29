<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            width: 210mm;
            height: 297mm;
        }
        .page {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 210mm;
            height: 297mm;
            /* margin: 11mm 14mm; */
            margin: auto;
            page-break-after: always; /* ページの後で改ページ */
        }
        table {
            width: calc(91mm * 2); /* 2列分の幅 */
            height: calc(55mm * 5); /* 5行分の高さ */
            border-collapse: collapse;
        }
        td {
            width: 91mm;
            height: 55mm;
            border: 1px solid #000; /* デバッグ用の枠線 */
            box-sizing: border-box; /* 枠線を含めたサイズ計算 */
            text-align: center;
            vertical-align: middle;
        }
        img {
            max-width: 91mm; /* 親要素の幅に合わせる */
            max-height: 55mm; /* 親要素の高さに合わせる */
            object-fit: contain; /* 画像を縮小して親要素に収める */
        }
    </style>
</head>
<body>
    @foreach (array_chunk($qrCodes, 10) as $chunk)
        <div class="page">
            <table>
                @foreach (array_chunk($chunk, 2) as $pair)
                    <tr>
                        @foreach ($pair as $qrCode)
                            <td>
                                <img src="{{ $qrCode }}" alt="QRコード">
                            </td>
                        @endforeach
                        @if (count($pair) < 2)
                            <td></td> <!-- 奇数の場合の空セル -->
                        @endif
                    </tr>
                @endforeach
            </table>
        </div>
    @endforeach
</body>
</html>
