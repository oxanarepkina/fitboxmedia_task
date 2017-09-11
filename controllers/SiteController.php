<?php

namespace app\controllers;

use app\models\SignupForm;
use app\models\User;
use DateTime;
use function PHPSTORM_META\elementType;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['/profile']);
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($model->login())
                return $this->redirect(['/profile']);
            else {
                $ip = $_SERVER['REMOTE_ADDR'];
                $current_date = new DateTime();

                $query = new Query;
                $query->select('date')
                    ->from('errors')
                    ->where(['ip' => $ip])
                    ->andWhere([
                            '>', 'date', date('Y-m-d H:i:s', strtotime('-5 minutes'))])
                    ->orderBy(['date' => SORT_DESC])
                    ->limit(3);
                $rows = $query->all();

                if (count($rows) >= 3 && $current_date->getTimestamp() - DateTime::createFromFormat("Y-m-d H:i:s", $rows[2]['date'])->getTimestamp() < 5 * 60 * 1000) {
                    $last_attempt = DateTime::createFromFormat("Y-m-d H:i:s", $rows[0]['date']);
                    return $this->render('login', [
                        'model' => $model,
                        'error' => true,
                        'last_attempt' => $last_attempt
                    ]);
                } else {
                    Yii::$app->db->createCommand()->insert('errors', [
                        'ip' => $ip,
                        'date' => $current_date->format("Y-m-d H:i:s"),
                    ])->execute();
                    return $this->render('login', [
                        'model' => $model,
                        'error' => true,
                    ]);
                }
            }
        }

        return $this->render('login', [
            'model' => $model,
            'error' => false
        ]);
    }

    public function actionProfile()
    {
        if (!Yii::$app->user->isGuest)
            return $this->render('profile');
        else
            return $this->redirect(['/login']);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->redirect(['/login']);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * @return string|Response
     */
    public function actionSignup()
    {
        $model = new SignupForm();

        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->signup()) {
                if (Yii::$app->getUser()->login($user)) {
                    return $this->goHome();
                }
            }
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }
}
