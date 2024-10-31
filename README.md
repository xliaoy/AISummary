# AISummary
Typecho的AI摘要插件 AISummary
# 说明
一个自己无聊时弄的小插件 有点鸡肋 写的不好的地方请各位嘴下留情
好了 进入正题

# 使用教程
首先下载插件到usr/plugins/目录进行解压（文件名必须是 AISummary 不然会报错）
然后在后台启用AISummary插件
接着把AI.php上传到主题的任意一个位置当然你要知道路径 等会要用（Joe再续前缘版可以上传到usr/themes/Joe/module/目录）
```php
<div class="joe_header__slideout-menu panel-b">
					<?php if ($this->fields->content) :?>
	<div class="ai-summary-block">
		<p>AI 摘要：<span class="typing-effect"><?php echo $this->fields->content;?></span></p>
	</div>
	<?php endif;?>
	<style>
   .ai-summary-block {
		background-color: #f5f5f5;
		padding: 15px;
		border: 1px solid #ddd;
		border-radius: 5px;
		margin-bottom: 20px;
	}
   .typing-effect {
		white-space: nowrap;
		overflow: hidden;
		animation: typing 2s steps(40) forwards;
	}
	@keyframes typing {
		from {
			width: 0;
		}
		to {
			width: 100%;
		}
	}
.joe_header__slideout-menu.panel-b {
	overflow-y: scroll; 
}
h2 {
	text-align: center;
}
h3 {
	color: black;
	font-size: smaller;
	text-align: center;
}
</style>
<script>
	document.addEventListener('DOMContentLoaded', function () {
		const typingElements = document.querySelectorAll('.typing-effect');
		typingElements.forEach(element => {
			element.classList.remove('typing-effect');
			const text = element.textContent;
			element.textContent = '';
			let i = 0;
			const typingInterval = setInterval(() => {
				if (i < text.length) {
					element.textContent += text[i];
					i++;
				} else {
					clearInterval(typingInterval);
				}
			}, 50);
		});
	});
</script>
</div>
<br>
```
然后在主题的post.php里面合适的位置添加以下代码
```php
<?php $this->need('module/AI.php'); ?>
```
Joe再续前缘版本可以添加在post.php的第120行下面第121行如图
![https://pan.skaco.cn/view.php/9633bbe0cc60215f47ddbbbd0bbc56c6.jpg](https://pan.skaco.cn/view.php/9633bbe0cc60215f47ddbbbd0bbc56c6.jpg)
