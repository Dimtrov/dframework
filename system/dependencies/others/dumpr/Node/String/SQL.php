<?php

namespace dFramework\dependencies\others\dumpr\Node\String;
use dFramework\dependencies\others\dumpr\Node\String0;
use dFramework\dependencies\others\dumpr\Rend;

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