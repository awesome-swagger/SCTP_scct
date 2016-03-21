<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "MileageEntryTb".
 *
 * @property string $MileageEntryID
 * @property string $MileageEntryUserID
 * @property string $MileageEntryStartingMileage
 * @property string $MileageEntryEndingMileage
 * @property string $MileageEntryStartDate
 * @property string $MileageEntryEndDate
 * @property string $MileageEntryDate
 * @property string $MileageEntryType
 * @property integer $MileageEntryMileageCardID
 * @property integer $MileageEntryActivityID
 * @property string $MileageEntryApprovedBy
 * @property integer $MileageEntryStatus
 * @property string $MileageEntryComment
 * @property string $MileageEntryCreatedDate
 * @property string $MileageEntryCreatedBy
 * @property string $MileageEntryModifiedDate
 * @property string $MileageEntryModifiedBy
 *
 * @property ActivityTb $mileageEntryActivity
 * @property MileageCardTb $mileageEntryMileageCard
 */
class MileageEntry extends \yii\base\model
{

	public $MileageEntryID;
	public $MileageEntryUserID;
	public $MileageEntryStartingMileage;
	public $MileageEntryEndingMileage;
	public $MileageEntryStartDate;
	public $MileageEntryEndDate;
	public $MileageEntryDate;
	public $MileageEntryType;
	public $MileageEntryMileageCardID;
	public $MileageEntryActivityID;
	public $MileageEntryApprovedBy;
	public $MileageEntryStatus;
	public $MileageEntryComment;
	public $MileageEntryCreatedDate;
	public $MileageEntryCreatedBy;
	public $MileageEntryModifiedDate;
	public $MileageEntryModifiedBy;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['MileageEntryStartingMileage', 'MileageEntryEndingMileage', 'MileageEntryID', 'MileageEntryType', 'MileageEntryMileageCardID', 'MileageEntryActivityID', 'MileageEntryStatus', 'MileageEntryUserID'], 'integer'],
            [['MileageEntryApprovedBy', 'MileageEntryComment', 'MileageEntryCreatedBy', 'MileageEntryModifiedBy'], 'string'],
            [['MileageEntryDate', 'MileageEntryStartDate', 'MileageEntryEndDate',  'MileageEntryCreatedDate', 'MileageEntryModifiedDate'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'MileageEntryID' => 'Mileage Entry ID',
			'MileageEntryUserID' => 'Mileage Entry User ID',
            'MileageEntryStartingMileage' => 'Mileage Entry Starting Mileage',
            'MileageEntryEndingMileage' => 'Mileage Entry Ending Mileage',
			'MileageEntryStartDate' => 'Mileage Entry Start Date',
			'MileageEntryEndDate' => 'Mileage Entry End Date',
			'MileageEntryDate' => 'Mileage Entry Date',
			'MileageEntryType' => 'Mileage Entry Type',
            'MileageEntryMileageCardID' => 'Mileage Entry Mileage Card ID',
            'MileageEntryActivityID' => 'Mileage Entry Activity ID',
            'MileageEntryApprovedBy' => 'Mileage Entry Approved By',
            'MileageEntryStatus' => 'Mileage Entry Status',
            'MileageEntryComment' => 'Mileage Entry Comment',
            'MileageEntryCreatedDate' => 'Mileage Entry Created Date',
            'MileageEntryCreatedBy' => 'Mileage Entry Created By',
            'MileageEntryModifiedDate' => 'Mileage Entry Modified Date',
            'MileageEntryModifiedBy' => 'Mileage Entry Modified By',
        ];
    }
}