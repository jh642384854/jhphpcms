module.exports = {
    title: '知码学院',
    description: '君哥带你上王者',
    dest: './dist',
    port: '7777',
//    theme: 'vdoing',
    head: [
        ['link', {rel: 'icon', href: '/logo.png'}]
    ],
    markdown: {
        lineNumbers: true
    },
    plugins: require('./plugins'),
    themeConfig: {
        nav: require('./nav'),
        sidebar: require('./siderbar'),
        sidebarDepth: 4,
        lastUpdated: 'Last Updated',
        searchMaxSuggestoins: 10,
        serviceWorker: {
            updatePopup: {
                message: "有新的内容.",
                buttonText: '更新'
            }
        },
        editLinks: true,
        editLinkText: '在 GitHub 上编辑此页 ！'
    }
}
