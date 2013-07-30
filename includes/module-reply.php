<?php
class Wechat_reply {

	function __construct( $wechat, $settings ) {

		$this->wechat 	= $wechat;
		$this->settings = $settings;

		if( "text" == $this->wechat->getData["MsgType"] ) {

			$this->wechat->enqueque( array( $this, "callback" ), 10 );

		}

	}

	function callback() {

		$all_replies= $this->settings["reply"];
		$receive 	= array();
		$send 		= array();
		$index 		= 0;
		$received 	= $this->wechat->getData( "Content" );

		foreach ($all_replies as $one_reply) {

			$receive[$index]= $one_reply["receive"];
			$send[$index] 	= $one_reply["send"];
			$index += 1;

		}

		$current_index 		= array_search( $received, $receive );

		if( !empty( $current_index ) ) {

			$allData 			= $this->wechat->parsedData;
			$allData["Content"]	= $receive[$current_index];
			return $allData;

		}

		return false;

	}

}
?>