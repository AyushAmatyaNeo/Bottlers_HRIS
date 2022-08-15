<?php

namespace AttendanceManagement\Repository;

use Application\Helper\EntityHelper;
use Application\Model\Model;
use Zend\Db\Adapter\AdapterInterface;
use Application\Repository\HrisRepository;

class ShiftRoasterRepository extends HrisRepository{
    protected $adapter;

    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter=$adapter;
    }

    public function shiftDetails($data){
        $sql="
    BEGIN 
        INSERT INTO HRIS_EMPLOYEE_SHIFT_ROASTER 
        (
            EMPLOYEE_ID,SHIFT_ID,FOR_DATE,CREATED_BY,MODIFIED_BY,CREATED_DT,MODIFED_DT
            )
        VALUES
        (
            {$data['employeeId']},
            (select shift_id from hris_shifts where lower(shift_ename) = lower('{$data['shiftId']}') and status='E')
            ,'{$data['for_date']}',null,null,null,null

        );
        HRIS_REATTENDANCE('{$data['for_date']}',{$data['employeeId']},'{$data['for_date']}');
    END;";

        // echo '<pre>';print_r($sql);die; 
        $statement=$this->adapter->query($sql);
        return $statement;
    }
}
?>