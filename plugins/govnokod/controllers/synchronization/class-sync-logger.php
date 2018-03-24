<?php

class SyncLogger {

    /**
     * @param string $message
     */
    public function log($message) {
        error_log(sprintf(
            "[%s] %s\n",
            current_time('mysql'),
            $message
        ), 3, __DIR__ . "/logs/log.txt");
    }

    /**
     * @param string|WP_Error $message
     */
    public function error($message) {
        if ($message instanceof WP_Error) {
            $err = $message;
            $messages = array();
            foreach ($err->get_error_codes() as $code) {
                $messages[] = $code . ': ' . $err->get_error_message($code);
            }
            $message = implode("\n", $messages);
        }
        $this->log($message);
    }
}