<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bucketUrl = env('S3_BUCKET_URL', '');

        DB::table('notifications')->insert([
            [
                'id' => '08eb4fd4-b656-4413-a61a-0cefa060addf',
                'type' => 'App\Notifications\LowStockNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => 2,
                'data' => json_encode([
                    'id' => 70,
                    'management_id' => 'CO-0787',
                    'image_path1' => $bucketUrl ? $bucketUrl . '/items/39476559_674ea0b531c6d.jpg' :  '/storage/items/39476559_674ea0b531c6d.jpg',
                    'item_name' => '箱ティッシュ',
                    'quantity' => 3,
                    'minimum_stock' => 5,
                    'message' => '在庫数が通知在庫数以下になっています'
                ]),
                'read_at' => NULL,
                'created_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/11/05 16:47:40'),
                'updated_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/11/05 16:47:40'),
            ],
            [
                'id' => '0c9f418f-5e98-49c9-9417-456ea746c11e',
                'type' => 'App\Notifications\LowStockNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => 3,
                'data' => json_encode([
                    'id' => 58,
                    'management_id' => 'CO-5743',
                    'image_path1' => $bucketUrl ? $bucketUrl . '/items/1653849578_674ea06d4a162.jpg' : '/storage/items/1653849578_674ea06d4a162.jpg',
                    'item_name' => 'プリンター用インク（ブラック）',
                    'quantity' => 1,
                    'minimum_stock' => 1,
                    'message' => '在庫数が通知在庫数以下になっています'
                ]),
                'read_at' => NULL,
                'created_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/15 13:27:05'),
                'updated_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/15 13:27:05'),
            ],
            [
                'id' => '0fbb76d4-6831-49aa-b072-4d24d71b3562',
                'type' => 'App\Notifications\RequestedItemNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => 3,
                'data' => json_encode([
                    'id' => 3,
                    'item_name' => '使い捨て手袋',
                    'requestor' => '井上',
                    'remarks_from_requestor' => 'なくなったため',
                    'message' => '備品のリクエストが追加されました'
                ]),
                'read_at' => NULL,
                'created_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/11/10 11:11:00'),
                'updated_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/11/10 11:11:00'),
            ],
            [
                'id' => '13cb252f-f614-4750-9b51-fd8a0b27e085',
                'type' => 'App\Notifications\LowStockNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => 3,
                'data' => json_encode([
                    'id' => 51,
                    'management_id' => 'CO-0931',
                    'image_path1' => $bucketUrl ? $bucketUrl . '/items/420484731_674ea042e336e.jpg' : '/storage/items/420484731_674ea042e336e.jpg',
                    'item_name' => 'クリップ',
                    'quantity' => 1,
                    'minimum_stock' => 1,
                    'message' => '在庫数が通知在庫数以下になっています'
                ]),
                'read_at' => NULL,
                'created_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/15 10:33:31'),
                'updated_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/15 10:33:31'),
            ],
            [
                'id' => '16e0221e-1314-43f2-a275-a7a26e6f196e',
                'type' => 'App\Notifications\RequestedItemNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => 3,
                'data' => json_encode([
                    'id' => 4,
                    'item_name' => '付箋（ポストイット）',
                    'requestor' => '清水',
                    'remarks_from_requestor' => 'あると便利なため',
                    'message' => '備品のリクエストが追加されました'
                ]),
                'read_at' => NULL,
                'created_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/11/25 15:20:00'),
                'updated_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/11/25 15:20:00'),
            ],
            [
                'id' => '1957f1e5-bc0c-4146-b178-f5c91044512d',
                'type' => 'App\Notifications\RequestedItemNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => 2,
                'data' => json_encode([
                    'id' => 4,
                    'item_name' => '付箋（ポストイット）',
                    'requestor' => '清水',
                    'remarks_from_requestor' => 'あると便利なため',
                    'message' => '備品のリクエストが追加されました'
                ]),
                'read_at' => NULL,
                'created_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/11/25 15:20:00'),
                'updated_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/11/25 15:20:00'),
            ],
            [
                'id' => '1d0e2317-f957-4f20-a6f5-c908a9feb948',
                'type' => 'App\Notifications\LowStockNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => 2,
                'data' => json_encode([
                    'id' => 58,
                    'management_id' => 'CO-5743',
                    'image_path1' => $bucketUrl ? $bucketUrl . '/items/1653849578_674ea06d4a162.jpg' : '/storage/items/1653849578_674ea06d4a162.jpg',
                    'item_name' => 'プリンター用インク（ブラック）',
                    'quantity' => 1,
                    'minimum_stock' => 1,
                    'message' => '在庫数が通知在庫数以下になっています'
                ]),
                'read_at' => NULL,
                'created_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/15 13:27:05'),
                'updated_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/15 13:27:05'),
            ],
            [
                'id' => '56457697-2601-4dd0-9e7a-9017b14b0a0a',
                'type' => 'App\Notifications\RequestedItemNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => 2,
                'data' => json_encode([
                    'id' => 3,
                    'item_name' => '使い捨て手袋',
                    'requestor' => '井上',
                    'remarks_from_requestor' => 'なくなったため',
                    'message' => '備品のリクエストが追加されました'
                ]),
                'read_at' => NULL,
                'created_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/11/10 11:11:00'),
                'updated_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/11/10 11:11:00'),
            ],
            [
                'id' => '5858ee2d-2e54-4e96-af2c-eb311bb290fa',
                'type' => 'App\Notifications\RequestedItemNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => 3,
                'data' => json_encode([
                    'id' => 2,
                    'item_name' => '延長コード',
                    'requestor' => '鈴木',
                    'remarks_from_requestor' => 'コンセントまで届かないため',
                    'message' => '備品のリクエストが追加されました'
                ]),
                'read_at' => NULL,
                'created_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/15 13:11:00'),
                'updated_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/15 13:11:00'),
            ],
            [
                'id' => '611c9f6a-3975-44f9-b1a3-393e57f6df59',
                'type' => 'App\Notifications\LowStockNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => 2,
                'data' => json_encode([
                    'id' => 65,
                    'management_id' => 'CO-3874',
                    'image_path1' => $bucketUrl ? $bucketUrl . '/items/1944435458_674ea09689404.jpg' : '/storage/items/1944435458_674ea09689404.jpg',
                    'item_name' => 'アルコール（アルコールディスペンサー用）',
                    'quantity' => 1,
                    'minimum_stock' => 1,
                    'message' => '在庫数が通知在庫数以下になっています'
                ]),
                'read_at' => NULL,
                'created_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/11/26 15:29:08'),
                'updated_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/11/26 15:29:08'),
            ],
            [
                'id' => '64e1f37b-f6f8-4628-9b3a-6b6934f63fa3',
                'type' => 'App\Notifications\LowStockNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => 2,
                'data' => json_encode([
                    'id' => 66,
                    'management_id' => 'CO-2554',
                    'image_path1' => $bucketUrl ? $bucketUrl .'/items/1408967480_674ea09c78ee9.jpg' : '/storage/items/1408967480_674ea09c78ee9.jpg',
                    'item_name' => '食器用洗剤',
                    'quantity' => 1,
                    'minimum_stock' => 1,
                    'message' => '在庫数が通知在庫数以下になっています'
                ]),
                'read_at' => NULL,
                'created_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/30 9:03:19'),
                'updated_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/30 9:03:19'),
            ],
            [
                'id' => '7a7f1465-1edf-4571-b8df-0c1de822cabf',
                'type' => 'App\Notifications\LowStockNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => 2,
                'data' => json_encode([
                    'id' => 71,
                    'management_id' => 'CO-8765',
                    'image_path1' => $bucketUrl ? $bucketUrl . '/items/No_Image.jpg' : '/storage/items/No_Image.jpg',
                    'item_name' => 'コロコロクリーナー替え',
                    'quantity' => 3,
                    'minimum_stock' => 3,
                    'message' => '在庫数が通知在庫数以下になっています'
                ]),
                'read_at' => NULL,
                'created_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/30 15:41:16'),
                'updated_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/30 15:41:16'),
            ],
            [
                'id' => '7dd85e6e-4813-4ebb-9311-b2d59c209b70',
                'type' => 'App\Notifications\RequestedItemNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => 2,
                'data' => json_encode([
                    'id' => 1,
                    'item_name' => '加湿器',
                    'requestor' => '田中',
                    'remarks_from_requestor' => '空気が乾燥しているため',
                    'message' => '備品のリクエストが追加されました'
                ]),
                'read_at' => NULL,
                'created_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/05 10:05:00'),
                'updated_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/05 10:05:00'),
            ],
            [
                'id' => '8416ed37-9e6a-4c4b-bb6f-939ccd277b8b',
                'type' => 'App\Notifications\LowStockNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => 2,
                'data' => json_encode([
                    'id' => 51,
                    'management_id' => 'CO-0931',
                    'image_path1' => $bucketUrl ? $bucketUrl . '/items/420484731_674ea042e336e.jpg' : '/storage/items/420484731_674ea042e336e.jpg',
                    'item_name' => 'クリップ',
                    'quantity' => 1,
                    'minimum_stock' => 1,
                    'message' => '在庫数が通知在庫数以下になっています'
                ]),
                'read_at' => NULL,
                'created_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/15 10:33:31'),
                'updated_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/15 10:33:31'),
            ],
            [
                'id' => '97713fa8-7407-4ce5-b4eb-53b13c127fde',
                'type' => 'App\Notifications\LowStockNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => 2,
                'data' => json_encode([
                    'id' => 69,
                    'management_id' => 'CO-5438',
                    'image_path1' => $bucketUrl ? $bucketUrl . '/items/1572301362_674ea0af60bca.jpg' : '/storage/items/1572301362_674ea0af60bca.jpg',
                    'item_name' => 'トイレットペーパー',
                    'quantity' => 5,
                    'minimum_stock' => 5,
                    'message' => '在庫数が通知在庫数以下になっています'
                ]),
                'read_at' => NULL,
                'created_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/08 15:48:21'),
                'updated_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/08 15:48:21'),
            ],
            [
                'id' => 'a431aafa-f1cd-4ff5-b00b-a9600ab789cf',
                'type' => 'App\Notifications\RequestedItemNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => 3,
                'data' => json_encode([
                    'id' => 5,
                    'item_name' => 'マウス',
                    'requestor' => '中村',
                    'remarks_from_requestor' => '壊れたため、新しいものが必要',
                    'message' => '備品のリクエストが追加されました'
                ]),
                'read_at' => NULL,
                'created_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/25 14:20:00'),
                'updated_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/25 14:20:00'),
            ],
            [
                'id' => 'a4f05bcb-12a2-4358-a0ce-72c529d3b836',
                'type' => 'App\Notifications\LowStockNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => 3,
                'data' => json_encode([
                    'id' => 59,
                    'management_id' => 'CO-1209',
                    'image_path1' => $bucketUrl ? $bucketUrl . '/items/869022593_674ea073761e8.jpg' : '/storage/items/869022593_674ea073761e8.jpg',
                    'item_name' => 'プリンター用インク（シアン）',
                    'quantity' => 1,
                    'minimum_stock' => 1,
                    'message' => '在庫数が通知在庫数以下になっています'
                ]),
                'read_at' => NULL,
                'created_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/11/11 10:11:23'),
                'updated_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/11/11 10:11:23'),
            ],
            [
                'id' => 'ae74abe2-a080-45a0-96c5-467140598709',
                'type' => 'App\Notifications\LowStockNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => 3,
                'data' => json_encode([
                    'id' => 71,
                    'management_id' => 'CO-8765',
                    'image_path1' => $bucketUrl ? $bucketUrl . '/items/No_Image.jpg' : '/storage/items/No_Image.jpg',
                    'item_name' => 'コロコロクリーナー替え',
                    'quantity' => 3,
                    'minimum_stock' => 3,
                    'message' => '在庫数が通知在庫数以下になっています'
                ]),
                'read_at' => NULL,
                'created_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/30 15:41:16'),
                'updated_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/30 15:41:16'),
            ],
            [
                'id' => 'b0d0062f-108f-4a20-9511-c5a4515109f2',
                'type' => 'App\Notifications\LowStockNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => 3,
                'data' => json_encode([
                    'id' => 72,
                    'management_id' => 'CO-3907',
                    'image_path1' => $bucketUrl ? $bucketUrl . '/items/1408706600_674ea0bf9ab7f.jpg' : '/storage/items/1408706600_674ea0bf9ab7f.jpg',
                    'item_name' => '使い捨て紙コップ',
                    'quantity' => 20,
                    'minimum_stock' => 50,
                    'message' => '在庫数が通知在庫数以下になっています'
                ]),
                'read_at' => NULL,
                'created_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/28 11:37:00'),
                'updated_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/28 11:37:00'),
            ],
            [
                'id' => 'b6f953b4-331f-4218-8ebb-50c8cbceb0ef',
                'type' => 'App\Notifications\LowStockNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => 2,
                'data' => json_encode([
                    'id' => 59,
                    'management_id' => 'CO-1209',
                    'image_path1' => $bucketUrl ? $bucketUrl . '/items/869022593_674ea073761e8.jpg' : '/storage/items/869022593_674ea073761e8.jpg',
                    'item_name' => 'プリンター用インク（シアン）',
                    'quantity' => 1,
                    'minimum_stock' => 1,
                    'message' => '在庫数が通知在庫数以下になっています'
                ]),
                'read_at' => NULL,
                'created_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/11/11 10:11:23'),
                'updated_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/11/11 10:11:23'),
            ],
            [
                'id' => 'bbbd9f25-4c45-4d17-a082-8907ea1a2ce1',
                'type' => 'App\Notifications\LowStockNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => 2,
                'data' => json_encode([
                    'id' => 64,
                    'management_id' => 'CO-0420',
                    'image_path1' => $bucketUrl ? $bucketUrl . '/items/66540761_674ea091384c1.jpg' : '/storage/items/66540761_674ea091384c1.jpg',
                    'item_name' => 'ペーパータオル',
                    'quantity' => 4,
                    'minimum_stock' => 5,
                    'message' => '在庫数が通知在庫数以下になっています'
                ]),
                'read_at' => NULL,
                'created_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/21 8:42:59'),
                'updated_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/21 8:42:59'),
            ],
            [
                'id' => 'cd72ceec-57c7-4dbc-8047-9501a5051c63',
                'type' => 'App\Notifications\LowStockNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => 3,
                'data' => json_encode([
                    'id' => 65,
                    'management_id' => 'CO-3874',
                    'image_path1' => $bucketUrl ? $bucketUrl . '/items/1944435458_674ea09689404.jpg' : '/storage/items/1944435458_674ea09689404.jpg',
                    'item_name' => 'アルコール（アルコールディスペンサー用）',
                    'quantity' => 1,
                    'minimum_stock' => 1,
                    'message' => '在庫数が通知在庫数以下になっています'
                ]),
                'read_at' => NULL,
                'created_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/11/26 15:29:08'),
                'updated_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/11/26 15:29:08'),
            ],
            [
                'id' => 'cd88092a-8602-4076-a1ee-f3596664b724',
                'type' => 'App\Notifications\LowStockNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => 3,
                'data' => json_encode([
                    'id' => 66,
                    'management_id' => 'CO-2554',
                    'image_path1' => $bucketUrl ? $bucketUrl . '/items/1408967480_674ea09c78ee9.jpg' : '/storage/items/1408967480_674ea09c78ee9.jpg',
                    'item_name' => '食器用洗剤',
                    'quantity' => 1,
                    'minimum_stock' => 1,
                    'message' => '在庫数が通知在庫数以下になっています'
                ]),
                'read_at' => NULL,
                'created_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/30 9:03:19'),
                'updated_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/30 9:03:19'),
            ],
            [
                'id' => 'cd958147-39bb-4eb0-b233-5d57708f59aa',
                'type' => 'App\Notifications\RequestedItemNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => 3,
                'data' => json_encode([
                    'id' => 1,
                    'item_name' => '加湿器',
                    'requestor' => '田中',
                    'remarks_from_requestor' => '空気が乾燥しているため',
                    'message' => '備品のリクエストが追加されました'
                ]),
                'read_at' => NULL,
                'created_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/05 10:05:00'),
                'updated_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/05 10:05:00'),
            ],
            [
                'id' => 'd6f037b4-eca0-48fd-bd6b-0a1fb6b6d254',
                'type' => 'App\Notifications\LowStockNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => 2,
                'data' => json_encode([
                    'id' => 72,
                    'management_id' => 'CO-3907',
                    'image_path1' => $bucketUrl ? $bucketUrl . '/items/1408706600_674ea0bf9ab7f.jpg' : '/storage/items/1408706600_674ea0bf9ab7f.jpg',
                    'item_name' => '使い捨て紙コップ',
                    'quantity' => 20,
                    'minimum_stock' => 50,
                    'message' => '在庫数が通知在庫数以下になっています'
                ]),
                'read_at' => NULL,
                'created_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/28 11:37:00'),
                'updated_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/28 11:37:00'),
            ],
            [
                'id' => 'e7e14dd2-5a27-419d-9517-a85ffb99ff5a',
                'type' => 'App\Notifications\LowStockNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => 3,
                'data' => json_encode([
                    'id' => 69,
                    'management_id' => 'CO-5438',
                    'image_path1' => $bucketUrl ? $bucketUrl . '/items/1572301362_674ea0af60bca.jpg' : '/storage/items/1572301362_674ea0af60bca.jpg',
                    'item_name' => 'トイレットペーパー',
                    'quantity' => 5,
                    'minimum_stock' => 5,
                    'message' => '在庫数が通知在庫数以下になっています'
                ]),
                'read_at' => NULL,
                'created_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/08 15:48:21'),
                'updated_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/08 15:48:21'),
            ],
            [
                'id' => 'e94ea1f0-5b8e-4720-8620-998368debd9f',
                'type' => 'App\Notifications\RequestedItemNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => 2,
                'data' => json_encode([
                    'id' => 5,
                    'item_name' => 'マウス',
                    'requestor' => '中村',
                    'remarks_from_requestor' => '壊れたため、新しいものが必要',
                    'message' => '備品のリクエストが追加されました'
                ]),
                'read_at' => NULL,
                'created_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/25 14:20:00'),
                'updated_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/25 14:20:00'),
            ],
            [
                'id' => 'fb95a1fc-efa3-4531-ae62-f899d6b996d5',
                'type' => 'App\Notifications\LowStockNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => 3,
                'data' => json_encode([
                    'id' => 64,
                    'management_id' => 'CO-0420',
                    'image_path1' => $bucketUrl ? $bucketUrl . '/items/66540761_674ea091384c1.jpg' : '/storage/items/66540761_674ea091384c1.jpg',
                    'item_name' => 'ペーパータオル',
                    'quantity' => 4,
                    'minimum_stock' => 5,
                    'message' => '在庫数が通知在庫数以下になっています'
                ]),
                'read_at' => NULL,
                'created_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/21 8:42:59'),
                'updated_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/21 8:42:59'),
            ],
            [
                'id' => 'fd05e5ac-78c0-4b03-b75a-4d43b61fd5f8',
                'type' => 'App\Notifications\RequestedItemNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => 2,
                'data' => json_encode([
                    'id' => 2,
                    'item_name' => '延長コード',
                    'requestor' => '鈴木',
                    'remarks_from_requestor' => 'コンセントまで届かないため',
                    'message' => '備品のリクエストが追加されました'
                ]),
                'read_at' => NULL,
                'created_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/15 13:11:00'),
                'updated_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/15 13:11:00'),
            ],
            [
                'id' => 'fd6c3fc7-6315-49a0-9a0e-24a943805161',
                'type' => 'App\Notifications\LowStockNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => 3,
                'data' => json_encode([
                    'id' => 70,
                    'management_id' => 'CO-0787',
                    'image_path1' => $bucketUrl ? $bucketUrl . '/items/39476559_674ea0b531c6d.jpg' : '/storage/items/39476559_674ea0b531c6d.jpg',
                    'item_name' => '箱ティッシュ',
                    'quantity' => 3,
                    'minimum_stock' => 5,
                    'message' => '在庫数が通知在庫数以下になっています'
                ]),
                'read_at' => NULL,
                'created_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/11/05 16:47:40'),
                'updated_at' => Carbon::createFromFormat('Y/m/d H:i:s', '2024/11/05 16:47:40'),
            ],
        ]);
    }
}