<?php
	/* FRCS
    Copyright (C) 2014 Jon Penn

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as
    published by the Free Software Foundation, either version 3 of the
    License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>. */
	
	require_once(dirname(__FILE__) . '/../../includes/db.inc.php');
	require_once(dirname(__FILE__) . '/../../includes/array_column.inc.php'); // just incase our version of php dosent include this function
	
	ini_set('session.use_only_cookies',0);
	session_start();
?><!DOCTYPE html>
<html>
	<head>
		<title>Table Editor</title>
		<style>
			table {
				border-collapse:collapse;
			}
			
			table,th, td {
				border: 1px solid black;
			}
		</style>
	</head>
	
	<body>
		<div>
			Table Editor 
			<?php
				$pages = db('SELECT name FROM pages ORDER BY weight, id ASC');
				foreach($pages as $page) {
					if(isset($_GET['page']) && $page->name == $_GET['page']) {
						echo $page->name . ' ';
					} else {
						echo '<a href="?page=' . $page->name . '&' . htmlspecialchars(SID) . '">' . $page->name . '</a> ';
					}
				}
			?>
		</div>
		<?php
			if(isset($_GET['page'])) {
				$page = db1('SELECT prow, ptable FROM pages WHERE name=?', $_GET['page']);
				$columns = dba('SELECT id, page, coutput, cname FROM columns WHERE page=? ORDER BY id ASC', $_GET['page']);
				?>
				<script>
					/////////////////
					// PAGE CONFIG //
					/////////////////
					var cOutputs	=	<?php echo json_encode(array_column($columns, 'coutput')); ?>;
					var cNames		=	<?php echo json_encode(array_column($columns, 'cname')); ?>;
					var cIds		=	<?php echo json_encode(array_column($columns, 'id')); ?>;
					var pTable		=	'<?php echo $page->ptable; ?>';
					var pRow		=	<?php echo $page->prow; ?>;
					var page		=	'<?php echo urlencode($_GET['page']); ?>';
					
					var jsonUrl		=	'data.php?page=' + page;
					function insertRowWithData(rowData) {
						var tableRow = table.insertRow(-1);
						tableRow.dataset.row = rowData[pRow];
						
						for(var displayColumn=0; displayColumn<cOutputs.length; displayColumn++) {
							var tableCell = tableRow.insertCell(-1);
							
							var input = document.createElement('input');
							input.type = 'text';
							input.value = rowData[cOutputs[displayColumn]];
							input.dataset.cOutput = cOutputs[displayColumn];
							input.dataset.cId = cIds[displayColumn];
							if(cOutputs.length >= 6) {
								input.style.width = '50px';
							}
							input.onfocus = function() {
								this.style.backgroundColor = 'yellow';
							};
							input.onblur = function() {
								
								for(var dataRow=0; dataRow<data.length; dataRow++) {
									if(data[dataRow][pRow] === this.parentNode.parentNode.dataset.row) {
										if(this.value != data[dataRow][this.dataset.cOutput] &&
										  !(this.value == '' && data[dataRow][this.dataset.cOutput] == null)) {
											this.parentNode.style.backgroundColor = 'yellow';
										}
										break;
									}
								}
								
								var request;
								if(window.XMLHttpRequest) {
									// code for IE7+, Firefox, Chrome, Opera, Safari
									request = new XMLHttpRequest();
								} else {
									// code for IE6, IE5
									request = new ActiveXObject('Microsoft.XMLHTTP');
								}
								
								request.stashedThis = this;
								
								request.onreadystatechange = function() {
									if(request.readyState == 4) {
										if(request.status == 200) {
											this.stashedThis.style.backgroundColor = null;
										} else {
											this.stashedThis.style.backgroundColor = 'red';
										}
									}
								}
								request.open('POST','update.php?<?php echo htmlspecialchars(SID); ?>',true);
								request.setRequestHeader('Content-type','application/x-www-form-urlencoded');
								var pramiters = 'password=' + encodeURIComponent(passwordBox.value) + 
												'&column=' + encodeURIComponent(this.dataset.cId) +
												'&row=' + encodeURIComponent(this.parentNode.parentNode.dataset.row);
								if(this.value != '') {
									pramiters += '&value=' + encodeURIComponent(this.value);
								}
								request.send(pramiters);
							};
							
							tableCell.appendChild(input);
						}
						
						var tableCell = tableRow.insertCell(-1);
						var deleteRowButton = document.createElement('button');
						deleteRowButton.appendChild(document.createTextNode('Delete'));
						deleteRowButton.onclick = function() {
							if(confirm('OK to Delete?')) {
								this.style.backgroundColor = 'yellow';
								
								var row = this.parentNode.parentNode.dataset.row;
								
								var request;
								if(window.XMLHttpRequest) {
									// code for IE7+, Firefox, Chrome, Opera, Safari
									request = new XMLHttpRequest();
								} else {
									// code for IE6, IE5
									request = new ActiveXObject('Microsoft.XMLHTTP');
								}
								
								request.stashedThis = this;
								
								request.onreadystatechange = function() {
									if(request.readyState == 4) {
										if(request.status == 200) {
											this.stashedThis.style.backgroundColor = null;
										} else {
											this.stashedThis.style.backgroundColor = 'red';
										}
									}
								}
								request.open('POST','update.php?<?php echo htmlspecialchars(SID); ?>',true);
								request.setRequestHeader('Content-type','application/x-www-form-urlencoded');
								request.send('password=' + encodeURIComponent(passwordBox.value) +
											'&table=' + encodeURIComponent(pTable) +
											'&row=' + encodeURIComponent(row));
							}
						};
						tableCell.appendChild(deleteRowButton);
						return tableRow;
					}
					
					var refreshRate = 5000;
					var request, data, newData, table, passwordBox;

					if(window.XMLHttpRequest) {
						// code for IE7+, Firefox, Chrome, Opera, Safari
						request = new XMLHttpRequest();
					} else {
						// code for IE6, IE5
						request = new ActiveXObject('Microsoft.XMLHTTP');
					}

					request.onreadystatechange = function() {
						if(request.readyState == 4) {
							if(request.status == 200) {
								newData = eval('(' + request.responseText + ')');
								if(data == null ) {
									var passwordBoxLabel = document.createTextNode(' Password:');
									document.body.appendChild(passwordBoxLabel);
									
									passwordBox = document.createElement('input');
									passwordBox.type = 'text';
									<?php
										if(isset($_SESSION['password'])) {
											echo 'passwordBox.value = \'' . $_SESSION['password'] . '\';';
										}
									?>
									passwordBox.onfocus = function() {
										this.style.backgroundColor = 'yellow';
									};
									passwordBox.onblur = function() {
										var request;
										if(window.XMLHttpRequest) {
											// code for IE7+, Firefox, Chrome, Opera, Safari
											request = new XMLHttpRequest();
										} else {
											// code for IE6, IE5
											request = new ActiveXObject('Microsoft.XMLHTTP');
										}
										
										request.stashedThis = this;
										
										request.onreadystatechange = function() {
											if(request.readyState == 4) {
												if(request.status == 200) {
													this.stashedThis.style.backgroundColor = null;
												} else {
													this.stashedThis.style.backgroundColor = 'red';
												}
											}
										}
										request.open('POST','update.php?<?php echo htmlspecialchars(SID); ?>',true);
										request.setRequestHeader('Content-type','application/x-www-form-urlencoded');
										request.send('password=' + encodeURIComponent(passwordBox.value));
									};
									passwordBox.onblur();
									document.body.appendChild(passwordBox);
									
									table = document.createElement('table');
									
									var headers = table.createTHead().insertRow(0);
									for(var displayColumn=0; displayColumn<cOutputs.length; displayColumn++) {
										var cell = headers.insertCell(-1);
										var title = document.createTextNode(cNames[displayColumn]);
										cell.appendChild(title);
									}
									var cell = headers.insertCell(-1);
									var title = document.createTextNode('DELETE');
									cell.appendChild(title);
									
									for(var row=0; row<newData.length; row++) {
										insertRowWithData(newData[row]);
									}
									
									document.body.appendChild(table);
									var newRowButton = document.createElement('button');
									newRowButton.appendChild(document.createTextNode('New Row'));
									newRowButton.onclick = function() {
										this.style.backgroundColor = 'yellow';
										
										var request;
										if(window.XMLHttpRequest) {
											// code for IE7+, Firefox, Chrome, Opera, Safari
											request = new XMLHttpRequest();
										} else {
											// code for IE6, IE5
											request = new ActiveXObject('Microsoft.XMLHTTP');
										}
										
										request.stashedThis = this;
										
										request.onreadystatechange = function() {
											if(request.readyState == 4) {
												if(request.status == 200) {
													this.stashedThis.style.backgroundColor = null;
												} else {
													this.stashedThis.style.backgroundColor = 'red';
												}
											}
										}
										request.open('POST','update.php?<?php echo htmlspecialchars(SID); ?>',true);
										request.setRequestHeader('Content-type','application/x-www-form-urlencoded');
										request.send('password=' + encodeURIComponent(passwordBox.value) +
													'&table=' + encodeURIComponent(pTable));
									};
									document.body.appendChild(newRowButton);
								} else {
									// loop through HTML table and remove red rows NOTE row=1 ships header row
									for(var row=1; row<table.rows.length; row++) {
										if(table.rows[row].style.backgroundColor == 'red') {
											table.deleteRow(row);
											row--; // fix moving indexes
										} else {
											// set all rows red
											table.rows[row].style.backgroundColor = 'red';
										}
									}

									
									
									// loop over new data cells and set row back to white
									for(var newDataRow=0; newDataRow<newData.length; newDataRow++) {
										// loop through table searching for newData[row][pRow]
										var foundMatch = false;
										for(var tableRow=0; tableRow<table.rows.length; tableRow++) {
											if(table.rows[tableRow].dataset.row == newData[newDataRow][pRow]) {
												table.rows[tableRow].style.backgroundColor = null;
												// if newData != data update html table and set row to green; set all cells to white
												for(var dataRow=0; dataRow<data.length; dataRow++) {
													if(data[dataRow][pRow] == newData[newDataRow][pRow]) {
														for(var displayColumn=0; displayColumn<cOutputs.length; displayColumn++) {
															if(data[dataRow][cOutputs[displayColumn]] != newData[newDataRow][cOutputs[displayColumn]]) {
																table.rows[tableRow].cells[displayColumn].getElementsByTagName('input')[0].value = newData[newDataRow][cOutputs[displayColumn]];
																table.rows[tableRow].cells[displayColumn].style.backgroundColor = null;
																table.rows[tableRow].style.backgroundColor = 'yellow';
															}
														}
														break;
													}
												}
												
												foundMatch = true;
												break;
											}
										}
										if(!foundMatch) {
											// if new cell does not corispond to existing row insert and set row to green
											tableRow = insertRowWithData(newData[newDataRow]);
											tableRow.style.backgroundColor = 'green';
										}
									}
								}
															refreshRate = 150000;
								if(newData.length < 1000)	refreshRate =  75000; 
								if(newData.length <  500)	refreshRate =  15000;
								if(newData.length <  250)	refreshRate =   5000;
								if(newData.length <  100)	refreshRate =   3000;
								if(newData.length <   50)	refreshRate =   2000;
								data = newData;
								document.body.style.backgroundColor = null;
							} else {
								document.body.style.backgroundColor = 'red';
							}
							setTimeout(function() {
								request.open('GET', jsonUrl, true);
								request.send();
							}, refreshRate);
						}
					}
					
					request.open('GET', jsonUrl, true);
					request.send();
				</script>
				<?php
			}
		?>
	</body>
</html>
