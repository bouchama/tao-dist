<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 */

use oat\taoQtiTest\models\tasks\ImportQtiTest;
use oat\taoTaskQueue\model\Entity\TaskLogEntity;
use oat\taoTaskQueue\model\TaskLogInterface;
use oat\taoTaskQueue\model\TaskLogActionTrait;

/**
 *
 * @author Absar Gilani & Rashid - PCG Team - {absar.gilani6@gmail.com}
 */
class taoQtiTest_actions_RestQtiTests extends \tao_actions_RestController
{
    const TASK_ID_PARAM = 'id';

    protected static $accepted_types = array(
        'application/zip',
        'application/x-zip-compressed',
        'multipart/x-zip',
        'application/x-compressed'
    );

    use TaskLogActionTrait;

    const PARAM_PACKAGE_NAME = 'qtiPackage';

    const ENABLE_METADATA_GUARDIANS = 'enableMetadataGuardians';

    public function index()
    {
        $this->returnFailure(new \common_exception_NotImplemented('This API does not support this call.'));
    }

    /**
     * Import file entry point by using $this->service
     * Check POST method & get valid uploaded file
     */
    public function import()
    {
        try {
            if ($this->getRequestMethod() != Request::HTTP_POST) {
                throw new \common_exception_NotImplemented('Only post method is accepted to import Qti package.');
            }

            $report = taoQtiTest_models_classes_CrudQtiTestsService::singleton()
                ->importQtiTest(
                    $this->getUploadedPackageData()['tmp_name'],
                    $this->getTestClass(),
                    $this->isMetadataGuardiansEnabled()
                );

            if ($report->getType() === common_report_Report::TYPE_SUCCESS) {
                $data = array();
                foreach ($report as $r) {
                    $values = $r->getData();
                    $testid = $values->rdfsResource->getUri();
                    foreach ($values->items as $item) {
                        $itemsid[] = $item->getUri();
                    }
                    $data[] = array(
                        'testId' => $testid,
                        'testItems' => $itemsid
                    );
                }
                return $this->returnSuccess($data);
            } else {
                throw new \common_exception_RestApi($report->getMessage());
            }
        } catch (\common_exception_RestApi $e) {
            return $this->returnFailure($e);
        }
    }

    /**
     * Import test package through the task queue.
     * Check POST method & get valid uploaded file
     */
    public function importDeferred()
    {
        try {
            if ($this->getRequestMethod() != Request::HTTP_POST) {
                throw new \common_exception_NotImplemented('Only post method is accepted to import Qti package.');
            }

            $task = ImportQtiTest::createTask(
                $this->getUploadedPackageData(),
                $this->getTestClass(),
                $this->isMetadataGuardiansEnabled()
            );

            $result = [
                'reference_id' => $task->getId()
            ];

            $taskLog = $this->getServiceManager()->get(TaskLogInterface::SERVICE_ID);

            if ($report = $taskLog->getReport($task->getId())) {
                $result['report'] = $report->toArray();
            }


            return $this->returnSuccess($result);
        } catch (\common_exception_RestApi $e) {
            return $this->returnFailure($e);
        }
    }


    /**
     * Action to retrieve test import status from queue
     */
    public function getStatus()
    {
        try {
            if (!$this->hasRequestParameter(self::TASK_ID_PARAM)) {
                throw new \common_exception_MissingParameter(self::TASK_ID_PARAM, $this->getRequestURI());
            }
            $data = $this->getTaskLogReturnData(
                $this->getRequestParameter(self::TASK_ID_PARAM),
                ImportQtiTest::class
            );
            $this->returnSuccess($data);
        } catch (\Exception $e) {
            $this->returnFailure($e);
        }
    }

    /**
     * Create a Test Class
     *
     * Label parameter is mandatory
     * If parent class parameter is an uri of valid test class, new class will be created under it
     * If not parent class parameter is provided, class will be created under root class
     * Comment parameter is not mandatory, used to describe new created class
     *
     * @return \core_kernel_classes_Class
     */
    public function createClass()
    {
        try {
            $class = $this->createSubClass(new \core_kernel_classes_Class(TAO_TEST_CLASS));

            $result = [
                'message' => __('Class successfully created.'),
                'class-uri' => $class->getUri(),
            ];

            $this->returnSuccess($result);
        } catch (\common_exception_ClassAlreadyExists $e) {
            $result = [
                'message' => $e->getMessage(),
                'class-uri' => $e->getClass()->getUri(),
            ];
            $this->returnSuccess($result);
        } catch (\Exception $e) {
            $this->returnFailure($e);
        }
    }

    /**
     * Add extra values to the JSON returned.
     *
     * @param TaskLogEntity $taskLogEntity
     * @return array
     */
    protected function addExtraReturnData(TaskLogEntity $taskLogEntity)
    {
        $data = [];

        if ($taskLogEntity->getReport()) {
            $plainReport = $this->getPlainReport($taskLogEntity->getReport());

            //the third report is report of import test
            if (isset($plainReport[2]) && isset($plainReport[2]->getData()['rdfsResource'])) {
                $data['testId'] = $plainReport[2]->getData()['rdfsResource']['uriResource'];
            }
        }

        return $data;
    }

     /**
      * @return array
      * @throws common_exception_Error
      * @throws common_exception_RestApi
      */
    private function getUploadedPackageData()
    {
        if (!tao_helpers_Http::hasUploadedFile(self::PARAM_PACKAGE_NAME)) {
            throw new common_exception_RestApi(__('Missed test package file'));
        }

        $fileData = tao_helpers_Http::getUploadedFile(self::PARAM_PACKAGE_NAME);

        $mimeType = tao_helpers_File::getMimeType($fileData['tmp_name']);

        if (!in_array($mimeType, self::$accepted_types)) {
            throw new \common_exception_RestApi(__('Wrong file mime type'));
        }

        return $fileData;
    }

    /**
     * Get class instance to import test
     * @throws \common_exception_RestApi
     * @return \core_kernel_classes_Class
     */
    private function getTestClass()
    {
        return $this->getClassFromRequest(new \core_kernel_classes_Class(TAO_TEST_CLASS));
    }

    /**
     * @return bool
     * @throws common_exception_RestApi
     */
    private function isMetadataGuardiansEnabled()
    {
        $enableMetadataGuardians = $this->getRequestParameter(self::ENABLE_METADATA_GUARDIANS);

        if (is_null($enableMetadataGuardians)) {
            return true; // default value if parameter not passed
        }

        if (!in_array($enableMetadataGuardians, ['true', 'false'])) {
            throw new \common_exception_RestApi(
                self::ENABLE_METADATA_GUARDIANS . ' parameter should be boolean (true or false).'
            );
        }

        return filter_var($enableMetadataGuardians, FILTER_VALIDATE_BOOLEAN);
    }
}
