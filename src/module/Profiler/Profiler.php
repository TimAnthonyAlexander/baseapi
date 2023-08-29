<?php

namespace timanthonyalexander\BaseApi\module\Profiler;

use timanthonyalexander\BaseApi\module\SystemConfig\SystemConfig;

class Profiler
{
    public static function start(string $subFunction = null): void
    {
        $subFunction = self::cleanSubFunction($subFunction);

        $callingClass  = debug_backtrace()[1]['class'];
        $callingMethod = debug_backtrace()[1]['function'] . '(' . ($subFunction) . ')';
        if (isset(debug_backtrace()[2]['class'], debug_backtrace()[2]['function'])) {
            $calledBy = debug_backtrace()[2]['class'] . '::' . debug_backtrace()[2]['function'];
        }

        $end = match (true) {
            str_contains($callingClass, 'module')     => 'module',
            str_contains($callingClass, 'model')      => 'model',
            str_contains($callingClass, 'controller') => 'controller',
            default                                   => 'other'
        };

        $callingClass = str_replace(['\\'], ['/'], $callingClass);

        $GLOBALS['profiler'][$callingClass][$callingMethod]['start']    = microtime(true);
        $GLOBALS['profiler'][$callingClass][$callingMethod]['calledBy'] = ($calledBy ?? '');
        $GLOBALS['profiler'][$callingClass][$callingMethod]['end']      = $end;
    } //end start()

    private static function cleanSubFunction(string $subFunction = null): string
    {
        if ($subFunction === null) {
            return (string) null;
        }

        if (str_contains($subFunction, '\\')) {
            $subFunctionArray = explode('\\', $subFunction);
            $subFunction      = (string) end($subFunctionArray) . '()';
        }

        return $subFunction;
    } //end cleanSubFunction()

    public static function stop(string $subFunction = null): void
    {
        $subFunction = self::cleanSubFunction($subFunction);

        $callingClass  = debug_backtrace()[1]['class'];
        $callingMethod = debug_backtrace()[1]['function'] . '(' . ($subFunction) . ')';

        $callingClass = str_replace(['\\'], ['/'], $callingClass);

        $GLOBALS['profiler'][$callingClass][$callingMethod]['stop'] = microtime(true);

        $GLOBALS['profiler'][$callingClass][$callingMethod]['calls']
            = ($GLOBALS['profiler'][$callingClass][$callingMethod]['calls'] ?? 0);
        $GLOBALS['profiler'][$callingClass][$callingMethod]['calls']++;

        if (isset($GLOBALS['profiler'][$callingClass][$callingMethod]['total'])) {
            $GLOBALS['profiler'][$callingClass][$callingMethod]['total']
                += ($GLOBALS['profiler'][$callingClass][$callingMethod]['stop'] - $GLOBALS['profiler'][$callingClass][$callingMethod]['start']);
        } else {
            $GLOBALS['profiler'][$callingClass][$callingMethod]['total']
                = ($GLOBALS['profiler'][$callingClass][$callingMethod]['stop'] - $GLOBALS['profiler'][$callingClass][$callingMethod]['start']);
        }

        // Now add the time of this call to ['perCall']
        $GLOBALS['profiler'][$callingClass][$callingMethod]['perCall']
            = ($GLOBALS['profiler'][$callingClass][$callingMethod]['total'] / $GLOBALS['profiler'][$callingClass][$callingMethod]['calls']);
    } //end stop()

    public static function get(): array
    {
        return ($GLOBALS['profiler'] ?? []);
    } //end get()

    public static function calculatePercentages(array $profiler): array
    {
        foreach ($profiler as $class => $profile) {
            $totalTime = 0;
            foreach ($profile as $function => $data) {
                $totalTime += $data['total'] ?? 0;
            }

            if ($totalTime === 0) {
                continue;
            }

            foreach ($profile as $function => $data) {
                $profiler[$class][$function]['percentage'] = (($data['total'] ?? 0) / $totalTime) * 100;
            }
        }

        return $profiler;
    }
}
