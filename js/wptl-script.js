/**
 * Javscripts for tracking hits for the plugin WPToplist
 * @author:		WPler <plugins@wpler.com>
 * @version:	1.0
 * @copyright	WPler
 */
(function($){
	trigger_hits={
		init:function(){
			$('.wptl_trackit').click(function(){
				var res=$.ajax({url:wptl.ajaxurl,method:'POST',data:{action:'wptl_trackit',id:$(this).data('id'),nonce:wptl.nonce}});
				res.done(function(x){console.log(x);});
			});
		}
	};
	$(document).ready(function(){trigger_hits.init();});
})(jQuery);
