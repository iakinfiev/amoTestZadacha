<?php


namespace app\models;


use yii\base\Model;

class Api extends Model
{
    public $userLogin;
    public $userHash;
    public $path;
    public $url;
    public $url_api;
    public $limit_offset;
    public $options;
    public $options_value;

    private $errors = [
        301 => 'Moved permanently',
        400 => 'Bad request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not found',
        500 => 'Internal server error',
        502 => 'Bad gateway',
        503 => 'Service unavailable',
    ];


    public function getLeadsId()
    {
        $type = 'GET';
        $link = $this->getUrl();

        $Response = $this->curlOptions($type, $link);

            foreach ($Response as $key => $value){
                $Response[] = $value['_embedded']['items'];
            }

        return $Response;
    }

    public function addTask($leads)
    {
        $type = 'POST';
        $link = $this->getUrl();
        $tasks = [];

        foreach ($leads as $key => $value){
            $tasks['add'][] = [
                'element_id' => $value, #ID сделки
                'element_type' => 2, #Показываем, что это - сделка, а не контакт
                'task_type' => 3, #Написать письмо
                'text' => 'Сделка без задачи',
                'responsible_user_id' => 109999,
                'complete_till_at' => 1375285346,
            ];
        }

        $Response = $this->curlOptions($type, $link, $tasks);

        foreach ($Response as $key => $value){
            $Response[] = $value['_embedded']['items'];
        }

        return $Response;
    }

    public function auth()
    {
        $type = 'POST';
        $user = [
            'USER_LOGIN' => $this->userLogin,
            'USER_HASH' => $this->userHash,
        ];

        $link = $this->getUrl();
        $Response = $this->curlOptions($type, $link, $user);
        $Response = $Response['response'];

        if (isset($Response['auth']))
        {
            return true;
        }

        return false;
    }

    private function curlOptions($type, $link, $data = null)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
        curl_setopt($curl, CURLOPT_URL, $link);
        if($type == 'POST'){
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        }
        if($data){
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_COOKIEFILE, __DIR__ . '/cookie.txt');
        curl_setopt($curl, CURLOPT_COOKIEJAR, __DIR__ . '/cookie.txt');
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        $out = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $code = (int) $code;
        $errors = $this->errors;

        try
        {
            if ($code != 200 && $code != 204) {
                throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error', $code);
            }

        } catch (Exception $E) {
            die('Ошибка: ' . $E->getMessage() . PHP_EOL . 'Код ошибки: ' . $E->getCode());
        }

        return json_decode($out, true);
    }

    /**
     * @return string
     */

    private function getUrl()
    {
        $link = $this->url . $this->url_api . $this->path;

        if(!empty($this->options) && !empty($this->options_value)){
            $link .= '?'. $this->options .'='. $this->options_value;
        } elseif (!empty($this->limit_offset)){
            $link .= '&'. $this->limit_offset;
        }
        return $link;
    }

}