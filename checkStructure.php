<?php

function xml_is_equal(SimpleXMLElement $xml1, SimpleXMLElement $xml2) {

	// check all attributes
	$search1 = array();
	$search2 = array();
	foreach ($xml1->attributes() as $a => $b) {
		$search1[$a] = "$b";		// force string conversion
	}
	foreach ($xml2->attributes() as $a => $b) {
		$search2[$a] = "$b";
	}
	if ($search1 != $search2) return false;//"mismatched attributes";

	// check all namespaces
	$ns1 = array();
	$ns2 = array();
	foreach ($xml1->getNamespaces() as $a => $b) {
		$ns1[$a] = "$b";
	}
	foreach ($xml2->getNamespaces() as $a => $b) {
		$ns2[$a] = "$b";
	}
	if ($ns1 != $ns2) return false;//"mismatched namespaces";

	// get all namespace attributes
	foreach ($ns1 as $ns) {			// don't need to cycle over ns2, since its identical to ns1
		$search1 = array();
		$search2 = array();
		foreach ($xml1->attributes($ns) as $a => $b) {
			$search1[$a] = "$b";
		}
		foreach ($xml2->attributes($ns) as $a => $b) {
			$search2[$a] = "$b";
		}
		if ($search1 != $search2) return false;//"mismatched ns:$ns attributes";
	}

	// get all children
	$search1 = array();
	$search2 = array();
	foreach ($xml1->children() as $b) {
		if (!isset($search1[$b->getName()]))
			$search1[$b->getName()] = array();
		$search1[$b->getName()][] = $b;
	}
	foreach ($xml2->children() as $b) {
		if (!isset($search2[$b->getName()]))
			$search2[$b->getName()] = array();
		$search2[$b->getName()][] = $b;
	}
	// cycle over children
	if (count($search1) != count($search2)) return false;//"mismatched children count";		// xml2 has less or more children names (we don't have to search through xml2's children too)
	foreach ($search1 as $child_name => $children) {
		if (!isset($search2[$child_name])) return false;//"xml2 does not have child $child_name";		// xml2 has none of this child
		if (count($search1[$child_name]) != count($search2[$child_name])) return false;//"mismatched $child_name children count";		// xml2 has less or more children
		foreach ($children as $child) {
			// do any of search2 children match?
			$found_match = false;
			$reasons = array();
			foreach ($search2[$child_name] as $id => $second_child) {
				if (($r = xml_is_equal($child, $second_child)) === true) {
					// found a match: delete second
					$found_match = true;
					unset($search2[$child_name][$id]);
				} else {
					$reasons[] = $r;
				}
			}
			if (!$found_match) return false;//"xml2 does not have specific $child_name child: " . implode("; ", $reasons);
		}
	}

	// finally, cycle over namespaced children
	foreach ($ns1 as $ns) {			// don't need to cycle over ns2, since its identical to ns1
		// get all children
		$search1 = array();
		$search2 = array();
		foreach ($xml1->children() as $b) {
			if (!isset($search1[$b->getName()]))
				$search1[$b->getName()] = array();
			$search1[$b->getName()][] = $b;
		}
		foreach ($xml2->children() as $b) {
			if (!isset($search2[$b->getName()]))
				$search2[$b->getName()] = array();
			$search2[$b->getName()][] = $b;
		}
		// cycle over children
		if (count($search1) != count($search2)) return false;//"mismatched ns:$ns children count";		// xml2 has less or more children names (we don't have to search through xml2's children too)
		foreach ($search1 as $child_name => $children) {
			if (!isset($search2[$child_name])) return false;//"xml2 does not have ns:$ns child $child_name";		// xml2 has none of this child
			if (count($search1[$child_name]) != count($search2[$child_name])) return false;//"mismatched ns:$ns $child_name children count";		// xml2 has less or more children
			foreach ($children as $child) {
				// do any of search2 children match?
				$found_match = false;
				foreach ($search2[$child_name] as $id => $second_child) {
					if (xml_is_equal($child, $second_child) === true) {
						// found a match: delete second
						$found_match = true;
						unset($search2[$child_name][$id]);
					}
				}
				if (!$found_match) return false;//"xml2 does not have specific ns:$ns $child_name child";
			}
		}
	}

	// if we've got through all of THIS, then we can say that xml1 has the same attributes and children as xml2.
	return true;
}

?>
