<?php

namespace app\controllers;

use app\components\DateHelper;
use app\components\MyArrayHelper;
use Yii;
use app\controllers\BaseController;
use yii\data\Pagination;
use yii\base\Exception;
use yii\filters\VerbFilter;
use yii\data\ArrayDataProvider;
use linslin\yii2\curl;
use yii\helpers\Json;
use yii\web\Request;
use \DateTime;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UnauthorizedHttpException;
use yii\base\Model;
use yii\web\Response;
use app\constants\Constants;
use app\models\EmployeeDetailTime;
use function date;
use function end;
use function http_build_query;
use function json_decode;
use function json_encode;
use function krsort;
use function print_r;
use function strtotime;

class EmployeeApprovalController extends BaseCardController
{
    /**
     * Lists a summary of user data.
     * @return mixed
     * @throws ForbiddenHttpException
     * @throws ServerErrorHttpException
     * @throws \yii\web\HttpException
     */
    public function actionIndex($projectID = null, $projectFilterString = null,  $activeWeek = null, $dateRange = null){
		//TODO clean up extra code
		// try {
			//guest redirect
			if (Yii::$app->user->isGuest)
			{
				return $this->redirect(['/login']);
			}

			//Check if user has permissions
			self::requirePermission("viewEmployeeApproval");

			//if request is not coming from report-summary reset session variables.
			$referrer = Yii::$app->request->referrer;
			if(!strpos($referrer,'employee-approval')){
				unset(Yii::$app->session['employeeApprovalFormData']);
				unset(Yii::$app->session['employeeApprovalSort']);
			}

			//check user role
            $isProjectManager = Yii::$app->session['UserAppRoleType'] == 'ProjectManager';

            // Store start/end date data
            $dateData = [];
            $startDate = null;
            $endDate = null;

            $model = new \yii\base\DynamicModel([
                'pageSize',
				'page',
                'filter',
                'dateRangeValue',
                'dateRangePicker',
                'clientID',
                'projectID',
				'employeeID'
            ]);
            $model->addRule('pageSize', 'string', ['max' => 32]);//get page number and records per page
			$model->addRule('page', 'integer');
            $model->addRule('filter', 'string', ['max' => 100]); // Don't want overflow but we can have a relatively high max
            $model->addRule('dateRangePicker', 'string', ['max' => 32]);
            $model->addRule('dateRangeValue', 'string', ['max' => 100]);
            $model->addRule('clientID', 'integer');
            $model->addRule('projectID', 'integer');
            $model->addRule('employeeID', 'integer');

            //get current and prior weeks date range
            $today = BaseController::getDate();
            $priorWeek = BaseController::getWeekBeginEnd("$today -1 week");
            $currentWeek = BaseController::getWeekBeginEnd($today);
            $other = "other";

            //create default prior/current week values
            $dateRangeDD = [
                $priorWeek => 'Prior Week',
                $currentWeek => 'Current Week',
                $other => 'Other'
            ];

			//"sort":"-RowLabels"
            //get sort data
            if (isset($_GET['sort'])){
                $sort = $_GET['sort'];
                //parse sort data
                $sortField = str_replace('-', '', $sort, $sortCount);
                $sortOrder = $sortCount > 0 ? 'DESC' : 'ASC';
				Yii::$app->session['employeeApprovalSort'] = [
					'sortField' => $sortField,
					'sortOrder' => $sortOrder
				];
            } else {
				if(Yii::$app->session['employeeApprovalSort']){
					$sortField = Yii::$app->session['employeeApprovalSort']['sortField'];
					$sortOrder = Yii::$app->session['employeeApprovalSort']['sortOrder'];
				}else{
					//default sort values
					$sortField = 'RowLabels';
					$sortOrder = 'ASC';
				}
            }

            // check if type was post, if so, get value from $model
            if ($model->load(Yii::$app->request->queryParams)){
				Yii::$app->session['employeeApprovalFormData'] = $model;
			}else{
				//set defaults to session data if avaliable
				if(Yii::$app->session['employeeApprovalFormData']){
					$model = Yii::$app->session['employeeApprovalFormData'];
				}else{
					//set default values
					$model->pageSize = 50;
					$model->page = 1;
					$model->employeeID = '';
					$model->dateRangePicker	= null;
					$model->dateRangeValue = $currentWeek;
					//set filters if data passed from home screen
					$model->filter = $projectFilterString != null ? urldecode($projectFilterString): '';
					$model->clientID = '';
					$model->projectID = $projectID != null ? $projectID : '';
					if($activeWeek == Constants::PRIOR_WEEK){
						$model->dateRangeValue = $priorWeek;
					}elseif($activeWeek == Constants::CURRENT_WEEK){ //not necessary since default is current, but in place for clarity
						$model->dateRangeValue = $currentWeek;
					}elseif($dateRange != null){
						$model->dateRangePicker	= $dateRange;
						$model->dateRangeValue = 'other';
					}
				}
            }

			//get start/end date based on dateRangeValue
            if ($model->dateRangeValue == 'other') {
                if ($model->dateRangePicker == null){
                    $endDate = $startDate = date('Y-m-d');
                }else {
                    $dateData 	= SELF::dateRangeProcessor($model->dateRangePicker);
                    $startDate 	= $dateData[0];
                    $endDate 	= $dateData[1];
                }
            }else{
                $dateRangeArray = BaseController::splitDateRange($model->dateRangeValue);
                $startDate = $dateRangeArray['startDate'];
                $endDate =  $dateRangeArray['endDate'];
            }

			//url encode filter
			$encodedFilter = urlencode($model->filter);
			//build params
			$httpQuery = http_build_query([
				'startDate' => $startDate,
				'endDate' => $endDate,
				'listPerPage' => $model->pageSize,
				'page' => $model->page,
				'filter' => $encodedFilter,
				'clientID' => $model->clientID,
				'projectID' => $model->projectID,
				'employeeID' => $model->employeeID,
				'sortField' => $sortField,
				'sortOrder' => $sortOrder,
			]);
			// set url

			$url = 'employee-approval&' . $httpQuery;

			//execute request
			$response = Parent::executeGetRequest($url, Constants::API_VERSION_3);
            $response = json_decode($response, true);
            $userData = $response['UserData'];
            $projData = $response['ProjData'];
            $statusData = $response['StatusData'];

			//get date values from user data for dynamic headers
			$dateHeaders = [];
			if($userData != null){
				foreach ($userData[0] as $key => $value){
					if(strpos($key, '/') !== false){
						$dateHeaders[] = $key;
					}
				}
			}

			//extract format indicators from response data
			$projectDropDown = $response['ProjectDropDown'];

			//check if user can approve cards
			$canApprove = self::can('timeCardApproveCards') && self::can('mileageCardApprove');

            // passing user data into dataProvider
            $userDataProvider = new ArrayDataProvider([
				'allModels' => $userData,
				'pagination' => false,
				'key' => function ($userData) {
					return array(
						'UserID' => $userData['UserID'],
						'UserName' => $userData['RowLabels']
					);
				}
			]);

			// passing project data into dataProvider
			$projDataProvider = new ArrayDataProvider([
				'allModels' => $projData,
				'pagination' => false
			]);

			// passing status data into dataProvider
			$statusDataProvider = new ArrayDataProvider([
				'allModels' => $statusData,
				'pagination' => false
			]);

			//sorting with dynamic headers may prove to be problematic, currently weekday headers are dates
			// Sorting UserData table
			// $userDataProvider->sort = [
				// 'defaultOrder' => [$sortField => ($sortOrder == 'ASC') ? SORT_ASC : SORT_DESC],
				// 'attributes' => [
				// ]
			// ];

			// set pages to dispatch table
			// $pages = new Pagination($response['pages']);

			$dataArray = [
				'userDataProvider' => $userDataProvider,
				'dateHeaders' => $dateHeaders, //list column header dates for the days of the week
				'projDataProvider' => $projDataProvider,
				'statusDataProvider' => $statusDataProvider,
				'dateRangeDD' => $dateRangeDD,
				'model' => $model,
				'projectDropDown' => $projectDropDown,
				'canApprove' => $canApprove,
				'isProjectManager' => $isProjectManager,
				'startDate' => $startDate,
				'endDate' =>  $endDate
			];
			//calling index page to pass dataProvider.
			if(Yii::$app->request->isAjax) {
				return $this->renderAjax('index', $dataArray);
			}else{
				return $this->render('index', $dataArray);
			}
        // } catch (UnauthorizedHttpException $e){
            // Yii::$app->response->redirect(['login/index']);
        // } catch(ForbiddenHttpException $e) {
            // throw $e;
        // } catch(ErrorException $e) {
            // throw new \yii\web\HttpException(400);
        // } catch(Exception $e) {
            // throw new ServerErrorHttpException();
        // }
    }

	/**
     * Lists a summary of user data.
     * @return mixed
     * @throws ForbiddenHttpException
     * @throws ServerErrorHttpException
     * @throws \yii\web\HttpException
     */
    public function actionEmployeeDetail($userID, $date){
		// try {
			//guest redirect
			if (Yii::$app->user->isGuest)
			{
				return $this->redirect(['/login']);
			}

			//Check if user has permissions
			self::requirePermission("employeeApprovalDetail");


			//build api url path
			$url = 'employee-approval%2Femployee-detail&' . http_build_query([
				'userID' => $userID,
				'date' => $date,
			]);

			//execute request
			$response = Parent::executeGetRequest($url, Constants::API_VERSION_3);
            $response = json_decode($response, true);
            $projectData = $response['ProjectData'];
            $breakdownData = $response['BreakdownData'];
            $totalData = $response['Totals'];

            // passing user data into dataProvider
            $projectDataProvider = new ArrayDataProvider([
				'allModels' => $projectData,
				'pagination' => false
			]);

			// passing project data into dataProvider
			$breakdownDataProvider = new ArrayDataProvider([
				'allModels' => $breakdownData,
				'pagination' => false,
				'key' => function ($breakdownData) {
					return array(
						'RowID' => $breakdownData['RowID']
					);
				}
			]);

			//check user role
			$isProjectManager = Yii::$app->session['UserAppRoleType'] == 'ProjectManager';
			$isSupervisor = Yii::$app->session['UserAppRoleType'] == 'Supervisor';

			$dataArray = [
				'projectDataProvider' => $projectDataProvider,
				'breakdownDataProvider' => $breakdownDataProvider,
				'totalData' => $totalData,
				'userID' => $userID,
				'date' => $date,
				'canAddTask' => ($isSupervisor | $isProjectManager),
			];
			//calling index page to pass dataProvider.
			if(Yii::$app->request->isAjax) {
				return $this->renderAjax('employee-detail', $dataArray);
			}else{
				return $this->render('employee-detail', $dataArray);
			}
        // } catch (UnauthorizedHttpException $e){
            // Yii::$app->response->redirect(['login/index']);
        // } catch(ForbiddenHttpException $e) {
            // throw $e;
        // } catch(ErrorException $e) {
            // throw new \yii\web\HttpException(400);
        // } catch(Exception $e) {
            // throw new ServerErrorHttpException();
        // }
    }

    public function actionAddTaskModal($userID, $date)
    {
        //guest redirect
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['/login']);
        }

        //Check if user has permissionse
        self::requirePermission("employeeApprovalDetailEdit");

        $model = new EmployeeDetailTime;
        $projectDropDown = [];


        // project dropdown
        $getProjectDropdownURL = 'project%2Fget-project-dropdowns&' . http_build_query([
                'userID' => $userID,
            ]);
        $getProjectDropdownResponse = Parent::executeGetRequest($getProjectDropdownURL, Constants::API_VERSION_3);
        $projectDropDown = json_decode($getProjectDropdownResponse, true);

        // get task dropdown
        $taskDropDown = [];
        if (isset($_POST['projectID'])) {
            $model->ProjectID = $_POST['projectID'];
            $getAllTaskUrl = 'task%2Fget-by-project&' . http_build_query([
                    'projectID' => $model->ProjectID,
                ]);
            $getAllTaskResponse = Parent::executeGetRequest($getAllTaskUrl, Constants::API_VERSION_3);
            $allTask = json_decode($getAllTaskResponse, true);

            if ($allTask) {
                foreach ($allTask['assets'] as $task) {
                    //$taskDropDown['Task ' . $task['TaskName']] = $task['TaskName'];
                    $taskDropDown[$task['TaskID']] = $task['TaskName'];
                }
            }
        }

        //build api url path
        $url = 'employee-approval%2Femployee-detail&' . http_build_query([
                'userID' => $userID,
                'date' => $date,
            ]);

        //execute request
        $response = Parent::executeGetRequest($url, Constants::API_VERSION_3);
        $response = json_decode($response, true);
        $breakdownData = $response['BreakdownData'];

        return $this->renderAjax('_employee-add-task-modal', [
            'model'           => $model,
            'projectDropDown' => $projectDropDown,
            'taskDropDown'    => $taskDropDown,
            'userID'          => $userID,
            'breakDownData'   => $breakdownData,
            'date'=>$date
        ]);
    }

	/**
     * Populates the Employee Detail Edit Modal
     * @return mixed
     * @throws ForbiddenHttpException
     * @throws ServerErrorHttpException
     * @throws \yii\web\HttpException
     */
    public function actionEmployeeDetailModal($userID, $date){
		try {
			//guest redirect
			if (Yii::$app->user->isGuest){
				return $this->redirect(['/login']);
			}

			//Check if user has permissionse
			self::requirePermission("employeeApprovalDetailEdit");

			//if GET pull data params to populate form
			if (isset($_POST)){
				$data = $_POST['Current'];
				$prevData = $_POST['Prev'];
				$nextData = $_POST['Next'];
				$model = new EmployeeDetailTime;
				$prevModel = new EmployeeDetailTime;
				$nextModel = new EmployeeDetailTime;
				$projectDropDown = [];
				$taskDropDown = [];

				$model->attributes = $data;
				$prevModel->attributes = $prevData;
				$nextModel->attributes = $nextData;

				$getProjectDropdownURL = 'project%2Fget-project-dropdowns&' . http_build_query([
					'userID' => $userID,
				]);
				$getProjectDropdownResponse = Parent::executeGetRequest($getProjectDropdownURL, Constants::API_VERSION_3);
				$projectDropDown = json_decode($getProjectDropdownResponse, true);

				$getAllTaskUrl = 'task%2Fget-by-project&' . http_build_query([
					'projectID' => $model->ProjectID,
				]);
				$getAllTaskResponse = Parent::executeGetRequest($getAllTaskUrl, Constants::API_VERSION_3);
				$allTask = json_decode($getAllTaskResponse, true);
				//add 0 index when no valid task
				if($model->TaskID == 0){
					$taskDropDown = [0 => $model->TaskName];
				}					
				foreach($allTask['assets'] as $task) {
					$taskDropDown[$task['TaskID']] = $task['TaskName'];
				}

				$dataArray = [
					'model' => $model,
					'prevModel' => $prevModel,
					'nextModel' => $nextModel,
					'projectDropDown' => $projectDropDown,
					'taskDropDown' => $taskDropDown,
				];
			}

			//calling index page to pass dataProvider.
			if(Yii::$app->request->isAjax) {
				return $this->renderAjax('_employee-detail-edit-modal', $dataArray);
			}else{
				return $this->render('_employee-detail-edit-modal', $dataArray);
			}
        } catch (UnauthorizedHttpException $e){
            Yii::$app->response->redirect(['login/index']);
        } catch(ForbiddenHttpException $e) {
            throw $e;
        } catch(ErrorException $e) {
            throw new \yii\web\HttpException(400);
        } catch(Exception $e) {
            throw new ServerErrorHttpException();
        }
    }

	public function actionApproveTimecards(){
		try{
			if (Yii::$app->request->isAjax) {
				//get requesting controller type
				$requestType = self::getRequestType();
				$data = Yii::$app->request->post();
				// loop the data array to get all id's.
				$cardIDArray = "";
				foreach($data['userid'] as $keyitem){
					$cardIDArray .= $keyitem['UserID'] . ",";
				}
				$cardIDArray = substr_replace($cardIDArray ,"", -1);
				$startDate = $data['startDate'];
				$endDate = $data['endDate'];
				$data = array(
					'cardIDArray' => $cardIDArray,
					'startDate' =>  $startDate,
					'endDate' =>  $endDate
				);
				$json_data = json_encode($data);
				Yii::trace("Data params json: " . $json_data);
				// post url
				$putUrl = $requestType.'%2Fapprove-timecards';
				$putResponse = Parent::executePutRequest($putUrl, $json_data, Constants::API_VERSION_3); // indirect rbac
				//Handle API response if we want to do more robust error handling
			} else {
			  throw new \yii\web\BadRequestHttpException;
			}
		} catch (UnauthorizedHttpException $e){
			Yii::$app->response->redirect(['login/index']);
		} catch(ForbiddenHttpException $e) {
			throw $e;
		} catch(ErrorException $e) {
			throw new \yii\web\HttpException(400);
		} catch(Exception $e) {
			throw new ServerErrorHttpException();
		}
	}

    /**
     * @param $userID
     * @param $date
     * @throws ForbiddenHttpException
     * @throws UnauthorizedHttpException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionAddTask($userID, $date)
    {
        if (Yii::$app->request->isAjax) {


            /** @var EmployeeDetailTime $employeeDetailTime */
            $employeeDetailTime = new EmployeeDetailTime();
            $employeeDetailTime->attributes = $_POST['EmployeeDetailTime'];

            //build api url path
            $url = 'employee-approval%2Femployee-detail&' . http_build_query([
                    'userID' => $userID,
                    'date'   => $date,
                ]);
            //execute request
            $response = Parent::executeGetRequest($url, Constants::API_VERSION_3);
            $response = json_decode($response, true);
            $breakDownData = $response['BreakdownData'];

            foreach ($breakDownData as $breakDown) {
                $checkBetweenDate = DateHelper::checkDateBetween($date . ' ' . $employeeDetailTime->StartTime.':00',
                    $date . ' ' . $employeeDetailTime->EndTime.':00', $date . ' ' . $breakDown['Start Time'],
                    $date . ' ' . $breakDown['End Time']
                );

                if ($checkBetweenDate) {
                    return Json::encode(['success' => false, 'msg' => 'Invalid datetime range']);
                }
            }


            $postData = [
                'New' => [
                    'ProjectID' => $employeeDetailTime->ProjectID,
                    'TaskID'    => $employeeDetailTime->TaskID,
                    'TaskName'  => $employeeDetailTime->TaskName,
                    'StartTime' => $date . ' ' . $employeeDetailTime->StartTime,
                    'EndTime'   => $date . ' ' . $employeeDetailTime->EndTime,
                    'UserID'    => $userID
                ]
            ];

            if ($breakDownData) {

                $startTimeArr = [];
                $endTimeArr = [];
                foreach ($breakDownData as $breakDown) {
                    $startTimeArr[strtotime($breakDown['Start Time'])] = $breakDown;
                    $endTimeArr[strtotime($breakDown['End Time'])] = $breakDown;
                }

                krsort($startTimeArr);
                krsort($endTimeArr);

                $startTime = $startTimeArr[MyArrayHelper::arrayKeyFirst($startTimeArr)];
                $endTime = end($endTimeArr);

                // check if morning or afternoon
                if ($employeeDetailTime->TimeOfDayName == EmployeeDetailTime::TIME_OF_DAY_MORNING) {

                    $current = $endTime;
                    $postData['Current'] = [
                        'ID'        => $current['RowID'],
                        'ProjectID' => $current['ProjectID'],
                        'TaskID'    => $current['TaskID'],
                        'TaskName'  => $current['TaskName'],
                        'StartTime' => $date . ' ' . $employeeDetailTime->StartTime,
                        'EndTime'   => $date . ' ' . $employeeDetailTime->StartTime
                    ];

                } elseif ($employeeDetailTime->TimeOfDayName == EmployeeDetailTime::TIME_OF_DAY_AFTERNOON) {

                    $current = $startTime;
                    $postData['Current'] = [
                        'ID'        => $current['RowID'],
                        'ProjectID' => $current['ProjectID'],
                        'TaskID'    => $current['TaskID'],
                        'TaskName'  => $current['TaskName'],
                        'StartTime' => $date . ' ' . $employeeDetailTime->EndTime,
                        'EndTime'   => $date . ' ' . $employeeDetailTime->EndTime
                    ];
                }
            }

            //execute post request
            $response = BaseController::executePostRequest('employee-approval%2Fcreate', json_encode($postData),
                Constants::API_VERSION_3);

            return $response;

        }
    }
	
	/**
     * Calls api route to update existing employee detail records
     * @return mixed
     * @throws ForbiddenHttpException
     * @throws ServerErrorHttpException
     * @throws \yii\web\HttpException
     */
	public function actionEmployeeDetailUpdate(){
		try{
			//guest redirect
			if (Yii::$app->user->isGuest){
				return $this->redirect(['/login']);
			}
			if (isset($_POST)){
				//encode json data
				$jsonData = json_encode($_POST);
				//build api url path
				$editUrl = 'employee-approval%2Fupdate';
				$response = Parent::executePutRequest($editUrl, $jsonData, Constants::API_VERSION_3);
				$response = json_decode($response, true);
				//TODO advance response handling for error handling
				return true;
			}else{
				return false;
			}	
		} catch (UnauthorizedHttpException $e){
            Yii::$app->response->redirect(['login/index']);
        } catch(ForbiddenHttpException $e) {
            throw $e;
        } catch(ErrorException $e) {
            throw new \yii\web\HttpException(400);
        } catch(Exception $e) {
            throw new ServerErrorHttpException();
        }
	}

	/**
     * Validate form values
     * @return mixed
     * @throws ForbiddenHttpException
     * @throws ServerErrorHttpException
     * @throws \yii\web\HttpException
     */
	public function actionEmployeeDetailValidate(){
		try{
			//guest redirect
			if (Yii::$app->user->isGuest){
				return $this->redirect(['/login']);
			}
			if (isset($_POST)){
				//if form is valid return empty error message so default to this
				$response = '';
				//check current start time is before end time
				$startTime = $_POST['Current']['StartTime'];
				$endTime = $_POST['Current']['EndTime'];
				if(strtotime($startTime) > strtotime($endTime)){
					//if form is invalid return error message
					$response = 'Start time must be before end time';
					return $response;
				}
				
				//check current start time is after pervious start time
				$prevStartTime = $_POST['Prev']['StartTime'];
				if(strtotime($startTime) < strtotime($prevStartTime)){
					//if form is invalid return error message
					$response = 'Start time must be after previous start time of ' . $prevStartTime;
					return $response;
				}
				
				//check current end time is before next end time
				$nextEndTime = $_POST['Next']['EndTime'];
				if(strtotime($endTime) > strtotime($nextEndTime)){
					//if form is invalid return error message
					$response = 'End time must be before next end time of ' . $nextEndTime;
					return $response;
				}
				return $response;
			}else{
				$response = 'Internal Server Error';
				return $response;
			}	
		} catch (UnauthorizedHttpException $e){
            Yii::$app->response->redirect(['login/index']);
        } catch(ForbiddenHttpException $e) {
            throw $e;
        } catch(ErrorException $e) {
            throw new \yii\web\HttpException(400);
        } catch(Exception $e) {
            throw new ServerErrorHttpException();
        }
	}
}