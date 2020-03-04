const Encore = require('@symfony/webpack-encore')

function extractEncoreConfig (name) {
  const config = Encore.getWebpackConfig()

  Encore.reset()

  return { ...config, name }
}

function configJavaScript ({ basePath }) {
  Encore
    .setOutputPath(`${basePath}/public/js`)
    .setPublicPath('/public/js')
    .disableSingleRuntimeChunk()
    .addEntry('babel-polyfill.min', '@babel/polyfill')
    .addEntry('admin.min', './resources/js/admin.js')
    .addEntry('front.min', './resources/js/front.js')
    .addEntry('expressCheckout.min', './resources/js/expressCheckout.js')
    .addEntry('payPalRedirect.min', './resources/js/payPalRedirect.js')
    .enableSourceMaps(!Encore.isProduction())

  return extractEncoreConfig('javascript-configuration')
}

function configCss ({ basePath }) {
  Encore
    .setOutputPath(`${basePath}/public/css`)
    .setPublicPath('/public/css')
    .disableSingleRuntimeChunk()
    .enableSassLoader()
    .addStyleEntry('admin.min', './resources/scss/admin.scss')
    .addStyleEntry('front.min', './resources/scss/front.scss')
    .enableSourceMaps(!Encore.isProduction())

  return extractEncoreConfig('css-configuration')
}

function config (env) {
  const config = [
    configJavaScript(env),
    configCss(env)
  ]

  return [...config]
}

module.exports = env => config(env)
