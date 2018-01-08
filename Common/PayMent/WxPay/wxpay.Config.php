<?php
/**
 * 	配置账号信息
 */
class WxPayConfig
{
    const APPID = 'wxbfde7f784be73a2f';
    const MCHID = '1259955201';
    const KEY = '424aea3ef58412a43712f31a306cbfd4';
    const APPSECRET = '424aea3ef58412a43712f31a306cbfd4';

    const SSLCERT_PATH = '../cert/apiclient_cert.pem';
    const SSLKEY_PATH = '../cert/apiclient_key.pem';

    const CURL_PROXY_HOST = "0.0.0.0";//"10.152.18.220";
    const CURL_PROXY_PORT = 0;//8080;

    const REPORT_LEVENL = 1;
}
