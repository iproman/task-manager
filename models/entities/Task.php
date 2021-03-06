<?php

namespace app\models\entities;

use yii\helpers\ArrayHelper;
use app\models\service\Statuses;
use Yii;

/**
 * This is the model class for table "task".
 *
 * @property int $id ID
 * @property string $name Name
 * @property string $content Content
 * @property int $status Status
 * @property string $branch Branch
 * @property int $project_id Project
 * @property int $created_at Created at
 * @property int $updated_at Updated at
 * @property string $event_time Event Time
 *
 * @property Project[] $project
 */
class Task extends Base
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%task}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                'name',
                'required'
            ],
            [
                [
                    'created_at',
                    'updated_at'
                ],
                'safe'
            ],
            [
                'name',
                'string',
                'max' => 64
            ],
            [
                'branch',
                'string',
                'max' => 10
            ],
            [
                'branch',
                'default',
                'value' => function ($model, $attribute) {
                    /** @var Task $model */
                    $task = Task::find()
                        ->where(['project_id' => $model->project_id])
                        ->asArray()
                        ->orderBy(['id' => SORT_DESC])
                        ->limit(1)->one();
                    $branch = $model->project->branch;
                    $num = preg_split("/$branch/", $task['branch']);
                    if (is_numeric($num[1]) && !empty($num)) {
                        $num = $num[1] + 1;
                        return $branch . "$num";
                    };
                    return $branch;
                }
            ],
            [
                'content',
                'default',
                'value' => null,
            ],
            [
                'content',
                'string',
                'max' => 2000
            ],
            [
                'status',
                'in',
                'range' => Statuses::getStatuses(),
            ],
            [
                'status',
                'default',
                'value' => Statuses::STATUS_NEW,
            ],
            [
                'project_id',
                'required'
            ],
            [
                'project_id',
                'integer'
            ],
            [

                'project_id',
                'exist',
                'skipOnError' => true,
                'targetClass' => Project::class,
                'targetAttribute' => [
                    'project_id' => 'id'
                ]
            ],
            [['event_time'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'content' => 'Content',
            'status' => 'Status',
            'branch' => 'Branch',
            'project_id' => 'Project',
            'created_at' => 'Created at',
            'updated_at' => 'Updated at',
            'event_time' => 'Event Time',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProject()
    {
        return $this->hasOne(Project::class, ['id' => 'project_id']);
    }

    /**
     * @param $project_id
     * @return array|\yii\db\ActiveRecord|null
     */
    public static function getCurrentProject($project_id)
    {
        return !empty($project_id) ? self::find()->where(['project_id' => $project_id])->limit(1)->one() : null;
    }

    /**
     * @return array
     */
    public static function getTaskNames()
    {
        $taskList = Task::find()->asArray()->all();

        return ArrayHelper::map($taskList, 'id', 'name');
    }

    /**
     * Return counted tasks.
     * @param null $status
     * @param null $project
     * @return mixed
     * @throws \Throwable
     */
    public static function getCountedTasks($status = null, $project = null)
    {
        $db = Yii::$app->db;
        $result = $db->cache(function ($db) use ($status, $project) {
            $task = Task::find();
            if (null !== $status && null === $project) {
                return (clone $task)
                    ->where(['=', 'status', $status])
                    ->count();
            } elseif (null === $status && null !== $project) {
                return (clone $task)
                    ->where(['=', 'project_id', $project])
                    ->count();
            } elseif (null !== $status && null !== $project) {
                return (clone $task)
                    ->where(['=', 'status', $status])
                    ->andWhere(['=', 'project_id', $project])
                    ->count();
            } else {
                return (clone $task)
                    ->count();
            }
        }, Yii::$app->cache['cache']['day']);

        return $result;
    }
}
