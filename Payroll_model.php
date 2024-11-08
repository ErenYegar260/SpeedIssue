<?php
defined('BASEPATH') or exit('No direct script access allowed');

class payroll_model extends CI_Model
{

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	// get payroll templates
	public function get_templates()
	{
		return $this->db->get("xin_salary_templates");
	}

	// get payroll templates > for companies
	public function get_comp_template($cid, $id)
	{

		$sql = 'SELECT * FROM xin_employees WHERE company_id = ? and user_role_id!=?';
		$binds = array($cid, 1);
		$query = $this->db->query($sql, $binds);
		return $query;
	}

	// get payroll templates > employee/company
	public function get_employee_comp_template($cid, $id, $lid)
	{
		if (!empty($cid)) {
			$this->db->where('company_id', $cid);
		}
		if (!empty($lid)) {
			$lids = explode(',', $lid);
			$this->db->where_in('location_id', $lids);
		}
		if (!empty($id)) {
			$ids = explode(',', $id);
			$this->db->where_in('user_id', $ids);
		}
		$this->db->where('user_id!=', 1);
		$this->db->where('super_privileges!=', 1);
		$query = $this->db->get('xin_employees');

		return $query;
	}

	// get total hours work > hourly template > payroll generate
	public function total_hours_worked($id, $attendance_date)
	{

		$sql = 'SELECT * FROM xin_attendance_time WHERE employee_id = ? and attendance_date like ?';
		$binds = array($id, '%' . $attendance_date . '%');
		$query = $this->db->query($sql, $binds);
		return $query;
	}

	// get total hours work > hourly template > payroll generate
	public function total_hours_worked_payslip($id, $attendance_date)
	{
		$sql = 'SELECT * FROM xin_attendance_time WHERE employee_id = ? and attendance_date like ?';
		$binds = array($id, '%' . $attendance_date . '%');
		$query = $this->db->query($sql, $binds);
		return $query;
	}

	// get advance salaries > all employee
	public function get_advance_salaries($company_id = 0)
	{
		if ($company_id > 0) {
			$this->db->where('company_id', $company_id);
		}
		$this->db->order_by('advance_salary_id', 'desc');
		return $this->db->get("xin_advance_salaries");
	}

	// get advance salaries > single employee
	public function get_advance_salaries_single($id)
	{

		$sql = 'SELECT * FROM xin_advance_salaries WHERE employee_id = ? ORDER BY advance_salary_id DESC';
		$binds = array($id);
		$query = $this->db->query($sql, $binds);
		return $query;
	}

	// get advance salaries report
	public function get_advance_salaries_report()
	{
		$this->db->query("SET SESSION sql_mode = ''");
		$return = $this->db->query("SELECT advance_salary_id,employee_id,company_id,month_year,one_time_deduct,monthly_installment,reason,status,total_paid,is_deducted_from_salary,created_at,SUM(`xin_advance_salaries`.advance_amount) AS advance_amount FROM `xin_advance_salaries` where status=1 group by employee_id");
		return $return;
	}

	// get advance salaries report >> single employee > current user
	public function advance_salaries_report_single($id)
	{

		$this->db->query("SET SESSION sql_mode = ''");
		$sql = 'SELECT advance_salary_id,employee_id,company_id,month_year,one_time_deduct,monthly_installment,reason,status,total_paid,is_deducted_from_salary,created_at,SUM(`xin_advance_salaries`.advance_amount) AS advance_amount FROM `xin_advance_salaries` where status=1 and employee_id = ? group by employee_id';
		$binds = array($id);
		$query = $this->db->query($sql, $binds);
		return $query;
	}


	// get payment history > all payslips
	public function all_payment_history()
	{
		return $this->db->get("xin_make_payment");
	}

	// new payroll > payslip
	public function employees_payment_history()
	{
		return $this->db->get("xin_salary_payslips");
	}

	// currency_converter
	public function get_currency_converter()
	{
		return $this->db->get("xin_currency_converter");
	}

	// get payslips of single employee
	public function get_payroll_slip($id)
	{

		$sql = 'SELECT * FROM xin_salary_payslips WHERE employee_id = ? and status = ?';
		$binds = array($id, 2);
		$query = $this->db->query($sql, $binds);
		return $query;
	}

	public function get_company_payslips($id)
	{

		$sql = 'SELECT * FROM xin_salary_payslips WHERE company_id = ? and status = ?';
		$binds = array($id, 2);
		$query = $this->db->query($sql, $binds);
		return $query;
	}

	// new payroll > payslip
	public function all_employees_payment_history()
	{
		$sql = 'SELECT * FROM xin_salary_payslips';
		$query = $this->db->query($sql);
		return $query;
	}

	// new payroll > payslip
	public function all_employees_payment_history_month($salary_month)
	{
		$sql = 'SELECT * FROM xin_salary_payslips WHERE salary_month = ?';
		$binds = array($salary_month);
		$query = $this->db->query($sql, $binds);
		return $query;
	}

	// get payslip history > company
	public function get_company_payslip_history($company_id)
	{

		$sql = 'SELECT * FROM xin_salary_payslips WHERE company_id = ?';
		$binds = array($company_id);
		$query = $this->db->query($sql, $binds);
		return $query;
	}

	// get payslip history > company
	public function get_company_payslip_history_month($company_id, $salary_month)
	{

		$sql = 'SELECT * FROM xin_salary_payslips WHERE company_id = ? and salary_month = ?';
		$binds = array($company_id, $salary_month);
		$query = $this->db->query($sql, $binds);
		return $query;
	}

	// get company/location payslips
	public function get_company_location_payslips($company_id, $location_id)
	{

		$sql = 'SELECT * FROM xin_salary_payslips WHERE company_id = ? and location_id = ?';
		$binds = array($company_id, $location_id);
		$query = $this->db->query($sql, $binds);
		return $query;
	}

	// get company/location payslips
	public function get_company_location_payslips_month($company_id, $location_id, $salary_month)
	{

		$sql = 'SELECT * FROM xin_salary_payslips WHERE company_id = ? and location_id = ? and salary_month = ?';
		$binds = array($company_id, $location_id, $salary_month);
		$query = $this->db->query($sql, $binds);
		return $query;
	}

	// get company/location/departments payslips
	public function get_company_location_department_payslips($company_id, $location_id, $department_id)
	{

		$sql = 'SELECT * FROM xin_salary_payslips WHERE company_id = ? and location_id = ? and department_id = ?';
		$binds = array($company_id, $location_id, $department_id);
		$query = $this->db->query($sql, $binds);
		return $query;
	}

	// get company/location/departments payslips
	public function get_company_location_department_payslips_month($company_id, $location_id, $department_id, $salary_month)
	{

		$sql = "SELECT * FROM xin_employee_exit WHERE is_inactivate_account = 1 AND DATE_FORMAT(exit_date, '%Y-%m') < '$salary_month'";
		$query = $this->db->query($sql);
		$in_active_employees = $query->result_array();
		$in_active_ids = array_column($in_active_employees, 'employee_id');
		$ids = implode(',', $in_active_ids);

		$this->db->select("*");
		$this->db->from('xin_salary_payslips');
		if ((!empty($company_id))) {
			$this->db->where('company_id', $company_id);
		}
		if ((!empty($location_id))) {
			$location_ids = explode(',',  $location_id);
			$this->db->where_in('location_id', $location_ids);
		}
		if ((!empty($department_id))) {
			$department_ids = explode(',',  $department_id);
			$this->db->where_in('department_id', $department_ids);
		}
		if ((!empty($salary_month))) {
			$this->db->where('salary_month', $salary_month);
		}
		if ((!empty($in_active_ids))) {
			$this->db->where_not_in('employee_id',$in_active_ids);
		}
		$query = $this->db->get();
		return $query;

		
	}

	// get company/location/departments payslips
	public function get_company_location_department_designation_payslips($company_id, $location_id, $department_id, $designation_id)
	{

		$sql = 'SELECT * FROM xin_salary_payslips WHERE company_id = ? and location_id = ? and department_id = ? and designation_id = ?';
		$binds = array($company_id, $location_id, $department_id, $designation_id);
		$query = $this->db->query($sql, $binds);
		return $query;
	}

	/// pay to all
	// get all employees
	public function get_all_employees()
	{
		$sql = 'SELECT * FROM xin_employees WHERE user_role_id!=?';
		$binds = array(1);
		$query = $this->db->query($sql, $binds);
		return $query;
	}

	// get payslip bulk > company
	public function get_company_payroll_employees($company_id)
	{

		$sql = 'SELECT * FROM xin_employees WHERE user_role_id!=1 and company_id = ?';
		$binds = array($company_id);
		$query = $this->db->query($sql, $binds);
		return $query;
	}

	// get payslip bulk > company|location
	public function get_company_location_payroll_employees($company_id, $location_id)
	{

		$sql = 'SELECT * FROM xin_employees WHERE user_role_id!=1 and company_id = ? and location_id = ?';
		$binds = array($company_id, $location_id);
		$query = $this->db->query($sql, $binds);
		return $query;
	}

	// get payslip bulk > company|location|department
	public function get_company_location_dep_payroll_employees($company_id, $location_id, $department_id)
	{

		$this->db->select("*");
        $this->db->from('xin_employees');
        if((!empty($company_id))){
            $this->db->where('company_id', $company_id);
        }
        if((!empty($location_id))){
            $this->db->where_in('location_id', $location_id);
        }
        if((!empty($department_id))){
            $this->db->where_in('department_id', $department_id);
        }
        
        $this->db->where('user_role_id != ', 1);
		$this->db->where('is_active', 1);
        $query = $this->db->get();
        return $query;
	}

	// get hourly wages
	public function get_hourly_wages()
	{
		return $this->db->get("xin_hourly_templates");
	}

	public function read_template_information($id)
	{

		$sql = 'SELECT * FROM xin_salary_templates WHERE salary_template_id = ?';
		$binds = array($id);
		$query = $this->db->query($sql, $binds);

		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return null;
		}
	}

	// get request date details > advance salary
	public function requested_date_details($id)
	{

		$sql = 'SELECT * FROM `xin_advance_salaries` WHERE employee_id = ? and status = ?';
		$binds = array($id, 1);
		$query = $this->db->query($sql, $binds);

		return $query;
	}

	public function read_hourly_wage_information($id)
	{

		$sql = 'SELECT * FROM xin_hourly_templates WHERE hourly_rate_id = ?';
		$binds = array($id);
		$query = $this->db->query($sql, $binds);

		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return null;
		}
	}

	public function read_currency_converter_information($id)
	{

		$sql = 'SELECT * FROM xin_currency_converter WHERE currency_converter_id = ?';
		$binds = array($id);
		$query = $this->db->query($sql, $binds);

		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return null;
		}
	}

	// get advance salaries report > view all
	public function advance_salaries_report_view($id)
	{

		$this->db->query("SET SESSION sql_mode = ''");
		$sql = 'SELECT advance_salary_id,company_id,employee_id,month_year,one_time_deduct,monthly_installment,reason,status,total_paid,is_deducted_from_salary,created_at,SUM(`xin_advance_salaries`.advance_amount) AS advance_amount FROM `xin_advance_salaries` where status=1 and employee_id= ? group by employee_id';
		$binds = array($id);
		$query = $this->db->query($sql, $binds);
		return $query->result();
	}

	public function read_make_payment_information($id)
	{

		$sql = 'SELECT * FROM xin_make_payment WHERE make_payment_id = ?';
		$binds = array($id);
		$query = $this->db->query($sql, $binds);

		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return null;
		}
	}

	public function read_payslip_information($id)
	{

		$sql = 'SELECT * FROM xin_salary_payslips WHERE payslip_id = ?';
		$binds = array($id);
		$query = $this->db->query($sql, $binds);

		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return null;
		}
	}

	// Function to Delete selected record from table
	public function delete_record($id)
	{
		$this->db->where('payslip_id', $id);
		$this->db->delete('xin_salary_payslips');
	}

	// Function to Delete selected record from table
	public function delete_payslip_allowances_items($id)
	{
		$this->db->where('payslip_id', $id);
		$this->db->delete('xin_salary_payslip_allowances');
	}

	// Function to Delete selected record from table
	public function delete_payslip_commissions_items($id)
	{
		$this->db->where('payslip_id', $id);
		$this->db->delete('xin_salary_payslip_commissions');
	}

	// Function to Delete selected record from table
	public function delete_payslip_loan_items($id)
	{
		$this->db->where('payslip_id', $id);
		$this->db->delete('xin_salary_payslip_loan');
	}

	public function delete_payslip_indemnity_items($data, $id)
	{
		$this->db->where('payslip_id', $id);
		if ($this->db->update('xin_employee_indemnity', $data)) {
			return true;
		} else {
			return false;
		}
	}

	// Function to Delete selected record from table
	public function delete_payslip_other_payment_items($id)
	{
		$this->db->where('payslip_id', $id);
		$this->db->delete('xin_salary_payslip_other_payments');
	}

	// Function to Delete selected record from table
	public function delete_payslip_overtime_items($id)
	{
		$this->db->where('payslip_id', $id);
		$this->db->delete('xin_salary_payslip_overtime');
	}

	// Function to Delete selected record from table
	public function delete_payslip_statutory_deductions_items($id)
	{
		$this->db->where('payslip_id', $id);
		$this->db->delete('xin_salary_payslip_statutory_deductions');
	}

	public function delete_payslip_advance_items($id)
	{
		$this->db->where('payslip_id', $id);
		$salary_payslip_advance_amount = $this->db->get('xin_salary_payslip_advance_amounts')->row();

		if ($salary_payslip_advance_amount) {
			$advance_salary_id = $salary_payslip_advance_amount->advance_salary_id;

			$this->db->where('advance_salary_id', $advance_salary_id);
			$advance_salary = $this->db->get('xin_advance_salaries')->row();

			if ($advance_salary) {
				$total_paid = $advance_salary->total_paid;
				$total_paid_new = $total_paid - $salary_payslip_advance_amount->amount;

				$this->db->where('advance_salary_id', $advance_salary_id);
				$this->db->update('xin_advance_salaries', array(
					'total_paid' => $total_paid_new,
				));
			}
		}

		$this->db->where('payslip_id', $id);
		$this->db->delete('xin_salary_payslip_advance_amounts');
	}

	public function read_advance_salary_info($id)
	{

		$sql = 'SELECT * FROM xin_advance_salaries WHERE advance_salary_id = ?';
		$binds = array($id);
		$query = $this->db->query($sql, $binds);

		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return null;
		}
	}

	// get advance salary by employee id >paid.total
	public function get_paid_salary_by_employee_id($id)
	{

		$this->db->query("SET SESSION sql_mode = ''");
		$sql = 'SELECT advance_salary_id,employee_id,month_year,one_time_deduct,monthly_installment,reason,status,total_paid,is_deducted_from_salary,created_at,SUM(`xin_advance_salaries`.advance_amount) AS advance_amount FROM `xin_advance_salaries` where status=1 and employee_id=? group by employee_id';
		$binds = array($id);
		$query = $this->db->query($sql, $binds);
		return $query->result();
	}

	// get advance salary by employee id
	public function advance_salary_by_employee_id($id)
	{

		$sql = 'SELECT * FROM xin_advance_salaries WHERE employee_id = ? and status = ? order by advance_salary_id desc';
		$binds = array($id, 1);
		$query = $this->db->query($sql, $binds);

		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return null;
		}
	}


	// Function to add record in table
	public function add_template($data)
	{
		$this->db->insert('xin_salary_templates', $data);
		if ($this->db->affected_rows() > 0) {
			return true;
		} else {
			return false;
		}
	}

	// Function to add record in table > advance salary
	public function add_advance_salary_payroll($data)
	{
		$this->db->insert('xin_advance_salaries', $data);
		if ($this->db->affected_rows() > 0) {
			//return true;
			return $this->db->insert_id();
		} else {
			return false;
		}
	}

	// Function to add record in table
	public function add_hourly_wages($data)
	{
		$this->db->insert('xin_hourly_templates', $data);
		if ($this->db->affected_rows() > 0) {
			return true;
		} else {
			return false;
		}
	}

	// Function to add record in table
	public function add_currency_converter($data)
	{
		$this->db->insert('xin_currency_converter', $data);
		if ($this->db->affected_rows() > 0) {
			return true;
		} else {
			return false;
		}
	}

	// Function to add record in table
	public function add_monthly_payment_payslip($data)
	{
		$this->db->insert('xin_make_payment', $data);
		if ($this->db->affected_rows() > 0) {
			return true;
		} else {
			return false;
		}
	}

	// Function to add record in table
	public function add_hourly_payment_payslip($data)
	{
		$this->db->insert('xin_make_payment', $data);
		if ($this->db->affected_rows() > 0) {
			return true;
		} else {
			return false;
		}
	}

	// Function to Delete selected record from table
	public function delete_template_record($id)
	{
		$this->db->where('salary_template_id', $id);
		$this->db->delete('xin_salary_templates');
	}

	// Function to Delete selected record from table
	public function delete_hourly_wage_record($id)
	{
		$this->db->where('hourly_rate_id', $id);
		$this->db->delete('xin_hourly_templates');
	}

	// Function to Delete selected record from table
	public function delete_currency_converter_record($id)
	{
		$this->db->where('currency_converter_id', $id);
		$this->db->delete('xin_currency_converter');
	}

	// Function to Delete selected record from table
	public function delete_advance_salary_record($id)
	{
		$this->db->where('advance_salary_id', $id);
		$this->db->delete('xin_advance_salaries');
	}

	// Function to update record in table
	public function update_template_record($data, $id)
	{
		$this->db->where('salary_template_id', $id);
		if ($this->db->update('xin_salary_templates', $data)) {
			return true;
		} else {
			return false;
		}
	}

	// get all hourly templates
	public function all_hourly_templates()
	{
		$query = $this->db->query("SELECT * from xin_hourly_templates");
		return $query->result();
	}

	// get all salary tempaltes > payroll templates
	public function all_salary_templates()
	{
		$query = $this->db->query("SELECT * from xin_salary_templates");
		return $query->result();
	}

	// Function to update record in table
	public function update_hourly_wages_record($data, $id)
	{
		$this->db->where('hourly_rate_id', $id);
		if ($this->db->update('xin_hourly_templates', $data)) {
			return true;
		} else {
			return false;
		}
	}

	// Function to update record in table
	public function update_currency_converter_record($data, $id)
	{
		$this->db->where('currency_converter_id', $id);
		if ($this->db->update('xin_currency_converter', $data)) {
			return true;
		} else {
			return false;
		}
	}

	// Function to update record in table > manage salary
	public function update_salary_template($data, $id)
	{
		$this->db->where('user_id', $id);
		if ($this->db->update('xin_employees', $data)) {
			return true;
		} else {
			return false;
		}
	}

	// Function to update record in table > deduction of advance salary
	public function updated_advance_salary_paid_amount($data, $id)
	{
		$this->db->where('employee_id', $id);
		if ($this->db->update('xin_advance_salaries', $data)) {
			return true;
		} else {
			return false;
		}
	}

	// Function to update record in table > advance salary
	public function updated_advance_salary_payroll($data, $id)
	{
		$this->db->where('advance_salary_id', $id);
		if ($this->db->update('xin_advance_salaries', $data)) {
			return true;
		} else {
			return false;
		}
	}

	// Function to update record in table > empty grade status
	public function update_empty_salary_template($data, $id)
	{
		$this->db->where('user_id', $id);
		if ($this->db->update('xin_employees', $data)) {
			return true;
		} else {
			return false;
		}
	}

	// Function to update record in table > set hourly grade
	public function update_hourlygrade_salary_template($data, $id)
	{
		$this->db->where('user_id', $id);
		if ($this->db->update('xin_employees', $data)) {
			return true;
		} else {
			return false;
		}
	}

	// Function to update record in table > set monthly grade
	public function update_monthlygrade_salary_template($data, $id)
	{
		$this->db->where('user_id', $id);
		if ($this->db->update('xin_employees', $data)) {
			return true;
		} else {
			return false;
		}
	}

	// Function to update record in table > zero hourly grade
	public function update_hourlygrade_zero($data, $id)
	{
		$this->db->where('user_id', $id);
		if ($this->db->update('xin_employees', $data)) {
			return true;
		} else {
			return false;
		}
	}

	// Function to update record in table > zero monthly grade
	public function update_monthlygrade_zero($data, $id)
	{
		$this->db->where('user_id', $id);
		if ($this->db->update('xin_employees', $data)) {
			return true;
		} else {
			return false;
		}
	}

	public function get_management_approval_count($location_id, $month_year)
	{

		$sql = 'SELECT * FROM management_payroll_approval WHERE location_id = ? AND month_year = ?';
		$binds = array($location_id, $month_year);
		$query = $this->db->query($sql, $binds);
		return $query->num_rows();;
	}

	public function get_management_approval_list($location_id, $company_id, $department_id, $month_year)
	{

		$this->db->select("*");
		$this->db->from('xin_employees');
		if (!empty($location_id)) {
			$this->db->where('location_id', $location_id);
		}
		if (!empty($company_id)) {
			$this->db->where('company_id', $company_id);
		}
		if (!empty($department_id)) {
			$this->db->where('department_id', $department_id);
		}
		$this->db->where('user_role_id != ', 1);
		$query = $this->db->get();
		$res = $query->result();
		return $res;
	}

	public function get_management_approval_list_new($location_id, $company_id, $employee_id, $month_year)
	{

		$this->db->select("*");
		$this->db->from('xin_employees');
		if ((!empty($company_id))) {
			$this->db->where('company_id', $company_id);
		}
		if ((!empty($location_id))) {
			$location_ids = explode(',',  $location_id);
			$this->db->where_in('location_id', $location_ids);
		}
		if ((!empty($employee_id))) {
			$employee_ids = explode(',',  $employee_id);
			$this->db->where_in('user_id', $employee_ids);
		}
		$this->db->where('super_privileges != ', 1);
		$this->db->where('user_role_id != ', 1);
		$query = $this->db->get();
		$res = $query->result();
		return $res;
	}


	public function bank_format_details_list($location_id, $month_year, $company_id, $user_id)
	{

		$this->db->select("xin_employees.*");
		$this->db->from('xin_employees');
		$this->db->join('management_payroll_approval', 'management_payroll_approval.employee_id = xin_employees.user_id', 'left');
		if (!empty($company_id)) {
			$this->db->where('xin_employees.company_id', $company_id);
		}
		if (!empty($location_id)) {
			$this->db->where_in('management_payroll_approval.location_id', $location_id);
		}
		if (!empty($user_id)) {
			$this->db->where_in('xin_employees.user_id', $user_id);
		}
		if (!empty($month_year)) {
			$this->db->where('management_payroll_approval.month_year', $month_year);
		}
		$this->db->where('xin_employees.user_role_id !=', 1);
		$this->db->where('xin_employees.payment_mode =', 'bank');
		$query = $this->db->get();
		return $query;
	}

	public function generated_bank_format_details_list($location_id, $month_year, $company_id, $user_id, $value_date)
	{

		$this->db->select("xin_employees.*,generated_bank_formats.month_year,generated_bank_formats.value_date,generated_bank_formats.id as generated_id");
		$this->db->from('generated_bank_formats');
		$this->db->join('xin_employees', 'xin_employees.user_id = generated_bank_formats.employee_id', 'left');
		if (!empty($company_id)) {
			$this->db->where('generated_bank_formats.company_id', $company_id);
		}
		if (!empty($location_id)) {
			$location_ids = explode(',', $location_id);
			$this->db->where_in('generated_bank_formats.location_id', $location_ids);
		}
		
		if (!empty($user_id)) {
			$user_ids = explode(',', $user_id);
			$this->db->where_in('xin_employees.user_id', $user_ids);
		}
		if (!empty($month_year)) {
			$this->db->where('generated_bank_formats.month_year', $month_year);
		}
		if (!empty($value_date)) {
			$this->db->where('generated_bank_formats.value_date', $value_date);
		}

		$this->db->where('user_role_id != ', 1);
		$this->db->where('super_privileges!= ', 1);
		$query = $this->db->get();
		return $query;
	}

	public function add_management_approval($data)
	{
		$this->db->insert('management_payroll_approval', $data);
		if ($this->db->affected_rows() > 0) {
			return $this->db->insert_id();//return true;
		} else {
			return false;
		}
	}

	public function add_bank_format($data)
	{
		$this->db->insert('generated_bank_formats', $data);
		if ($this->db->affected_rows() > 0) {
			return true;
		} else {
			return false;
		}
	}

	public function read_make_payment_payslip_check($employee_id, $p_date)
	{

		$sql = 'SELECT * FROM xin_salary_payslips WHERE employee_id = ? and salary_month = ?';
		$binds = array($employee_id, $p_date);
		$query = $this->db->query($sql, $binds);
		return $query;
	}

	public function read_make_payment_payslip_half_month_check($employee_id, $p_date)
	{

		$sql = "SELECT * FROM xin_salary_payslips WHERE is_half_monthly_payroll = '1' and employee_id = ? and salary_month = ?";
		$binds = array($employee_id, $p_date);
		$query = $this->db->query($sql, $binds);
		return $query;
	}

	public function read_make_payment_payslip_half_month_check_last($employee_id, $p_date)
	{

		$sql = "SELECT * FROM xin_salary_payslips WHERE is_half_monthly_payroll = '1' and employee_id = ? and salary_month = ? order by payslip_id desc";
		$binds = array($employee_id, $p_date);
		$query = $this->db->query($sql, $binds);
		return $query->result();
	}

	public function read_make_payment_payslip_half_month_check_first($employee_id, $p_date)
	{

		$sql = "SELECT * FROM xin_salary_payslips WHERE is_half_monthly_payroll = '1' and employee_id = ? and salary_month = ? order by payslip_id asc";
		$binds = array($employee_id, $p_date);
		$query = $this->db->query($sql, $binds);
		return $query->result();
	}

	public function read_make_payment_payslip($employee_id, $p_date)
	{

		$sql = 'SELECT * FROM xin_salary_payslips WHERE employee_id = ? and salary_month = ?';
		$binds = array($employee_id, $p_date);
		$query = $this->db->query($sql, $binds);

		return $query->result();
	}

	public function read_count_make_payment_payslip($employee_id, $p_date)
	{

		$sql = 'SELECT * FROM xin_salary_payslips WHERE employee_id = ? and salary_month = ?';
		$binds = array($employee_id, $p_date);
		$query = $this->db->query($sql, $binds);

		return $query->num_rows();
	}

	// Function to add record in table> salary payslip record
	public function add_salary_payslip($data)
	{
		$this->db->insert('xin_salary_payslips', $data);
		if ($this->db->affected_rows() > 0) {
			return $this->db->insert_id();
		} else {
			return false;
		}
	}

	// Function to add record in table> salary payslip record
	public function add_salary_payslip_allowances($data)
	{
		$this->db->insert('xin_salary_payslip_allowances', $data);
		if ($this->db->affected_rows() > 0) {
			return true;
		} else {
			return false;
		}
	}

	// Function to add record in table>
	public function add_salary_payslip_commissions($data)
	{
		$this->db->insert('xin_salary_payslip_commissions', $data);
		if ($this->db->affected_rows() > 0) {
			return true;
		} else {
			return false;
		}
	}

	// Function to add record in table>
	public function add_salary_payslip_other_payments($data)
	{
		$this->db->insert('xin_salary_payslip_other_payments', $data);
		if ($this->db->affected_rows() > 0) {
			return true;
		} else {
			return false;
		}
	}

	// Function to add record in table>
	public function add_salary_payslip_statutory_deductions($data)
	{
		$this->db->insert('xin_salary_payslip_statutory_deductions', $data);
		if ($this->db->affected_rows() > 0) {
			return true;
		} else {
			return false;
		}
	}

	// Function to add record in table> salary payslip record
	public function add_salary_payslip_loan($data)
	{
		$this->db->insert('xin_salary_payslip_loan', $data);
		if ($this->db->affected_rows() > 0) {
			return true;
		} else {
			return false;
		}
	}

	// Function to add record in table> salary payslip record
	public function add_salary_payslip_overtime($data)
	{
		$this->db->insert('xin_salary_payslip_overtime', $data);
		if ($this->db->affected_rows() > 0) {
			return true;
		} else {
			return false;
		}
	}

	public function read_salary_payslip_info($id)
	{

		$sql = 'SELECT * FROM xin_salary_payslips WHERE payslip_id = ?';
		$binds = array($id);
		$query = $this->db->query($sql, $binds);

		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return null;
		}
	}

	public function read_salary_payslip_info_key($id)
	{

		$sql = 'SELECT * FROM xin_salary_payslips WHERE payslip_key = ?';
		$binds = array($id);
		$query = $this->db->query($sql, $binds);

		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return null;
		}
	}

	// Function to update record in table > set hourly grade
	public function update_payroll_status($data, $id)
	{
		$this->db->where('payslip_key', $id);
		if ($this->db->update('xin_salary_payslips', $data)) {
			return true;
		} else {
			return false;
		}
	}

	public function find_monthly_leave_deduction($employee_id, $date)
	{
		$sql = "SELECT * FROM `xin_leave_applications` WHERE `employee_id` = ? AND `status` = '2' AND `from_date` LIKE ? ";
		$binds = array($employee_id, $date . '%');
		$query = $this->db->query($sql, $binds);

		$daily_wage = $this->find_daily_wage($employee_id, $date);
		$employee_under_probation = $this->Employees_model->employee_under_probation($employee_id);

		$deduction['days'] = 0;
		$deduction['amount'] = 0;
		$deduction['leave_types_count'] = array();

		foreach ($query->result() as $row) {
			$from_date = $row->from_date;
			$to_date = $row->to_date;
			$is_half_day = $row->is_half_day;
			$leave_type_id = $row->leave_type_id;
			$days = $row->days;

			if (isset($deduction['leave_types_count'][$leave_type_id])) {
				$deduction['leave_types_count'][$leave_type_id]['days'] += $days;
			} else {
				$deduction['leave_types_count'][$leave_type_id]['days'] = $days;

				$sql = "SELECT `type_name` FROM `xin_leave_type` WHERE `leave_type_id` = ?";
				$binds = array($leave_type_id);
				$query_leave = $this->db->query($sql, $binds);
				$leave_type_info = $query_leave->row();
				$deduction['leave_types_count'][$leave_type_id]['name'] = $leave_type_info->type_name;
			}

			if ($is_half_day == 1) {
				$daily_wage = $daily_wage / 2;
				$deduction['days'] += 0.5;
			} else {
				$deduction['days'] += $days;
			}

			$daily_wage = floor($daily_wage);

			if ($leave_type_id == 2) {
				if ($employee_under_probation) {
					$deduction['amount'] += $daily_wage * $days;
				} else {
					$previous_leave_count = $this->previous_leave_count($employee_id, $leave_type_id);

					for ($i = 0; $i < $days; $i++) {
						$leave_count = $previous_leave_count + 1;

						if ($leave_count > 35) {
							$deduction['amount'] += $daily_wage;
						} else if ($leave_count > 15) {
							$deduction['amount'] += $daily_wage / 2;
						}

						$previous_leave_count = $leave_count;
					}
				}
			}
		}

		return $deduction;
	}

	public function find_daily_wage($employee_id, $date)
	{
		$employee = $this->Employees_model->read_employee_information($employee_id);
		$basic_salary = $employee[0]->basic_salary;
		$month_days = $this->days_in_month($date);
		$daily_wage = $basic_salary / $month_days;
		$daily_wage = floor($daily_wage);

		return $daily_wage;
	}

	public function days_in_month($date)
	{
		$date_parse = date_parse($date);
		//	$days = cal_days_in_month(CAL_GREGORIAN, $date_parse['month'], $date_parse['year']);
		$days = $date_parse['month'] / $date_parse['year'];
		return $days;
	}

	public function previous_leave_count($employee_id, $leave_type_id)
	{
		if (date('m') == 1) return 0;

		$sql = "SELECT * FROM `xin_leave_applications` WHERE
				`leave_type_id` = ? AND
				`employee_id` = ? AND
				`status` = '2' AND
				`from_date` LIKE ? ";
		$binds = array($leave_type_id, $employee_id, date('Y') . '-%');
		$query = $this->db->query($sql, $binds);
		$count = 0;

		foreach ($query->result() as $row) {
			$from_date = $row->from_date;
			$from_date_parse = date_parse($from_date);

			if ($from_date_parse['month'] < date('m')) {
				$count += $row['days'];
			}
		}

		return $count;
	}

	public function read_employee_payslip_info_key($id, $start_date_y_m)
	{
		$sql = "SELECT * FROM xin_salary_payslips WHERE employee_id = ? AND salary_month LIKE '%$start_date_y_m%'";
		$binds = array($id);
		$query = $this->db->query($sql, $binds);
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return null;
		}
	}

	public function add_advance_sal_month_wise_dedec($data)
	{
		$this->db->insert('xin_advance_deductions_monthwise', $data);
		if ($this->db->affected_rows() > 0) {
			return $this->db->insert_id();
		} else {
			return false;
		}
	}

	public function get_employee_advance_salary_distrubtion($id, $advance_id)
	{
		$sql = 'SELECT * FROM xin_advance_deductions_monthwise WHERE employee_id = ? and advance_id = ?';
		$binds = array($id, $advance_id);
		$query = $this->db->query($sql, $binds);

		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return null;
		}
	}

	public function delete_advance_distribution_list($id, $advance_id)
	{
		$this->db->where('advance_id', $advance_id);
		$this->db->where('employee_id', $id);
		$this->db->delete('xin_advance_deductions_monthwise');
	}

	public function adv_salary_distrubution_record_update($data, $id)
	{
		$this->db->where('id', $id);
		if ($this->db->update('xin_advance_deductions_monthwise', $data)) {
			return true;
		} else {
			return false;
		}
	}

	public function get_employee_adv_sal_distrubtion_latest($id, $loan_id)
	{
		$sql = 'SELECT * FROM xin_advance_deductions_monthwise WHERE employee_id = ? and advance_id = ? order by id desc limit 1';
		$binds = array($id, $loan_id);
		$query = $this->db->query($sql, $binds);

		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return null;
		}
	}

	public function delete_advance_distribution_list_de($id)
	{
		$this->db->where('id', $id);
		$this->db->delete('xin_advance_deductions_monthwise');
	}

	public function dlt_advance_distrbutions($emp_id, $advance_id)
	{
		$this->db->where('advance_id', $advance_id);
		$this->db->where('employee_id', $emp_id);
		$this->db->delete('xin_advance_deductions_monthwise');
	}

	/*	public function GetLateDeductionCalcCommon($user_id,$paymonth,$payyear,$allowance_amount,$no_of_days){
			$total = 0;
			$totaldeduct = 0;
			$empdata = $this->Employees_model->get_employee_details($user_id);
			$basepay       = $empdata[0]->basic_salary;
			$p_date        = $payyear."-".$paymonth;
			$all_allo_and_dedct =  $this->Xin_model->payroll_all_dedctions_and_additions($user_id,$p_date,$basepay);
			$allowance_amount   =  $all_allo_and_dedct[0];
			$no_of_days         =  date('t', strtotime($p_date));
			$basic_payment      =  $basepay + $allowance_amount;

			$late_totaldetails = $this->Employees_model->getemployeelatedetails($user_id,$paymonth,$payyear);
			if(isset($late_totaldetails)){
			foreach($late_totaldetails as $latedetails){
				$day = $latedetails->attendance_date;
				$timestamp = strtotime($day);
				$dayname = date('l', $timestamp);
				$latecome_deduct = $this->Employees_model->get_employee_latecome_deduct($user_id,$day);
				$total_work = $this->Employees_model->get_employee_workdone($user_id,$day);
				$total_work_hours = $total_work[0]->totalhours;
				if($latecome_deduct){
				 $late_by_hours  = (isset($latecome_deduct[0]->late_by_hours))?$latecome_deduct[0]->late_by_hours:0;
				}
            if($total_work_hours< 8 && $total_work_hours> 0){
                           $latehours = 8 - round($total_work_hours,2);
                           $empdata = $this->Employees_model->get_employee_details($user_id);
                           $basepay       = $empdata[0]->basic_salary;
                           $basic_payment =  $basepay + $allowance_amount;
                           $hourdeduct = round(($basic_payment/$no_of_days)/8 * $latehours,3);
                           
                           $halfdays      =  $this->Employees_model->get_halfdays($user_id);
						   if(isset($halfdays)){
						   foreach ($halfdays as $row) {
					
						    $startDate     =  $row->from_date;
						    //echo $startDate;
						    if($day == $startDate && $latehours > 4){
						        //$halfday = "yes";
						        $latebyhours = 4 - round($total_work_hours,2);
						        $hourdeduct = round(($basic_payment/$no_of_days)/8 * $latebyhours,3);
						    }elseif($day == $startDate && $latehours <= 4){
						        $hourdeduct = 0;
						    }
						   
					     	}
						    }
						    
                           $totaldeduct = $hourdeduct + $latededuction;
                           $total += $hourdeduct + $latededuction;
                           
            }
            else{
                           $total +=  $latededuction;
            }
        }
        }
        if(!is_null($total)){    
      
          $late_deduct_amt = $total;
          
        }
        else{
          $late_deduct_amt = 0;
        }
        $userinfo            =  $this->Employees_model->get_employee_details($user_id);
        $companyinfo         =  $this->Employees_model->get_company_details($userinfo[0]->company_id);
        $late_status         =  (isset($companyinfo[0]->late_deduction))?$companyinfo[0]->late_deduction:0;
        if($late_status == 'no'){
          $late_deduct_amt = 0;
        }
        
        return array(round($late_deduct_amt,3));
	}*/

	public function GetLateDeductionCalcCommon($user_id, $pay_month, $pay_year, $no_of_days)
	{
		$total = 0;
		$totaldeduct = 0;
		$holiday_check = "no";
		$empdata = $this->Employees_model->get_employee_details($user_id);
		$basepay = $empdata[0]->basic_salary;
		$p_date = $pay_year . "-" . $pay_month;
		$all_allo_and_dedct = $this->Xin_model->payroll_all_dedctions_and_additions($user_id, $p_date, $basepay);
		$allowance_amount = $all_allo_and_dedct[0];
		$no_of_days = date('t', strtotime($p_date));
		$basic_payment = $basepay + $allowance_amount;
		$late_totaldetails = $this->Employees_model->getemployeelatedetails($user_id, $pay_month, $pay_year);
		$empdatas = $this->Employees_model->get_employee_data($user_id);
		$latestatus = $empdatas[0]->late_deduction;

		$user = $this->Employees_model->read_user_by_user_id($user_id);
		if ($user[0]->office_shift_id == 0) {
			$custom_shift = $this->Office_shift_custom_model->get($user_id, $p_date)->row_array();
			if ($latestatus == "hours_deduction") {
				if (isset($late_totaldetails)) {
					foreach ($late_totaldetails as $latedetails) {
						$day = $latedetails->attendance_date;
						$timestamp = strtotime($day);
						$dayname = date('l', $timestamp);

						$d = date('j', $timestamp);
						$shift_totalwork_hours = $custom_shift[$d . '_hrs'];

						$user = $this->Employees_model->read_user_by_user_id($user_id);
						$comp_id = $user[0]->company_id;;
						$holidays = $this->Employees_model->get_holidays($comp_id);
						$holiday_check = "no";

						if (isset($holidays)) {
							foreach ($holidays as $row) {
								$startDate = $row->start_date;
								$endDate = $row->end_date;

								if (($day >= $startDate) && ($day <= $endDate)) {
									$holiday_check = "yes";
								}
							}
						}

						$total_work = $this->Employees_model->get_employee_workdone($user_id, $day);
						$total_work_hours = $total_work[0]->totalhours;
						$latecome_deduct = $this->Employees_model->get_employee_latecome_deduct($user_id, $day);
						if (!empty($latecome_deduct)) {
							$late_by_hours = $latecome_deduct[0]->late_by_hours;
						} else {
							$late_by_hours = 0;
						}
						if (!empty($late_by_hours)) {
							$latededuction = round(($basic_payment / $no_of_days) / $shift_totalwork_hours * $late_by_hours, 3);
						} else {
							$latededuction = 0;
						}
						if ($dayname === "Friday" || $holiday_check === "yes") {
							$total_work_hours = 8;
						}
						unset($holiday_check);
						if ($total_work_hours < $shift_totalwork_hours && $total_work_hours > 0) {
							$latehours = $shift_totalwork_hours - round($total_work_hours, 2);
							//$empdata = $this->Employees_model->get_employee_details($user_id);
							//$basepay       = $empdata[0]->basic_salary;
							$hourdeduct = round(($basic_payment / $no_of_days) / $shift_totalwork_hours * $latehours, 3);

							$halfdays = $this->Employees_model->get_halfdays($user_id);
							if (isset($halfdays)) {
								foreach ($halfdays as $row) {

									$startDate = $row->from_date;
									$shift_half = $shift_totalwork_hours / 2;
									if ($day == $startDate && $latehours > $shift_half) {

										$latebyhours = $shift_half - round($total_work_hours, 2);
										$hourdeduct = round(($basic_payment / $no_of_days) / $shift_totalwork_hours * $latebyhours, 3);
										$latededuction = 0;
									} elseif ($day == $startDate && $latehours <= $shift_half) {
										$hourdeduct = 0;
										$latededuction = 0;
									}
								}
							}

							$total += $hourdeduct;
						}
					}
				}
			} elseif ($latestatus == "hoursandlate_deduction") {
				if (isset($late_totaldetails)) {
					foreach ($late_totaldetails as $latedetails) {
						$day = $latedetails->attendance_date;
						$timestamp = strtotime($day);
						$dayname = date('l', $timestamp);

						$d = date('j', $timestamp);
						$shift_totalwork_hours = $custom_shift[$d . '_hrs'];

						$user = $this->Employees_model->read_user_by_user_id($user_id);
						$comp_id = $user[0]->company_id;;
						$holidays = $this->Employees_model->get_holidays($comp_id);
						$holiday_check = "no";

						if (isset($holidays)) {
							foreach ($holidays as $row) {
								$startDate = $row->start_date;
								$endDate = $row->end_date;

								if (($day >= $startDate) && ($day <= $endDate)) {
									$holiday_check = "yes";
								}
							}
						}

						$total_work = $this->Employees_model->get_employee_workdone($user_id, $day);
						$total_work_hours = $total_work[0]->totalhours;
						$latecome_deduct = $this->Employees_model->get_employee_latecome_deduct($user_id, $day);
						if (!empty($latecome_deduct)) {
							$late_by_hours = $latecome_deduct[0]->late_by_hours;
						} else {
							$late_by_hours = 0;
						}
						if (!empty($late_by_hours)) {
							$latededuction = round(($basic_payment / $no_of_days) / $shift_totalwork_hours * $late_by_hours, 3);
						} else {
							$latededuction = 0;
						}
						if ($dayname === "Friday" || $holiday_check === "yes") {
							$total_work_hours = 8;
						}
						unset($holiday_check);
						if ($total_work_hours < $shift_totalwork_hours && $total_work_hours > 0) {
							$latehours = $shift_totalwork_hours - round($total_work_hours, 2);
							//$empdata = $this->Employees_model->get_employee_details($user_id);
							//$basepay       = $empdata[0]->basic_salary;
							$hourdeduct = round(($basic_payment / $no_of_days) / $shift_totalwork_hours * $latehours, 3);

							$halfdays = $this->Employees_model->get_halfdays($user_id);
							if (isset($halfdays)) {
								foreach ($halfdays as $row) {

									$startDate = $row->from_date;
									$shift_half = $shift_totalwork_hours / 2;
									if ($day == $startDate && $latehours > $shift_half) {

										$latebyhours = $shift_half - round($total_work_hours, 2);
										$hourdeduct = round(($basic_payment / $no_of_days) / $shift_totalwork_hours * $latebyhours, 3);
										$latededuction = 0;
									} elseif ($day == $startDate && $latehours <= $shift_half) {
										$hourdeduct = 0;
										$latededuction = 0;
									}
								}
							}
							$totaldeduct = $hourdeduct + $latededuction;
							$total += $hourdeduct + $latededuction;
						} elseif ($total_work[0]->totalhours > $shift_totalwork_hours && $latededuction > 0) {
							$total += $latededuction;
						}
					}
				}
			}
		} else {
			$holiday_check = "no";
			if ($latestatus == "hours_deduction") {
				if (isset($late_totaldetails)) {
					foreach ($late_totaldetails as $latedetails) {
						$day = $latedetails->attendance_date;
						$timestamp = strtotime($day);
						$dayname = date('l', $timestamp);

						$user = $this->Employees_model->read_user_by_user_id($user_id);
						$comp_id = $user[0]->company_id;;
						$holidays = $this->Employees_model->get_holidays($comp_id);
						$holiday_check = "no";

						if (isset($holidays)) {
							foreach ($holidays as $row) {
								$startDate = $row->start_date;
								$endDate = $row->end_date;

								if (($day >= $startDate) && ($day <= $endDate)) {
									$holiday_check = "yes";
								}
							}
						}

						$week_day = date('l', strtotime($day));
						$week_day_lower = strtolower($week_day);
						// Make Table field. Eg: thursday_out_time
						$table_field_in_time = $week_day_lower . '_in_time';
						$table_field_out_time = $week_day_lower . '_out_time';

						$this->db->where('office_shift_id', $user[0]->office_shift_id);
						$office_shift = $this->db->get('xin_office_shift')->row();
						$shift_start = $office_shift->$table_field_in_time;
						$shift_end = $office_shift->$table_field_out_time;
						$total_workhours = $office_shift->total_working_hours;
						$halftotal_workhours = $total_workhours / 2;

						$total_work = $this->Employees_model->get_employee_workdone($user_id, $day);
						$total_work_hours = $total_work[0]->totalhours;
						$latecome_deduct = $this->Employees_model->get_employee_latecome_deduct($user_id, $day);
						if (!empty($latecome_deduct)) {
							$late_by_hours = $latecome_deduct[0]->late_by_hours;
						} else {
							$late_by_hours = 0;
						}
						if (!empty($late_by_hours)) {
							$latededuction = round(($basic_payment / $no_of_days) / $total_workhours * $late_by_hours, 3);
						} else {
							$latededuction = 0;
						}
						if ($dayname === "Friday" || $holiday_check === "yes") {
							$total_work_hours = $total_workhours;
						}
						unset($holiday_check);
						if ($total_work_hours < $total_workhours && $total_work_hours > 0) {
							$latehours = $total_workhours - round($total_work_hours, 2);
							//$empdata = $this->Employees_model->get_employee_details($user_id);
							//$basepay       = $empdata[0]->basic_salary;
							$hourdeduct = round(($basic_payment / $no_of_days) / $total_workhours * $latehours, 3);

							$halfdays = $this->Employees_model->get_halfdays($user_id);
							if (isset($halfdays)) {
								foreach ($halfdays as $row) {

									$startDate = $row->from_date;
									if ($day == $startDate && $latehours > $halftotal_workhours) {

										$latebyhours = $halftotal_workhours - round($total_work_hours, 2);
										$hourdeduct = round(($basic_payment / $no_of_days) / $total_workhours * $latebyhours, 3);
										$latededuction = 0;
									} elseif ($day == $startDate && $latehours <= $halftotal_workhours) {
										$hourdeduct = 0;
										$latededuction = 0;
									}
								}
							}

							$total += $hourdeduct;
						}
					}
				}
			} elseif ($latestatus == "hoursandlate_deduction") {
				if (isset($late_totaldetails)) {
					foreach ($late_totaldetails as $latedetails) {
						$day = $latedetails->attendance_date;
						$timestamp = strtotime($day);
						$dayname = date('l', $timestamp);

						$user = $this->Employees_model->read_user_by_user_id($user_id);
						$comp_id = $user[0]->company_id;;
						$holidays = $this->Employees_model->get_holidays($comp_id);
						$holiday_check = "no";

						if (isset($holidays)) {
							foreach ($holidays as $row) {
								$startDate = $row->start_date;
								$endDate = $row->end_date;

								if (($day >= $startDate) && ($day <= $endDate)) {
									$holiday_check = "yes";
								}
							}
						}

						$week_day = date('l', strtotime($day));
						$week_day_lower = strtolower($week_day);
						// Make Table field. Eg: thursday_out_time
						$table_field_in_time = $week_day_lower . '_in_time';
						$table_field_out_time = $week_day_lower . '_out_time';

						$this->db->where('office_shift_id', $user[0]->office_shift_id);
						$office_shift = $this->db->get('xin_office_shift')->row();
						$shift_start = $office_shift->$table_field_in_time;
						$shift_end = $office_shift->$table_field_out_time;
						$total_workhours = $office_shift->total_working_hours;
						$halftotal_workhours = $total_workhours / 2;

						$total_work = $this->Employees_model->get_employee_workdone($user_id, $day);
						$total_work_hours = $total_work[0]->totalhours;
						$latecome_deduct = $this->Employees_model->get_employee_latecome_deduct($user_id, $day);
						if (!empty($latecome_deduct)) {
							$late_by_hours = $latecome_deduct[0]->late_by_hours;
						} else {
							$late_by_hours = 0;
						}
						if (!empty($late_by_hours)) {
							$latededuction = round(($basic_payment / $no_of_days) / $total_workhours * $late_by_hours, 3);
						} else {
							$latededuction = 0;
						}
						if ($dayname === "Friday" || $holiday_check === "yes") {
							$total_work_hours = $total_workhours;
						}
						unset($holiday_check);
						if ($total_work_hours < $total_workhours && $total_work_hours > 0) {
							$latehours = $total_workhours - round($total_work_hours, 2);
							//$empdata = $this->Employees_model->get_employee_details($user_id);
							//$basepay       = $empdata[0]->basic_salary;
							$hourdeduct = round(($basic_payment / $no_of_days) / $total_workhours * $latehours, 3);

							$halfdays = $this->Employees_model->get_halfdays($user_id);
							if (isset($halfdays)) {
								foreach ($halfdays as $row) {

									$startDate = $row->from_date;
									if ($day == $startDate && $latehours > $halftotal_workhours) {

										$latebyhours = $halftotal_workhours - round($total_work_hours, 2);
										$hourdeduct = round(($basic_payment / $no_of_days) / $total_workhours * $latebyhours, 3);
										$latededuction = 0;
									} elseif ($day == $startDate && $latehours <= $halftotal_workhours) {
										$hourdeduct = 0;
										$latededuction = 0;
									}
								}
							}
							$totaldeduct = $hourdeduct + $latededuction;
							$total += $hourdeduct + $latededuction;
						} elseif ($total_work[0]->totalhours > $total_workhours && $latededuction > 0) {
							$total += $latededuction;
						}
					}
				}
			}
		}
		return array(round($total, 3));
	}

	public function GetOvertimeCalcCommon($user_id, $paymonth, $payyear)
	{
		$overtimestatus = $this->Employees_model->get_employee_overtimestatus($user_id);
		$otstatus = $overtimestatus[0]->ot_eligible;

		$user = $this->Employees_model->read_user_by_user_id($user_id);
		$basic_pay = $user[0]->basic_salary;
		$overtime_totaldetails = $this->Employees_model->get_employee_overtimedetails_cuttoff($user_id, $paymonth, $payyear);
		$overtime_consts = $this->Xin_model->get_all_overtime_constants();
		$weekend = $overtime_consts[0]->weekend;
		$holiday = $overtime_consts[0]->holiday;
		$workingday = $overtime_consts[0]->workingday;
		$timeshift_percent = $overtime_consts[0]->timeshift_percent;
		$total_overtimeamount = 0;
		$overtimeamount = 0;
		global $holiday_check;
		global $weekends;

		if ($otstatus == "yes") {
			foreach ($overtime_totaldetails as $overtimedetails) {

				$user = $this->Employees_model->read_user_by_user_id($user_id);
				$basic_pay = $user[0]->basic_salary;
				$normal_ot_seconds = $overtimedetails->normal_ot_seconds;
				$extra_ot_seconds = $overtimedetails->extra_ot_seconds;
				$normal_ot_hours = round($normal_ot_seconds / 3600, 2);
				$extra_ot_hours = round($extra_ot_seconds / 3600, 2);

				$attn_date = $overtimedetails->date;

				if ($user[0]->office_shift_id == 0) {
					$office_shift = $this->Office_shift_custom_model->employee_current_shift($user[0]->user_id, "$payyear-$paymonth");

					$day = date('j', strtotime($attn_date));
					$table_field_in = $day . '_in';
					$table_field_out = $day . '_out';
					if ($office_shift[$table_field_in] == '' || $office_shift[$table_field_out] == '') {
						$weekends = "yes";
					}

					$comp_id = $user[0]->company_id;;
					$holidays = $this->Employees_model->get_holidays($comp_id);

					if (isset($holidays)) {
						foreach ($holidays as $row) {
							$startDate = $row->start_date;
							$endDate = $row->end_date;

							if (($attn_date >= $startDate) && ($attn_date <= $endDate)) {
								$holiday_check = "yes";
							}
						}
					}

					if ($normal_ot_hours > 0 && $extra_ot_hours > 0) {
						$overtimeamount = (($basic_pay / 30) / 8) * $extra_ot_hours * $timeshift_percent + (($basic_pay / 30) / 8) * $normal_ot_hours * $workingday;
					} elseif ($normal_ot_hours > 0 && $extra_ot_hours == 0) {
						$overtimeamount = (($basic_pay / 30) / 8) * $normal_ot_hours * $workingday;
					} elseif ($normal_ot_hours == 0 && $extra_ot_hours > 0) {
						$overtimeamount = (($basic_pay / 30) / 8) * $extra_ot_hours * $timeshift_percent;
					} elseif ($normal_ot_hours == 0 && $extra_ot_hours == 0) {
						$overtimeamount = 0;
					} elseif ($normal_ot_hours == 0 && $extra_ot_hours > 0 && $weekends = "yes") {
						$overtimeamount = (($basic_pay / 30) / 8) * $extra_ot_hours * $weekend;
						unset($weekends);
					} elseif ($normal_ot_hours == 0 && $extra_ot_hours > 0 && $holiday_check == "yes") {
						$overtimeamount = (($basic_pay / 30) / 8) * $extra_ot_hours * $holiday;
						unset($holiday_check);
					}
					$ot_hours = $normal_ot_hours + $extra_ot_hours;
				} else {
					$week_day = date('l', strtotime($attn_date));
					$week_day_lower = strtolower($week_day);
					// Make Table field. Eg: thursday_out_time
					$table_field_in_time = $week_day_lower . '_in_time';
					$table_field_out_time = $week_day_lower . '_out_time';

					$this->db->where('office_shift_id', $user[0]->office_shift_id);
					$office_shift = $this->db->get('xin_office_shift')->row();
					$shift_start = $office_shift->$table_field_in_time;
					$shift_end = $office_shift->$table_field_out_time;
					$total_workhours = $office_shift->total_working_hours;
					if (empty($shift_start)) {
						$weekends = "yes";
					}

					$comp_id = $user[0]->company_id;;
					$holidays = $this->Employees_model->get_holidays($comp_id);

					if (isset($holidays)) {
						foreach ($holidays as $row) {
							$startDate = $row->start_date;
							$endDate = $row->end_date;

							if (($attn_date >= $startDate) && ($attn_date <= $endDate)) {
								$holiday_check = "yes";
							}
						}
					}

					if ($normal_ot_hours > 0 && $extra_ot_hours > 0) {
						$overtimeamount = (($basic_pay / 30) / $total_workhours) * $extra_ot_hours * $timeshift_percent + (($basic_pay / 30) / $total_workhours) * $normal_ot_hours * $workingday;
					} elseif ($normal_ot_hours > 0 && $extra_ot_hours == 0) {
						$overtimeamount = (($basic_pay / 30) / $total_workhours) * $normal_ot_hours * $workingday;
					} elseif ($normal_ot_hours == 0 && $extra_ot_hours > 0) {
						$overtimeamount = (($basic_pay / 30) / $total_workhours) * $extra_ot_hours * $timeshift_percent;
					} elseif ($normal_ot_hours == 0 && $extra_ot_hours == 0) {
						$overtimeamount = 0;
					} elseif ($normal_ot_hours == 0 && $extra_ot_hours > 0 && $weekends = "yes") {
						$overtimeamount = (($basic_pay / 30) / $total_workhours) * $extra_ot_hours * $weekend;
						unset($weekends);
					} elseif ($normal_ot_hours == 0 && $extra_ot_hours > 0 && $holiday_check == "yes") {
						$overtimeamount = (($basic_pay / 30) / $total_workhours) * $extra_ot_hours * $holiday;
						unset($holiday_check);
					}
					$ot_hours = $normal_ot_hours + $extra_ot_hours;
				}


				/*$ot_hours       = round($overtimedetails->ot_seconds/3600,2);
				$overtimeamount = (($basic_pay/30)/8)*$ot_hours* 1.5;*/

				/*$totalhours = $overtimedetails->normal_ot_hours + $overtimedetails->extra_ot_hours;
				$normal_ot_hours    = $overtimedetails->normal_ot_hours;
				$extraot_hours      = $overtimedetails->extra_ot_hours;


				$attn_date          = $overtimedetails->attendance_date;
				$timestamp          = strtotime($attn_date);
				$day                = date('l', $timestamp);

				$comp_id            = $user[0]->company_id;
				$holidays           = $this->Employees_model->get_holidays($comp_id);
				global $holiday_check;

					if(isset($holidays))
					{
						foreach ($holidays as $row)
						{
							  $startDate     =  $row->start_date;
							  $endDate       =  $row->end_date;

							  if(($attn_date >= $startDate) && ($attn_date <= $endDate)){
									$holiday_check = "yes";
							  }
						}
					}*/

				/*$total_work         = $this->Employees_model->get_employee_workdone($user_id,$attn_date); 
                $total_work_hours   = $total_work[0]->totalhours;
                $latecome_deduct    = $this->Employees_model->get_employee_latecome_deduct($user_id,$attn_date);
                $late_by_hours      = $latecome_deduct[0]->late_by_hours;
                      if(!empty($latecome_deduct)){
                            $late_by_hours  = $latecome_deduct[0]->late_by_hours ;
                            if($late_by_hours > 0){
                                $late_by_hours  = $latecome_deduct[0]->late_by_hours + 0.08;
                            }
                      }
                      else{
                            $late_by_hours = 0;
                      }
                      if($total_work_hours <= 8 && $day !== "Friday" && $holiday_check !== "yes"){
                          $normal_ot_hours = 0;
                          
                      }elseif($total_work_hours > 8 && $normal_ot_hours > 0 && $late_by_hours > 0 && $extraot_hours == 0 && $day !== "Friday" && $holiday_check !== "yes"){
                          $normal_ot_hours = $normal_ot_hours - $late_by_hours;
                      }*/

				/*if($normal_ot_hours > 0 && $extraot_hours > 0){
					$overtimeamount = (($basic_pay/30)/8)* $extraot_hours* $timeshift_percent + (($basic_pay/30)/8)* $normal_ot_hours* $evening_timeshift_percent ;
				}elseif($normal_ot_hours > 0 && $extraot_hours == 0){
					$overtimeamount = (($basic_pay/30)/8)* $normal_ot_hours* $evening_timeshift_percent ;
				}elseif($normal_ot_hours == 0 && $extraot_hours > 0){
					$overtimeamount = (($basic_pay/30)/8)* $extraot_hours* $timeshift_percent;
				}elseif($normal_ot_hours == 0 && $extraot_hours == 0){
					$overtimeamount = 0;
				}elseif($normal_ot_hours == 0 && $extraot_hours > 0 && $day == "Friday"){
					$overtimeamount = (($basic_pay/30)/8)* $extraot_hours* $weekend;
				}elseif($normal_ot_hours == 0 && $extraot_hours > 0 && $holiday_check == "yes"){
					$overtimeamount = (($basic_pay/30)/8)* $extraot_hours* $holiday;
					unset($holiday_check);
				}*/

				$total_overtimeamount += $overtimeamount;
			}
			$overtime_amt = $total_overtimeamount;
		} else {
			$overtime_amt = 0;
		}
		return array(round($overtime_amt, 3));
	}

	public function delete_management_payroll_approvals_items($location_id, $month_year)
	{
		if (!empty($location_id)) {
			$this->db->where('location_id', $location_id);
		}
		if (!empty($month_year)) {
			$this->db->where('month_year', $month_year);
		}

		$this->db->delete('management_payroll_approval');
	}

	public function GetEncashmentamount($user_id, $pay_date)
	{
		$sql = 'SELECT * FROM xin_encashment WHERE employee_id = ? and retrive_month = ? and approval_status = 1';
		$binds = array($user_id, $pay_date);
		$query = $this->db->query($sql, $binds);
		// 		$str = $this->db->last_query();
		//         echo $str;
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return null;
		}
	}

	public function read_expense_claims($user_id, $month_year = null)
	{
		if (!$month_year) $month_year = date('Y-m');

		$this->db->where('employee_id', $user_id);
		$this->db->where('status', 2);
		$this->db->where('paid', 0);
		$this->db->where('payment_method', 'payroll');
		$this->db->where('payment_method_month_year', $month_year);
		$expenses = $this->db->get('xin_expense_clame');
		if ($expenses->num_rows() > 0) {
			return $expenses->result();
		} else {
			return null;
		}
	}

	public function read_advance_amount_lists($user_id, $month_year = null)
	{
		if (!$month_year) $month_year = date('Y-m');

		$this->db->where('employee_id', $user_id);
		$this->db->where('status', 1);
		$this->db->where('month_year', $month_year);
		$advance_amounts = $this->db->get('xin_advance_salaries');
		if ($advance_amounts->num_rows() > 0) {
			return $advance_amounts->result();
		} else {
			return null;
		}
	}

	// Function to add record in table> salary payslip record
	public function add_expense_claims($data)
	{
		$this->db->insert('xin_salary_payslip_expense_claims', $data);
		if ($this->db->affected_rows() > 0) {
			return true;
		} else {
			return false;
		}
	}


	// Function to add record in table> salary payslip record
	public function add_advance_amount_lists($data)
	{
		$this->db->insert('xin_salary_payslip_advance_amounts', $data);
		if ($this->db->affected_rows() > 0) {
			return true;
		} else {
			return false;
		}
	}

	public function expense_claims_update($data, $id)
	{
		$this->db->where('id', $id);
		if ($this->db->update('xin_expense_clame', $data)) {
			return true;
		} else {
			return false;
		}
	}

	public function read_payslip_expense_claims_information($id)
	{

		$sql = 'SELECT * FROM xin_salary_payslip_expense_claims WHERE payslip_id = ?';
		$binds = array($id);
		$query = $this->db->query($sql, $binds);

		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return null;
		}
	}

	// Function to Delete selected record from table
	public function delete_payslip_expense_claims($id)
	{
		$this->db->where('payslip_id', $id);
		$this->db->delete('xin_salary_payslip_expense_claims');
	}

	public function cash_format_details_list($location_id, $month_year, $company_id, $user_id)
	{

		$this->db->select("xin_employees.*");
		$this->db->from('xin_employees');
		$this->db->join('management_payroll_approval', 'management_payroll_approval.employee_id = xin_employees.user_id', 'left');
		if (!empty($company_id)) {
			$this->db->where('xin_employees.company_id', $company_id);
		}
		if (!empty($location_id)) {
			$location_ids = explode(',', $location_id);
			$this->db->where_in('management_payroll_approval.location_id', $location_ids);
		}
		if (!empty($user_id)) {
			$user_ids = explode(',', $user_id);
			$this->db->where_in('xin_employees.user_id', $user_ids);
		}
		if (!empty($month_year)) {
			$this->db->where('management_payroll_approval.month_year', $month_year);
		}
		if (!empty($location_id) || !empty($month_year)) {
			$this->db->where('xin_employees.user_role_id !=', 1);
		}
		$this->db->where('xin_employees.payment_mode =', 'cash');
		$this->db->where('super_privileges !=', 1);
		$query = $this->db->get();
		return $query;
	}
	public function cheque_format_details_list($location_id, $month_year, $company_id, $user_id)
	{

		$this->db->select("xin_employees.*");
		$this->db->from('xin_employees');
		$this->db->join('management_payroll_approval', 'management_payroll_approval.employee_id = xin_employees.user_id', 'left');
		if (!empty($company_id)) {
			$this->db->where('xin_employees.company_id', $company_id);
		}
		if (!empty($location_id)) {
			$location_ids = explode(',', $location_id);
			$this->db->where_in('management_payroll_approval.location_id', $location_ids);
		}
		if (!empty($user_id)) {
			$user_ids = explode(',', $user_id);
			$this->db->where_in('xin_employees.user_id', $user_ids);
		}
		if (!empty($month_year)) {
			$this->db->where('management_payroll_approval.month_year', $month_year);
		}
		if (!empty($location_id) || !empty($month_year)) {
			$this->db->where('xin_employees.user_role_id !=', 1);
		}
		$this->db->where('xin_employees.payment_mode =', 'cheque');
		$this->db->where('super_privileges!=', 1);
		$query = $this->db->get();
		return $query;
	}

	public function update_incentive_status($data1, $id)
	{
		$this->db->where('payslip_id', $id);
		if ($this->db->update('xin_salary_incentives', $data1)) {
			return true;
		} else {
			return false;
		}
	}

	public function read_management_information($id, $month_year)
	{

		$sql = 'SELECT * FROM management_payroll_approval WHERE employee_id = ? and month_year= ?';
		$binds = array($id, $month_year);
		$query = $this->db->query($sql, $binds);

		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return null;
		}
	}

	public function add_generate_bank_format($data)
	{
		$this->db->insert('generate_bank_format', $data);
		if ($this->db->affected_rows() > 0) {
			return true;
		} else {
			return false;
		}
	}

	public function ajax_location_employee_info($id)
	{
		if ($id != 0) {
			$sql = "SELECT * FROM xin_employees WHERE location_id = ? and user_role_id!='1'";
			$binds = array($id);
			$query = $this->db->query($sql, $binds);

			if ($query->num_rows() > 0) {
				return $query->result();
			} else {
				return null;
			}
		} else {

			$sql = "SELECT * FROM xin_employees WHERE user_role_id!='1'";
			$binds = array($id);
			$query = $this->db->query($sql, $binds);

			if ($query->num_rows() > 0) {
				return $query->result();
			} else {
				return null;
			}
		}
	}

	public function get_generated_bank_format($id)
	{
		$sql = "SELECT * FROM generated_bank_formats WHERE id = ?";
		$binds = array($id);
		$query = $this->db->query($sql, $binds);
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return null;
		}
	}

	
	public function delete_generated_bank_format($id)
	{
		$this->db->where('id', $id);
		$this->db->delete('generated_bank_formats');
	}


	/* Management approval delete */
	public function delete_management_approval_data($month_year,$employee_id)
	{
		$this->db->where('employee_id', $employee_id);
		$this->db->where('month_year', $month_year);
		$this->db->delete('management_payroll_approval');
	}
	public function delete_management_approval($id)
	{
		$this->db->where('id', $id);
		$this->db->delete('management_payroll_approval');
	}

	public function get_previous_refnodata($month)
	{
		$data = $this->db->select('ref_no')->where('salary_month', $month)->order_by('payslip_id', 'desc')->limit('1')->get('xin_salary_payslips')->row_array();
		if (!is_null($data)) {
			if (!is_null($data['ref_no'])) {
				$ref_data = explode("/", $data['ref_no']);
				if (!is_null($ref_data[4])) {
					return ((float)$ref_data[4] + 1);
				} else {
					return '00001';
				}
			} else {
				return '00001';
			}
		} else {
			return '00001';
		}
	}

	public function get_payslip_data($emp_id, $salary_month)
	{
		$sql = 'SELECT * FROM xin_salary_payslips WHERE employee_id = ? AND salary_month = ?';
		$binds = array($emp_id, $salary_month);
		$query = $this->db->query($sql, $binds);
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return null;
		}
	}

	public function list_payroll_status_report($cid, $id)
	{
		if (!empty($cid)) {
			$this->db->where('company_id', $cid);
		}

		if (!empty($id)) {
			$this->db->where_in('user_id', $id);
		}

		$this->db->where('user_role_id !=', 1);
		$this->db->where('super_privileges !=', 1);
		$query = $this->db->get('xin_employees');
		return $query;
	}

	public function all_employees_payment_history_pdf($p_company_id, $p_location_id, $p_department_id, $p_salary_month)
    {
        $this->db->select('*');
		$this->db->from('xin_salary_payslips');
        if(!empty($p_company_id)){
            $this->db->where('company_id', $p_company_id);
        }
        if(!empty($p_location_id)){
            $p_location_ids = explode(',',$p_location_id);
            $this->db->where_in('location_id', $p_location_ids);
        }
        if(!empty($p_department_id)){
            $p_department_ids = explode(',',$p_department_id);
            $this->db->where_in('department_id', $p_department_ids);
        }
        $this->db->where('salary_month', $p_salary_month);
		$query = $this->db->get();
        return $query;
    }

	//////////////////////////////Overtime Management////////////////////////////////////////////////////////////

	public function read_make_payment_employee_payslip_check($employee_id, $p_date)
    {

        $sql = 'SELECT * FROM xin_salary_payslips WHERE employee_id = ? and salary_month = ?';
        $binds = array($employee_id, $p_date);
        $query = $this->db->query($sql, $binds);
        return $query->result();
    }

	public function GetCompensateamount($user_id, $pay_date)
    {
        $sql = 'SELECT * FROM overtime_management WHERE employee_id = ? and month_year = ? and status = 2';
        $binds = array($user_id, $pay_date);
        $query = $this->db->query($sql, $binds);
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return null;
        }
    }

	/* ARBHQR0032 Start */
    public function add_salary_payslip_other_allowances($data)
    {
        $this->db->insert('xin_salary_payslip_other_allowances', $data);
        if ($this->db->affected_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function add_salary_payslip_other_deductions($data)
    {
        $this->db->insert('xin_salary_payslip_other_deductions', $data);
        if ($this->db->affected_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function delete_payslip_other_allowance_items($id)
    {
        $this->db->where('payslip_id', $id);
        $this->db->delete('xin_salary_payslip_other_allowances');
    }

    public function delete_payslip_other_deductions_items($id)
    {
        $this->db->where('payslip_id', $id);
        $this->db->delete('xin_salary_payslip_other_deductions');
    }
    /* ARBHQR0032 end */
	
	// ARBHQR0004 start
	// get company advance salaries report 
	public function get_company_advance_salary_report($id)
	{

		$this->db->query("SET SESSION sql_mode = ''");
		$sql = 'SELECT advance_salary_id,employee_id,company_id,month_year,one_time_deduct,monthly_installment,reason,status,total_paid,is_deducted_from_salary,created_at,SUM(`xin_advance_salaries`.advance_amount) AS advance_amount FROM `xin_advance_salaries` where status=1 and company_id = ? group by employee_id';
		$binds = array($id);
		$query = $this->db->query($sql, $binds);
		return $query;
	}
	// ARBHQR0004 end

	public function delete_generated_bank($id)
	{
		$this->db->where('id', $id);
		$this->db->delete('generated_bank_formats');
	}
	public function GetAnnualamount($user_id, $pay_date)
	{
		$sql = 'SELECT * FROM xin_annual_leave_applications WHERE employee_id = ? and payroll_month_year = ? and status = 2';
		$binds = array($user_id, $pay_date);
		$query = $this->db->query($sql, $binds);
		// 		$str = $this->db->last_query();
		//         echo $str;
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return null;
		}
	}
	public function read_management_information_byid($id)
	{

		$sql = 'SELECT * FROM management_payroll_approval WHERE id = ?';
		$binds = array($id);
		$query = $this->db->query($sql, $binds);

		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return null;
		}
	}

	public function add_hr_approved_allowances($data)
    {
        $this->db->insert('hr_level_approved_allowance', $data);
        if ($this->db->affected_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

	public function add_hr_approved_datas($table_name, $data) {
       
        $this->db->insert($table_name, $data);
        if ($this->db->affected_rows() > 0) {
            return true;
        } else {
            return false;
        }
       
    }
	public function delete_hr_approved_data($table_name, $id)
    {
        $this->db->delete($table_name, array('hr_approval_id' => $id));
    }
	public function add_hr_approved_payment_balance($data)
    {
        $this->db->insert('hr_level_approved_payment_balance', $data);
        if ($this->db->affected_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }
	public function add_salary_payslip_payment_balance($data)
	{
		$this->db->insert('payslip_payment_balance', $data);
		if ($this->db->affected_rows() > 0) {
			return true;
		} else {
			return false;
		}
	}
	public function delete_payment_balance($id)
	{
		$this->db->where('payslip_id', $id);
		$this->db->delete('payslip_payment_balance');
	}

	public function get_management_approval_list_new_payroll($location_id, $company_id,$department_id, $employee_id, $month_year,$start,$length)
	{

		$this->db->select("xin_employees.user_id, xin_employees.first_name, xin_employees.middle_name, xin_employees.last_name, xin_employees.employee_id, xin_employees.company_id, xin_employees.location_id, xin_employees.basic_salary, xin_employees.office_shift_id, xin_employees.wages_type");
		$this->db->from('xin_employees');
		$this->db->join('xin_employee_exit', 
						'xin_employee_exit.employee_id = xin_employees.user_id 
						 AND xin_employee_exit.is_inactivate_account = 1 
						 AND DATE_FORMAT(xin_employee_exit.exit_date, "%Y-%m") < ' . $this->db->escape($month_year), 
						'left');
		$this->db->where('xin_employee_exit.employee_id IS NULL');

		
		if (!empty($company_id)) {
			$this->db->where('xin_employees.company_id', $company_id);
		}
		if (!empty($location_id)) {
			$this->db->where_in('xin_employees.location_id', explode(',', $location_id));
		}
		if (!empty($department_id)) {
			$this->db->where_in('xin_employees.department_id', explode(',', $department_id));
		}
		if (!empty($employee_id) && $employee_id != 0) {
			$this->db->where_in('xin_employees.user_id', explode(',', $employee_id));
		}

		
		$this->db->where('super_privileges !=', 1);
		$this->db->where('user_role_id !=', 1);

	
		$this->db->limit($length,$start);

		$query = $this->db->get();
		return $query->result();

		
	}

	public function generated_bank_format_details_list_payroll($location_id, $month_year, $company_id,$department_id, $user_id, $value_date)
	{

		$this->db->select("xin_employees.*,generated_bank_formats.month_year,generated_bank_formats.value_date,generated_bank_formats.id as generated_id");
		$this->db->from('generated_bank_formats');
		$this->db->join('xin_employees', 'xin_employees.user_id = generated_bank_formats.employee_id', 'left');
		if (!empty($company_id)) {
			$this->db->where('generated_bank_formats.company_id', $company_id);
		}
		if (!empty($location_id)) {
			$location_ids = explode(',', $location_id);
			$this->db->where_in('generated_bank_formats.location_id', $location_ids);
		}
		if (!empty($department_id)) {
			$department_ids = explode(',', $department_id);
			$this->db->where_in('xin_employees.department_id', $department_ids);
		}
		
		if (!empty($user_id)) {
			$user_ids = explode(',', $user_id);
			$this->db->where_in('xin_employees.user_id', $user_ids);
		}
		if (!empty($month_year)) {
			$this->db->where('generated_bank_formats.month_year', $month_year);
		}
		if (!empty($value_date)) {
			$this->db->where('generated_bank_formats.value_date', $value_date);
		}

		$this->db->where('user_role_id != ', 1);
		$this->db->where('super_privileges!= ', 1);
		$query = $this->db->get();
		return $query;
	}

	public function cash_format_details_list_payroll($location_id, $month_year, $company_id,$department_id, $user_id)
	{

		$this->db->select("xin_employees.*");
		$this->db->from('xin_employees');
		$this->db->join('management_payroll_approval', 'management_payroll_approval.employee_id = xin_employees.user_id', 'left');
		if (!empty($company_id)) {
			$this->db->where('xin_employees.company_id', $company_id);
		}
		if (!empty($location_id)) {
			$location_ids = explode(',', $location_id);
			$this->db->where_in('management_payroll_approval.location_id', $location_ids);
		}
		if (!empty($user_id)) {
			$user_ids = explode(',', $user_id);
			$this->db->where_in('xin_employees.user_id', $user_ids);
		}
		if (!empty($department_id)) {
			$department_ids = explode(',', $department_id);
			$this->db->where_in('xin_employees.department_id', $department_ids);
		}
		if (!empty($month_year)) {
			$this->db->where('management_payroll_approval.month_year', $month_year);
		}
		if (!empty($location_id) || !empty($month_year)) {
			$this->db->where('xin_employees.user_role_id !=', 1);
		}
		$this->db->where('xin_employees.payment_mode =', 'cash');
		$this->db->where('super_privileges !=', 1);
		$query = $this->db->get();
		return $query;
	}

	public function cheque_format_details_list_payroll($location_id, $month_year, $company_id,$department_id, $user_id)
	{

		$this->db->select("xin_employees.*");
		$this->db->from('xin_employees');
		$this->db->join('management_payroll_approval', 'management_payroll_approval.employee_id = xin_employees.user_id', 'left');
		if (!empty($company_id)) {
			$this->db->where('xin_employees.company_id', $company_id);
		}
		if (!empty($location_id)) {
			$location_ids = explode(',', $location_id);
			$this->db->where_in('management_payroll_approval.location_id', $location_ids);
		}
		if (!empty($department_id)) {
			$department_ids = explode(',', $department_id);
			$this->db->where_in('xin_employees.department_id', $department_ids);
		}
		if (!empty($user_id)) {
			$user_ids = explode(',', $user_id);
			$this->db->where_in('xin_employees.user_id', $user_ids);
		}
		if (!empty($month_year)) {
			$this->db->where('management_payroll_approval.month_year', $month_year);
		}
		if (!empty($location_id) || !empty($month_year)) {
			$this->db->where('xin_employees.user_role_id !=', 1);
		}
		$this->db->where('xin_employees.payment_mode =', 'cheque');
		$this->db->where('super_privileges!=', 1);
		$query = $this->db->get();
		return $query;
	}


	public function get_employee_comp_template_payroll($cid, $id, $lid,$dpid,$month_year)
	{
		if (!empty($cid)) {
			$this->db->where('company_id', $cid);
		}
		if (!empty($lid)) {
			$lids = explode(',', $lid);
			$this->db->where_in('location_id', $lids);
		}
		if (!empty($dpid)) {
			$dpids = explode(',', $dpid);
			$this->db->where_in('department_id', $dpids);
		}
		if (!empty($id)) {
			$ids = explode(',', $id);
			$this->db->where_in('user_id', $ids);
		}
		$this->db->where('user_id!=', 1);
		$this->db->where('super_privileges!=', 1);
		$query = $this->db->get('xin_employees');

		return $query;
	}
	public function annual_leave_amount_payroll($user_id, $month_year)
	{
		$total_amount = 0;
		
		$leave_annual_det = $this->Employees_model->count_employee_wise_approved_annulal_leaves($user_id, $month_year);
		
		if($leave_annual_det->num_rows() > 0){
			
			foreach ($leave_annual_det->result() as $val) {
				$days = $val->days;
				$total_earning = $this->Timesheet_model->Basic_salary_for_annual($user_id, $val->from_date, $val->to_date);
				$days_ofmonth = $this->Timesheet_model->Days_ofmonth_for_annual();
				$increment = $this->Xin_model->increment_amount($user_id, $month_year);
				$annual_amt = ($total_earning + $increment[0]) / $days_ofmonth * $days;
				$total_amount+= $annual_amt;
													
				
			}
		}
		
		return $total_amount;
		
	}
	public function read_payslip_annual_leave_information($id)
	{

		$sql = 'SELECT * FROM xin_salary_payslip_annual_leave_amount WHERE payslip_id = ?';
		$binds = array($id);
		$query = $this->db->query($sql, $binds);

		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return null;
		}
	}
	public function ann_leave_update($data, $id)
	{
		$this->db->where('id', $id);
		if ($this->db->update('xin_annual_leave_applications', $data)) {
			return true;
		} else {
			return false;
		}
	}
	public function delete_payslip_ann_items($id)
	{
		$this->db->where('payslip_id', $id);
		$this->db->delete('xin_salary_payslip_annual_leave_amount');
		
		
	}
	public function get_management_approval_total_count($location_id, $company_id,$department_id, $employee_id, $month_year)
	{

		$this->db->select("COUNT(xin_employees.user_id) as total_count");
		$this->db->from('xin_employees');
		 // Join with employee exit table to exclude those who have left the company
		 $this->db->join(
			'xin_employee_exit', 
			'xin_employee_exit.employee_id = xin_employees.user_id 
			AND xin_employee_exit.is_inactivate_account = 1 
			AND DATE_FORMAT(xin_employee_exit.exit_date, "%Y-%m") < ' . $this->db->escape($month_year), 
			'left'
		);
		$this->db->where('xin_employee_exit.employee_id IS NULL');  // Ensure employee is not marked as exited
		
		// Apply filters if provided
		if (!empty($company_id)) {
			$this->db->where('xin_employees.company_id', $company_id);
		}
		if (!empty($location_id)) {
			$this->db->where_in('xin_employees.location_id', explode(',', $location_id));
		}
		if (!empty($department_id)) {
			$this->db->where_in('xin_employees.department_id', explode(',', $department_id));
		}
		if (!empty($employee_id) && $employee_id != 0) {
			$this->db->where_in('xin_employees.user_id', explode(',', $employee_id));
		}
		
		$this->db->where('super_privileges !=', 1);
		$this->db->where('user_role_id !=', 1);
		$query = $this->db->get();
		
		// Return the count of records
		$result = $query->row();
		return $result->total_count;

		
	}
	public function get_management_approval_list_new_payroll_excel_pdf($location_id, $company_id,$department_id, $employee_id, $month_year,$page)
	{

		$this->db->select("xin_employees.*");
		$this->db->from('xin_employees');
		$this->db->join('xin_employee_exit', 
						'xin_employee_exit.employee_id = xin_employees.user_id 
						 AND xin_employee_exit.is_inactivate_account = 1 
						 AND DATE_FORMAT(xin_employee_exit.exit_date, "%Y-%m") < ' . $this->db->escape($month_year), 
						'left');
		$this->db->where('xin_employee_exit.employee_id IS NULL');

		
		if (!empty($company_id)) {
			$this->db->where('xin_employees.company_id', $company_id);
		}
		if (!empty($location_id)) {
			$this->db->where_in('xin_employees.location_id', explode(',', $location_id));
		}
		if (!empty($department_id)) {
			$this->db->where_in('xin_employees.department_id', explode(',', $department_id));
		}
		if (!empty($employee_id) && $employee_id != 0) {
			$this->db->where_in('xin_employees.user_id', explode(',', $employee_id));
		}

		
		$this->db->where('super_privileges !=', 1);
		$this->db->where('user_role_id !=', 1);
		$limit = 10; 
		$offset = ($page - 1) * $limit; 
		$this->db->limit($limit, $offset); 
	
		

		$query = $this->db->get();
		return $query->result();

		
	}
	public function get_total_pages_count() {
        $this->db->from('temp_excel_payroll');
        $total_records = $this->db->count_all_results();

        return $total_records; 
    }
	public function insert_temp_excel_payroll($data) {

        $this->db->insert_batch('temp_excel_payroll', $data);
		
		if ($this->db->affected_rows() > 0) {
            return true; 
        } else {
            return false;
        }
    }

}
