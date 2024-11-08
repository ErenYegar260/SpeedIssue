$(document).ready(function() {
    var xin_table = $('#xin_table').dataTable({
        dom: 'lBfrtip',
        buttons: [
            {
                extend: 'pdf',
                attr: {
                    title: 'Pdf',
                    id: 'pdf_download'
                },
                action: function pdf_download() {
                    $('#management_approval_pdf').submit();
                    setTimeout(function() {
                        $("#pdf_div").load(" #pdf_div > *");
                    }, 80);
                }
            },
            {
                extend: 'excel',
                attr: {
                    title: 'Excel',
                    id: 'exceldownload'
                },
                action: function exceldownload() {
                    $('#e_month_year_val').val($('#month_year').val());
                    $('#management_excel').submit();
                    setTimeout(function() {
                        $("#excel_div").load(" #excel_div > *");
                    }, 80);
                }
            }
        ],
    });
    var currentPage = 1;
    var autoRunCompleted = false;
    
    function getTotalPagesFromDB() {
        return $.ajax({
            url: site_url + 'payroll/get_total_pages', // Assuming this endpoint returns the total number of pages
            type: 'GET',
            dataType: 'json', // Expecting a JSON response
            success: function(response) {
               // alert("Total Pages: " + response.totalPages);
                return response.totalPages; // Return total pages from the response
            },
            error: function(xhr, status, error) {
                console.error('Failed to fetch total pages:', status, error);
                return 0; // If there's an error, return 0 to avoid breaking functionality
            }
        });
    }
    function callBackendFunction(currentPage, totalPages) {
        var monthYear = $('#month_year').val();
        // alert("Total Pages: " + totalPages);
        // alert("Current Page: " + currentPage);

        // Disable and update Excel button text while loading
        $('#exceldownload').text('Excel Loading...').attr('disabled', true);

        $.ajax({
            url: site_url + 'payroll/prepare_data_for_listing',
            type: 'GET',
            data: {
                location_id: 0,
                company_id: 1,
                department_id: 0,
                employee_id: 0,
                p_date: monthYear,
                page: currentPage
            
            },
            success: function(response) {
                console.log('AJAX Success:', response); 
                let employeesData = response.employees_id_checked.employees_with_net_salary;

                console.log('Employees with net salary:', employeesData);
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
            }
        });
    }
    
    
    
    // Function to reload Excel button dynamically
    function reloadExcelButtonIfComplete(totalPages) {
        if (currentPage >= totalPages) {
            $('#exceldownload').text('Excel').attr('disabled', false); // Enable when all pages are processed
        } else {
            // Keep checking until process completes
            setTimeout(function() {
                getTotalPagesFromDB().then(function(totalPages) {
                    reloadExcelButtonIfComplete(totalPages);
                });
            }, 5000); // Check every 5 seconds
        }
    }
    
    // Auto-run process after fetching total pages from the DB
    getTotalPagesFromDB().then(function(totalPages) {
        callBackendFunction(currentPage, totalPages); // Initial call
    
        var autoRunInterval = setInterval(function() {
            currentPage++;
    
            if (currentPage > totalPages) {
                clearInterval(autoRunInterval); // Stop auto-run when all pages are processed
            } else {
                callBackendFunction(currentPage, totalPages);
            }
        }, 30000);
    
        // Dynamically reload Excel button state based on the total pages
        reloadExcelButtonIfComplete(totalPages);
    });
    

    $('[data-plugin="select_hrm"]').select2($(this).attr('data-options'));
    $('[data-plugin="select_hrm"]').select2({ width: '100%' });

    jQuery.get(site_url + "common_filter/get_multiple_all_company_locations/" + jQuery('#filter_company_id').val(), function(data, status) {
        jQuery('#filter_location').html(data);
    });
    jQuery.get(site_url + "common_filter/get_multiple_all_company_location_wise_employees/" + jQuery('#filter_company_id').val() + "/0", function(data, status) {
        jQuery('#filter_employee').html(data);
    });
    /* Delete data */
    $("#delete_record").submit(function(e) {
        /*Form Submit*/
        e.preventDefault();
        var obj = $(this),
            action = obj.attr('name');
        $.ajax({
            type: "POST",
            url: e.target.action,
            data: obj.serialize() + "&is_ajax=2&form=" + action,
            cache: false,
            success: function(JSON) {
                if (JSON.error != '') {
                    toastr.error(JSON.error);
                    Ladda.stopAll();
                } else {
                    //$('.delete-modal').modal('toggle');
                    xin_table.api().ajax.reload(function() {
                        toastr.success(JSON.result);
                    }, true);
                    Ladda.stopAll();
                }
            }
        });
    });

    // detail modal data payroll
    $('#payroll_template_modal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var employee_id = button.data('employee_id');
        var month_year = button.data('payment_date');
        var net_amount = button.data('net');
        // alert(net_amount);
        var modal = $(this);

        $.ajax({
            url: site_url + 'payroll/payroll_template_read/',
            type: "GET",
            data: 'jd=1&is_ajax=11&mode=not_paid&data=payroll_template&type=payroll_template&employee_id=' + employee_id + '&month_year=' + month_year + '&net_amount=' + net_amount,
            success: function(response) {
                if (response) {
                    $("#ajax_modal_payroll").html(response);
                }
            }
        });
    });
    // detail modal data  hourlywages
    $('#hourlywages_template_modal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var employee_id = button.data('employee_id');
        var payment_date = $('#month_year').val();
        var company_id = button.data('company_id');
        var modal = $(this);
        $.ajax({
            url: site_url + 'payroll/hourlywage_template_read/',
            type: "GET",
            data: 'jd=1&is_ajax=11&mode=not_paid&data=hourly_payslip&type=read_hourly_payment&employee_id=' + employee_id + '&pay_date=' + payment_date + '&company_id=' + company_id,
            success: function(response) {
                if (response) {
                    $("#ajax_modal_hourlywages").html(response);
                }
            }
        });
    });

    // detail modal data
    $('.detail_modal_data').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var employee_id = button.data('employee_id');
        var pay_id = button.data('pay_id');
        var company_id = button.data('company_id');
        var modal = $(this);
        $.ajax({
            url: site_url + 'payroll/make_payment_view/',
            type: "GET",
            data: 'jd=1&is_ajax=11&mode=modal&data=pay_payment&type=pay_payment&emp_id=' + employee_id + '&pay_id=' + pay_id + '&company_id=' + company_id,
            success: function(response) {
                if (response) {
                    $("#ajax_modal_details").html(response);
                }
            }
        });
    });


    // detail modal data
    $('.emo_monthly_pay').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var employee_id = button.data('employee_id');
        var payment_date = $('#month_year').val();
        var company_id = button.data('company_id');
        var modal = $(this);
        $.ajax({
            url: site_url + 'payroll/pay_salary/',
            type: "GET",
            data: 'jd=1&is_ajax=11&data=payment&type=monthly_payment&employee_id=' + employee_id + '&pay_date=' + payment_date + '&company_id=' + company_id,
            success: function(response) {
                if (response) {
                    $("#emo_monthly_pay_aj").html(response);
                }
            }
        });
    });

    $('.emo_hourly_pay').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var employee_id = button.data('employee_id');
        var payment_date = $('#month_year').val();
        var company_id = button.data('company_id');
        var modal = $(this);
        $.ajax({
            url: site_url + 'payroll/pay_hourly/',
            type: "GET",
            data: 'jd=1&is_ajax=11&data=hourly_payment&type=fhourly_payment&employee_id=' + employee_id + '&pay_date=' + payment_date + '&company_id=' + company_id,
            success: function(response) {
                if (response) {
                    $("#emo_hourly_pay_aj").html(response);
                }
            }
        });
    });
    /* Add data */
    /*Form Submit*/
    $("#user_salary_template").submit(function(e) {
        e.preventDefault();
        var obj = $(this),
            action = obj.attr('name');
        $('.save').prop('disabled', true);
        $('.icon-spinner3').show();
        $.ajax({
            type: "POST",
            url: e.target.action,
            data: obj.serialize() + "&is_ajax=1&edit_type=payroll&form=" + action,
            cache: false,
            success: function(JSON) {
                if (JSON.error != '') {
                    toastr.error(JSON.error);
                    $('.save').prop('disabled', false);
                    $('.icon-spinner3').hide();
                    Ladda.stopAll();
                } else {
                    xin_table.api().ajax.reload(function() {
                        toastr.success(JSON.result);
                    }, true);
                    $('.icon-spinner3').hide();
                    $('.save').prop('disabled', false);
                    Ladda.stopAll();
                }
            }
        });
    });

    /* Set Salary Details*/
    $("#set_salary_details").submit(function(e) {
        /*Form Submit*/
        e.preventDefault();
        currentPage = 1;
        getTotalPagesFromDB().then(function(totalPages) {
            callBackendFunction(currentPage, totalPages);
        });
        var obj = $(this),
            action = obj.attr('name');
        var company_id = jQuery('#filter_company_id').val();
        var employee_id = jQuery('#filter_employee_id').val();
        var location_id = jQuery('#filter_location_id').val();
        var department_id = jQuery('#filter_department_id').val();
        var month_year = jQuery('#month_year').val();

        jQuery('.p_month_year').val(month_year);
        jQuery('.p_company_id').val(company_id);
        jQuery('.p_location_id').val(location_id);
        jQuery('.p_employee_id').val(employee_id);
        jQuery('.p_department_id').val(department_id);
        $('#p_month').html(month_year);
        
        var xin_table2 = $('#xin_table').dataTable({
            "processing": true,
            "serverSide": true,
            "language": {
                processing: '<div class="loading-overlay is-active"><span class="fas fa-spinner fa-3x fa-spin"></span></div>'
            },
            "bDestroy": true,
            "ajax": {
                url: site_url + "payroll/management_approval_list/?location_id=" + location_id + "&month_year=" + month_year + "&company_id=" + company_id + "&employee_id=" + employee_id+ "&department_id=" + department_id,
                type: 'GET',
                "data": function (d) {
                    console.log("Start:", d.start);  // Logs the `start` parameter
                    console.log("Length:", d.length); // Logs the `length` parameter
                }
            },
            dom: 'lBfrtip',
            "buttons": [{
                    extend: 'pdf',
                    attr: {
                        title: 'Pdf',
                        id: 'pdf_download'
                    },
                    action: function pdf_download() {
                        $('#management_approval_pdf').submit();
                        setTimeout(function() {
                            $("#pdf_div").load(" #pdf_div > *");
                        }, 80);
                    }
                },
                {
                    extend: 'excel',
                    attr: {
                        title: 'Excel',
                        id: 'exceldownload'
                    },
                    action: function exceldownload() {
                        $('#e_month_year_val').val($('#month_year').val());
                        $('#management_excel').submit();
                        setTimeout(function() {
                            $("#excel_div").load(" #excel_div > *");
                        }, 80);
                    }
                }
            ],
            "fnDrawCallback": function(settings) {
                $('[data-toggle="tooltip"]').tooltip();
            },
            "initComplete": function(settings, json) {
                $('#payroll_date').html(month_year);
                Ladda.stopAll();
            }
        });
        $('.buttons-excel').click(function(){
            $.ajax({
                url: site_url + "payroll/activity_management_approve_excel/0",
                success: function(result){
              }});
        })
    
        $('.buttons-pdf').click(function(){
            $.ajax({
                url: site_url + "payroll/activity_management_approve_excel/1",
                success: function(result){
              }});
        })
    });
});
$(document).on("click", ".delete", function() {
    $('input[name=_token]').val($(this).data('record-id'));
    $('#delete_record').attr('action', base_url + '/payslip_delete/' + $(this).data('record-id')) + '/';
});
var checked_ids = [];
$(document).on("click", ".check_management_status", function() {
    checked_ids = [];
    var oTable = $('#xin_table').dataTable();
    var rowcollection = oTable.$(".check_management_status:checked", { "page": "all" });
    rowcollection.each(function(index, elem) {
        checked_ids.push($(elem).val());
    });
    $('#e_checked').val(checked_ids);
});



function getSelected() {
    var unique_checked_ids = [];
    // var unique_unchecked_ids = [];
    // var remaining_ids = [];
    $.each(checked_ids, function(i, el) {
        if ($.inArray(el, unique_checked_ids) === -1) unique_checked_ids.push(el);
    });

    // var oTable = $('#xin_table').dataTable();
    // var rowcollection =  oTable.$(".check_management_status", {"page": "all"});
    // rowcollection.each(function(index,elem){
    //     unchecked_ids.push($(elem).val());
    // });

    remaining_ids = unchecked_ids.filter(function(val) {
        return unique_checked_ids.indexOf(val) == -1;
    });

    // $.each(remaining_ids, function(i, el){
    //     if($.inArray(el, unique_unchecked_ids) === -1) unique_unchecked_ids.push(el);
    // });

    //unique_unchecked_ids,unique_checked_ids

    $('#e_checked').val(unique_checked_ids);
    //$('#e_unchecked').val(unique_unchecked_ids);

}

$(document).on("click", "#approve_selected", function() {
    $('#approve_selected').prop('disabled', true); 
    $('#e_location_id').val($('#aj_location_id').val());
    $('#e_month_year').val($('#month_year').val());
    checked_ids = [];
    var oTable = $('#xin_table').dataTable();
    var rowcollection = oTable.$(".check_management_status:checked", { "page": "all" });
    rowcollection.each(function(index, elem) {
        checked_ids.push($(elem).val());
    });
    $('#e_checked').val(checked_ids);
    if (checked_ids.length > 0) {
        toastr.success('Approved Successfully');
    } else {
        toastr.error('Something Wrong, Please Try Again');
    }
    $('#management_payroll_approval_status_change_form').submit();
});

/* new filter */
jQuery('body').on('change', '#filter_company_id', function() {
    jQuery.get(site_url + "common_filter/get_multiple_all_company_locations/" + jQuery(this).val(), function(data, status) {
        jQuery('#filter_location').html(data);
    });
    jQuery.get(site_url + "common_filter/get_multiple_all_company_location_wise_employees/" + jQuery(this).val() + "/0", function(data, status) {
        jQuery('#filter_employee').html(data);
    });
});

jQuery('body').on('change', '#filter_location_id', function() {
    jQuery.get(site_url + "common_filter/get_multiple_all_company_location_wise_employees/" + jQuery('#filter_company_id').val() + "/" + jQuery(this).val(), function(data, status) {
        jQuery('#filter_employee').html(data);
    });
});

/* new filter */