(function($) {

	var $menu = $("#menu");

	$menu.on("change", function() {

		if( $("#access_token").val() !== "" &&
			$menu.val() !== "" ) {

			$.post($("#ajaxurl").val(), {
				action 	: "wechat_menu",
				menu 	: $menu.val(),
				token 	: $("#access_token").val(),
			}, function(response) {
				$menu.parent("td").append(response);
			});

		}

	});

})(jQuery);