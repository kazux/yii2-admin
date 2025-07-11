<?php

namespace mdm\admin\models\searchs;

use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use mdm\admin\components\Configs;
use yii\rbac\Item;

/**
 * AuthItemSearch represents the model behind the search form about AuthItem.
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class AuthItem extends Model
{
    const TYPE_ROUTE = 101;

    public $name;
    public $type;
    public $description;
    public $ruleName;
    public $data;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'ruleName', 'description'], 'safe'],
            [['type'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('rbac-admin', 'Name'),
            'item_name' => Yii::t('rbac-admin', 'Name'),
            'type' => Yii::t('rbac-admin', 'Type'),
            'description' => Yii::t('rbac-admin', 'Description'),
            'ruleName' => Yii::t('rbac-admin', 'Rule Name'),
            'data' => Yii::t('rbac-admin', 'Data'),
        ];
    }

    /**
     * Search authitem
     * @param array $params
     * @return \yii\data\ActiveDataProvider|\yii\data\ArrayDataProvider
     */
    public function search($params)
    {
        /* @var \yii\rbac\Manager $authManager */
        $authManager = Configs::authManager();
        $advanced = Configs::instance()->advanced;
        if ($this->type == Item::TYPE_ROLE) {
            $items = $authManager->getRoles();
        } else {
            $items = array_filter($authManager->getPermissions(), function($item) use ($advanced){
              $isPermission = $this->type == Item::TYPE_PERMISSION;
              if ($advanced) {
                return $isPermission xor (strncmp($item->name, '/', 1) === 0 or strncmp($item->name, '@', 1) === 0);
              }
              else {
                return $isPermission xor strncmp($item->name, '/', 1) === 0;
              }
            });
        }
        $this->load($params);
      if ($this->validate()) {
        $search = mb_strtolower(trim((string)$this->name));
        $desc = mb_strtolower(trim((string)$this->description));
        $ruleName = $this->ruleName;

        $items = array_filter($items, function ($item) use ($search, $desc, $ruleName) {
          $itemNameLower = mb_strtolower($item->name);
          $itemDescLower = mb_strtolower($item->description);

          return (empty($search) || mb_strpos($itemNameLower, $search) !== false)
            && (empty($desc) || mb_strpos($itemDescLower, $desc) !== false)
            && (empty($ruleName) || $item->ruleName == $ruleName);
        });
      }

        return new ArrayDataProvider([
            'allModels' => $items,
        ]);
    }
}
