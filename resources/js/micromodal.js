import MicroModal from 'micromodal'; // es6 module

MicroModal.init({
  disableScroll: true,
  closeTrigger: 'data-micromodal-close', // モーダルを閉じるトリガー
  closeOnOutsideClick: true, 
});

// document.addEventListener('DOMContentLoaded', () => {
//   MicroModal.init({
//     disableScroll: true,
//     closeTrigger: 'data-micromodal-close',
//     closeOnOutsideClick: true,
//   });
// });