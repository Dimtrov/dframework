<?php

namespace dFramework\dependencies\others\dumpr\Node;
use dFramework\dependencies\others\dumpr\Node;

class Resource extends Node {
/*
	public function get_id() {
		return intval($this->raw);
	}
*/
	public function disp_val() {
		return $this->ref ? '<*>' : '< >';
	}
}
