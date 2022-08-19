<?php

namespace LeaveManagement\Repository;

use Application\Helper\Helper;
use Zend\Db\Sql\Select;
use Application\Repository\HrisRepository;
use Zend\Db\Adapter\AdapterInterface;

class LeaveReportRepository extends HrisRepository{

    protected $tableGateway;
    protected $adapter;

    public function __construct(AdapterInterface $adapter,$tableName = null)
    {
        parent::__construct($adapter,$tableName);
        
    }

    public function getLeaveReport($data) {
        $companyId=$data['companyId'];
        $fromDate = $data['fromDate'];
        $toDate = $data['toDate'];

        $fromDateCondition="";
        $toDateCondition="";

        if($fromDate!=null){
            $fromDateCondition="AND LA.START_DATE>=TO_DATE('{$fromDate}','DD-MM-YYYY')";
        }
        if($toDate!=null){
            $toDateCondition="AND LA.END_DATE('{$toDate}','DD-MM-YYYY')";
        }

        $sql="SELECT FUNCTION,HEAD_COUNT,EARNED_LEAVE,ACTUAL_USED_LEAVE,ROUND(ACTUAL_USED_LEAVE/Earned_Leave*100) AS USED_LEAVE_PERCENTAGE
        FROM (
        select DISTINCT D.DEPARTMENT_NAME  AS FUNCTION,
        COUNT(*) OVER(PARTITION BY E.DEPARTMENT_ID) AS HEAD_COUNT,
        E.DEPARTMENT_ID
        ,(SELECT SUM(previous_year_bal) + SUM(total_days) FROM HRIS_EMPLOYEE_LEAVE_ASSIGN where
        
        EMPLOYEE_ID IN (SELECT EMPLOYEE_ID FROM hris_employees WHERE DEPARTMENT_ID = D.DEPARTMENT_ID))
        AS EARNED_LEAVE 
        ,(SELECT SUM(previous_year_bal)+SUM(total_days)-SUM(BALANCE) FROM HRIS_EMPLOYEE_LEAVE_ASSIGN WHERE EMPLOYEE_ID IN(SELECT EMPLOYEE_ID FROM hris_employees WHERE DEPARTMENT_ID=D.DEPARTMENT_ID))
        AS ACTUAL_USED_LEAVE
        from hris_employees E
        INNER JOIN HRIS_DEPARTMENTS D ON E.DEPARTMENT_ID = D.DEPARTMENT_ID
        INNER JOIN HRIS_COMPANY c ON C.COMPANY_ID=E.COMPANY_ID
        WHERE E.STATUS='E'
        {$fromDateCondition}{$toDateCondition}
        ORDER BY E.DEPARTMENT_ID 
        )";

        // echo '<pre>';print_r($sql);die;
         $statement = $this->adapter->query($sql);
         $result = $statement->execute();
         return $result->current();
    }

}

?>
 