module.exports = {
  testEnvironment: 'jsdom',
  moduleFileExtensions: [
    'js',
    'json',
    // 優先的に.vueファイルをテストします
    'vue',
  ],
  transform: {
    // vueファイルを処理するためにvue-jestを使用します
    '.*\\.(vue)$': 'vue-jest',
    // babel-jestを使用してすべてのjsファイルを処理します
    '.*\\.(js)$': 'babel-jest',
  },
}
