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
  moduleNameMapper: {
    "^@vue/test-utils": "<rootDir>/node_modules/@vue/test-utils/dist/vue-test-utils.cjs.js",
    '^@/(.*)$': '<rootDir>/resources/js/$1'
  },
  // "collectCoverage": true,
  // "collectCoverageFrom": [
  //   "**/*.{js,vue}",
  //   "!**/node_modules/**"
  // ],
  // "coverageReporters": ["html", "text-summary"],
  // "globals": {
  //   "vue-jest": {
  //     "babelConfig": true
  //   }
  // }

}
