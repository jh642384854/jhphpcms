/*!
 * Image (upload) dialog plugin for Editor.md
 *
 * @file        diynotice-dialog.js
 * @author      pandao
 * @version     1.3.4
 * @updateTime  2015-06-09
 * {@link       https://github.com/pandao/editor.md}
 * @license     MIT
 */

(function() {

    var factory = function (exports) {

        var pluginName   = "diynotice-dialog";

        exports.fn.diynoticeDialog = function() {

            var _this       = this;
            var cm          = this.cm;
            var lang        = this.lang;
            var editor      = this.editor;
            var settings    = this.settings;
            var cursor      = cm.getCursor();
            var selection   = cm.getSelection();
            var classPrefix = this.classPrefix;
            var iframeName  = classPrefix + "diynotice-iframe";
            var dialogName  = classPrefix + pluginName, dialog;

            cm.focus();

            var loading = function(show) {
                var _loading = dialog.find("." + classPrefix + "dialog-mask");
                _loading[(show) ? "show" : "hide"]();
            };

            var guid   = (new Date).getTime();
            var dialogContent = '<div><select style="display: inline;width: 100%">' +
                '<option value="">请选择容器类型</option><option value="tip">提示</option><option value="warning">警告</option><option value="danger">危险</option><option value="details">详情</option>' +
                '</select></div>';

            dialog = this.createDialog({
                title      : '自定义容器',
                width      : 380,
                height     : 254,
                name       : dialogName,
                content    : dialogContent,
                mask       : settings.dialogShowMask,
                drag       : settings.dialogDraggable,
                lockScreen : settings.dialogLockScreen,
                maskStyle  : {
                    opacity         : settings.dialogMaskOpacity,
                    backgroundColor : settings.dialogMaskBgColor
                },
                buttons : {
                    enter : [lang.buttons.enter, function() {
                        var url  = this.find("[data-url]").val();
                        var alt  = this.find("[data-alt]").val();
                        var link = this.find("[data-link]").val();

                        cm.replaceSelection("[" + selection + ":testIcon]");

                        this.hide().lockScreen(false).hideMask();
                        //删除对话框
                        this.remove();
                        return false;
                    }],

                    cancel : [lang.buttons.cancel, function() {
                        this.hide().lockScreen(false).hideMask();
                        //删除对话框
                        this.remove();
                        return false;
                    }]
                }
            });
            dialog.attr("id", classPrefix + "diynotice-dialog-" + guid);

            this.dialogShowMask(dialog);
            this.dialogLockScreen();
            dialog.show();
        };

    };

    // CommonJS/Node.js
    if (typeof require === "function" && typeof exports === "object" && typeof module === "object")
    {
        module.exports = factory;
    }
    else if (typeof define === "function")  // AMD/CMD/Sea.js
    {
        if (define.amd) { // for Require.js

            define(["editormd"], function(editormd) {
                factory(editormd);
            });

        } else { // for Sea.js
            define(function(require) {
                var editormd = require("./../../editormd");
                factory(editormd);
            });
        }
    }
    else
    {
        factory(window.editormd);
    }

})();
