$(document).ready(function () {
	var $form = $('#regform');

	var $username = $form.find("input[name='username']");
	var $password = $form.find("input[name='password']");
	var $password_confirm = $form.find("input[name='password_confirm']");
	var $realname = $form.find("input[name='realname']");
	var $phone = $form.find("input[name='phone']");
	var $email = $form.find("input[name='email']");
	var $qq = $form.find("input[name='qq']");

	$form.submit(function () {
		if ($username.val().length < 6 || $username.val().length > 16) {
			alert('用户名长度为6~16位字符');
			$username.focus();
			return false;
		} else if (/^[a-zA-Z][a-zA-Z\-_0-9]+$/.test($username.val()) == false) {
			alert('用户名只能以字母、数字、下划线、中划线组成，且只能以字母开头');
			$username.focus();
			return false;
		} else if ($password.val().length < 6 || $password.val().length > 16) {
			alert('密码长度为6~16位字符');
			$password.focus();
			return false;
		} else if ($password.val() != $password_confirm.val()) {
			alert('两次输入密码不一致');
			$password.focus();
			return false;
		}
	});
});