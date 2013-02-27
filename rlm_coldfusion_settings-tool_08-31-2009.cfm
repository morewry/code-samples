<cfif (NOT StructIsEmpty(FORM)) AND IsDefined("FORM.Action") AND (FORM.Action IS "Change")>
	
	<!--- 
		Build "update" structure from dot-notation form names 
		(groups values to update according to primary keys)
		source: bennadel.com
	--->
	<cfloop item="key" collection="#FORM#">
		<cfif Find(".", key)>
			<cfset value = FORM[key]>
			<cfset StructDelete(FORM, key)>
			<cfset "update.#key#" = value>
		</cfif> <!--- Find(".", key) --->
	</cfloop> <!--- item="key" collection="#FORM#" --->

	<!--- Primary Loop --->
	<cfloop item="primary" collection="#update#">
		<cfset primaryid = ReplaceNoCase(primary, "p_", "")>

		<!--- Secondary Loop --->
		<cfloop item="secondary" collection="#update[primary]#">
			<cfset secondaryid = ReplaceNoCase(secondary, "s_", "")>

			<!--- Update update_table_name with new settings --->
			<cfif (EntityID IS NOT "") AND (StructKeyExists(update[primary][secondary], "update_table_name"))>
				<cfquery name="UpdateOptions_Type1">
					MERGE INTO	
						update_table_name
	
					USING	
						(SELECT
							#EntityID# entity, -- number(10)
							#primaryid# primaryid, -- number(5)
							'#secondaryid#' secondaryid -- varchar2(30)
						 FROM dual
						) temp
	
					ON
						(
							update_table_name.fk_entity = temp.entity
							AND update_table_name.fk_primary_id = temp.primaryid
							AND update_table_name.fk_secondary_id = temp.secondary
						)
				
					WHEN MATCHED THEN
	
						UPDATE SET
							update_table_name.setting_option_1 = '<cfif StructKeyExists(update[primary][secondary]["update_table_name"], "setting_option_1")>#update[primary][secondary]["update_table_name"]["setting_option_1"]#</cfif>', -- Setting for ____, possible values ____
							update_table_name.setting_option_2 = '<cfif StructKeyExists(update[primary][secondary]["update_table_name"], "setting_option_2")>#update[primary][secondary]["update_table_name"]["setting_option_2"]#</cfif>', -- Setting for ____, possible values ____
							update_table_name.setting_option_3 = '<cfif StructKeyExists(update[primary][secondary]["update_table_name"], "setting_option_3")>#update[primary][secondary]["update_table_name"]["setting_option_3"]#</cfif>', -- Setting for ____, possible values ____
							update_table_name.setting_option_4 = '<cfif StructKeyExists(update[primary][secondary]["update_table_name"], "setting_option_4")>#update[primary][secondary]["update_table_name"]["setting_option_4"]#</cfif>' -- Setting for ____, possible values ____
					
					WHEN NOT MATCHED THEN
				
						INSERT
							(fk_entity, fk_primary_id, secondary, setting_option_1, setting_option_2, setting_option_3, setting_option_4)
						VALUES
							(
								temp.entity,
								temp.primaryid,
								temp.secondary,
								'<cfif StructKeyExists(update[primary][secondary]["update_table_name"], "setting_option_1")>#update[primary][secondary]["update_table_name"]["setting_option_1"]#</cfif>', -- Setting for ____, possible values ____ (setting_option_1)
								'<cfif StructKeyExists(update[primary][secondary]["update_table_name"], "setting_option_2")>#update[primary][secondary]["update_table_name"]["setting_option_2"]#</cfif>', -- Setting for ____, possible values ____ (setting_option_2)
								'<cfif StructKeyExists(update[primary][secondary]["update_table_name"], "setting_option_3")>#update[primary][secondary]["update_table_name"]["setting_option_3"]#</cfif>', -- Setting for ____, possible values ____ (setting_option_3)
								'<cfif StructKeyExists(update[primary][secondary]["update_table_name"], "setting_option_4")>#update[primary][secondary]["update_table_name"]["setting_option_4"]#</cfif>' -- Setting for ____, possible values ____ (setting_option_4)
							)
				</cfquery>
			</cfif> <!--- (EntityID IS NOT "") AND (StructKeyExists(update[primary][secondary], "update_table_name")) --->
		</cfloop> <!--- item="secondary" collection="#update[primary]#" --->

		<!--- Update setting_option_5 with new settings --->
		<cfif (EntityID IS NOT "") AND (StructKeyExists(update[primary], "setting_option_5"))>
			<cfquery name="UpdateOptions_Type2">
				MERGE INTO	
					setting_option_5

				-- ... etc.
			</cfquery>
		</cfif> <!--- (EntityID IS NOT "") AND (StructKeyExists(update[primary], "second_update_table")) --->

		<!--- Update third_setting_table with new settings --->
		<cfif (EntityID IS NOT "") AND (StructKeyExists(update[primary], "third_setting_table"))>
			<cfquery name="UpdateOptions_Type3">
				MERGE INTO	
					third_setting_table

				-- ... etc.
			</cfquery>
		</cfif> <!--- (EntityID IS NOT "") AND (StructKeyExists(update[primary], "third_setting_table")) --->


	</cfloop> <!--- item="primary" collection="#update#" --->
</cfif> <!--- (NOT StructIsEmpty(FORM)) AND IsDefined("FORM.Action") AND (FORM.Action IS "Change") --->

<!--- Output form to control setting options --->
<form method="post" id="control_options">
	<input type="hidden" name="EntityID" value="<cfoutput>#ThisEntityID#</cfoutput>">
	<table width="100%" class="data_table">
		<colgroup width="325">
			<col width="65"></col>
			<col width="250"></col>
		</colgroup>
		<col width="150"></col>
		<colgroup width="460">
			<col width="115"></col>
			<col width="115"></col>
			<col width="115"></col>
			<col width="115"></col>
		</colgroup>
		<thead>
			<tr>
				<th colspan="2">Primary</th>
				<th rowspan="2">Secondary</th>
				<th rowspan="2">
					Setting 1
				</th>
				<th rowspan="2">
					Setting 2
				</th>
				<th rowspan="2">
					Setting 3
				</th>
				<th rowspan="2">
					Setting 4
				</th>
			</tr>
			<tr>
				<th>Primary ID</th>
				<th>Primary Name</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="7" align="center">
					<input type="submit" name="action" value="Change">
				</td>
			</tr>
		</tfoot>
		<tbody>
			<cfoutput query="qSettings" group="pk_primary_id">
			
			<!--- Determine rowspan --->
			<cfquery name="rowspan" dbtype="query">
				SELECT DISTINCT fk_secondary_id FROM qSettings WHERE pk_primary_id = #pk_primary_id#
			</cfquery>

			<tr>

				<td align="center"<cfif rowspan.recordCount GT 1> rowspan="#rowspan.recordCount#"</cfif>>
					[#NumberFormat(pk_primary_id, "00000")#</a>]
				</td>
				<td<cfif rowspan.recordCount GT 1> rowspan="#rowspan.recordCount#"</cfif>>
					#primary_name#
				</td>

				<cfoutput group="fk_secondary_id">
				<td>
					#fk_secondary_id#
				</td>

				<!--- Setting Option 1 --->
				<td class="setting">
					<!--- Setting Option 1 Override Controls/Status (Allowed-Override-Prohibited) --->
					<cfswitch expression="#setting_option_1#">
						<cfcase value="1">
							<!--- 1: Setting Option 1 Allowed --->
							<span class="label allowed">
								<img src="/images/pinvoke/icons/diagona/10/check.png" alt="" width="10" height="10"> 
								<span>Allowed</span>
							</span>
						</cfcase>
						<cfcase value="0">
							<!--- 0: Setting Option 1 Prohibited.  Per special permission, insert "1" into setting_option_4 to create an exception --->
							<cfif find("own", setting_option_4) GT 0>
								<span class="label prohibited">
									<img src="/images/pinvoke/icons/diagona/10/xmark.png" alt="" width="10" height="10">
									<span>Prohibited</span>
								</span>
								<label class="prohibited">
									<input type="checkbox" name="p_#pk_primary_id#.s_#fk_secondary_id#.update_table_name.setting_option_4" value="own" checked="checked"> 
									<span>Excepted</span>
								</label>
							<cfelse>
								<span class="label prohibited">
									<img src="/images/pinvoke/icons/diagona/10/xmark.png" alt="" width="10" height="10">
									<span>Prohibited</span>
								</span>
								<label class="prohibited">
									<input type="checkbox" name="p_#pk_primary_id#.s_#fk_secondary_id#.update_table_name.setting_option_4" value="own"> 
									<span>Exception</span>
								</label>
							</cfif>
						</cfcase>
						<cfdefaultcase>
							<!--- NULL: Setting Option 1 Override.  Insert "1" into setting_option_4 to authorize --->
							<cfif find("1", setting_option_4) GT 0>
								<label class="override">
									<input type="checkbox" name="p_#pk_primary_id#.s_#fk_secondary_id#.update_table_name.setting_option_4" value="own" checked="checked"> 
									<span>Overridden</span>
								</label>
							<cfelse>
								<label class="override">
									<input type="checkbox" name="p_#pk_primary_id#.s_#fk_secondary_id#.update_table_name.setting_option_4" value="own"> 
									<span>Override</span>
								</label>
							</cfif>
						</cfdefaultcase>
					</cfswitch>		
					<!--- Setting Option 1 Controls/Status (Accepted-Declined) --->
					<div class="accept">
					<cfswitch expression="#setting_option_1#">
						<cfcase value="1">
							<label class="checked">
								<input type="radio" name="p_#pk_primary_id#.s_#fk_secondary_id#.update_table_name.setting_option_1" value="1" checked="checked"> 
								<dfn title="Reminder of front-end setting / location this corresponds to">Accepted</dfn>
							</label>
							<label>
								<input type="radio" name="p_#pk_primary_id#.s_#fk_secondary_id#.update_table_name.setting_option_1" value="0"> 
								Declined
							</label>
						</cfcase>
						<cfdefaultcase>
							<label>
								<input type="radio" name="p_#pk_primary_id#.s_#fk_secondary_id#.update_table_name.setting_option_1" value="1"> 
								<dfn title="Reminder of front-end setting / location this corresponds to">Accepted</dfn>
							</label>
							<label class="checked">
								<input type="radio" name="p_#pk_primary_id#.s_#fk_secondary_id#.update_table_name.setting_option_1" value="0" checked="checked"> 
								Declined
							</label>
						</cfdefaultcase>
					</cfswitch>
					</div>
				</td>

				<!--- Setting Option 2 --->
				<td class="setting">
					<!--- Setting Option 2 Controls/Status (Allowed-Override-Prohibited) --->
					<cfswitch expression="#setting_option_4#">
						<cfcase value="1">
							<!--- 1: Setting Option 2 Override.  Insert "2" into setting_option_4 to authorize --->
							<cfif find("2", setting_option_4) GT 0>
								<label class="override">
									<input type="checkbox" name="p_#pk_primary_id#.s_#fk_secondary_id#.update_table_name.setting_option_4" value="2" checked="checked"> 
									<span>Overridden</span>
								</label>
							<cfelse>
								<label class="override">
									<input type="checkbox" name="p_#pk_primary_id#.s_#fk_secondary_id#.update_table_name.setting_option_4" value="2"> 
									<span>Override</span>
								</label>
							</cfif>
						</cfcase>
						<cfcase value="0">
							<!--- 0: Setting Option 2 Prohibited.  Per special permission, insert "2" into setting_option_4 to create an exception --->
							<cfif find("2", setting_option_4) GT 0>
								<span class="label prohibited">
									<img src="/images/pinvoke/icons/diagona/10/xmark.png" alt="" width="10" height="10"> 
									<span>Prohibited</span>
								</span>
								<label class="prohibited">
									<input type="checkbox" name="p_#pk_primary_id#.s_#fk_secondary_id#.update_table_name.setting_option_4" value="2" checked="checked">
									<span>Excepted</span>
								</label>
							<cfelse>
								<span class="label prohibited">
									<img src="/images/pinvoke/icons/diagona/10/xmark.png" alt="" width="10" height="10"> 
									<span>Prohibited</span>
								</span>
								<label class="prohibited">
									<input type="checkbox" name="p_#pk_primary_id#.s_#fk_secondary_id#.update_table_name.setting_option_4" value="2">
									<span>Exception</span>
								</label>
							</cfif>
						</cfcase>
						<cfcase value="2">
							<!--- 2: Setting Option 2 Allowed (type1) --->
							<span class="label allowed">
								<img src="/images/pinvoke/icons/diagona/10/check.png" alt="" width="10" height="10"> 
								<dfn title="Definition of which 'allowed' this is, there are two versions">Allowed</dfn>
							</span>
						</cfcase>
						<cfcase value="3">
							<!--- 3: Setting Option 2 Allowed (type2) --->
							<span class="label allowed">
								<img src="/images/pinvoke/icons/diagona/10/check.png" alt="" width="10" height="10"> 
								<dfn title="Definition of which 'allowed' this is, there are two versions">Allowed</dfn>
							</span>
						</cfcase>
					</cfswitch>		
					<!--- Setting Option 2 Controls/Status (Accepted-Declined) --->
					<div class="accept">
					<cfswitch expression="#setting_option_2#">
						<cfcase value="1">
							<label>
								<input type="radio" name="p_#pk_primary_id#.s_#fk_secondary_id#.update_table_name.setting_option_2" value="0"> 
								<dfn title="Reminder of front-end setting / location this corresponds to">Accepted</dfn>
							</label>
							<label class="checked">
								<input type="radio" name="p_#pk_primary_id#.s_#fk_secondary_id#.update_table_name.setting_option_2" value="1" checked="checked"> 
								Declined
							</label>
						</cfcase>
						<cfdefaultcase>
							<label class="checked">
								<input type="radio" name="p_#pk_primary_id#.s_#fk_secondary_id#.update_table_name.setting_option_2" value="0" checked="checked"> 
								<dfn title="Reminder of front-end setting / location this corresponds to">Accepted</dfn>
							</label>
							<label>
								<input type="radio" name="p_#pk_primary_id#.s_#fk_secondary_id#.update_table_name.setting_option_2" value="1"> 
								Declined
							</label>
						</cfdefaultcase>
					</cfswitch>
					</div>
				</td>
				<!--- /Setting Option 2 --->

				<!--- Setting Option 3 --->
				<td class="setting">
					<!--- Setting Option 3 Controls/Status (Allowed-Override-Prohibited) --->
					<cfswitch expression="#mls_mask_yn#">
						<cfcase value="O">
							<!--- O: Setting Option 3 Override.  Insert "3" into setting_option_4 to authorize --->
							<cfif find("3", setting_option_4) GT 0>
								<label class="override">
									<input type="checkbox" name="p_#pk_primary_id#.s_#fk_secondary_id#.update_table_name.setting_option_4" value="3" checked="checked"> 
									<span>Overridden</span>
								</label>
							<cfelse>
								<label class="override">
									<input type="checkbox" name="p_#pk_primary_id#.s_#fk_secondary_id#.update_table_name.setting_option_4" value="3"> 
									<span>Override</span>
								</label>
							</cfif>
						</cfcase>
						<cfcase value="1">
							<!--- 1: Setting Option 3 Prohibited.  Per special permission, insert "3" into setting_option_4 to create an exception --->
							<cfif find("3", setting_option_4) GT 0>
								<span class="label prohibited">
									<img src="/images/pinvoke/icons/diagona/10/xmark.png" alt="" width="10" height="10"> 
									<span>Prohibited</span>
								</span>
								<label class="prohibited">
									<input type="checkbox" name="p_#pk_primary_id#.s_#fk_secondary_id#.update_table_name.setting_option_4" value="3" checked="checked">
									<span>Excepted</span>
								</label>
							<cfelse>
								<span class="label prohibited">
									<img src="/images/pinvoke/icons/diagona/10/xmark.png" alt="" width="10" height="10"> 
									<span>Prohibited</span>
								</span>
								<label class="prohibited">
									<input type="checkbox" name="p_#pk_primary_id#.s_#fk_secondary_id#.update_table_name.setting_option_4" value="3">
									<span>Exception</span>
								</label>
							</cfif>
						</cfcase>
						<cfcase value="2">
							<!--- 2: Setting Option 3 (type1) --->
							<span class="label allowed">
								<img src="/images/pinvoke/icons/diagona/10/check.png" alt="" width="10" height="10"> 
								<dfn title="Definition of which 'allowed' this is, there are two versions">Allowed</dfn>
							</span>
						</cfcase>
						<cfcase value="3">
							<!--- 3: Setting Option 3 Allowed (type2) --->
							<span class="label allowed">
								<img src="/images/pinvoke/icons/diagona/10/check.png" alt="" width="10" height="10"> 
								<dfn title="Definition of which 'allowed' this is, there are two versions">Allowed</dfn>
							</span>
						</cfcase>
					</cfswitch>		
					<!--- Setting Option 3 Controls/Status (Accepted-Declined) --->
					<div class="accept">
					<cfswitch expression="#setting_option_3#">
						<cfcase value="1">
							<label>
								<input type="radio" name="p_#pk_primary_id#.s_#fk_secondary_id#.update_table_name.setting_option_3" value="0"> 
								<dfn title="Reminder of front-end setting / location this corresponds to">Accepted</dfn>
							</label>
							<label class="checked">
								<input type="radio" name="p_#pk_primary_id#.s_#fk_secondary_id#.update_table_name.setting_option_3" value="1" checked="checked"> 
								Declined
							</label>
						</cfcase>
						<cfdefaultcase>
							<label class="checked">
								<input type="radio" name="p_#pk_primary_id#.s_#fk_secondary_id#.update_table_name.setting_option_3" value="0" checked="checked"> 
								<dfn title="Reminder of front-end setting / location this corresponds to">Accepted</dfn>
							</label>
							<label>
								<input type="radio" name="p_#pk_primary_id#.s_#fk_secondary_id#.update_table_name.setting_option_3" value="1"> 
								Declined
							</label>
						</cfdefaultcase>
					</cfswitch>
					</div>
				</td>
				<!--- /Setting Option 3 --->
				
				<!--- Setting Option 5 --->
				<cfoutput group="setting_option_5">
					<cfparam name="lastRow" default="">
					<cfset thisRow = pk_primary_id>
					<cfif thisRow IS NOT lastRow>
					<td class="setting"<cfif rowspan.recordCount GT 1> rowspan="#rowspan.recordCount#"</cfif>>
						<!--- Setting Option 5 Controls/Status (Allowed-Override-Prohibited) --->
						<cfswitch expression="#setting_option_5_global#">
							<cfcase value="1">
								<!--- 1: type1 --->
								<cfif setting_option_5 IS "1">
									<label class="override">
										<input type="checkbox" name="p_#pk_primary_id#.setting_option_5" value="1" checked="checked"> 
										<dfn title="Definition of which 'allowed' this is, there are two versions">Authorized</dfn>
									</label>
								<cfelse>
									<label class="override">
										<input type="checkbox" name="p_#pk_primary_id#.setting_option_5" value="1"> 
										<dfn title="Definition of which 'allowed' this is, there are two versions">Authorize</dfn>
									</label>
								</cfif>
							</cfcase>
							<cfcase value="0">
								<!--- 0: type2 --->
								<cfif setting_option_5 IS 1>
									<label class="override">
										<input type="checkbox" name="p_#pk_primary_id#.setting_option_5" value="1" checked="checked"> 
										<dfn title="Definition of which 'allowed' this is, there are two versions">Authorized</dfn>
									</label>
								<cfelse>
									<label class="override">
										<input type="checkbox" name="p_#pk_primary_id#.setting_option_5" value="1"> 
										<dfn title="Definition of which 'allowed' this is, there are two versions">Authorize</dfn>
									</label>
								</cfif>
							</cfcase>
							<cfdefaultcase>
								<!--- NULL: Prohibited.  Per special permission, create an exception --->
								<cfif setting_option_5 IS 1>
									<span class="label prohibited">
										<img src="/images/pinvoke/icons/diagona/10/xmark.png" alt="" width="10" height="10"> 
										<span>Prohibited</span>
									</span>
									<label class="prohibited">
										<input type="checkbox" name="p_#pk_primary_id#.setting_option_5" value="1" checked="checked">
										<span>Excepted</span>
									</label>
								<cfelse>
									<span class="label prohibited">
										<img src="/images/pinvoke/icons/diagona/10/xmark.png" alt="" width="10" height="10"> 
										<span>Prohibited</span>
									</span>
									<label class="prohibited">
										<input type="checkbox" name="p_#pk_primary_id#.setting_option_5" value="1">
										<span>Exception</span>
									</label>
								</cfif>
							</cfdefaultcase>
						</cfswitch>
						<!--- Setting Option 5 Controls/Status (Accepted-Declined) --->
						<div class="accept">
						<cfswitch expression="#setting_option_5b#">
							<cfcase value="1">
								<label>
									<input type="radio" name="p_#pk_primary_id#.third_setting_table.setting_option_5b" value="0"> 
									<dfn title="Reminder of front-end setting / location this corresponds to">Accepted</dfn>
								</label>
								<label class="checked">
									<input type="radio" name="p_#pk_primary_id#.third_setting_table.setting_option_5b" value="1" checked="checked"> 
									Declined
								</label>
							</cfcase>
							<cfdefaultcase>
								<label class="checked">
									<input type="radio" name="p_#pk_primary_id#.third_setting_table.setting_option_5b" value="0" checked="checked"> 
									<dfn title="Reminder of front-end setting / location this corresponds to">Accepted</dfn>
								</label>
								<label>
									<input type="radio" name="p_#pk_primary_id#.third_setting_table.setting_option_5b" value="1"> 
									Declined
								</label>
							</cfdefaultcase>
						</cfswitch>
						</div>
					</td>
					<cfset lastRow = pk_primary_id>
					</cfif> <!--- thisRow IS NOT lastRow --->
					<!--- /Setting Option 5 --->

				</cfoutput> <!--- group="setting_option_5" --->
			</tr>

				</cfoutput> <!--- group="fk_secondary_id" --->
			</cfoutput> <!--- query="qSettings" group="pk_primary_id" --->
		</tbody>
	</table>
</form>