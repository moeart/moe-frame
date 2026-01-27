<?php
/**
 * MoeCrontab Class
 * For handling Linux-style scheduled tasks
 */
class MoeCrontab {
    private $tasks = [];
    
    /**
     * Add scheduled task
     * @param string $cronExpression Linux-style cron expression
     * @param string $command Command to execute, format: "App@Method"
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
     * Get all scheduled tasks
     * @return array
     */
    public function getTasks() {
        return $this->tasks;
    }
    
    /**
     * Check if the specified cron expression should run at current time
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
        
        // Check minute
        if (!$this->match($minute, date('i', $now))) {
            return false;
        }
        
        // Check hour
        if (!$this->match($hour, date('H', $now))) {
            return false;
        }
        
        // Check day of month
        if (!$this->match($dayOfMonth, date('d', $now))) {
            return false;
        }
        
        // Check month
        if (!$this->match($month, date('m', $now))) {
            return false;
        }
        
        // Check day of week
        if (!$this->match($dayOfWeek, date('w', $now))) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if value matches cron expression part
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
                $end = ($value < 24) ? 59 : 31; // Max value for minutes or hours
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
     * Run all tasks that should execute at current time
     */
    public function run() {
        foreach ($this->tasks as $task) {
            if ($this->shouldRun($task['expression'])) {
                $this->execute($task['command']);
            }
        }
    }
    
    /**
     * Execute specified command
     * @param string $command
     */
    public function execute($command) {
        list($app, $method) = explode('@', $command);
        
        // Try to load application class and execute method
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