 <?php
$usertype = $this->session->userdata('USERTYPE');
$role     = trim($this->session->userdata('ROLE'));
?>
 <nav class="navbar default-layout-navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
      <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
  
          <a class="navbar-brand brand-logo" href="<?php echo site_url(); ?>admin-dashboard"><img style="height: 8vh !important;" src="<?php echo base_url(); ?>support_assets/images/logowhite.png" alt="logo"/></a>
          <a class="navbar-brand brand-logo-mini" href="<?php echo site_url(); ?>admin-dashboard"><img src="<?php echo base_url(); ?>support_assets/images/logowhite.png" alt="logo"/></a>
        
      </div>
      <div class="navbar-menu-wrapper d-flex align-items-stretch">
        <div class="search-field d-none d-md-block">
          <form class="d-flex align-items-center h-100" action="#">
            <div class="input-group">
            <h3 style="color:#fff;font-weight:bold;"><?php
echo isset($current->store_name)
    ? strtoupper($current->store_name)
    : '';
?></h3> 
            </div>
          </form>
        </div>
        <ul class="navbar-nav navbar-nav-right">
          <li class="nav-item nav-profile dropdown">
            <a class="nav-link dropdown-toggle" id="profileDropdown" href="#" data-toggle="dropdown" aria-expanded="false">
              <div class="nav-profile-img">
                <img src="<?php echo base_url(); ?>support_assets/images/faces/face1.jpg" alt="image">
                <span class="availability-status online"></span>             
              </div>
              <div class="nav-profile-text">
                <p class="mb-1 text-black"><?php echo $this->session->userdata('USERNAME'); ?></p>
              </div>
            </a>
            <div class="dropdown-menu navbar-dropdown" aria-labelledby="profileDropdown">
             <!-- <a class="dropdown-item" href="#">
                <i class="mdi mdi-cached mr-2 text-success"></i>
                Activity Log
              </a>-->
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="<?php echo site_url(); ?>Logout">
                <i class="mdi mdi-logout mr-2 text-primary"></i>
                Signout
              </a>
        
             
            </div>
          </li>
          <li class="nav-item nav-profile dropdown" id="get_data_notification">
          
          </li>
          
          <li class="nav-item d-none d-lg-block full-screen-link">
            <a class="nav-link">
              <i class="mdi mdi-fullscreen" id="fullscreen-button"></i>
            </a>
          </li>
          <li class="nav-item nav-logout d-none d-lg-block">
              <a class="nav-link" href="<?php echo site_url(); ?>Logout">
              <i class="mdi mdi-power"></i>
            </a>
          </li>
          
        </ul>
        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
          <span class="mdi mdi-menu"></span>
        </button>
      </div>
    </nav>

 <div class="container-fluid page-body-wrapper">
  <nav class="sidebar sidebar-offcanvas" id="sidebar">
        <ul class="nav">
          <li class="nav-item nav-profile">
            <a href="#" class="nav-link">
              <div class="nav-profile-image">
                <img src="<?php echo base_url(); ?>support_assets/images/faces/face1.jpg" alt="profile">
                <span class="login-status online"></span> <!--change to offline or busy as needed-->              
              </div>
              <div class="nav-profile-text d-flex flex-column">
                <span class="font-weight-bold mb-2"><?php echo $this->session->userdata('USERNAME'); ?></span>
               <!-- <span class="text-secondary text-small">Project Manager</span>-->
              </div>
              <i class="mdi mdi-bookmark-check text-success nav-profile-badge"></i>
            </a>
          </li>
          <li class="nav-item <?php if($title=='Home'){ echo 'active liactive'; } ?>">
              <a class="nav-link" href="<?php echo site_url(); ?>">
              <span class="menu-title">Dashboard</span>
              <i class="mdi mdi-home menu-icon"></i>
            </a>
          </li>
          
         <?php
         
         
         if ($role == 'Manager' || $role == 'Sub Admin' || $role == 'Analyst') { ?>
          <?php if($role != 'Analyst'){ ?>
<li class="nav-item <?php if($title=='Gautam Ayurveda Employee'){ echo 'active liactive'; } ?>">
            <a class="nav-link" data-toggle="collapse" href="#ui-users" aria-expanded="false" aria-controls="ui-basic">
              <span class="menu-title">Employee</span>
              <i class="menu-arrow"></i>
              <i class="fa fa-users menu-icon"></i>
            </a>
           <div class="collapse" id="ui-users">
    <ul class="nav flex-column sub-menu">

        <?php
        $role = trim($this->session->userdata('ROLE'));

        if ($role == 'Manager') {
        ?>

            <li class="nav-item">
                <a class="nav-link" href="<?php echo site_url(); ?>employee">Employee List</a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="<?php echo site_url(); ?>employee?add=new">Add New Employee</a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="<?php echo site_url(); ?>employee?subadmin=true">Add Sub Admin</a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="<?php echo site_url(); ?>login-logs">Employee Logs</a>
            </li>

        <?php
        } elseif ($role == 'Sub Admin') {
        ?>

            <li class="nav-item">
                <a class="nav-link" href="<?php echo site_url(); ?>login-logs">Employee Logs</a>
            </li>

        <?php
        }
        ?>

    </ul>
</div>
          </li><?php } ?>
          <?php if($role != 'Analyst'){ ?>

<li class="nav-item <?php if($title=='Gautam Ayurveda Call History'){ echo 'active liactive'; } ?>">
    <a class="nav-link" data-toggle="collapse" href="#ui-calls" aria-expanded="false" aria-controls="ui-basic">
        <span class="menu-title">Calls</span>
        <i class="menu-arrow"></i>
        <i class="fa fa-phone menu-icon"></i>
    </a>

    <div class="collapse" id="ui-calls">
        <ul class="nav flex-column sub-menu">
            <li class="nav-item">
                <a class="nav-link" href="<?php echo site_url(); ?>call-logs">Call History</a>
            </li>
        </ul>
    </div>
</li>

<?php } ?>
            <li class="nav-item <?php if($title=='Create Lead'){ echo 'active liactive'; } ?>">
            <a class="nav-link" data-toggle="collapse" href="#ui-lead" aria-expanded="false" aria-controls="ui-basic">
              <span class="menu-title">Lead</span>
              <i class="menu-arrow"></i>
              <i class="fa fa-leanpub menu-icon"></i>
            </a>
            <div class="collapse" id="ui-lead">
    <ul class="nav flex-column sub-menu">

        <li class="nav-item">
            <a class="nav-link" href="<?php echo site_url() ?>create_lead">
                All Lead List
            </a>
        </li>

        <?php if($role != 'Analyst'){ ?>
        <li class="nav-item">
            <a class="nav-link" href="<?php echo site_url() ?>create_lead?add=new">
                Add New Lead
            </a>
        </li>
        <?php } ?>

        <?php if($role == 'Manager' || $role == 'Sub Admin' || $role == 'Analyst'){ ?>
        <li class="nav-item <?php if(isset($title) && $title == 'Core Appointment'){ echo 'active liactive'; } ?>">
            <a class="nav-link" href="<?php echo site_url('CoreAppointment'); ?>">
                Appointments
            </a>
        </li>
        <?php } ?>

    </ul>
</div>
          </li>
          
          <?php if($role != 'Analyst'){ ?>
<li class="nav-item">
    <a class="nav-link" href="<?php echo site_url(); ?>duplicate">
        <span class="menu-title">Duplicate</span>
        <i class="fa fa-clock-o menu-icon"></i>
    </a>
</li>
<?php } ?>
         <?php }else{ ?>
          <li class="nav-item <?php if($title=='Employee Lead'){ echo 'active liactive'; } ?>">
            <a class="nav-link" data-toggle="collapse" href="#ui-lead" aria-expanded="false" aria-controls="ui-basic">
              <span class="menu-title">Lead</span>
              <i class="menu-arrow"></i>
              <i class="fa fa-leanpub menu-icon"></i>
            </a>
            <div class="collapse" id="ui-lead">
              <ul class="nav flex-column sub-menu">
                  <li class="nav-item"> <a class="nav-link" href="<?php echo site_url() ?>employee_created_leads">All Lead List</a></li>
                  <li class="nav-item"> <a class="nav-link" href="<?php echo site_url() ?>employee_created_leads?add=new">Add New Lead</a></li> 
              </ul>
            </div>
          </li>
         
        
         <?php } ?>
         <?php if ($role == 'Manager' || $role == 'Sub Admin') { ?>
         <li class="nav-item <?php if(isset($lead_name) && $title==$lead_name.' Lead'){ echo 'active liactive'; }?>">
            <a class="nav-link" data-toggle="collapse" href="#ui-leads" aria-expanded="false" aria-controls="ui-leads">
              <span class="menu-title">Lead Filter</span>
              <i class="menu-arrow"></i>
              <i class="fa fa-filter menu-icon"></i>
            </a>
            <div class="collapse" id="ui-leads">
    <ul class="nav flex-column sub-menu">

    <?php 
    $query = $this->GenericModel->get_record("tbl_status","*"); 

    if($this->session->userdata('USERTYPE')=='Admin' OR $this->session->userdata('ROLE')=='Sub Admin'){
        $base_url = "https://team.gautamclinics.com/create_lead?";
    } else {
        $base_url = "https://team.gautamclinics.com/employee_created_leads?";
    }
    ?>

    <?php foreach($query as $row){ 
        $lead_name = $row->status; 
        $encoded_status = urlencode($lead_name);
    ?>

    <li class="nav-item <?php if($title == $lead_name.' Lead'){ echo 'active liactive'; }?>">
        <a class="nav-link" href="<?php echo $base_url . 'status=' . $encoded_status; ?>">
            <span class="menu-title"><?php echo $lead_name; ?> </span>
        </a>
    </li>

    <?php } ?>

    </ul>
</div>
          </li>
          <?php } ?>
          <?php if($this->session->userdata('USERTYPE')=='Employee'){ ?>
           <!--<li class="nav-item">
            <a class="nav-link" href="<?php echo site_url(); ?>dialer">
              <span class="menu-title">Dialer</span>
              <i class="fa fa-mobile menu-icon"></i>
            </a>
           
          </li>-->
          <li class="nav-item">
            <a class="nav-link" href="<?php echo site_url(); ?>timer">
              <span class="menu-title">Timer</span>
              <i class="fa fa-clock-o menu-icon"></i>
            </a>
           
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?php echo site_url(); ?>duplicate">
              <span class="menu-title">Duplicate
              </span>
              <i class="fa fa-clock-o menu-icon"></i>
            </a>
           
          </li>
          <?php } ?>
          <?php if($role == 'Analyst'){ ?>

<li class="nav-item">
    <a class="nav-link" data-toggle="collapse" href="#ui-leads">
        <span class="menu-title">Lead Filter</span>
        <i class="menu-arrow"></i>
        <i class="fa fa-filter menu-icon"></i>
    </a>

    <div class="collapse" id="ui-leads">
        <ul class="nav flex-column sub-menu">

        <?php
        $query = $this->GenericModel->get_record("tbl_status","*");

        foreach($query as $row){
        ?>

            <li class="nav-item">
                <a class="nav-link"
                   href="<?php echo site_url(); ?>create_lead?status=<?php echo urlencode($row->status); ?>">
                    <?php echo $row->status; ?>
                </a>
            </li>

        <?php } ?>

        </ul>
    </div>
</li>



<?php } ?>
        </ul>
      </nav>
     
     
     
     