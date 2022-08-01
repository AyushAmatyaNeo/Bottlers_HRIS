<?php
namespace AttendanceManagement\Repository;

use Application\Helper\EntityHelper;
use Application\Helper\Helper;
use Application\Model\Model;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Sql\Sql;
use Application\Repository\RepositoryInterface;

class WhereaboutsAssignRepository {
    private $adapter;

    public function __construct(AdapterInterface $adapter) {
        $this->adapter = $adapter;
    }

    public function getEmployeeList($data)
    {

        $condition = EntityHelper::getSearchConditon($data['companyId'], $data['branchId'], $data['departmentId'], $data['positionId'], $data['designationId'], $data['serviceTypeId'], $data['serviceEventTypeId'], $data['employeeTypeId'], $data['employeeId']);

        $sql = "SELECT 
                  E.FULL_NAME,
                  EWA.ORDER_BY,
                  case when EWA.ORDER_BY IS NOT NULL then 'Y'
                  else 'N' END AS ASSIGNED,
                  C.COMPANY_NAME,
                  B.BRANCH_NAME,
                  DEP.DEPARTMENT_NAME,
                  E.EMPLOYEE_ID,
                  E.EMPLOYEE_CODE,
                  P.POSITION_NAME,
                  DES.DESIGNATION_TITLE
                FROM HRIS_EMPLOYEES E
                LEFT JOIN HRIS_COMPANY C
                ON (E.COMPANY_ID=C.COMPANY_ID)
                LEFT JOIN HRIS_EMP_WHEREABOUT_ASN EWA
                ON (E.EMPLOYEE_ID = EWA.EMPLOYEE_ID)
                LEFT JOIN HRIS_BRANCHES B
                ON (E.BRANCH_ID=B.BRANCH_ID)
                LEFT JOIN HRIS_DEPARTMENTS DEP
                ON (E.DEPARTMENT_ID=DEP.DEPARTMENT_ID)
                LEFT JOIN HRIS_POSITIONS P 
                ON (P.POSITION_ID=E.POSITION_ID)
                LEFT JOIN HRIS_DESIGNATIONS DES
                ON (DES.DESIGNATION_ID=E.DESIGNATION_ID)
                WHERE 1            =1 
                 AND E.STATUS = 'E'  AND E.IS_ADMIN NOT IN ('Y')
                {$condition}
                ORDER BY EWA.ORDER_BY";

//        print_r($sql);
//        die();
        $statement = $this->adapter->query($sql);
        $result = $statement->execute();
        return $result;
    }

    public function updateWhereabouts($employeeId, $updateData){
        $checkSql = '';
        $sql = '';

        if($updateData['orderBy'] == null){
            $sql = "delete from HRIS_EMP_WHEREABOUT_ASN where EMPLOYEE_ID = {$employeeId}";
        }
        else {
            $sql = "
                BEGIN 
                HRIS_WHEREABOUT_ASSIGN({$employeeId},{$updateData['orderBy']});
                END;
                ";
        }

        EntityHelper::rawQueryResult($this->adapter, $sql);
        return;
    }

}
