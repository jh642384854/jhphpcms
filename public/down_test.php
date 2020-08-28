<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
</head>
<body>
<button id="download">下载</button>
<script src="https://apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js"></script>
<script>
    $(function () {
        $('#download').click(function () {
            download('http://www.jhphpcms.com/module/Attachment/down.html?id=3');
            /*$.ajax({
                type:"GET",
                url:'http://www.jhphpcms.com/module/Attachment/down.html?id=3',
                success:function (res,textStatus,request) {
                    //console.log(res)
                    var blob = res;
                    var reader = new FileReader();
                    reader.readAsDataURL(blob);    // 转换为base64，可以直接放入a表情href
                    reader.onload = function (e) {
                        // 转换完成，创建一个a标签用于下载
                        var a = document.createElement('a');
                        a.download = 'data.xls';
                        a.href = e.target.result;
                        $("body").append(a);    // 修复firefox中无法触发click
                        a.click();
                        $(a).remove();
                    }
                }
            })*/
        })
    })

    var postDownLoadFile = function (options) {
        var config = $.extend(true, { method: 'post' }, options);
        var $iframe = $('<iframe id="down-file-iframe" />');
        var $form = $('<form target="down-file-iframe" method="' + config.method + '" />');
        $form.attr('action', config.url);
        for (var key in config.data) {
            $form.append('<input type="hidden" name="' + key + '" value="' + config.data[key] + '" />');
        }
        $iframe.append($form);
        $(document.body).append($iframe);
        $form[0].submit();
        $iframe.remove();
    }
    //下面方法参考：https://blog.csdn.net/swl979623074/article/details/77855629/ 和 https://blog.csdn.net/codingnoob/article/details/80879208
    function download($url,$method='GET') {
        var url = $url;
        var xhr = new XMLHttpRequest();
        xhr.open($method, url, true);        // 也可以使用POST方式，根据接口
        xhr.responseType = "blob";    // 返回类型blob
        // 定义请求完成的处理函数，请求前也可以增加加载框/禁用下载按钮逻辑
        xhr.onload = function () {
            var disposition = this.getResponseHeader('content-disposition');
            var fileInfo = disposition.split(";")[1].trim();
            var filename = fileInfo.split('=')[1].replace(/\"/g, '');
            // 请求完成
            if (this.status === 200) {
                // 返回200
                if (window.FileReader) {
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
                    alert('请更换google内核的浏览器使用该功能');
                }
            }
        };
        // 发送ajax请求
        xhr.send()
    }
</script>
</body>
</html>