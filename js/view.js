var inWhichForm = "";
var selectedRow = null;
var selectedRowa = null;
var selectedRowb = null;
var selectedRowc = null;	
var selectedRowd = null;
var selectedRowe = null;
var resched = null;
var dia = [];
var matrix;

function renderReport(){
	getAllWorkByOrder(selectedRow, function(data5){
		getFSupplierPerfByOrder(selectedRow, function(data1){
			getTSupplierPerfByOrder(selectedRow, function(data2){
				getSubcontractPerfByOrder(selectedRow, function(data3){
					getJobPerfByOrder(selectedRow, function(data4){
						str = "";
						$.each(data5, function(index, datum){
							str += "<tr id="+datum.id_work_order+" onclick='selectRow(\"work\",this)'>";
							str += "<td width='10%'>"+datum.id_work_order+"</td>";
							str += "<td width='10%'>"+datum.color+"</td>";
							str += "<td width='10%'>"+datum.destination+"</td>";
							str += "<td width='5%'>"+datum.quantity+"</td>";
							str += "<td width='15%'>"+datum.cutting_date+"</td>";
							str += "<td width='15%'>"+datum.sewing_date+"</td>";
							str += "<td width='15%'>"+datum.delivery_date+"</td>";
							str += "</tr>";
						});
						$("#work-list").html(str);
						str = "";
						$.each(data1, function(index, datum){
							if(datum.calc != null){
								str += "<tr id="+datum.id_supplier+" onclick='selectRow(\"pa\",this)'>";
								str += "<td width='30%'>"+datum.id_supplier+"</td>";
								str += "<td width='40%'>"+datum.name_of_company+"</td>";
								str += "<td width='30%'>"+datum.number_of_late_job+" ("+new Number(datum.calc).toFixed(2)*100+"%)</td>";
								str += "</tr>";
							} 
						});
						$("#pa-list").html(str);
						
						str = "";
						$.each(data2, function(index, datum){
							if(datum.calc != null){
								str += "<tr id="+datum.id_supplier+" onclick='selectRow(\"pa\",this)'>";
								str += "<td width='30%'>"+datum.po_number+"</td>";
								str += "<td width='40%'>"+datum.name_of_company+"</td>";
								str += "<td width='30%'>"+datum.number_of_late_job+" ("+new Number(datum.calc).toFixed(2)*100+"%)</td>";
								str += "</tr>";
							} 
						});
					
						$("#pb-list").html(str);
						
						str = "";
						$.each(data3, function(index, datum){
							if(datum.calc != null){
								str += "<tr id="+datum.id_subcontract+" onclick='selectRow(\"pa\",this)'>";
								str += "<td width='30%'>"+datum.subcontract_number+"</td>";
								str += "<td width='40%'>"+datum.name_of_company+"</td>";
								str += "<td width='30%'>"+datum.number_of_late_job+" ("+new Number(datum.calc).toFixed(2)*100+"%)</td>";
								str += "</tr>";
							} 
						});
						
						$("#pc-list").html(str);
						
						str = "";
						$.each(data4, function(index, datum){
							if(datum.calc != null){
								str += "<tr id="+datum.id_ws_2+" onclick='selectRow(\"pd\",this)'>";
								str += "<td width='30%'>"+datum.id_activity+"</td>";
								str += "<td width='40%'>"+datum.activity+"</td>";
								str += "<td width='30%'>"+datum.number_of_late_job+" ("+new Number(datum.calc).toFixed(2)*100+"%)</td>";
								str += "</tr>";
							} 
						});
						$("#pd-list").html(str);
					});
				});
			});
		});
	});
}

function diagram(mode, wsId, id, name, startDate, endDate, status, type, pre, suc, comment) {
	this.wsId = wsId;
	this.id = id;
	this.name = name;
	this.startDate = startDate;
	this.endDate = endDate;
	this.status = status;
	this.type = type;
	this.pre = pre;
	this.suc = suc;
	this.col = 0;
	this.row = 0;
	
	str = "";
	if(mode == 1){
		this.comment = comment;
		str += "<div id='"+this.wsId+"' class='shapes' onclick='selectDiagramJob(this,\""+comment+"\",\""+id+"\")'>";
	} else if(mode == 2){
		this.step = comment;
		str += "<div id='"+this.wsId+"' class='shapes' onclick='selectDiagramTroll(this,\""+comment+"\",\""+id+"\")'>";
	}
	str += this.id;
	if(this.status == 0){
		str += "<img width='20' src='images/notstarted.png'/>";
	} else if(this.status == 1){
		str += "<img width='20' src='images/onprogress.png'/>";
	} else if(this.status == 2){
		str += "<img width='20' src='images/waiting.png'/>";
	} else if(this.status == 3){
		str += "<img width='20' src='images/stop.png'/>";
	} else if(this.status == 4){
		str += "<img width='20' src='images/finish.png'/>";
	}
	if(mode == 2){
		str += "<label> ("+this.step+") </label>";
	}
	str += "</div>";
	this.html = str;
	
}

function viewSupplierLevel3(){
	selectedRowe = null;
	str ="";
	str +="<table class='table wide' border='0'>";
	str +="	<caption>Supplier</caption>";
	str +="		<thead>";
	str +="			<tr>";
	str +="				<th width='10%'>ID</th>";
	str +="				<th width='25%'>Name</th>";
	str +="				<th width='20%'>Delivery Date</th>";
	str +="				<th width='15%'>Time Status</th>";
	str +="				<th width='15%'>Work Status</th>";
	str +="				<th width='15%'>End Date (Reschedule)</th>";
	str +="			</tr>";
	str +="		</thead>";
	str +="	</table>";
	str +="	<div class='auto med'>";
	str +="	<table class='table wide' border='0'>";
	str +="		<tbody id='sup-list'>";
	str +="		</tbody>";
	str +="	</table>";
	str +="	</div>";
	str +=" <div id='button-sup' class='right'></div>";
	$("#list").html(str);
	
	getAllSupplierStatus(selectedRowa, selectedRowd, function(data){
		str = "";
		$.each(data, function(index, datum){
			str += "<tr id="+datum.id_supplier_order+" onclick='selectRow(\"supstat\",this)'>";
			str += "<td width='10%'>"+datum.id_supplier+"</td>";
			str += "<td width='25%'>"+datum.name_of_company+" (supplier "+datum.type+")</td>";
			str += "<td width='20%'>"+datum.delivery_date+"</td>";
			
			var timeStatus = calculateTime(new Date(), datum.work_status, new Date(datum.start_date), new Date(datum.end_date), new Date(datum.delivery_date));
			if(timeStatus == 0) {
				str += "<td width='15%'><img width='20' src='images/warning.png'/></td>";
			} else if(timeStatus == 1){
				str += "<td width='15%'><img width='20' src='images/late.png'/></td>";
			} else if(timeStatus == 2){
				str += "<td width='15%'><img width='20' src='images/ontime.png'/></td>";
			} 
			saveSupTimeStatus(datum.po_number, timeStatus, function(ret){});
			
			if(datum.work_status == 0){
				str += "<td width='15%'><img width='20' src='images/notstarted.png'/></td>";
			} else if(datum.work_status == 1){
				str += "<td width='15%'><img width='20' src='images/onprogress.png'/></td>";
			} else if(datum.work_status == 2){
				str += "<td width='15%'><img width='20' src='images/waiting.png'/></td>";
			} else if(datum.work_status == 3){
				str += "<td width='15%'><img width='20' src='images/stop.png'/></td>";
			} else if(datum.work_status == 4){
				str += "<td width='15%'><img width='20' src='images/finish.png'/></td>";
			}
			str += "<td width='15%'>"+datum.end_date+"</td>";
			str += "</tr>";
		});
		$("#sup-list").html(str);
	});
	getSession("dept", function(dept){
		if(dept=="purchasingfabric" || dept=="purchasingtrim" || dept=="admin"){
			str = "";
			str += "<button class='button' id='delete-sup'>Delete</button>";
			str += "<button class='button' id='edit-sup'>Edit</button>";
			str += "<button class='button' id='add-sup'>Add</button>";
			$("#button-sup").html(str);
			$("#delete-sup").click(function(){
				deleteSupStatAction();
			});
			$("#edit-sup").click(function(){
				editSupStatAction();
			});
			$("#add-sup").click(function(){
				addSupStatAction();
			});
		}
	});
}

function viewSubcontractLevel3(){
	selectedRowe = null;
	str ="";
	str +="<table class='table wide' border='0'>";
	str +="	<caption>Subcontract</caption>";
	str +="		<thead>";
	str +="			<tr>";
	str +="				<th width='10%'>ID</th>";
	str +="				<th width='25%'>Name</th>";
	str +="				<th width='20%'>Delivery Date</th>";
	str +="				<th width='15%'>Work Status</th>";
	str +="				<th width='15%'>Work Status</th>";
	str +="				<th width='15%'>End Date (Reschedule)</th>";
	str +="			</tr>";
	str +="		</thead>";
	str +="	</table>";
	str +="	<div class='auto med'>";
	str +="	<table class='table wide' border='0'>";
	str +="		<tbody id='sub-list'>";
	str +="		</tbody>";
	str +="	</table>";
	str +="	</div>";
	str +=" <div id='button-sub' class='right'></div>";
	$("#list").html(str);
	getAllSubcontractStatus(selectedRowa, selectedRowd, function(data){
		str = "";
		$.each(data, function(index, datum){
			str += "<tr id="+datum.id_subcontract_order+" onclick='selectRow(\"substat\",this)'>";
			str += "<td width='10%'>"+datum.id_subcontract+"</td>";
			str += "<td width='25%'>"+datum.name_of_company+"</td>";
			str += "<td width='20%'>"+datum.end_date+"</td>";
			
			var timeStatus = calculateTime(new Date(), datum.work_status, new Date(datum.start_date), new Date(datum.end_date), new Date(datum.delivery_date));
			if(timeStatus == 0) {
				str += "<td width='15%'><img width='20' src='images/warning.png'/></td>";
			} else if(timeStatus == 1){
				str += "<td width='15%'><img width='20' src='images/late.png'/></td>";
			} else if(timeStatus == 2){
				str += "<td width='15%'><img width='20' src='images/ontime.png'/></td>";
			} 
			saveSubTimeStatus(datum.po_number, timeStatus, function(ret){});
			
			if(datum.work_status == 0){
				str += "<td width='15%'><img width='20' src='images/notstarted.png'/></td>";
			} else if(datum.work_status == 1){
				str += "<td width='15%'><img width='20' src='images/onprogress.png'/></td>";
			} else if(datum.work_status == 2){
				str += "<td width='15%'><img width='20' src='images/waiting.png'/></td>";
			} else if(datum.work_status == 3){
				str += "<td width='15%'><img width='20' src='images/stop.png'/></td>";
			} else if(datum.work_status == 4){
				str += "<td width='15%'><img width='20' src='images/finish.png'/></td>";
			}
			str += "<td width='15%'>"+datum.end_date+"</td>";
			str += "</tr>";
		});
		$("#sub-list").html(str);
	});
	getSession("dept", function(dept){
		if(dept=="ppic" || dept=="produksi" || dept=="sampleroom" || dept=="admin"){
			str = "";
			str += "<button class='button' id='delete-sub'>Delete</button>";
			str += "<button class='button' id='edit-sub'>Edit</button>";
			str += "<button class='button' id='add-sub'>Add</button>";
			$("#button-sub").html(str);
			$("#delete-sub").click(function(){
				deleteSubStatAction();
			});
			$("#edit-sub").click(function(){
				editSubStatAction();
			});
			$("#add-sub").click(function(){
				addSubStatAction();
			});
		}
	});
}

function formEditSubStatConfirm(){
	var id = $("#addsubstat-sub").val();
	var status = $("#addsubstat-status").val();
	var delivery = $("#addsubstat-delivery").val();
	var number = $("#addsubstat-number").val();
	$('#form-addsubstat').dialog('close');
	editSubcontractStatus(selectedRowe, status, delivery, number, function(data){
		if(data == 1) {
			renderDiagramJob(2);
		}
		else {
			inWhichForm = 'form-editsubstat-failed';
			openDialogBox("Add failed!");
		}
	});
}

function formAddSubStatConfirm(){
	var id = $("#addsubstat-sub").val();
	var status = $("#addsubstat-status").val();
	var delivery = $("#addsubstat-delivery").val();
	var ponumber = $("#addsubstat-number").val();
	$('#form-addsubstat').dialog('close');
	addSubcontractStatus(id, status, delivery, ponumber, selectedRowa, selectedRowd, function(data){
		if(data == 1) {
			renderDiagramJob(2);
		}
		else {
			inWhichForm = 'form-addsubstat-failed';
			openDialogBox("Add failed!");
		}
	});
}

function addSubStatAction(){
	getAllSubcontract(function(sub){
		str = "";
		$.each(sub, function(index, datum){
			str += "<option value="+datum.id_subcontract+">"+datum.name_of_company+"</option>";
		});
		$("#addsubstat-sub").html(str);
		str = "";
		str += "<option value='0'>Not Started Yet</option>";
		str += "<option value='1'>On Progress</option>";
		str += "<option value='3'>Stop</option>";
		str += "<option value='4'>Finish</option>";
		$("#addsubstat-status").html(str);
		$("#addsubstat-delivery").val('0000-00-00');
		$("#addsubstat-number").val(0);
		inWhichForm = 'form-addsubstat';
		$("#form-addsubstat").dialog('open');
	});
}

function deleteSubStatAction(){
	if(selectedRowe){
		inWhichForm = 'form-deletesubstat';
		$("#confirm-text").html('Are you sure to delete?');
		$("#dialog-confirm").dialog('open');
	}
}

function editSubStatAction(){
	if(selectedRowe){
		str = "";
		str += "<option value='0'>Not Started Yet</option>";
		str += "<option value='1'>On Progress</option>";
		str += "<option value='3'>Stop</option>";
		str += "<option value='4'>Finish</option>";
		$("#addsubstat-status").html(str);
		getSubcontractStatus(selectedRowe, function(data){
			$("#addsubstat-sub").html("<option value="+data.id_subcontract+">"+data.name_of_company+"</option>");
			$("#addsubstat-status").val(data.work_status);
			$("#addsubstat-delivery").val(data.end_date);
			$("#addsubstat-number").val(data.subcontract_number);
			inWhichForm = 'form-editsubstat';
			$("#form-addsubstat").dialog('open');
		});
	}
}

function formEditSupStatConfirm(){
	var id = $("#addsupstat-sup").val();
	var status = $("#addsupstat-status").val();
	var delivery = $("#addsupstat-delivery").val();
	var ponumber = $("#addsupstat-ponumber").val();
	$('#form-addsupstat').dialog('close');
	editSupplierStatus(selectedRowe, status, delivery, ponumber, function(data){
		if(data == 1) {
			renderDiagramJob(1);
		}
		else {
			inWhichForm = 'form-editsupstat-failed';
			openDialogBox("Add failed!");
		}
	});
}

function formAddSupStatConfirm(){
	var id = $("#addsupstat-sup").val();
	var status = $("#addsupstat-status").val();
	var delivery = $("#addsupstat-delivery").val();
	var ponumber = $("#addsupstat-ponumber").val();
	$('#form-addsupstat').dialog('close');
	addSupplierStatus(id, status, delivery, ponumber, selectedRowa, selectedRowd, function(data){
		if(data == 1) {
			renderDiagramJob(1);
		}
		else {
			inWhichForm = 'form-addsupstat-failed';
			openDialogBox("Add failed!");
		}
	});
}

function addSupStatAction(){
	getAllSupplier(function(sup){
		str = "";
		$.each(sup, function(index, datum){
			str += "<option value="+datum.id_supplier+">"+datum.name_of_company+"</option>";
		});
		$("#addsupstat-sup").html(str);
		str = "";
		str += "<option value='0'>Not Started Yet</option>";
		str += "<option value='1'>On Progress</option>";
		str += "<option value='3'>Stop</option>";
		str += "<option value='4'>Finish</option>";
		$("#addsupstat-status").html(str);
		$("#addsupstat-delivery").val('0000-00-00');
		$("#addsupstat-ponumber").val(0);
		inWhichForm = 'form-addsupstat';
		$("#form-addsupstat").dialog('open');
	});
}

function deleteSupStatAction(){
	if(selectedRowe){
		inWhichForm = 'form-deletesupstat';
		$("#confirm-text").html('Are you sure to delete?');
		$("#dialog-confirm").dialog('open');
	}
}

function editSupStatAction(){
	if(selectedRowe){
		str = "";
		str += "<option value='0'>Not Started Yet</option>";
		str += "<option value='1'>On Progress</option>";
		str += "<option value='3'>Stop</option>";
		str += "<option value='4'>Finish</option>";
		$("#addsupstat-status").html(str);
		getSupplierStatus(selectedRowe, function(data){
			$("#addsupstat-sup").html("<option value="+data.id_supplier+">"+data.name_of_company+"</option>");
			$("#addsupstat-status").val(data.work_status);
			$("#addsupstat-delivery").val(data.delivery_date);
			$("#addsupstat-ponumber").val(data.po_number);
			inWhichForm = 'form-editsupstat';
			$("#form-addsupstat").dialog('open');
		});
	}
}

function selectDiagramJob(id, comment, act){
	$("#diagram div").css('background',null);
	id.style.background = "#C6C6C6";
	selectedRowd = id.id;
	
	if(act == "01.03.01" || act == "02.07.02" || act == "02.08.01" || act == "02.08.03" || act == "02.08.04" ||
	   act == "03.03.02" || act == "01.06.01" || act == "02.05.04" || act == "02.06.01" || act == "02.06.03" ||
	   act == "02.06.04" || act == "03.04.02"){
		viewSupplierLevel3();
	} else if(act == "01.02.04" || act == "01.02.07" || act == "02.02.04" || act == "02.02.07" || act == "03.01.07" ||
		      act == "03.01.10" || act == "05.01.05" || act == "05.03.03"){
		viewSubcontractLevel3();
	} else {
		$("#list").html("");
	}
	$("#comment").val(comment);
}

function selectDiagramTroll(id, comment, act){
	$("#diagram div").css('background',null);
	id.style.background = "#C6C6C6";
	selectedRowd = id.id;
	
	if(act == "01.03.01" || act == "02.07.02" || act == "02.08.01" || act == "02.08.03" || act == "02.08.04" ||
	   act == "03.03.02" || act == "01.06.01" || act == "02.05.04" || act == "02.06.01" || act == "02.06.03" ||
	   act == "02.06.04" || act == "03.04.02"){
		viewSupplierLevel3();
	} else if(act == "01.02.04" || act == "01.02.07" || act == "02.02.04" || act == "02.02.07" || act == "03.01.07" ||
		      act == "03.01.10" || act == "05.01.05" || act == "05.03.03"){
		viewSubcontractLevel3();
	} else {
		$("#list").html("");
	}
	$("#comment").val(comment);
}

function initLoginButton(data){
	if(data == 0){
		$('#menu-login').html('Log In');
		$('#menu-login').click(function() {
			$('#form-login').dialog('open');
		});
	} else if(data == 1){
		$('#menu-login').html('Log Out');
		$('#menu-login').click(function() {
			logout(function(){
				window.location = ".";
			});
		});
		getSession("username", function(user){
			getSession("dept", function(dept){
				str = "";
				str += "Welcome " + user + " [<i>" + dept + "</i>]";
				$("#welcome").html(str);
			});
		});
	}
}

function dateTime() {
	var now      = new Date();
	var day      = new Array("Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday");
	var d        = now.getDay();
	var date     = (now.getDate() < 10) ? "0" + now.getDate() : now.getDate();
	var month    = new Array("January","February","March","April","May","June","July","August","September","October","November","December");
	var m        = now.getMonth();
	var year     = now.getFullYear();
	var hh       = (now.getHours() < 10) ? "0" + now.getHours() : now.getHours(); 
	var mm       = (now.getMinutes() < 10) ? "0" + now.getMinutes() : now.getMinutes(); 
	var ss       = (now.getSeconds() < 10) ? "0" + now.getSeconds() : now.getSeconds(); 
	var content = day[d] + ", " + month[m] + " " + date + ", " + year + " " + hh + ":" + mm + ":" + ss;
	$("#menu-date").html(content);
	setTimeout("dateTime()",1000);
}

function formEditTargetConfirm(){
	var start = $("#edittarget-start").val();
	var end = $("#edittarget-end").val();
	$('#form-edittarget').dialog('close');
	editTarget(selectedRow, start, end, function(data){
		if(data == 1) {
			toolboxAction("t31");
		}
		else {
			inWhichForm = 'form-edittarget-failed';
			openDialogBox("Edit failed!");
		}
	});
}

function formEditWorkConfirm(){
	var order = $("#addwork-order").val();
	var dest = $("#addwork-dest").val();
	var quantity = $("#addwork-quantity").val();
	var cutting = $("#addwork-cutting").val();
	var sewing = $("#addwork-sewing").val();
	var delivery = $("#addwork-delivery").val();
	$('#form-addwork').dialog('close');
	editWork(selectedRow, order, dest, quantity, cutting, sewing, delivery, function(data){
		if(data == 1) {
			toolboxAction("t32");
		}
		else {
			inWhichForm = 'form-editwork-failed';
			openDialogBox("Edit failed!");
		}
	});
}

function formAddWorkConfirm(){
	var order = $("#addwork-order").val();
	var dest = $("#addwork-dest").val();
	var quantity = $("#addwork-quantity").val();
	var cutting = $("#addwork-cutting").val();
	var sewing = $("#addwork-sewing").val();
	var delivery = $("#addwork-delivery").val();
	$('#form-addwork').dialog('close');
	addWork(order, dest, quantity, cutting, sewing, delivery, function(data){
		if(data == 1) {
			toolboxAction("t32");
		}
		else {
			inWhichForm = 'form-addwork-failed';
			openDialogBox("Add failed!");
		}
	});
}

function formEditOrderConfirm(){
	var style = $("#addorder-style").val();
	var buyer = $("#addorder-buyer").val();
	var season = $("#addorder-season").val();
	var product = $("#addorder-product").val();
	var quantity = $("#addorder-quantity").val();
	var order = $("#addorder-order").val();
	var confirm = $("#addorder-confirm").val();
	var delivery = $("#addorder-delivery").val();
	$('#form-addorder').dialog('close');
	editOrder(selectedRow, style, buyer, season, product, quantity, order, confirm, delivery, function(data){
		if(data == 1) {
			toolboxAction("t21");
		}
		else {
			inWhichForm = 'form-editorder-failed';
			openDialogBox("Edit failed!");
		}
	});
}

function formAddOrderConfirm(){
	var style = $("#addorder-style").val();
	var buyer = $("#addorder-buyer").val();
	var season = $("#addorder-season").val();
	var product = $("#addorder-product").val();
	var quantity = $("#addorder-quantity").val();
	var order = $("#addorder-order").val();
	var confirm = $("#addorder-confirm").val();
	var delivery = $("#addorder-delivery").val();
	$('#form-addorder').dialog('close');
	addOrder(style, buyer, season, product, quantity, order, confirm, delivery, function(data){
		if(data == 1) {
			toolboxAction("t21");
		}
		else {
			inWhichForm = 'form-addorder-failed';
			openDialogBox("Add failed!");
		}
	});
}

function formEditFSupplierConfirm(){
	var name = $("#addsup-name").val();
	var address = $("#addsup-address").val();
	var email = $("#addsup-email").val();
	var phone = $("#addsup-phone").val();
	$('#form-addsup').dialog('close');
	editFSupplier(selectedRowa, name, address, email, phone, function(data){
		if(data == 1) {
			toolboxAction("t5");
		}
		else {
			inWhichForm = 'form-addsup-failed';
			openDialogBox("Edit failed!");
		}
	});
}

function formAddFSupplierConfirm(){
	var name = $("#addsup-name").val();
	var address = $("#addsup-address").val();
	var email = $("#addsup-email").val();
	var phone = $("#addsup-phone").val();
	$('#form-addsup').dialog('close');
	addFSupplier(name, address, email, phone, function(data){
		if(data == 1) {
			toolboxAction("t5");
		}
		else {
			inWhichForm = 'form-addsup-failed';
			openDialogBox("Edit failed!");
		}
	});
}

function formEditTSupplierConfirm(){
	var name = $("#addsup-name").val();
	var address = $("#addsup-address").val();
	var email = $("#addsup-email").val();
	var phone = $("#addsup-phone").val();
	$('#form-addsup').dialog('close');
	editTSupplier(selectedRowb, name, address, email, phone, function(data){
		if(data == 1) {
			toolboxAction("t5");
		}
		else {
			inWhichForm = 'form-addsup-failed';
			openDialogBox("Edit failed!");
		}
	});
}

function formAddTSupplierConfirm(){
	var name = $("#addsup-name").val();
	var address = $("#addsup-address").val();
	var email = $("#addsup-email").val();
	var phone = $("#addsup-phone").val();
	$('#form-addsup').dialog('close');
	addTSupplier(name, address, email, phone, function(data){
		if(data == 1) {
			toolboxAction("t5");
		}
		else {
			inWhichForm = 'form-addsup-failed';
			openDialogBox("Edit failed!");
		}
	});
}

function formEditSubcontractConfirm(){
	var name = $("#addsup-name").val();
	var address = $("#addsup-address").val();
	var email = $("#addsup-email").val();
	var phone = $("#addsup-phone").val();
	$('#form-addsup').dialog('close');
	editSubcontract(selectedRowc, name, address, email, phone, function(data){
		if(data == 1) {
			toolboxAction("t5");
		}
		else {
			inWhichForm = 'form-addsup-failed';
			openDialogBox("Edit failed!");
		}
	});
}

function formAddSubcontractConfirm(){
	var name = $("#addsup-name").val();
	var address = $("#addsup-address").val();
	var email = $("#addsup-email").val();
	var phone = $("#addsup-phone").val();
	$('#form-addsup').dialog('close');
	addSubcontract(name, address, email, phone, function(data){
		if(data == 1) {
			toolboxAction("t5");
		}
		else {
			inWhichForm = 'form-addsup-failed';
			openDialogBox("Edit failed!");
		}
	});
}

function formEditBuyerConfirm(){
	var name = $("#addsup-name").val();
	var address = $("#addsup-address").val();
	var email = $("#addsup-email").val();
	var phone = $("#addsup-phone").val();
	$('#form-addsup').dialog('close');
	editBuyer(selectedRowd, name, address, email, phone, function(data){
		if(data == 1) {
			toolboxAction("t5");
		}
		else {
			inWhichForm = 'form-addsup-failed';
			openDialogBox("Edit failed!");
		}
	});
}

function formAddBuyerConfirm(){
	var name = $("#addsup-name").val();
	var address = $("#addsup-address").val();
	var email = $("#addsup-email").val();
	var phone = $("#addsup-phone").val();
	$('#form-addsup').dialog('close');
	addBuyer(name, address, email, phone, function(data){
		if(data == 1) {
			toolboxAction("t5");
		}
		else {
			inWhichForm = 'form-addsup-failed';
			openDialogBox("Add failed!");
		}
	});
}

function formEditUserConfirm(){
	var newusername = $("#adduser-username").val();
	var password = $("#adduser-password").val();
	var fullname = $("#adduser-fullname").val();
	var dept = $("#adduser-dept").val();
	$('#form-adduser').dialog('close');
	editUser(selectedRow, newusername, password, fullname, dept, function(data){
		if(data == 1) {
			toolboxAction("t1");
		}
		else {
			inWhichForm = 'form-edituser-failed';
			openDialogBox("Edit failed!");
		}
	});
}

function formAddUserConfirm(){
	var username = $("#adduser-username").val();
	var password = $("#adduser-password").val();
	var fullname = $("#adduser-fullname").val();
	var dept = $("#adduser-dept").val();
	$('#form-adduser').dialog('close');
	addUser(username, password, fullname, dept, function(data){
		if(data == 1) {
			toolboxAction("t1");
		}
		else {
			inWhichForm = 'form-adduser-failed';
			openDialogBox("Add failed!");
		}
	});
}

function formLoginConfirm(){
	var bValid = true;

	bValid = bValid && checkLength($(".login-tips"),$("#login-name"),"username",0,50);
	bValid = bValid && checkLength($(".login-tips"),$("#login-pass"),"password",0,50);

	bValid = bValid && checkRegexp($(".login-tips"),$("#login-name"),/^[a-z]([0-9a-z_])+$/i,"Username may consist of a-z, 0-9, underscores, begin with a letter.");
	bValid = bValid && checkRegexp($(".login-tips"),$("#login-pass"),/^([0-9a-zA-Z])+$/,"Password field only allow : a-z 0-9");
	
	if (bValid) {
		var user = $("#login-name").val();
		var pass = $("#login-pass").val();
		$(".login-tips").text('All form fields are required.');
		$('#form-login').dialog('close');
		login(user, pass, function(data){
			if(data == 1) {
				inWhichForm = 'form-login-success';
				openDialogBox("Login successful!");
			}
			else {
				inWhichForm = 'form-login-failed';
				openDialogBox("Login failed!");
			}
		});
	}
}

function checkLength(tips,o,n,min,max) {
	if ( o.val().length > max || o.val().length < min ) {
		o.addClass('ui-state-error');
		updateTips(tips,"Length of " + n + " must be between "+min+" and "+max+".");
		return false;
	} else {
		return true;
	}
}

function checkRegexp(tips,o,regexp,n) {
	if ( !( regexp.test( o.val() ) ) ) {
		o.addClass('ui-state-error');
		updateTips(tips,n);
		return false;
	} else {
		return true;
	}
}

function updateTips(tips,t) {
	tips
		.text(t)
		.addClass('ui-state-highlight');
	setTimeout(function() {
		tips.removeClass('ui-state-highlight', 1500);
	}, 500);
}

function openDialogBox(text){
	$('#dialog-box').dialog('open');
	$('#dialog-text').html("<div style='margin-top: 50; font-weight:normal;'>"+text+"</div>");
}

function calculateTime(curDate, status, startDatePlan, endDatePlan, endDate){
	var warning = 0;
	var late = 1;
	var ontime = 2;
	var def = 0;
	if(status == 0){
		if(curDate > startDatePlan){
			return warning;
		} else {
			return ontime;
		}
	} if(status == 2){
		if(curDate > startDatePlan){
			return warning;
		} else {
			return ontime;
		}
	} else {
		if(status == 4) {
			if(endDate > endDatePlan){
				return late;
			} else {
				return ontime;
			}
		} else {
			if(curDate > endDatePlan){
				return late;
			} else {
				return ontime;
			}
		}
	}
	return def;
}

function renderDiagramTroll(state){
	if(selectedRowa){
		getSession("dept", function(dept){
			getAllWSLevel21(selectedRowa, function(data3){
				selectedRowb = null;
				
				dia = [];
				$.each(data3, function(index, datum){
					var tDia = new diagram(2, datum.id_ws_2, datum.id_activity, datum.activity, datum.start_date, datum.end_date, datum.work_status, datum.type, datum.pre, datum.suc, datum.step);
					dia.push(tDia); 
				});
				sortDiagramTroll();
				pertAlignColTableTroll();
				pertAlignRowTableTroll();
				pertAlignStepTableTroll();
				
				var maxCol = getMaxCol();
				var maxRow = getMaxRow();
				
				matrix = new Array(maxRow + 1);
				for(var row = 0; row <= maxRow; row++){
					matrix[row] = new Array(maxCol + 1);
					for(var col = 0; col <= maxCol; col++){
						matrix[row][col] = "";
					}
				}
				
				$.each(dia, function(index, datum){
					//alert(datum.row + "-" + datum.col + ":" + matrix[datum.row][datum.col]);
					matrix[datum.row][datum.col] = datum.html;
				});
				
				str = "";
				str += "<table border='0'>";
				for(var row = 0; row <= maxRow; row++){
					str += "<tr>";
					for(var col = 0; col <= maxCol; col++){
						str += "<td>";
						//str += row + "-" + col;
						str += matrix[row][col];
						str += "</td>";
					}
					str += "</tr>";
				}
				str += "</table>";
				$("#diagram").html(str);
				if(dept=="ppic" || dept=="admin"){
					str = "";
					str += "<button class='button' id='resched'>Resched.</button>";
					$("#button").html(str);
					$("#resched").click(function(){
						rescheduleAction();
					});
				}
			});
		});
	}
}

function formReschedConfirm(){
	var id = $("#addsubstat-sub").val();
	var i = 0;
	var sum = 0;
	var max = 0;
	$.each(resched, function(index, datum){
		datum.day = $("#step"+i).val();
		if(i == 0){
			max = datum.max;
		}
		sum += new Number(datum.day);
		i++;
	});
	if(sum == max){
		$('#form-resched').dialog('close');
		setReschedList(resched, function(data){
			if(data == 1) {
				inWhichForm = 'form-resched-success';
				openDialogBox("Reschedule success!");
			}
			else {
				inWhichForm = 'form-resched-failed';
				openDialogBox("Reschedule failed!");
			}
		});
	} else {
		openDialogBox("Total days "+sum+" is not "+max);
	}
}

function rescheduleAction(){
	if(selectedRowa){
		resched = null;
		getReschedList(selectedRowa, function(data){
			resched = data;
			var str = "";
			var i = 0;
			$.each(data, function(index, datum){
				str += "<tr>";
				str += "<td width='80%'>"+datum.list+"</td>";
				str += "<td width='20%'><input width='20' id='step"+i+"' value='"+datum.day+"'></input></td>";
				str += "</tr>";
				i++;
			});
			$("#resched-list").html(str);
			$("#form-resched").dialog('open');
		});
	}
}

function renderDiagramJob(state){
	if(selectedRowb){
		setPageContent("jobdetail", function(data2){
			$("#content").html(data2);
			getAllWSLevel2(selectedRowb, function(data3){
				getAllWSLevel3(selectedRowb, function(data4){
					if(state == 1){
						viewSupplierLevel3();
					} else if(state == 2){
						viewSubcontractLevel3();
					} else {
						selectedRowc = null;
						selectedRowd = null;
					}
					str = "";
					$.each(data3, function(index, datum){
						str += "<tr id="+datum.id_ws_2+" onclick='selectRow(\"job2\",this)'>";
						str += "<td width='5%'>"+datum.id_activity+"</td>";
						str += "<td width='29%'>"+datum.activity+"</td>";
						str += "<td width='11%'>"+datum.start_date_plan+"</td>";
						str += "<td width='11%'>"+datum.end_date_plan+"</td>";
						str += "<td width='11%'>"+datum.start_date+"</td>";
						str += "<td width='11%'>"+datum.end_date+"</td>";
						str += "<td width='6%'>"+((new Date(datum.end_date_plan) - new Date())/(1000*60*60*24)).toFixed(0)+"</td>";
						var timeStatus = calculateTime(new Date(), datum.work_status, new Date(datum.start_date_plan), new Date(datum.end_date_plan), new Date(datum.end_date));
						if(timeStatus == 0) {
							str += "<td width='8%'><img width='20' src='images/warning.png'/></td>";
						} else if(timeStatus == 1){
							str += "<td width='8%'><img width='20' src='images/late.png'/></td>";
						} else if(timeStatus == 2){
							str += "<td width='8%'><img width='20' src='images/ontime.png'/></td>";
						} 
						saveTimeStatus(datum.id_ws_2, timeStatus, function(ret){});
						if(datum.work_status == 0){
							str += "<td width='8%'><img width='20' src='images/notstarted.png'/></td>";
						} else if(datum.work_status == 1){
							str += "<td width='8%'><img width='20' src='images/onprogress.png'/></td>";
						} else if(datum.work_status == 2){
							str += "<td width='8%'><img width='20' src='images/waiting.png'/></td>";
						} else if(datum.work_status == 3){
							str += "<td width='8%'><img width='20' src='images/stop.png'/></td>";
						} else if(datum.work_status == 4){
							str += "<td width='8%'><img width='20' src='images/finish.png'/></td>";
						}
						
						str += "</tr>";
					});
					$("#job2-list").html(str);
					
					dia = [];
					$.each(data4, function(index, datum){
						var tDia = new diagram(1, datum.id_ws_3, datum.id_activity, datum.activity, datum.start_date, datum.end_date, datum.work_status, datum.type, datum.pre, datum.suc, datum.comment);
						dia.push(tDia); 
					});
					sortDiagramJob();
					pertAlignColTableJob();
					pertAlignRowTableJob();
					
					var maxCol = getMaxCol();
					var maxRow = getMaxRow();
					
					matrix = new Array(maxRow + 1);
					for(var row = 0; row <= maxRow; row++){
						matrix[row] = new Array(maxCol + 1);
						for(var col = 0; col <= maxCol; col++){
							matrix[row][col] = "";
						}
					}
					
					$.each(dia, function(index, datum){
						//alert(datum.row + "-" + datum.col + ":" + matrix[datum.row][datum.col]);
						matrix[datum.row][datum.col] = datum.html;
					});
					
					str = "";
					str += "<table border='0'>";
					for(var row = 0; row <= maxRow; row++){
						str += "<tr>";
						for(var col = 0; col <= maxCol; col++){
							str += "<td>";
							//str += row + "-" + col;
							str += matrix[row][col];
							str += "</td>";
						}
						str += "</tr>";
					}
					str += "</table>";
					$("#diagram").html(str);
					
					$("#change-status").click(function(){
						if(selectedRowd){
							changeWs(selectedRowd, $("#status-opt").val(), $("#comment").val(), function(data){
								if(data == 0){
									openDialogBox('failed');
								} else {
									renderDiagramJob(false);
								}
							});
						}
					});
				});
			});
		});
	}
}

function toolboxAction(id){
	if(id == "t1"){
		selectedRow = null;
		getSession("dept", function(dept){
			if(dept=="admin"){
				setPageContent("user", function(data){
					$("#content").html(data);
					getAllUser(function(data1){
						str = "";
						$.each(data1, function(index, datum){
							str += "<tr id="+datum.username+" onclick='selectRow(\"user\",this)'>";
							str += "<td width='15%'>"+datum.username+"</td>";
							str += "<td width='35%'>"+datum.password+"</td>";
							str += "<td width='25%'>"+datum.fullname+"</td>";
							str += "<td width='25%'>"+datum.dept+"</td>";
							str += "</tr>";
						});
						
						$("#user-list").html(str);
						$("#delete-user").click(function(){
							deleteUserAction();
						});
						$("#edit-user").click(function(){
							editUserAction();
						});
						$("#add-user").click(function(){
							addUserAction();
						});
					});
				});
			}
		});
	} else if(id == "t2"){
		$("#content").html("<h1>Monitoring</h1>");
	} else if(id == "t3"){
		$("#content").html("<h1>Scheduling</h1>");
	} else if(id == "t4"){
		selectedRowa = null;
		selectedRowb = null;
		getSession("dept", function(dept){
			if(dept!=0) {
				setPageContent("controlling", function(data){
					$("#content").html(data);
					getAllOrderLate(function(data1){
						str = "";
						$.each(data1, function(index, datum){
							str += "<tr id="+datum.id_order+" onclick='selectRow(\"t0\",this)'>";
							str += "<td width='10%'>"+datum.id_order+"</td>";
							str += "<td width='10%'>"+datum.style+"</td>";
							str += "<td width='20%'>"+datum.name_of_company+"</td>";
							str += "<td width='20%'>"+datum.delivery_date+"</td>";
							if(datum.work_status == 0){
								str += "<td width='25%'><img width='20' src='images/notstarted.png'/></td>";
							} else if(datum.work_status == 1){
								str += "<td width='25%'><img width='20' src='images/onprogress.png'/></td>";
							} else if(datum.work_status == 2){
								str += "<td width='25%'><img width='20' src='images/waiting.png'/></td>";
							} else if(datum.work_status == 3){
								str += "<td width='25%'><img width='20' src='images/stop.png'/></td>";
							} else if(datum.work_status == 4){
								str += "<td width='25%'><img width='20' src='images/finish.png'/></td>";
							}
							if(datum.late_job != null){
								str += "<td width='20%'>"+datum.late_job+" day(s) on activity "+datum.id_late+"</td>";
							} else {
								str += "<td width='20%'>-</td>";
							}
							str += "</tr>";
						});
						$("#job0-list").html(str);
					});
				});
			}
		});
	} else if(id == "t6"){
		selectedRow = null;
		selectedRowa = null;
		selectedRowb = null;
		selectedRowc = null;
		selectedRowd = null;
		getSession("dept", function(dept){
			if(dept!=0) {
				setPageContent("performance", function(data){
					$("#content").html(data);
					getFSupplierPerf(function(data1){
						getTSupplierPerf(function(data2){
							getSubcontractPerf(function(data3){
								getJobPerf(function(data4){
									str = "";
									$.each(data1, function(index, datum){
										if(datum.calc != null){
											str += "<tr id="+datum.id_supplier+" onclick='selectRow(\"pa\",this)'>";
											str += "<td width='20%'>"+datum.name_of_company+"</td>";
											str += "<td width='20%'>"+datum.number_of_late_job+" ("+new Number(datum.calc).toFixed(2)*100+"%)</td>";
											str += "<td width='20%'>"+datum.number_of_ontime_job+" ("+(1 - new Number(datum.calc)).toFixed(2)*100+"%)</td>";
											str += "<td width='20%'>"+datum.total+"</td>";
											if(datum.calc <= 0.10){
												str += "<td width='20%'>Outstanding</td>";
											} else if(datum.calc > 0.10 && datum.calc <= 0.30){
												str += "<td width='20%'>Good</td>";
											} else if(datum.calc > 0.30 && datum.calc <= 0.50){
												str += "<td width='20%'>Average</td>";
											} else if(datum.calc > 0.50 && datum.calc <= 0.70){
												str += "<td width='20%'>Fair</td>";
											} else if(datum.calc > 0.70){
												str += "<td width='20%'>Poor</td>";
											} 
											str += "</tr>";
										} 
									});
									$("#pa-list").html(str);
									
									str = "";
									$.each(data2, function(index, datum){
										if(datum.calc != null){
											str += "<tr id="+datum.id_supplier+" onclick='selectRow(\"pb\",this)'>";
											str += "<td width='20%'>"+datum.name_of_company+"</td>";
											str += "<td width='20%'>"+datum.number_of_late_job+" ("+new Number(datum.calc).toFixed(2)*100+"%)</td>";
											str += "<td width='20%'>"+datum.number_of_ontime_job+" ("+(1 - new Number(datum.calc)).toFixed(2)*100+"%)</td>";
											str += "<td width='20%'>"+datum.total+"</td>";
											if(datum.calc <= 0.10){
												str += "<td width='20%'>Outstanding</td>";
											} else if(datum.calc > 0.10 && datum.calc <= 0.30){
												str += "<td width='20%'>Good</td>";
											} else if(datum.calc > 0.30 && datum.calc <= 0.50){
												str += "<td width='20%'>Average</td>";
											} else if(datum.calc > 0.50 && datum.calc <= 0.70){
												str += "<td width='20%'>Fair</td>";
											} else if(datum.calc > 0.70){
												str += "<td width='20%'>Poor</td>";
											} 
											str += "</tr>";
										} 
									});
								
									$("#pb-list").html(str);
									
									str = "";
									$.each(data3, function(index, datum){
										if(datum.calc != null){
											str += "<tr id="+datum.id_subcontract+" onclick='selectRow(\"pc\",this)'>";
											str += "<td width='20%'>"+datum.name_of_company+"</td>";
											str += "<td width='20%'>"+datum.number_of_late_job+" ("+new Number(datum.calc).toFixed(2)*100+"%)</td>";
											str += "<td width='20%'>"+datum.number_of_ontime_job+" ("+(1 - new Number(datum.calc)).toFixed(2)*100+"%)</td>";
											str += "<td width='20%'>"+datum.total+"</td>";
											if(datum.calc <= 0.10){
												str += "<td width='20%'>Outstanding</td>";
											} else if(datum.calc > 0.10 && datum.calc <= 0.30){
												str += "<td width='20%'>Good</td>";
											} else if(datum.calc > 0.30 && datum.calc <= 0.50){
												str += "<td width='20%'>Average</td>";
											} else if(datum.calc > 0.50 && datum.calc <= 0.70){
												str += "<td width='20%'>Fair</td>";
											} else if(datum.calc > 0.70){
												str += "<td width='20%'>Poor</td>";
											} 
											str += "</tr>";
										} 
									});
									
									$("#pc-list").html(str);
									
									str = "";
									$.each(data4, function(index, datum){
										if(datum.calc != null){
											str += "<tr id="+datum.id_ws_2+" onclick='selectRow(\"pd\",this)'>";
											str += "<td width='20%'>"+datum.id_activity+"</td>";
											str += "<td width='20%'>"+datum.number_of_late_job+" ("+new Number(datum.calc).toFixed(2)*100+"%)</td>";
											str += "<td width='20%'>"+datum.number_of_ontime_job+" ("+(1 - new Number(datum.calc)).toFixed(2)*100+"%)</td>";
											str += "<td width='20%'>"+datum.total+"</td>";
											if(datum.calc <= 0.10){
												str += "<td width='20%'>Outstanding</td>";
											} else if(datum.calc > 0.10 && datum.calc <= 0.30){
												str += "<td width='20%'>Good</td>";
											} else if(datum.calc > 0.30 && datum.calc <= 0.50){
												str += "<td width='20%'>Average</td>";
											} else if(datum.calc > 0.50 && datum.calc <= 0.70){
												str += "<td width='20%'>Fair</td>";
											} else if(datum.calc > 0.70){
												str += "<td width='20%'>Poor</td>";
											} 
											str += "</tr>";
										} 
									});
									$("#pd-list").html(str);
								});
							});
						});
					});
				});
			}
		});
	} else if(id == "t7"){
		setPageContent("report", function(data){
			getAllOrder(function(data1){
				$("#content").html(data);
				str = "";
				$.each(data1, function(index, datum){
					str += "<tr id="+datum.id_order+" onclick='selectRow(\"r1\",this)'>";
					str += "<td width='4%'>"+datum.id_order+"</td>";
					str += "<td width='8%'>"+datum.style+"</td>";
					str += "<td width='10%'>"+datum.name_of_company+"</td>";
					str += "<td width='10%'>"+datum.season+"</td>";
					str += "<td width='10%'>"+datum.product+"</td>";
					str += "<td width='7%'>"+datum.quantity+"</td>";
					str += "<td width='11%'>"+datum.order_date+"</td>";
					str += "<td width='11%'>"+datum.confirm_date+"</td>";
					str += "<td width='11%'>"+datum.delivery_date+"</td>";
					str += "<td width='9%'>"+datum.start_date+"</td>";
					str += "<td width='9%'>"+datum.end_date+"</td>";
					str += "</tr>";
				});
				$("#order-list").html(str);
			});
		});
	} else if(id == "t11"){
		selectedRow = null;
		$("#form-chgpwd").dialog('open');
	} else if(id == "t21"){
		selectedRow = null;
		getSession("dept", function(dept){
			if(dept!=0) {
				setPageContent("order", function(data){
					$("#content").html(data);
					getAllOrder(function(data1){
						str = "";
						$.each(data1, function(index, datum){
							str += "<tr id="+datum.id_order+" onclick='selectRow(\"order\",this)'>";
							str += "<td width='4%'>"+datum.id_order+"</td>";
							str += "<td width='8%'>"+datum.style+"</td>";
							str += "<td width='10%'>"+datum.name_of_company+"</td>";
							str += "<td width='10%'>"+datum.season+"</td>";
							str += "<td width='10%'>"+datum.product+"</td>";
							str += "<td width='7%'>"+datum.quantity+"</td>";
							str += "<td width='11%'>"+datum.order_date+"</td>";
							str += "<td width='11%'>"+datum.confirm_date+"</td>";
							str += "<td width='11%'>"+datum.delivery_date+"</td>";
							str += "<td width='9%'><button class='button' onclick='viewSupplier("+datum.id_order+")'>view</button></td>";
							str += "<td width='9%'><button class='button' onclick='viewSubcontract("+datum.id_order+")'>view</button></td>";
							str += "</tr>";
						});
						
						$("#order-list").html(str);
						$("#button-order").html("");
						if(dept=="marketing" || dept=="admin" || dept=="merchandiser"){
							str = "";
							str += "<button class='button' id='delete-order'>Delete</button>";
							$("#button-order").append(str);
							$("#delete-order").click(function(){
								deleteOrderAction();
							});
						}
						if(dept=="marketing" || dept=="admin" || dept=="merchandiser"){
							str = "";
							str += "<button class='button' id='edit-order1'>Edit Ord.</button>";
							$("#button-order").append(str);
							$("#edit-order1").click(function(){
								editOrderAction1();
							});
						}
						if(dept=="purchasingtrim" || dept=="purchasingfabric" || dept=="admin"){
							str = "";
							str += "<button class='button' id='edit-order2'>Edit Sup.</button>";
							$("#button-order").append(str);
							$("#edit-order2").click(function(){
								editOrderAction2();
							});
						}
						if(dept=="ppic" || dept=="produksi" || dept=="sampleroom" || dept=="admin"){
							str = "";
							str += "<button class='button' id='edit-order3'>Edit Sub.</button>";
							$("#button-order").append(str);
							$("#edit-order3").click(function(){
								editOrderAction3();
							});
						}
						if(dept=="marketing" || dept=="admin"){
							str = "";
							str += "<button class='button' id='add-order'>Add</button>";
							$("#button-order").append(str);
							$("#add-order").click(function(){
								addOrderAction();
							});
						}
					});
				});
			}
		});
	} else if(id == "t22"){
		selectedRowa = null;
		selectedRowb = null;
		getSession("dept", function(dept){
			if(dept!=0) {
				setPageContent("job", function(data){
					$("#content").html(data);
					getAllOrder(function(data1){
						str = "";
						$.each(data1, function(index, datum){
							str += "<tr id="+datum.id_order+" onclick='selectRow(\"job0\",this)'>";
							str += "<td width='20%'>"+datum.id_order+"</td>";
							str += "<td width='20%'>"+datum.style+"</td>";
							str += "<td width='20%'>"+datum.name_of_company+"</td>";
							str += "<td width='20%'>"+datum.delivery_date+"</td>";
							if(datum.work_status == 0){
								str += "<td width='25%'><img width='20' src='images/notstarted.png'/></td>";
							} else if(datum.work_status == 1){
								str += "<td width='25%'><img width='20' src='images/onprogress.png'/></td>";
							} else if(datum.work_status == 2){
								str += "<td width='25%'><img width='20' src='images/waiting.png'/></td>";
							} else if(datum.work_status == 3){
								str += "<td width='25%'><img width='20' src='images/stop.png'/></td>";
							} else if(datum.work_status == 4){
								str += "<td width='25%'><img width='20' src='images/finish.png'/></td>";
							}
							str += "</tr>";
						});
						$("#job0-list").html(str);
					});
					$("#see-more").click(function(){
						renderDiagramJob(false);
					});
				});
			}
		});
	} else if(id == "t5"){
		selectedRow = null;
		selectedRowa = null;
		selectedRowb = null;
		selectedRowc = null;
		selectedRowd = null;
		getSession("dept", function(dept){
			if(dept!=0) {
				setPageContent("profile", function(data){
					$("#content").html(data);
					getAllFSupplier(function(data1){
						getAllTSupplier(function(data2){
							getAllSubcontract(function(data3){
								getAllBuyer(function(data4){
									str = "";
									$.each(data1, function(index, datum){
										str += "<tr id="+datum.id_supplier+" onclick='selectRow(\"pa\",this)'>";
										str += "<td width='15%'>"+datum.name_of_company+"</td>";
										str += "<td width='35%'>"+datum.address+"</td>";
										str += "<td width='25%'>"+datum.email+"</td>";
										str += "<td width='25%'>"+datum.phone+"</td>";
										str += "</tr>";
									});
									$("#pa-list").html(str);
									
									if(dept=="purchasingfabric" || dept=="admin"){
										str = "";
										str += "<button class='button' id='delete-pa'>Delete</button>";
										str += "<button class='button' id='edit-pa'>Edit</button>";
										str += "<button class='button' id='add-pa'>Add</button>";
										$("#button-pa").html(str);
										$("#delete-pa").click(function(){
											deleteFSupplierAction();
										});
										$("#edit-pa").click(function(){
											editFSupplierAction();
										});
										$("#add-pa").click(function(){
											addFSupplierAction();
										});
									}
									
									str = "";
									$.each(data2, function(index, datum){
										str += "<tr id="+datum.id_supplier+" onclick='selectRow(\"pb\",this)'>";
										str += "<td width='15%'>"+datum.name_of_company+"</td>";
										str += "<td width='35%'>"+datum.address+"</td>";
										str += "<td width='25%'>"+datum.email+"</td>";
										str += "<td width='25%'>"+datum.phone+"</td>";
										str += "</tr>";
									});
									
									$("#pb-list").html(str);
									
									if(dept=="purchasingtrim" || dept=="admin"){
										str = "";
										str += "<button class='button' id='delete-pb'>Delete</button>";
										str += "<button class='button' id='edit-pb'>Edit</button>";
										str += "<button class='button' id='add-pb'>Add</button>";
										$("#button-pb").html(str);
										$("#delete-pb").click(function(){
											deleteTSupplierAction();
										});
										$("#edit-pb").click(function(){
											editTSupplierAction();
										});
										$("#add-pb").click(function(){
											addTSupplierAction();
										});
									}
									
									str = "";
									$.each(data3, function(index, datum){
										str += "<tr id="+datum.id_subcontract+" onclick='selectRow(\"pc\",this)'>";
										str += "<td width='15%'>"+datum.name_of_company+"</td>";
										str += "<td width='35%'>"+datum.address+"</td>";
										str += "<td width='25%'>"+datum.email+"</td>";
										str += "<td width='25%'>"+datum.phone+"</td>";
										str += "</tr>";
									});
									
									$("#pc-list").html(str);
									
									if(dept=="produksi" || dept=="ppic" || dept=="sampleroom" || dept=="admin"){
										str = "";
										str += "<button class='button' id='delete-pc'>Delete</button>";
										str += "<button class='button' id='edit-pc'>Edit</button>";
										str += "<button class='button' id='add-pc'>Add</button>";
										$("#button-pc").html(str);
										$("#delete-pc").click(function(){
											deleteSubcontractAction();
										});
										$("#edit-pc").click(function(){
											editSubcontractAction();
										});
										$("#add-pc").click(function(){
											addSubcontractAction();
										});
									}
									
									str = "";
									$.each(data4, function(index, datum){
										str += "<tr id="+datum.id_buyer+" onclick='selectRow(\"pd\",this)'>";
										str += "<td width='15%'>"+datum.name_of_company+"</td>";
										str += "<td width='35%'>"+datum.address+"</td>";
										str += "<td width='25%'>"+datum.email+"</td>";
										str += "<td width='25%'>"+datum.phone+"</td>";
										str += "</tr>";
									});
									$("#pd-list").html(str);
									
									if(dept=="admin"){
										str = "";
										str += "<button class='button' id='delete-pd'>Delete</button>";
										str += "<button class='button' id='edit-pd'>Edit</button>";
										str += "<button class='button' id='add-pd'>Add</button>";
										$("#button-pd").html(str);
										$("#delete-pd").click(function(){
											deleteBuyerAction();
										});
										$("#edit-pd").click(function(){
											editBuyerAction();
										});
										$("#add-pd").click(function(){
											addBuyerAction();
										});
									}
								});
							});
						});
					});
				});
			}
		});
	} else if(id == "t71"){
		selectedRow = null;
		getSession("dept", function(dept){
			if(dept!=0) {
				setPageContent("process", function(data){
					$("#content").html(data);
				});
			}
		});
	} else if(id == "t31"){
		selectedRow = null;
		getSession("dept", function(dept){
			if(dept!=0) {
				setPageContent("schedule", function(data){
					getActivityLevel2(function(data1){
						$("#content").html(data);
						str = "";
						$.each(data1, function(index, datum){
							str += "<tr id="+datum.id_activity+" onclick='selectRow(\"tl\",this)'>";
							str += "<td width='20%'>"+datum.id_activity+"</td>";
							str += "<td width='20%'>"+datum.activity+"</td>";
							strPre = "";
							if(datum.pre != null){
								var j = 0
								while(j < datum.pre.length){
									strPre += datum.pre[j].id_predecessor + " ";
									j++;
								}
							}
							str += "<td width='20%'>"+strPre+"</td>";
							str += "<td width='20%'>"+datum.target_start+"</td>";
							str += "<td width='20%'>"+datum.target_end+"</td>";
							str += "</tr>";
						});
						$("#tl-list").html(str);
						
						if(dept=="admin"){
							str = "";
							str += "<button class='button' id='edit-tl'>Edit</button>";
							$("#button-tl").html(str);
							$("#edit-tl").click(function(){
								editActivityAction();
							});
						}
					});
				});
			}
		});
	} else if(id == "t32"){
		selectedRow = null;
		getSession("dept", function(dept){
			if(dept!=0) {
				setPageContent("production", function(data){
					getAllWork(function(data1){
						$("#content").html(data);
						str = "";
						$.each(data1, function(index, datum){
							str += "<tr id="+datum.id_work_order+" onclick='selectRow(\"work\",this)'>";
							str += "<td width='10%'>"+datum.id_work_order+"</td>";
							str += "<td width='10%'>"+datum.id_order+"</td>";
							str += "<td width='20%'>"+datum.destination+"</td>";
							str += "<td width='15%'>"+datum.quantity+"</td>";
							str += "<td width='15%'>"+datum.cutting_date+"</td>";
							str += "<td width='15%'>"+datum.sewing_date+"</td>";
							str += "<td width='15%'>"+datum.delivery_date+"</td>";
							str += "</tr>";
						});
						$("#work-list").html(str);
						
						if(dept=="ppic" || dept=="admin"){
							str = "";
							str += "<button class='button' id='delete-work'>Delete</button>";
							str += "<button class='button' id='edit-work'>Edit</button>";
							str += "<button class='button' id='add-work'>Add</button>";
							$("#button-work").html(str);
							$("#delete-work").click(function(){
								deleteWorkAction();
							});
							$("#edit-work").click(function(){
								editWorkAction();
							});
							$("#add-work").click(function(){
								addWorkAction();
							});
						}
					});
				});
			}
		});
	}
}

function editActivityAction(){
	if(selectedRow){
		getActivity(selectedRow, function(data){
			$("#edittarget-start").val(data.target_start);
			$("#edittarget-end").val(data.target_end);
			inWhichForm = 'form-edittarget';
			$("#form-edittarget").dialog('open');
		});
	}
}

function viewSupplier(id){
	$("#form-view").dialog({ width: 650 });
	$("#form-view").dialog({ height: 300 });
	$("#form-view").dialog("open");
	str = "";
	str += "<div class='table-container'>";
	str += "		<table class='table fix' border='0'>";
	str += "			<caption>Trim Supplier</caption>";
	str += "			<thead>";
	str += "				<tr>";
	str += "					<th width='20%'>Name</th>";
	str += "					<th width='20%'>Address</th>";
	str += "					<th width='20%'>Email</th>";
	str += "					<th width='20%'>Phone</th>";
	str += "					<th width='20%'>PO Number</th>";
	str += "				</tr>";
	str += "			</thead>";
	str += "		</table>";
	str += "		<div class='auto low'>";
	str += "		<table class='table fix' border='0'>";
	str += "			<tbody id='view-list'>";				
	str += "			</tbody>";
	str += "		</table>";
	str += "		</div>";
	str += "	</div>";
	$("#view-content").html(str);
	getSupplierOrder(id, function(data){
		str = "";
		$.each(data, function(index, datum){
			str += "<tr>";
			str += "<td width='20%'>"+datum.name_of_company+"</td>";
			str += "<td width='20%'>"+datum.address+"</td>";
			str += "<td width='20%'>"+datum.email+"</td>";
			str += "<td width='20%'>"+datum.phone+"</td>";
			str += "<td width='20%'>"+datum.po_number+"</td>";
			str += "</tr>";
		});
		$("#view-list").html(str);
	});
}

function viewSubcontract(id){
	$("#form-view").dialog({ width: 650 });
	$("#form-view").dialog({ height: 300 });
	$("#form-view").dialog("open");
	str = "";
	str += "<div class='table-container'>";
	str += "		<table class='table fix' border='0'>";
	str += "			<caption>Trim Supplier</caption>";
	str += "			<thead>";
	str += "				<tr>";
	str += "					<th width='25%'>Name</th>";
	str += "					<th width='25%'>Address</th>";
	str += "					<th width='25%'>Email</th>";
	str += "					<th width='25%'>Phone</th>";
	str += "				</tr>";
	str += "			</thead>";
	str += "		</table>";
	str += "		<div class='auto low'>";
	str += "		<table class='table fix' border='0'>";
	str += "			<tbody id='view-list'>";				
	str += "			</tbody>";
	str += "		</table>";
	str += "		</div>";
	str += "	</div>";
	$("#view-content").html(str);
	getSubcontractOrder(id, function(data){
		str = "";
		$.each(data, function(index, datum){
			str += "<tr>";
			str += "<td width='15%'>"+datum.name_of_company+"</td>";
			str += "<td width='35%'>"+datum.address+"</td>";
			str += "<td width='25%'>"+datum.email+"</td>";
			str += "<td width='25%'>"+datum.phone+"</td>";
			str += "</tr>";
		});
		$("#view-list").html(str);
	});
}

function addWorkAction(){
	getAllOrder(function(ord){
		str = "";
		$.each(ord, function(index, datum){
			str += "<option value="+datum.id_order+">"+datum.id_order+"</option>";
		});
		$("#addwork-order").html(str);
		$("#addwork-order").val();
		$("#addwork-dest").val();
		$("#addwork-quantity").val(0);
		$("#addwork-cutting").val('0000-00-00');
		$("#addwork-sewing").val('0000-00-00');
		$("#addwork-delivery").val('2011-02-02');
		inWhichForm = 'form-addwork';
		$("#form-addwork").dialog('open');
	});
}

function deleteWorkAction(){
	if(selectedRow){
		inWhichForm = 'form-deletework';
		$("#confirm-text").html('Are you sure to delete?');
		$("#dialog-confirm").dialog('open');
	}
}

function editWorkAction(){
	if(selectedRow){
		getWork(selectedRow, function(data){
			getAllOrder(function(ord){
				str = "";
				$.each(ord, function(index, datum){
					str += "<option value="+datum.id_order+">"+datum.id_order+"</option>";
				});
				$("#addwork-order").html(str);
				$("#addwork-order").val(data.id_order);
				$("#addwork-dest").val(data.destination);
				$("#addwork-quantity").val(data.quantity);
				$("#addwork-cutting").val(data.cutting_date);
				$("#addwork-sewing").val(data.sewing_date);
				$("#addwork-delivery").val(data.delivery_date);
				inWhichForm = 'form-editwork';
				$("#form-addwork").dialog('open');
			});
		});
	}
}

function editOrderAction1(){
	if(selectedRow){
		getOrder(selectedRow, function(data){
			getAllBuyer(function(data1){
				str = "";
				$.each(data1, function(index, datum){
					str += "<option value='"+datum.id_buyer+"'>"+datum.name_of_company+"</option>";
				});
				$("#addorder-buyer").html(str);
				$("#addorder-style").val(data.style);
				$("#addorder-buyer").val(data.buyer);
				$("#addorder-season").val(data.season);
				$("#addorder-product").val(data.product);
				$("#addorder-quantity").val(data.quantity);
				$("#addorder-order").val(data.order_date);
				$("#addorder-confirm").val(data.confirm_date);
				$("#addorder-delivery").val(data.delivery_date);
				inWhichForm = 'form-editorder';
				$("#form-addorder").dialog('open');
			});
		});
	}
}

function editOrderAction2(){
	if(selectedRow){
		getSupplierOrder(selectedRow, function(data){
			getSupplierNotOrder(selectedRow, function(data1){
				str = "";
				$.each(data, function(index, datum){
					str += "<tr>";
					str += "<td width='30%'>"+datum.name_of_company+"</td>";
					str += "<td width='30%'>"+datum.type+"</td>";
					str += "<td width='40%'><button class='button'>Delete</button></td>";
					str += "</tr>";
				});
				$.each(data1, function(index, datum){
					str += "<tr>";
					str += "<td width='30%'>"+datum.name_of_company+"</td>";
					str += "<td width='30%'>"+datum.type+"</td>";
					str += "<td width='40%'><button class='button'>Add</button></td>";
					str += "</tr>";
				});
				$("#sup-list").html(str);
				$("#form-addsuporder").dialog({ width: 400 });
				$("#form-addsuporder").dialog({ height: 265 });
				$("#form-addsuporder").dialog("open");
			});
		});
	}
}

function editOrderAction3(){
	if(selectedRow){
		getSubcontractOrder(selectedRow, function(data){
			getSubcontractNotOrder(selectedRow, function(data1){
				str = "";
				$.each(data, function(index, datum){
					str += "<tr>";
					str += "<td width='30%'>"+datum.name_of_company+"</td>";
					str += "<td width='40%'><button class='button'>Delete</button></td>";
					str += "</tr>";
				});
				$.each(data1, function(index, datum){
					str += "<tr>";
					str += "<td width='30%'>"+datum.name_of_company+"</td>";
					str += "<td width='40%'><button class='button'>Add</button></td>";
					str += "</tr>";
				});
				$("#sub-list").html(str);
				$("#form-addsuborder").dialog({ width: 400 });
				$("#form-addsuborder").dialog({ height: 265 });
				$("#form-addsuborder").dialog("open");
			});
		});
	}
}

function addOrderAction(){
	getAllBuyer(function(data){
		str = "";
		$.each(data, function(index, datum){
			str += "<option value='"+datum.id_buyer+"'>"+datum.name_of_company+"</option>";
		});
		$("#addorder-buyer").html(str);
		$("#addorder-style").val('0');
		$("#addorder-season").val('');
		$("#addorder-product").val('');
		$("#addorder-quantity").val('0');
		$("#addorder-order").val('0000-00-00');
		$("#addorder-confirm").val('0000-00-00');
		$("#addorder-delivery").val('2012-12-12');
		inWhichForm = 'form-addorder';
		$("#form-addorder").dialog('open');
	});
}

function deleteOrderAction(){
	if(selectedRow){
		inWhichForm = 'form-deleteorder';
		$("#confirm-text").html('Are you sure to delete?');
		$("#dialog-confirm").dialog('open');
	}
}

function deleteBuyerAction(){
	if(selectedRowd){
		inWhichForm = 'form-deletepd';
		$("#confirm-text").html('Are you sure to delete?');
		$("#dialog-confirm").dialog('open');
	}
}

function editBuyerAction(){
	if(selectedRowd){
		getBuyer(selectedRowd, function(data){
			$("#addsup-name").val(data.name_of_company);
			$("#addsup-address").val(data.address);
			$("#addsup-email").val(data.email);
			$("#addsup-phone").val(data.phone);
			inWhichForm = 'form-editsupd';
			$("#form-addsup").dialog('open');
		});
	}
}

function addBuyerAction(){
	$("#addsup-name").val('');
	$("#addsup-address").val('');
	$("#addsup-email").val('');
	$("#addsup-phone").val('');
	inWhichForm = 'form-addsupd';
	$("#form-addsup").dialog('open');
}

function deleteSubcontractAction(){
	if(selectedRowc){
		inWhichForm = 'form-deletepc';
		$("#confirm-text").html('Are you sure to delete?');
		$("#dialog-confirm").dialog('open');
	}
}

function editSubcontractAction(){
	if(selectedRowc){
		getSubcontract(selectedRowc, function(data){
			$("#addsup-name").val(data.name_of_company);
			$("#addsup-address").val(data.address);
			$("#addsup-email").val(data.email);
			$("#addsup-phone").val(data.phone);
			inWhichForm = 'form-editsupc';
			$("#form-addsup").dialog('open');
		});
	}
}

function addSubcontractAction(){
	$("#addsup-name").val('');
	$("#addsup-address").val('');
	$("#addsup-email").val('');
	$("#addsup-phone").val('');
	inWhichForm = 'form-addsupc';
	$("#form-addsup").dialog('open');
}

function deleteTSupplierAction(){
	if(selectedRowb){
		inWhichForm = 'form-deletepb';
		$("#confirm-text").html('Are you sure to delete ?');
		$("#dialog-confirm").dialog('open');
	}
}

function editTSupplierAction(){
	if(selectedRowb){
		getSupplier(selectedRowb, function(data){
			$("#addsup-name").val(data.name_of_company);
			$("#addsup-address").val(data.address);
			$("#addsup-email").val(data.email);
			$("#addsup-phone").val(data.phone);
			inWhichForm = 'form-editsupb';
			$("#form-addsup").dialog('open');
		});
	}
}

function addTSupplierAction(){
	$("#addsup-name").val('');
	$("#addsup-address").val('');
	$("#addsup-email").val('');
	$("#addsup-phone").val('');
	inWhichForm = 'form-addsupb';
	$("#form-addsup").dialog('open');
}

function deleteFSupplierAction(){
	if(selectedRowa){
		inWhichForm = 'form-deletepa';
		$("#confirm-text").html('Are you sure to delete ?');
		$("#dialog-confirm").dialog('open');
	}
}

function editFSupplierAction(){
	if(selectedRowa){
		getSupplier(selectedRowa, function(data){
			$("#addsup-name").val(data.name_of_company);
			$("#addsup-address").val(data.address);
			$("#addsup-email").val(data.email);
			$("#addsup-phone").val(data.phone);
			inWhichForm = 'form-editsupa';
			$("#form-addsup").dialog('open');
		});
	}
}

function addFSupplierAction(){
	$("#addsup-name").val('');
	$("#addsup-address").val('');
	$("#addsup-email").val('');
	$("#addsup-phone").val('');
	inWhichForm = 'form-addsupa';
	$("#form-addsup").dialog('open');
}

function deleteUserAction(){
	if(selectedRow){
		inWhichForm = 'form-deleteuser';
		$("#confirm-text").html('Are you sure to delete \"' + selectedRow + '\"?');
		$("#dialog-confirm").dialog('open');
	}
}

function editUserAction(){
	if(selectedRow){
		getUser(selectedRow, function(data){
			$("#adduser-username").val(data.username);
			$("#adduser-password").val('');
			$("#adduser-fullname").val(data.fullname);
			$("#adduser-dept").val(data.dept);
			inWhichForm = 'form-edituser';
			$("#form-adduser").dialog('open');
		});
	}
}

function addUserAction(){
	$("#adduser-username").val('');
	$("#adduser-password").val('');
	$("#adduser-fullname").val('');
	$("#adduser-dept").val('');
	inWhichForm = 'form-adduser';
	$("#form-adduser").dialog('open');
}

function selectRow(form, id){
	if(form == "user"){
		$("#user-list tr").css('background',null);
		selectedRow = id.id;
	} else if(form == "r1"){
		$("#order-list tr").css('background',null);
		selectedRow = id.id;
		renderReport();
	} else if(form == "substat"){
		$("#sub-list tr").css('background',null);
		selectedRowe = id.id;
	} else if(form == "supstat"){
		$("#sup-list tr").css('background',null);
		selectedRowe = id.id;
	} else if(form == "t0"){
		$("#job0-list tr").css('background',null);
		selectedRowa = id.id;
		renderDiagramTroll(false);
	} else if(form == "tl"){
		$("#tl-list tr").css('background',null);
		selectedRow = id.id;
	} else if(form == "pa"){
		$("#pa-list tr").css('background',null);
		selectedRowa = id.id;
	} else if(form == "pb"){
		$("#pb-list tr").css('background',null);
		selectedRowb = id.id;
	} else if(form == "pc"){
		$("#pc-list tr").css('background',null);
		selectedRowc = id.id;
	} else if(form == "pd"){
		$("#pd-list tr").css('background',null);
		selectedRowd = id.id;
	} else if(form == "order"){
		$("#order-list tr").css('background',null);
		selectedRow = id.id;
	} else if(form == "work"){
		$("#work-list tr").css('background',null);
		selectedRow = id.id;
	} else if(form == "job0"){
		$("#job0-list tr").css('background',null);
		$("#job1-list").html('');
		selectedRowa = id.id;
		getAllWSLevel1(selectedRowa, function(data){
			str = "";
			$.each(data, function(index, datum){
				str = "<tr id="+datum.id_ws_1+" onclick='selectRow(\"job1\",this)'>";
				str += "<td width='10%'>"+datum.id_activity+"</td>";
				wo = "";
				wo += datum.activity;
				if(datum.id_work_order != 0){
					wo += " ("+datum.id_work_order+")";
				}
				str += "<td width='40%'>"+wo+"</td>";
				if(datum.work_status == 0){
					str += "<td width='25%'><img width='20' src='images/notstarted.png'/></td>";
				} else if(datum.work_status == 1){
					str += "<td width='25%'><img width='20' src='images/onprogress.png'/></td>";
				} else if(datum.work_status == 2){
					str += "<td width='25%'><img width='20' src='images/waiting.png'/></td>";
				} else if(datum.work_status == 3){
					str += "<td width='25%'><img width='20' src='images/stop.png'/></td>";
				} else if(datum.work_status == 4){
					str += "<td width='25%'><img width='20' src='images/finish.png'/></td>";
				}
				
				str += "<td width='25%' id='pr"+datum.id_ws_1+"'></td>";
				str += "</tr>";
				$("#job1-list").append(str);
				
				calculateProgress(datum.id_ws_1, function(progress){
					$("#pr"+datum.id_ws_1).html(progress+"%");
				});
			});
		});
	} else if(form == "job1"){
		$("#job1-list tr").css('background',null);
		selectedRowb = id.id;
	} else if(form == "job2"){
		$("#job2-list tr").css('background',null);
		selectedRowc = id.id;
	}
	
	id.style.background = "#C6C6C6";
}

function getIdxFromId(id){
	var i = 0;
	while(i < dia.length){
		if(id == dia[i].id){
			return i;
		}
		i++;
	}
	return -1;
}

function getDiaFromId(id){
	var i = 0;
	while(i < dia.length){
		if(id == dia[i].id){
			return dia[i];
		}
		i++;
	}
	return null;
}

function preCheckTroll(datum){
	if(datum.pre == null){
		return true;
	} else {
		var j = 0
		var retval = false;
		while(j < datum.pre.length){
			if(datum.id.substring(0, 2) != datum.pre[j].id_predecessor.substring(0, 2)){
				retval = true;
			}
			j++;
		}
		return retval;
	}
}

function preCheckJob(datum){
	if(datum.pre == null){
		return true;
	} else {
		var j = 0
		var retval = false;
		while(j < datum.pre.length){
			if(datum.id.substring(0, 2) != datum.pre[j].id_predecessor.substring(0, 2)){
				retval = true;
			}
			j++;
		}
		return retval;
	}
}

function pertAlignColTableTroll(){
	if (dia) {
		$.each(dia, function(index, datum){
			if(datum.pre == null){
				datum.col = 0;
			} else {
				var max = 0;
				for(var j = 0; j < datum.pre.length; j++){
					idx = getIdxFromId(datum.pre[j].id_predecessor);
					if(idx != -1){
						if(dia[idx].col >= max){
							max = dia[idx].col;
						}
					}
				}
				
				datum.col = max + 1;
			}
		});
	}
}

function countNColperStep(){
	avStep = [];
	sMin = [];
	sMax = [];
	for(var i = 0; i <= getMaxStep(); i++){
		avStep.push(0);
		sMin.push(-1);
		sMax.push(-1);
	}
	$.each(dia, function(index, datum){
		if(sMin[datum.step] != -1){
			if(datum.col < sMin[datum.step]){
				sMin[datum.step] = datum.col;
			}
		} else {
			sMin[datum.step] = datum.col;
		}
		if(sMax[datum.step] != -1){
			if(datum.col > sMax[datum.step]){
				sMax[datum.step] = datum.col;
			}
		} else {
			sMax[datum.step] = datum.col;
		}
		avStep[datum.step] = sMax[datum.step] - sMin[datum.step];
	});
	var i = 0;
	while(i < avStep.length){
		
		if(i > 0){
			avStep[i] += avStep[i - 1];
		}
		i++;
	}
	return avStep;
}

function pertAlignStepTableTroll(){
	if (dia) {
		avStep = countNColperStep();
		$.each(dia, function(index, datum){
			datum.col += avStep[datum.step];
		});
	}
}

function pertAlignRowTableTroll(){
	if (dia) {
		avRow = [];
		for(var i = 0; i <= getMaxCol(); i++){
			avRow.push(0);
		}
		$.each(dia, function(index, datum){
			if(datum.suc == null){
				datum.row = avRow[datum.col];
				avRow[datum.col]++;
			}
			else {
				for(var j = 0; j < datum.suc.length; j++){
					idx = getIdxFromId(datum.suc[j].id_successor);
					if(idx != -1){
						if(avRow[dia[idx].col] <= datum.row){
							dia[idx].row = datum.row;
							avRow[dia[idx].col] = datum.row;
						}
						dia[idx].row = avRow[dia[idx].col];
						avRow[dia[idx].col]++;
					}
				}
			}
		});
	}
}

function pertAlignColTableJob(){
	if (dia) {
		$.each(dia, function(index, datum){
			if(datum.pre == null){
				datum.col = 0;
			} else {
				var max = 0;
				for(var j = 0; j < datum.pre.length; j++){
					idx = getIdxFromId(datum.pre[j].id_predecessor);
					if(idx != -1){
						if(dia[idx].col >= max){
							max = dia[idx].col;
						}
					}
				}
				datum.col = max + 1;
			}
		});
	}
}

function pertAlignRowTableJob(){
	if (dia) {
		avRow = [];
		for(var i = 0; i <= getMaxCol(); i++){
			avRow.push(0);
		}
		$.each(dia, function(index, datum){
			if(preCheckJob(datum) && datum.col == 1){
				datum.row = avRow[datum.col];
				avRow[datum.col]++;
			}
			if(datum.suc == null){
				datum.row = avRow[datum.col];
				avRow[datum.col]++;
			}
			else {
				for(var j = 0; j < datum.suc.length; j++){
					idx = getIdxFromId(datum.suc[j].id_successor);
					if(idx != -1){
						if(avRow[dia[idx].col] <= datum.row){
							dia[idx].row = datum.row;
							avRow[dia[idx].col] = datum.row;
						}
						dia[idx].row = avRow[dia[idx].col];
						avRow[dia[idx].col]++;
					}
				}
			}
		});
	}
}

function getMaxRow(){
	if (dia) {
		max = 0;
		$.each(dia, function(index, datum){
			if(datum.row >= max){
				max = datum.row;
			}
		});
	}
	return max;
}

function getMaxCol(){
	if (dia) {
		max = 0;
		$.each(dia, function(index, datum){
			if(datum.col >= max){
				max = datum.col;
			}
		});
	}
	return max;
}

function getMaxStep(){
	if (dia) {
		max = 0;
		$.each(dia, function(index, datum){
			if(datum.step >= max){
				max = datum.step;
			}
		});
	}
	return max;
}

function sortDiagramJob(){
	n = dia.length;
	for (i = 0; i < n; i++){
		for (j = n-1; j > i; j--){
			idPrev = "" + dia[j-1].id;
			idPrev = 10 * idPrev.substring(3, 5) + idPrev.substring(6, 8);
			idNext = "" + dia[j].id;
			idNext = 10 * idNext.substring(3, 5) + idNext.substring(6, 8);
			//alert(idPrev + " - " + (idPrev * 10));
			if (idPrev > idNext) {
				var tDia = dia[j-1];
				dia[j-1] = dia[j];
				dia[j] = tDia;
			}
		}
	}
}

function sortDiagramTroll(){
	n = dia.length;
	for (i = 0; i < n; i++){
		for (j = n-1; j > i; j--){
			idPrev = "" + dia[j-1].id;
			idPrev = 10 * idPrev.substring(0, 2) + idPrev.substring(3, 5);
			idNext = "" + dia[j].id;
			idNext = 10 * idNext.substring(0, 2) + idNext.substring(3, 5);
			//alert(idPrev + " - " + (idPrev * 10));
			if (idPrev > idNext) {
				var tDia = dia[j-1];
				dia[j-1] = dia[j];
				dia[j] = tDia;
			}
		}
	}
}

$(document).ready(function () {
	setPageContent("test", function(data){
		$("#content").html(data);
		/*
		//id, name, startDate, endDate, status, type, pre, suc
		getAllWSLevel3(123249, function(data2){
			$.each(data2, function(index, datum){
				var tDia = new diagram(datum.id_activity, datum.activity, datum.start_date, datum.end_date, datum.work_status, datum.type, datum.pre, datum.suc);
				dia.push(tDia); 
			});
			sortDiagramJob();
			pertAlignColTableJob();
			pertAlignRowTableJob();
			
			var maxCol = getMaxCol();
			var maxRow = getMaxRow();
			
			matrix = new Array(maxRow + 1);
			for(var row = 0; row <= maxRow; row++){
				matrix[row] = new Array(maxCol + 1);
				for(var col = 0; col <= maxCol; col++){
					matrix[row][col] = "";
				}
			}
			
			$.each(dia, function(index, datum){
				//alert(datum.row + "-" + datum.col + ":" + matrix[datum.row][datum.col]);
				matrix[datum.row][datum.col] = datum.html;
			});
			
			str = "";
			str += "<table>";
			for(var row = 0; row <= maxRow; row++){
				str += "<tr>";
				for(var col = 0; col <= maxCol; col++){
					str += "<td>";
					//str += row + "-" + col;
					str += matrix[row][col];
					str += "</td>";
				}
				str += "</tr>";
			}
			str += "</table>";
			$("#test").html(str);
		});
		*/
	});
}); 

$(function(){
	dateTime();
	$("#tree")
        .jstree({ "plugins" : ["themes","html_data","ui"] })
        // 1) if using the UI plugin bind to select_node
        .bind("select_node.jstree", function (event, data) {
           // `data.rslt.obj` is the jquery extended node that was clicked
			toolboxAction(data.rslt.obj.attr("id"));
        })
        // 2) if not using the UI plugin - the Anchor tags work as expected
        //    so if the anchor has a HREF attirbute - the page will be changed
        //    you can actually prevent the default, etc (normal jquery usage)
        .delegate("a", "click", function (event, data) { event.preventDefault(); })
	$("#tree").jstree("open_all");
	isLoggedIn(function(data){
		initLoginButton(data);
    });
	
	$("#form-login").dialog({	
		autoOpen: false,
		height: 350,
		width: 300,
		modal: true,
		draggable: false,
		resizable: false,
		show: "clip",
		hide: "clip",
		buttons: {
			'Login': function(){
				formLoginConfirm();
			},
			Cancel: function() {
				$(".login-tips").text('All form fields are required.');
				$(this).dialog('close');
			}
		},
		close: function() {
			$("#login-name").removeClass('ui-state-error');
			$("#login-pass").removeClass('ui-state-error');
		},
		open: function() {
			$("#login-name").val('');
			$("#login-pass").val('');
			$("#login-name").focus();
		}
	});
	
	$("#dialog-box").dialog({
		autoOpen: false,
		resizable: false,
		height:160,
		modal: true,
		buttons: {
			'OK': function() {
				if(inWhichForm == 'form-login-success'){
					window.location = ".";
				}
				$(this).dialog('close');
			}
		}
	});
	
	$("#dialog-confirm").dialog({
		autoOpen: false,
		resizable: false,
		height:160,
		modal: true,
		buttons: {
			'Yes': function() {
				if(inWhichForm == 'form-deleteuser'){
					deleteUser(selectedRow, function(data){
						if(data == 1) {
							toolboxAction("t1");
						}
						else {
							inWhichForm = 'form-deleteuser-failed';
							openDialogBox("Delete failed!");
						}
					});
				} else if(inWhichForm == 'form-deletepa'){
					deleteFSupplier(selectedRowa, function(data){
						if(data == 1) {
							toolboxAction("t5");
						}
						else {
							inWhichForm = 'form-deletepa-failed';
							openDialogBox("Delete failed!");
						}
					});
				} else if(inWhichForm == 'form-deletepb'){
					deleteTSupplier(selectedRowb, function(data){
						if(data == 1) {
							toolboxAction("t5");
						}
						else {
							inWhichForm = 'form-deletepb-failed';
							openDialogBox("Delete failed!");
						}
					});
				} else if(inWhichForm == 'form-deletepc'){
					deleteSubcontract(selectedRowc, function(data){
						if(data == 1) {
							toolboxAction("t5");
						}
						else {
							inWhichForm = 'form-deletepc-failed';
							openDialogBox("Delete failed!");
						}
					});
				} else if(inWhichForm == 'form-deletepd'){
					deleteBuyer(selectedRowd, function(data){
						if(data == 1) {
							toolboxAction("t5");
						}
						else {
							inWhichForm = 'form-deletepd-failed';
							openDialogBox("Delete failed!");
						}
					});
				} else if(inWhichForm == 'form-deleteorder'){
					deleteOrder(selectedRow, function(data){
						if(data == 1) {
							toolboxAction("t21");
						}
						else {
							inWhichForm = 'form-deleteorder-failed';
							openDialogBox("Delete failed!");
						}
					});
				} else if(inWhichForm == 'form-deletework'){
					deleteWork(selectedRow, function(data){
						if(data == 1) {
							toolboxAction("t32");
						}
						else {
							inWhichForm = 'form-deletework-failed';
							openDialogBox("Delete failed!");
						}
					});
				} else if(inWhichForm == 'form-deletesupstat'){
					deleteSupplierStatus(selectedRowe, function(data){
						if(data == 1) {
							renderDiagramJob(1);
						}
						else {
							inWhichForm = 'form-deletesupstat-failed';
							openDialogBox("Delete failed!");
						}
					});
				} else if(inWhichForm == 'form-deletesubstat'){
					deleteSubcontractStatus(selectedRowe, function(data){
						if(data == 1) {
							renderDiagramJob(2);
						}
						else {
							inWhichForm = 'form-deletesubstat-failed';
							openDialogBox("Delete failed!");
						}
					});
				}
				
				$(this).dialog('close');
			},
			'No': function() {
				$(this).dialog('close');
			}
		}
	});
	
	$("#form-adduser").dialog({	
		autoOpen: false,
		height: 350,
		width: 300,
		modal: true,
		draggable: false,
		resizable: false,
		show: "clip",
		hide: "clip",
		buttons: {
			'Send': function(){
				if(inWhichForm == 'form-adduser'){
					formAddUserConfirm();
				} else if(inWhichForm == 'form-edituser'){
					formEditUserConfirm();
				}
			},
			Cancel: function() {
				$(this).dialog('close');
			}
		},
		close: function() {
			
		},
		open: function() {
			$("#adduser-username").focus();
		}
	});
	
	$("#form-chgpwd").dialog({	
		autoOpen: false,
		height: 280,
		width: 300,
		modal: true,
		draggable: false,
		resizable: false,
		show: "clip",
		hide: "clip",
		buttons: {
			'Change': function(){
				pwd = $("#chgpwd-pwd").val();
				newpwd = $("#chgpwd-newpwd").val();
				cfrpwd = $("#chgpwd-cfrpwd").val();
				$(this).dialog('close');
				changePwd(pwd, newpwd, cfrpwd, function(data){
					if(data == 1) {
						inWhichForm = 'form-chgpwd-success';
						openDialogBox("Change password successful!");
					}
					else {
						inWhichForm = 'form-chgpwd-failed';
						openDialogBox("Change password failed!");
					}
				});
			},
			Cancel: function() {
				$(this).dialog('close');
			}
		},
		close: function() {
			
		},
		open: function() {
			$("#chgpwd-pwd").focus();
		}
	});
	
	$("#form-addsup").dialog({	
		autoOpen: false,
		height: 350,
		width: 300,
		modal: true,
		draggable: false,
		resizable: false,
		show: "clip",
		hide: "clip",
		buttons: {
			'Send': function(){
				if(inWhichForm == 'form-addsupa'){
					formAddFSupplierConfirm();
				} else if(inWhichForm == 'form-editsupa'){
					formEditFSupplierConfirm();
				} else if(inWhichForm == 'form-addsupb'){
					formAddTSupplierConfirm();
				} else if(inWhichForm == 'form-editsupb'){
					formEditTSupplierConfirm();
				} else if(inWhichForm == 'form-addsupc'){
					formAddSubcontractConfirm();
				} else if(inWhichForm == 'form-editsupc'){
					formEditSubcontractConfirm();
				} else if(inWhichForm == 'form-addsupd'){
					formAddBuyerConfirm();
				} else if(inWhichForm == 'form-editsupd'){
					formEditBuyerConfirm();
				}
			},
			Cancel: function() {
				$(this).dialog('close');
			}
		},
		close: function() {
			
		},
		open: function() {
			
		}
	});
	
	$("#form-view").dialog({	
		autoOpen: false,
		modal: true,
		draggable: true,
		resizable: false,
		show: "clip",
		hide: "clip",
		buttons: {
			'OK': function(){
				$(this).dialog('close');
			}
		},
		close: function() {
			$(this).dialog({ position: 'center' });
		},
		open: function() {
			$(this).dialog({ position: 'center' });
		}
	});
	
	$('#login-pass').change(function() {
		//formLoginConfirm()
	});
	
	$("#form-addorder").dialog({	
		autoOpen: false,
		height: 500,
		width: 300,
		modal: true,
		draggable: false,
		resizable: false,
		show: "clip",
		hide: "clip",
		buttons: {
			'Send': function(){
				if(inWhichForm == 'form-addorder'){
					formAddOrderConfirm();
				} else if(inWhichForm == 'form-editorder'){
					formEditOrderConfirm();
				}
			},
			Cancel: function() {
				$(this).dialog('close');
			}
		},
		close: function() {
			
		},
		open: function() {
			
		}
	});
	
	$("#form-addsuporder").dialog({	
		autoOpen: false,
		modal: true,
		draggable: true,
		resizable: false,
		show: "clip",
		hide: "clip",
		buttons: {
			'OK': function(){
				$(this).dialog('close');
			}
		},
		close: function() {
			$(this).dialog({ position: 'center' });
		},
		open: function() {
			$(this).dialog({ position: 'center' });
		}
	});
	
	$("#form-addsuborder").dialog({	
		autoOpen: false,
		modal: true,
		draggable: true,
		resizable: false,
		show: "clip",
		hide: "clip",
		buttons: {
			'OK': function(){
				$(this).dialog('close');
			}
		},
		close: function() {
			$(this).dialog({ position: 'center' });
		},
		open: function() {
			$(this).dialog({ position: 'center' });
		}
	});
	
	$("#form-edittarget").dialog({	
		autoOpen: false,
		height: 200,
		width: 300,
		modal: true,
		draggable: false,
		resizable: false,
		show: "clip",
		hide: "clip",
		buttons: {
			'Send': function(){
				if(inWhichForm == 'form-edittarget'){
					formEditTargetConfirm();
				}
			},
			Cancel: function() {
				$(this).dialog('close');
			}
		}
	});
	
	$("#form-addwork").dialog({	
		autoOpen: false,
		height: 450,
		width: 300,
		modal: true,
		draggable: false,
		resizable: false,
		show: "clip",
		hide: "clip",
		buttons: {
			'Send': function(){
				if(inWhichForm == 'form-addwork'){
					formAddWorkConfirm();
				} else if(inWhichForm == 'form-editwork'){
					formEditWorkConfirm();
				}
			},
			Cancel: function() {
				$(this).dialog('close');
			}
		}
	});
	
	$("#form-addsupstat").dialog({	
		autoOpen: false,
		height: 300,
		width: 300,
		modal: true,
		draggable: false,
		resizable: false,
		show: "clip",
		hide: "clip",
		buttons: {
			'Send': function(){
				if(inWhichForm == 'form-addsupstat'){
					formAddSupStatConfirm();
				} else if(inWhichForm == 'form-editsupstat'){
					formEditSupStatConfirm();
				}
			},
			Cancel: function() {
				$(this).dialog('close');
			}
		}
	});
	
	$("#form-addsubstat").dialog({	
		autoOpen: false,
		height: 300,
		width: 300,
		modal: true,
		draggable: false,
		resizable: false,
		show: "clip",
		hide: "clip",
		buttons: {
			'Send': function(){
				if(inWhichForm == 'form-addsubstat'){
					formAddSubStatConfirm();
				} else if(inWhichForm == 'form-editsubstat'){
					formEditSubStatConfirm();
				}
			},
			Cancel: function() {
				$(this).dialog('close');
			}
		}
	});
	
	$("#form-resched").dialog({	
		autoOpen: false,
		modal: false,
		height: 300,
		width: 650,
		draggable: true,
		resizable: false,
		show: "clip",
		hide: "clip",
		buttons: {
			'Send': function(){
				formReschedConfirm();
			},
			Cancel: function() {
				$(this).dialog('close');
			}
		},
		close: function() {
			$(this).dialog({ position: 'center' });
		},
		open: function() {
			$(this).dialog({ position: 'center' });
		}
	});
});
