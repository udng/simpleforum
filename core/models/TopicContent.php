<?php
/**
 * @link http://www.simpleforum.org/
 * @copyright Copyright (c) 2015 Simple Forum
 * @author Jiandong Yu admin@simpleforum.org
 */

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class TopicContent extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%topic_content}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['content'], 'string', 'max' => 20000],
//            ['content', 'filter', 'filter' => 'nl2br'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'content' => '内容',
        ];
    }

	public function getTopic()
    {
        return $this->hasOne(Topic::className(), ['id' => 'topic_id'])
			->select(['created_at', 'user_id', 'node_id', 'title', 'tags']);
    }

	public function afterSave($insert, $changedAttributes)
	{
		if ($insert === true) {
			(new History([
				'user_id' => $this->topic->user_id,
				'action' => History::ACTION_ADD_TOPIC,
				'action_time' => $this->topic->created_at,
				'target' => $this->topic_id,
			]))->save(false);
			Siteinfo::updateCounterInfo('addTopic');
			UserInfo::updateCounterInfo('addTopic', $this->topic->user_id);
			Node::updateCounterInfo('addTopic', $this->topic->node_id);
			Notice::afterTopicInsert($this);
			Tag::afterTopicInsert($this);
		}
		return parent::afterSave($insert, $changedAttributes);
	}

}
