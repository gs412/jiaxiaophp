$(document).ready(function () {
	var $form = $('form#accountform');
	var $phone = $form.find('input[name="phone"]');
	var $qq = $form.find('input[name="qq"]');
	var $email = $form.find('input[name="email"]');

	$form.submit(function () {
		if (!/^1[\d]{10}$/.test($phone.val())) {
			alert('手机号格式不对（必须是11位数字，并且以1开头）');
			$phone.focus();
			return false;
		} else if (!/^\d+$/.test($qq.val())) {
			alert('qq号只能是数字');
			$qq.focus();
			return false;
		} else if (!/^([0-9A-Za-z\-_\.]+)@([0-9a-z]+\.[a-z]{2,3}(\.[a-z]{2})?)$/g.test($email.val())) {
			alert('email格式不对');
			$email.focus();
			return false;
		}
	});
});