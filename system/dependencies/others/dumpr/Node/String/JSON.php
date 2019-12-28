<?php

namespace dFramework\dependencies\others\dumpr\Node\String;
use dFramework\dependencies\others\dumpr\Node\String0;
use dFramework\dependencies\others\dumpr\Rend;

class JSON extends String0 {
	public function get_nodes() {
		return (array)$this->inter;
	}

	public function disp_val() {
		if (Rend::$json_pretty)
			return json_encode($this->inter, JSON_PRETTY_PRINT);
		else
			return parent::disp_val();
	}
}