<?php

namespace AttendanceManagement\Controller;

use Application\Controller\HrisController;
use Application\Helper\EntityHelper;
use Application\Helper\Helper;
use Application\Model\FiscalYear;
use Application\Model\Months;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Authentication\Storage\StorageInterface;
use Zend\View\Model\JsonModel;
use AttendanceManagement\Repository\ShiftRoasterRepository;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;
use Zend\Db\Adapter\Adapter;

class ShiftRoaster extends HrisController{
    public $adapter;

    public function _construct(AdapterInterface $adapter, StorageInterface $storage){
        parent::__construct($adapter,$storage);
        $this->adapter=$adapter;
        $this->initializeRepository(ShiftRoasterRepository::class);
    }

    public function indexAction()
    {
     
        return $this->stickFlashMessagesTo([
            'searchValues'=>EntityHelper::getSearchData($this->adapter),
            'acl'=>$this->acl,
        ]);
    }
    public function updateShiftRoasterAction(){
        $excelData =$_POST['data'];
        $basedOn = $_POST['basedOn'];

        // echo '<pre>';print_r($excelData);die;
        $detailRepo=new ShiftRoasterRepository($this->adapter);
        foreach ($excelData as $data){
                // if($data['A']==null || $data ['A']==''){continue;}
                $item['employeeId']=$data['A'];
                $item['for_date']=$data['C'];
                $item['shiftId']=$data['D'];
                // echo '<pre>';print_r($item);die;
                $detailRepo->shiftDetails($item);   

        
        }
        return new JsonModel(['success'=>true,'error'=>'']);

    }
}
?>
