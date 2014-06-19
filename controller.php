<?php
require_once('includes/config.php');
require_once('includes/session.php');
require_once('model.php');

session_init();

if($_GET) {
	$action = $_GET['action'];
	@$data = $_GET['data'];
	
	switch($action) {
		case 'get_resched_list':
			echo json_encode(get_resched_list($data['id']));
			break;
		case 'set_resched_list':
			echo set_resched_list($data['resched']);
			break;
		case 'get_job_perf':
			echo json_encode(get_job_perf());
			break;
		case 'get_subcontract_perf':
			echo json_encode(get_subcontract_perf());
			break;
		case 'get_tsupplier_perf':
			echo json_encode(get_tsupplier_perf());
			break;
		case 'get_fsupplier_perf':
			echo json_encode(get_fsupplier_perf());
			break;
		case 'get_job_perf_by_order':
			echo json_encode(get_job_perf($data['id']));
			break;
		case 'get_subcontract_perf_by_order':
			echo json_encode(get_subcontract_perf($data['id']));
			break;
		case 'get_tsupplier_perf_by_order':
			echo json_encode(get_tsupplier_perf($data['id']));
			break;
		case 'get_fsupplier_perf_by_order':
			echo json_encode(get_fsupplier_perf($data['id']));
			break;
		case 'save_time_status':
			echo save_time_status($data['id'], $data['status']);
			break;
		case 'save_sup_time_status':
			echo save_sup_time_status($data['id'], $data['status']);
			break;
		case 'save_sub_time_status':
			echo save_sub_time_status($data['id'], $data['status']);
			break;
		case 'edit_target':
			echo edit_target($data['id'], $data['start'], $data['end']);
			break;
		case 'get_activity':
			echo json_encode(get_activity($data['id']));
			break;
		case 'calculate_progress':
			echo calculate_progress($data['id']);
			break;
		case 'get_activity_level_2':
			echo json_encode(get_activity_level_2());
			break;
		case 'get_all_ws_level_3':
			echo json_encode(get_all_ws_level_3($data['id']));
			break;
		case 'get_all_ws_level_2':
			echo json_encode(get_all_ws_level_2($data['id']));
			break;
		case 'get_all_ws_level_2_1':
			echo json_encode(get_all_ws_level_2_1($data['id']));
			break;
		case 'get_all_ws_level_1':
			echo json_encode(get_all_ws_level_1($data['id']));
			break;
		case 'change_ws':
			echo change_ws($data['id'], $data['status'], $data['comment']);
			break;
		case 'change_pwd':
			echo change_pwd($data['pwd'], $data['newpwd'], $data['cfrpwd']);
			break;
		case 'get_order':
			echo json_encode(get_order($data['id']));
			break;
		case 'get_work':
			echo json_encode(get_work($data['id']));
			break;
		case 'get_user':
			echo json_encode(get_user($data['username']));
			break;
		case 'get_supplier':
			echo json_encode(get_supplier($data['id']));
			break;
		case 'get_supplier_status':
			echo json_encode(get_supplier_status($data['id']));
			break;
		case 'get_subcontract_status':
			echo json_encode(get_subcontract_status($data['id']));
			break;
		case 'get_all_supplier_status':
			echo json_encode(get_all_supplier_status($data['order'], $data['id']));
			break;
		case 'get_all_subcontract_status':
			echo json_encode(get_all_subcontract_status($data['order'], $data['id']));
			break;
		case 'get_subcontract':
			echo json_encode(get_subcontract($data['id']));
			break;
		case 'get_buyer':
			echo json_encode(get_buyer($data['id']));
			break;
		case 'delete_user':
			echo delete_user($data['username']);
			break;
		case 'delete_order':
			echo delete_order($data['id']);
			break;
		case 'delete_supplier_status':
			echo delete_supplier_status($data['id']);
			break;
		case 'delete_subcontract_status':
			echo delete_subcontract_status($data['id']);
			break;
		case 'delete_work':
			echo delete_work($data['id']);
			break;
		case 'delete_fsupplier':
			echo delete_supplier($data['id']);
			break;
		case 'delete_tsupplier':
			echo delete_supplier($data['id']);
			break;
		case 'delete_subcontract':
			echo delete_subcontract($data['id']);
			break;
		case 'delete_buyer':
			echo delete_buyer($data['id']);
			break;
		case 'edit_work':
			echo edit_work($data['id'], $data['order'], $data['dest'], $data['quantity'], $data['cutting'], $data['sewing'], $data['delivery']);
			break;
		case 'add_supplier_status':
			echo add_supplier_status($data['id'], $data['status'], $data['delivery'], $data['ponumber'], $data['order'], $data['idws3']);
			break;
		case 'edit_supplier_status':
			echo edit_supplier_status($data['id'], $data['status'], $data['delivery'], $data['ponumber']);
			break;
		case 'edit_subcontract_status':
			echo edit_subcontract_status($data['id'], $data['status'], $data['delivery'], $data['number']);
			break;
		case 'add_subcontract_status':
			echo add_subcontract_status($data['id'], $data['status'], $data['delivery'], $data['number'], $data['order'], $data['idws3']);
			break;
		case 'edit_subcontract_status':
			echo edit_subcontract_status($data['id'], $data['status'], $data['delivery'], $data['number']);
			break;
		case 'add_work':
			echo add_work($data['order'], $data['dest'], $data['quantity'], $data['cutting'], $data['sewing'], $data['delivery']);
			break;
		case 'add_order':
			echo add_order($data['style'], $data['buyer'], $data['season'], $data['product'], $data['quantity'], $data['order'], $data['confirm'], $data['delivery']);
			break;
		case 'edit_order':
			echo edit_order($data['id'], $data['style'], $data['buyer'], $data['season'], $data['product'], $data['quantity'], $data['order'], $data['confirm'], $data['delivery']);
			break;
		case 'edit_fsupplier':
			echo edit_fsupplier($data['id'], $data['name'], $data['address'], $data['email'], $data['phone']);
			break;
		case 'add_fsupplier':
			echo add_fsupplier($data['name'], $data['address'], $data['email'], $data['phone']);
			break;
		case 'edit_tsupplier':
			echo edit_tsupplier($data['id'], $data['name'], $data['address'], $data['email'], $data['phone']);
			break;
		case 'add_tsupplier':
			echo add_tsupplier($data['name'], $data['address'], $data['email'], $data['phone']);
			break;
		case 'edit_subcontract':
			echo edit_subcontract($data['id'], $data['name'],  $data['address'], $data['email'], $data['phone']);
			break;
		case 'edit_buyer':
			echo edit_buyer($data['id'], $data['name'],  $data['address'], $data['email'], $data['phone']);
			break;
		case 'add_subcontract':
			echo add_subcontract($data['name'], $data['address'], $data['email'], $data['phone']);
			break;
		case 'add_buyer':
			echo add_buyer($data['name'], $data['address'], $data['email'], $data['phone']);
			break;
		case 'edit_user':
			echo edit_user($data['username'], $data['newusername'], $data['password'], $data['fullname'], $data['dept']);
			break;
		case 'add_user':
			echo add_user($data['username'], $data['password'], $data['fullname'], $data['dept']);
			break;
		case 'get_all_user':
			echo json_encode(get_all_user());
			break;
		case 'get_all_order':
			echo json_encode(get_all_order());
			break;
		case 'get_all_order_late':
			echo json_encode(get_all_order_late());
			break;
		case 'get_all_work':
			echo json_encode(get_all_work());
			break;
		case 'get_all_work_by_order':
			echo json_encode(get_all_work_by_order($data['id']));
			break;
		case 'get_supplier_not_order':
			echo json_encode(get_supplier_not_order($data['id']));
			break;
		case 'get_supplier_order':
			echo json_encode(get_supplier_order($data['id']));
			break;
		case 'get_subcontract_not_order':
			echo json_encode(get_subcontract_not_order($data['id']));
			break;
		case 'get_subcontract_order':
			echo json_encode(get_subcontract_order($data['id']));
			break;
		case 'get_all_subcontract':
			echo json_encode(get_all_subcontract());
			break;
		case 'get_all_supplier':
			echo json_encode(get_all_supplier());
			break;
		case 'get_all_buyer':
			echo json_encode(get_all_buyer());
			break;
		case 'get_all_tsupplier':
			echo json_encode(get_all_tsupplier());
			break;
		case 'get_all_fsupplier':
			echo json_encode(get_all_fsupplier());
			break;
		case 'set_page_content':
			echo set_page_content($data['page']);
			break;
		case 'account_login':
			echo account_login($data['username'], $data['password']);
			break;
		case 'account_logout':
			account_logout();
			break;
		case 'account_is_logged_in':
			echo account_is_logged_in();
			break;
		case 'get_session':
			echo get_session($data['name']);
			break;
	}
}

?>