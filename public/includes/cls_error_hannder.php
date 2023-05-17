<?php
/**
 * Error and exception handler
 *
 * PHP version 7.0
 */
class ErrorHandlers
{
    static  $debug_mode = 0;
    static $path_log = '';
    static $file_log = '';

    public function __construct(){}

    /**
     * Register ErrorHandlers
     * @return [type] [description]
     */
    public function register($debug_mode = 0, $path_log = '')
    {
        self::$debug_mode = $debug_mode > 0 ? $debug_mode : self::$debug_mode;
        self::$path_log = !empty($path_log) ? $path_log : self::$path_log;

        self::$file_log = self::$path_log.'errors-hannder-'.date('Y-m-d').'.log';
        if (!file_exists(self::$file_log)) @mkdir(self::$path_log, 0755, true);

        set_error_handler(array($this, 'handleError'));
        set_exception_handler(array($this, 'exceptionHandler'));
        if (php_sapi_name() !== 'cli') {
            register_shutdown_function(array($this, 'handleShutdown'));
        }
    }

    /**
     * Error handler. Convert all errors to Exceptions by throwing an ErrorException.
     *
     * @param int $errno  Error level
     * @param string $errstr  Error message
     * @param string $errfile  Filename the error was raised in
     * @param int $errline  Line number in the file
     *
     * @return void
     */
    public static function handleError($errno, string $err_str, $errfile, $errline, $errcontext)
    {
        if (!(error_reporting() & $errno)) {
            //error do not match current error reporting level
            return true;
            //throw new ErrorException($err_str, 0, $errno, $errfile, $errline);
        }

        switch ($errno) {
            case E_ERROR:
                $errno_str = 'E_ERROR';
                break;
            case E_WARNING:
                $errno_str = 'E_WARNING';
                break;
            case E_PARSE:
                $errno_str = 'E_PARSE';
                break;
            case E_NOTICE:
                $errno_str = 'E_NOTICE';
                break;
            case E_CORE_ERROR:
                $errno_str = 'E_CORE_ERROR';
                break;
            case E_CORE_WARNING:
                $errno_str = 'E_CORE_WARNING';
                break;
            case E_COMPILE_ERROR:
                $errno_str = 'E_COMPILE_ERROR';
                break;
            case E_COMPILE_WARNING:
                $errno_str = 'E_COMPILE_WARNING ';
                break;
            case E_USER_ERROR  :
                $errno_str = 'E_USER_ERROR';
                break;
            case E_USER_WARNING:
                $errno_str = 'E_USER_WARNING';
                break;
            case E_USER_NOTICE:
                $errno_str = 'E_USER_NOTICE';
                break;
            case E_STRICT:
                $errno_str = 'E_STRICT';
                break;
            case E_DEPRECATED:
                $errno_str = 'E_DEPRECATED';
                break;
            case E_USER_DEPRECATED:
                $errno_str = 'E_USER_DEPRECATED';
                break;
            default:
                $errno_str = 'UNKNOWN';
        }

        $Exception = new Exception();
        $trace = $Exception->getTraceAsString();
        //Debug
        if(self::$debug_mode > 0){
            $errormsg = "<h1>Fatal error - ECShopVietnam.com </h1>";
            $errormsg .= "<p>The application could not run because of the following error: </p>";
            $errormsg .= "<p><strong>Type:</strong> $errno_str ($errno) </p>";
            $errormsg .= "<p><strong>Message:</strong> $err_str</p>";
            $errormsg .= "<p><strong>File:</strong> $errfile line $errline</p>";
            $errormsg .= "<h3>Stack trace:</h3>";
            $errormsg .= "<pre>$trace</pre>";
            $errormsg .= "<p>Please contact the developer to fix it.</p>";
            echo '<pre>'.$errormsg.'</pre>';
        }
        else{
            $message = "Message: $err_str at line $errfile in $errline\r\n Stack trace: \r\n$trace\r\n";
            self::witelog(self::$file_log,$message);
        }
    }

    public static function handleShutdown(){
         $error = error_get_last();
        if ($error !== null && $error['type'] === E_ERROR) {
            $message = "{$error['message']} \r\n";
            //Debug
            $errormsg = "<h1>Fatal error - ECShopVietnam.com </h1>";
            $errormsg .= "<p>The application could not run because of the following error: </p>";
            $errormsg .= "<p><strong>Type:</strong> handleShutdown ".$error['type']." </p>";

            if(self::$debug_mode > 0){
                $errormsg .= "<p><strong>Message:</strong> ".$error['message']." in ".$error['file']."</p>";
                $errormsg .= "<p><strong>Line:</strong> ".$error['line']."</p>";
            }
            else{
                $errormsg .= "<p>Please contact the developer to fix it.</p>";
                /* Write log to local for introduction mode */
                // $log = 'error-' . date('Y-m-d') . '.txt';
                // ini_set('error_log', $log);
                $message = "Message: ".$error['message']." at line ".$error['file']." in ".$error['line']."\r\n";
                //error_log($message);
                self::witelog(self::$file_log,$message);
            }
            echo '<pre>'.$errormsg.'</pre>';
            exit;
        }
    }

    /**
     * Exception handler.
     *
     * @param Exception $exception  The exception
     *
     * @return void
     */
    public static function exceptionHandler($exception)
    {
        $code = $exception->getCode();

        /* Show Message Error  to Client */
        $message_show = "<h1>Fatal error - ECShopVietnam.com </h1>";
        $message_show .= "<p>The application could not run because of the following error: </p>";
        $message_show .= "<p style='white-space:normal'>Message: '" . $exception->getMessage() . "'</p>";

        if(self::$debug_mode > 0){

            $message_show .= "<p>Stack trace: <br>" . $exception->getTraceAsString() . "</p>";
            $message_show .= "<p>Thrown in '" . $exception->getFile() . "' on line " . $exception->getLine() . "</p>";
        }
        else {
            // Code is 404 (not found) or 500 (general error) when mode introduction
            if ($code != 404) $code = 500;
            $message_show .= "<p>Please contact the developer to fix it.</p>";
            /* Write log to local */
            //$log = 'error-' . date('Y-m-d') . '.txt';
            //ini_set('error_log', $log);
            $message = "Uncaught exception: '" . get_class($exception) . "'";
            $message .= " with message '" . $exception->getMessage() . "'";
            $message .= "\nStack trace: " . $exception->getTraceAsString();
            $message .= "\nThrown in '" . $exception->getFile() . "' on line " . $exception->getLine();
            //error_log($message);
            self::witelog(self::$file_log,$message);
        }

        http_response_code($code);
        echo '<pre>'.$message_show.'</pre>';
        exit;
    }

    /**
     * [witelog description]
     * @param  [type] $filename [description]
     * @param  string $str      [description]
     * @return avoid
     */
    private static function witelog($filename, $str = ''){
        @file_put_contents($filename, date('Y-m-d H:i:s').' - ' .$str."\n\n", FILE_APPEND);
    }

}