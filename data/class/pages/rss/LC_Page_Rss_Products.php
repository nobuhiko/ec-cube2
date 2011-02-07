<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2010 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

// {{{ requires
require_once(CLASS_REALDIR . "pages/LC_Page.php");

/**
 * RSS(商品) のページクラス.
 *
 * @package Page
 * @author LOCKON CO.,LTD.
 * @version $Id$
 */
class LC_Page_Rss_Products extends LC_Page {

    // }}}
    // {{{ functions

    /**
     * Page を初期化する.
     *
     * @return void
     */
    function init() {
        parent::init();
        $this->tpl_mainpage = "rss/products.tpl";
        $this->encode = "UTF-8";
        $this->title = "商品一覧情報";
    }

    /**
     * Page のプロセス.
     *
     * @return void
     */
    function process() {
        $this->action();
    }

    /**
     * Page のアクション.
     *
     * @return void
     */
    function action() {
        $objView = new SC_SiteView();
        $objSiteInfo = new SC_SiteInfo();
        
        //店舗情報をセット
        $this->arrSiteInfo = $objSiteInfo->data;
        
        //商品IDを取得
        if ( isset($_GET['product_id']) && $_GET['product_id'] != '' && is_numeric($_GET['product_id']) ) {
            $product_id = $_GET['product_id'];
        } else {
            $product_id = '';
        }
        
        // モードによって分岐
        $mode = $this->getMode();
        switch ($mode) {
        case 'all':
            $arrProduct = $this->lfGetProductsDetailData($mode, $product_id);
            break;
        case 'list':
            if ( $product_id != '' && is_numeric($product_id) ) {
                $arrProduct = $this->lfGetProductsDetailData($mode, $product_id);
            } else {
                $arrProduct = $this->lfGetProductsListData();
            }
            break;
        default:
            if ( $product_id != '' && is_numeric($product_id) ) {
                $arrProduct = $this->lfGetProductsDetailData($mode, $product_id);
            } else {
                $arrProduct = $this->lfGetProductsAllData();
            }
            break;
        }
        
        // 商品情報をセット
        $this->arrProduct = $arrProduct;
        $this->arrProductKeys = $this->lfGetProductKeys($arrProduct);
        
        //セットしたデータをテンプレートファイルに出力
        $objView->assignobj($this);
        
        //キャッシュしない(念のため)
        header("Pragma: no-cache");
        
        //XMLテキスト(これがないと正常にRSSとして認識してくれないツールがあるため)
        header("Content-type: application/xml");
        P_DETAIL_URLPATH;
        
        //画面表示
        $objView->display($this->tpl_mainpage, true);
    }

    /**
     * デストラクタ.
     *
     * @return void
     */
    function destroy() {
        parent::destroy();
    }

    /**
     * lfGetProductsDetailData.
     *
     * @param str $mode モード
     * @param str $product_id 商品ID
     * @return array $arrProduct 商品情報の配列を返す
     */
    function lfGetProductsDetailData($mode, $product_id) {
        $objQuery = SC_Query::getSingletonInstance();
        //商品詳細を取得
        if ( $mode == 'all' ) {
            $arrProduct = $this->lfGetProductsDetail($objQuery, $mode);
        } else {
            $arrProduct = $this->lfGetProductsDetail($objQuery, $product_id);
        }
        // 値の整形
        foreach ($arrProduct as $key => $val) {
            //販売価格を税込みに編集
            $arrProduct[$key]['price02'] = SC_Helper_DB_Ex::sfCalcIncTax($arrProduct[$key]['price02']);
            // 画像ファイルのURLセット
            if ( file_exists(IMAGE_SAVE_REALDIR . $arrProduct[$key]['main_list_image']) ) {
                $dir = IMAGE_SAVE_RSS_URL;
            } else {
                $dir = IMAGE_TEMP_RSS_URL;
            }
            $arrProduct[$key]['main_list_image'] = $dir . $arrProduct[$key]['main_list_image'];
            if ( file_exists(IMAGE_SAVE_REALDIR . $arrProduct[$key]['main_image']) ){
                $dir = IMAGE_SAVE_RSS_URL;
            } else {
                $dir = IMAGE_TEMP_RSS_URL;
            }
            $arrProduct[$key]['main_image'] = $dir . $arrProduct[$key]['main_image'];
            if ( file_exists(IMAGE_SAVE_REALDIR . $arrProduct[$key]['main_large_image']) ) {
                $dir = IMAGE_SAVE_RSS_URL;
            } else {
                $dir = IMAGE_TEMP_RSS_URL;
            }
            $arrProduct[$key]['main_large_image'] = $dir . $arrProduct[$key]['main_large_image'];
            // ポイント計算
            $arrProduct[$key]['point'] = SC_Utils_Ex::sfPrePoint(
                $arrProduct[$key]['price02'],
                $arrProduct[$key]['point_rate'],
                POINT_RULE,
                $arrProduct[$key]['product_id']
            );
            // 在庫無制限
            if ( $arrProduct[$key]['stock_unlimited'] == 1 ) {
                $arrProduct[$key]['stock_unlimited'] = '在庫無制限';
            } else {
                $arrProduct[$key]['stock_unlimited'] = NULL;
            }
        }
        return $arrProduct;
    }

    /**
     * lfGetProductsListData.
     *
     * @return array $arrProduct 商品情報の配列を返す
     */
    function lfGetProductsListData() {
        $objQuery = SC_Query::getSingletonInstance();
        //商品一覧を取得
        $arrProduct = $objQuery->getAll('SELECT product_id, name AS product_name FROM dtb_products');
        return $arrProduct;
    }

    /**
     * lfGetProductsAllData.
     *
     * @return array $arrProduct 商品情報の配列を返す
     */
    function lfGetProductsAllData() {
        $objQuery = SC_Query::getSingletonInstance();
        //商品情報を取得
        $arrProduct = $this->lfGetProductsAllclass($objQuery);
        // 値の整形
        foreach ($arrProduct as $key => $val) {
            //販売価格を税込みに編集
            $arrProduct[$key]['price02_max'] = SC_Helper_DB_Ex::sfCalcIncTax($arrProduct[$key]['price02_max']);
            $arrProduct[$key]['price02_min'] = SC_Helper_DB_Ex::sfCalcIncTax($arrProduct[$key]['price02_min']);
            // 画像ファイルのURLセット
            if ( file_exists(IMAGE_SAVE_REALDIR . $arrProduct[$key]['main_list_image']) ) {
                $dir = IMAGE_SAVE_RSS_URL;
            } else {
                $dir = IMAGE_TEMP_RSS_URL;
            }
            $arrProduct[$key]['main_list_image'] = $dir . $arrProduct[$key]['main_list_image'];
            if ( file_exists(IMAGE_SAVE_REALDIR . $arrProduct[$key]['main_image']) ) {
                $dir = IMAGE_SAVE_RSS_URL;
            } else {
                $dir = IMAGE_TEMP_RSS_URL;
            }
            $arrProduct[$key]['main_image'] = $dir . $arrProduct[$key]['main_image'];
            if ( file_exists(IMAGE_SAVE_REALDIR . $arrProduct[$key]['main_large_image']) ) {
                $dir = IMAGE_SAVE_RSS_URL;
            } else {
                $dir = IMAGE_TEMP_RSS_URL;
            }
            $arrProduct[$key]['main_large_image'] = $dir . $arrProduct[$key]['main_large_image'];
            // ポイント計算
            $arrProduct[$key]['point_max'] = SC_Utils_Ex::sfPrePoint(
                $arrProduct[$key]['price02_max'],
                $arrProduct[$key]['point_rate'],
                POINT_RULE,
                $arrProduct[$key]['product_id']
            );
            $arrProduct[$key]['point_min'] = SC_Utils_Ex::sfPrePoint(
                $arrProduct[$key]['price02_min'],
                $arrProduct[$key]['point_rate'],
                POINT_RULE,
                $arrProduct[$key]['product_id']
            );
        }
        return $arrProduct;
    }

    /**
     * 商品情報を取得する
     *
     * @param SC_Query $objQuery DB操作クラス
     * @param integer $product_id 商品ID
     * @return array $arrProduct 取得結果を配列で返す
     */
    function lfGetProductsDetail(&$objQuery, $product_id = 'all'){
        $sql = '';
        $sql .= 'SELECT ';
        $sql .= '   prod.product_id ';
        $sql .= '   ,prod.name AS product_name ';
        $sql .= '   ,prod.category_id ';
        $sql .= '   ,prod.point_rate ';
        $sql .= '   ,prod.comment3 ';
        $sql .= '   ,prod.main_list_comment ';
        $sql .= '   ,prod.main_list_image ';
        $sql .= '   ,prod.main_comment ';
        $sql .= '   ,prod.main_image ';
        $sql .= '   ,prod.main_large_image ';
        $sql .= '   ,cls.product_code ';
        $sql .= '   ,cls.price01 ';
        $sql .= '   ,cls.price02 ';
        $sql .= '   ,cls.stock ';
        $sql .= '   ,cls.stock_unlimited ';
        $sql .= '   ,cls.classcategory_id1 ';
        $sql .= '   ,cls.classcategory_id2 ';
        $sql .= '   ,( ';
        $sql .= '     SELECT ';
        $sql .= '        name ';
        $sql .= '     FROM ';
        $sql .= '        dtb_classcategory AS clscat ';
        $sql .= '     WHERE ';
        $sql .= '        clscat.classcategory_id = cls.classcategory_id1 ';
        $sql .= '   ) AS classcategory_name1 ';
        $sql .= '   ,( ';
        $sql .= '     SELECT ';
        $sql .= '        name ';
        $sql .= '     FROM ';
        $sql .= '        dtb_classcategory AS clscat ';
        $sql .= '     WHERE ';
        $sql .= '        clscat.classcategory_id = cls.classcategory_id2 ';
        $sql .= '   ) AS classcategory_name2 ';
        $sql .= '   ,( ';
        $sql .= '     SELECT ';
        $sql .= '        category_name ';
        $sql .= '     FROM ';
        $sql .= '        dtb_category AS cat ';
        $sql .= '     WHERE ';
        $sql .= '        cat.category_id = prod.category_id ';
        $sql .= '   ) AS category_name ';
        $sql .= '   ,prod.update_date ';
        $sql .= ' FROM dtb_products AS prod, dtb_products_class AS cls';
        $sql .= ' WHERE prod.product_id = cls.product_id AND prod.del_flg = 0 AND prod.status = 1';

        if($product_id != 'all'){
            $sql .= ' AND prod.product_id = ?';
            $arrval = array($product_id);
        }
        $sql .= ' ORDER BY prod.product_id, cls.classcategory_id1, cls.classcategory_id2';
        $arrProduct = $objQuery->getAll($sql, $arrval);
        return $arrProduct;
    }

    /**
     * 商品情報を取得する(vw_products_allclass使用)
     *
     * @param SC_Query $objQuery DB操作クラス
     * @return array $arrProduct 取得結果を配列で返す
     */
    function lfGetProductsAllclass(&$objQuery){
        $sql = '';
        $sql .= ' SELECT';
        $sql .= '     T1.product_id,';
        $sql .= '     T1.name as product_name,';
        $sql .= '     T1.maker_id,';
        $sql .= '     T1.status,';
        $sql .= '     T1.comment1,';
        $sql .= '     T1.comment2,';
        $sql .= '     T1.comment3,';
        $sql .= '     T1.comment4,';
        $sql .= '     T1.comment5,';
        $sql .= '     T1.comment6,';
        $sql .= '     T1.note,';
        $sql .= '     T1.main_list_comment,';
        $sql .= '     T1.main_list_image,';
        $sql .= '     T1.main_comment,';
        $sql .= '     T1.main_image,';
        $sql .= '     T1.main_large_image,';
        $sql .= '     T1.sub_title1,';
        $sql .= '     T1.sub_comment1,';
        $sql .= '     T1.sub_image1,';
        $sql .= '     T1.sub_large_image1,';
        $sql .= '     T1.sub_title2,';
        $sql .= '     T1.sub_comment2,';
        $sql .= '     T1.sub_image2,';
        $sql .= '     T1.sub_large_image2,';
        $sql .= '     T1.sub_title3,';
        $sql .= '     T1.sub_comment3,';
        $sql .= '     T1.sub_image3,';
        $sql .= '     T1.sub_large_image3,';
        $sql .= '     T1.sub_title4,';
        $sql .= '     T1.sub_comment4,';
        $sql .= '     T1.sub_image4,';
        $sql .= '     T1.sub_large_image4,';
        $sql .= '     T1.sub_title5,';
        $sql .= '     T1.sub_comment5,';
        $sql .= '     T1.sub_image5,';
        $sql .= '     T1.sub_large_image5,';
        $sql .= '     T1.sub_title6,';
        $sql .= '     T1.sub_comment6,';
        $sql .= '     T1.sub_image6,';
        $sql .= '     T1.sub_large_image6,';
        $sql .= '     T1.del_flg,';
        $sql .= '     T1.creator_id,';
        $sql .= '     T1.create_date,';
        $sql .= '     T1.update_date,';
        $sql .= '     T1.deliv_date_id,';
        $sql .= '     T4.product_code_min,';
        $sql .= '     T4.product_code_max,';
        $sql .= '     T4.price01_min,';
        $sql .= '     T4.price01_max,';
        $sql .= '     T4.price02_min,';
        $sql .= '     T4.price02_max,';
        $sql .= '     T4.stock_min,';
        $sql .= '     T4.stock_max,';
        $sql .= '     T4.stock_unlimited_min,';
        $sql .= '     T4.stock_unlimited_max,';
        $sql .= '     T4.class_count,';
        $sql .= '     T3.rank AS category_rank,';
        $sql .= '     T2.category_id,';
        $sql .= '     T2.rank AS product_rank';
        $sql .= ' FROM';
        $sql .= '     dtb_products AS T1';
        $sql .= '     LEFT JOIN';
        $sql .= '         (';
        $sql .= '             SELECT';
        $sql .= '                 product_id,';
        $sql .= '                 MIN(product_code) AS product_code_min,';
        $sql .= '                 MAX(product_code) AS product_code_max,';
        $sql .= '                 MIN(price01) AS price01_min,';
        $sql .= '                 MAX(price01) AS price01_max,';
        $sql .= '                 MIN(price02) AS price02_min,';
        $sql .= '                 MAX(price02) AS price02_max,';
        $sql .= '                 MIN(stock) AS stock_min,';
        $sql .= '                 MAX(stock) AS stock_max,';
        $sql .= '                 MIN(stock_unlimited) AS stock_unlimited_min,';
        $sql .= '                 MAX(stock_unlimited) AS stock_unlimited_max,';
        $sql .= '                 COUNT(*) as class_count';
        $sql .= '             FROM';
        $sql .= '                 dtb_products_class';
        $sql .= '             GROUP BY';
        $sql .= '                 product_id';
        $sql .= '         ) AS T4';
        $sql .= '     ON';
        $sql .= '         T1.product_id = T4.product_id';
        $sql .= '     LEFT JOIN';
        $sql .= '         dtb_product_categories AS T2';
        $sql .= '     ON';
        $sql .= '         T1.product_id = T2.product_id';
        $sql .= '     LEFT JOIN';
        $sql .= '         dtb_category AS T3';
        $sql .= '     ON';
        $sql .= '         T2.category_id = T3.category_id';
        $sql .= ' WHERE';
        $sql .= '     T1.del_flg = 0 AND T1.status = 1 ';
        
        // 在庫無し商品の非表示
        if (NOSTOCK_HIDDEN === true) {
            $sql .= ' AND (T4.stock_max >= 1 OR T4.stock_unlimited_max = 1)';
        }
        
        $sql .= ' ORDER BY';
        $sql .= '     T1.product_id asc';
        
        $arrProduct = $objQuery->getAll($sql);
        return $arrProduct;
        
    }

    /**
     * lfGetProductKeys.
     *
     * @param array $arrProduct 商品データ配列
     * @return array $arrProductKeys 商品情報のkey配列を返す
     */
    function lfGetProductKeys($arrProduct) {
        $arrProductKeys = array();
        $arrProduct = SC_Utils_Ex::sfswaparray($arrProduct);
        if ( is_array($arrProduct) ) {
            $arrProductKeys = array_keys($arrProduct);
        }
        return $arrProductKeys;
    }

}
?>
