<?php
/* Generate Payslip view
*/
?>
<style>
    .theme-settings-open-btn {
        display: none !important;
    }
</style>
<?php $session = $this->session->userdata('username'); ?>
<?php $user_info = $this->Xin_model->read_user_info($session['user_id']); ?>
<?php $role_resources_ids = $this->Xin_model->user_role_resource(); ?>
<?php $get_animate = $this->Xin_model->get_content_animate(); ?>
<?php $system = $this->Xin_model->read_setting_info(1); ?>
<?php
$is_half_col = '5';
if ($system[0]->is_half_monthly == 1) {
    $bulk_form_url = 'admin/payroll/add_half_pay_to_all';
    $is_half_col = '12';
} else {
    $bulk_form_url = 'admin/payroll/add_pay_to_all';
    $is_half_col = '5';
}
?>
<?php $role_resources_ids = $this->Xin_model->user_role_resource(); ?>
<hr class="border-light m-0 mb-3" style="display:none;">
<div class="ui-bordered px-4 pt-4 mb-4">
    <?php $attributes = array('name' => 'set_salary_details', 'id' => 'set_salary_details', 'class' => 'm-b-1 add form-hrm'); ?>
    <?php $hidden = array('user_id' => $session['user_id']); ?>
    <?php echo form_open('admin/payroll/management_approval_list', $attributes, $hidden); ?>
    <div class="form-row">
        
        <div class="col-md-12">
        <?php $this->load->view('admin/common_filter/filter_company_location_department_employee_payoll_month_wise'); ?>
        </div>

       

        <div class="col-md-4 col-xl-2 mb-4">
            <label class="form-label d-none d-md-block">&nbsp;</label>
            <button type="submit" class="btn btn-secondary btn-block"> <i class="fas fa-search"></i> Search </button>
        </div>
       
    </div>
    <?php echo form_close(); ?>
</div>


<?php $attributes = array('name' => 'management_payroll_approval_status_change_form', 'id' => 'management_payroll_approval_status_change_form', 'method' => 'post'); ?>
<?php $hidden = array('user_id' => $session['user_id']); ?>
<?php echo form_open('admin/payroll/management_payroll_approval_status_change', $attributes, $hidden); ?>
<input type="hidden" name="e_location_id" id="e_location_id" value=''>
<input type="hidden" name="e_month_year" id="e_month_year" value=''>
<input type="hidden" name="e_checked_ids" id="e_checked" value=''>
<!--<input type="hidden" name="e_unchecked_ids" id="e_unchecked" value=''>-->
<?php echo form_close(); ?>

<div id="pdf_div">
    <?php $attributes = array('name' => 'management_approval_pdf', 'id' => 'management_approval_pdf', 'method' => 'post'); ?>
    <?php $hidden = array('user_id' => $session['user_id']); ?>
    <?php echo form_open('admin/payroll/management_approval_pdf_excel_new', $attributes, $hidden); ?>
    <input type="hidden" name="type" value="1">
    <input type="hidden" name="download_format" value="pdf_format">
    <!-- <input type="hidden" name="p_department_id" id="p_department_id" class="p_department_id"> -->
    <input type="hidden" name="p_employee_id" id="p_employee_id" class="p_employee_id">
    <input type="hidden" name="p_company_id" id="p_company_id" class="p_company_id">
    <input type="hidden" name="p_location_id" id="p_location_id" class="p_location_id">
    <input type="hidden" name="p_department_id" id="p_department_id" class="p_department_id">
    <input type="hidden" name="p_month_year" id="p_month_year" class="p_month_year">
    <?php echo form_close(); ?>
</div>
<div id="excel_div">
    <?php $attributes = array('name' => 'management_excel', 'id' => 'management_excel', 'method' => 'post'); ?>
    <?php $hidden = array('user_id' => $session['user_id']); ?>
    <?php echo form_open('admin/payroll/management_approval_pdf_excel_new', $attributes, $hidden); ?>
    <input type="hidden" name="type" value="2">
    <input type="hidden" name="download_format" value="excel_format">
    <input type="hidden" name="p_department_id" id="p_department_id" class="p_department_id">
    <input type="hidden" name="p_employee_id" id="p_employee_id" class="p_employee_id">
    <input type="hidden" name="p_company_id" id="p_company_id" class="p_company_id">
    <input type="hidden" name="p_location_id" id="p_location_id" class="p_location_id">
    <input type="hidden" name="p_month_year" id="p_month_year" class="p_month_year">
    <?php echo form_close(); ?>
</div>

<div class="card <?php echo $get_animate; ?>">
    <div class="box-header with-border">
        <div id="accordion">
            <div class="card-header with-elements"> <span class="card-header-title mr-2"><strong><?php echo $this->lang->line('xin_payment_info_for'); ?></strong>
                    <span id="payroll_date"><?php echo date('Y-m'); ?></span></span>
            </div>
        </div>
    </div>
    <div class="card-body">

        <div class="box-datatable table-responsive">
            <table class="datatables-demo table table-striped table-bordered" id="xin_table">
                <thead>
                    <tr>

                        <th>ID</th>
                        <th><?php echo $this->lang->line('xin_name'); ?></th>
                        <th><?php echo $this->lang->line('xin_salary_type'); ?></th>
                        <!-- <th><//?php echo $this->lang->line('xin_salary_title'); ?></th> -->
                        <th><?php echo 'Basic Salary'; ?></th>
                        <th><?php echo $this->lang->line('xin_payroll_net_salary'); ?></th>
                        <th><?php echo $this->lang->line('dashboard_xin_status'); ?></th>
                        <th>Approval Action</th>
                        <?php if (in_array('276', $role_resources_ids) || in_array('273', $role_resources_ids)) { ?>
                            <th><?php echo $this->lang->line('xin_action'); ?></th>
                        <?php } else {
                        } ?>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<div class="col-md-12" style="padding-left: 84%;">
    <label class="form-label d-none d-md-block">&nbsp;</label>
    <button type="button" class="btn btn-secondary btn-block" style="background-color: #0747A3;" id="approve_selected">
        <i class="fas fa-check-square"></i> Approve </button>
</div>


<style type="text/css">
    .hide-calendar .ui-datepicker-calendar {
        display: none !important;
    }

    .hide-calendar .ui-priority-secondary {
        display: none !important;
    }

    .select2-container {
        z-index: unset !important;
    }

    /*.textcolor{
  color:#0747a3!important;
}
.table th {color:#0747a3!important;}*/

    nav>.nav.nav-tabs {

        border: none;
        color: #fff !important;
        background: #0747a3;
        border-radius: 0;
        width: 307px !important;

    }

    nav>div a.nav-item.nav-link.active:after {
        display: none;
        content: "";
        position: relative;
        bottom: -60px;
        left: -10%;
        border: 15px solid transparent;
        border-top-color: #0747a3;
    }

    .tab-content {
        background: #fdfdfd;
        line-height: 25px;
        border: 1px solid #ddd;
        /*border-top:5px solid transparent;
    border-bottom:5px solid transparent;*/
        /*padding:30px 25px;*/
    }
    