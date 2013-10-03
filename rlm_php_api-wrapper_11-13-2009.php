<?php

	/*
		-----------------------------------------------------------------------------------------
		Build Request

			Required
				$command - a valid command name from the API documentation
				$data - associative array or domain name
				$result_view - to customize certain commands (domainGet)

		Creates an associative array (multidimensional) which reflects the XML structure
		(element names, nesting, etc.) required for a particular API command

			domainAdd
			domainAutoRenew
			domainCancel
			domainCheck
			domainGet
			domainLock
			domainModify
			ddomainPrivacyAdd
			domainPassword
			domainRenew
			domainTransferCancel
			domainTransferIn
			userAdd
			userModify
			zoneAdd
			zoneDelete
			zoneGet
		-----------------------------------------------------------------------------------------
	*/
	function build_request($command, $data, $result_view = false){

		global $application;

		/* Check $data, format, set defaults */
		if(check_array($data)){
			// Domain Name
			if(array_key_exists("domainName", $data)){
				$domainName = $data["domainName"];
			}
			// Determine country and state v. province
			if(array_key_exists("state", $data)){
				$data["country"] = select_country($data["state"]);
				if($data["country"] == "US"){
					$data["statekey"] = "state";
				}else{
					$data["statekey"] = "province";
				} // if($data["country"] == "US")
			} // if(array_key_exists("state", $data))
			// Get acct_name
			if(array_key_exists("customerID", $data) && array_key_exists("domainName", $data)){
				$acct_name = select_name($data["customerID"], $data["domainName"]);
				if(empty($acct_name)){
					$acct_name = $data["domainName"];
				} // if(empty($acct_name))
			} // if(array_key_exists("customerID", $data) && array_key_exists("domainName", $data))
		}
		elseif(is_numeric($data)){
			// do nothing
		}
		else{
			if(is_domain($data)){
				$domainName = $data;
			}else{
				return false;
			}
		} // if(check_array($data))
		// Find domain tld
		if(isset($domainName)){
			$temp = explode(".", $domainName);
			$dom = $temp[0];
			$tld = $temp[1];
		}else{
			$tld = null;
		}

		/* ----------- Specific Command Data ----------- */
		switch($command){

			case "domainAdd":
				/* User */
				$request["request"]["userId"] = $data["customerID"];
				/* Domain */
				$request["request"]["domainName"] = $data["domainName"];
				$request["request"]["term"] = $data["term"];
				/* Contacts Added Below */
				$request["request"]["contacts"] = build_request_contacts($data);
				/* Name Servers Added Below */
				$request["request"]["nameservers"] = build_request_ns($data);
				/* Zones */
				//$request["request"]["zones"] = build_request_zones($data);
				/* Extra Attributes */
				$request["request"]["extraAttributes"] = build_request_ex($tld);
			break;

			case "domainAutoRenew":
				/* Domain (Product) */
				$request["request"]["productId"] = get_productId($domainName);
				/* Renew Value */
				$request["request"]["autoRenew"] = $data["autoRenew"];
			break;

			case "domainCancel":
				/* Domain (Product) */
				$request["request"]["productId"] = get_productId($domainName);
			break;

			case "domainCheck":
				$domainName = explode(".", $data);
				/* Domain */
				$request["request"]["sld"] = $domainName[0];
				$request["request"]["extensions"]["extension"] = $domainName[1];
			break;

			case "domainGet":
				/* Domain, User, All */
				if(is_domain($data)){
				/* Domain name */
					$request["request"]["domains"]["domainName"] = $data;
				} // if(is_domain($data))
				elseif(is_account($data)){
				/* User domains */
					$request["request"]["userId"] = $data["userId"];
					//$request["request"]["page"] = $data["page"];
				} // elseif(is_account($data))
				elseif(is_numeric($data)){
				/* All domains */
					$request["request"]["page"] = $data;
				} // elseif(is_account($data))
			break;

			case "domainLock":
				/* Domain (Product) */
				$request["request"]["productId"] = get_productId($domainName);
				/* Lock Value */
				$request["request"]["registrarLock"] = $data["registrarLock"];
			break;

			case "domainModify":
				/* Domain (Product) */
				$request["request"]["productId"] = get_productId($domainName);
				/* Contacts Added Below */
				$request["request"]["contacts"] = build_request_contacts($data);
				/* Name Servers Added Below */
				$request["request"]["nameservers"] = build_request_ns($data);
				/* Extra Attributes */
				$request["request"]["extraAttributes"] = build_request_ex($tld);
			break;

			case "domainPassword":
				/* Domain (Product) */
				$request["request"]["productId"] = get_productId($domainName);
			break;

			case "domainPrivacyAdd":
			case "domainRenew":
				/* Domain (Product) */
				$request["request"]["productId"] = get_productId($domainName);
				/* Term */
				$request["request"]["term"] = $data["term"];
			break;

			case "domainTransferCancel":
				/* Domain (Product) */
				$request["request"]["productId"] = get_productId($domainName);
			break;

			case "domainTransferIn":
				/* User */
				$request["request"]["userId"] = $data["customerID"];
				/* Domain */
				$request["request"]["domainName"] = $data["domainName"];
				/* Authcode */
				$request["request"]["authCode"] = $data["authCode"];
				/* Contacts Added Below */
				$request["request"]["contacts"] = build_request_contacts($data);
				/* Extra Attributes */
				$request["request"]["extraAttributes"] = build_request_ex($tld);
			break;

			case "userAdd":
			case "userModify":
				/* User */
				$request["request"]["userId"] = $data["customerID"];
				$request["request"]["userAccountName"] = $acct_name;
				/* Contacts Added Below */
				$request["request"]["contacts"] = build_request_contacts($data);
			break;

			case "zoneAdd":
			case "zoneDelete":
				/* Domain (Product) */
				$request["request"]["productId"] = get_productId($domainName);
				/* Zones */
				//$request["request"]["zones"] = build_request_zones($data);
				//$request["zones"][0]["zone"]["zoneType"] = $data["zoneType"];
				//$request["zones"][0]["zone"]["zoneKey"] = $data["zoneKey"];
				//$request["zones"][0]["zone"]["zoneValue"] = $data["zoneValue"];
				//$request["zones"][0]["zone"]["zonePriority"] = $data["zonePriority"];
			break;

			case "zoneGet":
				/* Domain (Product) */
				$request["request"]["productId"] = get_productId($domainName);
			break;

		} // switch($command)

		if(!isset($request)){$request = false;}
		return $request;

	} // function build_request

	function build_request_contacts($data){
		$contacts = '';

		/* ----------- Domain Registrant / Contacts ----------- */
		/* Contact, Registration */
		$contacts[0]["contact"]["firstName"] = $data["firstName"];
		$contacts[0]["contact"]["lastName"] = $data["lastName"];
		if(isset($data["companyName"]) && strlen($data["companyName"]) > 0){
			$contacts[0]["contact"]["companyName"] = $data["companyName"];
		}
		$contacts[0]["contact"]["emailAddress"] = $data["emailAddress"];
		$contacts[0]["contact"]["telephoneNumber"] = '+1.' . $data["telephoneNumber"];
		if(isset($data["faxNumber"]) && strlen($data["faxNumber"]) > 0){
			$request["request"]["contacts"][0]["contact"]["faxNumber"] = '+1.' . $data["faxNumber"];
		}
		$contacts[0]["contact"]["addressLine1"] = $data["addressLine1"];
		if(isset($data["addressLine2"]) && strlen($data["addressLine2"]) > 0){
			$contacts[0]["contact"]["addressLine2"] = $data["addressLine2"];
		}
		$contacts[0]["contact"]["city"] = $data["city"];
		$contacts[0]["contact"][$data["statekey"]] = $data["state"];
		$contacts[0]["contact"]["postalCode"] = $data["postalCode"];
		$contacts[0]["contact"]["countryCode"] = $data["country"];
		$contacts[0]["contact"]["contactType"] = "Registration";

		/* Contact, Administration */
		$contacts[1]["contact"]["firstName"] = $data["firstName"];
		$contacts[1]["contact"]["lastName"] = $data["lastName"];
		if(isset($data["companyName"]) && strlen($data["companyName"]) > 0){
			$contacts[1]["contact"]["companyName"] = $data["companyName"];
		}
		$contacts[1]["contact"]["emailAddress"] = $data["emailAddress"];
		$contacts[1]["contact"]["telephoneNumber"] = '+1.' . $data["telephoneNumber"];
		if(isset($data["faxNumber"]) && strlen($data["faxNumber"]) > 0){
			$request["request"]["contacts"][1]["contact"]["faxNumber"] = '+1.' . $data["faxNumber"];
		}
		$contacts[1]["contact"]["addressLine1"] = $data["addressLine1"];
		if(isset($data["addressLine2"]) && strlen($data["addressLine2"]) > 0){
			$contacts[1]["contact"]["addressLine2"] = $data["addressLine2"];
		}
		$contacts[1]["contact"]["city"] = $data["city"];
		$contacts[1]["contact"][$data["statekey"]] = $data["state"];
		$contacts[1]["contact"]["postalCode"] = $data["postalCode"];
		$contacts[1]["contact"]["countryCode"] = $data["country"];
		$contacts[1]["contact"]["contactType"] = "Administration";

		return $contacts;
	} // function build_request_contacts

	function build_request_ns($data){
		global $application;
		$nameservers = '';

		/* ----------- Domain Name Servers ----------- */
		if($application["mode"]["testing"]){
			$nameservers[0]["nameserver"]["nsType"] = "Primary";
			$nameservers[0]["nameserver"]["nsName"] = "dns1.register.ag";
			$nameservers[1]["nameserver"]["nsType"] = "Secondary";
			$nameservers[1]["nameserver"]["nsName"] = "dns2.register.ag";
		}else{
			/* Primary NS */
			$nameservers[0]["nameserver"]["nsType"] = "Primary";
			$nameservers[0]["nameserver"]["nsName"] = "ns.homes.com";
			////$nameservers[0]["nameserver"]["nsIpAddress"] = "199.44.153.51";
			/* Secondary NS */
			$nameservers[1]["nameserver"]["nsType"] = "Secondary";
			$nameservers[1]["nameserver"]["nsName"] = "ns3.homes.com";
			////$nameservers[1]["nameserver"]["nsIpAddress"] = "199.44.154.51";
			/* Secondary NS */
			$nameservers[2]["nameserver"]["nsType"] = "Secondary";
			$nameservers[2]["nameserver"]["nsName"] = "ns1.homes.com";
			////$nameservers[2]["nameserver"]["nsIpAddress"] = "208.254.9.236";
		}

		return $nameservers;
	} // function build_request_ns()

	function build_request_ex($tld){
		$extraAttributes = '';

		/* ----------- Special Extensions ----------- */
		switch($tld){
			case "us":
				$extraAttributes = array();
				$extraAttributes[0]["extraAttribute"]["extraAttributeKey"] = "USAPPPURPOSE";
				$extraAttributes[0]["extraAttribute"]["extraAttributeValue"] = "P1";
				$extraAttributes[1]["extraAttribute"]["extraAttributeKey"] = "USNEXUSCATEGORY";
				$extraAttributes[1]["extraAttribute"]["extraAttributeValue"] = "C32";
			break;
		} // switch($tld)

		return $extraAttributes;
	} // function build_request_ex()

	/*
		-----------------------------------------------------------------------------------------
		Interpret Response

			Required
				$command - a valid command name from the API documentation
				$result - a simple_xml object of the XML response from the API
				$result_view - customize returned results for certain commands (ie: domainGet)
		-----------------------------------------------------------------------------------------
	*/
	function interpret_response($command, $result, $result_view = false){

		global $application;

		/* Status: success or failure? */
		if(is_object($result)){
			$status = (array)$result->xpath("//statusCode[1]");
			if(is_array($status) && (count($status) > 0)){
				$status = (string)$status[0];
			}else{
				$status = false;
			}
		}else{
			$status = false;
		} // if(is_object($result))

		if($status == "1000"){
			/* Success */
			$_SESSION["flag"] = false;

			/* Translate command */
			switch($command){

				case "domainAdd":
					//$response["command"] = (string)$result->command;
					//$response["reference"] = (string)$result->clientRef;
					//$response["status"] = (string)$result->status->statusCode . ' ' . (string)$result->status->statusDescription;
					$response["productId"] = (string)$result->response->productId;
				break;

				case "domainAutoRenew":
					//$response["command"] = (string)$result->command;
					//$response["reference"] = (string)$result->clientRef;
					//$response["status"] = (string)$result->status->statusCode . ' ' . (string)$result->status->statusDescription;
					$response["productId"] = (string)$result->response->productId;
				break;

				case "domainCancel":
					//$response["command"] = (string)$result->command;
					//$response["reference"] = (string)$result->clientRef;
					//$response["status"] = (string)$result->status->statusCode . ' ' . (string)$result->status->statusDescription;
					$response["productId"] = (string)$result->response->productId;
				break;

				case "domainCheck":
					//$response["command"] = (string)$result->command;
					//$response["reference"] = (string)$result->clientRef;
					//$response["status"] = (string)$result->status->statusCode . ' ' . (string)$result->status->statusDescription;
					$response["domainName"] = (string)$result->response->domain->domainName;
					$response["domainAvailable"] = (string)$result->response->domain->domainAvailable;
				break;

				case "domainGet":
					/* "is ours?" in our register.com account */
					$domainGet = $result->xpath("//recordCount[1]");
					$domainGet = (string)$domainGet[0];
					/* interpret based on # of results */
					if(($domainGet == 1) && ($result_view == false)){
						//$response["command"] = (string)$result->command;
						//$response["reference"] = (string)$result->clientRef;
						//$response["status"] = (string)$result->status->statusCode . ' ' . (string)$result->status->statusDescription;
						$response["recordCount"] = (string)$result->response->domainGet->recordCount;
						$response["totalPages"] = (string)$result->response->domainGet->totalPages;
						$response["currentPage"] = (string)$result->response->domainGet->currentPage;
						$response["userId"] = (string)$result->response->domainGet->domain->userId;
						$response["productId"] = (string)$result->response->domainGet->domain->domainInfo->productId;
						$response["domainName"] = (string)$result->response->domainGet->domain->domainInfo->domainName;
						$response["startDate"] = (string)$result->response->domainGet->domain->domainInfo->startDate;
						$response["expiryDate"] = (string)$result->response->domainGet->domain->domainInfo->expiryDate;
						$response["domainStatus"] = (string)$result->response->domainGet->domain->domainInfo->domainStatus;
						$response["autoRenew"] = (string)$result->response->domainGet->domain->domainInfo->autoRenew;
						/* Contacts */
						// XPath to get contacts
						// Cast simpleXML object to array of strings
						$response["contact"]["temp"] = $result->xpath("//contact");
						$response["contact"]["temp"] = (array)$response["contact"]["temp"];
						// Loop through contact info
						// Cast simple XML object to array of strings
						foreach($response["contact"]["temp"] as $key => $value){
							$response["contact"]["temp"][$key] = (array)$response["contact"]["temp"][$key];
							foreach($value as $sakey => $savalue){
								$contactType = (string)$response["contact"]["temp"][$key]["contactType"];
								// Isolate registration contact
								if($contactType == "Registration"){
									$response["contact"][$sakey] = (string)$response["contact"]["temp"][$key][$sakey];
								} // if($contactType == "Registration")
							} // foreach($value as $sakey => $savalue)
						} // foreach($response["contact"] as $key => $value)
						// Remove all but registration contact from return array
						unset($response["contact"]["temp"]);
					} // if($domainGet == 1)
					elseif(($domainGet > 1) || (($domainGet == 1) && ($result_view !== false))){
						$response["recordCount"] = (string)$result->response->domainGet->recordCount;
						$response["totalPages"] = (string)$result->response->domainGet->totalPages;
						$response["currentPage"] = (string)$result->response->domainGet->currentPage;
						switch($result_view){
							case "query":
								$response["recordSet"]["temp"]["userId"] = (array)$result->xpath("//userId");
								$response["recordSet"]["temp"]["productId"] = (array)$result->xpath("//productId");
								$response["recordSet"]["temp"]["domain"] = (array)$result->xpath("//domainName");
								$response["recordSet"]["temp"]["startDate"] = (array)$result->xpath("//startDate");
								$response["recordSet"]["temp"]["expiryDate"] = (array)$result->xpath("//expiryDate");
								$response["recordSet"]["temp"]["domainStatus"] = (array)$result->xpath("//domainStatus");
								$response["recordSet"]["temp"]["autoRenew"] = (array)$result->xpath("//autoRenew");
								$response["recordSet"]["temp"]["contact"] = (array)$result->xpath("//contact");
								// Cast simple XML object to array of strings
								foreach($response["recordSet"]["temp"] as $key => $value){
									foreach($value as $sakey => $savalue){
										$num = ((($sakey + $response["currentPage"]) + (24 * $response["currentPage"])) - 24) - 1;
										if($key != "contact"){
											$response["recordSet"][$key][$num] = (string)$response["recordSet"]["temp"][$key][$sakey];
										}else{
											$response["recordSet"]["temp"][$key][] = (array)$response["recordSet"]["temp"][$key][$sakey];
											foreach($response["recordSet"]["temp"][$key] as $takey => $tavalue){
												foreach($response["recordSet"]["temp"][$key][$takey] as $fakey => $favalue){
													$contactType = (string)$response["recordSet"]["temp"][$key][$takey]["contactType"];
													// Isolate registration contact
													if($contactType == "Registration"){
														$response["recordSet"][$key][$takey][$fakey] = (string)$response["recordSet"]["temp"][$key][$takey][$fakey];
													} // if($contactType == "Registration")
												} // foreach($response["recordSet"]["temp"][$key][$takey] as $fakey => $favalue)
											} // foreach($response["recordSet"][$key] as $takey => $tavalue)
										} // if($key != "contact")
									} // foreach($value as $sakey => $savalue)
								} // foreach($response["recordSet"]["domain"] as $key => $value)
								// Contacts
								$response["recordSet"]["contact"] = array_values($response["recordSet"]["contact"]);
								foreach($response["recordSet"]["contact"] as $key => $val){
									$num = ((($key + $response["currentPage"]) + (24 * $response["currentPage"])) - 24) - 1;
									foreach($val as $sakey => $saval){
										$response["recordSet"][$sakey][$num] = $saval;
									} // foreach($val as $sakey => $saval)
								} // foreach($response["recordSet"]["contact"] as $key => $val)
								// Remove temporary data
								unset($response["recordSet"]["temp"]);
								// Remove duplicate data
								unset($response["recordSet"]["contact"]);
							break;
							case "list":
								$response["recordSet"]["temp"]["domain"] = (array)$result->xpath("//domainName");
								// Cast simple XML object into an array of strings
								foreach($response["recordSet"]["temp"] as $key => $value){
									if(!is_array($value)){
										$response["recordSet"]["domain"] = (string)$value;
									} // if(!is_array($value))
								} // foreach($response["recordSet"]["temp"] as $key => $value)
								// Remove temporary data
								unset($response["recordSet"]["temp"]);
							break;
							default:
								$response["temp"] = (array)$result->xpath("//domain");
								foreach($response["temp"] as $key => $value){
									$value = (array)$value;
									$num = (($key + $response["currentPage"]) + (24 * $response["currentPage"])) - 24;
									foreach($value as $akey => $val){
										if($akey == "userId"){
											$response[$num][$akey] = (string)$val;
										}else{
											$val = (array)$val;
											foreach($val as $sakey => $saval){
												if(($sakey != "contact") && ($sakey != "nameServer")){
													$response[$num][$sakey] = (string)$saval;
												}else{
													if($sakey == "contact"){
														foreach($saval as $takey => $taval){
															$taval = (array)$taval;
															$contactType = (string)$taval["contactType"];
															if($contactType == "Registration"){
																foreach($taval as $fakey => $faval){
																	$response[$num]["contact"][$fakey] = (string)$faval;
																} // foreach($taval as $fakey => $faval)
															} // if($contactType == "Registration")
														} // foreach($saval as $takey => $taval)
													} // if($sakey == "contact")
												} // if(($sakey != "contact") && ($sakey != "nameServer"))
											} // foreach($val as $sakey => $saval)
										} // if($akey == "userId")
									} // foreach($value as $akey => $val)
								} // foreach($response["temp"] as $key => $value)
								// Remove temporary data
								unset($response["temp"]);
							break;
						} // switch($result_view)
					} // elseif($domainGet > 1)
					else{
						$response = false;
					} // else ... if($domainGet == 1)
				break;

				case "domainLock":
					//$response["command"] = (string)$result->command;
					//$response["reference"] = (string)$result->clientRef;
					//$response["status"] = (string)$result->status->statusCode . ' ' . (string)$result->status->statusDescription;
					$response["productId"] = (string)$result->response->productId;
				break;

				case "domainModify":
					//$response["command"] = (string)$result->command;
					//$response["reference"] = (string)$result->clientRef;
					//$response["status"] = (string)$result->status->statusCode . ' ' . (string)$result->status->statusDescription;
					$response["productId"] = (string)$result->response->productId;
				break;

				case "domainPassword":
					//$response["command"] = (string)$result->command;
					//$response["reference"] = (string)$result->clientRef;
					//$response["status"] = (string)$result->status->statusCode . ' ' . (string)$result->status->statusDescription;
					$response["productId"] = (string)$result->response->productId;
					$response["authCode"] = (string)$result->response->password;
				break;

				case "domainPrivacyAdd":
					//$response["command"] = (string)$result->command;
					//$response["reference"] = (string)$result->clientRef;
					//$response["status"] = (string)$result->status->statusCode . ' ' . (string)$result->status->statusDescription;
					$response["productId"] = (string)$result->response->productId;
				break;

				case "domainRenew":
					//$response["command"] = (string)$result->command;
					//$response["reference"] = (string)$result->clientRef;
					//$response["status"] = (string)$result->status->statusCode . ' ' . (string)$result->status->statusDescription;
					$response["productId"] = (string)$result->response->productId;
					$response["expiryDate"] = (string)$result->response->expiryDate;
				break;

				case "domainTransferCancel":
				case "domainTransferIn":
					//$response["command"] = (string)$result->command;
					//$response["reference"] = (string)$result->clientRef;
					//$response["status"] = (string)$result->status->statusCode . ' ' . (string)$result->status->statusDescription;
					$response["productId"] = (string)$result->response->productId;
				break;

				case "userAdd":
					if($status == "1000"){
						$response["userId"] = (string)$result->response->userId;
					}else{
						$response["userId"] = true; // actually handled in outer if
					} // if($status == "1000")
				break;

				case "userModify":
					$response["userId"] = (string)$result->response->userId;
				break;

				case "zoneAdd":
				case "zoneDelete":
					//$response["command"] = (string)$result->command;
					//$response["reference"] = (string)$result->clientRef;
					//$response["status"] = (string)$result->status->statusCode . ' ' . (string)$result->status->statusDescription;
					$response["productId"] = (string)$result->response->productId;
				break;

				case "zoneGet":
					//$response["command"] = (string)$result->command;
					//$response["reference"] = (string)$result->clientRef;
					//$response["status"] = (string)$result->status->statusCode . ' ' . (string)$result->status->statusDescription;
					$response["productId"] = (string)$result->response->productId;
					$response["zones"] = (array)$result->response->productId;
					/* Needs conversion like domainGet if going to be used */
				break;

				default:
					$response = false;
				break;

			} // switch($command)

		}elseif((($command == "userAdd") && ($status == "1005")) || (($command == "userModify") && ($status == "1101"))){
			/* Special Success Condition */
			$_SESSION["flag"] = false;
			$response["userId"] = true;
		}else{
			/* Failure */
			$_SESSION["flag"] = true;
			if(is_object($result)){
				$_SESSION["flag"] = false;
				$status_text = (array)$result->xpath("//statusDescription[1]");
				if(is_array($status_text) && (count($status_text) > 0)){
					$status_text = (string)$status_text[0];
				}else{
					$status_text = false;
				}
				$_SESSION["errors"][] = '<span class="errorrc">ERROR</span>: Error #' . $status . ' in <strong>' . $command . '</strong>: ' . $status_text;
			}else{
				$_SESSION["flag"] = true;
				$status_text = false;
				if(!$_SESSION["flag"]){
					$_SESSION["errors"][] = '<span class="errorrc">ERROR</span>: Register.com did not respond to the ' . $application["translateEnglish"][$command] . ' request.';
				}
			} // if(is_object($result))
			$response = false;
		} // if($status == "1000")

		if(!isset($response)){$response = false;}
		return $response;
	} // function interpret_response

?>
