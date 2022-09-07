(function($){
    'use strict';
    $(document).ready(function(){
        app.startEndDatePickerWithNepali('nepaliFromDate', 'fromDate', 'c', 'toDate', null, true);

        var $table=$("#leaveReportTable");
        var $search = $('#search');
        var $companyId = $('#companyId');

        $.each(document.searchManager.getIds(), function (key, value) {
            $('#' + value).select2();
        });

      
  debugger;
        app.initializeKendoGrid($table,[
            {field:"HEAD_COUNT", title:"Function"},
            {field:"TOTAL_EMPLOYEE",title:"Head Count"},
            {field:"TOTAL_LEAVE_ASSIGN",title:"Earned Leave"},
            {field:"LEAVE_USED",title:"Actual Leave Used"},
            {field:"USED_LEAVE_PERCENTAGE",title:"Used Leave %"},

        ]);

        /// app.searchTable('leaveReportTable');

        // app.pullDataById("", {}).then(function (response) {
        //     app.renderkendoGrid($table, response.data);
        // }, function (error) {

        /// });

        var exportMap = {
            'HEAD_COUNT':'Function',
            'TOTAL_EMPLOYEE':'Head Count',
            'TOTAL_LEAVE_ASSIGN':'Earned Leave',
            'LEAVE_USED':'Actual Leave Used',
            'USED_LEAVE_PERCENTAGE':'Used Leave %'
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

