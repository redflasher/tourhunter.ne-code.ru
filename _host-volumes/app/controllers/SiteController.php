<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\User;
use app\models\TransferForm;
use yii\data\ActiveDataProvider;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
        $query = User::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        return $this->render('balances', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionBalances()
    {
        $query = User::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        return $this->render('balances', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionMoneyTransfer()
    {
        $model = new TransferForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            $user = User::findOne(Yii::$app->user->identity->id);
            $recipient = User::findOne(['username' => $model->username]);

            $transferSum = $model->balance;

            if($user && $recipient)
            {
                $transaction = User::getDb()->beginTransaction();
                try {
                    $user->balance = (float)$user->balance - (float)$transferSum;
                    $recipient->balance = (float)$recipient->balance + (float)$transferSum;

                    $user->save();
                    $recipient->save();

                    $transaction->commit();

                    Yii::$app->session->setFlash('success', "Transfer is success.");
                } catch(\Exception $e) {
                    $transaction->rollBack();
                    throw $e;
                } catch(\Throwable $e) {
                    $transaction->rollBack();
                    throw $e;
                }
            }

            if (\Yii::$app->request->referrer)
                return $this->redirect(Yii::$app->request->referrer);
        }

        return $this->render('money-transfer', [
            'model' => $model,
        ]);

        return $this->render('balances', [
            'dataProvider' => $dataProvider,
        ]);
    }


}
