<?php
require_once('includes/config.php');
require_once('includes/database.php');
require_once('includes/session.php');

function set_resched_list($resched){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$retval = $config['function']['return']['success'];
	$i = 0;
	while($i < count($resched)){
		$j = 0;
		while($j < count($resched[$i]['id'])){
			if($j == 0){
				$sql = "UPDATE `".$config['db']['database']."`.`work_status_level_2` 
					SET `start_date_plan` = DATE_ADD(start_date_plan, INTERVAL ".$resched[$i]['day']." DAY)
					WHERE `id_ws_2` = ".$resched[$i]['id'][$j];
			} else {
				$sql = "UPDATE `".$config['db']['database']."`.`work_status_level_2` 
					SET `start_date_plan` = DATE_ADD(start_date_plan, INTERVAL ".$resched[$i]['day']." DAY), 
						`end_date_plan` = DATE_ADD(end_date_plan, INTERVAL ".$resched[$i - 1]['day']." DAY)
					WHERE `id_ws_2` = ".$resched[$i]['id'][$j];
			}
			
			if(!$conn->query($sql)) {
				$retval = $retval && $config['function']['return']['failure'];
			}
			$j++;
		}
		$i++;
	}
	
	$conn->close();
	
	return $retval;
}

function get_resched_list($id){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$id = mysql_real_escape_string($id);
	$late = null;
	$sql = "SELECT DISTINCT A.* , DATEDIFF(A.end_date, A.end_date_plan) AS late, E.step FROM  `work_status_level_2` A
			 LEFT JOIN `".$config['db']['database']."`.`work_status_level_1` B on (A.id_ws_1 = B.id_ws_1) 
			 LEFT JOIN activity_link C on (C.id_predecessor = A.id_activity) 
			 LEFT JOIN work_status_level_2 D on (C.id_successor = D.id_activity) 
			 LEFT JOIN activity E on (E.id_activity = A.id_activity) 
			 WHERE B.id_order = ".$id." AND A.time_status = 1 AND A.work_status = 4 AND D.work_status <> 4 ";
		
	
	$result = $conn->query($sql);
	$list = array();
	$successor = array();
	$i = 0;
	while($row = mysql_fetch_assoc($result)) {
		if($late == null){
			$late = $row2['late'];
		}
		if($row['late'] > $late){
			$late = $row['late'];
			$successor[$i] = array();
			$successor[$i][] = $row['id_ws_2'];
			$step = $row['step'];
		} else if($row['late'] == $late){
			$late = $row['late'];
			$successor[$i][] = $row['id_ws_2'];
		} 
	}
	
	$max_step = get_max_step();
	
	
	$id_step = array();
	
	$step = $step + 1;
	while($step <= $max_step && count($successor[$i]) > 0){
		$str = '(';
		$j = 0;
		while($j < count($successor[$i])){
			$str .= 'D.id_ws_2 = ';
			$str .= $successor[$i][$j];
			$j++;
			if($j < count($successor[$i])){
				$str .= ' OR ';
			}
		}
		$str .= ')';
		
		$sql = "select distinct A.* from work_status_level_2 A
			LEFT JOIN `work_status_level_1` B on (A.id_ws_1 = B.id_ws_1) 
			LEFT JOIN activity_link C on (C.id_successor = A.id_activity) 
			LEFT JOIN work_status_level_2 D on (C.id_predecessor = D.id_activity) 
			LEFT JOIN activity E on (E.id_activity = A.id_activity) 
			where B.id_order = ".$id." and E.step = ".$step." and E.type = 1 and ".$str;
		
		
		$result = $conn->query($sql);
		while($row = mysql_fetch_assoc($result)) {
			$successor[$i+1][] = $row['id_ws_2'];
			$list[$i]['id'][] = $row['id_ws_2'];
			$id_step[$i][] = $row['id_activity'];
		}
		
		if($i == 0){
			$list[$i]['day'] = $late;
			$list[$i]['max'] = $late;
		} else {
			$list[$i]['day'] = 0;
			$list[$i]['max'] = 0;
		}
		$list[$i]['list'] = "Step #".$step." [";
		$j = 0;
		while($j < count($id_step[$i])){
			$list[$i]['list'] .= $id_step[$i][$j];
			$j++;
			if($j < count($id_step[$i])){
				$list[$i]['list'] .= ', ';
			}
		}
		
		$list[$i]['list'] .= "]";
		$list[$i]['step'] = $step;
		$i++;
		$step++;
	}
	
	$conn->close();
	
	return $list;
}

function get_max_step(){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$sql = "SELECT * FROM `".$config['db']['database']."`.`activity`
			WHERE level = 2 AND type = 1";
	
	$result = $conn->query($sql);
	
	$max = 0;
	while($row = mysql_fetch_assoc($result)) {
		if($row['step'] >= $max){
			$max = $row['step'];
		}
	}
	
	$conn->close();
	
	return $max;
}

function get_all_order_late(){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$sql = "SELECT A.*, B.name_of_company FROM `".$config['db']['database']."`.`sales_order` A
			inner join `".$config['db']['database']."`.`buyer` B on (A.buyer = B.id_buyer)
			ORDER BY `delivery_date` ASC";
	
	$result = $conn->query($sql);
	
	$list = array();
	$i = 0;
	while($row = mysql_fetch_assoc($result)) {
		$list[] = $row;
		$late = null;
		
		$sql2 = "SELECT DISTINCT A . * , DATEDIFF(A.end_date, A.end_date_plan) AS late FROM  `work_status_level_2` A
				 LEFT JOIN `".$config['db']['database']."`.`work_status_level_1` B on (A.id_ws_1 = B.id_ws_1) 
				 LEFT JOIN activity_link C on (C.id_predecessor = A.id_activity) 
				 LEFT JOIN work_status_level_2 D on (C.id_successor = D.id_activity) 
				 WHERE B.id_order = ".$row['id_order']." AND A.time_status = 1 AND A.work_status = 4 AND D.work_status <> 4";
		$result2 = $conn->query($sql2);
		
		
		while($row2 = mysql_fetch_assoc($result2)) {
			if($late == null){
				$late = $row2['late'];
			}			
			if($row2['late'] > $late){
				$late = $row2['late'];
				$id_late = $row2['id_activity'];
			} else if($row2['late'] == $late){
				$late = $row2['late'];
				$id_late = $id_late.' '.$row2['id_activity'];
			} 
		}
		$list[$i]['late_job'] = $late;
		$list[$i]['id_late'] = $id_late;
		$i++;
	}
	
	$conn->close();
	
	return $list;
}

function get_all_ws_level_2_1($id){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$id = mysql_real_escape_string($id);
	
	$sql = "SELECT A.*, B.activity, B.step FROM `".$config['db']['database']."`.`work_status_level_2` A 
			left join  `".$config['db']['database']."`.activity B on (A.id_activity = B.id_activity)
			left join  `".$config['db']['database']."`.work_status_level_1 C on (C.id_ws_1 = A.id_ws_1)
			WHERE C.id_order = ".$id."
			ORDER BY C.id_order ASC";
	
	$result = $conn->query($sql);
	
	$list = array();
	while($row = mysql_fetch_assoc($result)) {
		$list[] = $row;
	}
	
	$i = 0;
	while($i < count($list)){
		$sql1 = "SELECT id_predecessor from `".$config['db']['database']."`.activity_link 
				WHERE id_successor = '".$list[$i]['id_activity']."'";
		$result1 = $conn->query($sql1);
		//$list[$i]['pre'] = Array();
		while($row1 = mysql_fetch_assoc($result1)) {
			$list[$i]['pre'][] = $row1;
		}
		
		$sql2 = "SELECT id_successor from `".$config['db']['database']."`.activity_link 
				WHERE id_predecessor = '".$list[$i]['id_activity']."'";
		$result2 = $conn->query($sql2);
		//$list[$i]['suc'] = Array();
		while($row2 = mysql_fetch_assoc($result2)) {
			$list[$i]['suc'][] = $row2;
		}
		
		$i++;
	}
	
	$conn->close();
	
	return $list;
}

function get_job_perf(){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$sql2 = "SELECT * 
			FROM  `".$config['db']['database']."`.`activity` 
			WHERE  `level` = 2";
	$result2 = $conn->query($sql2);
	$list = array();
	$i = 0;
	while($row2 = mysql_fetch_assoc($result2)) {
		$list[] = $row2;
		$sql = "SELECT * FROM `".$config['db']['database']."`.`work_status_level_2`
				WHERE id_activity = '".$row2['id_activity']."'";
		
		$result = $conn->query($sql);
		$total = 0;
		$late = 0;
		$ontime = 0;
		while($row = mysql_fetch_assoc($result)) {
			if($row['end_date'] != "0000-00-00"){
				$total++;
				if($row['time_status'] == 1){
					$late++;
				} else if($row['time_status'] == 2){
					$ontime++;
				}
			}
		}
		$list[$i]['number_of_late_job'] = $late;
		$list[$i]['number_of_ontime_job'] = $ontime;
		if($total != 0){
			$list[$i]['calc'] = $late / $total;
		} else {
			$list[$i]['calc'] = null;
		}
		$list[$i]['total'] = $total;
		$i++;
	}
	
	$conn->close();
	
	return $list;
}

function get_job_perf_by_order($id){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$sql2 = "SELECT * 
			FROM  `".$config['db']['database']."`.`activity` 
			WHERE  `level` = 2";
	$result2 = $conn->query($sql2);
	$list = array();
	$i = 0;
	while($row2 = mysql_fetch_assoc($result2)) {
		$list[] = $row2;
		$sql = "SELECT A.* FROM `".$config['db']['database']."`.`work_status_level_2` A
				inner join work_status_level_1 B on (A.id_ws_1 = B.id_ws_1) 
				WHERE B.id_order = ".$id." AND A.id_activity = '".$row2['id_activity']."'";
		
		$result = $conn->query($sql);
		$total = 0;
		$late = 0;
		$ontime = 0;
		while($row = mysql_fetch_assoc($result)) {
			if($row['end_date'] != "0000-00-00"){
				$total++;
				if($row['time_status'] == 1){
					$late++;
				} else if($row['time_status'] == 2){
					$ontime++;
				}
			}
		}
		$list[$i]['number_of_late_job'] = $late;
		$list[$i]['number_of_ontime_job'] = $ontime;
		if($total != 0){
			$list[$i]['calc'] = $late / $total;
		} else {
			$list[$i]['calc'] = null;
		}
		$list[$i]['total'] = $total;
		$i++;
	}
	
	$conn->close();
	
	return $list;
}

function get_subcontract_perf(){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$sql = "SELECT *, (number_of_late_job + number_of_ontime_job) AS total, number_of_late_job / (number_of_late_job + number_of_ontime_job) AS calc FROM `".$config['db']['database']."`.`subcontract`
			ORDER BY calc ASC";
	
	$result = $conn->query($sql);
	
	$list = array();
	while($row = mysql_fetch_assoc($result)) {
		$list[] = $row;
	}
	
	$conn->close();
	
	return $list;
}

function get_tsupplier_perf(){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$sql = "SELECT *, (number_of_late_job + number_of_ontime_job) AS total, number_of_late_job / (number_of_late_job + number_of_ontime_job) AS calc FROM `".$config['db']['database']."`.`supplier`
			WHERE type = 'trim'
			ORDER BY calc ASC";
	
	$result = $conn->query($sql);
	
	$list = array();
	while($row = mysql_fetch_assoc($result)) {
		$list[] = $row;
	}
	
	$conn->close();
	
	return $list;
}

function get_subcontract_perf_by_order($id){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$sql = "SELECT B.*, A.subcontract_number, (B.number_of_late_job + B.number_of_ontime_job) AS total, B.number_of_late_job / (B.number_of_late_job + B.number_of_ontime_job) AS calc  FROM `".$config['db']['database']."`.`subcontract_order` A 
			left join `".$config['db']['database']."`.subcontract B on (A.id_subcontract = B.id_subcontract)
			left join  `".$config['db']['database']."`.sales_order C on (A.id_order = C.id_order)
			WHERE B.type = 'trim' AND C.id_order = ".$id;
	
	$result = $conn->query($sql);
	
	$list = array();
	while($row = mysql_fetch_assoc($result)) {
		$list[] = $row;
	}
	
	$conn->close();
	
	return $list;
}

function get_tsupplier_perf_by_order($id){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	$sql = "SELECT B.*, A.po_number, (B.number_of_late_job + B.number_of_ontime_job) AS total, B.number_of_late_job / (B.number_of_late_job + B.number_of_ontime_job) AS calc  FROM `".$config['db']['database']."`.`supplier_order` A 
			left join `".$config['db']['database']."`.supplier B on (A.id_supplier = B.id_supplier)
			left join  `".$config['db']['database']."`.sales_order C on (A.id_order = C.id_order)
			WHERE B.type = 'trim' AND C.id_order = ".$id;
	
	$result = $conn->query($sql);
	
	$list = array();
	while($row = mysql_fetch_assoc($result)) {
		$list[] = $row;
	}
	
	$conn->close();
	
	return $list;
}

function get_fsupplier_perf_by_order($id){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	$sql = "SELECT B.*, A.po_number, (B.number_of_late_job + B.number_of_ontime_job) AS total, B.number_of_late_job / (B.number_of_late_job + B.number_of_ontime_job) AS calc  FROM `".$config['db']['database']."`.`supplier_order` A 
			left join `".$config['db']['database']."`.supplier B on (A.id_supplier = B.id_supplier)
			left join  `".$config['db']['database']."`.sales_order C on (A.id_order = C.id_order)
			WHERE B.type = 'fabric' AND C.id_order = ".$id;
	
	$result = $conn->query($sql);
	
	$list = array();
	while($row = mysql_fetch_assoc($result)) {
		$list[] = $row;
	}
	
	$conn->close();
	
	return $list;
}

function get_fsupplier_perf(){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$sql = "SELECT *, (number_of_late_job + number_of_ontime_job) AS total, number_of_late_job / (number_of_late_job + number_of_ontime_job) AS calc FROM `".$config['db']['database']."`.`supplier`
			WHERE type = 'fabric'
			ORDER BY calc ASC";
	
	$result = $conn->query($sql);
	
	$list = array();
	while($row = mysql_fetch_assoc($result)) {
		$list[] = $row;
	}
	
	$conn->close();
	
	return $list;
}

function calculate_progress($id){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$id = mysql_real_escape_string($id);
	
	$sql = "SELECT A.work_status, B.target_start, B.target_end from work_status_level_2 A 
			inner join activity B on (A.id_activity = B.id_activity)
			where A.id_ws_1 = ".$id;
	
	$result = $conn->query($sql);
	
	$retval = 0;
	$sum = 0;
	while($row = mysql_fetch_assoc($result)) {
		if($row['work_status'] == 4) {
			$retval += (1 + $row['target_start'] - $row['target_end']);	
			$str .= '<br>start: '.$row['target_start'];
			$str .= '<br>end: '.$row['target_end'];
		}
		$sum += (1 + $row['target_start'] - $row['target_end']);
	}
	
	$conn->close();
	
	
	if($sum != 0) {
		$retval = ($retval * 100) / $sum;
	} else {
		$revtal = 0;
	}
	return round($retval, 2);
}

function check_authority($id){
	//echo '<br/>check_authority('.$id.')';
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$id = mysql_real_escape_string($id);
	
	$sql = "SELECT A.dept FROM `work_status_level_3` B 
			inner join activity A on (B.id_activity = A.id_activity)
			WHERE B.id_ws_3 = ".$id;
	
	$result = $conn->query($sql);
	$retval = $config['function']['return']['failure'];
	
	if($row = mysql_fetch_assoc($result)) {
		$dept =  strtolower(str_replace (' ', '', $row['dept']));
		$userdept = get_session('dept');
		if($dept == $userdept || get_session('dept') == 'admin'){
			$retval = $config['function']['return']['success'];
		}
	}
	
	$conn->close();
	
	return $retval;
}

function change_order_status($id, $status){
	//echo '<br/>change_order_status('.$id.','.$status.')';
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$id = mysql_real_escape_string($id);
	$status = mysql_real_escape_string($status);
	$retval = $config['function']['return']['failure'];
	
	if($row['work_status'] == 0 && $status == 1){
		$sql = "UPDATE `".$config['db']['database']."`.`sales_order` 
				SET `work_status` = ".$status.", 
					`order_date` = '".date ('Y-m-j')."'
				WHERE `id_order` = ".$id;
				
		if($conn->query($sql)) {
			$retval = $config['function']['return']['success'];
		}
	} else if($status == 4) {
		$sql = "UPDATE `".$config['db']['database']."`.`sales_order` 
				SET `work_status` = ".$status.",
					`end_date` = '".date ('Y-m-j')."'
				WHERE `id_order` = ".$id;
				
		if($conn->query($sql)) {
			$retval = $config['function']['return']['success'];
		}
	} else {
		$sql = "UPDATE `".$config['db']['database']."`.`sales_order` 
				SET `work_status` = ".$status." 
				WHERE `id_order` = ".$id;
				
		if($conn->query($sql)) {
			$retval = $config['function']['return']['success'];
		}
	}
	
	
	if($status == 3){
		//stop
	} else if($status == 4){
		//wait next
	}
	
	
	
	$conn->close();
	
	return $retval;
}

function change_ws_1($id, $status){
	//echo '<br/>change_ws_1('.$id.','.$status.')';
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$id = mysql_real_escape_string($id);
	$status = mysql_real_escape_string($status);
	$retval = $config['function']['return']['failure'];
	
	
	$sql = "SELECT * FROM `".$config['db']['database']."`.`work_status_level_1` 
			WHERE `id_ws_1` = ".$id;

	$result = $conn->query($sql);
	while($row = mysql_fetch_assoc($result)) {
		if($row['work_status'] == 0 && $status == 1){
			$sql = "UPDATE `".$config['db']['database']."`.`work_status_level_1` 
					SET `work_status` = ".$status.", 
						`start_date` = '".date ('Y-m-j')."'
					WHERE `id_ws_1` = ".$id;
					
			if($conn->query($sql)) {
				$retval = $config['function']['return']['success'];
			}
		} else if($status == 4) {
			$sql = "UPDATE `".$config['db']['database']."`.`work_status_level_1` 
					SET `work_status` = ".$status.",
						`end_date` = '".date ('Y-m-j')."'
					WHERE `id_ws_1` = ".$id;
					
			if($conn->query($sql)) {
				$retval = $config['function']['return']['success'];
			}
		} else {
			$sql = "UPDATE `".$config['db']['database']."`.`work_status_level_1` 
					SET `work_status` = ".$status." 
					WHERE `id_ws_1` = ".$id;
					
			if($conn->query($sql)) {
				$retval = $config['function']['return']['success'];
			}
		}
		if($status == 1){
			if(check_same_level_1($row['id_order'], $status)){
				change_order_status($row['id_order'], $status);
			}
		} else if($status == 2){
			if(check_same_level_1($row['id_order'], $status)){
				change_order_status($row['id_order'], $status);
			}
		} else if($status == 3){
			change_order_status($row['id_order'], $status);
		} else if($status == 4){
			change_next_ws_1($id, $row['id_order']);
		}
	}
	$conn->close();
	
	return $retval;
}

function change_ws_2($id, $status){
	//echo '<br/>change_ws_2('.$id.','.$status.')';
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$id = mysql_real_escape_string($id);
	$status = mysql_real_escape_string($status);
	$retval = $config['function']['return']['failure'];
	
	
	$sql = "SELECT * FROM `".$config['db']['database']."`.`work_status_level_2` 
			WHERE `id_ws_2` = ".$id;

	$result = $conn->query($sql);
	while($row = mysql_fetch_assoc($result)) {
		if($row['work_status'] == 0 && $status == 1){
			$sql = "UPDATE `".$config['db']['database']."`.`work_status_level_2` 
					SET `work_status` = ".$status.", 
						`start_date` = '".date ('Y-m-j')."'
					WHERE `id_ws_2` = ".$id;
					
			if($conn->query($sql)) {
				$retval = $config['function']['return']['success'];
			}
		} else if($status == 4) {
			$sql = "UPDATE `".$config['db']['database']."`.`work_status_level_2` 
					SET `work_status` = ".$status.",
						`end_date` = '".date ('Y-m-j')."'
					WHERE `id_ws_2` = ".$id;
					
			if($conn->query($sql)) {
				$retval = $config['function']['return']['success'];
			}
		} else {
			$sql = "UPDATE `".$config['db']['database']."`.`work_status_level_2` 
					SET `work_status` = ".$status." 
					WHERE `id_ws_2` = ".$id;
					
			if($conn->query($sql)) {
				$retval = $config['function']['return']['success'];
			}
		}
		if($status == 1){
			if(check_same_level_2($row['id_ws_1'], $status)){
				change_ws_1($row['id_ws_1'], $status);
			}
		} else if($status == 2){
			if(check_same_level_2($row['id_ws_1'], $status)){
				change_ws_1($row['id_ws_1'], $status);
			}
		} else if($status == 3){
			change_ws_1($row['id_ws_1'], $status);
		} else if($status == 4){
			change_next_ws_2($id, $row['id_ws_1']);
		}
	}
	
	$conn->close();
	
	return $retval;
}

function change_ws($id, $status, $comment){
	$retval = $config['function']['return']['failure'];
	if(check_authority($id)){
		$retval = change_ws_3($id, $status, $comment);
	}
	return $retval;
}

function change_ws_3($id, $status, $comment){
	//echo '<br/>change_ws_3('.$id.','.$status.')';
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$id = mysql_real_escape_string($id);
	$status = mysql_real_escape_string($status);
	$comment = mysql_real_escape_string($comment);
	$retval = $config['function']['return']['failure'];
	

	$sql = "SELECT * FROM `".$config['db']['database']."`.`work_status_level_3` 
			WHERE `id_ws_3` = ".$id;

	$result = $conn->query($sql);
	while($row = mysql_fetch_assoc($result)) {
		if($comment != null){
			if($row['work_status'] == 0 && $status == 1){
				$sql = "UPDATE `".$config['db']['database']."`.`work_status_level_3` 
						SET `work_status` = ".$status.",
							`comment` = '".$comment."',
							`start_date` = '".date ('Y-m-j')."'
						WHERE `id_ws_3` = ".$id;
				if($conn->query($sql)) {
					$retval = $config['function']['return']['success'];
				}
			} else if($status == 4) {
				$sql = "UPDATE `".$config['db']['database']."`.`work_status_level_3` 
						SET `work_status` = ".$status.",
							`comment` = '".$comment."',
							`end_date` = '".date ('Y-m-j')."'
						WHERE `id_ws_3` = ".$id;
						
				if($conn->query($sql)) {
					$retval = $config['function']['return']['success'];
				}
			} else {
				$sql = "UPDATE `".$config['db']['database']."`.`work_status_level_3` 
						SET `work_status` = ".$status.", 
							`comment` = '".$comment."'
						WHERE `id_ws_3` = ".$id;
						
				if($conn->query($sql)) {
					$retval = $config['function']['return']['success'];
				}
			}
		} else {
			if($row['work_status'] == 0 && $status == 1){
				$sql = "UPDATE `".$config['db']['database']."`.`work_status_level_3` 
						SET `work_status` = ".$status.", 
							`start_date` = '".date ('Y-m-j')."'
						WHERE `id_ws_3` = ".$id;
				if($conn->query($sql)) {
					$retval = $config['function']['return']['success'];
				}
			} else if($status == 4) {
				$sql = "UPDATE `".$config['db']['database']."`.`work_status_level_3` 
						SET `work_status` = ".$status.",
							`end_date` = '".date ('Y-m-j')."'
						WHERE `id_ws_3` = ".$id;
						
				if($conn->query($sql)) {
					$retval = $config['function']['return']['success'];
				}
			} else {
				$sql = "UPDATE `".$config['db']['database']."`.`work_status_level_3` 
						SET `work_status` = ".$status." 
						WHERE `id_ws_3` = ".$id;
						
				if($conn->query($sql)) {
					$retval = $config['function']['return']['success'];
				}
			}
		}
		
		if($status == 1){
			if(check_same_level_3($row['id_ws_2'], $status)){
				change_ws_2($row['id_ws_2'], $status);
			}
		} else if($status == 2){
			if(check_same_level_3($row['id_ws_2'], $status)){
				change_ws_2($row['id_ws_2'], $status);
			}
		} else if($status == 3){
			change_ws_2($row['id_ws_2'], $status);
		} else if($status == 4){
			change_next_ws_3($id, $row['id_ws_2']);
		}
	}
	$conn->close();
	
	return $retval;
}

function check_same_level_1($id, $status){
	//echo '<br/>check_same_level_1('.$id.','.$status.')';
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	
	$sql = "SELECT * FROM `".$config['db']['database']."`.`work_status_level_1` 
			WHERE `id_order` = ".$id;
	$result = $conn->query($sql);
	$retval = true;
	while($row = mysql_fetch_assoc($result)) {
		if($status == 4){
			//cek jika ada status yang tidak bernilai finish (4)
			if($row['work_status'] != $status){
				$retval = false;
			}
		} else {
			//cek jika ada prioritas yang tidak memperbolehkan waiting, finish tidak dihitung
			if($row['work_status'] > $status && $row['work_status'] != 4){
				$retval = false;
			}
		}
	}
	$conn->close();
	
	return $retval;
}

function check_same_level_2($id, $status){
	//echo '<br/>check_same_level_2('.$id.','.$status.')';
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	
	$sql = "SELECT * FROM `".$config['db']['database']."`.`work_status_level_2` 
			WHERE `id_ws_1` = ".$id;
	
	$result = $conn->query($sql);
	$retval = true;
	while($row = mysql_fetch_assoc($result)) {
		if($status == 4){
			//cek jika ada status yang tidak bernilai finish (4)
			if($row['work_status'] != $status){
				$retval = false;
			}
		} else {
			//cek jika ada prioritas yang tidak memperbolehkan waiting, finish tidak dihitung
			if($row['work_status'] > $status && $row['work_status'] != 4){
				$retval = false;
			}
		}
	}
	$conn->close();
	
	return $retval;
}

function check_same_level_3($id, $status){
	//echo '<br/>check_same_level_3('.$id.','.$status.')';
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	
	$sql = "SELECT * FROM `".$config['db']['database']."`.`work_status_level_3` 
			WHERE `id_ws_2` = ".$id;
	$result = $conn->query($sql);
	$retval = true;
	while($row = mysql_fetch_assoc($result)) {
		if($status == 4){
			//cek jika ada status yang tidak bernilai finish (4)
			if($row['work_status'] != $status){
				$retval = false;
			}
		} else {
			//cek jika ada prioritas yang tidak memperbolehkan waiting, finish tidak dihitung
			if($row['work_status'] > $status && $row['work_status'] != 4){
				$retval = false;
			}
		}
	}
	$conn->close();
	
	return $retval;
}

function change_next_ws_1($id, $root){
	//echo '<br/>change_next_ws_1('.$id.')';
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$id = mysql_real_escape_string($id);
	$status = mysql_real_escape_string($status);
	$retval = $config['function']['return']['failure'];
	
	$sql2 = "SELECT A.id_ws_1, C.id_order, B.id_successor, B.id_predecessor  FROM work_status_level_1 A 
			inner join activity_link B on (B.id_successor = A.id_activity)
			inner join work_status_level_1 C on (B.id_predecessor = C.id_activity)
			where A.id_order = C.id_order AND C.id_ws_1 = ".$id;
	$result2 = $conn->query($sql2);
	
	if(mysql_num_rows($result2) == 0){
		if(check_same_level_1($root, 4)){
			change_order_status($root, 4);
		}
	}
	
	while($row = mysql_fetch_assoc($result2)) {
		if(pre_check_1($row['id_ws_1'])){
			change_ws_1($row['id_ws_1'], 2);
		}
		if(substr($row['id_successor'], 0, 5) != substr($row['id_predecessor'], 0, 5)){
			if(check_same_level_1($row['id_order'], 4)){
				change_order_status($row['id_order'], 4);
			}
		}
	}
	$conn->close();
	
	return $retval;
}

function change_next_ws_2($id, $root){
	//echo '<br/>change_next_ws_2('.$id.')';
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$id = mysql_real_escape_string($id);
	$retval = $config['function']['return']['failure'];
	
	$sql2 = "SELECT A.id_ws_2, C.id_ws_1, B.id_successor, B.id_predecessor  FROM work_status_level_2 A 
			inner join activity_link B on (B.id_successor = A.id_activity)
			inner join work_status_level_2 C on (B.id_predecessor = C.id_activity)
			inner join work_status_level_1 D on (D.id_ws_1 = A.id_ws_1)
			inner join work_status_level_1 E on (E.id_ws_1 = C.id_ws_1)
			where D.id_order = E.id_order AND C.id_ws_2 = ".$id;
	
	$result2 = $conn->query($sql2);
	
	if(mysql_num_rows($result2) == 0){
		if(check_same_level_2($root, 4)){
			change_ws_1($root, 4);
		}
	}
	
	while($row = mysql_fetch_assoc($result2)) {
		if(pre_check_2($row['id_ws_2'])){
			change_ws_2($row['id_ws_2'], 2);
		}
		if(substr($row['id_successor'], 0, 5) != substr($row['id_predecessor'], 0, 5)){
			if(check_same_level_2($row['id_ws_1'], 4)){
				change_ws_1($row['id_ws_1'], 4);
			}
		}
	}
	$conn->close();
	
	return $retval;
}

function change_next_ws_3($id, $root){
	//echo '<br/>change_next_ws_3('.$id.','.$root.')';
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$id = mysql_real_escape_string($id);
	$retval = $config['function']['return']['failure'];
	
	$sql2 = "SELECT A.id_ws_3, C.id_ws_2, B.id_successor, B.id_predecessor  FROM work_status_level_3 A 
			inner join activity_link B on (B.id_successor = A.id_activity)
			inner join work_status_level_3 C on (B.id_predecessor = C.id_activity)
			inner join work_status_level_2 D on (D.id_ws_2 = A.id_ws_2)
			inner join work_status_level_2 E on (E.id_ws_2 = C.id_ws_2)
			where D.id_ws_1 = E.id_ws_1 AND C.id_ws_3 = ".$id;
	$result2 = $conn->query($sql2);
	
	
	if(mysql_num_rows($result2) == 0){
		if(check_same_level_3($root, 4)){
			change_ws_2($root, 4);
		}
	}
	
	while($row = mysql_fetch_assoc($result2)) {
		//cek semua predecessor setelah id_ws_3 terpilih yang akan diubah jadi waiting
		if(pre_check_3($row['id_ws_3'])){
			change_ws_3($row['id_ws_3'], 2, null);
		}
		//
		if(substr($row['id_successor'], 0, 5) != substr($row['id_predecessor'], 0, 5)){
			if(check_same_level_3($row['id_ws_2'], 4)){
				change_ws_2($row['id_ws_2'], 4);
			}
		}
	}
	
	$conn->close();
	
	return $retval;
}

function pre_check_1($id){
	//echo '<br/>pre_check_1('.$id.')';
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$sql2 = "SELECT A.id_ws_1, A.work_status, C.id_order, B.id_successor, B.id_predecessor FROM work_status_level_1 A 
		inner join activity_link B on (B.id_predecessor = A.id_activity)
		inner join work_status_level_1 C on (B.id_successor = C.id_activity)
		where A.id_order = C.id_order AND C.id_ws_1 = ".$id;
	
	$result2 = $conn->query($sql2);
	$retval = true;
	while($row = mysql_fetch_assoc($result2)) {
		if($row['work_status'] < 4){
			$retval = false;
		}
	}
	$conn->close();
	
	return $retval;
}

function pre_check_2($id){
	//echo '<br/>pre_check_2('.$id.')';
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$sql2 = "SELECT A.id_ws_2, A.work_status, C.id_ws_1, B.id_successor, B.id_predecessor FROM work_status_level_2 A 
		inner join activity_link B on (B.id_predecessor = A.id_activity)
		inner join work_status_level_2 C on (B.id_successor = C.id_activity)
		inner join work_status_level_1 D on (D.id_ws_1 = A.id_ws_1)
		inner join work_status_level_1 E on (E.id_ws_1 = C.id_ws_1)
		where D.id_order = E.id_order AND C.id_ws_2 = ".$id;
	
	$result2 = $conn->query($sql2);
	$retval = true;
	while($row = mysql_fetch_assoc($result2)) {
		if($row['work_status'] < 4){
			$retval = false;
		}
	}
	$conn->close();
	
	return $retval;
}

function pre_check_3($id){
	//echo '<br/>pre_check_3('.$id.')';
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$sql2 = "SELECT A.id_ws_3, A.work_status, C.id_ws_2, B.id_successor, B.id_predecessor FROM work_status_level_3 A 
		inner join activity_link B on (B.id_predecessor = A.id_activity)
		inner join work_status_level_3 C on (B.id_successor = C.id_activity)
		inner join work_status_level_2 D on (D.id_ws_2 = A.id_ws_2)
		inner join work_status_level_2 E on (E.id_ws_2 = C.id_ws_2)
		where D.id_ws_1 = E.id_ws_1 AND C.id_ws_3 = ".$id;
	
	$result2 = $conn->query($sql2);
	$retval = true;
	while($row = mysql_fetch_assoc($result2)) {
		if($row['work_status'] < 4){
			$retval = false;
		}
	}
	$conn->close();
	
	return $retval;
}

function get_activity_level_2(){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	
	$sql = "SELECT * FROM `".$config['db']['database']."`.`activity`
			WHERE level = 2 
			ORDER BY id_activity ASC";

	$result = $conn->query($sql);
	
	$list = array();
	
	while($row = mysql_fetch_assoc($result)) {
		$list[] = $row;
	}
	$i = 0;
	while($i < count($list)){
		$sql1 = "SELECT id_predecessor from `".$config['db']['database']."`.activity_link 
				WHERE id_successor = '".$list[$i]['id_activity']."'";
		$result1 = $conn->query($sql1);
		//$list[$i]['pre'] = Array();
		while($row1 = mysql_fetch_assoc($result1)) {
			$list[$i]['pre'][] = $row1;
		}
		
		$i++;
	}
	
	$conn->close();
	
	return $list;
}

function get_all_ws_level_3($id){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$id = mysql_real_escape_string($id);
	
	$sql = "SELECT A.*, B.activity FROM `".$config['db']['database']."`.`work_status_level_3` A 
			left join  `".$config['db']['database']."`.activity B on (A.id_activity = B.id_activity)
			left join  `".$config['db']['database']."`.work_status_level_2 C on (C.id_ws_2 = A.id_ws_2)
			WHERE C.id_ws_1 = ".$id."
			ORDER BY B.activity ASC";

	$result = $conn->query($sql);
	
	$list = array();

	
	while($row = mysql_fetch_assoc($result)) {
		$list[] = $row;
	}
	$i = 0;
	while($i < count($list)){
		$sql1 = "SELECT id_predecessor from `".$config['db']['database']."`.activity_link 
				WHERE id_successor = '".$list[$i]['id_activity']."'";
		$result1 = $conn->query($sql1);
		//$list[$i]['pre'] = Array();
		while($row1 = mysql_fetch_assoc($result1)) {
			$list[$i]['pre'][] = $row1;
		}
		
		$sql2 = "SELECT id_successor from `".$config['db']['database']."`.activity_link 
				WHERE id_predecessor = '".$list[$i]['id_activity']."'";
		$result2 = $conn->query($sql2);
		//$list[$i]['suc'] = Array();
		while($row2 = mysql_fetch_assoc($result2)) {
			$list[$i]['suc'][] = $row2;
		}
		
		$i++;
	}
	
	$conn->close();
	
	return $list;
}

function get_all_ws_level_2($id){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$id = mysql_real_escape_string($id);
	
	$sql = "SELECT A.*, B.activity FROM `".$config['db']['database']."`.`work_status_level_2` A 
			left join  `".$config['db']['database']."`.activity B on (A.id_activity = B.id_activity)
			WHERE A.id_ws_1 = ".$id;

	$result = $conn->query($sql);
	
	$list = array();
	while($row = mysql_fetch_assoc($result)) {
		$list[] = $row;
	}
	
	$conn->close();
	
	return $list;
}

function get_all_ws_level_1($id){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$id = mysql_real_escape_string($id);
	
	$sql = "SELECT A.*, B.activity FROM `".$config['db']['database']."`.`work_status_level_1` A 
			left join  `".$config['db']['database']."`.activity B on (A.id_activity = B.id_activity)
			WHERE A.id_order = ".$id."
			ORDER BY B.id_activity ASC";

	$result = $conn->query($sql);
	
	$list = array();
	while($row = mysql_fetch_assoc($result)) {
		$list[] = $row;
	}
	
	$conn->close();
	
	return $list;
}

function get_order($id){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$id = mysql_real_escape_string($id);
	
	$sql = "SELECT * FROM `".$config['db']['database']."`.`sales_order` 
			WHERE id_order='".$id."'";

	$result = $conn->query($sql);
	
	return mysql_fetch_assoc($result);
}

function get_work($id){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$id = mysql_real_escape_string($id);
	
	$sql = "SELECT * FROM `".$config['db']['database']."`.`work_order` 
			WHERE id_work_order='".$id."'";

	$result = $conn->query($sql);
	
	return mysql_fetch_assoc($result);
}

function get_activity($id){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$id = mysql_real_escape_string($id);
	
	$sql = "SELECT * FROM `".$config['db']['database']."`.`activity` 
			WHERE id_activity='".$id."'";

	$result = $conn->query($sql);
	
	return mysql_fetch_assoc($result);
}

function edit_target($id, $start, $end){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$id = mysql_real_escape_string($id);
	$start = mysql_real_escape_string($start);
	$end = mysql_real_escape_string($end);
	
	
	$sql = "UPDATE `".$config['db']['database']."`.`activity` 
			SET
				`target_start` = ".$start.",
				`target_end` = ".$end."
			WHERE `id_activity` = ".$id;
			
	
	$retval = $config['function']['return']['failure'];
	if($conn->query($sql)) {
		$retval = $config['function']['return']['success'];
	}
	
	$conn->close();
	
	return $retval;
}

function edit_work($id, $order, $dest, $quantity, $cutting, $sewing, $delivery){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$id = mysql_real_escape_string($id);
	$order = mysql_real_escape_string($order);
	$dest = mysql_real_escape_string($dest);
	$quantity = mysql_real_escape_string($quantity);
	$cutting = mysql_real_escape_string($cutting);
	$sewing = mysql_real_escape_string($sewing);
	$delivery = mysql_real_escape_string($delivery);
	
	
	$sql = "UPDATE `".$config['db']['database']."`.`work_order` 
			SET
				`id_order` = ".$order.",
				`destination` = '".$dest."',
				`quantity` = ".$quantity.",
				`cutting_date` = '".$cutting."',
				`sewing_date` = '".$sewing."',
				`delivery_date` = '".$delivery."'
			WHERE `id_work_order` = '".$id."'";
			
	
	$retval = $config['function']['return']['failure'];
	if($conn->query($sql)) {
		if($cutting != '0000-00-00' && $sewing != '0000-00-00'){
			$sql = "SELECT A.* FROM `work_status_level_2` A 
					inner join `work_status_level_1` B on (A.id_ws_1 = B.id_ws_1)
					WHERE A.id_activity like '05%' AND B.id_work_order = '".$id."'";
			
			
			
			$result = $conn->query($sql);
			while($row = mysql_fetch_assoc($result)) {
				if($row['id_activity'] == '05.01'){
					$sql = "UPDATE `".$config['db']['database']."`.`work_status_level_2` 
							SET
								`start_date_plan` = '".$cutting."',
								`end_date_plan` = '".$sewing."'
							WHERE `id_ws_2` = '".$row['id_ws_2']."'";
					
					if($conn->query($sql)) {
						$retval = $config['function']['return']['success'];
					}
				} else if($row['id_activity'] == '05.02'){
					$sql = "UPDATE `".$config['db']['database']."`.`work_status_level_2` 
							SET
								`start_date_plan` = '".$sewing."',
								`end_date_plan` = DATE_SUB('".$delivery."', INTERVAL 1 DAY)
							WHERE `id_ws_2` = '".$row['id_ws_2']."'";
					
					if($conn->query($sql)) {
						$retval = $config['function']['return']['success'];
					}
				} else if($row['id_activity'] == '05.03'){
					$sql = "UPDATE `".$config['db']['database']."`.`work_status_level_2` 
							SET
								`start_date_plan` = DATE_SUB('".$delivery."', INTERVAL 1 DAY),
								`end_date_plan` = '".$delivery."'
							WHERE `id_ws_2` = '".$row['id_ws_2']."'";
					if($conn->query($sql)) {
						$retval = $config['function']['return']['success'];
					}
				} 
			}
		}
	}
	
	$conn->close();
	
	return $retval;
}

function edit_supplier_status($id, $status, $delivery, $ponumber){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$id = mysql_real_escape_string($id);
	$status = mysql_real_escape_string($status);
	$delivery = mysql_real_escape_string($delivery);
	$ponumber = mysql_real_escape_string($ponumber);
	
	if($row['work_status'] == 0 && $status == 1){
		$sql = "UPDATE `".$config['db']['database']."`.`supplier_order` 
			SET
				`work_status` = ".$status.",
				`delivery_date` = '".$delivery."',
				`start_date` = '".date ('Y-m-j')."',
				`po_number` = ".$ponumber."
			WHERE `id_supplier_order` = '".$id."'";
	} else if($status == 4) {
		$sql = "UPDATE `".$config['db']['database']."`.`supplier_order` 
			SET
				`work_status` = ".$status.",
				`delivery_date` = '".$delivery."',
				`end_date` = '".date ('Y-m-j')."',
				`po_number` = ".$ponumber."
			WHERE `id_supplier_order` = '".$id."'";
	} else {
		$sql = "UPDATE `".$config['db']['database']."`.`supplier_order` 
			SET
				`work_status` = ".$status.",
				`delivery_date` = '".$delivery."',
				`po_number` = ".$ponumber."
			WHERE `id_supplier_order` = '".$id."'";
	}
	
	$retval = $config['function']['return']['failure'];
	if($conn->query($sql)) {
		$retval = $config['function']['return']['success'];
	}
	
	$conn->close();
	
	return $retval;
}

function edit_subcontract_status($id, $status, $delivery, $number){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$id = mysql_real_escape_string($id);
	$status = mysql_real_escape_string($status);
	$delivery = mysql_real_escape_string($delivery);
	$number = mysql_real_escape_string($number);
	
	if($row['work_status'] == 0 && $status == 1){
		$sql = "UPDATE `".$config['db']['database']."`.`subcontract_order` 
			SET
				`work_status` = ".$status.",
				`delivery_date` = '".$delivery."',
				`start_date` = '".date ('Y-m-j')."',
				`subcontract_number` = ".$number."
			WHERE `id_subcontract_order` = '".$id."'";
	} else if($status == 4) {
		$sql = "UPDATE `".$config['db']['database']."`.`subcontract_order` 
			SET
				`work_status` = ".$status.",
				`delivery_date` = '".$delivery."',
				`end_date` = '".date ('Y-m-j')."',
				`subcontract_number` = ".$number."
			WHERE `id_subcontract_order` = '".$id."'";
	} else {
		$sql = "UPDATE `".$config['db']['database']."`.`subcontract_order` 
			SET
				`work_status` = ".$status.",
				`delivery_date` = '".$delivery."',
				`subcontract_number` = ".$number."
			WHERE `id_subcontract_order` = '".$id."'";
	}
	
	
	$retval = $config['function']['return']['failure'];
	if($conn->query($sql)) {
		$retval = $config['function']['return']['success'];
	}
	
	$conn->close();
	
	return $retval;
}

function edit_order($id, $style, $buyer, $season, $product, $quantity, $order, $confirm, $delivery){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$id = mysql_real_escape_string($id);
	$style = mysql_real_escape_string($style);
	$buyer = mysql_real_escape_string($buyer);
	$season = mysql_real_escape_string($season);
	$product = mysql_real_escape_string($product);
	$quantity = mysql_real_escape_string($quantity);
	$order = mysql_real_escape_string($order);
	$confirm = mysql_real_escape_string($confirm);
	$delivery = mysql_real_escape_string($delivery);
	
	
	$sql = "UPDATE `".$config['db']['database']."`.`sales_order` 
			SET
				`style` = ".$style.",
				`buyer` = '".$buyer."',
				`season` = '".$season."',
				`product` = '".$product."',
				`quantity` = ".$quantity.",
				`order_date` = '".$order."',
				`confirm_date` = '".$confirm."',
				`delivery_date` = '".$delivery."'
			WHERE `id_order` = '".$id."'";
			
	
	$retval = $config['function']['return']['failure'];
	if($conn->query($sql)) {
		$sql1 = "SELECT A.*, C.* FROM  `".$config['db']['database']."`.`work_status_level_2` A
				inner join `".$config['db']['database']."`.`work_status_level_1` B on (A.id_ws_1 = B.id_ws_1)
				inner join `".$config['db']['database']."`.`activity` C on (A.id_activity = C.id_activity)
				WHERE B.`id_order` = '".$id."' AND A.work_status = 0";
		
				
		$result1 = $conn->query($sql1);
				
		while($row1 = mysql_fetch_assoc($result1)) {
			$newdate1 = strtotime ( '-'.$row1['target_start'].' day' , strtotime ( $delivery ) ) ;
			$newdate1 = date ( 'Y-m-j' , $newdate1 );
			$newdate2 = strtotime ( '-'.$row1['target_end'].' day' , strtotime ( $delivery ) ) ;
			$newdate2 = date ( 'Y-m-j' , $newdate2 );
			
			$sql = "UPDATE `".$config['db']['database']."`.`work_status_level_2` 
			SET
				`start_date_plan` = '".$newdate1."',
				`end_date_plan` = '".$newdate2."'
			WHERE `id_ws_2` = '".$row1['id_ws_2']."'";
			
			if($conn->query($sql)) {
				$retval = $config['function']['return']['success'];
			}
		}
	}
	
	$conn->close();
	
	return $retval;
}

function add_work($order, $dest, $quantity, $cutting, $sewing, $delivery){
	global $config;
	
	$conn = new_connection();
	$conn->open();

	$order = mysql_real_escape_string($order);
	$dest = mysql_real_escape_string($dest);
	$quantity = mysql_real_escape_string($quantity);
	$cutting = mysql_real_escape_string($cutting);
	$sewing = mysql_real_escape_string($sewing);
	$delivery = mysql_real_escape_string($delivery);
	$sqlfunc = "DROP FUNCTION IF EXISTS SPLIT_STR;
			CREATE FUNCTION SPLIT_STR(
			  x VARCHAR(255),
			  delim VARCHAR(12),
			  pos INT
			)
			RETURNS VARCHAR(255)
			RETURN REPLACE(SUBSTRING(SUBSTRING_INDEX(x, delim, pos),
				   LENGTH(SUBSTRING_INDEX(x, delim, pos -1)) + 1),
				   delim, '')";
	$conn->query($sqlfunc);
	$sql = "INSERT INTO `".$config['db']['database']."`.`work_order` (
				`id_order`,
				`destination`,
				`quantity`,
				`cutting_date`,
				`sewing_date`,
				`delivery_date`)
			VALUES (
				".$order.",
				'".$dest."',
				".$quantity.",
				'".$cutting."',
				'".$sewing."',
				'".$delivery."')";
	
	$retval = $config['function']['return']['failure'];
	if($conn->query($sql)) {
		$id_work = $conn->get_last_insert_id();
		
		//level 1
		$sql1 = "SELECT * 
				FROM  `".$config['db']['database']."`.`activity` 
				WHERE  `level` = 1 AND `type` = 2";
		
		$result1 = $conn->query($sql1);
		
		while($row1 = mysql_fetch_assoc($result1)) {
			$sql11 = "INSERT INTO `".$config['db']['database']."`.`work_status_level_1` (
						`id_activity`,
						`id_work_order`,
						`id_order`,
						`type`)
					VALUES (
						'".$row1['id_activity']."',
						".$id_work.",
						".$order.",
						2)";
			
			if($conn->query($sql11)){
				$id_ws_1 = $conn->get_last_insert_id();
				//level 2
				$sql2 = "SELECT * 
						FROM  `".$config['db']['database']."`.`activity` 
						WHERE  `level` = 2 AND SPLIT_STR(`id_activity`, '.', 1) = '".$row1['id_activity']."'";
				$result2 = $conn->query($sql2);
				
				while($row2 = mysql_fetch_assoc($result2)) {
					$newdate1 = strtotime ( '-'.$row2['target_start'].' day' , strtotime ( $delivery ) ) ;
					$newdate1 = date ( 'Y-m-j' , $newdate1 );
					$newdate2 = strtotime ( '-'.$row2['target_end'].' day' , strtotime ( $delivery ) ) ;
					$newdate2 = date ( 'Y-m-j' , $newdate2 );
					
					$sql21 = "INSERT INTO `".$config['db']['database']."`.`work_status_level_2` (
								`id_activity`,
								`id_ws_1`,
								`start_date_plan`,
								`end_date_plan`,
								`type`)
							VALUES (
								'".$row2['id_activity']."',
								".$id_ws_1.",
								'".$newdate1."',
								'".$newdate2."',
								2)";
					if($conn->query($sql21)){
						$id_ws_2 = $conn->get_last_insert_id();
						//level 3
						$sql3 = "SELECT * 
								FROM  `".$config['db']['database']."`.`activity` 
								WHERE  `level` = 3 AND SPLIT_STR(`id_activity`, '.', 1) = '".$row1['id_activity']."'
								AND SPLIT_STR(`id_activity`, '.', 2) = SPLIT_STR('".$row2['id_activity']."', '.', 2)";
						
						$result3 = $conn->query($sql3);
						
						while($row3 = mysql_fetch_assoc($result3)) {
							$sql31 = "INSERT INTO `".$config['db']['database']."`.`work_status_level_3` (
										`id_activity`,
										`id_ws_2`,
										`type`)
									VALUES (
										'".$row3['id_activity']."',
										".$id_ws_2.",
										2)";
							if($conn->query($sql31)){
								$retval = $config['function']['return']['success'];
							}
						}
					}
				}
			}
		}
	}
	
	$conn->close();
	
	return $retval;
}

function dateDiff($start, $end) {
	$start_ts = strtotime($start);
	$end_ts = strtotime($end);
	$diff = $end_ts - $start_ts;
	return round($diff / 86400);
}

function add_order($style, $buyer, $season, $product, $quantity, $order, $confirm, $delivery){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$style = mysql_real_escape_string($style);
	$buyer = mysql_real_escape_string($buyer);
	$season = mysql_real_escape_string($season);
	$product = mysql_real_escape_string($product);
	$quantity = mysql_real_escape_string($quantity);
	$order = mysql_real_escape_string($order);
	$confirm = mysql_real_escape_string($confirm);
	$delivery = mysql_real_escape_string($delivery);
	$sqlfunc = "DROP FUNCTION IF EXISTS SPLIT_STR;
			CREATE FUNCTION SPLIT_STR(
			  x VARCHAR(255),
			  delim VARCHAR(12),
			  pos INT
			)
			RETURNS VARCHAR(255)
			RETURN REPLACE(SUBSTRING(SUBSTRING_INDEX(x, delim, pos),
				   LENGTH(SUBSTRING_INDEX(x, delim, pos -1)) + 1),
				   delim, '')";
	$conn->query($sqlfunc);
	$sql = "INSERT INTO `".$config['db']['database']."`.`sales_order` (
				`style`,
				`buyer`,
				`season`,
				`product`,
				`quantity`,
				`order_date`,
				`confirm_date`,
				`delivery_date`)
			VALUES (
				".$style.",
				'".$buyer."',
				'".$season."',
				'".$product."',
				".$quantity.",
				'".$order."',
				'".$confirm."',
				'".$delivery."')";
	
	$retval = $config['function']['return']['failure'];
	if(dateDiff(date("F j, Y"), $delivery) <= 180){
		if($conn->query($sql)) {
			$id_order = $conn->get_last_insert_id();
			
			//level 1
			$sql1 = "SELECT * 
					FROM  `".$config['db']['database']."`.`activity` 
					WHERE  `level` = 1 AND `type` = 1";
			
			$result1 = $conn->query($sql1);
			
			while($row1 = mysql_fetch_assoc($result1)) {
				$sql11 = "INSERT INTO `".$config['db']['database']."`.`work_status_level_1` (
							`id_activity`,
							`id_order`,
							`type`)
						VALUES (
							'".$row1['id_activity']."',
							".$id_order.",
							1)";
				
				if($conn->query($sql11)){
					$id_ws_1 = $conn->get_last_insert_id();
					//level 2
					$sql2 = "SELECT * 
							FROM  `".$config['db']['database']."`.`activity` 
							WHERE  `level` = 2 AND SPLIT_STR(`id_activity`, '.', 1) = '".$row1['id_activity']."'";
					$result2 = $conn->query($sql2);
					
					while($row2 = mysql_fetch_assoc($result2)) {
						$newdate1 = strtotime ( '-'.$row2['target_start'].' day' , strtotime ( $delivery ) ) ;
						$newdate1 = date ( 'Y-m-j' , $newdate1 );
						$newdate2 = strtotime ( '-'.$row2['target_end'].' day' , strtotime ( $delivery ) ) ;
						$newdate2 = date ( 'Y-m-j' , $newdate2 );
						
						$sql21 = "INSERT INTO `".$config['db']['database']."`.`work_status_level_2` (
									`id_activity`,
									`id_ws_1`,
									`start_date_plan`,
									`end_date_plan`,
									`type`)
								VALUES (
									'".$row2['id_activity']."',
									".$id_ws_1.",
									'".$newdate1."',
									'".$newdate2."',
									1)";
						if($conn->query($sql21)){
							$id_ws_2 = $conn->get_last_insert_id();
							//level 3
							$sql3 = "SELECT * 
									FROM  `".$config['db']['database']."`.`activity` 
									WHERE  `level` = 3 AND SPLIT_STR(`id_activity`, '.', 1) = '".$row1['id_activity']."'
									AND SPLIT_STR(`id_activity`, '.', 2) = SPLIT_STR('".$row2['id_activity']."', '.', 2)";
							
							$result3 = $conn->query($sql3);
							
							while($row3 = mysql_fetch_assoc($result3)) {
								$sql31 = "INSERT INTO `".$config['db']['database']."`.`work_status_level_3` (
											`id_activity`,
											`id_ws_2`,
											`type`)
										VALUES (
											'".$row3['id_activity']."',
											".$id_ws_2.",
											1)";
								if($conn->query($sql31)){
									$retval = $config['function']['return']['success'];
								}
							}
						}
					}
				}
			}
		}
	}
	
	$conn->close();
	
	return $retval;
}

function delete_work($id){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$id = mysql_real_escape_string($id);
	
	$sql = "DELETE FROM `".$config['db']['database']."`.`work_order`
			WHERE `id_work_order` = '".$id."'";
	$retval = $config['function']['return']['failure'];
	if($conn->query($sql)) {		
		$sql1 = "SELECT * FROM `".$config['db']['database']."`.`work_status_level_1`
			WHERE `id_work_order` = '".$id."'";
		$result1 = $conn->query($sql1);		
		while($row1 = mysql_fetch_assoc($result1)) {
			$sql2 = "SELECT * FROM `".$config['db']['database']."`.`work_status_level_2`
				WHERE `id_ws_1` = '".$row1['id_ws_1']."'";
			$result2 = $conn->query($sql2);	
			while($row2 = mysql_fetch_assoc($result2)) {
				$sql3 = "DELETE FROM `".$config['db']['database']."`.`work_status_level_3`
						WHERE `id_ws_2` = '".$row2['id_ws_2']."'";
				$retval = $config['function']['return']['failure'];
				if($conn->query($sql3)) {
					$retval = $config['function']['return']['success'];
				}
			}
			$sql4 = "DELETE FROM `".$config['db']['database']."`.`work_status_level_2`
					WHERE `id_ws_1` = '".$row1['id_ws_1']."'";
			$retval = $config['function']['return']['failure'];
			if($conn->query($sql4)) {
				$retval = $config['function']['return']['success'];
			}
		}
		$sql5 = "DELETE FROM `".$config['db']['database']."`.`work_status_level_1`
				WHERE `id_work_order` = '".$id."'";
		$retval = $config['function']['return']['failure'];
		if($conn->query($sql5)) {
			$retval = $config['function']['return']['success'];
		}
	}
	
	$conn->close();
	
	return $retval;
}

function delete_order($id){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$id = mysql_real_escape_string($id);
	
	$sql = "DELETE FROM `".$config['db']['database']."`.`sales_order`
			WHERE `id_order` = '".$id."'";
	$retval = $config['function']['return']['failure'];
	if($conn->query($sql)) {
		$sql = "DELETE FROM `".$config['db']['database']."`.`work_order`
			WHERE `id_order` = '".$id."'";
			
		$conn->query($sql);
		
		$sql = "DELETE FROM `".$config['db']['database']."`.`supplier_order`
			WHERE `id_order` = '".$id."'";
		if($conn->query($sql)) {
			$sql = "DELETE FROM `".$config['db']['database']."`.`subcontract_order`
			WHERE `id_order` = '".$id."'";
			if($conn->query($sql)) {
				$retval = $config['function']['return']['success'];
			}
		}
		
		$sql1 = "SELECT * FROM `".$config['db']['database']."`.`work_status_level_1`
			WHERE `id_order` = '".$id."'";
		$result1 = $conn->query($sql1);		
		while($row1 = mysql_fetch_assoc($result1)) {
			$sql2 = "SELECT * FROM `".$config['db']['database']."`.`work_status_level_2`
				WHERE `id_ws_1` = '".$row1['id_ws_1']."'";
			$result2 = $conn->query($sql2);	
			while($row2 = mysql_fetch_assoc($result2)) {
				$sql3 = "DELETE FROM `".$config['db']['database']."`.`work_status_level_3`
						WHERE `id_ws_2` = '".$row2['id_ws_2']."'";
				$retval = $config['function']['return']['failure'];
				if($conn->query($sql3)) {
					$retval = $config['function']['return']['success'];
				}
			}
			$sql4 = "DELETE FROM `".$config['db']['database']."`.`work_status_level_2`
					WHERE `id_ws_1` = '".$row1['id_ws_1']."'";
			$retval = $config['function']['return']['failure'];
			if($conn->query($sql4)) {
				$retval = $config['function']['return']['success'];
			}
		}
		$sql5 = "DELETE FROM `".$config['db']['database']."`.`work_status_level_1`
				WHERE `id_order` = '".$id."'";
		$retval = $config['function']['return']['failure'];
		if($conn->query($sql5)) {
			$retval = $config['function']['return']['success'];
		}
	}
	
	$conn->close();
	
	return $retval;
}

function get_subcontract_not_order($id){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$id = mysql_real_escape_string($id);
	
	$sql = "SELECT * from `".$config['db']['database']."`.subcontract where id_subcontract not in 
		(
			SELECT B.id_subcontract FROM `".$config['db']['database']."`.`subcontract_order` A 
			left join `".$config['db']['database']."`.subcontract B on (A.id_subcontract = B.id_subcontract)
			left join  `".$config['db']['database']."`.sales_order C on (A.id_order = C.id_order)
			WHERE C.id_order = ".$id.
		")";
	
	$result = $conn->query($sql);
	
	$list = array();
	while($row = mysql_fetch_assoc($result)) {
		$list[] = $row;
	}
	
	$conn->close();
	
	return $list;
}

function get_subcontract_order($id){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$id = mysql_real_escape_string($id);
	
	$sql = "SELECT B.* FROM `".$config['db']['database']."`.`subcontract_order` A 
		left join `".$config['db']['database']."`.subcontract B on (A.id_subcontract = B.id_subcontract)
		left join  `".$config['db']['database']."`.subcontract_order C on (A.id_order = C.id_order)
		WHERE C.id_order = ".$id;
	
	$result = $conn->query($sql);
	
	$list = array();
	while($row = mysql_fetch_assoc($result)) {
		$list[] = $row;
	}
	
	$conn->close();
	
	return $list;
}

function get_supplier_not_order($id){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$id = mysql_real_escape_string($id);
	
	$sql = "SELECT * from `".$config['db']['database']."`.supplier where id_supplier not in 
		(
			SELECT B.id_supplier FROM `".$config['db']['database']."`.`supplier_order` A 
			left join `".$config['db']['database']."`.supplier B on (A.id_supplier = B.id_supplier)
			left join  `".$config['db']['database']."`.sales_order C on (A.id_order = C.id_order)
			WHERE C.id_order = ".$id.
		")";
	
	$result = $conn->query($sql);
	
	$list = array();
	while($row = mysql_fetch_assoc($result)) {
		$list[] = $row;
	}
	
	$conn->close();
	
	return $list;
}

function get_supplier_order($id){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$id = mysql_real_escape_string($id);
	
	$sql = "SELECT B.*, A.po_number FROM `".$config['db']['database']."`.`supplier_order` A 
		left join `".$config['db']['database']."`.supplier B on (A.id_supplier = B.id_supplier)
		left join  `".$config['db']['database']."`.sales_order C on (A.id_order = C.id_order)
		WHERE C.id_order = ".$id;
	
	$result = $conn->query($sql);
	
	$list = array();
	while($row = mysql_fetch_assoc($result)) {
		$list[] = $row;
	}
	
	$conn->close();
	
	return $list;
}

function edit_fsupplier($id, $name, $address,  $email, $phone){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$id = mysql_real_escape_string($id);
	$name = mysql_real_escape_string($name);
	$address = mysql_real_escape_string($address);
	$email = mysql_real_escape_string($email);
	$phone = mysql_real_escape_string($phone);
	
	$sql = "UPDATE `".$config['db']['database']."`.`supplier` 
			SET
				`name_of_company` = '".$name."',
				`address` = '".$address."',
				`email` = '".$email."',
				`phone` = '".$phone."'
			WHERE `id_supplier` = '".$id."'";
	
	$retval = $config['function']['return']['failure'];
	if($conn->query($sql)) {
		$retval = $config['function']['return']['success'];
	}
	
	$conn->close();
	
	return $retval;
}

function add_fsupplier($name, $address, $email, $phone){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$name = mysql_real_escape_string($name);
	$address = mysql_real_escape_string($address);
	$email = mysql_real_escape_string($email);
	$phone = mysql_real_escape_string($phone);
	
	$sql = "INSERT INTO `".$config['db']['database']."`.`supplier` (
				`name_of_company`,
				`type`,
				`address`,
				`email`,
				`phone`)
			VALUES (
				'".$name."',
				'fabric',
				'".$address."',
				'".$email."',
				'".$phone."')";
	
	$retval = $config['function']['return']['failure'];
	if($conn->query($sql)) {
		$retval = $config['function']['return']['success'];
	}
	
	$conn->close();
	
	return $retval;
}

function edit_tsupplier($id, $name, $address,  $email, $phone){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$name = mysql_real_escape_string($name);
	$address = mysql_real_escape_string($address);
	$email = mysql_real_escape_string($email);
	$phone = mysql_real_escape_string($phone);
	
	$sql = "UPDATE `".$config['db']['database']."`.`supplier` 
			SET
				`name_of_company` = '".$name."',
				`address` = '".$address."',
				`email` = '".$email."',
				`phone` = '".$phone."'
			WHERE `id_supplier` = '".$id."'";
	
	$retval = $config['function']['return']['failure'];
	if($conn->query($sql)) {
		$retval = $config['function']['return']['success'];
	}
	
	$conn->close();
	
	return $retval;
}

function add_tsupplier($name, $address, $email, $phone){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$name = mysql_real_escape_string($name);
	$address = mysql_real_escape_string($address);
	$email = mysql_real_escape_string($email);
	$phone = mysql_real_escape_string($phone);
	
	$sql = "INSERT INTO `".$config['db']['database']."`.`supplier` (
				`name_of_company`,
				`type`,
				`address`,
				`email`,
				`phone`)
			VALUES (
				'".$name."',
				'trim',
				'".$address."',
				'".$email."',
				'".$phone."')";
	
	$retval = $config['function']['return']['failure'];
	if($conn->query($sql)) {
		$retval = $config['function']['return']['success'];
	}
	
	$conn->close();
	
	return $retval;
}

function save_time_status($id, $status){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$id = mysql_real_escape_string($id);
	$status = mysql_real_escape_string($status);
	
	$sql = "UPDATE `".$config['db']['database']."`.`work_status_level_2` 
			SET
				`time_status` = ".$status."
			WHERE `id_ws_2` = '".$id."'";
	
	$retval = $config['function']['return']['failure'];
	if($conn->query($sql)) {
		$retval = $config['function']['return']['success'];
	}
	
	$conn->close();
	
	return $retval;
}

function save_sup_time_status($id, $status){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$id = mysql_real_escape_string($id);
	$status = mysql_real_escape_string($status);
	
	$sql = "UPDATE `".$config['db']['database']."`.`supplier_order` 
			SET
				`time_status` = ".$status."
			WHERE `po_number` = '".$id."'";
	
	$retval = $config['function']['return']['failure'];
	if($conn->query($sql)) {
		$retval = $config['function']['return']['success'];
	}
	
	$conn->close();
	
	return $retval;
}

function save_sub_time_status($id, $status){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$id = mysql_real_escape_string($id);
	$status = mysql_real_escape_string($status);
	
	$sql = "UPDATE `".$config['db']['database']."`.`subcontract_order` 
			SET
				`time_status` = ".$status."
			WHERE `subcontract_number` = '".$id."'";
	
	$retval = $config['function']['return']['failure'];
	if($conn->query($sql)) {
		$retval = $config['function']['return']['success'];
	}
	
	$conn->close();
	
	return $retval;
}

function edit_subcontract($id, $name, $address,  $email, $phone){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$name = mysql_real_escape_string($name);
	$address = mysql_real_escape_string($address);
	$email = mysql_real_escape_string($email);
	$phone = mysql_real_escape_string($phone);
	
	$sql = "UPDATE `".$config['db']['database']."`.`subcontract` 
			SET
				`name_of_company` = '".$name."',
				`address` = '".$address."',
				`email` = '".$email."',
				`phone` = '".$phone."'
			WHERE `id_subcontract` = '".$id."'";
	
	$retval = $config['function']['return']['failure'];
	if($conn->query($sql)) {
		$retval = $config['function']['return']['success'];
	}
	
	$conn->close();
	
	return $retval;
}

function edit_buyer($id, $name, $address,  $email, $phone){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$name = mysql_real_escape_string($name);
	$address = mysql_real_escape_string($address);
	$email = mysql_real_escape_string($email);
	$phone = mysql_real_escape_string($phone);
	
	$sql = "UPDATE `".$config['db']['database']."`.`buyer` 
			SET
				`name_of_company` = '".$name."',
				`address` = '".$address."',
				`email` = '".$email."',
				`phone` = '".$phone."'
			WHERE `id_buyer` = '".$id."'";
	
	$retval = $config['function']['return']['failure'];
	if($conn->query($sql)) {
		$retval = $config['function']['return']['success'];
	}
	
	$conn->close();
	
	return $retval;
}

function add_subcontract($name, $address, $email, $phone){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$name = mysql_real_escape_string($name);
	$address = mysql_real_escape_string($address);
	$email = mysql_real_escape_string($email);
	$phone = mysql_real_escape_string($phone);
	
	$sql = "INSERT INTO `".$config['db']['database']."`.`subcontract` (
				`name_of_company`,
				`address`,
				`email`,
				`phone`)
			VALUES (
				'".$name."',
				'".$address."',
				'".$email."',
				'".$phone."')";
	
	$retval = $config['function']['return']['failure'];
	if($conn->query($sql)) {
		$retval = $config['function']['return']['success'];
	}
	
	$conn->close();
	
	return $retval;
}

function add_buyer($name, $address, $email, $phone){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$name = mysql_real_escape_string($name);
	$address = mysql_real_escape_string($address);
	$email = mysql_real_escape_string($email);
	$phone = mysql_real_escape_string($phone);
	
	$sql = "INSERT INTO `".$config['db']['database']."`.`buyer` (
				`name_of_company`,
				`address`,
				`email`,
				`phone`)
			VALUES (
				'".$name."',
				'".$address."',
				'".$email."',
				'".$phone."')";
				
	$retval = $config['function']['return']['failure'];
	if($conn->query($sql)) {
		$retval = $config['function']['return']['success'];
	}
	
	$conn->close();
	
	return $retval;
}

function delete_subcontract($id){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$id = mysql_real_escape_string($id);
	
	$sql = "DELETE FROM `".$config['db']['database']."`.`subcontract`
			WHERE `id_subcontract` = '".$id."'";
	
	$retval = $config['function']['return']['failure'];
	
	if($conn->query($sql)) {
		$sql = "DELETE FROM `".$config['db']['database']."`.`subcontract_order`
			WHERE `id_subcontract` = '".$id."'";
		
		if($conn->query($sql)) {
			$retval = $config['function']['return']['success'];
		}
	}
	
	$conn->close();
	
	return $retval;
}

function delete_buyer($id){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$id = mysql_real_escape_string($id);
	
	$sql = "DELETE FROM `".$config['db']['database']."`.`buyer`
			WHERE `id_buyer` = '".$id."'";
	
	$retval = $config['function']['return']['failure'];
	if($conn->query($sql)) {
		$retval = $config['function']['return']['success'];
	}
	
	$conn->close();
	
	return $retval;
}

function delete_supplier_status($id){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$id = mysql_real_escape_string($id);
	
	$sql = "DELETE FROM `".$config['db']['database']."`.`supplier_order`
			WHERE `id_supplier_order` = '".$id."'";
	
	$retval = $config['function']['return']['failure'];
	if($conn->query($sql)) {
		$retval = $config['function']['return']['success'];
	}
	
	$conn->close();
	
	return $retval;
}

function delete_subcontract_status($id){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$id = mysql_real_escape_string($id);
	
	$sql = "DELETE FROM `".$config['db']['database']."`.`subcontract_order`
			WHERE `id_subcontract_order` = '".$id."'";
	
	$retval = $config['function']['return']['failure'];
	if($conn->query($sql)) {
		$retval = $config['function']['return']['success'];
	}
	
	$conn->close();
	
	return $retval;
}

function delete_supplier($id){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$id = mysql_real_escape_string($id);
	
	$sql = "DELETE FROM `".$config['db']['database']."`.`supplier`
			WHERE `id_supplier` = '".$id."'";
	
	$retval = $config['function']['return']['failure'];
	if($conn->query($sql)) {
		$sql = "DELETE FROM `".$config['db']['database']."`.`supplier_order`
			WHERE `id_supplier` = '".$id."'";
		
		if($conn->query($sql)) {
			$retval = $config['function']['return']['success'];
		}
	}
	
	$conn->close();
	
	return $retval;
}

function get_all_subcontract(){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$sql = "SELECT * FROM `".$config['db']['database']."`.`subcontract`
			ORDER BY `id_subcontract` ASC";
	
	$result = $conn->query($sql);
	
	$list = array();
	while($row = mysql_fetch_assoc($result)) {
		$list[] = $row;
	}
	
	$conn->close();
	
	return $list;
}

function get_all_supplier(){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$sql = "SELECT * FROM `".$config['db']['database']."`.`supplier`
			ORDER BY `id_supplier` ASC";
	
	$result = $conn->query($sql);
	
	$list = array();
	while($row = mysql_fetch_assoc($result)) {
		$list[] = $row;
	}
	
	$conn->close();
	
	return $list;
}

function get_all_buyer(){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$sql = "SELECT * FROM `".$config['db']['database']."`.`buyer`
			ORDER BY `id_buyer` ASC";
	
	$result = $conn->query($sql);
	
	$list = array();
	while($row = mysql_fetch_assoc($result)) {
		$list[] = $row;
	}
	
	$conn->close();
	
	return $list;
}

function get_all_tsupplier(){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$sql = "SELECT * FROM `".$config['db']['database']."`.`supplier`
			WHERE type='trim'
			ORDER BY `id_supplier` ASC";
	
	$result = $conn->query($sql);
	
	$list = array();
	while($row = mysql_fetch_assoc($result)) {
		$list[] = $row;
	}
	
	$conn->close();
	
	return $list;
}

function get_all_fsupplier(){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$sql = "SELECT * FROM `".$config['db']['database']."`.`supplier`
			WHERE type='fabric'
			ORDER BY `id_supplier` ASC";
	
	$result = $conn->query($sql);
	
	$list = array();
	while($row = mysql_fetch_assoc($result)) {
		$list[] = $row;
	}
	
	$conn->close();
	
	return $list;
}

function change_pwd($pwd, $newpwd, $cfrpwd){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$pwd = mysql_real_escape_string($pwd);
	$newpwd = mysql_real_escape_string($newpwd);
	$cfrpwd = mysql_real_escape_string($cfrpwd);
	$pwd = md5($pwd);
	$newpwd = md5($newpwd);
	$cfrpwd = md5($cfrpwd);
	$username = get_session("username");
	
	$sql = "SELECT password FROM `".$config['db']['database']."`.`user` 
			WHERE username='".$username."'";

	$result = $conn->query($sql);
	$retval = $config['function']['return']['failure'];	
	
	if($row = mysql_fetch_assoc($result)){
		if(($row['password'] == $pwd) && ($newpwd == $cfrpwd)){	
			$sql = "UPDATE `".$config['db']['database']."`.`user` 
					SET
						`password` = '".$newpwd."'
					WHERE `username` = '".$username."'";

			if($conn->query($sql)) {
				$retval = $config['function']['return']['success'];
			}	
		}
	}
	
	return $retval;
}

function get_supplier($id){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$id = mysql_real_escape_string($id);
	
	$sql = "SELECT * FROM `".$config['db']['database']."`.`supplier` 
			WHERE id_supplier='".$id."'";

	$result = $conn->query($sql);
	
	return mysql_fetch_assoc($result);
}

function get_supplier_status($id){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$id = mysql_real_escape_string($id);
	
	$sql = "SELECT B.*, A.* FROM `".$config['db']['database']."`.`supplier_order` A 
			left join `".$config['db']['database']."`.supplier B on (A.id_supplier = B.id_supplier)
			WHERE id_supplier_order=".$id;

	$result = $conn->query($sql);
	
	return mysql_fetch_assoc($result);
}

function get_subcontract_status($id){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$id = mysql_real_escape_string($id);
	
	$sql = "SELECT B.*, A.* FROM `".$config['db']['database']."`.`subcontract_order` A 
			left join `".$config['db']['database']."`.subcontract B on (A.id_subcontract = B.id_subcontract)
			WHERE id_subcontract_order=".$id;
	
	$result = $conn->query($sql);
	
	return mysql_fetch_assoc($result);
}

function get_all_supplier_status($order, $id){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$id = mysql_real_escape_string($id);
	
	$sql = "SELECT B.*, A.* FROM `".$config['db']['database']."`.`supplier_order` A 
		left join `".$config['db']['database']."`.supplier B on (A.id_supplier = B.id_supplier)
		left join  `".$config['db']['database']."`.sales_order C on (A.id_order = C.id_order)
		WHERE A.id_order = ".$order;

	$result = $conn->query($sql);
	
	$list = array();
	while($row = mysql_fetch_assoc($result)) {
		$list[] = $row;
	}
	
	$conn->close();
	
	return $list;
}

function get_all_subcontract_status($order, $id){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$id = mysql_real_escape_string($id);
	
	$sql = "SELECT B.*, A.* FROM `".$config['db']['database']."`.`subcontract_order` A 
		left join `".$config['db']['database']."`.subcontract B on (A.id_subcontract = B.id_subcontract)
		left join  `".$config['db']['database']."`.sales_order C on (A.id_order = C.id_order)
		WHERE A.id_order = ".$order;
	
	$result = $conn->query($sql);
	
	$list = array();
	while($row = mysql_fetch_assoc($result)) {
		$list[] = $row;
	}
	
	$conn->close();
	
	return $list;
}

function get_subcontract($id){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$id = mysql_real_escape_string($id);
	
	$sql = "SELECT * FROM `".$config['db']['database']."`.`subcontract` 
			WHERE id_subcontract='".$id."'";

	$result = $conn->query($sql);
	
	return mysql_fetch_assoc($result);
}

function get_buyer($id){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$id = mysql_real_escape_string($id);
	
	$sql = "SELECT * FROM `".$config['db']['database']."`.`buyer` 
			WHERE id_buyer='".$id."'";

	$result = $conn->query($sql);
	
	return mysql_fetch_assoc($result);
}

function get_user($username){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$username = mysql_real_escape_string($username);
	
	$sql = "SELECT * FROM `".$config['db']['database']."`.`user` 
			WHERE username='".$username."'";

	$result = $conn->query($sql);
	
	return mysql_fetch_assoc($result);
}

function delete_user($username){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$username = mysql_real_escape_string($username);
	
	$sql = "DELETE FROM `".$config['db']['database']."`.`user`
			WHERE `username` = '".$username."'";
	
	$retval = $config['function']['return']['failure'];
	if($conn->query($sql)) {
		$retval = $config['function']['return']['success'];
	}
	
	$conn->close();
	
	return $retval;
}

function edit_user($username, $newusername,  $password, $fullname, $dept){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$username = mysql_real_escape_string($username);
	$newusername = mysql_real_escape_string($newusername);
	$password = mysql_real_escape_string($password);
	$fullname = mysql_real_escape_string($fullname);
	$dept = mysql_real_escape_string($dept);
	$password = md5($password);
	
	$sql = "UPDATE `".$config['db']['database']."`.`user` 
			SET
				`username` = '".$newusername."',
				`password` = '".$password."',
				`fullname` = '".$fullname."',
				`dept` = '".$dept."'
			WHERE `username` = '".$username."'";
	
	$retval = $config['function']['return']['failure'];
	if($conn->query($sql)) {
		$retval = $config['function']['return']['success'];
	}
	
	$conn->close();
	
	return $retval;
}

function add_supplier_status($id, $status, $delivery, $ponumber, $order, $idws3){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$id = mysql_real_escape_string($id);
	$status = mysql_real_escape_string($status);
	$delivery = mysql_real_escape_string($delivery);
	$ponumber = mysql_real_escape_string($ponumber);
	$order = mysql_real_escape_string($order);
	$idws3 = mysql_real_escape_string($idws3);
	
	$sql = "INSERT INTO `".$config['db']['database']."`.`supplier_order` (
				`id_supplier`,
				`id_order`,
				`po_number`,
				`delivery_date`,
				`work_status`,
				`id_ws_3`)
			VALUES (
				".$id.",
				".$order.",
				".$ponumber.",
				'".$delivery."',
				".$status.",
				'".$idws3."')";
	
	$retval = $config['function']['return']['failure'];
	if($conn->query($sql)) {
		$retval = $config['function']['return']['success'];
	}
	
	$conn->close();
	
	return $retval;
}

function add_subcontract_status($id, $status, $delivery, $number, $order, $idws3){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$id = mysql_real_escape_string($id);
	$status = mysql_real_escape_string($status);
	$delivery = mysql_real_escape_string($delivery);
	$number = mysql_real_escape_string($number);
	$order = mysql_real_escape_string($order);
	$idws3 = mysql_real_escape_string($idws3);
	
	$sql = "INSERT INTO `".$config['db']['database']."`.`subcontract_order` (
				`id_subcontract`,
				`id_order`,
				`subcontract_number`,
				`delivery_date`,
				`work_status`,
				`id_ws_3`)
			VALUES (
				".$id.",
				".$order.",
				".$number.",
				'".$delivery."',
				".$status.",
				'".$idws3."')";
	
	$retval = $config['function']['return']['failure'];
	if($conn->query($sql)) {
		$retval = $config['function']['return']['success'];
	}
	
	$conn->close();
	
	return $retval;
}

function add_user($username, $password, $fullname, $dept){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$username = mysql_real_escape_string($username);
	$password = mysql_real_escape_string($password);
	$fullname = mysql_real_escape_string($fullname);
	$dept = mysql_real_escape_string($dept);
	$password = md5($password);
	
	$sql = "INSERT INTO `".$config['db']['database']."`.`user` (
				`username`,
				`password`,
				`fullname`,
				`dept`)
			VALUES (
				'".$username."',
				'".$password."',
				'".$fullname."',
				'".$dept."')";
	
	$retval = $config['function']['return']['failure'];
	if($conn->query($sql)) {
		$retval = $config['function']['return']['success'];
	}
	
	$conn->close();
	
	return $retval;
}

function get_all_user(){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$sql = "SELECT * FROM `".$config['db']['database']."`.`user`
			ORDER BY `fullname` ASC";
	
	$result = $conn->query($sql);
	
	$list = array();
	while($row = mysql_fetch_assoc($result)) {
		$list[] = $row;
	}
	
	$conn->close();
	
	return $list;
}

function get_all_order(){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$sql = "SELECT A.*, B.name_of_company FROM `".$config['db']['database']."`.`sales_order` A
			inner join `".$config['db']['database']."`.`buyer` B on (A.buyer = B.id_buyer)
			ORDER BY `delivery_date` ASC";
	
	
	$result = $conn->query($sql);
	
	$list = array();
	while($row = mysql_fetch_assoc($result)) {
		$list[] = $row;
	}
	
	$conn->close();
	
	return $list;
}

function get_all_work(){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$sql = "SELECT * FROM `".$config['db']['database']."`.`work_order`
			ORDER BY `id_work_order` ASC";
	
	$result = $conn->query($sql);
	
	$list = array();
	while($row = mysql_fetch_assoc($result)) {
		$list[] = $row;
	}
	
	$conn->close();
	
	return $list;
}

function get_all_work_by_order($id){
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$sql = "SELECT * FROM `".$config['db']['database']."`.`work_order`
			WHERE id_order = ".$id." 
			ORDER BY `id_work_order` ASC";
	
	$result = $conn->query($sql);
	
	$list = array();
	while($row = mysql_fetch_assoc($result)) {
		$list[] = $row;
	}
	
	$conn->close();
	
	return $list;
}

function set_page_content($page){
	$retval = '';
	switch($page) {
		case 'report':
			$retval = file_get_contents('view-report.php');
			break;
		case 'performance':
			$retval = file_get_contents('view-performance.php');
			break;
		case 'controlling':
			$retval = file_get_contents('view-controlling.php');
			break;
		case 'schedule':
			$retval = file_get_contents('view-schedule.php');
			break;
		case 'production':
			$retval = file_get_contents('view-production.php');
			break;
		case 'process':
			$retval = file_get_contents('view-process.php');
			break;
		case 'test':
			$retval = file_get_contents('test.php');
			break;
		case 'user':
			$retval = file_get_contents('view-user.php');
			break;
		case 'profile':
			$retval = file_get_contents('view-profile.php');
			break;
		case 'order':
			$retval = file_get_contents('view-order.php');
			break;
		case 'job':
			$retval = file_get_contents('view-job.php');
			break;
		case 'jobdetail':
			$retval = file_get_contents('view-job-detail.php');
			break;
	}
	return $retval;
}

function get_session($name){
	return session_get($name);
}

function account_login($username, $password) {
	global $config;
	
	$conn = new_connection();
	$conn->open();
	
	$username = mysql_real_escape_string($username);
	$password = mysql_real_escape_string($password);
	$password = md5($password);
	
	$sql = "SELECT *
			FROM `".$config['db']['database']."`.`user`
			WHERE `username` = '".$username."' AND `password` = '".$password."'
			LIMIT 1";
	
	$retval = $config['function']['return']['failure'];
	if($result = $conn->query($sql)) {
		if($row = mysql_fetch_assoc($result)) {
			session_set('username', $username);
			session_set('dept', $row['dept']);
			
			$retval = $config['function']['return']['success'];
		}
	}
	
	$conn->close();
	
	return $retval;
}

function account_logout() {
	session_del('username');
	session_del('dept');
	
	return $config['function']['return']['success'];
}

function account_is_logged_in() {
	global $config;
	
	$retval = $config['function']['return']['failure'];
	if(session_get('username') && session_get('dept')) {
		$retval = $config['function']['return']['success'];
	}
	
	return $retval;
}

?>