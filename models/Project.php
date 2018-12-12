<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "project".
 *
 * @property int $id ID
 * @property int $title_id Title
 * @property int $time_id Time
 * @property int $created_at Создано
 * @property int $updated_at Обновлено
 *
 * @property Time $time
 * @property Title $title
 */
class Project extends Base
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%project}}';
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
                'name',
                'string'
            ],
            [
                [
                    'created_at',
                    'updated_at'
                ],
                'safe'
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название проекта',
            'created_at' => 'Создано',
            'updated_at' => 'Обновлено',
        ];
    }

}