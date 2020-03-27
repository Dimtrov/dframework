<?php

namespace dump_r\Node\String;
use dump_r\Node\String0;
use dump_r\Rend;

class SQL extends String0 {
    /**
     * @return string
     */
    public function disp_val() {
		if (Rend::$sql_pretty)
			return \SqlFormatter::format($this->raw, false);
		else
			return parent::disp_val();
	}
}