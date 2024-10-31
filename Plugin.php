<?php

/**
 * AISummary一个还在开发中的ai摘要插件，
 *
 * @package AISummary
 * @author 陈星燎
 * @version 0.1
 * @link http://www.skaco.cn
 */
class AISummary_Plugin implements Typecho_Plugin_Interface
{
    public static function activate()
    {
        Typecho_Plugin::factory('Widget_Abstract_Contents')->excerptEx = array('AISummary_Plugin', 'customExcerpt');
        Typecho_Plugin::factory('Widget_Contents_Post_Edit')->finishPublish = array('AISummary_Plugin', 'onFinishPublish');
    }

    public static function deactivate()
    {
    }

    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $maxLength = new Typecho_Widget_Helper_Form_Element_Text(
            'maxLength',
            NULL,
            '100',
            _t('摘要最大长度'),
            _t('请输入输出摘要的最大文字数量。')
        );
        $form->addInput($maxLength);

        $apiUrl = new Typecho_Widget_Helper_Form_Element_Text(
            'apiUrl',
            NULL,
            'https://api.qster.top/API/v1/chat/?type=text&msg=',
            _t('自定义 API 地址'),
            _t('不填写默认使用 https://api.qster.top/API/v1/chat/?type=text&msg=。')
        );
        $form->addInput($apiUrl);

        $jsonVariable = new Typecho_Widget_Helper_Form_Element_Text(
            'jsonVariable',
            NULL,
            'answer',
            _t('JSON 接口变量名'),
            _t('指定从 JSON 接口返回结果中获取的变量名。')
        );
        $form->addInput($jsonVariable);

        $apiType = new Typecho_Widget_Helper_Form_Element_Radio(
            'apiType',
            [
                'text' => _t('文本接口'),
                'json' => _t('JSON 接口')
            ],
            'text',
            _t('选择 API 类型'),
            _t('选择使用的 API 类型，文本接口或 JSON 接口。')
        );
        $form->addInput($apiType);
    }

    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
    }

    public static function customExcerpt($excerpt, $widget)
    {
        $customContent = $widget->fields->content;
        $maxLength =!empty(Typecho_Widget::widget('Widget_Options')->plugin('AISummary')->maxLength)? (int)Typecho_Widget::widget('Widget_Options')->plugin('AISummary')->maxLength : 100;
        // 设置默认值
        if ($customContent!== null) {
            $excerpt = $customContent;
            if (mb_strlen($excerpt) > $maxLength) {
                $excerpt = mb_substr($excerpt, 0, $maxLength). '...';
            }
        } else {
            $excerpt = '';
        }
        return $excerpt;
    }

    public static function onFinishPublish($contents, $obj)
    {
        $title = $contents['title'];
        $text = $contents['text'];
        $apiResponse = self::callApi($text);
        $db = Typecho_Db::get();
        $rows = $db->fetchRow($db->select()->from('table.fields')->where('cid =?', $obj->cid)->where('name =?', 'content'));
        if ($rows!== null) {
            if (isset($rows['str_value'])) {
                $db->query($db->update('table.fields')->rows(array('str_value' => $apiResponse))->where('cid =?', $obj->cid)->where('name =?', 'content'));
            }
        } else {
            $db->query($db->insert('table.fields')->rows(array('cid' => $obj->cid, 'name' => 'content', 'type' => 'str', 'str_value' => $apiResponse, 'int_value' => 0, 'float_value' => 0)));
        }
        return $contents;
    }

    private static function callApi($text)
    {
        $options = Typecho_Widget::widget('Widget_Options')->plugin('AISummary');
        $maxLength =!empty($options->maxLength)? (int)$options->maxLength : 100;
        $apiUrl =!empty($options->apiUrl)? $options->apiUrl : 'https://api.qster.top/API/v1/chat/?type=text&msg=';
        $apiType =!empty($options->apiType)? $options->apiType : 'text';
        $jsonVariable =!empty($options->jsonVariable)? $options->jsonVariable : 'answer';

        if ($apiType === 'json') {
            $fullUrl = $apiUrl. urlencode("请生成一篇不超过 {$maxLength} 字的摘要: ". $text);
            $maxRetries = 5;
            $retries = 0;
            $waitTime = 2;
            while ($retries < $maxRetries) {
                try {
                    $ch = curl_init($fullUrl);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $response = curl_exec($ch);
                    if (curl_errno($ch)) {
                        throw new Exception(curl_error($ch), curl_errno($ch));
                    }
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                    if ($httpCode == 200 &&!empty($response)) {
                        $data = json_decode($response, true);
                        if (isset($data[$jsonVariable])) {
                            return trim($data[$jsonVariable]);
                        } else {
                            throw new Exception("JSON variable not found.");
                        }
                    }
                    throw new Exception("HTTP status code: ". $httpCode);
                } catch (Exception $e) {
                    $retries++;
                    sleep($waitTime);
                    $waitTime *= 2;
                }
            }
            return "";
        } else {
            $fullUrl = $apiUrl. urlencode("请生成一篇不超过 {$maxLength} 字的摘要: ". $text);
            $maxRetries = 5;
            $retries = 0;
            $waitTime = 2;
            while ($retries < $maxRetries) {
                try {
                    $ch = curl_init($fullUrl);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $response = curl_exec($ch);
                    if (curl_errno($ch)) {
                        throw new Exception(curl_error($ch), curl_errno($ch));
                    }
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                    if ($httpCode == 200 &&!empty($response)) {
                        return trim($response);
                    }
                    throw new Exception("HTTP status code: ". $httpCode);
                } catch (Exception $e) {
                    $retries++;
                    sleep($waitTime);
                    $waitTime *= 2;
                }
            }
            return "";
        }
    }
}
