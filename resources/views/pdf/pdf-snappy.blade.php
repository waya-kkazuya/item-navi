<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>名刺レイアウト</title>
    <style>
        body {
            width: 210mm;
            height: 297mm;
            margin: 0;
            padding: 0;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
        }
        .card {
            width: 91mm;
            height: 55mm;
            margin: 5mm;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        img {
            max-width: 100%;
            max-height: 100%;
        }
    </style>
</head>
<body>
    {{-- @for ($i = 0; $i < 10; $i++)
        <div class="card">
            <img src="{{ asset('path/to/your/image.jpg') }}" alt="名刺画像">
        </div>
    @endfor --}}

      @foreach ($qrCodes as $qrCode)
        <div class="card">
            <img src="{{ $qrCode }}" alt="QRコード">
        </div>
      @endforeach
  </div>
</body>
</html>
