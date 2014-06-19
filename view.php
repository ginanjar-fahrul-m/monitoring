<?php
/* File   : index.php
 * Role   : Static Page, View (MVC)
 * Author : Ginanjar Fahrul M (gin)
 * E-Mail : ginanjar.fahrul.m@gmail.com
 * Team   : Institut Teknologi Bandung, Juni-Juli 2011
 *          
 * 
 * 
 * Halaman ini menggunakan type XHTML™ 1.0 The Extensible HyperText Markup Language (Second Edition)
 * A Reformulation of HTML 4 in XML 1.0
 * 
 */
	require_once('includes/config.php');
	require_once('includes/connection.class.php');
	require_once('includes/session.php');

	session_init();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">	
	<head>
		<link type="text/css" href="css/style.css" media="screen" rel="stylesheet" />
		<link type="text/css" href="css/custom-theme/jquery-ui-1.8.2.custom.css" rel="stylesheet" />	
		
		<script type="text/javascript" src="js/jquery-1.4.2.min.js"></script>
		<script type="text/javascript" src="js/jquery-ui-1.8.2.custom.min.js"></script>
		<script type="text/javascript" src="js/jquery.jstree.js"></script>
		<script type="text/javascript" src="js/jquery.jstree.min.js"></script>
		<script type="text/javascript" src="js/view.js"></script>
		<script type="text/javascript" src="js/controller.js"></script>
	</head>
	<body>
		<div id="header">
			<div id="upper">
				<ul>
					<li>
						<label href="#" id="menu-date" title="Date">Date </label>
					</li>
					<li>
						<label> | </label> 
					</li>
					<li>
						<a href="#" title="FAQ's">FAQ's </a>
					</li> 
					<li>
						<label> | </label> 
					</li>
					<li>
						<a href="#" title="Contact Us">Contact Us </a>
					</li>
					<li>
						<label> | </label> 
					</li>
					<li>
						<a href="#" id="menu-login" title="Log In">Log In </a>
					</li>
				</ul>
			</div>
		</div>
		<div id="page">
			<div class="ui-widget-content" id="top">
				<div id="app-name">
					<h2>JOB MONITORING SYSTEM PT. GISTEX GARMENT DIVISION</h2>
					<h3 id="welcome"></h3>
				</div>
				<div class="ui-widget-content" id="search-box">
					<p><label>Sales Order: </label><input type="text"></input><button class="ui-widget button">Search</button></p>
					<p><label>Work Order: </label><input type="text"></input><button class="ui-widget button">Search</button></p>
				</div>
			</div>
			<div class="ui-widget-content" id="sidebar">
				<div id="sidebar-title">Toolbox</div>
				<div id="tree">
					<ul>
						<li id="t1">
							<a href="#">User Management</a>
							<ul>
								<li id="t11">
									<a href="#">Change Password</a>
								</li>
							</ul>
						</li>
						<li id="t2">
							<a href="#">Monitoring</a>
							<ul>
								<li id="t21">
									<a href="#">Job Profile</a>
								</li>
								<li id="t22">
									<a href="#">Job Status</a>
								</li>
							</ul>
						</li>
						<li id="t3">
							<a href="#">Scheduling</a>
							<ul>
								<li id="t31">
									<a href="#">Time Target</a>
								</li>
								<li id="t32">
									<a href="#">Production Schedule</a>
								</li>
							</ul>
						</li>
						<li id="t4">
							<a href="#">Controlling</a>
						</li>
						<li id="t5">
							<a href="#">Profile</a>
						</li>
						<li id="t6">
							<a href="#">Performance</a>
						</li>
						<li id="t7">
							<a href="#">Report</a>
						</li>
					</ul>
				</div>
			</div>
			<div class="ui-widget-content" id="content">
				
			</div>
		</div>
		<div class="ui-widget-content title" id="footer">
			<p>Copyright &copy; Ginanjar Fahrul Muttaqin</p>
		</div>
		<div id="form">
			<div id="form-login" class="form" title="Login">
				<p align="center">Welcome!</p>
				<p align="center">JOB MONITORING SYSTEM</p>
				<p align="center">PT. GISTEX GARMENT DIVISION</p>
				<fieldset>
					<label>Username</label>
					<input type="text" id="login-name" class="text ui-widget-content ui-corner-all" />
					<label>Password</label>
					<input type="password" id="login-pass" value="" class="text ui-widget-content ui-corner-all" />
				</fieldset>
				<p class="login-tips">All form fields are required.</p>
			</div>
			<div id="dialog-box" title="Dialog">
				<div id="dialog-text" class="dialog-text"></div>
			</div>
			<div id="dialog-confirm" title="Dialog">
				<div id="confirm-text" class="dialog-text">Are you sure?</div>
			</div>
			<div id="form-adduser" class="form" title="Add User">
				<fieldset>
					<label>Username</label>
					<input type="text" id="adduser-username" class="text ui-widget-content ui-corner-all" />
					<label>Password</label>
					<input type="password" id="adduser-password" value="" class="text ui-widget-content ui-corner-all" />
					<label>Full Name</label>
					<input type="text" id="adduser-fullname" value="" class="text ui-widget-content ui-corner-all" />
					<label>Department</label>
					<select type="text" id="adduser-dept" value="" class="text ui-widget-content ui-corner-all">
						<option value="admin">Admin</option>
						<option value="marketing">Marketing</option>
						<option value="merchandiser">Merchandiser</option>
						<option value="purchasingfabric">Purchasing Fabric</option>
						<option value="purchasingtrim">Purchasing Trim</option>
						<option value="sampleroom">Sample Room</option>
						<option value="warehouse">Warehouse</option>
						<option value="ppic">PPIC</option>
						<option value="produksi">Produksi</option>
						<option value="qualitycontrol">Quality Control</option>
						<option value="exportimport">Export Import</option>
						<option value="ekspedisi">Ekspedisi</option>
					</select>
				</fieldset>
			</div>
			<div id="form-chgpwd" class="form" title="Change Password">
				<fieldset>
					<label>Password</label>
					<input type="password" id="chgpwd-pwd" class="text ui-widget-content ui-corner-all" />
					<label>New Password</label>
					<input type="password" id="chgpwd-newpwd" value="" class="text ui-widget-content ui-corner-all" />
					<label>Confirm Password</label>
					<input type="password" id="chgpwd-cfrpwd" value="" class="text ui-widget-content ui-corner-all" />
				</fieldset>
			</div>
			<div id="form-addsup" class="form" title="">
				<fieldset>
					<label>Name</label>
					<input type="text" id="addsup-name" class="text ui-widget-content ui-corner-all" />
					<label>Address</label>
					<input type="text" id="addsup-address" value="" class="text ui-widget-content ui-corner-all" />
					<label>Email</label>
					<input type="text" id="addsup-email" value="" class="text ui-widget-content ui-corner-all" />
					<label>Phone</label>
					<input type="text" id="addsup-phone" value="" class="text ui-widget-content ui-corner-all" />
				</fieldset>
			</div>
			<div id="form-view" class="" title="">
				<div id="view-content">
					
				</div>
			</div>
			<div id="form-addorder" class="form" title="">
				<fieldset>
					<label>Style</label>
					<input type="text" id="addorder-style" class="text ui-widget-content ui-corner-all" />
					<label>Buyer</label>
					<select type="text" id="addorder-buyer" value="" class="text ui-widget-content ui-corner-all" >
						
					</select>
					<label>Season</label>
					<input type="text" id="addorder-season" value="" class="text ui-widget-content ui-corner-all" />
					<label>Product</label>
					<input type="text" id="addorder-product" value="" class="text ui-widget-content ui-corner-all" />
					<label>Quantity</label>
					<input type="text" id="addorder-quantity" value="0" class="text ui-widget-content ui-corner-all" />
					<label>Order Date</label>
					<input type="text" id="addorder-order" value="0000-00-00" class="text ui-widget-content ui-corner-all" />
					<label>Confirm Date</label>
					<input type="text" id="addorder-confirm" value="0000-00-00" class="text ui-widget-content ui-corner-all" />
					<label>Delivery Date</label>
					<input type="text" id="addorder-delivery" value="2012-12-12" class="text ui-widget-content ui-corner-all" />
				</fieldset>
			</div>
			<div id="form-addsuporder" class="" title="">
				<fieldset>
					<div class="table-container">
						<table class="table wide" border="0">
							<thead>
								<tr>
									<th width="30%">Supplier</th>
									<th width="30%">Type</th>
									<th width="40%">Action</th>
								</tr>
							</thead>
						</table>
						<div class="auto low">
						<table class="table wide" border="0">
							<tbody id="sup-list">
								
							</tbody>
						</table>
						</div>
					</div>
				</fieldset>
			</div>
			<div id="form-addsuborder" class="" title="">
				<fieldset>
					<div class="table-container">
						<table class="table wide" border="0">
							<thead>
								<tr>
									<th width="30%">Subcontract</th>
									<th width="40%">Action</th>
								</tr>
							</thead>
						</table>
						<div class="auto low">
						<table class="table wide" border="0">
							<tbody id="sub-list">
								
							</tbody>
						</table>
						</div>
					</div>
				</fieldset>
			</div>
			<div id="form-edittarget" class="form" title="">
				<fieldset>
					<label>Target Start</label>
					<input type="text" id="edittarget-start" class="text ui-widget-content ui-corner-all" />
					<label>Target End</label>
					<input type="text" id="edittarget-end" class="text ui-widget-content ui-corner-all" />
				</fieldset>
			</div>
			<div id="form-addwork" class="form" title="">
				<fieldset>
					<label>Order</label>
					<select type="text" id="addwork-order" value="" class="text ui-widget-content ui-corner-all"></select>
					<label>Destination</label>
					<input type="text" id="addwork-dest" value="" class="text ui-widget-content ui-corner-all" />
					<label>Quantity</label>
					<input type="text" id="addwork-quantity" value="" class="text ui-widget-content ui-corner-all" />
					<label>Cutting Date</label>
					<input type="text" id="addwork-cutting" value="0000-00-00" class="text ui-widget-content ui-corner-all" />
					<label>Sewing Date</label>
					<input type="text" id="addwork-sewing" value="0000-00-00" class="text ui-widget-content ui-corner-all" />
					<label>Delivery Date</label>
					<input type="text" id="addwork-delivery" value="0000-00-00" class="text ui-widget-content ui-corner-all" />
				</fieldset>
			</div>
			<div id="form-addsupstat" class="form" title="">
				<fieldset>
					<label>Supplier</label>
					<select type="text" id="addsupstat-sup" value="" class="text ui-widget-content ui-corner-all"></select>
					<label>Work Status</label>
					<select type="text" id="addsupstat-status" value="" class="text ui-widget-content ui-corner-all"></select>
					<label>Delivery Date</label>
					<input type="text" id="addsupstat-delivery" value="" class="text ui-widget-content ui-corner-all" />
					<label>PO Number</label>
					<input type="text" id="addsupstat-ponumber" value="" class="text ui-widget-content ui-corner-all" />
				</fieldset>
			</div>
			<div id="form-addsubstat" class="form" title="">
				<fieldset>
					<label>Supplier</label>
					<select type="text" id="addsubstat-sub" value="" class="text ui-widget-content ui-corner-all"></select>
					<label>Work Status</label>
					<select type="text" id="addsubstat-status" value="" class="text ui-widget-content ui-corner-all"></select>
					<label>End Date</label>
					<input type="text" id="addsubstat-delivery" value="" class="text ui-widget-content ui-corner-all" />
					<label>Subcontract Number</label>
					<input type="text" id="addsubstat-number" value="" class="text ui-widget-content ui-corner-all" />
				</fieldset>
			</div>
			<div id="form-resched" class="" title="">
				<fieldset>
					<div class="table-container">
						<table class="table wide" border="0">
							<thead>
								<tr>
									<th width="80%">Step</th>
									<th width="20%">Resched</th>
								</tr>
							</thead>
						</table>
						<div class="auto low">
						<table class="table wide" border="0">
							<tbody id="resched-list">
								
							</tbody>
						</table>
						</div>
					</div>
				</fieldset>
			</div>
		</div>
	</body>
</html>