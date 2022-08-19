(function ($, app) {
    'use strict';
    $(document).ready(function () {
        var $employeeId = $('#employeeId');
        var $trainingId = $("#trainingId");
        var $title = $("#title");
        var $trainingType = $("#trainingType");
        var $startDate = $("#startDate");
        var $endDate = $("#endDate");
        var $nepaliStartDate = $("#nepaliStartDate");
        var $nepaliEndDate = $("#nepaliEndDate");
        var $duration = $("#duration");
        var $dailyTrainingHour = $("#dailyTrainingHour");
        var trainingList=[];
        var training=[];

        $('select').select2();
        app.startEndDatePickerWithNepali('nepaliStartDate', 'startDate', 'nepaliEndDate', 'endDate', function (fromDate, toDate) {
            if (fromDate <= toDate) {
                var oneDay = 24 * 60 * 60 * 1000; // hours*minutes*seconds*milliseconds
                var diffDays = Math.abs((fromDate.getTime() - toDate.getTime()) / (oneDay));
                var newValue = diffDays + 1;
                $duration.val(newValue);
            }
        });

        var trainingChange = function ($this) {
            if (typeof document.trainingList === 'undefined' || document.trainingList === null || document.trainingList.length === 0) {
                return;
            }
            var training = document.trainingList[$this.val()];
            var startDate = (training == null) ? '' : app.getSystemDate(training["START_DATE"]);
            var endDate = (training == null) ? '' : app.getSystemDate(training["END_DATE"]);
            
            if(training != null){
                $dailyTrainingHour.prop('readonly',true);
                $dailyTrainingHour.val(training['DAILY_TRAINING_HOUR']);
            }else{
                $dailyTrainingHour.val('');
                $dailyTrainingHour.prop('readonly',false);
                }
        

            $title.val((training == null) ? '' : training["TRAINING_NAME"]);
            $startDate.datepicker('setStartDate', startDate);
            $startDate.datepicker('setEndDate', endDate);
            $startDate.datepicker('setDate', startDate);
            $endDate.datepicker('setStartDate', startDate);
            $endDate.datepicker('setEndDate', endDate);
            $endDate.datepicker('setDate', endDate);
            $duration.val((training == null) ? '' : training["DURATION"]);
            $trainingType.val((training == null) ? '' : training["TRAINING_TYPE"]).trigger('change.select2');
            $(`input[type='radio'][name='isWithinCompany'][value='${(training == null) ? '' : training["IS_WITHIN_COMPANY"]}']`).prop('checked', true);
            app.lockField((training != null), [$title, $startDate,$nepaliStartDate, $endDate, $duration, $trainingType, $("input[name='isWithinCompany']")]);
        };

        
        $employeeId .on('change', function () {
            employeeChange(this);
        });
        var employeeChange = function (obj) {
            
            var $this = $(obj);
            app.floatingProfile.setDataFromRemote($this.val());
            App.blockUI({target: "#hris-page-content", message: "Fetching Employee Training"});
            app.pullDataById(document.wspullTrainingDetailWidEmployeeId, {
                'employeeId': $this.val()
            }).then(function (success) {
                App.unblockUI("#hris-page-content");
                trainingList = success.data;
                app.populateSelect($trainingId, trainingList, 'id', 'name', 'Select a Training', null, null, false);

            }, function (failure) {
                App.unblockUI("#hris-page-content");
            });
        };
        $trainingId.on('change', function () {
                 GetEmployeeTrainingDetails($(this));          
        });
        app.floatingProfile.setDataFromRemote($employeeId.val());
        app.setLoadingOnSubmit("TrainingRequest", function () {
            app.lockField(false, [$title, $startDate,$nepaliStartDate, $endDate, $duration, $trainingType, $("input[name='isWithinCompany']")]);
            return true;
        });
        var GetEmployeeTrainingDetails = function (obj) {            
            var $this = $(obj);
            app.pullDataById(document.wsPullTrainingDetail, {
                'trainingId': $this.val(),
                'employeeId': $employeeId.val()
            }).then(function (success) {
                if(success != null){
                    if(success.success == true){
                        training= success.data;
            
                        var startDate = (training == null) ? '' : (training["START_DATE"]);
                        var endDate = (training == null) ? '' : (training["END_DATE"]);
                        
                        if(training != null){
                            $dailyTrainingHour.prop('readonly',true);
                            $dailyTrainingHour.val(training['DAILY_TRAINING_HOUR']);
                        }else{
                            $dailyTrainingHour.val('');
                            $dailyTrainingHour.prop('readonly',false);
                            }
                        $title.val((training == null) ? '' : training["TRAINING_NAME"]);
                        $startDate.datepicker('setStartDate', startDate);
                        $startDate.datepicker('setEndDate', endDate);
                        $startDate.datepicker('setDate', startDate);
                        $endDate.datepicker('setStartDate', startDate);
                        $endDate.datepicker('setEndDate', endDate);
                        $endDate.datepicker('setDate', endDate);
                        $duration.val((training == null) ? '' : training["DURATION"]);
                        $trainingType.val((training == null) ? '' : training["TRAINING_TYPE"]).trigger('change.select2');
                        $(`input[type='radio'][name='isWithinCompany'][value='${(training == null) ? '' : training["IS_WITHIN_COMPANY"]}']`).prop('checked', true);
                        app.lockField((training != null), [$title, $startDate,$nepaliStartDate, $endDate, $duration, $trainingType, $("input[name='isWithinCompany']")]);
            
                    }
                }
            else{
                $title.val="";
                $startDate.val="";
                $endDate.val="";
                $duration.val="";
                $trainingType.val="";
            }
            }, function (failure) {
        });
        };
    });
})(window.jQuery, window.app);

