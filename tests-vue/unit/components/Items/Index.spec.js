import { shallowMount, mount } from '@vue/test-utils';
import Index from '@/Pages/Items/IndexPrepare.vuetopTablet.vue';
// import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
// import FlashMessage from '@/Components/FlashMessage.vue';
// import Pagination from '@/Components/Pagination.vue';
import faker from 'faker';


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

// describe('Items/Index.vue', () => {
//   it.only('itemsオブジェクトを正しく受け取っているか', () => {

//     // コンポーネントのマウント mountとshallowMountの違い
//     const wrapper = shallowMount(Index, {
//       props: { items: items.data }
//     });

//     console.log(wrapper);

//     // itemsプロパティが正しく受け取られていることを確認
//     expect(wrapper.props().items).toEqual(items.data);
    
//   });

describe('Items/Index.vue', () => {
  test('表の見出しの各項目が表示されている', () => {
    const wrapper = shallowMount(Index, {
      props: { items: items.data }
    });

    // await Vue.nextTick();

    const expectedHeaders = [
      '管理ID', '登録日', '備品名', 'カテゴリ', '在庫数', '利用状況', '使用者',
      '利用場所', '保管場所', '取得区分', '購入先', '取得価額', '取得年月日',
      '点検予定日', '廃棄予定日', 'メーカー', '製品番号', '備考'
    ]

    console.log('wrapper', wrapper)
    
    const headers = wrapper.findAll('th')
    console.log(headers);
    expect(headers.length).toBe(expectedHeaders.length)

    for (let i = 0; i < expectedHeaders.length; i++) {
      expect(headers.at(i).text()).toBe(expectedHeaders[i])
    }
  })
})


// describe('Items/Index.vue', () => {
//   it('itemsオブジェクトを正しく受け取っているか', () => {

//     // コンポーネントのマウント mountとshallowMountの違い
//     const wrapper = shallowMount(Index, {
//       props: { items: items.data }
//     });

//     // itemsプロパティが正しく受け取られていることを確認
//     expect(wrapper.props().items).toEqual(items.data);


//     // itemsの各属性が正しく表示されていることを確認
//     items.data.forEach((item, index) => {
//       console.log('index', index)
//       console.log('item', item)
//       const itemElement = wrapper.findAll('.item');
//       // const itemElement = wrapper.findAll('.item').at(index);
//       console.log('itemElement', itemElement);
//       expect(itemElement.find('.id').text()).toBe(item.id.toString());
//       expect(itemElement.find('.created_at').text()).toBe(item.created_at.toString());
//       expect(itemElement.find('.name').text()).toBe(item.name);
//       expect(itemElement.find('.category').text()).toBe(item.category.toString());
//       expect(itemElement.find('.stocks').text()).toBe(item.stocks.toString());
//       expect(itemElement.find('.minimum_stock').text()).toBe(item.minimum_stock.toString());
//       expect(itemElement.find('.usage_status').text()).toBe(item.usage_status);
//       expect(itemElement.find('.end_user').text()).toBe(item.end_user);
//       expect(itemElement.find('.location_of_use').text()).toBe(item.location_of_use.toString());
//       expect(itemElement.find('.storage_location').text()).toBe(item.storage_location.toString());
//       expect(itemElement.find('.acquisition_category').text()).toBe(item.acquisition_category);
//       expect(itemElement.find('.where_to_buy').text()).toBe(item.where_to_buy);
//       expect(itemElement.find('.price').text()).toBe(item.price.toString());
//       expect(itemElement.find('.date_of_acquisition').text()).toBe(item.date_of_acquisition.toString());
//       expect(itemElement.find('.inspection_schedule').text()).toBe(item.inspection_schedule.toString());
//       expect(itemElement.find('.disposal_schedule').text()).toBe(item.disposal_schedule.toString());
//       expect(itemElement.find('.manufacturer').text()).toBe(item.manufacturer);
//       expect(itemElement.find('.product_number').text()).toBe(item.product_number);
//       expect(itemElement.find('.remarks').text()).toBe(item.remarks);
//     });
//   });

// });
