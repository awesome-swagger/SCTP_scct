<?php

namespace app\controllers;

use Yii;
use app\models\MileageCard;
use app\models\MileageCardSearch;
use app\controllers\BaseController;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\grid\GridView;
use yii\data\ArrayDataProvider;
use linslin\yii2\curl;
use yii\web\Request;


/**
 * MileageCardController implements the CRUD actions for MileageCard model.
 */
class MileageCardController extends BaseController
{

    /**
     * Lists all MileageCard models.
     * @return mixed
     */
    public function actionIndex()
    {
		$url = "http://api.southerncrossinc.com/index.php?r=mileage-card%2Findex";
		$response = Parent::executeGetRequest($url);
		
        // passing decode data into dataProvider
		$dataProvider = new ArrayDataProvider([
		'allModels' => json_decode($response, true),]);

		// fill gridview by applying data provider
		GridView::widget([
			'dataProvider' => $dataProvider,
			]);
			
		//calling index page to pass dataProvider.
        return $this->render('index', [
            'dataProvider' => $dataProvider
        ]);
    }

    /**
     * Displays a single MileageCard model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
		$url = 'http://api.southerncrossinc.com/index.php?r=mileage-card%2Fview&id='.$id;
		$response = Parent::executeGetRequest($url);
		
		return $this -> render('view', ['model' => json_decode($response, true)]);
		
        // return $this->render('view', [
            // 'model' => $this->findModel($id),
        // ]);
    }

    /**
     * Creates a new MileageCard model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new \yii\base\DynamicModel([
			'MileageCardEmpID', 'MileageCardTechID', 'MileageCardProjectID', 'MileageCardType', 'MileageCardAppStatus', 
			'MileageCardCreateDate', 'MileageCardCreatedBy', 'MileageCardModifiedDate', 'MileageCardModifiedBy', 
			'MileagCardBusinessMiles', 'MileagCardPersonalMiles', 'isNewRecord'
		]);
		
		$model->addRule('MileageCardEmpID', 'integer')
			  ->addRule('MileageCardTechID', 'integer')
			  ->addRule('MileageCardProjectID', 'integer')
			  ->addRule('MileageCardAppStatus', 'integer')
			  ->addRule('MileagCardBusinessMiles', 'integer')
			  ->addRule('MileagCardPersonalMiles', 'integer')
			  ->addRule('MileageCardType', 'string')
			  ->addRule('MileageCardCreatedBy', 'string')
			  ->addRule('MileageCardModifiedBy', 'string')
			  ->addRule('MileageCardCreateDate', 'safe')
			  ->addRule('MileageCardModifiedDate', 'safe');

        // create curl object
		$curl = new curl\Curl();
		
		// post url
		$url_send = "http://api.southerncrossinc.com/index.php?r=mileage-card%2Fcreate";
		
        if ($model->load(Yii::$app->request->post())) {
			
			$data = array(
				'MileageCardEmpID' => $model->MileageCardEmpID,
				'MileageCardTechID' => $model->MileageCardTechID,
				'MileageCardProjectID' => $model->MileageCardProjectID,
				'MileageCardAppStatus' => $model->MileageCardAppStatus,
				'MileagCardBusinessMiles' => $model->MileagCardBusinessMiles,
				'MileagCardPersonalMiles' => $model->MileagCardPersonalMiles,
				'MileageCardType' => $model->MileageCardType,
				'MileageCardCreatedBy' => $model->MileageCardCreatedBy,
				'MileageCardModifiedBy' => $model->MileageCardModifiedBy,
				'MileageCardCreateDate' => $model->MileageCardCreateDate,
				'MileageCardModifiedDate' => $model->MileageCardModifiedDate,
			);
			
			$json_data = json_encode($data);		
			$ch = curl_init($url_send);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
			curl_setopt($ch, CURLOPT_POSTFIELDS,$json_data);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json',
				'Content-Length: ' . strlen($json_data))
			);			
			$result = curl_exec($ch);
			curl_close($ch);
			//var_dump($result);
			$obj = (array)json_decode($result,true);
			//var_dump($obj["MileageCardID"]);
            return $this->redirect(['view', 'id' => $obj["MileageCardID"]]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing MileageCard model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $getUrl = 'http://api.southerncrossinc.com/index.php?r=mileage-card%2Fview&id='.$id;
		$getResponse = json_decode(Parent::executeGetRequest($getUrl), true);

		$model = new \yii\base\DynamicModel($getResponse);
		
		$model->addRule('MileageCardEmpID', 'integer')
			  ->addRule('MileageCardTechID', 'integer')
			  ->addRule('MileageCardProjectID', 'integer')
			  ->addRule('MileageCardAppStatus', 'integer')
			  ->addRule('MileagCardBusinessMiles', 'integer')
			  ->addRule('MileagCardPersonalMiles', 'integer')
			  ->addRule('MileageCardType', 'string')
			  ->addRule('MileageCardCreatedBy', 'string')
			  ->addRule('MileageCardModifiedBy', 'string')
			  ->addRule('MileageCardCreateDate', 'safe')
			  ->addRule('MileageCardModifiedDate', 'safe');
		
		if ($model->load(Yii::$app->request->post()))
		{
			$data = array(
				'MileageCardEmpID' => $model->MileageCardEmpID,
				'MileageCardTechID' => $model->MileageCardTechID,
				'MileageCardProjectID' => $model->MileageCardProjectID,
				'MileageCardAppStatus' => $model->MileageCardAppStatus,
				'MileagCardBusinessMiles' => $model->MileagCardBusinessMiles,
				'MileagCardPersonalMiles' => $model->MileagCardPersonalMiles,
				'MileageCardType' => $model->MileageCardType,
				'MileageCardCreatedBy' => $model->MileageCardCreatedBy,
				'MileageCardModifiedBy' => $model->MileageCardModifiedBy,
				'MileageCardCreateDate' => $model->MileageCardCreateDate,
				'MileageCardModifiedDate' => $model->MileageCardModifiedDate,
			);
		
			$json_data = json_encode($data);
			
			$putUrl = 'http://api.southerncrossinc.com/index.php?r=mileage-card%2Fupdate&id='.$id;
			$putResponse = Parent::executePutRequest($putUrl, $json_data);
			
			$obj = json_decode($putResponse, true);
			
		return $this->redirect(['view', 'id' => $obj["MileageCardID"]]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing MileageCard model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $url = 'http://api.southerncrossinc.com/index.php?r=mileage-card%2Fdelete&id='.$id;
		Parent::executeDeleteRequest($url);
		$this->redirect('/index.php?r=mileage-card%2Findex');
    }

    /**
     * Finds the MileageCard model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return MileageCard the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = MileageCard::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
