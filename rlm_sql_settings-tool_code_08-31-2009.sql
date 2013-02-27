SELECT
	COUNT(DISTINCT fk_entity_id) "distinct keys"
FROM
	main_account_table
WHERE

	main_account_table.fk_entity_id IN (

		-- Entities relating to selected meta records AND whose account is the specified type & setting (scenario 1)

		SELECT
			entity_meta_xref.fk_entity_id
		FROM
			entity_meta_xref
		WHERE

			entity_meta_xref.fk_meta_id IN (

				-- Meta record which is active and of a specified type

				SELECT
					main_meta_table.pk_meta_id
				FROM
					main_meta_table
				WHERE
					main_meta_table.active = '1'
				AND
					main_meta_table.fk_meta_type = '1' -- Type ______
				AND
					main_meta_table.setting_opt_1 IS NOT null

			)

		AND
  
		entity_meta_xref.fk_entity_id IN (

			-- Entities who have accounts of a type and a certain setting
	
			SELECT
				main_account_table.fk_entity_id
			FROM
				main_account_table,
				account_settings_table
			WHERE
				main_account_table.pk_account_id = account_settings_table.fk_account_id
			AND
				account_settings_table.setting_opt_2 = '1'
			AND
				main_account_table.fk_account_type IN ('A','B','C','D') 
				 -- A - _____________
				 -- B - _____________
				 -- C - _____________
				 -- D - _____________
		)

	)

OR

	main_account_table.fk_entity_id IN (

		-- Entities relating to selected meta records AND whose account is the specified type & setting (scenario 2)

		SELECT
			entity_meta_xref.fk_entity_id
		FROM
			entity_meta_xref
		WHERE
			entity_meta_xref.setting_opt_3 = '1'
	
		AND
	
			entity_meta_xref.fk_meta_id IN (
	
				-- Meta record which is active and of a specified type
	
				SELECT
					main_meta_table.pk_meta_id
				FROM
					main_meta_table
				WHERE
					main_meta_table.active = '1'
				AND
					main_meta_table.fk_meta_type = '1' -- Type ______
				AND
					main_meta_table.setting_opt_1 IS NOT null
				AND
					main_meta_table.setting_opt_4 = '1'
	
			)
	
		AND
	  
			entity_meta_xref.fk_entity_id IN (
	
				-- Entities who have accounts
		
				SELECT
					main_account_table.fk_entity_id
				FROM
					main_account_table
				WHERE
					main_account_table.fk_account_type IN ('A','B','C','D','E','F','G','H','I')
					 -- A - _____________
					 -- B - _____________
					 -- C - _____________
					 -- D - _____________
					 -- E - _____________
					 -- F - _____________
					 -- G - _____________
					 -- H - _____________
					 -- I - _____________
			)

	)
