<?php

namespace LeaveManagement\Repository;

use Application\Helper\Helper;
use Zend\Db\Sql\Select;
use LeaveManagement\Model\LeaveMonths;
use Application\Repository\HrisRepository;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\TableGateway\TableGateway;


class LeaveReportRepository extends HrisRepository{

    protected $tableGateway;
    protected $adapter;

    public function __construct(AdapterInterface $adapter,$tableName = null)
    {
        parent::__construct($adapter,$tableName);
        $this->leaveMonthTableGateway = new TableGateway("HRIS_LEAVE_MONTH_CODE", $adapter);

        
    }

    public function getLeaveReport($data) {
        $companyId=$data['companyId'];
        $year = $data['leaveYear'];
        $month = $data['leaveMonth'];
        $sql="select Head_count, sum(total_leave_assigned) as Total_Leave_Assign,sum(total_employee) as Total_employee,sum(total_leave_used) as Leave_Used,sum(Used_leave_percentage) as Used_leave_Percentage
        from(
        select Head_count,total_employee,Total_leave_assigned,Total_leave_Used ,round(Total_leave_Used/nullif(Total_leave_assigned,0)*100) as Used_leave_Percentage from (
        SELECT
            department_id,
            department_name as Head_count,
            COUNT(*) AS total_employee,
            SUM(total_leave_assigned) as Total_leave_assigned,
            SUM(total_used_leave) as Total_leave_Used
        FROM
            (
                SELECT 
                    d.department_id,
                    d.department_name,
                    e.employee_id,
                    SUM(la.total_days + la.previous_year_bal) AS total_leave_assigned,
                    SUM(nvl(eul.used_leave, 0))               AS total_used_leave
                FROM
                    hris_departments           d
                    LEFT JOIN hris_employees             e ON ( e.department_id = d.department_id )
                    LEFT JOIN hris_employee_leave_assign la ON ( la.employee_id = e.employee_id )
                    LEFT JOIN hris_leave_master_setup    lms ON ( lms.leave_id = la.leave_id )
                    LEFT JOIN hris_company               c ON ( c.company_id = e.company_id )
                    LEFT JOIN (
                        SELECT
                            elr.leave_id,
                            lmc.leave_year_month_no,
                            elr.employee_id,
                            nvl(SUM(
                                CASE
                                    WHEN half_day = 'N' THEN
                                        no_of_days
                                    ELSE
                                        no_of_days / 2
                                END
                            ),
                                0) AS used_leave
                        FROM
                            hris_employee_leave_request elr
                            LEFT JOIN hris_leave_month_code       lmc ON ( lmc.leave_year_id =$year )
                            LEFT JOIN hris_leave_master_setup     lms2 ON ( lms2.leave_id = elr.leave_id )
                        WHERE
                                1 = 1
                            AND lms2.is_monthly = 'Y'
                                AND lms2.status = 'E'
    
                                    AND elr.end_date < lmc.to_date
                                        AND elr.status = 'AP'
                        GROUP BY
                            elr.leave_id,
                            lmc.leave_year_month_no,
                            elr.employee_id
                    )                          eul ON ( eul.employee_id = e.employee_id
                               AND eul.leave_year_month_no = la.fiscal_year_month_no
                                   AND eul.leave_id = lms.leave_id )
                WHERE
                        lms.is_monthly = 'Y'
                    AND lms.status = 'E'
                        AND la.fiscal_year_month_no =(select leave_year_month_no from HRIS_LEAVE_MONTH_CODE where month_id=$month)
               GROUP BY
                    d.department_id,
                    d.department_name,
                    e.employee_id
            )
        GROUP BY
            department_id,
            department_name)
            
            UNION 
            
        select Head_count,total_employee,Total_leave_assigned,Total_leave_Used ,round(Total_leave_Used/nullif(Total_leave_assigned,0)*100) as Used_leave_Percentage from (
        SELECT
            department_id,
            department_name as Head_count,
            COUNT(*) AS total_employee,
            SUM(total_leave_assigned) as Total_leave_assigned,
            SUM(total_used_leave) as Total_leave_Used
        FROM
            (
                SELECT
                    d.department_id,
                    d.department_name,
                    e.employee_id,
                    SUM(la.total_days + la.previous_year_bal) AS total_leave_assigned,
                    SUM(nvl(eul.used_leave, 0))               AS total_used_leave
                FROM
                    hris_departments           d
                    LEFT JOIN hris_employees             e ON ( e.department_id = d.department_id )
                    LEFT JOIN hris_employee_leave_assign la ON ( la.employee_id = e.employee_id )
                    LEFT JOIN hris_leave_master_setup    lms ON ( lms.leave_id = la.leave_id )
                    LEFT JOIN hris_company               c ON ( c.company_id = e.company_id )
                    LEFT JOIN (
                        SELECT
                            elr.leave_id,
                            elr.employee_id,
                            nvl(SUM(
                                CASE
                                    WHEN half_day = 'N' THEN
                                        no_of_days
                                    ELSE
                                        no_of_days / 2
                                END
                            ),
                            0) AS used_leave
                        FROM
                            hris_employee_leave_request elr
                            LEFT JOIN hris_leave_master_setup     lms2 ON ( lms2.leave_id = elr.leave_id )
                        WHERE
                            1 = 1
                            AND lms2.is_monthly = 'N'
                            AND lms2.status = 'E'
                            AND elr.status = 'AP'
                        GROUP BY
                            elr.leave_id,
                            elr.employee_id
                    )       eul ON ( eul.employee_id = e.employee_id
                            AND eul.leave_id = lms.leave_id )
                WHERE
                    lms.is_monthly = 'N'
                    AND lms.status = 'E'

                GROUP BY
                    d.department_id,
                    d.department_name,
                    e.employee_id
            )
        GROUP BY
            department_id,
            department_name)
            ) group by Head_count
        ";
        echo '<pre>';print_r($sql);die;
         return $this->rawQuery($sql);
        //  $statement = $this->adapter->query($sql);
        //  $result = $statement->execute();
        //  return $result->current();
    }
    public function fetchLeaveYearMonth() {
        $rowset = $this->leaveMonthTableGateway->select(function (Select $select) {
            $select->columns(Helper::convertColumnDateFormat($this->adapter, new LeaveMonths(), [
                        'fromDate',
                        'toDate',
                    ]), false);

            $select->where([LeaveMonths::STATUS => 'E']);
            $select->order("LEAVE_YEAR_MONTH_NO ASC");
        });

        return $rowset;
    }

    public function getCurrentLeaveMonth() {
        $sql = <<<EOT
            SELECT MONTH_ID,
              LEAVE_YEAR_ID,
              LEAVE_YEAR_MONTH_NO,
              YEAR,
              MONTH_NO,
              MONTH_EDESC,
              MONTH_NDESC,
              FROM_DATE,
              INITCAP(TO_CHAR(FROM_DATE,'DD-MON-YYYY')) AS FROM_DATE_AD,
              BS_DATE(FROM_DATE) AS FROM_DATE_BS,
              TO_DATE ,
              INITCAP(TO_CHAR(TO_DATE,'DD-MON-YYYY')) AS TO_DATE_AD,
              BS_DATE(TO_DATE) AS TO_DATE_BS
            FROM HRIS_LEAVE_MONTH_CODE
            WHERE TRUNC(SYSDATE) BETWEEN FROM_DATE AND TO_DATE
EOT;
        $statement = $this->adapter->query($sql);
        $result = $statement->execute();
        return $result->current();
    }
}
?>
 