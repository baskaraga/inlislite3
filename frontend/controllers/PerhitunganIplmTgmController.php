<?php
/**
 * @link https://www.inlislite.perpusnas.go.id/
 * @copyright Copyright (c) 2015 Perpustakaan Nasional Republik Indonesia
 * @license https://www.inlislite.perpusnas.go.id/licences
 */

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Html;


// Model
use common\models\Memberguesses;
use common\models\VPertumbJmlKunjunganBulanan;
use common\models\VPertumbJmlKunjungan;
use common\models\JenisAnggota;

use \DateTime;
use \DateInterval;
use \DatePeriod;

// Component


/**
* PerhitunganIplmTgmController implements the create actions for Members model.
* @author 
*/

class PerhitunganIplmTgmController extends Controller
{
    public $layout = 'base-layout';

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        if($_GET){
            echo'<pre>';print_r($_GET);

            if (isset($_GET['periode'])) 
            {
                if ($_GET['periode'] == "bulanan") 
                {
                    $periode_format = 'DATE_FORMAT(catalogs.CreateDate,"%M-%Y") Periode';
                    $periode = yii::t('app','Bulanan');
                    $periode2 = yii::t('app','Periode ').date("M", mktime(0, 0, 0, $_GET['fromBulan'], 10)).'-'.$_GET['fromTahun'].' s/d '.date("M", mktime(0, 0, 0, $_GET['toBulan'], 10)).'-'.$_GET['toTahun'];
                    $sqlPeriode = "BETWEEN '".date("Y-m-d", mktime(0,0,0,$_GET['fromBulan'],1,$_GET['fromTahun']))."' AND '".date("Y-m-t", mktime(0,0,0,$_GET['toBulan'],1,$_GET['toTahun']))."' ";
                    // $groupJmlPendidikan = " DATE_FORMAT(`members`.`CreateDate`, '%M') ";
                    
                    $dateStart = date("Y-m-d", mktime(0,0,0,$_GET['fromBulan'],1,$_GET['fromTahun']));
                    $dateEnd = date("Y-m-d", mktime(0,0,0,$_GET['toBulan'],1,$_GET['toTahun']));
                    
                    $timeStart = strtotime("$dateStart");
                    $timeEnd = strtotime("$dateEnd");
                    // Menambah bulan ini + semua bulan pada tahun sebelumnya
                    $numBulan = 1 + (date("Y",$timeEnd)-date("Y",$timeStart))*12;
                    // menghitung selisih bulan
                    $numBulan += date("m",$timeEnd)-date("m",$timeStart);
                    $numTahun = (date("Y",$timeEnd)-date("Y",$timeStart));
                    // print_r($sqlPeriode);

                    // Untuk statistik pertumbuhan Jumlah Anggota Perjenis
                    $begin = new DateTime( $dateStart );
                    $end = new DateTime( $dateEnd);
                    $interval = new DateInterval('P1M'); //Add 1 week
                    $period = new DatePeriod($begin, $interval, $end);
                    $aResult = array();
                    foreach ( $period as $dt )
                    {
                        $PerJmlAnggota = JenisAnggota::find()->select('jenisanggota')->asArray()->all(); 
                        foreach($PerJmlAnggota as $jenis)
                        {
                            $sqlPerJmlAnggota = "SELECT SUM(jumlah) AS jumlah, jenisanggota, tahun FROM  v_stat_anggota WHERE jenisanggota = '".$jenis['jenisanggota']."' and tahun = ".$dt->format('Y'). " AND bulan =".$dt->format('m'); 
                            // echo'<pre>';print_r($sqlPerJmlAnggota);
                            $pertumbuhananggotaVal = Yii::$app->db->createCommand($sqlPerJmlAnggota)->queryOne();
                            
                            $dataJumlahAnggota[$jenis['jenisanggota']][] = intval($pertumbuhananggotaVal['jumlah']);
                        }
                         
                    }
                    if($dataJumlahAnggota){
                        foreach ($dataJumlahAnggota as $key => $value) {
                            $totalJenis[] = [
                                'name' => $key,
                                'data' => $value
                            ];
                        }
                    }
                    

                    $content['totalJenis'] = $totalJenis;
                    // !Untuk statistik pertumbuhan Jumlah Anggota Perjenis


                    $databulan = array();
                    
                    foreach ( $period as $dt )
                    {

                        //Untuk Statistik Jumlah Kunjungan
                        $nonanggota = VPertumbJmlKunjunganBulanan::find()->where(['kriteria'=>'NONANGGOTA','tahun'=>$dt->format('Y'),'bulan'=>$dt->format('m')])->one();
                        $valNonAnggota[] = intval($nonanggota['jumlah']);

                        $anggota = VPertumbJmlKunjunganBulanan::find()->where(['kriteria'=>'ANGGOTA','tahun'=>$dt->format('Y'),'bulan'=>$dt->format('m')])->one();
                        $valAnggota[] = intval($anggota['jumlah']);

                        $rombongan = VPertumbJmlKunjunganBulanan::find()->where(['kriteria'=>'ROMBONGAN','tahun'=>$dt->format('Y'),'bulan'=>$dt->format('m')])->one();
                        $valRombongan[] = intval($rombongan['jumlah']);

                        //Untuk Statistik Jumlah Koleksi
                        $sqlPerJmlKoleksi = "SELECT * FROM v_stat_jumlah_koleksi WHERE tahun = ".$dt->format('Y')." and bulan = ".$dt->format('m');
                        $KoleksiVal = Yii::$app->db->createCommand($sqlPerJmlKoleksi)->queryOne(); 
                        // $pertumbuhanKoleksi[] = intval($KoleksiVal['jumlah']);
                        
                        $koleksiJudul[] = intval($KoleksiVal['jumlah_judul']);
                        $koleksiEksemplar[] = intval($KoleksiVal['jumlah_eksemplar']);
                        $koleksiDigital[] = intval($KoleksiVal['jumlah_dijital']);

                        // //Untuk Statistik Jumlah Koleksi dipinjam
                        // // ** Eksemplar
                        $sqlKoleksiDipinjamEksemplar = " SELECT 
                            YEAR(STR_TO_DATE(`colit`.`CreateDate`, '%Y-%m-%d')) AS `tahun`,
                            MONTH(STR_TO_DATE(`colit`.`CreateDate`, '%Y-%m-%d')) AS `bulan`,
                            COUNT(DISTINCT `colit`.`Collection_id`) AS `jumlah_eksemplar`
                            FROM
                            `collectionloanitems` `colit`
                            WHERE
                            (`colit`.`CreateDate` ".$sqlPeriode.")
                            AND YEAR(STR_TO_DATE(`colit`.`CreateDate`, '%Y-%m-%d')) = ".$dt->format('Y')."
                            AND MONTH(STR_TO_DATE(`colit`.`CreateDate`, '%Y-%m-%d')) = ".$dt->format('m')."
                            GROUP BY YEAR(STR_TO_DATE(`colit`.`CreateDate`, '%Y-%m-%d')) , MONTH(STR_TO_DATE(`colit`.`CreateDate`, '%Y-%m-%d'))
                        "; 
                        // echo "<br/>";
                        $koleksiDipinjamEksemplarVal = Yii::$app->db->createCommand($sqlKoleksiDipinjamEksemplar)->queryOne();
                        $koleksiDipinjamEksemplar[] = intval($koleksiDipinjamEksemplarVal['jumlah_eksemplar']);


                        // ** Judul
                        // $sqlKoleksiDipinjamJudul = "SELECT * FROM  v_stat_koleksi_dipinjam_judul WHERE tahun = ".$thn." and bulan = ".$bln; 
                        $sqlKoleksiDipinjamJudul = " SELECT 
                            YEAR(STR_TO_DATE(`colit`.`CreateDate`, '%Y-%m-%d')) AS `tahun`,
                            MONTH(STR_TO_DATE(`colit`.`CreateDate`, '%Y-%m-%d')) AS `bulan`,
                            COUNT(DISTINCT `cole`.`Catalog_id`) AS `jumlah_judul`
                            FROM
                            `collectionloanitems` `colit`
                            INNER JOIN `collections` `cole` ON `colit`.`Collection_id` = `cole`.`ID`
                            WHERE
                            (`colit`.`CreateDate` ".$sqlPeriode.")
                            AND YEAR(STR_TO_DATE(`colit`.`CreateDate`, '%Y-%m-%d')) =  ".$dt->format('Y')."
                            AND MONTH(STR_TO_DATE(`colit`.`CreateDate`, '%Y-%m-%d')) =  ".$dt->format('m')."
                            GROUP BY YEAR(STR_TO_DATE(`colit`.`CreateDate`, '%Y-%m-%d')) , MONTH(STR_TO_DATE(`colit`.`CreateDate`, '%Y-%m-%d'))
                            "; 
                        $koleksiDipinjamJudulVal = Yii::$app->db->createCommand($sqlKoleksiDipinjamJudul)->queryOne();
                        $koleksiDipinjamJudul[] = intval($koleksiDipinjamJudulVal['jumlah_judul']);

                        // //Untuk Statistik Jumlah Koleksi DIbaca Ditempat
                        // // ** Eksemplar
                        $sqlKoleksiDibacaEksemplar = "  SELECT 
                                YEAR(STR_TO_DATE(`baca`.`CreateDate`, '%Y-%m-%d')) AS `tahun`,
                                MONTH(STR_TO_DATE(`baca`.`CreateDate`, '%Y-%m-%d')) AS `bulan`,
                                COUNT(DISTINCT `baca`.`collection_id`) AS `jumlah_eksemplar`
                                FROM
                                `bacaditempat` `baca`
                                WHERE
                                  (`baca`.`CreateDate` ".$sqlPeriode.")
                                 AND YEAR(STR_TO_DATE(`baca`.`CreateDate`, '%Y-%m-%d')) = ".$dt->format('Y')."
                                 AND MONTH(STR_TO_DATE(`baca`.`CreateDate`, '%Y-%m-%d')) = ".$dt->format('m')."
                                GROUP BY YEAR(STR_TO_DATE(`baca`.`CreateDate`, '%Y-%m-%d')) , MONTH(STR_TO_DATE(`baca`.`CreateDate`, '%Y-%m-%d'))
                        "; 
                        // echo "<br/>";
                        $koleksiDibacaEksemplarVal = Yii::$app->db->createCommand($sqlKoleksiDibacaEksemplar)->queryOne();
                        $koleksiDibacaEksemplar[] = intval($koleksiDibacaEksemplarVal['jumlah_eksemplar']);



                        // ** Judul
                        $sqlKoleksiDibacaJudul = "  SELECT 
                            YEAR(STR_TO_DATE(`baca`.`CreateDate`, '%Y-%m-%d')) AS `tahun`,
                            MONTH(STR_TO_DATE(`baca`.`CreateDate`, '%Y-%m-%d')) AS `bulan`,
                            COUNT(DISTINCT `cole`.`Catalog_id`) AS `jumlah_judul`
                            FROM
                            `bacaditempat` `baca`
                            INNER JOIN `collections` `cole` ON `baca`.`collection_id` = `cole`.`ID`
                            WHERE
                              (`baca`.`CreateDate` ".$sqlPeriode.")
                             AND YEAR(STR_TO_DATE(`baca`.`CreateDate`, '%Y-%m-%d')) = ".$dt->format('Y')."
                             AND MONTH(STR_TO_DATE(`baca`.`CreateDate`, '%Y-%m-%d')) = ".$dt->format('m')."
                            GROUP BY YEAR(STR_TO_DATE(`baca`.`CreateDate`, '%Y-%m-%d')) , MONTH(STR_TO_DATE(`baca`.`CreateDate`, '%Y-%m-%d'))
                            "; 
                        $koleksiDibacaJudulVal = Yii::$app->db->createCommand($sqlKoleksiDibacaJudul)->queryOne();
                        $koleksiDibacaJudul[] = intval($koleksiDibacaJudulVal['jumlah_judul']);


                        $catbulan[] = $dt->format('F').' - '.$dt->format('Y');
                    }

                    // Untuk Statistik Jenis Pendidikan
                    $sqlJenisPendidikan = "call get_stat_jenis_pendidikan_bulan('".date('Y-m-d', $timeStart)."', '".date('Y-m-d', $timeEnd)."');";
                    $jenisPendidikan = Yii::$app->db->createCommand($sqlJenisPendidikan)->queryAll(); 
                    // echo'<pre>';print_r($jenisPendidikan);die;
                    
                    $isiPendidikan = array();
                    foreach ($jenisPendidikan as $jenisPendidikan) {
                        $bln = date_format(date_create($jenisPendidikan['Tanggal']), 'M');
                        $thn = date_format(date_create($jenisPendidikan['Tanggal']), 'Y');
                        
                        $isiPendidikan[] = [$jenisPendidikan['Keterangan'].' '.$bln.' '.$thn, intval($jenisPendidikan['Jumlah']) ];
                    }

                    // Untuk Statistik Range Umur
                    // $sqlRangeUmur = "SELECT * FROM v_stat_rangeumur_kunj WHERE tahun = ".$tahun;
                    $sqlRangeUmur = "call get_stat_range_umur('".date('Y-m-d', $timeStart)."', '".date('Y-m-d', $timeEnd)."');";
                    $rangeUmur = Yii::$app->db->createCommand($sqlRangeUmur)->queryAll(); 

                    // echo $sqlRangeUmur;die;

                    $isiUmur = array();
                    foreach ($rangeUmur as $rangeUmur) {
                        array_push($isiUmur,['name' => $rangeUmur['Keterangan'],'y'=> intval($rangeUmur['Jumlah']) ]);
                    }

                    //Untuk Statistik Kelas Subject
                    // $sqlKelasSubject = "SELECT * FROM v_stat_kelas_subjek WHERE tahun = ".$tahun;
                    $sqlKelasSubject = "call get_stat_kelas_subject_koleksi('".date('Y-m-d', $timeStart)."', '".date('Y-m-d', $timeEnd)."');";
                    $kelasSubject = Yii::$app->db->createCommand($sqlKelasSubject)->queryAll(); 
                   
                    $isiKelas = array();
                    foreach ($kelasSubject as $kelasSubject) {
                        array_push($isiKelas,['name' => $kelasSubject['Name'],'y'=> intval($kelasSubject['CountEksemplar']) ]);
                    }

                    //Untuk Statistik Kelas Subject Koleksi Dipinjam
                    // $sqlKelasSubject = "SELECT * FROM v_stat_kelas_subjek WHERE tahun = ".$tahun;
                    $sqlKelasSubjectKolDipinjam = "call getStatKlasSubjekKoleksiDipinjam('".date('Y-m-d', $timeStart)."', '".date('Y-m-d', $timeEnd)."');";
                    $kelasSubjectKolDipinjam = Yii::$app->db->createCommand($sqlKelasSubjectKolDipinjam)->queryAll(); 
                   
                    $isiKelasKolDipinjam = array();
                    foreach ($kelasSubjectKolDipinjam as $kelasSubjectKolDipinjam) {
                        array_push($isiKelasKolDipinjam,['name' => $kelasSubjectKolDipinjam['NAME'],'y'=> intval($kelasSubjectKolDipinjam['CountEksemplar']) ]);
                    }
                    // print_r($kelasSubjectKolDipinjam);die; 

                    //Untuk Statistik Kelas Subject Koleksi Dibaca
                    // $sqlKelasSubject = "SELECT * FROM v_stat_kelas_subjek WHERE tahun = ".$tahun;
                    $sqlKelasSubjectKolDibaca = "call getStatKlasSubjekKoleksiDibaca('".date('Y-m-d', $timeStart)."', '".date('Y-m-d', $timeEnd)."');";
                    $kelasSubjectKolDibaca = Yii::$app->db->createCommand($sqlKelasSubjectKolDibaca)->queryAll(); 
                   
                    $isiKelasKolDibaca = array();
                    foreach ($kelasSubjectKolDibaca as $kelasSubjectKolDibaca) {
                        array_push($isiKelasKolDibaca,['name' => $kelasSubjectKolDibaca['NAME'],'y'=> intval($kelasSubjectKolDibaca['CountEksemplar']) ]);
                    }
                    // print_r($kelasSubjectKolDibaca);die;
                    
                } 
                elseif ($_GET['periode'] == "tahunan") 
                {
                    $periode_format = 'DATE_FORMAT(catalogs.CreateDate,"%Y") Periode';
                    $periode = yii::t('app','Tahunan');
                    $periode2 = yii::t('app','Periode ').$_GET['fromTahunan'].' s/d '.$_GET['toTahunan'];
                    $sqlPeriode = "BETWEEN '".date("Y-m-d", mktime(0,0,0,1,1,$_GET['fromTahunan']))."' AND '".date("Y-m-d", mktime(0,0,0,12,31,$_GET['toTahunan']))."' ";
                    $dateStart = date("Y-m-d", mktime(0,0,0,1,1,$_GET['fromTahunan']));
                    $dateEnd = date("Y-m-d", mktime(0,0,0,12,31,$_GET['toTahunan']));
                    $groupJmlPendidikan = " DATE_FORMAT(`members`.`CreateDate`, '%Y') ";
                    

                    $timeStart = strtotime("$dateStart");
                    $timeEnd = strtotime("$dateEnd");
                    $numTahun = (date("Y",$timeEnd)-date("Y",$timeStart));


                    // Untuk statistik pertumbuhan Jumlah Anggota Perjenis
                    $PerJmlAnggota = JenisAnggota::find()->select('jenisanggota')->asArray()->all(); 
                    foreach($PerJmlAnggota as $jenis)
                    {
                        
                        for ($i=$numTahun; $i >= 0; $i--) 
                        { 
                            $thn = date("Y",$timeEnd)-$i;  // waktu saat ini dikurang 1 tahun 
                            $sqlPerJmlAnggota = "SELECT SUM(jumlah) AS jumlah, jenisanggota, tahun FROM  v_stat_anggota WHERE jenisanggota = '".$jenis['jenisanggota']."' and tahun = ".$thn;    
                            // echo'<pre>';print_r($sqlPerJmlAnggota);
                            
                            
                            $pertumbuhananggotaVal = Yii::$app->db->createCommand($sqlPerJmlAnggota)->queryOne();
                            
                            $dataJumlahAnggota[$jenis['jenisanggota']][] = intval($pertumbuhananggotaVal['jumlah']);
                            
                        }
                    }

                    foreach ($dataJumlahAnggota as $key => $value) {
                        $totalJenis[] = [
                            'name' => $key,
                            'data' => $value
                        ];
                    }

                    $content['totalJenis'] = $totalJenis;
                    // !Untuk statistik pertumbuhan Jumlah Anggota Perjenis


                    // print_r($PerJmlAnggota);die;


                    $databulan = array();
                    
                    for ($i=$numTahun; $i>= 0; $i--) 
                    { 
                        $thn = date("Y",$timeEnd)-$i;

                        //Untuk Statistik Jumlah Kunjungan
                        // echo 'tahun'.$thn.'bulan'.$bln.'<br>';
                        $nonanggota = VPertumbJmlKunjungan::find()->where(['kriteria'=>'NONANGGOTA','tahun'=>$thn])->one();
                        $valNonAnggota[] = intval($nonanggota['jumlah']);

                        $anggota = VPertumbJmlKunjungan::find()->where(['kriteria'=>'ANGGOTA','tahun'=>$thn])->one();
                        $valAnggota[] = intval($anggota['jumlah']);

                        $rombongan = VPertumbJmlKunjungan::find()->where(['kriteria'=>'ROMBONGAN','tahun'=>$thn])->one();
                        $valRombongan[] = intval($rombongan['jumlah']);

                        //Untuk Statistik Jumlah Koleksi
                        $sqlPerJmlKoleksi = "SELECT * FROM v_stat_jumlah_koleksi WHERE tahun = ".$thn;
                        $KoleksiVal = Yii::$app->db->createCommand($sqlPerJmlKoleksi)->queryOne(); 
                        // $pertumbuhanKoleksi[] = intval($KoleksiVal['jumlah']);
                        
                        $koleksiJudul[] = intval($KoleksiVal['jumlah_judul']);
                        $koleksiEksemplar[] = intval($KoleksiVal['jumlah_eksemplar']);
                        $koleksiDigital[] = intval($KoleksiVal['jumlah_dijital']);

                        // //Untuk Statistik Jumlah Koleksi dipinjam
                        // // ** Eksemplar
                        $sqlKoleksiDipinjamEksemplar = " SELECT 
                            YEAR(STR_TO_DATE(`colit`.`CreateDate`, '%Y-%m-%d')) AS `tahun`,
                            MONTH(STR_TO_DATE(`colit`.`CreateDate`, '%Y-%m-%d')) AS `bulan`,
                            COUNT(DISTINCT `colit`.`Collection_id`) AS `jumlah_eksemplar`
                            FROM
                            `collectionloanitems` `colit`
                            WHERE
                            (`colit`.`CreateDate` ".$sqlPeriode.")
                            AND YEAR(STR_TO_DATE(`colit`.`CreateDate`, '%Y-%m-%d')) = ".$thn."
                            GROUP BY YEAR(STR_TO_DATE(`colit`.`CreateDate`, '%Y-%m-%d'))
                        "; 
                        // echo'<pre>';print_r($sqlKoleksiDipinjamEksemplar);die;
                        // echo "<br/>";
                        $koleksiDipinjamEksemplarVal = Yii::$app->db->createCommand($sqlKoleksiDipinjamEksemplar)->queryOne();
                        $koleksiDipinjamEksemplar[] = intval($koleksiDipinjamEksemplarVal['jumlah_eksemplar']);


                        // ** Judul
                        // $sqlKoleksiDipinjamJudul = "SELECT * FROM  v_stat_koleksi_dipinjam_judul WHERE tahun = ".$thn." and bulan = ".$bln; 
                        $sqlKoleksiDipinjamJudul = " SELECT 
                            YEAR(STR_TO_DATE(`colit`.`CreateDate`, '%Y-%m-%d')) AS `tahun`,
                            MONTH(STR_TO_DATE(`colit`.`CreateDate`, '%Y-%m-%d')) AS `bulan`,
                            COUNT(DISTINCT `cole`.`Catalog_id`) AS `jumlah_judul`
                            FROM
                            `collectionloanitems` `colit`
                            INNER JOIN `collections` `cole` ON `colit`.`Collection_id` = `cole`.`ID`
                            WHERE
                            (`colit`.`CreateDate` ".$sqlPeriode.")
                            AND YEAR(STR_TO_DATE(`colit`.`CreateDate`, '%Y-%m-%d')) =  ".$thn."
                            GROUP BY YEAR(STR_TO_DATE(`colit`.`CreateDate`, '%Y-%m-%d')) 
                            "; 
                        $koleksiDipinjamJudulVal = Yii::$app->db->createCommand($sqlKoleksiDipinjamJudul)->queryOne();
                        $koleksiDipinjamJudul[] = intval($koleksiDipinjamJudulVal['jumlah_judul']);

                        // //Untuk Statistik Jumlah Koleksi DIbaca Ditempat
                        // // ** Eksemplar
                        $sqlKoleksiDibacaEksemplar = "  SELECT 
                                YEAR(STR_TO_DATE(`baca`.`CreateDate`, '%Y-%m-%d')) AS `tahun`,
                                MONTH(STR_TO_DATE(`baca`.`CreateDate`, '%Y-%m-%d')) AS `bulan`,
                                COUNT(DISTINCT `baca`.`collection_id`) AS `jumlah_eksemplar`
                                FROM
                                `bacaditempat` `baca`
                                WHERE
                                  (`baca`.`CreateDate` ".$sqlPeriode.")
                                 AND YEAR(STR_TO_DATE(`baca`.`CreateDate`, '%Y-%m-%d')) = ".$thn."
                                GROUP BY YEAR(STR_TO_DATE(`baca`.`CreateDate`, '%Y-%m-%d')) 
                        "; 
                        // echo "<br/>";
                        $koleksiDibacaEksemplarVal = Yii::$app->db->createCommand($sqlKoleksiDibacaEksemplar)->queryOne();
                        $koleksiDibacaEksemplar[] = intval($koleksiDibacaEksemplarVal['jumlah_eksemplar']);



                        // ** Judul
                        $sqlKoleksiDibacaJudul = "  SELECT 
                            YEAR(STR_TO_DATE(`baca`.`CreateDate`, '%Y-%m-%d')) AS `tahun`,
                            MONTH(STR_TO_DATE(`baca`.`CreateDate`, '%Y-%m-%d')) AS `bulan`,
                            COUNT(DISTINCT `cole`.`Catalog_id`) AS `jumlah_judul`
                            FROM
                            `bacaditempat` `baca`
                            INNER JOIN `collections` `cole` ON `baca`.`collection_id` = `cole`.`ID`
                            WHERE
                              (`baca`.`CreateDate` ".$sqlPeriode.")
                             AND YEAR(STR_TO_DATE(`baca`.`CreateDate`, '%Y-%m-%d')) = ".$thn."
                            GROUP BY YEAR(STR_TO_DATE(`baca`.`CreateDate`, '%Y-%m-%d')) 
                            "; 
                        $koleksiDibacaJudulVal = Yii::$app->db->createCommand($sqlKoleksiDibacaJudul)->queryOne();
                        $koleksiDibacaJudul[] = intval($koleksiDibacaJudulVal['jumlah_judul']);


                        $catbulan[] = $thn;

                    }//die;
                    // echo'<pre>';print_r($valNonAnggota);die;

                    // Untuk Statistik Jenis Pendidikan
                    $sqlJenisPendidikan = "call get_stat_jenis_pendidikan_tahun('".date('Y-m-d', $timeStart)."', '".date('Y-m-d', $timeEnd)."');";
                    $jenisPendidikan = Yii::$app->db->createCommand($sqlJenisPendidikan)->queryAll(); 
                    // echo'<pre>';print_r($jenisPendidikan);die;

                    $isiPendidikan = array();
                    foreach ($jenisPendidikan as $jenisPendidikan) {
                        $thn = date_format(date_create($jenisPendidikan['Tanggal']), 'Y');
                        $isiPendidikan[] = [$jenisPendidikan['Keterangan'].' '.$thn, intval($jenisPendidikan['Jumlah']) ];
                    }


                    // Untuk Statistik Range Umur
                    // $sqlRangeUmur = "SELECT * FROM v_stat_rangeumur_kunj WHERE tahun = ".$tahun;
                    $sqlRangeUmur = "call get_stat_range_umur('".date('Y-m-d', $timeStart)."', '".date('Y-m-d', $timeEnd)."');";
                    $rangeUmur = Yii::$app->db->createCommand($sqlRangeUmur)->queryAll(); 

                    // echo $sqlRangeUmur;die;

                    $isiUmur = array();
                    foreach ($rangeUmur as $rangeUmur) {
                        array_push($isiUmur,['name' => $rangeUmur['Keterangan'],'y'=> intval($rangeUmur['Jumlah']) ]);
                    }

                    
                    //Untuk Statistik Kelas Subject
                    // $sqlKelasSubject = "SELECT * FROM v_stat_kelas_subjek WHERE tahun = ".$tahun;
                    $sqlKelasSubject = "call get_stat_kelas_subject_koleksi('".date('Y-m-d', $timeStart)."', '".date('Y-m-d', $timeEnd)."');";
                    $kelasSubject = Yii::$app->db->createCommand($sqlKelasSubject)->queryAll(); 
                   
                    $isiKelas = array();
                    foreach ($kelasSubject as $kelasSubject) {
                        array_push($isiKelas,['name' => $kelasSubject['Name'],'y'=> intval($kelasSubject['CountEksemplar']) ]);
                    }

                    //Untuk Statistik Kelas Subject Koleksi Dipinjam
                    // $sqlKelasSubject = "SELECT * FROM v_stat_kelas_subjek WHERE tahun = ".$tahun;
                    $sqlKelasSubjectKolDipinjam = "call getStatKlasSubjekKoleksiDipinjam('".date('Y-m-d', $timeStart)."', '".date('Y-m-d', $timeEnd)."');";
                    $kelasSubjectKolDipinjam = Yii::$app->db->createCommand($sqlKelasSubjectKolDipinjam)->queryAll(); 
                   
                    $isiKelasKolDipinjam = array();
                    foreach ($kelasSubjectKolDipinjam as $kelasSubjectKolDipinjam) {
                        array_push($isiKelasKolDipinjam,['name' => $kelasSubjectKolDipinjam['NAME'],'y'=> intval($kelasSubjectKolDipinjam['CountEksemplar']) ]);
                    }
                    // print_r($kelasSubjectKolDipinjam);die;
                    
                    //Untuk Statistik Kelas Subject Koleksi Dibaca
                    // $sqlKelasSubject = "SELECT * FROM v_stat_kelas_subjek WHERE tahun = ".$tahun;
                    $sqlKelasSubjectKolDibaca = "call getStatKlasSubjekKoleksiDibaca('".date('Y-m-d', $timeStart)."', '".date('Y-m-d', $timeEnd)."');";
                    $kelasSubjectKolDibaca = Yii::$app->db->createCommand($sqlKelasSubjectKolDibaca)->queryAll(); 
                   
                    $isiKelasKolDibaca = array();
                    foreach ($kelasSubjectKolDibaca as $kelasSubjectKolDibaca) {
                        array_push($isiKelasKolDibaca,['name' => $kelasSubjectKolDibaca['NAME'],'y'=> intval($kelasSubjectKolDibaca['CountEksemplar']) ]);
                    }
                    // print_r($kelasSubjectKolDibaca);die;
                    
                }
                else 
                {
                    $periode = null;
                }
            }


            // echo'<pre>';print_r($dataJumlahAnggota);die;


                
            // echo'<pre>';print_r($catbulan);die;
            // Category bulanan
            $content['catbulan'] = $catbulan; 
            $content['rangeTahun'] = $periode2;

            //Untuk Statistik Jumlah Kunjungan
            $content['valNonAnggota'] = $valNonAnggota; 
            $content['valAnggota'] = $valAnggota;        
            $content['valRombongan'] = $valRombongan; 

            // Untuk Statistik Range Umur
            $content['rangeUmur'] = $isiUmur; 

            // Untuk Statistik Jumlah Anggota
            $content['pertumbuhananggota'] = $pertumbuhananggotaVal;

            // Untuk Statistik Jenis Pendidikan
            $content['jenisPendidikan'] = $isiPendidikan; 

            //Untuk Statistik Jumlah Koleksi
            // echo'<pre>';print_r($KoleksiVal);die;
            if($KoleksiVal){
                $content['jumlahKoleksi'] =  array(
                    [
                        'name'=> yii::t('app', 'Judul'),
                        'data' => array_values($koleksiJudul)
                    ],
                    [
                        'name'=> yii::t('app', 'Eksemplar'),
                        'data' => array_values($koleksiEksemplar)
                    ],
                    [
                        'name'=> yii::t('app', 'Konten Digital'),
                        'data' => array_values($koleksiDigital)
                    ]
                );     
            }
            

            

            //Untuk Statistik Kelas Subject Koleksi Dipinjam
            $content['kelasSubjectKolDipinjam'] = $isiKelasKolDipinjam; 

            //Untuk Statistik Kelas Subject Koleksi Dibaca
            $content['kelasSubjectKolDibaca'] = $isiKelasKolDibaca; 

            $content['get'] = $_GET;
            return $this->render('_filter', $content);
        }else{
            // $stTahun = VPertumbJmlKunjunganBulanan::find()->select('tahun')->groupBy('tahun')->all();

            // $tahun = 2015;
            $tahun = date("Y");

            $nowMinOnemonth = mktime(0, 0, 0, date("m"), 0, date("Y"));
            $nowMinOneYear = mktime(0, 0, 0, date("m")-12, 1, date("Y"));

            // echo'<pre>';print_r($nowMinOnemonth);
            // echo'<pre>';print_r($nowMinOneYear);die;
            

            $content['tahun'] = $tahun; 
           

             
            return $this->render('index', $content);
        }
        
       
    }




   protected function findModel($id)
    {
        if (($model = Memberguesses::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }



}
