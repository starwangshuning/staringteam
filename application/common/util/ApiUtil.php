<?php
namespace app\common\util;

class ApiUtil
{
    /**
     * 成功返回array对象
     * @param $data 响应数据
     * @param string $message 响应提示信息
     * @param string $status 响应状态
     * @param int $code 响应状态码
     */
    static public function resultArray($data, $message = '操作成功', $status = true ,$code = 200) {

        if( !$status ){
            $code = 404;
        }

        return [
            'status' => $status,
            'message' => $message,
            'data' => $data,
            'code' => $code,
        ];
    }

    /**
     * 当返回的内容没有数据对象时 使用该方法
     * 返回array对象
     * @param string $message 响应提示信息
     * @param bool $status 响应状态
     * @param int $code 响应状态码
     * @return array
     */
    static public function resultMessage($message = '操作成功', $status = true ,$code = 200){

        if( !$status ){
            $code = 404;
        }

        return [
            'status' => $status,
            'message' => $message,
            'code' => $code,
        ];
    }

}