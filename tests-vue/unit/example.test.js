import { mount } from '@vue/test-utils';
import App from '@/Components/App.vue';



describe('App', () => {
  let wrapper;

  beforeEach(() => {
    wrapper = mount(App)
  })
  
  it("click button count up", async () => {
    await wrapper.get('button').trigger('click')
    expect(wrapper.text()).toContain('Count is: 1')
  })
  it("click button count up", async () => {
    await wrapper.get('button').trigger('click')
    expect(wrapper.text()).toContain('Count is: 1')
  })
})

// describe('App', () => {
//   it("click button count up", async () => {
//     const wrapper = mount(App);
//     await wrapper.get('button').trigger('click')
//     expect(wrapper.text()).toContain('Count is: 1')
//   })
// })



// describe('App', () => {
//   it('test App Component', () => {
//     const wrapper = mount(App, {
//       props: {
//         admin: true,
//       },
//     })
//     const admin = wrapper.find('#admin')
//     expect(admin.isVisible()).toBe(true)
//   })
// })


// describe('App', () => {
//   it("test App Component",function(){
//     const wrapper = mount(App);
//     const admin = wrapper.find('#admin');
//     expect(admin.exists()).toBe(false)
//   })
// })


// describe('App', () => {
//   it("test App Component",function(){
//     const wrapper = mount(App);
//     const profile = wrapper.find('#admin'); //DOMWrapper
//     console.log(profile.text());
//   })
// })

// describe('App', () => {
//   it("computed property upper case",() => {
//     const wrapper = mount(App);
//     expect(wrapper.text()).toBe('JOHN')
//   })
// })


// test("test App Component",function(){
//   const wrapper = mount(App,{
//     props:{
//       msg: "World"
//     }
//   });
//   expect(wrapper.text()).toBe('Hello World')
//   console.log(wrapper.vm)
// })