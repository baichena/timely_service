<?php

namespace app\swoole\command;


use Swoole\Process;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\facade\Config;
use think\facade\Env;
use think\swoole\Http as HttpServer;
use think\Container;
use think\swoole\Server as ThinkServer;

/**
 * Swoole 命令行，支持操作：start|stop|restart|reload
 * 支持应用配置目录下的swoole_server.php文件进行参数配置
 */
class Chat extends Command
{
    protected $config = [];

    public function configure()
    {
        $this->setName('chat')
            ->addArgument('action', Argument::OPTIONAL, "start|stop|restart|reload", 'start')
            ->addOption('host', 'H', Option::VALUE_OPTIONAL, 'the host of swoole server.', null)
            ->addOption('port', 'p', Option::VALUE_OPTIONAL, 'the port of swoole server.', null)
            ->addOption('daemon', 'd', Option::VALUE_NONE, 'Run the swoole server in daemon mode.')
            ->setDescription('chat Swoole Server for ThinkPHP');
    }

    public function execute(Input $input, Output $output)
    {
        $action = $input->getArgument('action');
        if (!in_array($action, ['start', 'stop', 'reload', 'restart'])) {
            $output->writeln("<error>Invalid argument action:{$action}, Expected start|stop|restart|reload .</error>");
            return false;
        }
        $this->init();
        $this->$action();


    }

    protected function init()
    {
        $this->config = Config::pull('swoole_server');

        if (empty($this->config['pid_file'])) {
            $this->config['pid_file'] = Env::get('runtime_path') . 'swoole_server.pid';
        }

        // 避免pid混乱
        $this->config['pid_file'] .= '_' . $this->getPort();
    }

    /**
     * 启动server
     * @access protected
     * @return void
     */
    protected function start()
    {
        $pid = $this->getMasterPid();

        if ($this->isRunning($pid)) {
            $this->output->writeln('<error>swoole server process is already running.</error>');
            return false;
        }

        $this->output->writeln('Starting swoole server...');

        if (!empty($this->config['swoole_class'])) {
            $class = $this->config['swoole_class'];

            if (class_exists($class)) {
                $swoole = new $class;
                if (!$swoole instanceof ThinkServer) {
                    $this->output->writeln("<error>Swoole Server Class Must extends \\think\\swoole\\Server</error>");
                    return false;
                }
            } else {
                $this->output->writeln("<error>Swoole Server Class Not Exists : {$class}</error>");
                return false;
            }
        } else {
            $host = $this->getHost();
            $port = $this->getPort();
            $type = !empty($this->config['type']) ? $this->config['type'] : 'socket';
            $mode = !empty($this->config['mode']) ? $this->config['mode'] : SWOOLE_PROCESS;
            $sockType = !empty($this->config['sock_type']) ? $this->config['sock_type'] : SWOOLE_SOCK_TCP;

            switch ($type) {
                case 'socket':
                    $swooleClass = 'Swoole\Websocket\Server';
                    break;
                case 'http':
                    $swooleClass = 'Swoole\Http\Server';
                    break;
                default:
                    $swooleClass = 'Swoole\Server';
            }

            $swoole = new $swooleClass($host, $port, $mode, $sockType);

            // 开启守护进程模式
            if ($this->input->hasOption('daemon')) {
                $this->config['daemonize'] = true;
            }

            foreach ($this->config as $name => $val) {
                if (0 === strpos($name, 'on')) {
                    $swoole->on(substr($name, 2), $val);
                    unset($this->config[$name]);
                }
            }

            // 设置服务器参数
            $swoole->set($this->config);

            $this->output->writeln("Swoole {$type} server started: <{$host}:{$port}>" . PHP_EOL);
            $this->output->writeln('You can exit with <info>`CTRL-C`</info>');

            // 启动服务
            $swoole->start();
        }
    }

    /**
     * 柔性重启server
     * @access protected
     * @return void
     */
    protected function reload()
    {
        // 柔性重启使用管理PID
        $pid = $this->getMasterPid();

        if (!$this->isRunning($pid)) {
            $this->output->writeln('<error>no swoole server process running.</error>');
            return false;
        }

        $this->output->writeln('Reloading swoole server...');
        Process::kill($pid, SIGUSR1);
        $this->output->writeln('> success');
    }

    /**
     * 停止server
     * @access protected
     * @return void
     */
    protected function stop()
    {
        $pid = $this->getMasterPid();

        if (!$this->isRunning($pid)) {
            $this->output->writeln('<error>no swoole server process running.</error>');
            return false;
        }

        $this->output->writeln('Stopping swoole server...');

        Process::kill($pid, SIGTERM);
        $this->removePid();

        $this->output->writeln('> success');
    }

    protected function getHost()
    {
        if ($this->input->hasOption('host')) {
            $host = $this->input->getOption('host');
        } else {
            $host = !empty($this->config['host']) ? $this->config['host'] : '0.0.0.0';
        }

        return $host;
    }

    /**
     * 删除PID文件
     * @access protected
     * @return void
     */
    protected function removePid()
    {
        $masterPid = $this->config['pid_file'];

        if (is_file($masterPid)) {
            unlink($masterPid);
        }
    }

    protected function getPort()
    {
        if ($this->input->hasOption('port')) {
            $port = $this->input->getOption('port');
        } else {
            $port = !empty($this->config['port']) ? $this->config['port'] : 9501;
        }

        return $port;
    }

    /**
     * 获取主进程PID
     * @access protected
     * @return int
     */
    protected function getMasterPid()
    {
        $pidFile = $this->config['pid_file'];

        if (is_file($pidFile)) {
            $masterPid = (int)file_get_contents($pidFile);
        } else {
            $masterPid = 0;
        }

        return $masterPid;
    }

    /**
     * 判断PID是否在运行
     * @access protected
     * @param  int $pid
     * @return bool
     */
    protected function isRunning($pid)
    {
        if (empty($pid)) {
            return false;
        }

        return Process::kill($pid, 0);
    }
}
