select Head_count, sum(total_leave_assigned) as Total_Leave_Assign,sum(total_employee) as Total_employee,sum(total_leave_used) as Leave_Used,sum(Used_leave_percentage) as Used_leave_Percentage
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
                    LEFT JOIN hris_leave_month_code       lmc ON ( lmc.leave_year_id = 5 )
                    LEFT JOIN hris_leave_master_setup     lms2 ON ( lms2.leave_id = elr.leave_id )
                WHERE
                        1 = 1
                    AND lms2.is_monthly = 'Y'
                        AND lms2.status = 'E'
--and ELR.start_date between LMC.from_date and LMC.to_date
--and ELR.end_date between LMC.from_date and LMC.to_date
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
--and E.employee_id=1000668
            AND lms.status = 'E'
                AND la.fiscal_year_month_no = 2
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
            )                          eul ON ( eul.employee_id = e.employee_id
                       AND eul.leave_id = lms.leave_id )
        WHERE
                lms.is_monthly = 'N'
--and E.employee_id=1000668
            AND lms.status = 'E'
--and E.company_id=2
        GROUP BY
            d.department_id,
            d.department_name,
            e.employee_id
    )
GROUP BY
    department_id,
    department_name)
    ) group by Head_count;


