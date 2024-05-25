const getToday = () => {
  const today = new Date();
  const yyyy = today.getFullYear();
  const mm = ("0"+(today.getMonth()+1)).slice(-2);
  const dd = ("0"+today.getDate()).slice(-2);
  return yyyy+'-'+mm+'-'+dd;
}

// // common.jsか何かに切り分ける
// const getTodayForHistory = () => {
//   const today = new Date()
//   return `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`
// }

// const getOneWeekAgoForHistory = () => {
//   const oneWeekAgo = new Date()
//   oneWeekAgo.setDate(oneWeekAgo.getDate() - 7)
//   return `${oneWeekAgo.getFullYear()}-${String(oneWeekAgo.getMonth() + 1).padStart(2, '0')}-${String(oneWeekAgo.getDate()).padStart(2, '0')}`
// }


export { getToday }