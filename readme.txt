=== WPUPYUN ===

Contributors: laobuluo
Donate link: https://www.laobuluo.com/donate/
Tags:WordPress对象存储,WordPress加速,WordPress 又拍云存储, 又拍云WordPress,又拍云对象存储
Requires at least: 4.5.0
Tested up to: 5.3
Stable tag: 1.2
Requires PHP: 5.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

<strong>WordPress 又拍云对象存储插件（简称:WPUPYUN），基于又拍云对象存储与WordPress实现静态资源到又拍云对象存储中。提高网站项目的访问速度，以及静态资源的安全存储功能。站长交流QQ群： <a href="https://jq.qq.com/?_wv=1027&k=5gBE7Pt" target="_blank"> <font color="red">594467847</font></a></strong>

<strong>主要功能：</strong>

* 1、下载和激活【WPUPYUN】插件后，配置又拍云存储信息。
* 2、可以选择只存储到又拍云对象存储空间、也可以本地网站也同时备份。
* 3、选择又拍云对象存储必须自定义域名，不支持免费域名绑定，且域名需要备案过。
* 4、WPUPYUN插件更多详细介绍和安装：<a href="https://www.laobuluo.com/2620.html" target="_blank" >https://www.laobuluo.com/2620.html</a>

<strong>支持网站平台：</strong>

* 1. 老蒋部落 <a href="https://www.itbulu.com" target="_blank" >https://www.itbulu.com</a>
* 2. 老部落 <a href="https://www.laobuluo.com" target="_blank" >https://www.laobuluo.com</a>
* 3. 推荐文章：<a href="https://www.laobuluo.com/2113.html" target="_blank">新人建站常用的虚拟主机/云服务器 常用主机商选择建议</a>

== Installation ==

* 1、把WPUPYUN文件夹上传到/wp-content/plugins/目录下<br />
* 2、在后台插件列表中激活WPUPYUN<br />
* 3、在左侧【WP又拍云设置】菜单中输入又拍云存储空间账户信息。<br />
* 4、设置可以参考：https://www.laobuluo.com/2620.html

== Frequently Asked Questions ==

* 1.当发现插件出错时，开启调试获取错误信息。
* 2.我们可以选择备份对象存储或者本地同时备份。
* 3.如果已有网站使用WPUPYUN，插件调试没有问题之后，需要将原有本地静态资源上传到又拍云存储中，然后修改数据库原有固定静态文件链接路径。、
* 4.如果不熟悉使用这类插件的用户，一定要先备份，确保错误设置导致网站故障。

== Screenshots ==

1. screenshot-1.png

== Changelog ==

= 1.0 =
* 1. 完成又拍云对象存储自动同步WordPress图片静态文件插件；
* 2. 解决图片重复上传和超时问题
* 3. 解决Apache默认环境出现超时HTTP上传错误
* 4. 解决同步本地和又拍云存储图片错误问题

= 1.1 =
* 1. 解决WordPress升级5.3之后图片处理方式问题
* 2. 调试兼容WP5.3和老版本WP

= 1.2 =
* 1. 解决删除媒体库不同步删除问题
* 2. 重新调优部分代码


== Upgrade Notice ==
* 