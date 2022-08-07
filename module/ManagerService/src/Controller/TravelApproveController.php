<?php

namespace ManagerService\Controller;

use Application\Controller\HrisController;
use Application\Helper\EntityHelper;
use Application\Helper\Helper;
use Application\Helper\NumberHelper;
use Exception;
use ManagerService\Repository\TravelApproveRepository;
use Notification\Controller\HeadNotification;
use Notification\Model\NotificationEvents;
use SelfService\Form\TravelRequestForm;
use SelfService\Model\TravelRequest;
use SelfService\Repository\TravelExpenseDtlRepository;
use Zend\Authentication\Storage\StorageInterface;
use Zend\Db\Adapter\AdapterInterface;
use Zend\View\Model\JsonModel;
use SelfService\Repository\NewTravelRequestRepository;

class TravelApproveController extends HrisController {

    public function __construct(AdapterInterface $adapter, StorageInterface $storage) {
        parent::__construct($adapter, $storage);
        $this->initializeRepository(TravelApproveRepository::class);
        $this->initializeForm(TravelRequestForm::class);
    }

    public function indexAction() {
        $empId = $this->employeeId;

        $expenseDtlRepo = new TravelExpenseDtlRepository($this->adapter);
        $result = $expenseDtlRepo->fetchDesignation($empId);
        // echo '<pre>';print($result); die;
        $request = $this->getRequest();
        if ($request->isPost()) {
            try {
                $search['employeeId'] = $this->employeeId;
                $search['status'] = ['RQ', 'RC'];
                $rawList = $this->repository->getPendingList($this->employeeId);
                $list = Helper::extractDbData($rawList);
                // echo '<pre>'; print_r($list); die;
                return new JsonModel(['success' => true, 'data' => $list, 'error' => '']);
            } catch (Exception $e) {
                return new JsonModel(['success' => false, 'data' => [], 'error' => $e->getMessage()]);
            }
        }
        return Helper::addFlashMessagesToArray($this, [
            
            'designation' =>$result['DESIGNATION_ID']
            //'files' => $filesData
        ]);
    }

    public function expenseIndexAction() {
        $empId = $this->employeeId;

        $expenseDtlRepo = new TravelExpenseDtlRepository($this->adapter);
        $result = $expenseDtlRepo->fetchDesignation($empId);
        // echo '<pre>';print($result); die;
        $request = $this->getRequest();
        if ($request->isPost()) {
            try {
                $search['employeeId'] = $this->employeeId;
                $search['status'] = ['RQ', 'RC'];
                $rawList = $this->repository->getPendingListExpense($this->employeeId);
                $list = Helper::extractDbData($rawList);
                // echo '<pre>'; print_r($list); die;
                return new JsonModel(['success' => true, 'data' => $list, 'error' => '']);
            } catch (Exception $e) {
                return new JsonModel(['success' => false, 'data' => [], 'error' => $e->getMessage()]);
            }
        }
        return Helper::addFlashMessagesToArray($this, [
            
            'designation' =>$result['DESIGNATION_ID']
            //'files' => $filesData
        ]);
    }

    public function viewAction() {
        // var_dump('sdcsd'); die;
        $id = (int) $this->params()->fromRoute('id');
        $role = $this->params()->fromRoute('role');

        if ($id === 0 || $role === 0) {
            return $this->redirect()->toRoute("travelApprove");
        }
        // var_dump($role); die;
        if ($role == 'RP') {
            $role = 5;
        }
        $request = $this->getRequest();
        $travelRequestModel = new TravelRequest();
        $detail = $this->repository->fetchById($id);
        // echo '<pre>'; print_r($detail); die;
        $travelRequestModel->exchangeArrayFromDB($detail);
        //$filesData = $this->repository->fetchAttachmentsById($id);
        
        if ($request->isPost()) {

            $postedData = (array) $request->getPost();
            
            if ($postedData['requestedAmount'] != 0 || $postedData['requestedAmount'] != '' || $postedData['requestedAmount'] != null) {
                $requestedamount = $this->repository->insertRequestedAmount($id,$postedData['requestedAmount']);
            }
            $action = $postedData['submit'];
            // var_dump($action); die;
            if (trim($detail['TRAVEL_TYPE']) == 'ITR') {
                if ($detail['STATUS'] == 'RQ' || $detail['STATUS'] == 'RC') {
                    $this->makeDecision2($id, $role, $action, $postedData[$role == 2 ? $postedData['recommendedRemarks'] : $postedData['approvedRemarks'] ], true);
                } elseif ($detail['STATUS'] == 'A2') {
                    $this->makeDecision3($id, $role, $action, $postedData[$role == 2 ? $postedData['recommendedRemarks'] : $postedData['approvedRemarks']], true);
                }elseif ($detail['STATUS'] == 'A3') {
                    $this->makeDecision4($id, $role, $action, $postedData[$role == 2 ? $postedData['recommendedRemarks'] : $postedData['approvedRemarks']], true);
                }else{
                    $this->makeDecision5($id, $role, $action, $postedData[$role == 2 ? $postedData['recommendedRemarks'] : $postedData['approvedRemarks']], true);
                }
            }else {
                if ($detail['REQUESTED_TYPE'] == 'ep') {
                    if(isset($this->preference['travelSingleApprover']) && $this->preference['travelSingleApprover'] == 'Y'){
                        $this->makeDecisionTravel($id, $role, $action, $postedData[$role == 2 ? $postedData['recommendedRemarks'] : $postedData['approvedRemarks']], true);
                    }else{
                        $this->makeDecision($id, $role, $action, $postedData[$role == 2 ? $postedData['recommendedRemarks'] : $postedData['approvedRemarks']], true);
                    }
                } else {
                    // var_dump('here5'); die;
                    $this->makeDecision($id, $role, $action, $postedData[$role == 2 ? $postedData['recommendedRemarks'] : $postedData['approvedRemarks']], true);
                }
            }
           
            return $this->redirect()->toRoute("travelApprove");
        }


        $this->form->bind($travelRequestModel);

        $numberInWord = new NumberHelper();
        $advanceAmount = $numberInWord->toText($detail['REQUESTED_AMOUNT']);
        // echo '<pre>'; print_r($detail); die;
        $newRepo = new NewTravelRequestRepository($this->adapter);
        $fileDetails = $newRepo->fetchFilesById($id);
        return Helper::addFlashMessagesToArray($this, [
                    'id' => $id,
                    'role' => $role,
                    'form' => $this->form,
                    'recommender' => $detail['RECOMMENDED_BY_NAME'] == null ? $detail['RECOMMENDER_NAME'] : $detail['RECOMMENDED_BY_NAME'],
                    'approver' => $detail['APPROVED_BY_NAME'] == null ? $detail['APPROVER_NAME'] : $detail['APPROVED_BY_NAME'],
                    'detail' => $detail,
                    'todayDate' => date('d-M-Y'),
                    'advanceAmount' => $advanceAmount,
                    'filesnew' => $fileDetails
        ]);
    }

    public function expenseDetailAction() {
        $id = (int) $this->params()->fromRoute('id');
        $role = $this->params()->fromRoute('role');

        if ($id === 0) {
            return $this->redirect()->toRoute("travelApprove", ['action'=>'expenseIndex']);
        }
        // if ($role = 'RP') {
        //     $role = 4;
        // }
        $detail = $this->repository->fetchById($id);

        $authRecommender = $detail['RECOMMENDED_BY_NAME'] == null ? $detail['RECOMMENDER_NAME'] : $detail['RECOMMENDED_BY_NAME'];
        $authApprover = $detail['APPROVED_BY_NAME'] == null ? $detail['APPROVER_NAME'] : $detail['APPROVED_BY_NAME'];
        $recommenderId = $detail['RECOMMENDED_BY'] == null ? $detail['RECOMMENDER_ID'] : $detail['RECOMMENDED_BY'];


        $expenseDtlRepo = new TravelExpenseDtlRepository($this->adapter);
        $result = $expenseDtlRepo->fetchByTravelId($id);
        // echo '<pre>'; print_r($result); die;
        $totalAmount = 0;
      
        $transportType = [
            "AP" => "Aeroplane",
            "OV" => "Office Vehicles",
            "TI" => "Taxi",
            "BS" => "Bus",
            "OF"  => "On Foot"
        ];
        $numberInWord = new NumberHelper();
        $totalAmountSum =  $expenseDtlRepo->sumTotalAmount($id);
        // print_r($totalAmountSum);die;
        $totalExpenseInWords = $numberInWord->toText($totalAmountSum['TOTAL']);
        // echo '<pre>'; print_r($result); die;
        $newreqamt = $expenseDtlRepo->fetchRequestedAmountP($detail['REFERENCE_TRAVEL_ID']);
        // echo '<pre>'; print_r($newreqamt['REQUESTED_AMOUNT']); die;
        $detail['REQUESTED_AMOUNT'] = $newreqamt['REQUESTED_AMOUNT'];
        return Helper::addFlashMessagesToArray($this, [
                    'form' => $this->form,
                    'id' => $id,
                    'role' => $role,
                    'recommender' => $authRecommender,
                    'approver' => $authApprover,
                    'recommendedBy' => $recommenderId,
                    'employeeId' => $this->employeeId,
                    'expenseDtlList' => $result,
                    'transportType' => $transportType,
                    'todayDate' => date('d-M-Y'),
                    'detail' => $detail,
                    'totalAmount' => $totalAmountSum,
                    'totalAmountInWords' => $totalExpenseInWords,
                        ]
        );
    }

    public function statusAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            try {
                $searchQuery = $request->getPost();
                $searchQuery['employeeId'] = $this->employeeId;
                $rawList = $this->repository->getAllFilteredA((array) $searchQuery);
                $list = Helper::extractDbData($rawList);
                return new JsonModel(['success' => true, 'data' => $list, 'error' => '']);
            } catch (Exception $e) {
                return new JsonModel(['success' => false, 'data' => [], 'error' => $e->getMessage()]);
            }
        }
        $statusSE = $this->getStatusSelectElement(['name' => 'status', 'id' => 'status', 'class' => 'form-control reset-field', 'label' => 'Status']);
        return $this->stickFlashMessagesTo([
                    'travelStatus' => $statusSE,
                    'recomApproveId' => $this->employeeId,
                    'searchValues' => EntityHelper::getSearchData($this->adapter),
        ]);
    }

    public function statuseAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            try {
                $searchQuery = $request->getPost();
                $searchQuery['employeeId'] = $this->employeeId;
                $rawList = $this->repository->getAllFilteredE((array) $searchQuery);
                $list = Helper::extractDbData($rawList);

                // echo '<pre>'; print_r($list);die;
                return new JsonModel(['success' => true, 'data' => $list, 'error' => '']);
            } catch (Exception $e) {
                return new JsonModel(['success' => false, 'data' => [], 'error' => $e->getMessage()]);
            }
        }
        $statusSE = $this->getStatusSelectElement(['name' => 'status', 'id' => 'status', 'class' => 'form-control reset-field', 'label' => 'Status']);
        return $this->stickFlashMessagesTo([
                    'travelStatus' => $statusSE,
                    'recomApproveId' => $this->employeeId,
                    'searchValues' => EntityHelper::getSearchData($this->adapter),
        ]);
    }

    public function batchApproveRejectAction() {
        $request = $this->getRequest();
        try {
            $postData = $request->getPost();
            $this->makeDecision($postData['id'], $postData['role'], $postData['btnAction'] == "btnApprove");
            return new JsonModel(['success' => true, 'data' => null]);
        } catch (Exception $e) {
            return new JsonModel(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    private function makeDecision($id, $role, $approve, $remarks = null, $enableFlashNotification = false) {
        // 
        $notificationEvent = null;
        $message = null;
        $model = new TravelRequest();
        $model->travelId = $id;
        // var_dump($role); die;
        switch ($role) {
            case 2:
                $model->recommendedRemarks = $remarks;
                $model->recommendedDate = Helper::getcurrentExpressionDate();
                $model->recommendedBy = $this->employeeId;
                $model->status = ($approve == 'Approve') ? "RC" : "R";
                $message = $approve ? "Travel Request Recommended" : "Travel Request Rejected";
                $notificationEvent = $approve ? NotificationEvents::TRAVEL_RECOMMEND_ACCEPTED : NotificationEvents::TRAVEL_RECOMMEND_REJECTED;
                break;
            case 4:
                $model->approvedRemarks = $remarks;
                $model->approvedDate = Helper::getcurrentExpressionDate();
                $model->approvedBy = $this->employeeId;
                $model->recommendedDate = Helper::getcurrentExpressionDate();
                $model->recommendedBy = $this->employeeId;
                $model->status = ($approve == 'Approve') ? "AP" : "R";
                $notificationEvent = $approve ? NotificationEvents::TRAVEL_APPROVE_ACCEPTED : NotificationEvents::TRAVEL_APPROVE_REJECTED;
                break;
            case 3:
                $model->approvedRemarks = $remarks;
                $model->approvedDate = Helper::getcurrentExpressionDate();
                $model->approvedBy = $this->employeeId;
                $model->status = ($approve == 'Approve') ? "AP" : "R";
                $message = $approve ? "Travel Request Approved" : "Travel Request Rejected";
                $notificationEvent = $approve ? NotificationEvents::TRAVEL_APPROVE_ACCEPTED : NotificationEvents::TRAVEL_APPROVE_REJECTED;
                break;
        } 
        $editError=$this->repository->edit($model, $id);
        if ($enableFlashNotification) {
            $this->flashmessenger()->addMessage($message);
            $this->flashmessenger()->addMessage($editError);
        }
        try {
            HeadNotification::pushNotification($notificationEvent, $model, $this->adapter, $this);
        } catch (Exception $e) {
            $this->flashmessenger()->addMessage($e->getMessage());
        }
    }

    private function makeDecisionTravel($id, $role, $approve, $remarks = null, $enableFlashNotification = false) {
        // 
        $notificationEvent = null;
        $message = null;
        $model = new TravelRequest();
        $model->travelId = $id;
        // var_dump($role); die;
        switch ($role) {
            case 2:
                $model->approvedRemarks = $remarks;
                $model->approvedDate = Helper::getcurrentExpressionDate();
                $model->approvedBy = $this->employeeId;
                $model->recommendedDate = Helper::getcurrentExpressionDate();
                $model->recommendedBy = $this->employeeId;
                $model->status = ($approve == 'Approve') ? "AP" : "R";
                $notificationEvent = $approve ? NotificationEvents::TRAVEL_APPROVE_ACCEPTED : NotificationEvents::TRAVEL_APPROVE_REJECTED;
                break;
            case 4:
                $model->approvedRemarks = $remarks;
                $model->approvedDate = Helper::getcurrentExpressionDate();
                $model->approvedBy = $this->employeeId;
                $model->recommendedDate = Helper::getcurrentExpressionDate();
                $model->recommendedBy = $this->employeeId;
                $model->status = ($approve == 'Approve') ? "AP" : "R";
                $notificationEvent = $approve ? NotificationEvents::TRAVEL_APPROVE_ACCEPTED : NotificationEvents::TRAVEL_APPROVE_REJECTED;
                break;
            case 3:
                $model->approvedRemarks = $remarks;
                $model->approvedDate = Helper::getcurrentExpressionDate();
                $model->approvedBy = $this->employeeId;
                $model->recommendedDate = Helper::getcurrentExpressionDate();
                $model->recommendedBy = $this->employeeId;
                $model->status = ($approve == 'Approve') ? "AP" : "R";
                $notificationEvent = $approve ? NotificationEvents::TRAVEL_APPROVE_ACCEPTED : NotificationEvents::TRAVEL_APPROVE_REJECTED;
                break;
        } 
        $editError=$this->repository->edit($model, $id);
        if ($enableFlashNotification) {
            $this->flashmessenger()->addMessage($message);
            $this->flashmessenger()->addMessage($editError);
        }
        try {
            HeadNotification::pushNotification($notificationEvent, $model, $this->adapter, $this);
        } catch (Exception $e) {
            $this->flashmessenger()->addMessage($e->getMessage());
        }
    }


    public function travelExpenseToApproveAction()
    {
        $empId = $this->employeeId;
        $expenseDtlRepo = new TravelExpenseDtlRepository($this->adapter);
        $result = $expenseDtlRepo->fetchDesignation($empId);
        $request = $this->getRequest();
        if ($request->isPost()) {
            try {
                $rawList = $this->repository->getPendingExpenseList();
                $list = Helper::extractDbData($rawList);
                // echo '<pre>'; print_r($list); die;
                return new JsonModel(['success' => true, 'data' => $list, 'error' => '']);
            } catch (Exception $e) {
                return new JsonModel(['success' => false, 'data' => [], 'error' => $e->getMessage()]);
            }
        }
        return Helper::addFlashMessagesToArray($this, [
            
            'designation' =>$result['DESIGNATION_ID']
            //'files' => $filesData
        ]);
    }
    public function travelExpenseApprovedAction()
    {
        $empId = $this->employeeId;
        $expenseDtlRepo = new TravelExpenseDtlRepository($this->adapter);
        $result = $expenseDtlRepo->fetchDesignation($empId);
        $request = $this->getRequest();
        if ($request->isPost()) {
            try {
                $rawList = $this->repository->getApprovedExpenseList();
                $list = Helper::extractDbData($rawList);
                // echo '<pre>'; print_r($list); die;
                return new JsonModel(['success' => true, 'data' => $list, 'error' => '']);
            } catch (Exception $e) {
                return new JsonModel(['success' => false, 'data' => [], 'error' => $e->getMessage()]);
            }
        }
        return Helper::addFlashMessagesToArray($this, [
            
            'designation' =>$result['DESIGNATION_ID']
            //'files' => $filesData
        ]);
    }
    private function makeDecision0($id, $role, $approve, $remarks = null, $enableFlashNotification = false) {
        $notificationEvent = null;
        $message = null;
        $model = new TravelRequest();
        $model->travelId = $id;
        
        switch ($role) {
            case 2:
                $model->recommendedRemarks = $remarks;
                $model->recommendedDate = Helper::getcurrentExpressionDate();
                $model->recommendedBy = $this->employeeId;
                $model->status = ($approve == 'Approve') ? "RC" : "R";
                $message = $approve ? "Travel Expense Request" : "Travel Request Rejected";
                $notificationEvent = $approve ? NotificationEvents::TRAVEL_RECOMMEND_ACCEPTED : NotificationEvents::TRAVEL_RECOMMEND_REJECTED;
                break;
            case 4:
                
                $model->approvedRemarks = $remarks;
                $model->approvedDate = Helper::getcurrentExpressionDate();
                $model->approvedBy = $this->employeeId;
                $model->status = ($approve == 'Approve')? "RP" : "R";
                $message = $approve ? "Travel Expense Request" : "Travel Request Rejected";
                $notificationEvent = $approve ? NotificationEvents::TRAVEL_EXPENSE_APPROVE_ACCEPTED : NotificationEvents::TRAVEL_APPROVE_REJECTED;
                break;
            case 3:
                $model->approvedRemarks = $remarks;
                $model->approvedDate = Helper::getcurrentExpressionDate();
                $model->approvedBy = $this->employeeId;
                $model->status = $approve ? "RP" : "R";
                $message = $approve ? "Travel Expense Request" : "Travel Request Rejected";
                $notificationEvent = $approve ? NotificationEvents::TRAVEL_EXPENSE_APPROVE_ACCEPTED : NotificationEvents::TRAVEL_APPROVE_REJECTED;
                break;
            case 5:
                $model->approvedRemarks = $remarks;
                $model->approvedDate = Helper::getcurrentExpressionDate();
                $model->approvedBy = $this->employeeId;
                $model->status = ($approve == 'Approve') ? "AP" : "R";
                $message = $approve ? "Travel Request Approved" : "Travel Request Rejected";
                $notificationEvent = $approve ? NotificationEvents::TRAVEL_APPROVE_ACCEPTED : NotificationEvents::TRAVEL_APPROVE_REJECTED;
                break;
        } 
        $editError=$this->repository->edit($model, $id);
        if ($enableFlashNotification) {
            $this->flashmessenger()->addMessage($message);
            $this->flashmessenger()->addMessage($editError);
        }
        try {
            HeadNotification::pushNotification($notificationEvent, $model, $this->adapter, $this);
        } catch (Exception $e) {
            $this->flashmessenger()->addMessage($e->getMessage());
        }
    }
    private function makeDecision1($id, $role, $approve, $remarks = null, $enableFlashNotification = false) {
        $notificationEvent = null;
        $message = null;
        $model = new TravelRequest();
        $model->travelId = $id;
        // var_dump($role); die;
        switch ($role) {
            case 2:
                $model->recommendedRemarks = $remarks;
                $model->recommendedDate = Helper::getcurrentExpressionDate();
                $model->recommendedBy = $this->employeeId;
                $model->status = ($approve == 'Approve') ? "RC" : "R";
                $message = $approve ? "Travel Request Recommended" : "Travel Request Rejected";
                $notificationEvent = $approve ? NotificationEvents::TRAVEL_RECOMMEND_ACCEPTED : NotificationEvents::TRAVEL_RECOMMEND_REJECTED;
                break;
            case 4:
                $model->recommendedDate = Helper::getcurrentExpressionDate();
                $model->recommendedBy = $this->employeeId;
                $model->status = ($approve == 'Approve') ? "AP" : "R";
                $message = $approve ? "Travel Request Approved" : "Travel Request Rejected";
                $notificationEvent = $approve ? NotificationEvents::TRAVEL_APPROVE_ACCEPTED : NotificationEvents::TRAVEL_APPROVE_REJECTED;
                break;
            case 3:
                $model->approvedRemarks = $remarks;
                $model->approvedDate = Helper::getcurrentExpressionDate();
                $model->approvedBy = $this->employeeId;
                $model->status = $approve ? "RP" : "R";
                $message = $approve ? "Travel Request Approved" : "Travel Request Rejected";
                $notificationEvent = $approve ? NotificationEvents::TRAVEL_APPLIED : NotificationEvents::TRAVEL_APPROVE_REJECTED;
                break;
        } 
        $editError=$this->repository->edit($model, $id);
        if ($enableFlashNotification) {
            $this->flashmessenger()->addMessage($message);
            $this->flashmessenger()->addMessage($editError);
        }
        try {
            HeadNotification::pushNotification($notificationEvent, $model, $this->adapter, $this);
        } catch (Exception $e) {
            $this->flashmessenger()->addMessage($e->getMessage());
        }
    }
    private function makeDecision2($id, $role, $approve, $remarks = null, $enableFlashNotification = false) {
        $notificationEvent = null;
        $message = null;
        $model = new TravelRequest();
        $model->travelId = $id;
        // var_dump($role); die;
        switch ($role) {
            case 2:
                $model->recommendedRemarks = $remarks;
                $model->recommendedDate = Helper::getcurrentExpressionDate();
                $model->recommendedBy = $this->employeeId;
                $model->status = ($approve == 'Approve') ? "RC" : "R";
                $message = $approve ? "Travel Request Recommended" : "Travel Request Rejected";
                $notificationEvent = $approve ? NotificationEvents::TRAVEL_RECOMMEND_ACCEPTED : NotificationEvents::TRAVEL_RECOMMEND_REJECTED;
                break;
            case 4:
                $model->approvedRemarks = $remarks;
                $model->approvedDate = Helper::getcurrentExpressionDate();
                $model->approvedBy = $this->employeeId;
                $model->status = ($approve == 'Approve') ? "A2" : "R";
                $message = $approve ? "Travel Request Accepted" : "Travel Request Rejected";
                $notificationEvent = $approve ? NotificationEvents::TRAVEL_ACCEPTED_THIRD : NotificationEvents::TRAVEL_RECOMMEND_REJECTED;
                break;
            case 3:
                $model->approvedRemarks = $remarks;
                $model->approvedDate = Helper::getcurrentExpressionDate();
                $model->approvedBy = $this->employeeId;
                $model->status = $approve ? "A2" : "R";
                $message = $approve ? "Travel Request Approved" : "Travel Request Rejected";
                $notificationEvent = $approve ? NotificationEvents::TRAVEL_ACCEPTED_THIRD : NotificationEvents::TRAVEL_APPROVE_REJECTED;
                break;
        } 
        $editError=$this->repository->edit($model, $id);
        if ($enableFlashNotification) {
            $this->flashmessenger()->addMessage($message);
            $this->flashmessenger()->addMessage($editError);
        }
        try {
            HeadNotification::pushNotification($notificationEvent, $model, $this->adapter, $this);
        } catch (Exception $e) {
            $this->flashmessenger()->addMessage($e->getMessage());
        }
    }
    private function makeDecision3($id, $role, $approve, $remarks = null, $enableFlashNotification = false) {
        $notificationEvent = null;
        $message = null;
        $model = new TravelRequest();
        $model->travelId = $id;
        // var_dump($role); die;
        switch ($role) {
            case 2:
                $model->recommendedRemarks = $remarks;
                $model->recommendedDate = Helper::getcurrentExpressionDate();
                $model->recommendedBy = $this->employeeId;
                $model->status = ($approve == 'Approve') ? "RC" : "R";
                $message = $approve ? "Travel Request Recommended" : "Travel Request Rejected";
                $notificationEvent = $approve ? NotificationEvents::TRAVEL_RECOMMEND_ACCEPTED : NotificationEvents::TRAVEL_RECOMMEND_REJECTED;
                break;
            case 4:
                $model->recommendedDate = Helper::getcurrentExpressionDate();
                $model->recommendedBy = $this->employeeId;
                $model->status = ($approve == 'Approve') ? "A3" : "R";
                $message = $approve ? "Travel Request Approved" : "Travel Request Rejected";
                $notificationEvent = $approve ? NotificationEvents::TRAVEL_ACCEPTED_FOURTH : NotificationEvents::TRAVEL_RECOMMEND_REJECTED;
                break;
            case 3:
                $model->approvedRemarks = $remarks;
                $model->approvedDate = Helper::getcurrentExpressionDate();
                $model->approvedBy = $this->employeeId;
                $model->status = $approve ? "A3" : "R";
                $message = $approve ? "Travel Request Approved" : "Travel Request Rejected";
                $notificationEvent = $approve ? NotificationEvents::TRAVEL_APPLIED : NotificationEvents::TRAVEL_APPROVE_REJECTED;
                break;
        } 
        $editError=$this->repository->edit($model, $id);
        if ($enableFlashNotification) {
            $this->flashmessenger()->addMessage($message);
            $this->flashmessenger()->addMessage($editError);
        }
        try {
            // HeadNotification::pushNotification($notificationEvent, $model, $this->adapter, $this);
        } catch (Exception $e) {
            $this->flashmessenger()->addMessage($e->getMessage());
        }
    }
    private function makeDecision4($id, $role, $approve, $remarks = null, $enableFlashNotification = false) {
        $notificationEvent = null;
        $message = null;
        $model = new TravelRequest();
        $model->travelId = $id;
        // var_dump(NotificationEvents::TRAVEL_ACCEPTED_FIFTH); die;
        switch ($role) {
            case 2:
                $model->recommendedRemarks = $remarks;
                $model->recommendedDate = Helper::getcurrentExpressionDate();
                $model->recommendedBy = $this->employeeId;
                $model->status = ($approve == 'Approve') ? "RC" : "R";
                $message = $approve ? "Travel Request Recommended" : "Travel Request Rejected";
                $notificationEvent = $approve ? NotificationEvents::TRAVEL_RECOMMEND_ACCEPTED : NotificationEvents::TRAVEL_RECOMMEND_REJECTED;
                break;
            case 4:
                $model->recommendedDate = Helper::getcurrentExpressionDate();
                $model->recommendedBy = $this->employeeId;
                $model->status = ($approve == 'Approve') ? "A4" : "R";
                $message = $approve ? "Travel Request Approved" : "Travel Request Rejected";
                $notificationEvent = $approve ? NotificationEvents::TRAVEL_ACCEPTED_FIFTH : NotificationEvents::TRAVEL_RECOMMEND_REJECTED;
                break;
            case 3:
                $model->approvedRemarks = $remarks;
                $model->approvedDate = Helper::getcurrentExpressionDate();
                $model->approvedBy = $this->employeeId;
                $model->status = $approve ? "A4" : "R";
                $message = $approve ? "Travel Request Approved" : "Travel Request Rejected";
                $notificationEvent = $approve ? NotificationEvents::TRAVEL_APPLIED : NotificationEvents::TRAVEL_APPROVE_REJECTED;
                break;
        } 
        $editError=$this->repository->edit($model, $id);
        if ($enableFlashNotification) {
            $this->flashmessenger()->addMessage($message);
            $this->flashmessenger()->addMessage($editError);
        }
       
        try {
            // HeadNotification::pushNotification($notificationEvent, $model, $this->adapter, $this);
        } catch (Exception $e) {
            $this->flashmessenger()->addMessage($e->getMessage());
        }
        if ($enableFlashNotification) {
            $this->flashmessenger()->addMessage($message);
            $this->flashmessenger()->addMessage($editError);
        }
    }
    private function makeDecision5($id, $role, $approve, $remarks = null, $enableFlashNotification = false) {
        $notificationEvent = null;
        $message = null;
        $model = new TravelRequest();
        $model->travelId = $id;
        // var_dump(NotificationEvents::TRAVEL_APPROVE_SIXTH); die;
        switch ($role) {
            case 2:
                $model->recommendedRemarks = $remarks;
                $model->recommendedDate = Helper::getcurrentExpressionDate();
                $model->recommendedBy = $this->employeeId;
                $model->status = ($approve == 'Approve') ? "RC" : "R";
                $message = $approve ? "Travel Request Recommended" : "Travel Request Rejected";
                $notificationEvent = $approve ? NotificationEvents::TRAVEL_RECOMMEND_ACCEPTED : NotificationEvents::TRAVEL_RECOMMEND_REJECTED;
                break;
            case 4:
                $model->recommendedDate = Helper::getcurrentExpressionDate();
                $model->recommendedBy = $this->employeeId;
                $model->status = ($approve == 'Approve') ? "AP" : "R";
                $message = $approve ? "Travel Request Approved" : "Travel Request Rejected";
                $notificationEvent = $approve ? NotificationEvents::TRAVEL_APPROVE_SIXTH : NotificationEvents::TRAVEL_APPROVE_REJECTED;
                break;
            case 3:
                $model->approvedRemarks = $remarks;
                $model->approvedDate = Helper::getcurrentExpressionDate();
                $model->approvedBy = $this->employeeId;
                $model->status = ($approve == 'Approve') ? "AP" : "R";
                $message = $approve ? "Travel Request Approved" : "Travel Request Rejected";
                $notificationEvent = $approve ? NotificationEvents::TRAVEL_APPROVE_ACCEPTED : NotificationEvents::TRAVEL_APPROVE_REJECTED;
                break;
        } 
        $editError=$this->repository->edit($model, $id);
        if ($enableFlashNotification) {
            $this->flashmessenger()->addMessage($message);
            $this->flashmessenger()->addMessage($editError);
        }
        try {
            HeadNotification::pushNotification($notificationEvent, $model, $this->adapter, $this);
        } catch (Exception $e) {
            $this->flashmessenger()->addMessage($e->getMessage());
        }
    }
    public function itrToApproveAction()
    {
        $eid = $this->employeeId;
        $expenseDtlRepo = new TravelExpenseDtlRepository($this->adapter);
        $result = $expenseDtlRepo->fetchDesignation($eid);
        // var_dump($result['DESIGNATION_ID']); die;
        // $rawList = $this->repository->getPendingInternationalList($result['DESIGNATION_ID']);
        // $list = Helper::extractDbData($rawList);
        // echo '<pre>'; print_r($list); die;
        $request = $this->getRequest();
        if ($request->isPost()) {
            try {
                $rawList = $this->repository->getPendingInternationalList($result['DESIGNATION_ID']);
                $list = Helper::extractDbData($rawList);
                // echo '<pre>'; print_r($list); die;
                return new JsonModel(['success' => true, 'data' => $list, 'error' => '']);
            } catch (Exception $e) {
                return new JsonModel(['success' => false, 'data' => [], 'error' => $e->getMessage()]);
            }
        }
    }
    public function approveViewAction() {
        $id = (int) $this->params()->fromRoute('id');
        $role = $this->params()->fromRoute('role');

        if ($id === 0 || $role === 0) {
            return $this->redirect()->toRoute("travelApprove");
        }
        // var_dump($role); die;
        if ($role != '4') {
            $role = '4';
        }
        $request = $this->getRequest();
        $travelRequestModel = new TravelRequest();
        $detail = $this->repository->fetchById($id);
        // echo '<pre>'; print_r($detail); die;
        $travelRequestModel->exchangeArrayFromDB($detail);
        //$filesData = $this->repository->fetchAttachmentsById($id);
        $this->form->bind($travelRequestModel);

        $numberInWord = new NumberHelper();
        $advanceAmount = $numberInWord->toText($detail['REQUESTED_AMOUNT']);
        $newRepo = new NewTravelRequestRepository($this->adapter);
        $fileDetails = $newRepo->fetchFilesById($id);
        return Helper::addFlashMessagesToArray($this, [
                    'id' => $id,
                    'role' => $role,
                    'form' => $this->form,
                    'recommender' => $detail['RECOMMENDED_BY_NAME'] == null ? $detail['RECOMMENDER_NAME'] : $detail['RECOMMENDED_BY_NAME'],
                    'approver' => $detail['APPROVED_BY_NAME'] == null ? $detail['APPROVER_NAME'] : $detail['APPROVED_BY_NAME'],
                    'detail' => $detail,
                    'todayDate' => date('d-M-Y'),
                    'advanceAmount' => $advanceAmount,
                    'filesnew' => $fileDetails
        ]);
    }
    public function expenseDetailApproveAction() {
        $id = (int) $this->params()->fromRoute('id');
        $role = $this->params()->fromRoute('role');

        if ($id === 0) {
            return $this->redirect()->toRoute("travelApprove");
        }
        if ($role = 'RP') {
            $role = 4;
        }
        $detail = $this->repository->fetchById($id);

        $authRecommender = $detail['RECOMMENDED_BY_NAME'] == null ? $detail['RECOMMENDER_NAME'] : $detail['RECOMMENDED_BY_NAME'];
        $authApprover = $detail['APPROVED_BY_NAME'] == null ? $detail['APPROVER_NAME'] : $detail['APPROVED_BY_NAME'];
        $recommenderId = $detail['RECOMMENDED_BY'] == null ? $detail['RECOMMENDER_ID'] : $detail['RECOMMENDED_BY'];


        $expenseDtlRepo = new TravelExpenseDtlRepository($this->adapter);
        $result = $expenseDtlRepo->fetchByTravelId($id);
        $totalAmount = 0;
      
        $transportType = [
            "AP" => "Aeroplane",
            "OV" => "Office Vehicles",
            "TI" => "Taxi",
            "BS" => "Bus",
            "OF"  => "On Foot"
        ];
        $numberInWord = new NumberHelper();
        $totalAmountSum =  $expenseDtlRepo->sumTotalAmount($id);
        $totalExpenseInWords = $numberInWord->toText($totalAmountSum['TOTAL']);
        
        $newreqamt = $expenseDtlRepo->fetchRequestedAmountP($detail['REFERENCE_TRAVEL_ID']);
        // echo '<pre>'; print_r($newreqamt['REQUESTED_AMOUNT']); die;
        $detail['REQUESTED_AMOUNT'] = $newreqamt['REQUESTED_AMOUNT'];
        return Helper::addFlashMessagesToArray($this, [
                    'form' => $this->form,
                    'id' => $id,
                    'role' => $role,
                    'recommender' => $authRecommender,
                    'approver' => $authApprover,
                    'recommendedBy' => $recommenderId,
                    'employeeId' => $this->employeeId,
                    'expenseDtlList' => $result,
                    'transportType' => $transportType,
                    'todayDate' => date('d-M-Y'),
                    'detail' => $detail,
                    'totalAmount' => $totalAmountSum['TOTAL'],
                    'totalAmountInWords' => $totalExpenseInWords,
                        ]
        );
    }

}
