<?php

namespace frontend\controllers;

use common\models\Availability;
use common\models\Email;
use common\models\Hospital;
use common\models\HospitalCategory;
use common\models\Locality;
use common\models\MailHospital;
use common\models\Medicament;
use common\models\Package;
use common\models\Rating;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\Cors;
use yii\helpers\VarDumper;
use yii\web\Controller;

/**
 * Site controller
 */
class ApiController extends Controller
{
    const LIMIT = 100;
    public $enableCsrfValidation = false;

    public function beforeAction($action)
    {
        Yii::$app->request->enableCsrfValidation = false;

        Yii::$app->request->parsers['application/json'] = 'yii\web\JsonParser';

        return parent::beforeAction($action); // TODO: Change the autogenerated stub
    }


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
        Yii::$app->request->enableCsrfValidation = false;
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
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
                Yii::$app->mailer->compose('@app/views/api/mail',['email'=>$email,'phone'=>$phone, 'fio'=>$fio, 'text'=>$text])
                    ->setFrom('mail.eliky@gmail.com')
                    ->setTo($emailHospital->email)
                    ->setSubject('Повідомлення від користувача eliky - ' . $email)
                    ->send();
                $email_model = new Email();
                $email_model->email = $email;
                $email_model->phone = $phone;
                $email_model->fio = $fio;
                $email_model->hospital_id = $hospital;
                $email_model->text = $text;
                $email_model->checked = 0;
                $email_model->save();
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

    /**
     * @return array
     */
    public function actionRating(){
        Yii::$app->request->enableCsrfValidation = false;
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if (Yii::$app->request->post()) {
            $hospital = Yii::$app->request->post('email_hospital_id');
            $rating = Yii::$app->request->post('rating');
            $comment = Yii::$app->request->post('comment');
            $device_id = Yii::$app->request->post('device_id');
            $name = Yii::$app->request->post('name');

//            $lastRating = Rating::find()->select('create_at')->where(['device_id'=>$device_id, 'hospital_id'=>$hospital])->orderBy('create_at desc')->one();
//            if (!empty($lastRating) && (time() - strtotime($lastRating->create_at))<864000){
//                return [
//                    'data'=>[],
//                    'status'=>'error',
//                    'message' => 'Нельзя добавлять оценку чаще чем 1 раз в 10 дней. Последняя оценка в '.$lastRating->create_at
//                ];
//            }
            $ratingModel = new Rating();
            $ratingModel->hospital_id = $hospital;
            $ratingModel->rating = $rating;
            $ratingModel->name = $name;
            $ratingModel->comment = $comment;
            $ratingModel->device_id = $device_id;
            if($ratingModel->save()){
                $ratingModel->calcRating();
                return [
                    'data'=>[],
                    'status'=>'success',
                    'message' => ''
                ];
            }else{
                return [
                    'data'=>[],
                    'status'=>'error',
                    'message' => 'Ошибка при сохранении'
                ];
            }

        }
    }
    public function actionRatingList($email_hospital_id){
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $ratings = Rating::find()->where(['hospital_id'=>$email_hospital_id])->asArray()->all();
        return $ratings;
    }
}
