import { shallowMount } from '@vue/test-utils';
import ItemsComponent from '@/components/ItemsComponent.vue';

describe('ItemsComponent.vue', () => {
  it('receives items prop correctly', () => {
    // テストデータの作成
    const items = [
      { id: 1, name: 'Item 1' },
      { id: 2, name: 'Item 2' },
      // 他のアイテム...
    ];

    // コンポーネントのマウント
    const wrapper = shallowMount(ItemsComponent, {
      props: { items }
    });

    // itemsプロパティが正しく受け取られていることを確認
    expect(wrapper.props().items).toEqual(items);
  });
});
