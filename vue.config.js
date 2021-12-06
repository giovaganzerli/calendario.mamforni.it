// DEVELOPMENT -> /mamforni.it/calendario/dist
// PRODUCTION -> /dist

//NB: forse serve a volte
//publicPath: (process.env.NODE_ENV === 'production') ? '/calendario' : '/',

module.exports = {
    publicPath: (process.env.NODE_ENV === 'production') ? '/' : '/',
    outputDir: 'dist',
    indexPath: 'index.html'
}