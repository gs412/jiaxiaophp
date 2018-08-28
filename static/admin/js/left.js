$(document).ready(function(){
	var $h4 = $('div.menu h4');
	$h4.attr('unselectable','on')
		.css({'-moz-user-select':'-moz-none',
			'-moz-user-select':'none',
			'-o-user-select':'none',
			'-khtml-user-select':'none',
			'-webkit-user-select':'none',
			'-ms-user-select':'none',
			'user-select':'none'
		}).bind('selectstart', function(){ return false; });
	$h4.click(function(){
		var $this = $(this);
		$this.parent().find('h4').css({'font-weight':'normal'});
		$this.css({'font-weight':'bold'});
		var $ul = $this.next("ul");
		if($ul.is(":hidden")){
			$ul.parent().find('ul').slideUp(100);
			$ul.slideDown(100);
		}else{
			$ul.slideUp(100);
		}
	});
});