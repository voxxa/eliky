<?php

namespace frontend\controllers;

use common\models\Availability;
use common\models\Hospital;
use common\models\HospitalCategory;
use common\models\Locality;
use common\models\MailHospital;
use common\models\Medicament;
use common\models\Package;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\Cors;
use yii\web\Controller;

/**
 * Site controller
 */
class ApiController extends Controller
{
    const LIMIT = 100;



    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('index');
    }
    /**
     * @param int $limit
     * @param string $region
     * @param int $page
     * @return mixed
     */
    public function actionLocality($limit = self::LIMIT, $region = '', $page = 1)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $locality = Locality::find();
        if (!empty($region)){
            $locality->andWhere(['region'=>$region]);
        }
        $count = ceil($locality->count()/$limit);
        $res = $locality->limit($limit)->offset(($page - 1) * $limit)->orderBy('title')->all();
        return [
            'data'=>$res,
            'pages'=>[
                'current'=>$page,
                'count'=>$count
            ],
            'status'=>'success'
        ];
    }

    /**
     * @param int $page
     * @return mixed
     */
    public function actionMedicament($limit = self::LIMIT, $page = 1)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $medicament = Medicament::find();
        $count = ceil($medicament->count()/$limit);
        $res = $medicament->limit($limit)->offset(($page - 1) * $limit)->orderBy('title')->all();
        return [
            'data'=>$res,
            'pages'=>[
                'current'=>$page,
                'count'=>$count
            ],
            'status'=>'success'
        ];
    }

    /**
     * @return mixed
     */
    public function actionPackage()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $res = Package::find()->all();
        return [
            'data'=>$res,
            'pages'=>[
                'current'=>1,
                'count'=>1
            ],
            'status'=>'success'
        ];
    }
    /**
     * @return mixed
     */
    public function actionRegions()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $res = Hospital::find()->select('region_id, region_title')->distinct()->all();
        return [
            'data'=>$res,
            'pages'=>[
                'current'=>1,
                'count'=>1
            ],
            'status'=>'success'
        ];
    }

    /**
     * @param int $page
     * @return mixed
     */
    public function actionHospital($limit = self::LIMIT, $locality_id= '', $region ='', $page = 1)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $hospitals = Hospital::find();
        if (!empty($region)){
            $hospitals->andWhere(['region_title'=>$region]);
        }
        if (!empty($locality_id)){
            $hospitals->andWhere(['locality_id'=>$locality_id]);
        }
        $count = ceil($hospitals->count()/$limit);
        $res = $hospitals->limit($limit)->offset(($page - 1) * $limit)->orderBy('title')->all();
        return [
            'data'=>$res,
            'pages'=>[
                'current'=>$page,
                'count'=>$count
            ],
            'status'=>'success'
        ];
    }


    /**
     * @param int $limit
     * @param $hospital_id
     * @param int $page
     * @return mixed
     */
    public function actionHospitalCategory($limit = self::LIMIT, $hospital_id, $page = 1)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $hospital_category = HospitalCategory::find()->where(['hospital_id' => $hospital_id]);
        $count = ceil($hospital_category->count()/$limit);
        $res=$hospital_category->limit($limit)->offset(($page - 1) * $limit)->all();
        return [
            'data'=>$res,
            'pages'=>[
                'current'=>$page,
                'count'=>$count
            ],
            'status'=>'success'
        ];
    }

    /**
     * @param int $limit
     * @param $hospital_id
     * @param int $page
     * @return mixed
     */
    public function actionAvailability($limit = self::LIMIT, $hospital_id, $page = 1)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $availability = Availability::find()->where(['hospital_id' => $hospital_id]);
        $count = ceil($availability->count()/$limit);
        $res = $availability->limit($limit)->offset(($page - 1) * $limit)->all();
        return [
            'data'=>$res,
            'pages'=>[
                'current'=>$page,
                'count'=>$count
            ],
            'status'=>'success'
        ];
    }

    /**
     * @return array
     */
    public function actionMailHospitals(){
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $hospitals = MailHospital::find()->orderBy('rergion')->all();
        return [
            'data'=>$hospitals,
            'status'=>'success'
        ];
    }

    /**
     * @return array
     */
    public function actionSendMail(){
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $this->enableCsrfValidation = false;
        if (Yii::$app->request->post()) {
            $email = Yii::$app->request->post('email');
            $hospital = Yii::$app->request->post('hospital');
            $text = Yii::$app->request->post('text');
            $phone = Yii::$app->request->post('phone');
            $fio = Yii::$app->request->post('fio');
            $emailHospital = MailHospital::find()->where(['id'=>$hospital])->one();
            if (empty($emailHospital)){
                return [
                    'data'=>[],
                    'status'=>'error',
                    'message' => 'Больница не найдена'
                ];
            }
            try {
                Yii::$app->mailer->compose()
                    ->setFrom('mail.eliky@gmail.com')
                    ->setTo($emailHospital->email)
                    ->setSubject('Сообщение от пользователя eliky - ' . $email)
                    ->setTextBody($text)
                    ->send();
            } catch (\Exception $e){
                return [
                    'data'=>[],
                    'status'=>'error',
                    'message' => $e->getMessage()
                ];
            }
            return [
                'data'=>[],
                'status'=>'success',
                'message' => ''
            ];
        }
    }


}
