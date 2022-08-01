(function ($, app) {
    'use strict';
    $(document).ready(function () {
        $('select').select2();
        var $table = $('#table');
        var $search = $('#search');

        var columns = [
            {field: "EMPLOYEE_CODE", title: "Code", locked: true, width: 100},
            {field: "FULL_NAME", title: "Employee Name", locked: true, width: 180},
            {field: "BRANCH_NAME", title: "Branch Name", width: 140},
            {field: "DEPARTMENT_NAME", title: "Department Name", width: 160},
            {field: "DESIGNATION_TITLE", title: "Designation Name", width: 160},
            {field: "POSITION_NAME", title: "Position Name", width: 160},
            {field: "ORDER_BY", title: "Order", width: 160}
            ]


        var map = {
            'EMPLOYEE_CODE': 'Code',
            'FULL_NAME': 'Name',
            'BRANCH_NAME': 'Branch Name',
            'DEPARTMENT_NAME': 'Department Name',
            'DESIGNATION_TITLE': 'Designation Name',
            'POSITION_NAME': 'Position Name',
            'ORDER_BY': 'Order'
        };

        app.searchTable('table', ['EMPLOYEE_CODE', 'FULL_NAME'], false);

        app.initializeKendoGrid($table, columns, null, null, null, 'Whereabouts Report');

        $search.on('click', function () {
            var q = document.searchManager.getSearchValues();

            app.serverRequest(document.getWhereabouts, q).then(function (response) {
                if (response.success) {
                    app.renderKendoGrid($table, response.data);
                } else {
                    app.showMessage(response.error, 'error');
                }
            }, function (error) {

            });
        });

        $('#excelExport').on('click', function () {
            app.excelExport($table, map, 'Whereabouts Report.xlsx');
        });
        $('#pdfExport').on('click', function () {
            app.pdfExport($table, map, 'Whereabouts Report.pdf');
        });

    });
})(window.jQuery, window.app);