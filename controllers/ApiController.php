<?php


namespace app\controllers;

use app\models\Api;
use yii\web\Controller;

class ApiController extends Controller
{
    public function actionIndex()
    {
// Естественно данные для свойств берем из базы


        $data = [
            'userLogin' => 'test@testmail.com',
            'userHash' => '7ebefd1d4741106a4daa0e0a673bba2e4dc16054',
            'path' => 'leads',
            'url' => 'https://test.amocrm.ru',
            'url_api' => '/private/api/auth.php?type=json',
        ];
        $model = new Api($data);


        $result = $model->auth();

        if($result = true){
            $model->path = 'leads';
            $model->url_api = '/api/v2/';
            $model->limit_offset = 500;
            $model->options = 'filter/tasks';
            $model->options_value = 1;

            $leads = $model->getLeadsId();
        }

        if(isset($leads) && !empty($leads)){
            $model->path = 'tasks';
            $model->limit_offset = '';
            $model->options = '';
            $model->options_value = '';

            $model->addTask($leads = []);
        }
        exit;
    }
}