<?php

namespace LeaveManagement\Controller;

use Application\Controller\HrisController;
use Application\Helper\EntityHelper;
use Application\Helper\Helper;
use Exception;
use LeaveManagement\Repository\LeaveReportRepository;
use Zend\Authentication\Storage\StorageInterface;
use Zend\Db\Adapter\AdapterInterface;
use Zend\View\Model\JsonModel;

class LeaveReport extends HrisController{

public function __construct(AdapterInterface $adapter,StorageInterface $storage)
    {
        parent::__construct($adapter,$storage);
        $this->initializeRepository(LeaveReportRepository::class);
        
    }

    public function indexAction()
    {
        return $this->stickFlashMessagesTo([
            'searchValues'=>EntityHelper::getSearchData($this->adapter),
            'acl' => $this->acl,
            'preference' => $this->preference


        ]);
    }

    public function pullLeaveReportListAction(){
        try{
            $request=$this->getRequest();
            $data=$request->getPost();
            // echo '<pre>';print_r($data);die;
            $recordList=$this->repository->getLeaveReport($data);
            // echo '<pre>';print_r($recordList);die;
            return new JsonModel(["success"=>"true","data"=>$recordList,"message"=>null]);
        } catch(Exception $e){
            return new JsonModel(['success'=>false,'data'=>null,'message'=>$e->getMessage()]);
        }
    }

    
}
?>