/*! 定义编辑器标准配置 */
CKEDITOR.editorConfig = function (config) {
    config.language = 'zh-cn';
    config.toolbar = [
        {name: 'document', items: ['Source']},
        {name: 'styles', items: ['Font', 'FontSize']},
        {name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', 'RemoveFormat', 'TextColor', 'BGColor', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', 'NumberedList', 'BulletedList']},
        {name: 'uimage', items: ['Link', 'Unlink', 'Table', 'UploadImage', 'UploadMusic', 'UploadVideo', 'UploadHtml']},
        {name: 'tools', items: ['Maximize']}
    ];
    config.allowedContent = true;
    config.format_tags = 'p;h1;h2;h3;pre';
    config.extraPlugins = 'uimage,umusic,uhtml,uvideo';
    config.removeButtons = 'Underline,Subscript,Superscript';
    config.removeDialogTabs = 'image:advanced;link:advanced';
    config.font_names = '微软雅黑/Microsoft YaHei;宋体/SimSun;新宋体/NSimSun;仿宋/FangSong;楷体/KaiTi;黑体/SimHei;' + config.font_names;
};

/*! 自定义图片上传插件 */
CKEDITOR.plugins.add("uimage", {
    init: function (editor) {
        editor.ui.addButton("UploadImage", {label: "上传本地图片", command: 'uimage', icon: 'image', toolbar: 'insert,10'});
        setTimeout(function () {
            $('#cke_' + editor.name).find('.cke_button__uploadimage_label').parent().map(function () {
                $(this).attr('data-type', 'png,jpg,gif').attr('data-file', 'mul').uploadFile(function (url) {
                    editor.insertElement(CKEDITOR.dom.element.createFromHtml('<img style="max-width:100%;border:0" alt="" src="' + url + '">'));
                });
            });
        }, 100);
    }
});

/*! 自定义视频插入插件 */
CKEDITOR.plugins.add('umusic', {
    init: function (editor) {
        editor.ui.addButton("UploadMusic", {label: "上传MP3文件", command: 'umusic', icon: 'specialchar', toolbar: 'insert,10'});
        setTimeout(function () {
            $('#cke_' + editor.name).find('.cke_button__uploadmusic_label').parent().map(function () {
                $(this).attr('data-type', 'mp3').attr('data-file', 'mul').uploadFile(function (url) {
                    editor.insertElement(CKEDITOR.dom.element.createFromHtml('<audio controls="controls"><source src="' + url + '" type="audio/mpeg"></audio>'));
                });
            });
        }, 100);
    }
});

/*! 自定义视频插入插件 */
CKEDITOR.plugins.add('uvideo', {
    init: function (editor) {
        editor.ui.addButton("UploadVideo", {label: "上传MP4文件", command: 'uvideo', icon: 'flash', toolbar: 'insert,10'});
        setTimeout(function () {
            $('#cke_' + editor.name).find('.cke_button__uploadvideo_label').parent().map(function () {
                $(this).attr('data-type', 'mp4').attr('data-file', 'mul').uploadFile(function (url) {
                    editor.insertElement(CKEDITOR.dom.element.createFromHtml('<video width="100%" controls="controls"><source src="' + url + '" type="audio/mp4"></video>'));
                });
            });
        }, 100);
    }
});
var diyCode = '<div class="ckeditor-dialog-container" style="padding: 20px;">\n' +
    '    <div class="ckeditor-code-toolbar" style="margin-bottom: 15px">代码语言：\n' +
    '        <select name="diylang" class="layui-select">\n' +
    '            <option selected="selected" value="">请选择代码语言</option>\n' +
    '            <option value="properties">.properties</option>\n' +
    '            <option value="asp">ASP</option>\n' +
    '            <option value="actionscript">ActionScript(3.0)/Flash/Flex</option>\n' +
    '            <option value="bash">Bash/Bat</option>\n' +
    '            <option value="css">CSS</option>\n' +
    '            <option value="c">C</option>\n' +
    '            <option value="cpp">C++</option>\n' +
    '            <option value="csharp">C#</option>\n' +
    '            <option value="coffeescript">CoffeeScript</option>\n' +
    '            <option value="d">D</option>\n' +
    '            <option value="dart">Dart</option>\n' +
    '            <option value="delphi">Delphi/Pascal</option>\n' +
    '            <option value="erlang">Erlang</option>\n' +
    '            <option value="go">Golang</option>\n' +
    '            <option value="groovy">Groovy</option>\n' +
    '            <option value="html">HTML</option>\n' +
    '            <option value="java">Java</option>\n' +
    '            <option value="json">JSON</option>\n' +
    '            <option value="javascript">Javascript</option>\n' +
    '            <option value="lua">Lua</option>\n' +
    '            <option value="less">LESS</option>\n' +
    '            <option value="markdown">Markdown</option>\n' +
    '            <option value="objective-c">Objective-C</option>\n' +
    '            <option value="php">PHP</option>\n' +
    '            <option value="perl">Perl</option>\n' +
    '            <option value="python">Python</option>\n' +
    '            <option value="r">R</option>\n' +
    '            <option value="rst">reStructedText</option>\n' +
    '            <option value="ruby">Ruby</option>\n' +
    '            <option value="sql">SQL</option>\n' +
    '            <option value="sass">SASS/SCSS</option>\n' +
    '            <option value="shell">Shell</option>\n' +
    '            <option value="scala">Scala</option>\n' +
    '            <option value="swift">Swift</option>\n' +
    '            <option value="vb">VB/VBScript</option>\n' +
    '            <option value="xml">XML</option>\n' +
    '            <option value="yaml">YAML</option>\n' +
    '            <option value="other">其他语言</option>\n' +
    '        </select>\n' +
    '    </div>\n' +
    '    <textarea name="diycode" class="layui-textarea" style="height:400px "></textarea>\n' +
    '</div>';
/*! 自定义代码插入插件 */
CKEDITOR.plugins.add('uhtml', {
    init: function (editor) {
        editor.ui.addButton("UploadHtml", {label: "插入代码", command: 'uhtml', icon: 'creatediv', toolbar: 'insert,10'});
        editor.addCommand('uhtml', {
            exec: function (editor) {
                layer.open({
                    type:1,
                    title:'插入代码',
                    content:diyCode,
                    area: ['800px', '600px'],
                    btn: ['确定','取消'],
                    yes: function(index, layero){
                        var lang = $('select[name="diylang"] option:selected').val();
                        var codeContent = $('textarea[name="diycode"]').val();
                        if(lang == ''){
                            layer.alert("请选择代码语言");
                            return false;
                        }
                        if(codeContent == ''){
                            layer.alert("请输入代码内容");
                            return false;
                        }
                        element = CKEDITOR.dom.element.createFromHtml('<pre><code class="hljs '+lang+'">\n' + codeContent + '\n</code></pre>');
                        editor.insertElement(element);
                        layer.close(index);
                    },
                    cancel: function(){
                        //右上角关闭回调
                        //return false 开启该代码可禁止点击该按钮关闭
                    }
                });
            }
        });
    }
});