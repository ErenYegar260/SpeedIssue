<?php

/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the HRSALE License
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.hrsale.com/license.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to hrsalesoft@gmail.com so we can send you a copy immediately.
 *
 * @author   HRSALE
 * @author-email  hrsalesoft@gmail.com
 * @copyright  Copyright © hrsale.com. All Rights Reserved
 */
defined('BASEPATH') or exit('No direct script access allowed');

class Payroll extends MY_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->library('Pdf');
		//load the model
		$this->load->model("Payroll_model");
		$this->load->model("Xin_model");
		$this->load->model("Employees_model");
		$this->load->model("Designation_model");
		$this->load->model("Department_model");
		$this->load->model("Location_model");
		$this->load->model("Timesheet_model");
		$this->load->model("Overtime_request_model");
		$this->load->model("Company_model");
		$this->load->model("Finance_model");
		$this->load->helper('string');
		$this->load->model("Roles_model");
		$this->load->model("Resignation_model");
		$this->load->model("Loan");
		$this->load->model("Advance");
		$this->load->model("Office_shift_custom_model");
		$this->load->model("Reports_model");
	}

	/*Function to set JSON output*/
	public function output($Return = array())
	{
		/*Set response header*/
		header("Access-Control-Allow-Origin: *");
		header("Content-Type: application/json; charset=UTF-8");
		/*Final JSON response*/
		exit(json_encode($Return));
	}

	


	public function management_approval()
	{
		$session = $this->session->userdata('username');
		if (empty($session)) {
			redirect('admin/');
		}
		$data['title'] = 'Management Approval | ' . $this->Xin_model->site_title();
		$data['all_employees'] = $this->Xin_model->all_employees();
		//$data['all_companies'] = $this->Xin_model->get_companies();
		$data['breadcrumbs'] = 'Management Approval';
		$data['all_locations'] = $this->Xin_model->all_company_locations();
		$data['get_all_companies'] = $this->Xin_model->get_companies();
		$data['path_url'] = 'management_approval';
		$role_resources_ids = $this->Xin_model->user_role_resource();
		if (in_array('272', $role_resources_ids)) {
			if (!empty($session)) {
				$data['subview'] = $this->load->view("admin/payroll/management_approval", $data, TRUE);
				$this->load->view('admin/layout/layout_main', $data); //page load
			} else {
				redirect('admin/');
			}
		} else {
			redirect('admin/dashboard');
		}
	}

	public function management_payroll_approval()
	{
		$location_id = $this->input->post('location_id');
		$month_year = $this->input->post('month_year');
		$count = $this->Payroll_model->get_management_approval_count($location_id, $month_year);
		if ($count == 0) {
			$data = array(
				'location_id' => $location_id,
				'month_year' => $month_year,
				'status' => $this->input->post('status')

			);
			$this->Payroll_model->add_management_approval($data);
		}
		redirect('admin/payroll/bank_format');
	}

	public function management_approval_list()
	{
		//ini_set('max_execution_time', '1000');
		//error_reporting(0);

		$data['title'] = $this->Xin_model->site_title();
		$session = $this->session->userdata('username');
		if (empty($session)) {
			redirect('admin/');
		}
		// Datatables Variables
		$draw = intval($this->input->get("draw"));
		$start = intval($this->input->get("start"));
		$length = intval($this->input->get("length"));
		
		// date and employee id/company id
		$p_date = $this->input->get("month_year");
		$role_resources_ids = $this->Xin_model->user_role_resource();
		$total_count = $this->Payroll_model->get_management_approval_total_count($this->input->get("location_id"), $this->input->get("company_id"),$this->input->get("department_id"), $this->input->get("employee_id"), $p_date);
		// print_r($total_count);
		$user_info = $this->Xin_model->read_user_info($session['user_id']);
		if ($user_info[0]->user_role_id == 1  || in_array('1500', $role_resources_ids)) {
			$payslip = $this->Payroll_model->get_management_approval_list_new_payroll($this->input->get("location_id"), $this->input->get("company_id"),$this->input->get("department_id"), $this->input->get("employee_id"), $p_date,$start,$length);
			$payslip_count = count($payslip);
		} elseif ($user_info[0]->super_privileges == 1 && $user_info[0]->super_privilege_criteria == 0) {
			$payslip = $this->Payroll_model->get_management_approval_list_new_payroll($this->input->get("location_id"), $this->input->get("company_id"),$this->input->get("department_id"), $this->input->get("employee_id"), '', $p_date);
			$payslip_count = count($payslip);
		} elseif (in_array('275', $role_resources_ids) && $user_info[0]->super_privileges == 1 && $user_info[0]->super_privilege_criteria == 1) {
			$payslip = $this->Payroll_model->get_management_approval_list_new_payroll($this->input->get("location_id"), $this->input->get("company_id"),$this->input->get("department_id"), $this->input->get("employee_id"), '', $p_date);
			$payslip_count = count($payslip);
		} elseif (in_array('275', $role_resources_ids) && $user_info[0]->super_privileges == 0) {
			$payslip = $this->Payroll_model->get_management_approval_list_new_payroll($this->input->get("location_id"), $user_info[0]->company_id,$this->input->get("department_id"), $this->input->get("employee_id"), '', $p_date);
			$payslip_count = count($payslip);
		} else {
			$payslip = $this->Payroll_model->get_management_approval_list_new_payroll('', '','', $session['user_id'], $p_date);
			$payslip_count = count($payslip);
		}
		$system = $this->Xin_model->read_setting_info(1);
		$data = array();
		if (!empty($payslip)) {
			foreach ($payslip as $r) {
				// user full name
				$emp_name = $r->first_name . ' ' . $r->middle_name . ' ' . $r->last_name;
				$full_name = '<a target="_blank" class="text-primary" href="' . site_url() . 'admin/employees/detail/' . $r->user_id . '">' . $emp_name . '</a>';

				// get total hours > worked > employee
				$pay_date = $this->input->get('month_year');
				
				// get company
				$company = $this->Xin_model->read_company_info($r->company_id);
				if (!is_null($company)) {
					$comp_name = $company[0]->name;
				} else {
					$comp_name = '--';
				}

				// 1: salary type
				if ($r->wages_type == 1) {
					$wages_type = $this->lang->line('xin_payroll_basic_salary');
					if ($system[0]->is_half_monthly == 1) {
						$basic_salary = $r->basic_salary / 2;
					} else {
						$basic_salary = $r->basic_salary;
					}
					$p_class = 'emo_monthly_pay';
					$view_p_class = 'payroll_template_modal';
				} else if ($r->wages_type == 2) {
					$wages_type = $this->lang->line('xin_employee_daily_wages');
					if ($pcount > 0) {
						$basic_salary = $pcount * $r->basic_salary;
					} else {
						$basic_salary = $pcount;
					}
					$p_class = 'emo_hourly_pay';
					$view_p_class = 'hourlywages_template_modal';
				} else {
					$wages_type = $this->lang->line('xin_payroll_basic_salary');
					if ($system[0]->is_half_monthly == 1) {
						$basic_salary = $r->basic_salary / 2;
					} else {
						$basic_salary = $r->basic_salary;
					}
					$p_class = 'emo_monthly_pay';
					$view_p_class = 'payroll_template_modal';
				}
				// all allowances, all loan/deductions
				$all_allo_and_dedct = $this->Xin_model->payroll_all_dedctions_and_additions($r->user_id, $p_date, $basic_salary);

				$salary_calc = $this->Xin_model->payroll_GrossSalary($r->user_id, $p_date, $basic_salary, $all_allo_and_dedct);
				$total_net_salary = $salary_calc[0];

		
				$status_pay_value = 0;	
				$status_pay = '<span class="label label-danger">' . $this->lang->line('xin_payroll_unpaid') . '</span>';
				if (in_array('274', $role_resources_ids)) {
					$mpay = '<span data-toggle="tooltip" data-state="primary" data-placement="top" title="Payment Summary"><button type="button" class="btn icon-btn btn-sm btn-outline-secondary waves-effect waves-light" data-toggle="modal" data-target=".' . $p_class . '" data-employee_id="' . $r->user_id . '" data-payment_date="' . $p_date . '" data-company_id="' . $this->input->get("company_id") . '"><span class="far fa-money-bill-alt"></span></button></span>';
				} else {
					$mpay = '';
				}
				$delete = '';
				$total_net_salary = $total_net_salary;	
				if (in_array('276', $role_resources_ids)) {
					$detail = '<span data-toggle="tooltip" data-state="primary" data-placement="top" title="' . $this->lang->line('xin_view') . '"><button type="button" class="btn icon-btn btn-sm btn-outline-secondary waves-effect waves-light" data-toggle="modal" data-target="#' . $view_p_class . '" data-employee_id="' . $r->user_id . '" data-payment_date="' . $p_date . '"" data-net="' . $total_net_salary . '"><span class="fa fa-eye"></span></button></span>';
				} else {
					$detail  = '';
				}
				

				$net_salary = number_format((float)$total_net_salary, 2, '.', '');
				$basic_salary = number_format((float)$basic_salary, 2, '.', '');
				
				if ($basic_salary == 0 || $basic_salary == '') {
					$fmpay = '';
				} else {
					$fmpay = $mpay;
				}

				//new leave calc
				$month_year = $pay_date;
				$start_date_y = date('m', strtotime($month_year));
				$start_date_y_m = date('Y-m', strtotime($month_year));
				$emp_leaves = $this->Employees_model->emp_leave_fetch($r->user_id, $start_date_y);

				$user_id = $r->user_id;
				$date = strtotime(date("Y-m-d"));
				if (!isset($month_year)) {
					$day = date('d', $date);
					$month = date('m', $date);
					$year = date('Y', $date);
					$month_year = date('Y-m');
				} else {
					$imonth_year = explode('-', $month_year);
					$day = date('d', $date);
					$month = date($imonth_year[1], $date);
					$year = date($imonth_year[0], $date);
					$month_year = $month_year;
				}
				$daysInMonth = date('t', mktime(0, 0, 0, $month, 1, $year));
				$k = 1;
				$extra_amount = $tot_days_lop_normal = 0;
				$tot_days = 0;

				//Using attendance
				$arry_years = array();
				$st = '';
				$p_count_val = 0;

				$method = $this->Xin_model->payroll_find_method();
				if ($method == 'cuttoff') {
					$payroll_date = $year . '-' . $month;
					$user = $this->Xin_model->read_user_info($r->user_id);
					$company_id = $user[0]->company_id;
					$location_id = $user[0]->location_id;
					if (strtotime(date('Y-m', strtotime($user[0]->date_of_joining))) == strtotime($month_year)) {
						$start_date_co = $user[0]->date_of_joining;
					} else {
						$start_date_co = $this->Xin_model->payroll_startdate($payroll_date, $company_id, $location_id);
					}
					$end_date_co = $this->Xin_model->payroll_enddate($payroll_date, $company_id, $location_id);
				} else {
					$start_date_co = $year . '-' . $month . '-' . '01';
					$end_date_co = $year . '-' . $month . '-' . $daysInMonth;
				}



				$present_arr = array();
				//$st = $this->db->last_query();
				$leave_arr = $half_leave_arr = array();
				$total_leaves = count($leave_arr) + count($half_leave_arr);
				$p_count_val += abs($total_leaves - $tot_days);
				$present_count = $public_holiday_count = $holiday_count = $leave__half_day_count = 0;
				$absent_count = 0;
				$leave_count = 0;
				$user = $this->Xin_model->read_user_info($r->user_id);
				if (strtotime($month_year) == strtotime(date('Y-m'))) {
					$month_year_att = $month_year . '-01';
					if ($user[0]->date_of_leaving) {
						if (strtotime($month_year) == strtotime(date("Y-m", strtotime($user[0]->date_of_leaving)))) {
							if (strtotime(date("Y-m-d", strtotime($user[0]->date_of_leaving))) > strtotime(date("Y-m-d"))) {
								$difference_days = 0;
							} else {
								$get_attendance_last_entered = $this->Timesheet_model->attendance_this_month_value_last($user_id, $start_date_y_m);
								if ($get_attendance_last_entered) {
									$month_year_att_end = $get_attendance_last_entered[0]->attendance_date;
									$startDate = new DateTime($month_year_att);
									$endDate = new DateTime($month_year_att_end);
									$difference = $endDate->diff($startDate);
									$difference_days = $difference->format("%a") + 1;
								} else {
									$difference_days = 0;
								}
							}
						} else {
							$difference_days = 0;
						}
					} else {
						$get_attendance_last_entered = $this->Timesheet_model->attendance_this_month_value_last($user_id, $start_date_y_m);
						if ($get_attendance_last_entered) {
							$month_year_att_end = $get_attendance_last_entered[0]->attendance_date;
							//$month_year_att_end = $month_year.'-'.date('d');
							$startDate = new DateTime($month_year_att);
							$endDate = new DateTime($month_year_att_end);
							$difference = $endDate->diff($startDate);
							$difference_days = $difference->format("%a") + 1;
						} else {
							$difference_days = 0;
						}
					}
				} else if (strtotime($month_year) <= strtotime(date('Y-m'))) {
					$month_year_att = $month_year . '-01';
					$get_attendance_last_entered = $this->Timesheet_model->attendance_this_month_value_last($user_id, $start_date_y_m);
					if ($get_attendance_last_entered) {
						$month_year_att_end = $get_attendance_last_entered[0]->attendance_date;
						//$month_year_att_end = $month_year.'-'.date('d');
						$startDate = new DateTime($month_year_att);
						$endDate = new DateTime($month_year_att_end);
						$difference = $endDate->diff($startDate);
						$difference_days = $difference->format("%a") + 1;
					} else {
						$difference_days = 0;
					}
				} else if (strtotime($month_year) >= strtotime(date('Y-m'))) {
					$difference_days = 0;
				}
				if ($difference_days >= 25) {
					$difference_days = $daysInMonth;
				}


				$office_shift = $this->Timesheet_model->read_office_shift_information($r->office_shift_id);

				//Number of days calc
				$total_k = 1;
				$k = 1;
				$extra_amount = 0;
				$tot_days = 0;
				$emp_leave_in_year_count = $extra_in_month = $total_final = $final_amount = 0;
				$month_year_end = date("Y-m-d", strtotime("$month_year-01"));
				$month_last_date = date("Y-m-d", strtotime("$month_year-$daysInMonth"));
				$totol_deduction = $count_l = $hlfcount = $tot_days_lop = 0;

				//end number of calc
				$attendence_calc = $this->Xin_model->AttendnceCount_calc_common($r->office_shift_id, $start_date_co, $end_date_co, $r->company_id, $r->user_id, $difference_days, $year, $month);
				if ($attendence_calc) {
					$tot_days_lop_normal = $attendence_calc[0];
					$totl_num_days_pres = $attendence_calc[1];
				} else {
					$tot_days_lop_normal = 0;
					$totl_num_days_pres = 0;
				}
				$tot_days_lop = $tot_days_lop_normal;

				$bs_pay = $r->basic_salary;
				/* Lop Calc Common */
				$lop_calc = $this->Xin_model->LopCalcCommon($net_salary, $daysInMonth, $tot_days_lop);
				$leave_cut_salary = $lop_calc[0];
				/* End Lop calc Common */

				/* Net salary calculations */
				$net_salary_calc = $this->Xin_model->NetsalaryCalcCommon($total_net_salary, $daysInMonth, $totl_num_days_pres);
				$net_salary = $net_salary_calc[0];
				
				/* Final Net salary */

				$final_net_salary = $this->Xin_model->FinalNetSalaryCalcCommon($net_salary, $leave_cut_salary, $all_allo_and_dedct);
				$net_salary = $final_net_salary[0];


				$start_date_y_m = date('Y-m', strtotime($month_year));

				$totl_amt = 0;
				//leave encashment calc
				$start_date_y = date('m', strtotime($month_year));
				$start_date_y_m = date('Y-m', strtotime($month_year));
				$emp_leave_enchas = $this->Employees_model->emp_leave_encashment_fetch($user_id, $start_date_y);

				$totl_amt_leva = $annual_amt_pay = 0;
				if ($emp_leave_enchas) {
					foreach ($emp_leave_enchas as $emp_leave_ench) {
						$totl_amt_leva += $emp_leave_ench->amount;
					}
				}

				$encashment = $this->Payroll_model->GetEncashmentamount($user_id, $start_date_y_m);
				if(!empty($encashment))
				{
				foreach ($encashment as $emp_leave) {
					if ($emp_leave->given_methode == 'Connect_To_Payroll') {
						$annual_amt_pay += $emp_leave->amount;
					}
				}
			    }

				$total_compensateamount_allowance = $total_compensateamount_deduction = 0;
				$compensateamount = $this->Payroll_model->GetCompensateamount($user_id, $start_date_y_m);
				if (!empty($compensateamount)) {
					foreach ($compensateamount as $val) {
						if ($val->amount_type == 1) {
							$total_compensateamount_allowance += $val->total_amount;
						} else {
							$total_compensateamount_deduction += $val->total_amount;
						}
					}
				}

				$net_salary = $net_salary + $totl_amt + $totl_amt_leva + $annual_amt_pay + $total_compensateamount_allowance - $total_compensateamount_deduction;
				
				$basic_salary = $this->Xin_model->company_currency_sign($basic_salary, $r->company_id);
				if ($net_salary < 0) {
					$net_salary = '0.00';
				}
				$iemp_name = $emp_name;

				//action link
				$act = $detail . $fmpay . $delete;
				if ($r->wages_type == 1) {
					if ($system[0]->is_half_monthly == 1) {
						$emp_payroll_wage = $wages_type;
					} else {
						$emp_payroll_wage = $wages_type;
					}
				} else {
					$emp_payroll_wage = $wages_type;
				}
				if (in_array('81', $role_resources_ids)) {
					$emp_id = '<a target="_blank" href="' . site_url('admin/employees/detail/') . $r->user_id . '" class="text-muted" data-state="primary" data-placement="top" data-toggle="tooltip" title="Employee Detail">' . $r->employee_id . ' <i class="fas fa-arrow-circle-right"></i></a>';
				} else {
					$emp_id = $r->employee_id;
				}

				$paymonth = date('m', strtotime($pay_date));
				$payyear = date('Y', strtotime($pay_date));
				$empdata = $this->Employees_model->get_employee_details($user_id);
				$basepay = $empdata[0]->basic_salary;
				$no_of_days = date('t', strtotime($pay_date));

				$late_amount = 0;
				$latededuction = $this->Xin_model->GetLateDeductionCalcCommon($user_id, $paymonth, $payyear, $no_of_days);
				$late_amount = $latededuction[0];

				$userinfo = $this->Employees_model->get_employee_details($user_id);
				$companyinfo = $this->Employees_model->get_company_details($userinfo[0]->company_id);
				$late_status = $companyinfo[0]->late_deduction;
				if ($late_status == 'yes') {
					$late_amount = $late_amount;
				}
				
				$overtime_amount = $this->Xin_model->getEmployeeMonthlyOTamount($user_id, $pay_date);

				$overtime_incentive_amount = $this->Xin_model->getEmployeeMonthlyOT_incentive_amount($user_id, $pay_date);

				// Air ticket Encashment Start
				$encashment_check = $this->Employees_model->employee_encashment_check($user_id, $month_year);
				if (!empty($encashment_check)) {
					$encashstatus = $encashment_check[0]->status;
					if (!is_null($encashstatus)) {
						$encashrate = $this->Employees_model->get_employee_encashrate_payroll($user_id, $month_year);
						$encash_amt = $encashrate[0]->amount;
					} else {
						$encash_amt = 0;
					}
				} else {
					$encash_amt = 0;
				}

				// Air ticket Encashment End
				//$nettotal = $net_salary;
				
				$overtimestatus = $this->Employees_model->get_employee_overtimestatus($user_id);
				$payment_balance = $this->Employees_model->get_employee_payment_balance($user_id,$p_date);
				$otstatus = $overtimestatus[0]->ot_eligible;
				if ($otstatus == "yes") {
					if ($net_salary == 0) {
						$nettotal = 0.000;
					} else {
						$nettotal = $net_salary + $overtime_amount  + $overtime_incentive_amount - $late_amount;
					}
				} else {
					if ($net_salary == 0) {
						$nettotal = 0.000;
					} else {
						$nettotal = $net_salary - $late_amount;
					}
				}
				$nettotal +=  $payment_balance ; 
				
				$deduction_amt_net_total_zero = 0;
				$deduction_overtime_amount_total_zero = 0;
				$deduction_late_amount_total_zero = 0;
				if ($nettotal <= 0) {
					$deduction_amt_net_total_zero = $leave_cut_salary;
					$deduction_late_amount_total_zero = $late_amount;
					if ($otstatus == "yes") {
						$deduction_overtime_amount_total_zero = $overtime_amount + $overtime_incentive_amount;
					}
				}
				
				/* Expense Claim Start */
				//if ($nettotal > 0) {
				$expense_total_claim = $this->Xin_model->FindExpenseClaimTotal($user_id, $this->input->get('month_year'));
				/* if annual leave connect to payroll start */
				$annual_leave_amnt = 0 ;
				$annual_leave_amnt = $this->Payroll_model->annual_leave_amount_payroll($user_id,$month_year) ;
				
				/* if annual leave connect to payroll start */
				$nettotal = $nettotal + $expense_total_claim + $encash_amt + $deduction_overtime_amount_total_zero - ($deduction_amt_net_total_zero + $deduction_late_amount_total_zero);
				//}
				
				if ($nettotal < 0) {
					$nettotal = 0;
				}
				/* Expense Claim End */
				$nettotal +=$annual_leave_amnt; 
				$nettotal_salary = $nettotal;
				$nettotal = $this->Xin_model->company_currency_sign($nettotal, $r->company_id);


				$location_id = $r->location_id;
				$monthyear = $payyear . "-" . $paymonth;
				$get_management_status = $this->Employees_model->get_management_employee_status($r->user_id, $location_id, $p_date);


				$days_in_month = $days = date("t");

				$payment_check = $this->Payroll_model->read_make_payment_payslip_check($user_id, $p_date);
				if ($payment_check->num_rows() > 0) {

					$make_payment = $this->Payroll_model->read_make_payment_payslip($r->user_id, $p_date);
					$nettotal = $this->Xin_model->company_currency_sign($make_payment[0]->net_salary, $r->company_id);
					$nettotal_salary = $make_payment[0]->net_salary;
				}
				$management_status_val = 1;
				if (empty($get_management_status)) {
					$management_status_val = 1;
					$disable = '';
					if ($nettotal_salary <= 0) {
						$disable = 'disabled';
					} else {
						$disable = 'checked';
					}
					$management_status = '<input type="checkbox" class="editor-active check_management_status" name="status_val" value="' . $r->user_id . '" ' . $disable . '>';
				} else {
					$management_status_val = 0;
					$management_status = '<input type="checkbox" class="editor-active check_management_status" name="status_val" value="' . $r->user_id . '" checked>';
				}
				$current_month = date('Y-m');
				$search_month = date('Y-m', strtotime($month_year));
				if (strtotime($search_month) > strtotime($current_month)) {
					$nettotal = $this->Xin_model->company_currency_sign(0, $r->company_id);
				}

				if ($status_pay_value < 1 && $management_status_val > 0) {
					if (in_array('276', $role_resources_ids) || in_array('273', $role_resources_ids)) {
						$data[] = array(

							$emp_id,
							$iemp_name,
							$emp_payroll_wage,
							$basic_salary,
							$nettotal,
							$status_pay,
							$management_status,
							$act,
						);
					} else {
						$data[] = array(
							$emp_id,
							$iemp_name,
							$emp_payroll_wage,
							$basic_salary,
							$nettotal,
							$status_pay,
							$management_status
						);
					}
				}
			}
			
			if (sizeof($data) > 0) {
				usort($data, function ($a, $b) {
					return $a[5] > $b[5];
				});
			}
		}


		$output = array(
			"draw" => $draw,
			"recordsTotal" => $total_count,
			"recordsFiltered" => $total_count,
			"data" => $data
		);
		echo json_encode($output);
		exit();
	}

	
	public function management_approval_pdf_excel_new()
	{
		ini_set('max_execution_time', '1000');
		error_reporting(0);

		$data['title'] = $this->Xin_model->site_title();
		$session = $this->session->userdata('username');
		if (empty($session)) {
			redirect('admin/');
		}
		// Datatables Variables
		$draw = intval($this->input->get("draw"));
		$start = intval($this->input->get("start"));
		$length = intval($this->input->get("length"));

		// date and employee id/company id
		if (empty($this->input->post("p_month_year"))) {
			$p_date = date('Y-m');
		} else {
			$p_date = $this->input->post("p_month_year");
		}
		  
		//$search_department_id = $this->input->post("p_department_id");
		$search_employee_id = $this->input->post("p_employee_id");
		$search_company_id = $this->input->post("p_company_id");
		$search_location_id = $this->input->post("p_location_id");
		$role_resources_ids = $this->Xin_model->user_role_resource();
		$user_info = $this->Xin_model->read_user_info($session['user_id']);/* ARBHQR0024 start*/
		if ($user_info[0]->user_role_id == 1  || in_array('1500', $role_resources_ids)) {
			$payslip = $this->Payroll_model->get_management_approval_list_new_payroll_excel_pdf($this->input->post("p_location_id"), $this->input->post("p_company_id"),$this->input->post("p_department_id"), $this->input->post("p_employee_id"), $p_date);
			$payslip_count = count($payslip);
		} elseif ($user_info[0]->super_privileges == 1 && $user_info[0]->super_privilege_criteria == 0) {
			$payslip = $this->Payroll_model->get_management_approval_list_new_payroll($this->input->post("p_location_id"), $this->input->post("p_company_id"),$this->input->post("p_department_id"), $this->input->post("p_employee_id"), '', $p_date);
			$payslip_count = count($payslip);
		} elseif (in_array('275', $role_resources_ids) && $user_info[0]->super_privileges == 1 && $user_info[0]->super_privilege_criteria == 1) {
			$payslip = $this->Payroll_model->get_management_approval_list_new_payroll($this->input->post("p_location_id"), $this->input->post("p_company_id"), $this->input->post("p_department_id"),$this->input->post("p_employee_id"), '', $p_date);
			$payslip_count = count($payslip);
		} elseif (in_array('275', $role_resources_ids) && $user_info[0]->super_privileges == 0) {
			$payslip = $this->Payroll_model->get_management_approval_list_new_payroll($this->input->post("p_location_id"), $user_info[0]->company_id,$this->input->post("p_department_id"), $this->input->post("p_employee_id"), '', $p_date);
			$payslip_count = count($payslip);
		} else {
			$payslip = $this->Payroll_model->get_management_approval_list_new_payroll('', '','', $session['user_id'], $p_date);
			$payslip_count = count($payslip);
		}/* ARBHQR0024 end*/

		$system = $this->Xin_model->read_setting_info(1);

		$company = $this->Xin_model->read_company_info($search_company_id);
		if (!empty($search_company_id)) {
			if (!is_null($company)) {
				$company_logo = $company[0]->logo;
				if ($company_logo) {
					$logo = base_url() . '/uploads/company/' . $company_logo;
				} else {
					$general_info = $this->Xin_model->read_general_company_info();
					if ($general_info['default_logo'] != '') {
						$logo = base_url() . '/uploads/company/' . $general_info['default_logo'];
					} else {
						$logo = base_url() . '/skin/logo/sys_logo.jpg';
					}
				}
			} else {
				$logo = base_url() . '/skin/logo/sys_logo.jpg';
			}
			$comapny_name = $company[0]->name;
		} else {
			$general_info = $this->Xin_model->read_general_company_info();
			if ($general_info['default_logo'] != '') {
				$logo = base_url() . '/uploads/company/' . $general_info['default_logo'];
			} else {
				$logo = base_url() . '/skin/logo/sys_logo.jpg';
			}
			$comapny_name = $general_info['company_name'];
		}

		$data = array();
		if (!empty($payslip)) {
			foreach ($payslip as $r) {
				// user full name
				$emp_name = $r->first_name . ' ' . $r->middle_name . ' ' . $r->last_name;
				$full_name = '<a target="_blank" class="text-primary" href="' . site_url() . 'admin/employees/detail/' . $r->user_id . '">' . $emp_name . '</a>';
				//legacy code
				if(!empty($r->arabic_name)){
					$leagacy_code = $r->arabic_name;
				}else{
					$leagacy_code = '--';
				}
				// department
				$department = $this->Department_model->read_department_information($r->department_id);
				if (!is_null($department)) {
					$department_name = $department[0]->department_name;
				} else {
					$department_name = '--';
				}

				// location
				$location = $this->Location_model->read_location_information($r->location_id);
				if (!is_null($location)) {
					$location_name = $location[0]->location_name;
				} else {
					$location_name = '--';
				}

				//Date of joining 
				$doj = new DateTime($r->date_of_joining);
				$date_of_joining = $doj->format('d-M-Y');

				//nationality
				if($r->nationality_id == '17'){
					$nationality ='Local';
				}else{
					$nationality = 'Expact';
				}

				//cpr no
				$cprnumber_result = $this->Employees_model->read_employee_cpr_information($r->user_id);
				if(!empty($cprnumber_result)){
					$cpr_no = $cprnumber_result[0]->cpr_num;
				}else{
					$cpr_no = '--';
				}

				//swift, iban
				$bankdetails = $this->Employees_model->get_bank_details($r->user_id);
				if(!empty($bankdetails)){
					$iban = $bankdetails[0]->iban;
					$swift = '--';
				}else{
					$iban = '--';
					$swift = '--';
				}

				$basic_earning =0 ;
				
				

				
				// get total hours > worked > employee
				$pay_date = $this->input->post('p_month_year');

				//overtime request
				$overtime_count = $this->Overtime_request_model->get_overtime_request_count($r->user_id, $this->input->get('month_year'));
				$re_hrs_old_int1 = 0;
				$re_hrs_old_seconds = 0;
				$re_pcount = 0;
				foreach ($overtime_count as $overtime_hr) {
					// total work
					$request_clock_in = new DateTime($overtime_hr->request_clock_in);
					$request_clock_out = new DateTime($overtime_hr->request_clock_out);
					$re_interval_late = $request_clock_in->diff($request_clock_out);
					$re_hours_r = $re_interval_late->format('%h');
					$re_minutes_r = $re_interval_late->format('%i');
					$re_total_time = $re_hours_r . ":" . $re_minutes_r . ":" . '00';

					$re_str_time = $re_total_time;

					$re_str_time = preg_replace("/^([\d]{1,2})\:([\d]{2})$/", "00:$1:$2", $re_str_time);

					sscanf($re_str_time, "%d:%d:%d", $hours, $minutes, $seconds);

					$re_hrs_old_seconds = $hours * 3600 + $minutes * 60 + $seconds;

					$re_hrs_old_int1 += $re_hrs_old_seconds;

					$re_pcount = gmdate("H", $re_hrs_old_int1);
				}
				$result = $this->Payroll_model->total_hours_worked($r->user_id, $pay_date);
				$hrs_old_int1 = 0;
				$pcount = 0;
				$Trest = 0;
				$total_time_rs = 0;
				$hrs_old_int_res1 = 0;
				foreach ($result->result() as $hour_work) {
					// total work
					$clock_in = new DateTime($hour_work->clock_in);
					$clock_out = new DateTime($hour_work->clock_out);
					$interval_late = $clock_in->diff($clock_out);
					$hours_r = $interval_late->format('%h');
					$minutes_r = $interval_late->format('%i');
					$total_time = $hours_r . ":" . $minutes_r . ":" . '00';

					$str_time = $total_time;

					$str_time = preg_replace("/^([\d]{1,2})\:([\d]{2})$/", "00:$1:$2", $str_time);

					sscanf($str_time, "%d:%d:%d", $hours, $minutes, $seconds);

					$hrs_old_seconds = $hours * 3600 + $minutes * 60 + $seconds;

					$hrs_old_int1 += $hrs_old_seconds;

					$pcount = gmdate("H", $hrs_old_int1);
				}
				$pcount = $pcount + $re_pcount;
				// get company
				$company = $this->Xin_model->read_company_info($r->company_id);
				if (!is_null($company)) {
					$comp_name = $company[0]->name;
				} else {
					$comp_name = '--';
				}

				// 1: salary type
				if ($r->wages_type == 1) {
					$wages_type = $this->lang->line('xin_payroll_basic_salary');
					if ($system[0]->is_half_monthly == 1) {
						$basic_salary = $r->basic_salary / 2;
					} else {
						$basic_salary = $r->basic_salary;
					}
					$p_class = 'emo_monthly_pay';
					$view_p_class = 'payroll_template_modal';
				} else if ($r->wages_type == 2) {
					$wages_type = $this->lang->line('xin_employee_daily_wages');
					if ($pcount > 0) {
						$basic_salary = $pcount * $r->basic_salary;
					} else {
						$basic_salary = $pcount;
					}
					$p_class = 'emo_hourly_pay';
					$view_p_class = 'hourlywages_template_modal';
				} else {
					$wages_type = $this->lang->line('xin_payroll_basic_salary');
					if ($system[0]->is_half_monthly == 1) {
						$basic_salary = $r->basic_salary / 2;
					} else {
						$basic_salary = $r->basic_salary;
					}
					$p_class = 'emo_monthly_pay';
					$view_p_class = 'payroll_template_modal';
				}
				// all allowances, all loan/deductions
				$all_allo_and_dedct = $this->Xin_model->payroll_all_dedctions_and_additions($r->user_id, $p_date, $basic_salary);

				$allowance_amount_p = $all_allo_and_dedct[0];
				$loan_de_amount = $all_allo_and_dedct[1];
				$commissions_amount = $all_allo_and_dedct[2];
				$other_payments_amount = $all_allo_and_dedct[3];
				$statutory_deductions_amount = $all_allo_and_dedct[4];
				$saudi_gosi = $all_allo_and_dedct[6];
				$advance_amount = $all_allo_and_dedct[7];
				$new_gosi_amt = $all_allo_and_dedct[8];
				$incentives = $all_allo_and_dedct[9];
				$other_deductions_amount = $all_allo_and_dedct[12];
				$other_allowance = $all_allo_and_dedct[11];
				$all_other_payment = $other_payments_amount;
				$estatutory_deductions = $statutory_deductions_amount;
				//special allowance ,normal allowance , house allowance 
				$special_allowance = $normal_allowance = $house_allowance = 0;
				$new_allowance_datas = $this->Xin_model->new_allowance_datas($r->user_id, $p_date, $basic_salary);
				
				$special_allowance = $new_allowance_datas[0];
				$normal_allowance = $new_allowance_datas[1];
				$house_allowance = $new_allowance_datas[2];
				//increment amount 
				$increment_data = $this->Xin_model->increment_amount($r->user_id, $p_date);
				

				$salary_calc = $this->Xin_model->payroll_GrossSalary($r->user_id, $p_date, $basic_salary, $all_allo_and_dedct);
				$total_net_salary = $salary_calc[0];
				// make payment
				//   var_dump($system[0]->is_half_monthly);die();
				$status_pay_value = 0;
				if ($system[0]->is_half_monthly == 1) {
					$payment_check = $this->Payroll_model->read_make_payment_payslip_half_month_check($r->user_id, $p_date);
					$payment_last = $this->Payroll_model->read_make_payment_payslip_half_month_check_last($r->user_id, $p_date);
					if ($payment_check->num_rows() > 1) {
						//foreach($payment_last as $payment_half_last){
						$make_payment = $this->Payroll_model->read_make_payment_payslip($r->user_id, $p_date);
						$view_url = site_url() . 'admin/payroll/payslip/id/' . $make_payment[0]->payslip_key;

						$status_pay = '<span class="label label-success">' . $this->lang->line('xin_payroll_paid') . '</span>';
						$status_pay_value = 1;
						//$mpay = '<span data-toggle="tooltip" data-placement="top" title="'.$this->lang->line('xin_payroll_make_payment').'"><button type="button" class="btn icon-btn btn-sm btn-outline-secondary waves-effect waves-light" data-toggle="modal" data-target=".'.$p_class.'" data-employee_id="'. $r->user_id . '" data-payment_date="'. $p_date . '" data-company_id="'.$this->input->get("company_id").'"><span class="far fa-money-bill-alt"></span></button></span>';
						$mpay = '<span data-toggle="tooltip" data-placement="top" title="' . $this->lang->line('xin_payroll_view_payslip') . '"><a href="' . $view_url . '"><button type="button" class="btn icon-btn btn-sm btn-outline-secondary waves-effect waves-light"><span class="far fa-arrow-alt-circle-right"></span></button></a></span><span data-toggle="tooltip" data-placement="top" title="' . $this->lang->line('xin_download') . '"><a href="' . site_url() . 'admin/payroll/pdf_create/p/' . $make_payment[0]->payslip_key . '"><button type="button" class="btn icon-btn btn-sm btn-outline-secondary waves-effect waves-light"><span class="oi oi-cloud-download"></span></button></a></span>';
						if (in_array('273', $role_resources_ids)) {
							$delete = '<span data-toggle="tooltip" data-state="danger" data-placement="top" title="' . $this->lang->line('xin_delete') . '"><button type="button" class="btn icon-btn btn-sm btn-outline-danger waves-effect waves-light delete" data-toggle="modal" data-target=".delete-modal" data-record-id="' . $make_payment[0]->payslip_id . '"><span class="fas fa-trash-restore"></span></button></span>';
						} else {
							$delete = '';
						}
						$delete = $delete . '<code>' . $this->lang->line('xin_title_first_half') . '</code><br>' . '<span data-toggle="tooltip" data-placement="top" title="' . $this->lang->line('xin_payroll_view_payslip') . '"><a href="' . site_url() . 'admin/payroll/payslip/id/' . $payment_last[0]->payslip_key . '"><button type="button" class="btn icon-btn btn-sm btn-outline-secondary waves-effect waves-light"><span class="far fa-arrow-alt-circle-right"></span></button></a></span><span data-toggle="tooltip" data-placement="top" title="' . $this->lang->line('xin_download') . '"><a href="' . site_url() . 'admin/payroll/pdf_create/p/' . $payment_last[0]->payslip_key . '"><button type="button" class="btn icon-btn btn-sm btn-outline-secondary waves-effect waves-light"><span class="oi oi-cloud-download"></span></button></a></span><span data-toggle="tooltip" data-state="danger" data-placement="top" title="' . $this->lang->line('xin_delete') . '"><button type="button" class="btn icon-btn btn-sm btn-outline-danger waves-effect waves-light delete" data-toggle="modal" data-target=".delete-modal" data-record-id="' . $payment_last[0]->payslip_id . '"><span class="fas fa-trash-restore"></span></button></span><code>' . $this->lang->line('xin_title_second_half') . '</code>';
						//}
						//detail link
						$detail = '';
						$total_net_salary = $make_payment[0]->net_salary;
					} else if ($payment_check->num_rows() > 0) {
						$make_payment = $this->Payroll_model->read_make_payment_payslip($r->user_id, $p_date);
						$view_url = site_url() . 'admin/payroll/payslip/id/' . $make_payment[0]->payslip_key;

						$status_pay = '<span class="label label-success">' . $this->lang->line('xin_payroll_paid') . '</span>';
						$status_pay_value = 1;
						$mpay = '<span data-toggle="tooltip" data-state="primary" data-placement="top" title="' . $this->lang->line('xin_payroll_make_payment') . '"><button type="button" class="btn icon-btn btn-sm btn-outline-secondary waves-effect waves-light" data-toggle="modal" data-target=".' . $p_class . '" data-employee_id="' . $r->user_id . '" data-payment_date="' . $p_date . '" data-company_id="' . $this->input->get("company_id") . '"><span class="far fa-money-bill-alt"></span></button></span>';
						$mpay .= '<span data-toggle="tooltip" data-state="primary" data-placement="top" title="' . $this->lang->line('xin_payroll_view_payslip') . '"><a href="' . $view_url . '"><button type="button" class="btn icon-btn btn-sm btn-outline-secondary waves-effect waves-light"><span class="far fa-arrow-alt-circle-right"></span></button></a></span><span data-toggle="tooltip" data-state="primary" data-placement="top" title="' . $this->lang->line('xin_download') . '"><a href="' . site_url() . 'admin/payroll/pdf_create/p/' . $make_payment[0]->payslip_key . '"><button type="button" class="btn icon-btn btn-sm btn-outline-secondary waves-effect waves-light"><span class="oi oi-cloud-download"></span></button></a></span>';
						if (in_array('273', $role_resources_ids)) {
							$delete = '<span data-toggle="tooltip" data-state="danger" data-placement="top" title="' . $this->lang->line('xin_delete') . '"><button type="button" class="btn icon-btn btn-sm btn-outline-danger waves-effect waves-light delete" data-toggle="modal" data-target=".delete-modal" data-record-id="' . $make_payment[0]->payslip_id . '"><span class="fas fa-trash-restore"></span></button></span>';
						} else {
							$delete = '';
						}
						$delete = $delete . '<code>' . $this->lang->line('xin_title_first_half') . '</code>';
						$detail = '';
						$total_net_salary = $make_payment[0]->net_salary;
					} else {
						$status_pay = '<span class="label label-danger">' . $this->lang->line('xin_payroll_unpaid') . '</span>';
						$mpay = '<span data-toggle="tooltip" data-state="primary" data-placement="top" title="' . $this->lang->line('xin_payroll_make_payment') . '"><button type="button" class="btn icon-btn btn-sm btn-outline-secondary waves-effect waves-light" data-toggle="modal" data-target=".' . $p_class . '" data-employee_id="' . $r->user_id . '" data-payment_date="' . $p_date . '" data-company_id="' . $this->input->get("company_id") . '"><span class="far fa-money-bill-alt"></span></button></span>';
						$delete = '';
						//detail link
						$detail = '<span data-toggle="tooltip" data-placement="top" title="' . $this->lang->line('xin_view') . '"><button type="button" class="btn icon-btn btn-xs btn-outline-secondary waves-effect waves-light" data-toggle="modal" data-target=".' . $view_p_class . '" data-employee_id="' . $r->user_id . '" data-payment_date="' . $pay_date . '"><span class="fa fa-eye"></span></button></span>';

						$total_net_salary = $total_net_salary;
					}
					//detail link
					//$detail = '';
				} else {
					$payment_check = $this->Payroll_model->read_make_payment_payslip_check($r->user_id, $p_date);
					if ($payment_check->num_rows() > 0) {
						$make_payment = $this->Payroll_model->read_make_payment_payslip($r->user_id, $p_date);
						$view_url = site_url() . 'admin/payroll/payslip/id/' . $make_payment[0]->payslip_key;

						$status_pay = '<span class="label label-success">' . $this->lang->line('xin_payroll_paid') . '</span>';
						$status_pay_value = 1;
						$mpay = '<span data-toggle="tooltip" data-state="primary" data-placement="top" title="' . $this->lang->line('xin_payroll_view_payslip') . '"><a href="' . $view_url . '"><button type="button" class="btn icon-btn btn-sm btn-outline-secondary waves-effect waves-light"><span class="far fa-arrow-alt-circle-right"></span></button></a></span><span data-toggle="tooltip" data-state="primary" data-placement="top" title="' . $this->lang->line('xin_download') . '"><a href="' . site_url() . 'admin/payroll/pdf_create/p/' . $make_payment[0]->payslip_key . '"><button type="button" class="btn icon-btn btn-sm btn-outline-secondary waves-effect waves-light"><span class="oi oi-cloud-download"></span></button></a></span>';
						if (in_array('273', $role_resources_ids)) {
							$delete = '<span data-toggle="tooltip" data-state="danger" data-placement="top" title="' . $this->lang->line('xin_delete') . '"><button type="button" class="btn icon-btn btn-sm btn-outline-danger waves-effect waves-light delete" data-toggle="modal" data-target=".delete-modal" data-record-id="' . $make_payment[0]->payslip_id . '"><span class="fas fa-trash-restore"></span></button></span>';
						} else {
							$delete = '';
						}
						//$total_net_salary = $make_payment[0]->net_salary;
					} else {
						$status_pay = '<span class="label label-danger">' . $this->lang->line('xin_payroll_unpaid') . '</span>';
						$mpay = '<span data-toggle="tooltip" data-state="primary" data-placement="top" title="' . $this->lang->line('xin_payroll_make_payment') . '"><button type="button" class="btn icon-btn btn-sm btn-outline-secondary waves-effect waves-light" data-toggle="modal" data-target=".' . $p_class . '" data-employee_id="' . $r->user_id . '" data-payment_date="' . $p_date . '" data-company_id="' . $this->input->get("company_id") . '"><span class="far fa-money-bill-alt"></span></button></span>';
						$delete = '';
						$total_net_salary = $total_net_salary;
					}
					//detail link
					if (in_array('276', $role_resources_ids)) {
						$detail = '<span data-toggle="tooltip" data-state="primary" data-placement="top" title="' . $this->lang->line('xin_view') . '"><button type="button" class="btn icon-btn btn-sm btn-outline-secondary waves-effect waves-light" data-toggle="modal" data-target="#' . $view_p_class . '" data-employee_id="' . $r->user_id . '" data-payment_date="' . $p_date . '"" data-net="' . $total_net_salary . '"><span class="fa fa-eye"></span></button></span>';
					} else {
						$detail  = '';
					}
				}

				$net_salary = number_format((float)$total_net_salary, 2, '.', '');
				$basic_salary = number_format((float)$basic_salary, 2, '.', '');
				//}

				if ($basic_salary == 0 || $basic_salary == '') {
					$fmpay = '';
				} else {
					$fmpay = $mpay;
				}

				//new leave calc
				$month_year = $pay_date;
				$start_date_y = date('m', strtotime($month_year));
				$start_date_y_m = date('Y-m', strtotime($month_year));
				$emp_leaves = $this->Employees_model->emp_leave_fetch($r->user_id, $start_date_y);

				$user_id = $r->user_id;
				$date = strtotime(date("Y-m-d"));
				if (!isset($month_year)) {
					$day = date('d', $date);
					$month = date('m', $date);
					$year = date('Y', $date);
					$month_year = date('Y-m');
				} else {
					$imonth_year = explode('-', $month_year);
					$day = date('d', $date);
					$month = date($imonth_year[1], $date);
					$year = date($imonth_year[0], $date);
					$month_year = $month_year;
				}
				$daysInMonth = date('t', mktime(0, 0, 0, $month, 1, $year));
				$k = 1;
				$extra_amount = $tot_days_lop_normal = 0;
				$tot_days = 0;

				//Using attendance
				$arry_years = array();
				$st = '';
				$p_count_val = 0;
				// $start_date_co = $year . '-' . $month . '-' . '01';
				// $end_date_co = $year . '-' . $month . '-' . $daysInMonth;

				$method = $this->Xin_model->payroll_find_method();
				if ($method == 'cuttoff') {
					$payroll_date = $year . '-' . $month;
					$user = $this->Xin_model->read_user_info($r->user_id);
					$company_id = $user[0]->company_id;
					$location_id = $user[0]->location_id;
					if (strtotime(date('Y-m', strtotime($user[0]->date_of_joining))) == strtotime($month_year)) {
						$start_date_co = $user[0]->date_of_joining;
					} else {
						$start_date_co = $this->Xin_model->payroll_startdate($payroll_date, $company_id, $location_id);
					}
					$end_date_co = $this->Xin_model->payroll_enddate($payroll_date, $company_id, $location_id);
				} else {
					$start_date_co = $year . '-' . $month . '-' . '01';
					$end_date_co = $year . '-' . $month . '-' . $daysInMonth;
				}


				$present_arr = array();
				//$st = $this->db->last_query();
				$leave_arr = $half_leave_arr = array();
				$total_leaves = count($leave_arr) + count($half_leave_arr);
				$p_count_val += abs($total_leaves - $tot_days);
				$present_count = $public_holiday_count = $holiday_count = $leave__half_day_count = 0;
				$absent_count = 0;
				$leave_count = 0;
				$user = $this->Xin_model->read_user_info($r->user_id);
				if (strtotime($month_year) == strtotime(date('Y-m'))) {
					$month_year_att = $month_year . '-01';
					if ($user[0]->date_of_leaving) {
						if (strtotime($month_year) == strtotime(date("Y-m", strtotime($user[0]->date_of_leaving)))) {
							if (strtotime(date("Y-m-d", strtotime($user[0]->date_of_leaving))) > strtotime(date("Y-m-d"))) {
								$difference_days = 0;
							} else {
								$get_attendance_last_entered = $this->Timesheet_model->attendance_this_month_value_last($user_id, $start_date_y_m);
								if ($get_attendance_last_entered) {
									$month_year_att_end = $get_attendance_last_entered[0]->attendance_date;
									//$month_year_att_end = $month_year.'-'.date('d');
									$startDate = new DateTime($month_year_att);
									$endDate = new DateTime($month_year_att_end);
									$difference = $endDate->diff($startDate);
									$difference_days = $difference->format("%a") + 1;
								} else {
									$difference_days = 0;
								}
							}
						} else {
							$difference_days = 0;
						}
					} else {
						$get_attendance_last_entered = $this->Timesheet_model->attendance_this_month_value_last($user_id, $start_date_y_m);
						if ($get_attendance_last_entered) {
							$month_year_att_end = $get_attendance_last_entered[0]->attendance_date;
							//$month_year_att_end = $month_year.'-'.date('d');
							$startDate = new DateTime($month_year_att);
							$endDate = new DateTime($month_year_att_end);
							$difference = $endDate->diff($startDate);
							$difference_days = $difference->format("%a") + 1;
						} else {
							$difference_days = 0;
						}
					}
				} else if (strtotime($month_year) <= strtotime(date('Y-m'))) {
					$month_year_att = $month_year . '-01';
					$get_attendance_last_entered = $this->Timesheet_model->attendance_this_month_value_last($user_id, $start_date_y_m);
					if ($get_attendance_last_entered) {
						$month_year_att_end = $get_attendance_last_entered[0]->attendance_date;
						//$month_year_att_end = $month_year.'-'.date('d');
						$startDate = new DateTime($month_year_att);
						$endDate = new DateTime($month_year_att_end);
						$difference = $endDate->diff($startDate);
						$difference_days = $difference->format("%a") + 1;
					} else {
						$difference_days = 0;
					}
				} else if (strtotime($month_year) >= strtotime(date('Y-m'))) {
					$difference_days = 0;
				}
				if ($difference_days >= 25) {
					$difference_days = $daysInMonth;
				}


				$office_shift = $this->Timesheet_model->read_office_shift_information($r->office_shift_id);

				//Number of days calc
				$total_k = 1;
				$k = 1;
				$extra_amount = 0;
				$tot_days = 0;
				$emp_leave_in_year_count = $extra_in_month = $total_final = $final_amount = 0;
				$month_year_end = date("Y-m-d", strtotime("$month_year-01"));
				$month_last_date = date("Y-m-d", strtotime("$month_year-$daysInMonth"));
				$totol_deduction = $count_l = $hlfcount = $tot_days_lop = 0;

				//end number of calc
				$attendence_calc = $this->Xin_model->AttendnceCount_calc_common($r->office_shift_id, $start_date_co, $end_date_co, $r->company_id, $r->user_id, $difference_days, $year, $month);
				if ($attendence_calc) {
					$tot_days_lop_normal = $attendence_calc[0];
					$totl_num_days_pres = $attendence_calc[1];
				} else {
					$tot_days_lop_normal = 0;
					$totl_num_days_pres = 0;
				}
				$tot_days_lop = $tot_days_lop_normal;
				$bs_pay = $r->basic_salary;
				//days - present days 
				$present_days = $totl_num_days_pres;
				$basic_earning = ($bs_pay / 30) * $totl_num_days_pres; 
				
				

				
				/* Lop Calc Common */
				$lop_calc = $this->Xin_model->LopCalcCommon($net_salary, $daysInMonth, $tot_days_lop);
				$leave_cut_salary = $lop_calc[0];
				/* End Lop calc Common */

				/* Net salary calculations */
				$net_salary_calc = $this->Xin_model->NetsalaryCalcCommon($total_net_salary, $daysInMonth, $totl_num_days_pres);
				$net_salary = $net_salary_calc[0];


				/* Final Net salary */
				
				$final_net_salary = $this->Xin_model->FinalNetSalaryCalcCommon($net_salary, $leave_cut_salary, $all_allo_and_dedct);
				$net_salary = $final_net_salary[0];
				
				$start_date_y_m = date('Y-m', strtotime($month_year));

				$sal_ded = $totl_amt = 0;
				//leave encashment calc
				$start_date_y = date('m', strtotime($month_year));
				$start_date_y_m = date('Y-m', strtotime($month_year));
				$emp_leave_enchas = $this->Employees_model->emp_leave_encashment_fetch($user_id, $start_date_y);
				
				$totl_amt_leva = $annual_amt_pay = 0;
				if ($emp_leave_enchas) {
					foreach ($emp_leave_enchas as $emp_leave_ench) {
						$totl_amt_leva += $emp_leave_ench->amount;
					}
				}

				$encashment = $this->Payroll_model->GetEncashmentamount($user_id, $start_date_y_m);
				foreach ($encashment as $emp_leave) {
					if ($emp_leave->given_methode == 'Connect_To_Payroll') {
						$annual_amt_pay += $emp_leave->amount;
					}
				}

				$total_compensateamount_allowance = $total_compensateamount_deduction = 0;
				$compensateamount = $this->Payroll_model->GetCompensateamount($user_id, $start_date_y_m);
				if (!empty($compensateamount)) {
					foreach ($compensateamount as $val) {
						if ($val->amount_type == 1) {
							$total_compensateamount_allowance += $val->total_amount;
						} else {
							$total_compensateamount_deduction += $val->total_amount;
						}
					}
				}

				$net_salary = $net_salary + $totl_amt + $totl_amt_leva + $annual_amt_pay + $total_compensateamount_allowance - $total_compensateamount_deduction;
				
				$basic_salary = $this->Xin_model->company_currency_sign($basic_salary, $r->company_id);
				if ($net_salary < 0) {
					$net_salary = '0.00';
				}
				$iemp_name = $emp_name;

				//action link
				$act = $detail . $fmpay . $delete;
				if ($r->wages_type == 1) {
					if ($system[0]->is_half_monthly == 1) {
						$emp_payroll_wage = $wages_type;
					} else {
						$emp_payroll_wage = $wages_type;
					}
				} else {
					$emp_payroll_wage = $wages_type;
				}
				if (in_array('83', $role_resources_ids)) {
					$emp_id = '<a target="_blank" href="' . site_url('admin/employees/detail/') . $r->user_id . '" class="text-muted" data-state="primary" data-placement="top" data-toggle="tooltip" title="Employee Detail">' . $r->employee_id . ' <i class="fas fa-arrow-circle-right"></i></a>';
				} else {
					$emp_id = $r->employee_id;
				}

				$paymonth = date('m', strtotime($pay_date));
				$payyear = date('Y', strtotime($pay_date));
				$empdata = $this->Employees_model->get_employee_details($user_id);
				$basepay = $empdata[0]->basic_salary;
				$no_of_days = date('t', strtotime($pay_date));

				$late_amount = $payment_balance = 0;
				$latededuction = $this->Xin_model->GetLateDeductionCalcCommon($user_id, $paymonth, $payyear, $no_of_days);
				$late_amount = $latededuction[0];
				$userinfo = $this->Employees_model->get_employee_details($user_id);
				$companyinfo = $this->Employees_model->get_company_details($userinfo[0]->company_id);
				$late_status = $companyinfo[0]->late_deduction;
				if ($late_status == 'yes') {
					$late_amount = $late_amount;
				}
				// Get Overtime Amount
				//$overtime = $this->Payroll_model->GetOvertimeCalcCommon($user_id, $paymonth, $payyear);
				// $overtime = $this->Xin_model->GetOvertimeCalcCommon($user_id, $paymonth, $payyear);
				// $overtime_amount = $overtime[0];
				$overtime_amount = $this->Xin_model->getEmployeeMonthlyOTamount($user_id, $pay_date);

				$payment_balance = $this->Employees_model->get_employee_payment_balance($user_id,$p_date);

				$overtime_incentive_amount = $this->Xin_model->getEmployeeMonthlyOT_incentive_amount($user_id, $pay_date);
				// End Overtime Amount

				// Air ticket Encashment Start
				$encashment_check = $this->Employees_model->employee_encashment_check($user_id, $month_year);
				if (!empty($encashment_check)) {
					$encashstatus = $encashment_check[0]->status;
					if (!is_null($encashstatus)) {
						$encashrate = $this->Employees_model->get_employee_encashrate_payroll($user_id, $month_year);
						$encash_amt = $encashrate[0]->amount;
					} else {
						$encash_amt = 0;
					}
				} else {
					$encash_amt = 0;
				}

				// Air ticket Encashment End
				//ot hours normal
				$ot_hr = 0;
				$otDetails = $this->Employees_model->get_employee_ot_data($user_id,$start_date_co,$end_date_co); 
				
				foreach ($otDetails as $overtimedetails) {
					
					if($overtimedetails->approved_ot_amount !== 0){
						$ot_hr+= $overtimedetails->approved_ot_hours;
					}
				}
				//ot hours incentive
				$inc_ot_hr = 0;
				$ot_incentive_Details = $this->Employees_model->get_employee__inc_ot_data($user_id,$start_date_co,$end_date_co); 
				
				foreach ($ot_incentive_Details as $overtimedetails) {
					
					if($overtimedetails->approved_ot__incentive_amount !== 0){
						$inc_ot_hr+= $overtimedetails->approved_ot_incentive_hours;
					}
				}
				//$nettotal = $net_salary;
				$overtimestatus = $this->Employees_model->get_employee_overtimestatus($user_id);
				$otstatus = $overtimestatus[0]->ot_eligible;
				if ($otstatus == "yes") {
					if ($net_salary == 0) {
						$nettotal = 0.000;
					} else {
						$nettotal = $net_salary + $overtime_amount + $overtime_incentive_amount - $late_amount;
					}
				} else {
					if ($net_salary == 0) {
						$nettotal = 0.000;
					} else {
						$nettotal = $net_salary - $late_amount;
					}
				}
				$nettotal +=  $payment_balance ; 

				$deduction_amt_net_total_zero = 0;
				$deduction_overtime_amount_total_zero = 0;
				$deduction_late_amount_total_zero = 0;
				if ($nettotal <= 0) {
					$deduction_amt_net_total_zero = $leave_cut_salary;
					$deduction_late_amount_total_zero = $late_amount;
					if ($otstatus == "yes") {
						$deduction_overtime_amount_total_zero = $overtime_amount + $overtime_incentive_amount;
					}
				}
				$expense_total_claim = 0;
				/* Expense Claim Start */
				//if ($nettotal > 0) {
				$expense_total_claim = $this->Xin_model->FindExpenseClaimTotal($user_id, $p_date);
				/* if annual leave connect to payroll start */
				$annual_leave_amnt = 0 ;
				$annual_leave_amnt = $this->Payroll_model->annual_leave_amount_payroll($user_id,$month_year) ;
				// $annualLeave = $this->Payroll_model->GetAnnualamount($r->user_id, $p_date);
				
				// if(!empty($annualLeave)){
				// 	foreach ($annualLeave as $emp_leave) {
				// 		if ($emp_leave->type_pay == 'Payroll') {
				// 			$annual_leave_amnt += $emp_leave->L_S_P;
				// 		}
				// 	}
				// }else{
				// 	$annual_leave_amnt = 0 ;
				// }
				$nettotal = $nettotal+  $encash_amt; 
				/* if annual leave connect to payroll 
				$nettotal = $nettotal + $expense_total_claim + $encash_amt + $deduction_overtime_amount_total_zero - ($deduction_amt_net_total_zero + $deduction_late_amount_total_zero);
				//}
				if ($nettotal < 0) {
					$nettotal = 0;
				}
				/* Expense Claim End */
				$nettotal = $nettotal + $annual_leave_amnt + $expense_total_claim;
				$nettotal_salary = $nettotal;
				$nettotal = $this->Xin_model->company_currency_sign($nettotal, $r->company_id);


				$location_id = $r->location_id;
				$monthyear = $payyear . "-" . $paymonth;
				$get_management_status = $this->Employees_model->get_management_employee_status($r->user_id, $location_id, $p_date);


				$days_in_month = $days = date("t");

				$payment_check = $this->Payroll_model->read_make_payment_payslip_check($user_id, $p_date);
				if ($payment_check->num_rows() > 0) {

					$make_payment = $this->Payroll_model->read_make_payment_payslip($r->user_id, $p_date);
					$nettotal = $this->Xin_model->company_currency_sign($make_payment[0]->net_salary, $r->company_id);
					$nettotal_salary = $make_payment[0]->net_salary;
				}
				$management_status_val = 1;
				if (empty($get_management_status)) {
					$management_status_val = 1;
					$disable = '';
					if ($nettotal_salary <= 0) {
						$disable = 'disabled';
					} else {
						$disable = 'checked';
					}
					$management_status = '<input type="checkbox" class="editor-active check_management_status" name="status_val" value="' . $r->user_id . '" ' . $disable . '>';
				} else {
					$management_status_val = 0;
					$management_status = '<input type="checkbox" class="editor-active check_management_status" name="status_val" value="' . $r->user_id . '" checked>';
				}
				$current_month = date('Y-m');
				$search_month = date('Y-m', strtotime($month_year));
				if (strtotime($search_month) > strtotime($current_month)) {
					$nettotal = $this->Xin_model->company_currency_sign(0, $r->company_id);
				}
				$monthly_allowance = 0;
				$sal_ded = $other_deductions_amount;
				$monthly_allowance = $other_allowance;
				
				//	$get_attendance_last_entered = $this->Timesheet_model->attendance_this_month_value_last($user_id,$start_date_y_m);
				if ($status_pay_value < 1 && $management_status_val > 0) {
					$data[] = array(
						'emp_id' => $emp_id,
						'emp_name' => $iemp_name,
						'emp_payroll_wage' => $emp_payroll_wage,
						'basic_salary' => $basic_salary,
						'nettotal' => $nettotal,
						'status_pay' => $status_pay,
						'leave_cut_salary' => $leave_cut_salary,
						'allowance_amount_p' => ($allowance_amount_p - ($normal_allowance + $special_allowance + $house_allowance)),
						'commissions_amount' => $commissions_amount,
						'loan_de_amount' => $loan_de_amount,
						'overtime_amount' => $overtime_amount,
						'overtime_incentive_amount' => $overtime_incentive_amount,
						'estatutory_deductions' => $estatutory_deductions,
						'all_other_payment' => $all_other_payment,
						'new_gosi_amt' => $new_gosi_amt,
						'saudi_gosi' => $saudi_gosi,
						'advance_amount' => $advance_amount,
						'totl_amt_leva' => $totl_amt_leva,
						'annual_amt_pay' => $annual_amt_pay,
						'late_amount' => $late_amount,
						'encash_amt' => $encash_amt,
						'expense_total_claim' => $expense_total_claim,
						'nettotal' => $nettotal,
						'incentives' => $incentives,
						'annual_leave_amnt' => number_format($annual_leave_amnt,3),
						'payment_balance' => $payment_balance,
						'legacy_code' =>$leagacy_code,
						'department_name'=> $department_name,
						'location_name' => $location_name,
						'date_of_joining' => $date_of_joining,
						'nationality' => $nationality,
						'cpr_no' =>$cpr_no,
						'swift' => $swift,
						'iban' => $iban,
						'present_days' => $present_days,
						'special_allowance'=>$special_allowance,
						'normal_allowance'=>$normal_allowance,
						'house_allowance'=>$house_allowance,
						'ot_hr' => $ot_hr,
						'inc_ot_hr' => $inc_ot_hr,
						'increment' =>$increment_data[0],
						'sal_ded' => $sal_ded,
						'basic_earning' => number_format($basic_earning, 3),
						'bs_pay' => $bs_pay,
						'monthly_allowance' => $monthly_allowance,

						

					);
				}
			}
		}


		$this->load->library('M_pdf');
		$mpdf = $this->m_pdf->load([

			'mode' => 'utf-8',

			'format' => 'A4',

			'orientation' => 'l'

		]);
		$res['lists'] = $data;
		$res['logo'] = $logo;
		$res['comapny_name'] = $comapny_name;
		
		$res['month_year'] = date('Y-F', strtotime($p_date . '-01'));
		if ($this->input->post("download_format") == 'pdf_format') {
			$html = $this->load->view("admin/payroll/management_approval_pdf_new", $res,true);
			$mpdf->WriteHTML($html);
			$mpdf->Output('Management Approval - ' . $res['month_year'] . '.pdf', 'D'); //

		} else {
			$htmls = $this->load->view("admin/payroll/management_excel_new", $res,true);
			
			$file = 'Management Approval - ' . $res['month_year'] . '.xls';

			header("Content-type: application/vnd.ms-excel");
			header("Content-Disposition: attachment; filename=$file");
			echo $htmls;
		}
	}
	// background excel generation
	public function get_total_pages() {
   
		$totalPages = $this->Payroll_model->get_total_pages_count();
	
		echo json_encode(['totalPages' => $totalPages]);
	}
	public function prepare_data_for_listing()
	{	
		$location_id = $this->input->get('location_id');
		$company_id = $this->input->get('company_id');
		$department_id = $this->input->get('department_id');
		$employee_id = $this->input->get('employee_id');
		$p_date = $this->input->get('p_date');
		$page = $this->input->get('page');
		
		if($page == ''){
			$page = 1;
		}
		$month_year = $p_date;
		$start_date_y_m = date('Y-m', strtotime($month_year));
		$session = $this->session->userdata('username');
		
		$user_info = $this->Xin_model->read_user_info($session['user_id']);/* ARBHQR0024 start*/
		if ($user_info[0]->user_role_id == 1  || in_array('1500', $role_resources_ids)) {
			$payslip = $this->Payroll_model->get_management_approval_list_new_payroll_excel_pdf($this->input->post("location_id"), $this->input->post("company_id"),$this->input->post("department_id"), $this->input->post("employee_id"), $p_date,$page);
			$payslip_count = count($payslip);
		} elseif ($user_info[0]->super_privileges == 1 && $user_info[0]->super_privilege_criteria == 0) {
			$payslip = $this->Payroll_model->get_management_approval_list_new_payroll($this->input->post("p_location_id"), $this->input->post("p_company_id"),$this->input->post("p_department_id"), $this->input->post("p_employee_id"), '', $p_date);
			$payslip_count = count($payslip);
		} elseif (in_array('275', $role_resources_ids) && $user_info[0]->super_privileges == 1 && $user_info[0]->super_privilege_criteria == 1) {
			$payslip = $this->Payroll_model->get_management_approval_list_new_payroll($this->input->post("p_location_id"), $this->input->post("p_company_id"), $this->input->post("p_department_id"),$this->input->post("p_employee_id"), '', $p_date);
			$payslip_count = count($payslip);
		} elseif (in_array('275', $role_resources_ids) && $user_info[0]->super_privileges == 0) {
			$payslip = $this->Payroll_model->get_management_approval_list_new_payroll($this->input->post("p_location_id"), $user_info[0]->company_id,$this->input->post("p_department_id"), $this->input->post("p_employee_id"), '', $p_date);
			$payslip_count = count($payslip);
		} else {
			$payslip = $this->Payroll_model->get_management_approval_list_new_payroll('', '','', $session['user_id'], $p_date);
			$payslip_count = count($payslip);
		}/* ARBHQR0024 end*/
		
		$system = $this->Xin_model->read_setting_info(1);
		$data_newone = [];

		$data = array();
		if (!empty($payslip)) {
			foreach ($payslip as $r) {
				
				$emp_name = $r->first_name . ' ' . $r->middle_name . ' ' . $r->last_name;
				$pay_date = $p_date;

				// get company
				$company = $this->Xin_model->read_company_info($r->company_id);
				if (!is_null($company)) {
					$comp_name = $company[0]->name;
				} else {
					$comp_name = '--';
				}

				// 1: salary type
				if ($r->wages_type == 1) {
					$wages_type = $this->lang->line('xin_payroll_basic_salary');
					if ($system[0]->is_half_monthly == 1) {
						$basic_salary = $r->basic_salary / 2;
					} else {
						$basic_salary = $r->basic_salary;
					}
					$p_class = 'emo_monthly_pay';
					$view_p_class = 'payroll_template_modal';
				} else if ($r->wages_type == 2) {
					$wages_type = $this->lang->line('xin_employee_daily_wages');
					if ($pcount > 0) {
						$basic_salary = $pcount * $r->basic_salary;
					} else {
						$basic_salary = $pcount;
					}
					$p_class = 'emo_hourly_pay';
					$view_p_class = 'hourlywages_template_modal';
				} else {
					$wages_type = $this->lang->line('xin_payroll_basic_salary');
					if ($system[0]->is_half_monthly == 1) {
						$basic_salary = $r->basic_salary / 2;
					} else {
						$basic_salary = $r->basic_salary;
					}
					$p_class = 'emo_monthly_pay';
					$view_p_class = 'payroll_template_modal';
				}
				// all allowances, all loan/deductions
				$all_allo_and_dedct = $this->Xin_model->payroll_all_dedctions_and_additions($r->user_id, $p_date, $basic_salary);

				$allowance_amount_p = $all_allo_and_dedct[0];
				$loan_de_amount = $all_allo_and_dedct[1];
				$commissions_amount = $all_allo_and_dedct[2];
				$other_payments_amount = $all_allo_and_dedct[3];
				$statutory_deductions_amount = $all_allo_and_dedct[4];
				$saudi_gosi = $all_allo_and_dedct[6];
				$advance_amount = $all_allo_and_dedct[7];
				$new_gosi_amt = $all_allo_and_dedct[8];
				$incentives = $all_allo_and_dedct[9];
				$other_deductions_amount = $all_allo_and_dedct[12];
				$other_allowance = $all_allo_and_dedct[11];
				$all_other_payment = $other_payments_amount;
				$estatutory_deductions = $statutory_deductions_amount;
				//special allowance ,normal allowance , house allowance 
				$special_allowance = $normal_allowance = $house_allowance = 0;
				$new_allowance_datas = $this->Xin_model->new_allowance_datas($r->user_id, $p_date, $basic_salary);
				
				$special_allowance = $new_allowance_datas[0];
				$normal_allowance = $new_allowance_datas[1];
				$house_allowance = $new_allowance_datas[2];
				//increment amount 
				$increment_data = $this->Xin_model->increment_amount($r->user_id, $p_date);
				

				$salary_calc = $this->Xin_model->payroll_GrossSalary($r->user_id, $p_date, $basic_salary, $all_allo_and_dedct);
				$total_net_salary = $salary_calc[0];
				
				$status_pay_value = 0;
				$payment_check = $this->Payroll_model->read_make_payment_payslip_check($r->user_id, $p_date);
				if ($payment_check->num_rows() > 0) {
					$status_pay_value = 1;
				}
				$total_net_salary = $total_net_salary;
				$net_salary = number_format((float)$total_net_salary, 2, '.', '');
				$basic_salary = number_format((float)$basic_salary, 2, '.', '');
				
				//new leave calc
				$month_year = $pay_date;
				$start_date_y = date('m', strtotime($month_year));
				$start_date_y_m = date('Y-m', strtotime($month_year));
				$emp_leaves = $this->Employees_model->emp_leave_fetch($r->user_id, $start_date_y);

				$user_id = $r->user_id;
				$date = strtotime(date("Y-m-d"));
				if (!isset($month_year)) {
					$day = date('d', $date);
					$month = date('m', $date);
					$year = date('Y', $date);
					$month_year = date('Y-m');
				} else {
					$imonth_year = explode('-', $month_year);
					$day = date('d', $date);
					$month = date($imonth_year[1], $date);
					$year = date($imonth_year[0], $date);
					$month_year = $month_year;
				}
				$daysInMonth = date('t', mktime(0, 0, 0, $month, 1, $year));
				$k = 1;
				$extra_amount = $tot_days_lop_normal = 0;
				$tot_days = 0;

				//Using attendance
				$arry_years = array();
				$st = '';
				$p_count_val = 0;
				
				$method = $this->Xin_model->payroll_find_method();
				if ($method == 'cuttoff') {
					$payroll_date = $year . '-' . $month;
					$user = $this->Xin_model->read_user_info($r->user_id);
					$company_id = $user[0]->company_id;
					$location_id = $user[0]->location_id;
					if (strtotime(date('Y-m', strtotime($user[0]->date_of_joining))) == strtotime($month_year)) {
						$start_date_co = $user[0]->date_of_joining;
					} else {
						$start_date_co = $this->Xin_model->payroll_startdate($payroll_date, $company_id, $location_id);
					}
					$end_date_co = $this->Xin_model->payroll_enddate($payroll_date, $company_id, $location_id);
				} else {
					$start_date_co = $year . '-' . $month . '-' . '01';
					$end_date_co = $year . '-' . $month . '-' . $daysInMonth;
				}


				$present_arr = array();
				$leave_arr = $half_leave_arr = array();
				$total_leaves = count($leave_arr) + count($half_leave_arr);
				$p_count_val += abs($total_leaves - $tot_days);
				$present_count = $public_holiday_count = $holiday_count = $leave__half_day_count = 0;
				$absent_count = 0;
				$leave_count = 0;
				$user = $this->Xin_model->read_user_info($r->user_id);
				if (strtotime($month_year) == strtotime(date('Y-m'))) {
					$month_year_att = $month_year . '-01';
					if ($user[0]->date_of_leaving) {
						if (strtotime($month_year) == strtotime(date("Y-m", strtotime($user[0]->date_of_leaving)))) {
							if (strtotime(date("Y-m-d", strtotime($user[0]->date_of_leaving))) > strtotime(date("Y-m-d"))) {
								$difference_days = 0;
							} else {
								$get_attendance_last_entered = $this->Timesheet_model->attendance_this_month_value_last($user_id, $start_date_y_m);
								if ($get_attendance_last_entered) {
									$month_year_att_end = $get_attendance_last_entered[0]->attendance_date;
								
									$startDate = new DateTime($month_year_att);
									$endDate = new DateTime($month_year_att_end);
									$difference = $endDate->diff($startDate);
									$difference_days = $difference->format("%a") + 1;
								} else {
									$difference_days = 0;
								}
							}
						} else {
							$difference_days = 0;
						}
					} else {
						$get_attendance_last_entered = $this->Timesheet_model->attendance_this_month_value_last($user_id, $start_date_y_m);
						if ($get_attendance_last_entered) {
							$month_year_att_end = $get_attendance_last_entered[0]->attendance_date;
							
							$startDate = new DateTime($month_year_att);
							$endDate = new DateTime($month_year_att_end);
							$difference = $endDate->diff($startDate);
							$difference_days = $difference->format("%a") + 1;
						} else {
							$difference_days = 0;
						}
					}
				} else if (strtotime($month_year) <= strtotime(date('Y-m'))) {
					$month_year_att = $month_year . '-01';
					$get_attendance_last_entered = $this->Timesheet_model->attendance_this_month_value_last($user_id, $start_date_y_m);
					if ($get_attendance_last_entered) {
						$month_year_att_end = $get_attendance_last_entered[0]->attendance_date;
						
						$startDate = new DateTime($month_year_att);
						$endDate = new DateTime($month_year_att_end);
						$difference = $endDate->diff($startDate);
						$difference_days = $difference->format("%a") + 1;
					} else {
						$difference_days = 0;
					}
				} else if (strtotime($month_year) >= strtotime(date('Y-m'))) {
					$difference_days = 0;
				}
				if ($difference_days >= 25) {
					$difference_days = $daysInMonth;
				}


				$office_shift = $this->Timesheet_model->read_office_shift_information($r->office_shift_id);
				$total_k = 1;
				$k = 1;
				$extra_amount = 0;
				$tot_days = 0;
				$emp_leave_in_year_count = $extra_in_month = $total_final = $final_amount = 0;
				$month_year_end = date("Y-m-d", strtotime("$month_year-01"));
				$month_last_date = date("Y-m-d", strtotime("$month_year-$daysInMonth"));
				$totol_deduction = $count_l = $hlfcount = $tot_days_lop = 0;

				//end number of calc
				$attendence_calc = $this->Xin_model->AttendnceCount_calc_common($r->office_shift_id, $start_date_co, $end_date_co, $r->company_id, $r->user_id, $difference_days, $year, $month);
				if ($attendence_calc) {
					$tot_days_lop_normal = $attendence_calc[0];
					$totl_num_days_pres = $attendence_calc[1];
				} else {
					$tot_days_lop_normal = 0;
					$totl_num_days_pres = 0;
				}
				$tot_days_lop = $tot_days_lop_normal;
				$bs_pay = $r->basic_salary;
				$present_days = $totl_num_days_pres;
				$basic_earning = ($bs_pay / 30) * $totl_num_days_pres; 
				/* Lop Calc Common */
				$lop_calc = $this->Xin_model->LopCalcCommon($net_salary, $daysInMonth, $tot_days_lop);
				$leave_cut_salary = $lop_calc[0];
				/* End Lop calc Common */

				/* Net salary calculations */
				$net_salary_calc = $this->Xin_model->NetsalaryCalcCommon($total_net_salary, $daysInMonth, $totl_num_days_pres);
				$net_salary = $net_salary_calc[0];


				/* Final Net salary */
				
				$final_net_salary = $this->Xin_model->FinalNetSalaryCalcCommon($net_salary, $leave_cut_salary, $all_allo_and_dedct);
				$net_salary = $final_net_salary[0];
				
				$start_date_y_m = date('Y-m', strtotime($month_year));

				$sal_ded = $totl_amt = 0;
				//leave encashment calc
				$start_date_y = date('m', strtotime($month_year));
				$start_date_y_m = date('Y-m', strtotime($month_year));
				$emp_leave_enchas = $this->Employees_model->emp_leave_encashment_fetch($user_id, $start_date_y);
				
				$totl_amt_leva = $annual_amt_pay = 0;
				if ($emp_leave_enchas) {
					foreach ($emp_leave_enchas as $emp_leave_ench) {
						$totl_amt_leva += $emp_leave_ench->amount;
					}
				}

				$encashment = $this->Payroll_model->GetEncashmentamount($user_id, $start_date_y_m);
				if(!empty($encashment)){
					foreach ($encashment as $emp_leave) {
						if ($emp_leave->given_methode == 'Connect_To_Payroll') {
							$annual_amt_pay += $emp_leave->amount;
						}
					}
				}

				$total_compensateamount_allowance = $total_compensateamount_deduction = 0;
				$compensateamount = $this->Payroll_model->GetCompensateamount($user_id, $start_date_y_m);
				if (!empty($compensateamount)) {
					foreach ($compensateamount as $val) {
						if ($val->amount_type == 1) {
							$total_compensateamount_allowance += $val->total_amount;
						} else {
							$total_compensateamount_deduction += $val->total_amount;
						}
					}
				}

				$net_salary = $net_salary + $totl_amt + $totl_amt_leva + $annual_amt_pay + $total_compensateamount_allowance - $total_compensateamount_deduction;
				
				$basic_salary = $this->Xin_model->company_currency_sign($basic_salary, $r->company_id);
				if ($net_salary < 0) {
					$net_salary = '0.00';
				}
				

			
				if ($r->wages_type == 1) {
					if ($system[0]->is_half_monthly == 1) {
						$emp_payroll_wage = $wages_type;
					} else {
						$emp_payroll_wage = $wages_type;
					}
				} else {
					$emp_payroll_wage = $wages_type;
				}
				
				$emp_id = $r->employee_id;

				$paymonth = date('m', strtotime($pay_date));
				$payyear = date('Y', strtotime($pay_date));
				$empdata = $this->Employees_model->get_employee_details($user_id);
				$basepay = $empdata[0]->basic_salary;
				$no_of_days = date('t', strtotime($pay_date));

				$late_amount = $payment_balance = 0;
				$latededuction = $this->Xin_model->GetLateDeductionCalcCommon($user_id, $paymonth, $payyear, $no_of_days);
				$late_amount = $latededuction[0];
				$userinfo = $this->Employees_model->get_employee_details($user_id);
				$companyinfo = $this->Employees_model->get_company_details($userinfo[0]->company_id);
				$late_status = $companyinfo[0]->late_deduction;
				if ($late_status == 'yes') {
					$late_amount = $late_amount;
				}
				print_r($pay_date.' ');
				$overtime_amount = $this->Xin_model->getEmployeeMonthlyOTamount($user_id, $pay_date);
				

				$payment_balance = $this->Employees_model->get_employee_payment_balance($user_id,$p_date);

				$overtime_incentive_amount = $this->Xin_model->getEmployeeMonthlyOT_incentive_amount($user_id, $pay_date);
				
				// Air ticket Encashment Start
				$encashment_check = $this->Employees_model->employee_encashment_check($user_id, $month_year);
				if (!empty($encashment_check)) {
					$encashstatus = $encashment_check[0]->status;
					if (!is_null($encashstatus)) {
						$encashrate = $this->Employees_model->get_employee_encashrate_payroll($user_id, $month_year);
						$encash_amt = $encashrate[0]->amount;
					} else {
						$encash_amt = 0;
					}
				} else {
					$encash_amt = 0;
				}

				//ot hours normal
				$ot_hr = 0;
				$otDetails = $this->Employees_model->get_employee_ot_data($user_id,$start_date_co,$end_date_co); 
				
				foreach ($otDetails as $overtimedetails) {
					
					if($overtimedetails->approved_ot_amount !== 0){
						$ot_hr+= $overtimedetails->approved_ot_hours;
					}
				}
				//ot hours incentive
				$inc_ot_hr = 0;
				$ot_incentive_Details = $this->Employees_model->get_employee__inc_ot_data($user_id,$start_date_co,$end_date_co); 
				
				foreach ($ot_incentive_Details as $overtimedetails) {
					
					if($overtimedetails->approved_ot__incentive_amount !== 0){
						$inc_ot_hr+= $overtimedetails->approved_ot_incentive_hours;
					}
				}
				//$nettotal = $net_salary;
				$overtimestatus = $this->Employees_model->get_employee_overtimestatus($user_id);
				$otstatus = $overtimestatus[0]->ot_eligible;
				if ($otstatus == "yes") {
					if ($net_salary == 0) {
						$nettotal = 0.000;
					} else {
						$nettotal = $net_salary + $overtime_amount + $overtime_incentive_amount - $late_amount;
					}
				} else {
					if ($net_salary == 0) {
						$nettotal = 0.000;
					} else {
						$nettotal = $net_salary - $late_amount;
					}
				}
				$nettotal +=  $payment_balance ; 

				$deduction_amt_net_total_zero = 0;
				$deduction_overtime_amount_total_zero = 0;
				$deduction_late_amount_total_zero = 0;
				if ($nettotal <= 0) {
					$deduction_amt_net_total_zero = $leave_cut_salary;
					$deduction_late_amount_total_zero = $late_amount;
					if ($otstatus == "yes") {
						$deduction_overtime_amount_total_zero = $overtime_amount + $overtime_incentive_amount;
					}
				}
				$expense_total_claim = 0;
				/* Expense Claim Start */
				//if ($nettotal > 0) {
				$expense_total_claim = $this->Xin_model->FindExpenseClaimTotal($user_id, $p_date);
				/* if annual leave connect to payroll start */
				$annual_leave_amnt = 0 ;
				$annual_leave_amnt = $this->Payroll_model->annual_leave_amount_payroll($user_id,$month_year) ;
				
				$nettotal = $nettotal+  $encash_amt; 
			
				/* Expense Claim End */
				$nettotal = $nettotal + $annual_leave_amnt + $expense_total_claim;
				$nettotal_salary = $nettotal;
				$nettotal = $this->Xin_model->company_currency_sign($nettotal, $r->company_id);


				$location_id = $r->location_id;
				$monthyear = $payyear . "-" . $paymonth;
				$get_management_status = $this->Employees_model->get_management_employee_status($r->user_id, $location_id, $p_date);


				$days_in_month = $days = date("t");

				$management_status_val = 1;
				if (empty($get_management_status)) {
					$management_status_val = 1;
				} else {
					$management_status_val = 0;
				}
				$current_month = date('Y-m');
				$search_month = date('Y-m', strtotime($month_year));
				if (strtotime($search_month) > strtotime($current_month)) {
					$nettotal = $this->Xin_model->company_currency_sign(0, $r->company_id);
				}
				$monthly_allowance = 0;
				$sal_ded = $other_deductions_amount;
				$monthly_allowance = $other_allowance;
				if($r->nationality_id == '17'){
					$nationality ='Local';
				}else{
					$nationality = 'Expact';
				}
				//swift, iban
				$bankdetails = $this->Employees_model->get_bank_details($r->user_id);
				if(!empty($bankdetails)){
					$iban = $bankdetails[0]->iban;
					$swift = '--';
				}else{
					$iban = '--';
					$swift = '--';
				}
				
				if ($status_pay_value < 1 && $management_status_val > 0) {
					$data_temp = array(
						array(
							'user_id' =>$r->user_id,
							'employee_id' => $emp_id,
							'mpi_id' => $r->arabic_name,
							'location_id' => $r->location_id,
							'department_id' => $r->department_id,
							'emp_name' => $emp_name,
							'date_of_joining' => $r->date_of_joining,
							'nationality' => $nationality,
							'cpr_no' =>isset($cpr_num[0]->cpr_num) ? $cpr_num[0]->cpr_num : null,
							'swift' => $swift,
							'iban' => $iban,
							'present_days' => $present_days,
							'basic_salary' => $bs_pay,
							'basic_earning' => number_format($basic_earning, 3),
							'special_allowance'=>$special_allowance,
							'normal_allowance'=>$normal_allowance,
							'house_allowance'=>$house_allowance,
							'increment' =>$increment_data[0],
							'ot_hours' => $ot_hr,
							'ot_inc_hour' => $inc_ot_hr,
							'ot_amnt' => $overtime_amount,
							'ot_inc_amnt' => $overtime_incentive_amount,
							'bonus' => $monthly_allowance,
							'arrear_sal' => $payment_balance,
							'leave_encashment' => $annual_amt_pay,
							'ticket_encashment' => $encash_amt,
							'annual_amnt' => number_format($annual_leave_amnt,3),
							'late' => $late_amount,
							'sal_ded' => $sal_ded,
							'gosi_ded' => $new_gosi_amt,
							'lop' => $leave_cut_salary,
							'net_salary' => $nettotal,
							
						)
					);
				}
				$temp_storage = $this->Payroll_model->insert_temp_excel_payroll($data_temp);
			}
		}


		
	}

}
