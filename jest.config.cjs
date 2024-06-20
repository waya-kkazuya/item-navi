module.exports = {
  // testEnvironment: 'jsdom',
  testEnvironment: 'jest-environment-jsdom',
  moduleFileExtensions: [
    'js',
    'json',
    // 優先的に.vueファイルをテストします
    'vue',
  ],
  transform: {
    // vueファイルを処理するためにvue3-jestを使用します
    '.*\\.(vue)$': '@vue/vue3-jest',
    // babel-jestを使用してすべてのjsファイルを処理します
    '.*\\.(js)$': 'babel-jest',
  },
  testMatch: ['<rootDir>/tests-vue/**/*.test.js'],
}
