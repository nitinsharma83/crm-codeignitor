
 <script src="<?php echo base_url(); ?>support_assets/js/jquery.min.js"></script>
 <script src="<?php echo base_url(); ?>support_assets/js/bootstrap.min.js"></script>
 <script src="<?php echo base_url(); ?>support_assets/js/jquery-ui.js"></script>
 <script src="<?php echo base_url(); ?>support_assets/js/jquery.validate.min.js"></script>
 <script src="<?php echo base_url(); ?>support_assets/js/jquery.dataTables.min.js"></script>
 <script src="<?php echo base_url(); ?>support_assets/js/dataTables.bootstrap4.min.js"></script>

 <script src="<?php echo base_url(); ?>support_assets/js/main.js"></script> 
 <script src="<?php echo base_url(); ?>support_assets/vendors/js/vendor.bundle.base.js"></script>
 <script src="<?php echo base_url(); ?>support_assets/vendors/js/vendor.bundle.addons.js"></script>
 <script src="<?php echo base_url(); ?>support_assets/js/off-canvas.js"></script>
 <script src="<?php echo base_url(); ?>support_assets/js/misc.js"></script>
 <script src="<?php echo base_url(); ?>support_assets/js/dashboard.js"></script>
 <style>
  .table th img, .table td img {
    width: 100%;
    height: unset;
    border-radius: unset;
}

#followupPopup {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.6);
    z-index: 9999;
}

.popup-box {
    position: absolute;
    top: 20%;
    left: 50%;
    transform: translateX(-50%);
    background: #fff;
    padding: 25px;
    width: 400px;
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(0,0,0,0.3);
    animation: pop 0.3s ease;
}

@keyframes pop {
    from { transform: translate(-50%, -50%) scale(0.5); }
    to { transform: translate(-50%, 0) scale(1); }
}

.close-btn {
    position: absolute;
    right: 15px;
    top: 10px;
    font-size: 20px;
    cursor: pointer;
}

.popup-box h4 {
    color: #ff5722;
    margin-bottom: 15px;
}
</style>
<div id="followupPopup" style="display:none;">
    <div class="popup-box">
        
        <span class="close-btn">&times;</span>

        <h4>📞 Follow-up Reminder</h4>

        <div id="followupContent"></div>

    </div>
</div>
<script>
function checkFollowups(){
    
    $.get("<?php echo base_url('Api_Controller/get_followups'); ?>", function(res){

        let data = JSON.parse(res);

        if(data.length > 0){

            let html = '';

            data.forEach(function(item){

                html += `
<div class="followup-item" style="border-bottom:1px solid #ddd;padding:10px;position:relative;">
    
    <span class="close-single" data-id="${item.id}" 
    style="position:absolute;right:10px;top:5px;cursor:pointer;font-size:18px;">×</span>

    <strong>${item.customer_name}</strong><br>
📱 ${item.mobile_no.replace(/\D/g,'').length >= 10 
? item.mobile_no.replace(/\D/g,'').substring(0,4) + 'XXXXXX' 
: item.mobile_no}<br>

    <a class="btn btn-info btn-sm mt-2 view-btn" 
    data-id="${item.id}"
    href="https://team.gautamclinics.com/detail_lead_details?view=${btoa(item.id)}">
    View Lead
    </a>

</div>
`;
            });

            $("#followupContent").html(html);
            $("#followupPopup").fadeIn();

            // 🔔 optional sound
            let audio = new Audio("https://www.soundjay.com/buttons/beep-01a.mp3");
            audio.play();
        }

    });
}

// check every 30 seconds
setInterval(checkFollowups, 30000);

// ✅ When clicking VIEW
$(document).on('click','.view-btn', function(){

    let id = $(this).data('id');

    $.post("<?php echo base_url('Api_Controller/mark_followup_handled'); ?>", {
        lead_id: id
    });

});


// ✅ When clicking CLOSE (X)
$(document).on('click','.close-single', function(){

    let id = $(this).data('id');

    let row = $(this).closest('.followup-item');

    $.post("<?php echo base_url('Api_Controller/mark_followup_handled'); ?>", {
        lead_id: id
    });

    row.remove();

    // close popup if empty
    if($('.followup-item').length == 0){
        $("#followupPopup").fadeOut();
    }

});
$(document).on('click', '.close-btn', function () {
    $("#followupPopup").fadeOut();
});
</script>
<script>
setInterval(checkNewLead, 5000); // every 5 sec

function checkNewLead() {
    $.ajax({
        url: "<?= site_url('Lead/get_popup_lead') ?>",
        type: "GET",
        dataType: "json",
        success: function(res){

            if(res.status){

                let lead = res.data;
                let pid = btoa(lead.id);
                let status = encodeURIComponent(lead.status);

                let html = `
                    <p><strong>Name:</strong> ${lead.customer_name}</p>
                    <p>
<strong>Mobile:</strong> ${
lead.mobile_no.replace(/\D/g,'').length >= 10
? lead.mobile_no.replace(/\D/g,'').substring(0,4) + 'XXXXXX'
: lead.mobile_no
}
</p>
                    <p><strong>Requirement:</strong> ${lead.requirement}</p>
                    <p><strong>Status:</strong> ${lead.status}</p>

                    <div class="text-center mt-3">
                        <a href="detail_lead_details?view=${pid}&lead_status=${status}" 
                           class="btn btn-primary m-1">
                           👁 View Lead
                        </a>

                        <a href="?call=${pid}" 
                           class="btn btn-success m-1">
                           📞 Call Now
                        </a>
                    </div>
                `;

                $("#leadModalBody").html(html);

                $("#autoLeadModal").modal({
                    backdrop: 'static',
                    keyboard: false
                });

                $("#autoLeadModal").modal('show');
            }

        }
    });
}

</script>
<div class="modal fade" id="autoLeadModal" tabindex="-1">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">📞 New Call Lead</h5>
      </div>

      <div class="modal-body" id="leadModalBody">
        <!-- AJAX content will come here -->
      </div>

    </div>
  </div>
</div>