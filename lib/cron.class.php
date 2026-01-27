<?php
/**
 * MoeCrontab 类
 * 用于处理 Linux 风格的计划任务
 */
class MoeCrontab {
    private $tasks = [];
    
    /**
     * 添加计划任务
     * @param string $cronExpression Linux 风格的 cron 表达式
     * @param string $command 要执行的命令，格式为 "App@Method"
     * @return $this
     */
    public function C($cronExpression, $command) {
        $this->tasks[] = [
            'expression' => $cronExpression,
            'command' => $command
        ];
        return $this;
    }
    
    /**
     * 获取所有计划任务
     * @return array
     */
    public function getTasks() {
        return $this->tasks;
    }
    
    /**
     * 检查指定的 cron 表达式是否应该在当前时间执行
     * @param string $cronExpression
     * @return bool
     */
    public function shouldRun($cronExpression) {
        $parts = explode(' ', $cronExpression);
        if (count($parts) != 5) {
            return false;
        }
        
        list($minute, $hour, $dayOfMonth, $month, $dayOfWeek) = $parts;
        $now = time();
        
        // 检查分钟
        if (!$this->match($minute, date('i', $now))) {
            return false;
        }
        
        // 检查小时
        if (!$this->match($hour, date('H', $now))) {
            return false;
        }
        
        // 检查日
        if (!$this->match($dayOfMonth, date('d', $now))) {
            return false;
        }
        
        // 检查月
        if (!$this->match($month, date('m', $now))) {
            return false;
        }
        
        // 检查星期
        if (!$this->match($dayOfWeek, date('w', $now))) {
            return false;
        }
        
        return true;
    }
    
    /**
     * 检查值是否匹配 cron 表达式的部分
     * @param string $expressionPart
     * @param string $value
     * @return bool
     */
    private function match($expressionPart, $value) {
        if ($expressionPart == '*') {
            return true;
        }
        
        if (strpos($expressionPart, '/') !== false) {
            list($range, $step) = explode('/', $expressionPart);
            if ($range == '*') {
                $start = 0;
                $end = ($value < 24) ? 59 : 31; // 分钟或小时的最大值
            } else {
                list($start, $end) = explode('-', $range);
            }
            
            for ($i = $start; $i <= $end; $i += $step) {
                if ($i == $value) {
                    return true;
                }
            }
            return false;
        }
        
        if (strpos($expressionPart, '-') !== false) {
            list($start, $end) = explode('-', $expressionPart);
            return $value >= $start && $value <= $end;
        }
        
        if (strpos($expressionPart, ',') !== false) {
            $values = explode(',', $expressionPart);
            return in_array($value, $values);
        }
        
        return $expressionPart == $value;
    }
    
    /**
     * 运行所有应该在当前时间执行的任务
     */
    public function run() {
        foreach ($this->tasks as $task) {
            if ($this->shouldRun($task['expression'])) {
                $this->execute($task['command']);
            }
        }
    }
    
    /**
     * 执行指定的命令
     * @param string $command
     */
    public function execute($command) {
        list($app, $method) = explode('@', $command);
        
        // 尝试加载应用类并执行方法
        $appFile = __DIR__ . '/../app/' . $app . '.php';
        if (file_exists($appFile)) {
            require_once $appFile;
        }
        
        if (class_exists($app)) {
            $instance = new $app();
            if (method_exists($instance, $method)) {
                $instance->$method();
                echo "[CRON] Executed: $command\n";
                return;
            }
        }
        
        echo "[CRON] Error: Could not execute $command\n";
    }
}
?>