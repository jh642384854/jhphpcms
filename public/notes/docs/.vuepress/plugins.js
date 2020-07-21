module.exports = [
    ['@vuepress/back-to-top'],
    '@vuepress/active-header-links', {
        sidebarLinkSelector: '.sidebar-link',
        headerAnchorSelector: '.header-anchor'
    },
    ['@vuepress/nprogress'],
    '@vuepress/medium-zoom',{
        selector: 'img',
        // See: https://github.com/francoischalifour/medium-zoom#options
        options: {
            margin: 16
        }
    }
];