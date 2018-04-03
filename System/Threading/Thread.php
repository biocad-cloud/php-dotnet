<?php

class Thread {

    const micro2Milli = 1000000;

    /*
     * Suspends the current thread for the specified number of milliseconds.
     *
     * @param millisecondsTimeout: The number of milliseconds for which the thread is suspended. 
     * If the value of the millisecondsTimeout argument is zero, the thread relinquishes the 
     * remainder of its time slice to any thread of equal priority that is ready to run. 
     * If there are no other threads of equal priority that are ready to run, execution of the 
     * current thread is not suspended.
     * 
     */
    public static function Sleep($millisecondsTimeout) {
        $sec  = $millisecondsTimeout / 1000;
        $msec = -1;

        if ($sec < 1) {
            $sec  = 0;
            $msec = $millisecondsTimeout * self::micro2Milli;
        } else {
            $sec  = intval($sec);
            $msec = ($millisecondsTimeout - $sec * 1000) * self::micro2Milli;
        }

        time_nanosleep ($sec, $msec);
    }
}

?>