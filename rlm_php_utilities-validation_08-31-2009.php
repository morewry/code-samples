<?php

	/*
		-----------------------------------------------------------------------------------------
		Array to XML

			Arguments
				Required $array

		Returns a string of XML from input array (bases nesting on keys)
		-----------------------------------------------------------------------------------------
	*/
	function array_to_xml($array){
		// verify array
		if(is_array($array)){
			// count elements is gt 0
			if(count($array) > 0){
				// start list
				if(!isset($list) || empty($list)){$list = '';}
				// loop through array
				foreach($array as $key => $value){
					if(!empty($value)){
						// begin list item
						if(!is_numeric($key)){$list .=  '<' . trim($key) . '>';}
						// if list item is an array
						if(is_array($value)){
							// count elements is gt 0
							if(count($value) > 0){
								$list .= array_to_xml($value);
							} // if(count($value) > 0)
						}else{
							$list .= trim($value);
						} // if(is_array($value))
						if(!is_numeric($key)){$list .= '</' . trim($key) . '>';}
					} // if(!empty($value))
				} // foreach($array as $key => $value)
			} // if(count($array) > 0)
		} // if(is_array($array))
		return $list;
	} // function array_to_html_list($array)

	/*
		-----------------------------------------------------------------------------------------
		Format Phone Number

			Arguments
				Required $number

		Returns formatted phone number from 10-digit number
		-----------------------------------------------------------------------------------------
	*/
	function format_phonenumber($number){
		
		$pattern = "([\d]{3})([\d]{3})([\d]{4})";
		$replacement = "($1) $2-$3";
		$result = preg_replace('@' . $pattern . '@siU', $replacement, $number);

		return $result;
	}

	/*
		-----------------------------------------------------------------------------------------
		Validate Results

			Arguments
				Required $array
				Required $set
				Optional $errors

		Perform validation on data in an array
		Ensure data is the correct length
		Ensure data matches a required format
		Remove invalid data (numeric, string, array)
		Add blank items (present)
		Ensure all required data exists

		Validation settings in settings_validate.php {
			[defined in order...]
			1. Numeric - $application["validate"][$data_set]["numeric"][$number] = $data_item
				Verify this item is a number.  Remove non-numeric chars.  Check again, remove.
			2. String - $application["validate"][$data_set]["string"][$number] = $data_item
				Verify this item is a string.  If it is not, it is removed.
			3. Array - $application["validate"][$data_set]["array"][$number] = $data_item
				Verify this item is an array.  If it is not, it is removed.
			4. Present - $application["validate"][$data_set]["present"][$number] = $data_item
				Verify this item is present, even if blank.  Create it blank if it is not present.
			5. Length - $application["validate"][$data_set]["length"][$data_item] = $number
				Verify this item's length, truncate it if it is too long.
			6. Mask - $application["validate"][$data_set]["mask"][$data_item] = $regular_expression_pattern
				Verify the format of this item.  Fail validation if the pattern doesn't match.
			7. Required - $application["validate"][$data_set]["required"][$number] = $data_item
				Fail validation if this item isn't in the data set
		}
		-----------------------------------------------------------------------------------------
	*/
	function validate_results(&$array, $set, $errors = true){

		global $application;

		// count required items
		if(array_key_exists("required", $application["validate"][$set])){
			$req_count = count($application["validate"][$set]["required"]);
			$req_exist = 0;
		}else{
			$req_count = 0;
			$req_exist = 0;
		} // if(array_key_exists("required", $application["validate"][$set]))

		// for each validation type in this $set
		foreach($application["validate"][$set] as $key => $val){
			foreach($application["validate"][$set][$key] as $sakey => $item){

				/* Length & Mask 
					(are in the validation array in a slightly different way, need $sakey) */
				if(array_key_exists($sakey, $array) && isset($array[$sakey]) && (strlen($array[$sakey]) > 0)){

					switch($key){
						case "mask":
							// verify item format, note any mismatch
							if(!preg_match('@' . $item . '@siu', $array[$sakey])){
								if($errors){$_SESSION["errors"][] = '<span class="error">ERROR</span>: input for <a href=#'. $sakey . '>' . $application["translateEnglish"][$sakey] . "</a> (" . $array[$sakey] . ") appears to be invalid in " . $set . " form -- please check for accuracy.";}
								$mask[$sakey] = false;
							} // if(strlen($array[$sakey]) > $item)
						break;
						case "length":
							// verify item length, truncate if too long
							if(strlen($array[$sakey]) > $item){
								if($errors){$_SESSION["errors"][] = '<span class="warn">Warning</span>: input for <a href=#'. $sakey . '>' . $application["translateEnglish"][$sakey] . "</a> (" . $array[$sakey] . ") was too long in " . $set . " form -- it has been truncated.";}
								$array[$sakey] = substr($array[$sakey], 0, $item);
							} // if(strlen($array[$sakey]) > $item)
						break;
					} // switch($key)

				} // if(array_key_exists($sakey, $array) && isset($array[$sakey]) && (strlen($array[$sakey]) > 0))				

				/* Numeric, String, Array, Required, Present */
				if(array_key_exists($item, $array) && isset($array[$item]) && (strlen($array[$item]) > 0)){
					/* Only if the item exists */
					switch($key){
						case "numeric":
							// verify each numeric item, remove if not numeric
							$array[$item] = preg_replace("@([\D])@siu", "", $array[$item]);
							if(!is_numeric($array[$item])){
								if($errors){$_SESSION["errors"][] = '<span class="warn">Warning</span>: input for <a href=#'. $item . '>' . $application["translateEnglish"][$item] . "</a> (" . $array[$item] . ") was not numeric in " . $set . " form -- it has been removed.";}
								unset($array[$item]);
							} // if(!is_numeric($array[$item]))
						break;
						case "string":
							// verify each string item, remove if not string
							if(!is_string($array[$item])){
								if($errors){$_SESSION["errors"][] = '<span class="warn">Warning</span>: input for <a href=#'. $item . '>' . $application["translateEnglish"][$item] . "</a> (" . $array[$item] . ") was not a string in " . $set . " form -- it has been removed.";}
								unset($array[$item]);
							} // if(!is_string($array[$item]))
						break;
						case "array":
							// verify each array item, remove if not array
							if(!is_array($array[$item])){
								if($errors){$_SESSION["errors"][] = '<span class="warn">Warning</span>: input for <a href=#'. $item . '>' . $application["translateEnglish"][$item] . "</a> (" . $array[$item] . ") was not an array in " . $set . " form -- it has been removed.";}
								unset($array[$item]);
							} // if(!is_array($array[$item]))
						break;
						case "required":
							// verify that each required item exists and contains a value
							$req_exist++;
						break;
					} // switch($key)

				}else{
					/* If the item doesn't exist */
					switch($key){
						case "present":
							// create blank item
							$array[$item] = "";
						break;
						case "required":
							// failure of required item
							if($errors){$_SESSION["errors"][] = '<span class="error">ERROR</span>: required item <a href=#'. $item . '>' . $application["translateEnglish"][$item] . "</a> was missing in " . $set . " form -- please enter <strong>" . $application["translateEnglish"][$item] . "</strong>.";}
						break;
					} // switch($key)

				} // if(array_key_exists($item, $array) && isset($array[$item]) && (strlen($array[$item]) > 0))

			} // foreach($application["validate"][$set][$key] as $item)
		} // foreach($application["validate"][$set] as $key => $val)

		/* Result */
		// if all required items exist
		if($req_count == $req_exist){
			// if any mask patterns failed
			if(isset($mask)){
				$result = false;
			}else{
				$result = true;
			} // if(check_array($mask))
		}else{
			$result = false;
		} // if($req_count == $req_exist)

		// if the result hasn't been set yet (there were no required items that could fail)
		if(!isset($result)){$result = true;}

		return $result;
	} // function validate_results($array, $set)

?>