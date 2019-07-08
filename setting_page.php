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
        <p>插件网站： <a href="https://www.laobuluo.com" target="_blank">老部落</a> / <a href="https://www.laobuluo.com/2186.html" target="_blank">WPCOS发布页面地址</a> / <a href="https://www.laobuluo.com/2196.html" target="_blank"> <font color="red">WP又拍云安装详细教程</font></a></p>
        <p>优惠促销： <a href="https://www.laobuluo.com/tengxunyun/" target="_blank">最新腾讯云优惠汇总</a> / <a href="https://www.laobuluo.com/goto/qcloud-cos" target="_blank">腾讯云COS资源包优惠</a></p>
        <p>站长互助QQ群： <a href="https://jq.qq.com/?_wv=1027&k=5gBE7Pt" target="_blank"> <font color="red">594467847</font></a>（宗旨：多做事，少说话，效率至上）</p>
   
      <hr/>
    <form action="<?php echo wp_nonce_url('./admin.php?page=' . WPUpYun_BASEFOLDER . '/actions.php'); ?>" name="wpcosform" method="post">
        <table>
            <tr>
                <td style="text-align:right;">
                    <b>Service名称：</b>
                </td>
                <td>
                    <input type="text" name="serviceName" value="<?php echo esc_attr($wpupyun_options['serviceName']); ?>" size="50"
                           placeholder="Service Name"/>

                    <p>1. 需要在腾讯云创建<code>bucket</code>存储桶。注意：填写"存储桶名称-对应ID"。</p>
                    <p>2. 示范： <code>laobuluo-xxxxxx</code></p>
                </td>
            </tr>

            <tr>
                <td style="text-align:right;">
                    <b>又拍云远程地址：</b>
                </td>
                <td>
                    <input type="text" name="upload_url_path" value="<?php echo esc_url(get_option('upload_url_path')); ?>" size="50"
                           placeholder="请输入又拍云远程地址"/>

                    <p><b>设置注意事项：</b></p>

                    <p>1. 一般我们是以：<code>http://{cos域名}/{本地文件夹}</code>，同样不要用"/"结尾。</p>

                    <p>2. <code>{cos域名}</code> 是需要在设置的存储桶中查看的。"存储桶列表"，当前存储桶的"基础配置"的"访问域名"中。</p>

                    <p>3. 如果我们自定义域名的，<code>{cos域名}</code> 则需要用到我们自己自定义的域名。</p>
                    <p>4. 示范1： <code>https://laobuluo-xxxxxxx.cos.ap-shanghai.myqcloud.com/wp-content/uploads</code></p>
                    <p>5. 示范2： <code>https://cos.laobuluo.com/wp-content/uploads</code></p>
                </td>
            </tr>
            <tr>
                <td style="text-align:right;">
                    <b>OperatorName 设置：</b>
                 </td>
                <td><input type="text" name="operatorName" value="<?php echo esc_attr($wpupyun_options['operatorName']); ?>" size="50" placeholder="operatorName"/></td>
            </tr>
            <tr>
                <td style="text-align:right;">
                    <b>OperatorPwd 设置：</b>
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
</div>
<?php
}
?>