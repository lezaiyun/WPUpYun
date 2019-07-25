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

		    $wpupyun_options['no_local_file'] = (isset($_POST['no_local_file'])) ? True : False;
            $wpupyun_options['serviceName'] = (isset($_POST['serviceName'])) ? sanitize_text_field(trim(stripslashes($_POST['serviceName']))) : '';
            $wpupyun_options['operatorName'] = (isset($_POST['operatorName'])) ? sanitize_text_field(trim(stripslashes($_POST['operatorName']))) : '';
            $wpupyun_options['operatorPwd'] = (isset($_POST['operatorPwd'])) ? sanitize_text_field(trim(stripslashes($_POST['operatorPwd']))) : '';

            // 不管结果变没变，有提交则直接以提交的数据 更新wpupyun_options
            update_option('wpupyun_options', $wpupyun_options);

            # 替换 upload_url_path 的值
            update_option('upload_url_path', esc_url_raw(trim(trim(stripslashes($_POST['upload_url_path'])))));

            ?>
            <div style="font-size: 25px;color: red; margin-top: 20px;font-weight: bold;"><p>WP又拍云插件设置保存完毕!!!</p></div>

            <?php

        }
    }

?>

    <style>
        table {
            border-collapse: collapse;
        }

        table, td, th {border: 1px solid #cccccc;padding:5px;}
        .buttoncss {background-color: #4CAF50;
            border: none;cursor:pointer;
            color: white;
            padding: 15px 22px;
            text-align: center;
            text-decoration: none;
            display: inline-block;border-radius: 5px;
            font-size: 12px;font-weight: bold;
        }
        .buttoncss:hover {
            background-color: #008CBA;
            color: white;
        }
        input{border: 1px solid #ccc;padding: 5px 0px;border-radius: 3px;padding-left:5px;}
    </style>
<div style="margin:5px;">
    <h2>WordPress UpYun（WPUpYun）又拍云对象存储设置</h2>
    <hr/>
    
        <p>WordPress UpYun（简称:WPUpYun），基于又拍云对象存储与WordPress实现静态资源到又拍云对象存储中。提高网站项目的访问速度，以及静态资源的安全存储功能。</p>
        <p>插件网站： <a href="https://www.laobuluo.com" target="_blank">老部落</a> / <a href="https://www.laobuluo.com/2620.html" target="_blank">WPUpYun插件发布页面地址</a>  / 站长创业交流QQ群： <a href="https://jq.qq.com/?_wv=1027&k=5gBE7Pt" target="_blank"> <font color="red">594467847</font></a>（宗旨：多做事，少说话）</p>
        <p>推荐文章： <a href="https://www.laobuluo.com/2113.html" target="_blank">新人建站常用的虚拟主机/云服务器 常用主机商选择建议</a></p>
   
      <hr/>
    <form action="<?php echo wp_nonce_url('./admin.php?page=' . WPUpYun_BASEFOLDER . '/actions.php'); ?>" name="wpcosform" method="post">
        <table>
            <tr>
                <td style="text-align:right;">
                    <b>服务名称：</b>
                </td>
                <td>
                    <input type="text" name="serviceName" value="<?php echo esc_attr($wpupyun_options['serviceName']); ?>" size="50"
                           placeholder="Service Name"/>

                    <p>1. 创建云存储服务填写的名称。示范： <code>laobuluo</code></p>
                </td>
            </tr>

            <tr>
                <td style="text-align:right;">
                    <b>绑定加速域名：</b>
                </td>
                <td>
                    <input type="text" name="upload_url_path" value="<?php echo esc_url(get_option('upload_url_path')); ?>" size="50"
                           placeholder="请输入又拍云存储绑定加速域名"/>

                    <p><b>设置注意事项：</b></p>

                    <p>1. 一般我们是以：<code>http(s)://{自定义加速域名}</code>，同样不要用"/"结尾。</p>

                    <p>2. 不要使用又拍云存储自带的测试域名，测试域名不提供公开使用，必须自定义绑定备案域名。</p>

                    <p>3. 示范： <code>http(s)://upyun.laobuluo.com</code></p>
                </td>
            </tr>
            <tr>
                <td style="text-align:right;">
                    <b>授权操作员用户名：</b>
                 </td>
                <td><input type="text" name="operatorName" value="<?php echo esc_attr($wpupyun_options['operatorName']); ?>" size="50" placeholder="operatorName"/></td>
            </tr>
            <tr>
                <td style="text-align:right;">
                    <b>授权操作员密码：</b>
                </td>
                <td>
                    <input type="text" name="operatorPwd" value="<?php echo esc_attr($wpupyun_options['operatorPwd']); ?>" size="50" placeholder="operatorPwd"/>
                </td>
            </tr>
            <tr>
                <td style="text-align:right;">
                    <b>不在本地保存：</b>
                </td>
                <td>
                    <input type="checkbox"
                           name="no_local_file"
                        <?php
                            if ($wpupyun_options['no_local_file']) {
                                echo 'checked="TRUE"';
                            }
					    ?>
                    />

                    <p>如果不想同步在服务器中备份静态文件就 "勾选"。我个人喜欢只存储在又拍云对象存储中，这样缓解服务器存储量。</p>
                </td>
            </tr>
            
            <tr>
                <th>
                    
                </th>
                <td><input type="submit" name="submit" value="保存WPUpYun设置" class="buttoncss" /></td>

            </tr>
        </table>
        
        <input type="hidden" name="type" value="cos_info_set">
    </form>
    <p><b>WPUPYUN插件注意事项：</b></p>
    <p>1. 如果我们有多个网站需要使用WPUPYUN插件，需要给每一个网站独立不同的存储空间。</p>
    <p>2. 使用WPUPYUN插件分离图片、附件文件，存储在WPUPYUN存储空间根目录，比如：2019、2018、2017这样的直接目录，不会有wp-content这样目录。</p>
    <p>3. 如果我们已运行网站需要使用WPUPYUN插件，插件激活之后，需要将本地wp-content目录中的文件对应时间目录上传至WPUPYUN存储空间中，且需要在数据库替换静态文件路径生效。</p>
    <p>4. 详细使用教程参考：<a href="https://www.laobuluo.com/2620.html" target="_blank">WPUPYUN发布页面地址</a>，或者加入QQ群： <a href="https://jq.qq.com/?_wv=1027&k=5gBE7Pt" target="_blank"> <font color="red">594467847</font></a>。</p>
</div>
<?php
}
?>