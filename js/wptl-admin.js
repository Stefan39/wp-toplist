/**
 * Javascript for the WP-Toplist Backend
 * @author		WPler <plugins@wpler.com>
 * @version		1.0
 * @copyright	WPler
 */
(function($){
	trigger_banner={
		init:function(){
			var option=$('#wptlform #listform option:selected').val();
			if (option=='banner'){$('#wptlform tr.showbanner').fadeIn(250);}
			else{$('#wptlform tr.showbanner').fadeOut(250);}
			$('#wptlform #listform').change(function(){
				if ($(this).children('option:selected').val()=='banner'){
					$('#wptlform tr.showbanner').fadeIn(250);
				}else{
					$('#wptlform tr.showbanner').fadeOut(250);
				}
			});
		}
	};
	trigger_seconds={
		init:function(){
			$('#wptlform #ip-in').change(function(){
				$(this).next('.description span').text($(this).val());
			});
			$('#wptlform #ip-out').change(function(){
				$(this).next('.description span').text($(this).val());
			});
		}
	};
	$(document).ready(function(){
		trigger_banner.init();
		trigger_seconds.init();
	});
})(jQuery);
