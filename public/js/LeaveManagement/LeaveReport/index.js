(function($){
    'use strict';
    $(document).ready(function(){
        app.startEndDatePickerWithNepali('nepaliFromDate', 'fromDate', 'c', 'toDate', null, true);

        var $table=$('#leaveReportTable');
        var $search = $('#search');
        var $companyId = $('#companyId');

        $.each(document.searchManager.getIds(), function (key, value) {
            $('#' + value).select2();
        });

      

        app.initializeKendoGrid($table,[
            {field:"Head_count", title:"Function"},
            {field:"Total_employee",title:"Head Count"},
            {field:"Total_Leave_Assign",title:"Earned Leave"},
            {field:"Leave_Used",title:"Actual Leave Used"},
            {field:"Used_leave_Percentage",title:"Used Leave %"},

        ]);

        // app.searchTable('leaveReportTable');

        // app.pullDataById("", {}).then(function (response) {
        //     app.renderkendoGrid($table, response.data);
        // }, function (error) {

        // });

        var exportMap = {
            'Head_count':'Function',
            'Total_employee':'Head Count',
            'Total_Leave_Assign':'Earned Leave',
            'Leave_Used':'Actual Leave Used',
            'Used_leave_Percentage':'Used Leave %'
        }
        // map = app.prependPrefExportMap(map);
        var months=null;
        var $year=$('#leaveYear');
        var $month=$('#leaveMonth');
        app.setLeaveMonth($year,$month,function(yearList,monthList,currentMonth){
            months=monthList;
        });

        var onSearch=function(){

            App.blockUI({target:"#hris-page-content"});
            app.pullDataById(document.pullLeaveReportListLink,{
                'companyId':$companyId.val(),
                'leaveYear':$year.val(),
                'leaveMonth':$month.val()
            }).then(function(success){
                App.unblockUI("#hris-page-content");
                console.log(success);
                app.renderKendoGrid($table,success.data);
            },function(failure){
                App.unblockUI('#hris_page-content');
            });
        };

        $search.on('click',function(){
            onSearch();
        })
        

        

        $('#excelExport').on('click', function () {
            app.excelExport($table, exportMap, "Leave Report List.xlsx");
        });
        $('#pdfExport').on('click', function () {
            app.exportToPDF($table, exportMap, "Leave Report List.pdf");
        });
    });
}
)(window.jQuery,window.app);

