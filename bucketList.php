<?php
require './vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

// S3インスタンス作成時の引数
$s3 = new S3Client([
    'region' => 'ap-northeast-1',
    'version' => 'latest'
]);

# バケット一覧の表示
try {
    $result = $s3->listBuckets();
    foreach ($result['Buckets'] as $bucket) {
        echo $bucket['Name'] . "\n";
    }
} catch (\Throwable $e) {
    echo $e->getMessage() . "\n";
}