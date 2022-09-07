<?php

namespace LeaveManagement\Controller;

use Application\Controller\HrisController;
use Application\Helper\EntityHelper;
use Application\Helper\Helper;
use Application\Model\FiscalYear;
use Exception;
use LeaveManagement\Repository\LeaveReportRepository;
use Zend\Authentication\Storage\StorageInterface;
use Zend\Db\Adapter\Adapter;
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
        $fiscalYearKV=EntityHelper::getTableKVList($this->adapter,FiscalYear::TABLE_NAME,FiscalYear::FISCAL_YEAR_ID,[FiscalYear::FISCAL_YEAR_NAME]);
        return $this->stickFlashMessagesTo([
            'searchValues'=>EntityHelper::getSearchData($this->adapter),
            'acl' => $this->acl,
            'preference' => $this->preference,
            'fiscalYearKV'=>$fiscalYearKV

        ]);
    }

    public function pullLeaveReportListAction(){
        try{
            $request=$this->getRequest();
            $data=$request->getPost();
            $recordList=$this->repository->getLeaveReport($data);
            return new JsonModel(["success"=>"true","data"=>$recordList,"message"=>null]);
        } catch(Exception $e){
            return new JsonModel(['success'=>false,'data'=>null,'message'=>$e->getMessage()]);
        }
    }

    public function getLeaveYearMonthAction(){
        try{
        $data['years']=EntityHelper::getTableList($this->adapter,"HRIS_LEAVE_YEARS",["LEAVE_YEAR_ID","LEAVE_YEAR_NAME"]);
        $data['months']=iterator_to_array($this->repository->fetchLeaveYearMonth(),false);

        //echo '<pre>';print_r(  $data['months']);die;
        $data['currentMonth']=$this->repository->getCurrentLeaveMonth();
        return new JsonModel(['success'=>true,'data'=>$data,'error'=>'']);
    }catch (Exception $e){
        return new JsonModel(['success'=>false,'data'=>[],'error'=>$e->getMessage()]);
    }
}
    
}

