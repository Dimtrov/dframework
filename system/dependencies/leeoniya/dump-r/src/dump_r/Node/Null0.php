<?php

namespace dump_r\Node;
use dump_r\Node;

class Null0 extends Node {
	public function chk_ref() {
		return false;
	}

	public function disp_val() {
		return 'null';
	}
}