import { shallowMount } from '@vue/test-utils';
import Index from '@/Pages/Items/Index.vue';
import faker from 'faker';

describe('Items/Index.vue', () => {
  it('itemsオブジェクトを正しく受け取っているか', () => {

  // テストデータの作成 paginateオブジェクトの構造を再現
  // 階層の適切な処理
  const items = {
      data: Array.from({ length: 20 }, () => ({
        id: faker.datatype.number(),
        name: faker.commerce.productName(),
        category_id: faker.datatype.number(),
        image_path1: faker.image.imageUrl(),
        image_path2: faker.image.imageUrl(),
        image_path3: faker.image.imageUrl(),
        stocks: faker.datatype.number(),
        minimum_stock: faker.datatype.number(),
        usage_status: faker.random.arrayElement(['未使用', '使用中']),
        end_user: faker.name.findName(),
        location_of_use_id: faker.datatype.number(),
        storage_location_id: faker.datatype.number(),
        acquisition_category: faker.random.arrayElement(['購入', 'リース（レンタル）', '譲渡', 'その他']),
        where_to_buy: faker.internet.url(),
        price: faker.commerce.price(),
        date_of_acquisition: faker.date.past(),
        inspection_schedule: faker.date.future(),
        disposal_schedule: faker.date.future(),
        manufacturer: faker.company.companyName(),
        product_number: faker.finance.account(),
        remarks: faker.lorem.sentence(),
        qrcode_path: faker.image.imageUrl(),
        created_at: faker.date.past(),
      })),
      // current_page: 1,
      // first_page_url: "http://example.com/pagination?page=1",
      // from: 1,
      // last_page: 5,
      // last_page_url: "http://example.com/pagination?page=5",
      // next_page_url: "http://example.com/pagination?page=2",
      // path: "http://example.com/pagination",
      // per_page: 20,
      // prev_page_url: null,
      // to: 20,
      // total: 100,
  };


    // コンポーネントのマウント
    const wrapper = shallowMount(Index, {
      props: { items: items.data }
    });

    // itemsプロパティが正しく受け取られていることを確認
    expect(wrapper.props().items).toEqual(items.data);
  });
});
