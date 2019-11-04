<?php
/**
 * Created by PhpStorm.
 * User: zhc
 * Date: 2019/10/23
 * Time: 17:20
 */

namespace app\swoole\service;

use think\swoole\Server;
use think\facade\Env;
use exception\BaseException;
use  think\facade\Log;

class Service
{

    // 事件回调定义
    public function onOpen($server, $request)
    {
        echo "server: handshake success with fd{$request->fd}\n";
    }


    public function onMessage($server, $frame)
    {
        echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
        try {
            Log::record('WebSocket请求开始，请求信息[' . json_encode($frame) . ']');
            $data = json_decode($frame->data, true);
            $cmd = $data['cmd'];
            $messge = $data['data'];
            $resut = Event::$cmd($frame->fd, $messge,$server);
            $server->push($resut['fd'], $resut['data']);
        } catch (BaseException $e) {
            Log::record('WebSocket请求异常,异常信息' . $e->getMessage());
            Log::record('WebSocket请求异常,异常信息' . $e->getFile().$e->getLine());
            $res = ['code' => $e->getCode(), 'msg' => $e->getMessage(), 'data' => '', 'cmd' => ''];
        } catch (\Error $er) {
            Log::record('WebSocket请求异常,异常信息' . $er->getMessage());
            Log::record('WebSocket请求异常,异常信息' . $er->getFile().$er->getLine());
            $res = ['code' => $er->getCode(), 'msg' => $er->getMessage(), 'data' => '', 'cmd' => ''];
        } catch (\Exception $era) {
            Log::record('WebSocket请求异常,异常信息' . $era->getMessage());
            Log::record('WebSocket请求异常,异常信息' . $era->getFile().$era->getLine());
            $res = ['code' => $era->getCode(), 'msg' => $era->getMessage(), 'data' => '', 'cmd' => ''];
        } catch (\ErrorException $ere) {
            Log::record('WebSocket请求异常,异常信息' . $ere->getMessage());
            Log::record('WebSocket请求异常,异常信息' . $ere->getFile().$ere->getLine());
            $res = ['code' => $ere->getCode(), 'msg' => $ere->getMessage(), 'data' => '', 'cmd' => ''];
        }
        if(isset($res)){
            $server->push($frame->fd, json_encode($res));
        }

    }

    public function onRequest($request, $response)
    {
        $response->end("<h1>Hello Swoole. #" . rand(1000, 9999) . "</h1>");
    }

    public function onClose($server, $fd)
    {
        try {
            Log::record('WebSocket关闭请求开始，请求信息[' . json_encode($server) . ']');
            $resut = Event::disconnect($fd,$server);
            echo "client {$fd} closed\n";
        } catch (BaseException $e) {
            Log::record('WebSocket请求异常,异常信息' . $e->getMessage());
            Log::record('WebSocket请求异常,异常信息' . $e->getFile().$e->getLine());

        } catch (\Error $er) {
            Log::record('WebSocket请求异常,异常信息' . $er->getMessage());
            Log::record('WebSocket请求异常,异常信息' . $er->getFile().$er->getLine());

        } catch (\Exception $era) {
            Log::record('WebSocket请求异常,异常信息' . $era->getMessage());
            Log::record('WebSocket请求异常,异常信息' . $era->getFile().$era->getLine());

        } catch (\ErrorException $ere) {
            Log::record('WebSocket请求异常,异常信息' . $ere->getMessage());
            Log::record('WebSocket请求异常,异常信息' . $ere->getFile().$ere->getLine());
        }

    }


}
