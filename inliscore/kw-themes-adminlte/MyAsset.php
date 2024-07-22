<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace inliscore\adminlte;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class MyAsset extends AssetBundle
{
    public $sourcePath = '@inliscore/adminlte/assets';
    public $css = [
        //'css/bootstrap.min.css',
        'css/font-awesome.min.css',
        'css/font-google.css',
        'css/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css',
        'css/AdminLTE.css',
        //'css/kw-style.css',
        'css/iCheck/square/blue.css',
        'css/sweetalert/sweetalert.css',
        'css/morris/morris.css',
    ];
    public $js = [
        //'js/bootstrap.min.js',
        'js/AdminLTE/app.js',
        'js/plugins/slimScroll/jquery.slimscroll.min.js',
        //'js/plugins/fastclick/fastclick.min.js',
        'js/plugins/iCheck/icheck.min.js',
        'js/plugins/jquery-cookie/jquery.cookie.js',
        'js/moment.js',
        'js/jquery.repeater.js',
        'js/livestamp.min.js',
        'js/core.js',
        'js/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js',
        'js/plugins/sweetalert/sweetalert.min.js',
        'js/plugins/jquery-print/jQuery.print.js',
        'js/query-builder.id.js',
        'js/plugins/bootstrap-filestyle/bootstrap-filestyle.min.js',
        //'js/plugins/jquery-barcode.min.js',
        //'js/AdminLTE/demo.js',
        'js/plugins/raphael/raphael-min.js',
        'js/plugins/morris/morris.min.js',
	   //'js/plugins/JsBarcode.all.min.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'leandrogehlen\querybuilder\QueryBuilderAsset',
    ];

    /**
     * @var string|bool Choose skin color, eg. `'skin-blue'` or set `false` to disable skin loading
     * @see https://almsaeedstudio.com/themes/AdminLTE/documentation/index.html#layout
     */
    public $skin = '_all-skins';

    /**
     * @inheritdoc
     */
    public function init()
    {
        // Append skin color file if specified
        if ($this->skin) {
            if (('_all-skins' !== $this->skin) && (strpos($this->skin, 'skin-') !== 0)) {
                throw new Exception('Invalid skin specified');
            }

            $this->css[] = sprintf('css/skins/%s.min.css', $this->skin);
        }

        parent::init();
    }
}
