<?php
/**
 *  插件设置页面
 */
function wpupyun_setting_page() {
// 如果当前用户权限不足
	if (!current_user_can('manage_options')) {
		wp_die('Insufficient privileges!');
	}

	$wpupyun_options = get_option('wpupyun_options');
	if ($wpupyun_options && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce']) && !empty($_POST)) {
		if($_POST['type'] == 'cos_info_set') {

		    $wpupyun_options['no_local_file'] = isset($_POST['no_local_file']);
            $wpupyun_options['serviceName'] = isset($_POST['serviceName']) ? sanitize_text_field(trim(stripslashes($_POST['serviceName']))) : '';
            $wpupyun_options['operatorName'] = isset($_POST['operatorName']) ? sanitize_text_field(trim(stripslashes($_POST['operatorName']))) : '';
            $wpupyun_options['operatorPwd'] = isset($_POST['operatorPwd']) ? sanitize_text_field(trim(stripslashes($_POST['operatorPwd']))) : '';
            $wpupyun_options['opt']['auto_rename'] = isset($_POST['auto_rename']);

            # 设置图片处理参数
            $wpupyun_options = wpupyun_set_img_process_handle($wpupyun_options, $_POST);
            $wpupyun_options = wpupyun_set_thumbsize($wpupyun_options, isset($_POST['disable_thumb']) );

            // 不管结果变没变，有提交则直接以提交的数据 更新wpupyun_options
            update_option('wpupyun_options', $wpupyun_options);
            # 替换 upload_url_path 的值
            update_option('upload_url_path', esc_url_raw(trim(stripslashes($_POST['upload_url_path']))));

            ?>
            <div class="notice notice-success settings-error is-dismissible"><p><strong>设置已保存。</strong></p></div>

            <?php

        } elseif ($_POST['type'] == 'upyun_info_replace') {
            $wpupyun_options = wpupyun_legacy_data_replace();
        }
    }

?>

<style type="text/css">
        .wp-hidden{position: relative;display: inline-block;}
        .wp-hidden .eyes{padding:5px;position: absolute;right: 10px; top:0; color:#0071a1;}
    </style>

<div class="wrap">
    <h1 class="wp-heading-inline">WordPress又拍云对象存储设置【WPUpYun】</h1> <a href="https://www.cnwper.com/702.html" target="_blank"class="page-title-action">插件介绍</a>
        <hr class="wp-header-end">        
    
        <p>WordPress又拍云对象存储（简称:WPUpYun），基于又拍云对象存储与WordPress实现静态资源到又拍云对象存储中。提高网站项目的访问速度，以及静态资源的安全存储功能。</p>
         <p>快速导航：<a href="https://www.laobuluo.com/2113.html" target="_blank">新人建站常用的虚拟主机/云服务器</a> / 站长QQ群： <a href="https://jq.qq.com/?_wv=1027&k=5IpUNWK" target="_blank"> <font color="red">1012423279</font></a>（交流建站和运营） / 公众号：QQ69377078（插件反馈）</p>
       
   
      <hr/>
    <form action="<?php echo wp_nonce_url('./admin.php?page=' . WPUpYun_BASEFOLDER . '/actions.php'); ?>" name="wpcosform" method="post">
        <table class="form-table">
            <tr>
                <th scope="row">
                       服务名称
                    </th>
                <td>
                    <input type="text" name="serviceName" value="<?php echo esc_attr($wpupyun_options['serviceName']); ?>" size="50"
                           placeholder="云存储服务名称"/>

                    <p>创建云存储服务填写的名称。示范： <code>laobuluo</code></p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                       绑定加速域名
                    </th>
                <td>
                    <input type="text" name="upload_url_path" value="<?php echo esc_url(get_option('upload_url_path')); ?>" size="50"
                           placeholder="请输入又拍云存储绑定加速域名"/>

                    <p>1. 一般我们是以：<code>http(s)://{自定义加速域名}</code>，同样不要用"/"结尾。</p>

                    <p>2. 不要使用又拍云存储自带的测试域名，测试域名不提供公开使用，必须自定义绑定备案域名，支持自定义二级目录。</p>

                    <p>3. 示范： <code>http(s)://upyun.laobuluo.com</code></p>

                    <p>4. 示范： <code>http(s)://upyun.laobuluo.com/cnwper</code></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                       授权操作员用户名
                    </th>
                <td>
                    <div class="wp-hidden">
                        <input type="password" name="operatorName" value="<?php echo esc_attr($wpupyun_options['operatorName']); ?>" size="50" placeholder="操作员用户名"/>
                        <div class="eyes">
                            <span class="dashicons dashicons-hidden"></span>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                 <th scope="row">
                       授权操作员密码
                    </th>
                <td>
                    <div class="wp-hidden">
                        <input type="password" name="operatorPwd" value="<?php echo esc_attr($wpupyun_options['operatorPwd']); ?>" size="50" placeholder="操作员密码"/>
                        <div class="eyes">
                            <span class="dashicons dashicons-hidden"></span>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    自动重命名
                </th>
                <td>
                    <input type="checkbox"
                           name="auto_rename"
                        <?php
                        if ($wpupyun_options['opt']['auto_rename']) {
                            echo 'checked="TRUE"';
                        }
                        ?>
                    />
                    <label>上传文件自动重命名，解决中文文件名或者重复文件名问题</label>
                </td>
            </tr>
            <tr>
                <th scope="row">
                       不在本地保存
                    </th>
                <td>
                    <input type="checkbox"
                           name="no_local_file"
                        <?php
                            if ($wpupyun_options['no_local_file']) {
                                echo 'checked="TRUE"';
                            }
					    ?>
                    />

                     <label>如不想在服务器中备份静态文件就 "勾选"。</label>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    禁止缩略图
                </th>
                <td>
                    <input type="checkbox"
                           name="disable_thumb"
                        <?php
                        if (isset($wpupyun_options['opt']['thumbsize'])) {
                            echo 'checked="TRUE"';
                        }
                        ?>
                    />
                    <label>仅生成和上传主图，禁止缩略图裁剪。</label>
                </td>
            </tr>
            <tr>
                <th scope="row">图片处理</th>
                <td>
                    <fieldset>
                        <input type="checkbox" name="img_process_switch" onchange="checkboxOnclick(this)"
                            <?php
                            if( isset($wpupyun_options['opt']['img_process']['switch']) &&
                                $wpupyun_options['opt']['img_process']['switch'] == True){
                                echo 'checked="TRUE"';
                            }
                            ?>
                        >
                        <label>开启图片处理</label>
                        <p class="clashid" style="display:
                        <?php
                        if( isset($wpupyun_options['opt']['img_process']['switch']) &&
                            $wpupyun_options['opt']['img_process']['switch'] == True){
                            echo 'block';
                        } else {
                            echo 'none';
                        }
                        ?>;">
                            <?php
                            if ( !isset($wpupyun_options['opt']['img_process']['style_value'])
                                or $wpupyun_options['opt']['img_process']['style_value'] === '/format/webp/lossless/true'
                                or $wpupyun_options['opt']['img_process']['style_value'] === '' ) {
                                echo '<label>
                                            <input name="img_process_style_choice" type="radio" value="0" checked="TRUE" > webp压缩图片
                                            </label><br/>
                                            <label>
                                            <input name="img_process_style_choice" type="radio" value="1">自定义规则
                                        </label><br/>
                                        <input style="min-width: 348px;"
                                        name="img_process_style_customize" type="text" id="rss_rule" placeholder="请填写自定义规则" 
                                        value="" disabled="disabled">';
                            } else {
                                echo '<label>
                                            <input name="img_process_style_choice" type="radio" value="0" > webp压缩图片
                                            </label><br/>
                                            <label>
                                            <input name="img_process_style_choice" type="radio" value="1" checked="TRUE" >自定义规则
                                        </label><br/>
                                        <input style="min-width: 348px;"
                                        name="img_process_style_customize" type="text" id="rss_rule" placeholder="请填写自定义规则" 
                                        value="' . $wpupyun_options['opt']['img_process']['style_value'] . '" >';
                            }
                            ?>
                        </p>
                        <p>支持又拍云图片处理功能，编辑图片，压缩、转换格式、文字图片水印等。（ <a href="https://help.upyun.com/knowledge-base/image/" target="_blank">官方文档</a>）</p>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <th>
                    
                </th>
                <td><input type="submit" name="submit" value="保存设置" class="button button-primary" /></td>

            </tr>
        </table>
        
        <input type="hidden" name="type" value="cos_info_set">
    </form>
    <hr>
    <p><strong>替换说明：</strong></p>
    <p>1. 网站本地已有静态文件，需要在测试兼容插件之后，将本地文件对应目录上传到对象存储目录中（可用 <a href="https://help.upyun.com/knowledge-base/developer_tools/" target="_blank">FTP工具</a>）</p>
    <p>2. 初次使用对象存储插件，可以通过下面按钮一键快速替换网站内容中的原有图片地址更换为又拍云地址</p>
    <p>3. 如果是从其他对象存储或者外部存储替换的，可用 <a href="https://www.cnwper.com/359.html" target="_blank">WPReplace</a> 插件替换。</p>
    <p>4. 建议不熟悉的朋友先备份网站和数据。</p>
    <table class="form-table">
        <form action="<?php echo wp_nonce_url('./admin.php?page=' . WPUpYun_BASEFOLDER . '/actions.php'); ?>" name="wpupyunform2" method="post">
            <tr>
                <th scope="row">
                    一键替换
                </th>
                <td>
                    <input type="hidden" name="type" value="upyun_info_replace">
                    <?php if(array_key_exists('wpupyun_legacy_data_replace', $wpupyun_options['opt']) && $wpupyun_options['opt']['wpupyun_legacy_data_replace'] == 1) {
                        echo '<input type="submit" disabled name="submit" value="已替换" class="button" />';
                    } else {
                        echo '<input type="submit" name="submit" value="一键替换地址" class="button" />';
                    }
                    ?>
                    <p>一键将本地静态文件URL替换成又拍云对象存储路径，不熟悉的朋友请先备份</p>
                </td>
            </tr>
        </form>
    </table>

    <hr>
    <div style='text-align:center;line-height: 50px;'>
        <a href="https://www.cnwper.com" target="_blank">插件主页</a> | <a href="https://www.cnwper.com/702.html" target="_blank">插件发布页面</a> | <a href="https://jq.qq.com/?_wv=1027&k=5IpUNWK" target="_blank">QQ群：1012423279</a> | 公众号：QQ69377078（插件反馈）

    </div>
</div>

<script>
    function getElementsClass(classnames) {
        let classobj = new Array();
        let classint = 0;
        let tags = document.getElementsByTagName("*");
        for (let i in tags) {
            if (tags[i].nodeType == 1) {
                if (tags[i].getAttribute("class") == classnames) {
                    classobj[classint] = tags[i];
                    classint++;
                }
            }
        }
        return classobj;
    }

    let eyes = getElementsClass("eyes");

    for (let i = 0; i < eyes.length; i++) {

        (function(i) {
            eyes[i].onclick = function() {
                let inpu = this.previousElementSibling;
                if (inpu.type == "password") {
                    inpu.type = "text";
                    this.children[0].classList.replace("dashicons-hidden", "dashicons-visibility");
                } else {
                    inpu.type = "password";
                    this.children[0].classList.replace("dashicons-visibility", "dashicons-hidden");
                }
            }
        })(i);
    }

    // img_handler
    let clashid = getElementsClass("clashid");
    function checkboxOnclick(checkbox){
        if ( checkbox.checked){
            clashid[0].style.display='block';
        }else{
            clashid[0].style.display='none';
        }
    }
    let selectValue = null;
    let els = document.querySelectorAll("[name=img_process_style_choice]");
    let rule = document.querySelectorAll("[name=img_process_style_customize]");
    for (el of els) {
        el.addEventListener("click", function() {
            if (selectValue == this.value && selectValue) {
                this.checked = "";
                selectValue = null;
            } else {
                selectValue = this.value;
            }
            if(selectValue=='1'){
                rule[0].disabled= false;
            }else{
                rule[0].disabled= true;
            }
        });
    }
</script>


<?php
}
?>