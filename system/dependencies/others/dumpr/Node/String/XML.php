<?php

namespace dFramework\dependencies\others\dumpr\Node\String;
use dFramework\dependencies\others\dumpr\Node\String0;
use dFramework\dependencies\others\dumpr\Rend;

class XML extends String0 {
	public function get_nodes() {
		return (array)$this->inter;
	}

	// dont show length, or find way to detect uniform subnodes and treat as XML [] vs XML {}
	public function get_len() {
		return null;
	}

	public function disp_val() {
		if (Rend::$xml_pretty) {
			$dom = dom_import_simplexml($this->inter)->ownerDocument;
			$dom->formatOutput = true;
			return trim($dom->saveXML());
		}
		else
			return parent::disp_val();
	}
}