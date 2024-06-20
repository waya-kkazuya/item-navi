import Vue from 'vue';
import { mount } from '@vue/test-utils';

const App = {
  template:`
  <div>Hello World</div>
  `
}

test("test App Component",function(){
  const wrapper = mount(App);
  console.log(wrapper.text())
})