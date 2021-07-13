

<div class="app-page-title">
	<div class="page-title-wrapper">
		<div class="page-title-heading">
			<div class="page-title-icon">
				<i class="pe-7s-notebook text-success"></i>
			</div>
			<div><?php echo l('Import '). $this->company_name .l(' data'); ?>

		</div>
	</div>
</div>
</div>


<div class="main-card card">
	<div class="card-body">

<!-- <h4>
		The Import Includes:
	</h4> -->
<!-- <div class="grid-container">
  <div class="grid-item"><i class="fa fa-check" style="font-size:20px;color:green"></i> <label for="text2"> Rates</label></div>
  <div class="grid-item"><i class="fa fa-check" style="font-size:20px;color:green"></i> <label for="text2"> Rooms</label></div>
  <div class="grid-item"><i class="fa fa-check" style="font-size:20px;color:green"></i> <label for="text2"> Customers</label></div>
  <div class="grid-item"><i class="fa fa-check" style="font-size:20px;color:green"></i> <label for="text2"> Bookings</label></div>
  <div class="grid-item"><i class="fa fa-check" style="font-size:20px;color:green"></i> <label for="text2"> Charges</label></div>
  <div class="grid-item"><i class="fa fa-check" style="font-size:20px;color:green"></i> <label for="text2"> Payments</label></div>
  <div class="grid-item"><i class="fa fa-check" style="font-size:20px;color:green"></i> <label for="text2"> Company Settings</label></div>
</div>
 -->

	<form enctype="multipart/form-data" method="post" action="/settings/company/import_company_data">
<h4>Import Zip File: </h4>
 <input type="file" name="file">
<br><br>
<input type="submit" name="submit" value="Import" class="btn btn-primary"> <br><br>
</form>




</div>
</div>
