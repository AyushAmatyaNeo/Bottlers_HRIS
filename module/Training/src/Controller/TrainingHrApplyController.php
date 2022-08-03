<?php

namespace Training\Controller;

use Application\Controller\HrisController;
use Application\Custom\CustomViewModel;
use Application\Helper\EntityHelper;
use Application\Helper\Helper;
use Application\Helper\NumberHelper;
use Exception;
use Notification\Controller\HeadNotification;
use Notification\Model\NotificationEvents;
use SelfService\Form\TrainingRequestForm;
use SelfService\Model\TrainingRequest as TrainingRequestModel;
use Setup\Repository\TrainingRepository;
use SelfService\Repository\TrainingRequestRepository;
use Zend\Authentication\AuthenticationService;
use Setup\Model\HrEmployees;
use Zend\Authentication\Storage\StorageInterface;
use Zend\Db\Adapter\AdapterInterface;
use Zend\View\Model\JsonModel;


class TrainingHrApplyController extends HrisController {

    public function __construct(AdapterInterface $adapter, StorageInterface $storage) {
        parent::__construct($adapter, $storage);
        $this->initializeRepository(TrainingRequestRepository::class);
        $this->initializeForm(TrainingRequestForm::class);
        $auth = new AuthenticationService();
        $this->employeeId = $auth->getStorage()->read()['employee_id'];
        $this->preference = $auth->getStorage()->read()['preference'];
    }

    public function indexAction() {
        $request = $this->getRequest();
        $model = new TrainingRequestModel();

        if ($request->isPost()) {
            $postData = $request->getPost();
            $this->form->setData($postData);

            if ($this->form->isValid()) {
                $model->exchangeArrayFromForm($this->form->getData());
                if ($postData['trainingId'] == -1) {
                    $model->trainingId = null;
                }
                $model->requestId = ((int) Helper::getMaxId($this->adapter, TrainingRequestModel::TABLE_NAME, TrainingRequestModel::REQUEST_ID)) + 1;
                $model->employeeId = $this->employeeId;
                $model->requestedDate = Helper::getcurrentExpressionDate();
                $model->status = 'RQ';

                $this->repository->add($model);
                // var_dump('fbd');die;

                $this->flashmessenger()->addMessage("Training Request Successfully added!!!");
                try {
                    HeadNotification::pushNotification(NotificationEvents::TRAINING_APPLIED, $model, $this->adapter, $this);
                } catch (Exception $e) {
                    $this->flashmessenger()->addMessage($e->getMessage());
                }
                return $this->redirect()->toRoute("trainingStatus");
            }
        }
        $this->prepareForm();
        $trainings = $this->getTrainingList($this->employeeId);
        // echo '<pre>';print_r($trainings );die;

        return Helper::addFlashMessagesToArray($this, [
                    'form' => $this->form,
                    'trainingList' => $trainings['trainingList'],
                    'employees' => EntityHelper::getTableKVListWithSortOption($this->adapter, "HRIS_EMPLOYEES", "EMPLOYEE_ID", ["EMPLOYEE_CODE", "FULL_NAME"], ["STATUS" => 'E', 'RETIRED_FLAG' => 'N'], "FULL_NAME", "ASC", "-", false, true, $this->employeeId),
                    'customRenderer' => Helper::renderCustomView()
        ]);
    }
    public function prepareForm() {
        $trainingList = $this->getTrainingList($this->employeeId);

        $trainingId = $this->form->get('trainingId');
        $trainingId->setValueOptions($trainingList['trainingKVList']);

        $trainingType = $this->form->get('trainingType');
        $trainingType->setValueOptions($this->trainingTypes);
    }
    private function getTrainingList($employeeId) {
        // echo '<pre>';print_r('cbvcb');die;
        if ($this->trainingList === null) {
            $trainingRepo = new TrainingRepository($this->adapter);
            $trainingResult = $trainingRepo->selectAll($employeeId);
            $trainingList = [];
            $allTrainings = [];
            $trainingList[''] = "---";
            foreach ($trainingResult as $trainingRow) {
                $trainingList[$trainingRow['TRAINING_ID']] = $trainingRow['TRAINING_NAME'] . " (" . $trainingRow['START_DATE'] . " to " . $trainingRow['END_DATE'] . ")";
                $allTrainings[$trainingRow['TRAINING_ID']] = $trainingRow;
            }
            $this->trainingList = ['trainingKVList' => $trainingList, 'trainingList' => $allTrainings];
        }
        return $this->trainingList;
    }
    
    private $trainingList = null;
    private $trainingTypes = array(
        'CP' => 'Personal',
        'CC' => 'Company Contribution'
    );
}