(function($) {

	var $menu = $("#menu");

	$menu.on("change", function() {

		if( $("#access_token").val() !== "" &&
			$menu.val() !== "" ) {

			$.post($("#ajaxurl").val(), {
				menu 	: $menu.val(),
				token 	: $("#access_token").val(),
				action 	: "wechat_menu",
			}, function(response) {
				console.log(response);
				//$menu.parent("td").append(response);
			});

		}

	});

})(jQuery);