var url = "controller.php"

function getReschedList(id, callback){
	var getparam = {
		action: 'get_resched_list',
		data: {
			id: id
		}
	}
	$.getJSON(url, getparam, callback);
}

function setReschedList(resched, callback){
	var getparam = {
		action: 'set_resched_list',
		data: {
			resched: resched
		}
	}
	$.getJSON(url, getparam, callback);
}

function getSubcontractPerf(callback){
	var getparam = {
		action: 'get_subcontract_perf',
		data: {
			
		}
	}
	$.getJSON(url, getparam, callback);
}

function getSubcontractPerfByOrder(id, callback){
	var getparam = {
		action: 'get_subcontract_perf_by_order',
		data: {
			id: id
		}
	}
	$.getJSON(url, getparam, callback);
}

function getJobPerf(callback){
	var getparam = {
		action: 'get_job_perf',
		data: {
			
		}
	}
	$.getJSON(url, getparam, callback);
}

function getJobPerfByOrder(id, callback){
	var getparam = {
		action: 'get_job_perf_by_order',
		data: {
			id: id
		}
	}
	$.getJSON(url, getparam, callback);
}

function getTSupplierPerf(callback){
	var getparam = {
		action: 'get_tsupplier_perf',
		data: {
			
		}
	}
	$.getJSON(url, getparam, callback);
}

function getFSupplierPerf(callback){
	var getparam = {
		action: 'get_fsupplier_perf',
		data: {
			
		}
	}
	$.getJSON(url, getparam, callback);
}

function getTSupplierPerfByOrder(id, callback){
	var getparam = {
		action: 'get_tsupplier_perf_by_order',
		data: {
			id: id
		}
	}
	$.getJSON(url, getparam, callback);
}

function getFSupplierPerfByOrder(id, callback){
	var getparam = {
		action: 'get_fsupplier_perf_by_order',
		data: {
			id: id
		}
	}
	$.getJSON(url, getparam, callback);
}

function saveTimeStatus(id, status, callback){
	var getparam = {
		action: 'save_time_status',
		data: {
			id: id,
			status: status
		}
	}
	$.get(url, getparam, callback);
}

function saveSupTimeStatus(id, status, callback){
	var getparam = {
		action: 'save_sup_time_status',
		data: {
			id: id,
			status: status
		}
	}
	$.get(url, getparam, callback);
}

function saveSubTimeStatus(id, status, callback){
	var getparam = {
		action: 'save_sub_time_status',
		data: {
			id: id,
			status: status
		}
	}
	$.get(url, getparam, callback);
}

function editTarget(id, start, end, callback){
	var getparam = {
		action: 'edit_target',
		data: {
			id: id,
			start: start,
			end: end
		}
	}
	$.get(url, getparam, callback);
}

function calculateProgress(id, callback){
	var getparam = {
		action: 'calculate_progress',
		data: {
			id: id
		}
	}
	$.get(url, getparam, callback);
}

function getActivityLevel2(callback){
	var getparam = {
		action: 'get_activity_level_2',
		data: {
			
		}
	}
	$.getJSON(url, getparam, callback);
}

function getAllWSLevel3(id, callback){
	var getparam = {
		action: 'get_all_ws_level_3',
		data: {
			id: id
		}
	}
	$.getJSON(url, getparam, callback);
}

function getAllWSLevel2(id, callback){
	var getparam = {
		action: 'get_all_ws_level_2',
		data: {
			id: id
		}
	}
	$.getJSON(url, getparam, callback);
}

function getAllWSLevel21(id, callback){
	var getparam = {
		action: 'get_all_ws_level_2_1',
		data: {
			id: id
		}
	}
	$.getJSON(url, getparam, callback);
}

function getAllWSLevel1(id, callback){
	var getparam = {
		action: 'get_all_ws_level_1',
		data: {
			id: id
		}
	}
	$.getJSON(url, getparam, callback);
}

function getActivity(id, callback){
	var getparam = {
		action: 'get_activity',
		data: {
			id: id
		}
	}
	$.getJSON(url, getparam, callback);
}

function getOrder(id, callback){
	var getparam = {
		action: 'get_order',
		data: {
			id: id
		}
	}
	$.getJSON(url, getparam, callback);
}

function getWork(id, callback){
	var getparam = {
		action: 'get_work',
		data: {
			id: id
		}
	}
	$.getJSON(url, getparam, callback);
}

function editWork(id, order, dest, quantity, cutting, sewing, delivery, callback){
	var getparam = {
		action: 'edit_work',
		data: {
			id: id,
			order: order,
			dest: dest,
			quantity: quantity,
			cutting: cutting,
			sewing: sewing,
			delivery: delivery
		}
	}
	$.get(url, getparam, callback);
}

function editOrder(id, style, buyer, season, product, quantity, order, confirm, delivery, callback){
	var getparam = {
		action: 'edit_order',
		data: {
			id: id,
			style: style,
			buyer: buyer,
			season: season,
			product: product,
			quantity: quantity,
			order: order,
			confirm: confirm,
			delivery: delivery
		}
	}
	$.get(url, getparam, callback);
}

function addSubcontractStatus(id, status, delivery, number, order, idws3, callback){
	var getparam = {
		action: 'add_subcontract_status',
		data: {
			id: id,
			status: status,
			delivery: delivery,
			number: number,
			order: order,
			idws3: idws3
		}
	}
	$.get(url, getparam, callback);
}

function editSubcontractStatus(id, status, delivery, number, callback){
	var getparam = {
		action: 'edit_subcontract_status',
		data: {
			id: id,
			status: status,
			delivery: delivery,
			number: number
		}
	}
	$.get(url, getparam, callback);
}

function addSupplierStatus(id, status, delivery, ponumber, order, idws3, callback){
	var getparam = {
		action: 'add_supplier_status',
		data: {
			id: id,
			status: status,
			delivery: delivery,
			ponumber: ponumber,
			order: order,
			idws3: idws3
		}
	}
	$.get(url, getparam, callback);
}

function editSupplierStatus(id, status, delivery, ponumber, callback){
	var getparam = {
		action: 'edit_supplier_status',
		data: {
			id: id,
			status: status,
			delivery: delivery,
			ponumber: ponumber
		}
	}
	$.get(url, getparam, callback);
}

function editSubcontractStatus(id, status, delivery, number, callback){
	var getparam = {
		action: 'edit_subcontract_status',
		data: {
			id: id,
			status: status,
			delivery: delivery,
			number: number
		}
	}
	$.get(url, getparam, callback);
}

function addOrder(style, buyer, season, product, quantity, order, confirm, delivery, callback){
	var getparam = {
		action: 'add_order',
		data: {
			style: style,
			buyer: buyer,
			season: season,
			product: product,
			quantity: quantity,
			order: order,
			confirm: confirm,
			delivery: delivery
		}
	}
	$.get(url, getparam, callback);
}

function addWork(order, dest, quantity, cutting, sewing, delivery, callback){
	var getparam = {
		action: 'add_work',
		data: {
			order: order,
			dest: dest,
			quantity: quantity,
			cutting: cutting,
			sewing: sewing,
			delivery: delivery
		}
	}
	$.get(url, getparam, callback);
}

function deleteOrder(id, callback){
	var getparam = {
		action: 'delete_order',
		data: {
			id: id
		}
	}
	$.get(url, getparam, callback);
}

function deleteWork(id, callback){
	var getparam = {
		action: 'delete_work',
		data: {
			id: id
		}
	}
	$.get(url, getparam, callback);
}

function deleteSupplierStatus(id, callback){
	var getparam = {
		action: 'delete_supplier_status',
		data: {
			id: id
		}
	}
	$.get(url, getparam, callback);
}

function deleteSubcontractStatus(id, callback){
	var getparam = {
		action: 'delete_subcontract_status',
		data: {
			id: id
		}
	}
	$.get(url, getparam, callback);
}

function getSubcontractNotOrder(id, callback){
	var getparam = {
		action: 'get_subcontract_not_order',
		data: {
			id: id
		}
	}
	$.getJSON(url, getparam, callback);
}

function getSubcontractOrder(id, callback){
	var getparam = {
		action: 'get_subcontract_order',
		data: {
			id: id
		}
	}
	$.getJSON(url, getparam, callback);
}

function getSupplierNotOrder(id, callback){
	var getparam = {
		action: 'get_supplier_not_order',
		data: {
			id: id
		}
	}
	$.getJSON(url, getparam, callback);
}

function getSupplierOrder(id, callback){
	var getparam = {
		action: 'get_supplier_order',
		data: {
			id: id
		}
	}
	$.getJSON(url, getparam, callback);
}

function getAllOrder(callback){
	var getparam = {
		action: 'get_all_order',
		data: {
			
		}
	}
	$.getJSON(url, getparam, callback);
}

function getAllOrderLate(callback){
	var getparam = {
		action: 'get_all_order_late',
		data: {
			
		}
	}
	$.getJSON(url, getparam, callback);
}

function getAllWork(callback){
	var getparam = {
		action: 'get_all_work',
		data: {
			
		}
	}
	$.getJSON(url, getparam, callback);
}

function getAllWorkByOrder(id, callback){
	var getparam = {
		action: 'get_all_work_by_order',
		data: {
			id: id
		}
	}
	$.getJSON(url, getparam, callback);
}

function getSupplier(id, callback){
	var getparam = {
		action: 'get_supplier',
		data: {
			id: id
		}
	}
	$.getJSON(url, getparam, callback);
}

function getAllSupplierStatus(order, id, callback){
	var getparam = {
		action: 'get_all_supplier_status',
		data: {
			order: order,
			id: id
		}
	}
	$.getJSON(url, getparam, callback);
}

function getAllSubcontractStatus(order, id, callback){
	var getparam = {
		action: 'get_all_subcontract_status',
		data: {
			order: order,
			id: id
		}
	}
	$.getJSON(url, getparam, callback);
}

function getSupplierStatus(id, callback){
	var getparam = {
		action: 'get_supplier_status',
		data: {
			id: id
		}
	}
	$.getJSON(url, getparam, callback);
}

function getSubcontractStatus(id, callback){
	var getparam = {
		action: 'get_subcontract_status',
		data: {
			id: id
		}
	}
	$.getJSON(url, getparam, callback);
}

function getSubcontract(id, callback){
	var getparam = {
		action: 'get_subcontract',
		data: {
			id: id
		}
	}
	$.getJSON(url, getparam, callback);
}

function getBuyer(id, callback){
	var getparam = {
		action: 'get_buyer',
		data: {
			id: id
		}
	}
	$.getJSON(url, getparam, callback);
}

function addSubcontract(name, address, email, phone, callback){
	var getparam = {
		action: 'add_subcontract',
		data: {
			name: name,
			address: address,
			email: email,
			phone: phone
		}
	}
	$.get(url, getparam, callback);
}

function addBuyer(name, address, email, phone, callback){
	var getparam = {
		action: 'add_buyer',
		data: {
			name: name,
			address: address,
			email: email,
			phone: phone
		}
	}
	$.get(url, getparam, callback);
}

function editSubcontract(id, name, address, email, phone, callback){
	var getparam = {
		action: 'edit_subcontract',
		data: {
			id: id,
			name: name,
			address: address,
			email: email,
			phone: phone
		}
	}
	$.get(url, getparam, callback);
}

function editBuyer(id, name, address, email, phone, callback){
	var getparam = {
		action: 'edit_buyer',
		data: {
			id: id,
			name: name,
			address: address,
			email: email,
			phone: phone
		}
	}
	$.get(url, getparam, callback);
}

function addTSupplier(name, address, email, phone, callback){
	var getparam = {
		action: 'add_tsupplier',
		data: {
			name: name,
			address: address,
			email: email,
			phone: phone
		}
	}
	$.get(url, getparam, callback);
}

function editTSupplier(id, name, address, email, phone, callback){
	var getparam = {
		action: 'edit_tsupplier',
		data: {
			id: id,
			name: name,
			address: address,
			email: email,
			phone: phone
		}
	}
	$.get(url, getparam, callback);
}

function addFSupplier(name, address, email, phone, callback){
	var getparam = {
		action: 'add_fsupplier',
		data: {
			name: name,
			address: address,
			email: email,
			phone: phone
		}
	}
	$.get(url, getparam, callback);
}

function editFSupplier(id, name, address, email, phone, callback){
	var getparam = {
		action: 'edit_fsupplier',
		data: {
			id: id,
			name: name,
			address: address,
			email: email,
			phone: phone
		}
	}
	$.get(url, getparam, callback);
}

function deleteFSupplier(id, callback){
	var getparam = {
		action: 'delete_fsupplier',
		data: {
			id: id
		}
	}
	$.get(url, getparam, callback);
}

function deleteTSupplier(id, callback){
	var getparam = {
		action: 'delete_tsupplier',
		data: {
			id: id
		}
	}
	$.get(url, getparam, callback);
}

function deleteSubcontract(id, callback){
	var getparam = {
		action: 'delete_subcontract',
		data: {
			id: id
		}
	}
	$.get(url, getparam, callback);
}

function deleteBuyer(id, callback){
	var getparam = {
		action: 'delete_buyer',
		data: {
			id: id
		}
	}
	$.get(url, getparam, callback);
}

function getAllSubcontract(callback){
	var getparam = {
		action: 'get_all_subcontract',
		data: {
			
		}
	}
	$.getJSON(url, getparam, callback);
}

function getAllSupplier(callback){
	var getparam = {
		action: 'get_all_supplier',
		data: {
			
		}
	}
	$.getJSON(url, getparam, callback);
}

function getAllBuyer(callback){
	var getparam = {
		action: 'get_all_buyer',
		data: {
			
		}
	}
	$.getJSON(url, getparam, callback);
}

function getAllTSupplier(callback){
	var getparam = {
		action: 'get_all_tsupplier',
		data: {
			
		}
	}
	$.getJSON(url, getparam, callback);
}

function getAllFSupplier(callback){
	var getparam = {
		action: 'get_all_fsupplier',
		data: {
			
		}
	}
	$.getJSON(url, getparam, callback);
}

function changeWs(id, status, comment, callback){
	var getparam = {
		action: 'change_ws',
		data: {
			id: id,
			status: status,
			comment: comment
		}
	}
	$.get(url, getparam, callback);
}

function changePwd(pwd, newpwd, cfrpwd, callback){
	var getparam = {
		action: 'change_pwd',
		data: {
			pwd: pwd,
			newpwd: newpwd,
			cfrpwd: cfrpwd
		}
	}
	$.get(url, getparam, callback);
}

function getUser(username, callback){
	var getparam = {
		action: 'get_user',
		data: {
			username: username
		}
	}
	$.getJSON(url, getparam, callback);
}

function deleteUser(username, callback){
	var getparam = {
		action: 'delete_user',
		data: {
			username: username
		}
	}
	$.get(url, getparam, callback);
}

function addUser(username, password, fullname, dept, callback){
	var getparam = {
		action: 'add_user',
		data: {
			username: username,
			password: password,
			fullname: fullname,
			dept: dept
		}
	}
	$.get(url, getparam, callback);
}

function editUser(username, newusername, password, fullname, dept, callback){
	var getparam = {
		action: 'edit_user',
		data: {
			username: username,
			newusername: newusername,
			password: password,
			fullname: fullname,
			dept: dept
		}
	}
	$.get(url, getparam, callback);
}

function getAllUser(callback){
	var getparam = {
		action: 'get_all_user',
		data: {
			
		}
	}
	$.getJSON(url, getparam, callback);
}

function setPageContent(page, callback){
	var getparam = {
		action: 'set_page_content',
		data: {
			page: page
		}
	}
	$.get(url, getparam, callback);
}

function getSession(sessionName, callback){
	var getparam = {
		action: 'get_session',
		data: {
			name: sessionName
		}
	}
	$.get(url, getparam, callback);
}

function login(user, pass, callback){
	var getparam = {
		action: 'account_login',
		data: {
			username: user,
			password: pass
		}
	}
	$.get(url, getparam, callback);
}

function isLoggedIn(callback){
	var getparam = {
		action: 'account_is_logged_in',
		data: {
			
		}
	}
	$.get(url, getparam, callback);
}

function logout(callback){
	var getparam = {
		action: 'account_logout',
		data: {
			
		}
	}	
	$.get(url, getparam, callback);
}