// +----------------------------------------------------------------------
// | ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2020 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: http://demo.thinkadmin.top
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | gitee 代码仓库：https://gitee.com/zoujingli/ThinkAdmin
// | github 代码仓库：https://github.com/zoujingli/ThinkAdmin
// +----------------------------------------------------------------------

// Layui & jQuery
if (typeof jQuery === 'undefined') window.$ = window.jQuery = layui.$;
window.form = layui.form, window.layer = layui.layer, window.laydate = layui.laydate;

window.appRoot = (function (src) {
    return src.pop(), src.pop(), src.join('/') + '/';
})(document.scripts[document.scripts.length - 1].src.split('/'));

window.baseRoot = (function (src) {
    return src.substring(0, src.lastIndexOf("/") + 1);
})(document.scripts[document.scripts.length - 1].src);

window.tapiRoot = window.tapiRoot || window.baseRoot + "?s=admin"

// require 配置参数
require.config({
    waitSeconds: 60,
    baseUrl: baseRoot,
    map: {'*': {css: baseRoot + 'plugs/require/css.js'}},
    paths: {
        //'editormd': ['plugs/editormd/editormd'],
        'md5': ['plugs/jquery/md5.min'],
        'json': ['plugs/jquery/json.min'],
        'michat': ['plugs/michat/michat'],
        'base64': ['plugs/jquery/base64.min'],
        'upload': [tapiRoot + '/api.upload?.js'],
        'echarts': ['plugs/echarts/echarts.min'],
        'echarts_china_map': ['plugs/echarts/map/js/china'], //Echarts中国地图JS
        'angular': ['plugs/angular/angular.min'],
        'ckeditor': ['plugs/ckeditor/ckeditor'],
        'websocket': ['plugs/socket/websocket'],
        'pcasunzips': ['plugs/jquery/pcasunzips'],
        'jquery.ztree': ['plugs/ztree/ztree.all.min'],
        'jquery.masonry': ['plugs/jquery/masonry.min'],
        'jquery.autocompleter': ['plugs/jquery/autocompleter.min'],
    },
    shim: {
        'websocket': {deps: [baseRoot + 'plugs/socket/swfobject.min.js']},
        'jquery.ztree': {deps: ['jquery', 'css!' + baseRoot + 'plugs/ztree/zTreeStyle/zTreeStyle.css']},
        'jquery.autocompleter': {deps: ['jquery', 'css!' + baseRoot + 'plugs/jquery/autocompleter.css']},
    }
});

// 注册jquery到require模块
define('jquery', [], function () {
    return layui.$;
});

$(function () {
    //全局注册第三方组件
    layui.config({
        base: "/static/plugs/layui/mods/"
    }).extend({
        treeTable:'treetable-lay/treeTable'
    })
    window.$body = $('body');
    /*! 消息组件实例 */
    $.msg = new function (that) {
        that = this;
        this.idx = [];
        this.shade = [0.02, '#000'];
        // 关闭消息框
        this.close = function (index) {
            return layer.close(index);
        };
        // 弹出警告框
        this.alert = function (msg, callback) {
            var index = layer.alert(msg, {end: callback, scrollbar: false});
            return this.idx.push(index), index;
        };
        // 确认对话框
        this.confirm = function (msg, ok, no) {
            var index = layer.confirm(msg, {title: '操作确认', btn: ['确认', '取消']}, function () {
                typeof ok === 'function' && ok.call(this, index);
            }, function () {
                typeof no === 'function' && no.call(this, index);
                that.close(index);
            });
            return index;
        };
        // 显示成功类型的消息
        this.success = function (msg, time, callback) {
            var index = layer.msg(msg, {icon: 1, shade: this.shade, scrollbar: false, end: callback, time: (time || 2) * 1000, shadeClose: true});
            return this.idx.push(index), index;
        };
        // 显示失败类型的消息
        this.error = function (msg, time, callback) {
            var index = layer.msg(msg, {icon: 2, shade: this.shade, scrollbar: false, time: (time || 3) * 1000, end: callback, shadeClose: true});
            return this.idx.push(index), index;
        };
        // 状态消息提示
        this.tips = function (msg, time, callback) {
            var index = layer.msg(msg, {time: (time || 3) * 1000, shade: this.shade, end: callback, shadeClose: true});
            return this.idx.push(index), index;
        };
        // 显示正在加载中的提示
        this.loading = function (msg, callback) {
            var index = msg ? layer.msg(msg, {icon: 16, scrollbar: false, shade: this.shade, time: 0, end: callback}) : layer.load(2, {time: 0, scrollbar: false, shade: this.shade, end: callback});
            return this.idx.push(index), index;
        };
        // 自动处理显示Think返回的Json数据
        this.auto = function (ret, time) {
            var url = ret.url || (typeof ret.data === 'string' ? ret.data : '');
            var msg = ret.msg || (typeof ret.info === 'string' ? ret.info : '');
            if (parseInt(ret.code) === 1 && time === 'false') {
                return url ? (window.location.href = url) : $.form.reload();
            }
            return (parseInt(ret.code) === 1) ? this.success(msg, time, function () {
                url ? (window.location.href = url) : $.form.reload();
                for (var i in that.idx) layer.close(that.idx[i]);
                that.idx = [];
            }) : this.error(msg, 3, function () {
                url ? window.location.href = url : '';
            });
        };
    };

    /*! 表单自动化组件 */
    $.form = new function (that) {
        that = this;
        // 内容区选择器
        this.selecter = '.layui-layout-admin>.layui-body';
        // 刷新当前页面
        this.reload = function () {
            window.onhashchange.call(this);
        };
        // 内容区域动态加载后初始化
        this.reInit = function ($dom) {
            $.vali.listen(this);
            $dom = $dom || $(this.selecter);
            $dom.find('[required]').map(function ($parent) {
                if (($parent = $(this).parent()) && $parent.is('label')) {
                    $parent.addClass('label-required-prev');
                } else {
                    $parent.prevAll('label').addClass('label-required-next');
                }
            });
            $dom.find('input[data-date-range]').map(function () {
                this.setAttribute('autocomplete', 'off');
                laydate.render({
                    type: this.getAttribute('data-date-range') || 'date',
                    range: true, elem: this, done: function (value) {
                        $(this.elem).val(value).trigger('change');
                    }
                });
            });
            $dom.find('input[data-date-input]').map(function () {
                this.setAttribute('autocomplete', 'off');
                laydate.render({
                    type: this.getAttribute('data-date-input') || 'date',
                    range: false, elem: this, done: function (value) {
                        $(this.elem).val(value).trigger('change');
                    }
                });
            });
            $dom.find('[data-file]:not([data-inited])').map(function (index, elem, $this, field) {
                $this = $(elem), field = $this.attr('data-field') || 'file';
                if (!$this.data('input')) $this.data('input', $('[name="' + field + '"]').get(0));
                $this.uploadFile(function (url, file) {
                    $($this.data('input')).data('file', file).val(url).trigger('change');
                });
            });
        };
        // 在内容区显示视图
        this.show = function (html) {
            $(this.selecter).html(html);
            this.reInit($(this.selecter));
            setTimeout(function () {
                that.reInit($(that.selecter));
            }, 500);
        };
        // 以HASH打开新网页
        this.href = function (url, obj) {
            if (url !== '#') window.location.href = '#' + $.menu.parseUri(url, obj);
            else if (obj && obj.getAttribute('data-menu-node')) {
                $('[data-menu-node^="' + obj.getAttribute('data-menu-node') + '-"][data-open!="#"]:first').trigger('click');
            }
        };
        // 异步加载的数据
        this.load = function (url, data, method, callback, loading, tips, time, headers) {
            var index = loading !== false ? $.msg.loading(tips) : 0;
            if (typeof data === 'object' && typeof data['_token_'] === 'string') {
                headers = headers || {}, headers['User-Form-Token'] = data['_token_'], delete data['_token_'];
            }
            $.ajax({
                data: data || {},
                type: method || 'GET',
                url: $.menu.parseUri(url),
                beforeSend: function (xhr, i) {
                    if (typeof Pace === 'object' && loading !== false) Pace.restart();
                    if (typeof headers === 'object') for (i in headers) xhr.setRequestHeader(i, headers[i]);
                },
                error: function (XMLHttpRequest, $dialog, dialogIdx, iframe) {
                    if (parseInt(XMLHttpRequest.status) !== 200 && XMLHttpRequest.responseText.indexOf('Call Stack') > -1) try {
                        dialogIdx = layer.open({title: XMLHttpRequest.status + ' - ' + XMLHttpRequest.statusText, type: 2, move: false, content: 'javascript:;'});
                        layer.full(dialogIdx), $dialog = $('#layui-layer' + dialogIdx), iframe = $dialog.find('iframe').get(0);
                        (iframe.contentDocument || iframe.contentWindow.document).write(XMLHttpRequest.responseText);
                        $dialog.find('.layui-layer-setwin').css({right: '35px', top: '28px'}).find('a').css({marginLeft: 0});
                        $dialog.find('.layui-layer-title').css({color: 'red', height: '70px', lineHeight: '70px', fontSize: '22px', textAlign: 'center', fontWeight: 700});
                    } catch (e) {
                        layer.close(dialogIdx);
                    }
                    layer.closeAll('loading');
                    if (parseInt(XMLHttpRequest.status) !== 200) {
                        $.msg.tips('E' + XMLHttpRequest.status + ' - 服务器繁忙，请稍候再试！');
                    } else {
                        this.success(XMLHttpRequest.responseText);
                    }
                },
                success: function (ret,textStatus,request) {
                    if (typeof callback === 'function' && callback.call(that, ret) === false) return false;
                    return typeof ret === 'object' ? $.msg.auto(ret, time || ret.wait || undefined) : that.show(ret);
                },
                complete: function () {
                    $.msg.close(index);
                }
            });
        };
        // 加载HTML到目标位置
        this.open = function (url, data, callback, loading, tips) {
            this.load(url, data, 'get', function (ret) {
                return (typeof ret === 'object' ? $.msg.auto(ret) : that.show(ret)), false;
            }, loading, tips);
        };
        // 打开一个iframe窗口
        this.iframe = function (url, title, area) {
            return layer.open({title: title || '窗口', type: 2, area: area || ['800px', '580px'], fix: true, maxmin: false, content: url,scrollbar:true});
        };
        // 加载HTML到弹出层
        this.modal = function (url, data, title, callback, loading, tips) {
            this.load(url, data, 'GET', function (res, index) {
                if (typeof (res) === 'object') return $.msg.auto(res), false;
                index = layer.open({
                    type: 1, btn: false, area: "800px", content: res, title: title || '', success: function (dom, index) {
                        $(dom).find('[data-close]').off('click').on('click', function () {
                            if ($(this).attr('data-confirm')) return $.msg.confirm($(this).attr('data-confirm'), function (_index) {
                                layer.close(_index), layer.close(index);
                            }), false;
                            layer.close(index);
                        });
                        $.form.reInit($(dom));
                    }
                });
                $.msg.idx.push(index);
                return (typeof callback === 'function') && callback.call(that);
            }, loading, tips);
        };
    };

    /*! 后台菜单辅助插件 */
    $.menu = new function (that) {
        that = this;
        // 计算URL地址中有效的URI
        this.getUri = function (uri) {
            uri = uri || window.location.href;
            uri = (uri.indexOf(window.location.host) > -1 ? uri.split(window.location.host)[1] : uri);
            return (uri.indexOf('#') > -1 ? uri.split('#')[1] : uri).split('?')[0];
        };
        // 通过URI查询最有可能的菜单NODE
        this.queryNode = function (url, node) {
            node = node || location.href.replace(/.*spm=([\d\-m]+).*/ig, '$1');
            if (!/^m-/.test(node)) {
                var $menu = $('[data-menu-node][data-open*="' + url.replace(/\.html$/ig, '') + '"]');
                return $menu.size() ? $menu.get(0).getAttribute('data-menu-node') : '';
            }
            return node;
        };
        // URL转URI
        this.parseUri = function (uri, obj) {
            var params = {};
            if (uri.indexOf('?') > -1) {
                var attrs = uri.split('?')[1].split('&');
                for (var i in attrs) if (attrs[i].indexOf('=') > -1) {
                    var tmp = attrs[i].split('=').slice();
                    if (typeof tmp[0] === 'string' && tmp[0].length > 0) {
                        params[tmp[0]] = decodeURIComponent(tmp[1].replace(/%2B/ig, '%20'));
                    }
                }
            }
            uri = this.getUri(uri);
            if (typeof params.spm !== 'string') {
                params.spm = obj && obj.getAttribute('data-menu-node') || this.queryNode(uri);
            }
            if (typeof params.spm !== 'string' || params.spm.length < 1) delete params.spm;
            // 生成新的 URL 参数
            var attrs = [];
            for (var i in params) attrs.push([i, params[i]].join('='));
            var query = '?' + attrs.join('&');
            return uri + (query === '?' ? '' : query);
        };
        // 后台菜单动作初始化
        this.listen = function () {
            /*! 初始化操作 */
            layui.form.on('switch(ThinkAdminDebug)', function (data) {
                jQuery.post(tapiRoot + '/api.plugs/debug', {state: data.elem.checked ? 1 : 0});
            });
            /*! 菜单模式切换 */
            (function ($menu, miniClass) {
                /*! Mini 菜单模式切换及显示 */
                if (layui.data('admin-menu-type')['type-mini']) $menu.addClass(miniClass);
                $body.on('click', '[data-target-menu-type]', function () {
                    $menu.toggleClass(miniClass), layui.data('admin-menu-type', {key: 'type-mini', value: $menu.hasClass(miniClass)});
                }).on('resize', function () {
                    $body.width() > 1000 ? (layui.data('admin-menu-type')['type-mini'] ? $menu.addClass(miniClass) : $menu.removeClass(miniClass)) : $menu.addClass(miniClass);
                }).trigger('resize');
                /*! Mini 菜单模式时TIPS文字显示 */
                $('[data-target-tips]').mouseenter(function () {
                    if ($menu.hasClass(miniClass)) $(this).attr('index', layer.tips($(this).attr('data-target-tips') || '', this));
                }).mouseleave(function () {
                    layer.close($(this).attr('index'));
                });
            })($('.layui-layout-admin'), 'layui-layout-left-mini');
            /*!  左则二级菜单展示 */
            $('[data-submenu-layout]>a').on('click', function () {
                that.syncOpenStatus(1);
            });
            /*! 同步二级菜单展示状态 */
            this.syncOpenStatus = function (mode) {
                $('[data-submenu-layout]').map(function (node) {
                    node = $(this).attr('data-submenu-layout');
                    if (mode === 1) {
                        layui.data('admin-menu-stat', {key: node, value: $(this).hasClass('layui-nav-itemed') ? 2 : 1});
                    } else if ((layui.data('admin-menu-stat')[node] || 2) === 2) {
                        $(this).addClass('layui-nav-itemed');
                    }
                });
            };
            window.onhashchange = function (hash, node) {
                hash = window.location.hash || '';
                if (hash.length < 1) return $('[data-menu-node][data-open!="#"]:first').trigger('click');
                $.form.load(hash), that.syncOpenStatus(2);
                // 菜单选择切换
                node = that.queryNode(that.getUri());
                if (/^m-/.test(node)) {
                    var $all = $('a[data-menu-node]').parent(), tmp = node.split('-'), tmpNode = tmp.shift();
                    while (tmp.length > 0) {
                        tmpNode = tmpNode + '-' + tmp.shift();
                        $all = $all.not($('a[data-menu-node="' + tmpNode + '"]').parent().addClass('layui-this'));
                    }
                    $all.removeClass('layui-this');
                    // 菜单模式切换
                    if (node.split('-').length > 2) {
                        var _tmp = node.split('-'), _node = _tmp.shift() + '-' + _tmp.shift();
                        $('[data-menu-layout]').not($('[data-menu-layout="' + _node + '"]').removeClass('layui-hide')).addClass('layui-hide');
                        $('[data-menu-node="' + node + '"]').parent().parent().parent().addClass('layui-nav-itemed');
                        $('.layui-layout-admin').removeClass('layui-layout-left-hide');
                    } else $('.layui-layout-admin').addClass('layui-layout-left-hide');
                    that.syncOpenStatus(1);
                }
            };
            // URI初始化动作
            window.onhashchange.call(this);
        };
    };

    /*! 注册对象到Jq */
    $.vali = function (form, callback, options) {
        return (new function (that) {
            that = this;
            // 表单元素
            this.tags = 'input,textarea,select';
            // 检测元素事件
            this.checkEvent = {change: true, blur: true, keyup: false};
            // 去除字符串两头的空格
            this.trim = function (str) {
                return str.replace(/(^\s*)|(\s*$)/g, '');
            };
            // 标签元素是否可见
            this.isVisible = function (ele) {
                return $(ele).is(':visible');
            };
            // 检测属性是否有定义
            this.hasProp = function (ele, prop) {
                if (typeof prop !== "string") return false;
                var attrProp = ele.getAttribute(prop);
                return (typeof attrProp !== 'undefined' && attrProp !== null && attrProp !== false);
            };
            // 判断表单元素是否为空
            this.isEmpty = function (ele, value) {
                var trim = this.trim(ele.value);
                value = value || ele.getAttribute('placeholder');
                return (trim === "" || trim === value);
            };
            // 正则验证表单元素
            this.isRegex = function (ele, regex, params) {
                var input = $(ele).val(), real = this.trim(input);
                regex = regex || ele.getAttribute('pattern');
                if (real === "" || !regex) return true;
                return new RegExp(regex, params || 'i').test(real);
            };
            // 检侧所的表单元素
            this.checkAllInput = function () {
                var isPass = true;
                $(form).find(this.tags).each(function () {
                    if (that.checkInput(this) === false) return $(this).focus(), isPass = false;
                });
                return isPass;
            };
            // 检测表单单元
            this.checkInput = function (input) {
                var tag = input.tagName.toLowerCase(), need = this.hasProp(input, "required");
                var type = (input.getAttribute("type") || '').replace(/\W+/, "").toLowerCase();
                if (this.hasProp(input, 'data-auto-none')) return true;
                var ingoreTags = ['select'], ingoreType = ['radio', 'checkbox', 'submit', 'reset', 'image', 'file', 'hidden'];
                for (var i in ingoreTags) if (tag === ingoreTags[i]) return true;
                for (var i in ingoreType) if (type === ingoreType[i]) return true;
                if (need && this.isEmpty(input)) return this.remind(input);
                return this.isRegex(input) ? (this.hideError(input), true) : this.remind(input);
            };
            // 验证标志
            this.remind = function (input) {
                if (!this.isVisible(input)) return true;
                this.showError(input, input.getAttribute('title') || input.getAttribute('placeholder') || '输入错误');
                return false;
            };
            // 错误消息显示
            this.showError = function (ele, content) {
                $(ele).addClass('validate-error'), this.insertError(ele);
                $($(ele).data('input-info')).addClass('layui-anim layui-anim-fadein').css({width: 'auto'}).html(content);
            };
            // 错误消息消除
            this.hideError = function (ele) {
                $(ele).removeClass('validate-error'), this.insertError(ele);
                $($(ele).data('input-info')).removeClass('layui-anim-fadein').css({width: '30px'}).html('');
            };
            // 错误消息标签插入
            this.insertError = function (ele) {
                var $html = $('<span style="padding-right:12px;color:#a94442;position:absolute;right:0;font-size:12px;z-index:2;display:block;width:34px;text-align:center;pointer-events:none"></span>');
                $html.css({top: $(ele).position().top + 'px', paddingBottom: $(ele).css('paddingBottom'), lineHeight: $(ele).css('height')});
                $(ele).data('input-info') || $(ele).data('input-info', $html.insertAfter(ele));
            };
            // 表单验证入口
            this.check = function (form, callback) {
                $(form).attr("novalidate", "novalidate");
                $(form).find(that.tags).map(function () {
                    this.bindEventMethod = function () {
                        that.checkInput(this);
                    };
                    for (var e in that.checkEvent) if (that.checkEvent[e] === true) {
                        $(this).off(e, this.bindEventMethod).on(e, this.bindEventMethod);
                    }
                });
                $(form).bind("submit", function (event) {
                    if (that.checkAllInput() && typeof callback === 'function') {
                        if (typeof CKEDITOR === 'object' && typeof CKEDITOR.instances === 'object') {
                            for (var i in CKEDITOR.instances) CKEDITOR.instances[i].updateElement();
                        }
                        callback.call(this, $(form).formToJson());
                    }
                    return event.preventDefault(), false;
                });
                $(form).find('[data-form-loaded]').map(function () {
                    $(this).html(this.getAttribute('data-form-loaded') || this.innerHTML);
                    $(this).removeAttr('data-form-loaded').removeClass('layui-disabled');
                });
                return $(form).data('validate', this);
            };
        }).check(form, callback, options);
    };

    /*! 自动监听规则内表单 */
    $.vali.listen = function () {
        $('form[data-auto]').map(function () {
            if ($(this).attr('data-listen') !== 'true') $(this).attr('data-listen', 'true').vali(function (data) {
                var call = $(this).attr('data-callback') || '_default_callback';
                var type = this.getAttribute('method') || 'POST', tips = this.getAttribute('data-tips') || undefined;
                var time = this.getAttribute('data-time') || undefined, href = this.getAttribute('action') || window.location.href;
                $.form.load(href, data, type, window[call] || undefined, true, tips, time);
            });
        });
    };

    /*! 注册对象到JqFn */
    $.fn.vali = function (callback, options) {
        return $.vali(this, callback, options);
    };

    /*! 表单转JSON */
    $.fn.formToJson = function () {
        var self = this, data = {}, pushCounters = {};
        var patterns = {"key": /[a-zA-Z0-9_]+|(?=\[\])/g, "push": /^$/, "fixed": /^\d+$/, "named": /^[a-zA-Z0-9_]+$/};
        this.build = function (base, key, value) {
            base[key] = value;
            return base;
        };
        this.pushCounter = function (name) {
            if (pushCounters[name] === undefined) pushCounters[name] = 0;
            return pushCounters[name]++;
        };
        $.each($(this).serializeArray(), function () {
            var key, keys = this.name.match(patterns.key), merge = this.value, name = this.name;
            while ((key = keys.pop()) !== undefined) {
                name = name.replace(new RegExp("\\[" + key + "\\]$"), '');
                if (key.match(patterns.push)) { // push
                    merge = self.build([], self.pushCounter(name), merge);
                } else if (key.match(patterns.fixed)) { // fixed
                    merge = self.build([], key, merge);
                } else if (key.match(patterns.named)) { // named
                    merge = self.build({}, key, merge);
                }
            }
            data = $.extend(true, data, merge);
        });
        return data;
    };

    /*! 全局文件上传入口 */
    $.fn.uploadFile = function (callback) {
        if (this.attr('data-inited')) return false;
        var that = this, mode = $(this).attr('data-file') || 'one';
        this.attr('data-inited', true).attr('data-multiple', (mode !== 'btn' && mode !== 'one') ? 1 : 0);
        require(['upload'], function (apply) {
            apply.call(this, that, callback);
        });
    };

    /*! 上传单个附件 */
    $.fn.uploadOneAttach = function(){
        return this.each(function (input, template) {
            input = $(this), template = $('<button type="button" class="layui-btn"><i class="layui-icon"></i>上传附件</button>'), attachVal = this.value ? this.value : "";
            template.attr('data-type', input.data('type') || 'doc,docx,xls,xlsx,ppt,pptx,pdf,zip,rar,tar.gz,tar');
            template.attr('data-catid', input.data('catid') || 0);
            template.attr('data-modelid', input.data('modelid') || 1);
            template.attr('data-field', input.attr('name') || 'attach').data('input', this);
            input.attr('name', template.attr('data-field')).after(template)
            template.uploadFile(function (url, fileobj) {
                input.val(fileobj.name+'|'+fileobj.size+'|'+url);
                showAttach(fileobj);
            });
            if(attachVal != ""){
                var fileData = attachVal.split('|');
                var fileobj = {name:fileData[0],size:fileData[1],xurl:fileData[2]};
                showAttach(fileobj);
            }
            function showAttach(fileobj) {
                $info = '<div class="layui-upload-list"><table class="layui-table"><thead><tr><th>文件名</th><th>文件大小</th><th>服务器地址</th></tr></thead><tbody><tr><td>'+fileobj.name+'</td><td>'+ (fileobj.size/1024).toFixed(1) +'kb</td><td>'+fileobj.xurl+'</td></tr></tbody></table></div>';
                template.before($info);
            }
        }), this;
    };

    /*! 上传多个附件 */
    $.fn.uploadMultipleAttach = function(){
        return this.each(function () {
            var $button = $('<button type="button" class="layui-btn"><i class="layui-icon"></i>上传附件</button>');
            var attachObjs = [];
            //获取默认值
            if(this.value != ""){
                var attachData = this.value.split('|');
                for(i = 0,arrlen = attachData.length;i<arrlen;i++){
                    var fileData = attachData[i].split(':');
                    var fileObj = {name:fileData[0],size:fileData[1],xurl:fileData[2]};
                    attachObjs.push(fileObj);
                }
            }
            var $input = $(this), name = $input.attr('name') || 'attach',catid = $input.data('catid'),modelid = $input.data('modelid'),upmaxsize = $input.data('upmaxsize') || 10, type = $input.data('type') || 'doc,docx,xls,xlsx,ppt,pptx,pdf,zip,rar,tar.gz,tar';
            $button.attr('data-type', type).attr('data-field', name).attr('data-file', 'mut').data('catid', catid).data('modelid', modelid).data('upmaxsize', upmaxsize).data('input', this); //data('upmaxsize', upmaxsize)是设置当前可以上传的文件个数
            $input.attr('name', name).after($button);
            var $attachStr = '<div class="layui-upload-list"  style="display:none" id="layui-upload-list"><table class="layui-table"><thead><tr><th>文件名</th><th>文件大小</th><th>服务器地址</th></tr></thead><tbody id="attach_tbody"></tbody></table></div>';
            $button.uploadFile(function (url, fileobj) {
                attachObjs.push(fileobj.name+':'+fileobj.size+':'+url), $input.val(attachObjs.join('|'));
                $("#layui-upload-list").show();
                $("#attach_tbody").append(showAttachContainer([fileobj]));
            });
            $button.before($attachStr);
            if (attachObjs.length > 0){
                $attachStr += showAttachContainer(attachObjs);
                $button.before($attachStr);
            }
            function showAttachContainer(fileobjs) {
                var $attachStr = '';
                $(fileobjs).each(function (idx, fileobj) {
                    $attachStr += '<tr><td>'+fileobj.name+'</td><td>'+ (fileobj.size/1024).toFixed(1) +'kb</td><td>'+fileobj.xurl+'</td></tr>';
                });
                return $attachStr;
            }
        }), this;
    };

    /*! 上传单张图片 */
    $.fn.uploadOneImage = function () {
        return this.each(function (input, template) {
            input = $(this), template = $('<a data-file="one" class="uploadimage"><span class="layui-icon">&#x1006;</span></a>');
            template.attr('data-type', input.data('type') || 'png,jpg,gif');
            template.attr('data-catid', input.data('catid') || 0);
            template.attr('data-modelid', input.data('modelid') || 1);
            template.attr('data-field', input.attr('name') || 'image').data('input', this);
            template.find('span').on('click', function (event) {
                event.stopPropagation(), template.attr('style', ''), input.val('');
            });
            input.attr('name', template.attr('data-field')).after(template).on('change', function () {
                if (this.value) template.css('backgroundImage', 'url(' + encodeURI(this.value) + ')');
            }).trigger('change');
        }), this;
    };

    /*! 上传多张图片 */
    $.fn.uploadMultipleImage = function () {
        return this.each(function () {
            var $button = $('<a class="uploadimage"></a>'), images = this.value ? this.value.split('|') : [];
            var $input = $(this), name = $input.attr('name') || 'umt-image',catid = $input.data('catid'),modelid = $input.data('modelid'),upmaxsize = $input.data('upmaxsize') || 10, type = $input.data('type') || 'png,jpg,gif';
            $button.attr('data-type', type).attr('data-field', name).attr('data-file', 'mut').data('catid', catid).data('modelid', modelid).data('upmaxsize', upmaxsize).data('input', this);
            $input.attr('name', name).after($button), $button.uploadFile(function (src) {
                images.push(src), $input.val(images.join('|')), showImageContainer([src]);
            });
            if (images.length > 0) showImageContainer(images);

            function showImageContainer(srcs) {
                $(srcs).each(function (idx, src, $image) {
                    $image = $('<div class="uploadimage uploadimagemtl"><a class="layui-icon margin-right-5">&#xe602;</a><a class="layui-icon margin-right-5">&#x1006;</a><a class="layui-icon margin-right-5">&#xe603;</a></div>');
                    $image.attr('data-tips-image', encodeURI(src)).css('backgroundImage', 'url(' + encodeURI(src) + ')').on('click', 'a', function (event, index, prevs, $item) {
                        event.stopPropagation(), $item = $(this).parent(), index = $(this).index(), prevs = $button.prevAll('div.uploadimage').length;
                        if (index === 0 && $item.index() !== prevs) $item.next().after($item);
                        else if (index === 2 && $item.index() > 1) $item.prev().before($item);
                        else if (index === 1) $item.remove();
                        images = [], $button.prevAll('.uploadimage').map(function () {
                            images.push($(this).attr('data-tips-image'));
                        });
                        images.reverse(), $input.val(images.join('|'));
                    }), $button.before($image);
                });
            };
        }), this;
    };

    /*! 注册 data-load 事件行为 */
    $body.on('click', '[data-load]', function () {
        var url = $(this).attr('data-load'), tips = $(this).attr('data-tips'), time = $(this).attr('data-time');
        if ($(this).attr('data-confirm')) return $.msg.confirm($(this).attr('data-confirm'), function () {
            $.form.load(url, {}, 'get', null, true, tips, time);
        });
        $.form.load(url, {}, 'get', null, true, tips, time);
    });

    /*! 注册 data-serach 表单搜索行为 */
    $body.on('submit', 'form.form-search', function () {
        var url = $(this).attr('action').replace(/&?page=\d+/g, ''), split = url.indexOf('?') === -1 ? '?' : '&';
        if ((this.method || 'get').toLowerCase() === 'get') {
            if (location.href.indexOf('spm=') > -1) {
                return window.location.href = '#' + $.menu.parseUri(url + split + $(this).serialize());
            } else {
                return window.location.href = $.menu.parseUri(url + split + $(this).serialize());
            }
        }
        $.form.load(url, this, 'post');
    });

    /*! 注册 data-modal 事件行为 */
    $body.on('click', '[data-modal]', function () {
        return $.form.modal($(this).attr('data-modal'), 'open_type=modal', $(this).attr('data-title') || $(this).text() || '编辑');
    });

    /*! 注册 data-open 事件行为 */
    $body.on('click', '[data-open]', function () {
        $.form.href($(this).attr('data-open'), this);
    });

    /*! 注册 data-dbclick 事件行为 */
    $body.on('dblclick', '[data-dbclick]', function () {
        $(this).find(this.getAttribute('data-dbclick') || '[data-dbclick]').trigger('click');
    });

    /*! 注册 data-reload 事件行为 */
    $body.on('click', '[data-reload]', function () {
        $.form.reload();
    });

    /*! 注册 data-check 事件行为 */
    $body.on('click', '[data-check-target]', function () {
        var checked = !!this.checked;
        $($(this).attr('data-check-target')).map(function () {
            this.checked = checked;
            $(this).trigger('change');
        });
    });

    /*! 注册 data-action 事件行为 */
    $body.on('click', '[data-action]', function () {
        var $this = $(this), data = {}, time = $this.attr('data-time'), action = $this.attr('data-action');
        var loading = $this.attr('data-loading'), method = $this.attr('data-method') || 'post';
        var rule = $this.attr('data-value') || (function (rule, ids) {
            $($this.attr('data-target') || 'input[type=checkbox].list-check-box').map(function () {
                (this.checked) && ids.push(this.value);
            });
            return ids.length > 0 ? rule.replace('{key}', ids.join(',')) : '';
        }).call(this, $this.attr('data-rule') || '', []) || '';
        if (rule.length < 1) return $.msg.tips('请选择需要更改的数据！');
        var rules = rule.split(';');
        for (var i in rules) {
            if (rules[i].length < 2) return $.msg.tips('异常的数据操作规则，请修改规则！');
            data[rules[i].split('#')[0]] = rules[i].split('#')[1];
        }
        data['_token_'] = $this.attr('data-token') || $this.attr('data-csrf') || '--';
        var load = loading !== 'false', tips = typeof loading === 'string' ? loading : undefined;
        if (!$this.attr('data-confirm')) $.form.load(action, data, method, false, load, tips, time);
        else $.msg.confirm($this.attr('data-confirm'), function () {
            $.form.load(action, data, method, false, load, tips, time);
        });
    });

    /*! 表单元素失焦时提交 */
    $body.on('blur', '[data-action-blur]', function () {
        var data = {}, that = this, $this = $(this), action = $this.attr('data-action-blur');
        var time = $this.attr('data-time'), loading = $this.attr('data-loading') || false;
        var load = loading !== 'false', tips = typeof loading === 'string' ? loading : undefined;
        var method = $this.attr('data-method') || 'post', confirm = $this.attr('data-confirm');
        var attrs = $this.attr('data-value').replace('{value}', $this.val()).split(';');
        for (var i in attrs) {
            if (attrs[i].length < 2) return $.msg.tips('异常的数据操作规则，请修改规则！');
            data[attrs[i].split('#')[0]] = attrs[i].split('#')[1];
        }
        that.callback = function (ret) {
            $this.css('border', (ret && ret.code) ? '1px solid #e6e6e6' : '1px solid red');
            return false;
        };
        data['_token_'] = $this.attr('data-token') || $this.attr('data-csrf') || '--';
        if (!confirm) return $.form.load(action, data, method, that.callback, load, tips, time);
        $.msg.confirm(confirm, function () {
            $.form.load(action, data, method, that.callback, load, tips, time);
        });
    });

    /*! 使用原生ajax方式来进行文件下载 */
    $body.on('click', '[data-action-down]', function () {
        var $this = $(this), data = {}, time = $this.attr('data-time'), action = $this.attr('data-action-down');
        var loading = $this.attr('data-loading'), method = $this.attr('data-method') || 'post';
        var rule = $this.attr('data-value') || (function (rule, ids) {
            $($this.attr('data-target') || 'input[type=checkbox].list-check-box').map(function () {
                (this.checked) && ids.push(this.value);
            });
            return ids.length > 0 ? rule.replace('{key}', ids.join(',')) : '';
        }).call(this, $this.attr('data-rule') || '', []) || '';
        if (rule.length < 1) return $.msg.tips('请选择需要更改的数据！');
        var rules = rule.split(';');
        for (var i in rules) {
            if (rules[i].length < 2) return $.msg.tips('异常的数据操作规则，请修改规则！');
            data[rules[i].split('#')[0]] = rules[i].split('#')[1];
        }
        data['_token_'] = $this.attr('data-token') || $this.attr('data-csrf') || '--';
        var url = '';
        if(method == 'GET'){
            var contact = '?'
            for(var key in data){
                contact += key+'='+data[key]+'&'
            }
            url = action+contact.substr(0,contact.length-1);
        }else{
            url = action;
        }
        var xhr = new XMLHttpRequest();
        xhr.open(method, url, true);        // 也可以使用POST方式，根据接口
        xhr.responseType = "blob";    // 返回类型blob
        // 发送ajax请求
        if(method == 'GET'){
            xhr.send()
        }else{
            xhr.send(JSON.stringify(data))
        }
        xhr.onreadystatechange  = function () {
            if(xhr.readyState == 4 && xhr.status == 200){
                if (window.FileReader) {
                    var disposition = this.getResponseHeader('content-disposition');
                    var fileInfo = disposition.split(";")[1].trim();
                    var filename = fileInfo.split('=')[1].replace(/\"/g, '');
                    var blob = this.response;
                    var reader = new FileReader();
                    reader.readAsDataURL(blob);    // 转换为base64，可以直接放入a表情href
                    reader.onload = function (e) {
                        // 转换完成，创建一个a标签用于下载
                        var a = document.createElement('a');
                        a.download = filename;
                        a.href = e.target.result;
                        $("body").append(a);    // 修复firefox中无法触发click
                        a.click();
                        $(a).remove();
                    }
                }else{
                    $.msg.tips('请更换google内核或IE10以上的浏览器使用该功能！');
                }
            }else{
                $.msg.tips('下载失败');
            }
        };
    });

    /*! 表单元素失去焦点时数字 */
    $body.on('blur', '[data-blur-number]', function (fiexd) {
        fiexd = this.getAttribute('data-blur-number') || 0;
        this.value = (parseFloat(this.value) || 0).toFixed(fiexd);
    });

    /*! 注册 data-href 事件行为 */
    $body.on('click', '[data-href]', function (href) {
        href = $(this).attr('data-href');
        if (href && href.indexOf('#') !== 0) window.location.href = href;
    });

    /*! 注册 data-iframe 事件行为 */
    $body.on('click', '[data-iframe]', function () {
        let area = undefined;
        if($(this).attr('data-area')){
            area = eval($(this).attr('data-area'));
        }
        $(this).attr('data-index', $.form.iframe(
            $(this).attr('data-iframe'), $(this).attr('data-title') || '窗口',area)
        );
    });

    /*! 注册 data-icon 事件行为 */
    $body.on('click', '[data-icon]', function (field, location) {
        location = tapiRoot + '/api.plugs/icon';
        field = $(this).attr('data-icon') || $(this).attr('data-field') || 'icon';
        $.form.iframe(location + (location.indexOf('?') > -1 ? '&' : '?') + 'field=' + field, '图标选择');
    });

    /*! 注册 data-copy 事件行为 */
    $body.on('click', '[data-copy]', function () {
        $.copyToClipboard(this.getAttribute('data-copy'));
    });
    $.copyToClipboard = function (content, input) {
        input = document.createElement('textarea');
        input.style.position = 'absolute', input.style.left = '-100000px';
        input.style.width = '1px', input.style.height = '1px', input.innerText = content;
        document.body.appendChild(input), input.select(), setTimeout(function () {
            document.execCommand('Copy') ? $.msg.tips('复制成功') : $.msg.tips('复制失败，请使用鼠标操作复制！');
            document.body.removeChild(input);
        }, 100);
    };

    /*! 注册 data-tips-text 事件行为 */
    $body.on('mouseenter', '[data-tips-text]', function () {
        $(this).attr('index', layer.tips($(this).attr('data-tips-text'), this, {tips: [$(this).attr('data-tips-type') || 3, '#78BA32']}));
    }).on('mouseleave', '[data-tips-text]', function () {
        layer.close($(this).attr('index'));
    });

    /*! 注册 data-tips-image 事件行为 */
    $body.on('click', '[data-tips-image]', function () {
        $.previewImage(this.getAttribute('data-tips-image') || this.src, this.getAttribute('data-width'));
    });
    $.previewImage = function (src, area) {
        var img = new Image(), index = $.msg.loading();
        img.style.background = '#fff', img.style.display = 'none';
        img.style.height = 'auto', img.style.width = area || '480px';
        document.body.appendChild(img), img.onerror = function () {
            $.msg.close(index);
        }, img.onload = function () {
            layer.open({
                type: 1, shadeClose: true, success: img.onerror, content: $(img), title: false,
                area: area || '480px', closeBtn: 1, skin: 'layui-layer-nobg', end: function () {
                    document.body.removeChild(img);
                }
            });
        };
        img.src = src;
    };

    /*! 注册 data-phone-view 事件行为 */
    $body.on('click', '[data-phone-view]', function () {
        $.previewPhonePage(this.getAttribute('data-phone-view') || this.href);
    });
    $.previewPhonePage = function (href, title, template) {
        template = '<div><div class="mobile-preview pull-left"><div class="mobile-header">_TITLE_</div><div class="mobile-body"><iframe id="phone-preview" src="_URL_" frameborder="0" marginheight="0" marginwidth="0"></iframe></div></div></div>';
        layer.style(layer.open({type: true, scrollbar: false, area: ['320px', '600px'], title: false, closeBtn: true, shadeClose: false, skin: 'layui-layer-nobg', content: $(template.replace('_TITLE_', title || '公众号').replace('_URL_', href)).html()}), {boxShadow: 'none'});
    };

    /*! 表单编辑返回操作 */
    $body.on('click', '[data-history-back]', function (title) {
        title = this.getAttribute('data-history-back') || '确定要返回上一页吗？';
        $.msg.confirm(title, function (index) {
            history.back();
            $.msg.close(index);
        })
    });

    /*! 异步任务状态监听与展示 */
    $body.on('click', '[data-queue]', function (action) {
        action = this.getAttribute('data-queue') || '';
        if (action.length < 1) return $.msg.tips('任务地址不能为空！');
        this.doRuntime = function (index) {
            $.form.load(action, {}, 'post', function (ret) {
                if (typeof ret.data === 'string' && ret.data.indexOf('Q') === 0) {
                    return $.loadQueue(ret.data, true), false;
                }
            }), $.msg.close(index);
        };
        $(this).attr('data-confirm') ? $.msg.confirm($(this).attr('data-confirm'), this.doRuntime) : this.doRuntime(0);
    });
    $.loadQueue = function (code, doScript, doAjax) {
        layer.open({
            type: 1, title: false, area: ['560px', '315px'], anim: 2, shadeClose: false, end: function () {
                doAjax = false;
            }, content: '' +
            '<div class="padding-30 padding-bottom-0" style="width:500px" data-queue-load="' + code + '">' +
            '   <div class="layui-elip nowrap" data-message-title></div>' +
            '   <div class="margin-top-15 layui-progress layui-progress-big" lay-showPercent="yes"><div class="layui-progress-bar transition" lay-percent="0.00%"></div></div>' +
            '   <div class="margin-top-15"><textarea class="layui-textarea layui-bg-black border-0" disabled style="resize:none;overflow:hidden;height:190px"></textarea></div>' +
            '</div>'
        });
        (function loadprocess(code, that) {
            that = this, this.$box = $('[data-queue-load=' + code + ']');
            if (doAjax === false || that.$box.length < 1) return false;
            this.$area = that.$box.find('textarea'), this.$title = that.$box.find('[data-message-title]');
            this.$percent = that.$box.find('.layui-progress div'), this.runCache = function (code, index, value) {
                this.ckey = code + '_' + index, this.ctype = 'admin-queue-script';
                return value !== undefined ? layui.data(this.ctype, {key: this.ckey, value: value}) : layui.data(this.ctype)[this.ckey] || 0;
            };
            this.setState = function (status, message) {
                if (message.indexOf('javascript:') === -1) if (status === 1) {
                    that.$title.html('<b class="color-text">' + message + '</b>').addClass('text-center');
                    that.$percent.addClass('layui-bg-blue').removeClass('layui-bg-green layui-bg-red');
                } else if (status === 2) {
                    if (message.indexOf('>>>') > -1) {
                        that.$title.html('<b class="color-blue">' + message + '</b>').addClass('text-center');
                    } else {
                        that.$title.html('<b class="color-blue">正在处理：</b>' + message).removeClass('text-center');
                    }
                    that.$percent.addClass('layui-bg-blue').removeClass('layui-bg-green layui-bg-red');
                } else if (status === 3) {
                    that.$title.html('<b class="color-green">' + message + '</b>').addClass('text-center');
                    that.$percent.addClass('layui-bg-green').removeClass('layui-bg-blue layui-bg-red');
                } else if (status === 4) {
                    that.$title.html('<b class="color-red">' + message + '</b>').addClass('text-center');
                    that.$percent.addClass('layui-bg-red').removeClass('layui-bg-blue layui-bg-green');
                }
            };
            $.form.load(tapiRoot + '/api.queue/progress', {code: code}, 'post', function (ret) {
                if (ret.code) {
                    that.lines = [];
                    for (this.lineIndex in ret.data.history) {
                        this.line = ret.data.history[this.lineIndex], this.percent = '[ ' + this.line.progress + '% ] ';
                        if (this.line.message.indexOf('javascript:') === -1) {
                            that.lines.push(this.line.message.indexOf('>>>') > -1 ? this.line.message : this.percent + this.line.message);
                        } else if (!that.runCache(code, this.lineIndex) && doScript !== false) {
                            that.runCache(code, this.lineIndex, 1), location.href = this.line.message;
                        }
                    }
                    that.$area.val(that.lines.join("\n")), that.$area.animate({scrollTop: that.$area[0].scrollHeight + 'px'}, 200);
                    that.$percent.attr('lay-percent', (parseFloat(ret.data.progress || '0.00').toFixed(2)) + '%'), layui.element.render();
                    if (ret.data.status > 0) that.setState(parseInt(ret.data.status), ret.data.message);
                    else return that.setState(4, '获取任务详情失败！'), false;
                    if (parseInt(ret.data.status) === 3 || parseInt(ret.data.status) === 4) return false;
                    return setTimeout(function () {
                        loadprocess(code);
                    }, Math.floor(Math.random() * 200)), false;
                }
            }, false);
        })(code)
    };

    /*! 图片加载异常处理 */
    document.addEventListener('error', function (e, elem) {
        elem = e.target;
        if (elem.tagName.toLowerCase() === 'img') {
            elem.src = baseRoot + 'theme/img/404_icon.png';
        }
    }, true);

    /*! 初始化事件 */
    $.menu.listen();
    $.vali.listen();

});
