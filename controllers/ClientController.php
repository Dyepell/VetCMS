<?php


namespace app\controllers;
use app\models\Analys_blood;
use app\models\AnalysbloodForm;
use app\models\Client;
use app\models\ClientForm;
use app\models\Doctor;
use app\models\DoctorForm;
use app\models\Expense_catalog;
use app\models\Expense_catalogForm;
use app\models\Expense_prihod;
use app\models\Expense_prihodForm;
use app\models\Facility;
use app\models\FacilityForm;
use app\models\Istbol;
use app\models\IstbolForm;
use app\models\KattovForm;
use app\models\Oplata;
use app\models\OplataForm;
use app\models\PacientForm;
use app\models\Poroda;
use app\models\Pred_uslForm;
use app\models\PriceForm;
use app\models\Sale;
use app\models\SaleForm;
use app\models\SearchForm;
use app\models\SearchModel;
use app\models\SelectForm;
use app\models\Diagnoz;
use app\models\Price;
use app\models\Biohim;
use app\models\Mocha;
use app\models\MochaForm;
use app\models\Uzi;
use app\models\UziForm;
use app\models\Other_isl;
use app\models\Other_islForm;
use app\models\Kattov;
use app\models\TovarForm;
use app\models\Prihod_tovaraForm;
use app\models\Prihod_tovara;
use yii\helpers\StringHelper;
use app\models\Pred_usl;
use PhpOffice\PhpWord\PhpWord;



use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\IWriter;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


use app\models\Facility_test;


use app\models\Vid;
use app\models\Vizit;
use app\models\Sl_vakc;
use app\models\VizitForm;
use PHPExcel;
use yii\base\Model;
use yii\debug\models\timeline\Search;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\data\ActiveDataProvider;
use app\models\BiohimForm;

use app\models\Pacient;
use Yii;

use yii\data\Pagination;


class ClientController extends AppController
{
    public $layout='basic';

    public function beforeAction($action)
    {
        if ($action->id=='index'){
            $this->enableCsrfValidation=false;
        }
        return parent::beforeAction($action);
    }

    public function actionIndex(){
        $model = new SearchForm();
        if(Yii::$app->request->isPjax){

            $test = $_POST[SearchForm];


            $answer = $test['FAM'];
            if ($answer==''){
                $searchProvider=NULL;
            }else{
                $searchProvider = new ActiveDataProvider([
                    'query' => Client::find()->where(['like', 'FAM', $answer.'%', false]),
                    'pagination'=>false,
                    'sort'=> ['defaultOrder' => ['NAME' => SORT_ASC]],



                ]);




                }

            $model->load(Yii::$app->request->post());




            return $this->render('registration', compact('model', 'answer',  'searchProvider'));
        }
        $this->view->title='Регистрация';









        $selectClientId=$_GET['selectClientId'];
        $selectClient=Client::find()->where(['ID_CL'=>$selectClientId])->with('pacients')->all();
        $pacients=Pacient::find()->where(['ID_CL'=>$selectClientId]);

        $dataProvider = new ActiveDataProvider([
            'query' => Client::find(),
            'pagination' => [
                'pageSize' => 10,

            ],
        ]);
        if(!Yii::$app->request->get('page')){
            $dataProvider->pagination->page = ceil($dataProvider->getTotalCount() / $dataProvider->pagination->pageSize) - 1;
        }
//        $clients = Client::find();
//        $pageCount=$clients->count();
//        $pages= new Pagination(['totalCount'=>$pageCount, 'pageSize'=>20]);
//        $clientsPagination=$clients->offset($pages->offset)->limit($pages->limit)->all();





        return $this->render('registration', compact('pages',  'searchValue', 'model', 'dataProvider', 'selectClient', 'selectClientId'));
    }

    public function actionClientout(){
        $selectClient =1;
        return '123';

    }
    public function actionAnketa(){
        $this->enableCsrfValidation = false;


        $clientId=$_GET['clientId'];
        $model = ClientForm::findOne(['ID_CL'=>$clientId]);
        $pacients=Pacient::find()->where(['ID_CL'=>$clientId])->all();
        $pacModel= PacientForm::find()->where(['ID_CL'=>$clientId])->with('vid')->with('poroda')->with('doctor')->all();
        $newPacient=new PacientForm();
        $this->view->title=$model->FAM.' '.$model->NAME;

        if ( $model->load(Yii::$app->request->post()) ){
            if ($model->save()){

                return $this->refresh();
            }
        }


        if (Yii::$app->request->post(PacientForm)!=null){


            $PAC= Yii::$app->request->post(PacientForm);
            $PAC=$PAC['$i'];
            if ($PAC['ID_PAC']==''){
                if ( $newPacient->load(Yii::$app->request->post()) ){
                    $newPacient->DATE=date('Y-m-d');
                    if ($newPacient->save()){

                        return $this->refresh();
                    }
                }
            }



            $selectPacient=PacientForm::find()->where(['ID_PAC'=>$PAC['ID_PAC']])->all();

            $selectPacient[0]->KLICHKA=$PAC['KLICHKA'];
            $selectPacient[0]->ID_VID=$PAC['ID_VID'];
            $selectPacient[0]->ID_POR=$PAC['ID_POR'];
            $selectPacient[0]->BDAY=$PAC['BDAY'];
            $selectPacient[0]->POL=$PAC['POL'];
            $selectPacient[0]->VOZR=$PAC['VOZR'];
            $selectPacient[0]->ID_LDOC=$PAC['ID_LDOC'];
            $selectPacient[0]->PRIMECH=$PAC['PRIMECH'];




            $selectPacient[0]->save();
            $this->refresh();

        }




//        if ( $pacModel[]->load(Yii::$app->request->post()) ){
//            if ($pacModel[Yii::$app->request->post(['$i'])]->save()){
//
//                return $this->refresh();
//            }
//        }



        return $this->render('anketa', compact('clientId', 'model', 'pacients', 'pacModel', 'newPacient'));
    }
    public function actionClientadd(){

        $model = new ClientForm();
        $model->FIRST_DATE_N=date("Y-m-d");
        if ( $model->load(Yii::$app->request->post()) ){
            if ($model->save()){
                $clientId=$model->ID_CL;

                $this->redirect("index.php?r=client/anketa&clientId=".$clientId);
            }
        }else{
            return $this->render('anketa', compact('clientId', 'model'));
        }



    }
    public function actionClientdelete(){
        $clientId=$_GET['clientId'];

        $model=ClientForm::findOne(['ID_CL'=>$clientId]);
        $visits=Vizit::find()->where(['ID_CL'=>$model->ID_CL])->all();
        $pacients=Pacient::find()->where(['ID_CL'=>$model->ID_CL])->all();
        foreach ($pacients as $pac){
            $pac->delete();
        }
        foreach ($visits as $visit){
            $visit->delete();
        }
        $facility=Facility::find()->where(['ID_CL'=>$model->ID_CL])->all();
        foreach ($facility  as $fac){
            $fac->delete();
        }
        $model->delete();
        $this->redirect("index.php?r=client");


    }
    public function actionPacientdelete(){
        $clientId=$_GET['clientId'];
        $deletePacient=$_GET['deletePacient'];
        $model=PacientForm::findOne(['ID_PAC'=>$deletePacient]);
        $visits=Vizit::find()->where(['ID_PAC'=>$model->ID_PAC])->all();
        foreach ($visits as $visit){
            $visit->delete();
        }
        $facility=Facility::find()->where(['ID_PAC'=>$model->ID_PAC])->all();
        foreach ($facility  as $fac){
            $fac->delete();
        }
        $model->delete();
        $this->redirect("index.php?r=client/anketa&clientId=".$clientId);


    }

    public function actionVisits(){

       $pacientId=$_GET['pacientId'];
       $clientId=$_GET['clientId'];
        $dataProvider = new ActiveDataProvider([
            'query' => Vizit::find()->where(['ID_PAC'=>$pacientId])->with('diagnoz'),
            'pagination'=>[
                'pageSize'=>10,
            ],

        ]);
        $istbolProvider = new ActiveDataProvider([
            'query' => Istbol::find()->where(['ID_PAC'=>$pacientId]),
            'pagination' => false,


        ]);
        if(!Yii::$app->request->get('page')){
            $dataProvider->pagination->page = ceil($dataProvider->getTotalCount() / $dataProvider->pagination->pageSize) - 1;
        }
        $vakcineProvider = new ActiveDataProvider([
            'query' => Sl_vakc::find()->where(['ID_PAC'=>$pacientId])->orderBy(['ID_SLV'=>SORT_ASC]),
            'pagination' => false,

        ]);


        $pacient=Pacient::findOne(['ID_PAC'=>$pacientId]);
        $client=Client::findOne(['ID_CL'=>$pacient->ID_CL]);
        $this->view->title='Визиты: '.$pacient->KLICHKA;

        return $this->render('visits', compact('dataProvider', 'client', 'pacient', 'vakcineProvider', 'istbolProvider'));
    }

    public function actionVisit(){
        if ($_GET['ID_VISIT']!=NULL){
            $visit=VizitForm::findOne(['ID_VISIT'=>$_GET['ID_VISIT']]);
            $pacient=Pacient::findOne(['ID_PAC'=>$visit->ID_PAC]);
            $doctors=Doctor::find()->all();
            $doc=[];
            $uslugi=Facility::find()->where(['ID_VISIT'=>$_GET['ID_VISIT']])->all();

            for($i=0;$i<count($doctors); $i++)
            {
                $doc[$doctors[$i]->ID_DOC]=$doctors[$i]->NAME;
            }
            $prFacProvider = new ActiveDataProvider([
                'query' => Facility::find()->where(['ID_PAC'=>$pacient->ID_PAC]),
                'pagination' => false,


            ]);
            $oplataProvider = new ActiveDataProvider([
                'query' => Oplata::find()->where(['ID_VIZIT'=>$visit->ID_VISIT]),
                'pagination' => false,


            ]);
            $FacilityProvider = new ActiveDataProvider([
                'query' => Facility::find()->where(['ID_VISIT'=>$visit->ID_VISIT]),
                'pagination' => false,


            ]);
            $prVisit=Vizit::find()->where(['ID_PAC'=>$pacient->ID_PAC])->andwhere(['<', 'ID_VISIT', $visit->ID_VISIT])->orderBy(['ID_VISIT'=>SORT_DESC])->limit(1)->all();

            $prFacilityProvider = new ActiveDataProvider([
                'query' => Facility::find()->where(['ID_VISIT'=>$prVisit[0]->ID_VISIT]),
                'pagination' => false,


            ]);


            $fac =Facility::find()->where(['ID_VISIT'=>$visit->ID_VISIT])->all();

            $istbolProvider = new ActiveDataProvider([
                'query' => Istbol::find()->where(['ID_PAC'=>$visit->ID_PAC]),
                'pagination' => false,


            ]);
        $visit->DATE=date("d.m.Y", strtotime($visit->DATE));
        }else{
            $visit=new VizitForm();
            $visit->ID_PAC=$_GET['ID_PAC'];
            $pac=Pacient::findOne(['ID_PAC'=>$visit->ID_PAC]);

            $visit->ID_CL=$pac->ID_CL;
            $visit->DATE=date("d.m.Y");
        }
        if ( $visit->load(Yii::$app->request->post()) ){
            $visit->DATE=date("Y-m-d", strtotime($visit->DATE));
            if ($visit->SUMMAO!=''){
                $client=Client::findOne(['ID_CL'=>$visit->ID_CL]);
                $oplata=new OplataForm();
                $oplata->ID_CL=$client->ID_CL;
                $oplata->DATE=date('Y-m-d');
                $oplata->VID_OPL=$visit->VIDOPL;
                $oplata->SUMM=$visit->SUMMAO;
                $oplata->ID_VIZIT=$visit->ID_VISIT;
                $oplata->save();
                $visit->DOLG=$visit->DOLG-$visit->SUMMAO;
                $visit->SUMMAO='';

                if($visit->DOLG==0){
                    $visit->DATE_OPL=date("Y-m-d");
                }
            }

            if ($visit->save()){
                $ID_VISIT=$visit->ID_VISIT;

                return $this->redirect("index.php?r=client/visit&ID_VISIT=".$ID_VISIT.'&ID_PAC='.$_GET['ID_PAC']);
            }
        }








        $this->view->title='Визит: '.$pacient->KLICHKA;
        return $this->render('visit', compact('pacient', 'visit', 'prFacProvider', 'FacilityProvider', 'totalSumm', 'istbolProvider', 'oplataProvider','prFacilityProvider', 'doc'));
    }

    public function actionPrice(){


        $teraphyProvider = new ActiveDataProvider([
            'query' => Price::find()->where(['ID_SPDOC'=>'1']),
            'pagination' => false,
            'sort'=> ['defaultOrder' => ['NAME' => SORT_ASC]],

        ]);
        $surgaryProvider = new ActiveDataProvider([
            'query' => Price::find()->where(['ID_SPDOC'=>'2']),
            'pagination' => false,
            'sort'=> ['defaultOrder' => ['NAME' => SORT_ASC]],

        ]);
        $uziProvider = new ActiveDataProvider([
            'query' => Price::find()->where(['ID_SPDOC'=>'3']),
            'pagination' => false,
            'sort'=> ['defaultOrder' => ['NAME' => SORT_ASC]],

        ]);
        $medicinesProvider = new ActiveDataProvider([
            'query' => Price::find()->where(['ID_SPDOC'=>'4']),
            'pagination' => false,
            'sort'=> ['defaultOrder' => ['NAME' => SORT_ASC]],

        ]);
        $vakcineProvider = new ActiveDataProvider([
            'query' => Price::find()->where(['ID_SPDOC'=>'5']),
            'pagination' => false,
            'sort'=> ['defaultOrder' => ['NAME' => SORT_ASC]],

        ]);
        $degProvider = new ActiveDataProvider([
            'query' => Price::find()->where(['ID_SPDOC'=>'6']),
            'pagination' => false,
            'sort'=> ['defaultOrder' => ['NAME' => SORT_ASC]],

        ]);
        $analysisProvider = new ActiveDataProvider([
            'query' => Price::find()->where(['ID_SPDOC'=>'7']),
            'pagination' => false,
            'sort'=> ['defaultOrder' => ['NAME' => SORT_ASC]],

        ]);
        $feedProvider = new ActiveDataProvider([
            'query' => Price::find()->where(['ID_SPDOC'=>'8']),
            'pagination' => false,
            'sort'=> ['defaultOrder' => ['NAME' => SORT_ASC]],

        ]);



        return $this->render('price', compact('teraphyProvider', 'surgaryProvider',
            'uziProvider', 'medicinesProvider', 'vakcineProvider', 'degProvider','analysisProvider',
        'feedProvider'));
    }
    public function actionAddprice(){
        if ($_GET['ID_PR']!=NULL){
            $model=PriceForm::findOne(['ID_PR'=>$_GET['ID_PR']]);


        }else{
            $model=new PriceForm();
            $model->DATA=date('d.m.Y');
        }

            if ( $model->load(Yii::$app->request->post()) ){

                if ($model->save()){

                    return $this->redirect("index.php?r=client/price");
                }
            }
        return $this->render('addPrice', compact('model'));
    }
    public function  actionPricedelete(){
        $model=Price::findOne(['ID_PR'=>$_GET['ID_PR']]);
        $model->delete();
        return $this->redirect("index.php?r=client/price");
    }
    public function actionDoctor(){
        $dataProvider = new ActiveDataProvider([
            'query' => Doctor::find(),
            'pagination' => false,


        ]);
        return $this->render('doctor', compact('dataProvider'));
    }
    public function actionAdddoctor(){
        if ($_GET['ID_DOC']!=NULL){
            $model = DoctorForm::findOne(['ID_DOC'=>$_GET['ID_DOC']]);
        }else{
            $model = new DoctorForm();
        }
        if ( $model->load(Yii::$app->request->post()) ){

            if ($model->save()){

                return $this->redirect("index.php?r=client/doctor");
            }
        }
        return $this->render('adddoctor', compact('model'));
    }
    public function  actionDoctordelete(){
        $model=Doctor::findOne(['ID_DOC'=>$_GET['ID_DOC']]);
        $model->delete();
        return $this->redirect("index.php?r=client/doctor");
    }
    public function actionFacility(){
//        $facility = new FacilityForm();
//        $facility->DATA=date("d.m.Y");
//        $visit=Vizit::findOne(['ID_VISIT'=>$_GET['ID_VISIT']]);
//        $pacient=Pacient::findOne(['ID_PAC'=>$visit->ID_PAC]);
//        $client=Client::findOne(['ID_CL'=>$pacient->ID_CL]);
//        $facility->ID_CL=$client->ID_CL;
//        $facility->ID_PAC=$pacient->ID_PAC;
//        $ID_PAC=$facility->ID_PAC;
//
//        if ( $facility->load(Yii::$app->request->post()) ){
//            $facility->ID_VISIT=$_GET['ID_VISIT'];
//            $price=Price::findOne(['ID_PR'=>$facility->ID_PR]);
//            $facility->PRICE=$price->PRICE;
//            $facility->SUMMA=$price->PRICE * $facility->KOL;
//            $visit->DOLG=$visit->DOLG+$facility->SUMMA;
//            $visit->SUMMAV=$visit->SUMMAV + $facility->SUMMA;
//
//            $facility->DATA=date("Y-m-d", strtotime($facility->DATA));
//            if($facility->DATASL!=NULL){
//                $facility->DATASL=date('Y-m-d', strtotime($facility->DATASL));
//            }
//            if($facility->DATASL==''){
//                $facility->DATASL=NULL;
//            }
//
//            if($visit->DATE_OPL!=NULL){
//                $visit->DATE_OPL='';
//            }
//
//
//
//            $visit->save();
//            if ($facility->save()){
//
//                return $this->redirect("index.php?r=client/visit&ID_VISIT=".$_GET['ID_VISIT'].'&ID_PAC='.$ID_PAC);
//            }
//        }
        $searchModel= new SearchModel();
        $dataProvider=$searchModel->search(Yii::$app->request->get());
        $dataProvider->pagination=false;
        $doctors=Doctor::find()->all();
        $doc=[];
        $uslugi=Facility::find()->where(['ID_VISIT'=>$_GET['ID_VISIT']])->all();

        for($i=0;$i<count($doctors); $i++)
        {
        $doc[$doctors[$i]->ID_DOC]=$doctors[$i]->NAME;
        }



        return $this->render('facility', compact('facility', 'dataProvider', 'searchModel', 'doc', 'uslugi'));
    }
    public function actionIstbol(){
        if ($_GET['ID_IST']!=NULL){
            $istbol = IstbolForm::findOne(['ID_IST'=>$_GET['ID_IST']]);
            $pacient = Pacient::findOne(['ID_PAC'=>$istbol->ID_PAC]);

        }else{

            $istbol =new IstbolForm();
            $istbol->DIST=date("d.m.Y");
            $pacient = Pacient::findOne(['ID_PAC'=>$_GET['ID_PAC']]);
        }


        if ( $istbol->load(Yii::$app->request->post()) ){

            if ($istbol->save()){

                return $this->redirect("index.php?r=client/istbol&ID_IST=".$istbol->ID_IST);
            }
        }
        return $this->render('istbol', compact('istbol', 'pacient'));
    }
    public function  actionIstboldelete(){
        $model=Istbol::findOne(['ID_IST'=>$_GET['ID_IST']]);
        $pacient=Pacient::findOne(['ID_PAC'=>$model->ID_PAC]);
        $model->delete();
        return $this->redirect("index.php?r=client/anketa&clientId=".$pacient->ID_CL);
    }

    public function actionAnalysis(){
        $pacient=Pacient::findOne(['ID_PAC'=>$_GET['ID_PAC']]);
        $this->view->title='Исследования: '.$pacient->KLICHKA;
        $bloodProvider = new ActiveDataProvider([
            'query' => Analys_blood::find()->where(['ID_PAC'=>$pacient->ID_PAC]),
            'pagination' => false,


        ]);
        $BiohimProvider = new ActiveDataProvider([
            'query' => Biohim::find()->where(['ID_PAC'=>$pacient->ID_PAC]),
            'pagination' => false,


        ]);
        $MochaProvider = new ActiveDataProvider([
            'query' => Mocha::find()->where(['ID_PAC'=>$pacient->ID_PAC]),
            'pagination' => false,


        ]);
        $UziProvider = new ActiveDataProvider([
            'query' => Uzi::find()->where(['ID_PAC'=>$pacient->ID_PAC]),
            'pagination' => false,


        ]);
        $OtherProvider = new ActiveDataProvider([
            'query' => Other_isl::find()->where(['ID_PAC'=>$pacient->ID_PAC]),
            'pagination' => false,


        ]);
        return $this->render('analysis', compact('pacient', 'bloodProvider', 'BiohimProvider', 'MochaProvider',
        'UziProvider', 'OtherProvider'));
    }
    public function actionBlood(){
        if ($_GET['ID_BLOOD']!=NULL){
            $blood = AnalysbloodForm::findOne(['ID_BLOOD'=>$_GET['ID_BLOOD']]);
            $blood->DATE=date('d.m.Y', strtotime($blood->DATE));
        }else{
            $blood=new AnalysbloodForm();
            $blood->ID_PAC=$_GET['ID_PAC'];
            $blood->DATE=date('d.m.Y');
        }

        $pacient=Pacient::findOne(['ID_PAC'=>$_GET['ID_PAC']]);

        if ( $blood->load(Yii::$app->request->post()) ){
            $blood->DATE=date('Y-m-d', strtotime($blood->DATE));
            if ($blood->save()){

                $ID_BLOOD=$blood->ID_BLOOD;


                return $this->redirect('index.php?r=client/blood&ID_PAC='.$pacient->ID_PAC.'&ID_BLOOD='.$ID_BLOOD);
            }
        }
        return $this->render('blood', compact('blood', 'pacient'));
    }
    public function actionBlooddelete(){
        $blood = Analys_blood::findOne(['ID_BLOOD'=>$_GET['ID_BLOOD']]);
        $ID_PAC=$blood->ID_PAC;
        $blood->delete();
        return $this->redirect('index.php?r=client/analysis&ID_PAC='.$ID_PAC);
    }
    public function actionFacilitydelete(){
        $facility=Facility::findOne(['ID_FAC'=>$_GET['ID_FAC']]);

        $visit=Vizit::findOne(['ID_VISIT'=>$_GET['ID_VISIT']]);
        $summ=$facility->SUMMA;

        $visit->SUMMAV=round($visit->SUMMAV-$summ);
        $visit->DOLG=round($visit->DOLG-$summ);
        $excat=Expense_catalog::find()->all();
        $ex=[];
        foreach ($excat as $item){
            array_push($ex, $item->ID_PR);
        }
        if( in_array($facility->ID_PR, $ex)){
            $expense = Expense_catalog::findOne(['ID_PR' => $facility->ID_PR]);
            $expense->KOL=$expense->KOL+$facility->KOL;
            $expense->SUMM=$expense->PRICE*$expense->KOL;
            $expense->save();
        }

        $facility->delete();
        $visit->save();

        return $this->redirect('index.php?r=client/visit&ID_VISIT='.$_GET['ID_VISIT']);
    }


    public function actionBiohim(){
        if ($_GET['ID_BIOHIM']!=NULL){
            $blood = BiohimForm::findOne(['ID_BIOHIM'=>$_GET['ID_BIOHIM']]);
            $blood->DATE = date('d.m.Y', strtotime($blood->DATE));
        }else{
            $blood=new BiohimForm();
            $blood->ID_PAC=$_GET['ID_PAC'];
            $blood->DATE=date('d.m.Y');

        }

        $pacient=Pacient::findOne(['ID_PAC'=>$_GET['ID_PAC']]);

        if ( $blood->load(Yii::$app->request->post()) ){
            $blood->DATE=date('Y-m-d', strtotime($blood->DATE));
            if ($blood->save()){

                $ID_BIOHIM=$blood->ID_BIOHIM;
                return $this->redirect('index.php?r=client/biohim&ID_PAC='.$pacient->ID_PAC.'&ID_BIOHIM='.$ID_BIOHIM);
            }
        }
        return $this->render('biohim', compact('blood', 'pacient'));
    }

    public function actionBiohimdelete(){
        $blood = Biohim::findOne(['ID_BIOHIM'=>$_GET['ID_BIOHIM']]);
        $ID_PAC=$blood->ID_PAC;
        $blood->delete();
        return $this->redirect('index.php?r=client/analysis&ID_PAC='.$ID_PAC);
    }



    public function actionMocha(){
        if ($_GET['ID_MOCHA']!=NULL){
            $blood = MochaForm::findOne(['ID_MOCHA'=>$_GET['ID_MOCHA']]);
            $blood->DATE = date('d.m.Y', strtotime($blood->DATE));

        }else{
            $blood=new MochaForm();
            $blood->ID_PAC=$_GET['ID_PAC'];
            $blood->DATE=date('d.m.Y');
        }

        $pacient=Pacient::findOne(['ID_PAC'=>$_GET['ID_PAC']]);

        if ( $blood->load(Yii::$app->request->post()) ){
            $blood->DATE=date('Y-m-d', strtotime($blood->DATE));
            if ($blood->save()){

                $ID_MOCHA=$blood->ID_MOCHA;
                return $this->redirect('index.php?r=client/mocha&ID_PAC='.$pacient->ID_PAC.'&ID_MOCHA='.$ID_MOCHA);
            }
        }
        return $this->render('mocha', compact('blood', 'pacient'));
    }

    public function actionMochadelete(){
        $blood = Mocha::findOne(['ID_MOCHA'=>$_GET['ID_MOCHA']]);
        $ID_PAC=$blood->ID_PAC;
        $blood->delete();
        return $this->redirect('index.php?r=client/analysis&ID_PAC='.$ID_PAC);
    }



    public function actionUzi(){
        if ($_GET['ID_UZI']!=NULL){
            $blood = UziForm::findOne(['ID_UZI'=>$_GET['ID_UZI']]);
            $blood->DATE = date('d.m.Y', strtotime($blood->DATE));
        }else{
            $blood=new UziForm();
            $blood->ID_PAC=$_GET['ID_PAC'];
            $blood->DATE=date('d.m.Y');
        }

        $pacient=Pacient::findOne(['ID_PAC'=>$_GET['ID_PAC']]);

        if ( $blood->load(Yii::$app->request->post()) ){
            $blood->DATE=date('Y-m-d', strtotime($blood->DATE));
            if ($blood->save()){

                $ID_UZI=$blood->ID_UZI;
                return $this->redirect('index.php?r=client/uzi&ID_PAC='.$pacient->ID_PAC.'&ID_UZI='.$ID_UZI);
            }
        }
        return $this->render('uzi', compact('blood', 'pacient'));
    }

    public function actionUzidelete(){
        $blood = Uzi::findOne(['ID_UZI'=>$_GET['ID_UZI']]);
        $ID_PAC=$blood->ID_PAC;
        $blood->delete();
        return $this->redirect('index.php?r=client/analysis&ID_PAC='.$ID_PAC);
    }



public function actionOther(){
    if ($_GET['ID_OTHER']!=NULL){
        $blood = Other_islForm::findOne(['ID_OTHER'=>$_GET['ID_OTHER']]);
        $blood->DATE = date('d.m.Y', strtotime($blood->DATE));
    }else{
        $blood=new Other_islForm();
        $blood->ID_PAC=$_GET['ID_PAC'];
        $blood->DATE=date('d.m.Y');
    }

    $pacient=Pacient::findOne(['ID_PAC'=>$_GET['ID_PAC']]);

    if ( $blood->load(Yii::$app->request->post()) ){
        $blood->DATE=date('Y-m-d', strtotime($blood->DATE));
        if ($blood->save()){

            $ID_OTHER=$blood->ID_OTHER;
            return $this->redirect('index.php?r=client/other&ID_PAC='.$pacient->ID_PAC.'&ID_OTHER='.$ID_OTHER);
        }
    }

    return $this->render('other', compact('blood', 'pacient'));
}

public function actionOtherdelete(){
    $blood = Other_isl::findOne(['ID_OTHER'=>$_GET['ID_OTHER']]);
    $ID_PAC=$blood->ID_PAC;
    $blood->delete();
    return $this->redirect('index.php?r=client/analysis&ID_PAC='.$ID_PAC);
}


public function actionCatalog(){
    $KattovProvider = new ActiveDataProvider([
        'query' => Kattov::find()->orderBy(['NAME' => SORT_ASC]),
        'pagination' => false,
    ]);

    $model=new TovarForm();
    if ( $model->load(Yii::$app->request->post()) ){

        if ($model->save()){

            return $this->refresh();
        }
    }
        return $this->render('catalog', compact('KattovProvider', 'model'));
}
    public function actionTovardelete(){
        $ID_TOV=$_GET['ID_TOV'];

        $model=TovarForm::findOne(['ID_TOV'=>$ID_TOV]);
        $model->delete();
        $this->redirect("index.php?r=client/catalog");


    }

    public function actionPrihod_tovara(){
        $PrihodProvider = new ActiveDataProvider([
            'query' => Prihod_tovara::find(),
            'pagination' => false,
        ]);
        $date=date('d.m.Y');
        return $this->render('prihod_tovara', compact('PrihodProvider', 'date'));
    }
    public function actionPrihod(){
        if ($_GET['ID_PRIHOD']!=NULL){
            $model=Prihod_tovara::findOne(['ID_PRIHOD'=>$_GET['ID_PRIHOD']]);

        }else{
            $model=new Prihod_tovaraForm();
            $model->DATE=date("d.m.Y");
        }

        if ( $model->load(Yii::$app->request->post()) ){



            $model->SUMM=$model->KOL*$model->PRICE;
            $model->DATE=date("Y-m-d", strtotime( $model->DATE));


            if ($model->save()){


                return $this->redirect("index.php?r=client/prihod_tovara");
            }
        }
        return $this->render('prihod', compact('model'));
    }

    public function actionPrihoddelete(){
        $ID_PRIHOD=$_GET['ID_PRIHOD'];

        $model=Prihod_tovaraForm::findOne(['ID_PRIHOD'=>$ID_PRIHOD]);
        if ($model->ID_TOV) {
            $tovar = Kattov::findOne(['ID_TOV' => $model->ID_TOV]);
            $tovar->KOL = $tovar->KOL - $model->KOL;
            $tovar->save();
        }
        $model->delete();
        $this->redirect("index.php?r=client/prihod_tovara");


    }
    public function actionTovar(){
        if ($_GET['ID_TOV']!=NULL){
            $model=KattovForm::findOne(['ID_TOV'=>$_GET['ID_TOV']]);

        }else{
            $model=new KattovForm();
        }

        if ( $model->load(Yii::$app->request->post()) ){

            if ($model->save()){

                return $this->redirect("index.php?r=client/catalog");
            }
        }
        return $this->render('tovar', compact('model'));
    }

    public function actionSale(){
        $SaleProvider = new ActiveDataProvider([
            'query' => Sale::find(),
            'pagination' => [
                'pageSize' => 10,

            ],
        ]);
        if(!Yii::$app->request->get('page')){
            $SaleProvider->pagination->page = ceil($SaleProvider->getTotalCount() / $SaleProvider->pagination->pageSize) - 1;
        }
        return $this->render('sale', compact('SaleProvider'));
    }

    public function actionAddsale(){
        if ($_GET['ID_SALE']!=NULL){
            $model=SaleForm::findOne(['ID_SALE'=>$_GET['ID_SALE']]);

        }else{
            $model=new SaleForm();
            $model->DATE=date("d.m.Y");
            $model->SKIDKA=0;

        }
        $pr=Prihod_tovara::find()->joinWith('tovar')->orderBy(['NAME'=>SORT_ASC])->all();

        $prihod=[];
        foreach ($pr as $item){
            $prihod[$item->ID_PRIHOD]=$item->tovar->NAME.' (остаток - '.$item->KOL.'; цена продажи - '.$item->SELL_PRICE.' руб.)';
        }






        if ( $model->load(Yii::$app->request->post()) ){

            $model->DATE=date('Y-m-d', strtotime($model->DATE));
            $tovar=Prihod_tovara::findOne(['ID_PRIHOD'=>$model->ID_PRIHOD]);
            $tovar->KOL=$tovar->KOL-$model->KOL;

            $model->SUMM=$model->KOL*$tovar->SELL_PRICE;
            $tovar->save();

            if ($model->save()){

                return $this->redirect("index.php?r=client/sale");
            }
        }
        return $this->render('addsale', compact('model', 'prihod'));
    }

    public function  actionSaledelete(){
        $model=Sale::findOne(['ID_SALE'=>$_GET['ID_SALE']]);

        $model->delete();
        return $this->redirect("index.php?r=client/sale");
    }

    public function actionReport_ostatki_form(){

        Yii::setAlias('@reports', Yii::$app->basePath.'/отчеты');

        $spreadsheet=new Spreadsheet();
        $sheet=$spreadsheet->getActiveSheet();
        $tovar=Kattov::find()->orderBy(['NAME'=>SORT_ASC])->all();
        $sheet->setCellValue('A1', 'ЗооДоктор');
        $sheet->setCellValue('D1', date("d.m.Y"));


        $sheet->setCellValue('A2', 'Отчет по остаткам товара');
        $spreadsheet->getActiveSheet()->getStyle('A')->getAlignment()->setWrapText(true);
        $titles=[
            [
                'name'=>'Наименование товара',
                'cell'=>'A',
            ],
            [
                'name'=>'Кол-во',
                'cell'=>'B',
            ],
            [
                'name'=>'Цена продажи',
                'cell'=>'C',
            ],



        ];
        for ($j = 0; $j < count($titles); $j++) {
            $string = $titles[$j]['name'];
            $cellLatter = $titles[$j]['cell'] . 4;
            $sheet->setCellValue($cellLatter, $string);
        }
        $activeRow=4;
        for($i=0;$i<count($tovar); $i++){
            $activeRow++;
            $cellA='A'.$activeRow;
            $cellB='B'.$activeRow;
            $cellC='C'.$activeRow;
            $sheet->setCellValue($cellA,$tovar[$i]->NAME);
            $sheet->setCellValue($cellB, $tovar[$i]->KOL);
            $sheet->setCellValue($cellC, $tovar[$i]->PRICE);
            $sheet->getColumnDimension('A')->setWidth(42);
            $sheet->getColumnDimension('C')->setWidth(14);


        }

        $sheet->getStyle('A4:' . $cellC)
            ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);


        $filename='/Отчет по остаткам товара от '.date('d.m.Y').'.xlsx';


        $writer = new Xlsx($spreadsheet);
        $writer->save(Yii::getAlias('@reports').$filename);
        $path = \Yii::getAlias('@reports') ;

        $file = $path.$filename;

        if (file_exists($file)) {
            \Yii::$app->response->sendFile($file);
        }else {
            throw new \Exception('Файл не найден');
        }



    }

    public function actionReport_prihod(){

        Yii::setAlias('@reports', Yii::$app->basePath.'/отчеты');

        $spreadsheet=new Spreadsheet();
        $sheet=$spreadsheet->getActiveSheet();
        $firstdate= ($_GET['FIRST_DATE']);
        $secondtdate= ($_GET['SECOND_DATE']);


        $firstdate =date("Y-m-d", strtotime($firstdate));
        $secondtdate =date("Y-m-d", strtotime($secondtdate));

        $prihod=Prihod_tovara::find()->where(['between', 'DATE', $firstdate, $secondtdate])->all();
        $sheet->setCellValue('A1', 'ЗооДоктор');
        $sheet->setCellValue('D1', date("d.m.Y"));


        $sheet->setCellValue('A2', 'Отчет по поставкам товара c '.date('d.m.Y', strtotime($firstdate)).' по '.date('d.m.Y', strtotime($secondtdate)));
        $titles=[
            [
                'name'=>'Дата',
                'cell'=>'A',
            ],
            [
                'name'=>'Наименование',
                'cell'=>'B',
            ],
            [
                'name'=>'Кол-во',
                'cell'=>'C',
            ],
            [
                'name'=>'Цена закупки',
                'cell'=>'D',
            ],
            [
                'name'=>'Сумма',
                'cell'=>'E',
            ]



        ];
        for ($j = 0; $j < count($titles); $j++) {
            $string = $titles[$j]['name'];
            $cellLatter = $titles[$j]['cell'] . 4;
            $sheet->setCellValue($cellLatter, $string);
        }
        $activeRow=4;
        $totalSumm=0;
        for($i=0;$i<count($prihod); $i++){
            $activeRow++;
            $tovar=Kattov::findOne(['ID_TOV'=>$prihod[$i]->ID_TOV]);
            $cellA='A'.$activeRow;
            $cellB='B'.$activeRow;
            $cellC='C'.$activeRow;
            $cellD='D'.$activeRow;
            $cellE='E'.$activeRow;
            $sheet->setCellValue($cellA, date('d.m.Y', strtotime($prihod[$i]->DATE)));
            $sheet->setCellValue($cellB,$tovar->NAME);
            $sheet->setCellValue($cellC, $prihod[$i]->KOL);
            $sheet->setCellValue($cellD, $prihod[$i]->PRICE);
            $sheet->setCellValue($cellE, $prihod[$i]->SUMM);
            $totalSumm=$totalSumm+$prihod[$i]->SUMM;
            $sheet->getColumnDimension('A')->setWidth(11);
            $sheet->getColumnDimension('B')->setWidth(40);
            $sheet->getColumnDimension('D')->setWidth(14);


        }
        $activeRow++;
        $cellA='A'.$activeRow;
        $cellD='D'.$activeRow;
        $cellE='E'.$activeRow;
        $sheet->setCellValue($cellE, $totalSumm);
        $spreadsheet->getActiveSheet()->getStyle('B')->getAlignment()->setWrapText(true);
        $sheet->getStyle('A4:' . $cellE)
            ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()->mergeCells($cellA . ':' . $cellD);

        $filename='/Отчет по поставкам товара c '.date('d.m.Y', strtotime($firstdate)).' по '.date('d.m.Y', strtotime($secondtdate)).'.xlsx';


        $writer = new Xlsx($spreadsheet);
        $writer->save(Yii::getAlias('@reports').$filename);
        $path = \Yii::getAlias('@reports') ;

        $file = $path.$filename;

        if (file_exists($file)) {
            \Yii::$app->response->sendFile($file);
        }else {
            throw new \Exception('Файл не найден');
        }



    }
    public function actionVisitdelete(){
        $visit=Vizit::findOne(['ID_VISIT'=>$_GET['ID_VISIT']]);
        $idClient=$visit->ID_CL;
        $fasility=Facility::find()->where(['ID_VISIT'=>$visit->ID_VISIT])->all();
        foreach ($fasility as $fac){
            $fac->delete();
        }
        $visit->delete();
        return $this->redirect('index.php?r=client/anketa&clientId='.$idClient);

    }

    public function actionNew_facility(){
        $ID_VISIT=$_GET['ID_VISIT'];
        $visit=Vizit::findOne(['ID_VISIT'=>$ID_VISIT]);
        $excat=Expense_catalog::find()->all();
        $ex=[];
        foreach ($excat as $item){
            array_push($ex, $item->ID_PR);
        }

        $doctor=$_GET['doctor'];

       foreach ($_GET as $key => $item) {
           if($key!='id'&&$key!='r' &&$key!='name'&&$key!='SearchModel'&&$key!='ID_VISIT'&&$key!='doctor'&&$key!='DATASL'){
               if($item!='') {
                   $price=Price::findOne(['ID_PR'=>$key]);
                   $facility=new FacilityForm();
                   $facility->ID_PAC=$visit->ID_PAC;
                   $facility->ID_CL=$visit->ID_CL;
                   $facility->ID_DOC=$doctor;
                   $facility->ID_PR=$key;
                   $facility->PRICE=$price->PRICE;
                   $facility->KOL=$item;
                   $facility->SUMMA=round($facility->KOL*$facility->PRICE);
                   $facility->DATA=date('Y-m-d');
                   $facility->ID_VISIT=$ID_VISIT;
                   $visit->SUMMAV=round($visit->SUMMAV+$facility->SUMMA);
                   $visit->DOLG=round($visit->DOLG+$facility->SUMMA);
                   $procedure=Price::findOne(['ID_PR'=>$facility->ID_PR]);
                   if($procedure->ID_SPDOC==5&&$_GET['DATASL']!=''){
                       $slVakc=new Sl_vakc();
                       $slVakc->ID_PAC=$visit->ID_PAC;
                       $slVakc->DATA=date('d.m.Y');
                       $slVakc->NAME=$procedure->NAME;
                       $slVakc->ID_PR=$procedure->ID_PR;
                       $slVakc->DATASL=date('d.m.Y', strtotime($_GET['DATASL']));
                       $facility->DATASL=date('Y-m-d', strtotime($_GET['DATASL']));
                       $slVakc->save();

                   }
                   if( in_array($facility->ID_PR, $ex)){
                       $expense = Expense_catalog::findOne(['ID_PR' => $facility->ID_PR]);
                       $expense->KOL=$expense->KOL-$facility->KOL;
                       $expense->SUMM=$expense->PRICE*$expense->KOL;
                       $expense->save();
                   }

                   $facility->save();
                   $visit->save();


               }
           }

       }

        return $this->redirect('index.php?r=client/facility&ID_VISIT='.$ID_VISIT);
    }

    public function actionDocagree(){
        Yii::setAlias('@reports', Yii::$app->basePath . '/отчеты');
        $visit=Vizit::findOne(['ID_VISIT'=>$_GET['ID_VISIT']]);
        $pacient=Pacient::findOne(['ID_PAC'=>$visit->ID_PAC]);
        $client=Client::findOne(['ID_CL'=>$pacient->ID_CL]);
        $vid=Vid::findOne(['ID_VID'=>$pacient->ID_VID]);
        $poroda=Poroda::findOne(['ID_POR'=>$pacient->ID_POR]);

        $fio=$client->FAM.' '.$client->NAME.' '.$client->OTCH;
        $adres=$client->STREET.' '.$client->HOUSE.'-'.$client->FLAT;
        $document=new PhpWord();
        $document->setDefaultFontName('Times New Roman');
        $document->setDefaultFontSize(14);
        $fontStyle = array( 'size'=>11, 'spaceAfter'=>0);
        $document->addFontStyle('myTextStyle', $fontStyle); //myTextStyle - это имя стиля



        $section = $document->addSection();



        $document->addParagraphStyle('p2Style', array('align'=>'top', 'spaceAfter'=>0));
        $section->addText('ЗооДоктор', 'myTextStyle', [ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER , 'spaceAfter'=>0 ]);

        $section->addText('тел. 7 (909) 726-94-49','myTextStyle', [ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER ,'spaceAfter'=>0]);
        $section->addTextBreak(1);
        $section->addText('СОГЛАШЕНИЕ НА ТЕРАПЕВТИЧЕСКИЕ И ХИРУРГИЧЕСКИЕ МАНИПУЛЯЦИИ', ['bold'=>true ], [ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $section->addTextBreak();

        $section->addText('Дата: '.date('d.m.Y'), [], [ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
        $section->addText('Ф.И.О. владельца: '.$fio, [], [ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
        $section->addText('Адрес: '.$adres, [], [ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
        $section->addText('Вид:  '.$vid->NAMEVID.'      Порода: '.$poroda->NAMEPOR, [], [ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
        $section->addText('Кличка животного: '.$pacient->KLICHKA.'      Пол: '.$pacient->POL.'      Возраст:________', [], [ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH ]);

        $section->addText('      Я, владелец вышеуказанного животного (ответственное лицо), даю согласие на проведение следующих процедур:',[], [ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH ]);
        $section->addText('_______________________________________________________'.
        '______________________________________________________________________________'
            . '________________________________________________________________________'
            .'__________________________________________________________________________.',[], [ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH ]);

        $section->addText('      Я разрешаю испоьзование тех лекарственных средств, какие, по мнению врачей, будут необходимы для'.
            ' проведения указанных процедур.',[], [ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH ]);

        $section->addText('      Я понимаю, что различия между организмами животных даже одной породы делает невозможным гарантирование'.
            ' того, что вышеуказанная процедура будет иметь желаемый результат и что, хотя и очень редко, .'.
            'могут возникнуть неожиданные реакции (включая летальный исход).',[], [ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH ]);
        $section->addText('Подпись владельца или ответственного лица: _________________________',[], [ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH ]);
        $section->addText('Телефон, по которому мы можем связаться с Вами: '.$client->PHONES.' ______________________.',[], [ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH ]);

        $filename='/Соглашение'.'.docx';
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($document, 'Word2007');
        $objWriter->save(Yii::getAlias('@reports').$filename);

        $path = \Yii::getAlias('@reports');

        $file = $path.$filename;
        if (file_exists($file)) {
            \Yii::$app->response->sendFile($file);
        }else {
            throw new \Exception('Файл не найден');
        }

    }
    public function actionDocdolg(){
        Yii::setAlias('@reports', Yii::$app->basePath . '/отчеты');
        $visit=Vizit::findOne(['ID_VISIT'=>$_GET['ID_VISIT']]);
        $pacient=Pacient::findOne(['ID_PAC'=>$visit->ID_PAC]);
        $client=Client::findOne(['ID_CL'=>$pacient->ID_CL]);

        $fio=$client->FAM.' '.$client->NAME.' '.$client->OTCH;
        $adres=$client->STREET.' '.$client->HOUSE.'-'.$client->FLAT;
        $document=new PhpWord();
        $document->setDefaultFontName('Times New Roman');
        $document->setDefaultFontSize(14);
        $fontStyle = array( 'size'=>12, 'spaceAfter'=>0, 'bold'=>true);
        $document->addFontStyle('myTextStyle', $fontStyle); //myTextStyle - это имя стиля



        $section = $document->addSection();



        $document->addParagraphStyle('p2Style', array('align'=>'top', 'spaceAfter'=>0));
        $section->addText('Договор возмездного оказания ветеринарных услуг №_____________', 'myTextStyle', [ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $section->addText('Г. Чайковский           								'.date('d.m.Y'), 'myTextStyle', [ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH ]);
        $section->addText('ИП Зайцева Н.В. именуемое в дальнейшем "Исполнитель", с одной стороны, и '.$fio.', именуемый в'.
            ' дальнейцем "Заказчик", с другой стороны, заключили настоящий договор о нижеследующем:', [], [ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH ]);
        $section->addText('1. Предмет договора:',['bold'=>true],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH ]);

        $section->addText('1.1. Заказчик поручает, а Исполнитель принимает на себя обязательства по оказанию следующих ветеринарных услуг:',[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH ]);
        $section->addText('______________________________________________________________'.
            '__________________________________________________________________________________'.
            '__________________________________________________________________________________'.
            '___________________________________________________________________________________'.
            '___________________________________________________________________________',[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH ]);

        $section->addText('2. Цена договора',['bold'=>true],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH ]);
        $section->addText('Заказчик обязуется оплатить услуги, указанные п.1 настоящего Договора в размере',[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH ]);
        $section->addText('________________________________________________________________',[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH ]);
        $section->addText('и в срок до ________________________________',[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH ]);

        $section->addText('Реквизиты сторон:',['bold'=>true],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH ]);
        $section->addText('Заказчик:            '.$fio,['bold'=>true],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH ]);
        $section->addText('Проживающий по адресу:            '.$adres,['bold'=>true],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH ]);
        $section->addText($client->PHONES,['bold'=>true],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH ]);

        $section->addText('Паспорт: ______________________________, '.'выдан "____" ________________ года, ___________________________________________ ',[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH ]);
        $section->addText('_______________________________                   		_____________',[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH ]);

        $section->addText('Исполнитель:',['bold'=>true],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH ]);
        $section->addText('ИП Зайцева Н.В.',[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH ]);
        $section->addText('Адрес: Пермский край, г. Чайковский, ул. Кабалевского, 24 ',[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH ]);
        $section->addText('ИНН:_____________________                   				_____________',[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH ]);


        $filename='/Договор возмездного оказания'.'.docx';
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($document, 'Word2007');
        $objWriter->save(Yii::getAlias('@reports').$filename);

        $path = \Yii::getAlias('@reports');

        $file = $path.$filename;
        if (file_exists($file)) {
            \Yii::$app->response->sendFile($file);
        }else {
            throw new \Exception('Файл не найден');
        }
    }
    public function actionPrintblood(){
        Yii::setAlias('@analysis', Yii::$app->basePath . '/Анализы');
       $analyz=Analys_blood::findOne(['ID_BLOOD'=>$_GET['ID_BLOOD']]);
        $pacient=Pacient::findOne(['ID_PAC'=>$analyz->ID_PAC]);
        $client=Client::findOne(['ID_CL'=>$pacient->ID_CL]);
        $vid=Vid::findOne(['ID_VID'=>$pacient->ID_VID]);
        $spreadsheet=new Spreadsheet();
        $sheet=$spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'ЗооДоктор');
        $sheet->mergeCells('A2:D2');
        $sheet->mergeCells('A3:D3');
        $sheet->setCellValue('A2', 'Аналаиз крови - '.$vid->NAMEVID .' '.$pacient->KLICHKA.' от '.date('d.m.Y', strtotime($analyz->DATE)));
        $sheet->setCellValue('A3', 'Клиент - '.$client->FAM.' '.$client->NAME.' '.$client->OTCH);


        $titles=[
            [
                'name'=>'Показатель',
                'cell'=>'A',
            ],
            [
                 'name'=>'Норма для кошек',
                'cell'=>'B',
            ],
            [
                'name'=>'Норма для собак',
                'cell'=>'C',
            ],
            [
                'name'=>$pacient->KLICHKA,
                'cell'=>'D',
            ]
        ];
        for ($j = 0; $j < count($titles); $j++) {

            $string = $titles[$j]['name'];
            $cellLatter = $titles[$j]['cell'] . 5;
            $sheet->setCellValue($cellLatter, $string);
        }

        $sheet->setCellValue('A6','Эритроциты');
        $sheet->setCellValue('A7','Гемоглобин');
        $sheet->setCellValue('A8','Цветовой показатель');
        $sheet->setCellValue('A9','Лейкоциты');
        $sheet->setCellValue('A10','Базофилы');
        $sheet->setCellValue('A11','Эозинофилы');
        $sheet->setCellValue('A12','Нейтрофилы миелоциты');
        $sheet->setCellValue('A13','Нейтрофилы юные');
        $sheet->setCellValue('A14','Нейтрофилы палочкоядерные');
        $sheet->setCellValue('A15','Нейтрофилы сегментоядерные');
        $sheet->setCellValue('A16','Лимфоциты');
        $sheet->setCellValue('A17','Моноциты');
        $sheet->setCellValue('A18','Плазм. клетки');
        $sheet->setCellValue('A19','	СОЭ');
        $sheet->setCellValue('A20','Особые отметки');
        $sheet->setCellValue('A21','Гематокрит');
        $sheet->setCellValue('A22','Тромбоциты');
        $sheet->setCellValue('A23','Тромбокрит');
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(17);
        $sheet->getColumnDimension('C')->setWidth(17);
        $sheet->getColumnDimension('D')->setWidth(17);

        $sheet->setCellValue('B6','5-10,0');
        $sheet->setCellValue('B7','80-150');
        $sheet->setCellValue('B8','24-45%');
        $sheet->setCellValue('B9','5,5-15,5');
        $sheet->setCellValue('B10','0-1');
        $sheet->setCellValue('B11','0-4');
        $sheet->setCellValue('B12','0');
        $sheet->setCellValue('B13','0');
        $sheet->setCellValue('B14','0-3');
        $sheet->setCellValue('B15','0');
        $sheet->setCellValue('B16','20-55');
        $sheet->setCellValue('B17','1-4');

        $sheet->setCellValue('C6','5,5-8,5');
        $sheet->setCellValue('C7','150-180');
        $sheet->setCellValue('C8','37-55%');
        $sheet->setCellValue('C9','6-17,0');
        $sheet->setCellValue('C10','0');
        $sheet->setCellValue('C11','0-5');
        $sheet->setCellValue('C12','0');
        $sheet->setCellValue('C13','0');
        $sheet->setCellValue('C14','0-3');
        $sheet->setCellValue('C15','0');
        $sheet->setCellValue('C16','15');
        $sheet->setCellValue('C17','1-7');

        $sheet->setCellValue('D6',$analyz->ERIT);
        $sheet->setCellValue('D7',$analyz->GEMOG);
        $sheet->setCellValue('D8',$analyz->COLOR);
        $sheet->setCellValue('D9',$analyz->LEIC);
        $sheet->setCellValue('D10',$analyz->BAZ);
        $sheet->setCellValue('D11',$analyz->EOZ);
        $sheet->setCellValue('D12',$analyz->MIEL);
        $sheet->setCellValue('D13',$analyz->NUN);
        $sheet->setCellValue('D14',$analyz->NPAL);
        $sheet->setCellValue('D15',$analyz->NSEG);
        $sheet->setCellValue('D16',$analyz->LIMF);
        $sheet->setCellValue('D17',$analyz->MONO);
        $sheet->setCellValue('D18',$analyz->PLAZM);
        $sheet->setCellValue('D19',$analyz->SOE);
        $sheet->setCellValue('D20',$analyz->OSOTM);
        $sheet->setCellValue('D21',$analyz->GEMAT);
        $sheet->setCellValue('D22',$analyz->TROMBOCIT);
        $sheet->setCellValue('D23',$analyz->TROMBOKRIT);

        $sheet->mergeCells('C25:D25');
        $sheet->setCellValue('C25', 'Подпись:______________');
        $sheet->getStyle('C25')->getAlignment()->setHorizontal('right');

        $sheet->getStyle('B6:B23')->getAlignment()->setHorizontal('left');
        $sheet->getStyle('C6:C23')->getAlignment()->setHorizontal('left');
        $sheet->getStyle('D6:D23')->getAlignment()->setHorizontal('left');

        $sheet->getStyle('A5:D23')
            ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);



        $filename='/Анализ крови '.$pacient->KLICHKA.' от '.date('d.m.Y',strtotime($analyz->DATE)).'.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save(Yii::getAlias('@analysis').$filename);
        $path = \Yii::getAlias('@analysis');

        $file = $path.$filename;
        if (file_exists($file)) {
            \Yii::$app->response->sendFile($file);
        }else {
            throw new \Exception('Файл не найден');
        }
    }

    public function actionPrintbiohim(){
        Yii::setAlias('@analysis', Yii::$app->basePath . '/Анализы');
        $analyz=Biohim::findOne(['ID_BIOHIM'=>$_GET['ID_BIOHIM']]);
        $pacient=Pacient::findOne(['ID_PAC'=>$analyz->ID_PAC]);
        $client=Client::findOne(['ID_CL'=>$pacient->ID_CL]);
        $vid=Vid::findOne(['ID_VID'=>$pacient->ID_VID]);
        $spreadsheet=new Spreadsheet();
        $sheet=$spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'ЗооДоктор');
        $sheet->mergeCells('A2:D2');
        $sheet->mergeCells('A3:D3');
        $sheet->setCellValue('A2', 'Биохимический анализ крови - '.$vid->NAMEVID.' '.$pacient->KLICHKA.' от '.date('d.m.Y', strtotime($analyz->DATE)));
        $sheet->setCellValue('A3', 'Клиент - '.$client->FAM.' '.$client->NAME.' '.$client->OTCH);


        $titles=[
            [
                'name'=>'Показатель',
                'cell'=>'A',
            ],
            [
                'name'=>'Норма для кошек',
                'cell'=>'B',
            ],
            [
                'name'=>'Норма для собак',
                'cell'=>'C',
            ],
            [
                'name'=>$pacient->KLICHKA,
                'cell'=>'D',
            ]
        ];
        for ($j = 0; $j < count($titles); $j++) {

            $string = $titles[$j]['name'];
            $cellLatter = $titles[$j]['cell'] . 5;
            $sheet->setCellValue($cellLatter, $string);
        }

        $sheet->setCellValue('A6','Белок общий');
        $sheet->setCellValue('A7','Билирубин общий');
        $sheet->setCellValue('A8','Билирубин прямой');
        $sheet->setCellValue('A9','Билирубин непрямой');
        $sheet->setCellValue('A10','Ас-АТ');
        $sheet->setCellValue('A11','Ал-АТ');
        $sheet->setCellValue('A12','Сахар');
        $sheet->setCellValue('A13','Мочевина');
        $sheet->setCellValue('A14','Креатин');
        $sheet->setCellValue('A15','ЛДГ');
        $sheet->setCellValue('A16','Гамма ГТП');
        $sheet->setCellValue('A17','Амилаза');
        $sheet->setCellValue('A18','Калий');
        $sheet->setCellValue('A19','Кальций');
        $sheet->setCellValue('A20','Щалочная фосфатаза');
        $sheet->setCellValue('A21','Фосфор');

        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(17);
        $sheet->getColumnDimension('C')->setWidth(17);
        $sheet->getColumnDimension('D')->setWidth(17);

        $sheet->setCellValue('B6','56-78');
        $sheet->setCellValue('B7','1,2-7,9');
        $sheet->setCellValue('B8','0,3');
        $sheet->setCellValue('B9','0,3');
        $sheet->setCellValue('B10','9,2-145');
        $sheet->setCellValue('B11','18-70');
        $sheet->setCellValue('B12','3,4-6,9');
        $sheet->setCellValue('B13','5,5-11,1');
        $sheet->setCellValue('B14','48,6-165');
        $sheet->setCellValue('B15','55-155');
        $sheet->setCellValue('B16','1-10');
        $sheet->setCellValue('B17','400-700');
        $sheet->setCellValue('B18','3,8-5,3');
        $sheet->setCellValue('B19','2-2,7');
        $sheet->setCellValue('B20','10-50');
        $sheet->setCellValue('B21','0,71-1,9');

        $sheet->setCellValue('C6','59-76');
        $sheet->setCellValue('C7','0,9-10,6');
        $sheet->setCellValue('C8','0,3');
        $sheet->setCellValue('C9','0,3');
        $sheet->setCellValue('C10','17-45');
        $sheet->setCellValue('C11','20-73');
        $sheet->setCellValue('C12','4,4-5,5');
        $sheet->setCellValue('C13','3,1-9,2');
        $sheet->setCellValue('C14','46,2-114');
        $sheet->setCellValue('C15','23-164');
        $sheet->setCellValue('C16','1-10');
        $sheet->setCellValue('C17','165-1350');
        $sheet->setCellValue('C18','3,8-5,6');
        $sheet->setCellValue('C19','3,8-5,6');
        $sheet->setCellValue('C20','0,85-107');
        $sheet->setCellValue('C21','0,97-1,45');

        $sheet->setCellValue('D6',$analyz->BELOK);
        $sheet->setCellValue('D7',$analyz->BILIRUB_OBSH);
        $sheet->setCellValue('D8',$analyz->BILIRUB_PR);
        $sheet->setCellValue('D9',$analyz->BILIRUB_NEPR);
        $sheet->setCellValue('D10',$analyz->AC_AT);
        $sheet->setCellValue('D11',$analyz->AL_AT);
        $sheet->setCellValue('D12',$analyz->SUGAR);
        $sheet->setCellValue('D13',$analyz->MOCH);
        $sheet->setCellValue('D14',$analyz->KREATIN);
        $sheet->setCellValue('D15',$analyz->LDG);
        $sheet->setCellValue('D16',$analyz->GAMMA_GTP);
        $sheet->setCellValue('D17',$analyz->AMILAZA);
        $sheet->setCellValue('D18',$analyz->KALIY);
        $sheet->setCellValue('D19',$analyz->KALCIY);
        $sheet->setCellValue('D20',$analyz->SHELOCH);
        $sheet->setCellValue('D21',$analyz->FOSFOR);


        $sheet->mergeCells('C23:D23');
        $sheet->setCellValue('C23', 'Подпись:______________');
        $sheet->getStyle('C23')->getAlignment()->setHorizontal('right');

        $sheet->getStyle('B6:B21')->getAlignment()->setHorizontal('left');
        $sheet->getStyle('C6:C21')->getAlignment()->setHorizontal('left');
        $sheet->getStyle('D6:D21')->getAlignment()->setHorizontal('left');

        $sheet->getStyle('A5:D21')
            ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);



        $filename='/Биохимический анализ крови '.$pacient->KLICHKA.' от '.date('d.m.Y',strtotime($analyz->DATE)).'.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save(Yii::getAlias('@analysis').$filename);
        $path = \Yii::getAlias('@analysis');

        $file = $path.$filename;
        if (file_exists($file)) {
            \Yii::$app->response->sendFile($file);
        }else {
            throw new \Exception('Файл не найден');
        }
    }

    public function actionPrintmocha(){
        Yii::setAlias('@analysis', Yii::$app->basePath . '/Анализы');
        $analyz=Mocha::findOne(['ID_MOCHA'=>$_GET['ID_MOCHA']]);
        $pacient=Pacient::findOne(['ID_PAC'=>$analyz->ID_PAC]);
        $client=Client::findOne(['ID_CL'=>$pacient->ID_CL]);
        $vid=Vid::findOne(['ID_VID'=>$pacient->ID_VID]);
        $spreadsheet=new Spreadsheet();
        $sheet=$spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'ЗооДоктор');
        $sheet->mergeCells('A2:D2');
        $sheet->mergeCells('A3:D3');
        $sheet->setCellValue('A2', 'Общий анализ мочи - '.$vid->NAMEVID.' '.$pacient->KLICHKA.' от '.date('d.m.Y', strtotime($analyz->DATE)));
        $sheet->setCellValue('A3', 'Клиент - '.$client->FAM.' '.$client->NAME.' '.$client->OTCH);


        $titles=[
            [
                'name'=>'Показатель',
                'cell'=>'A',
            ],
            [
                'name'=>'Норма для кошек',
                'cell'=>'B',
            ],
            [
                'name'=>'Норма для собак',
                'cell'=>'C',
            ],
            [
                'name'=>$pacient->KLICHKA,
                'cell'=>'D',
            ]
        ];
        for ($j = 0; $j < count($titles); $j++) {

            $string = $titles[$j]['name'];
            $cellLatter = $titles[$j]['cell'] . 5;
            $sheet->setCellValue($cellLatter, $string);
        }

        $sheet->setCellValue('A6','Количество, цвет и др.');
        $sheet->setCellValue('A7','Белок');
        $sheet->setCellValue('A8','Сахар');
        $sheet->setCellValue('A9','Ацетон');
        $sheet->setCellValue('A10','Уробин');
        $sheet->setCellValue('A11','Реакция');
        $sheet->setCellValue('A12','Лейкоциты');
        $sheet->setCellValue('A13','Эритроциты');
        $sheet->setCellValue('A14','Цилиндроиды гиалиновые');
        $sheet->setCellValue('A15','Цилиндроиды зернистые');
        $sheet->setCellValue('A16','Цилиндроиды восковидные');
        $sheet->setCellValue('A17','Цилиндроиды');
        $sheet->setCellValue('A18','Эпителий');
        $sheet->setCellValue('A19','Эпителий почечный');
        $sheet->setCellValue('A20','Эпителий плоский');
        $sheet->setCellValue('A21','	Слизь');
        $sheet->setCellValue('A22','	Соли');
        $sheet->setCellValue('A23','	Бактерии');

        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(17);
        $sheet->getColumnDimension('C')->setWidth(17);
        $sheet->getColumnDimension('D')->setWidth(17);



        $sheet->setCellValue('D6',$analyz->KOL);
        $sheet->setCellValue('D7',$analyz->BELOK);
        $sheet->setCellValue('D8',$analyz->SUGAR);
        $sheet->setCellValue('D9',$analyz->ACETONE);
        $sheet->setCellValue('D10',$analyz->UROB);
        $sheet->setCellValue('D11',$analyz->REACT);
        $sheet->setCellValue('D12',$analyz->LEIC);
        $sheet->setCellValue('D13',$analyz->ERIT);
        $sheet->setCellValue('D14',$analyz->CIL_GAL);
        $sheet->setCellValue('D15',$analyz->CIL_ZERN);
        $sheet->setCellValue('D16',$analyz->CIL_VOSK);
        $sheet->setCellValue('D17',$analyz->CILINDROID);
        $sheet->setCellValue('D18',$analyz->EPIT);
        $sheet->setCellValue('D19',$analyz->EPIT_POCH);
        $sheet->setCellValue('D20',$analyz->EPIT_PLOSK);
        $sheet->setCellValue('D21',$analyz->SLIZ);
        $sheet->setCellValue('D22',$analyz->SULT);
        $sheet->setCellValue('D23',$analyz->BAKT);


        $sheet->mergeCells('C25:D25');
        $sheet->setCellValue('C25', 'Подпись:______________');
        $sheet->getStyle('C25')->getAlignment()->setHorizontal('right');

        $sheet->getStyle('B6:B23')->getAlignment()->setHorizontal('left');
        $sheet->getStyle('C6:C23')->getAlignment()->setHorizontal('left');
        $sheet->getStyle('D6:D23')->getAlignment()->setHorizontal('left');

        $sheet->getStyle('A5:D23')
            ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);



        $filename='/Общий анализ мочи '.$pacient->KLICHKA.' от '.date('d.m.Y',strtotime($analyz->DATE)).'.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save(Yii::getAlias('@analysis').$filename);
        $path = \Yii::getAlias('@analysis');

        $file = $path.$filename;
        if (file_exists($file)) {
            \Yii::$app->response->sendFile($file);
        }else {
            throw new \Exception('Файл не найден');
        }
    }

    public function actionExpense_catalog(){

        $expense_catalog = new ActiveDataProvider([
            'query' => Expense_catalog::find(),
            'pagination' => false,


        ]);
        return $this->render('expense_catalog', compact('expense_catalog'));
    }

    public function actionExpenseadd(){

        if ($_GET['ID_EX']!=NULL){
            $model=Expense_catalogForm::findOne(['ID_EX'=>$_GET['ID_EX']]);


        }else{
            $model=new Expense_catalogForm();
            $model->DATE=date('d.m.Y');
        }

        if ( $model->load(Yii::$app->request->post()) ){
            $model->SUMM=$model->PRICE*$model->KOL;
            $model->DATE=date('Y-m-d', strtotime($model->DATE));

            if ($model->save()){

                return $this->redirect("index.php?r=client/expense_catalog");
            }
        }
        return $this->render('expenseadd', compact('model'));
    }

    public function actionExpense_prihod(){

        $expense_prihod = new ActiveDataProvider([
            'query' => Expense_prihod::find(),
            'pagination' => false,


        ]);
        return $this->render('expense_prihod', compact('expense_prihod'));
    }

    public function actionExpense_prihodadd(){

        if ($_GET['ID_EXPR']!=NULL){
            $model=Expense_prihodForm::findOne(['ID_EXPR'=>$_GET['ID_EXPR']]);


        }else{
            $model=new Expense_prihodForm();
            $model->DATE=date('d.m.Y');

        }

        if ( $model->load(Yii::$app->request->post()) ){
            $ex=Expense_catalog::findOne(['ID_EX'=>$model->ID_EX]);
            $ex->PRICE=$model->PRICE;
            $ex->KOL=$ex->KOL+$model->KOL;
            $ex->SUMM=$ex->PRICE*$ex->KOL;
            $ex->save();
            $model->DATE=date('Y-m-d', strtotime($model->DATE));

            if ($model->save()){

                return $this->redirect("index.php?r=client/expense_catalog");
            }
        }
        return $this->render('expense_prihodadd', compact('model'));
    }


    public function actionPrintuzi(){
        Yii::setAlias('@analysis', Yii::$app->basePath . '/Анализы');
        $uzi=Uzi::findOne(['ID_UZI'=>$_GET['ID_UZI']]);
        $pacient=Pacient::findOne(['ID_PAC'=>$uzi->ID_PAC]);
        $client=Client::findOne(['ID_CL'=>$pacient->ID_CL]);
        $vid=Vid::findOne(['ID_VID'=>$pacient->ID_VID]);




        $document=new PhpWord();
        $document->setDefaultFontName('Times New Roman');
        $document->setDefaultFontSize(14);
        $fontStyle = array( 'size'=>11, 'spaceAfter'=>0);
        $document->addFontStyle('myTextStyle', $fontStyle); //myTextStyle - это имя стиля

        $section = $document->addSection();



        $document->addParagraphStyle('p2Style', array('align'=>'top', 'spaceAfter'=>0));
        $section->addText('УЗИ', ['bold'=>true], [ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH  ]);
        $section->addText(date('d.m.Y', strtotime($uzi->DATE)), ['bold'=>true], [ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH  ]);
        $section->addText('Клиент - '.$client->FAM.' '.$client->NAME.' '.$client->OTCH, ['bold'=>true], [ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH  ]);
        $section->addText('Пациент - '.$vid->NAMEVID.' '.$pacient->KLICHKA, ['bold'=>true], [ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH  ]);
        $section->addText('Заключение', ['bold'=>true], [ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER  ]);
        $uzi->OP = str_replace("\n", "<w:br/>", $uzi->OP);

        $section->addText($uzi->OP, [], [ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT  ]);

        $filename='/Узи '.$pacient->KLICHKA.' от '.date('d.m.Y', strtotime($uzi->DATE)).'.docx';
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($document, 'Word2007');
        $objWriter->save(Yii::getAlias('@analysis').$filename);

        $path = \Yii::getAlias('@analysis');

        $file = $path.$filename;
        if (file_exists($file)) {
            \Yii::$app->response->sendFile($file);
        }else {
            throw new \Exception('Файл не найден');
        }

    }

    public function actionPrintother(){
        Yii::setAlias('@analysis', Yii::$app->basePath . '/Анализы');
        $other=Other_isl::findOne(['ID_OTHER'=>$_GET['ID_OTHER']]);
        $pacient=Pacient::findOne(['ID_PAC'=>$other->ID_PAC]);
        $client=Client::findOne(['ID_CL'=>$pacient->ID_CL]);
        $vid=Vid::findOne(['ID_VID'=>$pacient->ID_VID]);




        $document=new PhpWord();
        $document->setDefaultFontName('Times New Roman');
        $document->setDefaultFontSize(14);
        $fontStyle = array( 'size'=>11, 'spaceAfter'=>0);
        $document->addFontStyle('myTextStyle', $fontStyle); //myTextStyle - это имя стиля

        $section = $document->addSection();



        $document->addParagraphStyle('p2Style', array('align'=>'top', 'spaceAfter'=>0));
        $section->addText('ИССЛЕДОВАНИЕ', ['bold'=>true], [ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH  ]);
        $section->addText(date('d.m.Y', strtotime($other->DATE)), ['bold'=>true], [ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH  ]);
        $section->addText('Клиент - '.$client->FAM.' '.$client->NAME.' '.$client->OTCH, ['bold'=>true], [ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH  ]);
        $section->addText('Пациент - '.$vid->NAMEVID.' '.$pacient->KLICHKA, ['bold'=>true], [ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH  ]);
        $section->addText('Заключение', ['bold'=>true], [ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER  ]);
        $other->OP = str_replace("\n", "<w:br/>", $other->OP);

        $section->addText($other->OP, [], [ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT  ]);

        $filename='/Исследование  '.$pacient->KLICHKA.' от '.date('d.m.Y', strtotime($other->DATE)).'.docx';
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($document, 'Word2007');
        $objWriter->save(Yii::getAlias('@analysis').$filename);

        $path = \Yii::getAlias('@analysis');

        $file = $path.$filename;
        if (file_exists($file)) {
            \Yii::$app->response->sendFile($file);
        }else {
            throw new \Exception('Файл не найден');
        }

    }

    public function actionDocuslugi(){
        Yii::setAlias('@analysis', Yii::$app->basePath . '/Анализы');

        $pacient=Pacient::findOne(['ID_PAC'=>$_GET['ID_PAC']]);
        $client=Client::findOne(['ID_CL'=>$pacient->ID_CL]);
        $vid=Vid::findOne(['ID_VID'=>$pacient->ID_VID]);
        $poroda=Poroda::findOne(['ID_POR'=>$pacient->ID_POR]);
        $fio=$client->FAM.' '.$client->NAME.' '.$client->OTCH;
        $adres=$client->STREET.' '.$client->HOUSE.'-'.$client->FLAT;
        if($pacient->VOZR==''){
            $vozr='________';
        }else{
            $vozr=$pacient->VOZR;
        }
        if($pos= strripos($pacient->KLICHKA, '(')){
            $str=strpos($pacient->KLICHKA, "(");
            $name=substr($pacient->KLICHKA, 0, $str);
        }else{
            $name=$pacient->KLICHKA;
        }





        $document=new PhpWord();
        $document->setDefaultFontName('Times New Roman');
        $document->setDefaultFontSize(9);
        $section = $document->addSection(['marginTop' => '400', 'marginLeft'=>'500', 'marginRight'=>'500', 'marginBottom'=>'400']);
        $section->addText('Договор об оказании ветеринарных услуг №___________________________'.date('Y').' год'
            , ['bold'=>true], [ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER  ]);

        $section->addText('ИП Зайцева Наталья Владимировна (Ветеринарная клиника "ЗооДоктор"), именуемая в дальнейшем Клиника, '.
            'действующая на основании свидетельства серии 308592021200018, с одной стороны и '. $fio.' (далее - Владелец Пациента), '.
            'являющийся владельцем животного: '
            , [], [ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0 ]);
        $section->addText('вид животного - '.$vid->NAMEVID.', кличка - '.$name.', пол - '.$pacient->POL.', возраст - '.$vozr.
            ' (далее - Пациент), с другой стороны, вместе в дальнейшем именуемые Стороны, заключили настоящий договор (далее - Договор) '.
            'о нижеследующем:',
            [],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH ]);


        $section->addText('1. Предмет договора:',['bold'=>true],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH , 'spaceAfter'=>0 ]);
        $section->addText('1.1. По настоящему Договору Клиника принимает на себя обязательство по предоставлению Пациенту платных ветеринарных услуг, '.
            'отвечающим требованиям, установленным на территории Российской Федерации, а владелец пациента обязуется оплатить оказанные услуги в соответствии с разделом 5 Договора.'
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);
        $section->addText('1.2. Владелец Пациента при подписании настоящего Договора ознакомлен с перечнем предоставляемых Клиникой услуг и их стоимостью.'
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);
        $section->addText('1.3. Владелец пациента при подписании настоящего Договора ознакомлен с Правилами обслуживания клиентов.'
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);


        $section->addText('2. Условия выполнения работ:',['bold'=>true],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH , 'spaceAfter'=>0  ]);
        $section->addText('2.1. Клиника оказывает ветеринарные услуги Владельцу Пациента после подписания Договора.'
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);
        $section->addText('2.2. В случаях необходимости оказания услуг по стационарному лечению, хирургическим вмешательствам, анестезиологическому пособию Клиника'.
            " оформляет, а Владелец Пациента подписывает Информированное согласие в виде Дополнительного соглашения к настоящему Договору."
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);
        $section->addText('2.3. Клиника предоставляет ветеринарные услуги без лицензии, на основании Федерального Закона "О лицензировании отдельных видов деятельности".'
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);
        $section->addText('2.4. Клиника осуществляет розничную торговлю лекарственными средствами, предназначенными для животных на основании Лицензии № 59-15-3-000228 '.
            "от 29 апреля 2015 года."
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);

        $section->addText('3. Права и обязанности сторон.',['bold'=>true],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0   ]);
        $section->addText('3.1. Владелец Пациента имеет право:'
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);
        $section->addText("3.1.1. Получать ветеринарные услуги, необходимые Пациенту в соответствии с возможностями, которыми располагает Клиника."
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);
        $section->addText("3.1.2. Получать информацию о заболевании животного, плане и ходе лечения, а так же о прогнозах, возможных последствиях и прочих обстоятельствах, которые могут сопровождать или возникать в процессе проведения лечения, операции."
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);
        $section->addText("3.1.3. Требовать проведения по его просьбе консилиума и консультаций других специалистов с условием оплаты им всех расходов."
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);
        $section->addText("3.1.4. Требовать безвозмездного устранения недостатков уменьшения цены в случае ненадлежащего оказания ветеринарных услуг, которые определяется в установленном порядке."
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);
        $section->addText("3.1.5. Отказаться от получения ветеринарной услуги с получением оплаченной суммы за вычетом затрат Клиники, связанных с подготовкой по оказанию услуги."
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);
        $section->addText("3.1.6. Обратиться к Исполнителю с просьбой поменять лечащего врача в процессе оказания ветеринарных услуг."
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);
        $section->addText("3.1.7. Прервать курс лечения по собственному желанию в любое время, за исключением момента введения препарата, обеспечивающего наркозный сон и до полного пробуждения Пациента. Прерывая курс лечения, Владелец Пациента подписывает отказ от дальнейшего лечения."
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);

        $section->addText('3.2. Владелец Пациента обязан:'
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);
        $section->addText("3.2.1. Предоставить полную и достоверную информацию о животном, об имеющихся и перенесенных заболеваниях, травмах, операциях, известных аллергических реакциях, противопоказаниях."
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);
        $section->addText("3.2.2. Своевременно и полностью оплатить лечение в соответствии с п.5 настоящего договора."
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);
        $section->addText("3.2.3. Обеспечить соблюдение назначений врача Пациентом."
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);
        $section->addText("3.2.4. Своевременно информировать специалистов Клиники о любых изменениях самочувствия и состояния здоровья Пациента."
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);
        $section->addText("3.2.5. Неукоснительно выполнять Правила обслуживания посетителей."
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);
        $section->addText("3.2.6. В случае необходимости стационарного лечения, хирургических вмешательств, анестезиологического пособия, инвазивных манипуляций подписать Информированное согласие."
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);
        $section->addText("3.2.7. В клучае отказа подписать Информированное согласие Владелец Пациента обязан подписать Отказ от дальнейшего лечения."
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);
        $section->addText("3.2.8. В клучае несоглачия Владельца Пациента подписать Информированное согласие, Клиника оставляет за собой право отказать Владельцу Пациента в дальнейшем оказании услуг по лечению, без ущерба вышесказанного в п. 3.2.7."
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);
        $section->addText("3.2.9. В случае отказа Владельца Пациента подписать Информированное согласие и\или Отказ от дальнейшего лечения, клиника оставляет за собой право расценивать это как согласие на все дальнейшие ".
            "лечебные мероприятия, экстренно необходимые Пациенту для стабилизации его состояния и на оплату всех оказанных Пациенту услуг."
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);
        $section->addText("3.2.10. Произвести возмещение убытков за ущерб, причиненный им самим или Пациентом любому виду имущества Клиники (уничтожение, порча, повреждение и т.д.) или другим посетителям Клиники (Владельцам Пациента и\или Пациентам)."
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);

        $section->addText('3.3. Клиника имеет право:'
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);
        $section->addText("3.3.1. Назначать специалистов, оказывающих ветеринарную услугу, проводить необходимые консультации, в ходе которых решать вопрос об объеме обследования, выборе метода лечения, привлекать к исполнению обязательств по настоящему Договору третьих лиц, сторонние организации и сторонних специалистов."
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);
        $section->addText("3.3.2. Отказать в оказании платной ветеринарной услуги в случае: "
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);
        $section->addText("3.3.2.1. Любых противопоказаниях, в том числе и к проведению хирургического лечения."
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);
        $section->addText("3.3.2.2. Заведомо известной невозможности достичь результатов лечения."
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);
        $section->addText("3.3.2.3. Неоплаты лечения."
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);
        $section->addText("3.3.2.4. При предоставлении Клинике Владельцем Пациента неполных, недостоверных, а так же заведомо ложных сведений и данных о состоянии здоровья Пациента."
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);
        $section->addText("3.3.2.5. Отказа Владельца Пациента сообщить необходимую информацию."
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);
        $section->addText("3.3.2.6. При агрессивном поведении Владельца Пациента (агрессивное поведение - оскорбительные высказывания или действия, направленные в адрес персонала Клиники или посетителей Клиники)."
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);
        $section->addText("3.3.2.7. При невозможности оказания услуг ввиду агрессивного поведения животного и при отсутсвии возможности введения успакаивающего препарата животному."
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);
        $section->addText("3.3.2.8. Невыполнения Владельцем Пациента предписаний и требований врача, Правил обслуживания без возврата Владельцу Пациента оплаченной суммы."
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);

        $section->addText("3.3.3. В случае возникновения у Пациента неотложных состояний самостоятельно определять объем исследований, манипуляций, оперативных вмешательств, необходимых для установления диагноза, обследования и оказания ветеринарной помощи, не согласованной с Владельцем Пациента ранее и не оговоренной в Информированном согласии."
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);
        $section->addText("3.4. Клиника обязана:"
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);
        $section->addText("3.4.2. Предоставить Владельцу Пациента информцацию о сути заболевания, возможных исходах лечения и прочих обстоятельствах, которые могут сопровождать или возникать в процессе проведения лечения, операции или иных процедур."
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);
        $section->addText("3.4.3. Предоставить Владельцу Пациента информацию о ходе лечения и о характере проводимых процедур, их важности, значимости, степени необходимости и возможных альтернативах."
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);
        $section->addText("3.4.4. Провести по просьбе Владельца Пациента консилиум или консультацию с другими специалистами с условием оплаты Владельцем Пациента всех расходов."
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);
        $section->addText("3.4.5. Оказать Пациенту квалифицированную, качественную помощь. Объективным критерием качественной помощи является соответствие назначенного лечения симптоматическому комплексу или диагнозу."
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);
        $section->addText("Осуществлять процедуры в соответствии с апробированными и признанными методиками лечения, а так же новейшими достижениями в области ветеринарии, доступными для Клиники и в соответствии с условиями настоящего Договора."
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);



        $section->addText('4. Гарантии и ответственность',['bold'=>true],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0   ]);
        $section->addText("4.1. Клиника несет ответственность перед Владельцем Пациента в соответствии с действующим законодательством Российской Федерации только за умышленные действия или бездействие работников, но не более чем в размере реального ущерба, причиненного Пациенту."
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);
        $section->addText("4.2. Клиника не несет ответственность за ущерб, нанесенный Пациенту действиями третьих лиц, сторонних организаций и специалистов."
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);
        $section->addText("4.3. При предоставлении Владельцем Пациента анализов, сделанных сторонними ветеринарными учреждениями (третьими лицами), Клиника исходит из их добросовестности и не несет ответственности в случае предоставления результатов анализов, не соответствующих реальной картине (истории) болезни, а так же возможные последствия в такой ситуации."
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);
        $section->addText("4.4. Клиника не несет ответственности за результаты оказания ветеринарных услуг в случаях несоблюдения Владельцем Пациента рекомендаций по лечению и иных неправомерных действий."
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);
        $section->addText("4.5. Клиника несет ответственность за качество оказываемых услуг или проводимых процедур, равно как и не несет ответственность за достижение\не достижение желаемых результатов лечения или проведения процедуры."
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);

        $section->addText('5. Стоимость услуг и порядок оплаты',['bold'=>true],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0   ]);
        $section->addText("5.1. Стоимость ветеринарных услуг определяется в соответствии с прейскурантом исполнителя."
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);
        $section->addText("5.2. Оплата осуществляется сразу после оказания ветеринарных услуг."
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);
        $section->addText("5.3. Оплата таких ветеринарных услуг, как пребывание в дневном стационаре, хирургические операции, осуществляется авансом платежом в размере не менее 25% от предполагаемой стоимости услуг. Оставшаяся часть оплачивается после оказания этих ветеринарных услуг."
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);

        $section->addText('6. Действие договора',['bold'=>true],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0   ]);
        $section->addText("6.1. Настоящий Договор вступает в силу с момента его подписания и действует в течение 1 (одного) календарного года, а в части взаимных расчетов до полного их завершения. Если ни одна из сторон в течение 30 (тридцати) дней до истечения срока действия настоящего Договора не зависит о намерении его расторгнуть, то Договор считается автоматически пролонгированным на следующий календарный год, количество пролонгаций не ограничено."
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);
        $section->addText("6.2. Договор может быть расторгнут по соглашению сторон досрочно, а так же в случаях, предусмотренных действующим законодательством РФ."
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);
        $section->addText("6.3. Каждая сторона обязуется хранить конфиденциальность в отношении любой информации, которая станет ей известна в связи с исполнением настоящего Договора."
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);
        $section->addText("6.4. Настоящий договор составлен в двух экземплярах, имеющих одинаковую юридическую силу, из которых 1-й экземпляр находится у Клиники, 2-й у Владельца Пациента."
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);

        $section->addText('7. Прочие условия договора',['bold'=>true],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0   ]);
        $section->addText("7.1. Все споры Стороны обязуются разрешать путем переговором. Претензии по поводу качества оказанных услуг рассматриваются главным врачем Клиники или его заместителем. В случае, если не будет достигнуто согласие, споры разрешаются в порядке, установленным действующим законодательством Российской Федерации."
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);
        $section->addText("7.2. Для исполнения обязательств по Договору Доверенному лицу необходимо иметь при себе паспорт или документ, удостоверяющий личность."
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);
        $section->addText("7.3. Я согласен\не согласен на обработку персональных данных и получение информационных уведомлений от Клиники."
            ,[],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>0  ]);

        $section->addText('8. Адреса и реквизиты сторон',['bold'=>true],[ 'align' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter'=>50   ]);



        $styleTable = array('borderSize' => false, 'borderColor' => '999999');

        $cellHCentered = array('align' => 'both');
        $cellVCentered = array('valign' => 'both');

        $document->addTableStyle('Colspan Rowspan', $styleTable);
        $table = $section->addTable('Colspan Rowspan');
        $table->addRow(null, array('tblHeader' => true));
        $table->addCell(6000, $cellVCentered)->addText('Владелец Пациента:', array('bold' => true), $cellHCentered);
        $table->addCell(6000, $cellVCentered)->addText('Клиника: ', array('bold' => true), $cellHCentered);


        $table->addRow();
        if($client->PHONES==''){
            $phone='________________________';
        }else{
            $phone=$client->PHONES;
        }
        $cell=$table->addCell(2000, $cellVCentered);
        $cell->addText('ФИО: '.$fio, null, $cellHCentered);
        $cell->addText('Документ: _______________________________________', null, $cellHCentered);
        $cell->addText('Адрес: '.$adres, null, $cellHCentered);
        $cell->addText('Телефон: '.$phone, null, $cellHCentered);
        $cell->addText('Подпись: ________________________ ', null, $cellHCentered);

        $cell=$table->addCell(2000, $cellVCentered);
        $cell->addText('ИНН 592006771808', null, $cellHCentered);
        $cell->addText('Адрес: Пермский край, г. Чайковский, ул. Кабалевского д. 24а', null, $cellHCentered);
        $cell->addText('Телефон: 89097269449, 4-21-30', null, $cellHCentered);



        $filename='/Договор об оказании вет. услуг '.$pacient->KLICHKA.' ('.$fio.').docx';
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($document, 'Word2007');
        $objWriter->save(Yii::getAlias('@analysis').$filename);
        $path = \Yii::getAlias('@analysis');

        $file = $path.$filename;
        if (file_exists($file)) {
            \Yii::$app->response->sendFile($file);
        }else {
            throw new \Exception('Файл не найден');
        }
    }
    public function actionOplatadelete(){
        $visit=Vizit::findOne(['ID_VISIT'=>$_GET['ID_VISIT']]);
        $oplata=Oplata::findOne(['ID_OPL'=>$_GET['ID_OPL']]);
        $visit->DOLG=$visit->DOLG+$oplata->SUMM;
        $ID_VISIT=$visit->ID_VISIT;
        $visit->save();
        $oplata->delete();
        $this->redirect('index.php?r=client/visit&ID_VISIT='.$ID_VISIT);

    }
    public function actionPr_facility(){
        $facilityId=[];
        foreach ($_GET as $key => $item) {

            if($item==1&&$key!='doctor'){
                array_push($facilityId, $key);
            }


        }
        $totalSumm=0;
        foreach ($facilityId as $item){
            $oldFac=Facility::findOne(['ID_FAC'=>$item]);
            $newFac=new Facility();
            $newFac->ID_PAC=$oldFac->ID_PAC;
            $newFac->ID_CL=$oldFac->ID_CL;
            $newFac->ID_DOC=$_GET['doctor'];
            $newFac->ID_PR=$oldFac->ID_PR;
            $newFac->PRICE=$oldFac->PRICE;
            $newFac->KOL=$oldFac->KOL;
            $newFac->SUMMA=$oldFac->SUMMA;
            $newFac->DATA=date('Y-m-d');
            $newFac->ID_VISIT=$_GET['ID_VISIT'];
            $totalSumm=$totalSumm+$newFac->SUMMA;
            $newFac->save();




        }
        $visit=Vizit::findOne(['ID_VISIT'=>$_GET['ID_VISIT']]);
        $visit->SUMMAV=$visit->SUMMAV+$totalSumm;
        $visit->DOLG=$visit->DOLG+$totalSumm;
        $visit->save();
        $this->redirect('index.php?r=client/visit&ID_VISIT='.$_GET['ID_VISIT']);
    }

}

