<?php

namespace SelfService\Controller;

use Application\Controller\HrisController;
use Application\Helper\Helper;
use Exception;
use SelfService\Repository\HolidayRepository;
use Zend\Authentication\Storage\StorageInterface;
use Zend\Db\Adapter\AdapterInterface;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Application\Helper\EntityHelper;
use Application\Model\FiscalYear;

class Holiday extends HrisController {

    public function __construct(AdapterInterface $adapter, StorageInterface $storage) {
        parent::__construct($adapter, $storage);
        $this->initializeRepository(HolidayRepository::class);
    }

    public function indexAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            try {
                $data = $request->getPost();
                $rawList = $this->repository->selectAll($this->employeeId, $data['fiscalYear']);
                $holidays = Helper::extractDbData($rawList);
                return new JsonModel(['success' => true, 'data' => $holidays, 'error' => '']);
            } catch (Exception $e) {
                return new JsonModel(['success' => false, 'data' => [], 'error' => $e->getMessage()]);
            }
        }
        $fiscalYearKV = EntityHelper::getTableKVList($this->adapter, FiscalYear::TABLE_NAME, FiscalYear::FISCAL_YEAR_ID, [FiscalYear::FISCAL_YEAR_NAME]);
        return $this->stickFlashMessagesTo([
                'form' => $this->form,
                'customRenderer' => Helper::renderCustomView(),
                'searchValues' => EntityHelper::getSearchData($this->adapter),
                'fiscalYearKV' => $fiscalYearKV
        ]);
    }

}
