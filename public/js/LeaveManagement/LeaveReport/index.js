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
            {field:"FUNCTION", title:"Function"},
            {field:"HEAD_COUNT",title:"Head Count"},
            {field:"EARNED_LEAVE",title:"Earned Leave"},
            {field:"ACTUAL_USED_LEAVE",title:"Actual Leave Used"},
            {field:"USED_LEAVE_PERCENTAGE",title:"Used Leave %"},

        ]);

        // app.searchTable('leaveReportTable');

        // app.pullDataById("", {}).then(function (response) {
        //     app.renderkendoGrid($table, response.data);
        // }, function (error) {

        // });

        var exportMap = {
            'FUNCTION':'Function',
            'HEAD_COUNT':'Head Count',
            'EARNED_LEAVE':'Earned Leave',
            'ACTUAL_USED_LEAVE':'Actual Leave Used',
            'USED_LEAVE_PERCENTAGE':'Used Leave %'
        }
        // map = app.prependPrefExportMap(map);

        $search.on('click',function(){
            var q=document.searchManager.getSearchValues();
            q['fromDate']=$('#fromDate').val();
            q['toDate']=$('#toDate').val();
            q['companyId']=$companyId.val();

            App.blockUI({target:"#hris-page-content"});
            app.pullDataById(document.pullLeaveReportListLink,q).then(function(success){
                App.unblockUI("#hris-page-content");
                // console.log(success);
                app.renderKendoGrid($table,success.data);
            },function(failure){
                App.unblockUI('#hris_page-content');
            });
        });

        $('#excelExport').on('click', function () {
            app.excelExport($table, exportMap, "Leave Report List.xlsx");
        });
        $('#pdfExport').on('click', function () {
            app.exportToPDF($table, exportMap, "Leave Report List.pdf");
        });
    });
}
)(window.jQuery,window.app);


