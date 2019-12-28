<?php

namespace dFramework\dependencies\others\dumpr\Node;

use dFramework\dependencies\others\dumpr\Node;

class Boolean extends Node {
	public function chk_ref() {
		return false;
	}

	public function disp_val() {
		return $this->raw ? 'true' : 'false';
	}
}