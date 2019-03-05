<?php
namespace app\controllers;

use Yii;
use app\controllers\BaseController;
use yii\data\Pagination;
use yii\data\ArrayDataProvider;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UnauthorizedHttpException;
use linslin\yii2\curl;
use app\constants\Constants;

/**
 * TaskController implements the CRUD actions for task model.
 */
class MileageTaskController extends BaseController
{	
	/**
     * Add New Task Entry.
     * If creation is successful, the browser will be redirected to the 'show-entries' page.
     * @param $mileageCardID
     * @param $sundayDate
     * @param $saturdayDate
     * @param $mileageCardProjectID
     * @return render
     * @throws \yii\web\HttpException
     */

    public function actionAddMileageEntryTask($weekStart = null, $weekEnd = null, $mileageCardID = null, $sundayDate = null, $saturdayDate = null, $mileageCardProjectID = null)
    {
		try{
			//guest redirect
			if (Yii::$app->user->isGuest) {
				return $this->redirect(['/login']);
			}

			//convert sundayDate and saturdayDate to MM/dd/YYYY format
			$sundayDate = date( "m/d/Y", strtotime(str_replace('-', '/', $sundayDate)));
			$saturdayDate = date( "m/d/Y", strtotime(str_replace('-', '/', $saturdayDate)));

			self::requirePermission("createTaskEntry");

			$model = new \yii\base\DynamicModel([
				'MileageCardID',
				'Date',
				'TotalMiles',
				'WeekStart',
				'WeekEnd'
			]);
			$model-> addRule('MileageCardID', 'integer', null, 'required');
			$model-> addRule('Date', 'string', ['max' => 32], 'required');
			$model-> addRule('TotalMiles', 'number', null, 'required');
			$model-> addRule('WeekStart', 'string', ['max' => 32], 'required');
			$model-> addRule('WeekEnd', 'string', ['max' => 32], 'required');
				
			if ($model->load(Yii::$app->request->post()) && $model->validate()) {
				$mileage_task_data = array(
					'MileageCardID' => $model->MileageCardID,
					'Date' => $model->Date,
					'CreatedByUserName' => Yii::$app->session['UserName'],
					'TotalMiles' => $model->TotalMiles,
					'MileageType' => 'AdminDriveMeter',
				);

				$testDate = date( "Y-m-d", strtotime(str_replace('-', '/',$model->Date)));
				
				// make sure date within range
				if (strtotime($testDate) < strtotime($model->WeekStart) || strtotime($testDate) > strtotime($model->WeekEnd)) {
					throw new \yii\web\HttpException(400);
				}
				
				$json_data = json_encode($mileage_task_data);
				// post url
				$url = 'mileage-entry%2Fcreate-task';
				$response = Parent::executePostRequest($url, $json_data, Constants::API_VERSION_3);
				return $response;
			} else {
				$model->WeekStart = $weekStart;
				$model->WeekEnd = $weekEnd;
				$model->MileageCardID = $mileageCardID;
				$dataArray = [
					'model' => $model,
					'sundayDate' => $sundayDate,
					'saturdayDate' => $saturdayDate,
					'mileageCardProjectID' => $mileageCardProjectID,
				];
				
				if (Yii::$app->request->isAjax) {
					return $this->renderAjax('mileage_task_modal', $dataArray);
				} else {
					return $this->render('mileage_task_modal', $dataArray);
				}
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
	
	public function actionViewMileageEntryTaskByDay($mileageCardID, $date){
		try{
			//guest redirect
			if (Yii::$app->user->isGuest) {
				return $this->redirect(['/login']);
			}
			
			self::requirePermission("mileageEntryView");
			
			//create model form update form
			$model = new \yii\base\DynamicModel([
				'EntryID',
				'StartTime',
				'EndTime',
				'StartingMileage',
				'EndingMileage',
				'PersonalMiles',
				'AdminMiles'
			]);
			$model-> addRule('EntryID', 'integer', null, 'required');
			$model-> addRule('StartTime', 'string', ['max' => 32], 'required');
			$model-> addRule('EndTime', 'string', ['max' => 32], 'required');
			$model-> addRule('StartingMileage', 'number', null, 'required');
			$model-> addRule('EndingMileage', 'number', null, 'required');
			$model-> addRule('PersonalMiles', 'number', null, 'required');
			$model-> addRule('AdminMiles', 'number', null, 'required');
			
			//get entries for grid view
			$getUrl = 'mileage-entry%2Fview-entries&' . http_build_query([
				'cardID' => $mileageCardID,
				'date' => $date
			]);
			$getResponseData = json_decode(Parent::executeGetRequest($getUrl, Constants::API_VERSION_3), true); //indirect RBAC
			$entries = $getResponseData['entries'];
			
			$mileageEntryDataProvider = new ArrayDataProvider([
				'allModels' => $entries,
				'key' => 'EntryID',
				'pagination' => false
			]);
			
			$dataArray = [
				'mileageEntryDataProvider' => $mileageEntryDataProvider,
				'model' => $model,
			];	
			
			if (Yii::$app->request->isAjax) {
				return $this->renderAjax('mileage_entry_view_modal', $dataArray);
			} else {
				return $this->render('mileage_entry_view_modal', $dataArray);
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
