<?php /*a:1:{s:64:"F:\php_study\third\www.jhphpcms.com\app\cms\stubs\text\form.html";i:1590470445;}*/ ?>
<div class="layui-form-item">
    <label class="layui-form-label">表单长度</label>
    <div class="layui-input-block">
        <input name="name" value='<?php echo htmlentities((isset($data) && ($data !== '')?$data:"456")); ?>' required placeholder="请输入模型名称" class="layui-input">
        <p class="help-block">必填，请填写模型名称（如：文章模型），建议字符不要太长，一般4-6个汉字</p>
    </div>
</div>