<?php
require_once 'api.php';
# SDK最低支持版本

define( 'WPUpYun_VERSION', 2.1 );  // 插件数据版本
define( 'WPUpYun_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );  // 插件路径
define( 'WPUpYun_BASENAME', plugin_basename(__FILE__));
define( 'WPUpYun_BASEFOLDER', plugin_basename(dirname(__FILE__)));

// 初始化选项
function wpupyun_set_options() {
    $options = array(
	    'version' => WPUpYun_VERSION,  # 用于以后当有数据结构升级时初始化数据
	    'serviceName' => "",
		'operatorName' => "",
		'operatorPwd' => "",
		'no_local_file' => False,  # 不在本地保留备份
	    'upyun_url_path' => '',
        'opt' => array(
            'auto_rename' => False,
            'img_process' => array(
                'switch' => False,
                'style_value' => '',
            ),
        ),  # 此行往下为2.0版本新增选项
	);
	$wpupyun_options = get_option('wpupyun_options');
	if(!$wpupyun_options){
        add_option('wpupyun_options', $options, '', 'yes');
	};

	if ( isset($wpupyun_options['upyun_url_path']) && $wpupyun_options['upyun_url_path'] != '' ) {
		update_option('upload_url_path', $wpupyun_options['upyun_url_path']);
	}
}

// 升级选项内容
function wpupyun_upgrade_options($plugin){
    if ($plugin == 'wpupyun') {
        $wpupyun_options = get_option('wpupyun_options');
        if ($wpupyun_options['version'] == 1.0) {
            $wpupyun_options['opt'] = array(
                'auto_rename' => False,
                'img_process' => array(
                    'switch' => False,
                    'style_value' => '',
                )
            );  // 自动重命名开关
            $wpupyun_options['version'] = WPUpYun_VERSION;
        }
        update_option('wpupyun_options', $wpupyun_options);
    }
}

function wpupyun_restore_options () {
    $wpupyun_options = get_option('wpupyun_options');
    $wpupyun_options['upyun_url_path'] = get_option('upload_url_path');
	update_option('wpupyun_options', $wpupyun_options);
	update_option('upload_url_path', '');
}


/**
 * 删除本地文件
 * @param $file_path : 文件路径
 * @return bool
 */
function wpupyun_delete_local_file($file_path) {
	try {
		# 文件不存在
		if (!@file_exists($file_path)) {
			return TRUE;
		}
		# 删除文件
		if (!@unlink($file_path)) {
			return FALSE;
		}
		return TRUE;
	} catch (Exception $ex) {
		return FALSE;
	}
}


/**
 * 文件上传功能基础函数，被其它需要进行文件上传的模块调用
 * @param $key  : 远端需要的Key值[包含路径]
 * @param $file_local_path : 文件在本地的路径。
 * @param bool $no_local_file : 如果为真，则不在本地保留附件
 *
// * @return bool  : 暂未想好如何与wp进行响应。
*/
function wpupyun_file_upload($key, $file_local_path, $no_local_file = False) {
	$wpupyun_options = get_option('wpupyun_options');
    $upyun = new UpYunApi($wpupyun_options);
    $upload_url_path = get_option('upload_url_path');

	### 上传文件流
	  # 由于增加了独立文件名钩子对对象存储中同名文件的判断，避免同名文件的存在，因此这里直接覆盖上传。
    $upyun->Upload( wpupyun_key_handle($key, $upload_url_path), fopen($file_local_path, 'rb') );
	
    // 如果上传成功，且不再本地保存，在此删除本地文件
    if ($no_local_file) {
        wpupyun_delete_local_file($file_local_path);
    }
}


/**
 * 删除远程附件（包括图片的原图）
 * @param $post_id
 */
function wpupyun_delete_remote_attachment($post_id) {
	// 获取要删除的对象Key的数组
	$deleteObjects = array();
	$meta = wp_get_attachment_metadata( $post_id );
    $upload_url_path = get_option('upload_url_path');

	if (isset($meta['file'])) {
		$attachment_key = $meta['file'];
		array_push($deleteObjects, ltrim(wpupyun_key_handle($attachment_key, $upload_url_path), '/'));
	} else {
		$file = get_attached_file( $post_id );
		$attached_key = str_replace( wp_get_upload_dir()['basedir'] . '/', '', $file );  # 不能以/开头
		$deleteObjects[] = ltrim(wpupyun_key_handle($attached_key, $upload_url_path), '/');
	}

	if (isset($meta['sizes']) && count($meta['sizes']) > 0) {
		foreach ($meta['sizes'] as $val) {
			$attachment_thumbs_key = dirname($meta['file']) . '/' . $val['file'];
			$deleteObjects[] = ltrim(wpupyun_key_handle($attachment_thumbs_key, $upload_url_path), '/');
		}
	}

    if ( !empty( $deleteObjects ) ) {
        // 执行删除远程对象
        $upyun = new UpYunApi(get_option('wpupyun_options'));
        //删除文件, 每个数组1000个元素
        $upyun->Delete($deleteObjects);
    }
}


/**
 * 上传图片及缩略图
 * @param $metadata: 附件元数据
 * @return array $metadata: 附件元数据
 * 官方的钩子文档上写了可以添加 $attachment_id 参数，但实际测试过程中部分wp接收到不存在的参数时会报错，上传失败，返回报错为“HTTP错误”
 */
function wpupyun_upload_and_thumbs( $metadata ) {
	$wpupyun_options = get_option('wpupyun_options');
	$wp_uploads = wp_upload_dir();  # 获取上传路径

	if (isset( $metadata['file'] )) {
		# 1.先上传主图
		// wp_upload_path['base_dir'] + metadata['file']
		$attachment_key = $metadata['file'];  // 远程key路径
		$attachment_local_path = $wp_uploads['basedir'] . '/'. $attachment_key;  # 在本地的存储路径
		wpupyun_file_upload($attachment_key, $attachment_local_path, $wpupyun_options['no_local_file']);  # 调用上传函数
	}

	# 如果存在缩略图则上传缩略图
	if (isset($metadata['sizes']) && count($metadata['sizes']) > 0) {

		// 文件名可能相同，上传操作时会判断是否存在，如果存在则不会执行上传。
		foreach ($metadata['sizes'] as $val) {
			$attachment_thumbs_key = dirname($metadata['file']) . '/' . $val['file'];  // 生成object在对象存储的 key
			$attachment_thumbs_local_path = $wp_uploads['basedir'] . '/' . $attachment_thumbs_key;  // 本地存储路径
			wpupyun_file_upload($attachment_thumbs_key, $attachment_thumbs_local_path, $wpupyun_options['no_local_file']);  //调用上传函数
		}
	}

	return $metadata;
}

/**
 * @param array  $upload {
 *     Array of upload data.
 *
 *     @type string $file Filename of the newly-uploaded file.
 *     @type string $url  URL of the uploaded file.
 *     @type string $type File type.
 * @return array  $upload
 */
function wpupyun_upload_attachments ($upload) {
	$mime_types       = get_allowed_mime_types();
	$image_mime_types = array(
		// Image formats.
		$mime_types['jpg|jpeg|jpe'],
		$mime_types['gif'],
		$mime_types['png'],
        // 默认图片编辑支持以上3种格式
		$mime_types['bmp'],
		$mime_types['tiff|tif'],
		$mime_types['ico'],
	);
	if ( ! in_array( $upload['type'], $image_mime_types ) ) {
		$key        = str_replace( wp_upload_dir()['basedir'] . '/', '', $upload['file'] );
		$local_path = $upload['file'];
		wpupyun_file_upload( $key, $local_path, get_option('wpupyun_options')['no_local_file'] );
	}

	return $upload;
}


/**
 * Filters the result when generating a unique file name.
 *
 * @since 4.5.0
 *
 * @param string        $filename                 Unique file name.

 * @return string New filename, if given wasn't unique
 *
 * 参数 $ext 在官方钩子文档中可以使用，部分 WP 版本因为多了这个参数就会报错。 返回“HTTP错误”
 */
function wpupyun_unique_filename( $filename ) {
	$ext = '.' . pathinfo( $filename, PATHINFO_EXTENSION );
	$number = '';
    $upyun = new UpYunApi(get_option('wpupyun_options'));
    $upload_url_path = get_option('upload_url_path');
	while ( $upyun->hasExist( wpupyun_key_handle(wp_get_upload_dir()['subdir'] . "/$filename", $upload_url_path)) ) {
		$new_number = (int) $number + 1;
		if ( '' == "$number$ext" ) {
			$filename = "$filename-" . $new_number;
		} else {
			$filename = str_replace( array( "-$number$ext", "$number$ext" ), '-' . $new_number . $ext, $filename );
		}
		$number = $new_number;
	}
	return $filename;
}

function wpupyun_key_handle($key, $upload_url_path){
    # 参数2 为了减少option的获取次数
    $url_parse = wp_parse_url($upload_url_path);
    # 约定url不要以/结尾，减少判断条件
    if (array_key_exists('path', $url_parse)) {
        $key = $url_parse['path'] . '/' . $key;
    }
    return $key;
}

// 自动重命名
function wpupyun_sanitize_file_name( $filename ){
    $wpupyun_options = get_option('wpupyun_options');
    if ($wpupyun_options['opt']['auto_rename']) {
        return date("YmdHis") . "" . mt_rand(100, 999) . "." . pathinfo($filename, PATHINFO_EXTENSION);
    } else {
        return $filename;
    }
}


// 在导航栏“设置”中添加条目
function wpupyun_add_setting_page() {
	if (!function_exists('wpupyun_setting_page')) {
		require_once 'setting_page.php';
	}
	add_options_page('WP又拍云设置', '又拍云存储设置', 'manage_options', __FILE__, 'wpupyun_setting_page');
}

// 在插件列表页添加设置按钮
function wpupyun_plugin_action_links($links, $file) {
	if ($file == plugin_basename(dirname(__FILE__) . '/index.php')) {
		$links[] = '<a href="admin.php?page=' . WPUpYun_BASEFOLDER . '/actions.php">设置</a>';
	}
	return $links;
}

function wpupyun_set_thumbsize($wpupyun_options, $set_thumb){
    if($set_thumb) {
        $wpupyun_options['opt']['thumbsize'] = array(
            'thumbnail_size_w' => get_option('thumbnail_size_w'),
            'thumbnail_size_h' => get_option('thumbnail_size_h'),
            'medium_size_w'    => get_option('medium_size_w'),
            'medium_size_h'    => get_option('medium_size_h'),
            'large_size_w'     => get_option('large_size_w'),
            'large_size_h'     => get_option('large_size_h'),
            'medium_large_size_w' => get_option('medium_large_size_w'),
            'medium_large_size_h' => get_option('medium_large_size_h'),
        );
        update_option('thumbnail_size_w', 0);
        update_option('thumbnail_size_h', 0);
        update_option('medium_size_w', 0);
        update_option('medium_size_h', 0);
        update_option('large_size_w', 0);
        update_option('large_size_h', 0);
        update_option('medium_large_size_w', 0);
        update_option('medium_large_size_h', 0);
        update_option('wpupyun_options', $wpupyun_options);
    } else {
        if(isset($wpupyun_options['opt']['thumbsize'])) {
            update_option('thumbnail_size_w', $wpupyun_options['opt']['thumbsize']['thumbnail_size_w']);
            update_option('thumbnail_size_h', $wpupyun_options['opt']['thumbsize']['thumbnail_size_h']);
            update_option('medium_size_w', $wpupyun_options['opt']['thumbsize']['medium_size_w']);
            update_option('medium_size_h', $wpupyun_options['opt']['thumbsize']['medium_size_h']);
            update_option('large_size_w', $wpupyun_options['opt']['thumbsize']['large_size_w']);
            update_option('large_size_h', $wpupyun_options['opt']['thumbsize']['large_size_h']);
            update_option('medium_large_size_w', $wpupyun_options['opt']['thumbsize']['medium_large_size_w']);
            update_option('medium_large_size_h', $wpupyun_options['opt']['thumbsize']['medium_large_size_h']);
            unset($wpupyun_options['opt']['thumbsize']);
            update_option('wpupyun_options', $wpupyun_options);
        }
    }
    return $wpupyun_options;
}

function wpupyun_legacy_data_replace() {
    global $wpdb;

    $originalContent = home_url('/wp-content/uploads');
    $newContent = get_option('upload_url_path');

    # 文章内容文字/字符替换
    $result = $wpdb->query(
        "UPDATE {$wpdb->prefix}posts SET `post_content` = REPLACE( `post_content`, '{$originalContent}', '{$newContent}');"
    );

    $wpupyun_options = get_option('wpupyun_options');
    $wpupyun_options['opt']['wpupyun_legacy_data_replace'] = 1;
    update_option('wpupyun_options', $wpupyun_options);
    return $wpupyun_options;
}

function wpupyun_image_processing($content){
    $wpupyun_options = get_option("wpupyun_options");
    if ( isset($wpupyun_options['opt']['img_process']) && $wpupyun_options['opt']['img_process']['switch'] === True ) {
        $media_url = get_option('upload_url_path');
        $pattern = '#<img[\s\S]*?src\s*=\s*[\"|\'](.*?)[\"|\'][\s\S]*?>#ims';  // img匹配正则
        $content = preg_replace_callback(
            $pattern,
            function($matches) use ($wpupyun_options, $media_url) {
                if (strpos($matches[1], $media_url) === false) {
                    return $matches[0];
                } else {
                    return str_replace(
                        $matches[1],
                        $matches[1] . '!' . $wpupyun_options['opt']['img_process']['style_value'],
                        $matches[0]);
                }
            },
            $content);
    }
    return $content;
}

function wpupyun_set_img_process_handle($wpupyun_options, $img_process){
    if( isset($img_process['img_process_switch']) ){
        $wpupyun_options['opt']['img_process']['switch'] = True;
        switch( sanitize_text_field(trim(stripslashes($img_process['img_process_style_choice']))) ){
            case "0":
                $wpupyun_options['opt']['img_process']['style_value'] = '/format/webp/lossless/true';
                break;
            case "1":
                $wpupyun_options['opt']['img_process']['style_value'] = sanitize_text_field(trim(stripslashes($img_process['img_process_style_customize'])));
                break;
        }
    } else {
        $wpupyun_options['opt']['img_process']['switch'] = False;
    }
    return $wpupyun_options;
}