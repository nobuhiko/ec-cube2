<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2007 LOCKON CO.,LTD. All Rights Reserved.
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
$include_dir = realpath(dirname( __FILE__));
require_once($include_dir . "/../define.php");
if (!defined("CLASS_PATH")) {
    /** クラスパス */
    define("CLASS_PATH", $include_dir . "/.." . HTML2DATA_DIR . "class/");
}

if (!defined("CLASS_EX_PATH")) {
    /** クラスパス */
    define("CLASS_EX_PATH", $include_dir . "/.." . HTML2DATA_DIR . "class_extends/");
}

if (!defined("CACHE_PATH")) {
    /** キャッシュ生成ディレクトリ */
    define("CACHE_PATH", $include_dir . "/.." . HTML2DATA_DIR . "cache/");
}
require_once(CLASS_EX_PATH . "SC_Initial_Ex.php");
// アプリケーション初期化処理
$objInit = new SC_Initial_Ex();
$objInit->init();

require_once($include_dir . "/.." . HTML2DATA_DIR . "include/module.inc");
require_once(CLASS_EX_PATH . "util_extends/GC_Utils_Ex.php");
require_once(CLASS_EX_PATH . "util_extends/SC_Utils_Ex.php");
require_once(CLASS_EX_PATH . "db_extends/SC_DB_MasterData_Ex.php");
require_once(CLASS_EX_PATH . "db_extends/SC_DB_DBFactory_Ex.php");
require_once(CLASS_PATH . "SC_View.php");
require_once(CLASS_PATH . "SC_DbConn.php");
require_once(CLASS_PATH . "SC_Session.php");
require_once(CLASS_PATH . "SC_Query.php");
require_once(CLASS_PATH . "SC_SelectSql.php");
require_once(CLASS_PATH . "SC_CheckError.php");
require_once(CLASS_PATH . "SC_PageNavi.php");
require_once(CLASS_PATH . "SC_Date.php");
require_once(CLASS_PATH . "SC_Image.php");
require_once(CLASS_PATH . "SC_UploadFile.php");
require_once(CLASS_PATH . "SC_SiteInfo.php");
require_once(CLASS_PATH . "SC_SendMail.php");
require_once(CLASS_PATH . "SC_FormParam.php");
require_once(CLASS_PATH . "SC_CustomerList.php");
require_once(CLASS_PATH . "SC_Customer.php");
require_once(CLASS_PATH . "SC_Pdf.php");
require_once(CLASS_EX_PATH . "helper_extends/SC_Helper_PageLayout_Ex.php");
require_once(CLASS_EX_PATH . "helper_extends/SC_Helper_DB_Ex.php");
require_once(CLASS_EX_PATH . "helper_extends/SC_Helper_Session_Ex.php");

require_once($include_dir . "/.." . HTML2DATA_DIR . "module/Mail.php");
require_once($include_dir . "/.." . HTML2DATA_DIR . "module/Mail/mime.php");

// インストールチェック
SC_Utils_Ex::sfInitInstall();

// セッションハンドラ開始
$objSession = new SC_Helper_Session_Ex();
// セッション初期化・開始
require_once CLASS_PATH . 'session/SC_SessionFactory.php';
$sessionFactory = SC_SessionFactory::getInstance();
$sessionFactory->initSession();
?>
