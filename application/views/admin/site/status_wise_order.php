
        <div class="content-wrapper">
          <div class="page-header">
            <h3 class="page-title">
              <span class="page-title-icon bg-gradient-primary text-white mr-2">
                <i class="fa fa-users"></i>                 
              </span>
           <?php echo $_GET['status']; ?> Lead
            </h3> 
          </div>
          <div class="row">
           <div class="col-md-12">
          
           
               
                 
             
                 
                 
                 
              
         
             
               <input type="hidden" name="status" id="status" value="<?php echo $_GET['status']; ?>">
                <div class="card">
            <div class="card-header">
                <i class="mdi mdi-table-large"></i><?php echo $_GET['status']; ?> Order List <!--<a href="<?php echo site_url() ?>create_lead?add=new" class="btn btn-gradient-info btn-sm"> Add Lead <i class="fa fa-plus"></i></a>-->
            </div>
              <div class="card-body">
                  
                 <?php if($this->session->userdata('success')){ ?>
                  <div class="alert alert-success">
                      <?php echo $this->session->userdata('success'); ?>
                  </div>
                 <?php $this->session->unset_userdata('success'); } ?>
                  <div class="table-responsive">
                      <table class="table table-bordered" id="all_orders_details">
                          <thead>
  <tr>
    <th>Tracking Status</th>
    <th>Username</th>
    <th>Customer Name</th>
    <th>Customer Mobile</th>
    <th>Alternate Number</th>
    <th>Pin Code</th>
    <th>Address</th>
    <th>Landmark</th>
    <th>State</th>
    <th>Post Office</th>
    <th>District</th>
    <th>Medicine</th>
    <th>COD</th>
    <th>Total Ammount</th>
    <th>Discount Type</th>
    <th>Discount Percent</th>
   
    <th>Order Date</th>
    <th>Action</th>
  </tr>
</thead>
 
                      </table>   
                  </div>
              </div>
            </div>
     
           </div>
          </div>   
        </div>
         
     