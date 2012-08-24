<?php

$list = '[id]
      ,[relate_id]
      ,[parent_id]
      ,[user_id]
      ,[order_id]
      ,[created_dt]
      ,[modified_dt]
      ,[complete_dt]
      ,[status]
      ,[zone_id]
      ,[nurse_id]
      ,[visit_date]
      ,[visit_time]
      ,[visit_time_end]
      ,[wish_time]
      ,[patient_id]
      ,[discount_id]
      ,[referer_id]
      ,[partner_id]
      ,[sum]
      ,[final_sum]
      ,[town]
      ,[street]
      ,[house]
      ,[house_case]
      ,[building]
      ,[entrance]
      ,[floor]
      ,[flat]
      ,[speaker]
      ,[coord_x]
      ,[coord_y]
      ,[phone]
      ,[mobile]
      ,[email]
      ,[reject_reason]
      ,[reject_comment]
      ,[permit_status]
      ,[driving]
      ,[extra]
      ,[delivery_address]
      ,[checkup]
      ,[checkup_data]
      ,[guarantee]
      ,[guarantee_path]
      ,[long_distance]
      ,[logist_order]
      ,[route_complete]
      ,[called]
      ,[called_attempt]
      ,[not_satisfied]';

$list = explode(',', preg_replace('#[\s\[\]]+#si', '', $list)); echo join(',', $list);
$tpl = '
		+ CASE 
			WHEN i.%%%% != d.%%%% THEN \'"%%%%": "\'+CAST(i.%%%% AS VARCHAR(max))+\'",\'
			ELSE \'\'
		END';

$tpl = '
		+ \'"%%%%": "\'+CAST(ISNULL(%%%%, \'\') AS VARCHAR(max))+\'",\'';

$sql = '';

foreach($list as $v) $sql .= str_replace('%%%%', $v, $tpl);
file_put_contents('sql.sql', $sql);
