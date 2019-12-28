<?php

namespace dFramework\dependencies\others\dumpr\Node;
use dFramework\dependencies\others\dumpr\Node;

class Null0 extends Node {
	public function chk_ref() {
		return false;
	}

	public function disp_val() {
		return 'null';
	}
}