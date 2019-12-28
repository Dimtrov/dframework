<?php

namespace dFramework\dependencies\others\dumpr\Node\String\JSON;
use dFramework\dependencies\others\dumpr\Node\String\JSON;

class Array0 extends JSON {
	public function get_len() {
		return count($this->nodes);
	}
}